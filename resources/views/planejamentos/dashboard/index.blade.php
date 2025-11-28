@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Planejamentos', 'url' => '#']
]" />

<div class="space-y-6">
    <!-- Header com ações principais -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Planejamentos de Aula</h1>
            <p class="text-gray-600">Gerencie e acompanhe todos os planejamentos pedagógicos</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            @permission('planejamentos.criar')
                <!-- Novo Wizard Unificado -->
                <x-button href="{{ route('planejamentos.wizard') }}" color="primary" class="inline-flex items-center">
                    <i class="fas fa-magic mr-2"></i>
                    Wizard Inteligente
                </x-button>
                
                <!-- Criação Tradicional -->
                <x-button href="{{ route('planejamentos.create') }}" color="secondary" class="inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Modo Tradicional
                </x-button>
            @endpermission
            
            <!-- Dropdown de Relatórios -->
            <div class="relative" x-data="{ open: false }">
                <x-button color="secondary" class="inline-flex items-center" @click="open = !open">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Relatórios
                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </x-button>
                
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-10">
                    <div class="py-1">
                        <a href="#" onclick="window.print()" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-print mr-2"></i>
                            Imprimir Lista
                        </a>
                        <a href="{{ route('planejamentos.export', ['format' => 'pdf']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-pdf mr-2"></i>
                            Exportar PDF
                        </a>
                        <a href="{{ route('planejamentos.export', ['format' => 'excel']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-file-excel mr-2"></i>
                            Exportar Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total de Planejamentos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Planejamentos Ativos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Aprovados</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['aprovados'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Pendentes de Aprovação -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pendentes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pendentes'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Rascunhos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-gray-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rascunhos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['rascunhos'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Avançados -->
    <x-card>
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                Filtros
            </h3>
        </div>

        <form method="GET" action="{{ route('planejamentos.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Modalidade -->
                <div>
                    <label for="modalidade" class="block text-sm font-medium text-gray-700 mb-1">Modalidade</label>
                    <select name="modalidade" id="modalidade" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas as modalidades</option>
                        @foreach(\App\Models\Planejamento::getModalidadesOptions() as $key => $value)
                            <option value="{{ $key }}" {{ request('modalidade') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Turno -->
                <div>
                    <label for="turno" class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                    <select name="turno" id="turno" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os turnos</option>
                        @foreach(\App\Models\Planejamento::getTurnosOptions() as $key => $value)
                            <option value="{{ $key }}" {{ request('turno') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os status</option>
                        <option value="rascunho" {{ request('status') == 'rascunho' ? 'selected' : '' }}>Rascunho</option>
                        <option value="finalizado" {{ request('status') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                        <option value="aprovado" {{ request('status') == 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                    </select>
                </div>

                <!-- Período -->
                <div>
                    <label for="periodo" class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                    <select name="periodo" id="periodo" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os períodos</option>
                        <option value="atual" {{ request('periodo') == 'atual' ? 'selected' : '' }}>Período Atual</option>
                        <option value="proximo" {{ request('periodo') == 'proximo' ? 'selected' : '' }}>Próximo Período</option>
                        <option value="anterior" {{ request('periodo') == 'anterior' ? 'selected' : '' }}>Período Anterior</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <x-button type="submit" color="primary" class="inline-flex items-center">
                    <i class="fas fa-search mr-2"></i>
                    Filtrar
                </x-button>
                
                <x-button type="button" color="secondary" class="inline-flex items-center" onclick="window.location.href='{{ route('planejamentos.index') }}'">
                    <i class="fas fa-times mr-2"></i>
                    Limpar Filtros
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- Lista de Planejamentos -->
    <x-card>
        <div class="border-b border-gray-200 pb-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    Planejamentos
                </h3>
                
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">
                        {{ $planejamentos->total() }} {{ $planejamentos->total() == 1 ? 'resultado' : 'resultados' }}
                    </span>
                </div>
            </div>
        </div>

        @if($planejamentos->count() > 0)
            <!-- Tabela Responsiva -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Planejamento
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Modalidade
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Período
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Professor
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($planejamentos as $planejamento)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $planejamento->titulo ?: 'Planejamento #' . $planejamento->id }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $planejamento->turma ? $planejamento->turma->nome : 'N/A' }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $planejamento->modalidade_formatada }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($planejamento->data_inicio && $planejamento->data_fim)
                                        {{ $planejamento->data_inicio->format('d/m/Y') }} - {{ $planejamento->data_fim->format('d/m/Y') }}
                                    @else
                                        {{ $planejamento->numero_dias }} {{ $planejamento->numero_dias == 1 ? 'dia' : 'dias' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                            @include('planejamentos.components.status-badge', ['status' => $planejamento->status_efetivo])
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $planejamento->user->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Dropdown de Visualização -->
                                        <div class="relative" x-data="{ open: false }">
                                            <x-button color="secondary" size="sm" @click="open = !open" class="inline-flex items-center">
                                                <i class="fas fa-eye mr-1"></i>
                                                <i class="fas fa-chevron-down text-xs"></i>
                                            </x-button>
                                            
                                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-10">
                                                <div class="py-1">
                                                    <a href="{{ route('planejamentos.view', $planejamento) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-eye mr-2"></i>
                                                        Visualização Completa
                                                    </a>
                                                    <a href="{{ route('planejamentos.show', $planejamento) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-list mr-2"></i>
                                                        Visualização Simples
                                                    </a>
                                                    <a href="{{ route('planejamentos.preview', $planejamento) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-search mr-2"></i>
                                                        Pré-visualização
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @can('update', $planejamento)
                                            @if($planejamento->status_efetivo === 'rascunho')
                                                <x-button href="{{ route('planejamentos.wizard', ['edit' => $planejamento->id]) }}" color="primary" size="sm" title="Continuar planejamento">
                                                    <i class="fas fa-edit"></i>
                                                </x-button>
                                            @endif
                                        @endcan
                                        
                                        <!-- Dropdown de Ações -->
                                        <div class="relative" x-data="{ open: false }">
                                            <x-button color="secondary" size="sm" @click="open = !open" class="inline-flex items-center">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </x-button>
                                            
                                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-10">
                                                <div class="py-1">
                                                    <a href="{{ route('planejamentos.export', [$planejamento, 'format' => 'pdf']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-file-pdf mr-2"></i>
                                                        Exportar PDF
                                                    </a>
                                                    <a href="{{ route('planejamentos.export', [$planejamento, 'format' => 'docx']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <i class="fas fa-file-word mr-2"></i>
                                                        Exportar Word
                                                    </a>
                                                    @can('update', $planejamento)
                                                        @if($planejamento->status_efetivo === 'rascunho')
                                                            <a href="{{ route('planejamentos.wizard', ['edit' => $planejamento->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                                <i class="fas fa-magic mr-2"></i>
                                                                Editar com Wizard
                                                            </a>
                                                        @endif
                                                    @endcan
                                                    @permission('planejamentos.excluir')
                                                        @if($planejamento->user_id === Auth::id() || Auth::user()->isSuperAdmin())
                                                            <hr class="my-1">
                                                            <form method="POST" action="{{ route('planejamentos.destroy', $planejamento) }}" class="inline w-full" onsubmit="return confirm('Tem certeza que deseja excluir este planejamento?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                                    <i class="fas fa-trash mr-2"></i>
                                                                    Excluir
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endpermission
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="mt-6">
                {{ $planejamentos->links('components.pagination') }}
            </div>
        @else
            <!-- Estado Vazio -->
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum planejamento encontrado</h3>
                <p class="text-gray-600 mb-6">
                    @if(request()->hasAny(['modalidade', 'turno', 'status', 'periodo']))
                        Tente ajustar os filtros ou criar um novo planejamento.
                    @else
                        Comece criando seu primeiro planejamento de aula.
                    @endif
                </p>
                
                @permission('planejamentos.criar')
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <x-button href="{{ route('planejamentos.wizard') }}" color="primary" class="inline-flex items-center">
                            <i class="fas fa-magic mr-2"></i>
                            Usar Wizard Inteligente
                        </x-button>
                        <x-button href="{{ route('planejamentos.create') }}" color="secondary" class="inline-flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Modo Tradicional
                        </x-button>
                    </div>
                @endpermission
            </div>
        @endif
    </x-card>
</div>
@endsection