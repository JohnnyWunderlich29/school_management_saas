@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Relatórios', 'url' => route('reports.index')],
    ['title' => $report->name, 'url' => '#']
]" />

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8">
        <!-- Informações do Relatório (60% da tela) -->
        <div class="lg:col-span-3">
            <div class="bg-white shadow rounded-lg p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $report->name }}</h1>
                    <p class="text-gray-600 mt-1">{{ $report->description ?? 'Relatório gerado automaticamente pelo sistema' }}</p>
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

            <!-- Informações Básicas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-bar text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Tipo</p>
                            <p class="text-lg font-semibold text-gray-900 capitalize">{{ $report->type }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Concluído
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calendar text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Criado em</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $report->created_at->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-500">Formato</p>
                            <p class="text-lg font-semibold text-gray-900 uppercase">{{ $report->format }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros Aplicados -->
            @if($report->filters)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Filtros Aplicados</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($report->filters as $key => $value)
                        @if($value)
                            <div class="bg-blue-50 rounded-lg p-3">
                                <p class="text-sm font-medium text-blue-900">{{ ucfirst(str_replace('_', ' ', $key)) }}</p>
                                <p class="text-sm text-blue-700">{{ $value }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif</div>
        </div>
        
        <!-- Espaço adicional (40% da tela) -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-center py-8">
                    <i class="fas fa-info-circle text-indigo-600 text-3xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Informações Adicionais</h3>
                    <p class="text-gray-600">Esta área pode ser utilizada para exibir estatísticas adicionais ou informações complementares do relatório.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dados do Relatório -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">
                <i class="fas fa-table mr-2 text-indigo-600"></i>
                Dados do Relatório
            </h2>
        </div>
        
        <div class="p-6">
            @switch($report->type)
                @case('attendance')
                    @include('reports.partials.attendance-table', ['data' => $report->data])
                    @break
                @case('schedule')
                    @include('reports.partials.schedule-table', ['data' => $report->data])
                    @break
                @case('performance')
                    @include('reports.partials.performance-table', ['data' => $report->data])
                    @break
                @case('financial')
                    @include('reports.partials.financial-table', ['data' => $report->data])
                    @break
                @default
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mb-4"></i>
                        <p class="text-gray-600">Tipo de relatório não suportado para visualização em tabela.</p>
                    </div>
            @endswitch
        </div>
    </div>
</div>
@endsection