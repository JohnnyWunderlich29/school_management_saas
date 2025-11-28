<form method="POST" action="{{ route('grade-aulas.update', $gradeAula) }}" class="space-y-6">
    @csrf
    @method('PUT')
    
    <!-- Primeira linha: Turma e Disciplina -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Turma -->
        <div>
            <label for="turma_id" class="block text-sm font-medium text-gray-700 mb-2">
                Turma <span class="text-red-500">*</span>
            </label>
            <select name="turma_id" id="turma_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione uma turma</option>
                @foreach($turmas as $turma)
                    <option value="{{ $turma->id }}" {{ old('turma_id', $gradeAula->turma_id) == $turma->id ? 'selected' : '' }}>
                        {{ $turma->nome }}
                        @if($turma->grupo && $turma->grupo->modalidadeEnsino)
                            - {{ $turma->grupo->modalidadeEnsino->nome }}
                        @endif
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione uma disciplina</option>
                @foreach($disciplinas as $disciplina)
                    <option value="{{ $disciplina->id }}" {{ old('disciplina_id', $gradeAula->disciplina_id) == $disciplina->id ? 'selected' : '' }}>
                        {{ $disciplina->nome }}
                    </option>
                @endforeach
            </select>
            @error('disciplina_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Segunda linha: Professor e Sala -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Professor -->
        <div>
            <label for="funcionario_id" class="block text-sm font-medium text-gray-700 mb-2">
                Professor <span class="text-red-500">*</span>
            </label>
            <select name="funcionario_id" id="funcionario_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione um professor</option>
                @foreach($professores as $professor)
                    <option value="{{ $professor->id }}" {{ old('funcionario_id', $gradeAula->funcionario_id) == $professor->id ? 'selected' : '' }}>
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione uma sala</option>
                @foreach($salas as $sala)
                    <option value="{{ $sala->id }}" {{ old('sala_id', $gradeAula->sala_id) == $sala->id ? 'selected' : '' }}>
                        {{ $sala->nome }}
                    </option>
                @endforeach
            </select>
            @error('sala_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Terceira linha: Dia da Semana e Horário -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Dia da Semana -->
        <div>
            <label for="dia_semana" class="block text-sm font-medium text-gray-700 mb-2">
                Dia da Semana <span class="text-red-500">*</span>
            </label>
            <select name="dia_semana" id="dia_semana" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione o dia</option>
                <option value="1" {{ old('dia_semana', $gradeAula->dia_semana) == '1' ? 'selected' : '' }}>Segunda-feira</option>
                <option value="2" {{ old('dia_semana', $gradeAula->dia_semana) == '2' ? 'selected' : '' }}>Terça-feira</option>
                <option value="3" {{ old('dia_semana', $gradeAula->dia_semana) == '3' ? 'selected' : '' }}>Quarta-feira</option>
                <option value="4" {{ old('dia_semana', $gradeAula->dia_semana) == '4' ? 'selected' : '' }}>Quinta-feira</option>
                <option value="5" {{ old('dia_semana', $gradeAula->dia_semana) == '5' ? 'selected' : '' }}>Sexta-feira</option>
                <option value="6" {{ old('dia_semana', $gradeAula->dia_semana) == '6' ? 'selected' : '' }}>Sábado</option>
                <option value="0" {{ old('dia_semana', $gradeAula->dia_semana) == '0' ? 'selected' : '' }}>Domingo</option>
            </select>
            @error('dia_semana')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Horário -->
        <div>
            <label for="tempo_slot_id" class="block text-sm font-medium text-gray-700 mb-2">
                Horário <span class="text-red-500">*</span>
            </label>
            <select name="tempo_slot_id" id="tempo_slot_id" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione o horário</option>
                @foreach($tempoSlots as $slot)
                    <option value="{{ $slot->id }}" {{ old('tempo_slot_id', $gradeAula->tempo_slot_id) == $slot->id ? 'selected' : '' }}>
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
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Aula</label>
        
        <!-- Toggle Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
            <!-- Aula Anual -->
            <div>
                <input type="radio" name="tipo_aula" id="aula_anual" value="anual" 
                       class="sr-only" {{ old('tipo_aula', $gradeAula->tipo_aula ?? 'anual') == 'anual' ? 'checked' : '' }}>
                <label class="flex flex-col items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" for="aula_anual">
                    <i class="fas fa-calendar-alt text-2xl mb-2 text-blue-600"></i>
                    <span class="font-medium">Aula Anual</span>
                    <span class="text-sm text-gray-500">Aula regular durante todo o ano letivo</span>
                </label>
            </div>

            <!-- Aula com Período Específico -->
            <div>
                <input type="radio" name="tipo_aula" id="aula_periodo" value="periodo" 
                       class="sr-only" {{ old('tipo_aula', $gradeAula->tipo_aula) == 'periodo' ? 'checked' : '' }}>
                <label class="flex flex-col items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" for="aula_periodo">
                    <i class="fas fa-calendar-week text-2xl mb-2 text-green-600"></i>
                    <span class="font-medium">Período Específico</span>
                    <span class="text-sm text-gray-500">Aula com data de início e fim</span>
                </label>
            </div>
        </div>

        <!-- Campos de Período (mostrados apenas quando "periodo" está selecionado) -->
        <div id="campos-periodo" class="space-y-4" style="display: {{ old('tipo_aula', $gradeAula->tipo_aula) == 'periodo' ? 'block' : 'none' }};">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Tipo de Período -->
                <div>
                    <label for="tipo_periodo" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Período <span class="text-red-500">*</span>
                    </label>
                    <select name="tipo_periodo" id="tipo_periodo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione o tipo</option>
                        <option value="bimestre" {{ old('tipo_periodo', $gradeAula->tipo_periodo) == 'bimestre' ? 'selected' : '' }}>Bimestre</option>
                        <option value="trimestre" {{ old('tipo_periodo', $gradeAula->tipo_periodo) == 'trimestre' ? 'selected' : '' }}>Trimestre</option>
                        <option value="semestre" {{ old('tipo_periodo', $gradeAula->tipo_periodo) == 'semestre' ? 'selected' : '' }}>Semestre</option>
                        <option value="personalizado" {{ old('tipo_periodo', $gradeAula->tipo_periodo) == 'personalizado' ? 'selected' : '' }}>Personalizado</option>
                    </select>
                    @error('tipo_periodo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Data de Início -->
                <div>
                    <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                        Data de Início <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="data_inicio" id="data_inicio"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('data_inicio', $gradeAula->data_inicio?->format('Y-m-d')) }}">
                    @error('data_inicio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Data de Fim -->
                <div>
                    <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-2">
                        Data de Fim <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="data_fim" id="data_fim"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('data_fim', $gradeAula->data_fim?->format('Y-m-d')) }}">
                    @error('data_fim')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Observações -->
    <div>
        <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
        <textarea name="observacoes" id="observacoes" rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Observações sobre esta aula...">{{ old('observacoes', $gradeAula->observacoes) }}</textarea>
        @error('observacoes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Botões -->
    <div class="flex items-center justify-end pt-4 border-t space-x-3">
        <button type="button" onclick="closeEditModal()" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
            <i class="fas fa-times mr-2"></i>
            Cancelar
        </button>
        
        <button type="submit" 
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
            <i class="fas fa-save mr-2"></i>
            Salvar Alterações
        </button>
    </div>
</form>

<script>
// Função para alternar campos de período
function toggleCamposPeriodo() {
    const aulaAnual = document.getElementById('aula_anual');
    const aulaPeriodo = document.getElementById('aula_periodo');
    const camposPeriodo = document.getElementById('campos-periodo');
    const tipoPeriodo = document.getElementById('tipo_periodo');
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');

    if (aulaPeriodo && aulaPeriodo.checked) {
        camposPeriodo.style.display = 'block';
        tipoPeriodo.required = true;
        dataInicio.required = true;
        dataFim.required = true;
    } else {
        camposPeriodo.style.display = 'none';
        tipoPeriodo.required = false;
        dataInicio.required = false;
        dataFim.required = false;
        
        // Limpar valores quando não é período
        tipoPeriodo.value = '';
        dataInicio.value = '';
        dataFim.value = '';
    }
}

// Event listeners para os radio buttons
document.addEventListener('DOMContentLoaded', function() {
    const aulaAnual = document.getElementById('aula_anual');
    const aulaPeriodo = document.getElementById('aula_periodo');
    
    if (aulaAnual) {
        aulaAnual.addEventListener('change', toggleCamposPeriodo);
    }
    if (aulaPeriodo) {
        aulaPeriodo.addEventListener('change', toggleCamposPeriodo);
    }
    
    // Inicializar estado dos campos
    toggleCamposPeriodo();
    
    // Atualizar estilos dos radio buttons
    updateRadioStyles();
});

function updateRadioStyles() {
    const radios = document.querySelectorAll('input[name="tipo_aula"]');
    radios.forEach(radio => {
        const label = document.querySelector(`label[for="${radio.id}"]`);
        if (radio.checked) {
            label.classList.add('border-blue-500', 'bg-blue-50');
            label.classList.remove('border-gray-200');
        } else {
            label.classList.remove('border-blue-500', 'bg-blue-50');
            label.classList.add('border-gray-200');
        }
        
        radio.addEventListener('change', function() {
            updateRadioStyles();
            toggleCamposPeriodo();
        });
    });
}
</script>