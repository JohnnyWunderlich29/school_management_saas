@extends('layouts.app')

@section('title', 'Turmas')

@section('content')
<style>
    /* Estilos para Drag & Drop */
    .draggable {
        cursor: move;
        transition: all 0.2s ease;
    }
    
    .draggable:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .dragging {
        opacity: 0.5;
        transform: rotate(5deg);
    }
    
    .drop-zone-active {
        border: 2px dashed #3B82F6 !important;
        background-color: #EFF6FF !important;
    }
    
    .drop-zone-hover {
        border: 2px solid #3B82F6 !important;
        background-color: #DBEAFE !important;
        transform: scale(1.02);
    }
    
    /* Animações para toasts */
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    .toast-enter {
        animation: slideInRight 0.3s ease-out;
    }
    
    /* Indicadores visuais de status */
    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    .status-ativo {
        background-color: #10B981;
    }
    
    .status-inativo {
        background-color: #EF4444;
    }
</style>


    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-3a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Turmas</span>
                </div>
            </li>
        </ol>
    </nav>
    
<x-card>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gerenciar Turmas</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie as turmas da escola</p>
        </div>
        <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nova Turma
        </button>
    </div>

    <x-collapsible-filter 
        title="Filtros de Turmas" 
        :action="route('turmas.index')" 
        :clear-route="route('turmas.index')"
        target="turmas-list-wrapper"
    >
        <x-filter-field 
            name="buscar" 
            label="Nome da Turma" 
            placeholder="Buscar por nome da turma..."
        />
        
        <x-filter-field 
            name="turno_id" 
            label="Turno" 
            type="select"
            :options="$turnos->pluck('nome', 'id')->prepend('Todos os turnos', '')"
        />
        
        <x-filter-field 
            name="nivel_ensino_id" 
            label="Nível de Ensino" 
            type="select"
            :options="$niveisEnsino->pluck('nome', 'id')->prepend('Todos os níveis', '')"
        />
        
        <x-filter-field 
            name="status" 
            label="Status" 
            type="select"
            :options="['' => 'Todos', '1' => 'Ativas', '0' => 'Inativas']"
        />
    </x-collapsible-filter>

    <!-- Lista de Turmas -->
    <div id="turmas-list-wrapper" data-ajax-content>
        <x-loading-overlay id="turmas-loading-overlay"/>

        <!-- Barra de Ordenação -->
        @php
            $currentSort = request('sort');
            if (!$currentSort && request('ordenar')) {
                $mapOrdenar = ['nome' => 'nome', 'codigo' => 'codigo', 'ocupacao' => 'ocupacao', 'criado' => 'created_at'];
                $currentSort = $mapOrdenar[request('ordenar')] ?? 'nome';
            }
            $currentSort = $currentSort ?: 'nome';
            $currentDirection = strtolower(request('direction', $currentSort === 'created_at' ? 'desc' : 'asc'));
        @endphp
        <div class="flex items-center justify-between mb-4">
            <div class="text-sm text-gray-600">
                Ordenar por:
                @php
                    $sorts = [
                        ['key' => 'nome', 'label' => 'Nome'],
                        ['key' => 'codigo', 'label' => 'Código'],
                        ['key' => 'ocupacao', 'label' => 'Ocupação'],
                        ['key' => 'turno', 'label' => 'Turno'],
                        ['key' => 'nivel_ensino', 'label' => 'Nível de Ensino'],
                        ['key' => 'created_at', 'label' => 'Criado em'],
                    ];
                @endphp
                @foreach($sorts as $s)
                    @php
                        $isCurrent = $currentSort === $s['key'];
                        $nextDir = $isCurrent && $currentDirection === 'asc' ? 'desc' : 'asc';
                    @endphp
                    <a 
                        href="{{ route('turmas.index', array_merge(request()->query(), ['sort' => $s['key'], 'direction' => $nextDir])) }}"
                        class="sort-link inline-flex items-center px-2 py-1 rounded hover:bg-gray-100 ml-2 {{ $isCurrent ? 'text-blue-600 font-medium' : 'text-gray-700' }}"
                        data-sort="{{ $s['key'] }}"
                        data-direction="{{ $nextDir }}"
                        aria-current="{{ $isCurrent ? 'true' : 'false' }}"
                        title="Ordenar por {{ $s['label'] }}"
                    >
                        {{ $s['label'] }}
                        @if($isCurrent)
                            <i class="fas fa-sort-{{ $currentDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                        @else
                            <i class="fas fa-sort ml-1 text-gray-400"></i>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($turmas as $turma)
            <div class="turma-card bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200"
                 data-turma-id="{{ $turma->id }}"
                 data-turma-nome="{{ $turma->nome }}"
                 data-turma-capacidade="{{ $turma->capacidade }}"
                 data-turma-ocupacao="{{ $turma->alunos_count }}"
                 ondrop="dropAluno(event)"
                 ondragover="allowDrop(event)"
                 ondragenter="dragEnter(event)"
                 ondragleave="dragLeave(event)">
                
                <!-- Header do Card -->
                <div class="p-4 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $turma->nome }}</h3>
                            <div class="flex items-center text-sm text-gray-600">
                                <span class="status-indicator {{ $turma->ativo ? 'status-ativo' : 'status-inativo' }}"></span>
                                {{ $turma->ativo ? 'Ativa' : 'Inativa' }}
                            </div>
                        </div>
                        <div class="relative">
                            <button onclick="toggleDropdown('dropdown-{{ $turma->id }}')" class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                </svg>
                            </button>
                            <div id="dropdown-{{ $turma->id }}" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200 hidden">
                                <div class="py-1">
                                    <button onclick="verAlunos({{ $turma->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-users mr-2"></i>
                                        Ver Alunos
                                    </button>
                                    <button onclick="adicionarAluno({{ $turma->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user-plus mr-2"></i>
                                        Adicionar Aluno
                                    </button>
                                    <div class="border-t border-gray-100"></div>
                                    <button onclick="gerarRelatorioTurma({{ $turma->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-file-pdf mr-2"></i>
                                        Relatório
                                    </button>
                                    <div class="border-t border-gray-100"></div>
                                    <button onclick="viewTurma({{ $turma->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-eye mr-2"></i>
                                        Visualizar
                                    </button>
                                    <button onclick="editTurma({{ $turma->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-edit mr-2"></i>
                                        Editar
                                    </button>
                                    <button onclick="deleteTurma({{ $turma->id }}, '{{ $turma->nome }}')" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <i class="fas fa-trash mr-2"></i>
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações da Turma -->
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Turno</p>
                            <p class="text-sm font-medium text-gray-900">{{ $turma->turno->nome ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Nível de Ensino</p>
                            <p class="text-sm font-medium text-gray-900">{{ $turma->nivelEnsino->nome ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Indicador de Capacidade -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Ocupação</span>
                            <span class="text-sm font-medium text-gray-900">{{ $turma->alunos_count }}/{{ $turma->capacidade ?? 'N/A' }}</span>
                        </div>
                        @if($turma->capacidade)
                            @php
                                $percentual = ($turma->alunos_count / $turma->capacidade) * 100;
                                $corBarra = $percentual >= 100 ? 'bg-red-500' : ($percentual >= 80 ? 'bg-yellow-500' : 'bg-green-500');
                            @endphp
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="{{ $corBarra }} h-2 rounded-full transition-all duration-300" style="width: {{ min($percentual, 100) }}%"></div>
                            </div>
                            @if($percentual >= 100)
                                <p class="text-xs text-red-600 mt-1">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Turma lotada
                                </p>
                            @elseif($percentual >= 80)
                                <p class="text-xs text-yellow-600 mt-1">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Quase lotada
                                </p>
                            @else
                                <p class="text-xs text-green-600 mt-1">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ $turma->capacidade - $turma->alunos_count }} vagas disponíveis
                                </p>
                            @endif
                        @endif
                    </div>

                    <!-- Lista de Alunos (Drag & Drop) -->
                    @if($turma->alunos->count() > 0)
                        <div class="space-y-2">
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Alunos</p>
                            <div class="max-h-32 overflow-y-auto space-y-1">
                                @foreach($turma->alunos->take(5) as $aluno)
                                    <div class="draggable flex items-center justify-between p-2 bg-gray-50 rounded text-sm"
                                         draggable="true"
                                         data-aluno-id="{{ $aluno->id }}"
                                         data-aluno-nome="{{ $aluno->nome . ' ' . $aluno->sobrenome }}"
                                         data-turma-origem="{{ $turma->id }}"
                                         ondragstart="dragStart(event)"
                                         ondragend="dragEnd(event)">
                                        <div class="flex items-center">
                                            <i class="fas fa-grip-vertical text-gray-400 mr-2"></i>
                                            <span class="text-gray-900">{{ $aluno->nome . ' ' . $aluno->sobrenome }}</span>
                                        </div>
                                        @if(!$aluno->ativo)
                                            <span class="text-xs text-yellow-600">(inativo)</span>
                                        @endif
                                    </div>
                                @endforeach
                                @if($turma->alunos->count() > 5)
                                    <p class="text-xs text-gray-500 text-center py-1">
                                        +{{ $turma->alunos->count() - 5 }} alunos...
                                    </p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-gray-500">
                            <i class="fas fa-user-slash text-2xl mb-2"></i>
                            <p class="text-sm">Nenhum aluno matriculado</p>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
        </div>

        @if($turmas->count() > 0)
            <div class="mt-6">
                {{ $turmas->appends(request()->query())->links('components.pagination') }}
            </div>
        @endif

    @if($turmas->isEmpty())
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma turma encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">Comece criando uma nova turma.</p>
            <div class="mt-6">
                <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nova Turma
                </button>
            </div>
        </div>
    @endif
    </div>
</x-card>

<!-- Fechar dropdowns ao clicar fora -->
<script>
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('[id$="-dropdown"]').forEach(d => d.classList.add('hidden'));
        }
    });
