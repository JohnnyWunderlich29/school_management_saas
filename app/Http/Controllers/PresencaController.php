<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presenca;
use App\Models\Aluno;
use App\Models\Funcionario;
use App\Models\Historico;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class PresencaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Determinar a data para filtro
        if ($request->has('data_inicio') && $request->has('data_fim')) {
            $dataInicio = $request->data_inicio;
            $dataFim = $request->data_fim;
        } else if ($request->has('data')) {
            $dataInicio = $dataFim = $request->data;
        } else {
            // Por padrÃ£o, mostrar presenÃ§as do dia atual
            $dataInicio = $dataFim = Carbon::today()->format('Y-m-d');
        }
        
        // Verificar se o usuÃ¡rio Ã© superadmin ou suporte
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->hasRole('suporte');
        
        // Determinar a escola atual
        $escolaId = $isSuperAdminOrSupport && session('escola_atual') 
            ? session('escola_atual') 
            : $user->escola_id;
            
        // Base de salas (sem join com alunos): estatÃ­sticas serÃ£o calculadas via GradeAula->turma
        $salasQuery = \App\Models\Sala::withoutGlobalScope('escola')
            ->select('salas.*');
            
        // Aplicar filtro de escola apenas se nÃ£o for superadmin ou suporte, ou se tiver uma escola selecionada
        if (!$isSuperAdminOrSupport || $escolaId) {
            $salasQuery->where('salas.escola_id', $escolaId);
        }
        
        $salasQuery->where('salas.ativo', true);
        
        // Aplicar filtros de sala se necessÃ¡rio
        if ($request->has('sala_id')) {
            $salasQuery->where('salas.id', $request->sala_id);
        }

        // OrdenaÃ§Ã£o dinÃ¢mica (seguindo padrÃ£o de /responsaveis e demais telas)
        $allowedSortsDb = ['codigo', 'nome'];
        $allowedSortsComputed = ['total_alunos', 'presencas_registradas', 'presentes', 'ausentes', 'nao_registrados'];
        $sort = $request->get('sort');
        $direction = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = 15;
        $page = (int) $request->get('page', 1);

        // Mapear dias do perÃ­odo (limitado para performance, seguindo padrÃ£o de outras telas)
        $diaSemanaMap = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terca',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sabado',
        ];

        $diasPeriodo = Carbon::parse($dataFim)->diffInDays(Carbon::parse($dataInicio)) + 1;
        $datasPeriodo = [];
        $dataAtual = Carbon::parse($dataInicio);
        $dataFinal = Carbon::parse($dataFim);
        $maxDias = 4; // mantÃ©m a limitaÃ§Ã£o usada em outras telas para evitar consultas pesadas
        $cont = 0;
        while ($dataAtual <= $dataFinal && $cont < $maxDias) {
            $datasPeriodo[] = $dataAtual->copy();
            $dataAtual->addDay();
            $cont++;
        }

        // Transformer para calcular estatÃ­sticas por sala via turmas agendadas (GradeAula)
        $calcEstatisticasSala = function($sala) use ($datasPeriodo, $diaSemanaMap, $dataInicio, $dataFim, $diasPeriodo, $escolaId) {
            // Coletar turmas agendadas na sala no perÃ­odo
            $turmaIds = collect();
            foreach ($datasPeriodo as $dataCarbon) {
                $dow = (int) $dataCarbon->dayOfWeek;
                $diaSlug = $diaSemanaMap[$dow] ?? null;
                if (!$diaSlug || $diaSlug === 'domingo') {
                    continue; // ignorar domingos
                }

                $gradesDia = \App\Models\GradeAula::where('sala_id', $sala->id)
                    ->where('ativo', true)
                    ->where('dia_semana', $diaSlug)
                    ->when($escolaId, function($q) use ($escolaId) {
                        $q->whereHas('turma', function($qt) use ($escolaId) {
                            $qt->where('escola_id', $escolaId);
                        });
                    })
                    ->get();

                if ($gradesDia->count() > 0) {
                    $turmaIds = $turmaIds->merge($gradesDia->pluck('turma_id')->filter());
                }
            }

            $turmaIds = $turmaIds->filter()->unique()->values();

            // Alunos ativos das turmas encontradas
            $alunoIds = collect();
            if ($turmaIds->count() > 0) {
                $alunoIds = \App\Models\Aluno::whereIn('turma_id', $turmaIds)
                    ->where('ativo', true)
                    ->pluck('id');
            }

            $totalAlunos = $alunoIds->unique()->count();

            // PresenÃ§as no perÃ­odo para esses alunos
            $presencasQuery = \App\Models\Presenca::whereIn('aluno_id', $alunoIds);
            if ($dataInicio === $dataFim) {
                $presencasQuery->whereDate('data', $dataInicio);
            } else {
                $presencasQuery->whereBetween('data', [$dataInicio, $dataFim]);
            }
            $presencas = $presencasQuery->get();

            $presencasRegistradas = $presencas->count();
            $presentes = $presencas->where('presente', true)->count();
            $ausentes = $presencas->where('presente', false)->count();

            // AproximaÃ§Ã£o: espera-se 1 registro por aluno por dia
            $naoRegistrados = max(($totalAlunos * min($diasPeriodo, count($datasPeriodo))) - $presencasRegistradas, 0);

            // Injetar na instÃ¢ncia para uso na view
            $sala->total_alunos = $totalAlunos;
            $sala->presencas_registradas = $presencasRegistradas;
            $sala->presentes = $presentes;
            $sala->ausentes = $ausentes;
            $sala->nao_registrados = $naoRegistrados;

            return $sala;
        };

        // Aplicar ordenaÃ§Ã£o e paginaÃ§Ã£o
        if ($sort && in_array($sort, $allowedSortsComputed)) {
            // Buscar todas as salas, calcular estatÃ­sticas, ordenar e paginar em memÃ³ria
            $salasBase = $salasQuery->orderBy('salas.codigo')->get();
            $salasEstatisticas = $salasBase->map($calcEstatisticasSala);
            $salasEstatisticas = $direction === 'desc'
                ? $salasEstatisticas->sortByDesc($sort)->values()
                : $salasEstatisticas->sortBy($sort)->values();

            $total = $salasEstatisticas->count();
            $items = $salasEstatisticas->slice(($page - 1) * $perPage, $perPage)->values();
            $salasComEstatisticas = new LengthAwarePaginator($items, $total, $perPage, $page, [
                'path' => $request->url(),
                'pageName' => 'page',
            ]);
            $salasComEstatisticas->appends($request->query());
        } else {
            // OrdenaÃ§Ã£o por campos do banco (default: codigo)
            $orderField = ($sort && in_array($sort, $allowedSortsDb)) ? $sort : 'codigo';
            $salasComEstatisticas = $salasQuery->orderBy('salas.' . $orderField, $direction)->paginate($perPage)->withQueryString();
            // Calcular estatÃ­sticas somente para a pÃ¡gina atual
            $salasComEstatisticas->getCollection()->transform($calcEstatisticasSala);
        }
        
        // Carregar dados para os selects (apenas quando necessÃ¡rio)
        $alunosQuery = Aluno::select('id', 'nome', 'sobrenome')->where('ativo', true);
        $funcionariosQuery = Funcionario::select('id', 'nome', 'sobrenome')->where('ativo', true);
        $todasSalasQuery = \App\Models\Sala::select('id', 'codigo', 'nome')->where('ativo', true);
        
        // Aplicar filtro de escola apenas se nÃ£o for superadmin ou suporte, ou se tiver uma escola selecionada
        if (!$isSuperAdminOrSupport || $escolaId) {
            $alunosQuery->where('escola_id', $escolaId);
            $funcionariosQuery->where('escola_id', $escolaId);
            $todasSalasQuery->where('escola_id', $escolaId);
        }
        
        $alunos = $alunosQuery->orderBy('nome')->get();
        $funcionarios = $funcionariosQuery->orderBy('nome')->get();
        $todasSalas = $todasSalasQuery->orderBy('codigo')->get();
        
        // Adicionar log para debug
        if ($isSuperAdminOrSupport) {
            \Illuminate\Support\Facades\Log::info("Acesso Ã  tela de presenÃ§as por usuÃ¡rio " . 
                ($user->isSuperAdmin() ? 'superadmin' : 'suporte') . 
                ". Total de salas: " . $salasComEstatisticas->total());
        }
        
        return view('presencas.index', compact('salasComEstatisticas', 'alunos', 'funcionarios', 'todasSalas', 'dataInicio', 'dataFim'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->hasRole('suporte');
        
        $escolaId = $isSuperAdminOrSupport && session('escola_atual') 
            ? session('escola_atual') 
            : $user->escola_id;
            
        $alunosQuery = Aluno::where('ativo', true);
        $funcionariosQuery = Funcionario::where('ativo', true);
        
        // Aplicar filtro de escola apenas se nÃ£o for superadmin ou suporte, ou se tiver uma escola selecionada
        if (!$isSuperAdminOrSupport || $escolaId) {
            $alunosQuery->where('escola_id', $escolaId);
            $funcionariosQuery->where('escola_id', $escolaId);
        }
        
        $alunos = $alunosQuery->orderBy('nome')->get();
        $funcionarios = $funcionariosQuery->orderBy('nome')->get();
        $dataAtual = Carbon::now()->format('Y-m-d');
        $horaAtual = Carbon::now()->format('H:i');
        
        // Adicionar log para debug
        if ($isSuperAdminOrSupport) {
            \Illuminate\Support\Facades\Log::info("Acesso Ã  tela de criaÃ§Ã£o de presenÃ§as por usuÃ¡rio " . 
                ($user->isSuperAdmin() ? 'superadmin' : 'suporte'));
        }
        
        return view('presencas.create', compact('alunos', 'funcionarios', 'dataAtual', 'horaAtual'));
    }

    /**
     * Show the form for quick attendance registration
     */
    public function registroRapido()
    {
        $user = Auth::user();
        $dataAtual = Carbon::now()->format('Y-m-d');
        $horaAtual = Carbon::now()->format('H:i');
        
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->hasRole('suporte');
        
        $escolaId = $isSuperAdminOrSupport && session('escola_atual') 
            ? session('escola_atual') 
            : $user->escola_id;
        
        if ($user->isAdminOrCoordinator() || $isSuperAdminOrSupport) {
            // Admin/Coordenador/Superadmin/Suporte vÃª todos os alunos da escola
            $alunosQuery = Aluno::with('sala')->where('ativo', true);
            
            // Aplicar filtro de escola apenas se nÃ£o for superadmin ou suporte, ou se tiver uma escola selecionada
            if (!$isSuperAdminOrSupport || $escolaId) {
                $alunosQuery->where('escola_id', $escolaId);
            }
            
            $alunos = $alunosQuery->orderBy('nome')->get();
        } else {
            // Verificar se o professor estÃ¡ em sala neste momento
            $professorEmSala = $this->verificarProfessorEmSala($user, $dataAtual, $horaAtual);
            
            if (!$professorEmSala) {
                return redirect()->back()->with('warning', 'VocÃª sÃ³ pode lanÃ§ar presenÃ§as nos horÃ¡rios em que estÃ¡ escalado em sala de aula.');
            }
            
            // Professor vÃª apenas alunos das salas onde estÃ¡ escalado neste momento
            $salasAtivas = $this->getSalasAtivasProfessor($user, $dataAtual, $horaAtual);
            
            if ($salasAtivas->isEmpty()) {
                $alunos = collect();
            } else {
                $alunos = Aluno::with('sala')
                    ->where('ativo', true)
                    ->whereIn('sala_id', $salasAtivas->pluck('id'))
                    ->orderBy('nome')
                    ->get();
            }
        }
        
        // Verificar se jÃ¡ existem registros para hoje
        $presencasHoje = Presenca::whereDate('data', $dataAtual)
            ->pluck('aluno_id')
            ->toArray();

        // Obter salas baseado nas permissÃµes do usuÃ¡rio
        if ($user->isAdminOrCoordinator() || $isSuperAdminOrSupport) {
            $salasQuery = \App\Models\Sala::ativas();
            
            // Aplicar filtro de escola apenas se nÃ£o for superadmin ou suporte, ou se tiver uma escola selecionada
            if (!$isSuperAdminOrSupport || $escolaId) {
                $salasQuery->where('escola_id', $escolaId);
            }
            
            $salas = $salasQuery->orderBy('codigo')->get();
        } else {
            $salas = $user->salas()
                ->where('salas.ativo', true)
                ->wherePivot('ativo', true)
                ->orderBy('codigo')
                ->get();
        }
        
        // Adicionar log para debug
        if ($isSuperAdminOrSupport) {
            \Illuminate\Support\Facades\Log::info("Acesso Ã  tela de registro rÃ¡pido de presenÃ§as por usuÃ¡rio " . 
                ($user->isSuperAdmin() ? 'superadmin' : 'suporte') . 
                ". Total de alunos: " . $alunos->count() . 
                ". Total de salas: " . $salas->count());
        }
        
        return view('presencas.registro-rapido', compact('alunos', 'dataAtual', 'horaAtual', 'presencasHoje', 'salas'));
    }

    /**
     * Verifica se o professor estÃ¡ escalado em sala neste momento
     */
    private function verificarProfessorEmSala($user, $data, $hora)
    {
        $funcionario = $user->funcionario;
        
        if (!$funcionario) {
            return false;
        }
        
        return \App\Models\Escala::where('funcionario_id', $funcionario->id)
            ->where('data', $data)
            ->where('tipo_atividade', 'em_sala')
            ->where('hora_inicio', '<=', $hora)
            ->where('hora_fim', '>=', $hora)
            ->exists();
    }

    /**
     * ObtÃ©m as salas onde o professor estÃ¡ escalado neste momento
     */
    private function getSalasAtivasProfessor($user, $data, $hora)
    {
        $funcionario = $user->funcionario;
        
        if (!$funcionario) {
            return collect();
        }
        
        $escalasAtivas = \App\Models\Escala::with('sala')
            ->where('funcionario_id', $funcionario->id)
            ->where('data', $data)
            ->where('tipo_atividade', 'em_sala')
            ->where('hora_inicio', '<=', $hora)
            ->where('hora_fim', '>=', $hora)
            ->whereNotNull('sala_id')
            ->get();
            
        return $escalasAtivas->pluck('sala')->filter();
    }

    /**
     * Store multiple attendance records at once
     */
    public function registroRapidoStore(Request $request)
    {
        // Filtrar apenas presenÃ§as que foram selecionadas (tÃªm o campo presente definido)
        $presencasFiltradas = [];
        if ($request->has('presencas')) {
            foreach ($request->presencas as $index => $presenca) {
                if (isset($presenca['presente'])) {
                    $presencasFiltradas[] = $presenca;
                }
            }
        }
        
        // Atualizar o request com as presenÃ§as filtradas
        $request->merge(['presencas' => $presencasFiltradas]);
        
        // Usar sempre a data e hora atuais
        $dataAtual = Carbon::now()->format('Y-m-d');
        $horaAtual = Carbon::now()->format('H:i');
        
        $validator = Validator::make($request->all(), [
            'presencas' => 'required|array|min:1',
            'presencas.*.aluno_id' => 'required|exists:alunos,id',
            'presencas.*.presente' => 'required|in:0,1',
            'presencas.*.justificativa' => 'nullable|string',
        ], [
            'presencas.required' => 'Selecione pelo menos um aluno para registrar a presenÃ§a.',
            'presencas.min' => 'Selecione pelo menos um aluno para registrar a presenÃ§a.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = Auth::user();
            $funcionarioId = $user->funcionario ? $user->funcionario->id : 1;
            $registrosProcessados = 0;
            $registrosIgnorados = 0;
            $registrosNaoAutorizados = 0;

            // Obter salas do usuÃ¡rio se nÃ£o for admin/coordenador
            $salasDoUsuario = [];
            if (!$user->isAdminOrCoordinator()) {
                $salasDoUsuario = $user->salas()->pluck('salas.id')->toArray();
            }

            foreach ($request->presencas as $presencaData) {
                // Verificar se jÃ¡ existe registro para este aluno nesta data e tempo de aula
                $tempoAula = $presencaData['tempo_aula'] ?? null;
                $existente = Presenca::where('aluno_id', $presencaData['aluno_id'])
                    ->whereDate('data', $dataAtual)
                    ->where(function($query) use ($tempoAula) {
                        if ($tempoAula) {
                            $query->where('tempo_aula', $tempoAula);
                        } else {
                            $query->whereNull('tempo_aula');
                        }
                    })
                    ->first();
                    
                if ($existente) {
                    $registrosIgnorados++;
                    continue;
                }

                // Verificar se o professor tem acesso a este aluno (se nÃ£o for admin/coordenador)
                if (!$user->isAdminOrCoordinator()) {
                    $aluno = Aluno::find($presencaData['aluno_id']);
                    if (!$aluno || !in_array($aluno->sala_id, $salasDoUsuario)) {
                        $registrosNaoAutorizados++;
                        continue;
                    }
                }

                // Obter sala do aluno no momento do registro
                $aluno = \App\Models\Aluno::find($presencaData['aluno_id']);

                $presenca = Presenca::create([
                    'aluno_id' => $presencaData['aluno_id'],
                    // Prioriza sala informada no contexto (ex.: /presencas/lancar?sala_id=..)
                    'sala_id' => $request->get('sala_id') ?: ($aluno ? $aluno->sala_id : null),
                    'funcionario_id' => $funcionarioId,
                    'data' => $dataAtual,
                    'tempo_aula' => $presencaData['tempo_aula'] ?? null,
                    'presente' => $presencaData['presente'],
                    'hora_entrada' => $presencaData['presente'] ? $horaAtual : null,
                    'hora_saida' => null,
                    'justificativa' => $presencaData['justificativa'] ?? null,
                    'observacoes' => null,
                ]);
                
                // Registrar no histÃ³rico
                Historico::registrar(
                    'criado',
                    'Presenca',
                    $presenca->id,
                    null,
                    $presenca->toArray(),
                    'PresenÃ§a registrada via registro rÃ¡pido'
                );
                
                $registrosProcessados++;
            }

            $mensagem = "Registro de presenÃ§a realizado com sucesso! {$registrosProcessados} registros processados.";
            if ($registrosIgnorados > 0) {
                $mensagem .= " {$registrosIgnorados} registros foram ignorados (jÃ¡ existiam).";
            }
            if ($registrosNaoAutorizados > 0) {
                $mensagem .= " {$registrosNaoAutorizados} registros nÃ£o foram processados (alunos nÃ£o estÃ£o em suas salas).";
            }

            return redirect()->route('presencas.registro-rapido')
                ->with('success', $mensagem);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao registrar presenÃ§as: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Register early departure for students
     */
    public function registrarSaidaMaisCedo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aluno_id' => 'required|exists:alunos,id',
            'data' => 'required|date',
            'hora_saida' => 'required|date_format:H:i',
            'justificativa' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Buscar o registro de presenÃ§a do dia
            $presenca = Presenca::where('aluno_id', $request->aluno_id)
                ->whereDate('data', $request->data)
                ->first();

            if (!$presenca) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registro de presenÃ§a nÃ£o encontrado para este aluno hoje.'
                ], 404);
            }

            if ($presenca->hora_saida) {
                return response()->json([
                    'success' => false,
                    'message' => 'SaÃ­da jÃ¡ foi registrada para este aluno.'
                ], 400);
            }

            $dadosAntigos = $presenca->toArray();
            
            $presenca->update([
                'hora_saida' => $request->hora_saida,
                'justificativa' => $request->justificativa
            ]);
            
            // Registrar no histÃ³rico
            Historico::registrar(
                'atualizado',
                'Presenca',
                $presenca->id,
                $dadosAntigos,
                $presenca->fresh()->toArray(),
                'SaÃ­da mais cedo registrada'
            );

            return response()->json([
                'success' => true,
                'message' => 'SaÃ­da mais cedo registrada com sucesso!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar saÃ­da: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aluno_id' => 'required|exists:alunos,id',
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data' => 'required|date',
            'presente' => 'required|boolean',
            'hora_entrada' => 'nullable|date_format:H:i',
            'hora_saida' => 'nullable|date_format:H:i',
            'justificativa' => 'nullable|string|required_if:presente,0',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = Auth::user();
            
            // Verificar se o professor tem acesso a este aluno (se nÃ£o for admin/coordenador)
            if (!$user->isAdminOrCoordinator()) {
                $aluno = Aluno::find($request->aluno_id);
                $salasDoUsuario = $user->salas()->pluck('salas.id')->toArray();
                
                if (!$aluno || !in_array($aluno->sala_id, $salasDoUsuario)) {
                    return redirect()->back()
                        ->with('error', 'VocÃª nÃ£o tem permissÃ£o para registrar presenÃ§a deste aluno.')
                        ->withInput();
                }
            }
            
            // Verificar se jÃ¡ existe registro para este aluno nesta data
            $existente = Presenca::where('aluno_id', $request->aluno_id)
                ->whereDate('data', $request->data)
                ->first();
                
            if ($existente) {
                return redirect()->back()
                    ->with('error', 'JÃ¡ existe um registro de presenÃ§a para este aluno nesta data.')
                    ->withInput();
            }
            
            // Garantir que o aluno está carregado para obter sala_id
            $aluno = \App\Models\Aluno::find($request->aluno_id);

            $presenca = Presenca::create([
                'aluno_id' => $request->aluno_id,
                // Prioriza sala informada no contexto (se vier do form)
                'sala_id' => $request->get('sala_id') ?: ($aluno ? $aluno->sala_id : null),
                'funcionario_id' => $request->funcionario_id,
                'data' => $request->data,
                'presente' => $request->presente,
                'hora_entrada' => $request->hora_entrada,
                'hora_saida' => $request->hora_saida,
                'justificativa' => $request->justificativa,
                'observacoes' => $request->observacoes,
            ]);
            
            // Registrar no histÃ³rico
            Historico::registrar(
                'criado',
                'Presenca',
                $presenca->id,
                null,
                $presenca->toArray(),
                'PresenÃ§a registrada individualmente'
            );

            return redirect()->route('presencas.index')
                ->with('success', 'PresenÃ§a registrada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao registrar presenÃ§a: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * MÃ©todo para registrar presenÃ§a rapidamente
     */
    public function registrarPresenca(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aluno_id' => 'required|exists:alunos,id',
            'tipo' => 'required|in:entrada,saida',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados invÃ¡lidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $hoje = Carbon::today();
            $agora = Carbon::now()->format('H:i');
            $funcionarioId = $user->funcionario->id ?? $request->funcionario_id;
            
            // Verificar se o professor tem acesso a este aluno (se nÃ£o for admin/coordenador)
            if (!$user->isAdminOrCoordinator()) {
                $aluno = Aluno::find($request->aluno_id);
                $salasDoUsuario = $user->salas()->pluck('salas.id')->toArray();
                
                if (!$aluno || !in_array($aluno->sala_id, $salasDoUsuario)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'VocÃª nÃ£o tem permissÃ£o para registrar presenÃ§a deste aluno.'
                    ], 403);
                }
            }
            
            // Verificar se jÃ¡ existe registro para este aluno hoje
            $presenca = Presenca::where('aluno_id', $request->aluno_id)
                ->whereDate('data', $hoje)
                ->first();
                
            if ($request->tipo === 'entrada') {
                if ($presenca) {
                    // Atualizar hora de entrada
                    $dadosAntigos = $presenca->toArray();
                    $presenca->update([
                        'presente' => true,
                        'hora_entrada' => $agora,
                        'funcionario_id' => $funcionarioId,
                    ]);
                    
                    // Registrar no histÃ³rico
                    Historico::registrar(
                        'atualizado',
                        'Presenca',
                        $presenca->id,
                        $dadosAntigos,
                        $presenca->fresh()->toArray(),
                        'Entrada registrada via registro rÃ¡pido'
                    );
                } else {
                    // Criar novo registro
                    // Garantir que o aluno está carregado para obter sala_id
                    $aluno = \App\Models\Aluno::find($request->aluno_id);
                    $presenca = Presenca::create([
                        'aluno_id' => $request->aluno_id,
                        'sala_id' => $request->get('sala_id') ?: ($aluno ? $aluno->sala_id : null),
                        'funcionario_id' => $funcionarioId,
                        'data' => $hoje,
                        'presente' => true,
                        'hora_entrada' => $agora,
                    ]);
                    
                    // Registrar no histÃ³rico
                    Historico::registrar(
                        'criado',
                        'Presenca',
                        $presenca->id,
                        null,
                        $presenca->toArray(),
                        'Entrada registrada via registro rÃ¡pido'
                    );
                }
                
                $mensagem = 'Entrada registrada com sucesso!';
            } else { // saida
                if ($presenca) {
                    // Atualizar hora de saÃ­da
                    $dadosAntigos = $presenca->toArray();
                    $presenca->update([
                        'hora_saida' => $agora,
                    ]);
                    
                    // Registrar no histÃ³rico
                    Historico::registrar(
                        'atualizado',
                        'Presenca',
                        $presenca->id,
                        $dadosAntigos,
                        $presenca->fresh()->toArray(),
                        'SaÃ­da registrada via registro rÃ¡pido'
                    );
                    
                    $mensagem = 'SaÃ­da registrada com sucesso!';
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'NÃ£o hÃ¡ registro de entrada para este aluno hoje.'
                    ], 400);
                }
            }
            
            $aluno = Aluno::find($request->aluno_id);
            
            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'aluno' => $aluno->nomeCompleto,
                'hora' => $agora
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar presença: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {

        $data = $request->get('data', now()->format('Y-m-d'));
        // Corrige casos em que o front envia &amp; no href e o PHP recebe "amp;sala_id"
        $salaId = $request->get('sala_id', $request->get('amp;sala_id'));
        
        // Se sala_id não foi fornecida, usar a primeira sala do usuário ou todas se for admin
        if (!$salaId) {
            if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('coordenador')) {
                $sala = \App\Models\Sala::with('alunos')->where('ativo', true)->first();
            } else {
                $sala = auth()->user()->salas()
                    ->with('alunos')
                    ->where('salas.ativo', true)
                    ->wherePivot('ativo', true)
                    ->first();
            }
        } else {
            // Ignorar escopo global de escola ao buscar sala espec difica
            $sala = \App\Models\Sala::withoutGlobalScope('escola')
                ->with(['alunos' => function($q){ $q->withoutGlobalScope('escola'); }])
                ->where('ativo', true)
                ->find($salaId);
        }
        // Removido dd($salaId) que interrompia a execução
        if (!$sala) {
            return redirect()->route('presencas.index')->with('error', 'Nenhuma sala encontrada.');
        }
        // Sincronizar contexto de escola para superadmin/suporte ao acessar com sala_id específico
        if (
            $salaId &&
            (auth()->user()->isSuperAdmin() || auth()->user()->hasRole('suporte') || auth()->user()->temCargo('Suporte') || auth()->user()->temCargo('Suporte Técnico'))
        ) {
            if (!session('escola_atual') || session('escola_atual') !== $sala->escola_id) {
                session(['escola_atual' => $sala->escola_id]);
            }
        }
        
        // Verificar permissÃµes (liberar superadmin/suporte)
        $isSuperAdminOrSupport = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte');
        if (!$isSuperAdminOrSupport && !auth()->user()->hasRole('admin') && !auth()->user()->hasRole('coordenador')) {
            $salasDoUsuario = auth()->user()->salas->pluck('id')->toArray();
            if (!in_array($sala->id, $salasDoUsuario)) {
                return redirect()->route('presencas.index')->with('error', 'VocÃª nÃ£o tem permissÃ£o para visualizar esta sala.');
            }
        }
        
        // Obter alunos pela GradeAula no dia informado (em vez da sala fixa)
        // Determinar o dia da semana no formato usado pela GradeAula
        $dow = (int) \Carbon\Carbon::parse($data)->dayOfWeek; // 0=domingo ... 6=sábado
        $diaSemanaMap = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terca',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sabado',
        ];
        $diaSlug = $diaSemanaMap[$dow] ?? null;

        $alunos = collect();
        if ($diaSlug && $diaSlug !== 'domingo') {
            // Buscar turmas agendadas nesta sala e dia, respeitando o período da grade
            $gradesQuery = \App\Models\GradeAula::where('ativo', true)
                ->where('sala_id', $sala->id)
                ->where('dia_semana', $diaSlug)
                // Dentro do período: início <= data e fim >= data, aceitando nulos como indefinidos
                ->where(function ($q) use ($data) {
                    $q->whereNull('data_inicio')
                      ->orWhere('data_inicio', '<=', $data);
                })
                ->where(function ($q) use ($data) {
                    $q->whereNull('data_fim')
                      ->orWhere('data_fim', '>=', $data);
                });

            $turmaIds = $gradesQuery->pluck('turma_id')->unique()->filter()->values();

            if ($turmaIds->isNotEmpty()) {
                $alunos = \App\Models\Aluno::where('ativo', true)
                    ->whereIn('turma_id', $turmaIds)
                    ->orderBy('nome')
                    ->get();
            }
        }
        
        // Obter presenÃ§as da data especÃ­fica
        $presencas = Presenca::whereDate('data', $data)
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->get();
        
        // Montar grade/aulas do dia para esta sala (acontecimentos do dia)
        $gradesDia = collect();
        $resumoAulas = collect();
        $temposDia = collect();
        $turmasOptionsDia = [];
        if ($diaSlug && $diaSlug !== 'domingo') {
            $gradesDia = \App\Models\GradeAula::with(['turma:id,nome','disciplina:id,nome','professor:id,nome','tempoSlot:id,ordem,hora_inicio,hora_fim,tipo'])
                ->where('ativo', true)
                ->where('sala_id', $sala->id)
                ->where('dia_semana', $diaSlug)
                ->where(function ($q) use ($data) {
                    $q->whereNull('data_inicio')
                      ->orWhere('data_inicio', '<=', $data);
                })
                ->where(function ($q) use ($data) {
                    $q->whereNull('data_fim')
                      ->orWhere('data_fim', '>=', $data);
                })
                ->get()
                ->sortBy(function($g){ return optional($g->tempoSlot)->ordem ?? 999; })
                ->values();

            foreach ($gradesDia as $grade) {
                $turmaId = $grade->turma_id;
                $alunoIdsTurma = \App\Models\Aluno::where('ativo', true)->where('turma_id', $turmaId)->pluck('id');
                $ordem = optional($grade->tempoSlot)->ordem ? (int) $grade->tempoSlot->ordem : null;
                $presencasTurma = $presencas->whereIn('aluno_id', $alunoIdsTurma->all());
                if ($ordem) {
                    $presencasTurma = $presencasTurma->where('tempo_aula', $ordem);
                }
                $totalTurma = $alunoIdsTurma->count();
                $presentesSlot = $presencasTurma->where('presente', true)->count();
                $ausentesSlot = $presencasTurma->where('presente', false)->count();
                $naoRegistradosSlot = max($totalTurma - $presencasTurma->count(), 0);

                $resumoAulas->push([
                    'turma_id' => $turmaId,
                    'ordem' => $ordem,
                    'hora_inicio' => optional($grade->tempoSlot)->hora_inicio,
                    'hora_fim' => optional($grade->tempoSlot)->hora_fim,
                    'tipo_tempo' => optional($grade->tempoSlot)->tipo,
                    'turma' => optional($grade->turma)->nome,
                    'disciplina' => optional($grade->disciplina)->nome,
                    'professor' => optional($grade->professor)->nome,
                    'total' => $totalTurma,
                    'presentes' => $presentesSlot,
                    'ausentes' => $ausentesSlot,
                    'nao_registrados' => $naoRegistradosSlot,
                ]);

                if ($ordem) {
                    $temposDia->push($ordem);
                }
            }

            // Opções de turmas e tempos
            $temposDia = $temposDia->unique()->sort()->values();
            $turmasOptionsDia = $gradesDia->pluck('turma')->filter()->unique('id')->mapWithKeys(function($t){
                return [$t->id => $t->nome];
            })->toArray();
        }
        // Mapa por tempo (aluno -> tempo_aula -> presença)
        $presencasPorTempo = [];
        foreach ($presencas as $p) {
            if (!is_null($p->tempo_aula)) {
                $presencasPorTempo[$p->aluno_id][(int)$p->tempo_aula] = $p;
            }
        }

        // Filtros por turma/tempo (somente leitura)
        $filtroTurmaId = $request->get('turma_id');
        $filtroTempo = $request->get('tempo_aula', $request->get('tempo_slot_id'));
        // Validar turma contra opções do dia para evitar lista vazia indevida
        if ($filtroTurmaId && !empty($turmasOptionsDia)) {
            $validTurmaIds = array_map('intval', array_keys($turmasOptionsDia));
            if (!in_array((int) $filtroTurmaId, $validTurmaIds, true)) {
                $filtroTurmaId = null;
            }
        }
        if ($filtroTurmaId) {
            $alunos = $alunos->where('turma_id', (int) $filtroTurmaId)->values();
        }

        // Calcular estatísticas considerando filtros
        if ($filtroTempo) {
            $tempoInt = (int) $filtroTempo;
            $alunoIdsList = $alunos->pluck('id')->all();
            $presencasTempo = $presencas->whereIn('aluno_id', $alunoIdsList)->where('tempo_aula', $tempoInt);
            $presentes = $presencasTempo->where('presente', true)->count();
            $ausentes = $presencasTempo->where('presente', false)->count();
            $naoRegistrados = max($alunos->count() - $presencasTempo->count(), 0);
        } else {
            $presentes = $presencas->where('presente', true)->count();
            $ausentes = $presencas->where('presente', false)->count();
            $naoRegistrados = $alunos->count() - $presencas->count();
        }

        // Resumo por turma (percentuais por dia considerando apenas presenças por tempo)
        $resumoPorTurma = collect();
        if ($gradesDia->isNotEmpty()) {
            // tempos por turma
            $temposPorTurma = [];
            foreach ($gradesDia as $g) {
                $ordem = optional($g->tempoSlot)->ordem ? (int)$g->tempoSlot->ordem : null;
                if ($ordem) {
                    $temposPorTurma[$g->turma_id] = isset($temposPorTurma[$g->turma_id]) ? array_unique(array_merge($temposPorTurma[$g->turma_id], [$ordem])) : [$ordem];
                }
            }
            foreach ($temposPorTurma as $turmaId => $tempos) {
                $alunoIdsTurma = \App\Models\Aluno::where('ativo', true)->where('turma_id', $turmaId)->pluck('id');
                $expected = $alunoIdsTurma->count() * count($tempos);
                $presentesC = 0;
                $ausentesC = 0;
                // Contabilizar apenas registros com tempo definido
                foreach ($tempos as $t) {
                    $presencasSlot = $presencas
                        ->whereIn('aluno_id', $alunoIdsTurma->all())
                        ->where('tempo_aula', (int) $t);
                    $presentesC += $presencasSlot->where('presente', true)->count();
                    $ausentesC += $presencasSlot->where('presente', false)->count();
                }
                $reg = $presentesC + $ausentesC;
                $naoReg = max($expected - $reg, 0);
                $percent = $expected > 0 ? round(($presentesC / $expected) * 100) : 0;
                $resumoPorTurma->push([
                    'turma_id' => $turmaId,
                    'turma' => optional($gradesDia->firstWhere('turma_id', $turmaId)->turma)->nome ?? 'Turma',
                    'expected' => $expected,
                    'presentes' => $presentesC,
                    'ausentes' => $ausentesC,
                    'nao_registrados' => $naoReg,
                    'percentual' => $percent,
                    'tempos' => $tempos,
                ]);
            }
        }
        
        // Preparar opÃ§Ãµes de salas para o filtro (apenas para admin/coordenador)
        $salasOptions = [];
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('coordenador')) {
            $salasQuery = \App\Models\Sala::where('ativo', true);
            
            // Aplicar filtro de escola
            if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
                if (session('escola_atual')) {
                    $salasQuery->where('escola_id', session('escola_atual'));
                }
            } else {
                if (auth()->user()->escola_id) {
                    $salasQuery->where('escola_id', auth()->user()->escola_id);
                }
            }
            
            $salasOptions = $salasQuery->orderBy('codigo')
                ->get()
                ->pluck('nome_completo', 'id')
                ->toArray();
        }
        
        return view('presencas.show', compact(
            'sala', 'data', 'alunos', 'presencas',
            'presentes', 'ausentes', 'naoRegistrados', 'salasOptions',
            'gradesDia', 'resumoAulas', 'temposDia', 'turmasOptionsDia',
            'presencasPorTempo', 'filtroTurmaId', 'filtroTempo', 'resumoPorTurma'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $presenca = Presenca::findOrFail($id);
        $alunos = Aluno::where('ativo', true)->orderBy('nome')->get();
        $funcionarios = Funcionario::where('ativo', true)->orderBy('nome')->get();
        
        return view('presencas.edit', compact('presenca', 'alunos', 'funcionarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'aluno_id' => 'required|exists:alunos,id',
            'funcionario_id' => 'required|exists:funcionarios,id',
            'data' => 'required|date',
            'presente' => 'required|boolean',
            'hora_entrada' => 'nullable|date_format:H:i',
            'hora_saida' => 'nullable|date_format:H:i',
            'justificativa' => 'nullable|string|required_if:presente,0',
            'observacoes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = Auth::user();
            $presenca = Presenca::findOrFail($id);
            
            // Verificar se o professor tem acesso a este aluno (se nÃ£o for admin/coordenador)
            if (!$user->isAdminOrCoordinator()) {
                $aluno = Aluno::find($request->aluno_id);
                $salasDoUsuario = $user->salas()->pluck('salas.id')->toArray();
                
                if (!$aluno || !in_array($aluno->sala_id, $salasDoUsuario)) {
                    return redirect()->back()
                        ->with('error', 'VocÃª nÃ£o tem permissÃ£o para editar presenÃ§a deste aluno.')
                        ->withInput();
                }
            }
            
            // Verificar se jÃ¡ existe outro registro para este aluno nesta data
            $existente = Presenca::where('aluno_id', $request->aluno_id)
                ->whereDate('data', $request->data)
                ->where('id', '!=', $id)
                ->first();
                
            if ($existente) {
                return redirect()->back()
                    ->with('error', 'JÃ¡ existe outro registro de presenÃ§a para este aluno nesta data.')
                    ->withInput();
            }
            
            $dadosAntigos = $presenca->toArray();
            
            $presenca->update([
                'aluno_id' => $request->aluno_id,
                'funcionario_id' => $request->funcionario_id,
                'data' => $request->data,
                'presente' => $request->presente,
                'hora_entrada' => $request->hora_entrada,
                'hora_saida' => $request->hora_saida,
                'justificativa' => $request->justificativa,
                'observacoes' => $request->observacoes,
            ]);
            
            // Registrar no histÃ³rico
            Historico::registrar(
                'atualizado',
                'Presenca',
                $presenca->id,
                $dadosAntigos,
                $presenca->fresh()->toArray(),
                'PresenÃ§a atualizada'
            );

            return redirect()->route('presencas.index')
                ->with('success', 'PresenÃ§a atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atualizar presenÃ§a: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = Auth::user();
            $presenca = Presenca::with('aluno')->findOrFail($id);
            
            // Verificar se o professor tem acesso a este aluno (se nÃ£o for admin/coordenador)
            if (!$user->isAdminOrCoordinator()) {
                $salasDoUsuario = $user->salas()->pluck('salas.id')->toArray();
                
                if (!$presenca->aluno || !in_array($presenca->aluno->sala_id, $salasDoUsuario)) {
                    return redirect()->route('presencas.index')
                        ->with('error', 'VocÃª nÃ£o tem permissÃ£o para remover presenÃ§a deste aluno.');
                }
            }
            
            $dadosAntigos = $presenca->toArray();
            
            $presenca->delete();
            
            // Registrar no histÃ³rico
            Historico::registrar(
                'excluido',
                'Presenca',
                $presenca->id,
                $dadosAntigos,
                null,
                'PresenÃ§a removida'
            );
            
            return redirect()->route('presencas.index')
                ->with('success', 'Registro de presenÃ§a removido com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('presencas.index')
                ->with('error', 'Erro ao remover registro de presenÃ§a: ' . $e->getMessage());
        }
    }

    public function lancar(Request $request)
    {
        // Limitar a exibiÃ§Ã£o para 5 dias anteriores ao dia atual
        $hoje = now()->format('Y-m-d');
        $dataInicio = $request->get('data_inicio', now()->subDays(4)->format('Y-m-d'));
        $dataFim = $request->get('data_fim', $hoje);
        
        // Validar se a data de inÃ­cio nÃ£o Ã© futura
        if ($dataInicio > $hoje) {
            $dataInicio = $hoje;
        }
        
        $salaId = $request->get('sala_id');
        
        // Determinar contexto de escola para filtragem
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->hasRole('suporte');
        $escolaId = $isSuperAdminOrSupport && session('escola_atual')
            ? session('escola_atual')
            : $user->escola_id;

        // Obter salas do usuÃ¡rio baseado no papel, aplicando filtro por escola quando aplicÃ¡vel
        if ($isSuperAdminOrSupport || $user->hasRole('admin') || $user->hasRole('coordenador')) {
            $salasQuery = \App\Models\Sala::with('alunos')->where('ativo', true);
            $todasSalasQuery = \App\Models\Sala::orderBy('codigo')->where('ativo', true);
            // Filtrar por escola: para superadmin/suporte somente quando houver escola selecionada; para admin/coordenador sempre
            if (!$isSuperAdminOrSupport || $escolaId) {
                $salasQuery->where('escola_id', $escolaId);
                $todasSalasQuery->where('escola_id', $escolaId);
            }
            $todasSalas = $todasSalasQuery->get();
        } else {
            // Professores e demais veem apenas suas prÃ³prias salas
            $salasQuery = $user->salas()
                ->with('alunos')
                ->where('salas.ativo', true)
                ->wherePivot('ativo', true);
            $todasSalas = $user->salas()->orderBy('codigo')->get();
        }
        
        // Aplicar filtro de sala se especificado
        if ($salaId) {
            $salasQuery->where('salas.id', $salaId);
        }

        $salas = $salasQuery->get();

        // SUBSTITUIR listagem de alunos por sala por alunos das turmas agendadas na sala (GradeAula)
        // Alinha com a regra de negÃ³cio: alunos pertencem a turmas; turmas ocupam salas via grade de aulas
        if ($salas->count() > 0) {
            // Preparar mapeamento de dia da semana (Carbon -> slugs usados em GradeAula)
            $diaSemanaMap = [
                1 => 'segunda',
                2 => 'terca',
                3 => 'quarta',
                4 => 'quinta',
                5 => 'sexta',
                6 => 'sabado',
                0 => 'domingo',
            ];

            // Datas jÃ¡ sÃ£o definidas mais abaixo; aqui precisamos antecipar cÃ¡lculo simples do perÃ­odo
            $datasPeriodo = [];
            $dataAtualTmp = \Carbon\Carbon::parse($dataInicio);
            $dataFinalTmp = \Carbon\Carbon::parse($dataFim);
            $maxDiasTmp = 5; // manter mesma limitaÃ§Ã£o da view
            $diasContadosTmp = 0;
            while ($dataAtualTmp <= $dataFinalTmp && $diasContadosTmp < $maxDiasTmp) {
                $datasPeriodo[] = $dataAtualTmp->copy();
                $dataAtualTmp->addDay();
                $diasContadosTmp++;
            }

            foreach ($salas as $sala) {
                $turmaIds = collect();

                foreach ($datasPeriodo as $dataCarbon) {
                    // Obter slug do dia da semana conforme GradeAula::DIAS_SEMANA
                    $dow = (int) $dataCarbon->dayOfWeek; // 0=domingo, 1=segunda, ... 6=sabado
                    $diaSlug = $diaSemanaMap[$dow] ?? null;
                    if (!$diaSlug || $diaSlug === 'domingo') {
                        // Ignorar domingo por padrÃ£o
                        continue;
                    }

                    // Buscar turmas agendadas nesta sala para o diaSlug
                    $gradesDia = \App\Models\GradeAula::with(['turma:id,nome', 'turma.alunos' => function($q) {
                            $q->select('id','nome','sobrenome','turma_id');
                        }])
                        ->where('sala_id', $sala->id)
                        ->where('ativo', true)
                        ->where('dia_semana', $diaSlug)
                        ->get();

                    if ($gradesDia->count() > 0) {
                        $turmaIds = $turmaIds->merge($gradesDia->pluck('turma_id')->filter());
                    }
                }

                $turmaIds = $turmaIds->filter()->unique()->values();

                // Carregar alunos de todas as turmas encontradas (Ãºnicos)
                if ($turmaIds->count() > 0) {
                    $alunosDasTurmas = \App\Models\Aluno::select('id','nome','sobrenome','turma_id')
                        ->whereIn('turma_id', $turmaIds)
                        ->where('ativo', true)
                        ->orderBy('nome')
                        ->get();

                    // Sobrescrever a relaÃ§Ã£o 'alunos' da sala com a coleÃ§Ã£o baseada em turmas via grade
                    $sala->setRelation('alunos', $alunosDasTurmas);
                } else {
                    // Nenhuma turma agendada para o perÃ­odo filtrado: zera alunos exibidos para esta sala
                    $sala->setRelation('alunos', collect());
                }
            }
        }
        
        // Obter todas as datas no intervalo (limitado a 5 dias)
        $datas = [];
        $dataAtual = \Carbon\Carbon::parse($dataInicio);
        $dataFinal = \Carbon\Carbon::parse($dataFim);
        
        // Garantir que nÃ£o mostramos mais de 5 dias
        $maxDias = 5;
        $diasContados = 0;
        
        while ($dataAtual <= $dataFinal && $diasContados < $maxDias) {
            $datas[] = [
                'data' => $dataAtual->format('Y-m-d'),
                'eh_fim_semana' => $dataAtual->isWeekend(),
                'eh_hoje' => $dataAtual->format('Y-m-d') === $hoje
            ];
            $dataAtual->addDay();
            $diasContados++;
        }
        
        // Obter presenÃ§as existentes no perÃ­odo
        $datasSimples = array_column($datas, 'data');
        $presencasExistentes = Presenca::whereIn(DB::raw('DATE(data)'), $datasSimples)->get();
        // Reorganizar as presenÃ§as para suportar mÃºltiplos tempos por data
        $presencasFormatadas = [];
        foreach ($presencasExistentes as $presenca) {
            $dataKey = $presenca->data->format('Y-m-d');
            $alunoId = $presenca->aluno_id;
            $tempo = $presenca->tempo_aula ?? 0; // 0 indica registro sem tempo especÃ­fico
            if (!isset($presencasFormatadas[$dataKey])) $presencasFormatadas[$dataKey] = [];
            if (!isset($presencasFormatadas[$dataKey][$alunoId])) $presencasFormatadas[$dataKey][$alunoId] = [];
            $presencasFormatadas[$dataKey][$alunoId][$tempo] = $presenca;
        }

        // Calcular tempos de aula disponÃ­veis por sala e data, considerando o professor logado
        $temposDisponiveis = [];
        $diaSemanaMap = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terca',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sabado',
        ];
        $funcionarioIdLogado = $user->funcionario ? $user->funcionario->id : null;
        $filtrarPorProfessor = !$user->hasRole('admin') && !$user->hasRole('coordenador') && !$isSuperAdminOrSupport;
        foreach ($salas as $sala) {
            foreach ($datas as $dInfo) {
                $dataStr = $dInfo['data'];
                $dow = (int) \Carbon\Carbon::parse($dataStr)->dayOfWeek;
                $diaSlug = $diaSemanaMap[$dow] ?? null;
                $tempos = [];
                if ($diaSlug && $diaSlug !== 'domingo') {
                    $gradesQuery = \App\Models\GradeAula::with(['tempoSlot' => function($q){ $q->select('id','ordem','tipo'); }])
                        ->where('sala_id', $sala->id)
                        ->where('ativo', true)
                        ->where('dia_semana', $diaSlug);
                    if ($filtrarPorProfessor && $funcionarioIdLogado) {
                        $gradesQuery->where('funcionario_id', $funcionarioIdLogado);
                    }
                    $grades = $gradesQuery->get();
                    foreach ($grades as $grade) {
                        $ordem = $grade->tempoSlot ? (int) $grade->tempoSlot->ordem : null;
                        if ($ordem && $ordem >= 1 && $ordem <= 5) {
                            $tempos[] = $ordem;
                        }
                    }
                }
                $temposDisponiveis[$sala->id][$dataStr] = array_values(array_unique($tempos));
            }
        }

        return view('presencas.lancar', compact('salas', 'datas', 'presencasFormatadas', 'dataInicio', 'dataFim', 'todasSalas', 'temposDisponiveis'));
    }

    public function lancarStore(Request $request)
    {
        // Validar se as datas nÃ£o sÃ£o futuras
        $hoje = now()->format('Y-m-d');
        
        $request->validate([
            'data_inicio' => 'required|date|before_or_equal:' . $hoje,
            'data_fim' => 'required|date|after_or_equal:data_inicio|before_or_equal:' . $hoje,
            'presencas' => 'required|array',
            'presencas.*.*.aluno_id' => 'required|exists:alunos,id',
            'presencas.*.*.data' => 'required|date|before_or_equal:' . $hoje,
            'presencas.*.*.presente' => 'required|boolean',
            'presencas.*.*.justificativa' => 'nullable|string|max:255'
        ]);

        // Verificar permissÃµes do usuÃ¡rio antes de processar
        $user = auth()->user();
        $temPermissaoTotal = $user->hasRole('superadmin') || $user->hasRole('suporte') || 
                             $user->hasRole('admin') || $user->hasRole('coordenador');
        
        $funcionarioId = null;
        if ($user->funcionario) {
            $funcionarioId = $user->funcionario->id;
        } else {
            $funcionarioId = $user->id; // Fallback para usuÃ¡rios sem funcionario_id
        }

        $processados = 0;
        $atualizados = 0;
        $ignorados = 0;
        $erros = [];

        foreach ($request->presencas as $alunoId => $datasPresencas) {
            foreach ($datasPresencas as $data => $dadosPresenca) {
                // Verificar se a data Ã© futura
                if ($data > $hoje) {
                    $erros[] = "NÃ£o Ã© possÃ­vel registrar presenÃ§as para datas futuras: {$data}";
                    continue;
                }
                
                // Verificar permissÃµes do usuÃ¡rio
                $aluno = \App\Models\Aluno::find($alunoId);
                
                if (!$temPermissaoTotal) {
                    $salasDoUsuario = $user->salas()->pluck('salas.id')->toArray();
                    if (!$aluno || !in_array($aluno->sala_id, $salasDoUsuario)) {
                        $erros[] = "Sem permissÃ£o para registrar presenÃ§a do aluno {$aluno->nome} {$aluno->sobrenome}";
                        continue;
                    }
                }

                // Verificar se jÃ¡ existe presenÃ§a para este aluno nesta data
                $presencaExistente = Presenca::where('aluno_id', $alunoId)
                    ->whereDate('data', $data)
                    ->first();

                try {
                    if ($presencaExistente) {
                        // Atualizar presenÃ§a existente
                        $presencaExistente->update([
                            'presente' => $dadosPresenca['presente'],
                            'hora_entrada' => $dadosPresenca['presente'] ? $presencaExistente->hora_entrada ?? now()->format('H:i') : null,
                            'justificativa' => $dadosPresenca['justificativa'] ?? $presencaExistente->justificativa,
                            'funcionario_id' => $funcionarioId
                        ]);
                        $atualizados++;
                    } else {
                        // Criar nova presenÃ§a
                        Presenca::create([
                            'aluno_id' => $alunoId,
                            'sala_id' => $request->get('sala_id') ?: ($aluno ? $aluno->sala_id : null),
                            'data' => $data,
                            'presente' => $dadosPresenca['presente'],
                            'hora_entrada' => $dadosPresenca['presente'] ? now()->format('H:i') : null,
                            'justificativa' => $dadosPresenca['justificativa'] ?? null,
                            'funcionario_id' => $funcionarioId
                        ]);
                        $processados++;
                    }
                } catch (\Exception $e) {
                    $erros[] = "Erro ao registrar presenÃ§a do aluno {$aluno->nome} {$aluno->sobrenome} em {$data}: {$e->getMessage()}";
                }
            }
        }

        $mensagem = "PresenÃ§as processadas: {$processados}";
        if ($atualizados > 0) {
            $mensagem .= ", atualizadas: {$atualizados}";
        }
        if ($ignorados > 0) {
            $mensagem .= ", ignoradas: {$ignorados}";
        }
        if (count($erros) > 0) {
            $mensagem .= ", erros: " . count($erros);
        }

        if (count($erros) > 0) {
            return redirect()->back()->with('warning', $mensagem)->with('errors', $erros);
        }

        return redirect()->route('presencas.lancar', [
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim
        ])->with('success', $mensagem);
    }

    /**
     * Store or update individual attendance record via AJAX
     */
    public function storeIndividual(Request $request)
    {
        $request->validate([
            'aluno_id' => 'required|exists:alunos,id',
            'data' => 'required|date',
            'presente' => 'required|boolean',
            'tempo_aula' => 'nullable|integer|min:1|max:5',
            'justificativa' => 'nullable|string|max:500'
        ]);

        try {
            // Verificar permissÃµes do usuÃ¡rio
            $user = auth()->user();
            $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->hasRole('suporte') || $user->temCargo('Suporte TÃ©cnico');
            $aluno = \App\Models\Aluno::find($request->aluno_id);
            
            // Permitir admin/coordenador e tambÃ©m superadmin/suporte (com contexto de escola)
            if (!$user->isAdminOrCoordinator() && !$isSuperAdminOrSupport) {
                $salasDoUsuario = $user->salas()->pluck('salas.id')->toArray();
                if (!$aluno || !in_array($aluno->sala_id, $salasDoUsuario)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sem permissÃ£o para registrar presenÃ§a deste aluno'
                    ], 403);
                }
            }

            // Validar contexto de escola para superadmin/suporte
            if ($isSuperAdminOrSupport) {
                $escolaContexto = session('escola_atual');
                if (!$escolaContexto) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selecione uma escola para registrar presenÃ§a (contexto ausente)'
                    ], 403);
                }
                if ($aluno && $aluno->escola_id && $aluno->escola_id != $escolaContexto) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aluno nÃ£o pertence Ã  escola selecionada'
                    ], 403);
                }
            }

            // Verificar se jÃ¡ existe presenÃ§a para este aluno nesta data
            $data = $request->data;
            // Garantir que a data esteja em um formato vÃ¡lido para o banco de dados
            if (!is_string($data)) {
                $data = date('Y-m-d', strtotime($data));
            }
            
            $query = Presenca::where('aluno_id', $request->aluno_id)
                ->whereDate('data', $data);

            // Diferenciar por tempo de aula quando fornecido
            if ($request->filled('tempo_aula')) {
                $query->where('tempo_aula', (int) $request->tempo_aula);
            } else {
                $query->whereNull('tempo_aula');
            }

            $presencaExistente = $query->first(['id', 'aluno_id', 'data', 'tempo_aula', 'presente', 'justificativa', 'hora_entrada', 'hora_saida', 'funcionario_id']);

            if ($presencaExistente) {
                // Atualizar presenÃ§a existente
                $dadosAntigos = $presencaExistente->toArray();
                
                $presencaExistente->update([
                    'presente' => $request->presente,
                    'hora_entrada' => $request->presente ? ($presencaExistente->hora_entrada ?? now()->format('H:i')) : null,
                    'justificativa' => !$request->presente ? $request->justificativa : null,
                    'funcionario_id' => auth()->user()->funcionario ? auth()->user()->funcionario->id : 1
                ]);

                // Registrar no histÃ³rico
                \App\Models\Historico::registrar(
                    'atualizado',
                    'Presenca',
                    $presencaExistente->id,
                    $dadosAntigos,
                    $presencaExistente->fresh()->toArray(),
                    'PresenÃ§a atualizada via registro individual'
                );

                return response()->json([
                    'success' => true,
                    'message' => 'PresenÃ§a atualizada com sucesso',
                    'data' => $presencaExistente
                ]);
            } else {
                // Criar nova presenÃ§a
                // Garantir que o aluno está carregado para obter sala_id
                $aluno = $aluno ?? \App\Models\Aluno::find($request->aluno_id);
                $presenca = Presenca::create([
                    'aluno_id' => $request->aluno_id,
                    'sala_id' => $request->get('sala_id') ?: ($aluno ? $aluno->sala_id : null),
                    'data' => $data,
                    'tempo_aula' => $request->filled('tempo_aula') ? (int) $request->tempo_aula : null,
                    'presente' => $request->presente,
                    'hora_entrada' => $request->presente ? now()->format('H:i') : null,
                    'justificativa' => !$request->presente ? $request->justificativa : null,
                    'funcionario_id' => auth()->user()->funcionario ? auth()->user()->funcionario->id : 1
                ]);

                // Registrar no histÃ³rico
                \App\Models\Historico::registrar(
                    'criado',
                    'Presenca',
                    $presenca->id,
                    null,
                    $presenca->toArray(),
                    'PresenÃ§a criada via registro individual'
                );

                return response()->json([
                    'success' => true,
                    'message' => 'PresenÃ§a registrada com sucesso',
                    'data' => $presenca
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar presenÃ§a: ' . $e->getMessage()
            ], 500);
        }
    }
}
