@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Transferências', 'url' => route('transferencias.index')],
    ['title' => 'Nova Solicitação', 'url' => '#']
]" />

    <!-- Header responsivo com melhor layout mobile -->
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Nova Solicitação de Transferência</h1>
            <p class="mt-1 text-sm text-gray-600">Criar uma nova solicitação de transferência de aluno</p>
        </div>
        <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
            <x-button href="{{ route('transferencias.index') }}" color="secondary" class="w-full sm:justify-center">
                <i class="fas fa-arrow-left mr-1"></i> 
                <span class="hidden md:inline">Voltar para Transferências</span>
                <span class="md:hidden">Voltar</span>
            </x-button>
        </div>
    </div>

    <x-card>
        <form action="{{ route('transferencias.store') }}" method="POST" id="formTransferencia">
            @csrf
            
            <!-- Layout responsivo com melhor espaçamento mobile -->
            <div class="space-y-6">
                <!-- Seção de Seleção do Aluno -->
                <div class="space-y-4">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-base font-medium text-gray-900 flex items-center">
                            <i class="fas fa-user-graduate text-blue-600 mr-2"></i>
                            Seleção do Aluno
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Escolha o aluno que será transferido</p>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Seleção do Aluno -->
                        <div>
                            <label for="aluno_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Aluno <span class="text-red-500">*</span>
                            </label>
                            <select name="aluno_id" id="aluno_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                <option value="">Selecione um aluno...</option>
                                @foreach($alunos as $aluno)
                                    <option value="{{ $aluno->id }}" data-turma="{{ $aluno->turma_id }}" data-turma-nome="{{ $aluno->turma ? $aluno->turma->nome : 'Sem turma' }}">
                                        {{ $aluno->nome_completo }} - {{ $aluno->cpf }}
                                        @if($aluno->turma)
                                            (Turma: {{ $aluno->turma->codigo }})
                                        @else
                                            (Sem turma)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('aluno_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Turma Atual -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Turma Atual</label>
                            <div id="turmaAtual" class="block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 text-gray-500 text-sm min-h-[38px] flex items-center">
                                Selecione um aluno primeiro
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção de Turma Destino -->
                <div class="space-y-4">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-base font-medium text-gray-900 flex items-center">
                            <i class="fas fa-door-open text-green-600 mr-2"></i>
                            Turma de Destino
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Escolha a nova turma para o aluno</p>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Seleção da Turma Destino -->
                        <div>
                            <label for="turma_destino_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Turma Destino <span class="text-red-500">*</span>
                            </label>
                            <select name="turma_destino_id" id="turma_destino_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required>
                                <option value="">Selecione uma turma...</option>
                                @foreach($turmas as $turma)
                                    <option value="{{ $turma->id }}" data-capacidade="{{ $turma->capacidade }}" data-ocupacao="{{ $turma->alunos_count }}">
                                        {{ $turma->codigo }} - {{ $turma->nome }}
                                        ({{ $turma->alunos_count }}/{{ $turma->capacidade }})
                                    </option>
                                @endforeach
                            </select>
                            @error('turma_destino_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Informações da Turma Destino -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Informações da Turma</label>
                            <div id="infoTurmaDestino" class="block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 text-gray-500 text-sm min-h-[38px] flex items-center">
                                Selecione uma turma primeiro
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Seção de Motivo da Transferência -->
                <div class="space-y-4">
                    <div class="border-b border-gray-200 pb-4">
                        <h3 class="text-base font-medium text-gray-900 flex items-center">
                            <i class="fas fa-edit text-orange-600 mr-2"></i>
                            Motivo da Transferência
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Descreva o motivo para esta transferência</p>
                    </div>
                    
                    <div>
                        <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo <span class="text-red-500">*</span>
                        </label>
                        <textarea name="motivo" id="motivo" rows="4" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm resize-none" placeholder="Descreva o motivo da transferência..." required>{{ old('motivo') }}</textarea>
                        @error('motivo')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Seção de Informações e Avisos -->
                <div class="space-y-4">
                    <!-- Informações da turma atual -->
                    <div id="infoTurmaAtual" class="p-4 bg-blue-50 border border-blue-200 rounded-lg hidden">
                        <h4 class="text-sm font-medium text-blue-800 mb-2 flex items-center">
                            <i class="fas fa-info-circle mr-2"></i>
                            Informações da Turma Atual
                        </h4>
                        <div id="detalheTurmaAtual" class="text-sm text-blue-700"></div>
                    </div>

                    <!-- Aviso de capacidade -->
                    <div id="avisoCapacidade" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Atenção!</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p id="mensagemCapacidade"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="pt-6 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <a href="{{ route('transferencias.index') }}" class="inline-flex justify-center items-center py-2.5 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <span class="hidden sm:inline">Cancelar</span>
                            <span class="sm:hidden">Voltar</span>
                        </a>
                        <button type="submit" class="inline-flex justify-center items-center py-2.5 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                            <i class="fas fa-exchange-alt mr-2"></i>
                            <span class="hidden sm:inline">Realizar Transferência</span>
                            <span class="sm:hidden">Transferir</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </x-card>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const alunoSelect = document.getElementById('aluno_id');
    const turmaDestinoSelect = document.getElementById('turma_destino_id');
    const infoTurmaAtual = document.getElementById('infoTurmaAtual');
    const textoSalaAtual = document.getElementById('textoSalaAtual');
    const avisoCapacidade = document.getElementById('avisoCapacidade');
    const textoCapacidade = document.getElementById('textoCapacidade');

    const turmaAtual = document.getElementById('turmaAtual');
    const detalheTurmaAtual = document.getElementById('detalheTurmaAtual');
    const infoTurmaDestino = document.getElementById('infoTurmaDestino');
    const mensagemCapacidade = document.getElementById('mensagemCapacidade');

    // Função para atualizar informações da turma atual
    function atualizarInfoTurmaAtual() {
        const alunoSelecionado = alunoSelect.options[alunoSelect.selectedIndex];
        if (alunoSelect.value && alunoSelecionado) {
            const turmaNome = alunoSelecionado.dataset.turmaNome;
            turmaAtual.textContent = turmaNome || 'Sem turma';
            detalheTurmaAtual.textContent = `O aluno está atualmente na turma: ${turmaNome || 'Sem turma'}`;
            infoTurmaAtual.style.display = 'block';
        } else {
            turmaAtual.textContent = 'Selecione um aluno primeiro';
            infoTurmaAtual.style.display = 'none';
        }
    }

    // Função para verificar capacidade da turma de destino
    function verificarCapacidade() {
        const turmaSelecionada = turmaDestinoSelect.options[turmaDestinoSelect.selectedIndex];
        if (turmaDestinoSelect.value && turmaSelecionada) {
            const capacidade = parseInt(turmaSelecionada.dataset.capacidade);
            const ocupacao = parseInt(turmaSelecionada.dataset.ocupacao);
            const turmaNome = turmaSelecionada.text;
            
            infoTurmaDestino.textContent = `${turmaNome} - Ocupação: ${ocupacao}/${capacidade}`;
            
            if (ocupacao >= capacidade) {
                mensagemCapacidade.textContent = 'A turma selecionada está com capacidade máxima. A transferência pode não ser possível.';
                avisoCapacidade.style.display = 'block';
            } else {
                avisoCapacidade.style.display = 'none';
            }
        } else {
            infoTurmaDestino.textContent = 'Selecione uma turma primeiro';
            avisoCapacidade.style.display = 'none';
        }
    }

    // Event listeners
    alunoSelect.addEventListener('change', atualizarInfoTurmaAtual);
    turmaDestinoSelect.addEventListener('change', verificarCapacidade);

    // Inicializar se já houver valores selecionados
    atualizarInfoTurmaAtual();
    verificarCapacidade();

    // Validação antes do envio
    document.querySelector('form').addEventListener('submit', function(e) {
        const alunoId = alunoSelect.value;
        const salaDestinoId = turmaDestinoSelect.value;
        
        if (!alunoId) {
            e.preventDefault();
            alert('Por favor, selecione um aluno.');
            alunoSelect.focus();
            return;
        }
        
        if (!salaDestinoId) {
            e.preventDefault();
            alert('Por favor, selecione uma turma de destino.');
            turmaDestinoSelect.focus();
            return;
        }
        
        // Verificar se a sala de destino tem capacidade
        const turmaSelecionada = turmaDestinoSelect.options[turmaDestinoSelect.selectedIndex];
        const capacidade = parseInt(turmaSelecionada.dataset.capacidade);
        const ocupacao = parseInt(turmaSelecionada.dataset.ocupacao);
        
        if (ocupacao >= capacidade) {
            e.preventDefault();
            alert('A turma selecionada já atingiu sua capacidade máxima!');
            return;
        }
    });
});
</script>
@endpush