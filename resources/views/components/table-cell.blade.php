@props([
    'align' => 'left',
    'header' => false,
])

@php
    $alignClass = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ][$align] ?? 'text-left';
    
    $baseClasses = $header 
        ? "px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider $alignClass"
        : "px-6 py-4 whitespace-nowrap text-sm text-gray-500 $alignClass";
@endphp

@if($header)
    <th scope="col" {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </th>
@else
    <td {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </td>
@endif