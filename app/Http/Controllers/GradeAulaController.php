<?php

namespace App\Http\Controllers;

use App\Models\GradeAula;
use App\Models\Turma;
use App\Models\Disciplina;
use App\Models\Funcionario;
use App\Models\Sala;
use App\Models\TempoSlot;
use App\Models\Historico;
use App\Services\GradeAulaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GradeAulaController extends Controller
{
    protected $gradeAulaService;

    public function __construct(GradeAulaService $gradeAulaService)
    {
        $this->gradeAulaService = $gradeAulaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Determinar escola_id para filtros (seguindo padrão do sistema)
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        // Se não há escola definida, não mostrar nenhum dado
        if (!$escolaId) {
            $gradeAulas = new LengthAwarePaginator([], 0, 20, 1, [
                'path' => request()->url(),
                'pageName' => 'page',
            ]);
            $turmas = collect();
            $disciplinas = collect();
            $professores = collect();
            $salas = collect();
            $tempoSlots = collect();
            $totalAulas = 0;
            $professoresAtivos = 0;
            $turmasAtivas = 0;
            $salasEmUso = 0;
        } else {
            // Filtrar grade de aulas por escola através do relacionamento com turma
            $query = GradeAula::with(['turma.grupo.modalidadeEnsino', 'disciplina', 'funcionario', 'professor', 'sala', 'tempoSlot'])
                ->whereHas('turma', function($q) use ($escolaId) {
                    $q->where('escola_id', $escolaId);
                });

            // Filtros adicionais
            if ($request->filled('turma_id')) {
                $query->where('turma_id', $request->turma_id);
            }

            if ($request->filled('sala_id')) {
                $query->where('sala_id', $request->sala_id);
            }

            if ($request->filled('professor_id')) {
                $query->where('funcionario_id', $request->professor_id);
            }

            if ($request->filled('disciplina_id')) {
                $query->where('disciplina_id', $request->disciplina_id);
            }

            if ($request->filled('dia_semana')) {
                $query->where('dia_semana', $request->dia_semana);
            }

            if ($request->filled('tipo_aula')) {
                $query->where('tipo_aula', $request->tipo_aula);
            }

            if ($request->filled('tipo_periodo')) {
                $query->where('tipo_periodo', $request->tipo_periodo);
            }

            if ($request->filled('permite_substituicao')) {
                $query->where('permite_substituicao', $request->permite_substituicao == '1');
            }

            $gradeAulas = $query->ativas()
                ->orderBy('dia_semana')
                ->orderBy('tempo_slot_id')
                ->paginate(20)
                ->appends($request->query());

            // Dados para filtros e visualização - filtrados por escola
            $turmas = Turma::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('nome')
                ->get();

            // Lista paginada de turmas para exibição (evita paginação por aula na list-view)
            $turmasPaginadas = Turma::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->when($request->filled('turma_id'), function($q) use ($request) {
                    $q->where('id', $request->turma_id);
                })
                ->orderBy('nome')
                ->paginate(15)
                ->appends($request->query());
                
            // Trazer apenas disciplinas únicas por nome (evita duplicar por nível)
            $disciplinas = Disciplina::where('ativo', true)
                ->orderBy('nome')
                ->get()
                ->unique('nome')
                ->values();
                
            $professores = Funcionario::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->where(function($query) {
                    $query->where('cargo', 'like', '%professor%')
                          ->orWhere('cargo', 'like', '%Professor%');
                })
                ->orderBy('nome')
                ->get();
                
            $salas = Sala::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('nome')
                ->get();
                
            $tempoSlots = TempoSlot::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('hora_inicio')
                ->get();

            // Calcular estatísticas para os cards - filtradas por escola
            $totalAulas = GradeAula::ativas()
                ->whereHas('turma', function($q) use ($escolaId) {
                    $q->where('escola_id', $escolaId);
                })
                ->count();
                
            $professoresAtivos = Funcionario::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->where(function($query) {
                    $query->where('cargo', 'like', '%professor%')
                          ->orWhere('cargo', 'like', '%Professor%');
                })
                ->count();
                
            $turmasAtivas = Turma::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->count();
                
            $salasEmUso = GradeAula::ativas()
                ->whereHas('turma', function($q) use ($escolaId) {
                    $q->where('escola_id', $escolaId);
                })
                ->distinct('sala_id')
                ->whereNotNull('sala_id')
                ->count();
        }

        if ($request->expectsJson()) {
            return response()->json($gradeAulas);
        }

        return view('grade-aulas.index', compact(
            'gradeAulas', 
            'turmas', 
            'disciplinas', 
            'professores', 
            'salas', 
            'tempoSlots',
            'turmasPaginadas',
            'totalAulas',
            'professoresAtivos',
            'turmasAtivas',
            'salasEmUso'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Determinar escola_id para filtros (seguindo padrão do sistema)
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        // Se não há escola definida, retornar dados vazios
        if (!$escolaId) {
            $turmas = collect();
            $disciplinas = collect();
            $professores = collect();
            $salas = collect();
            $tempoSlots = collect();
        } else {
            // Filtrar dados por escola
            $turmas = Turma::with(['grupo.modalidadeEnsino'])
                ->where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('nome')
                ->get();
                
            $disciplinas = Disciplina::where('ativo', true)
                ->orderBy('nome')
                ->get();
                
            $professores = Funcionario::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->where(function($query) {
                    $query->where('cargo', 'like', '%professor%')
                          ->orWhere('cargo', 'like', '%Professor%');
                })
                ->orderBy('nome')
                ->get();
                
            $salas = Sala::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('nome')
                ->get();
                
            $tempoSlots = TempoSlot::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('hora_inicio')
                ->get()
                ->unique(function ($slot) {
                    return $slot->hora_inicio . '|' . $slot->hora_fim;
                })
                ->values();
        }

        return view('grade-aulas.create', compact('turmas', 'disciplinas', 'professores', 'salas', 'tempoSlots'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // Determinar escola_id para validações (seguindo padrão do sistema)
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        // Validações básicas primeiro (sem dia da semana)
        $request->validate([
            'turma_id' => 'required|integer',
            'disciplina_id' => 'required|integer',
            'funcionario_id' => 'required|integer',
            'sala_id' => 'required|integer',
            'tempo_slot_id' => 'required|integer',
            'tipo_aula' => 'required|in:anual,periodo',
            'tipo_periodo' => 'required_if:tipo_aula,periodo|nullable|in:curso_intensivo,substituicao,reforco,recuperacao,outro',
            'data_inicio' => 'required_if:tipo_aula,periodo|nullable|date',
            'data_fim' => 'required_if:tipo_aula,periodo|nullable|date|after_or_equal:data_inicio',
            'observacoes' => 'nullable|string|max:500',
            'permite_substituicao' => 'boolean',
        ]);

        // Validar dia(s) da semana: aceitar único (dia_semana) ou múltiplos (dias_semana[])
        if (is_array($request->input('dias_semana')) && count($request->input('dias_semana')) > 0) {
            $request->validate([
                'dias_semana' => 'array|min:1',
                'dias_semana.*' => 'in:segunda,terca,quarta,quinta,sexta,sabado',
            ]);
        } else {
            $request->validate([
                'dia_semana' => 'required|in:segunda,terca,quarta,quinta,sexta,sabado',
            ]);
        }

        if (!$escolaId) {
            $errorMessage = 'Escola não definida. Entre em contato com o administrador.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            return back()->withInput()->with('error', $errorMessage);
        }

        // Validar se turma pertence à escola atual
        $turma = Turma::find($request->turma_id);
        if (!$turma || $turma->escola_id != $escolaId) {
            $errorMessage = 'A turma selecionada não pertence à escola atual.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            return back()->withInput()->with('error', $errorMessage);
        }

        // Validar se disciplina existe (disciplinas agora são globais)
        $disciplina = Disciplina::find($request->disciplina_id);
        if (!$disciplina) {
            $errorMessage = 'A disciplina selecionada não foi encontrada.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            return back()->withInput()->with('error', $errorMessage);
        }

        // Validar se professor pertence à escola atual
        $funcionario = Funcionario::find($request->funcionario_id);
        if (!$funcionario || $funcionario->escola_id != $escolaId) {
            $errorMessage = 'O professor selecionado não pertence à escola atual.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            return back()->withInput()->with('error', $errorMessage);
        }

        // Validar se sala pertence à escola atual
        $sala = Sala::find($request->sala_id);
        if (!$sala || $sala->escola_id != $escolaId) {
            $errorMessage = 'A sala selecionada não pertence à escola atual.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            return back()->withInput()->with('error', $errorMessage);
        }

        // Validar se tempo_slot pertence à escola atual
        $tempoSlot = TempoSlot::find($request->tempo_slot_id);
        if (!$tempoSlot || $tempoSlot->escola_id != $escolaId) {
            $errorMessage = 'O horário selecionado não pertence à escola atual.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 422);
            }
            return back()->withInput()->with('error', $errorMessage);
        }

        // Validações de existência adicionais (após validar que pertencem à escola)
        $request->validate([
            'turma_id' => 'exists:turmas,id',
            'disciplina_id' => 'exists:disciplinas,id',
            'funcionario_id' => 'exists:funcionarios,id',
            'sala_id' => 'exists:salas,id',
            'tempo_slot_id' => 'exists:tempo_slots,id',
        ]);

        try {
            // Criar uma ou várias aulas conforme seleção de dias
            if (is_array($request->input('dias_semana')) && count($request->input('dias_semana')) > 0) {
                $criados = DB::transaction(function () use ($request) {
                    $registros = [];
                    foreach (array_unique($request->input('dias_semana')) as $dia) {
                        $dados = $request->all();
                        $dados['dia_semana'] = $dia;
                        $registros[] = $this->gradeAulaService->criarGradeAula($dados);
                    }
                    return $registros;
                });

                // Registrar histórico para cada aula criada
                foreach ($criados as $ga) {
                    try {
                        Historico::registrar(
                            'criado',
                            'GradeAula',
                            $ga->id,
                            null,
                            $ga->toArray(),
                            'Aula criada'
                        );
                    } catch (\Exception $e) {
                        // Evitar quebra do fluxo caso falhe o histórico
                    }
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Aulas criadas com sucesso!',
                        'count' => count($criados),
                        'data' => collect($criados)->map(function ($ga) {
                            return $ga->load(['turma', 'disciplina', 'funcionario', 'sala', 'tempoSlot']);
                        }),
                    ], 201);
                }

                return redirect()->route('grade-aulas.index')
                    ->with('success', 'Aulas criadas com sucesso (' . count($criados) . ').');
            }

            // Caso contrário, criar apenas uma aula
            $gradeAula = $this->gradeAulaService->criarGradeAula($request->all());

            // Registrar histórico de criação
            try {
                Historico::registrar(
                    'criado',
                    'GradeAula',
                    $gradeAula->id,
                    null,
                    $gradeAula->toArray(),
                    'Aula criada'
                );
            } catch (\Exception $e) {
                // Evitar quebra do fluxo caso falhe o histórico
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aula criada com sucesso!',
                    'data' => $gradeAula->load(['turma', 'disciplina', 'funcionario', 'sala', 'tempoSlot'])
                ], 201);
            }

            return redirect()->route('grade-aulas.index')
                ->with('success', 'Aula criada com sucesso!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(GradeAula $gradeAula)
    {
        $gradeAula->load(['turma.grupo.modalidadeEnsino', 'disciplina', 'funcionario', 'sala', 'tempoSlot']);

        if (request()->expectsJson()) {
            // Adicionar horários formatados ao tempo_slot
            $gradeAulaArray = $gradeAula->toArray();
            if (isset($gradeAulaArray['tempo_slot'])) {
                $gradeAulaArray['tempo_slot']['hora_inicio_formatada'] = $gradeAula->tempoSlot->hora_inicio_formatada;
                $gradeAulaArray['tempo_slot']['hora_fim_formatada'] = $gradeAula->tempoSlot->hora_fim_formatada;
            }
            
            return response()->json($gradeAulaArray);
        }

        return view('grade-aulas.show', compact('gradeAula'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GradeAula $gradeAula)
    {
        // Determinar escola_id para filtros (seguindo padrão do sistema)
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        // Se não há escola definida, retornar dados vazios
        if (!$escolaId) {
            $turmas = collect();
            $disciplinas = collect();
            $professores = collect();
            $salas = collect();
            $tempoSlots = collect();
        } else {
            // Filtrar dados por escola
            $turmas = Turma::with(['grupo.modalidadeEnsino'])
                ->where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('nome')
                ->get();
                
            $disciplinas = Disciplina::where('ativo', true)
                ->orderBy('nome')
                ->get();
                
            $professores = Funcionario::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->where(function($query) {
                    $query->where('cargo', 'like', '%professor%')
                          ->orWhere('cargo', 'like', '%Professor%');
                })
                ->orderBy('nome')
                ->get();
                
            $salas = Sala::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('nome')
                ->get();
                
            $tempoSlots = TempoSlot::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('hora_inicio')
                ->get();
        }

        return view('grade-aulas.edit', compact('gradeAula', 'turmas', 'disciplinas', 'professores', 'salas', 'tempoSlots'));
    }

    /**
     * Show the form for editing the specified resource in modal.
     */
    public function editModal(GradeAula $gradeAula)
    {
        // Determinar escola_id para filtros (seguindo padrão do sistema)
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        // Se não há escola definida, retornar dados vazios
        if (!$escolaId) {
            $turmas = collect();
            $disciplinas = collect();
            $professores = collect();
            $salas = collect();
            $tempoSlots = collect();
        } else {
            // Filtrar dados por escola
            $turmas = Turma::with(['grupo.modalidadeEnsino'])
                ->where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('nome')
                ->get();
                
            $disciplinas = Disciplina::where('ativo', true)
                ->orderBy('nome')
                ->get();
                
            $professores = Funcionario::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->where(function($query) {
                    $query->where('cargo', 'like', '%professor%')
                          ->orWhere('cargo', 'like', '%Professor%');
                })
                ->orderBy('nome')
                ->get();
                
            $salas = Sala::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('nome')
                ->get();
                
            $tempoSlots = TempoSlot::where('ativo', true)
                ->where('escola_id', $escolaId)
                ->orderBy('hora_inicio')
                ->get();
        }

        return view('grade-aulas.partials.edit-form', compact('gradeAula', 'turmas', 'disciplinas', 'professores', 'salas', 'tempoSlots'));
    }

    /**
     * Retorna todas as aulas ativas de uma turma (sem paginação) em JSON.
     */
    public function listarPorTurma(Request $request)
    {
        $request->validate([
            'turma_id' => 'required|exists:turmas,id'
        ]);

        // Determinar escola_id para filtros (seguindo padrão do sistema)
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        // Garantir que a turma pertence à escola atual
        $turma = Turma::find($request->turma_id);
        if (!$turma || $turma->escola_id != $escolaId) {
            return response()->json([
                'success' => false,
                'message' => 'Turma não encontrada ou não pertence à escola atual.'
            ], 404);
        }

        $aulas = GradeAula::with(['disciplina', 'professor', 'sala', 'tempoSlot'])
            ->where('turma_id', $turma->id)
            ->ativas()
            ->orderBy('dia_semana')
            ->orderBy('tempo_slot_id')
            ->get();

        return response()->json([
            'success' => true,
            'turma' => [
                'id' => $turma->id,
                'nome' => $turma->nome,
            ],
            'aulas' => $aulas,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GradeAula $gradeAula)
    {
        $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'disciplina_id' => 'required|exists:disciplinas,id',
            'funcionario_id' => 'required|exists:funcionarios,id',
            'sala_id' => 'required|exists:salas,id',
            'tempo_slot_id' => 'required|exists:tempo_slots,id',
            'dia_semana' => 'required|in:segunda,terca,quarta,quinta,sexta,sabado',
            'tipo_aula' => 'required|in:anual,periodo',
            'tipo_periodo' => 'required_if:tipo_aula,periodo|nullable|in:curso_intensivo,substituicao,reforco,recuperacao,outro',
            'data_inicio' => 'required_if:tipo_aula,periodo|nullable|date',
            'data_fim' => 'required_if:tipo_aula,periodo|nullable|date|after_or_equal:data_inicio',
            'observacoes' => 'nullable|string|max:500',
            'ativo' => 'boolean',
            'permite_substituicao' => 'boolean',
        ]);

        try {
            // Capturar dados antigos para histórico
            $dadosAntigos = $gradeAula->toArray();
            $gradeAulaAtualizada = $this->gradeAulaService->atualizarGradeAula($gradeAula->id, $request->all());

            // Registrar histórico de atualização
            try {
                Historico::registrar(
                    'atualizado',
                    'GradeAula',
                    $gradeAula->id,
                    $dadosAntigos,
                    $gradeAulaAtualizada->toArray(),
                    'Aula atualizada'
                );
            } catch (\Exception $e) {
                // Evitar quebra do fluxo caso falhe o histórico
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aula atualizada com sucesso!',
                    'data' => $gradeAulaAtualizada->load(['turma', 'disciplina', 'funcionario', 'sala', 'tempoSlot'])
                ]);
            }

            return redirect()->route('grade-aulas.index')
                ->with('success', 'Aula atualizada com sucesso!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GradeAula $gradeAula)
    {
        try {
            // Capturar dados antigos para histórico
            $dadosAntigos = $gradeAula->toArray();
            $gradeAula->update(['ativo' => false]);

            // Registrar histórico de remoção (desativação)
            try {
                Historico::registrar(
                    'excluido',
                    'GradeAula',
                    $gradeAula->id,
                    $dadosAntigos,
                    $gradeAula->fresh()->toArray(),
                    'Aula removida'
                );
            } catch (\Exception $e) {
                // Evitar quebra do fluxo caso falhe o histórico
            }

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aula removida com sucesso!'
                ]);
            }

            return redirect()->route('grade-aulas.index')
                ->with('success', 'Aula removida com sucesso!');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao remover aula: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Erro ao remover aula: ' . $e->getMessage());
        }
    }

    /**
     * Retorna salas disponíveis para um horário específico
     */
    public function salasDisponiveis(Request $request): JsonResponse
    {
        $request->validate([
            'tempo_slot_id' => 'required|exists:tempo_slots,id',
            'dia_semana' => 'required|in:segunda,terca,quarta,quinta,sexta,sabado',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'grade_aula_id' => 'nullable|exists:grade_aulas,id',
        ]);

        $salas = $this->gradeAulaService->getSalasDisponiveis(
            $request->tempo_slot_id,
            $request->dia_semana,
            $request->data_inicio ? Carbon::parse($request->data_inicio) : null,
            $request->data_fim ? Carbon::parse($request->data_fim) : null,
            $request->grade_aula_id
        );

        return response()->json($salas);
    }

    /**
     * Retorna professores disponíveis para um horário específico
     */
    public function professoresDisponiveis(Request $request): JsonResponse
    {
        $request->validate([
            'tempo_slot_id' => 'required|exists:tempo_slots,id',
            'dia_semana' => 'required|in:segunda,terca,quarta,quinta,sexta,sabado',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'grade_aula_id' => 'nullable|exists:grade_aulas,id',
        ]);

        $professores = $this->gradeAulaService->getProfessoresDisponiveis(
            $request->tempo_slot_id,
            $request->dia_semana,
            $request->data_inicio ? Carbon::parse($request->data_inicio) : null,
            $request->data_fim ? Carbon::parse($request->data_fim) : null,
            $request->grade_aula_id
        );

        return response()->json($professores);
    }

    /**
     * Retorna a grade de uma turma específica
     */
    public function gradeTurma(Turma $turma): JsonResponse
    {
        $grade = $this->gradeAulaService->getGradeTurma($turma->id);
        return response()->json($grade);
    }

    /**
     * Retorna a ocupação de uma sala específica
     */
    public function ocupacaoSala(Sala $sala, Request $request): JsonResponse
    {
        $request->validate([
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);

        $ocupacao = $this->gradeAulaService->getOcupacaoSala(
            $sala->id,
            $request->data_inicio ? Carbon::parse($request->data_inicio) : null,
            $request->data_fim ? Carbon::parse($request->data_fim) : null
        );

        return response()->json($ocupacao);
    }

    /**
     * Verifica conflitos em tempo real para criação/edição de aulas
     */
    public function verificarConflitos(Request $request): JsonResponse
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'sala_id' => 'required|exists:salas,id',
            'dia_semana' => 'required|in:segunda,terca,quarta,quinta,sexta,sabado',
            'tempo_slot_id' => 'required|exists:tempo_slots,id',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'grade_aula_id' => 'nullable|exists:grade_aulas,id',
        ]);

        $conflitos = [];
        $temConflitos = false;

        // Converter datas para objetos Carbon quando necessário
        $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : null;
        $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : null;

        // Verificar conflito de professor
        $professorDisponivel = $this->gradeAulaService->professorEstaDisponivel(
            $request->funcionario_id,
            $request->tempo_slot_id,
            $request->dia_semana,
            $dataInicio,
            $dataFim,
            $request->grade_aula_id
        );

        if (!$professorDisponivel) {
            $conflitos[] = [
                'tipo' => 'professor',
                'severidade' => 'error',
                'mensagem' => 'Professor já possui aula agendada neste horário.'
            ];
            $temConflitos = true;
        }

        // Verificar conflito de sala
        $salaDisponivel = $this->gradeAulaService->salaEstaDisponivel(
            $request->sala_id,
            $request->tempo_slot_id,
            $request->dia_semana,
            $dataInicio,
            $dataFim,
            $request->grade_aula_id
        );

        if (!$salaDisponivel) {
            $conflitos[] = [
                'tipo' => 'sala',
                'severidade' => 'error',
                'mensagem' => 'Sala já está ocupada neste horário.'
            ];
            $temConflitos = true;
        }

        // Verificar regras adicionais
        $regrasAdicionais = $this->verificarRegrasAdicionais($request);
        $conflitos = array_merge($conflitos, $regrasAdicionais);

        return response()->json([
            'success' => true,
            'conflitos' => $conflitos,
            'tem_conflitos' => $temConflitos
        ]);
    }

    /**
     * Verifica regras adicionais de negócio
     */
    private function verificarRegrasAdicionais(Request $request): array
    {
        $avisos = [];

        // Verificar se é fim de semana (sábado)
        if ($request->dia_semana === 'sabado') {
            $avisos[] = [
                'tipo' => 'fim_semana',
                'mensagem' => 'Aula agendada para sábado',
                'severidade' => 'warning'
            ];
        }

        // Verificar se há muitas aulas do mesmo professor no dia
        $aulasNoDia = GradeAula::where('funcionario_id', $request->funcionario_id)
            ->where('dia_semana', $request->dia_semana)
            ->where('ativo', true)
            ->when($request->grade_aula_id, function($q) use ($request) {
                $q->where('id', '!=', $request->grade_aula_id);
            })
            ->count();

        if ($aulasNoDia >= 6) {
            $avisos[] = [
                'tipo' => 'sobrecarga_professor',
                'mensagem' => 'Professor já possui ' . $aulasNoDia . ' aulas neste dia',
                'severidade' => 'warning'
            ];
        }

        return $avisos;
    }

    /**
     * Obter sugestões de horários disponíveis
     */
    public function obterSugestoesHorarios(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'sala_id' => 'required|exists:salas,id',
            'dia_semana' => 'required|in:segunda,terca,quarta,quinta,sexta,sabado',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'turma_id' => 'nullable|exists:turmas,id'
        ]);

        $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : null;
        $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : null;

        $sugestoes = $this->gradeAulaService->obterSugestoesHorarios(
            $request->funcionario_id,
            $request->sala_id,
            $request->dia_semana,
            $dataInicio,
            $dataFim,
            $request->turma_id
        );

        return response()->json([
            'success' => true,
            'sugestoes' => $sugestoes
        ]);
    }

    /**
     * Obter sugestões de salas disponíveis para um horário específico
     */
    public function obterSugestoesSalas(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'tempo_slot_id' => 'required|exists:tempo_slots,id',
            'dia_semana' => 'required|in:segunda,terca,quarta,quinta,sexta,sabado',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio'
        ]);

        $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : null;
        $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : null;

        $sugestoes = $this->gradeAulaService->obterSugestoesSalas(
            $request->funcionario_id,
            $request->tempo_slot_id,
            $request->dia_semana,
            $dataInicio,
            $dataFim
        );

        return response()->json([
            'success' => true,
            'sugestoes' => $sugestoes
        ]);
    }

    /**
     * Obter disciplinas filtradas por nível de ensino da turma
     */
    public function obterDisciplinasPorTurma(Request $request)
    {
        $request->validate([
            'turma_id' => 'required|exists:turmas,id'
        ]);

        // Determinar escola_id para filtros (seguindo padrão do sistema)
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        // Buscar a turma e seu nível de ensino
        $turma = Turma::with('nivelEnsino')->find($request->turma_id);
        
        if (!$turma || $turma->escola_id != $escolaId) {
            return response()->json([
                'success' => false,
                'message' => 'Turma não encontrada ou não pertence à escola atual.'
            ], 404);
        }

        // Buscar disciplinas relacionadas ao nível de ensino da turma (deduplicadas por nome)
        $disciplinas = Disciplina::whereHas('niveisEnsino', function($query) use ($turma) {
                $query->where('nivel_ensino_id', $turma->nivel_ensino_id);
            })
            ->where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get(['id', 'nome', 'codigo', 'cor_hex'])
            ->unique('nome')
            ->values();

        return response()->json([
            'success' => true,
            'disciplinas' => $disciplinas,
            'nivel_ensino' => [
                'id' => $turma->nivelEnsino->id,
                'nome' => $turma->nivelEnsino->nome,
                'codigo' => $turma->nivelEnsino->codigo
            ]
        ]);
    }

    /**
     * Obter professores alternativos para um horário/sala específicos
     */
    public function obterProfessoresAlternativos(Request $request)
    {
        $request->validate([
            'sala_id' => 'required|exists:salas,id',
            'tempo_slot_id' => 'required|exists:tempo_slots,id',
            'dia_semana' => 'required|in:segunda,terca,quarta,quinta,sexta,sabado',
            'disciplina_id' => 'required|exists:disciplinas,id',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio'
        ]);

        $dataInicio = $request->data_inicio ? Carbon::parse($request->data_inicio) : null;
        $dataFim = $request->data_fim ? Carbon::parse($request->data_fim) : null;

        $professoresAlternativos = $this->gradeAulaService->obterProfessoresAlternativos(
            $request->sala_id,
            $request->tempo_slot_id,
            $request->dia_semana,
            $request->disciplina_id,
            $dataInicio,
            $dataFim
        );

        return response()->json([
            'success' => true,
            'professores_alternativos' => $professoresAlternativos
        ]);
    }
    
    /**
     * Obter tempo slots filtrados pelo turno da turma
     */
    public function obterTempoSlotsPorTurma(Request $request)
    {
        $request->validate([
            'turma_id' => 'required|exists:turmas,id',
        ]);

        // Determinar escola_id para filtros (seguindo padrão do sistema)
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        $turma = Turma::findOrFail($request->turma_id);
        $turnoId = $turma->turno_id;

        // Buscar slots do turno da turma e da escola atual
        $slots = TempoSlot::where('ativo', true)
            ->when($escolaId, function ($q) use ($escolaId) {
                $q->where('escola_id', $escolaId);
            })
            ->where('turno_id', $turnoId)
            ->orderBy('hora_inicio')
            ->get()
            // Deduplicar por janela de tempo (hora_inicio + hora_fim)
            ->unique(function ($slot) {
                return $slot->hora_inicio . '|' . $slot->hora_fim;
            })
            ->values();

        return response()->json([
            'success' => true,
            'tempo_slots' => $slots->map(function ($s) {
                return [
                    'id' => $s->id,
                    'hora_inicio' => $s->hora_inicio,
                    'hora_fim' => $s->hora_fim,
                ];
            })
        ]);
    }
}
