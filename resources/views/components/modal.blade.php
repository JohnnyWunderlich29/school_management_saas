@props([
    'name' => null,
    'show' => false,
    'title' => 'Modal',
    'maxWidth' => 'w-11/12 md:w-3/4 lg:w-1/2',
    'id' => 'modal',
    'closable' => true,
    'footer' => null
])

@php
$id = $name ?? $id;
@endphp

<!-- Modal Overlay (padrão /settings "Novo Gateway") -->
<div 
    x-data="{ show: {{ $show ? 'true' : 'false' }}, overlay: {{ $show ? 'true' : 'false' }} }"
    x-init="overlay = show"
    x-on:open-modal.window="if($event.detail == '{{ $id }}' || ($event.detail && $event.detail.id == '{{ $id }}')) { overlay = true; show = true; }"
    x-on:close.window="show = false; setTimeout(() => overlay = false, 200)"
    x-on:close-modal.window="show = false; setTimeout(() => overlay = false, 200)"
    x-on:keydown.escape.window="show = false; setTimeout(() => overlay = false, 200)"
    x-show="overlay"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
    style="display: none;"
>
    <div 
        class="relative top-20 w-full {{ $maxWidth }} bg-white rounded-md shadow-lg border p-5"
        x-show="show"
        x-on:click.away="{{ $closable ? 'show = false; setTimeout(() => overlay = false, 200)' : '' }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <!-- Modal Header (padrão /settings) -->
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200">
            {{-- Cabeçalho customizável via named slot `header`; fallback para título padrão --}}
            {{-- Se o slot "header" for usado, ele sobrescreve o título padrão --}}
            @isset($header)
                {{ $header }}
            @else
                <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
            @endisset
            @if($closable)
                <button 
                    type="button" 
                    x-on:click="show = false; setTimeout(() => overlay = false, 200)" 
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>

        <!-- Modal Body -->
        <div class="max-h-[70vh] overflow-y-auto">
            {{ $slot }}
        </div>

        <!-- Modal Footer (sem fundo, alinhado à direita) -->
        @if($footer)
            <div class="flex items-center justify-end pt-4 space-x-3">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>

<script>
// Compatibilidade com o código antigo
function showModal(modalId) {
    window.dispatchEvent(new CustomEvent('open-modal', { detail: modalId }));
}

function closeModal(modalId) {
    window.dispatchEvent(new CustomEvent('close-modal'));
}
</script>