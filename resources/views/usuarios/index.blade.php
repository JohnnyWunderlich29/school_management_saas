@extends('layouts.app')

@section('content')
    <x-breadcrumbs :items="[['title' => 'Usuários', 'url' => '#']]" />
    <x-card>
        <!-- Cabeçalho responsivo -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Usuários</h1>
                    <p class="mt-1 text-sm sm:text-base text-gray-600">Gerencie os usuários do sistema</p>
                </div>

                @permission('usuarios.criar')
                    <a href="{{ route('usuarios.create') }}"
                        class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 w-full sm:w-auto">
                        <i class="fas fa-plus mr-2"></i>
                        <span class="hidden sm:inline">Novo Usuário</span>
                        <span class="sm:hidden">Novo</span>
                    </a>
                @endpermission
            </div>
        </div>

        <!-- Filtros -->
        <x-collapsible-filter title="Filtros de Busca" action="{{ route('usuarios.index') }}"
            clear-route="{{ route('usuarios.index') }}" target="usuarios-list-wrapper">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-filter-field name="nome" label="Nome" type="text" placeholder="Digite o nome do usuário"
                    value="{{ request('nome') }}" />

                <x-filter-field name="email" label="Email" type="text" placeholder="Digite o email do usuário"
                    value="{{ request('email') }}" />

                <x-filter-field name="cargo_id" label="Cargo" type="select" :options="$cargos->pluck('nome', 'id')->toArray()"
                    empty-option="Todos os cargos" />
            </div>
        </x-collapsible-filter>

        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('error') }}
                </div>
            </div>
        @endif

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

        <!-- Lista de Usuários -->
        <div id="usuarios-list-wrapper" class="relative">
            <x-loading-overlay message="Atualizando usuários..." />
            <div data-ajax-content>
        <!-- Tabela desktop -->
        <x-table class="hidden md:block"
            :headers="[
                ['label' => 'ID', 'sort' => 'id'],
                ['label' => 'Nome', 'sort' => 'name'],
                ['label' => 'Email', 'sort' => 'email'],
                ['label' => 'Cargo'],
                ['label' => 'Criado em', 'sort' => 'created_at']
            ]"
            :actions="true"
            striped
            hover
            responsive
            sortable
            :currentSort="request('sort')"
            :currentDirection="request('direction', 'desc')"
        >
            @forelse($users as $user)
                <x-table-row>
                    <x-table-cell>{{ $user->id }}</x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center">
                            <div
                                class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-gray-500 text-xs">{{ $user->email }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>{{ $user->email }}</x-table-cell>
                    <x-table-cell>
                        @forelse($user->cargos as $cargo)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mr-1">
                                {{ $cargo->nome }}
                            </span>
                        @empty
                            <span class="text-gray-400">Sem cargo</span>
                        @endforelse
                    </x-table-cell>
                    <x-table-cell>{{ $user->created_at->format('d/m/Y H:i') }}</x-table-cell>
                    <x-table-cell align="right">
                        <div class="flex justify-end space-x-2">
                            @permission('usuarios.listar')
                                <a href="{{ route('usuarios.show', $user) }}" class="text-indigo-600 hover:text-indigo-900"
                                    title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endpermission

                            @permission('usuarios.editar')
                                <a href="{{ route('usuarios.edit', $user) }}" class="text-yellow-600 hover:text-yellow-900"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endpermission

                            @permission('usuarios.excluir')
                                <button type="button" 
                                    onclick="confirmToggleStatus('{{ $user->id }}', '{{ $user->name }}', '{{ $user->ativo ? 'inativar' : 'ativar' }}', '{{ route('usuarios.toggle-status', $user) }}')"
                                    class="{{ $user->ativo ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}" 
                                    title="{{ $user->ativo ? 'Inativar' : 'Ativar' }}"
                                    @if ($user->id === auth()->id()) disabled @endif>
                                    <i class="fas {{ $user->ativo ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                </button>
                            @endpermission
                        </div>
                    </x-table-cell>
                </x-table-row>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Nenhum usuário encontrado.
                    </td>
                </tr>
            @endforelse
        </x-table>

        <!-- Layout mobile otimizado com cards -->
        <div class="md:hidden space-y-4">
            @forelse($users as $user)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <!-- Header do card -->
                    <div class="flex items-center mb-4">
                        <div
                            class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                            <i class="fas fa-user text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-base">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-500">ID: {{ $user->id }}</p>
                        </div>
                    </div>

                    <!-- Informações do usuário -->
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-gray-400 w-5 mr-2"></i>
                            <span class="text-sm text-gray-900">{{ $user->email }}</span>
                        </div>

                        <div class="flex items-center">
                            <i class="fas fa-briefcase text-gray-400 w-5 mr-2"></i>
                            @forelse($user->cargos as $cargo)
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mr-1">
                                    {{ $cargo->nome }}
                                </span>
                            @empty
                                <span class="text-gray-500 text-sm">Sem cargo</span>
                            @endforelse
                        </div>

                        <div class="flex items-center">
                            <i class="fas fa-calendar text-gray-400 w-5 mr-2"></i>
                            <span class="text-sm text-gray-600">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <!-- Botões de ação com touch targets otimizados -->
                    <div class="w-full overflow-hidden">
                        <div class="flex space-x-2">
                            @permission('usuarios.visualizar')
                                <a href="{{ route('usuarios.show', $user) }}"
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                                    <i class="fas fa-eye mr-2"></i>
                                    Visualizar
                                </a>
                            @endpermission
                            @permission('usuarios.editar')
                                <a href="{{ route('usuarios.edit', $user) }}"
                                    class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                                    <i class="fas fa-edit mr-2"></i>
                                    Editar
                                </a>
                            @endpermission
                            @permission('usuarios.excluir')
                                <button type="button"
                                    onclick="confirmToggleStatus('{{ $user->id }}', '{{ $user->name }}', '{{ $user->ativo ? 'inativar' : 'ativar' }}', '{{ route('usuarios.toggle-status', $user) }}')"
                                    class="flex-1 {{ $user->ativo ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors"
                                    @if ($user->id === auth()->id()) disabled @endif>
                                    <i class="fas {{ $user->ativo ? 'fa-user-slash' : 'fa-user-check' }} mr-2"></i>
                                    {{ $user->ativo ? 'Inativar' : 'Ativar' }}
                                </button>
                            @endpermission
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-users text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">Nenhum usuário encontrado.</p>
                </div>
            @endforelse
        </div>

        <!-- Paginação responsiva -->
        <div class="mt-6">
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
                <!-- Resumo dos resultados -->
                <div class="text-sm text-gray-700">
                    Mostrando {{ $users->firstItem() ?? 0 }} a {{ $users->lastItem() ?? 0 }} de {{ $users->total() }}
                    usuários
                </div>

                <!-- Links de paginação -->
        <div class="flex justify-center">
            {{ $users->links('components.pagination') }}
        </div>
    </div>
