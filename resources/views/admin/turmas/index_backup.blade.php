@extends('layouts.app')

@section('content')
<style>
/* Estilos para Drag & Drop */
.aluno-item.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
    transition: all 0.2s ease;
}

.turma-card.drop-zone-active {
    border: 2px dashed #3b82f6;
    background-color: #eff6ff;
    transform: scale(1.02);
    transition: all 0.3s ease;
}

.turma-card.drop-zone-hover {
    border: 2px solid #10b981;
    background-color: #ecfdf5;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);
    transform: scale(1.05);
}

.aluno-item {
    transition: all 0.2s ease;
}

.aluno-item:hover {
    background-color: #f9fafb;
    transform: translateX(4px);
}

.cursor-move {
    cursor: move;
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

.toast {
    animation: slideInRight 0.3s ease-out;
}

/* Indicadores visuais melhorados */
.status-indicator {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-left: 8px;
}

.status-ativa {
    background-color: #dcfce7;
    color: #166534;
}

.status-inativa {
    background-color: #f3f4f6;
    color: #6b7280;
}

.ocupacao-baixa {
    background-color: #dcfce7;
    color: #166534;
}

.ocupacao-media {
    background-color: #fef3c7;
    color: #92400e;
}

.ocupacao-alta {
    background-color: #fee2e2;
    color: #991b1b;
}
</style>

<div class="container mx-auto px-4 py-6">
    <!-- Breadcrumb -->
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
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

    <!-- Header -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Gerenciar Turmas</h1>
                <div class="flex space-x-3">
                    @can('turmas.criar')
                    <x-button type="button" color="primary" onclick="openCreateModal()">
                        <i class="fas fa-plus mr-2"></i>Nova Turma
                    </x-button>
                    @endcan
                    <x-button href="{{ route('dashboard') }}" color="secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros e Busca Avançada -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <form method="GET" action="{{ route('turmas.index') }}" class="space-y-4">
                <!-- Linha 1: Busca Principal -->
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   name="busca" 
                                   value="{{ request('busca') }}"
                                   placeholder="🔍 Buscar turmas por nome, código ou descrição..."
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <x-button type="submit" color="primary">
                            <i class="fas fa-search mr-2"></i>
                            Buscar
                        </x-button>
                        <x-button href="{{ route('turmas.index') }}" color="secondary">
                            <i class="fas fa-times mr-2"></i>
                            Limpar
                        </x-button>
                    </div>
                </div>

                <!-- Linha 2: Filtros Avançados -->
                <div class="flex flex-col md:flex-row gap-4 pt-4 border-t border-gray-200">
                    <!-- Status -->
                    <div class="flex-1">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">📊 Status</label>
                        <select name="status" id="status" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Todas</option>
                            <option value="ativa" {{ request('status') == 'ativa' ? 'selected' : '' }}>🟢 Ativas</option>
                            <option value="inativa" {{ request('status') == 'inativa' ? 'selected' : '' }}>⚪ Inativas</option>
                            <option value="lotada" {{ request('status') == 'lotada' ? 'selected' : '' }}>🔴 Lotadas</option>
                            <option value="vagas" {{ request('status') == 'vagas' ? 'selected' : '' }}>🟢 Com Vagas</option>
                        </select>
                    </div>

                    <!-- Ano Letivo -->
                    <div class="flex-1">
                        <label for="ano_letivo" class="block text-sm font-medium text-gray-700 mb-1">📅 Ano Letivo</label>
                        <select name="ano_letivo" id="ano_letivo" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Todos os anos</option>
                            @for($ano = date('Y') + 1; $ano >= date('Y') - 5; $ano--)
                                <option value="{{ $ano }}" {{ request('ano_letivo') == $ano ? 'selected' : '' }}>{{ $ano }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Turno -->
                    <div class="flex-1">
                        <label for="turno" class="block text-sm font-medium text-gray-700 mb-1">⏰ Turno</label>
                        <select name="turno" id="turno" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">Todos os turnos</option>
                            <option value="matutino" {{ request('turno') == 'matutino' ? 'selected' : '' }}>🌅 Matutino</option>
                            <option value="vespertino" {{ request('turno') == 'vespertino' ? 'selected' : '' }}>🌞 Vespertino</option>
                            <option value="noturno" {{ request('turno') == 'noturno' ? 'selected' : '' }}>🌙 Noturno</option>
                            <option value="integral" {{ request('turno') == 'integral' ? 'selected' : '' }}>🌅🌙 Integral</option>
                        </select>
                    </div>

                    <!-- Ordenação -->
                    <div class="flex-1">
                        <label for="ordenar" class="block text-sm font-medium text-gray-700 mb-1">📋 Ordenar por</label>
                        <select name="ordenar" id="ordenar" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="nome" {{ request('ordenar') == 'nome' ? 'selected' : '' }}>Nome</option>
                            <option value="codigo" {{ request('ordenar') == 'codigo' ? 'selected' : '' }}>Código</option>
                            <option value="ocupacao" {{ request('ordenar') == 'ocupacao' ? 'selected' : '' }}>Ocupação</option>
                            <option value="criado" {{ request('ordenar') == 'criado' ? 'selected' : '' }}>Data de Criação</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Turmas -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4">
            @if($turmas->count() > 0)
                <!-- Layout Mobile - Cards -->
                <div class="space-y-4">
                    @forelse($turmas as $turma)
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 turma-card" 
                             data-turma-id="{{ $turma->id }}" 
                             data-turma-nome="{{ $turma->nome }}"
                             data-turma-capacidade="{{ $turma->capacidade }}"
                             data-turma-ocupacao="{{ $turma->alunos_count ?? 0 }}"
                             ondrop="drop(event)" 
                             ondragover="allowDrop(event)"
                             ondragenter="dragEnter(event)"
                             ondragleave="dragLeave(event)">
                            <!-- Header do Card -->
                            <div class="p-4 border-b border-gray-100">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start flex-1 min-w-0">
                                        @php
                                            $alunosCount = $turma->alunos_count ?? 0;
                                            $percentage = $turma->capacidade > 0 ? ($alunosCount / $turma->capacidade) * 100 : 0;
                                            
                                            // Definir indicador visual baseado no status
                                            if (!$turma->ativo) {
                                                $indicador = '⚪';
                                                $bgColor = 'from-gray-400 to-gray-500';
                                                $statusText = 'Inativa';
                                                $statusBg = 'bg-gray-100';
                                                $statusColor = 'text-gray-800';
                                            } elseif ($percentage >= 100) {
                                                $indicador = '🔴';
                                                $bgColor = 'from-red-500 to-red-600';
                                                $statusText = 'Lotada';
                                                $statusBg = 'bg-red-100';
                                                $statusColor = 'text-red-800';
                                            } elseif ($percentage >= 90) {
                                                $indicador = '🟡';
                                                $bgColor = 'from-yellow-500 to-yellow-600';
                                                $statusText = 'Quase Lotada';
                                                $statusBg = 'bg-yellow-100';
                                                $statusColor = 'text-yellow-800';
                                            } else {
                                                $indicador = '🟢';
                                                $bgColor = 'from-green-500 to-green-600';
                                                $statusText = 'Vagas Disponíveis';
                                                $statusBg = 'bg-green-100';
                                                $statusColor = 'text-green-800';
                                            }
                                        @endphp
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br {{ $bgColor }} flex items-center justify-center text-white mr-3 flex-shrink-0 shadow-sm relative">
                                            <i class="fas fa-chalkboard-teacher text-lg"></i>
                                            <span class="absolute -top-1 -right-1 text-lg">{{ $indicador }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <h3 class="font-semibold text-gray-900 truncate text-lg">{{ $turma->nome }}</h3>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusBg }} {{ $statusColor }}">
                                                    <span class="mr-1">{{ $indicador }}</span>
                                                    {{ $statusText }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 mt-1">
                                                <span class="font-mono bg-gray-100 px-2 py-1 rounded text-xs">{{ $turma->codigo }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informações da Turma -->
                            <div class="p-4">
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-layer-group w-4 h-4 mr-2 text-gray-400"></i>
                                        <span class="truncate">{{ $turma->grupo->nome ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-clock w-4 h-4 mr-2 text-gray-400"></i>
                                        <span class="truncate">{{ $turma->turno->nome ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-calendar w-4 h-4 mr-2 text-gray-400"></i>
                                        <span>{{ $turma->ano_letivo }}</span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-graduation-cap w-4 h-4 mr-2 text-gray-400"></i>
                                        <span>{{ $turma->alunos_count ?? 0 }}/{{ $turma->capacidade }}</span>
                                    </div>
                                </div>

                                <!-- Indicador de Capacidade -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-gray-600">Ocupação</span>
                                        <span class="font-medium 
                                            @if(($turma->alunos_count ?? 0) == 0) text-gray-500
                                            @elseif(($turma->alunos_count ?? 0) / $turma->capacidade < 0.7) text-green-600
                                            @elseif(($turma->alunos_count ?? 0) / $turma->capacidade < 0.9) text-yellow-600
                                            @else text-red-600
                                            @endif">
                                            {{ $turma->alunos_count ?? 0 }}/{{ $turma->capacidade }} 
                                            ({{ $turma->capacidade > 0 ? round((($turma->alunos_count ?? 0) / $turma->capacidade) * 100) : 0 }}%)
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        @php
                                            $alunosCount = $turma->alunos_count ?? 0;
                                            $percentage = $turma->capacidade > 0 ? ($alunosCount / $turma->capacidade) * 100 : 0;
                                            $colorClass = 'bg-gray-400';
                                            if ($percentage > 0) {
                                                if ($percentage < 70) $colorClass = 'bg-green-500';
                                                elseif ($percentage < 90) $colorClass = 'bg-yellow-500';
                                                else $colorClass = 'bg-red-500';
                                            }
                                        @endphp
                                        <div class="h-2 rounded-full transition-all duration-300 {{ $colorClass }}" 
                                             style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                </div>

                                <!-- Ações Rápidas -->
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @can('turmas.visualizar')
                                    <button 
                                        onclick="verAlunos({{ $turma->id }}, {{ json_encode($turma->nome) }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 text-xs font-medium rounded-md hover:bg-blue-100 transition-colors duration-200"
                                    >
                                        <i class="fas fa-users mr-1"></i>
                                        Ver Alunos
                                    </button>
                                    @endcan
                                    
                                    @can('turmas.editar')
                                    <button 
                                        onclick="adicionarAluno({{ $turma->id }}, {{ json_encode($turma->nome) }})"
                                        class="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-700 text-xs font-medium rounded-md hover:bg-green-100 transition-colors duration-200"
                                    >
                                        <i class="fas fa-user-plus mr-1"></i>
                                        Adicionar Aluno
                                    </button>
                                    @endcan
                                    
                                    @if(($turma->alunos_count ?? 0) > 0)
                                        <button 
                                            onclick="gerarRelatorioTurma()"
                                            class="inline-flex items-center px-3 py-1.5 bg-purple-50 text-purple-700 text-xs font-medium rounded-md hover:bg-purple-100 transition-colors duration-200"
                                        >
                                            <i class="fas fa-chart-bar mr-1"></i>
                                            Relatórios
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Ações Principais -->
                            <div class="px-4 pb-4 border-t border-gray-100 bg-gray-50 rounded-b-lg">
                                <div class="flex justify-between items-center pt-3">
                                    <div class="flex space-x-2">
                                        @can('turmas.editar')
                                        <button 
                                            onclick="editTurma({{ $turma->id }})"
                                            class="inline-flex items-center px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-md transition-colors duration-200 shadow-sm"
                                        >
                                            <i class="fas fa-edit mr-1"></i>
                                            Editar
                                        </button>
                                        @endcan
                                        
                                        @can('turmas.excluir')
                                        <button 
                                            onclick="deleteTurma({{ $turma->id }}, {{ json_encode($turma->nome) }})"
                                            class="inline-flex items-center px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-md transition-colors duration-200 shadow-sm"
                                        >
                                            <i class="fas fa-trash mr-1"></i>
                                            Excluir
                                        </button>
                                        @endcan
                                    </div>
                                    
                                    @php
                                        $alunosCount = $turma->alunos_count ?? 0;
                                        $vagasDisponiveis = $turma->capacidade - $alunosCount;
                                    @endphp
                                    @if($vagasDisponiveis > 0)
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                            <i class="fas fa-check mr-1"></i>
                                            {{ $vagasDisponiveis }} vagas
                                        </span>
                                    @elseif($vagasDisponiveis == 0)
                                        <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Lotada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                            <i class="fas fa-times mr-1"></i>
                                            Superlotada
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white border border-gray-200 rounded-lg p-8 shadow-sm text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-chalkboard-teacher text-4xl mb-4"></i>
                                <p class="text-lg font-medium">Nenhuma turma encontrada</p>
                                <p class="text-sm mt-1 mb-4">Clique em "Nova Turma" para adicionar</p>
                                @can('turmas.criar')
                                <button 
                                    onclick="openCreateModal()"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
                                >
                                    <i class="fas fa-plus mr-1"></i> Nova Turma
                                </button>
                                @endcan
                            </div>
                        </div>
                    @endforelse
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-users text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma turma encontrada</h3>
                    <p class="text-gray-600 mb-4">Comece criando sua primeira turma.</p>
                    @can('turmas.criar')
                    <x-button type="button" color="primary" onclick="openCreateModal()">
                        <i class="fas fa-plus mr-2"></i>Criar Primeira Turma
                    </x-button>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Definir funções no escopo global
window.toggleDropdown = function(dropdownId) {
    console.log('toggleDropdown chamada para:', dropdownId);
    const dropdown = document.getElementById(dropdownId);
    
    if (!dropdown) {
        console.error('Dropdown não encontrado:', dropdownId);
        return;
    }
    
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
    
    // Fechar todos os outros dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== dropdownId) {
            d.classList.add('hidden');
        }
    });
    
    // Toggle do dropdown atual
    const isHidden = dropdown.classList.contains('hidden');
    console.log('Dropdown estava oculto:', isHidden);
    dropdown.classList.toggle('hidden');
    console.log('Dropdown agora está oculto:', dropdown.classList.contains('hidden'));
};

// Variáveis globais para modais
let turmaAtual = null;

// Função para abrir modal Ver Alunos
window.verAlunos = function(turmaId, turmaNome) {
    turmaAtual = turmaId;
    document.getElementById('modal-turma-nome').textContent = `Alunos da ${turmaNome}`;
    document.getElementById('ver-alunos-modal').classList.remove('hidden');
    carregarAlunosTurma(turmaId);
};

// Função para abrir modal de adicionar aluno
window.adicionarAluno = function(turmaId, nomeTurma) {
    turmaAtual = turmaId;
    document.getElementById('adicionar-aluno-modal').classList.remove('hidden');
    carregarAlunosDisponiveis();
};

window.viewTurma = function(id) {
    // Buscar dados da turma
    fetch(`/admin/turmas/${id}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Preencher dados no modal de visualização
            document.getElementById('view-turma-nome').textContent = data.turma.nome;
            document.getElementById('view-turma-codigo').textContent = data.turma.codigo;
            document.getElementById('view-turma-turno').textContent = data.turma.turno ? data.turma.turno.nome : 'N/A';
            document.getElementById('view-turma-grupo').textContent = data.turma.grupo ? data.turma.grupo.nome : 'N/A';
            document.getElementById('view-turma-capacidade').textContent = data.turma.capacidade || 'N/A';
            document.getElementById('view-turma-ano-letivo').textContent = data.turma.ano_letivo;
            document.getElementById('view-turma-status').textContent = data.turma.ativo ? 'Ativo' : 'Inativo';
            document.getElementById('view-turma-status').className = data.turma.ativo ? 'text-green-600 font-medium' : 'text-red-600 font-medium';
            
            // Abrir modal
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: 'view-turma-modal'
            }));
        } else {
            alert('Erro ao carregar dados da turma');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao carregar dados da turma');
    });
};

window.editTurma = function(id) {
    // Buscar dados da turma para edição
    fetch(`/admin/turmas/${id}/edit`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Preencher formulário de edição
            const form = document.getElementById('edit-turma-form');
            form.action = `/admin/turmas/${id}`;
            
            document.getElementById('edit-nome').value = data.turma.nome;
            document.getElementById('edit-capacidade').value = data.turma.capacidade;
            document.getElementById('edit-ano-letivo').value = data.turma.ano_letivo;
            
            // Preencher select de turnos
            const turnoSelect = document.getElementById('edit-turno-id');
            turnoSelect.innerHTML = '<option value="">Selecione um turno</option>';
            data.turnos.forEach(turno => {
                const option = document.createElement('option');
                option.value = turno.id;
                option.textContent = `${turno.nome} (${turno.hora_inicio} - ${turno.hora_fim})`;
                option.selected = turno.id === data.turma.turno_id;
                turnoSelect.appendChild(option);
            });
            
            // Preencher select de grupos
            const grupoSelect = document.getElementById('edit-grupo-id');
            grupoSelect.innerHTML = '<option value="">Selecione um grupo</option>';
            data.grupos.forEach(grupo => {
                const option = document.createElement('option');
                option.value = grupo.id;
                option.textContent = grupo.nome;
                option.selected = grupo.id === data.turma.grupo_id;
                grupoSelect.appendChild(option);
            });
            
            // Definir checkbox de status
            document.getElementById('edit-ativo').checked = data.turma.ativo;
            
            // Abrir modal
            const modal = document.getElementById('edit-turma-modal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        } else {
            alert('Erro ao carregar dados da turma');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao carregar dados da turma');
    });
};

window.deleteTurma = function(id, nome) {
    // Armazenar dados para exclusão
    window.turmaToDelete = { id: id, nome: nome };
    
    // Atualizar texto de confirmação
    const confirmationText = document.getElementById('delete-confirmation-text');
    if (confirmationText) {
        confirmationText.textContent = `Tem certeza que deseja excluir a turma "${nome}"? Esta ação não pode ser desfeita.`;
    }
    
    // Abrir modal
    const modal = document.getElementById('delete-turma-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
};

window.openCreateModal = function() {
    console.log('openCreateModal chamada');
    const modal = document.getElementById('create-turma-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
};

window.closeCreateModal = function() {
    const modal = document.getElementById('create-turma-modal');
    if (modal) {
        modal.classList.add('hidden');
        // Limpar formulário
        const form = document.getElementById('turma-form');
        if (form) {
            form.reset();
        }
    }
};

window.closeEditModal = function() {
    const modal = document.getElementById('edit-turma-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
};

window.closeDeleteModal = function() {
    const modal = document.getElementById('delete-turma-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
    // Limpar dados armazenados
    window.turmaToDelete = null;
};

window.confirmDeleteTurma = function() {
    if (window.turmaToDelete) {
        const { id, nome } = window.turmaToDelete;
        
        // Aqui você implementaria a requisição para excluir a turma
        // Por exemplo:
        // fetch(`/admin/turmas/${id}`, {
        //     method: 'DELETE',
        //     headers: {
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        //         'Content-Type': 'application/json'
        //     }
        // }).then(response => {
        //     if (response.ok) {
        //         location.reload(); // Recarregar página após exclusão
        //     }
        // });
        
        console.log('Excluir turma:', id, nome);
        closeDeleteModal();
    }
};

// Fechar dropdowns ao clicar fora
document.addEventListener('click', function(event) {
    if (!event.target.closest('[onclick*="toggleDropdown"]')) {
        const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
        allDropdowns.forEach(d => d.classList.add('hidden'));
    }
});

// Fechar dropdowns ao clicar fora - versão melhorada
document.addEventListener('click', function(e) {
    // Verificar se o clique não foi em um botão de dropdown ou no próprio dropdown
    if (!e.target.closest('.relative')) {
        const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
        allDropdowns.forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});
</script>

<!-- Modal para adicionar nova turma -->
<!-- Modal para adicionar nova turma -->
<div id="create-turma-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Adicionar Nova Turma</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeCreateModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Body -->
            <div class="py-4">
                <form id="turma-form" method="POST" action="{{ route('admin.turmas.store') }}">
                    @csrf
                    
                    <!-- Área para mensagens de erro/sucesso -->
                    <div id="form-messages" class="mb-4" style="display: none;"></div>

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="nome" value="Nome" />
                            <x-input id="nome" name="nome" type="text" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">O código da turma será gerado automaticamente</p>
                        </div>

                        <div>
                            <x-input-label for="turno_id" value="Turno" />
                            <x-select id="turno_id" name="turno_id" class="mt-1 block w-full" required>
                                <option value="">Selecione um turno</option>
                                @php
                                    $escola_id = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte') 
                                        ? (session('escola_atual') ?: auth()->user()->escola_id) 
                                        : auth()->user()->escola_id;
                                    $turnos = App\Models\Turno::where('ativo', true)
                                        ->where('escola_id', $escola_id)
                                        ->orderBy('ordem')
                                        ->orderBy('nome')
                                        ->get();
                                @endphp
                                @foreach($turnos as $turno)
                                    <option value="{{ $turno->id }}">{{ $turno->nome }} ({{ $turno->hora_inicio }} - {{ $turno->hora_fim }})</option>
                                @endforeach
                            </x-select>
                            <x-input-error :messages="$errors->get('turno_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="grupo_id" value="Grupo" />
                            <x-select id="grupo_id" name="grupo_id" class="mt-1 block w-full" required>
                                <option value="">Selecione um grupo</option>
                                @php
                                    $grupos = App\Models\Grupo::where('ativo', true)
                                        ->where('escola_id', $escola_id)
                                        ->orderBy('ordem')
                                        ->orderBy('nome')
                                        ->get();
                                @endphp
                                @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id }}">{{ $grupo->nome }}</option>
                                @endforeach
                            </x-select>
                            <x-input-error :messages="$errors->get('grupo_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="capacidade" value="Capacidade" />
                            <x-input id="capacidade" name="capacidade" type="number" min="1" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('capacidade')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="ano_letivo" value="Ano Letivo" />
                            <x-select id="ano_letivo" name="ano_letivo" class="mt-1 block w-full" required>
                                <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                <option value="{{ date('Y')+1 }}">{{ date('Y')+1 }}</option>
                                <option value="{{ date('Y')-1 }}">{{ date('Y')-1 }}</option>
                            </x-select>
                            <x-input-error :messages="$errors->get('ano_letivo')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeCreateModal()" class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-md transition-colors duration-200">
                            Cancelar
                        </button>
                        <button type="submit" id="submit-btn" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                            <i class="fas fa-save mr-1"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('turma-form');
            const messagesDiv = document.getElementById('form-messages');
            const submitBtn = document.getElementById('submit-btn');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Desabilitar botão de envio
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';
                
                // Limpar mensagens anteriores
                messagesDiv.style.display = 'none';
                messagesDiv.innerHTML = '';
                
                // Enviar dados via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Sucesso - mostrar mensagem e recarregar página
                        messagesDiv.innerHTML = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"><i class="fas fa-check-circle mr-2"></i>' + data.message + '</div>';
                        messagesDiv.style.display = 'block';
                        
                        // Recarregar página após 1.5 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        // Erro - mostrar mensagens de erro
                        let errorHtml = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">';
                        
                        if (data.message) {
                            errorHtml += '<p><i class="fas fa-exclamation-circle mr-2"></i>' + data.message + '</p>';
                        }
                        
                        if (data.errors) {
                            errorHtml += '<ul class="mt-2 list-disc list-inside">';
                            Object.values(data.errors).forEach(errors => {
                                errors.forEach(error => {
                                    errorHtml += '<li>' + error + '</li>';
                                });
                            });
                            errorHtml += '</ul>';
                        }
                        
                        errorHtml += '</div>';
                        messagesDiv.innerHTML = errorHtml;
                        messagesDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    messagesDiv.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"><i class="fas fa-exclamation-circle mr-2"></i>Erro ao processar solicitação. Tente novamente.</div>';
                    messagesDiv.style.display = 'block';
                })
                .finally(() => {
                    // Reabilitar botão
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Salvar';
                });
            });
        });
        
        // Gerenciamento de modais
        window.addEventListener('open-modal', function(e) {
            console.log('Evento open-modal recebido:', e.detail);
            const modalId = e.detail;
            const modal = document.getElementById(modalId);
            if (modal) {
                console.log('Modal encontrado:', modalId);
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                console.error('Modal não encontrado:', modalId);
            }
        });
        
        window.addEventListener('close-modal', function(e) {
            const modalId = e.detail;
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        });
        
        // Fechar modal ao clicar fora
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('bg-gray-600') && e.target.classList.contains('bg-opacity-50')) {
                const modals = ['view-turma-modal', 'edit-turma-modal', 'create-turma-modal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && !modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                });
            }
        });
        
        // Envio do formulário de criação
        const createForm = document.getElementById('create-turma-form');
        if (createForm) {
            createForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const actionUrl = this.action;
                
                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fechar modal
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: 'create-turma-modal'
                    }));
                    
                    // Mostrar mensagem de sucesso
                    alert('Turma criada com sucesso!');
                    
                    // Recarregar página
                    window.location.reload();
                } else {
                    alert('Erro ao criar turma: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao criar turma');
            });
        }
        
        // Envio do formulário de edição
        const editForm = document.getElementById('edit-turma-form');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const actionUrl = this.action;
                
                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fechar modal
                    window.dispatchEvent(new CustomEvent('close-modal', {
                        detail: 'edit-turma-modal'
                    }));
                    
                    // Mostrar mensagem de sucesso
                    alert('Turma atualizada com sucesso!');
                    
                    // Recarregar página
                    window.location.reload();
                } else {
                    alert('Erro ao atualizar turma: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar turma');
            });
        }
    </script>

<!-- Modal de Visualização de Turma -->
<div id="view-turma-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Visualizar Turma</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'view-turma-modal' }))">
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Grupo</label>
                        <p id="view-turma-grupo" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
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
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'view-turma-modal' }))">
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
                            <label for="edit-grupo-id" class="block text-sm font-medium text-gray-700 mb-1">Grupo</label>
                            <select id="edit-grupo-id" name="grupo_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione um grupo</option>
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

<!-- Modal de Adição de Nova Turma -->
<div id="create-turma-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Nova Turma</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'create-turma-modal' }))">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Formulário -->
            <form id="create-turma-form" action="{{ route('admin.turmas.store') }}" method="POST" class="mt-4">
                @csrf
                
                <div class="space-y-4">
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome da Turma <span class="text-red-500">*</span></label>
                        <input type="text" id="nome" name="nome" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="turno_id" class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                            <select id="turno_id" name="turno_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione um turno</option>
                                @php
                                    $escola_id = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte') 
                                        ? (session('escola_atual') ?: auth()->user()->escola_id) 
                                        : auth()->user()->escola_id;
                                    $turnos = App\Models\Turno::where('ativo', true)
                                        ->where('escola_id', $escola_id)
                                        ->orderBy('ordem')
                                        ->orderBy('nome')
                                        ->get();
                                @endphp
                                @foreach($turnos as $turno)
                                    <option value="{{ $turno->id }}">{{ $turno->nome }} ({{ $turno->hora_inicio }} - {{ $turno->hora_fim }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="grupo_id" class="block text-sm font-medium text-gray-700 mb-1">Grupo</label>
                            <select id="grupo_id" name="grupo_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Selecione um grupo</option>
                                @php
                                    $grupos = App\Models\Grupo::where('ativo', true)
                                        ->where('escola_id', $escola_id)
                                        ->orderBy('ordem')
                                        ->orderBy('nome')
                                        ->get();
                                @endphp
                                @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id }}">{{ $grupo->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="capacidade" class="block text-sm font-medium text-gray-700 mb-1">Capacidade</label>
                            <input type="number" id="capacidade" name="capacidade" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="ano_letivo" class="block text-sm font-medium text-gray-700 mb-1">Ano Letivo <span class="text-red-500">*</span></label>
                            <input type="number" id="ano_letivo" name="ano_letivo" value="{{ date('Y') }}" min="2020" max="2030" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'create-turma-modal' }))">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Criar Turma
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Transferir Aluno -->
<div id="transferir-aluno-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-exchange-alt mr-2 text-blue-600"></i>
                    Transferir Aluno
                </h3>
                <button onclick="closeTransferirAlunoModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Informações do Aluno -->
            <div class="bg-blue-50 p-4 rounded-lg mb-4">
                <div class="flex items-center">
                    <i class="fas fa-user text-blue-600 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-gray-900" id="transfer-aluno-nome">Nome do Aluno</h4>
                        <p class="text-sm text-gray-600">Turma atual: <span id="transfer-turma-atual">Turma Atual</span></p>
                    </div>
                </div>
            </div>
            
            <!-- Busca de Turmas -->
            <div class="mb-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input 
                        type="text" 
                        id="buscar-turma-destino" 
                        placeholder="Buscar turma de destino..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onkeyup="filtrarTurmasDestino()"
                    >
                </div>
            </div>
            
            <!-- Lista de Turmas Disponíveis -->
            <div class="mb-4">
                <h5 class="text-sm font-medium text-gray-700 mb-2">Selecione a turma de destino:</h5>
                <div id="lista-turmas-destino" class="max-h-64 overflow-y-auto border border-gray-200 rounded-md">
                    <!-- Conteúdo será carregado via JavaScript -->
                </div>
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button 
                    onclick="closeTransferirAlunoModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                >
                    Cancelar
                </button>
                <button 
                    id="confirmar-transferencia-btn"
                    onclick="confirmarTransferencia()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled
                >
                    <i class="fas fa-exchange-alt mr-1"></i> Transferir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Alunos -->
<div id="ver-alunos-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-users mr-2 text-indigo-600"></i>
                    <span id="modal-turma-nome">Alunos da Turma</span>
                </h3>
                <button onclick="closeVerAlunosModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Busca -->
            <div class="mb-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input 
                        type="text" 
                        id="buscar-aluno" 
                        placeholder="Buscar aluno..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        onkeyup="filtrarAlunos()"
                    >
                </div>
            </div>
            
            <!-- Lista de Alunos -->
            <div id="lista-alunos" class="max-h-96 overflow-y-auto">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
            
            <!-- Footer -->
            <div class="flex justify-between items-center mt-6 pt-4 border-t">
                <button 
                    onclick="openAdicionarAlunoModal()" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
                >
                    <i class="fas fa-plus mr-1"></i> Adicionar Aluno
                </button>
                <button 
                    onclick="gerarRelatorioTurma()" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
                >
                    <i class="fas fa-chart-bar mr-1"></i> Relatório
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar Aluno -->
<div id="adicionar-aluno-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-user-plus mr-2 text-green-600"></i>
                    Adicionar Aluno à Turma
                </h3>
                <button onclick="closeAdicionarAlunoModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Busca -->
            <div class="mb-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input 
                        type="text" 
                        id="buscar-aluno-adicionar" 
                        placeholder="Buscar por nome, CPF ou código..." 
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        onkeyup="buscarAlunosDisponiveis()"
                    >
                </div>
            </div>
            
            <!-- Alunos Disponíveis -->
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-clipboard-list mr-2"></i>
                    Alunos Disponíveis:
                </h4>
                <div id="alunos-disponiveis" class="max-h-48 overflow-y-auto border border-gray-200 rounded-md p-3">
                    <!-- Conteúdo será carregado via AJAX -->
                </div>
            </div>
            
            <!-- Alunos em Outras Turmas -->
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2 text-yellow-500"></i>
                    Alunos em Outras Turmas:
                </h4>
                <div id="alunos-outras-turmas" class="max-h-32 overflow-y-auto border border-gray-200 rounded-md p-3">
                    <!-- Conteúdo será carregado via AJAX -->
                </div>
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button 
                    onclick="closeAdicionarAlunoModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                >
                    Cancelar
                </button>
                <button 
                    onclick="adicionarAlunosSelecionados()" 
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                >
                    Adicionar Selecionados
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let alunosSelecionados = [];

// Função para fechar modal Ver Alunos
function closeVerAlunosModal() {
    document.getElementById('ver-alunos-modal').classList.add('hidden');
    document.getElementById('buscar-aluno').value = '';
}

// Função para abrir modal Adicionar Aluno
function openAdicionarAlunoModal() {
    if (!turmaAtual) return;
    document.getElementById('adicionar-aluno-modal').classList.remove('hidden');
    carregarAlunosDisponiveis();
}

// Função para fechar modal Adicionar Aluno
function closeAdicionarAlunoModal() {
    document.getElementById('adicionar-aluno-modal').classList.add('hidden');
    document.getElementById('buscar-aluno-adicionar').value = '';
    alunosSelecionados = [];
}

// Função para adicionar aluno (chamada pelo botão)
function adicionarAluno(turmaId, turmaNome) {
    turmaAtual = turmaId;
    openAdicionarAlunoModal();
}

// Carregar alunos da turma
function carregarAlunosTurma(turmaId) {
    fetch(`/admin/turmas/${turmaId}/alunos`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('lista-alunos');
            if (data.alunos && data.alunos.length > 0) {
                container.innerHTML = data.alunos.map(aluno => `
                    <div class="flex items-center justify-between p-3 border-b border-gray-200 hover:bg-gray-50 aluno-item cursor-move" 
                         draggable="true" 
                         data-aluno-id="${aluno.id}" 
                         data-aluno-nome="${aluno.nome}"
                         data-turma-origem="${turmaId}"
                         ondragstart="dragStart(event)"
                         ondragend="dragEnd(event)">
                        <div class="flex items-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full ${aluno.status === 'ativo' ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600'} mr-3">
                                <i class="fas ${aluno.status === 'ativo' ? 'fa-check' : 'fa-exclamation-triangle'} text-sm"></i>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    <i class="fas fa-grip-vertical text-gray-400 mr-2"></i>
                                    ${aluno.nome}
                                </p>
                                ${aluno.status !== 'ativo' ? `<p class="text-xs text-yellow-600">(${aluno.status})</p>` : ''}
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

// Carregar alunos disponíveis
function carregarAlunosDisponiveis() {
    fetch(`/admin/turmas/${turmaAtual}/alunos-disponiveis`)
        .then(response => response.json())
        .then(data => {
            // Alunos disponíveis (sem turma)
            const containerDisponiveis = document.getElementById('alunos-disponiveis');
            if (data.disponiveis && data.disponiveis.length > 0) {
                containerDisponiveis.innerHTML = data.disponiveis.map(aluno => `
                    <div class="flex items-center p-2 hover:bg-gray-50 rounded">
                        <input 
                            type="checkbox" 
                            id="aluno-${aluno.id}" 
                            value="${aluno.id}"
                            onchange="toggleAlunoSelecionado(${aluno.id})"
                            class="mr-3 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                        >
                        <label for="aluno-${aluno.id}" class="text-sm text-gray-900 cursor-pointer">
                            ${aluno.nome} ${aluno.observacao ? `(${aluno.observacao})` : ''}
                        </label>
                    </div>
                `).join('');
            } else {
                containerDisponiveis.innerHTML = `
                    <div class="text-center py-4 text-gray-500">
                        <p class="text-sm">Nenhum aluno disponível</p>
                    </div>
                `;
            }

            // Alunos em outras turmas
            const containerOutrasTurmas = document.getElementById('alunos-outras-turmas');
            if (data.outras_turmas && data.outras_turmas.length > 0) {
                containerOutrasTurmas.innerHTML = data.outras_turmas.map(aluno => `
                    <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                        <span class="text-sm text-gray-900">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                            ${aluno.nome} (${aluno.turma_atual})
                        </span>
                        <button 
                            onclick="abrirModalTransferencia(${aluno.id}, \"${aluno.nome}\", \"${aluno.turma_atual}\", ${aluno.turma_atual_id})" 
                            class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded hover:bg-blue-200"
                        >
                            <i class="fas fa-exchange-alt mr-1"></i>Transferir
                        </button>
                    </div>
                `).join('');
            } else {
                containerOutrasTurmas.innerHTML = `
                    <div class="text-center py-4 text-gray-500">
                        <p class="text-sm">Nenhum aluno em outras turmas</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erro ao carregar alunos disponíveis:', error);
        });
}

// Toggle seleção de aluno
function toggleAlunoSelecionado(alunoId) {
    const index = alunosSelecionados.indexOf(alunoId);
    if (index > -1) {
        alunosSelecionados.splice(index, 1);
    } else {
        alunosSelecionados.push(alunoId);
    }
}

// Adicionar alunos selecionados
function adicionarAlunosSelecionados() {
    if (alunosSelecionados.length === 0) {
        if (typeof window.alertSystem !== 'undefined' && window.alertSystem.warning) {
            window.alertSystem.warning('Selecione pelo menos um aluno para adicionar.');
        } else {
            alert('Selecione pelo menos um aluno para adicionar.');
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
            if (typeof window.alertSystem !== 'undefined' && window.alertSystem.success) {
                window.alertSystem.success(data.message || 'Alunos adicionados com sucesso!');
            } else {
                alert('Alunos adicionados com sucesso!');
            }
            closeAdicionarAlunoModal();
            carregarAlunosTurma(turmaAtual);
            location.reload(); // Recarregar para atualizar contadores
        } else {
            if (typeof window.alertSystem !== 'undefined' && window.alertSystem.error) {
                window.alertSystem.error('Erro ao adicionar alunos: ' + (data.message || 'Erro desconhecido'));
            } else {
                alert('Erro ao adicionar alunos: ' + (data.message || 'Erro desconhecido'));
            }
        }
    })
    .catch(error => {
        console.error('Erro ao adicionar alunos:', error);
        if (typeof window.alertSystem !== 'undefined' && window.alertSystem.error) {
            window.alertSystem.error('Erro ao adicionar alunos. Tente novamente.');
        } else {
            alert('Erro ao adicionar alunos.');
        }
    });
}

// Filtrar alunos na busca
function filtrarAlunos() {
    const busca = document.getElementById('buscar-aluno').value.toLowerCase();
    const alunos = document.querySelectorAll('#lista-alunos > div');
    
    alunos.forEach(aluno => {
        const nome = aluno.querySelector('p').textContent.toLowerCase();
        if (nome.includes(busca)) {
            aluno.style.display = 'flex';
        } else {
            aluno.style.display = 'none';
        }
    });
}

// Buscar alunos disponíveis
function buscarAlunosDisponiveis() {
    const busca = document.getElementById('buscar-aluno-adicionar').value;
    if (busca.length >= 2) {
        fetch(`/admin/turmas/${turmaAtual}/alunos-disponiveis?busca=${encodeURIComponent(busca)}`)
            .then(response => response.json())
            .then(data => {
                // Atualizar listas com resultados da busca
                carregarAlunosDisponiveis();
            });
    } else if (busca.length === 0) {
        carregarAlunosDisponiveis();
    }
}

// Remover aluno da turma
function removerAluno(alunoId) {
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
                location.reload(); // Recarregar para atualizar contadores
            } else {
                alert('Erro ao remover aluno: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao remover aluno:', error);
            alert('Erro ao remover aluno.');
        });
    }
}

// Transferir aluno
function transferirAluno(alunoId) {
    if (confirm('Tem certeza que deseja transferir este aluno para esta turma?')) {
        fetch(`/admin/turmas/${turmaAtual}/transferir-aluno/${alunoId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Aluno transferido com sucesso!');
                carregarAlunosDisponiveis();
                carregarAlunosTurma(turmaAtual);
                location.reload(); // Recarregar para atualizar contadores
            } else {
                alert('Erro ao transferir aluno: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro ao transferir aluno:', error);
            alert('Erro ao transferir aluno.');
        });
    }
}

// Editar aluno
function editarAluno(alunoId) {
    // Implementar redirecionamento para página de edição do aluno
    window.location.href = `/alunos/${alunoId}/edit`;
}



// ===== FUNÇÕES DE DRAG & DROP =====
let draggedElement = null;
let draggedData = null;

// Início do arrasto
function dragStart(event) {
    draggedElement = event.target;
    draggedData = {
        alunoId: event.target.dataset.alunoId,
        alunoNome: event.target.dataset.alunoNome,
        turmaOrigem: event.target.dataset.turmaOrigem
    };
    
    // Adicionar efeito visual
    event.target.style.opacity = '0.5';
    event.target.classList.add('dragging');
    
    // Destacar turmas válidas para drop
    document.querySelectorAll('.turma-card').forEach(card => {
        if (card.dataset.turmaId !== draggedData.turmaOrigem) {
            card.classList.add('drop-zone-active');
        }
    });
}

// Final do arrasto
function dragEnd(event) {
    event.target.style.opacity = '1';
    event.target.classList.remove('dragging');
    
    // Remover destaque das turmas
    document.querySelectorAll('.turma-card').forEach(card => {
        card.classList.remove('drop-zone-active', 'drop-zone-hover');
    });
    
    draggedElement = null;
    draggedData = null;
}

// Permitir drop
function allowDrop(event) {
    event.preventDefault();
}

// Entrar na zona de drop
function dragEnter(event) {
    event.preventDefault();
    if (draggedData && event.currentTarget.dataset.turmaId !== draggedData.turmaOrigem) {
        event.currentTarget.classList.add('drop-zone-hover');
    }
}

// Sair da zona de drop
function dragLeave(event) {
    event.currentTarget.classList.remove('drop-zone-hover');
}

// Executar drop
function dropAluno(event) {
    event.preventDefault();
    event.currentTarget.classList.remove('drop-zone-hover');
    
    if (!draggedData) return;
    
    const turmaDestino = event.currentTarget.dataset.turmaId;
    const turmaDestinoNome = event.currentTarget.dataset.turmaNome;
    const capacidade = parseInt(event.currentTarget.dataset.turmaCapacidade);
    const ocupacao = parseInt(event.currentTarget.dataset.turmaOcupacao);
    
    // Verificar se não é a mesma turma
    if (turmaDestino === draggedData.turmaOrigem) {
        return;
    }
    
    // Verificar capacidade
    if (ocupacao >= capacidade) {
        alert(`❌ Não é possível transferir o aluno.\nA turma '${turmaDestinoNome}' está lotada (${ocupacao}/${capacidade}).`);
        return;
    }
    
    // Confirmação visual
    const confirmacao = confirm(
        `🔄 Confirmar Transferência\n\n` +
        `Aluno: ${draggedData.alunoNome}\n` +
        `Para: ${turmaDestinoNome}\n` +
        `Ocupação atual: ${ocupacao}/${capacidade}\n\n` +
        `Deseja confirmar a transferência?`
    );
    
    if (confirmacao) {
        transferirAlunoViaDragDrop(draggedData.alunoId, turmaDestino, turmaDestinoNome);
    }
}

// Função para transferir aluno via drag & drop
function transferirAlunoViaDragDrop(alunoId, turmaDestinoId, turmaDestinoNome) {
    // Mostrar loading
    const loadingToast = document.createElement('div');
    loadingToast.className = `fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg z-50`;
    loadingToast.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>Transferindo aluno...`;
    document.body.appendChild(loadingToast);
    
    fetch(`/alunos/${alunoId}/transferir`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            turma_id: turmaDestinoId
        })
    })
    .then(response => response.json())
    .then(data => {
        document.body.removeChild(loadingToast);
        
        if (data.success) {
            // Toast de sucesso
            const successToast = document.createElement('div');
            successToast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
            successToast.innerHTML = `<i class="fas fa-check mr-2"></i>Aluno transferido para "${turmaDestinoNome}" com sucesso!`;
            document.body.appendChild(successToast);
            
            setTimeout(() => {
                document.body.removeChild(successToast);
            }, 3000);
            
            // Recarregar dados
            if (turmaAtual) {
                carregarAlunosTurma(turmaAtual);
            }
            location.reload(); // Atualizar contadores das turmas
        } else {
            // Toast de erro
            const errorToast = document.createElement('div');
            errorToast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
            errorToast.innerHTML = `<i class="fas fa-times mr-2"></i>Erro: ${data.message}`;
            document.body.appendChild(errorToast);
            
            setTimeout(() => {
                document.body.removeChild(errorToast);
            }, 3000);
        }
    })
    .catch(error => {
        document.body.removeChild(loadingToast);
        console.error('Erro ao transferir aluno:', error);
        
        const errorToast = document.createElement('div');
        errorToast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
        errorToast.innerHTML = '<i class="fas fa-times mr-2"></i>Erro ao transferir aluno.';
        document.body.appendChild(errorToast);
        
        setTimeout(() => {
            document.body.removeChild(errorToast);
        }, 3000);
    });
}

// ===== FUNÇÕES DO MODAL DE TRANSFERÊNCIA =====
let alunoParaTransferir = null;
let turmaSelecionada = null;

// Abrir modal de transferência
function abrirModalTransferencia(alunoId, alunoNome, turmaAtual, turmaAtualId) {
    alunoParaTransferir = {
        id: alunoId,
        nome: alunoNome,
        turmaAtual: turmaAtual,
        turmaAtualId: turmaAtualId
    };
    
    // Preencher informações do aluno
    document.getElementById('transfer-aluno-nome').textContent = alunoNome;
    document.getElementById('transfer-turma-atual').textContent = turmaAtual;
    
    // Limpar busca e seleção
    document.getElementById('buscar-turma-destino').value = '';
    turmaSelecionada = null;
    document.getElementById('confirmar-transferencia-btn').disabled = true;
    
    // Carregar turmas disponíveis
    carregarTurmasDisponiveis();
    
    // Mostrar modal
    document.getElementById('transferir-aluno-modal').classList.remove('hidden');
}

// Fechar modal de transferência
function closeTransferirAlunoModal() {
    document.getElementById('transferir-aluno-modal').classList.add('hidden');
    alunoParaTransferir = null;
    turmaSelecionada = null;
}

// Carregar turmas disponíveis
function carregarTurmasDisponiveis() {
    const busca = document.getElementById('buscar-turma-destino').value;
    const turmaAtualId = alunoParaTransferir ? alunoParaTransferir.turmaAtualId : null;
    
    let url = '{{ route("admin.turmas.listar-todas") }}';
    const params = new URLSearchParams();
    
    if (busca) {
        params.append('busca', busca);
    }
    
    if (turmaAtualId) {
        params.append('excluir_turma_id', turmaAtualId);
    }
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('lista-turmas-destino');
            
            if (data.turmas && data.turmas.length > 0) {
                // Filtrar turma atual
                const turmasDisponiveis = data.turmas.filter(turma => 
                    turma.nome !== alunoParaTransferir.turmaAtual
                );
                
                container.innerHTML = turmasDisponiveis.map(turma => {
                    const ocupacao = turma.alunos_count || 0;
                    const capacidade = turma.capacidade || 0;
                    const percentual = capacidade > 0 ? Math.round((ocupacao / capacidade) * 100) : 0;
                    const isLotada = ocupacao >= capacidade;
                    
                    return `
                        <div class="turma-destino-item p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer ${isLotada ? 'opacity-50' : ''}" 
                             ${isLotada ? '' : `onclick="selecionarTurmaDestino(${turma.id}, '${turma.nome.replace(/'/g, "\\'")}', ${ocupacao}, ${capacidade})"`}>
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <h6 class="font-medium text-gray-900">${turma.nome}</h6>
                                    <p class="text-sm text-gray-600">
                                        ${turma.turno} - ${turma.grupo}
                                    </p>
                                    <div class="flex items-center mt-1">
                                        <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-${isLotada ? 'red' : percentual > 80 ? 'yellow' : 'green'}-500 h-2 rounded-full" 
                                                 style="width: ${Math.min(percentual, 100)}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500">${ocupacao}/${capacidade}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    ${isLotada ? 
                                        '<span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Lotada</span>' : 
                                        '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Disponível</span>'
                                    }
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-exclamation-circle text-2xl mb-2"></i>
                        <p>Nenhuma turma disponível</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Erro ao carregar turmas:', error);
            document.getElementById('lista-turmas-destino').innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p>Erro ao carregar turmas</p>
                </div>
            `;
        });
}

// Selecionar turma de destino
function selecionarTurmaDestino(turmaId, turmaNome, ocupacao, capacidade) {
    turmaSelecionada = {
        id: turmaId,
        nome: turmaNome,
        ocupacao: ocupacao,
        capacidade: capacidade
    };
    
    // Remover seleção anterior
    document.querySelectorAll('.turma-destino-item').forEach(item => {
        item.classList.remove('bg-blue-100', 'border-blue-500');
    });
    
    // Adicionar seleção atual
    event.target.closest('.turma-destino-item').classList.add('bg-blue-100', 'border-blue-500');
    
    // Habilitar botão de confirmação
    document.getElementById('confirmar-transferencia-btn').disabled = false;
}

// Filtrar turmas de destino
function filtrarTurmasDestino() {
    const busca = document.getElementById('buscar-turma-destino').value.toLowerCase();
    const items = document.querySelectorAll('.turma-destino-item');
    
    items.forEach(item => {
        const texto = item.textContent.toLowerCase();
        if (texto.includes(busca)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Confirmar transferência
function confirmarTransferencia() {
    if (!alunoParaTransferir || !turmaSelecionada) {
        alert('Erro: dados de transferência incompletos');
        return;
    }
    
    const confirmacao = confirm(
        `🔄 Confirmar Transferência\n\n` +
        `Aluno: ${alunoParaTransferir.nome}\n` +
        `De: ${alunoParaTransferir.turmaAtual}\n` +
        `Para: ${turmaSelecionada.nome}\n` +
        `Ocupação destino: ${turmaSelecionada.ocupacao}/${turmaSelecionada.capacidade}\n\n` +
        `Deseja confirmar a transferência?`
    );
    
    if (confirmacao) {
        executarTransferencia();
    }
}

// Executar transferência
function executarTransferencia() {
    // Mostrar loading
    const loadingToast = document.createElement('div');
    loadingToast.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg z-50';
    loadingToast.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Transferindo aluno...';
    document.body.appendChild(loadingToast);
    
    fetch('{{ route("admin.turmas.transferir-aluno") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            aluno_id: alunoParaTransferir.id,
            turma_destino_id: turmaSelecionada.id
        })
    })
    .then(response => response.json())
    .then(data => {
        document.body.removeChild(loadingToast);
        
        if (data.success) {
            // Fechar modal
            closeTransferirAlunoModal();
            
            // Mostrar sucesso
            const successToast = document.createElement('div');
            successToast.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
            successToast.innerHTML = '<i class="fas fa-check mr-2"></i>Aluno transferido com sucesso!';
            document.body.appendChild(successToast);
            
            // Recarregar dados
            if (typeof carregarAlunosDisponiveis === 'function') {
                carregarAlunosDisponiveis();
            }
            
            setTimeout(() => {
                document.body.removeChild(successToast);
            }, 3000);
        } else {
            const errorToast = document.createElement('div');
            errorToast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
            errorToast.innerHTML = '<i class="fas fa-times mr-2"></i>' + (data.message || 'Erro ao transferir aluno');
            document.body.appendChild(errorToast);
            
            setTimeout(() => {
                document.body.removeChild(errorToast);
            }, 3000);
        }
    })
    .catch(error => {
        document.body.removeChild(loadingToast);
        console.error('Erro ao transferir aluno:', error);
        
        const errorToast = document.createElement('div');
        errorToast.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg z-50';
        errorToast.innerHTML = '<i class="fas fa-times mr-2"></i>Erro ao transferir aluno.';
        document.body.appendChild(errorToast);
        
        setTimeout(() => {
            document.body.removeChild(errorToast);
        }, 3000);
    });
}

// Gerar relatório da turma
function gerarRelatorioTurma() {
    window.open(`/admin/turmas/${turmaAtual}/relatorio`, '_blank');
}
</script>

@endsection