@props(['striped' => false, 'index' => 0])

<tr {{ $attributes->merge(['class' => $striped && $index % 2 === 1 ? 'bg-gray-50' : 'bg-white hover:bg-gray-50']) }}>
    {{ $slot }}
</tr>