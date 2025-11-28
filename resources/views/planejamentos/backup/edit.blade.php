@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Planejamentos', 'url' => route('planejamentos.index')],
    ['title' => $planejamento->titulo ?: 'Planejamento #' . $planejamento->id, 'url' => route('planejamentos.show', $planejamento)],
    ['title' => 'Editar', 'url' => '#']
]" />

    <!-- Header responsivo -->
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Editar Planejamento de Aula</h1>
            <p class="mt-1 text-sm text-gray-600">Modifique as informações do planejamento conforme necessário</p>
        </div>
        <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
            <x-button href="{{ route('planejamentos.show', $planejamento) }}" color="secondary" class="w-full sm:justify-center">
                <i class="fas fa-arrow-left mr-1"></i> 
                <span class="hidden md:inline">Voltar para Visualização</span>
                <span class="md:hidden">Voltar</span>
            </x-button>
        </div>
    </div>

    <!-- Formulário Multi-etapas -->
    <form id="planejamentoForm" action="{{ route('planejamentos.update', $planejamento) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Progress Bar -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Progresso do Planejamento</h3>
                <span id="stepCounter" class="text-sm text-gray-600">Etapa 1 de 7</span>
            </div>
            
            <!-- Progress bar responsivo -->
            <div class="w-full bg-gray-200 rounded-full h-2 md:h-3">
                <div id="progressBar" class="bg-blue-600 h-2 md:h-3 rounded-full transition-all duration-300" style="width: 14.28%"></div>
            </div>
            
            <!-- Steps indicators (hidden on mobile) -->
            <div class="hidden md:flex justify-between mt-4 text-xs text-gray-500">
                <span class="step-label active">Modalidade</span>
                <span class="step-label">Unidade</span>
                <span class="step-label">Turno</span>
                <span class="step-label">Sala</span>
                <span class="step-label">Professor</span>
                <span class="step-label">Turma</span>
                <span class="step-label">Período</span>
            </div>
        </div>

        <!-- Etapa 1: Modalidade -->
        <div id="step1" class="step-content">
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center text-sm font-medium mr-3">1</span>
                        Etapa da Educação Básica / Modalidade
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">Selecione a modalidade de ensino para este planejamento</p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="modalidade" class="block text-sm font-medium text-gray-700 mb-2">
                            Modalidade de Ensino <span class="text-red-500">*</span>
                        </label>
                        <select id="modalidade" name="modalidade" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione a modalidade...</option>
                                @foreach(\App\Models\Planejamento::getModalidadesOptions() as $key => $value)
                                    <option value="{{ $key }}" {{ old('modalidade', $planejamento->modalidade) == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                        </select>
                        @error('modalidade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Etapa 2: Unidade Escolar -->
        <div id="step2" class="step-content hidden">
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center text-sm font-medium mr-3">2</span>
                        Unidade Escolar
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">Esta informação é baseada no seu perfil e não pode ser alterada</p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="unidade_escolar" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome da Unidade Escolar
                        </label>
                        <input type="text" id="unidade_escolar" name="unidade_escolar" 
                               value="{{ old('unidade_escolar', $planejamento->unidade_escolar) }}" readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-600">
                        <p class="mt-1 text-xs text-gray-500">Esta informação é definida automaticamente com base no seu perfil</p>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Etapa 3: Turno -->
        <div id="step3" class="step-content hidden">
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center text-sm font-medium mr-3">3</span>
                        Turno
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">Selecione o turno em que as aulas serão ministradas</p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="turno" class="block text-sm font-medium text-gray-700 mb-2">
                            Turno <span class="text-red-500">*</span>
                        </label>
                        <select id="turno" name="turno" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione o turno...</option>
                            <option value="matutino" {{ old('turno', $planejamento->turno) == 'matutino' ? 'selected' : '' }}>Matutino</option>
                            <option value="vespertino" {{ old('turno', $planejamento->turno) == 'vespertino' ? 'selected' : '' }}>Vespertino</option>
                            <option value="noturno" {{ old('turno', $planejamento->turno) == 'noturno' ? 'selected' : '' }}>Noturno</option>
                            <option value="integral" {{ old('turno', $planejamento->turno) == 'integral' ? 'selected' : '' }}>Integral</option>
                        </select>
                        @error('turno')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Etapa 4: Sala -->
        <div id="step4" class="step-content hidden">
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center text-sm font-medium mr-3">4</span>
                        Sala
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">Escolha a sala onde as aulas serão ministradas</p>
                </div>
                
                <div class="space-y-4">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Informação sobre Salas
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>A seleção de sala será configurada posteriormente no sistema. Por enquanto, continue com as próximas etapas do planejamento.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Etapa 5: Tipo de Professor -->
        <div id="step5" class="step-content hidden">
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center text-sm font-medium mr-3">5</span>
                        Tipo de Professor
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">Defina o tipo de aula que será ministrada</p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="disciplina_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Disciplina <span class="text-red-500">*</span>
                        </label>
                        <select id="disciplina_id" name="disciplina_id" required 
                                data-selected="{{ $planejamento->disciplina_id ?? '' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione a disciplina...</option>
                            <!-- As opções serão carregadas via JavaScript baseado na modalidade -->
                        </select>
                        @error('disciplina_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Campo tipo_professor mantido temporariamente para compatibilidade -->
                    <input type="hidden" id="tipo_professor" name="tipo_professor" value="{{ $planejamento->tipo_professor }}">
                </div>
            </x-card>
        </div>

        <!-- Etapa 6: Turma -->
        <div id="step6" class="step-content hidden">
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center text-sm font-medium mr-3">6</span>
                        Turma
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">Selecione a turma para este planejamento</p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="turma" class="block text-sm font-medium text-gray-700 mb-2">
                            Turma <span class="text-red-500">*</span>
                        </label>
                        <select id="turma_id" name="turma_id" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione a turma...</option>
                            @foreach($turmas as $turma)
                                <option value="{{ $turma->id }}" {{ old('turma_id', $planejamento->turma_id) == $turma->id ? 'selected' : '' }}>
                                    {{ $turma->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('turma_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Etapa 7: Período -->
        <div id="step7" class="step-content hidden">
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <span class="bg-blue-100 text-blue-800 rounded-full w-8 h-8 flex items-center justify-center text-sm font-medium mr-3">7</span>
                        Período do Planejamento
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">Defina o período e duração do planejamento</p>
                </div>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="numero_dias" class="block text-sm font-medium text-gray-700 mb-2">
                                Número de Dias <span class="text-red-500">*</span>
                            </label>
                            <select id="numero_dias" name="numero_dias" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione...</option>
                                @for($i = 1; $i <= 20; $i++)
                                    <option value="{{ $i }}" {{ old('numero_dias', $planejamento->numero_dias) == $i ? 'selected' : '' }}>
                                        {{ $i }} {{ $i == 1 ? 'dia' : 'dias' }}
                                    </option>
                                @endfor
                            </select>
                            @error('numero_dias')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                                Data de Início <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="data_inicio" name="data_inicio" required
                                   value="{{ old('data_inicio', $planejamento->data_inicio ? $planejamento->data_inicio->format('Y-m-d') : '') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('data_inicio')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div id="data_fim_display" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data de Término (Calculada Automaticamente)</label>
                        <div id="data_fim_text" class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-600"></div>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Seções Opcionais -->
        <div id="optional-sections" class="hidden space-y-6">
            <!-- Título e Objetivo Geral -->
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-edit text-blue-600 mr-2"></i>
                        Título e Objetivo Geral
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">Informações opcionais para complementar o planejamento</p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                            Título do Planejamento
                        </label>
                        <input type="text" id="titulo" name="titulo" 
                               value="{{ old('titulo', $planejamento->titulo) }}"
                               placeholder="Ex: Planejamento de Matemática - 1º Bimestre"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        @error('titulo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="objetivo_geral" class="block text-sm font-medium text-gray-700 mb-2">
                            Objetivo Geral
                        </label>
                        <textarea id="objetivo_geral" name="objetivo_geral" rows="3"
                                  placeholder="Descreva o objetivo geral deste planejamento..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('objetivo_geral', $planejamento->objetivo_geral) }}</textarea>
                        @error('objetivo_geral')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Botões de Navegação -->
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0 bg-white p-4 md:p-6 rounded-lg shadow-sm border border-gray-200">
            <button type="button" id="prevBtn" class="hidden w-full md:w-auto px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <i class="fas fa-arrow-left mr-2"></i>Anterior
            </button>
            
            <div class="flex space-x-4 w-full md:w-auto">
                <button type="button" id="nextBtn" class="flex-1 md:flex-none px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Próximo<i class="fas fa-arrow-right ml-2"></i>
                </button>
                
                <button type="submit" id="submitBtn" class="hidden flex-1 md:flex-none px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-save mr-2"></i>Atualizar Planejamento
                </button>
            </div>
        </div>
    </form>

@endsection

<script src="{{ asset('js/planejamento-steps.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const planejamentoSteps = new PlanejamentoSteps(true); // true para modo de edição
        
        // Dados existentes do planejamento para pré-preenchimento
        const planejamentoData = {
            modalidade: '{{ $planejamento->modalidade }}',
            turno: '{{ $planejamento->turno }}',
            tipo_professor: '{{ $planejamento->tipo_professor }}',
            turma_id: '{{ $planejamento->turma_id }}',
            numero_dias: '{{ $planejamento->numero_dias }}',
            data_inicio: '{{ $planejamento->data_inicio }}',
            data_fim: '{{ $planejamento->data_fim }}',
            titulo: '{{ $planejamento->titulo }}',
            objetivo_geral: '{{ $planejamento->objetivo_geral }}'
        };
        
        planejamentoSteps.loadExistingData(planejamentoData);
    });
</script>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 7;
    
    // Elementos
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('progressBar');
    const stepCounter = document.getElementById('stepCounter');
    const optionalSections = document.getElementById('optional-sections');
    
    // Campos do formulário
    const modalidadeSelect = document.getElementById('modalidade');
    const turnoSelect = document.getElementById('turno');
    // const salaSelect = document.getElementById('sala_id'); // Removido - sala não implementada
    const tipoProfessorSelect = document.getElementById('tipo_professor');
    const numeroDiasSelect = document.getElementById('numero_dias');
    const dataInicioInput = document.getElementById('data_inicio');
    const dataFimDisplay = document.getElementById('data_fim_display');
    const dataFimText = document.getElementById('data_fim_text');
    
    // Valores iniciais para edição
    const initialValues = {
        modalidade: '{{ $planejamento->modalidade }}',
        turno: '{{ $planejamento->turno }}',
        tipo_professor: '{{ $planejamento->tipo_professor }}'
    };
    
    // Inicializar formulário
    initializeForm();
    
    function initializeForm() {
        // Carregar tipos de professor baseado nos valores iniciais
        // Salas serão implementadas futuramente
        
        if (initialValues.modalidade) {
            loadTiposProfessor(initialValues.modalidade, initialValues.tipo_professor);
        }
        
        // Calcular data fim se necessário
        if (numeroDiasSelect.value && dataInicioInput.value) {
            calculateDataFim();
        }
    }
    
    function showStep(step) {
        // Esconder todas as etapas
        for (let i = 1; i <= totalSteps; i++) {
            document.getElementById(`step${i}`).classList.add('hidden');
        }
        
        // Mostrar etapa atual
        document.getElementById(`step${step}`).classList.remove('hidden');
        
        // Atualizar progress bar
        const progress = (step / totalSteps) * 100;
        progressBar.style.width = progress + '%';
        stepCounter.textContent = `Etapa ${step} de ${totalSteps}`;
        
        // Atualizar step labels
        const stepLabels = document.querySelectorAll('.step-label');
        stepLabels.forEach((label, index) => {
            if (index < step - 1) {
                label.classList.add('completed');
                label.classList.remove('active');
            } else if (index === step - 1) {
                label.classList.add('active');
                label.classList.remove('completed');
            } else {
                label.classList.remove('active', 'completed');
            }
        });
        
        // Controlar botões
        prevBtn.classList.toggle('hidden', step === 1);
        
        if (step === totalSteps) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
            optionalSections.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
            optionalSections.classList.add('hidden');
        }
    }
    
    function validateStep(step) {
        switch(step) {
            case 1:
                return modalidadeSelect.value !== '';
            case 2:
                return true; // Unidade escolar é readonly
            case 3:
                return turnoSelect.value !== '';
            case 4:
                return true; // Sala não implementada - sempre válida
            case 5:
                return tipoProfessorSelect.value !== '';
            case 6:
                return document.getElementById('turma').value !== '';
            case 7:
                return numeroDiasSelect.value !== '' && dataInicioInput.value !== '';
            default:
                return true;
        }
    }
    
    function showValidationError(step) {
        let message = '';
        switch(step) {
            case 1:
                message = 'Por favor, selecione a modalidade de ensino.';
                break;
            case 3:
                message = 'Por favor, selecione o turno.';
                break;
            case 4:
                message = 'Etapa de sala não implementada.';
                break;
            case 5:
                message = 'Por favor, selecione o tipo de professor.';
                break;
            case 6:
                message = 'Por favor, selecione a turma.';
                break;
            case 7:
                message = 'Por favor, preencha o número de dias e a data de início.';
                break;
        }
        
        window.alertSystem.error(message);
    }
    
    // Event listeners para navegação
    nextBtn.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            if (currentStep < totalSteps) {
                currentStep++;
                showStep(currentStep);
            }
        } else {
            showValidationError(currentStep);
        }
    });
    
    prevBtn.addEventListener('click', function() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });
    
    // Event listeners para carregamento dinâmico
    modalidadeSelect.addEventListener('change', function() {
        const modalidade = this.value;
        
        // Limpar tipos de professor
        tipoProfessorSelect.innerHTML = '<option value="">Selecione o tipo...</option>';
        
        if (modalidade) {
            loadTiposProfessor(modalidade);
        }
    });
    
    turnoSelect.addEventListener('change', function() {
        const turno = this.value;
        
        // Salas serão implementadas futuramente
    });
    
    // Event listeners para cálculo de data fim
    numeroDiasSelect.addEventListener('change', calculateDataFim);
    dataInicioInput.addEventListener('change', calculateDataFim);
    
    // function loadSalas - removida, salas serão implementadas futuramente
    
    function loadTiposProfessor(modalidade, selectedTipo = null) {
        fetch(`/planejamentos/tipos-professor?modalidade=${modalidade}`)
            .then(response => response.json())
            .then(data => {
                tipoProfessorSelect.innerHTML = '<option value="">Selecione o tipo...</option>';
                data.forEach(tipo => {
                    const option = document.createElement('option');
                    option.value = tipo.value;
                    option.textContent = tipo.label;
                    if (selectedTipo && tipo.value === selectedTipo) {
                        option.selected = true;
                    }
                    tipoProfessorSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Erro ao carregar tipos de professor:', error);
            });
    }
    
    function calculateDataFim() {
        const numeroDias = parseInt(numeroDiasSelect.value);
        const dataInicio = new Date(dataInicioInput.value);
        
        if (numeroDias && dataInicioInput.value) {
            const dataFim = new Date(dataInicio);
            dataFim.setDate(dataFim.getDate() + numeroDias - 1);
            
            const dataFimFormatted = dataFim.toLocaleDateString('pt-BR');
            dataFimText.textContent = dataFimFormatted;
            dataFimDisplay.classList.remove('hidden');
        } else {
            dataFimDisplay.classList.add('hidden');
        }
    }
    
    // Inicializar primeira etapa
    showStep(1);
});
</script>

<style>
.step-label.active {
    color: #2563eb;
    font-weight: 600;
}

.step-label.completed {
    color: #059669;
    font-weight: 500;
}
</style>
@endpush