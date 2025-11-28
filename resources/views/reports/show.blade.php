@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Relatórios', 'url' => route('reports.index')],
    ['title' => $report->name, 'url' => '#']
]" />

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $report->name }}</h1>
            <p class="text-gray-600">{{ $report->description ?? 'Relatório gerado automaticamente pelo sistema' }}</p>
        </div>
        <div class="flex space-x-3">
            @if($report->status === 'completed' && $report->file_path && !$report->isExpired())
                <x-button
                    color="primary"
                    href="{{ route('reports.download', $report) }}"
                >
                    <i class="fas fa-download mr-2"></i>
                    Download
                </x-button>
            @endif
            <x-button
                color="secondary"
                href="{{ route('reports.index') }}"
            >
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Informações do Relatório -->
        <div class="lg:col-span-1">
            <x-card>
                <x-slot name="title">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                        Informações do Relatório
                    </div>
                </x-slot>
                
                <div class="space-y-4">
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Tipo:</span>
                        <span class="text-gray-900 capitalize">{{ $report->type }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($report->status === 'completed') bg-green-100 text-green-800
                            @elseif($report->status === 'processing') bg-yellow-100 text-yellow-800
                            @elseif($report->status === 'pending') bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800 @endif">
                            @if($report->status === 'completed') Concluído
                            @elseif($report->status === 'processing') Processando
                            @elseif($report->status === 'pending') Pendente
                            @else Falhou @endif
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Criado em:</span>
                        <span class="text-gray-900">{{ $report->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    @if($report->updated_at != $report->created_at)
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Atualizado em:</span>
                        <span class="text-gray-900">{{ $report->updated_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    @endif
                    @if($report->filters)
                    <div class="pt-2">
                        <span class="font-medium text-gray-700 block mb-2">Filtros Aplicados:</span>
                        <div class="bg-gray-50 rounded-lg p-3">
                            @foreach($report->filters as $key => $value)
                                @if($value)
                                    <div class="text-sm text-gray-600 mb-1">
                                        <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> {{ $value }}
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </x-card>
        </div>

        <!-- Conteúdo do Relatório -->
        <div class="lg:col-span-2">
            @if($report->status === 'completed' && $report->data)
                <x-card>
                    <x-slot name="title">
                        <div class="flex items-center">
                            <i class="fas fa-chart-bar text-indigo-600 mr-2"></i>
                            Resumo dos Dados
                        </div>
                    </x-slot>
                    
                    <div class="space-y-6">
                        @switch($report->type)
                            @case('attendance')
                                @include('reports.partials.attendance-summary', ['data' => $report->data])
                                @break
                            @case('schedule')
                                @include('reports.partials.schedule-summary', ['data' => $report->data])
                                @break
                            @case('performance')
                                @include('reports.partials.performance-summary', ['data' => $report->data])
                                @break
                            @case('financial')
                                @include('reports.partials.financial-summary', ['data' => $report->data])
                                @break
                        @endswitch
                    </div>
                </x-card>
            @endif

            @if($report->status === 'processing')
                <x-card>
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-spinner fa-spin text-2xl text-yellow-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Processando Relatório</h3>
                        <p class="text-gray-600 mb-4">Seu relatório está sendo gerado. Isso pode levar alguns minutos.</p>
                        <div class="bg-yellow-50 rounded-lg p-4 text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            Esta página será atualizada automaticamente quando o relatório estiver pronto.
                        </div>
                    </div>
                </x-card>
            @endif

            @if($report->status === 'pending')
                <x-card>
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto bg-blue-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-clock text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Relatório Pendente</h3>
                        <p class="text-gray-600 mb-4">Seu relatório está na fila para processamento.</p>
                        <div class="bg-blue-50 rounded-lg p-4 text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            O processamento iniciará em breve. Você será notificado quando estiver concluído.
                        </div>
                    </div>
                </x-card>
            @endif

            @if($report->status === 'failed')
                <x-card>
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Erro no Processamento</h3>
                        <p class="text-gray-600 mb-4">Ocorreu um erro ao gerar o relatório.</p>
                        <div class="bg-red-50 rounded-lg p-4 text-sm text-red-800 mb-4">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            Por favor, tente gerar o relatório novamente ou entre em contato com o suporte.
                        </div>
                        <x-button
                            color="primary"
                            href="{{ route('reports.create') }}"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            Gerar Novo Relatório
                        </x-button>
                    </div>
                </x-card>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($report->status === 'processing')
<script>
    // Auto-refresh para relatórios em processamento
    setTimeout(function() {
        window.location.reload();
    }, 30000); // Atualiza a cada 30 segundos
</script>
@endif
@endpush