<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Aluno;
use App\Models\Funcionario;
use App\Models\Presenca;
use App\Models\Escala;
use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Exibir lista de relatórios
     */
    public function index(Request $request): View
    {
        $query = Report::where('user_id', auth()->id());

        // Filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Ordenação dinâmica
        $allowedSorts = [
            'id' => 'id',
            'name' => 'name',
            'type' => 'type',
            'format' => 'format',
            'status' => 'status',
            'created_at' => 'created_at',
            'file_size' => 'file_size',
        ];
        $sort = $request->input('sort', 'created_at');
        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (isset($allowedSorts[$sort])) {
            $query->orderBy($allowedSorts[$sort], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $reports = $query->paginate(20)->withQueryString();
        
        // Estatísticas
        $baseStatsQuery = Report::where('user_id', auth()->id());
        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'completed' => (clone $baseStatsQuery)->completed()->count(),
            'pending' => (clone $baseStatsQuery)->pending()->count(),
            'processing' => (clone $baseStatsQuery)->processing()->count()
        ];

        return view('reports.index', compact('reports', 'stats'));
    }

    /**
     * Exibir formulário de criação de relatório
     */
    public function create(): View
    {
        // Restringir à escola atual explicitamente para garantir isolamento
        $escolaAtual = \App\Http\Middleware\EscolaContext::getEscolaAtual();
        $salas = Sala::where('escola_id', $escolaAtual)->ativas()->get();
        $funcionarios = Funcionario::where('escola_id', $escolaAtual)->ativos()->get();
        
        return view('reports.create', compact('salas', 'funcionarios'));
    }

    /**
     * Gerar novo relatório
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:attendance,schedule,performance,financial',
            'format' => 'required|in:pdf,excel,csv',
            'description' => 'nullable|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'sala_id' => 'nullable|exists:salas,id',
            'funcionario_id' => 'nullable|exists:funcionarios,id'
        ]);

        // Determinar escola alvo do relatório a partir do contexto
        $escolaId = \App\Http\Middleware\EscolaContext::getEscolaAtual();
        if (!$escolaId) {
            // fallback para usuário quando não houver escola em contexto
            $escolaId = auth()->user()->escola_id;
        }
        if (!$escolaId) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma escola selecionada. Selecione uma escola para gerar o relatório.',
            ], 422);
        }

        $filters = [
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'sala_id' => $request->sala_id,
            'funcionario_id' => $request->funcionario_id
        ];

        $report = Report::create([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
            'filters' => $filters,
            'data' => [],
            'format' => $request->input('format'),
            'status' => 'pending',
            'user_id' => auth()->id(),
            'escola_id' => $escolaId,
        ]);

        // Processar relatório em background (simulado)
        $this->processReport($report);

        return response()->json([
            'success' => true,
            'message' => 'Relatório criado com sucesso! Você será notificado quando estiver pronto.',
            'report_id' => $report->id
        ]);
    }

    /**
     * Processar relatório (simulação de processamento em background)
     */
    private function processReport(Report $report)
    {
        $report->markAsProcessing();

        try {
            $data = $this->generateReportData($report);
            $report->update(['data' => $data]);

            $filePath = $this->generateReportFile($report);
            $fileSize = Storage::size($filePath);
            
            $report->markAsCompleted($filePath, $fileSize);

            // Criar notificação de sucesso
            \App\Models\Notification::createForUser(
                $report->user_id,
                'success',
                'Relatório Concluído',
                "O relatório '{$report->name}' foi gerado com sucesso e está pronto para download.",
                ['report_id' => $report->id],
                route('reports.show', $report->id),
                'Ver Relatório'
            );

        } catch (\Exception $e) {
            $report->markAsFailed();

            // Criar notificação de erro
            \App\Models\Notification::createForUser(
                $report->user_id,
                'error',
                'Erro no Relatório',
                "Ocorreu um erro ao gerar o relatório '{$report->name}'. Tente novamente.",
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Gerar dados do relatório
     */
    private function generateReportData(Report $report): array
    {
        $filters = $report->filters;
        $dateFrom = Carbon::parse($filters['date_from']);
        $dateTo = Carbon::parse($filters['date_to']);

        switch ($report->type) {
            case 'attendance':
                return $this->generateAttendanceData($dateFrom, $dateTo, $filters);
            case 'schedule':
                return $this->generateScheduleData($dateFrom, $dateTo, $filters);
            case 'performance':
                return $this->generatePerformanceData($dateFrom, $dateTo, $filters);
            case 'financial':
                return $this->generateFinancialData($dateFrom, $dateTo, $filters);
            default:
                return [];
        }
    }

    /**
     * Gerar dados de presença
     */
    private function generateAttendanceData($dateFrom, $dateTo, $filters): array
    {
        $query = Presenca::whereBetween('data', [$dateFrom, $dateTo])
            ->with(['aluno', 'funcionario']);

        if ($filters['sala_id']) {
            $query->whereHas('aluno', function($q) use ($filters) {
                $q->where('sala_id', $filters['sala_id']);
            });
        }

        if ($filters['funcionario_id']) {
            $query->where('funcionario_id', $filters['funcionario_id']);
        }

        $presencas = $query->get();

        return [
            'total_registros' => $presencas->count(),
            'presentes' => $presencas->where('presente', true)->count(),
            'ausentes' => $presencas->where('presente', false)->count(),
            'taxa_presenca' => $presencas->count() > 0 ? round(($presencas->where('presente', true)->count() / $presencas->count()) * 100, 2) : 0,
            'detalhes' => $presencas->map(function($presenca) {
                return [
                    'data' => $presenca->data->format('d/m/Y'),
                    'aluno' => $presenca->aluno->nome . ' ' . $presenca->aluno->sobrenome,
                    'funcionario' => $presenca->funcionario->nome . ' ' . $presenca->funcionario->sobrenome,
                    'presente' => $presenca->presente ? 'Sim' : 'Não',
                    'hora_entrada' => $presenca->hora_entrada ? $presenca->hora_entrada->format('H:i') : null,
                    'hora_saida' => $presenca->hora_saida ? $presenca->hora_saida->format('H:i') : null
                ];
            })->toArray()
        ];
    }

    /**
     * Gerar dados de escala
     */
    private function generateScheduleData($dateFrom, $dateTo, $filters): array
    {
        $query = Escala::whereBetween('data', [$dateFrom, $dateTo])
            ->with(['funcionario', 'sala']);

        if ($filters['sala_id']) {
            $query->where('sala_id', $filters['sala_id']);
        }

        if ($filters['funcionario_id']) {
            $query->where('funcionario_id', $filters['funcionario_id']);
        }

        $escalas = $query->get();

        return [
            'total_escalas' => $escalas->count(),
            'total_horas' => $escalas->sum(function($escala) {
                $inicio = Carbon::parse($escala->hora_inicio);
                $fim = Carbon::parse($escala->hora_fim);
                return $inicio->diffInHours($fim);
            }),
            'detalhes' => $escalas->map(function($escala) {
                $inicio = Carbon::parse($escala->hora_inicio);
                $fim = Carbon::parse($escala->hora_fim);
                return [
                    'data' => $escala->data->format('d/m/Y'),
                    'funcionario' => $escala->funcionario->nome . ' ' . $escala->funcionario->sobrenome,
                    'sala' => $escala->sala ? $escala->sala->nome_completo : 'N/A',
                    'hora_inicio' => $escala->hora_inicio,
                    'hora_fim' => $escala->hora_fim,
                    'horas_trabalhadas' => $inicio->diffInHours($fim),
                    'tipo_atividade' => $escala->tipo_atividade ?? 'N/A'
                ];
            })->toArray()
        ];
    }

    /**
     * Gerar dados de performance
     */
    private function generatePerformanceData($dateFrom, $dateTo, $filters): array
    {
        // Dados de performance baseados em presenças e escalas
        $presencas = Presenca::whereBetween('data', [$dateFrom, $dateTo])->get();
        $escalas = Escala::whereBetween('data', [$dateFrom, $dateTo])->get();
        
        return [
            'periodo' => $dateFrom->format('d/m/Y') . ' a ' . $dateTo->format('d/m/Y'),
            'total_dias' => $dateFrom->diffInDays($dateTo) + 1,
            'media_presencas_dia' => round($presencas->count() / ($dateFrom->diffInDays($dateTo) + 1), 2),
            'media_escalas_dia' => round($escalas->count() / ($dateFrom->diffInDays($dateTo) + 1), 2),
            'funcionario_mais_ativo' => $this->getFuncionarioMaisAtivo($escalas),
            'sala_mais_utilizada' => $this->getSalaMaisUtilizada($escalas)
        ];
    }

    /**
     * Gerar dados financeiros (simulado)
     */
    private function generateFinancialData($dateFrom, $dateTo, $filters): array
    {
        // Simulação de dados financeiros
        $escalas = Escala::whereBetween('data', [$dateFrom, $dateTo])
            ->with('funcionario')
            ->get();
        
        $custoTotal = $escalas->sum(function($escala) {
            $horas = Carbon::parse($escala->hora_inicio)->diffInHours(Carbon::parse($escala->hora_fim));
            return $horas * 25; // R$ 25 por hora (simulado)
        });
        
        return [
            'periodo' => $dateFrom->format('d/m/Y') . ' a ' . $dateTo->format('d/m/Y'),
            'custo_total_estimado' => $custoTotal,
            'custo_medio_dia' => round($custoTotal / ($dateFrom->diffInDays($dateTo) + 1), 2),
            'total_horas_trabalhadas' => $escalas->sum(function($escala) {
                return Carbon::parse($escala->hora_inicio)->diffInHours(Carbon::parse($escala->hora_fim));
            })
        ];
    }

    /**
     * Gerar arquivo do relatório
     */
    private function generateReportFile(Report $report): string
    {
        $fileName = 'reports/' . $report->id . '_' . time() . '.' . $report->format;
        
        switch ($report->format) {
            case 'pdf':
                return $this->generatePDF($report, $fileName);
            case 'excel':
                return $this->generateExcel($report, $fileName);
            case 'csv':
                return $this->generateCSV($report, $fileName);
            default:
                throw new \Exception('Formato não suportado');
        }
    }

    /**
     * Gerar PDF
     */
    private function generatePDF(Report $report, string $fileName): string
    {
        $html = view('reports.pdf', compact('report'))->render();
        
        // Simulação de geração de PDF
        Storage::put($fileName, $html);
        
        return $fileName;
    }

    /**
     * Gerar Excel
     */
    private function generateExcel(Report $report, string $fileName): string
    {
        // Simulação de geração de Excel
        $content = "Nome,Valor\n";
        foreach ($report->data['detalhes'] ?? [] as $item) {
            $content .= implode(',', array_values($item)) . "\n";
        }
        
        Storage::put($fileName, $content);
        
        return $fileName;
    }

    /**
     * Gerar CSV
     */
    private function generateCSV(Report $report, string $fileName): string
    {
        // Simulação de geração de CSV
        $content = "Nome,Valor\n";
        foreach ($report->data['detalhes'] ?? [] as $item) {
            $content .= implode(',', array_values($item)) . "\n";
        }
        
        Storage::put($fileName, $content);
        
        return $fileName;
    }

    /**
     * Exibir relatório
     */
    public function show(Report $report): View
    {
        // Verificar se o usuário pode ver este relatório
        if ($report->user_id !== auth()->id()) {
            abort(403);
        }

        return view('reports.show', compact('report'));
    }

    /**
     * Visualizar dados do relatório
     */
    public function view(Report $report): View
    {
        // Verificar se o usuário pode ver este relatório
        if ($report->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$report->isCompleted() || !$report->data) {
            abort(404, 'Dados do relatório não encontrados');
        }

        return view('reports.view', compact('report'));
    }

    /**
     * Download do relatório
     */
    public function download(Report $report): BinaryFileResponse
    {
        // Verificar se o usuário pode baixar este relatório
        if ($report->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$report->isCompleted() || !$report->file_path || $report->isExpired()) {
            abort(404, 'Arquivo não encontrado ou expirado');
        }

return response()->download(Storage::path($report->file_path), $report->name . '.' . $report->format);
    }

    /**
     * Excluir relatório
     */
    public function destroy(Report $report): JsonResponse
    {
        // Verificar se o usuário pode excluir este relatório
        if ($report->user_id !== auth()->id()) {
            return response()->json(['error' => 'Não autorizado'], 403);
        }

        $report->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Obter funcionário mais ativo
     */
    private function getFuncionarioMaisAtivo($escalas)
    {
        $funcionarios = $escalas->groupBy('funcionario_id');
        $maisAtivo = $funcionarios->sortByDesc(function($escalas) {
            return $escalas->count();
        })->first();
        
        return $maisAtivo ? $maisAtivo->first()->funcionario->nome . ' ' . $maisAtivo->first()->funcionario->sobrenome : 'N/A';
    }

    /**
     * Obter sala mais utilizada
     */
    private function getSalaMaisUtilizada($escalas)
    {
        $salas = $escalas->where('sala_id', '!=', null)->groupBy('sala_id');
        $maisUtilizada = $salas->sortByDesc(function($escalas) {
            return $escalas->count();
        })->first();
        
        return $maisUtilizada ? $maisUtilizada->first()->sala->nome_completo : 'N/A';
    }
}