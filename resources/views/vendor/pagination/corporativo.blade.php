@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegação de paginação" class="flex items-center justify-end space-x-1 text-sm select-none">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed" aria-disabled="true" aria-label="Anterior">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50" aria-label="Anterior">‹</a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="px-3 py-2 text-gray-500">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-2 rounded-lg bg-blue-600 text-white" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50" aria-label="Próxima">›</a>
        @else
            <span class="px-3 py-2 rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed" aria-disabled="true" aria-label="Próxima">›</span>
        @endif
    </nav>
@endif