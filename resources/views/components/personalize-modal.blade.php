<div id="{{ $id }}" class="fixed inset-0 bg-black bg-opacity-30 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm sm:max-w-lg p-4 sm:p-6 my-6 sm:my-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            <button class="text-gray-500 hover:text-gray-700" data-action="close-modal" aria-label="Fechar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="space-y-3 max-h-[65vh] sm:max-h-[70vh] overflow-y-auto">
            {{ $body ?? $slot ?? '' }}
        </div>

        <div class="mt-6 flex items-center justify-between">
            <div class="space-x-2 flex items-center flex-wrap gap-2">
                {{ $footerLeft ?? '' }}
            </div>
            <div class="flex items-center justify-end space-x-3">
                @isset($errorId)
                    <span id="{{ $errorId }}" class="mr-auto text-xs text-red-600 hidden"></span>
                @endisset
                {{ $footerRight ?? '' }}
            </div>
        </div>
    </div>
</div>