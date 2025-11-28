<!-- Etapa 3: Turmas e Disciplinas -->
<form id="step-3-form">
<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <i class="fas fa-users text-blue-600 mr-2"></i>
            Turma e Disciplina
        </h3>
        <p class="text-gray-600 mt-1">Selecione a turma e disciplina para este planejamento</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Turma -->
        <div>
            <label for="turma_id" class="block text-sm font-medium text-gray-700 mb-2">
                Turma <span class="text-red-500">*</span>
            </label>
            <select name="turma_id" id="turma_id" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Carregando turmas...</option>
            </select>
            @error('turma_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
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
                    <option value="{{ $disciplina->id }}" 
                            data-carga-horaria=""
                            {{ (old('disciplina_id', $planejamento->disciplina_id ?? '') == $disciplina->id) ? 'selected' : '' }}>
                        {{ $disciplina->nome }}
                    </option>
                @endforeach
            </select>
            @error('disciplina_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Informações da Turma Selecionada -->
    <div id="turma-info" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Informações da Turma</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
            <div>
                <span class="font-medium">Série:</span>
                <span id="turma-serie">-</span>
            </div>
            <div>
                <span class="font-medium">Nº de Alunos:</span>
                <span id="turma-alunos">-</span>
            </div>
            <div>
                <span class="font-medium">Sala:</span>
                <span id="turma-sala">-</span>
            </div>
        </div>
        
        <!-- Lista de Alunos -->
        <div class="mt-4">
            <button type="button" onclick="toggleAlunosList()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                <i class="fas fa-users mr-1"></i>
                Ver lista de alunos
            </button>
            <div id="alunos-list" class="hidden mt-2 max-h-32 overflow-y-auto bg-white border border-gray-200 rounded p-2">
                <!-- Lista será carregada via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Informações da Disciplina Selecionada -->
    <div id="disciplina-info" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-3">Informações da Disciplina</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
            <div>
                <span class="font-medium">Carga Horária:</span>
                <span id="disciplina-carga">-</span>
            </div>
            <div>
                <span class="font-medium">Área do Conhecimento:</span>
                <span id="disciplina-area">-</span>
            </div>
        </div>
        
        <!-- Objetivos da Disciplina -->
        <div class="mt-3">
            <span class="font-medium text-sm text-gray-700">Objetivos Gerais:</span>
            <div id="disciplina-objetivos" class="text-sm text-gray-600 mt-1">
                <!-- Objetivos serão carregados via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Professor Responsável -->
    <div>
        <label for="professor_id" class="block text-sm font-medium text-gray-700 mb-2">
            Professor Responsável
        </label>
        <select name="professor_id" id="professor_id" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <option value="">Selecione um professor (opcional)</option>
            @foreach($professores as $professor)
                <option value="{{ $professor->id }}" 
                        {{ (old('professor_id', $planejamento->professor_id ?? Auth::id()) == $professor->id) ? 'selected' : '' }}>
                    {{ $professor->name }} - {{ $professor->email }}
                </option>
            @endforeach
        </select>
        <p class="text-gray-500 text-xs mt-1">Se não selecionado, você será definido como responsável</p>
        @error('professor_id')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Verificação de Conflitos -->
    <div id="conflitos-check" class="hidden">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-red-800">Conflitos Detectados</h4>
                    <div id="conflitos-messages" class="text-sm text-red-700 mt-1">
                        <!-- Mensagens de conflito serão inseridas aqui -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dicas -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-lightbulb text-blue-600 mt-0.5"></i>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-blue-800">Dicas para esta etapa:</h4>
                <ul class="text-sm text-blue-700 mt-1 space-y-1">
                    <li>• A turma determina o nível e quantidade de alunos para o planejamento</li>
                    <li>• A disciplina define os conteúdos e objetivos específicos</li>
                    <li>• Verifique se há conflitos com outros planejamentos da mesma turma</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</form>

<script>
// Remover IIFE para evitar problemas de escopo quando re-executado
console.log('🚀 STEP-3: Script executado diretamente!');

const turmaSelect = document.getElementById('turma_id');
const disciplinaSelect = document.getElementById('disciplina_id');
const professorSelect = document.getElementById('professor_id');
const turmaInfo = document.getElementById('turma-info');
const disciplinaInfo = document.getElementById('disciplina-info');
const conflitosCheck = document.getElementById('conflitos-check');
// Garantir stores globais
window.planejamentoWizard = window.planejamentoWizard || { formData: {} };
window.planejamentoWizard.formData[3] = window.planejamentoWizard.formData[3] || {};
window.wizardData = window.wizardData || {};
window.wizardData.step3 = window.wizardData.step3 || {};
    
    console.log('🔍 STEP-3: Elementos encontrados:', {
        turmaSelect: !!turmaSelect,
        disciplinaSelect: !!disciplinaSelect,
        professorSelect: !!professorSelect
    });

    // Função para carregar turmas baseado na modalidade, nível de ensino e turno das etapas anteriores
    function loadTurmasFiltered() {
        // Obter dados das etapas anteriores do wizard
        const wizard = window.planejamentoWizard;
        
        if (!wizard || !wizard.formData || !wizard.formData[1] || !wizard.formData[2]) {
            console.log('Dados das etapas anteriores não encontrados');
            return;
        }

        const step1Data = wizard.formData[1];
        const step2Data = wizard.formData[2];
        
        const modalidadeId = step1Data.modalidade_ensino_id;
        const nivelEnsinoId = step1Data.nivel_ensino_id;
        const turnoId = step2Data.turno_id;

        if (!modalidadeId || !nivelEnsinoId || !turnoId) {
            console.log('Modalidade, nível de ensino ou turno não selecionados nas etapas anteriores');
            return;
        }

        // Limpar opções atuais (exceto a primeira)
        turmaSelect.innerHTML = '<option value="">Selecione uma turma</option>';
        disciplinaSelect.innerHTML = '<option value="">Selecione uma disciplina</option>';
        
        // Mostrar loading
        turmaSelect.disabled = true;
        turmaSelect.innerHTML = '<option value="">Carregando turmas...</option>';

        // Fazer requisição para buscar turmas filtradas
        fetch(`/api/planejamentos/turmas-filtered?modalidade_id=${modalidadeId}&nivel_ensino_id=${nivelEnsinoId}&turno_id=${turnoId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            turmaSelect.innerHTML = '<option value="">Selecione uma turma</option>';
            
            if (data.error) {
                console.error('Erro ao carregar turmas:', data.error);
                turmaSelect.innerHTML = '<option value="">Erro ao carregar turmas</option>';
                return;
            }

            // Adicionar turmas filtradas
            data.forEach(turma => {
                const option = document.createElement('option');
                option.value = turma.id;
                option.textContent = `${turma.nome}`;
                if (turma.grupo && turma.grupo.nome) {
                    option.textContent += ` - ${turma.grupo.nome}`;
                }
                if (turma.nivel_ensino && turma.nivel_ensino.nome) {
                    option.textContent += ` (${turma.nivel_ensino.nome})`;
                }
                
                // Adicionar dados para uso posterior
                option.dataset.serie = turma.grupo ? turma.grupo.nome : '';
                option.dataset.alunos = '0'; // Será carregado via API quando selecionado
                option.dataset.sala = ''; // Será carregado via API quando selecionado
                option.dataset.disciplinas = JSON.stringify(turma.disciplinas || []);
                
                turmaSelect.appendChild(option);
            });

            // Reabilitar select
            turmaSelect.disabled = false;

            // Se havia um valor selecionado anteriormente, tentar restaurar
            const savedTurmaId = wizard.formData[3] && wizard.formData[3].turma_id;
            const oldTurmaId = '{{ old("turma_id", $planejamento->turma_id ?? "") }}';
            
            const turmaIdToRestore = savedTurmaId || oldTurmaId;
            if (turmaIdToRestore) {
                turmaSelect.value = turmaIdToRestore;
                turmaSelect.dispatchEvent(new Event('change'));
            }
        })
        .catch(error => {
            console.error('Erro ao carregar turmas:', error);
            turmaSelect.innerHTML = '<option value="">Erro ao carregar turmas</option>';
            turmaSelect.disabled = false;
        });
    }

    // Função para carregar disciplinas baseado na turma selecionada
    function loadDisciplinasForTurma(turmaOption) {
        // Limpar select de disciplinas
        disciplinaSelect.innerHTML = '<option value="">Selecione uma disciplina</option>';
        
        if (!turmaOption || !turmaOption.dataset.disciplinas) {
            return;
        }

        try {
            const disciplinas = JSON.parse(turmaOption.dataset.disciplinas);
            
            if (!disciplinas || disciplinas.length === 0) {
                disciplinaSelect.innerHTML = '<option value="">Nenhuma disciplina disponível para esta turma</option>';
                return;
            }

            // Adicionar disciplinas da turma selecionada
            disciplinas.forEach(disciplina => {
                const option = document.createElement('option');
                option.value = disciplina.id;
                option.textContent = disciplina.nome;
                //Não tem necessidade de mostrar
                //if (disciplina.area) {
                    //option.textContent += ` (${disciplina.area})`;
                //}
                if (disciplina.carga_horaria) {
                    option.textContent += ` - ${disciplina.carga_horaria}h`;
                }
                disciplinaSelect.appendChild(option);
            });

            // Se havia um valor selecionado anteriormente, tentar restaurar
            const wizard = window.planejamentoWizard;
            const savedDisciplinaId = wizard.formData[3] && wizard.formData[3].disciplina_id;
            if (savedDisciplinaId) {
                disciplinaSelect.value = savedDisciplinaId;
                disciplinaSelect.dispatchEvent(new Event('change'));
            }
        } catch (error) {
            console.error('Erro ao carregar disciplinas da turma:', error);
            disciplinaSelect.innerHTML = '<option value="">Erro ao carregar disciplinas</option>';
        }
    }

    // Carregar turmas quando a etapa for carregada
    console.log('🕐 STEP-3: Tentando carregar turmas...');
    
    let turmasCarregadas = false;
    
    // Função para tentar carregar turmas apenas uma vez
    function tentarCarregarTurmas() {
        if (turmasCarregadas) {
            console.log('🔄 STEP-3: Turmas já foram carregadas, ignorando...');
            return;
        }
        
        if (window.planejamentoWizard && window.planejamentoWizard.formData && window.planejamentoWizard.formData[1] && window.planejamentoWizard.formData[2]) {
            console.log('🎯 STEP-3: Wizard encontrado, carregando turmas...');
            turmasCarregadas = true;
            loadTurmasFiltered();
        }
    }
    
    // Tentar carregar imediatamente
    tentarCarregarTurmas();
    
    // Se não funcionou, tentar novamente após um delay
    if (!turmasCarregadas) {
        setTimeout(() => {
            console.log('🕐 STEP-3: Tentando carregar turmas após delay...');
            tentarCarregarTurmas();
        }, 1000);
    }

    // Carregar informações da turma
    turmaSelect.addEventListener('change', function() {
        const turmaId = this.value;
        const selectedOption = this.options[this.selectedIndex];
        // Persistir em stores
        try {
            window.planejamentoWizard.formData[3].turma_id = turmaId || '';
            window.wizardData.step3.turma_id = turmaId || '';
        } catch (e) { console.warn('STEP-3: falha ao persistir turma_id', e); }
        
        if (turmaId) {
            // Mostrar informações básicas
            document.getElementById('turma-serie').textContent = selectedOption.dataset.serie || '-';
            document.getElementById('turma-alunos').textContent = selectedOption.dataset.alunos || '-';
            
            // Carregar disciplinas da turma selecionada
            loadDisciplinasForTurma(selectedOption);
            
            // Carregar informações detalhadas
            fetch(`/api/turmas/${turmaId}`)
                .then(response => response.json())
                .then(data => {
                    const t = data && data.turma ? data.turma : {};
                    // Atualizar série (preferir o dataset já formatado)
                    document.getElementById('turma-serie').textContent = selectedOption.dataset.serie || '-';
                    // Atualizar número de alunos
                    document.getElementById('turma-alunos').textContent = (typeof t.alunos_count !== 'undefined') ? t.alunos_count : '-';
                    // Atualizar sala
                    document.getElementById('turma-sala').textContent = t.sala || '-';
                    turmaInfo.classList.remove('hidden');
                    verificarConflitos();
                })
                .catch(error => {
                    console.error('Erro ao carregar informações da turma:', error);
                    turmaInfo.classList.add('hidden');
                });
        } else {
            turmaInfo.classList.add('hidden');
            // Limpar disciplinas quando nenhuma turma estiver selecionada
            disciplinaSelect.innerHTML = '<option value="">Selecione uma disciplina</option>';
        }
    });

    // Carregar informações da disciplina
    // Persistir disciplina e opcionalmente carregar informações
    disciplinaSelect.addEventListener('change', function() {
        const disciplinaId = this.value;
        const selectedOption = this.options[this.selectedIndex];
        try {
            window.planejamentoWizard.formData[3].disciplina_id = disciplinaId || '';
            window.wizardData.step3.disciplina_id = disciplinaId || '';
        } catch (e) { console.warn('STEP-3: falha ao persistir disciplina_id', e); }

        if (disciplinaId) {
            disciplinaInfo.classList.remove('hidden');
            // Atualizar carga se disponível no option
            const cargaTxt = selectedOption && selectedOption.textContent && selectedOption.textContent.match(/-\s*(\d+)h/);
            if (cargaTxt && document.getElementById('disciplina-carga')) {
                document.getElementById('disciplina-carga').textContent = `${cargaTxt[1]}h`;
            }
            verificarConflitos();
        } else {
            disciplinaInfo.classList.add('hidden');
        }
    });

    // Verificar conflitos
    function verificarConflitos() {
        const turmaId = turmaSelect.value;
        const disciplinaId = disciplinaSelect.value;
        const professorId = professorSelect.value;

        if (turmaId && disciplinaId) {
            fetch('/api/verificar-conflitos-planejamento', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    turma_id: turmaId,
                    disciplina_id: disciplinaId,
                    professor_id: professorId,
                    planejamento_id: {{ $planejamento->id ?? 'null' }}
                })
            })
            .then(response => response.json())
            .then(data => {
                const messagesDiv = document.getElementById('conflitos-messages');
                messagesDiv.innerHTML = '';

                if (data.conflitos && data.conflitos.length > 0) {
                    data.conflitos.forEach(conflito => {
                        const p = document.createElement('p');
                        p.textContent = '• ' + conflito;
                        messagesDiv.appendChild(p);
                    });
                    conflitosCheck.classList.remove('hidden');
                } else {
                    conflitosCheck.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Erro ao verificar conflitos:', error);
            });
        }
    }

    // Verificar conflitos quando professor mudar
    professorSelect.addEventListener('change', function() {
        try {
            window.planejamentoWizard.formData[3].professor_id = this.value || '';
            window.wizardData.step3.professor_id = this.value || '';
        } catch (e) { console.warn('STEP-3: falha ao persistir professor_id', e); }
        verificarConflitos();
    });

    // Verificar se os campos já estão preenchidos
    if (turmaSelect.value) {
        turmaSelect.dispatchEvent(new Event('change'));
    }
    if (disciplinaSelect.value) {
        disciplinaSelect.dispatchEvent(new Event('change'));
    }

// Persistência inicial ao carregar etapa
try {
    if (turmaSelect && turmaSelect.value) {
        window.planejamentoWizard.formData[3].turma_id = turmaSelect.value;
        window.wizardData.step3.turma_id = turmaSelect.value;
    }
    if (disciplinaSelect && disciplinaSelect.value) {
        window.planejamentoWizard.formData[3].disciplina_id = disciplinaSelect.value;
        window.wizardData.step3.disciplina_id = disciplinaSelect.value;
    }
    if (professorSelect && professorSelect.value) {
        window.planejamentoWizard.formData[3].professor_id = professorSelect.value;
        window.wizardData.step3.professor_id = professorSelect.value;
    }
} catch (e) { console.warn('STEP-3: falha na persistência inicial', e); }

function toggleAlunosList() {
    const alunosList = document.getElementById('alunos-list');
    const turmaId = document.getElementById('turma_id').value;
    
    if (alunosList.classList.contains('hidden')) {
        if (turmaId && alunosList.innerHTML.trim() === '') {
            // Carregar lista de alunos
            fetch(`/api/turmas/${turmaId}/alunos`)
                .then(response => response.json())
                .then(data => {
                    const alunos = Array.isArray(data) ? data : (data.alunos || []);
                    if (alunos.length > 0) {
                        alunosList.innerHTML = alunos.map(aluno => 
                            `<div class="text-xs text-gray-600 py-1">${aluno.nome}</div>`
                        ).join('');
                    } else {
                        alunosList.innerHTML = '<div class="text-xs text-gray-500">Nenhum aluno matriculado</div>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar alunos:', error);
                    alunosList.innerHTML = '<div class="text-xs text-red-500">Erro ao carregar alunos</div>';
                });
        }
        alunosList.classList.remove('hidden');
    } else {
        alunosList.classList.add('hidden');
    }
}

</script>