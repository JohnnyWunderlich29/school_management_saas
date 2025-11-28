@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Cargos', 'url' => '#']
]" />

<x-card>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Cargos</h1>
            <p class="mt-1 text-sm text-gray-600">Gerenciamento de cargos e permissões</p>
        </div>
        @permission('cargos.criar')
            <x-button href="{{ route('cargos.create') }}" color="primary">
                <i class="fas fa-plus mr-1"></i> Novo Cargo
            </x-button>
        @endpermission
    </div>

@if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Filtros com atualização via AJAX -->
    <x-collapsible-filter clear-route="{{ route('cargos.index') }}" target="cargos-list-wrapper">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-filter-field name="nome" label="Nome" type="text" placeholder="Digite o nome do cargo"
                value="{{ request('nome') }}" />

            <x-filter-field name="ativo" label="Status" type="select"
                :options="['1' => 'Ativo', '0' => 'Inativo']" empty-option="Todos os status" />

            <x-filter-field name="tipo_cargo" label="Tipo" type="select"
                :options="['professor' => 'Professor', 'coordenador' => 'Coordenador', 'administrador' => 'Administrador', 'outro' => 'Outro']"
                empty-option="Todos os tipos" />
        </div>
    </x-collapsible-filter>

    <!-- Wrapper da lista com AJAX -->
    <div id="cargos-list-wrapper" class="relative">
        <x-loading-overlay message="Atualizando cargos..." />
        <div data-ajax-content>
    <!-- Layout Desktop: tabela tradicional -->
    <div class="hidden md:block">
        <x-card>
            <x-table 
                :headers="[
                    ['label' => 'ID', 'sort' => 'id'],
                    ['label' => 'Nome', 'sort' => 'nome'],
                    'Descrição',
                    'Permissões',
                    ['label' => 'Status', 'sort' => 'ativo'],
                    ['label' => 'Criado em', 'sort' => 'created_at'],
                ]" 
                :actions="true"
                striped
                hover
                responsive
                sortable
                :currentSort="request('sort')"
                :currentDirection="request('direction', 'desc')"
            >
                @forelse($cargos as $cargo)
                    <x-table-row>
                        <x-table-cell>{{ $cargo->id }}</x-table-cell>
                        <x-table-cell>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $cargo->nome }}</div>
                                    @if($cargo->descricao)
                                        <div class="text-gray-500 text-xs">{{ Str::limit($cargo->descricao, 50) }}</div>
                                    @endif
                                </div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>{{ Str::limit($cargo->descricao, 50) }}</x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $cargo->permissoes->count() }} permissões
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cargo->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $cargo->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>{{ $cargo->created_at->format('d/m/Y H:i') }}</x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex justify-end space-x-2">
                                @permission('cargos.visualizar')
                                    <a href="{{ route('cargos.show', $cargo) }}" class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endpermission
                                
                                @permission('cargos.editar')
                                    <a href="{{ route('cargos.edit', $cargo) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endpermission
                                
                                @permission('cargos.excluir')
                                    <form action="{{ route('cargos.destroy', $cargo) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este cargo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endpermission
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhum cargo encontrado.
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div class="mt-4">
                {{ $cargos->links('components.pagination') }}
            </div>
        </x-card>
    </div>

    <!-- Layout Mobile: cards otimizados -->
    <div class="md:hidden space-y-4">
        @forelse($cargos as $cargo)
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <!-- Header do card com nome e status -->
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                        <i class="fas fa-user-shield text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-gray-900 text-base">{{ $cargo->nome }}</h3>
                        <p class="text-sm text-gray-500">ID: {{ $cargo->id }}</p>
                    </div>
                    <div class="ml-2">
                        @if($cargo->ativo)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Ativo
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>
                                Inativo
                            </span>
                        @endif
                    </div>
                </div>
                
                <!-- Descrição -->
                @if($cargo->descricao)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700">{{ $cargo->descricao }}</p>
                    </div>
                @endif
                
                <!-- Informações em grid -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                        <div class="text-lg font-semibold text-blue-700">{{ $cargo->permissoes->count() }}</div>
                        <div class="text-xs text-blue-600 mt-1">Permissões</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-sm font-medium text-gray-900">{{ $cargo->created_at->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-600 mt-1">Criado em</div>
                    </div>
                </div>
                
                <!-- Botões de ação com touch targets otimizados -->
                <div class="flex space-x-2">
                    @permission('cargos.visualizar')
                        <a href="{{ route('cargos.show', $cargo) }}" 
                           class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                            <i class="fas fa-eye mr-2"></i>
                            Visualizar
                        </a>
                    @endpermission
                    
                    @permission('cargos.editar')
                        <a href="{{ route('cargos.edit', $cargo) }}" 
                           class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                            <i class="fas fa-edit mr-2"></i>
                            Editar
                        </a>
                    @endpermission
                    
                    @permission('cargos.excluir')
                        <form action="{{ route('cargos.destroy', $cargo) }}" method="POST" class="flex-1" onsubmit="return confirm('Tem certeza que deseja excluir este cargo?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full bg-red-600 hover:bg-red-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                                <i class="fas fa-trash mr-2"></i>
                                Excluir
                            </button>
                        </form>
                    @endpermission
                </div>
            </div>
        @empty
            <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-user-shield text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-500">Nenhum cargo encontrado.</p>
            </div>
        @endforelse
        
        <!-- Paginação mobile -->
        <div class="mt-4">
            {{ $cargos->links('components.pagination') }}
        </div>
    </div>
    </div> <!-- data-ajax-content -->
    </div> <!-- cargos-list-wrapper -->
