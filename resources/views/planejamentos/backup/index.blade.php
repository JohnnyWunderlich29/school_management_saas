@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="container mx-auto px-4 py-6">
            <x-breadcrumbs :items="[['title' => 'Planejamentos', 'url' => '#']]" />

            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Planejamentos</h1>
                        <p class="mt-2 text-sm text-gray-600">Gerencie os planejamentos de aula da sua instituição</p>
                    </div>
                    @permission('planejamentos.criar')
                        <div class="mt-4 sm:mt-0">
                            <x-button href="{{ route('planejamentos.create') }}" color="primary"
                                class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="hidden sm:inline">Novo Planejamento</span>
                                <span class="sm:hidden">Novo</span>
                            </x-button>
                        </div>
                    @endpermission
                </div>
            </div>

            <x-card class="shadow-lg border-0">

                <!-- Filtros -->
                <x-collapsible-filter title="Filtros de Busca" :action="route('planejamentos.index')" :clear-route="route('planejamentos.index')">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <x-filter-field name="modalidade" label="Modalidade" type="select"
                            empty-option="Todas as modalidades" :options="collect(\App\Models\Planejamento::getModalidadesOptions())" />

                        <x-filter-field name="turno" label="Turno" type="select" empty-option="Todos os turnos"
                            :options="collect(\App\Models\Planejamento::getTurnosOptions())" />

                        <x-filter-field name="status" label="Status" type="select" empty-option="Todos os status"
                            :options="collect([
                                'rascunho' => 'Rascunho',
                                'finalizado' => 'Finalizado',
                                'aprovado' => 'Aprovado',
                            ])" />

                        <x-date-filter-with-arrows name="data_inicio" label="Data Início" :value="$dataInicio"
                            data-fim-name="data_fim" :data-fim-value="$dataFim" />
                    </div>
                </x-collapsible-filter>


                <!-- Lista de Planejamentos -->
                @if ($planejamentos->count() > 0)
                    <!-- Desktop Table -->
                    <div class="hidden lg:block">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <x-table>
                                <x-table-header>
                                    <x-table-row>
                                        <x-table-cell header>Título / Modalidade</x-table-cell>
                                        <x-table-cell header>Turno / Turma</x-table-cell>
                                        <x-table-cell header>Professor</x-table-cell>
                                        <x-table-cell header>Período</x-table-cell>
                                        <x-table-cell header>Status</x-table-cell>
                                        <x-table-cell header>Criado em</x-table-cell>
                                        <x-table-cell header class="text-center">Ações</x-table-cell>
                                    </x-table-row>
                                </x-table-header>
                                <x-table-body>

                                    @foreach ($planejamentos as $planejamento)
                                        <x-table-row>
                                            <x-table-cell>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $planejamento->titulo ?: 'Planejamento #' . $planejamento->id }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $planejamento->modalidade_formatada }}
                                                    </div>
                                                </div>
                                            </x-table-cell>
                                            <x-table-cell>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $planejamento->turno_formatado }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $planejamento->turma ? $planejamento->turma->nome : 'N/A' }}
                                                    </div>
                                                </div>
                                            </x-table-cell>
                                            <x-table-cell>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $planejamento->user ? $planejamento->user->name : 'N/A' }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $planejamento->tipo_professor_formatado }}
                                                </div>
                                            </x-table-cell>
                                            <x-table-cell>
                                                <div class="text-sm">
                                                    <div class="font-medium text-gray-900">{{ $planejamento->numero_dias }}
                                                        {{ $planejamento->numero_dias == 1 ? 'dia' : 'dias' }}</div>
                                                    <div class="text-gray-500">
                                                        {{ $planejamento->data_inicio ? $planejamento->data_inicio->format('d/m/Y') : 'N/A' }}
                                                        @if ($planejamento->data_fim)
                                                            - {{ $planejamento->data_fim->format('d/m/Y') }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </x-table-cell>
                                            <x-table-cell>
                                                @php
                                                    $statusColors = [
                                                        'rascunho' => 'bg-yellow-100 text-yellow-800',
                                                        'finalizado' => 'bg-blue-100 text-blue-800',
                                                        'aprovado' => 'bg-green-100 text-green-800',
                                                    ];
                                                @endphp
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$planejamento->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $planejamento->status_formatado }}
                                                </span>
                                            </x-table-cell>
                                            <x-table-cell>
                                                <div class="text-sm text-gray-900">
                                                    {{ $planejamento->created_at->format('d/m/Y') }}</div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $planejamento->created_at->format('H:i') }}</div>
                                            </x-table-cell>
                                            <x-table-cell class="text-center">
                                                <div class="flex items-center justify-center space-x-3">
                                                    @permission('planejamentos.visualizar')
                                                        <a href="{{ route('planejamentos.show', $planejamento) }}"
                                                            class="inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium rounded-md transition-colors duration-200"
                                                            title="Visualizar">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                                </path>
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                                </path>
                                                            </svg>
                                                            Ver
                                                        </a>
                                                    @endpermission

                                                    @permission('planejamentos.editar')
                                                        @if(in_array($planejamento->status, ['rascunho', 'finalizado']) && ($planejamento->user_id === Auth::id() || Auth::user()->isSuperAdmin()))
                                                            <a href="{{ route('planejamentos.detalhado', $planejamento) }}"
                                                                class="inline-flex items-center px-3 py-1.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-xs font-medium rounded-md transition-colors duration-200"
                                                                title="Editar Detalhes">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                    </path>
                                                                </svg>
                                                                Editar
                                                            </a>
                                                        @endif
                                                    @endpermission

                                                    @permission('planejamentos.excluir')
                                                        @if($planejamento->status === 'rascunho' && ($planejamento->user_id === Auth::id() || Auth::user()->isSuperAdmin()))
                                                            <form action="{{ route('planejamentos.destroy', $planejamento) }}"
                                                                method="POST" class="inline"
                                                                onsubmit="return confirm('Tem certeza que deseja excluir este planejamento?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-md transition-colors duration-200"
                                                                    title="Excluir">
                                                                    <svg class="w-3 h-3 mr-1" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                        </path>
                                                                    </svg>
                                                                    Excluir
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endpermission
                                                </div>
                                            </x-table-cell>
                                        </x-table-row>
                                    @endforeach
                                </x-table-body>
                            </x-table>
                        </div>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="lg:hidden space-y-4">
                        @foreach ($planejamentos as $planejamento)
                            <x-card class="hover:shadow-lg transition-all duration-200">
                                <!-- Header do Card -->
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 text-lg leading-tight">
                                            {{ $planejamento->titulo ?: 'Planejamento #' . $planejamento->id }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ $planejamento->modalidade_formatada }}
                                        </p>
                                    </div>
                                    <div class="ml-3">
                                        @php
                                            $statusColors = [
                                                'rascunho' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                'finalizado' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'aprovado' => 'bg-green-100 text-green-800 border-green-200',
                                            ];
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium border {{ $statusColors[$planejamento->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                                            {{ $planejamento->status_formatado }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Informações do Planejamento -->
                                <div class="space-y-3 mb-5">
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600 font-medium">Turno</span>
                                        <span
                                            class="text-sm text-gray-900 font-semibold">{{ $planejamento->turno_formatado }}</span>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600 font-medium">Turma</span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $planejamento->turma ? $planejamento->turma->nome : 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-600 font-medium">Período</span>
                                        <span class="text-sm text-gray-900 font-semibold">{{ $planejamento->numero_dias }}
                                            {{ $planejamento->numero_dias == 1 ? 'dia' : 'dias' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between py-2">
                                        <span class="text-sm text-gray-600 font-medium">Datas</span>
                                        <div class="text-right">
                                            <div class="text-sm text-gray-900 font-semibold">
                                                {{ $planejamento->data_inicio ? $planejamento->data_inicio->format('d/m/Y') : 'N/A' }}
                                            </div>
                                            @if ($planejamento->data_fim)
                                                <div class="text-xs text-gray-500">
                                                    até {{ $planejamento->data_fim->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Ações e Data de Criação -->
                                <div class="flex flex-col space-y-3">
                                    <div
                                        class="flex items-center justify-between text-xs text-gray-500 pb-3 border-b border-gray-100">
                                        <span>Criado em</span>
                                        <span>{{ $planejamento->created_at->format('d/m/Y H:i') }}</span>
                                    </div>

                                    <div class="flex items-center justify-center space-x-2">
                                        @permission('planejamentos.visualizar')
                                            <x-button variant="secondary" size="sm" href="{{ route('planejamentos.show', $planejamento) }}" title="Ver Detalhes">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                                Ver Detalhes
                                            </x-button>
                                        @endpermission

                                        @permission('planejamentos.editar')
                                            @if(in_array($planejamento->status, ['rascunho', 'finalizado']) && ($planejamento->user_id === Auth::id() || Auth::user()->isSuperAdmin()))
                                                <x-button variant="outline" size="sm" href="{{ route('planejamentos.detalhado', $planejamento) }}" title="Editar Detalhes">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                        </path>
                                                    </svg>
                                                </x-button>
                                            @endif
                                        @endpermission

                                        @permission('planejamentos.excluir')
                                            @if($planejamento->status === 'rascunho' && ($planejamento->user_id === Auth::id() || Auth::user()->isSuperAdmin()))
                                                <form action="{{ route('planejamentos.destroy', $planejamento) }}" method="POST"
                                                    class="inline"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir este planejamento?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-button type="submit" variant="danger" size="sm" title="Excluir">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </x-button>
                                                </form>
                                            @endif
                                        @endpermission
                                    </div>
                                </div>
                            </x-card>
                        @endforeach
                    </div>

                    <!-- Paginação -->
                    <div class="mt-4">
                        {{ $planejamentos->links('components.pagination') }}
                    </div>
                @else
                    <!-- Estado vazio -->
                    <x-card class="p-8">
                        <div class="text-center">
                            <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1h3z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum planejamento encontrado</h3>
                            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                                @if (request()->hasAny(['modalidade', 'turno', 'status', 'data_inicio']))
                                    Não há planejamentos que correspondam aos filtros selecionados. Tente ajustar os
                                    critérios de busca.
                                @else
                                    Você ainda não criou nenhum planejamento de aula. Comece criando seu primeiro
                                    planejamento.
                                @endif
                            </p>
                            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                @if (request()->hasAny(['sala_id', 'modalidade', 'turno', 'status', 'data_inicio']))
                                    <a href="{{ route('planejamentos.index') }}"
                                        class="inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Limpar filtros
                                    </a>
                                @endif

                                @can('planejamentos.criar')
                                    <a href="{{ route('planejamentos.create') }}"
                                        class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        {{ request()->hasAny(['modalidade', 'turno', 'status', 'data_inicio']) ? 'Criar planejamento' : 'Criar primeiro planejamento' }}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </x-card>
                @endif
            </x-card>
        </div>
    </div>

@endsection
