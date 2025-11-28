@props([
    'name' => '',
    'label' => '',
    'type' => 'text',
    'placeholder' => '',
    'value' => '',
    'options' => [],
    'emptyOption' => 'Todos',
    'required' => false
])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    @if($type === 'select')
        <select 
            name="{{ $name }}" 
            id="{{ $name }}" 
            class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            {{ $required ? 'required' : '' }}
        >
            @if($emptyOption)
                <option value="">{{ $emptyOption }}</option>
            @endif
            @foreach($options as $optionValue => $optionLabel)
                <option 
                    value="{{ $optionValue }}" 
                    {{ request($name, $value) == $optionValue ? 'selected' : '' }}
                >
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @elseif($type === 'date')
        <input 
            type="date" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ request($name, $value) }}" 
            class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            {{ $required ? 'required' : '' }}
        >
    @elseif($type === 'number')
        <input 
            type="number" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ request($name, $value) }}" 
            placeholder="{{ $placeholder }}" 
            class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            {{ $required ? 'required' : '' }}
        >
    @else
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ request($name, $value) }}" 
            placeholder="{{ $placeholder }}" 
            class="block w-full px-4 py-3 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            {{ $required ? 'required' : '' }}
        >
    @endif
</div>