</script>

<script>
    function showTurmasLoading() {
        const overlay = document.getElementById('turmas-loading-overlay');
        if (overlay) overlay.classList.remove('hidden');
    }
    function hideTurmasLoading() {
        const overlay = document.getElementById('turmas-loading-overlay');
        if (overlay) overlay.classList.add('hidden');
    }
    async function updateTurmasContainer(url, pushHistory = true) {
        try {
            showTurmasLoading();
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('#turmas-list-wrapper');
            const container = document.getElementById('turmas-list-wrapper');
            if (newContent && container) {
                container.innerHTML = newContent.innerHTML;
                if (pushHistory) {
                    const newUrl = new URL(url, window.location.origin);
                    window.history.pushState({}, '', newUrl);
                }
                initTurmasAjaxBindings();
            }
        } catch (e) {
            console.error('Erro ao atualizar turmas:', e);
        } finally {
            hideTurmasLoading();
        }
    }
    function initTurmasAjaxBindings() {
        // Interceptar ordenação
        document.querySelectorAll('#turmas-list-wrapper a.sort-link').forEach(link => {
            link.addEventListener('click', function(ev) {
                ev.preventDefault();
                const sort = this.getAttribute('data-sort');
                const direction = this.getAttribute('data-direction') || 'asc';
                const url = new URL(window.location.href);
                url.searchParams.set('sort', sort);
                url.searchParams.set('direction', direction);
                updateTurmasContainer(url.toString());
            });
        });
        // Interceptar paginação
        document.querySelectorAll('#turmas-list-wrapper nav[aria-label="Pagination"] a, #turmas-list-wrapper .pagination a').forEach(a => {
            a.addEventListener('click', function(ev) {
                ev.preventDefault();
                const href = this.getAttribute('href');
                if (href) updateTurmasContainer(href);
            });
        });
    }
    window.addEventListener('popstate', function() {
        updateTurmasContainer(window.location.href, false);
    });
    document.addEventListener('DOMContentLoaded', initTurmasAjaxBindings);
