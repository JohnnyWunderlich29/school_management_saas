@php
    $statusConfig = [
        'rascunho' => [
            'color' => 'gray',
            'icon' => 'fas fa-edit',
            'text' => 'Rascunho',
        ],
        'revisao' => [
            'color' => 'yellow',
            'icon' => 'fas fa-clock',
            'text' => 'Aguardando aprovação',
        ],
        'finalizado' => [
            'color' => 'yellow',
            'icon' => 'fas fa-clock',
            'text' => 'Aguardando Aprovação',
        ],
        'aprovado' => [
            'color' => 'green',
            'icon' => 'fas fa-check-circle',
            'text' => 'Aprovado',
        ],
        'rejeitado' => [
            'color' => 'red',
            'icon' => 'fas fa-exclamation-circle',
            'text' => 'Correção Solicitada',
        ],
    ];

    $config = $statusConfig[$status] ?? $statusConfig['rascunho'];

    $colorClasses = [
        'gray' => 'bg-gray-100 text-gray-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'green' => 'bg-green-100 text-green-800',
        'red' => 'bg-red-100 text-red-800',
    ];
@endphp

<span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClasses[$config['color']] }}">
    <i class="{{ $config['icon'] }} mr-1"></i>
    {{ $config['text'] }}
</span>
