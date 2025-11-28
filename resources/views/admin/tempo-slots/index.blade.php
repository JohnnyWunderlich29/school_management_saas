@extends('layouts.app')

@section('title', 'Tempo Slots')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-8" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <a href="{{ route('admin.turnos.index') }}" class="hover:text-blue-600 transition-colors">Turnos</a>
                </li>
                <li>
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <a href="{{ route('admin.turnos.show', $turno->id) }}" class="hover:text-blue-600 transition-colors">{{ $turno->nome }}</a>
                </li>
                <li>
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li class="text-gray-900 font-medium">Tempo Slots</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-2xl font-bold text-gray-900">Tempo Slots - {{ $turno->nome }}</h1>
                        <p class="text-sm text-gray-600 mt-1">Gerencie os períodos de tempo do turno</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('admin.turnos.tempo-slots.create', $turno) }}" 
                           class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Novo Tempo Slot
                        </a>
                        <a href="{{ route('admin.turnos.show', $turno->id) }}" 
                           class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($tempoSlots->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($tempoSlots as $tempoSlot)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center space-x-2">
                                    @php
                                        $tipoColors = [
                                            'aula' => 'bg-blue-100 text-blue-800',
                                            'intervalo' => 'bg-green-100 text-green-800',
                                            'almoco' => 'bg-yellow-100 text-yellow-800',
                                            'recreio' => 'bg-purple-100 text-purple-800',
                                            'outro' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $colorClass = $tipoColors[$tempoSlot->tipo] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                        {{ ucfirst($tempoSlot->tipo) }}
                                    </span>
                                </div>
                                <div class="relative">
                                    <button type="button" class="p-1 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded" onclick="toggleDropdown('dropdown-{{ $tempoSlot->id }}')">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                        </svg>
                                    </button>
                                    <div id="dropdown-{{ $tempoSlot->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                                        <div class="py-1">
                                            <button onclick="openViewModal({{ $tempoSlot->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Visualizar
                                            </button>
                                            <button onclick="openEditModal({{ $tempoSlot->id }})" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Editar
                                            </button>
                                            <hr class="my-1">
                                            <form action="{{ route('admin.turnos.tempo-slots.destroy', [$turno, $tempoSlot]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50" onclick="return confirm('Tem certeza que deseja excluir este tempo slot?')">
                                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Horário</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $tempoSlot->hora_inicio }} - {{ $tempoSlot->hora_fim }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Duração</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $tempoSlot->duracao_minutos }} min</p>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Ordem</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    #{{ $tempoSlot->ordem }}
                                </span>
                            </div>
                            
                            @if($tempoSlot->descricao)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Descrição</p>
                                    <p class="text-sm text-gray-700 leading-relaxed">{{ $tempoSlot->descricao }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Estado vazio -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum tempo slot encontrado</h3>
                    <p class="text-gray-500 mb-6 max-w-sm mx-auto">Comece criando o primeiro tempo slot para organizar os períodos deste turno.</p>
                    <a href="{{ route('admin.turnos.tempo-slots.create', $turno) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Criar Primeiro Tempo Slot
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal de Visualização -->
<x-modal name="view-modal" title="Visualizar Tempo Slot" max-width="2xl">
    <div id="view-modal-content" class="space-y-4">
        <!-- Conteúdo será carregado via AJAX -->
    </div>
    <x-slot name="footer">
        <button type="button" onclick="closeModal('view-modal')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
            Fechar
        </button>
    </x-slot>
</x-modal>

<!-- Modal de Edição -->
<x-modal name="edit-modal" title="Editar Tempo Slot" max-width="2xl">
    <div id="edit-modal-content" class="space-y-4">
        <!-- Conteúdo será carregado via AJAX -->
    </div>
    <x-slot name="footer">
        <button type="button" onclick="closeModal('edit-modal')" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors mr-2">
            Cancelar
        </button>
        <button type="button" onclick="submitEditForm()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
            Salvar
        </button>
    </x-slot>
</x-modal>

<script>
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
    
    // Fechar todos os outros dropdowns
    allDropdowns.forEach(d => {
        if (d.id !== dropdownId) {
            d.classList.add('hidden');
        }
    });
    
    // Toggle do dropdown atual
    dropdown.classList.toggle('hidden');
}

