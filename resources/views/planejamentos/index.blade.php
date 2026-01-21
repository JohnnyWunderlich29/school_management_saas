@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="container mx-auto py-6">
            <x-breadcrumbs :items="[['title' => 'Planejamentos', 'url' => '#']]" />

            <x-card class="shadow-lg border-0">
                <!-- Header Section -->
                <div class="mb-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Planejamentos</h1>
                            <p class="mt-2 text-sm text-gray-600">Gerencie os planejamentos de aula da sua instituição</p>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4 sm:mt-0">
                            @can('create', App\Models\Planejamento::class)
                                <div class="flex flex-col mt-4 sm:mt-0 gap-3 md:flex-row">
                                    <x-button href="{{ route('planejamentos.wizard') }}" color="primary"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-lg">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <span class="hidden sm:inline">Novo Planejamento</span>
                                        <span class="sm:hidden">Novo</span>
                                    </x-button>
                                    <!-- VAMOS APRESENTAR AINDA -->
                                    <!--
                                        @if (Auth::user()->isAdminOrCoordinator() || Auth::user()->isSuperAdmin())
        <x-button href="{{ route('planejamentos.conflitos.index') }}" color="secondary"
                                                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-gray-600 hover:bg-gray-700 text-gray-300 font-medium rounded-lg transition-colors duration-200">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                                    </path>
                                                </svg>
                                                <span class="hidden sm:inline">Gestão de Conflitos</span>
                                                <span class="sm:hidden">Conflitos</span>
                                            </x-button>
        @endif
                                        -->
                                </div>
                            @endcan
                            @permission('planejamentos.visualizar')
                                <div class="flex flex-col mt-4 sm:mt-0 gap-3 md:flex-row">
                                    <x-button
                                        href="{{ route('planejamentos.cronograma-dia', ['data' => request('data_inicio') ? \Carbon\Carbon::parse(request('data_inicio'))->format('Y-m-d') : now()->format('Y-m-d')]) }}"
                                        color="secondary"
                                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium rounded-lg transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1h3z">
                                            </path>
                                        </svg>
                                        Cronograma de Hoje
                                    </x-button>
                                </div>
                            @endpermission
                        </div>
                    </div>
                </div>



                <!-- Filtros -->
                <x-collapsible-filter title="Filtros de Busca" :action="route('planejamentos.index')" :clear-route="route('planejamentos.index')"
                    target="planejamentos-list-wrapper">

                    <x-filter-field name="modalidade" label="Modalidade" type="select" empty-option="Todas as modalidades"
                        :options="collect(\App\Models\Planejamento::getModalidadesOptions())" />

                    <x-filter-field name="turno" label="Turno" type="select" empty-option="Todos os turnos"
                        :options="collect(\App\Models\Planejamento::getTurnosOptions())" />

                    <x-filter-field name="status" label="Status" type="select" empty-option="Todos os status"
                        :options="collect([
                            'rascunho' => 'Rascunho',
                            'finalizado' => 'Aguardando Aprovação',
                            'aprovado' => 'Aprovado',
                            'rejeitado' => 'Correção Solicitada',
                        ])" />

                    <x-filter-field name="turma_id" label="Turma" type="select" empty-option="Todas as turmas"
                        :options="$turmas->pluck('nome', 'id')" />

                    <x-date-filter-with-arrows name="data_inicio" label="Data Início" :value="request('data_inicio')"
                        data-fim-name="data_fim" :data-fim-value="request('data_fim')" />

                </x-collapsible-filter>


                <!-- Lista de Planejamentos -->
                <div id="planejamentos-list-wrapper" class="relative">
                    <x-loading-overlay message="Atualizando planejamentos..." />
                    <div data-ajax-content>
                        @if ($planejamentos->count() > 0)
                            <!-- Desktop Table -->
                            <div class="hidden lg:block">
                                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                                    <x-table :headers="[
                                        ['label' => 'Título / Modalidade', 'sort' => 'titulo'],
                                        ['label' => 'Turno / Turma', 'sort' => 'turno'],
                                        ['label' => 'Professor'],
                                        ['label' => 'Período', 'sort' => 'data_inicio'],
                                        ['label' => 'Status', 'sort' => 'status'],
                                        ['label' => 'Criado em', 'sort' => 'created_at'],
                                    ]" :actions="true" striped hover responsive sortable
                                        :currentSort="request('sort')" :currentDirection="request('direction', 'desc')">

                                        @foreach ($planejamentos as $planejamento)
                                            <x-table-row>
                                                <x-table-cell>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $planejamento->titulo ?: 'Planejamento #' . $planejamento->id }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $planejamento->modalidade_formatada ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                </x-table-cell>
                                                <x-table-cell>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $planejamento->turno_formatado ?? 'N/A' }}
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
                                                        {{ $planejamento->tipo_professor_formatado ?? 'N/A' }}
                                                    </div>
                                                </x-table-cell>
                                                <x-table-cell>
                                                    <div class="text-sm">
                                                        <div class="font-medium text-gray-900">
                                                            {{ $planejamento->numero_dias }}
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
                                                    @include('planejamentos.components.status-badge', [
                                                        'status' => $planejamento->status_efetivo,
                                                    ])
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

                                                        @permission('planejamentos.visualizar')
                                                            <a href="{{ route('planejamentos.cronograma', ['planejamento' => $planejamento->id, 'data' => now()->format('Y-m-d')]) }}"
                                                                class="inline-flex items-center px-3 py-1.5 bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium rounded-md transition-colors duration-200"
                                                                title="Ver Cronograma Diário">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path>
                                                                </svg>
                                                                Ver Cronograma Diário
                                                            </a>
                                                        @endpermission

                                                        @permission('planejamentos.editar')
                                                            @if (in_array($planejamento->status_efetivo, ['rascunho', 'reprovado', 'rejeitado']))
                                                                <a href="{{ route('planejamentos.wizard', ['edit' => $planejamento->id]) }}"
                                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-xs font-medium rounded-md transition-colors duration-200"
                                                                    title="{{ $planejamento->status_efetivo === 'rascunho' ? 'Continuar planejamento' : 'Corrigir planejamento' }}">
                                                                    <svg class="w-3 h-3 mr-1" fill="none"
                                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2"
                                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                        </path>
                                                                    </svg>
                                                                    {{ $planejamento->status_efetivo === 'rascunho' ? 'Editar' : 'Corrigir' }}
                                                                </a>
                                                            @endif
                                                        @endpermission

                                                        @permission('planejamentos.excluir')
                                                            @if ($planejamento->status_efetivo === 'rascunho')
                                                                <form
                                                                    action="{{ route('planejamentos.destroy', $planejamento) }}"
                                                                    method="POST" class="inline"
                                                                    onsubmit="return confirm('Tem certeza que deseja excluir este planejamento?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-md transition-colors duration-200"
                                                                        title="Excluir">
                                                                        <svg class="w-3 h-3 mr-1" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
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
                                                    {{ $planejamento->modalidade_formatada ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <div class="ml-3">
                                                @include('planejamentos.components.status-badge', [
                                                    'status' => $planejamento->status_efetivo,
                                                ])
                                            </div>
                                        </div>

                                        <!-- Informações do Planejamento -->
                                        <div class="space-y-3 mb-5">
                                            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                                <span class="text-sm text-gray-600 font-medium">Turno</span>
                                                <span
                                                    class="text-sm text-gray-900 font-semibold">{{ $planejamento->turno_formatado ?? 'N/A' }}</span>
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
                                                <span
                                                    class="text-sm text-gray-900 font-semibold">{{ $planejamento->numero_dias }}
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

                                            <div
                                                class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center justify-center">
                                                @can('view', $planejamento)
                                                    <x-button variant="secondary" size="sm"
                                                        href="{{ route('planejamentos.show', $planejamento) }}"
                                                        title="Ver Detalhes" class="w-full sm:w-auto">
                                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                            </path>
                                                        </svg>
                                                        Ver Detalhes
                                                    </x-button>
                                                @endcan

                                                @permission('planejamentos.visualizar')
                                                    <x-button variant="outline" size="sm"
                                                        href="{{ route('planejamentos.cronograma', ['planejamento' => $planejamento->id, 'data' => now()->format('Y-m-d')]) }}"
                                                        title="Ver Cronograma Diário" class="w-full sm:w-auto">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path>
                                                        </svg>
                                                        Cronograma Diário
                                                    </x-button>
                                                @endpermission

                                                @can('update', $planejamento)
                                                    @if ($planejamento->status_efetivo === 'rascunho')
                                                        <x-button variant="outline" size="sm"
                                                            href="{{ route('planejamentos.wizard', ['edit' => $planejamento->id]) }}"
                                                            title="Continuar planejamento" class="w-full sm:w-auto">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                </path>
                                                            </svg>
                                                        </x-button>
                                                    @elseif (in_array($planejamento->status_efetivo, ['revisao', 'rejeitado']))
                                                        <x-button variant="outline" size="sm"
                                                            href="{{ route('planejamentos.edit', $planejamento) }}"
                                                            title="Editar" class="w-full sm:w-auto">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                </path>
                                                            </svg>
                                                            Editar
                                                        </x-button>
                                                    @endif
                                                @endcan

                                                @can('delete', $planejamento)
                                                    @if ($planejamento->status_efetivo === 'rascunho')
                                                        <form action="{{ route('planejamentos.destroy', $planejamento) }}"
                                                            method="POST" class="inline"
                                                            onsubmit="return confirm('Tem certeza que deseja excluir este planejamento?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <x-button type="submit" variant="danger" size="sm"
                                                                title="Excluir" class="w-full sm:w-auto">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                    </path>
                                                                </svg>
                                                            </x-button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </div>
                                        </div>
                                    </x-card>
                                @endforeach
                            </div>

                            <!-- Paginação -->
                            <!-- Paginação -->
                            <div class="mt-4">
                                {{ $planejamentos->links('components.pagination') }}
                            </div>
                        @else
                            <!-- Estado vazio -->
                            <x-card class="p-8">
                                <div class="text-center">
                                    <div
                                        class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1h3z">
                                            </path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum planejamento encontrado
                                    </h3>
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
                                        @if (request()->hasAny(['modalidade', 'turno', 'status', 'data_inicio']))
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

                                        @can('create', App\Models\Planejamento::class)
                                            <a href="{{ route('planejamentos.wizard') }}"
                                                class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
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
                    </div>
                </div>
            </x-card>
        </div>
    </div>

@endsection
@push('scripts')
    <script>
        function showPlanejamentosLoading() {
            const wrapper = document.getElementById('planejamentos-list-wrapper');
            if (!wrapper) return;
            const overlay = wrapper.querySelector('.loading-overlay');
            if (overlay) overlay.classList.remove('hidden');
        }

        function hidePlanejamentosLoading() {
            const wrapper = document.getElementById('planejamentos-list-wrapper');
            if (!wrapper) return;
            const overlay = wrapper.querySelector('.loading-overlay');
            if (overlay) overlay.classList.add('hidden');
        }

        async function updatePlanejamentosContainer(url, pushState = true) {
            const wrapper = document.getElementById('planejamentos-list-wrapper');
            if (!wrapper) return;
            const ajaxContent = wrapper.querySelector('[data-ajax-content]');
            if (!ajaxContent) return;

            try {
                showPlanejamentosLoading();
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Falha ao buscar dados');
                const html = await response.text();

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newContent = tempDiv.querySelector('#planejamentos-list-wrapper [data-ajax-content]') || tempDiv
                    .querySelector('[data-ajax-content]');

                if (newContent) {
                    ajaxContent.innerHTML = newContent.innerHTML;
                } else {
                    console.warn('Conteúdo AJAX não encontrado na resposta');
                }

                hidePlanejamentosLoading();
                initPlanejamentosAjaxBindings();
                if (pushState) window.history.pushState({
                    url
                }, '', url);
            } catch (err) {
                console.error(err);
                hidePlanejamentosLoading();
            }
        }

        function initPlanejamentosAjaxBindings() {
            const wrapper = document.getElementById('planejamentos-list-wrapper');
            if (!wrapper) return;

            // Ordenação (links do cabeçalho da tabela)
            const sortLinks = wrapper.querySelectorAll('thead a[href]');
            sortLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = e.currentTarget.href;
                    updatePlanejamentosContainer(url);
                });
            });

            // Paginação
            const paginationLinks = wrapper.querySelectorAll(
                'nav[aria-label="Pagination Navigation"] a[href], .pagination a[href]');
            paginationLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = e.currentTarget.href;
                    updatePlanejamentosContainer(url);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            initPlanejamentosAjaxBindings();
            window.addEventListener('popstate', (event) => {
                const url = event.state?.url || window.location.href;
                updatePlanejamentosContainer(url, false);
            });
        });
    </script>
@endpush
