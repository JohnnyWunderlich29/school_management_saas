@extends('layouts.app')

@section('content')
    <x-card>
        <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
            <div>
                <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Responsáveis</h1>
                <p class="mt-1 text-sm text-gray-600">Gerenciamento de responsáveis</p>
            </div>
            <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
                @if(request()->has('mostrar_inativos'))
                    <x-button href="{{ route('responsaveis.index', request()->except('mostrar_inativos')) }}" color="secondary" class="w-auto sm:justify-center">
                        <i class="fas fa-eye mr-1"></i> 
                        <span class="hidden md:inline">Apenas Ativos</span>
                        <span class="md:hidden">Ativos</span>
                    </x-button>
                @else
                    <x-button href="{{ route('responsaveis.index', array_merge(request()->all(), ['mostrar_inativos' => '1'])) }}" color="secondary" class="w-auto sm:justify-center">
                        <i class="fas fa-eye-slash mr-1"></i> 
                        <span class="hidden md:inline">Mostrar Todos</span>
                        <span class="md:hidden">Todos</span>
                    </x-button>
                @endif
                <x-button href="{{ route('responsaveis.create') }}" color="primary" class="w-auto sm:justify-center">
                    <i class="fas fa-plus mr-1"></i> 
                    <span class="hidden md:inline">Novo Responsável</span>
                    <span class="md:hidden">Novo</span>
                </x-button>
            </div>
        </div>

        <x-collapsible-filter title="Filtros de Responsáveis" :action="route('responsaveis.index')" :clear-route="route('responsaveis.index')" target="responsaveis-list-wrapper">
            <x-filter-field name="nome" label="Nome" type="text" placeholder="Buscar por nome..." />
            
            <x-filter-field 
                name="ativo" 
                label="Status" 
                type="select"
                :options="['true' => 'Ativos', 'false' => 'Inativos']"
            />

            <x-filter-field name="parentesco" label="Parentesco" type="select" empty-option="Todos" :options="[
                'Pai' => 'Pai',
                'Mãe' => 'Mãe',
                'Avô' => 'Avô',
                'Avó' => 'Avó',
                'Tio' => 'Tio',
                'Tia' => 'Tia',
                'Responsável Legal' => 'Responsável Legal',
                'Outro' => 'Outro',
            ]" />
        </x-collapsible-filter>
        <div id="responsaveis-list-wrapper" class="relative">
            <x-loading-overlay message="Atualizando responsáveis..." />
            <div data-ajax-content>
        <!-- Tabela para desktop -->
        <div class="hidden md:block">
            <x-table 
                :headers="[
                    ['label' => 'ID', 'sort' => 'id'],
                    ['label' => 'Nome', 'sort' => 'nome'],
                    ['label' => 'Parentesco', 'sort' => 'parentesco'],
                    'Contato',
                    'Permissões',
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
                @forelse($responsaveis as $index => $responsavel)
                    <x-table-row :striped="true" :index="$index">
                        <x-table-cell>{{ $responsavel->id }}</x-table-cell>
                        <x-table-cell>
                            <div class="flex items-center">
                                <div
                                    class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-500 mr-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $responsavel->nome }}
                                        {{ $responsavel->sobrenome }}</div>
                                    @if ($responsavel->email)
                                        <div class="text-gray-500 text-xs">{{ $responsavel->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>{{ $responsavel->parentesco }}</x-table-cell>
                        <x-table-cell>
                            <div>{{ $responsavel->telefone_principal }}</div>
                            @if ($responsavel->telefone_secundario)
                                <div class="text-gray-500 text-xs">{{ $responsavel->telefone_secundario }}</div>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            <div class="flex flex-col space-y-1">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $responsavel->autorizado_buscar ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $responsavel->autorizado_buscar ? 'Pode buscar' : 'Não pode buscar' }}
                                </span>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $responsavel->contato_emergencia ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $responsavel->contato_emergencia ? 'Contato de emergência' : 'Não é contato de emergência' }}
                                </span>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $responsavel->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <i class="fas fa-{{ $responsavel->ativo ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                {{ $responsavel->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('responsaveis.show', $responsavel->id) }}"
                                    class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('responsaveis.edit', $responsavel->id) }}"
                                    class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        onclick="confirmToggleStatus('responsável', '{{ $responsavel->nome }} {{ $responsavel->sobrenome }}', {{ $responsavel->ativo ? 'true' : 'false' }}, '{{ route('responsaveis.toggle-status', $responsavel->id) }}')"
                                        class="{{ $responsavel->ativo ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900' }}" title="{{ $responsavel->ativo ? 'Inativar' : 'Ativar' }}">
                                    <i class="fas {{ $responsavel->ativo ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                </button>
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhum responsável encontrado.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        <!-- Layout mobile otimizado com cards -->
        <div class="md:hidden space-y-4">
            @forelse($responsaveis as $responsavel)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <!-- Header do card -->
                    <div class="flex items-center mb-4">
                        <div
                            class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-500 mr-3">
                            <i class="fas fa-user text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-base">{{ $responsavel->nome }}
                                {{ $responsavel->sobrenome }}</h3>
                            <div class="flex items-center space-x-2">
                                <p class="text-sm text-gray-500">{{ $responsavel->parentesco }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $responsavel->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="fas fa-{{ $responsavel->ativo ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                    {{ $responsavel->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>
                        <div class="text-xs text-gray-400">
                            #{{ $responsavel->id }}
                        </div>
                    </div>

                    <!-- Informações de contato -->
                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-phone text-blue-500 w-4 mr-2"></i>
                            <span class="text-sm text-gray-900">{{ $responsavel->telefone_principal }}</span>
                        </div>
                        @if ($responsavel->telefone_secundario)
                            <div class="flex items-center mb-2">
                                <i class="fas fa-phone text-blue-400 w-4 mr-2"></i>
                                <span class="text-sm text-gray-600">{{ $responsavel->telefone_secundario }}</span>
                            </div>
                        @endif
                        @if ($responsavel->email)
                            <div class="flex items-center">
                                <i class="fas fa-envelope text-purple-500 w-4 mr-2"></i>
                                <span class="text-sm text-gray-600">{{ $responsavel->email }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Permissões em grid -->
                    <div class="grid grid-cols-1 gap-2 mb-4">
                        <div
                            class="flex items-center justify-between p-2 {{ $responsavel->autorizado_buscar ? 'bg-green-50' : 'bg-red-50' }} rounded">
                            <div class="flex items-center">
                                <i
                                    class="fas fa-{{ $responsavel->autorizado_buscar ? 'check-circle' : 'times-circle' }} text-{{ $responsavel->autorizado_buscar ? 'green' : 'red' }}-500 mr-2"></i>
                                <span
                                    class="text-sm font-medium text-{{ $responsavel->autorizado_buscar ? 'green' : 'red' }}-700">Buscar
                                    Aluno</span>
                            </div>
                            <span
                                class="text-xs text-{{ $responsavel->autorizado_buscar ? 'green' : 'red' }}-600">{{ $responsavel->autorizado_buscar ? 'Autorizado' : 'Não autorizado' }}</span>
                        </div>
                        <div
                            class="flex items-center justify-between p-2 {{ $responsavel->contato_emergencia ? 'bg-blue-50' : 'bg-gray-50' }} rounded">
                            <div class="flex items-center">
                                <i
                                    class="fas fa-{{ $responsavel->contato_emergencia ? 'exclamation-triangle' : 'user-friends' }} text-{{ $responsavel->contato_emergencia ? 'blue' : 'gray' }}-500 mr-2"></i>
                                <span
                                    class="text-sm font-medium text-{{ $responsavel->contato_emergencia ? 'blue' : 'gray' }}-700">Emergência</span>
                            </div>
                            <span
                                class="text-xs text-{{ $responsavel->contato_emergencia ? 'blue' : 'gray' }}-600">{{ $responsavel->contato_emergencia ? 'Sim' : 'Não' }}</span>
                        </div>
                    </div>

                    <!-- Botões de ação otimizados para touch -->
                    <div class="grid grid-cols-3 gap-2">
                        <a href="{{ route('responsaveis.show', $responsavel->id) }}" 
                           class="bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-center py-3 px-3 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-all duration-150 shadow-sm hover:shadow-md">
                            <i class="fas fa-eye text-sm"></i>
                            <span class="ml-1.5 hidden xs:inline">Ver</span>
                        </a>
                        <a href="{{ route('responsaveis.edit', $responsavel->id) }}" 
                           class="bg-yellow-500 hover:bg-yellow-600 active:bg-yellow-700 text-yellow-50 text-center py-3 px-3 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-all duration-150 shadow-sm hover:shadow-md">
                            <i class="fas fa-edit text-sm text-white"></i>
                            <span class="ml-1.5 hidden xs:inline text-yellow-50">Editar</span>
                        </a>
                        <button type="button" 
                                onclick="confirmToggleStatus('responsável', '{{ $responsavel->nome }} {{ $responsavel->sobrenome }}', {{ $responsavel->ativo ? 'true' : 'false' }}, '{{ route('responsaveis.toggle-status', $responsavel->id) }}')"
                                class="w-full {{ $responsavel->ativo ? 'bg-orange-500 hover:bg-orange-600 active:bg-orange-700' : 'bg-green-500 hover:bg-green-600 active:bg-green-700' }} text-white text-center py-3 px-3 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-all duration-150 shadow-sm hover:shadow-md">
                            <i class="fas {{ $responsavel->ativo ? 'fa-user-slash' : 'fa-user-check' }} text-sm"></i>
                            <span class="ml-1.5 hidden xs:inline">{{ $responsavel->ativo ? 'Inativar' : 'Ativar' }}</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-users text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">Nenhum responsável encontrado.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $responsaveis->links('components.pagination') }}
        </div>
            </div>
        </div>
    </x-card>

    <!-- Modal de confirmação para toggle de status -->
    <x-toggle-status-modal />

    <script>
        // AJAX bindings para ordenação e paginação na lista de responsáveis
        function showResponsaveisLoading() {
            const wrapper = document.getElementById('responsaveis-list-wrapper');
            if (!wrapper) return;
            const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
            if (overlay) overlay.classList.remove('hidden');
            wrapper.style.pointerEvents = 'none';
        }

        function hideResponsaveisLoading() {
            const wrapper = document.getElementById('responsaveis-list-wrapper');
            if (!wrapper) return;
            const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
            if (overlay) overlay.classList.add('hidden');
            wrapper.style.pointerEvents = '';
        }

        function updateResponsaveisContainer(url, pushState = true) {
            const wrapper = document.getElementById('responsaveis-list-wrapper');
            if (!wrapper) { window.location.href = url; return; }
            const ajaxArea = wrapper.querySelector('[data-ajax-content]');
            if (!ajaxArea) { window.location.href = url; return; }

            showResponsaveisLoading();
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(resp => resp.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newWrapper = doc.querySelector('#responsaveis-list-wrapper');
                    const newAjaxArea = newWrapper ? newWrapper.querySelector('[data-ajax-content]') : null;
                    if (newAjaxArea) {
                        ajaxArea.innerHTML = newAjaxArea.innerHTML;
                        if (pushState) window.history.pushState(null, '', url);
                        initResponsavelAjaxBindings();
                    } else {
                        window.location.href = url;
                    }
                })
                .catch(() => { window.location.href = url; })
                .finally(() => { hideResponsaveisLoading(); });
        }

        function initResponsavelAjaxBindings() {
            const wrapper = document.getElementById('responsaveis-list-wrapper');
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
                    updateResponsaveisContainer(url);
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
                    updateResponsaveisContainer(url);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initResponsavelAjaxBindings();
            window.addEventListener('popstate', function() {
                updateResponsaveisContainer(window.location.href, false);
            });
        });

        // Fallback: garantir que confirmToggleStatus exista mesmo antes do componente carregar
        if (typeof window.confirmToggleStatus !== 'function') {
            window.confirmToggleStatus = function(entityType, entityName, currentStatus, route, method = 'PATCH') {
                // Se o modal já iniciou via Alpine, use diretamente
                if (window.toggleStatusModal && typeof window.toggleStatusModal.show === 'function') {
                    window.toggleStatusModal.show({ entityType, entityName, currentStatus, route, method });
                } else {
                    // Guarda dados até o modal inicializar e tenta abrir
                    window.__toggleStatusPending = { entityType, entityName, currentStatus, route, method };
                    if (typeof showModal === 'function') { showModal('toggle-status-modal'); }
                }
            };

            // Quando Alpine inicializar o componente, consome qualquer pendência
            document.addEventListener('alpine:init', function() {
                if (window.__toggleStatusPending && window.toggleStatusModal) {
                    window.toggleStatusModal.show(window.__toggleStatusPending);
                    delete window.__toggleStatusPending;
                }
            });
        }
    </script>
@endsection
