@props([
    'title' => null,
    'subtitle' => null,
    'footer' => null,
    'padding' => true,
    'headerActions' => null,
    'tone' => 'default', // default | report
])

@php
    $baseClasses = match($tone) {
        'report' => 'bg-slate-50 border border-slate-200 rounded-lg shadow-sm overflow-hidden',
        default => 'bg-white shadow rounded-lg overflow-hidden',
    };
    $headerBorder = $tone === 'report' ? 'border-b border-slate-200' : 'border-b border-gray-200';
    $footerBorder = $tone === 'report' ? 'bg-slate-100 border-t border-slate-200' : 'bg-gray-50 border-t border-gray-200';
@endphp

<div {{ $attributes->merge(['class' => $baseClasses]) }}>
    @if ($title || $subtitle || $headerActions)
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center {{ $headerBorder }}">
            <div>
                @if ($title)
                    <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $title }}</h3>
                @endif
                
                @if ($subtitle)
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
            
            @if ($headerActions)
                <div class="flex space-x-2">
                    {{ $headerActions }}
                </div>
            @endif
        </div>
    @endif
    
    <div @class(['px-4 py-5 sm:p-6' => $padding])>
        {{ $slot }}
    </div>
    
    @if ($footer)
        <div class="px-4 py-4 sm:px-6 {{ $footerBorder }}">
            {{ $footer }}
        </div>
    @endif
</div>