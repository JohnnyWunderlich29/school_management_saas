@extends('layouts.app')

@section('title', 'Nova Conversa')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <x-card>
        <!-- Header -->
        <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
            <div>
                <h1 class="text-lg md:text-2xl font-semibold text-gray-900">
                    <i class="fas fa-plus mr-2 text-indigo-600"></i>
                    Nova Conversa
                </h1>
                <p class="mt-1 text-sm text-gray-600">Crie uma nova conversa para comunicação</p>
            </div>
            <div>
                <a href="{{ route('conversas.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>

        <!-- Formulário -->
        <div class="bg-white">
                    <form action="{{ route('conversas.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Título da Conversa <span class="text-red-500">*</span>
                                </label>
                                <x-input type="text" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('titulo') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                       id="titulo" 
                                       name="titulo" 
                                       value="{{ old('titulo') }}" 
                                       required 
                                       placeholder="Ex: Reunião de Pais - Turma A" />
                                @error('titulo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Conversa <span class="text-red-500">*</span>
                                </label>
                                <x-select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('tipo') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                        id="tipo" 
                                        name="tipo" 
                                        required>
                                    <option value="">Selecione o tipo...</option>
                                    <option value="individual" {{ old('tipo') === 'individual' ? 'selected' : '' }}>Individual</option>
                                    <option value="grupo" {{ old('tipo') === 'grupo' ? 'selected' : '' }}>Grupo</option>
                                    <option value="turma" {{ old('tipo') === 'turma' ? 'selected' : '' }}>Turma</option>
                                </x-select>
                                @error('tipo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div>
                            <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <x-textarea class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('descricao') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                      id="descricao" 
                                      name="descricao" 
                                      rows="3" 
                                      placeholder="Descreva o objetivo desta conversa...">{{ old('descricao') }}</x-textarea>
                            @error('descricao')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Campo de turma (aparece quando tipo = turma) -->
                        <div id="campo-turma" style="display: none;">
                            <label for="turma_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Turma <span class="text-red-500">*</span>
                            </label>
                            <select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('turma_id') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                    id="turma_id" 
                                    name="turma_id">
                                <option value="">Selecione a turma...</option>
                                @foreach($turmas as $turma)
                                    <option value="{{ $turma->id }}" {{ old('turma_id') == $turma->id ? 'selected' : '' }}>
                                        {{ $turma->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('turma_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Campo de participantes (aparece quando tipo = individual ou grupo) -->
                        <div id="campo-participantes" style="display: none;">
                            <label for="participantes" class="block text-sm font-medium text-gray-700 mb-2">
                                Participantes <span class="text-red-500">*</span>
                            </label>
                            
                            <!-- Busca de participantes -->
                            <div class="mb-3">
                                <input type="text" 
                                       id="busca-participantes" 
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                       placeholder="Buscar participantes por nome ou email...">
                            </div>
                            
                            <!-- Lista de participantes com checkboxes -->
                            <div class="border border-gray-300 rounded-md max-h-60 overflow-y-auto bg-white">
                                <div class="p-2">
                                    <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox" id="selecionar-todos" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm font-medium text-gray-700">Selecionar todos</span>
                                    </label>
                                </div>
                                <div class="border-t border-gray-200"></div>
                                <div id="lista-participantes">
                                    @foreach($usuarios as $usuario)
                                        <label class="participante-item flex items-center p-2 hover:bg-gray-50 cursor-pointer" 
                                               data-nome="{{ strtolower($usuario->name) }}" 
                                               data-email="{{ strtolower($usuario->email) }}">
                                            <input type="checkbox" 
                                                   name="participantes[]" 
                                                   value="{{ $usuario->id }}" 
                                                   class="participante-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                   {{ in_array($usuario->id, old('participantes', [])) ? 'checked' : '' }}>
                                            <div class="ml-3 flex-1">
                                                <div class="text-sm font-medium text-gray-900">{{ $usuario->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $usuario->email }}</div>
                                                @if($usuario->cargos->isNotEmpty())
                                                    <div class="text-xs text-indigo-600">{{ $usuario->cargos->first()->nome }}</div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Contador de selecionados -->
                            <div class="mt-2 text-sm text-gray-600">
                                <span id="contador-selecionados">0</span> participante(s) selecionado(s)
                            </div>
                            
                            @error('participantes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Mensagem inicial -->
                        <div>
                            <label for="mensagem_inicial" class="block text-sm font-medium text-gray-700 mb-2">
                                Mensagem Inicial <span class="text-red-500">*</span>
                            </label>
                            <x-textarea class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('mensagem_inicial') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror" 
                                      id="mensagem_inicial" 
                                      name="mensagem_inicial" 
                                      rows="4" 
                                      required
                                      placeholder="Digite a primeira mensagem da conversa...">{{ old('mensagem_inicial') }}</x-textarea>
                            @error('mensagem_inicial')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                       type="checkbox" 
                                       id="ativo" 
                                       name="ativo" 
                                       value="1" 
                                       {{ old('ativo', true) ? 'checked' : '' }}>
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="ativo" class="font-medium text-gray-700">
                                    Conversa ativa
                                </label>
                                <p class="text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Conversas inativas não permitem novas mensagens.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-2 sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('conversas.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-save mr-2"></i>
                                Criar Conversa
                            </button>
                        </div>
                    </form>
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('tipo');
    const campoTurma = document.getElementById('campo-turma');
    const campoParticipantes = document.getElementById('campo-participantes');
    const turmaSelect = document.getElementById('turma_id');
    const participantesCheckboxes = document.querySelectorAll('.participante-checkbox');
    

    
    function toggleCampos() {
        const tipo = tipoSelect.value;

        
        // Resetar campos
        campoTurma.style.display = 'none';
        campoParticipantes.style.display = 'none';
        turmaSelect.required = false;
        
        // Remover required dos checkboxes
        participantesCheckboxes.forEach(checkbox => {
            checkbox.required = false;
        });
        
        // Mostrar campos baseado no tipo
        switch(tipo) {
            case 'turma':

                campoTurma.style.display = 'block';
                turmaSelect.required = true;
                break;
            case 'individual':
            case 'grupo':

                campoParticipantes.style.display = 'block';
                // Não definimos required nos checkboxes individuais, 
                // a validação será feita no JavaScript
                break;
        }
    }
    
    // Executar na inicialização
    toggleCampos();
    
    // Executar quando o tipo mudar
    tipoSelect.addEventListener('change', toggleCampos);
    
    // Funcionalidade de busca de participantes
    const buscaInput = document.getElementById('busca-participantes');
    const participantesItems = document.querySelectorAll('.participante-item');
    
    if (buscaInput) {
        buscaInput.addEventListener('input', function() {
            const termo = this.value.toLowerCase();
            
            participantesItems.forEach(item => {
                const nome = item.dataset.nome;
                const email = item.dataset.email;
                
                if (nome.includes(termo) || email.includes(termo)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // Funcionalidade de selecionar todos
    const selecionarTodos = document.getElementById('selecionar-todos');
    const contadorSelecionados = document.getElementById('contador-selecionados');
    
    function atualizarContador() {
        const selecionados = document.querySelectorAll('.participante-checkbox:checked').length;
        if (contadorSelecionados) {
            contadorSelecionados.textContent = selecionados;
        }
    }
    
    if (selecionarTodos) {
        selecionarTodos.addEventListener('change', function() {
            const visibleCheckboxes = Array.from(participantesCheckboxes).filter(checkbox => {
                return checkbox.closest('.participante-item').style.display !== 'none';
            });
            
            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            
            atualizarContador();
        });
    }
    
    // Atualizar contador quando checkboxes individuais mudarem
    participantesCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', atualizarContador);
    });
    
    // Inicializar contador
    atualizarContador();
    
    // Validação do formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        const tipo = tipoSelect.value;
        const participantesSelecionados = document.querySelectorAll('.participante-checkbox:checked');
        const turmaId = document.getElementById('turma_id').value;
        
        if (tipo === 'individual' && participantesSelecionados.length !== 1) {
            e.preventDefault();
            alert('Para conversas individuais, você deve selecionar exatamente 1 participante.');
            return;
        }
        
        if (tipo === 'grupo' && participantesSelecionados.length < 2) {
            e.preventDefault();
            alert('Para conversas em grupo, você deve selecionar pelo menos 2 participantes.');
            return;
        }
        
        if (tipo === 'turma' && !turmaId) {
            e.preventDefault();
            alert('Para conversas de turma, selecione uma turma.');
            return;
        }
    });
});
</script>
@endpush

@push('styles')
<style>
/* Estilos customizados para select múltiplo */
select[multiple] option:checked {
    background-color: #4f46e5;
    color: white;
}

select[multiple] option {
    padding: 8px 12px;
}
</style>
@endpush