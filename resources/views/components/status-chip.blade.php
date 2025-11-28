@props([
    'status',
    'label' => null,
])

@php
    $map = [
        // Empréstimos
        'ativo' => 'bg-indigo-100 text-indigo-800',
        'devolvido' => 'bg-green-100 text-green-800',
        'renovado' => 'bg-sky-100 text-sky-800',
        'atrasado' => 'bg-red-100 text-red-800',

        // Reservas
        'ativa' => 'bg-indigo-100 text-indigo-800',
        'processada' => 'bg-green-100 text-green-800',
        'cancelada' => 'bg-red-100 text-red-800',
        'expirada' => 'bg-yellow-100 text-yellow-800',

        // Multas
        'pendente' => 'bg-yellow-100 text-yellow-800',
        'paga' => 'bg-green-100 text-green-800',

        // Prioridades
        'alta' => 'bg-red-100 text-red-800',
        'media' => 'bg-yellow-100 text-yellow-800',
        'baixa' => 'bg-sky-100 text-sky-800',

        // Info genérico
        'info' => 'bg-blue-100 text-blue-800',
    ];

    $normalized = is_string($status) ? strtolower($status) : $status;
    $classes = $map[$normalized] ?? 'bg-indigo-100 text-indigo-800';
    $labelText = $label ?? (is_string($status) ? ucfirst($status) : $status);
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium $classes"]) }}>
    {{ $labelText }}
</span>