</script>

<!-- Modal de Criação de Turma -->
<div id="create-turma-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Nova Turma</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeCreateModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Formulário -->
            <form id="create-turma-form" action="{{ route('admin.turmas.store') }}" method="POST" class="mt-4">
                @csrf
                
                <div id="create-messages" class="mb-4" style="display: none;"></div>
                
                <div class="space-y-4">
                    <div>
                        <label for="create-nome" class="block text-sm font-medium text-gray-700 mb-1">Nome da Turma <span class="text-red-500">*</span></label>
                        <input type="text" id="create-nome" name="nome" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="create-turno-id" class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                            <select id="create-turno-id" name="turno_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione um turno</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="create-nivel-ensino-id" class="block text-sm font-medium text-gray-700 mb-1">Nível de Ensino</label>
                            <select id="create-nivel-ensino-id" name="nivel_ensino_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione um nível de ensino</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="create-capacidade" class="block text-sm font-medium text-gray-700 mb-1">Capacidade</label>
                            <input type="number" id="create-capacidade" name="capacidade" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="create-ano-letivo" class="block text-sm font-medium text-gray-700 mb-1">Ano Letivo <span class="text-red-500">*</span></label>
                            <input type="number" id="create-ano-letivo" name="ano_letivo" min="2020" max="2030" value="{{ date('Y') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" id="create-ativo" name="ativo" value="1" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="create-ativo" class="ml-2 block text-sm text-gray-900">Turma ativa</label>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 hover:bg-gray-400 text-sm font-medium rounded-md transition-colors duration-200" onclick="closeCreateModal()">
                        Cancelar
                    </button>
                    <button type="submit" id="create-submit-btn" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        <i class="fas fa-save mr-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Visualização de Turma -->
<div id="view-turma-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Visualizar Turma</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeViewModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Conteúdo -->
            <div class="mt-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                        <p id="view-turma-nome" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                        <p id="view-turma-codigo" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                        <p id="view-turma-turno" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nível de Ensino</label>
                        <p id="view-turma-nivel-ensino" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacidade</label>
                        <p id="view-turma-capacidade" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ano Letivo</label>
                        <p id="view-turma-ano-letivo" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <p id="view-turma-status" class="text-sm font-medium bg-gray-50 p-2 rounded"></p>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end mt-6 pt-4 border-t">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors" onclick="closeViewModal()">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição de Turma -->
<div id="edit-turma-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Editar Turma</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeEditModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Formulário -->
            <form id="edit-turma-form" method="POST" class="mt-4">
                @csrf
                @method('PUT')
                
                <div class="space-y-4">
                    <div>
                        <label for="edit-nome" class="block text-sm font-medium text-gray-700 mb-1">Nome da Turma <span class="text-red-500">*</span></label>
                        <input type="text" id="edit-nome" name="nome" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit-turno-id" class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                            <select id="edit-turno-id" name="turno_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione um turno</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="edit-nivel-ensino-id" class="block text-sm font-medium text-gray-700 mb-1">Nível de Ensino</label>
                            <select id="edit-nivel-ensino-id" name="nivel_ensino_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione um nível de ensino</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit-capacidade" class="block text-sm font-medium text-gray-700 mb-1">Capacidade</label>
                            <input type="number" id="edit-capacidade" name="capacidade" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="edit-ano-letivo" class="block text-sm font-medium text-gray-700 mb-1">Ano Letivo <span class="text-red-500">*</span></label>
                            <input type="number" id="edit-ano-letivo" name="ano_letivo" min="2020" max="2030" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center">
                            <input type="checkbox" id="edit-ativo" name="ativo" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="edit-ativo" class="ml-2 block text-sm text-gray-900">Turma ativa</label>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 hover:bg-gray-400 text-sm font-medium rounded-md transition-colors duration-200" onclick="closeEditModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div id="delete-turma-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Confirmar Exclusão</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeDeleteModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Content -->
            <div class="mt-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-gray-900">Tem certeza?</h4>
                        <p class="mt-1 text-sm text-gray-600" id="delete-confirmation-text">
                            Esta ação não pode ser desfeita.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors" onclick="closeDeleteModal()">
                    Cancelar
                </button>
                <button type="button" id="confirm-delete-btn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors" onclick="confirmDeleteTurma()">
                    Excluir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Ver Alunos -->