</x-card>
<script>
    // AJAX bindings para ordenação e paginação na lista de cargos
    function showCargosLoading() {
        const wrapper = document.getElementById('cargos-list-wrapper');
        if (!wrapper) return;
        const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
        if (overlay) overlay.classList.remove('hidden');
        wrapper.style.pointerEvents = 'none';
    }

    function hideCargosLoading() {
        const wrapper = document.getElementById('cargos-list-wrapper');
        if (!wrapper) return;
        const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
        if (overlay) overlay.classList.add('hidden');
        wrapper.style.pointerEvents = '';
    }

    function updateCargosContainer(url, pushState = true) {
        const wrapper = document.getElementById('cargos-list-wrapper');
        if (!wrapper) { window.location.href = url; return; }
        const ajaxArea = wrapper.querySelector('[data-ajax-content]');
        if (!ajaxArea) { window.location.href = url; return; }

        showCargosLoading();
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(resp => resp.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newWrapper = doc.querySelector('#cargos-list-wrapper');
                const newAjaxArea = newWrapper ? newWrapper.querySelector('[data-ajax-content]') : null;
                if (newAjaxArea) {
                    ajaxArea.innerHTML = newAjaxArea.innerHTML;
                    if (pushState) window.history.pushState(null, '', url);
                    initCargosAjaxBindings();
                } else {
                    window.location.href = url;
                }
            })
            .catch(() => { window.location.href = url; })
            .finally(() => { hideCargosLoading(); });
    }

    function initCargosAjaxBindings() {
        const wrapper = document.getElementById('cargos-list-wrapper');
        if (!wrapper) return;
        const ajaxArea = wrapper.querySelector('[data-ajax-content]');
        if (!ajaxArea) return;

        // Interceptar ordenação nos cabeçalhos da tabela
        const sortLinks = ajaxArea.querySelectorAll('thead a[href]');
        sortLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                updateCargosContainer(url);
            });
        });

        // Interceptar links de paginação
        const paginationLinks = ajaxArea.querySelectorAll('.pagination a[href], nav[aria-label="Pagination"] a[href]');
        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                updateCargosContainer(url);
            });
        });
    }

    // Inicializar ao carregar a página
    document.addEventListener('DOMContentLoaded', initCargosAjaxBindings);

    // Suporte ao histórico do navegador (voltar/avançar)
    window.addEventListener('popstate', (event) => {
        const url = document.location.href;
        updateCargosContainer(url, false);
    });
</script>
@endsection