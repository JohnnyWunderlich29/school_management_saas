<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Calendário Anual {{ $year }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #111827; margin: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .title { font-size: 22px; font-weight: bold; }
        .subtitle { font-size: 14px; color: #374151; }
        .legend { display: flex; flex-wrap: wrap; gap: 8px; margin: 12px 0; }
        .legend-item { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; }
        .color { width: 12px; height: 12px; border-radius: 3px; display: inline-block; }
        .months { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; page-break-inside: avoid; }
        .month { border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; }
        .month-title { font-size: 14px; font-weight: bold; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #e5e7eb; padding: 4px; text-align: left; }
        th { background: #f9fafb; font-weight: 600; }
        .events-list { margin-top: 16px; }
        .events-list h3 { font-size: 14px; margin-bottom: 8px; }
        .event-item { margin-bottom: 6px; font-size: 12px; }
        .footer { position: fixed; bottom: 12px; left: 20px; right: 20px; font-size: 11px; color: #6b7280; text-align: right; }
        @page { margin: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">Calendário Anual {{ $year }}</div>
            <div class="subtitle">{{ $escolaNome }}</div>
        </div>
        <div class="subtitle">Título: Calendário Anual {{ $year }}</div>
    </div>

    <div class="legend">
        <div class="legend-item"><span class="color" style="background: {{ $colorMap['aula'] }}"></span> Aulas</div>
        <div class="legend-item"><span class="color" style="background: {{ $colorMap['feriado'] }}"></span> Feriados</div>
        <div class="legend-item"><span class="color" style="background: {{ $colorMap['recesso'] }}"></span> Recesso</div>
        <div class="legend-item"><span class="color" style="background: {{ $colorMap['avaliacao'] }}"></span> Avaliações</div>
        <div class="legend-item"><span class="color" style="background: {{ $colorMap['evento'] }}"></span> Eventos</div>
        <div class="legend-item"><span class="color" style="background: {{ $colorMap['matricula'] }}"></span> Matrículas</div>
    </div>

    <div class="months">
        @php
            $meses = [
                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
                7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
            ];
        @endphp
        @for ($m = 1; $m <= 12; $m++)
            <div class="month">
                <div class="month-title">{{ $meses[$m] }} / {{ $year }}</div>
                @php
                    $doMes = $eventsByMonth[$m] ?? collect();
                    $porDia = $doMes->groupBy(function($ev) { return $ev->start ? $ev->start->format('d') : ($ev->end ? $ev->end->format('d') : ''); });
                @endphp
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">Dia</th>
                            <th>Eventos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($porDia as $dia => $lista)
                            <tr>
                                <td>{{ $dia }}</td>
                                <td>
                                    @foreach ($lista as $ev)
                                        @php $cor = $colorMap[$ev->categoria] ?? '#3b82f6'; @endphp
                                        <span style="display:inline-flex; align-items:center; gap:6px; margin-right:10px;">
                                            <span class="color" style="background: {{ $cor }}"></span>
                                            {{ $ev->title }}
                                            @if (!$ev->all_day)
                                                ({{ optional($ev->start)->format('H:i') }}@if($ev->end) - {{ optional($ev->end)->format('H:i') }}@endif)
                                            @endif
                                        </span>
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" style="color:#9ca3af;">Sem eventos neste mês</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endfor
    </div>

    <div class="events-list">
        <h3>Lista de eventos por cor/categoria</h3>
        @foreach ($eventsByCategoria as $categoria => $lista)
            @php $cor = $colorMap[$categoria] ?? '#3b82f6'; @endphp
            <div class="event-item">
                <strong style="color: {{ $cor }};">{{ ucfirst($categoria) }}</strong>:
                @foreach ($lista as $ev)
                    <span style="margin-right: 10px;">
                        {{ optional($ev->start)->format('d/m') }} - {{ $ev->title }}
                    </span>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="footer">
        Relatório gerado em {{ $generatedAt->format('d/m/Y H:i') }}
    </div>
</body>
</html>