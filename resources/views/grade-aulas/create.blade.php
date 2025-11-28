@extends('layouts.app')

@section('title', 'Criar Nova Aula')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col self-end justify-between md:flex-row">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Nova Aula</h1>
                    <p class="mt-2 text-gray-600">Configure uma nova aula na grade horária</p>
                </div>
                <div>
                <a href="{{ route('grade-aulas.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
            </div>
        </div>

        <form id="gradeAulaForm" action="{{ route('grade-aulas.store') }}" method="POST" class="space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Formulário Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Informações Básicas -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            Informações Básicas
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Turma -->
                            <div>
                                <label for="turma_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Turma <span class="text-red-500">*</span>
                                </label>
                                <select name="turma_id" id="turma_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Selecione uma turma</option>
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

                            <!-- Disciplina -->
                            <div>
                                <label for="disciplina_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Disciplina <span class="text-red-500">*</span>
                                </label>
                                <select name="disciplina_id" id="disciplina_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Selecione uma disciplina</option>
                                    @foreach($disciplinas as $disciplina)
                                        <option value="{{ $disciplina->id }}" {{ old('disciplina_id') == $disciplina->id ? 'selected' : '' }}>
                                            {{ $disciplina->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('disciplina_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Professor -->
                            <div>
                                <label for="funcionario_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Professor <span class="text-red-500">*</span>
                                </label>
                                <select name="funcionario_id" id="funcionario_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Selecione um professor</option>
                                    @foreach($professores as $professor)
                                        <option value="{{ $professor->id }}" {{ old('funcionario_id') == $professor->id ? 'selected' : '' }}>
                                            {{ $professor->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('funcionario_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Sala -->
                            <div>
                                <label for="sala_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sala <span class="text-red-500">*</span>
                                </label>
                                <select name="sala_id" id="sala_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Selecione uma sala</option>
                                    @foreach($salas as $sala)
                                        <option value="{{ $sala->id }}" {{ old('sala_id') == $sala->id ? 'selected' : '' }}>
                                            {{ $sala->nome }} ({{ $sala->capacidade }} lugares)
                                        </option>
                                    @endforeach
                                </select>
                                @error('sala_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Horário e Data -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-clock text-green-500 mr-2"></i>
                            Horário e Período
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Dia da Semana -->
                            <div class="flex flex-col col-span-2 md:flex-row gap-6">
                            <div>
                                <label for="dia_semana" class="block text-sm font-medium text-gray-700 mb-2">
                                    Dia da Semana <span class="text-red-500">*</span>
                                </label>
                                <select name="dia_semana" id="dia_semana" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Selecione o dia</option>
                                    <option value="segunda" {{ old('dia_semana') == 'segunda' ? 'selected' : '' }}>Segunda-feira</option>
                                    <option value="terca" {{ old('dia_semana') == 'terca' ? 'selected' : '' }}>Terça-feira</option>
                                    <option value="quarta" {{ old('dia_semana') == 'quarta' ? 'selected' : '' }}>Quarta-feira</option>
                                    <option value="quinta" {{ old('dia_semana') == 'quinta' ? 'selected' : '' }}>Quinta-feira</option>
                                    <option value="sexta" {{ old('dia_semana') == 'sexta' ? 'selected' : '' }}>Sexta-feira</option>
                                    <option value="sabado" {{ old('dia_semana') == 'sabado' ? 'selected' : '' }}>Sábado</option>
                                </select>
                                @error('dia_semana')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <!-- Aplicar a múltiplos dias (opcional) -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Aplicar a múltiplos dias (opcional)
                                    </label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @php($diasSelecionados = old('dias_semana', []))
                                        <label class="inline-flex items-center space-x-2">
                                            <input type="checkbox" name="dias_semana[]" value="segunda" class="rounded" {{ in_array('segunda', $diasSelecionados) ? 'checked' : '' }}>
                                            <span>Segunda-feira</span>
                                        </label>
                                        <label class="inline-flex items-center space-x-2">
                                            <input type="checkbox" name="dias_semana[]" value="terca" class="rounded" {{ in_array('terca', $diasSelecionados) ? 'checked' : '' }}>
                                            <span>Terça-feira</span>
                                        </label>
                                        <label class="inline-flex items-center space-x-2">
                                            <input type="checkbox" name="dias_semana[]" value="quarta" class="rounded" {{ in_array('quarta', $diasSelecionados) ? 'checked' : '' }}>
                                            <span>Quarta-feira</span>
                                        </label>
                                        <label class="inline-flex items-center space-x-2">
                                            <input type="checkbox" name="dias_semana[]" value="quinta" class="rounded" {{ in_array('quinta', $diasSelecionados) ? 'checked' : '' }}>
                                            <span>Quinta-feira</span>
                                        </label>
                                        <label class="inline-flex items-center space-x-2">
                                            <input type="checkbox" name="dias_semana[]" value="sexta" class="rounded" {{ in_array('sexta', $diasSelecionados) ? 'checked' : '' }}>
                                            <span>Sexta-feira</span>
                                        </label>
                                        <label class="inline-flex items-center space-x-2">
                                            <input type="checkbox" name="dias_semana[]" value="sabado" class="rounded" {{ in_array('sabado', $diasSelecionados) ? 'checked' : '' }}>
                                            <span>Sábado</span>
                                        </label>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Se você marcar múltiplos dias, criaremos uma aula anual para cada dia selecionado, usando o mesmo professor, sala, horário e disciplina.</p>
                                </div>
                            </div>

                            <!-- Horário -->
                            <div>
                                <label for="tempo_slot_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Horário <span class="text-red-500">*</span>
                                </label>
                                <select name="tempo_slot_id" id="tempo_slot_id" required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    <option value="">Selecione o horário</option>
                                    @foreach($tempoSlots as $slot)
                                        <option value="{{ $slot->id }}" {{ old('tempo_slot_id') == $slot->id ? 'selected' : '' }}>
                                            {{ $slot->hora_inicio }} - {{ $slot->hora_fim }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tempo_slot_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            </div>

                            <!-- Tipo de Aula -->
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-4">
                                    Tipo de Aula
                                </label>
                                
                                <!-- Toggle Buttons -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <!-- Aula Anual -->
                                    <div class="relative">
                                        <input type="radio" name="tipo_aula" id="aula_anual" value="anual" 
                                               class="sr-only peer" {{ old('tipo_aula', 'anual') == 'anual' ? 'checked' : '' }}>
                                        <label for="aula_anual" 
                                               class="flex flex-col items-center justify-center p-6 bg-white border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-blue-50 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all duration-200">
                                            <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-3 peer-checked:bg-blue-500">
                                                <i class="fas fa-calendar-alt text-blue-600 peer-checked:text-white"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Aula Anual</h3>
                                            <p class="text-sm text-gray-600 text-center">
                                                Aula regular que ocorre durante todo o ano letivo (segunda à sexta-feira)
                                            </p>
                                        </label>
                                    </div>

                                    <!-- Aula com Período Específico -->
                                    <div class="relative">
                                        <input type="radio" name="tipo_aula" id="aula_periodo" value="periodo" 
                                               class="sr-only peer" {{ old('tipo_aula') == 'periodo' ? 'checked' : '' }}>
                                        <label for="aula_periodo" 
                                               class="flex flex-col items-center justify-center p-6 bg-white border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-green-50 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all duration-200">
                                            <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-3 peer-checked:bg-green-500">
                                                <i class="fas fa-clock text-green-600 peer-checked:text-white"></i>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Período Específico</h3>
                                            <p class="text-sm text-gray-600 text-center">
                                                Cursos intensivos, substituições ou aulas temporárias
                                            </p>
                                        </label>
                                    </div>
                                </div>

                                <!-- Campos de Data (aparecem apenas para período específico) -->
                                <div id="campos-periodo" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-green-50 rounded-lg border border-green-200">
                                    <!-- Tipo de Período -->
                                    <div>
                                        <label for="tipo_periodo" class="block text-sm font-medium text-gray-700 mb-2">
                                            Tipo de Período
                                        </label>
                                        <select name="tipo_periodo" id="tipo_periodo"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                                            <option value="">Selecione o tipo</option>
                                            <option value="curso_intensivo" {{ old('tipo_periodo') == 'curso_intensivo' ? 'selected' : '' }}>Curso Intensivo</option>
                                            <option value="substituicao" {{ old('tipo_periodo') == 'substituicao' ? 'selected' : '' }}>Substituição</option>
                                            <option value="reforco" {{ old('tipo_periodo') == 'reforco' ? 'selected' : '' }}>Reforço</option>
                                            <option value="recuperacao" {{ old('tipo_periodo') == 'recuperacao' ? 'selected' : '' }}>Recuperação</option>
                                            <option value="outro" {{ old('tipo_periodo') == 'outro' ? 'selected' : '' }}>Outro</option>
                                        </select>
                                        @error('tipo_periodo')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Data Início -->
                                    <div>
                                        <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                                            Data de Início
                                        </label>
                                        <input type="date" name="data_inicio" id="data_inicio" value="{{ old('data_inicio') }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                                        @error('data_inicio')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Data Fim -->
                                    <div>
                                        <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-2">
                                            Data de Fim
                                        </label>
                                        <input type="date" name="data_fim" id="data_fim" value="{{ old('data_fim') }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                                        @error('data_fim')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações Adicionais -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-cog text-purple-500 mr-2"></i>
                            Configurações Adicionais
                        </h2>
                        
                        <div class="space-y-6">
                            <!-- Permite Substituição -->
                            <div class="flex items-start space-x-3">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" name="permite_substituicao" id="permite_substituicao" value="1"
                                           {{ old('permite_substituicao') ? 'checked' : '' }}
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                                </div>
                                <div class="text-sm">
                                    <label for="permite_substituicao" class="font-medium text-gray-700">
                                        Permitir substituição de professor
                                    </label>
                                    <p class="text-gray-500">
                                        Marque esta opção se outro professor pode assumir esta aula quando necessário
                                    </p>
                                </div>
                            </div>

                            <!-- Observações -->
                            <div>
                                <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Observações
                                </label>
                                <textarea name="observacoes" id="observacoes" rows="3" 
                                          placeholder="Adicione observações sobre esta aula..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none">{{ old('observacoes') }}</textarea>
                                @error('observacoes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex items-center justify-end space-x-4 pt-6">
                        <a href="{{ route('grade-aulas.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" id="submitBtn"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="submitText">Criar Aula</span>
                            <i id="submitIcon" class="fas fa-plus ml-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Painel Lateral - Sugestões e Status -->
                <div class="space-y-6">
                    <!-- Status de Verificação -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-shield-alt text-blue-500 mr-2"></i>
                            Verificação de Conflitos
                        </h3>
                        
                        <div id="conflict-status" class="space-y-3">
                            <div class="flex items-center text-gray-500">
                                <i class="fas fa-info-circle mr-2"></i>
                                <span class="text-sm">Preencha os campos para verificar conflitos</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sugestões de Horários -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                            Sugestões de Horários
                        </h3>
                        
                        <div id="horarios-sugeridos" class="space-y-2">
                            <div class="text-gray-500 text-sm">
                                <i class="fas fa-clock mr-2"></i>
                                Selecione professor e dia para ver sugestões
                            </div>
                        </div>
                    </div>

                    <!-- Sugestões de Salas -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-door-open text-green-500 mr-2"></i>
                            Sugestões de Salas
                        </h3>
                        
                        <div id="salas-sugeridas" class="space-y-2">
                            <div class="text-gray-500 text-sm">
                                <i class="fas fa-building mr-2"></i>
                                Selecione horário e dia para ver salas disponíveis
                            </div>
                        </div>
                    </div>

                    <!-- Professores Alternativos -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" id="professores-alternativos-container" style="display: none;">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-users text-purple-500 mr-2"></i>
                            Professores Alternativos
                        </h3>
                        
                        <div id="professores-alternativos" class="space-y-2">
                            <!-- Será preenchido dinamicamente -->
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
        <span class="text-gray-700">Verificando disponibilidade...</span>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos do formulário
    const form = document.getElementById('gradeAulaForm');
    const turmaSelect = document.getElementById('turma_id');
    const disciplinaSelect = document.getElementById('disciplina_id');
    const funcionarioSelect = document.getElementById('funcionario_id');
    const salaSelect = document.getElementById('sala_id');
    const diaSelect = document.getElementById('dia_semana');
    const tempoSlotSelect = document.getElementById('tempo_slot_id');
    const dataInicioInput = document.getElementById('data_inicio');
    const dataFimInput = document.getElementById('data_fim');
    const permiteSubstituicaoCheckbox = document.getElementById('permite_substituicao');
    
    // Elementos de exibição
    const conflictStatus = document.getElementById('conflict-status');
    const horariosSugeridos = document.getElementById('horarios-sugeridos');
    const salasSugeridas = document.getElementById('salas-sugeridas');
    const professoresAlternativos = document.getElementById('professores-alternativos');
    const professoresAlternativosContainer = document.getElementById('professores-alternativos-container');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitIcon = document.getElementById('submitIcon');
    
    // Debounce para otimizar requisições
    let debounceTimer;
    
    // Função para mostrar loading
    function showLoading() {
        loadingOverlay.classList.remove('hidden');
    }
    
    // Função para esconder loading
    function hideLoading() {
        loadingOverlay.classList.add('hidden');
    }
    
    // Função para verificar conflitos
    async function verificarConflitos() {
        const formData = new FormData(form);
        
        // Verificar se os campos obrigatórios estão preenchidos
        if (!formData.get('funcionario_id') || !formData.get('sala_id') || 
            !formData.get('dia_semana') || !formData.get('tempo_slot_id')) {
            conflictStatus.innerHTML = `
                <div class="flex items-center text-gray-500">
                    <i class="fas fa-info-circle mr-2"></i>
                    <span class="text-sm">Preencha os campos para verificar conflitos</span>
                </div>
            `;
            return;
        }
        
        try {
            showLoading();
            
            const response = await fetch('{{ route("grade-aulas.verificar.conflitos") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (data.conflitos && data.conflitos.length > 0) {
                    // Há conflitos
                    conflictStatus.innerHTML = `
                        <div class="space-y-2">
                            <div class="flex items-center text-red-600">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <span class="text-sm font-medium">Conflitos detectados</span>
                            </div>
                            ${data.conflitos.map(conflito => `
                                <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                    <p class="text-sm text-red-700">${conflito.mensagem || conflito}</p>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    // Sem conflitos
                    conflictStatus.innerHTML = `
                        <div class="flex items-center text-green-600">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span class="text-sm font-medium">Nenhum conflito detectado</span>
                        </div>
                    `;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        } catch (error) {
            console.error('Erro ao verificar conflitos:', error);
            conflictStatus.innerHTML = `
                <div class="flex items-center text-red-600">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span class="text-sm">Erro ao verificar conflitos</span>
                </div>
            `;
        } finally {
            hideLoading();
        }
    }
    
    // Função para obter sugestões de horários
    async function obterSugestoesHorarios() {
        if (!funcionarioSelect.value || !salaSelect.value || !diaSelect.value) {
            horariosSugeridos.innerHTML = `
                <div class="text-gray-500 text-sm">
                    <i class="fas fa-clock mr-2"></i>
                    Selecione professor, sala e dia para ver sugestões
                </div>
            `;
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('funcionario_id', funcionarioSelect.value);
            formData.append('sala_id', salaSelect.value);
            formData.append('dia_semana', diaSelect.value);
            if (turmaSelect && turmaSelect.value) formData.append('turma_id', turmaSelect.value);
            if (dataInicioInput.value) formData.append('data_inicio', dataInicioInput.value);
            if (dataFimInput.value) formData.append('data_fim', dataFimInput.value);
            
            const response = await fetch('{{ route("grade-aulas.sugestoes.horarios") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.sugestoes.length > 0) {
                horariosSugeridos.innerHTML = data.sugestoes.map(sugestao => `
                    <button type="button" 
                            class="w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors sugestao-horario"
                            data-tempo-slot-id="${sugestao.tempo_slot_id}">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900">${sugestao.hora_inicio} - ${sugestao.hora_fim}</span>
                            <i class="fas fa-plus text-blue-500"></i>
                        </div>
                    </button>
                `).join('');
                
                // Adicionar event listeners para as sugestões
                document.querySelectorAll('.sugestao-horario').forEach(btn => {
                    btn.addEventListener('click', function() {
                        tempoSlotSelect.value = this.dataset.tempoSlotId;
                        tempoSlotSelect.dispatchEvent(new Event('change'));
                    });
                });
            } else {
                horariosSugeridos.innerHTML = `
                    <div class="text-gray-500 text-sm">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhum horário disponível encontrado
                    </div>
                `;
            }
        } catch (error) {
            console.error('Erro ao obter sugestões de horários:', error);
        }
    }

    // Função para filtrar tempo slots por turma (turno) e reconstruir o select
    async function filtrarTempoSlotsPorTurma() {
        if (!turmaSelect || !turmaSelect.value) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('turma_id', turmaSelect.value);

            const response = await fetch('{{ route("grade-aulas.tempo-slots.por-turma") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

            if (data.success && Array.isArray(data.tempo_slots)) {
                const currentValue = tempoSlotSelect.value;
                // Limpar opções e recriar
                tempoSlotSelect.innerHTML = '<option value="">Selecione o horário</option>';
                const textosVistos = new Set();

                data.tempo_slots.forEach(slot => {
                    const texto = `${slot.hora_inicio} - ${slot.hora_fim}`;
                    if (textosVistos.has(texto)) return; // Deduplicação defensiva
                    textosVistos.add(texto);

                    const option = document.createElement('option');
                    option.value = slot.id;
                    option.textContent = texto;
                    tempoSlotSelect.appendChild(option);
                });

                // Restaurar seleção se ainda existir
                if ([...tempoSlotSelect.options].some(opt => opt.value === currentValue)) {
                    tempoSlotSelect.value = currentValue;
                } else {
                    tempoSlotSelect.value = '';
                }

                // Atualizar disponibilidade com base nos campos atuais
                atualizarTempoSlotsComDisponibilidade();
            }
        } catch (error) {
            console.error('Erro ao filtrar tempo slots por turma:', error);
        }
    }

    // Desabilitar horários indisponíveis no select com base nas sugestões
    async function atualizarTempoSlotsComDisponibilidade() {
        if (!salaSelect.value || !diaSelect.value || !funcionarioSelect.value) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('funcionario_id', funcionarioSelect.value);
            formData.append('sala_id', salaSelect.value);
            formData.append('dia_semana', diaSelect.value);
            if (turmaSelect && turmaSelect.value) formData.append('turma_id', turmaSelect.value);
            if (dataInicioInput.value) formData.append('data_inicio', dataInicioInput.value);
            if (dataFimInput.value) formData.append('data_fim', dataFimInput.value);

            const response = await fetch('{{ route("grade-aulas.sugestoes.horarios") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            if (data.success && Array.isArray(data.sugestoes)) {
                const mapa = new Map(data.sugestoes.map(s => [String(s.tempo_slot_id), s]));
                [...tempoSlotSelect.options].forEach(opt => {
                    if (!opt.value) return; // pular placeholder
                    const s = mapa.get(String(opt.value));
                    if (s) {
                        const disponivel = s.status === 'disponivel';
                        opt.disabled = !disponivel;
                        // Atualiza o texto para indicar indisponibilidade (sem poluir demais)
                        const baseTexto = `${s.hora_inicio} - ${s.hora_fim}`;
                        opt.textContent = disponivel ? baseTexto : `${baseTexto} (indisponível)`;
                    }
                });
            }
        } catch (error) {
            console.error('Erro ao atualizar disponibilidade dos horários:', error);
        }
    }
    
    // Função para obter sugestões de salas
    async function obterSugestoesSalas() {
        if (!funcionarioSelect.value || !tempoSlotSelect.value || !diaSelect.value) {
            salasSugeridas.innerHTML = `
                <div class="text-gray-500 text-sm">
                    <i class="fas fa-building mr-2"></i>
                    Selecione professor, horário e dia para ver salas disponíveis
                </div>
            `;
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('funcionario_id', funcionarioSelect.value);
            formData.append('tempo_slot_id', tempoSlotSelect.value);
            formData.append('dia_semana', diaSelect.value);
            if (dataInicioInput.value) formData.append('data_inicio', dataInicioInput.value);
            if (dataFimInput.value) formData.append('data_fim', dataFimInput.value);
            
            const response = await fetch('{{ route("grade-aulas.sugestoes.salas") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.sugestoes.length > 0) {
                salasSugeridas.innerHTML = data.sugestoes.map(sugestao => `
                    <button type="button" 
                            class="w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition-colors sugestao-sala"
                            data-sala-id="${sugestao.sala_id}">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-gray-900">${sugestao.sala_nome}</span>
                                <span class="text-sm text-gray-500 block">${sugestao.capacidade} lugares</span>
                            </div>
                            <i class="fas fa-plus text-green-500"></i>
                        </div>
                    </button>
                `).join('');
                
                // Adicionar event listeners para as sugestões
                document.querySelectorAll('.sugestao-sala').forEach(btn => {
                    btn.addEventListener('click', function() {
                        salaSelect.value = this.dataset.salaId;
                        salaSelect.dispatchEvent(new Event('change'));
                    });
                });
            } else {
                salasSugeridas.innerHTML = `
                    <div class="text-gray-500 text-sm">
                        <i class="fas fa-info-circle mr-2"></i>
                        Nenhuma sala disponível encontrada
                    </div>
                `;
            }
        } catch (error) {
            console.error('Erro ao obter sugestões de salas:', error);
        }
    }
    
    // Função para filtrar disciplinas por turma
    async function filtrarDisciplinasPorTurma() {
        if (!turmaSelect.value) {
            // Se não há turma selecionada, mostrar todas as disciplinas
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('turma_id', turmaSelect.value);
            
            const response = await fetch('{{ route("grade-aulas.disciplinas.por-turma") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Limpar o select de disciplinas
                disciplinaSelect.innerHTML = '<option value="">Selecione uma disciplina</option>';
                
                // Adicionar as disciplinas filtradas
                data.disciplinas.forEach(disciplina => {
                    const option = document.createElement('option');
                    option.value = disciplina.id;
                    option.textContent = disciplina.nome;
                    if (disciplina.codigo) {
                        option.textContent += ` (${disciplina.codigo})`;
                    }
                    disciplinaSelect.appendChild(option);
                });
                
                // Mostrar informação sobre o nível de ensino (opcional)
                console.log(`Disciplinas filtradas para: ${data.nivel_ensino.nome}`);
            } else {
                console.error('Erro ao filtrar disciplinas:', data.message);
            }
        } catch (error) {
            console.error('Erro ao filtrar disciplinas por turma:', error);
        }
    }

    // Função para obter professores alternativos
    async function obterProfessoresAlternativos() {
        if (!permiteSubstituicaoCheckbox.checked || !salaSelect.value || !tempoSlotSelect.value || !diaSelect.value || !disciplinaSelect.value) {
            professoresAlternativosContainer.style.display = 'none';
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('sala_id', salaSelect.value);
            formData.append('tempo_slot_id', tempoSlotSelect.value);
            formData.append('dia_semana', diaSelect.value);
            formData.append('disciplina_id', disciplinaSelect.value);
            if (dataInicioInput.value) formData.append('data_inicio', dataInicioInput.value);
            if (dataFimInput.value) formData.append('data_fim', dataFimInput.value);
            
            const response = await fetch('{{ route("grade-aulas.professores.alternativos") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.professores_alternativos && data.professores_alternativos.length > 0) {
                professoresAlternativosContainer.style.display = 'block';
                professoresAlternativos.innerHTML = data.professores_alternativos.map(professor => `
                    <div class="p-3 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-medium text-gray-900">${professor.nome}</span>
                                <span class="text-sm text-gray-500 block">${professor.cargo || 'Professor'}</span>
                            </div>
                            <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">Disponível</span>
                        </div>
                    </div>
                `).join('');
            } else {
                professoresAlternativosContainer.style.display = 'none';
            }
        } catch (error) {
            console.error('Erro ao obter professores alternativos:', error);
        }
    }
    
    // Event listeners para campos que afetam as sugestões
    function onFieldChange() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            verificarConflitos();
            obterSugestoesHorarios();
            obterSugestoesSalas();
            if (permiteSubstituicaoCheckbox && permiteSubstituicaoCheckbox.checked) {
                obterProfessoresAlternativos();
            }
        }, 500);
    }
    
    // Event listener específico para turma (filtrar disciplinas)
    if (turmaSelect) {
        turmaSelect.addEventListener('change', function() {
            filtrarDisciplinasPorTurma();
            filtrarTempoSlotsPorTurma();
            onFieldChange(); // Manter a funcionalidade existente
        });
    }

    // Adicionar event listeners para os outros campos
    [disciplinaSelect, funcionarioSelect, salaSelect, diaSelect, tempoSlotSelect, dataInicioInput, dataFimInput].forEach(element => {
        if (element) {
            element.addEventListener('change', () => {
                onFieldChange();
                if (element === salaSelect || element === diaSelect || element === funcionarioSelect) {
                    atualizarTempoSlotsComDisponibilidade();
                }
            });
        }
    });
    
    // Event listener para o checkbox de substituição
    if (permiteSubstituicaoCheckbox) {
        permiteSubstituicaoCheckbox.addEventListener('change', function() {
            if (this.checked) {
                obterProfessoresAlternativos();
            } else {
                if (professoresAlternativos) {
                    professoresAlternativos.innerHTML = '';
                }
            }
        });
    }
    
    // Controle dos toggle buttons para tipo de aula
    const aulaAnualRadio = document.getElementById('aula_anual');
    const aulaPeriodoRadio = document.getElementById('aula_periodo');
    const camposPeriodo = document.getElementById('campos-periodo');
    
    function toggleCamposPeriodo() {
        if (aulaPeriodoRadio && aulaPeriodoRadio.checked) {
            camposPeriodo.classList.remove('hidden');
            // Tornar campos obrigatórios quando visíveis
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');
            const tipoPeriodo = document.getElementById('tipo_periodo');
            
            if (dataInicio) dataInicio.required = true;
            if (dataFim) dataFim.required = true;
            if (tipoPeriodo) tipoPeriodo.required = true;
        } else {
            camposPeriodo.classList.add('hidden');
            // Remover obrigatoriedade quando ocultos
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');
            const tipoPeriodo = document.getElementById('tipo_periodo');
            
            if (dataInicio) {
                dataInicio.required = false;
                dataInicio.value = '';
            }
            if (dataFim) {
                dataFim.required = false;
                dataFim.value = '';
            }
            if (tipoPeriodo) {
                tipoPeriodo.required = false;
                tipoPeriodo.value = '';
            }
        }
    }
    
    // Event listeners para os toggle buttons
    if (aulaAnualRadio) {
        aulaAnualRadio.addEventListener('change', toggleCamposPeriodo);
    }
    if (aulaPeriodoRadio) {
        aulaPeriodoRadio.addEventListener('change', toggleCamposPeriodo);
    }
    
    // Inicializar estado dos campos
    toggleCamposPeriodo();

    // Submissão do formulário
    form.addEventListener('submit', function(e) {
        
        submitBtn.disabled = true;
        submitText.textContent = 'Criando...';
        submitIcon.className = 'fas fa-spinner fa-spin ml-2';
    });
});
</script>
@endpush