<div id="ver-alunos-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Alunos da Turma</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeVerAlunosModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Busca -->
            <div class="mt-4 mb-4">
                <input type="text" id="buscar-aluno" placeholder="Buscar aluno..." onkeyup="filtrarAlunos()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Lista de Alunos -->
            <div id="lista-alunos" class="max-h-96 overflow-y-auto space-y-2">
                <!-- Alunos serão carregados via JavaScript -->
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end mt-6 pt-4 border-t">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors" onclick="closeVerAlunosModal()">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Adicionar Aluno -->
<div id="adicionar-aluno-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Adicionar Alunos à Turma</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeAdicionarAlunoModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Busca -->
            <div class="mt-4 mb-4">
                <input type="text" id="buscar-aluno-adicionar" placeholder="Buscar aluno..." onkeyup="buscarAlunosDisponiveis()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-4">
                <nav class="-mb-px flex space-x-8">
                    <button class="tab-button active border-b-2 border-blue-500 py-2 px-1 text-sm font-medium text-blue-600" onclick="switchTab('disponiveis')">
                        Alunos Disponíveis
                    </button>
                    <button class="tab-button border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700" onclick="switchTab('outras-turmas')">
                        Em Outras Turmas
                    </button>
                </nav>
            </div>
            
            <!-- Conteúdo das Tabs -->
            <div id="tab-disponiveis" class="tab-content">
                <div id="alunos-disponiveis" class="max-h-64 overflow-y-auto space-y-2 mb-4">
                    <!-- Alunos disponíveis serão carregados via JavaScript -->
                </div>
            </div>
            
            <div id="tab-outras-turmas" class="tab-content hidden">
                <div id="alunos-outras-turmas" class="max-h-64 overflow-y-auto space-y-2 mb-4">
                    <!-- Alunos de outras turmas serão carregados via JavaScript -->
                </div>
            </div>
            
            <!-- Footer -->
            <div class="flex justify-between mt-6 pt-4 border-t">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors" onclick="closeAdicionarAlunoModal()">
                    Cancelar
                </button>
                <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors" onclick="adicionarAlunosSelecionados()">
                    Adicionar Selecionados
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Transferência de Aluno -->
<div id="transferir-aluno-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Transferir Aluno</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeTransferirAlunoModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Informações do Aluno -->
            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-2">Informações do Aluno</h4>
                <p class="text-sm text-gray-600">
                    <strong>Nome:</strong> <span id="transfer-aluno-nome"></span>
                </p>
                <p class="text-sm text-gray-600">
                    <strong>Turma Atual:</strong> <span id="transfer-turma-atual"></span>
                </p>
            </div>
            
            <!-- Busca de Turma -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Turma de Destino</label>
                <input type="text" id="buscar-turma-destino" placeholder="Digite o nome da turma..." onkeyup="carregarTurmasDisponiveis()" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Lista de Turmas -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Selecionar Turma de Destino</label>
                <div id="lista-turmas-destino" class="max-h-48 overflow-y-auto border border-gray-300 rounded-md">
                    <!-- Turmas serão carregadas via JavaScript -->
                </div>
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors" onclick="closeTransferirAlunoModal()">
                    Cancelar
                </button>
                <button type="button" id="confirmar-transferencia-btn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors" onclick="confirmarTransferencia()" disabled>
                    Confirmar Transferência
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação usando componente -->
<x-modal name="confirmar-transferencia-modal" title="Confirmar Transferência" :closable="true" maxWidth="w-11/12 md:w-3/4 lg:w-1/2">
    <div class="space-y-4">
        <div class="p-4 bg-gray-50 rounded-md">
            <p class="text-sm text-gray-700">
                <strong>Aluno:</strong> <span id="confirm-transfer-aluno-nome"></span>
            </p>
            <p class="text-sm text-gray-700">
                <strong>Turma de destino:</strong> <span id="confirm-transfer-turma-destino"></span>
            </p>
        </div>
        <div id="motivo-container" class="mt-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Motivo da transferência</label>
            <textarea id="transferencia-motivo" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Descreva o motivo..."></textarea>
            <p class="text-xs text-gray-500 mt-1">Obrigatório para solicitar transferência para aprovação.</p>
        </div>
    </div>

    @slot('footer')
        <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400" onclick="closeModal('confirmar-transferencia-modal')">Cancelar</button>
        <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" onclick="submitConfirmarTransferencia()">Salvar solicitação</button>
    @endslot
</x-modal>

