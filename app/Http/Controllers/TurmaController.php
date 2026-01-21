<?php

namespace App\Http\Controllers;

use App\Models\Historico;
use App\Models\NivelEnsino;
use App\Models\Turma;
use App\Models\Turno;
use App\Services\AlertService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TurmaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Turma::with(['turno', 'nivelEnsino'])->withCount('alunos');

        // Para super admins e suporte, filtrar pela escola da sessão se definida
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
            }
            // Se não há escola na sessão, super admins veem todas as turmas (incluindo as com escola_id NULL)
        } else {
            // Para usuários normais, verificar permissão e filtrar por sua escola
            if (! auth()->user()->temPermissao('turmas.ver')) {
                abort(403, 'Acesso negado');
            }

            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            } else {
                abort(403, 'Usuário não está associado a uma escola');
            }
        }

        // Filtro de busca (aceita 'busca' e 'buscar')
        if ($request->filled('busca') || $request->filled('buscar')) {
            $busca = $request->input('busca', $request->input('buscar'));
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('codigo', 'like', "%{$busca}%")
                    ->orWhere('descricao', 'like', "%{$busca}%");
            });
        }

        // Filtro de status
        if ($request->filled('status')) {
            $status = $request->status;
            if (in_array($status, ['1', '0', 1, 0], true)) {
                $query->where('ativo', (int) $status);
            } else {
                switch ($status) {
                    case 'ativa':
                        $query->where('ativo', true);
                        break;
                    case 'inativa':
                        $query->where('ativo', false);
                        break;
                    case 'lotada':
                        $query->whereRaw('alunos_count >= COALESCE(capacidade, 0)');
                        break;
                    case 'vagas':
                        $query->whereRaw('alunos_count < COALESCE(capacidade, 0)');
                        break;
                }
            }
        }

        // Filtro de ano letivo
        if ($request->filled('ano_letivo')) {
            $query->where('ano_letivo', $request->ano_letivo);
        }

        // Filtro de turno
        if ($request->filled('turno')) {
            $query->whereHas('turno', function ($q) use ($request) {
                $q->where('nome', $request->turno);
            });
        }
        if ($request->filled('turno_id')) {
            $query->where('turno_id', $request->turno_id);
        }

        // Filtro de nível de ensino
        if ($request->filled('nivel_ensino_id')) {
            $query->where('nivel_ensino_id', $request->nivel_ensino_id);
        }

        // Ordenação dinâmica (preferir sort/direction, aceitar fallback 'ordenar')
        $allowedSorts = ['id', 'nome', 'codigo', 'ocupacao', 'ativo', 'created_at', 'turno', 'nivel_ensino'];
        $sort = $request->input('sort');
        $fallbackOrdenar = $request->input('ordenar');
        if (! $sort && $fallbackOrdenar) {
            // Mapear valores antigos para novos
            $mapOrdenar = [
                'nome' => 'nome',
                'codigo' => 'codigo',
                'ocupacao' => 'ocupacao',
                'criado' => 'created_at',
                'turno' => 'turno',
                'nivel' => 'nivel_ensino',
            ];
            $sort = $mapOrdenar[$fallbackOrdenar] ?? 'nome';
        }
        if (! in_array($sort, $allowedSorts, true)) {
            // Padrão
            $sort = 'nome';
        }
        $direction = strtolower($request->input('direction', $sort === 'created_at' ? 'desc' : 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sort === 'ocupacao') {
            // Evitar divisão por zero
            $query->orderByRaw('CASE WHEN capacidade IS NULL OR capacidade = 0 THEN 0 ELSE (alunos_count * 1.0) / capacidade END '.$direction);
        } elseif ($sort === 'turno') {
            // Ordenar pelo nome do turno (relação belongsTo)
            $query->leftJoin('turnos', 'turnos.id', '=', 'turmas.turno_id')
                ->select('turmas.*')
                ->orderBy('turnos.nome', $direction);
        } elseif ($sort === 'nivel_ensino') {
            // Ordenar pelo nome do nível de ensino (relação belongsTo)
            $query->leftJoin('niveis_ensino', 'niveis_ensino.id', '=', 'turmas.nivel_ensino_id')
                ->select('turmas.*')
                ->orderBy('niveis_ensino.nome', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        // Paginação e preservação de query string
        $turmas = $query->paginate(12)->withQueryString();

        // Carregar turnos, grupos e coordenadores para os filtros e modals
        $escola_id = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')
            ? (session('escola_atual') ?: auth()->user()->escola_id)
            : auth()->user()->escola_id;

        $turnos = Turno::where('ativo', true)
            ->where('escola_id', $escola_id)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $niveisEnsino = NivelEnsino::whereHas('escolaNiveisConfig', function ($query) use ($escola_id) {
            $query->where('escola_id', $escola_id);
        })
            ->orderBy('nome')
            ->get();

        // Buscar coordenadores da escola
        $coordenadores = \App\Models\User::where('escola_id', $escola_id)
            ->whereHas('cargos', function ($q) {
                $q->where('nome', 'like', '%Coordenador%')
                    ->orWhere('tipo_cargo', 'coordenador');
            })
            ->where('ativo', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.turmas.index', compact('turmas', 'turnos', 'niveisEnsino', 'coordenadores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'turno_id' => 'required|exists:turnos,id',
                'nivel_ensino_id' => 'required|exists:niveis_ensino,id',
                'coordenador_id' => 'nullable|exists:users,id',
                'capacidade' => 'nullable|integer|min:1|max:100',
                'ano_letivo' => 'required|integer|min:2000|max:2100',
                'descricao' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                AlertService::validacao('Dados inválidos', $validator->errors()->toArray());

                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->all();
            // Definir ativo como true por padrão se não especificado
            $data['ativo'] = $request->has('ativo') ? (bool) $request->input('ativo') : true;

            // Definir escola_id baseado no usuário
            if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
                $data['escola_id'] = session('escola_atual') ?: auth()->user()->escola_id;
            } else {
                $data['escola_id'] = auth()->user()->escola_id;
            }

            // Verificar se escola_id foi definido
            if (! $data['escola_id']) {
                AlertService::erro('Erro de configuração', 'Não foi possível determinar a escola. Selecione uma escola primeiro.');

                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível determinar a escola. Selecione uma escola primeiro.',
                ], 422);
            }

            // Manter nivel_ensino_id para associar a turma ao nível de ensino

            // Gerar código automaticamente
            $prefix = substr(strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $data['nome'])), 0, 3);
            $escolaPrefix = substr(strtoupper(str_replace(' ', '', auth()->user()->escola->nome ?? 'ESC')), 0, 3);
            $anoSuffix = substr($data['ano_letivo'], -2);
            $randomPart = strtoupper(substr(md5(uniqid()), 0, 4));

            $data['codigo'] = "{$escolaPrefix}{$prefix}{$anoSuffix}{$randomPart}";

            // Garantir que o código seja único
            $count = 1;
            $originalCodigo = $data['codigo'];
            while (Turma::where('codigo', $data['codigo'])->exists()) {
                $data['codigo'] = $originalCodigo.$count;
                $count++;
            }

            DB::beginTransaction();

            $turma = Turma::create($data);

            // Registrar no histórico
            Historico::registrar(
                'criado',
                'Turma',
                $turma->id,
                null, // dados antigos (não há para criação)
                $turma->toArray()
            );

            DB::commit();

            AlertService::success('Turma criada com sucesso!');

            return response()->json([
                'success' => true,
                'message' => 'Turma criada com sucesso!',
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            AlertService::error('Ocorreu um erro ao criar a turma. Tente novamente.');

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Turma $turma)
    {
        try {
            // Verificar se o usuário pode atualizar esta turma
            if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
                if (session('escola_atual') && $turma->escola_id !== session('escola_atual')) {
                    abort(404);
                }
            } else {
                if (auth()->user()->escola_id && $turma->escola_id !== auth()->user()->escola_id) {
                    abort(404);
                }
            }

            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'turno_id' => 'required|exists:turnos,id',
                'nivel_ensino_id' => 'required|exists:niveis_ensino,id',
                'coordenador_id' => 'nullable|exists:users,id',
                'capacidade' => 'nullable|integer|min:1|max:100',
                'ano_letivo' => 'required|integer|min:2000|max:2100',
                'descricao' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                AlertService::validacao('Dados inválidos', $validator->errors()->toArray());

                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Armazenar dados antigos para o histórico
            $dadosAntigos = $turma->toArray();

            $data = $request->all();
            // Definir ativo baseado no checkbox ou manter valor atual se não especificado
            $data['ativo'] = $request->has('ativo') ? (bool) $request->input('ativo') : $turma->ativo;

            // Manter nivel_ensino_id para associar a turma ao nível de ensino

            DB::beginTransaction();

            $turma->update($data);

            // Registrar no histórico
            Historico::registrar(
                'atualizado',
                'Turma',
                $turma->id,
                $dadosAntigos,
                $turma->fresh()->toArray()
            );

            DB::commit();

            AlertService::success('Turma atualizada com sucesso!');

            return response()->json([
                'success' => true,
                'message' => 'Turma atualizada com sucesso!',
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            AlertService::error('Ocorreu um erro ao atualizar a turma. Tente novamente.');

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Turma $turma)
    {
        // Verificar se o usuário pode visualizar esta turma
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
            if (session('escola_atual') && $turma->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turma->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $turma->load(['turno', 'nivelEnsino', 'escola', 'salas', 'coordenador']);
        // Contar alunos e anexar dados úteis para o wizard
        $turma->loadCount('alunos');
        // Adicionar a primeira sala associada como atributo simples
        $turma->setAttribute('sala', optional($turma->salas->first())->nome);

        // Carregar dados necessários para o formulário de edição
        $escola_id = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')
            ? (session('escola_atual') ?: auth()->user()->escola_id)
            : auth()->user()->escola_id;

        $turnos = \App\Models\Turno::where('ativo', true)
            ->where('escola_id', $escola_id)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $niveisEnsino = \App\Models\NivelEnsino::whereHas('escolaNiveisConfig', function ($query) use ($escola_id) {
            $query->where('escola_id', $escola_id);
        })
            ->orderBy('nome')
            ->get();

        $coordenadores = \App\Models\User::where('escola_id', $escola_id)
            ->whereHas('cargos', function ($q) {
                $q->where('nome', 'like', '%Coordenador%')
                    ->orWhere('tipo_cargo', 'coordenador');
            })
            ->where('ativo', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'turma' => $turma,
            'turnos' => $turnos,
            'niveisEnsino' => $niveisEnsino,
            'coordenadores' => $coordenadores,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Turma $turma)
    {
        // Verificar se o usuário pode editar esta turma
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
            if (session('escola_atual') && $turma->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turma->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        // Carregar dados necessários para o formulário
        $escola_id = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')
            ? (session('escola_atual') ?: auth()->user()->escola_id)
            : auth()->user()->escola_id;

        $turnos = \App\Models\Turno::where('ativo', true)
            ->where('escola_id', $escola_id)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $niveisEnsino = \App\Models\NivelEnsino::whereHas('escolaNiveisConfig', function ($query) use ($escola_id) {
            $query->where('escola_id', $escola_id);
        })
            ->orderBy('nome')
            ->get();

        $coordenadores = \App\Models\User::where('escola_id', $escola_id)
            ->whereHas('cargos', function ($q) {
                $q->where('nome', 'like', '%Coordenador%')
                    ->orWhere('tipo_cargo', 'coordenador');
            })
            ->where('ativo', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'turma' => $turma,
            'turnos' => $turnos,
            'niveisEnsino' => $niveisEnsino,
            'coordenadores' => $coordenadores,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Turma $turma)
    {
        try {
            // Verificar se o usuário pode excluir esta turma
            if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
                if (session('escola_atual') && $turma->escola_id !== session('escola_atual')) {
                    abort(404);
                }
            } else {
                if (auth()->user()->escola_id && $turma->escola_id !== auth()->user()->escola_id) {
                    abort(404);
                }
            }

            // Verificar se a turma tem alunos associados
            if ($turma->alunos()->count() > 0) {
                AlertService::warning('Não é possível excluir esta turma pois existem alunos associados a ela.');

                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir esta turma pois existem alunos associados a ela.',
                ], 422);
            }

            // Verificar se a turma tem planejamentos associados
            if ($turma->planejamentos()->count() > 0) {
                AlertService::warning('Não é possível excluir esta turma pois existem planejamentos associados a ela.');

                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir esta turma pois existem planejamentos associados a ela.',
                ], 422);
            }

            // Armazenar dados da turma para o histórico antes da exclusão
            $dadosTurma = $turma->toArray();

            DB::beginTransaction();

            $turma->delete();

            // Registrar no histórico
            Historico::registrar(
                'excluido',
                'Turma',
                $dadosTurma['id'],
                $dadosTurma,
                null // dados novos (não há para exclusão)
            );

            DB::commit();

            AlertService::success('Turma excluída com sucesso!');

            return response()->json([
                'success' => true,
                'message' => 'Turma excluída com sucesso!',
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            AlertService::error('Ocorreu um erro ao excluir a turma. Tente novamente.');

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.',
            ], 500);
        }
    }

    /**
     * Obter alunos da turma para o modal
     */
    public function getAlunos(Turma $turma)
    {
        // Verificar se o usuário pode ver esta turma
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
            if (session('escola_atual') && $turma->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turma->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $alunosCollection = $turma->alunos()
            ->select('id', 'nome', 'email', 'telefone', 'data_nascimento', 'created_at', 'ativo')
            ->orderBy('nome')
            ->get();

        // Identificar alunos com transferência pendente
        $alunoIds = $alunosCollection->pluck('id');
        $pendentesIds = \App\Models\Transferencia::pendentes()
            ->whereIn('aluno_id', $alunoIds)
            ->pluck('aluno_id')
            ->toArray();

        $alunos = $alunosCollection->map(function ($aluno) use ($pendentesIds) {
            return [
                'id' => $aluno->id,
                'nome' => $aluno->nome,
                'email' => $aluno->email,
                'telefone' => $aluno->telefone,
                'idade' => $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->age : null,
                'data_matricula' => $aluno->created_at->format('d/m/Y'),
                'ativo' => $aluno->ativo,
                'transferencia_pendente' => in_array($aluno->id, $pendentesIds),
            ];
        });

        return response()->json([
            'success' => true,
            'alunos' => $alunos,
            'total' => $alunos->count(),
            'turma' => [
                'id' => $turma->id,
                'nome' => $turma->nome,
                'codigo' => $turma->codigo,
                'capacidade' => $turma->capacidade,
                'vagas_disponiveis' => $turma->vagas_disponiveis,
            ],
        ]);
    }

    /**
     * Obter alunos disponíveis para adicionar à turma
     */
    public function getAlunosDisponiveis(Request $request, Turma $turma)
    {
        // Verificar se o usuário pode editar esta turma
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
            if (session('escola_atual') && $turma->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turma->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $busca = $request->get('busca', '');
        $mode = $request->get('mode'); // 'disponiveis' | 'outras' | null (ambas)
        $perPage = max(1, (int) $request->get('per_page', 10));
        $pageDisponiveis = max(1, (int) $request->get('page_disponiveis', 1));
        $pageOutras = max(1, (int) $request->get('page_outras', 1));
        $escola_id = $turma->escola_id;

        // Buscar alunos disponíveis (sem turma)
        $queryDisponiveis = \App\Models\Aluno::where('escola_id', $escola_id)
            ->where('ativo', true)
            ->whereNull('turma_id');

        if ($busca) {
            $queryDisponiveis->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%")
                    ->orWhere('cpf', 'like', "%{$busca}%");
            });
        }

        // Paginação para disponíveis
        $totalDisponiveis = (clone $queryDisponiveis)->count();
        $alunosDisponiveis = [];
        $hasMoreDisponiveis = false;
        if (! $mode || $mode === 'disponiveis') {
            $alunosDisponiveis = (clone $queryDisponiveis)
                ->select('id', 'nome', 'sobrenome', 'email', 'data_nascimento')
                ->orderBy('nome')
                ->skip(($pageDisponiveis - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function ($aluno) {
                    return [
                        'id' => $aluno->id,
                        'nome' => $aluno->nome,
                        'sobrenome' => $aluno->sobrenome,
                        'email' => $aluno->email,
                        'idade' => $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->age : null,
                    ];
                });
            $hasMoreDisponiveis = ($pageDisponiveis * $perPage) < $totalDisponiveis;
        }

        // Buscar alunos em outras turmas
        $queryOutrasTurmas = \App\Models\Aluno::where('escola_id', $escola_id)
            ->where('ativo', true)
            ->whereNotNull('turma_id')
            ->where('turma_id', '!=', $turma->id)
            ->with('turma:id,nome');

        if ($busca) {
            $queryOutrasTurmas->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%")
                    ->orWhere('cpf', 'like', "%{$busca}%");
            });
        }

        // Paginação para outras turmas
        $totalOutras = (clone $queryOutrasTurmas)->count();
        $alunosOutrasTurmas = [];
        $hasMoreOutras = false;
        if (! $mode || $mode === 'outras') {
            $alunosOutrasTurmas = (clone $queryOutrasTurmas)
                ->select('id', 'nome', 'sobrenome', 'email', 'data_nascimento', 'turma_id')
                ->orderBy('nome')
                ->skip(($pageOutras - 1) * $perPage)
                ->take($perPage)
                ->get()
                ->map(function ($aluno) {
                    return [
                        'id' => $aluno->id,
                        'nome' => $aluno->nome,
                        'sobrenome' => $aluno->sobrenome,
                        'email' => $aluno->email,
                        'idade' => $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->age : null,
                        'turma_atual' => $aluno->turma ? $aluno->turma->nome : 'Turma não encontrada',
                        'turma_atual_id' => $aluno->turma_id,
                    ];
                });
            $hasMoreOutras = ($pageOutras * $perPage) < $totalOutras;
        }

        // Resposta flexível para modo único ou ambos
        $response = [
            'success' => true,
        ];
        if (! $mode || $mode === 'disponiveis') {
            $response['disponiveis'] = $alunosDisponiveis;
            $response['has_more_disponiveis'] = $hasMoreDisponiveis;
        }
        if (! $mode || $mode === 'outras') {
            $response['outras_turmas'] = $alunosOutrasTurmas;
            $response['has_more_outras'] = $hasMoreOutras;
        }

        return response()->json($response);
    }

    /**
     * Adicionar aluno à turma
     */
    public function adicionarAluno(Request $request, Turma $turma)
    {
        // Verificar se o usuário pode editar esta turma
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
            if (session('escola_atual') && $turma->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turma->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $request->validate([
            'aluno_id' => 'required|exists:alunos,id',
        ]);

        $aluno = \App\Models\Aluno::findOrFail($request->aluno_id);

        // Verificar se o aluno pertence à mesma escola
        if ($aluno->escola_id !== $turma->escola_id) {
            return response()->json([
                'success' => false,
                'message' => 'O aluno não pertence à mesma escola da turma.',
            ], 422);
        }

        // Verificar se o aluno já está na turma
        if ($aluno->turma_id === $turma->id) {
            return response()->json([
                'success' => false,
                'message' => 'O aluno já está matriculado nesta turma.',
            ], 422);
        }

        // Verificar se há vagas disponíveis
        if ($turma->alunos()->count() >= $turma->capacidade) {
            return response()->json([
                'success' => false,
                'message' => 'A turma já atingiu sua capacidade máxima.',
            ], 422);
        }

        // Adicionar aluno à turma
        $aluno->update(['turma_id' => $turma->id]);

        return response()->json([
            'success' => true,
            'message' => 'Aluno adicionado à turma com sucesso!',
            'aluno' => [
                'id' => $aluno->id,
                'nome' => $aluno->nome,
                'email' => $aluno->email,
                'idade' => $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->age : null,
                'data_matricula' => now()->format('d/m/Y'),
            ],
        ]);
    }

    /**
     * Remover aluno da turma
     */
    public function removerAluno(Turma $turma, \App\Models\Aluno $aluno)
    {
        // Verificar se o usuário pode editar esta turma
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
            if (session('escola_atual') && $turma->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turma->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        // Verificar se o aluno está na turma
        if ($aluno->turma_id !== $turma->id) {
            return response()->json([
                'success' => false,
                'message' => 'O aluno não está matriculado nesta turma.',
            ], 422);
        }

        // Remover aluno da turma
        $aluno->update(['turma_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Aluno removido da turma com sucesso!',
        ]);
    }

    /**
     * Adicionar múltiplos alunos à turma
     */
    public function adicionarAlunos(Request $request, Turma $turma)
    {
        // Verificar se o usuário pode editar esta turma
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
            if (session('escola_atual') && $turma->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turma->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $request->validate([
            'alunos' => 'required|array|min:1',
            'alunos.*' => 'required|exists:alunos,id',
        ]);

        $alunosIds = $request->alunos;
        $alunosAdicionados = [];
        $erros = [];

        foreach ($alunosIds as $alunoId) {
            $aluno = \App\Models\Aluno::find($alunoId);

            if (! $aluno) {
                $erros[] = "Aluno com ID {$alunoId} não encontrado.";

                continue;
            }

            // Verificar se o aluno pertence à mesma escola
            if ($aluno->escola_id !== $turma->escola_id) {
                $erros[] = "O aluno {$aluno->nome} não pertence à mesma escola da turma.";

                continue;
            }

            // Verificar se o aluno já está na turma
            if ($aluno->turma_id === $turma->id) {
                $erros[] = "O aluno {$aluno->nome} já está matriculado nesta turma.";

                continue;
            }

            // Verificar se há vagas disponíveis
            $alunosNaTurma = $turma->alunos()->count() + count($alunosAdicionados);
            if ($alunosNaTurma >= $turma->capacidade) {
                $erros[] = 'A turma já atingiu sua capacidade máxima. Não é possível adicionar mais alunos.';
                break;
            }

            // Adicionar aluno à turma
            $aluno->update(['turma_id' => $turma->id]);
            $alunosAdicionados[] = [
                'id' => $aluno->id,
                'nome' => $aluno->nome,
                'email' => $aluno->email,
                'idade' => $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->age : null,
                'data_matricula' => now()->format('d/m/Y'),
            ];
        }

        if (count($alunosAdicionados) > 0 && count($erros) === 0) {
            return response()->json([
                'success' => true,
                'message' => count($alunosAdicionados) === 1
                    ? 'Aluno adicionado com sucesso!'
                    : count($alunosAdicionados).' alunos adicionados com sucesso!',
                'alunos' => $alunosAdicionados,
            ]);
        } elseif (count($alunosAdicionados) > 0 && count($erros) > 0) {
            return response()->json([
                'success' => true,
                'message' => count($alunosAdicionados).' alunos adicionados com sucesso, mas alguns erros ocorreram.',
                'alunos' => $alunosAdicionados,
                'erros' => $erros,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum aluno pôde ser adicionado.',
                'erros' => $erros,
            ], 422);
        }
    }

    /**
     * Listar todas as turmas para transferência de alunos
     */
    public function listarTodas(Request $request)
    {
        // Determinar escola_id baseado no usuário
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
            $escola_id = session('escola_atual') ?: auth()->user()->escola_id;
        } else {
            $escola_id = auth()->user()->escola_id;
        }

        if (! $escola_id) {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível determinar a escola.',
            ], 422);
        }

        $query = \App\Models\Turma::where('escola_id', $escola_id)
            ->where('ativo', true)
            ->with(['turno:id,nome', 'nivelEnsino:id,nome'])
            ->withCount('alunos');

        // Filtro de busca
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('codigo', 'like', "%{$busca}%");
            });
        }

        // Excluir turma atual se especificada
        if ($request->filled('excluir_turma_id')) {
            $query->where('id', '!=', $request->excluir_turma_id);
        }

        $turmas = $query->orderBy('nome')
            ->limit(20)
            ->get()
            ->map(function ($turma) {
                return [
                    'id' => $turma->id,
                    'nome' => $turma->nome,
                    'codigo' => $turma->codigo,
                    'turno' => $turma->turno ? $turma->turno->nome : 'N/A',
                    'nivel_ensino' => $turma->nivelEnsino ? $turma->nivelEnsino->nome : 'N/A',
                    'capacidade' => $turma->capacidade,
                    'alunos_count' => $turma->alunos_count,
                    'vagas_disponiveis' => $turma->capacidade - $turma->alunos_count,
                    'tem_vagas' => ($turma->capacidade - $turma->alunos_count) > 0,
                ];
            });

        return response()->json([
            'success' => true,
            'turmas' => $turmas,
        ]);
    }

    /**
     * Transferir aluno entre turmas
     */
    public function transferirAluno(Request $request)
    {
        $request->validate([
            'aluno_id' => 'required|exists:alunos,id',
            'turma_destino_id' => 'required|exists:turmas,id',
        ]);

        $aluno = \App\Models\Aluno::findOrFail($request->aluno_id);
        $turmaDestino = \App\Models\Turma::findOrFail($request->turma_destino_id);

        // Determinar escola_id baseado no usuário
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
            $escola_id = session('escola_atual') ?: auth()->user()->escola_id;
        } else {
            $escola_id = auth()->user()->escola_id;
        }

        // Verificar se o aluno e a turma pertencem à mesma escola do usuário
        if ($aluno->escola_id !== $escola_id || $turmaDestino->escola_id !== $escola_id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para transferir este aluno ou para esta turma.',
            ], 403);
        }

        // Verificar se o aluno já está na turma de destino
        if ($aluno->turma_id === $turmaDestino->id) {
            return response()->json([
                'success' => false,
                'message' => 'O aluno já está matriculado nesta turma.',
            ], 422);
        }

        // Verificar se há vagas disponíveis na turma de destino
        $alunosNaTurmaDestino = $turmaDestino->alunos()->count();
        if ($alunosNaTurmaDestino >= $turmaDestino->capacidade) {
            return response()->json([
                'success' => false,
                'message' => 'A turma de destino não possui vagas disponíveis.',
            ], 422);
        }

        // Obter nome da turma atual para o log
        $turmaAtual = $aluno->turma ? $aluno->turma->nome : 'Sem turma';

        // Realizar a transferência
        $aluno->update(['turma_id' => $turmaDestino->id]);

        return response()->json([
            'success' => true,
            'message' => "Aluno {$aluno->nome} transferido com sucesso de '{$turmaAtual}' para '{$turmaDestino->nome}'.",
        ]);
    }

    /**
     * Toggle status ativo/inativo da turma
     */
    public function toggleStatus(Turma $turma)
    {
        try {
            // Verificar se o usuário pode alterar o status desta turma
            if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte Técnico')) {
                if (session('escola_atual') && $turma->escola_id !== session('escola_atual')) {
                    abort(404);
                }
            } else {
                if (auth()->user()->escola_id && $turma->escola_id !== auth()->user()->escola_id) {
                    abort(404);
                }
            }

            // Armazenar dados antigos para o histórico
            $dadosAntigos = $turma->toArray();

            DB::beginTransaction();

            // Alternar o status
            $novoStatus = ! $turma->ativo;
            $turma->update(['ativo' => $novoStatus]);

            // Registrar no histórico
            $acao = $novoStatus ? 'ativado' : 'inativado';
            Historico::registrar(
                $acao,
                'Turma',
                $turma->id,
                $dadosAntigos,
                $turma->fresh()->toArray()
            );

            DB::commit();

            $statusTexto = $novoStatus ? 'ativada' : 'inativada';
            AlertService::sucesso('Status alterado', "A turma foi {$statusTexto} com sucesso!");

            return response()->json([
                'success' => true,
                'message' => "Turma {$statusTexto} com sucesso!",
                'novo_status' => $novoStatus,
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            AlertService::sistema('Erro interno', 'Ocorreu um erro ao alterar o status da turma. Tente novamente.');

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.',
            ], 500);
        }
    }
}