</div>
            </div> <!-- data-ajax-content -->
        </div> <!-- usuarios-list-wrapper -->
    </x-card>

<x-toggle-status-modal />

<script>
    // AJAX bindings para ordenação e paginação na lista de usuários
    function showUsuariosLoading() {
        const wrapper = document.getElementById('usuarios-list-wrapper');
        if (!wrapper) return;
        const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
        if (overlay) overlay.classList.remove('hidden');
        wrapper.style.pointerEvents = 'none';
    }

    function hideUsuariosLoading() {
        const wrapper = document.getElementById('usuarios-list-wrapper');
        if (!wrapper) return;
        const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
        if (overlay) overlay.classList.add('hidden');
        wrapper.style.pointerEvents = '';
    }

    function updateUsuariosContainer(url, pushState = true) {
        const wrapper = document.getElementById('usuarios-list-wrapper');
        if (!wrapper) { window.location.href = url; return; }
        const ajaxArea = wrapper.querySelector('[data-ajax-content]');
        if (!ajaxArea) { window.location.href = url; return; }

        showUsuariosLoading();
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(resp => resp.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newWrapper = doc.querySelector('#usuarios-list-wrapper');
                const newAjaxArea = newWrapper ? newWrapper.querySelector('[data-ajax-content]') : null;
                if (newAjaxArea) {
                    ajaxArea.innerHTML = newAjaxArea.innerHTML;
                    if (pushState) window.history.pushState(null, '', url);
                    initUsuariosAjaxBindings();
                } else {
                    window.location.href = url;
                }
            })
            .catch(() => { window.location.href = url; })
            .finally(() => { hideUsuariosLoading(); });
    }

    function initUsuariosAjaxBindings() {
        const wrapper = document.getElementById('usuarios-list-wrapper');
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
                updateUsuariosContainer(this.href);
            });
        });

        // Interceptar paginação
        const paginationLinks = ajaxArea.querySelectorAll('nav[aria-label="Pagination Navigation"] a[href]');
        paginationLinks.forEach(link => {
            if (link.dataset.ajaxBound === '1') return;
            link.dataset.ajaxBound = '1';
            link.addEventListener('click', function(e) {
                e.preventDefault();
                updateUsuariosContainer(this.href);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initUsuariosAjaxBindings();
        window.addEventListener('popstate', function() {
            updateUsuariosContainer(window.location.href, false);
        });
    });
</script>
@endsection
