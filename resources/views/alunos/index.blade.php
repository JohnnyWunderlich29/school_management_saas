@extends('layouts.app')

@section('content')
    <x-card>
        <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
            <div>
                <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Alunos</h1>
                <p class="mt-1 text-sm text-gray-600">Gerenciamento de alunos</p>
            </div>
            <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
                @if(request()->has('mostrar_inativos'))
                    <x-button href="{{ route('alunos.index', request()->except('mostrar_inativos')) }}" color="secondary" class="w-auto sm:justify-center">
                        <i class="fas fa-eye mr-1"></i> 
                        <span class="hidden md:inline">Apenas Ativos</span>
                        <span class="md:hidden">Ativos</span>
                    </x-button>
                @else
                    <x-button href="{{ route('alunos.index', array_merge(request()->all(), ['mostrar_inativos' => '1'])) }}" color="secondary" class="w-auto sm:justify-center">
                        <i class="fas fa-eye-slash mr-1"></i> 
                        <span class="hidden md:inline">Mostrar Todos</span>
                        <span class="md:hidden">Todos</span>
                    </x-button>
                @endif
                <x-button href="{{ route('alunos.create') }}" color="primary" class="w-auto sm:justify-center">
                    <i class="fas fa-plus mr-1"></i> 
                    <span class="hidden md:inline">Novo Aluno</span>
                    <span class="md:hidden">Novo</span>
                </x-button>
            </div>
        </div>

        <x-collapsible-filter 
            title="Filtros de Alunos" 
            :action="route('alunos.index')" 
            :clear-route="route('alunos.index')"
            target="alunos-list-wrapper"
        >
            <x-filter-field 
                name="nome" 
                label="Nome" 
                placeholder="Buscar por nome..."
            />
            
            <x-filter-field 
                name="ativo" 
                label="Status" 
                type="select"
                :options="['true' => 'Ativos', 'false' => 'Inativos']"
            />
        </x-collapsible-filter>

        <div id="alunos-list-wrapper" class="relative">
            <x-loading-overlay message="Atualizando alunos..." />
            <div data-ajax-content>
        <!-- Desktop Table - Hidden on mobile -->
        <div class="hidden md:block">
            <x-table 
                :headers="[
                    ['label' => 'ID', 'sort' => 'id'],
                    ['label' => 'Nome', 'sort' => 'nome'],
                    ['label' => 'Data de Nascimento', 'sort' => 'data_nascimento'],
                    ['label' => 'Telefone', 'sort' => 'telefone'],
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
                @forelse($alunos as $index => $aluno)
                    <x-table-row :striped="true" :index="$index">
                        <x-table-cell>{{ $aluno->id }}</x-table-cell>
                        <x-table-cell>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $aluno->nome }} {{ $aluno->sobrenome }}</div>
                                    @if($aluno->email)
                                        <div class="text-gray-500 text-xs">{{ $aluno->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>{{ $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->format('d/m/Y') : '-' }}</x-table-cell>
                        <x-table-cell>{{ $aluno->telefone ?: '-' }}</x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $aluno->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $aluno->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('alunos.show', $aluno->id) }}" class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('alunos.edit', $aluno->id) }}" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form id="toggle-form-{{ $aluno->id }}" action="{{ route('alunos.toggleStatus', $aluno->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" onclick="openToggleModal({{ $aluno->id }}, '{{ $aluno->nome }} {{ $aluno->sobrenome }}', {{ $aluno->ativo ? 'true' : 'false' }})" class="{{ $aluno->ativo ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900' }}" title="{{ $aluno->ativo ? 'Inativar' : 'Ativar' }} aluno">
                                        <i class="fas {{ $aluno->ativo ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhum aluno encontrado.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        <!-- Mobile Cards - Visible only on mobile -->
        <div class="md:hidden space-y-3">
            @forelse($alunos as $aluno)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow duration-200">
                    <!-- Header com avatar, nome e status -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center flex-1 min-w-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white mr-3 flex-shrink-0 shadow-sm">
                                <i class="fas fa-user-graduate text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 truncate leading-tight">{{ $aluno->nome }} {{ $aluno->sobrenome }}</h3>
                                @if($aluno->email)
                                    <p class="text-sm text-gray-500 truncate mt-0.5">{{ $aluno->email }}</p>
                                @endif
                                <div class="flex items-center mt-1">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $aluno->ativo ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $aluno->ativo ? 'bg-green-500' : 'bg-red-500' }} mr-1.5"></span>
                                        {{ $aluno->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Informações organizadas em grid -->
                    <div class="bg-gray-50 rounded-lg p-3 mb-4 space-y-2">
                        <div class="flex items-center justify-between py-1">
                            <span class="text-sm text-gray-600 font-medium">ID</span>
                            <span class="text-sm font-semibold text-gray-900">#{{ $aluno->id }}</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex items-center justify-between py-1">
                                <span class="text-sm text-gray-600 font-medium flex items-center">
                                    <i class="fas fa-calendar-alt text-gray-400 mr-2 text-xs"></i>
                                    Nascimento
                                </span>
                                <span class="text-sm font-semibold text-gray-900">{{ $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->format('d/m/Y') : '-' }}</span>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex items-center justify-between py-1">
                                <span class="text-sm text-gray-600 font-medium flex items-center">
                                    <i class="fas fa-phone text-gray-400 mr-2 text-xs"></i>
                                    Telefone
                                </span>
                                <span class="text-sm font-semibold text-gray-900">{{ $aluno->telefone ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botões de ação otimizados para touch -->
                    <div class="grid grid-cols-3 gap-2">
                        <a href="{{ route('alunos.show', $aluno->id) }}" 
                           class="bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-center py-3 px-3 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-all duration-150 shadow-sm hover:shadow-md">
                            <i class="fas fa-eye text-sm"></i>
                            <span class="ml-1.5 hidden xs:inline">Ver</span>
                        </a>
                        <a href="{{ route('alunos.edit', $aluno->id) }}" 
                           class="bg-yellow-500 hover:bg-yellow-600 active:bg-yellow-700 text-yellow-50 text-center py-3 px-3 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-all duration-150 shadow-sm hover:shadow-md">
                            <i class="fas fa-edit text-sm text-white"></i>
                            <span class="ml-1.5 hidden xs:inline text-yellow-50">Editar</span>
                        </a>
                        <form id="toggle-form-mobile-{{ $aluno->id }}" action="{{ route('alunos.toggleStatus', $aluno->id) }}" method="POST" class="">
                            @csrf
                            @method('PATCH')
                            <button type="button" onclick="openToggleModal({{ $aluno->id }}, '{{ $aluno->nome }} {{ $aluno->sobrenome }}', {{ $aluno->ativo ? 'true' : 'false' }})" 
                                    class="w-full {{ $aluno->ativo ? 'bg-orange-500 hover:bg-orange-600 active:bg-orange-700' : 'bg-green-500 hover:bg-green-600 active:bg-green-700' }} text-white text-center py-3 px-3 rounded-lg font-medium text-sm min-h-[44px] flex items-center justify-center transition-all duration-150 shadow-sm hover:shadow-md">
                                <i class="fas {{ $aluno->ativo ? 'fa-user-slash' : 'fa-user-check' }} text-sm"></i>
                                <span class="ml-1.5 hidden xs:inline">{{ $aluno->ativo ? 'Inativar' : 'Ativar' }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                    <div class="text-gray-500">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-user-graduate text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum aluno encontrado</h3>
                        <p class="text-sm text-gray-500 mb-4">Tente ajustar os filtros ou adicione novos alunos</p>
                        <a href="{{ route('alunos.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Adicionar Aluno
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $alunos->links('components.pagination') }}
        </div>
            </div>
        </div>
    </x-card>

    <!-- Modal de Confirmação -->
    <div id="toggleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                    <i id="modalIcon" class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4" id="modalTitle">Confirmar Ação</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="modalMessage">
                        Tem certeza que deseja realizar esta ação?
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="confirmButton" class="px-4 py-2 bg-orange-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-300">
                        Sim
                    </button>
                    <button id="cancelButton" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentAlunoId = null;
        
        function openToggleModal(alunoId, alunoNome, isAtivo) {
            currentAlunoId = alunoId;
            const modal = document.getElementById('toggleModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalIcon = document.getElementById('modalIcon');
            const confirmButton = document.getElementById('confirmButton');
            
            if (isAtivo) {
                modalTitle.textContent = 'Inativar Aluno';
                modalMessage.textContent = `Tem certeza que deseja inativar o aluno ${alunoNome}?`;
                modalIcon.className = 'fas fa-user-slash text-orange-600 text-xl';
                confirmButton.className = 'px-4 py-2 bg-orange-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-300';
                confirmButton.textContent = 'Inativar';
            } else {
                modalTitle.textContent = 'Ativar Aluno';
                modalMessage.textContent = `Tem certeza que deseja ativar o aluno ${alunoNome}?`;
                modalIcon.className = 'fas fa-user-check text-green-600 text-xl';
                confirmButton.className = 'px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300';
                confirmButton.textContent = 'Ativar';
            }
            
            modal.classList.remove('hidden');
        }
        
        function closeToggleModal() {
            const modal = document.getElementById('toggleModal');
            modal.classList.add('hidden');
            currentAlunoId = null;
        }
        
        function confirmToggle() {
            if (currentAlunoId) {
                // Tentar primeiro o formulário desktop
                let form = document.getElementById(`toggle-form-${currentAlunoId}`);
                // Se não encontrar, tentar o formulário mobile
                if (!form) {
                    form = document.getElementById(`toggle-form-mobile-${currentAlunoId}`);
                }
                
                if (form) {
                    form.submit();
                }
            }
            closeToggleModal();
        }
        
        // Event listeners
        document.getElementById('confirmButton').addEventListener('click', confirmToggle);
        document.getElementById('cancelButton').addEventListener('click', closeToggleModal);
        
        // Fechar modal ao clicar fora dele
        document.getElementById('toggleModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeToggleModal();
            }
        });
        
        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeToggleModal();
            }
        });
    </script>
    <script>
        // AJAX bindings para ordenação e paginação na lista de alunos
        function showAlunosLoading() {
            const wrapper = document.getElementById('alunos-list-wrapper');
            if (!wrapper) return;
            const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
            if (overlay) overlay.classList.remove('hidden');
            wrapper.style.pointerEvents = 'none';
        }

        function hideAlunosLoading() {
            const wrapper = document.getElementById('alunos-list-wrapper');
            if (!wrapper) return;
            const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
            if (overlay) overlay.classList.add('hidden');
            wrapper.style.pointerEvents = '';
        }

        function updateAlunosContainer(url, pushState = true) {
            const wrapper = document.getElementById('alunos-list-wrapper');
            if (!wrapper) { window.location.href = url; return; }
            const ajaxArea = wrapper.querySelector('[data-ajax-content]');
            if (!ajaxArea) { window.location.href = url; return; }

            showAlunosLoading();
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(resp => resp.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newWrapper = doc.querySelector('#alunos-list-wrapper');
                    const newAjaxArea = newWrapper ? newWrapper.querySelector('[data-ajax-content]') : null;
                    if (newAjaxArea) {
                        ajaxArea.innerHTML = newAjaxArea.innerHTML;
                        if (pushState) window.history.pushState(null, '', url);
                        initAlunoAjaxBindings();
                    } else {
                        window.location.href = url;
                    }
                })
                .catch(() => { window.location.href = url; })
                .finally(() => { hideAlunosLoading(); });
        }

        function initAlunoAjaxBindings() {
            const wrapper = document.getElementById('alunos-list-wrapper');
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
                    updateAlunosContainer(url);
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
                    updateAlunosContainer(url);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            initAlunoAjaxBindings();
            window.addEventListener('popstate', function() {
                updateAlunosContainer(window.location.href, false);
            });
        });
    </script>
@endsection