// Fechar dropdown ao clicar fora
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('[id^="dropdown-"]');
    const buttons = document.querySelectorAll('[onclick^="toggleDropdown"]');
    
    let clickedButton = false;
    buttons.forEach(button => {
        if (button.contains(event.target)) {
            clickedButton = true;
        }
    });
    
    if (!clickedButton) {
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }
});

// Função para abrir modal de visualização
function openViewModal(tempoSlotId) {
    const turnoId = {{ $turno->id }};
    
    fetch(`/admin/turnos/${turnoId}/tempo-slots/${tempoSlotId}/modal-show`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('view-modal-content').innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-2">Informações Básicas</h4>
                        <p><strong>Nome:</strong> ${data.data.nome}</p>
                        <p><strong>Tipo:</strong> ${data.data.tipo}</p>
                        <p><strong>Horário:</strong> ${data.data.horario}</p>
                        <p><strong>Duração:</strong> ${data.data.duracao}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-2">Detalhes</h4>
                        <p><strong>Ordem:</strong> #${data.data.ordem}</p>
                        <p><strong>Ativo:</strong> ${data.data.ativo}</p>
                        <p><strong>Turno:</strong> ${data.data.turno}</p>
                        <p><strong>Descrição:</strong> ${data.data.descricao}</p>
                    </div>
                </div>
            `;
            showModal('view-modal');
        } else {
            alert('Erro ao carregar dados do tempo slot');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao carregar dados do tempo slot');
    });
}

// Função para abrir modal de edição
function openEditModal(tempoSlotId) {
    const turnoId = {{ $turno->id }};
    
    fetch(`/admin/turnos/${turnoId}/tempo-slots/${tempoSlotId}/modal-edit`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let tipoOptions = '';
            Object.entries(data.data.tipos).forEach(([value, label]) => {
                const selected = value === data.data.tipo ? 'selected' : '';
                tipoOptions += `<option value="${value}" ${selected}>${label}</option>`;
            });
            
            const ativoChecked = data.data.ativo ? 'checked' : '';
            
            document.getElementById('edit-modal-content').innerHTML = `
                <form id="edit-tempo-slot-form" action="${data.data.update_url}" method="POST">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="PUT">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                            <input type="text" name="nome" id="nome" value="${data.data.nome}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="tipo" id="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                ${tipoOptions}
                            </select>
                        </div>
                        <div>
                            <label for="hora_inicio" class="block text-sm font-medium text-gray-700 mb-1">Horário Início</label>
                            <input type="time" name="hora_inicio" id="hora_inicio" value="${data.data.hora_inicio}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="hora_fim" class="block text-sm font-medium text-gray-700 mb-1">Horário Fim</label>
                            <input type="time" name="hora_fim" id="hora_fim" value="${data.data.hora_fim}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="ordem" class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                            <input type="number" name="ordem" id="ordem" value="${data.data.ordem}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label for="duracao_minutos" class="block text-sm font-medium text-gray-700 mb-1">Duração (minutos)</label>
                            <input type="number" name="duracao_minutos" id="duracao_minutos" value="${data.data.duracao_minutos}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <textarea name="descricao" id="descricao" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">${data.data.descricao || ''}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="ativo" value="1" ${ativoChecked} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Ativo</span>
                            </label>
                        </div>
                    </div>
                </form>
            `;
            showModal('edit-modal');
        } else {
            alert('Erro ao carregar dados do tempo slot');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao carregar dados do tempo slot');
    });
}

// Função para submeter o formulário de edição
function submitEditForm() {
    const form = document.getElementById('edit-tempo-slot-form');
    if (form) {
        form.submit();
    }
}

// Funções auxiliares para controle de modais
function showModal(modalName) {
    window.dispatchEvent(new CustomEvent('open-modal', { detail: modalName }));
}

function closeModal(modalName) {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: modalName }));
}
</script>
@endsection