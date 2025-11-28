@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Salas', 'url' => '#']
]" />

    <x-card>
        <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Salas</h1>
                <p class="mt-1 text-sm text-gray-600">Gerenciamento de salas</p>
            </div>
            @permission('salas.criar')
                <x-button href="{{ route('salas.create') }}" color="primary" class="sm:justify-center">
                    <i class="fas fa-plus mr-1"></i> Nova Sala
                </x-button>
            @endpermission
        </div>

        <x-collapsible-filter 
            title="Filtros de Salas" 
            :action="route('salas.index')" 
            :clear-route="route('salas.index')"
            target="salas-list-wrapper"
        >
            <x-filter-field 
                name="nome" 
                label="Nome" 
                type="text"
                placeholder="Buscar por nome..."
            />
            
            <x-filter-field 
                name="codigo" 
                label="Código" 
                type="text"
                placeholder="Buscar por código..."
            />
            
            <x-filter-field 
                name="ativo" 
                label="Status" 
                type="select"
                empty-option="Todos"
                :options="['1' => 'Ativo', '0' => 'Inativo']"
            />
            
            @if(isset($escolas) && $escolas->count() > 1)
            <x-filter-field 
                name="escola_id" 
                label="Escola" 
                type="select"
                empty-option="Todas as escolas"
                :options="$escolas->pluck('nome', 'id')"
            />
            @endif
        </x-collapsible-filter>

        <!-- Wrapper AJAX para listagem e paginação -->
        <div id="salas-list-wrapper" data-ajax-content="true">
            <x-loading-overlay id="salas-loading" class="hidden" />

        <!-- Layout Desktop - Tabela -->
        <div class="hidden md:block">
            <x-table 
                :headers="[
                    ['label' => 'Nome', 'sort' => 'nome'],
                    ['label' => 'Código', 'sort' => 'codigo'],
                    ['label' => 'Capacidade', 'sort' => 'capacidade'],
                    ['label' => 'Tipo', 'sort' => 'tipo'],
                    ['label' => 'Escola', 'sort' => 'escola'],
                    ['label' => 'Status', 'sort' => 'ativo'],
                ]" 
                :actions="true"
                striped
                hover
                responsive
                sortable
                :currentSort="request('sort')"
                :currentDirection="request('direction', 'asc')"
            >
                @forelse($salas as $index => $sala)
                    <x-table-row :striped="true" :index="$index">
                        <x-table-cell>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $sala->nome }}</div>
                                    @if($sala->descricao)
                                        <div class="text-sm text-gray-500">{{ Str::limit($sala->descricao, 50) }}</div>
                                    @endif
                                </div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $sala->codigo }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="flex items-center">
                                <i class="fas fa-users text-blue-500 mr-1"></i>
                                <span class="text-sm font-medium">{{ $sala->capacidade ?? 'N/A' }}</span>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            @if($sala->tipo)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-tag mr-1"></i>
                                    {{ $sala->tipo }}
                                </span>
                            @else
                                <span class="text-gray-400">N/A</span>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            {{ $sala->escola->nome ?? 'N/A' }}
                        </x-table-cell>
                        <x-table-cell>
                            @if($sala->ativo)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Ativa
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Inativa
                                </span>
                            @endif
                        </x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex justify-end space-x-2">
                                @permission('salas.visualizar')
                                    <a href="{{ route('salas.show', $sala) }}" class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endpermission
                                
                                @permission('salas.editar')
                                    <a href="{{ route('salas.edit', $sala) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endpermission
                                
                                @permission('salas.editar')
                                    <form action="{{ route('salas.toggle-status', $sala) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja {{ $sala->ativo ? 'desativar' : 'ativar' }} esta sala?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-{{ $sala->ativo ? 'gray' : 'green' }}-600 hover:text-{{ $sala->ativo ? 'gray' : 'green' }}-900" title="{{ $sala->ativo ? 'Desativar' : 'Ativar' }}">
                                            <i class="fas fa-{{ $sala->ativo ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                @endpermission
                                
                                @permission('salas.excluir')
                                    <form action="{{ route('salas.destroy', $sala) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta sala? Esta ação não pode ser desfeita.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endpermission

                                @permission('presencas.criar')
                                    <a href="{{ route('presencas.registro-rapido', ['sala_id' => $sala->id]) }}" class="text-green-600 hover:text-green-900" title="Chamada do dia">
                                        <i class="fas fa-list-check"></i>
                                    </a>
                                @endpermission
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhuma sala encontrada.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        <!-- Layout Mobile - Cards -->
        <div class="md:hidden space-y-4">
            @forelse($salas as $sala)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                    <!-- Header do Card -->
                    <div class="p-4 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                    <i class="fas fa-door-open text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $sala->nome }}</h3>
                                    <p class="text-sm text-gray-500">#{{ $sala->id }}</p>
                                </div>
                            </div>
                            @if($sala->ativo)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Ativa
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Inativa
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Conteúdo do Card -->
                    <div class="p-4 space-y-3">
                        @if($sala->descricao)
                            <div>
                                <p class="text-sm text-gray-600">{{ $sala->descricao }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 flex items-center space-x-2">
                                <i class="fas fa-barcode text-gray-400"></i>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Código</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $sala->codigo }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-users text-gray-400"></i>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Capacidade</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $sala->capacidade ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-tag text-gray-400"></i>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Tipo</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $sala->tipo ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ações do Card -->
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 rounded-b-lg">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @permission('salas.visualizar')
                                <a href="{{ route('salas.show', $sala) }}" 
                                   class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-colors">
                                    <i class="fas fa-eye mr-2"></i>
                                    Ver
                                </a>
                            @endpermission
                            
                            @permission('salas.editar')
                                <a href="{{ route('salas.edit', $sala) }}" 
                                   class="w-full bg-yellow-600 hover:bg-yellow-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-colors">
                                    <i class="fas fa-edit mr-2"></i>
                                    Editar
                                </a>
                            @endpermission
                            
                            @permission('salas.editar')
                                <form action="{{ route('salas.toggle-status', $sala) }}" method="POST" class="w-full" onsubmit="return confirm('Tem certeza que deseja {{ $sala->ativo ? 'desativar' : 'ativar' }} esta sala?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full bg-{{ $sala->ativo ? 'gray' : 'green' }}-600 hover:bg-{{ $sala->ativo ? 'gray' : 'green' }}-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-colors">
                                        <i class="fas fa-{{ $sala->ativo ? 'pause' : 'play' }} mr-2"></i>
                                        {{ $sala->ativo ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                            @endpermission
                            
                            @permission('salas.excluir')
                                <form action="{{ route('salas.destroy', $sala) }}" method="POST" class="w-full" onsubmit="return confirm('Tem certeza que deseja excluir esta sala? Esta ação não pode ser desfeita.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-colors">
                                        <i class="fas fa-trash mr-2"></i>
                                        Excluir
                                    </button>
                                </form>
                            @endpermission

                            @permission('presencas.criar')
                                <a href="{{ route('presencas.registro-rapido', ['sala_id' => $sala->id]) }}" 
                                   class="col-span-2 w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-colors">
                                    <i class="fas fa-list-check mr-2"></i>
                                    Chamada do dia
                                </a>
                            @endpermission
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-door-open text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 text-sm">Nenhuma sala encontrada.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $salas->links('components.pagination') }}
        </div>
        </div> <!-- fim do wrapper AJAX -->

        <!-- Scripts AJAX para ordenação e paginação das Salas -->
        <script>
            function showSalasLoading() {
                const el = document.getElementById('salas-loading');
                if (el) el.classList.remove('hidden');
            }

            function hideSalasLoading() {
                const el = document.getElementById('salas-loading');
                if (el) el.classList.add('hidden');
            }

            async function updateSalasContainer(url, replaceHistory = true) {
                const container = document.getElementById('salas-list-wrapper');
                if (!container) return;
                showSalasLoading();
                try {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContainer = doc.querySelector('#salas-list-wrapper');
                    if (newContainer) {
                        container.innerHTML = newContainer.innerHTML;
                        if (replaceHistory) {
                            history.pushState({ page: 'salas-index' }, '', url);
                        }
                        initSalasAjaxBindings();
                    }
                } catch (err) {
                    console.error('Erro ao atualizar Salas via AJAX:', err);
                } finally {
                    hideSalasLoading();
                }
            }

            function initSalasAjaxBindings() {
                const container = document.getElementById('salas-list-wrapper');
                if (!container) return;

                // Ordenação (links com data-ajax-link)
                container.querySelectorAll('[data-ajax-link="true"]').forEach(link => {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        updateSalasContainer(this.href);
                    });
                });

                // Ordenação (links do cabeçalho da tabela)
                const sortHeaderLinks = container.querySelectorAll('thead a[href]');
                sortHeaderLinks.forEach(link => {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        updateSalasContainer(this.href);
                    });
                });

                // Paginação
                const paginationLinks = container.querySelectorAll('.pagination a');
                paginationLinks.forEach(link => {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        updateSalasContainer(this.href);
                    });
                });
            }

            window.addEventListener('DOMContentLoaded', initSalasAjaxBindings);
            window.addEventListener('popstate', function () {
                const url = document.location.href;
                updateSalasContainer(url, false);
            });
        </script>
    </x-card>
@endsection