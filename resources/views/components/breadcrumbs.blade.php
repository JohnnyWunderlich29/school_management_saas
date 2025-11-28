@props(['items' => []])

@if(count($items) > 0)
<nav class="flex mb-8" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2">
        <!-- Home/Dashboard -->
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-indigo-600 inline-flex items-center transition duration-200">
                <svg class="w-5 h-5 mr-2.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>
                Home
            </a>
        </li>
        
        @foreach($items as $index => $item)
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    @if($loop->last)
                        <span class="text-gray-500 ml-1 md:ml-2 text-sm font-medium" aria-current="page">{{ $item['title'] }}</span>
                    @else
                        @php
                            if (isset($item['url'])) {
                                $url = $item['url'];
                            } elseif (isset($item['route'])) {
                                $params = isset($item['params']) ? $item['params'] : [];
                                $url = route($item['route'], $params);
                            } else {
                                $url = '#';
                            }
                        @endphp
                        <a href="{{ $url }}" class="text-gray-700 hover:text-indigo-600 ml-1 md:ml-2 text-sm font-medium transition duration-200">
                            {{ $item['title'] }}
                        </a>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
@endif