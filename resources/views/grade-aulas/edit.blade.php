@extends('layouts.app')

@section('title', 'Editar Aula na Grade')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Editar Aula na Grade</h3>
                    <div class="card-tools">
                        <a href="{{ route('grade-aulas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('grade-aulas.update', $gradeAula) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <!-- Turma -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="turma_id" class="form-label">Turma <span class="text-danger">*</span></label>
                                    <select name="turma_id" id="turma_id" class="form-select @error('turma_id') is-invalid @enderror" required>
                                        <option value="">Selecione uma turma</option>
                                        @foreach($turmas as $turma)
                                            <option value="{{ $turma->id }}" {{ old('turma_id', $gradeAula->turma_id) == $turma->id ? 'selected' : '' }}>
                                                {{ $turma->nome }} - {{ $turma->grupo->modalidadeEnsino->nome ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('turma_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Disciplina -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="disciplina_id" class="form-label">Disciplina <span class="text-danger">*</span></label>
                                    <select name="disciplina_id" id="disciplina_id" class="form-select @error('disciplina_id') is-invalid @enderror" required>
                                        <option value="">Selecione uma disciplina</option>
                                        @foreach($disciplinas as $disciplina)
                                            <option value="{{ $disciplina->id }}" {{ old('disciplina_id', $gradeAula->disciplina_id) == $disciplina->id ? 'selected' : '' }}>
                                                {{ $disciplina->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('disciplina_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Professor -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="funcionario_id" class="form-label">Professor <span class="text-danger">*</span></label>
                                    <select name="funcionario_id" id="funcionario_id" class="form-select @error('funcionario_id') is-invalid @enderror" required>
                                        <option value="">Selecione um professor</option>
                                        @foreach($professores as $professor)
                                            <option value="{{ $professor->id }}" {{ old('funcionario_id', $gradeAula->funcionario_id) == $professor->id ? 'selected' : '' }}>
                                                {{ $professor->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('funcionario_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Sala -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sala_id" class="form-label">Sala <span class="text-danger">*</span></label>
                                    <select name="sala_id" id="sala_id" class="form-select @error('sala_id') is-invalid @enderror" required>
                                        <option value="">Selecione uma sala</option>
                                        @foreach($salas as $sala)
                                            <option value="{{ $sala->id }}" {{ old('sala_id', $gradeAula->sala_id) == $sala->id ? 'selected' : '' }}>
                                                {{ $sala->nome }} (Cap: {{ $sala->capacidade }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sala_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Dia da Semana -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="dia_semana" class="form-label">Dia da Semana <span class="text-danger">*</span></label>
                                    <select name="dia_semana" id="dia_semana" class="form-select @error('dia_semana') is-invalid @enderror" required>
                                        <option value="">Selecione o dia</option>
                                        <option value="segunda" {{ old('dia_semana', $gradeAula->dia_semana) == 'segunda' ? 'selected' : '' }}>Segunda-feira</option>
                                        <option value="terca" {{ old('dia_semana', $gradeAula->dia_semana) == 'terca' ? 'selected' : '' }}>Terça-feira</option>
                                        <option value="quarta" {{ old('dia_semana', $gradeAula->dia_semana) == 'quarta' ? 'selected' : '' }}>Quarta-feira</option>
                                        <option value="quinta" {{ old('dia_semana', $gradeAula->dia_semana) == 'quinta' ? 'selected' : '' }}>Quinta-feira</option>
                                        <option value="sexta" {{ old('dia_semana', $gradeAula->dia_semana) == 'sexta' ? 'selected' : '' }}>Sexta-feira</option>
                                        <option value="sabado" {{ old('dia_semana', $gradeAula->dia_semana) == 'sabado' ? 'selected' : '' }}>Sábado</option>
                                    </select>
                                    @error('dia_semana')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Tempo Slot -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tempo_slot_id" class="form-label">Horário <span class="text-danger">*</span></label>
                                    <select name="tempo_slot_id" id="tempo_slot_id" class="form-select @error('tempo_slot_id') is-invalid @enderror" required>
                                        <option value="">Selecione o horário</option>
                                        @foreach($tempoSlots as $slot)
                                            <option value="{{ $slot->id }}" {{ $gradeAula->tempo_slot_id == $slot->id ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($slot->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->hora_fim)->format('H:i') }}
                            </option>
                                        @endforeach
                                    </select>
                                    @error('tempo_slot_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="ativo" class="form-label">Status</label>
                                    <select name="ativo" id="ativo" class="form-select @error('ativo') is-invalid @enderror">
                                        <option value="1" {{ old('ativo', $gradeAula->ativo) == '1' ? 'selected' : '' }}>Ativo</option>
                                        <option value="0" {{ old('ativo', $gradeAula->ativo) == '0' ? 'selected' : '' }}>Inativo</option>
                                    </select>
                                    @error('ativo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Tipo de Aula -->
                        <div class="mb-4">
                            <label class="form-label">Tipo de Aula</label>
                            
                            <!-- Toggle Buttons -->
                            <div class="row g-3 mb-3">
                                <!-- Aula Anual -->
                                <div class="col-md-6">
                                    <input type="radio" name="tipo_aula" id="aula_anual" value="anual" 
                                           class="btn-check" {{ old('tipo_aula', $gradeAula->tipo_aula ?? 'anual') == 'anual' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-primary w-100 p-3" for="aula_anual">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                            <h6 class="mb-1">Aula Anual</h6>
                                            <small class="text-muted">Aula regular durante todo o ano letivo</small>
                                        </div>
                                    </label>
                                </div>

                                <!-- Aula com Período Específico -->
                                <div class="col-md-6">
                                    <input type="radio" name="tipo_aula" id="aula_periodo" value="periodo" 
                                           class="btn-check" {{ old('tipo_aula', $gradeAula->tipo_aula) == 'periodo' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-success w-100 p-3" for="aula_periodo">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-clock fa-2x mb-2"></i>
                                            <h6 class="mb-1">Período Específico</h6>
                                            <small class="text-muted">Cursos intensivos, substituições</small>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Campos de Data (aparecem apenas para período específico) -->
                            <div id="campos-periodo" class="d-none">
                                <div class="alert alert-success">
                                    <div class="row">
                                        <!-- Tipo de Período -->
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="tipo_periodo" class="form-label">Tipo de Período</label>
                                                <select name="tipo_periodo" id="tipo_periodo" class="form-select @error('tipo_periodo') is-invalid @enderror">
                                                    <option value="">Selecione o tipo</option>
                                                    <option value="curso_intensivo" {{ old('tipo_periodo', $gradeAula->tipo_periodo) == 'curso_intensivo' ? 'selected' : '' }}>Curso Intensivo</option>
                                                    <option value="substituicao" {{ old('tipo_periodo', $gradeAula->tipo_periodo) == 'substituicao' ? 'selected' : '' }}>Substituição</option>
                                                    <option value="reforco" {{ old('tipo_periodo', $gradeAula->tipo_periodo) == 'reforco' ? 'selected' : '' }}>Reforço</option>
                                                    <option value="recuperacao" {{ old('tipo_periodo', $gradeAula->tipo_periodo) == 'recuperacao' ? 'selected' : '' }}>Recuperação</option>
                                                    <option value="outro" {{ old('tipo_periodo', $gradeAula->tipo_periodo) == 'outro' ? 'selected' : '' }}>Outro</option>
                                                </select>
                                                @error('tipo_periodo')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Data de Início -->
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="data_inicio" class="form-label">Data de Início</label>
                                                <input type="date" name="data_inicio" id="data_inicio" 
                                                       class="form-control @error('data_inicio') is-invalid @enderror" 
                                                       value="{{ old('data_inicio', $gradeAula->data_inicio?->format('Y-m-d')) }}">
                                                @error('data_inicio')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Data de Fim -->
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="data_fim" class="form-label">Data de Fim</label>
                                                <input type="date" name="data_fim" id="data_fim" 
                                                       class="form-control @error('data_fim') is-invalid @enderror" 
                                                       value="{{ old('data_fim', $gradeAula->data_fim?->format('Y-m-d')) }}">
                                                @error('data_fim')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Observações -->
                        <div class="mb-3">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea name="observacoes" id="observacoes" rows="3" 
                                      class="form-control @error('observacoes') is-invalid @enderror" 
                                      placeholder="Observações sobre esta aula...">{{ old('observacoes', $gradeAula->observacoes) }}</textarea>
                            @error('observacoes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Verificação de Conflitos -->
                        <div id="conflitos-alert" class="alert alert-warning d-none">
                            <h6><i class="fas fa-exclamation-triangle"></i> Conflitos Detectados:</h6>
                            <ul id="lista-conflitos"></ul>
                        </div>
                    </div>

                    <div class="flex items-center justify-end px-6 py-4 border-t border-gray-200 bg-gray-50 space-x-3">
                        <button type="button" onclick="window.history.back()" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </button>
                        
                        <a href="{{ route('grade-aulas.show', $gradeAula) }}" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 border border-transparent rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                            <i class="fas fa-eye mr-2"></i>
                            Visualizar
                        </a>
                        
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i>
                            Atualizar Aula
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar conflitos quando os campos relevantes mudarem
    const camposConflito = ['funcionario_id', 'sala_id', 'dia_semana', 'tempo_slot_id', 'data_inicio', 'data_fim'];
    
    camposConflito.forEach(campo => {
        const element = document.getElementById(campo);
        if (element) {
            element.addEventListener('change', debounce(verificarConflitos, 500));
        }
    });

    // Debounce para evitar muitas requisições
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function verificarConflitos() {
        const funcionarioId = document.getElementById('funcionario_id').value;
        const salaId = document.getElementById('sala_id').value;
        const diaSemana = document.getElementById('dia_semana').value;
        const tempoSlotId = document.getElementById('tempo_slot_id').value;
        const dataInicio = document.getElementById('data_inicio').value;
        const dataFim = document.getElementById('data_fim').value;

        // Limpar alertas anteriores
        limparAlertas();

        if (funcionarioId && salaId && diaSemana && tempoSlotId) {
            // Mostrar indicador de carregamento
            mostrarCarregando();

            // Fazer requisição AJAX
            fetch('{{ route("grade-aulas.verificar.conflitos") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    funcionario_id: funcionarioId,
                    sala_id: salaId,
                    dia_semana: diaSemana,
                    tempo_slot_id: tempoSlotId,
                    data_inicio: dataInicio || null,
                    data_fim: dataFim || null,
                    grade_aula_id: {{ $gradeAula->id ?? 'null' }} // Excluir a própria aula na edição
                })
            })
            .then(response => response.json())
            .then(data => {
                esconderCarregando();
                
                if (data.success) {
                    mostrarResultadoConflitos(data.conflitos, data.tem_conflitos);
                } else {
                    console.error('Erro na verificação:', data);
                }
            })
            .catch(error => {
                esconderCarregando();
                console.error('Erro na requisição:', error);
            });
        }
    }

    function mostrarCarregando() {
        const container = document.getElementById('conflict-alerts');
        if (container) {
            container.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    Verificando conflitos...
                </div>
            `;
        }
    }

    function esconderCarregando() {
        // A função mostrarResultadoConflitos vai substituir o conteúdo
    }

    function limparAlertas() {
        const container = document.getElementById('conflict-alerts');
        if (container) {
            container.innerHTML = '';
        }
    }

    function mostrarResultadoConflitos(conflitos, temConflitos) {
        const container = document.getElementById('conflict-alerts');
        if (!container) return;

        if (conflitos.length === 0) {
            container.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i>
                    Nenhum conflito detectado
                </div>
            `;
            return;
        }

        let html = '';
        conflitos.forEach(conflito => {
            const alertClass = conflito.severidade === 'error' ? 'alert-danger' : 'alert-warning';
            const icon = conflito.severidade === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle';
            
            html += `
                <div class="alert ${alertClass}">
                    <i class="fas ${icon} mr-2"></i>
                    ${conflito.mensagem}
                </div>
            `;
        });

        container.innerHTML = html;

        // Desabilitar botão de submit se houver conflitos críticos
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = temConflitos;
            if (temConflitos) {
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            } else {
                submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
            }
        }
    }

    // Controle dos toggle buttons para tipo de aula
    const aulaAnualRadio = document.getElementById('aula_anual');
    const aulaPeriodoRadio = document.getElementById('aula_periodo');
    const camposPeriodo = document.getElementById('campos-periodo');
    
    function toggleCamposPeriodo() {
        if (aulaPeriodoRadio && aulaPeriodoRadio.checked) {
            camposPeriodo.classList.remove('d-none');
            // Tornar campos obrigatórios quando visíveis
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');
            const tipoPeriodo = document.getElementById('tipo_periodo');
            
            if (dataInicio) dataInicio.required = true;
            if (dataFim) dataFim.required = true;
            if (tipoPeriodo) tipoPeriodo.required = true;
        } else {
            camposPeriodo.classList.add('d-none');
            // Remover obrigatoriedade quando ocultos para aulas anuais
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');
            const tipoPeriodo = document.getElementById('tipo_periodo');
            
            if (dataInicio) dataInicio.required = false;
            if (dataFim) dataFim.required = false;
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
});
</script>
@endpush
@endsection