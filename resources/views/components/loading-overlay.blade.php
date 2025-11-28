@props([
    'id' => null,
    'message' => null,
    'fullscreen' => false,
])

<!-- retirao da classe : bg-black bg-opacity-30-->
<div @if($id) id="{{ $id }}" @endif 
     class="{{ $fullscreen ? 'fixed inset-0' : 'absolute inset-0' }}  flex items-center justify-center z-50 hidden"
     data-loading-overlay>
    <!-- <div class="bg-white rounded-lg p-6 flex items-center space-x-3 shadow-lg"> -->
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
        {{-- @if($message)
            <span class="text-gray-700 font-medium">{{ $message }}</span>
        @endif --}}
    <!-- </div> -->
</div>