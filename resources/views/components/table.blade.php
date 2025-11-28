@props([
    'headers' => [],
    'rows' => [],
    'actions' => false,
    'striped' => true,
    'hover' => true,
    'responsive' => true,
    'sortable' => false,
    'currentSort' => null,
    'currentDirection' => 'asc',
])

<div {{ $attributes->merge(['class' => $responsive ? 'overflow-x-auto' : '']) }}>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @foreach ($headers as $header)
                    @php
                        $isConfig = is_array($header);
                        $label = $isConfig ? ($header['label'] ?? '') : $header;
                        $sortKey = $isConfig ? ($header['sort'] ?? null) : null;
                        $isActive = $sortable && $sortKey && $currentSort === $sortKey;
                        $nextDir = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
                    @endphp
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        @if($sortable && $sortKey)
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $sortKey, 'direction' => $nextDir, 'page' => 1]) }}" class="group inline-flex items-center text-gray-700 hover:text-gray-900">
                                <span>{{ $label }}</span>
                                <span class="ml-2 text-gray-400 group-hover:text-gray-500">
                                    @if($isActive)
                                        @if($currentDirection === 'asc')
                                            <i class="fas fa-arrow-up"></i>
                                        @else
                                            <i class="fas fa-arrow-down"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </span>
                            </a>
                        @else
                            {{ $label }}
                        @endif
                    </th>
                @endforeach
                
                @if ($actions)
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                @endif
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            {{ $slot }}
        </tbody>
    </table>
</div>