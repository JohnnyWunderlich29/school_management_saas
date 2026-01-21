<?php

namespace App\Http\Controllers;

use App\Models\Planejamento;
use App\Models\PlanejamentoDiario;
use App\Models\CampoExperiencia;
use App\Models\SaberConhecimento;
use App\Models\ObjetivoAprendizagem;
use App\Models\Sala;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\AlertService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Actions\Planejamento\CreatePlanejamentoAction;
use App\Actions\Planejamento\UpdatePlanejamentoAction;

class PlanejamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Se for coordenador, mostrar planejamentos das salas sob sua coordenação
        // Se for admin, mostrar todos os planejamentos
        // Se for professor, mostrar apenas seus planejamentos
        if ($user->isAdminOrCoordinator() || $user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $query = Planejamento::with(['user', 'turma']);
        } else {
            $query = Planejamento::with(['user', 'turma'])
                ->where('user_id', Auth::id());
        }

        // Filtro por modalidade
        if ($request->filled('modalidade')) {
            $query->where('modalidade', $request->modalidade);
        }

        // Filtro por turno
        if ($request->filled('turno')) {
            $query->where('turno', $request->turno);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por turma_id (select)
        if ($request->filled('turma_id')) {
            $query->where('turma_id', $request->turma_id);
        }

        // Filtro por turma (busca por nome/código/descrição da turma)
        if ($request->filled('turma')) {
            $buscaTurma = trim($request->turma);
            $query->whereHas('turma', function($q) use ($buscaTurma) {
                $q->where('nome', 'like', "%{$buscaTurma}%")
                  ->orWhere('codigo', 'like', "%{$buscaTurma}%")
                  ->orWhere('descricao', 'like', "%{$buscaTurma}%");
            });
        }

        // Filtro por sala removido - será implementado futuramente

        // Filtro por data de início
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_inicio', '>=', $request->data_inicio);
        }

        // Filtro por data de fim
        if ($request->filled('data_fim')) {
            $query->whereDate('data_fim', '<=', $request->data_fim);
        }

        // Padronizar por escola atual para todos os perfis (incl. superadmin/suporte)
        \Illuminate\Support\Facades\Log::info(
            'Consultando planejamentos com status: ' . request('status', 'todos') .
            ' | Escola atual: ' . (\App\Http\Middleware\EscolaContext::getEscolaAtual() ?: 'nenhuma')
        );

        // Ordenação dinâmica por parâmetros sort/direction
        $allowedSorts = ['id', 'titulo', 'turno', 'data_inicio', 'status', 'created_at'];
        $sort = in_array($request->get('sort'), $allowedSorts) ? $request->get('sort') : 'created_at';
        $direction = in_array($request->get('direction'), ['asc', 'desc']) ? $request->get('direction') : 'desc';

        $planejamentos = $query
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        // Log para debug - mostrar quantos planejamentos foram encontrados
        \Illuminate\Support\Facades\Log::info('Planejamentos encontrados: ' . $planejamentos->total() .
            ' (Status: ' . implode(', ', $planejamentos->pluck('status')->unique()->toArray()) . ')');

        // Buscar todas as salas para o filtro
        $todasSalas = Sala::ativas()->orderBy('codigo')->get();

        // Carregar turmas da escola atual para o filtro de select
        $userCtx = auth()->user();
        if ($userCtx->isSuperAdmin() || $userCtx->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $userCtx->escola_id;
        } else {
            $escolaId = $userCtx->escola_id;
        }

        if ($escolaId) {
            $turmas = Turma::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('nome')
                ->get();
        } else {
            $turmas = collect();
        }

        // Definir datas padrão para o filtro
        $dataInicio = $request->get('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->get('data_fim', now()->endOfMonth()->format('Y-m-d'));

        return view('planejamentos.index', compact('planejamentos', 'todasSalas', 'dataInicio', 'dataFim', 'turmas'));
    }

    /**
     * Retorna modalidades de ensino que possuem salas cadastradas.
     */
    public function getModalidadesComSalas(Request $request)
    {
        try {
            // Determinar escola_id para filtros (seguindo padrão do sistema)
            $user = auth()->user();
            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $escolaId = session('escola_atual') ?: $user->escola_id;
            } else {
                $escolaId = $user->escola_id;
            }

            // Se não há escola definida, retornar array vazio
            if (!$escolaId) {
                return response()->json([]);
            }

            // Buscar modalidades que possuem grupos com turmas que têm grade de aulas com salas
            // FILTRADO POR ESCOLA através do relacionamento turma->escola_id
            $modalidades = \App\Models\ModalidadeEnsino::whereHas('grupos', function ($query) use ($escolaId) {
                $query->where('ativo', true)
                    ->whereHas('turmas', function ($turmaQuery) use ($escolaId) {
                        $turmaQuery->where('ativo', true)
                            ->where('escola_id', $escolaId) // FILTRO POR ESCOLA
                            ->whereHas('gradeAulas', function ($gradeQuery) {
                                $gradeQuery->where('ativo', true)
                                    ->whereHas('sala', function ($salaQuery) {
                                        $salaQuery->where('ativo', true);
                                    });
                            });
                    });
            })
                ->ativas()
                ->ordenados()
                ->get(['id', 'nome', 'codigo']);

            return response()->json($modalidades);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar modalidades com salas: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar modalidades.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna os turnos associados a uma modalidade de ensino.
     */
    public function getTurnosPorModalidade(Request $request)
    {
        try {
            $modalidadeEnsinoId = $request->input('modalidade_ensino_id');
            $modalidadeEnsino = \App\Models\ModalidadeEnsino::find($modalidadeEnsinoId);

            if (!$modalidadeEnsino) {
                return response()->json([]);
            }

            $turnos = $modalidadeEnsino->turnos()->where('turnos.ativo', true)->orderBy('turnos.nome')->get(['turnos.id', 'turnos.nome']);

            return response()->json($turnos);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Illuminate\Support\Facades\Log::error('Erro ao buscar turnos por modalidade: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar turnos.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna os grupos associados a uma modalidade de ensino e turno.
     */
    public function getGruposPorModalidadeTurno(Request $request)
    {
        try {
            $modalidadeEnsinoId = $request->input('modalidade_ensino_id');
            $turnoId = $request->input('turno_id');

            $grupos = \App\Models\Grupo::ativos()
                ->where('modalidade_ensino_id', $modalidadeEnsinoId)
                ->whereHas('turnos', function ($query) use ($turnoId) {
                    $query->where('turnos.id', $turnoId);
                })
                ->orderBy('nome')
                ->get(['id', 'nome']);

            return response()->json($grupos);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar grupos por modalidade e turno: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar grupos.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna as turmas associadas a um grupo e turno.
     */
    public function getTurmasPorGrupoTurno(Request $request)
    {
        try {
            $grupoId = $request->input('grupo_id');
            $turnoId = $request->input('turno_id');
            $escolaId = auth()->user()->escola_id;

            $grupo = \App\Models\Grupo::find($grupoId);
            $turno = \App\Models\Turno::find($turnoId);

            if (!$grupo || !$turno) {
                return response()->json([]);
            }

            $modalidadeEnsinoId = $grupo->modalidade_ensino_id;
            $turnoCodigo = $turno->codigo;

            $turmas = \App\Models\Turma::ativas()
                ->whereHas('nivelEnsino', function ($query) use ($modalidadeEnsinoId, $turnoCodigo) {
                    $query->porModalidade($modalidadeEnsinoId)
                        ->porTurno($turnoCodigo);
                })
                ->whereHas('salas', function ($query) use ($escolaId) {
                    $query->where('escola_id', $escolaId)
                        ->where('ativo', true);
                })
                ->orderBy('nome')
                ->get(['id', 'nome']);

            return response()->json($turmas);
        } catch (\Exception $e) {

            \Illuminate\Support\Facades\Log::error('Erro ao buscar turmas por grupo e turno: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar turmas.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna os professores associados a uma turma e disciplina.
     */
    public function getProfessoresPorTurmaDisciplina(Request $request)
    {
        try {
            $turmaId = $request->input('turma_id');
            $disciplinaId = $request->input('disciplina_id');
            $escolaId = auth()->user()->escola_id;

            if (!$turmaId || !$disciplinaId) {
                return response()->json(['error' => 'Parâmetros turma_id e disciplina_id são obrigatórios.'], 400);
            }

            // Buscar professores pela grade de aulas, garantindo vínculo turma-disciplina-professor
            $gradeEntries = \App\Models\GradeAula::where('turma_id', $turmaId)
                ->where('disciplina_id', $disciplinaId)
                ->get();

            $professores = $gradeEntries
                ->map(function ($ga) {
                    return $ga->professor ? $ga->professor->user : null;
                })
                ->filter()
                ->unique('id')
                ->filter(function ($u) use ($escolaId) {
                    return $u && $u->ativo && ($u->escola_id == $escolaId);
                })
                ->sortBy('name')
                ->values()
                ->map(function ($u) {
                    return ['id' => $u->id, 'name' => $u->name];
                });

            return response()->json($professores);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar professores por turma e disciplina: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar professores.', 'message' => $e->getMessage()], 500);
        }
    }



    // Lógica para buscar disciplinas por turma
    public function getDisciplinasPorTurma(Request $request)
    {
        try {
            $turmaId = $request->input('turma_id');
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
            $userId = auth()->id();

            \Illuminate\Support\Facades\Log::info("Buscando disciplinas para usuário ID: {$userId}, turma ID: {$turmaId}, escola ID: {$escolaId}");

            $turma = \App\Models\Turma::find($turmaId);

            if (!$turma) {
                return response()->json(['error' => 'Turma não encontrada.'], 404);
            }

            // Usar o nível de ensino da turma para buscar disciplinas
            if (!$turma->nivel_ensino_id) {
                return response()->json(['error' => 'Nível de ensino não encontrado para esta turma.'], 404);
            }

            // Buscar disciplinas ativas vinculadas ao nível de ensino da turma
            $disciplinas = \App\Models\Disciplina::select('id', 'nome')
                ->whereHas('niveisEnsino', function($query) use ($turma) {
                    $query->where('nivel_ensino_id', $turma->nivel_ensino_id);
                })
                ->where('ativo', true)
                ->orderBy('ordem')
                ->orderBy('nome')
                ->get();

            \Illuminate\Support\Facades\Log::info("Disciplinas encontradas: " . $disciplinas->count());

            return response()->json($disciplinas);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar disciplinas por turma: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar disciplinas.', 'message' => $e->getMessage()], 500);
        }
    }



    /**
     * Retorna as disciplinas associadas a uma modalidade, turno e grupo.
     */
    public function getDisciplinasPorModalidadeTurnoGrupo(Request $request)
    {
        // Validação direta dos parâmetros
        $validated = $request->validate([
            'modalidade_id' => 'required|integer',
            'turno_id' => 'required|integer',
            'grupo_id' => 'required|integer',
            'escola_id' => 'nullable|integer',
        ]);

        $modalidadeId = $validated['modalidade_id'];
        $turnoId = $validated['turno_id'];
        $grupoId = $validated['grupo_id'];
        $escolaId = $validated['escola_id'] ?? auth()->user()->escola_id;
        $userId = auth()->id();

        \Illuminate\Support\Facades\Log::info("Buscando disciplinas por modalidade/turno/grupo para usuário ID: {$userId}", [
            'modalidade_id' => $modalidadeId,
            'turno_id' => $turnoId,
            'grupo_id' => $grupoId,
            'escola_id' => $escolaId
        ]);

        try {
            // Buscar todas as disciplinas ativas (agora globais), independente da modalidade
            $disciplinas = \App\Models\Disciplina::query()
                ->where('disciplinas.ativo', true)
                ->orderBy('disciplinas.nome')
                ->distinct()
                ->get(['disciplinas.id', 'disciplinas.nome']);

            \Illuminate\Support\Facades\Log::info("Disciplinas encontradas: " . $disciplinas->count(), [
                'disciplinas' => $disciplinas->pluck('nome')->toArray()
            ]);

            return response()->json($disciplinas);

        } catch (\Throwable $e) {
            \Log::error('Erro ao buscar disciplinas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Erro ao buscar disciplinas.',
                'message' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor.'
            ], 500);
        }
    }


    /**
     * Retorna as turmas associadas a uma disciplina específica.
     */
    public function getTurmasPorDisciplina(Request $request)
    {
        try {
            $disciplinaId = $request->input('disciplina_id');
            $modalidadeId = $request->input('modalidade_id');
            $turnoId = $request->input('turno_id');
            $grupoId = $request->input('grupo_id');
            $userId = auth()->id();

            if (!$disciplinaId || !$modalidadeId || !$turnoId || !$grupoId) {
                return response()->json(['error' => 'Parâmetros disciplina_id, modalidade_id, turno_id e grupo_id são obrigatórios.'], 400);
            }

            // Determinar a escola atual
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;

            \Illuminate\Support\Facades\Log::info("Buscando salas por disciplina para usuário ID: {$userId}", [
                'disciplina_id' => $disciplinaId,
                'modalidade_id' => $modalidadeId,
                'turno_id' => $turnoId,
                'grupo_id' => $grupoId,
                'escola_id' => $escolaId
            ]);

            // Buscar todas as salas ativas que atendem aos critérios
            $salas = \App\Models\Sala::where('ativo', true)
                ->where('modalidade_ensino_id', $modalidadeId)
                ->where('turno_id', $turnoId)
                ->where('grupo_id', $grupoId)
                ->where('escola_id', $escolaId)
                ->orderBy('nome')
                ->get(['id', 'nome', 'codigo']);

            \Illuminate\Support\Facades\Log::info("Salas encontradas: " . $salas->count(), [
                'salas' => $salas->pluck('nome')->toArray()
            ]);

            // Transformar para o formato esperado
            $result = $salas->map(function ($sala) {
                return [
                    'id' => $sala->id,
                    'nome' => $sala->nome . ' - ' . $sala->codigo
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar salas por disciplina: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar salas.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Retorna os tipos de professor ativos.
     */
    public function getTiposProfessor(Request $request)
    {
        try {
            // Simplificando para retornar todos os tipos de professor ativos
            // Não é mais necessário filtrar por modalidade
            $tiposProfessor = \App\Models\TipoProfessor::ativos()
                ->orderBy('nome')
                ->get(['id', 'nome', 'codigo']);

            // Transformar para o formato esperado pelo frontend
            $tiposFormatados = $tiposProfessor->map(function ($tipo) {
                return [
                    'value' => $tipo->codigo,
                    'label' => $tipo->nome
                ];
            });

            return response()->json($tiposFormatados);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar tipos de professor: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar tipos de professor.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $escolaId = $user->escola_id;
        $escolaNome = $user->escola->nome ?? 'N/A';

        // Para administradores, buscar todas as escolas
        $escolas = null;
        if ($user->isSuperAdmin()) {
            $escolas = \App\Models\Escola::where('ativo', true)->orderBy('nome')->get();
        }

        // Buscar modalidades configuradas para a escola
        $modalidades = \App\Models\ModalidadeEnsino::whereHas('configuracaoEscola', function ($query) use ($escolaId) {
            $query->where('escola_id', $escolaId)->where('ativo', true);
        })
            ->ativas()
            ->ordenados()
            ->get();

        return view('planejamentos.create', compact('escolaId', 'escolaNome', 'escolas', 'modalidades'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, CreatePlanejamentoAction $createPlanejamentoAction)
    {
        try {
            $planejamento = $createPlanejamentoAction->execute($request->all());

            return response()->json([
                'message' => 'Planejamento criado com sucesso!',
                'planejamento' => $planejamento,
                'id' => $planejamento->id
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Por favor, corrija os seguintes erros:'
            ], 422);
        } catch (\Exception $e) {
            $code = $e->getCode();
            if ($code === 403) {
                return response()->json(['errors' => ['permission' => [$e->getMessage()]]], 403);
            }

            AlertService::systemError('Erro ao criar planejamento', $e);
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Planejamento $planejamento)
    {
        $user = Auth::user();

        // Verificar se o usuário pode visualizar este planejamento
        $podeVisualizar = $planejamento->user_id === $user->id ||
            $user->isAdminOrCoordinator() ||
            $user->isSuperAdmin() ||
            $user->temCargo('Suporte') ||
            $this->coordenadorTemAcessoSala($user, $planejamento);

        if (!$podeVisualizar) {
            abort(403, 'Acesso negado.');
        }

        $planejamento->load(['user', 'turma']);

        return view('planejamentos.show', compact('planejamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Planejamento $planejamento)
    {
        $this->authorize('update', $planejamento);

        // Prevent editing approved plannings
        if ($planejamento->status === 'aprovado') {
            return redirect()->route('planejamentos.show', $planejamento)
                ->with('warning', 'Planejamentos aprovados não podem ser editados. Apenas visualização.');
        }
        
        // Prevent editing plannings under review (unless user has approve permission)
        // 'finalizado' is used for "Aguardando Aprovação"
        if (($planejamento->status === 'finalizado' || $planejamento->status === 'revisao') && !auth()->user()->can('planejamentos.aprovar')) {
            return redirect()->route('planejamentos.show', $planejamento)
                ->with('warning', 'Este planejamento está em revisão e não pode ser editado.');
        }

        $modalidades = Planejamento::getModalidadesOptions();
        $turnos = Planejamento::getTurnosOptions();
        $tiposProfessor = Planejamento::getTiposProfessorOptions();
        $turmas = \App\Models\Turma::orderBy('nome')->get();
        $salas = Sala::ativas()->orderBy('codigo')->get();
        $unidadeEscolar = 'Escola Municipal';

        return view('planejamentos.edit', compact(
            'planejamento',
            'modalidades',
            'turnos',
            'tiposProfessor',
            'turmas',
            'salas',
            'unidadeEscolar'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Planejamento $planejamento, UpdatePlanejamentoAction $updatePlanejamentoAction)
    {
        $this->authorize('update', $planejamento);

        try {
            $updatePlanejamentoAction->execute($planejamento, $request->all());

            return redirect()->route('planejamentos.show', $planejamento)
                ->with('success', 'Planejamento atualizado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            AlertService::systemError('Erro ao atualizar planejamento', $e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Planejamento $planejamento)
    {
        $user = Auth::user();

        // Verificar se o usuário pode excluir este planejamento
        $podeExcluir = $planejamento->user_id === $user->id ||
            $user->isAdminOrCoordinator() ||
            $user->isSuperAdmin() ||
            $user->temCargo('Suporte') ||
            $this->coordenadorTemAcessoSala($user, $planejamento);

        if (!$podeExcluir) {
            abort(403, 'Acesso negado.');
        }

        try {
            $planejamento->delete();

            AlertService::success('Planejamento excluído com sucesso!');
            return redirect()->route('planejamentos.index');

        } catch (\Exception $e) {
            AlertService::systemError('Erro ao excluir planejamento', $e);
            return redirect()->back();
        }
    }

    /**
     * Retorna o último período de planejamento realizado para uma turma, professor e disciplina.
     */
    public function getUltimoPeriodoPlanejamento(Request $request)
    {
        try {
            $turmaId = $request->input('turma_id');
            $disciplinaId = $request->input('disciplina_id');
            $tipoProfessor = $request->input('tipo_professor');
            $modalidadeId = $request->input('modalidade_id');
            $turnoId = $request->input('turno_id');
            $grupoEducacionalId = $request->input('grupo_educacional_id');
            $user = auth()->user();
            $escolaId = $user->escola_id;

            // Log para debug
            \Log::info('Parâmetros recebidos em getUltimoPeriodoPlanejamento', [
                'turma_id' => $turmaId,
                'disciplina_id' => $disciplinaId,
                'modalidade_id' => $modalidadeId,
                'turno_id' => $turnoId,
                'grupo_educacional_id' => $grupoEducacionalId
            ]);

            // Não exigir tipo_professor para buscar o último período
            if (!$turmaId) {
                return response()->json(['ultimo_periodo' => null]);
            }

            // Buscar o último planejamento para os mesmos parâmetros (incluindo user_id e tipo_professor)
            $ultimoPlanejamento = Planejamento::where('turma_id', $turmaId)
                ->where('user_id', $user->id)
                ->where('tipo_professor', $tipoProfessor)
                ->where('escola_id', $escolaId)
                ->whereIn('status', ['rascunho', 'aberto', 'finalizado', 'aprovado']) // Excluir apenas rejeitados
                ->orderBy('data_fim', 'desc')
                ->first();

            if ($ultimoPlanejamento) {
                // Retornar o dia seguinte ao último planejamento
                $dataFimOriginal = $ultimoPlanejamento->data_fim->format('Y-m-d');
                $proximaDataInicio = $ultimoPlanejamento->data_fim->copy()->addDay()->format('Y-m-d');

                \Log::info('Último planejamento encontrado', [
                    'planejamento_id' => $ultimoPlanejamento->id,
                    'data_fim_ultimo' => $dataFimOriginal,
                    'proxima_data_inicio' => $proximaDataInicio,
                    'status' => $ultimoPlanejamento->status,
                    'turma_id' => $turmaId
                ]);

                return response()->json(['ultimo_periodo' => $proximaDataInicio]);
            }

            // Se não há planejamentos anteriores, retornar null para usar data atual
            return response()->json(['ultimo_periodo' => null]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar último período de planejamento: ' . $e->getMessage());
            return response()->json(['ultimo_periodo' => null]);
        }
    }
    /**
     * Método AJAX para buscar níveis de ensino por modalidade e turno
     */


    /**
     * Método AJAX para buscar tipos de professor por modalidade
     */
    public function getTurnosDisponiveis(Request $request)
    {
        try {
            // Aceitar tanto 'modalidade' quanto 'modalidade_id' para compatibilidade
            $modalidadeId = $request->input('modalidade_id') ?: $request->input('modalidade');
            $escolaId = auth()->user()->escola_id;

            if (!$modalidadeId) {
                return response()->json(['error' => 'Parâmetro modalidade ou modalidade_id é obrigatório.'], 400);
            }

            // Verificar se a modalidade existe
            $modalidade = \App\Models\ModalidadeEnsino::find($modalidadeId);
            if (!$modalidade) {
                return response()->json(['error' => 'Modalidade não encontrada.'], 404);
            }

            // Buscar todos os turnos ativos, independente de terem salas associadas
            $turnos = \App\Models\Turno::where('ativo', true)
                ->orderBy('nome')
                ->get(['id', 'nome'])
                ->map(function ($turno) use ($modalidadeId, $escolaId) {
                    // Mapear nome do turno para o valor esperado pela validação
                    $turnoValue = strtolower($turno->nome);

                    return [
                        'value' => $turnoValue,
                        'label' => $turno->nome,
                        'id' => $turno->id,
                        'salas_ativas' => \App\Models\Sala::where('modalidade_ensino_id', $modalidadeId)
                            ->where('turno_id', $turno->id)
                            ->where('escola_id', $escolaId)
                            ->where('ativo', true)
                            ->count()
                    ];
                });

            return response()->json($turnos);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Modalidade de ensino não encontrada.'], 404);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao carregar turnos disponíveis: " . $e->getMessage());
            return response()->json(['error' => 'Erro interno do servidor.'], 500);
        }
    }

    public function getNiveisEnsino(Request $request)
    {
        try {
            $modalidadeId = $request->input('modalidade_id');
            $turnoId = $request->input('turno_id');

            // Obter escola do usuário logado
            $user = auth()->user();
            $escolaId = null;

            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $escolaId = session('escola_atual') ?: $user->escola_id;
            } else {
                $escolaId = $user->escola_id;
            }

            if (!$modalidadeId) {
                return response()->json(['error' => 'Parâmetro modalidade_id é obrigatório.'], 400);
            }

            if (!$escolaId) {
                return response()->json(['error' => 'Usuário deve estar associado a uma escola.'], 400);
            }

            // Buscar modalidade para obter o código
            $modalidade = \App\Models\ModalidadeEnsino::find($modalidadeId);
            if (!$modalidade) {
                return response()->json(['error' => 'Modalidade não encontrada.'], 400);
            }

            // Buscar níveis configurados para a escola que são compatíveis com a modalidade
            $niveisConfigurados = \DB::table('escola_niveis_config as enc')
                ->join('niveis_ensino as ne', 'enc.nivel_ensino_id', '=', 'ne.id')
                ->where('enc.escola_id', $escolaId)
                ->where('enc.ativo', true)
                ->where('ne.ativo', true)
                ->whereJsonContains('ne.modalidades_compativeis', $modalidade->codigo)
                ->select('ne.id', 'ne.nome', 'ne.codigo')
                ->orderBy('ne.nome')
                ->get();

            return response()->json($niveisConfigurados);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar níveis de ensino: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar níveis de ensino.', 'message' => $e->getMessage()], 500);
        }
    }

    public function getGruposEducacionais(Request $request)
    {
        try {
            $modalidadeId = $request->input('modalidade_id');
            $turnoId = $request->input('turno_id');
            $escolaId = auth()->user()->escola_id;

            if (!$modalidadeId || !$turnoId) {
                return response()->json(['error' => 'Parâmetros modalidade_id e turno_id são obrigatórios.'], 400);
            }

            $grupos = \App\Models\Grupo::whereHas('salas', function ($query) use ($modalidadeId, $turnoId, $escolaId) {
                $query->where('modalidade_ensino_id', $modalidadeId)
                    ->where('turno_id', $turnoId)
                    ->where('escola_id', $escolaId)
                    ->where('ativo', true);
            })
                ->ativos()
                ->orderBy('nome')
                ->get(['id', 'nome']);

            return response()->json($grupos);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar grupos educacionais: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar grupos educacionais.', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Busca o último planejamento do usuário logado
     */
    public function getUltimoPlanejamento(Request $request)
    {
        try {
            $ultimoPlanejamento = Planejamento::where('user_id', Auth::id())
                ->orderBy('data_fim', 'desc')
                ->first();

            if ($ultimoPlanejamento) {
                return response()->json([
                    'data_fim' => $ultimoPlanejamento->data_fim,
                    'data_inicio' => $ultimoPlanejamento->data_inicio
                ]);
            } else {
                return response()->json(['message' => 'Nenhum planejamento encontrado'], 404);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar último planejamento: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar último planejamento.'], 500);
        }
    }

    /**
     * Página para gerenciar conflitos de planejamentos
     */
    public function conflitos(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdminOrCoordinator()) {
            abort(403, 'Acesso negado.');
        }

        $planejamentos = collect();

        if ($request->filled('data')) {
            $data = \Carbon\Carbon::parse($request->data);

            $planejamentos = Planejamento::where(function ($query) use ($data) {
                $query->where('data_inicio', '<=', $data)
                    ->where('data_fim', '>=', $data);
            })
                ->with(['user', 'turma'])
                ->orderBy('data_inicio')
                ->get();
        }

        return view('planejamentos.conflitos', compact('planejamentos'));
    }

    /**
     * Excluir planejamento conflitante
     */
    public function excluirConflito(Planejamento $planejamento)
    {
        $user = Auth::user();

        if (!$user->isAdminOrCoordinator()) {
            abort(403, 'Acesso negado.');
        }

        try {
            $planejamento->delete();
            AlertService::success('Planejamento excluído com sucesso!');
        } catch (\Exception $e) {
            AlertService::systemError('Erro ao excluir planejamento', $e);
        }

        return redirect()->back();
    }

    /**
     * Verificar todos os conflitos
     */
    public function verificarTodosConflitos()
    {
        $user = Auth::user();

        if (!$user->isAdminOrCoordinator()) {
            abort(403, 'Acesso negado.');
        }

        $conflitos = [];

        // Buscar planejamentos agrupados por turma, user_id e tipo_professor
        $grupos = Planejamento::selectRaw('turma_id, user_id, tipo_professor, COUNT(*) as total')
            ->groupBy('turma_id', 'user_id', 'tipo_professor')
            ->having('total', '>', 1)
            ->get();

        foreach ($grupos as $grupo) {
            $planejamentos = Planejamento::where('turma_id', $grupo->turma_id)
                ->where('user_id', $grupo->user_id)
                ->where('tipo_professor', $grupo->tipo_professor)
                ->orderBy('data_inicio')
                ->get();

            for ($i = 0; $i < $planejamentos->count() - 1; $i++) {
                $atual = $planejamentos[$i];
                $proximo = $planejamentos[$i + 1];

                // Verificar sobreposição
                if ($atual->data_fim >= $proximo->data_inicio) {
                    $conflitos[] = [
                        'atual' => $atual->toArray(),
                        'proximo' => $proximo->toArray()
                    ];
                }
            }
        }

        return view('planejamentos.conflitos', compact('conflitos'));
    }

    /**
     * Exibe a tela de planejamento detalhado baseado na BNCC
     */
    public function showDetalhado(Planejamento $planejamento)
    {
        // Verificar se o usuário tem permissão para visualizar este planejamento
        if ($planejamento->user_id !== Auth::id() && !Auth::user()->hasRole('coordenador')) {
            abort(403, 'Acesso negado.');
        }

        // Buscar ou criar planejamento detalhado
        $planejamentoDetalhado = $planejamento->planejamentoDetalhado ?? new \App\Models\PlanejamentoDetalhado();

        // Buscar campos de experiência
        $camposExperiencia = \App\Models\CampoExperiencia::ativos()->get();

        // Buscar objetivos de aprendizagem
        $objetivosAprendizagem = \App\Models\ObjetivoAprendizagem::ativos()
            ->with('campoExperiencia')
            ->get()
            ->groupBy('campo_experiencia_id');

        return view('planejamentos.detalhado', compact(
            'planejamento',
            'planejamentoDetalhado',
            'camposExperiencia',
            'objetivosAprendizagem'
        ));
    }

    /**
     * Salva o planejamento detalhado
     */
    public function storeDetalhado(Request $request, Planejamento $planejamento)
    {
        try {
            $status = $request->status ?? 'rascunho';

            $planejamentoDetalhado = \App\Models\PlanejamentoDetalhado::create([
                'planejamento_id' => $planejamento->id,
                'campos_experiencia_selecionados' => $request->campos_experiencia_selecionados,
                'saberes_conhecimentos' => $request->saberes_conhecimentos,
                'objetivos_aprendizagem_selecionados' => $request->objetivos_aprendizagem_selecionados,
                'encaminhamentos_metodologicos' => $request->encaminhamentos_metodologicos,
                'recursos' => $request->recursos,
                'registros_anotacoes' => $request->registros_anotacoes,
                'status' => $status,
                'finalizado_em' => $status === 'finalizado' ? now() : null
            ]);

            // Atualizar também o status do planejamento principal
            $planejamento->update([
                'status' => $status
            ]);

            // Notificar coordenadores quando professor finalizar planejamento
            if ($status === 'finalizado') {
                $this->notificarCoordenadoresFinalizacao($planejamento);
            }

            return response()->json([
                'success' => true,
                'message' => 'Planejamento salvo com sucesso!',
                'data' => $planejamentoDetalhado
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao salvar planejamento detalhado: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao salvar planejamento.'], 500);
        }
    }

    /**
     * Atualiza o planejamento detalhado
     */
    public function updateDetalhado(Request $request, Planejamento $planejamento)
    {
        try {
            DB::beginTransaction();
            $planejamentoDetalhado = $planejamento->planejamentoDetalhado;

            if (!$planejamentoDetalhado) {
                return $this->storeDetalhado($request, $planejamento);
            }

            $novoStatus = $request->status ?? $planejamentoDetalhado->status;

            $planejamentoDetalhado->update([
                'campos_experiencia_selecionados' => $request->campos_experiencia_selecionados,
                'saberes_conhecimentos' => $request->saberes_conhecimentos,
                'objetivos_aprendizagem_selecionados' => $request->objetivos_aprendizagem_selecionados,
                'encaminhamentos_metodologicos' => $request->encaminhamentos_metodologicos,
                'recursos' => $request->recursos,
                'registros_anotacoes' => $request->registros_anotacoes,
                'status' => $novoStatus,
                'finalizado_em' => $novoStatus === 'finalizado' ? now() : null
            ]);

            // Atualizar também o status do planejamento principal
            $planejamento->update([
                'status' => $novoStatus
            ]);

            // Se finalizar via detalhado, notificar coordenadores
            if ($novoStatus === 'finalizado') {
                $this->notificarCoordenadoresFinalizacao($planejamento);
            }

            return response()->json([
                'success' => true,
                'message' => 'Planejamento atualizado com sucesso!',
                'data' => $planejamentoDetalhado
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao atualizar planejamento detalhado: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao atualizar planejamento.'], 500);
        }
    }

    /**
     * Aprova um planejamento detalhado
     */
    public function aprovar(Request $request, Planejamento $planejamento)
    {
        // Verificar se o usuário é coordenador ou admin
        $user = Auth::user();
        if (!$user->isAdminOrCoordinator()) {
            return response()->json(['error' => 'Acesso negado. Apenas coordenadores podem aprovar planejamentos.'], 403);
        }

        // Se for coordenador (não admin), verificar se está vinculado à sala do planejamento
        if ($user->temCargo('Coordenador') && !$user->temCargo('Administrador')) {
            // Buscar a sala relacionada ao planejamento através da turma
            $turma = $planejamento->turma;
            if ($turma && $turma->sala && $turma->sala->coordenador_id !== $user->id) {
                return response()->json(['error' => 'Acesso negado. Você só pode aprovar planejamentos de salas sob sua coordenação.'], 403);
            }
        }

        try {
            $planejamentoDetalhado = $planejamento->planejamentoDetalhado;

            // Se não houver detalhado, permitir aprovação quando o principal estiver em revisão ou finalizado
            if (!$planejamentoDetalhado) {
                if (!in_array($planejamento->status, ['finalizado', 'revisao'])) {
                    return response()->json(['error' => 'Apenas planejamentos em revisão ou finalizados podem ser aprovados.'], 422);
                }
                $planejamentoDetalhado = \App\Models\PlanejamentoDetalhado::create([
                    'planejamento_id' => $planejamento->id,
                    'status' => 'aprovado',
                    'finalizado_em' => now(),
                    'aprovado_em' => now(),
                    'aprovado_por' => Auth::id(),
                    'observacoes_aprovacao' => $request->observacoes_aprovacao
                ]);
            } else {
                if (!in_array($planejamentoDetalhado->status, ['finalizado', 'revisao'])) {
                    return response()->json(['error' => 'Apenas planejamentos em revisão ou finalizados podem ser aprovados.'], 422);
                }
                $planejamentoDetalhado->update([
                    'status' => 'aprovado',
                    'aprovado_em' => now(),
                    'aprovado_por' => Auth::id(),
                    'observacoes_aprovacao' => $request->observacoes_aprovacao
                ]);
            }

            // Também atualizar o status do planejamento principal
            $planejamento->update([
                'status' => 'aprovado'
            ]);

            // Notificar o professor responsável
            try {
                $turma = $planejamento->turma;
                $sala = $turma ? $turma->sala : null;
                $destinatarioId = $planejamento->professor_id ?: $planejamento->user_id;

                if ($destinatarioId) {
                    \App\Models\Notification::createForUser(
                        $destinatarioId,
                        'success',
                        'Planejamento Aprovado',
                        'Seu planejamento foi aprovado pelo coordenador.' .
                        ($turma ? ' Turma: ' . $turma->nome : '') .
                        ($sala ? ' | Sala: ' . $sala->codigo : ''),
                        [
                            'planejamento_id' => $planejamento->id,
                            'turma_id' => $turma ? $turma->id : null
                        ],
                        route('planejamentos.show', $planejamento),
                        'Visualizar Planejamento'
                    );
                }
            } catch (\Exception $notifyEx) {
                \Illuminate\Support\Facades\Log::warning('Falha ao notificar professor sobre aprovação: ' . $notifyEx->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Planejamento aprovado com sucesso!',
                'data' => $planejamentoDetalhado->fresh()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao aprovar planejamento: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao aprovar planejamento.'], 500);
        }
    }

    /**
     * Rejeita um planejamento detalhado
     */
    public function rejeitar(Request $request, Planejamento $planejamento)
    {
        // Verificar se o usuário é coordenador ou admin
        $user = Auth::user();
        if (!$user->isAdminOrCoordinator()) {
            return response()->json(['error' => 'Acesso negado. Apenas coordenadores podem rejeitar planejamentos.'], 403);
        }

        // Se for coordenador (não admin), verificar se está vinculado à(s) sala(s) do planejamento
        if ($user->temCargo('Coordenador') && !$user->temCargo('Administrador')) {
            if (!$this->coordenadorTemAcessoSala($user, $planejamento)) {
                return response()->json(['error' => 'Acesso negado. Você só pode rejeitar planejamentos de salas sob sua coordenação.'], 403);
            }
        }

        $request->validate([
            'observacoes_aprovacao' => 'required|string|max:1000'
        ]);

        try {
            DB::beginTransaction();
            $planejamentoDetalhado = $planejamento->planejamentoDetalhado;

            // Se não houver detalhado, permitir rejeição quando o principal estiver em revisão ou finalizado
            if (!$planejamentoDetalhado) {
                if (!in_array($planejamento->status, ['finalizado', 'revisao'])) {
                    DB::rollBack();
                    return response()->json(['error' => 'Apenas planejamentos em revisão ou finalizados podem ser rejeitados.'], 422);
                }
                $planejamentoDetalhado = \App\Models\PlanejamentoDetalhado::create([
                    'planejamento_id' => $planejamento->id,
                    'status' => 'reprovado',
                    'aprovado_em' => null,
                    'aprovado_por' => null,
                    'observacoes_aprovacao' => $request->observacoes_aprovacao
                ]);
            } else {
                if (!in_array($planejamentoDetalhado->status, ['finalizado', 'revisao'])) {
                    DB::rollBack();
                    return response()->json(['error' => 'Apenas planejamentos em revisão ou finalizados podem ser rejeitados.'], 422);
                }
                $planejamentoDetalhado->update([
                    'status' => 'reprovado',
                    'aprovado_em' => null,
                    'aprovado_por' => null,
                    'observacoes_aprovacao' => $request->observacoes_aprovacao
                ]);
            }

            // Também atualizar o status do planejamento principal e registrar rejeição
            $planejamento->status = 'rejeitado';
            // Incrementar contador de rejeições
            $planejamento->rejeicoes_count = ($planejamento->rejeicoes_count ?? 0) + 1;
            // Salvar último motivo de rejeição em campo dedicado
            $planejamento->observacoes_rejeicao = $request->observacoes_aprovacao;
            // Registrar motivo da rejeição nas observações do planejamento (histórico simples)
            $observacaoAtual = $planejamento->observacoes ?? '';
            $prefixo = '[' . now()->format('d/m/Y H:i') . '] Rejeitado: ' . $request->observacoes_aprovacao;
            $planejamento->observacoes = trim($observacaoAtual . (strlen($observacaoAtual) ? "\n" : '') . $prefixo);
            $planejamento->save();
            DB::commit();

            // Notificar o professor responsável com botão para corrigir no wizard
            try {
                $turma = $planejamento->turma;
                $sala = $turma?->sala;
                $destinatarioId = $planejamento->professor_id ?: $planejamento->user_id;
                if ($destinatarioId) {
                    \App\Models\Notification::createForUser(
                        $destinatarioId,
                        'warning',
                        'Planejamento Rejeitado',
                        'Seu planejamento foi rejeitado pelo coordenador.' .
                            ($turma ? ' Turma: ' . $turma->nome : '') .
                            ($sala ? ' | Sala: ' . $sala->codigo : '') .
                            '. Motivo: ' . $request->observacoes_aprovacao,
                        [
                            'planejamento_id' => $planejamento->id,
                            'turma_id' => $turma?->id,
                            'sala_id' => $sala?->id
                        ],
                        route('planejamentos.wizard', ['edit' => $planejamento->id]),
                        'Corrigir Planejamento'
                    );
                } else {
                    \Illuminate\Support\Facades\Log::warning('Rejeição de planejamento sem usuário destinatário (user_id/professor_id ausentes).');
                }
            } catch (\Exception $notifyEx) {
                \Illuminate\Support\Facades\Log::warning('Falha ao notificar professor sobre rejeição: ' . $notifyEx->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Planejamento rejeitado. O professor poderá fazer as correções necessárias.',
                'data' => $planejamentoDetalhado->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Erro ao rejeitar planejamento: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao rejeitar planejamento.'], 500);
        }
    }


    /**
     * Verifica se um coordenador tem acesso à sala relacionada ao planejamento
     */
    private function coordenadorTemAcessoSala($user, $planejamento): bool
    {
        // Verificar se o usuário é coordenador
        if (!$user->isCoordenador()) {
            return false;
        }

        // Buscar salas relacionadas ao planejamento via grade de aulas (turma ↔ sala)
        $turma = $planejamento->turma;
        if (!$turma) {
            return false;
        }

        $salaIds = \App\Models\GradeAula::where('turma_id', $turma->id)
            ->pluck('sala_id')
            ->unique()
            ->filter()
            ->values();

        if ($salaIds->isEmpty()) {
            return false;
        }

        // Coordenador tem acesso se estiver vinculado a alguma destas salas via user_salas
        return $user->salas()->whereIn('salas.id', $salaIds)->exists();
    }

    /**
     * Exibe o wizard unificado de criação/edição
     */
    public function wizard(Request $request)
    {
        $planejamento = null;
        if ($request->has('edit') && $request->edit) {
            $planejamento = Planejamento::findOrFail($request->edit);
            $this->authorize('update', $planejamento);
        }

        return view('planejamentos.wizard.index', compact('planejamento'));
    }

    /**
     * Processa uma etapa específica do wizard
     */
    public function wizardStep(Request $request, $step)
    {
        // Log wizard access for debugging
        Log::info('Wizard step requested', [
            'step' => $step,
            'user_id' => auth()->id(),
            'escola_atual' => session('escola_atual'),
            'edit_param' => $request->get('edit'),
            'is_super_admin' => auth()->user()->isSuperAdmin(),
            'user_escola_id' => auth()->user()->escola_id,
            'request_url' => $request->fullUrl()
        ]);

        $validSteps = ['1', '2', '3', '4', '5', '6'];

        if (!in_array($step, $validSteps)) {
            Log::warning('Invalid wizard step requested', ['step' => $step, 'user_id' => auth()->id()]);
            abort(404, 'Etapa inválida');
        }

        $data = [];

        // Carregar planejamento se estiver editando
        $planejamento = null;
        if ($request->has('edit') && $request->edit) {
            try {
                $planejamento = Planejamento::findOrFail($request->edit);
                $this->authorize('update', $planejamento);
                
                Log::info('Wizard step loaded in edit mode', [
                    'step' => $step,
                    'planejamento_id' => $planejamento->id,
                    'user_id' => auth()->id(),
                    'escola_id' => session('escola_atual')
                ]);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::error('Planejamento not found in wizard', [
                    'planejamento_id' => $request->edit,
                    'user_id' => auth()->id(),
                    'step' => $step
                ]);
                // Instead of aborting, redirect to start fresh wizard
                return redirect()->route('planejamentos.wizard')
                    ->with('warning', 'Planejamento não encontrado. Iniciando novo planejamento.');
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                Log::error('Authorization failed in wizard', [
                    'planejamento_id' => $request->edit,
                    'user_id' => auth()->id(),
                    'step' => $step
                ]);
                abort(403, 'Você não tem permissão para editar este planejamento.');
            }
        }

        // Sempre passar a variável $planejamento para a view (mesmo quando null)
        $data['planejamento'] = $planejamento;

        // Preparar dados específicos para cada etapa
        switch ($step) {
            case '1':
                // Obter escola do usuário logado
                $user = auth()->user();
                $escolaId = null;

                if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                    $escolaId = session('escola_atual') ?: $user->escola_id;
                } else {
                    $escolaId = $user->escola_id;
                }

                // Buscar apenas modalidades que estão configuradas para a escola
                $modalidadesConfiguradas = \DB::table('escola_modalidades_config')
                    ->where('escola_id', $escolaId)
                    ->pluck('modalidade_ensino_id')
                    ->toArray();

                // Separar modalidades BNCC e personalizadas que estão configuradas para a escola
                $data['modalidades_bncc'] = \App\Models\ModalidadeEnsino::whereNull('escola_id')
                    ->whereIn('id', $modalidadesConfiguradas)
                    ->where('ativo', true)
                    ->orderBy('nome')
                    ->get();

                $data['modalidades_personalizadas'] = \App\Models\ModalidadeEnsino::where('escola_id', $escolaId)
                    ->whereIn('id', $modalidadesConfiguradas)
                    ->where('ativo', true)
                    ->orderBy('nome')
                    ->get();

                // Separar níveis BNCC e personalizados
                // Níveis BNCC têm códigos padronizados (EI_, EF_, EM_, EJA_)
                $data['niveis_bncc'] = \App\Models\NivelEnsino::where(function ($query) {
                    $query->where('codigo', 'like', 'EI_%')
                        ->orWhere('codigo', 'like', 'EF_%')
                        ->orWhere('codigo', 'like', 'EM_%')
                        ->orWhere('codigo', 'like', 'EJA_%');
                })
                    ->where('ativo', true)
                    ->orderBy('nome')
                    ->get();

                $data['niveis_personalizados'] = \App\Models\NivelEnsino::where(function ($query) {
                    $query->where('codigo', 'not like', 'EI_%')
                        ->where('codigo', 'not like', 'EF_%')
                        ->where('codigo', 'not like', 'EM_%')
                        ->where('codigo', 'not like', 'EJA_%');
                })
                    ->where('ativo', true)
                    ->orderBy('nome')
                    ->get();
                break;

            case '2':
                // Obter apenas a escola do usuário logado
                $user = auth()->user();
                $escolaId = null;

                if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                    $escolaId = session('escola_atual') ?: $user->escola_id;
                } else {
                    $escolaId = $user->escola_id;
                }

                if ($escolaId) {
                    $data['escola'] = \App\Models\Escola::find($escolaId);
                    $data['turnos'] = \App\Models\Turno::where('escola_id', $escolaId)->where('ativo', true)->get();
                } else {
                    $data['escola'] = null;
                    $data['turnos'] = collect();
                }
                break;

            case '3':
                $data['turmas'] = \App\Models\Turma::with('grupo')->withCount('alunos')->get();
                $data['disciplinas'] = \App\Models\Disciplina::all();
                $data['grupos'] = \App\Models\Grupo::all();

                // Carregar professores (usuários com cargo de professor)
                $data['professores'] = \App\Models\User::whereHas('cargos', function ($query) {
                    $query->where('tipo_cargo', 'professor')
                        ->orWhere('nome', 'like', '%professor%');
                })->where('ativo', true)->orderBy('name')->get();
                break;

            case '4':
                // Dados para configuração de período
                break;

            case '5':
                // Dados para conteúdo pedagógico
                try {
                    \Log::info('Wizard Step 5 iniciado', [
                        'planejamento_id' => $planejamento ? $planejamento->id : null,
                    ]);

                    // Renomear para snake_case para consistência com atributos
                    // Filtrar campos de experiência pela modalidade do nível de ensino selecionado
                    $query = \App\Models\CampoExperiencia::ativos();

                    if ($planejamento && $planejamento->nivelEnsino) {
                        $modalidades = $planejamento->nivelEnsino->modalidades_compativeis ?? [];
                        $query->porModalidade($modalidades);
                    } elseif ($request->has('nivel_ensino_id')) {
                        $nivel = \App\Models\NivelEnsino::find($request->nivel_ensino_id);
                        if ($nivel) {
                            $query->porModalidade($nivel->modalidades_compativeis ?? []);
                        }
                    }

                    $data['campos_experiencia'] = $query->get();

                    // Se estiver em modo edição, carregar diários existentes do planejamento
                    if ($planejamento) {
                        $diarios = \App\Models\PlanejamentoDiario::where('planejamento_id', $planejamento->id)
                            ->orderBy('data', 'asc')
                            ->get()
                            ->map(function ($d) {
                                return [
                                    'data' => $d->data instanceof \Carbon\Carbon ? $d->data->format('Y-m-d') : (string) $d->data,
                                    'campos_experiencia' => is_array($d->campos_experiencia) ? $d->campos_experiencia : (array) ($d->campos_experiencia ?? []),
                                    // Preservar texto legado; quando array, enviar como array
                                    'saberes_conhecimentos' => is_array($d->saberes_conhecimentos) ? $d->saberes_conhecimentos : ($d->saberes_conhecimentos ?? ''),
                                    'objetivos_especificos' => is_array($d->objetivos_especificos) ? implode("\n", $d->objetivos_especificos) : ($d->objetivos_especificos ?? ''),
                                    'objetivos_aprendizagem' => is_array($d->objetivos_aprendizagem) ? $d->objetivos_aprendizagem : (array) ($d->objetivos_aprendizagem ?? []),
                                    'metodologia' => $d->metodologia ?? '',
                                    'recursos_predefinidos' => is_array($d->recursos_predefinidos) ? $d->recursos_predefinidos : (array) ($d->recursos_predefinidos ?? []),
                                    'recursos_personalizados' => $d->recursos_personalizados ?? ''
                                ];
                            });
                        $data['diarios'] = $diarios;

                        \Log::info('Wizard Step 5 diários carregados', [
                            'diarios_count' => $diarios->count(),
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Log::error('Erro ao preparar dados da Etapa 5', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Continuar com defaults para não quebrar a view
                    $data['campos_experiencia'] = $data['campos_experiencia'] ?? collect();
                    $data['diarios'] = $data['diarios'] ?? collect();
                }
                break;

            case '6':
                // Dados para finalização
                break;
        }

        return view("planejamentos.wizard.steps.step-{$step}", $data);
    }

    /**
     * Valida uma etapa do wizard
     */
    public function validateWizardStep(Request $request)
    {
        $step = $request->input('step');
        $data = $request->input('data', []);

        // Validação específica por etapa
        $rules = $this->getValidationRulesForStep($step);

        $validator = Validator::make($data, $rules);

        // Validação customizada para etapa 2 - verificar se escola pertence ao usuário
        if ($step == '2' && isset($data['escola_id'])) {
            $user = auth()->user();
            $escolaPermitida = null;

            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $escolaPermitida = session('escola_atual') ?: $user->escola_id;
            } else {
                $escolaPermitida = $user->escola_id;
            }

            if ($data['escola_id'] != $escolaPermitida) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('escola_id', 'Você só pode criar planejamentos para a escola à qual está vinculado.');
                });
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Etapa validada com sucesso'
        ]);
    }

    /**
     * Salva o planejamento através do wizard
     */
    public function wizardStore(Request $request)
    {
        Log::info('=== WIZARD STORE EXECUTADO ===', ['timestamp' => now()]);

        try {
            $rawData = $request->all();
            $isDraft = $request->boolean('save_as_draft', false);

            // Log dos dados recebidos para debug
            Log::info('Dados recebidos no wizardStore', [
                'raw_data' => $rawData,
                'is_draft' => $isDraft,
                'request_method' => $request->method(),
                'request_url' => $request->url(),
                'user_id' => auth()->id()
            ]);

            // Processar dados estruturados por etapas
            $data = [];
            foreach ($rawData as $key => $value) {
                if (is_numeric($key)) {
                    // É uma etapa do wizard, mesclar os dados
                    if (is_array($value)) {
                        $data = array_merge($data, $value);
                    }
                } else {
                    // É um campo direto (save_as_draft, planejamento_id, etc.)
                    $data[$key] = $value;
                }
            }

            Log::info('Dados processados no wizardStore', [
                'processed_data' => $data,
                'is_draft' => $isDraft
            ]);

            // Mapear acao_finalizacao para status e flag de rascunho
            $acaoFinalizacao = $data['acao_finalizacao'] ?? null;
            if ($acaoFinalizacao === 'rascunho') {
                $isDraft = true;
                $data['status'] = 'rascunho';
            } elseif ($acaoFinalizacao === 'revisao') {
                $isDraft = false;
                $data['status'] = 'revisao';
            } else {
                // Definir status padrão baseado no save_as_draft quando não fornecido
                if (!isset($data['status']) || !$data['status']) {
                    $data['status'] = $isDraft ? 'rascunho' : 'finalizado';
                }
            }

            // Para rascunhos, usar validação mais flexível
            if ($isDraft) {
                $validator = $this->validateWizardDataForDraft($data);
            } else {
                $validator = $this->validateWizardData($data);
            }

            if ($validator->fails()) {
                Log::error('Validação falhou no wizardStore', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Criar ou atualizar planejamento
            try {
                DB::beginTransaction();
                $planejamento = $this->createOrUpdateFromWizard($data);
                // Persistir diários do planejamento conforme novo payload
                $diarios = $data['planejamentos_diarios'] ?? [];
                $persistidos = 0;
                $removidos = 0;

                if (is_array($diarios)) {
                    $datasRecebidas = [];
                    $inicio = $planejamento->data_inicio ? Carbon::parse($planejamento->data_inicio)->startOfDay() : null;
                    $fim = $planejamento->data_fim ? Carbon::parse($planejamento->data_fim)->endOfDay() : null;

                    foreach ($diarios as $entry) {
                        if (!is_array($entry) || !isset($entry['data'])) {
                            continue;
                        }

                        try {
                            $dataDia = Carbon::parse($entry['data']);
                        } catch (\Exception $e) {
                            Log::warning('Data inválida em planejamentos_diarios', ['entry' => $entry]);
                            continue;
                        }

                        // Filtrar por período do planejamento, se disponível
                        if ($inicio && $fim && ($dataDia->lt($inicio) || $dataDia->gt($fim))) {
                            Log::info('Ignorando diário fora do período', [
                                'data' => $dataDia->toDateString(),
                                'inicio' => $inicio->toDateString(),
                                'fim' => $fim->toDateString()
                            ]);
                            continue;
                        }

                        $datasRecebidas[] = $dataDia->toDateString();

                        $payload = [
                            'dia_semana' => isset($entry['dia_semana']) ? (int) $entry['dia_semana'] : (int) $dataDia->dayOfWeek,
                            'planejado' => array_key_exists('planejado', $entry) ? (bool) $entry['planejado'] : true,
                            'campos_experiencia' => $entry['campos_experiencia'] ?? [],
                            'saberes_conhecimentos' => $entry['saberes_conhecimentos'] ?? null,
                            'objetivos_especificos' => $entry['objetivos_especificos'] ?? null,
                            'metodologia' => $entry['metodologia'] ?? null,
                            'recursos_predefinidos' => $entry['recursos_predefinidos'] ?? [],
                            'recursos_personalizados' => $entry['recursos_personalizados'] ?? null,
                        ];

                        PlanejamentoDiario::updateOrCreate(
                            [
                                'planejamento_id' => $planejamento->id,
                                'data' => $dataDia->toDateString(),
                            ],
                            $payload
                        );
                        $persistidos++;
                    }

                    // Se não for rascunho, remover diários que ficaram fora do payload (limpeza)
                    if (!$isDraft && $inicio && $fim && count($datasRecebidas) > 0) {
                        $removidos = PlanejamentoDiario::where('planejamento_id', $planejamento->id)
                            ->whereBetween('data', [$inicio->toDateString(), $fim->toDateString()])
                            ->whereNotIn('data', $datasRecebidas)
                            ->delete();
                    }
                }

                // Se não for rascunho e o planejamento estiver em "revisao",
                // garantir o registro detalhado com status "revisao" (não finalizado).
                if (!$isDraft && ($planejamento->status === 'revisao')) {
                    $detalhado = $planejamento->planejamentoDetalhado;
                    if ($detalhado) {
                        $detalhado->update([
                            'status' => 'revisao',
                            'finalizado_em' => null
                        ]);
                    } else {
                        \App\Models\PlanejamentoDetalhado::create([
                            'planejamento_id' => $planejamento->id,
                            'status' => 'revisao',
                            'finalizado_em' => null
                        ]);
                    }
                }

                DB::commit();
                Log::info('Transação concluída com sucesso', [
                    'planejamento_id' => $planejamento->id,
                    'diarios_persistidos' => $persistidos,
                    'diarios_removidos' => $removidos,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro na transação do planejamento: ' . $e->getMessage(), ['exception' => $e]);
                throw $e;
            }

            $message = $isDraft ? 'Rascunho salvo com sucesso!' : 'Planejamento salvo com sucesso!';

            if (!$isDraft && ($planejamento->status === 'revisao')) {
                $this->notificarCoordenadoresFinalizacao($planejamento);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'planejamento_id' => $planejamento->id,
                'redirect_url' => $isDraft ? null : route('planejamentos.view', $planejamento),
                'redirect' => $isDraft ? null : route('planejamentos.view', $planejamento)
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao salvar planejamento via wizard: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Inicializa um rascunho de Planejamento ao entrar na Etapa 5
     * Recebe dados agregados das etapas 1-4 e cria/atualiza o Planejamento com status "rascunho".
     */
    public function wizardInitDraft(Request $request)
    {
        Log::info('=== WIZARD INIT DRAFT EXECUTADO ===', ['timestamp' => now()]);

        try {
            $rawData = $request->all();

            // Mesclar dados das etapas numéricas (1..4) com campos diretos
            $data = [];
            foreach ($rawData as $key => $value) {
                if (is_numeric($key)) {
                    if (is_array($value)) {
                        $data = array_merge($data, $value);
                    }
                } else {
                    $data[$key] = $value;
                }
            }

            // Forçar status rascunho
            $data['status'] = $data['status'] ?? 'rascunho';

            Log::info('Dados processados no wizardInitDraft', [
                'processed_data' => $data,
            ]);

            // Validação flexível para rascunho
            $validator = $this->validateWizardDataForDraft($data);
            if ($validator->fails()) {
                Log::error('Validação falhou no wizardInitDraft', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();
            try {
                $planejamento = $this->createOrUpdateFromWizard($data);
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Rascunho inicializado com sucesso!',
                    'planejamento_id' => $planejamento->id
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Erro ao inicializar rascunho via wizard: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Upsert de PlanejamentoDiario para salvar/atualizar um dia (rascunho por padrão)
     */
    public function wizardUpsertDiario(Request $request)
    {
        Log::info('=== WIZARD UPSERT DIARIO EXECUTADO ===', ['timestamp' => now()]);

        try {
            $data = $request->all();

            // Validação mínima
            $validator = Validator::make($data, [
                'planejamento_id' => 'required|exists:planejamentos,id',
                'data' => 'required|date_format:Y-m-d',
            ], [
                'planejamento_id.required' => 'O planejamento é obrigatório.',
                'planejamento_id.exists' => 'Planejamento não encontrado.',
                'data.required' => 'A data do diário é obrigatória.',
                'data.date_format' => 'A data informada é inválida. Use o formato YYYY-MM-DD.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $planejamento = Planejamento::findOrFail($data['planejamento_id']);
            try {
                $this->authorize('update', $planejamento);
            } catch (\Illuminate\Auth\Access\AuthorizationException $authEx) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para alterar este planejamento.'
                ], 403);
            }

            // Garantir que a data esteja dentro do período quando existir
            // Usar formato explícito para evitar deslocamentos de timezone
            $dataDia = Carbon::createFromFormat('Y-m-d', $data['data'])->startOfDay();
            if ($planejamento->data_inicio && $planejamento->data_fim) {
                $inicio = Carbon::parse($planejamento->data_inicio)->startOfDay();
                $fim = Carbon::parse($planejamento->data_fim)->endOfDay();
                if ($dataDia->lt($inicio) || $dataDia->gt($fim)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A data do diário está fora do período do planejamento.'
                    ], 400);
                }
            }

            // Atualiza e7 e3o parcial: s f3 definir campos presentes na requisi e7 e3o para evitar sobrescrever dados existentes
            $payload = [
                'dia_semana' => isset($data['dia_semana']) ? (int) $data['dia_semana'] : (int) $dataDia->dayOfWeek,
            ];

            if (array_key_exists('planejado', $data)) {
                $payload['planejado'] = (bool) $data['planejado'];
            }
            if (array_key_exists('campos_experiencia', $data)) {
                $payload['campos_experiencia'] = $data['campos_experiencia'] ?? [];
            }
            if (array_key_exists('saberes_conhecimentos', $data)) {
                $payload['saberes_conhecimentos'] = $data['saberes_conhecimentos'] ?? null;
            }
            if (array_key_exists('objetivos_especificos', $data)) {
                // Campo text; aceitar string e ignorar array
                $payload['objetivos_especificos'] = $data['objetivos_especificos'] ?? null;
            }
            if (array_key_exists('metodologia', $data)) {
                $payload['metodologia'] = $data['metodologia'] ?? null;
            }
            if (array_key_exists('objetivos_aprendizagem', $data)) {
                $payload['objetivos_aprendizagem'] = $data['objetivos_aprendizagem'] ?? [];
            }
            if (array_key_exists('recursos_predefinidos', $data)) {
                $payload['recursos_predefinidos'] = $data['recursos_predefinidos'] ?? [];
            }
            if (array_key_exists('recursos_personalizados', $data)) {
                $payload['recursos_personalizados'] = $data['recursos_personalizados'] ?? null;
            }

            $diario = PlanejamentoDiario::updateOrCreate(
                [
                    'planejamento_id' => $planejamento->id,
                    'data' => $dataDia->toDateString(),
                ],
                $payload
            );

            return response()->json([
                'success' => true,
                'message' => 'Diário salvo com sucesso!',
                'diario' => $diario
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar diário via wizard: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Nova visualização de planejamento
     */
    public function viewPlanejamento(Planejamento $planejamento)
    {
        $this->authorize('view', $planejamento);

        return view('planejamentos.view.show', compact('planejamento'));
    }

    /**
     * Preview do planejamento
     */
    public function previewPlanejamento(Planejamento $planejamento)
    {
        $this->authorize('view', $planejamento);

        return view('planejamentos.view.preview', compact('planejamento'));
    }

    /**
     * Cronograma Diário agregado por data para acesso rápido de professores
     */
    public function cronogramaDia(Request $request)
    {
        $user = Auth::user();
        $dataStr = $request->get('data') ?: now()->format('Y-m-d');
        try {
            $data = Carbon::parse($dataStr);
        } catch (\Exception $e) {
            $data = now();
        }

        // Carregar planejamentos visíveis ao usuário
        if ($user->isAdminOrCoordinator() || $user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $query = Planejamento::query();
        } else {
            $query = Planejamento::where('user_id', $user->id);
        }

        // Eager load de relações e filtrar diários pela data
        $planejamentos = $query
            ->with(['turma', 'disciplina', 'diarios' => function($q) use ($data) {
                $q->whereDate('data', $data->toDateString());
            }])
            ->orderBy('data_inicio', 'desc')
            ->get();

        $prevDate = $data->copy()->subDay()->format('Y-m-d');
        $nextDate = $data->copy()->addDay()->format('Y-m-d');

        // Mapas de nomes para exibir em vez de IDs
        $camposMap = CampoExperiencia::pluck('nome', 'id')->toArray();
        $saberesMap = SaberConhecimento::pluck('titulo', 'id')->toArray();
        $objetivosMap = ObjetivoAprendizagem::selectRaw("id, CONCAT(COALESCE(codigo, ''), CASE WHEN COALESCE(codigo, '') = '' THEN '' ELSE ' - ' END, descricao) AS label")
            ->pluck('label', 'id')
            ->toArray();

        return view('planejamentos.cronograma-dia', [
            'planejamentos' => $planejamentos,
            'data' => $data,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'maps' => [
                'campos' => $camposMap,
                'saberes' => $saberesMap,
                'objetivos' => $objetivosMap,
            ],
        ]);
    }

    /**
     * Detalhe do cronograma diário para um planejamento específico, com navegação entre dias
     */
    public function cronogramaDetalhe(Planejamento $planejamento, Request $request)
    {
        $this->authorize('view', $planejamento);

        $dataStr = $request->get('data') ?: now()->format('Y-m-d');
        try {
            $data = Carbon::parse($dataStr);
        } catch (\Exception $e) {
            $data = now();
        }

        // Garantir limites do período do planejamento
        $inicio = $planejamento->data_inicio ?: $data->copy();
        $fim = $planejamento->data_fim ?: $data->copy();
        if ($data->lt($inicio)) {
            $data = $inicio->copy();
        }
        if ($data->gt($fim)) {
            $data = $fim->copy();
        }

        $diarioDoDia = $planejamento->diarios()->whereDate('data', $data->toDateString())->first();

        $prevDate = $data->copy()->subDay();
        $nextDate = $data->copy()->addDay();
        if ($prevDate->lt($inicio)) {
            $prevDate = null;
        }
        if ($nextDate->gt($fim)) {
            $nextDate = null;
        }

        // Mapas de nomes para exibir em vez de IDs
        $camposMap = CampoExperiencia::pluck('nome', 'id')->toArray();
        $saberesMap = SaberConhecimento::pluck('titulo', 'id')->toArray();
        $objetivosMap = ObjetivoAprendizagem::selectRaw("id, CONCAT(COALESCE(codigo, ''), CASE WHEN COALESCE(codigo, '') = '' THEN '' ELSE ' - ' END, descricao) AS label")
            ->pluck('label', 'id')
            ->toArray();

        $viewData = [
            'planejamento' => $planejamento,
            'data' => $data,
            'diario' => $diarioDoDia,
            'prevDate' => $prevDate?->format('Y-m-d'),
            'nextDate' => $nextDate?->format('Y-m-d'),
            'maps' => [
                'campos' => $camposMap,
                'saberes' => $saberesMap,
                'objetivos' => $objetivosMap,
            ],
        ];

        if ($request->ajax()) {
            // Retorna apenas o conteúdo do card para navegação dinâmica
            return response()->view('planejamentos.partials.cronograma-detalhe-card', $viewData);
        }

        return view('planejamentos.cronograma-detalhe', $viewData);
    }
    /**
     * Exporta o planejamento em diferentes formatos
     */
    public function exportPlanejamento(Planejamento $planejamento, $format)
    {
        $this->authorize('view', $planejamento);

        $validFormats = ['pdf', 'docx', 'excel'];

        if (!in_array($format, $validFormats)) {
            abort(404);
        }

        // Implementar lógica de exportação
        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($planejamento);
            case 'docx':
                return $this->exportToDocx($planejamento);
            case 'excel':
                return $this->exportToExcel($planejamento);
        }
    }

    /**
     * Edita uma seção específica do planejamento
     */
    public function editSection(Planejamento $planejamento, $section)
    {
        $this->authorize('update', $planejamento);

        $validSections = ['informacoes', 'configuracao', 'periodo', 'conteudo', 'metodologia', 'avaliacao', 'recursos', 'observacoes'];

        if (!in_array($section, $validSections)) {
            abort(404);
        }

        return view("planejamentos.view.edit.{$section}", compact('planejamento'));
    }

    /**
     * Atualiza uma seção específica do planejamento
     */
    public function updateSection(Request $request, Planejamento $planejamento, $section)
    {
        $this->authorize('update', $planejamento);

        try {
            $data = $request->all();

            // Validação específica por seção
            $rules = $this->getValidationRulesForSection($section);
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Atualizar seção específica
            $this->updatePlanejamentoSection($planejamento, $section, $data);

            return response()->json([
                'success' => true,
                'message' => 'Seção atualizada com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao atualizar seção {$section}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    // Métodos auxiliares para APIs do wizard

    public function getUnidades()
    {
        // Implementar lógica para buscar unidades escolares
        return response()->json([]);
    }

    public function getTurnosByUnidade(Request $request)
    {
        try {
            $modalidadeId = $request->input('modalidade_id');
            $nivelEnsinoId = $request->input('nivel_ensino_id');

            $user = auth()->user();
            $escolaId = null;

            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $escolaId = session('escola_atual') ?: $user->escola_id;
            } else {
                $escolaId = $user->escola_id;
            }

            if (!$modalidadeId || !$nivelEnsinoId) {
                return response()->json(['error' => 'Modalidade e nível de ensino são obrigatórios'], 400);
            }

            // Buscar o nível de ensino para verificar quais turnos ele suporta
            $nivelEnsino = \App\Models\NivelEnsino::find($nivelEnsinoId);
            if (!$nivelEnsino) {
                return response()->json(['error' => 'Nível de ensino não encontrado'], 404);
            }

            // Buscar turnos que existem em grades de aulas ativas para turmas do nível de ensino selecionado
            // Verificar se o nível de ensino é compatível com a modalidade
            $modalidade = \App\Models\ModalidadeEnsino::find($modalidadeId);
            if (!$modalidade) {
                return response()->json(['error' => 'Modalidade não encontrada'], 404);
            }

            $nivelCompativel = \App\Models\NivelEnsino::where('id', $nivelEnsinoId)
                ->whereJsonContains('modalidades_compativeis', $modalidade->codigo)
                ->exists();

            if (!$nivelCompativel) {
                return response()->json(['error' => 'Nível de ensino não é compatível com a modalidade selecionada'], 400);
            }


            $turnosComGradeAtiva = \App\Models\GradeAula::select('turnos.id', 'turnos.nome', 'turnos.hora_inicio', 'turnos.hora_fim', 'turmas.nivel_ensino_id')
                ->join('turmas', 'grade_aulas.turma_id', '=', 'turmas.id')
                ->join('turnos', 'turnos.id', '=', 'turmas.turno_id')
                ->where('grade_aulas.ativo', true)
                ->where('turmas.ativo', true)
                ->where('turmas.escola_id', $escolaId)
                ->where('turmas.nivel_ensino_id', $nivelEnsinoId)
                ->distinct()
                ->get();

            // Filtrar turnos que:
            // 1. Existem em grades de aulas ativas
            // 2. São suportados pelo nível de ensino
            // 3. Têm configuração ativa na escola para essa modalidade
            $turnos = collect($turnosComGradeAtiva)->filter(function ($turno) use ($nivelEnsino, $escolaId, $modalidadeId) {
                // Verificar se o nível de ensino suporta este turno
                $turnoNome = strtolower($turno->nome);
                if (!$nivelEnsino->suportaTurno($turnoNome)) {
                    return false;
                }

                // Verificar se a escola tem configuração ativa para esta modalidade e turno
                $config = \App\Models\EscolaModalidadeConfig::where('escola_id', $escolaId)
                    ->where('modalidade_ensino_id', $modalidadeId)
                    ->where('ativo', true)
                    ->first();

                if (!$config) {
                    return false;
                }

                // Verificar se a configuração permite este turno
                $turnosPermitidos = $config->getTurnosPermitidos();
                return in_array($turnoNome, $turnosPermitidos);
            })
                ->map(function ($turno) {
                    return [
                        'id' => $turno->id,
                        'nome' => $turno->nome,
                        'codigo' => strtolower($turno->nome),
                        'hora_inicio' => $turno->hora_inicio ? substr($turno->hora_inicio, 0, 5) : null,
                        'hora_fim' => $turno->hora_fim ? substr($turno->hora_fim, 0, 5) : null
                    ];
                })
                ->values();

            return response()->json($turnos);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar turnos por unidade: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar turnos.', 'message' => $e->getMessage()], 500);
        }
    }

    public function getTurmasByUnidadeTurno(Request $request)
    {
        try {
            $escolaId = auth()->user()->escola_id;
            $modalidadeId = $request->input('modalidade_id');
            $nivelEnsinoId = $request->input('nivel_ensino_id');
            $turnoId = $request->input('turno_id');

            if (!$modalidadeId || !$nivelEnsinoId || !$turnoId) {
                return response()->json(['error' => 'Modalidade, nível de ensino e turno são obrigatórios'], 400);
            }

            // Buscar o turno para obter seu código
            $turno = \App\Models\Turno::find($turnoId);
            if (!$turno) {
                return response()->json(['error' => 'Turno não encontrado'], 404);
            }

            $turnoCodigo = strtolower($turno->nome);

            // Buscar turmas que:
            // 1. Estão ativas
            // 2. Pertencem à escola do usuário
            // 3. Têm nível de ensino compatível com a modalidade
            // 4. Suportam o turno selecionado
            // 5. Têm salas ativas na escola
            $turmas = \App\Models\Turma::ativas()
                ->where('escola_id', $escolaId)
                ->whereHas('nivelEnsino', function ($query) use ($modalidadeId, $turnoCodigo) {
                    $query->where('ativo', true)
                        ->porModalidade($modalidadeId)
                        ->porTurno($turnoCodigo);
                })
                ->whereHas('salas', function ($query) use ($escolaId) {
                    $query->where('escola_id', $escolaId)
                        ->where('ativo', true);
                })
                ->with([
                    'nivelEnsino',
                    'grupo',
                    'salas' => function ($query) use ($escolaId) {
                        $query->where('escola_id', $escolaId)->where('ativo', true);
                    },
                    'disciplinas' => function ($query) {
                        $query->where('ativo', true);
                    }
                ])
                ->orderBy('nome')
                ->get()
                ->map(function ($turma) {
                    return [
                        'id' => $turma->id,
                        'nome' => $turma->nome,
                        'serie' => $turma->grupo ? $turma->grupo->ano_serie_formatado : 'N/A',
                        'nivel_ensino' => $turma->nivelEnsino ? $turma->nivelEnsino->nome : 'N/A',
                        'grupo' => $turma->grupo ? $turma->grupo->nome : 'N/A',
                        'total_alunos' => $turma->alunos()->count(),
                        'sala_nome' => $turma->salas->first() ? $turma->salas->first()->nome : 'N/A',
                        'salas' => $turma->salas->map(function ($sala) {
                            return [
                                'id' => $sala->id,
                                'nome' => $sala->nome,
                                'capacidade' => $sala->capacidade
                            ];
                        }),
                        'disciplinas' => $turma->disciplinas->map(function ($disciplina) {
                            return [
                                'id' => $disciplina->id,
                                'nome' => $disciplina->nome,
                                'area' => $disciplina->area_conhecimento ?? 'Geral',
                                'carga_horaria' => $disciplina->carga_horaria ?? null
                            ];
                        })
                    ];
                });

            return response()->json($turmas);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar turmas por unidade e turno: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar turmas.', 'message' => $e->getMessage()], 500);
        }
    }

    public function getDisciplinasByTurma($turma)
    {
        // Implementar lógica para buscar disciplinas por turma
        return response()->json([]);
    }

    public function getProfessoresByTurmaDisciplina($turma, $disciplina)
    {
        // Implementar lógica para buscar professores por turma e disciplina
        return response()->json([]);
    }

    /**
     * Retorna detalhes da disciplina para a etapa 3 do wizard.
     * Resposta esperada: { area: string|null, objetivos: string[] }
     */
    public function getDisciplinaDetalhes($disciplinaId)
    {
        try {
            $disciplina = \App\Models\Disciplina::find($disciplinaId);
            if (!$disciplina) {
                return response()->json(['error' => 'Disciplina não encontrada.'], 404);
            }

            $area = $disciplina->area_conhecimento ?? null;
            $objetivos = [];
            $saberes = [];

            // Para Educação Infantil, mapear objetivos pelo Campo de Experiência
            if ($area) {
                // Normalizar área (remover possíveis prefixos "-" vindos da disciplina)
                $normalizedArea = ltrim($area, "- ");
                $campo = \App\Models\CampoExperiencia::where('nome', $normalizedArea)->first();
                if ($campo) {
                    $objetivos = \App\Models\ObjetivoAprendizagem::ativos()
                        ->where('campo_experiencia_id', $campo->id)
                        ->orderBy('codigo')
                        ->pluck('descricao')
                        ->toArray();

                    // Opcional: retornar estrutura agrupada por saber/conhecimento
                    $saberes = \App\Models\SaberConhecimento::ativos()
                        ->where('campo_experiencia_id', $campo->id)
                        ->orderBy('ordem')
                        ->orderBy('titulo')
                        ->get()
                        ->map(function ($saber) {
                            $objetivosSaber = \App\Models\ObjetivoAprendizagem::ativos()
                                ->where('saber_conhecimento_id', $saber->id)
                                ->orderBy('codigo')
                                ->get(['codigo', 'descricao'])
                                ->map(function ($obj) {
                                    return [
                                        'codigo' => $obj->codigo,
                                        'descricao' => $obj->descricao,
                                    ];
                                })
                                ->toArray();

                            return [
                                'id' => $saber->id,
                                'titulo' => $saber->titulo,
                                'objetivos' => $objetivosSaber,
                            ];
                        })
                        ->toArray();
                }
            }

            return response()->json([
                'area' => $area,
                'objetivos' => $objetivos,
                'saberes' => $saberes,
            ]);

        } catch (\Throwable $e) {
            \Log::error('Erro ao carregar detalhes da disciplina', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Erro ao carregar detalhes da disciplina.',
                'message' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor.'
            ], 500);
        }
    }

    public function verificarCompatibilidade(Request $request)
    {
        // Implementar lógica de verificação de compatibilidade
        return response()->json(['compatible' => true]);
    }

    public function verificarConflitos(Request $request)
    {
        // Implementar lógica de verificação de conflitos
        // Mantemos a resposta vazia por ora, usando a chave esperada pelo frontend
        return response()->json(['conflitos' => []]);
    }

    public function getCamposExperiencia()
    {
        // Implementar lógica para buscar campos de experiência
        return response()->json([]);
    }

    public function getObjetivosAprendizagem(Request $request)
    {
        try {
            $query = \App\Models\ObjetivoAprendizagem::with('campoExperiencia')->ativos();

            // Filtrar por um único campo de experiência
            if ($request->filled('campo_experiencia_id')) {
                $query->where('campo_experiencia_id', $request->campo_experiencia_id);
            }

            // Filtrar por múltiplos campos (campos_experiencia[])
            if ($request->filled('campos_experiencia')) {
                $campos = is_array($request->campos_experiencia) ? $request->campos_experiencia : [$request->campos_experiencia];
                $query->whereIn('campo_experiencia_id', $campos);
            }

            // Filtrar por faixa etária se fornecido
            if ($request->filled('faixa_etaria')) {
                $query->porFaixaEtaria($request->faixa_etaria);
            }

            // Busca textual se fornecida
            if ($request->filled('busca')) {
                $busca = $request->busca;
                $query->where(function ($q) use ($busca) {
                    $q->where('codigo', 'like', "%{$busca}%")
                        ->orWhere('descricao', 'like', "%{$busca}%");
                });
            }

            $objetivos = $query->orderBy('codigo')->get();

            return response()->json([
                'success' => true,
                'objetivos' => $objetivos->map(function ($objetivo) {
                    return [
                        'id' => $objetivo->id,
                        'codigo' => $objetivo->codigo,
                        'descricao' => $objetivo->descricao,
                        'faixa_etaria' => $objetivo->faixa_etaria,
                        'campo_experiencia' => [
                            'id' => $objetivo->campoExperiencia->id,
                            'nome' => $objetivo->campoExperiencia->nome
                        ]
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar objetivos de aprendizagem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSaberesConhecimentos(Request $request)
    {
        try {
            $query = \App\Models\SaberConhecimento::with('campoExperiencia')->ativos();

            // Filtrar por um único campo de experiência
            if ($request->filled('campo_experiencia_id')) {
                $query->where('campo_experiencia_id', $request->campo_experiencia_id);
            }

            // Filtrar por múltiplos campos (campos_experiencia[])
            if ($request->filled('campos_experiencia')) {
                $campos = is_array($request->campos_experiencia) ? $request->campos_experiencia : [$request->campos_experiencia];
                $query->whereIn('campo_experiencia_id', $campos);
            }

            // Busca textual em título/descrição
            if ($request->filled('busca')) {
                $busca = $request->busca;
                $query->where(function ($q) use ($busca) {
                    $q->where('titulo', 'like', "%{$busca}%")
                      ->orWhere('descricao', 'like', "%{$busca}%");
                });
            }

            $saberes = $query->orderBy('ordem')->orderBy('titulo')->get();

            return response()->json([
                'success' => true,
                'saberes' => $saberes->map(function ($saber) {
                    return [
                        'id' => $saber->id,
                        'titulo' => $saber->titulo,
                        'descricao' => $saber->descricao,
                        'ordem' => $saber->ordem,
                        'campo_experiencia' => [
                            'id' => $saber->campoExperiencia->id,
                            'nome' => $saber->campoExperiencia->nome,
                        ],
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar saberes e conhecimentos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSugestoesSaberes(Request $request)
    {
        try {
            // Validar se foi fornecido pelo menos um campo de experiência
            if (!$request->filled('campos_experiencia')) {
                return response()->json([
                    'success' => false,
                    'message' => 'É necessário selecionar pelo menos um campo de experiência'
                ], 400);
            }

            $camposExperiencia = $request->campos_experiencia;

            // Buscar campos de experiência selecionados
            $campos = \App\Models\CampoExperiencia::whereIn('id', $camposExperiencia)->ativos()->get();

            if ($campos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum campo de experiência válido encontrado'
                ], 404);
            }

            // Sugestões de saberes e conhecimentos baseadas nos campos de experiência da BNCC
            $sugestoesPorCampo = [
                'O eu, o outro e o nós' => [
                    'Desenvolvimento da identidade pessoal e autonomia',
                    'Reconhecimento e expressão de emoções e sentimentos',
                    'Interação social e cooperação com pares e adultos',
                    'Respeito às diferenças individuais e culturais',
                    'Construção de vínculos afetivos seguros',
                    'Desenvolvimento da autoestima e autoconfiança',
                    'Participação em atividades coletivas e colaborativas'
                ],
                'Corpo, gestos e movimentos' => [
                    'Coordenação motora fina e grossa',
                    'Expressão corporal e gestual',
                    'Controle e consciência corporal',
                    'Orientação espacial e temporal',
                    'Ritmo e movimento',
                    'Cuidados com o próprio corpo e higiene',
                    'Exploração de diferentes formas de locomoção'
                ],
                'Traços, sons, cores e formas' => [
                    'Exploração de materiais e técnicas artísticas',
                    'Desenvolvimento da criatividade e imaginação',
                    'Percepção visual, auditiva e tátil',
                    'Expressão através das artes visuais',
                    'Apreciação de diferentes manifestações artísticas',
                    'Experimentação com cores, formas e texturas',
                    'Desenvolvimento da sensibilidade estética'
                ],
                'Escuta, fala, pensamento e imaginação' => [
                    'Desenvolvimento da linguagem oral',
                    'Ampliação do vocabulário',
                    'Compreensão e interpretação de textos',
                    'Expressão de ideias e sentimentos através da fala',
                    'Contato com diferentes gêneros textuais',
                    'Desenvolvimento da imaginação e criatividade',
                    'Iniciação ao mundo da escrita'
                ],
                'Espaços, tempos, quantidades, relações e transformações' => [
                    'Exploração do ambiente e suas características',
                    'Noções básicas de tempo e espaço',
                    'Contagem e quantificação',
                    'Observação de fenômenos naturais',
                    'Classificação e seriação de objetos',
                    'Relações de causa e efeito',
                    'Experimentação e investigação científica'
                ]
            ];

            $sugestoes = [];
            foreach ($campos as $campo) {
                if (isset($sugestoesPorCampo[$campo->nome])) {
                    $sugestoes = array_merge($sugestoes, $sugestoesPorCampo[$campo->nome]);
                }
            }

            // Remover duplicatas e reorganizar
            $sugestoes = array_unique($sugestoes);
            sort($sugestoes);

            return response()->json([
                'success' => true,
                'sugestoes' => array_values($sugestoes),
                'campos_selecionados' => $campos->pluck('nome')->toArray()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar sugestões de saberes: ' . $e->getMessage()
            ], 500);
        }
    }

    // Métodos auxiliares privados

    private function getValidationRulesForStep($step)
    {
        $rules = [
            '1' => [
                'modalidade_ensino_id' => 'required|exists:modalidades_ensino,id',
                'nivel_ensino_id' => 'required|exists:niveis_ensino,id',
                'titulo' => 'nullable|string|max:255',
                'descricao' => 'nullable|string|max:1000'
            ],
            '2' => [
                'turno_id' => 'required|exists:turnos,id',
                'escola_id' => 'required|exists:escolas,id'
            ],
            '3' => [
                'turma_id' => 'required|exists:turmas,id',
                'disciplina_id' => 'required|exists:disciplinas,id',
                'professor_responsavel' => 'nullable|string|max:255',
                'grupo_id' => 'nullable|exists:grupos,id'
            ],
            '4' => [
                'data_inicio' => 'required|date|after_or_equal:today',
                'numero_dias' => 'required|integer|min:1|max:365',
                'carga_horaria_aula' => 'required|numeric|min:0.5|max:8',
                'aulas_por_semana' => 'required|integer|min:1|max:40',
                'tipo_periodo' => 'required|string|in:semanal,quinzenal,mensal,bimestral,trimestral,semestral,anual'
            ],
            '5' => [
                'campos_experiencia' => 'required|array|min:1',
                'campos_experiencia.*' => 'string|max:255',
                'saberes_conhecimentos' => 'nullable|string|max:5000',
                'metodologia' => 'required|string|min:10|max:5000',
                'objetivo_geral' => 'nullable|string|max:2000',
                'objetivos_especificos' => 'nullable|array',
                'objetivos_especificos.*' => 'string|max:500',
                'competencias_bncc' => 'nullable|array',
                'competencias_bncc.*' => 'string|max:500',
                'habilidades_bncc' => 'nullable|array',
                'habilidades_bncc.*' => 'string|max:500'
            ],
            '6' => [
                'observacoes_finais' => 'nullable|string|max:2000',
                'aceita_termos' => 'required|accepted',
                'recursos_necessarios' => 'nullable|array',
                'recursos_necessarios.*' => 'string|max:255',
                'avaliacao_metodos' => 'nullable|array',
                'avaliacao_metodos.*' => 'string|max:255'
            ]
        ];

        return $rules[$step] ?? [];
    }

    private function getValidationRulesForSection($section)
    {
        $rules = [
            'informacoes_basicas' => [
                'titulo' => 'required|string|max:255',
                'modalidade' => 'required|exists:modalidades_ensino,id',
                'nivel_ensino' => 'required|string',
                'tipo_professor' => 'required|string'
            ],
            'configuracao_escolar' => [
                'unidade_escolar' => 'required|string|max:255',
                'turno_id' => 'required|exists:turnos,id',
                'escola_id' => 'required|exists:escolas,id'
            ],
            'turma_disciplina' => [
                'turma_id' => 'required|exists:turmas,id',
                'disciplina_id' => 'required|exists:disciplinas,id',
                'professor_responsavel' => 'nullable|string|max:255'
            ],
            'periodo_carga' => [
                'data_inicio' => 'required|date|after_or_equal:today',
                'numero_dias' => 'required|integer|min:1|max:365',
                'carga_horaria_aula' => 'required|numeric|min:0.5|max:8',
                'aulas_por_semana' => 'required|integer|min:1|max:40'
            ],
            'conteudo_pedagogico' => [
                'campos_experiencia' => 'required|array|min:1',
                'campos_experiencia.*' => 'string',
                'saberes_conhecimentos' => 'nullable|string|max:5000',
                'metodologia' => 'required|string|min:10|max:5000',
                'objetivo_geral' => 'nullable|string|max:2000',
                'objetivos_especificos' => 'nullable|array',
                'objetivos_especificos.*' => 'string|max:500',
                'competencias_bncc' => 'nullable|array',
                'competencias_bncc.*' => 'string|max:500',
                'habilidades_bncc' => 'nullable|array',
                'habilidades_bncc.*' => 'string|max:500'
            ],
            'finalizacao' => [
                'observacoes_finais' => 'nullable|string|max:2000',
                'aceita_termos' => 'required|accepted',
                'recursos_necessarios' => 'nullable|array',
                'recursos_necessarios.*' => 'string|max:255',
                'avaliacao_metodos' => 'nullable|array',
                'avaliacao_metodos.*' => 'string|max:255'
            ]
        ];

        return $rules[$section] ?? [];
    }

    private function validateWizardDataForDraft($data)
    {
        $rules = [
            // Informações básicas (Etapa 1) - mais flexível para rascunhos
            'modalidade' => 'nullable|exists:modalidades_ensino,id',
            'modalidade_ensino_id' => 'nullable|exists:modalidades_ensino,id',
            'nivel_ensino' => 'nullable|string|in:infantil,fundamental_1,fundamental_2,medio,eja,superior',
            'nivel_ensino_id' => 'nullable',
            'tipo_professor' => 'nullable|string|in:titular,substituto,auxiliar,especialista',
            'tipo_professor_id' => 'nullable|string|in:titular,substituto,auxiliar,especialista',

            // Configuração escolar (Etapa 2)
            'unidade_escolar' => 'nullable|string|max:255',
            'turno_id' => 'nullable|exists:turnos,id',
            'escola_id' => 'nullable|exists:escolas,id',

            // Turma e disciplina (Etapa 3)
            'turma_id' => 'nullable|exists:turmas,id',
            'disciplina_id' => 'nullable|exists:disciplinas,id',
            'professor_id' => 'nullable|exists:users,id',
            'professor_responsavel' => 'nullable|string|max:255',

            // Período e carga horária (Etapa 4) - sem validação de data futura para rascunhos
            'data_inicio' => 'nullable|date',
            'numero_dias' => 'nullable|integer|min:1|max:365',
            'carga_horaria_aula' => 'nullable|numeric|min:0.5|max:8',
            'aulas_por_semana' => 'nullable|integer|min:1|max:40',
            'tipo_periodo' => 'nullable|string|in:semanal,quinzenal,mensal,bimestral,trimestral,semestral,anual',

            // Conteúdo pedagógico (Etapa 5) - mais flexível
            'campos_experiencia' => 'nullable|array',
            'campos_experiencia.*' => 'string|max:255',
            'saberes_conhecimentos' => 'nullable|string|max:5000',
            'metodologia' => 'nullable|string|max:5000',
            'objetivo_geral' => 'nullable|string|max:2000',
            'objetivos_especificos' => 'nullable|array',
            'objetivos_especificos.*' => 'string|max:500',
            'objetivos_aprendizagem' => 'nullable|array',
            'objetivos_aprendizagem.*' => 'string|max:500',
            'competencias_bncc' => 'nullable|array',
            'competencias_bncc.*' => 'string|max:500',
            'habilidades_bncc' => 'nullable|array',
            'habilidades_bncc.*' => 'string|max:500',

            // Finalização (Etapa 6) - não obrigatório para rascunhos
            'aceita_termos' => 'nullable',
            'observacoes_finais' => 'nullable|string|max:2000',
            'recursos_necessarios' => 'nullable|array',
            'recursos_necessarios.*' => 'string|max:255',
            'avaliacao_metodos' => 'nullable|array',
            'avaliacao_metodos.*' => 'string|max:255',

            // Campos obrigatórios
            'titulo' => 'nullable|string|max:255',
            'grupo_id' => 'nullable|exists:grupos,id'
        ];

        $messages = [
            'modalidade.exists' => 'A modalidade selecionada não é válida.',
            'nivel_ensino.in' => 'O nível de ensino deve ser: infantil, fundamental 1, fundamental 2, médio, EJA ou superior.',
            'tipo_professor.in' => 'O tipo de professor deve ser: titular, substituto, auxiliar ou especialista.',
            'turma_id.exists' => 'A turma selecionada não é válida.',
            'disciplina_id.exists' => 'A disciplina selecionada não é válida.',
            'data_inicio.date' => 'A data de início deve ser uma data válida.',
            'numero_dias.min' => 'O planejamento deve ter pelo menos 1 dia.',
            'numero_dias.max' => 'O planejamento não pode exceder 365 dias.',
            'carga_horaria_aula.min' => 'A carga horária mínima é de 0,5 horas.',
            'carga_horaria_aula.max' => 'A carga horária máxima é de 8 horas por aula.',
            'aulas_por_semana.min' => 'Deve haver pelo menos 1 aula por semana.',
            'aulas_por_semana.max' => 'Não pode exceder 40 aulas por semana.',
            'saberes_conhecimentos.max' => 'Os saberes e conhecimentos não podem exceder 5000 caracteres.',
            'metodologia.max' => 'A metodologia não pode exceder 5000 caracteres.'
        ];

        $validator = Validator::make($data, $rules, $messages);

        return $validator;
    }

    private function validateWizardData($data)
    {
        $rules = [
            // Informações básicas (Etapa 1)
            'modalidade' => 'nullable|exists:modalidades_ensino,id',
            'modalidade_ensino_id' => 'nullable|exists:modalidades_ensino,id',
            'nivel_ensino' => 'nullable|string|in:infantil,fundamental_1,fundamental_2,medio,eja,superior',
            'nivel_ensino_id' => 'nullable',
            'tipo_professor' => 'nullable|string|in:titular,substituto,auxiliar,especialista',
            'tipo_professor_id' => 'nullable|string|in:titular,substituto,auxiliar,especialista',

            // Configuração escolar (Etapa 2)
            'unidade_escolar' => 'nullable|string|max:255',
            'turno_id' => 'required|exists:turnos,id',
            'escola_id' => 'required|exists:escolas,id',

            // Turma e disciplina (Etapa 3)
            'turma_id' => 'required|exists:turmas,id',
            'disciplina_id' => 'required|exists:disciplinas,id',
            'professor_id' => 'required|exists:users,id',
            'professor_responsavel' => 'nullable|string|max:255',

            // Período e carga horária (Etapa 4)
            'data_inicio' => 'required|date|after_or_equal:today',
            'numero_dias' => 'required|integer|min:1|max:365',
            'carga_horaria_aula' => 'required|numeric|min:0.5|max:8',
            'aulas_por_semana' => 'required|integer|min:1|max:40',
            'tipo_periodo' => 'required|string|in:semanal,quinzenal,mensal,bimestral,trimestral,semestral,anual',

            // Conteúdo pedagógico (Etapa 5)
            'campos_experiencia' => 'required|array|min:1',
            'campos_experiencia.*' => 'string|max:255',
            'saberes_conhecimentos' => 'nullable|string|max:5000',
            'metodologia' => 'required|string|min:10|max:5000',
            'objetivo_geral' => 'nullable|string|max:2000',
            'objetivos_especificos' => 'nullable|array',
            'objetivos_especificos.*' => 'string|max:500',
            'competencias_bncc' => 'nullable|array',
            'competencias_bncc.*' => 'string|max:500',
            'habilidades_bncc' => 'nullable|array',
            'habilidades_bncc.*' => 'string|max:500',

            // Finalização (Etapa 6)
            'aceita_termos' => 'nullable|accepted',
            'aceitar_termos' => 'nullable|accepted',
            'observacoes_finais' => 'nullable|string|max:2000',
            'recursos_necessarios' => 'nullable|array',
            'recursos_necessarios.*' => 'string|max:255',
            'avaliacao_metodos' => 'nullable|array',
            'avaliacao_metodos.*' => 'string|max:255',

            // Campos opcionais
            'titulo' => 'nullable|string|max:255',
            'grupo_id' => 'nullable|exists:grupos,id'
        ];

        $messages = [
            'modalidade.required' => 'A modalidade de ensino é obrigatória.',
            'modalidade.exists' => 'A modalidade selecionada não é válida.',
            'nivel_ensino.required' => 'O nível de ensino é obrigatório.',
            'nivel_ensino.in' => 'O nível de ensino deve ser: infantil, fundamental 1, fundamental 2, médio, EJA ou superior.',
            'tipo_professor.required' => 'O tipo de professor é obrigatório.',
            'tipo_professor.in' => 'O tipo de professor deve ser: titular, substituto, auxiliar ou especialista.',
            'turma_id.required' => 'A turma é obrigatória.',
            'turma_id.exists' => 'A turma selecionada não é válida.',
            'disciplina_id.required' => 'A disciplina é obrigatória.',
            'disciplina_id.exists' => 'A disciplina selecionada não é válida.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.after_or_equal' => 'A data de início deve ser hoje ou uma data futura.',
            'numero_dias.required' => 'O número de dias é obrigatório.',
            'numero_dias.min' => 'O planejamento deve ter pelo menos 1 dia.',
            'numero_dias.max' => 'O planejamento não pode exceder 365 dias.',
            'carga_horaria_aula.required' => 'A carga horária por aula é obrigatória.',
            'carga_horaria_aula.min' => 'A carga horária mínima é de 0,5 horas.',
            'carga_horaria_aula.max' => 'A carga horária máxima é de 8 horas por aula.',
            'aulas_por_semana.required' => 'O número de aulas por semana é obrigatório.',
            'aulas_por_semana.min' => 'Deve haver pelo menos 1 aula por semana.',
            'aulas_por_semana.max' => 'Não pode exceder 40 aulas por semana.',
            'campos_experiencia.required' => 'Pelo menos um campo de experiência deve ser selecionado.',
            'saberes_conhecimentos.required' => 'Os saberes e conhecimentos são obrigatórios.',
            'saberes_conhecimentos.min' => 'Os saberes e conhecimentos devem ter pelo menos 10 caracteres.',
            'metodologia.required' => 'A metodologia é obrigatória.',
            'metodologia.min' => 'A metodologia deve ter pelo menos 10 caracteres.',
            'titulo.required' => 'O título do planejamento é obrigatório.',
            'titulo.max' => 'O título deve ter no máximo 255 caracteres.',
            'objetivos_aprendizagem.required' => 'Pelo menos um objetivo de aprendizagem deve ser selecionado.',
            'objetivos_aprendizagem.min' => 'Pelo menos um objetivo de aprendizagem deve ser selecionado.',
            'nivel_ensino_id.required' => 'O nível de ensino é obrigatório.',
            'professor_id.required' => 'O professor responsável é obrigatório.',
            'professor_id.exists' => 'O professor selecionado não é válido.',
            'aceita_termos.required' => 'É necessário aceitar os termos e condições.',
            'aceita_termos.accepted' => 'É necessário aceitar os termos e condições.'
        ];

        $validator = Validator::make($data, $rules, $messages);

        // Validação adicional: finalização exige diários completos
        $validator->after(function ($validator) use ($data) {
            $diarios = $data['planejamentos_diarios'] ?? [];

            if (!is_array($diarios) || count($diarios) === 0) {
                $validator->errors()->add('planejamentos_diarios', 'Os diários do planejamento são obrigatórios para a finalização.');
                return;
            }

            // Verificar quantidade mínima conforme número de dias planejados
            $numeroDias = isset($data['numero_dias']) ? (int) $data['numero_dias'] : null;
            if ($numeroDias && $numeroDias > 0) {
                // Considerar entradas únicas por data
                $datas = collect($diarios)
                    ->filter(fn($d) => is_array($d) && isset($d['data']) && $d['data'])
                    ->pluck('data')
                    ->unique()
                    ->values();
                if ($datas->count() < $numeroDias) {
                    $faltam = $numeroDias - $datas->count();
                    $validator->errors()->add('planejamentos_diarios', "Planejamento diário incompleto: faltam {$faltam} de {$numeroDias} dia(s) planejados.");
                }
            }

            // Verificar campos obrigatórios em cada diário planejado
            $incompletos = [];
            foreach ($diarios as $entry) {
                if (!is_array($entry)) { continue; }
                $dataDia = $entry['data'] ?? null;
                $planejado = array_key_exists('planejado', $entry) ? (bool) $entry['planejado'] : true; // default true
                if (!$planejado) { continue; }

                $campos = $entry['campos_experiencia'] ?? [];
                $saberes = $entry['saberes_conhecimentos'] ?? '';
                $objetivos = $entry['objetivos_aprendizagem'] ?? [];

                $faltando = [];
                if (!is_array($campos) || count($campos) === 0) { $faltando[] = 'campos de experiência'; }
                if (!is_array($objetivos) || count($objetivos) === 0) { $faltando[] = 'objetivos de aprendizagem'; }
                if ((is_array($saberes) && count($saberes) === 0) || (is_string($saberes) && trim($saberes) === '')) { $faltando[] = 'saberes e conhecimentos'; }

                if (count($faltando) > 0) {
                    $rotulo = $dataDia ? $dataDia : 'dia sem data';
                    $incompletos[] = $rotulo . ' (' . implode(', ', $faltando) . ')';
                }
            }

            if (count($incompletos) > 0) {
                $validator->errors()->add('planejamentos_diarios', 'Dias com campos obrigatórios em falta: ' . implode('; ', $incompletos));
            }
        });

        return $validator;
    }

    private function createOrUpdateFromWizard($data)
    {
        try {
            $user = Auth::user();
            
            Log::info('Iniciando createOrUpdateFromWizard', [
                'user_id' => $user->id,
                'data_recebida' => $data
            ]);

            // Compatibilidade: aceitar 'id' como alias de 'planejamento_id'
            if ((!isset($data['planejamento_id']) || !$data['planejamento_id']) && isset($data['id']) && $data['id']) {
                $data['planejamento_id'] = $data['id'];
            }

            // Mapear campos do formulário para os campos do banco
            $modalidade = $data['modalidade'] ?? $data['modalidade_ensino_id'] ?? null;
            $nivelEnsino = $data['nivel_ensino'] ?? $data['nivel_ensino_id'] ?? null;
            $tipoProf = $data['tipo_professor'] ?? $data['tipo_professor_id'] ?? null;

            // Definir escola sob contexto para evitar nulos em superadmin/suporte
            $escolaIdContext = null;
            try {
                if (method_exists($user, 'isSuperAdmin') && ($user->isSuperAdmin() || (method_exists($user, 'temCargo') && $user->temCargo('Suporte')))) {
                    $escolaIdContext = \App\Http\Middleware\EscolaContext::getEscolaAtual() ?: $user->escola_id;
                } else {
                    $escolaIdContext = $user->escola_id;
                }
            } catch (\Throwable $e) {
                $escolaIdContext = $user->escola_id;
            }

            // Preparar dados do planejamento
            $planejamentoData = [
                'user_id' => $user->id,
                'escola_id' => $data['escola_id'] ?? $escolaIdContext,
                'modalidade_id' => $modalidade,
                'modalidade' => $modalidade, // Para compatibilidade
                'nivel_ensino_id' => $nivelEnsino,
                'nivel_ensino' => $nivelEnsino, // Para compatibilidade
                'turno_id' => $data['turno_id'] ?? null,
                'turma_id' => $data['turma_id'] ?? null,
                'disciplina_id' => $data['disciplina_id'] ?? null,
                'professor_id' => $data['professor_id'] ?? $user->id,
                'data_inicio' => $data['data_inicio'] ?? null,
                'data_fim' => $data['data_fim'] ?? null,
                'numero_dias' => $data['numero_dias'] ?? null,
                'titulo' => $data['titulo'] ?? $this->generateDefaultTitle($data),
                'objetivo_geral' => $data['objetivo_geral'] ?? '',
                'objetivos_especificos' => $this->processArrayField($data['objetivos_especificos'] ?? []),
                'competencias_bncc' => $this->processArrayField($data['competencias_bncc'] ?? []),
                'habilidades_bncc' => $this->processArrayField($data['habilidades_bncc'] ?? []),
                'metodologia' => $data['metodologia'] ?? '',
                'recursos_necessarios' => $this->processArrayField($data['recursos_necessarios'] ?? []),
                'avaliacao_metodos' => $this->processArrayField($data['avaliacao_metodos'] ?? []),
                'observacoes' => $data['observacoes'] ?? $data['observacoes_finais'] ?? '',
                'observacoes_finais' => $data['observacoes_finais'] ?? '',
                'status' => $data['status'] ?? 'rascunho',
                'tipo_professor' => $tipoProf,
                'unidade_escolar' => $data['unidade_escolar'] ?? null,
                'professor_responsavel' => $data['professor_responsavel'] ?? $user->name,
                'carga_horaria_aula' => $data['carga_horaria_aula'] ?? null,
                'carga_horaria_total' => $data['carga_horaria_total'] ?? null,
                'aulas_por_semana' => $data['aulas_por_semana'] ?? null,
                'total_aulas' => $data['total_aulas'] ?? null,
                'tipo_periodo' => $data['tipo_periodo'] ?? null,
                'bimestre' => $data['bimestre'] ?? null,
                'ano_letivo' => $data['ano_letivo'] ?? null,
                'campos_experiencia' => $this->processArrayField($data['campos_experiencia'] ?? []),
                'saberes_conhecimentos' => $data['saberes_conhecimentos'] ?? null,
                'objetivos_aprendizagem' => $this->processArrayField($data['objetivos_aprendizagem'] ?? [])
            ];
            
            Log::info('Dados preparados para salvar', [
                'planejamentoData' => $planejamentoData
            ]);

            // Verificar se é edição ou criação
            if (isset($data['planejamento_id']) && $data['planejamento_id']) {
                Log::info('Atualizando planejamento existente', ['planejamento_id' => $data['planejamento_id']]);
                $planejamento = Planejamento::findOrFail($data['planejamento_id']);
                $this->authorize('update', $planejamento);

                // Preservar campos fixos quando não vierem explicitamente no payload
                if (!isset($data['professor_id']) || !$data['professor_id']) {
                    $planejamentoData['professor_id'] = $planejamento->professor_id;
                }
                if (!isset($data['professor_responsavel']) || !$data['professor_responsavel']) {
                    $planejamentoData['professor_responsavel'] = $planejamento->professor_responsavel;
                }
                if (!isset($data['user_id']) || !$data['user_id']) {
                    $planejamentoData['user_id'] = $planejamento->user_id;
                }

                $planejamento->update($planejamentoData);
                Log::info('Planejamento atualizado com sucesso', ['planejamento_id' => $planejamento->id]);
            } else {
                Log::info('Criando novo planejamento');
                $planejamento = Planejamento::create($planejamentoData);
                Log::info('Novo planejamento criado com sucesso', ['planejamento_id' => $planejamento->id]);
            }

            return $planejamento;

        } catch (\Exception $e) {
            Log::error('Erro ao criar/atualizar planejamento via wizard: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Processa campos de array vindos do JavaScript
     */
    private function processArrayField($field)
    {
        if (is_string($field)) {
            // Se for uma string JSON, decodificar
            $decoded = json_decode($field, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
            // Se não for JSON válido, retornar como array com um elemento
            return [$field];
        }
        
        if (is_array($field)) {
            return $field;
        }
        
        // Se for null ou outro tipo, retornar array vazio
        return [];
    }

    private function updatePlanejamentoSection($planejamento, $section, $data)
    {
        try {
            $updateData = [];

            switch ($section) {
                case 'informacoes_basicas':
                    $updateData = [
                        'titulo' => $data['titulo'] ?? $planejamento->titulo,
                        'modalidade' => $data['modalidade'] ?? $data['modalidade_ensino_id'] ?? $planejamento->modalidade,
                        'nivel_ensino' => $data['nivel_ensino'] ?? $data['nivel_ensino_id'] ?? $planejamento->nivel_ensino,
                        'tipo_professor' => $data['tipo_professor'] ?? $data['tipo_professor_id'] ?? $planejamento->tipo_professor
                    ];
                    break;

                case 'configuracao_escolar':
                    $updateData = [
                        'unidade_escolar' => $data['unidade_escolar'] ?? $planejamento->unidade_escolar,
                        'turno_id' => $data['turno_id'] ?? $planejamento->turno_id,
                        'escola_id' => $data['escola_id'] ?? $planejamento->escola_id
                    ];
                    break;

                case 'turma_disciplina':
                    $updateData = [
                        'turma_id' => $data['turma_id'] ?? $planejamento->turma_id,
                        'disciplina_id' => $data['disciplina_id'] ?? $planejamento->disciplina_id,
                        'professor_responsavel' => $data['professor_responsavel'] ?? $planejamento->professor_responsavel
                    ];
                    break;

                case 'periodo_carga':
                    $updateData = [
                        'data_inicio' => $data['data_inicio'] ?? $planejamento->data_inicio,
                        'numero_dias' => $data['numero_dias'] ?? $planejamento->numero_dias,
                        'carga_horaria_aula' => $data['carga_horaria_aula'] ?? $planejamento->carga_horaria_aula,
                        'aulas_por_semana' => $data['aulas_por_semana'] ?? $planejamento->aulas_por_semana
                    ];
                    break;

                case 'conteudo_pedagogico':
                    $updateData = [
                        'campos_experiencia' => $data['campos_experiencia'] ?? $planejamento->campos_experiencia,
                        'saberes_conhecimentos' => $data['saberes_conhecimentos'] ?? $planejamento->saberes_conhecimentos,
                        'metodologia' => $data['metodologia'] ?? $planejamento->metodologia,
                        'objetivo_geral' => $data['objetivo_geral'] ?? $planejamento->objetivo_geral,
                        'objetivos_especificos' => $data['objetivos_especificos'] ?? $planejamento->objetivos_especificos,
                        'competencias_bncc' => $data['competencias_bncc'] ?? $planejamento->competencias_bncc,
                        'habilidades_bncc' => $data['habilidades_bncc'] ?? $planejamento->habilidades_bncc
                    ];
                    break;

                case 'finalizacao':
                    $updateData = [
                        'observacoes' => $data['observacoes_finais'] ?? $planejamento->observacoes,
                        'recursos_necessarios' => $data['recursos_necessarios'] ?? $planejamento->recursos_necessarios,
                        'avaliacao_metodos' => $data['avaliacao_metodos'] ?? $planejamento->avaliacao_metodos
                    ];
                    break;
            }

            $planejamento->update($updateData);

        } catch (\Exception $e) {
            Log::error("Erro ao atualizar seção {$section}: " . $e->getMessage());
            throw $e;
        }
    }

    private function generateDefaultTitle($data)
    {
        $disciplinaId = $data['disciplina_id'] ?? null;
        $turmaId = $data['turma_id'] ?? null;

        $disciplina = $disciplinaId ? \App\Models\Disciplina::find($disciplinaId) : null;
        $turma = $turmaId ? \App\Models\Turma::find($turmaId) : null;

        $disciplinaNome = $disciplina ? $disciplina->nome : 'Disciplina';
        $turmaNome = $turma ? $turma->nome : 'Turma';

        return "Planejamento de {$disciplinaNome} - {$turmaNome}";
    }

    private function exportToPdf($planejamento)
    {
        // Carregar relações importantes para renderização
        $planejamento->load(['user', 'turma', 'disciplina', 'planejamentoDetalhado', 'diarios']);

        // Se DomPDF estiver disponível, usar para gerar PDF
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            try {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('planejamentos.view.preview', [
                    'planejamento' => $planejamento,
                ]);

                $fileName = 'planejamento_' . $planejamento->id . '.pdf';
                return $pdf->download($fileName);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Erro ao exportar PDF: ' . $e->getMessage());
                return response()->json(['error' => 'Falha ao gerar PDF.'], 500);
            }
        }

        // Fallback: baixar HTML como arquivo para abrir/imprimir
        $html = view('planejamentos.view.preview', compact('planejamento'))->render();
        $fileName = 'planejamento_' . $planejamento->id . '.html';
        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    private function exportToDocx($planejamento)
    {
        $planejamento->load(['user', 'turma', 'disciplina', 'planejamentoDetalhado', 'diarios']);

        // Se PhpWord estiver disponível, gerar DOCX
        if (class_exists(\PhpOffice\PhpWord\PhpWord::class)) {
            try {
                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $section = $phpWord->addSection();

                $section->addTitle($planejamento->titulo ?? 'Planejamento', 1);
                $section->addText('Professor: ' . ($planejamento->professor->name ?? $planejamento->criador->name ?? '—'));
                $section->addText('Turma: ' . ($planejamento->turma->nome ?? '—'));
                $section->addText('Disciplina: ' . ($planejamento->disciplina->nome ?? '—'));
                $section->addText('Período: ' . ($planejamento->data_inicio?->format('d/m/Y') ?? '—') . ' a ' . ($planejamento->data_fim?->format('d/m/Y') ?? '—'));

                if (!empty($planejamento->objetivo_geral)) {
                    $section->addTitle('Objetivo Geral', 2);
                    $section->addText($planejamento->objetivo_geral);
                }

                if (!empty($planejamento->objetivos_especificos)) {
                    $section->addTitle('Objetivos Específicos', 2);
                    foreach ((array) $planejamento->objetivos_especificos as $item) {
                        $section->addListItem($item);
                    }
                }

                if (!empty($planejamento->metodologia)) {
                    $section->addTitle('Metodologia', 2);
                    $section->addText(is_array($planejamento->metodologia) ? implode('; ', $planejamento->metodologia) : (string) $planejamento->metodologia);
                }

                if (!empty($planejamento->recursos_necessarios)) {
                    $section->addTitle('Recursos', 2);
                    foreach ((array) $planejamento->recursos_necessarios as $rec) {
                        $section->addListItem($rec);
                    }
                }

                if (!empty($planejamento->avaliacao_metodos)) {
                    $section->addTitle('Avaliação', 2);
                    foreach ((array) $planejamento->avaliacao_metodos as $av) {
                        $section->addListItem($av);
                    }
                }

                // Diário resumido
                if ($planejamento->diarios && $planejamento->diarios->count()) {
                    $section->addTitle('Cronograma Diário', 2);
                    foreach ($planejamento->diarios as $d) {
                        $section->addText(($d->data?->format('d/m/Y') ?? '—') . ' - ' . ($d->dia_semana ?? ''));
                        if (!empty($d->objetivos_especificos)) {
                            foreach ((array) $d->objetivos_especificos as $itm) {
                                $section->addListItem($itm);
                            }
                        }
                    }
                }

                $fileName = 'planejamento_' . $planejamento->id . '.docx';
                $tempFile = storage_path('app/temp_' . uniqid() . '.docx');
                $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
                $writer->save($tempFile);

                return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Erro ao exportar DOCX: ' . $e->getMessage());
                return response()->json(['error' => 'Falha ao gerar DOCX.'], 500);
            }
        }

        // Fallback: gerar HTML e servir como .doc para abrir no Word
        $html = view('planejamentos.view.preview', compact('planejamento'))->render();
        $fileName = 'planejamento_' . $planejamento->id . '.doc';
        return response($html, 200)
            ->header('Content-Type', 'application/msword; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    private function exportToExcel($planejamento)
    {
        $planejamento->load(['user', 'turma', 'disciplina', 'planejamentoDetalhado', 'diarios']);

        // Se Maatwebsite Excel estiver disponível, poderemos implementar classe export específica futuramente
        if (class_exists(\Maatwebsite\Excel\Facades\Excel::class) && class_exists(\Maatwebsite\Excel\Excel::class)) {
            try {
                // Fallback mesmo com Excel disponível: gerar CSV simples sem depender de uma classe export
                // Caso necessário, no futuro substituímos por Excel::download(new PlanejamentoExport(...), ...)
            } catch (\Throwable $e) {
                // Continua no fallback CSV abaixo
                \Illuminate\Support\Facades\Log::warning('Falha no caminho Excel::download, usando CSV: ' . $e->getMessage());
            }
        }

        // Gerar CSV compatível com Excel (UTF-8 com BOM)
        $csvRows = [];
        $esc = function($value) {
            $v = is_array($value) ? implode('; ', $value) : (string) $value;
            $v = str_replace('"', '""', $v);
            return '"' . str_replace('"', '""', $v) . '"';
        };

        // Cabeçalho principal
        $headers = [
            'Título', 'Modalidade', 'Turma', 'Disciplina', 'Professor', 'Data Início', 'Data Fim',
            'Objetivo Geral', 'Objetivos Específicos', 'Metodologia', 'Recursos', 'Avaliação', 'Observações'
        ];
        $csvRows[] = implode(',', array_map($esc, $headers));

        $csvRows[] = implode(',', [
            $esc($planejamento->titulo ?? ''),
            $esc($planejamento->modalidade_ensino ?? $planejamento->getModalidadeFormatadaAttribute() ?? ''),
            $esc($planejamento->turma->nome ?? ''),
            $esc($planejamento->disciplina->nome ?? ''),
            $esc($planejamento->professor->name ?? $planejamento->criador->name ?? ''),
            $esc(optional($planejamento->data_inicio)->format('d/m/Y')),
            $esc(optional($planejamento->data_fim)->format('d/m/Y')),
            $esc($planejamento->objetivo_geral ?? ''),
            $esc($planejamento->objetivos_especificos ?? []),
            $esc($planejamento->metodologia ?? ''),
            $esc($planejamento->recursos_necessarios ?? []),
            $esc($planejamento->avaliacao_metodos ?? []),
            $esc($planejamento->observacoes ?? '')
        ]);

        // Linha em branco e cabeçalho do diário
        $csvRows[] = '';
        $diarioHeader = [
            'Data', 'Dia da Semana', 'Planejado', 'Campos de Experiência', 'Saberes/Conhecimentos', 'Objetivos Específicos', 'Objetivos de Aprendizagem', 'Metodologia', 'Recursos Predefinidos', 'Recursos Personalizados'
        ];
        $csvRows[] = implode(',', array_map($esc, $diarioHeader));

        foreach ($planejamento->diarios as $d) {
            $csvRows[] = implode(',', [
                $esc(optional($d->data)->format('d/m/Y')),
                $esc($d->dia_semana ?? ''),
                $esc($d->planejado ? 'Sim' : 'Não'),
                $esc($d->campos_experiencia ?? []),
                $esc($d->saberes_conhecimentos ?? []),
                $esc($d->objetivos_especificos ?? []),
                $esc($d->objetivos_aprendizagem ?? []),
                $esc($d->metodologia ?? ''),
                $esc($d->recursos_predefinidos ?? []),
                $esc($d->recursos_personalizados ?? '')
            ]);
        }

        $bom = "\xEF\xBB\xBF"; // BOM para Excel reconhecer UTF-8
        $csvContent = $bom . implode("\r\n", $csvRows) . "\r\n";

        $fileName = 'planejamento_' . $planejamento->id . '.csv';
        return response($csvContent, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Exibe a interface de gestão de conflitos
     */
    public function conflitosIndex()
    {
        $this->authorize('viewAny', Planejamento::class);

        // Estatísticas de conflitos
        $stats = $this->getConflictStats();

        // Conflitos recentes (se houver)
        $conflitos = $this->detectarConflitosRecentes();

        return view('planejamentos.conflitos.index', compact('stats', 'conflitos'));
    }

    /**
     * Verifica todos os conflitos do sistema
     */
    public function verificarTodosConflitosEnhanced(Request $request)
    {
        $this->authorize('viewAny', Planejamento::class);

        try {
            // Buscar todos os planejamentos ativos
            $planejamentos = Planejamento::with(['turma', 'professor', 'disciplina', 'sala'])
                ->where('status', '!=', 'cancelado')
                ->get();

            $conflitos = $this->analisarConflitosCompletos($planejamentos);
            $stats = $this->calcularEstatisticasConflitos($conflitos);

            return response()->json([
                'success' => true,
                'conflitos' => $conflitos,
                'stats' => $stats,
                'total' => count($conflitos)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar conflitos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica conflitos com filtros personalizados
     */
    public function verificarConflitosPersonalizado(Request $request)
    {
        $this->authorize('viewAny', Planejamento::class);

        $request->validate([
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'modalidade' => 'nullable|string|in:presencial,ead,hibrido',
            'tipo_conflito' => 'nullable|string|in:horario,professor,sala,turma,recurso'
        ]);

        try {
            $query = Planejamento::with(['turma', 'professor', 'disciplina', 'sala'])
                ->where('status', '!=', 'cancelado');

            // Aplicar filtros
            if ($request->data_inicio) {
                $query->where('data_inicio', '>=', $request->data_inicio);
            }

            if ($request->data_fim) {
                $query->where('data_fim', '<=', $request->data_fim);
            }

            if ($request->modalidade) {
                $query->where('modalidade', $request->modalidade);
            }

            $planejamentos = $query->get();
            $conflitos = $this->analisarConflitosCompletos($planejamentos, $request->tipo_conflito);
            $stats = $this->calcularEstatisticasConflitos($conflitos);

            return response()->json([
                'success' => true,
                'conflitos' => $conflitos,
                'stats' => $stats,
                'total' => count($conflitos)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar conflitos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna a lista de conflitos via AJAX
     */
    public function listaConflitosAjax(Request $request)
    {
        $conflitos = $request->input('conflitos', []);

        return view('planejamentos.conflitos.lista', compact('conflitos'));
    }

    /**
     * Exibe detalhes de um conflito específico
     */
    public function detalhesConflito($id)
    {
        // Buscar conflito específico
        $conflito = $this->buscarConflitoPorId($id);

        if (!$conflito) {
            abort(404, 'Conflito não encontrado');
        }

        return view('planejamentos.conflitos.detalhes', compact('conflito'));
    }

    /**
     * Ignora um conflito específico
     */
    public function ignorarConflito(Request $request, $id)
    {
        $this->authorize('update', Planejamento::class);

        try {
            // Implementar lógica para ignorar conflito
            // Pode ser salvo em uma tabela de conflitos ignorados

            return response()->json([
                'success' => true,
                'message' => 'Conflito ignorado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao ignorar conflito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marca um conflito como resolvido
     */
    public function resolverConflito(Request $request, $id)
    {
        $this->authorize('update', Planejamento::class);

        try {
            // Implementar lógica para resolver conflito
            // Pode incluir atualizações nos planejamentos envolvidos

            return response()->json([
                'success' => true,
                'message' => 'Conflito resolvido com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao resolver conflito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporta um conflito específico
     */
    public function exportarConflito($id)
    {
        $conflito = $this->buscarConflitoPorId($id);

        if (!$conflito) {
            abort(404, 'Conflito não encontrado');
        }

        // Implementar exportação do conflito
        return response()->download($this->gerarRelatorioConflito($conflito));
    }

    /**
     * Gera relatório geral de conflitos
     */
    public function relatorioConflitos()
    {
        $this->authorize('viewAny', Planejamento::class);

        $planejamentos = Planejamento::with(['turma', 'professor', 'disciplina', 'sala'])
            ->where('status', '!=', 'cancelado')
            ->get();

        $conflitos = $this->analisarConflitosCompletos($planejamentos);
        $stats = $this->calcularEstatisticasConflitos($conflitos);

        // Implementar geração de relatório
        return $this->gerarRelatorioGeralConflitos($conflitos, $stats);
    }

    /**
     * Métodos privados para análise de conflitos
     */
    private function getConflictStats()
    {
        // Implementar cálculo de estatísticas
        return [
            'total_conflitos' => 0,
            'criticos' => 0,
            'resolvidos' => 0,
            'taxa_resolucao' => '0%'
        ];
    }

    private function detectarConflitosRecentes()
    {
        // Implementar detecção de conflitos recentes
        return [];
    }

    private function analisarConflitosCompletos($planejamentos, $tipoFiltro = null)
    {
        $conflitos = [];

        foreach ($planejamentos as $planejamento) {
            // Verificar conflitos de horário
            if (!$tipoFiltro || $tipoFiltro === 'horario') {
                $conflitosHorario = $this->verificarConflitosHorario($planejamento, $planejamentos);
                $conflitos = array_merge($conflitos, $conflitosHorario);
            }

            // Verificar conflitos de professor
            if (!$tipoFiltro || $tipoFiltro === 'professor') {
                $conflitosProfessor = $this->verificarConflitosProfessor($planejamento, $planejamentos);
                $conflitos = array_merge($conflitos, $conflitosProfessor);
            }

            // Verificar conflitos de sala
            if (!$tipoFiltro || $tipoFiltro === 'sala') {
                $conflitosSala = $this->verificarConflitosSala($planejamento, $planejamentos);
                $conflitos = array_merge($conflitos, $conflitosSala);
            }

            // Verificar conflitos de turma
            if (!$tipoFiltro || $tipoFiltro === 'turma') {
                $conflitosTurma = $this->verificarConflitosTurma($planejamento, $planejamentos);
                $conflitos = array_merge($conflitos, $conflitosTurma);
            }

            // Verificar conflitos de recurso
            if (!$tipoFiltro || $tipoFiltro === 'recurso') {
                $conflitosRecurso = $this->verificarConflitosRecurso($planejamento, $planejamentos);
                $conflitos = array_merge($conflitos, $conflitosRecurso);
            }
        }

        return $this->removerConflitosduplicados($conflitos);
    }

    private function verificarConflitosHorario($planejamento, $todosplanejamentos)
    {
        $conflitos = [];

        foreach ($todosplanejamentos as $outro) {
            if ($planejamento->id === $outro->id)
                continue;

            // Verificar sobreposição de horários
            if ($this->horariosSesobrepoe($planejamento, $outro)) {
                $conflitos[] = [
                    'id' => 'horario_' . $planejamento->id . '_' . $outro->id,
                    'tipo' => 'horario',
                    'severidade' => 'alta',
                    'titulo' => 'Conflito de Horário',
                    'descricao' => 'Dois planejamentos estão agendados para o mesmo horário',
                    'data_hora' => $planejamento->data_inicio,
                    'planejamentos' => [
                        [
                            'id' => $planejamento->id,
                            'titulo' => $planejamento->titulo,
                            'turma' => $planejamento->turma->nome ?? '',
                            'disciplina' => $planejamento->disciplina->nome ?? ''
                        ],
                        [
                            'id' => $outro->id,
                            'titulo' => $outro->titulo,
                            'turma' => $outro->turma->nome ?? '',
                            'disciplina' => $outro->disciplina->nome ?? ''
                        ]
                    ],
                    'sugestoes' => [
                        'Alterar o horário de um dos planejamentos',
                        'Verificar se as turmas são diferentes',
                        'Considerar modalidade híbrida ou EAD'
                    ],
                    'pode_ignorar' => false
                ];
            }
        }

        return $conflitos;
    }

    private function verificarConflitosProfessor($planejamento, $todosplanejamentos)
    {
        $conflitos = [];

        if (!$planejamento->professor_id)
            return $conflitos;

        foreach ($todosplanejamentos as $outro) {
            if ($planejamento->id === $outro->id)
                continue;
            if (!$outro->professor_id)
                continue;

            // Verificar se é o mesmo professor em horários sobrepostos
            if (
                $planejamento->professor_id === $outro->professor_id &&
                $this->horariosSesobrepoe($planejamento, $outro)
            ) {

                $conflitos[] = [
                    'id' => 'professor_' . $planejamento->id . '_' . $outro->id,
                    'tipo' => 'professor',
                    'severidade' => 'critica',
                    'titulo' => 'Conflito de Professor',
                    'descricao' => 'Professor agendado para duas aulas simultâneas',
                    'professor' => $planejamento->professor->nome ?? '',
                    'data_hora' => $planejamento->data_inicio,
                    'planejamentos' => [
                        [
                            'id' => $planejamento->id,
                            'titulo' => $planejamento->titulo,
                            'turma' => $planejamento->turma->nome ?? '',
                            'disciplina' => $planejamento->disciplina->nome ?? ''
                        ],
                        [
                            'id' => $outro->id,
                            'titulo' => $outro->titulo,
                            'turma' => $outro->turma->nome ?? '',
                            'disciplina' => $outro->disciplina->nome ?? ''
                        ]
                    ],
                    'sugestoes' => [
                        'Alterar o professor de uma das aulas',
                        'Reagendar um dos planejamentos',
                        'Verificar disponibilidade de professores substitutos'
                    ],
                    'pode_ignorar' => false
                ];
            }
        }

        return $conflitos;
    }

    private function verificarConflitosSala($planejamento, $todosplanejamentos)
    {
        $conflitos = [];

        if (!$planejamento->sala_id)
            return $conflitos;

        foreach ($todosplanejamentos as $outro) {
            if ($planejamento->id === $outro->id)
                continue;
            if (!$outro->sala_id)
                continue;

            // Verificar se é a mesma sala em horários sobrepostos
            if (
                $planejamento->sala_id === $outro->sala_id &&
                $this->horariosSesobrepoe($planejamento, $outro)
            ) {

                $conflitos[] = [
                    'id' => 'sala_' . $planejamento->id . '_' . $outro->id,
                    'tipo' => 'sala',
                    'severidade' => 'alta',
                    'titulo' => 'Conflito de Sala',
                    'descricao' => 'Sala agendada para duas aulas simultâneas',
                    'sala' => $planejamento->sala->nome ?? '',
                    'data_hora' => $planejamento->data_inicio,
                    'planejamentos' => [
                        [
                            'id' => $planejamento->id,
                            'titulo' => $planejamento->titulo,
                            'turma' => $planejamento->turma->nome ?? '',
                            'disciplina' => $planejamento->disciplina->nome ?? ''
                        ],
                        [
                            'id' => $outro->id,
                            'titulo' => $outro->titulo,
                            'turma' => $outro->turma->nome ?? '',
                            'disciplina' => $outro->disciplina->nome ?? ''
                        ]
                    ],
                    'sugestoes' => [
                        'Alterar a sala de uma das aulas',
                        'Verificar salas disponíveis no mesmo horário',
                        'Considerar aula em laboratório ou ambiente externo'
                    ],
                    'pode_ignorar' => false
                ];
            }
        }

        return $conflitos;
    }

    private function verificarConflitosTurma($planejamento, $todosplanejamentos)
    {
        $conflitos = [];

        if (!$planejamento->turma_id)
            return $conflitos;

        foreach ($todosplanejamentos as $outro) {
            if ($planejamento->id === $outro->id)
                continue;
            if (!$outro->turma_id)
                continue;

            // Verificar se é a mesma turma em horários sobrepostos
            if (
                $planejamento->turma_id === $outro->turma_id &&
                $this->horariosSesobrepoe($planejamento, $outro)
            ) {

                $conflitos[] = [
                    'id' => 'turma_' . $planejamento->id . '_' . $outro->id,
                    'tipo' => 'turma',
                    'severidade' => 'critica',
                    'titulo' => 'Conflito de Turma',
                    'descricao' => 'Turma agendada para duas aulas simultâneas',
                    'turma' => $planejamento->turma->nome ?? '',
                    'data_hora' => $planejamento->data_inicio,
                    'planejamentos' => [
                        [
                            'id' => $planejamento->id,
                            'titulo' => $planejamento->titulo,
                            'turma' => $planejamento->turma->nome ?? '',
                            'disciplina' => $planejamento->disciplina->nome ?? ''
                        ],
                        [
                            'id' => $outro->id,
                            'titulo' => $outro->titulo,
                            'turma' => $outro->turma->nome ?? '',
                            'disciplina' => $outro->disciplina->nome ?? ''
                        ]
                    ],
                    'sugestoes' => [
                        'Reagendar um dos planejamentos',
                        'Verificar se as disciplinas podem ser combinadas',
                        'Dividir a turma em grupos menores'
                    ],
                    'pode_ignorar' => false
                ];
            }
        }

        return $conflitos;
    }

    private function verificarConflitosRecurso($planejamento, $todosplanejamentos)
    {
        $conflitos = [];

        // Implementar verificação de recursos (laboratórios, equipamentos, etc.)
        // Esta é uma funcionalidade mais avançada que pode ser implementada posteriormente

        return $conflitos;
    }

    private function horariosSesobrepoe($planejamento1, $planejamento2)
    {
        $inicio1 = \Carbon\Carbon::parse($planejamento1->data_inicio);
        $fim1 = \Carbon\Carbon::parse($planejamento1->data_fim);
        $inicio2 = \Carbon\Carbon::parse($planejamento2->data_inicio);
        $fim2 = \Carbon\Carbon::parse($planejamento2->data_fim);

        return $inicio1->lt($fim2) && $inicio2->lt($fim1);
    }

    private function removerConflitosduplicados($conflitos)
    {
        $conflitosUnicos = [];
        $idsProcessados = [];

        foreach ($conflitos as $conflito) {
            $chave = $conflito['tipo'] . '_' . implode('_', array_column($conflito['planejamentos'], 'id'));

            if (!in_array($chave, $idsProcessados)) {
                $conflitosUnicos[] = $conflito;
                $idsProcessados[] = $chave;
            }
        }

        return $conflitosUnicos;
    }

    private function calcularEstatisticasConflitos($conflitos)
    {
        $total = count($conflitos);
        $criticos = count(array_filter($conflitos, fn($c) => $c['severidade'] === 'critica'));
        $resolvidos = 0; // Implementar lógica para conflitos resolvidos

        $taxaResolucao = $total > 0 ? round(($resolvidos / $total) * 100, 1) . '%' : '0%';

        return [
            'total_conflitos' => $total,
            'criticos' => $criticos,
            'resolvidos' => $resolvidos,
            'taxa_resolucao' => $taxaResolucao
        ];
    }

    private function buscarConflitoPorId($id)
    {
        // Implementar busca de conflito por ID
        // Pode ser necessário recriar o conflito baseado nos planejamentos
        return null;
    }

    private function gerarRelatorioConflito($conflito)
    {
        // Implementar geração de relatório individual
        return '';
    }

    private function gerarRelatorioGeralConflitos($conflitos, $stats)
    {
        // Implementar geração de relatório geral
        return response()->json(['conflitos' => $conflitos, 'stats' => $stats]);
    }

    /**
     * Filtra níveis de ensino por modalidade selecionada
     */
    public function getNiveisPorModalidade(Request $request, $modalidade)
    {
        $modalidadeId = $modalidade;
        if (!$modalidadeId) {
            return response()->json([
                'niveis_bncc' => [],
                'niveis_personalizados' => []
            ]);
        }

        // Buscar a modalidade selecionada
        $modalidade = \App\Models\ModalidadeEnsino::find($modalidadeId);

        if (!$modalidade) {
            return response()->json([
                'niveis_bncc' => [],
                'niveis_personalizados' => []
            ]);
        }

        // Obter escola do usuário logado
        $user = auth()->user();
        $escolaId = null;

        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }
        // Buscar níveis configurados para a escola (através da tabela escola_niveis_config)
        $niveisEscola = \DB::table('escola_niveis_config')
            ->where('escola_id', $escolaId)
            ->pluck('nivel_ensino_id')
            ->toArray();

        // Buscar todos os níveis configurados para a escola
        $todosNiveis = \App\Models\NivelEnsino::whereIn('id', $niveisEscola)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        // Buscar níveis que estão sendo utilizados por turmas ativas da escola
        // que possuem grade_aulas ativas
        $niveisEmUso = \DB::table('turmas')
            ->join('grade_aulas', 'turmas.id', '=', 'grade_aulas.turma_id')
            ->where('turmas.escola_id', $escolaId)
            ->where('turmas.ativo', true)
            ->where('grade_aulas.ativo', true)
            ->whereNotNull('turmas.nivel_ensino_id')
            ->distinct()
            ->pluck('turmas.nivel_ensino_id')
            ->toArray();

        // Filtrar níveis compatíveis com a modalidade E que estão em uso
        $niveisCompativeis = $todosNiveis->filter(function ($nivel) use ($modalidade, $niveisEmUso) {
            // Primeiro verificar se o nível está sendo usado por alguma turma
            if (!in_array($nivel->id, $niveisEmUso)) {
                return false;
            }

            // Se modalidades_compativeis for NULL ou vazio, NÃO considerar compatível
            if (is_null($nivel->modalidades_compativeis)) {
                return false;
            }

            // Se for string, tentar decodificar JSON
            if (is_string($nivel->modalidades_compativeis)) {
                if ($nivel->modalidades_compativeis === '' || $nivel->modalidades_compativeis === '[]') {
                    return false;
                }
                $modalidadesCompativeis = json_decode($nivel->modalidades_compativeis, true);
                if (!is_array($modalidadesCompativeis) || empty($modalidadesCompativeis)) {
                    return false;
                }
            } else if (is_array($nivel->modalidades_compativeis)) {
                // Se já for array, usar diretamente
                $modalidadesCompativeis = $nivel->modalidades_compativeis;
                if (empty($modalidadesCompativeis)) {
                    return false;
                }
            } else {
                return false; // Tipo desconhecido, NÃO considerar compatível
            }

            // Verificar se a modalidade está na lista de compatíveis (apenas por código)
            return in_array($modalidade->codigo, $modalidadesCompativeis);
        });

        // Separar níveis BNCC e personalizados
        $niveis_bncc = $niveisCompativeis->filter(function ($nivel) {
            return preg_match('/^(EI_|EF_|EM_|EJA_)/', $nivel->codigo);
        })->values();

        $niveis_personalizados = $niveisCompativeis->filter(function ($nivel) {
            return !preg_match('/^(EI_|EF_|EM_|EJA_)/', $nivel->codigo);
        })->values();

        return response()->json([
            'niveis_bncc' => $niveis_bncc,
            'niveis_personalizados' => $niveis_personalizados
        ]);
    }

    /**
     * Retorna turmas filtradas por modalidade, nível de ensino e turno
     * Para uso no wizard step 3
     */
    public function getTurmasFiltered(Request $request)
    {
        try {
            $modalidadeId = $request->input('modalidade_id');
            $nivelEnsinoId = $request->input('nivel_ensino_id');
            $turnoId = $request->input('turno_id');
            
            // Obter escola_id tratando SuperAdmin e Suporte
            $user = auth()->user();
            $escolaId = null;

            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $escolaId = session('escola_atual') ?: $user->escola_id;
            } else {
                $escolaId = $user->escola_id;
            }

            // Validar parâmetros obrigatórios
            if (!$modalidadeId || !$nivelEnsinoId || !$turnoId) {
                return response()->json(['error' => 'Parâmetros modalidade_id, nivel_ensino_id e turno_id são obrigatórios'], 400);
            }

            // Validar se temos um escola_id válido
            if (!$escolaId) {
                return response()->json(['error' => 'Não foi possível determinar a escola do usuário'], 400);
            }


            $turmaIds = \App\Models\GradeAula::select('turmas.id')
                ->join('turmas', 'grade_aulas.turma_id', '=', 'turmas.id')
                ->where('grade_aulas.ativo', true)
                ->where('turmas.ativo', true)
                ->where('turmas.escola_id', $escolaId)
                ->where('turmas.turno_id', $turnoId)
                ->where('turmas.nivel_ensino_id', $nivelEnsinoId)
                ->distinct()
                ->pluck('turmas.id');

            // Carregar turmas com relações necessárias (sem 'disciplinas', pois não há relação direta)
            $turmas = \App\Models\Turma::with([
                    'grupo', 
                    'nivelEnsino'
                ])
                ->whereIn('id', $turmaIds)
                ->get()
                ->map(function ($turma) {
                    // Buscar disciplinas ativas vinculadas via GradeAula para esta turma
                    $disciplinas = \App\Models\Disciplina::select('disciplinas.id', 'disciplinas.nome', 'disciplinas.area_conhecimento')
                        ->join('grade_aulas', 'grade_aulas.disciplina_id', '=', 'disciplinas.id')
                        ->where('grade_aulas.turma_id', $turma->id)
                        ->where('grade_aulas.ativo', true)
                        ->where('disciplinas.ativo', true)
                        ->distinct()
                        ->get()
                        ->map(function ($disciplina) {
                            return [
                                'id' => $disciplina->id,
                                'nome' => $disciplina->nome,
                                'area' => $disciplina->area_conhecimento ?? 'Geral',
                                // Carga horária pode ser obtida via DisciplinaNivelEnsino se necessário; mantendo null por ora
                                'carga_horaria' => null
                            ];
                        });

                    return [
                        'id' => $turma->id,
                        'nome' => $turma->nome,
                        'grupo' => $turma->grupo,
                        'nivel_ensino' => $turma->nivelEnsino,
                        'disciplinas' => $disciplinas
                    ];
                });

            return response()->json($turmas);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao buscar turmas filtradas: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar turmas.', 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Notifica coordenadores sobre a finalização de um planejamento para revisão
     */
    private function notificarCoordenadoresFinalizacao(Planejamento $planejamento)
    {
        try {
            $turma = $planejamento->turma;
            $sala = $turma ? $turma->sala : null;
            $escolaId = $planejamento->escola_id;

            $coordenadoresIds = collect();

            // 1. Prioridade: Coordenador vinculado à Turma
            if ($turma && $turma->coordenador_id) {
                $coordenadoresIds->push($turma->coordenador_id);
            }

            // 2. Se não houver coordenador específico, buscar TODOS coordenadores e administradores da escola
            if ($coordenadoresIds->isEmpty()) {
                // Coordenadores
                $coordenadoresEscola = User::where('escola_id', $escolaId)
                    ->whereHas('cargos', function ($query) {
                        $query->where('nome', 'like', '%Coordenador%')
                              ->orWhere('tipo_cargo', 'coordenador');
                    })
                    ->pluck('id');
                
                $coordenadoresIds = $coordenadoresIds->merge($coordenadoresEscola);

                // Administradores (apenas no fallback)
                $admins = User::where('escola_id', $escolaId)
                    ->whereHas('cargos', function ($query) {
                        $query->where('nome', 'like', '%Administrador%')
                              ->orWhere('tipo_cargo', 'admin');
                    })
                    ->pluck('id');
                
                $coordenadoresIds = $coordenadoresIds->merge($admins);
            }
            
            // Garantir unicidade
            $destinatarios = $coordenadoresIds->unique();

            foreach ($destinatarios as $userId) {
                // Evitar notificar o próprio autor se ele for coordenador/admin
                if ($userId == $planejamento->user_id) {
                    continue;
                }

                \App\Models\Notification::createForUser(
                    $userId,
                    'info',
                    'Planejamento para Revisão',
                    'O professor ' . ($planejamento->user ? $planejamento->user->name : 'N/A') . 
                    ' enviou um planejamento para revisão (' . ($turma ? $turma->nome : 'N/A') . ').',
                    [
                        'planejamento_id' => $planejamento->id,
                        'turma_id' => $turma ? $turma->id : null
                    ],
                    route('planejamentos.show', $planejamento),
                    'Revisar Planejamento'
                );
            }

        } catch (\Exception $e) {
            // Silenciar erro de notificação para não bloquear o fluxo principal
            Log::error('Erro ao notificar coordenadores: ' . $e->getMessage());
        }
    }
}

