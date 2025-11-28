@props([
    'items' => [], // [['title' => '', 'date' => Carbon|string|null, 'description' => null, 'markerColor' => 'blue']]
    'lineColor' => 'gray-200',
])

@php
    use Illuminate\Support\Carbon as SupportCarbon;
    $normalizeDate = function($date) {
        if ($date instanceof SupportCarbon || $date instanceof \Carbon\Carbon) {
            return $date->format('d/m/Y H:i:s');
        }
        return $date;
    };
@endphp

<div class="relative pl-6">
    <div class="absolute left-2 top-0 bottom-0 border-l border-{{ $lineColor }}"></div>
    <div class="space-y-4">
        @foreach($items as $item)
            @php
                $color = $item['markerColor'] ?? 'gray';
                $title = $item['title'] ?? '';
                $date = $normalizeDate($item['date'] ?? null);
                $description = $item['description'] ?? null;
            @endphp
            <div class="relative">
                <span class="absolute -left-4 top-1 w-3 h-3 rounded-full bg-{{ $color }}-500 border-2 border-white shadow"></span>
                <div class="bg-gray-50 rounded p-3">
                    @if($title)
                        <div class="text-sm font-semibold text-gray-900">{{ $title }}</div>
                    @endif
                    @if($date)
                        <div class="text-xs text-gray-600">{{ $date }}</div>
                    @endif
                    @if($description)
                        <div class="text-xs text-gray-500 mt-1">{{ $description }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>