@push('scripts')
<script>
(function initTurmasScripts() {
    if (window.turmasInitDone) return;
    window.turmasInitDone = true;
    // ===== VARIÁVEIS GLOBAIS =====
    let turmaAtual = null;
    let turmaParaExcluir = null;
    let alunosSelecionados = [];
    let alunoParaTransferir = null;
    let turmaSelecionada = null;

    // ===== FUNÇÕES DE MODAL =====
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // ===== FUNÇÕES DE DROPDOWN =====
    window.toggleDropdown = function(id) {
        const dropdown = document.getElementById(id);
        const isHidden = dropdown.classList.contains('hidden');
        
        // Fechar todos os dropdowns
        document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.classList.add('hidden'));
        
        // Abrir o dropdown clicado se estava fechado
        if (isHidden) {
            dropdown.classList.remove('hidden');
        }
    };

    // Fechar dropdown ao clicar fora dele
    if (!window.turmasEventsBound) {
      document.addEventListener('click', function(event) {
        // Verificar se o clique foi fora de qualquer dropdown ou botão de dropdown
        const isDropdownButton = event.target.closest('button[onclick*="toggleDropdown"]');
        const isDropdownContent = event.target.closest('[id^="dropdown-"]');
        
        // Se não clicou no botão nem no conteúdo do dropdown, fechar todos
        if (!isDropdownButton && !isDropdownContent) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        }
      });
      window.turmasEventsBound = true;
    }

    // ===== FUNÇÕES DE TURMA =====
    window.openCreateModal = function() {
        // Carregar turnos (usar dados globais)
        const selectTurnos = document.getElementById('create-turno-id');
        selectTurnos.innerHTML = '<option value="">Selecione um turno</option>';
        if (window.turnosData && Array.isArray(window.turnosData)) {
            window.turnosData.forEach(turno => {
                const option = document.createElement('option');
                option.value = turno.id;
                option.textContent = turno.nome;
                selectTurnos.appendChild(option);
            });
        }
        
        // Carregar níveis de ensino configurados para a escola (usar dados globais) e agrupar por categoria
        const selectNiveis = document.getElementById('create-nivel-ensino-id');
        selectNiveis.innerHTML = '<option value="">Selecione um nível de ensino</option>';
        if (window.niveisEnsinoData && Array.isArray(window.niveisEnsinoData)) {
            const grupos = {
                'Ensino Infantil': [],
                'Ensino Fundamental': [],
                'Ensino Médio': [],
                'Outros': []
            };
            window.niveisEnsinoData.forEach(nivel => {
                const mods = Array.isArray(nivel.modalidades_compativeis) ? nivel.modalidades_compativeis : [];
                let grupo = 'Outros';
                if (mods.includes('EI')) grupo = 'Ensino Infantil';
                else if (mods.includes('EF')) grupo = 'Ensino Fundamental';
                else if (mods.includes('EM')) grupo = 'Ensino Médio';
                grupos[grupo].push(nivel);
            });
            ['Ensino Infantil', 'Ensino Fundamental', 'Ensino Médio', 'Outros'].forEach(label => {
                const niveis = grupos[label];
                if (!niveis || niveis.length === 0) return;
                const optgroup = document.createElement('optgroup');
                optgroup.label = label;
                niveis.forEach(nivel => {
                    const option = document.createElement('option');
                    option.value = nivel.id;
                    option.textContent = nivel.codigo ? `${nivel.nome} (${nivel.codigo})` : nivel.nome;
                    optgroup.appendChild(option);
                });
                selectNiveis.appendChild(optgroup);
            });
        }
        
        openModal('create-turma-modal');
    };

    window.closeCreateModal = function() {
        closeModal('create-turma-modal');
    };

    window.viewTurma = function(id) {
        fetch(`/admin/turmas/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const turma = data.turma;
                    document.getElementById('view-turma-nome').textContent = turma.nome;
                    document.getElementById('view-turma-codigo').textContent = turma.codigo || 'N/A';
                    document.getElementById('view-turma-turno').textContent = turma.turno ? turma.turno.nome : 'N/A';
                    document.getElementById('view-turma-nivel-ensino').textContent = turma.nivelEnsino ? turma.nivelEnsino.nome : 'N/A';
                    document.getElementById('view-turma-capacidade').textContent = turma.capacidade || 'N/A';
                    document.getElementById('view-turma-ano-letivo').textContent = turma.ano_letivo || 'N/A';
                    
                    const statusElement = document.getElementById('view-turma-status');
                    statusElement.textContent = turma.ativo ? 'Ativa' : 'Inativa';
                    statusElement.className = `text-sm font-medium bg-gray-50 p-2 rounded ${turma.ativo ? 'text-green-600' : 'text-red-600'}`;
                    
                    openModal('view-turma-modal');
                }
            })
            .catch(error => {
                console.error('Erro ao carregar turma:', error);
                if (window.alertSystem) {
                    window.alertSystem.error('Erro ao carregar dados da turma');
                }
            });
    };

    window.closeViewModal = function() {
        closeModal('view-turma-modal');
    };

    window.editTurma = function(id) {
        fetch(`/admin/turmas/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const turma = data.turma;
                    const niveisEnsino = data.niveisEnsino || [];
                    const turnos = data.turnos || [];
                    
                    // Preencher formulário
                    document.getElementById('edit-nome').value = turma.nome;
                    document.getElementById('edit-capacidade').value = turma.capacidade;
                    document.getElementById('edit-ano-letivo').value = turma.ano_letivo;
                    document.getElementById('edit-ativo').checked = turma.ativo;
                    
                    // Carregar níveis de ensino configurados (usar dados que já vêm da API) e agrupar por categoria
                    const selectNiveis = document.getElementById('edit-nivel-ensino-id');
                    selectNiveis.innerHTML = '<option value="">Selecione um nível de ensino</option>';
                    const gruposEdit = {
                        'Ensino Infantil': [],
                        'Ensino Fundamental': [],
                        'Ensino Médio': [],
                        'Outros': []
                    };
                    niveisEnsino.forEach(nivel => {
                        const mods = Array.isArray(nivel.modalidades_compativeis) ? nivel.modalidades_compativeis : [];
                        let grupo = 'Outros';
                        if (mods.includes('EI')) grupo = 'Ensino Infantil';
                        else if (mods.includes('EF')) grupo = 'Ensino Fundamental';
                        else if (mods.includes('EM')) grupo = 'Ensino Médio';
                        gruposEdit[grupo].push(nivel);
                    });
                    ['Ensino Infantil', 'Ensino Fundamental', 'Ensino Médio', 'Outros'].forEach(label => {
                        const niveis = gruposEdit[label];
                        if (!niveis || niveis.length === 0) return;
                        const optgroup = document.createElement('optgroup');
                        optgroup.label = label;
                        niveis.forEach(nivel => {
                            const option = document.createElement('option');
                            option.value = nivel.id;
                            option.textContent = nivel.codigo ? `${nivel.nome} (${nivel.codigo})` : nivel.nome;
                            if (nivel.id == turma.nivel_ensino_id) option.selected = true;
                            optgroup.appendChild(option);
                        });
                        selectNiveis.appendChild(optgroup);
                    });
                    
                    // Carregar turnos (usar dados que já vêm da API)
                    const selectTurnos = document.getElementById('edit-turno-id');
                    selectTurnos.innerHTML = '<option value="">Selecione um turno</option>';
                    turnos.forEach(turno => {
                        const option = document.createElement('option');
                        option.value = turno.id;
                        option.textContent = turno.nome;
                        if (turno.id == turma.turno_id) option.selected = true;
                        selectTurnos.appendChild(option);
                    });
                    
                    // Definir action do formulário
                    document.getElementById('edit-turma-form').action = `/admin/turmas/${id}`;
                    
                    openModal('edit-turma-modal');
                }
            })
            .catch(error => {
                console.error('Erro ao carregar turma:', error);
                if (window.alertSystem) {
                    window.alertSystem.error('Erro ao carregar dados da turma');
                }
            });
    };

    window.closeEditModal = function() {
        closeModal('edit-turma-modal');
    };

    window.deleteTurma = function(id, nome) {
        turmaParaExcluir = id;
        document.getElementById('delete-confirmation-text').textContent = 
            `Tem certeza que deseja excluir a turma "${nome}"? Esta ação não pode ser desfeita.`;
        
        openModal('delete-turma-modal');
    };

    window.closeDeleteModal = function() {
        closeModal('delete-turma-modal');
    };

    window.confirmDeleteTurma = function() {
        if (turmaParaExcluir) {
            fetch(`/admin/turmas/${turmaParaExcluir}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
                    location.reload();
                } else {
                    if (window.alertSystem) {
                        window.alertSystem.error('Erro ao excluir turma: ' + data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao excluir turma:', error);
                if (window.alertSystem) {
                    window.alertSystem.error('Erro ao excluir turma');
                }
            });
        }
    };

    // ===== FUNÇÕES DE ALUNOS =====
    // Estado de paginação para o modal "Adicionar Alunos"
    let disponiveisPage = 1;
    let outrasPage = 1;
    let perPageAlunos = 10;
    let hasMoreDisponiveis = true;
    let hasMoreOutras = true;
    let loadingDisponiveis = false;
    let loadingOutras = false;
    let buscaAlunosModal = '';
    let alunosScrollBound = false;
    window.verAlunos = function(turmaId) {
        turmaAtual = turmaId;
        carregarAlunosTurma(turmaId);
        openModal('ver-alunos-modal');
    };

    window.closeVerAlunosModal = function() {
        closeModal('ver-alunos-modal');
    };

    window.adicionarAluno = function(turmaId) {
        turmaAtual = turmaId;
        alunosSelecionados = [];
        carregarAlunosDisponiveis();
        openModal('adicionar-aluno-modal');
    };

    window.closeAdicionarAlunoModal = function() {
        closeModal('adicionar-aluno-modal');
    };

    function carregarAlunosTurma(turmaId) {
        fetch(`/admin/turmas/${turmaId}/alunos`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('lista-alunos');
                if (data.alunos && data.alunos.length > 0) {
                    container.innerHTML = data.alunos.map(aluno => `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-grip-vertical text-gray-400 mr-2"></i>
                                        ${aluno.nome}
                                        ${aluno.transferencia_pendente ? `<i class=\"fas fa-exchange-alt text-yellow-500 ml-2\" title=\"Transferência pendente\"></i>` : ''}
                                    </p>
                                    ${!aluno.ativo ? `<p class="text-xs text-yellow-600">(inativo)</p>` : ''}
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="editarAluno(${aluno.id})" class="text-blue-600 hover:text-blue-800" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="removerAluno(${aluno.id})" class="text-red-600 hover:text-red-800" title="Remover">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-user-slash text-3xl mb-2"></i>
                            <p>Nenhum aluno matriculado nesta turma</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro ao carregar alunos:', error);
                document.getElementById('lista-alunos').innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                        <p>Erro ao carregar alunos</p>
                    </div>
                `;
            });
    }

    function carregarAlunosDisponiveis() {
        // Reset de estado e busca
        const buscaInput = document.getElementById('buscar-aluno-adicionar');
        buscaAlunosModal = buscaInput ? buscaInput.value.trim() : '';
        disponiveisPage = 1;
        outrasPage = 1;
        hasMoreDisponiveis = true;
        hasMoreOutras = true;
        loadingDisponiveis = false;
        loadingOutras = false;

        // Limpar containers
        const containerDisponiveis = document.getElementById('alunos-disponiveis');
        const containerOutrasTurmas = document.getElementById('alunos-outras-turmas');
        if (containerDisponiveis) containerDisponiveis.innerHTML = '';
        if (containerOutrasTurmas) containerOutrasTurmas.innerHTML = '';

        // Carregar primeira página de cada aba
        loadDisponiveisPage();
        loadOutrasPage();

        // Bind de scroll (uma vez)
        if (!alunosScrollBound) {
            if (containerDisponiveis) {
                containerDisponiveis.addEventListener('scroll', function() {
                    if (loadingDisponiveis || !hasMoreDisponiveis) return;
                    const nearBottom = this.scrollTop + this.clientHeight >= this.scrollHeight - 20;
                    if (nearBottom) {
                        disponiveisPage += 1;
                        loadDisponiveisPage();
                    }
                });
            }
            if (containerOutrasTurmas) {
                containerOutrasTurmas.addEventListener('scroll', function() {
                    if (loadingOutras || !hasMoreOutras) return;
                    const nearBottom = this.scrollTop + this.clientHeight >= this.scrollHeight - 20;
                    if (nearBottom) {
                        outrasPage += 1;
                        loadOutrasPage();
                    }
                });
            }
            alunosScrollBound = true;
        }
    }

    function loadDisponiveisPage() {
        loadingDisponiveis = true;
        const container = document.getElementById('alunos-disponiveis');
        fetch(`/admin/turmas/${turmaAtual}/alunos-disponiveis?mode=disponiveis&page_disponiveis=${encodeURIComponent(disponiveisPage)}&per_page=${encodeURIComponent(perPageAlunos)}&busca=${encodeURIComponent(buscaAlunosModal)}`)
            .then(response => response.json())
            .then(data => {
                const lista = data.disponiveis || [];
                if (lista.length > 0) {
                    const html = lista.map(aluno => `
                        <div class="flex items-center p-2 hover:bg-gray-50 rounded">
                            <input 
                                type="checkbox" 
                                id="aluno-${aluno.id}" 
                                value="${aluno.id}"
                                onchange="toggleAlunoSelecionado(${aluno.id})"
                                class="mr-3 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                            >
                            <label for="aluno-${aluno.id}" class="text-sm text-gray-900 cursor-pointer">
                                ${[aluno.nome, aluno.sobrenome].filter(Boolean).join(' ')}
                            </label>
                        </div>
                    `).join('');
                    container.insertAdjacentHTML('beforeend', html);
                } else if (disponiveisPage === 1) {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-user-slash text-3xl mb-2"></i>
                            <p>Nenhum aluno disponível</p>
                        </div>
                    `;
                }
                hasMoreDisponiveis = !!data.has_more_disponiveis;
            })
            .catch(error => {
                console.error('Erro ao carregar alunos disponíveis:', error);
            })
            .finally(() => {
                loadingDisponiveis = false;
            });
    }

    function loadOutrasPage() {
        loadingOutras = true;
        const container = document.getElementById('alunos-outras-turmas');
        fetch(`/admin/turmas/${turmaAtual}/alunos-disponiveis?mode=outras&page_outras=${encodeURIComponent(outrasPage)}&per_page=${encodeURIComponent(perPageAlunos)}&busca=${encodeURIComponent(buscaAlunosModal)}`)
            .then(response => response.json())
            .then(data => {
                const lista = data.outras_turmas || [];
                if (lista.length > 0) {
                    const html = lista.map(aluno => `
                        <div class="flex items-center p-2 hover:bg-gray-50 rounded">
                            <input 
                                type="checkbox" 
                                id="aluno-outras-${aluno.id}" 
                                value="${aluno.id}"
                                onchange="toggleAlunoSelecionado(${aluno.id})"
                                class="mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label for="aluno-outras-${aluno.id}" class="text-sm text-gray-900 cursor-pointer">
                                ${[aluno.nome, aluno.sobrenome].filter(Boolean).join(' ')} <span class="text-xs text-gray-500">(${aluno.turma_atual})</span>
                            </label>
                        </div>
                    `).join('');
                    container.insertAdjacentHTML('beforeend', html);
                } else if (outrasPage === 1) {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-user-slash text-3xl mb-2"></i>
                            <p>Nenhum aluno em outras turmas</p>
                        </div>
                    `;
                }
                hasMoreOutras = !!data.has_more_outras;
            })
            .catch(error => {
                console.error('Erro ao carregar alunos de outras turmas:', error);
            })
            .finally(() => {
                loadingOutras = false;
            });
    }

    window.toggleAlunoSelecionado = function(alunoId) {
        const index = alunosSelecionados.indexOf(alunoId);
        if (index > -1) {
            alunosSelecionados.splice(index, 1);
        } else {
            alunosSelecionados.push(alunoId);
        }
    };

    window.switchTab = function(tab) {
        // Remover classe ativa de todos os botões
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });

        // Esconder todo o conteúdo das tabs
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });

        // Ativar tab selecionada
        const activeButton = event.target;
        activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
        activeButton.classList.remove('border-transparent', 'text-gray-500');

        // Mostrar conteúdo da tab
        document.getElementById(`tab-${tab}`).classList.remove('hidden');
    };

    window.adicionarAlunosSelecionados = function() {
        if (alunosSelecionados.length === 0) {
            if (window.alertSystem) {
                window.alertSystem.warning('Selecione pelo menos um aluno');
            }
            return;
        }

        fetch(`/admin/turmas/${turmaAtual}/adicionar-alunos`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                alunos: alunosSelecionados
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeAdicionarAlunoModal();
                location.reload();
            } else {
                if (window.alertSystem) {
                    window.alertSystem.error('Erro ao adicionar alunos: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Erro ao adicionar alunos:', error);
            if (window.alertSystem) {
                window.alertSystem.error('Erro ao adicionar alunos');
            }
        });
    };

    window.removerAluno = function(alunoId) {
        if (confirm('Tem certeza que deseja remover este aluno da turma?')) {
            fetch(`/admin/turmas/${turmaAtual}/remover-aluno/${alunoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    carregarAlunosTurma(turmaAtual);
                } else {
                    if (window.alertSystem) {
                        window.alertSystem.error('Erro ao remover aluno: ' + data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao remover aluno:', error);
                if (window.alertSystem) {
                    window.alertSystem.error('Erro ao remover aluno');
                }
            });
        }
    };

    // ===== FUNÇÕES DE TRANSFERÊNCIA =====
    window.abrirModalTransferencia = function(alunoId, alunoNome, turmaAtual) {
        alunoParaTransferir = {
            id: alunoId,
            nome: alunoNome,
            turma_atual: turmaAtual
        };

        document.getElementById('transfer-aluno-nome').textContent = alunoNome;
        document.getElementById('transfer-turma-atual').textContent = turmaAtual;
        
        carregarTurmasDisponiveis();
        openModal('transferir-aluno-modal');
    };

    window.closeTransferirAlunoModal = function() {
        closeModal('transferir-aluno-modal');
        alunoParaTransferir = null;
        turmaSelecionada = null;
    };

    window.carregarTurmasDisponiveis = function() {
        const busca = document.getElementById('buscar-turma-destino').value;
        
        fetch(`/admin/turmas/disponiveis?busca=${encodeURIComponent(busca)}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('lista-turmas-destino');
                if (data.turmas && data.turmas.length > 0) {
                    container.innerHTML = data.turmas.map(turma => `
                        <div class="turma-option p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0" 
                             onclick="selecionarTurmaDestino(${turma.id}, '${turma.nome}')">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-900">${turma.nome}</p>
                                    <p class="text-sm text-gray-600">${turma.turno} - ${turma.grupo}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">${turma.ocupacao}/${turma.capacidade || 'N/A'}</p>
                                    ${turma.vagas_disponiveis > 0 ? 
                                        `<p class="text-xs text-green-600">${turma.vagas_disponiveis} vagas</p>` : 
                                        `<p class="text-xs text-red-600">Lotada</p>`
                                    }
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-search text-3xl mb-2"></i>
                            <p>Nenhuma turma encontrada</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro ao carregar turmas:', error);
            });
    };

    window.selecionarTurmaDestino = function(turmaId, turmaNome) {
        turmaSelecionada = {
            id: turmaId,
            nome: turmaNome
        };

        // Remover seleção anterior
        document.querySelectorAll('.turma-option').forEach(option => {
            option.classList.remove('bg-blue-50', 'border-blue-200');
        });

        // Adicionar seleção atual
        event.currentTarget.classList.add('bg-blue-50', 'border-blue-200');

        // Habilitar botão de confirmação
        document.getElementById('confirmar-transferencia-btn').disabled = false;
    };

    window.confirmarTransferencia = function() {
        if (!alunoParaTransferir || !turmaSelecionada) {
            if (window.alertSystem) {
                window.alertSystem.warning('Selecione uma turma de destino');
            }
            return;
        }

        // Preenche informações no modal de confirmação
        document.getElementById('confirm-transfer-aluno-nome').textContent = alunoParaTransferir.nome;
        document.getElementById('confirm-transfer-turma-destino').textContent = turmaSelecionada.nome;
        document.getElementById('transferencia-motivo').value = '';

        // Abrir modal do componente
        showModal('confirmar-transferencia-modal');
    };

    window.submitConfirmarTransferencia = function() {
        if (!alunoParaTransferir || !turmaSelecionada) {
            if (window.alertSystem) {
                window.alertSystem.warning('Seleção inválida');
            }
            return;
        }

        const motivo = document.getElementById('transferencia-motivo').value.trim();
        if (motivo.length === 0) {
            if (window.alertSystem) {
                window.alertSystem.warning('Informe o motivo da transferência.');
            }
            return;
        }

        fetch(`/alunos/${alunoParaTransferir.id}/transferir`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                turma_id: turmaSelecionada.id,
                solicitar_transferencia_sala: true,
                motivo: motivo
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('confirmar-transferencia-modal');
                closeTransferirAlunoModal();
                if (window.alertSystem) {
                    const linkUrl = data.transferencia_id ? `/transferencias/${data.transferencia_id}` : '/transferencias';
                    window.alertSystem.success(`Solicitação criada. <a href="${linkUrl}" target="_blank" class="underline">Ver transferência</a>.`);
                }
                if (data.transferencia_id) {
                    try { window.open(`/transferencias/${data.transferencia_id}`, '_blank'); } catch (e) {}
                }
                // Não recarregar a página para manter o contexto do usuário
            } else {
                if (window.alertSystem) {
                    window.alertSystem.error('Erro ao transferir aluno: ' + (data.message || ''));
                }
            }
        })
        .catch(error => {
            console.error('Erro ao transferir aluno:', error);
            if (window.alertSystem) {
                window.alertSystem.error('Erro ao transferir aluno');
            }
        });
    };

    // ===== FUNÇÕES DE DRAG & DROP =====
    window.dragStart = function(e) {
        e.dataTransfer.setData('text/plain', JSON.stringify({
            alunoId: e.target.dataset.alunoId,
            alunoNome: e.target.dataset.alunoNome,
            turmaOrigem: e.target.dataset.turmaOrigem
        }));
        e.target.classList.add('dragging');
    };

    window.dragEnd = function(e) {
        e.target.classList.remove('dragging');
    };

    window.allowDrop = function(e) {
        e.preventDefault();
    };

    window.dragEnter = function(e) {
        e.preventDefault();
        e.currentTarget.classList.add('drop-zone-hover');
    };

    window.dragLeave = function(e) {
        e.currentTarget.classList.remove('drop-zone-hover');
    };

    window.dropAluno = function(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('drop-zone-hover');
        
        const data = JSON.parse(e.dataTransfer.getData('text/plain'));
        const turmaDestino = e.currentTarget.dataset.turmaId;
        const turmaDestinoNome = e.currentTarget.dataset.turmaNome;
        
        if (data.turmaOrigem === turmaDestino) {
            return; // Mesma turma
        }
        // Prepara estado e abre modal de confirmação
        alunoParaTransferir = {
            id: data.alunoId,
            nome: data.alunoNome,
            turma_atual: data.turmaOrigem
        };
        turmaSelecionada = {
            id: turmaDestino,
            nome: turmaDestinoNome
        };
        confirmarTransferencia();
    };

    // ===== FUNÇÕES DE BUSCA E FILTRO =====
    window.filtrarAlunos = function() {
        const busca = document.getElementById('buscar-aluno').value.toLowerCase();
        const alunos = document.querySelectorAll('#lista-alunos > div');
        
        alunos.forEach(aluno => {
            const nome = aluno.textContent.toLowerCase();
            if (nome.includes(busca)) {
                aluno.style.display = 'block';
            } else {
                aluno.style.display = 'none';
            }
        });
    };

    window.buscarAlunosDisponiveis = function() {
        // Busca server-side: reinicia listagens com filtro
        disponiveisPage = 1;
        outrasPage = 1;
        hasMoreDisponiveis = true;
        hasMoreOutras = true;
        carregarAlunosDisponiveis();
    };

    // ===== OUTRAS FUNÇÕES =====
    window.gerarRelatorioTurma = function(turmaId) {
        window.open(`/admin/turmas/${turmaId}/relatorio`, '_blank');
    };

    // ===== MANIPULAÇÃO DE FORMULÁRIOS =====
    document.getElementById('create-turma-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('create-submit-btn');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                const messagesDiv = document.getElementById('create-messages');
                messagesDiv.innerHTML = `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        ${data.message || 'Erro ao criar turma'}
                    </div>
                `;
                messagesDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            const messagesDiv = document.getElementById('create-messages');
            messagesDiv.innerHTML = `
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    Erro ao criar turma
                </div>
            `;
            messagesDiv.style.display = 'block';
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });

    document.getElementById('edit-turma-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeEditModal();
                location.reload();
            } else {
                if (window.alertSystem) {
                    window.alertSystem.error('Erro ao editar turma: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            if (window.alertSystem) {
                window.alertSystem.error('Erro ao editar turma');
            }
        });
    });



    // ===== FECHAR MODAIS AO CLICAR FORA =====
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
            const modalId = e.target.id;
            if (modalId.includes('modal')) {
                closeModal(modalId);
            }
        }
    });
    
    // Dados disponíveis globalmente
    window.turnosData = @json($turnos);
    window.niveisEnsinoData = @json($niveisEnsino);
})();
</script>
@endpush

@endsection