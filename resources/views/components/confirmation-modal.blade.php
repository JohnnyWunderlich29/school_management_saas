@props([
    'id' => 'confirmation-modal',
    'title' => 'Confirmação',
    'message' => 'Tem certeza que deseja continuar?',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'confirmColor' => 'red', // red, green, blue, yellow
    'showInput' => false,
    'inputLabel' => '',
    'inputPlaceholder' => '',
    'inputRequired' => false,
])

@php
    $confirmColors = [
        'red' => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'green' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
        'blue' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        'yellow' => 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
    ];
@endphp

<div x-data="{
    open: false,
    inputValue: '',
    callback: null,
    show(options = {}) {
        this.inputValue = '';
        this.callback = options.callback || null;
        this.open = true;
        if (options.inputValue) {
            this.inputValue = options.inputValue;
        }
        // Focus no input se existir
        this.$nextTick(() => {
            if (this.$refs.modalInput) {
                this.$refs.modalInput.focus();
            }
        });
    },
    hide() {
        this.open = false;
        this.callback = null;
        this.inputValue = '';
    },
    confirm() {
        @if($showInput && $inputRequired)
        if (!this.inputValue.trim()) {
            this.$refs.modalInput.focus();
            return;
        }
        @endif

        if (this.callback) {
            this.callback(this.inputValue);
        }
        this.hide();
    }
}" x-show="open"
    x-on:show-confirmation.window="if(!$event.detail.modalId || $event.detail.modalId == '{{ $id }}') show($event.detail)"
    x-on:keydown.escape.window="hide()" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
    id="{{ $id }}">

    <!-- Background overlay -->
    <div class="fixed inset-0 bg-transparent transition-opacity" x-show="open"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-on:click="hide()"></div>

    <!-- Modal panel -->
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div x-show="open" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <!-- Icon -->
                    <div
                        class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full {{ $confirmColor === 'red' ? 'bg-red-100' : ($confirmColor === 'green' ? 'bg-green-100' : 'bg-blue-100') }} sm:mx-0 sm:h-10 sm:w-10">
                        @if ($confirmColor === 'red')
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        @elseif($confirmColor === 'green')
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        @else
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                        @endif
                    </div>

                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">{{ $title }}</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">{{ $message }}</p>

                            @if ($showInput)
                                <div class="mt-4">
                                    @if ($inputLabel)
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-2">{{ $inputLabel }}</label>
                                    @endif
                                    <textarea x-ref="modalInput" x-model="inputValue" @if ($inputRequired) required @endif
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        rows="3" placeholder="{{ $inputPlaceholder }}" x-on:keydown.enter.ctrl="confirm()"></textarea>
                                    @if ($inputRequired)
                                        <p class="mt-1 text-xs text-gray-500">Este campo é obrigatório</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button" x-on:click="confirm()"
                    class="inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto {{ $confirmColors[$confirmColor] ?? $confirmColors['red'] }}">
                    {{ $confirmText }}
                </button>
                <button type="button" x-on:click="hide()"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    {{ $cancelText }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Função global para mostrar modal de confirmação
    window.showConfirmation = function(options = {}) {
        window.dispatchEvent(new CustomEvent('show-confirmation', {
            detail: options
        }));
    };

    // Função helper para confirmação simples
    window.confirmAction = function(message, callback, options = {}) {
        showConfirmation({
            ...options,
            callback: (inputValue) => {
                if (callback) callback(inputValue);
            }
        });
    };

    // Função helper para confirmação com input
    window.confirmWithInput = function(message, inputLabel, callback, options = {}) {
        showConfirmation({
            ...options,
            showInput: true,
            inputLabel: inputLabel,
            inputRequired: true,
            callback: (inputValue) => {
                if (callback) callback(inputValue);
            }
        });
    };
</script>
