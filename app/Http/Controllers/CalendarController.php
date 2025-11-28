<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\CalendarEvent;
use App\Models\Escola;
use App\Http\Middleware\EscolaContext;

class CalendarController extends Controller
{
    /**
     * Página principal do calendário escolar.
     */
    public function index(Request $request): View
    {
        return view('calendario.index');
    }

    /**
     * Lista eventos do banco com filtros do calendário.
     */
    public function events(Request $request): JsonResponse
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $categoriaReq = $request->query('categoria');
        $audienciaReq = $request->query('audiencia');
        $anoReq = $request->query('ano');

        $schoolId = $this->resolveSchoolId($request);

        $query = CalendarEvent::query();
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        // Filtro de período (compatível com FullCalendar)
        if ($start && $end) {
            $query->where(function ($q) use ($start, $end) {
                $q->whereDate('start', '<=', $end)
                  ->where(function ($qq) use ($start) {
                      $qq->whereNull('end')->orWhereDate('end', '>=', $start);
                  });
            });
        }

        if ($categoriaReq && $categoriaReq !== 'todos') {
            $query->where('categoria', $categoriaReq);
        }

        if ($audienciaReq && $audienciaReq !== 'todos') {
            $query->where('audiencia', $audienciaReq);
        }

        if ($anoReq) {
            $query->whereYear('start', (int) $anoReq);
        }

        $events = $query->orderBy('start')->get()->map(function (CalendarEvent $ev) {
            $color = $this->colorByCategory($ev->categoria);
            return [
                'id' => $ev->id,
                'title' => $ev->title,
                'start' => $ev->start ? $ev->start->toIso8601String() : null,
                'end' => $ev->end ? $ev->end->toIso8601String() : null,
                'allDay' => (bool) $ev->all_day,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'categoria' => $ev->categoria,
                    'audiencia' => $ev->audiencia,
                    'descricao' => $ev->descricao,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Cria um novo evento.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'all_day' => 'nullable|boolean',
            'categoria' => 'required|string|in:aula,feriado,recesso,avaliacao,evento,matricula',
            'audiencia' => 'required|string|in:gestores,docentes,responsaveis,alunos',
            'descricao' => 'nullable|string|max:1000',
        ]);

        $schoolId = $this->resolveSchoolId($request);

        $event = CalendarEvent::create([
            'title' => $data['title'],
            'start' => $data['start'],
            'end' => $data['end'] ?? null,
            'all_day' => (bool) ($data['all_day'] ?? false),
            'categoria' => $data['categoria'],
            'audiencia' => $data['audiencia'],
            'descricao' => $data['descricao'] ?? null,
            'created_by' => Auth::id(),
            'school_id' => $schoolId,
        ]);

        return response()->json(['success' => true, 'event' => ['id' => $event->id]]);
    }

    /**
     * Atualiza um evento existente.
     */
    public function update(Request $request, CalendarEvent $event): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'all_day' => 'nullable|boolean',
            'categoria' => 'required|string|in:aula,feriado,recesso,avaliacao,evento,matricula',
            'audiencia' => 'required|string|in:gestores,docentes,responsaveis,alunos',
            'descricao' => 'nullable|string|max:1000',
        ]);

        $schoolId = $this->resolveSchoolId($request);
        if ($event->school_id && $schoolId && (int)$event->school_id !== (int)$schoolId) {
            return response()->json(['success' => false, 'message' => 'Evento pertence a outra escola.'], 403);
        }

        $event->update([
            'title' => $data['title'],
            'start' => $data['start'],
            'end' => $data['end'] ?? null,
            'all_day' => (bool) ($data['all_day'] ?? false),
            'categoria' => $data['categoria'],
            'audiencia' => $data['audiencia'],
            'descricao' => $data['descricao'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove um evento.
     */
    public function destroy(Request $request, CalendarEvent $event): JsonResponse
    {
        $schoolId = $this->resolveSchoolId($request);
        if ($event->school_id && $schoolId && (int)$event->school_id !== (int)$schoolId) {
            return response()->json(['success' => false, 'message' => 'Evento pertence a outra escola.'], 403);
        }

        $event->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Resolve o school_id considerando usuário autenticado e sessão.
     */
    private function resolveSchoolId(Request $request): ?int
    {
        $user = $request->user();
        if ($user) {
            if (isset($user->school_id) && $user->school_id) return (int) $user->school_id;
            if (isset($user->escola_id) && $user->escola_id) return (int) $user->escola_id;
        }
        $sessionSchool = session('escola_atual');
        if ($sessionSchool) return (int) $sessionSchool;
        $schoolId = $request->input('school_id') ?? $request->input('escola_id');
        return $schoolId ? (int) $schoolId : null;
    }

    /**
     * Mapeia cor por categoria.
     */
    private function colorByCategory(?string $cat): string
    {
        $map = [
            'aula' => '#10b981',
            'feriado' => '#ef4444',
            'recesso' => '#f59e0b',
            'avaliacao' => '#8b5cf6',
            'evento' => '#3b82f6',
            'matricula' => '#ec4899',
        ];
        return $map[$cat ?? 'evento'] ?? '#3b82f6';
    }

    /**
     * Download do PDF anual com 12 meses e lista de eventos.
     */
    public function downloadAnnualPdf(Request $request)
    {
        $year = (int) ($request->query('ano') ?? date('Y'));
        $schoolId = $this->resolveSchoolId($request);

        $escolaNome = config('app.name');
        if ($schoolId) {
            $escola = Escola::find($schoolId);
            if ($escola && ($escola->nome ?? null)) {
                $escolaNome = $escola->nome;
            }
        }

        $events = CalendarEvent::query()
            ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
            ->whereYear('start', $year)
            ->orderBy('start')
            ->get();

        $eventsByCategoria = $events->groupBy('categoria');
        $eventsByMonth = $events->groupBy(function($ev) {
            return $ev->start ? $ev->start->month : ($ev->end ? $ev->end->month : null);
        });

        $colorMap = [
            'aula' => $this->colorByCategory('aula'),
            'feriado' => $this->colorByCategory('feriado'),
            'recesso' => $this->colorByCategory('recesso'),
            'avaliacao' => $this->colorByCategory('avaliacao'),
            'evento' => $this->colorByCategory('evento'),
            'matricula' => $this->colorByCategory('matricula'),
        ];

        $data = [
            'year' => $year,
            'escolaNome' => $escolaNome,
            'generatedAt' => now(),
            'events' => $events,
            'eventsByCategoria' => $eventsByCategoria,
            'eventsByMonth' => $eventsByMonth,
            'colorMap' => $colorMap,
        ];

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            try {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('calendario.pdf.anual', $data);
                $fileName = 'calendario_anual_' . $year . '.pdf';
                return $pdf->download($fileName);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF anual do calendário: ' . $e->getMessage());
                return response()->json(['error' => 'Falha ao gerar PDF.'], 500);
            }
        }

        $html = view('calendario.pdf.anual', $data)->render();
        $fileName = 'calendario_anual_' . $year . '.html';
        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}