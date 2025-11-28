@props([
    'type' => 'text',
    'name' => null,
    'id' => null,
    'value' => null,
    'label' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => null,
])

@php
    // Resolve o name a partir do prop explícito, atributo HTML ou fallback para id
    $name = $name ?? $attributes->get('name') ?? $id;
    $id = $id ?? $name;

    // Verifica erros apenas quando name estiver presente
    $hasError = $error || (!empty($name) && isset($errors) && $errors->has($name));
    $errorMessage = $error ?? (!empty($name) && isset($errors) ? $errors->first($name) : null);
@endphp

<div class="mb-4">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if ($required)
                <span class="text-red-600">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-md shadow-sm">
        @php
            $__rawValue = old($name, $value);
            if (is_array($__rawValue)) {
                // Evita erro do htmlspecialchars com array; converte para string segura
                $__rawValue = implode(', ', array_map(function($v){ return is_scalar($v) ? (string)$v : ''; }, $__rawValue));
            } elseif (is_object($__rawValue) && method_exists($__rawValue, '__toString')) {
                $__rawValue = (string)$__rawValue;
            } elseif (!is_scalar($__rawValue) && !is_null($__rawValue)) {
                $__rawValue = '';
            }
        @endphp
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $id }}" 
            value="{{ $__rawValue }}" 
            placeholder="{{ $placeholder }}" 
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge([
                'class' => 'block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm' . 
                ($hasError ? ' border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500' : '')
            ]) }}
        >
        {{-- Ícones/ações overlay dentro do campo --}}
        {{ $slot ?? '' }}
    </div>

    @if ($hasError)
        <p class="mt-1 text-sm text-red-600">{{ $errorMessage }}</p>
    @endif

    @if ($help)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif
</div>