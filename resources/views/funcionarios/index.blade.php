@extends('layouts.app')

@section('title', 'Funcionários')

@section('content')
    <x-breadcrumbs :items="[['title' => 'Funcionários', 'url' => '#']]" />
    <x-card>
        <!-- Header responsivo -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-3 sm:space-y-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Funcionários</h1>
                <p class="text-gray-600 mt-1">Gerencie os funcionários da escola</p>
            </div>
            <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
                <x-button href="{{ route('funcionarios.create') }}" color="primary" class="w-full sm:justify-center">
                    <i class="fas fa-plus mr-2"></i>
                    <span class="hidden md:inline">Novo Funcionário</span>
                    <span class="md:hidden">Novo</span>
                </x-button>
            </div>
        </div>

        <!-- Filtros -->
        <x-collapsible-filter title="Filtros de Busca" action="{{ route('funcionarios.index') }}"
            clear-route="{{ route('funcionarios.index') }}" target="funcionarios-list-wrapper">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-filter-field name="nome" label="Nome" type="text" placeholder="Digite o nome do funcionário"
                    value="{{ request('nome') }}" />

                <x-filter-field name="cargo" label="Cargo" type="text" placeholder="Digite o cargo"
                    value="{{ request('cargo') }}" />

                <x-filter-field name="ativo" label="Status" type="select" :options="['true' => 'Ativo', 'false' => 'Inativo']"
                    empty-option="Todos os status" value="{{ request('ativo') }}" />
            </div>
        </x-collapsible-filter>

        <!-- Mensagens de sucesso -->
        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <!-- Mensagens de erro -->
        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Erros de validação -->
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <div>
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Header com estatísticas - responsivo -->
            <div class="mb-4">
                <h3 class="text-mobile-title text-gray-900 mb-3">Lista de Funcionários</h3>
                <!-- Desktop: horizontal -->
                <div class="hidden md:flex space-x-2">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-users mr-1"></i>{{ $funcionarios->total() }} Total
                    </span>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check mr-1"></i>{{ $funcionarios->where('ativo', true)->count() }} Ativos
                    </span>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <i class="fas fa-times mr-1"></i>{{ $funcionarios->where('ativo', false)->count() }} Inativos
                    </span>
                </div>
                <!-- Mobile: grid 3 colunas -->
                <div class="md:hidden grid grid-cols-3 gap-2">
                    <span
                        class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-users mr-1"></i>{{ $funcionarios->total() }}
                    </span>
                    <span
                        class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check mr-1"></i>{{ $funcionarios->where('ativo', true)->count() }}
                    </span>
                    <span
                        class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <i class="fas fa-times mr-1"></i>{{ $funcionarios->where('ativo', false)->count() }}
                    </span>
                </div>
            </div>

            <div id="funcionarios-list-wrapper" class="relative">
                <x-loading-overlay message="Atualizando funcionários..." />
                <div data-ajax-content>
            <!-- Desktop Layout (Table) -->
            <div class="hidden md:block">
                <x-table 
                    :headers="[
                        ['label' => 'ID', 'sort' => 'id'],
                        ['label' => 'Nome', 'sort' => 'nome'],
                        ['label' => 'Cargo', 'sort' => 'cargo'],
                        'Contato',
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
                    @forelse($funcionarios as $index => $funcionario)
                        <x-table-row :striped="true" :index="$index">
                            <x-table-cell>{{ $funcionario->id }}</x-table-cell>
                            <x-table-cell>
                                <div class="flex items-center">
                                    <div
                                        class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-500 mr-3">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $funcionario->nome }}
                                            {{ $funcionario->sobrenome }}</div>
                                        @if ($funcionario->email)
                                            <div class="text-gray-500 text-xs">{{ $funcionario->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </x-table-cell>
                            <x-table-cell>
                                <div class="text-gray-900">{{ $funcionario->cargo }}</div>
                                @if ($funcionario->departamento)
                                    <div class="text-gray-500 text-xs">{{ $funcionario->departamento }}</div>
                                @endif
                            </x-table-cell>
                            <x-table-cell>{{ $funcionario->telefone ?: '-' }}</x-table-cell>
                            <x-table-cell>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $funcionario->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $funcionario->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </x-table-cell>
                            <x-table-cell align="right">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('funcionarios.show', $funcionario->id) }}"
                                        class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('funcionarios.templates.index', $funcionario->id) }}"
                                        class="text-blue-600 hover:text-blue-900" title="Templates">
                                        <i class="fas fa-calendar-alt"></i>
                                    </a>
                                    <a href="{{ route('funcionarios.edit', $funcionario->id) }}"
                                        class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmToggleStatus('{{ $funcionario->nome }}', '{{ $funcionario->ativo ? 'inativar' : 'ativar' }}', '{{ route('funcionarios.toggle-status', $funcionario->id) }}')"
                                        class="{{ $funcionario->ativo ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}"
                                        title="{{ $funcionario->ativo ? 'Inativar' : 'Ativar' }}">
                                        <i class="fas {{ $funcionario->ativo ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </div>
                            </x-table-cell>
                        </x-table-row>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                Nenhum funcionário encontrado.
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </div>

            <!-- Mobile Layout (Cards) -->
            <div class="block md:hidden space-y-4">
                @forelse($funcionarios as $funcionario)
                    <x-card class="mobile-card-shadow rounded-xl border-0 overflow-hidden">
                        <!-- Card Header -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 border-b border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <!-- Avatar -->
                                    <div
                                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-lg">
                                            {{ strtoupper(substr($funcionario->nome, 0, 1)) }}
                                        </span>
                                    </div>
                                    <!-- Nome e Cargo -->
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-mobile-title font-semibold text-gray-900 truncate">
                                            {{ $funcionario->nome }}</h3>
                                        <p class="text-mobile-subtitle text-gray-600 truncate">{{ $funcionario->cargo }}
                                        </p>
                                    </div>
                                </div>
                                <!-- Status Badge -->
                                <span
                                    class="text-mobile-badge px-3 py-1.5 rounded-full font-medium flex-shrink-0 {{ $funcionario->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $funcionario->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4">
                            <!-- Informações em Grid -->
                            <div class="grid grid-cols-1 xs:grid-cols-2 gap-3 mb-4">
                                <div class="flex items-center space-x-2">
                                    <div
                                        class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-mobile-caption text-gray-500 text-xs">ID</p>
                                        <p class="text-mobile-body text-gray-900 font-medium truncate">
                                            {{ $funcionario->id }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <div
                                        class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-mobile-caption text-gray-500 text-xs">Contato</p>
                                        <p class="text-mobile-body text-gray-900 font-medium truncate">
                                            {{ $funcionario->telefone ?: 'Não informado' }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <a href="{{ route('funcionarios.show', $funcionario->id) }}"
                                    class="touch-button bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white text-center py-3 px-4 rounded-lg text-mobile-button font-medium transition-all duration-200 focus-ring flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    <span class="hidden xs:inline">Ver</span>
                                </a>
                                <a href="{{ route('funcionarios.edit', $funcionario->id) }}"
                                    class="touch-button bg-yellow-500 hover:bg-yellow-600 active:bg-yellow-700 text-white text-center py-3 px-4 rounded-lg text-mobile-button font-medium transition-all duration-200 focus-ring flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    <span class="hidden xs:inline">Editar</span>
                                </a>
                                <a href="{{ route('funcionarios.templates.index', $funcionario->id) }}"
                                    class="touch-button bg-purple-500 hover:bg-purple-600 active:bg-purple-700 text-white text-center py-3 px-4 rounded-lg text-mobile-button font-medium transition-all duration-200 focus-ring flex items-center justify-center space-x-2">
                                    <i class="fas fa-calendar-alt text-sm"></i>
                                    <span class="hidden xs:inline">Templates</span>
                                </a>
                                <button onclick="confirmToggleStatus('{{ $funcionario->nome }}', '{{ $funcionario->ativo ? 'inativar' : 'ativar' }}', '{{ route('funcionarios.toggle-status', $funcionario->id) }}')"
                                    class="touch-button {{ $funcionario->ativo ? 'bg-red-500 hover:bg-red-600 active:bg-red-700' : 'bg-green-500 hover:bg-green-600 active:bg-green-700' }} text-white py-3 px-4 rounded-lg text-mobile-button font-medium transition-all duration-200 focus-ring flex items-center justify-center space-x-2">
                                    @if($funcionario->ativo)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728">
                                            </path>
                                        </svg>
                                        <span class="hidden xs:inline">Inativar</span>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7">
                                            </path>
                                        </svg>
                                        <span class="hidden xs:inline">Ativar</span>
                                    @endif
                                </button>
                            </div>
                        </div>
                    </x-card>
                @empty
                    <!-- Estado Vazio Melhorado -->
                    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhum funcionário encontrado</h3>
                        <p class="text-gray-500 mb-6">Não há funcionários cadastrados ou que correspondam aos filtros
                            aplicados. Tente ajustar os filtros ou adicione um novo funcionário.</p>
                        <a href="{{ route('funcionarios.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Adicionar Funcionário
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $funcionarios->links('components.pagination') }}
            </div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Modal de confirmação para ativar/inativar -->
    <x-toggle-status-modal />

    <script>
        // AJAX bindings para ordenação e paginação na lista de funcionários
        function showFuncionariosLoading() {
            const wrapper = document.getElementById('funcionarios-list-wrapper');
            if (!wrapper) return;
            const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
            if (overlay) overlay.classList.remove('hidden');
            wrapper.style.pointerEvents = 'none';
        }

        function hideFuncionariosLoading() {
            const wrapper = document.getElementById('funcionarios-list-wrapper');
            if (!wrapper) return;
            const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
            if (overlay) overlay.classList.add('hidden');
            wrapper.style.pointerEvents = '';
        }

        function updateFuncionariosContainer(url, pushState = true) {
            const wrapper = document.getElementById('funcionarios-list-wrapper');
            if (!wrapper) { window.location.href = url; return; }
            const ajaxArea = wrapper.querySelector('[data-ajax-content]');
            if (!ajaxArea) { window.location.href = url; return; }

            showFuncionariosLoading();
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(resp => resp.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newWrapper = doc.querySelector('#funcionarios-list-wrapper');
                    const newAjaxArea = newWrapper ? newWrapper.querySelector('[data-ajax-content]') : null;
                    if (newAjaxArea) {
                        ajaxArea.innerHTML = newAjaxArea.innerHTML;
                        if (pushState) window.history.pushState(null, '', url);
                        initFuncionarioAjaxBindings();
                    } else {
                        window.location.href = url;
                    }
                })
                .catch(() => { window.location.href = url; })
                .finally(() => { hideFuncionariosLoading(); });
        }

        function initFuncionarioAjaxBindings() {
            const wrapper = document.getElementById('funcionarios-list-wrapper');
            if (!wrapper) return;
            const ajaxArea = wrapper.querySelector('[data-ajax-content]');
            if (!ajaxArea) return;

            // Interceptar ordenação nos cabeçalhos da tabela
            const sortLinks = ajaxArea.querySelectorAll('thead a[href]');
            sortLinks.forEach(link => {
                if (link.dataset.ajaxBound === '1') return;
                link.dataset.ajaxBound = '1';
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.href;
                    updateFuncionariosContainer(url);
                });
            });

            // Interceptar paginação
            const paginationLinks = ajaxArea.querySelectorAll('nav[aria-label="Pagination Navigation"] a[href]');
            paginationLinks.forEach(link => {
                if (link.dataset.ajaxBound === '1') return;
                link.dataset.ajaxBound = '1';
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.href;
                    updateFuncionariosContainer(url);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initFuncionarioAjaxBindings();
            window.addEventListener('popstate', function() {
                updateFuncionariosContainer(window.location.href, false);
            });
        });
    </script>
@endsection
