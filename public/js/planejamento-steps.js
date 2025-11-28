/**
 * JavaScript para gerenciar as etapas sequenciais do planejamento de aula
 * Funcionalidades:
 * - Navegação entre etapas com validação
 * - Carregamento dinâmico de salas e tipos de professor
 * - Cálculo automático de data fim
 * - Validação de formulário
 */

class PlanejamentoSteps {
    constructor(isEditMode = false) {
        this.currentStep = 1;
        this.totalSteps = 10;
        this.isEditMode = isEditMode;
        
        this.initializeElements();
        this.bindEvents();
        this.initializeForm();
    }
    
    initializeElements() {
        // Botões de navegação
        this.prevBtn = document.getElementById('btn-anterior');
        this.nextBtn = document.getElementById('btn-proximo');
        this.submitBtn = document.getElementById('btn-salvar');
        
        // Elementos de progresso
        this.progressBar = document.getElementById('progressBar');
        this.stepCounter = document.getElementById('stepCounter');
        this.optionalSections = document.getElementById('optional-sections');
        
        // Campos do formulário
        this.modalidadeSelect = document.getElementById('modalidade');
        this.turnoSelect = document.getElementById('turno'); // Input hidden
        this.turnoRadios = document.querySelectorAll('input[name="turno"]'); // Radio buttons
        this.nivelEnsinoSelect = document.getElementById('nivel_ensino_id');
        this.turmaSelect = document.getElementById('turma_id');
        this.tipoProfessorSelect = document.getElementById('tipo_professor');
        this.numeroDiasSelect = document.getElementById('numero_dias');
        this.dataInicioInput = document.getElementById('data_inicio');
        this.dataFimDisplay = document.getElementById('data_fim_display');
        this.dataFimText = document.getElementById('data_fim_text');
        this.grupoSelect = document.getElementById('grupo_id');
        this.disciplinaSelect = document.getElementById('disciplina_id');
        this.professorSelect = document.getElementById('professor_id');
        
        // Verificar se é modo de edição
        this.isEditMode = document.querySelector('form').action.includes('/planejamentos/') && 
                         document.querySelector('input[name="_method"][value="PUT"]') !== null;
    }
    
    bindEvents() {
        // Navegação
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.nextStep());
        }
        
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.prevStep());
        }
        
        // Carregamento dinâmico
        if (this.modalidadeSelect) {
            this.modalidadeSelect.addEventListener('change', () => this.onModalidadeChange());
        }
        
        // Vincular eventos aos radio buttons de turno
        if (this.turnoRadios && this.turnoRadios.length > 0) {
            this.turnoRadios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (radio.checked) {
                        // Atualizar o input hidden
                        if (this.turnoSelect) {
                            this.turnoSelect.value = radio.value;
                        }
                        // Chamar o método de mudança de turno
                        this.onTurnoChange();
                    }
                });
            });
        }
        
        if (this.nivelEnsinoSelect) {
            this.nivelEnsinoSelect.addEventListener('change', () => this.onNivelEnsinoChange());
        }
        
        if (this.grupoSelect) {
            this.grupoSelect.addEventListener('change', () => this.onGrupoChange());
        }
        
        if (this.disciplinaSelect) {
            this.disciplinaSelect.addEventListener('change', () => this.onDisciplinaChange());
        }
        
        if (this.professorSelect) {
            this.professorSelect.addEventListener('change', () => this.onProfessorChange());
        }
        
        // Cálculo de data fim
        if (this.numeroDiasSelect) {
            this.numeroDiasSelect.addEventListener('change', () => this.calculateDataFim());
        }
        
        if (this.dataInicioInput) {
            this.dataInicioInput.addEventListener('change', () => this.calculateDataFim());
        }
        
        // Validação em tempo real
        this.bindRealTimeValidation();
    }
    
    bindRealTimeValidation() {
        const requiredFields = [
            this.modalidadeSelect,
            this.turnoSelect,
            this.nivelEnsinoSelect,
            this.grupoSelect,
            this.turmaSelect,
            this.disciplinaSelect,
            this.professorSelect,
            this.numeroDiasSelect,
            this.dataInicioInput
        ];
        
        requiredFields.forEach(field => {
            if (field) {
                field.addEventListener('change', () => this.clearFieldError(field));
                field.addEventListener('input', () => this.clearFieldError(field));
            }
        });
    }
    
    initializeForm() {
        // Se for modo de edição, carregar dados existentes
        if (this.isEditMode) {
            this.loadExistingData();
        }
        
        // Mostrar primeira etapa
        this.showStep(1);
        
        // Calcular data fim se já houver dados
        this.calculateDataFim();
    }
    
    validateGrupo() {
        return {
            isValid: this.grupoSelect.value !== '',
            message: 'Por favor, selecione o grupo educacional.',
            field: this.grupoSelect
        };
    }
    
    validateDisciplina() {
        return {
            isValid: this.disciplinaSelect.value !== '',
            message: 'Por favor, selecione a disciplina.',
            field: this.disciplinaSelect
        };
    }
    
    validateProfessor() {
        return {
            isValid: this.professorSelect.value !== '',
            message: 'Por favor, selecione o professor.',
            field: this.professorSelect
        };
    }
    
    validatePeriodo() {
        const numeroDiasValid = this.numeroDiasSelect.value !== '';
        const dataInicioValid = this.dataInicioInput.value !== '';
        
        if (!numeroDiasValid) {
            return {
                isValid: false,
                message: 'Por favor, selecione o número de dias.',
                field: this.numeroDiasSelect
            };
        }
        
        if (!dataInicioValid) {
            return {
                isValid: false,
                message: 'Por favor, selecione a data de início.',
                field: this.dataInicioInput
            };
        }
        
        return { isValid: true };
    }
    
    loadExistingData(data = null) {
        if (data && this.isEditMode) {
            // Pré-preencher campos com dados existentes
            if (data.modalidade && this.modalidadeSelect) {
                this.modalidadeSelect.value = data.modalidade;
            }
            if (data.turno) {
                // Atualizar input hidden
                if (this.turnoSelect) {
                    this.turnoSelect.value = data.turno;
                }
                // Marcar o radio button correspondente nos novos cards dinâmicos
                setTimeout(() => {
                    const turnoRadios = document.querySelectorAll('input[name="turno_radio"]');
                    turnoRadios.forEach(radio => {
                        radio.checked = radio.value === data.turno;
                    });
                }, 500); // Aguardar carregamento dos cards
            }
            if (data.turma_id && this.turmaSelect) {
                this.turmaSelect.value = data.turma_id;
            }
            if (data.numero_dias && this.numeroDiasSelect) {
                this.numeroDiasSelect.value = data.numero_dias;
            }
            if (data.data_inicio && this.dataInicioInput) {
                this.dataInicioInput.value = data.data_inicio;
            }
            if (data.titulo) {
                const tituloInput = document.getElementById('titulo');
                if (tituloInput) tituloInput.value = data.titulo;
            }
            if (data.objetivo_geral) {
                const objetivoInput = document.getElementById('objetivo_geral');
                if (objetivoInput) objetivoInput.value = data.objetivo_geral;
            }
        }
        
        // Carregar níveis de ensino se modalidade e turno já estão selecionados
        if (this.modalidadeSelect.value && this.turnoSelect.value && this.nivelEnsinoSelect) {
            const selectedNivelId = this.nivelEnsinoSelect.dataset.selected || this.nivelEnsinoSelect.value;
            this.loadNiveisEnsino(this.modalidadeSelect.value, this.turnoSelect.value, selectedNivelId);
        }
        
        // Carregar turmas se nível de ensino e turno já estão selecionados
        if (this.nivelEnsinoSelect && this.nivelEnsinoSelect.value && this.turnoSelect.value && this.turmaSelect) {
            const selectedTurmaId = this.turmaSelect.dataset.selected || this.turmaSelect.value;
            this.loadTurmas(this.nivelEnsinoSelect.value, this.turnoSelect.value, selectedTurmaId);
        }
        // Carregar tipos de professor se modalidade já está selecionada
        if (this.modalidadeSelect.value) {
            const selectedTipo = this.tipoProfessorSelect.dataset.selected || this.tipoProfessorSelect.value;
            this.loadTiposProfessor(this.modalidadeSelect.value, selectedTipo);
        }
    }
    
    async loadTurmasPorGrupoTurno(grupoId, turnoId, selectedTurmaId = null) {
        try {
            const response = await fetch(`/planejamentos/turmas-por-grupo-turno?grupo_id=${grupoId}&turno_id=${turnoId}`);
            const data = await response.json();
            
            this.turmaSelect.innerHTML = '<option value="">Selecione a Turma</option>';
            data.forEach(turma => {
                const option = document.createElement('option');
                option.value = turma.id;
                option.textContent = turma.nome;
                if (selectedTurmaId && turma.id == selectedTurmaId) {
                    option.selected = true;
                }
                this.turmaSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Erro ao carregar turmas:', error);
            this.turmaSelect.innerHTML = '<option value="">Erro ao carregar turmas</option>';
        }
    }
    
    async loadDisciplinasPorTurma(turmaId, selectedDisciplinaId = null) {
        try {
            // Corrigindo a URL para usar o endpoint correto
            const response = await fetch(`/planejamentos/get-disciplinas-por-turma?turma_id=${turmaId}`);
            const data = await response.json();
            
            console.log('Disciplinas recebidas:', data);
            
            this.disciplinaSelect.innerHTML = '<option value="">Selecione a Disciplina</option>';
            data.forEach(disciplina => {
                const option = document.createElement('option');
                option.value = disciplina.id;
                option.textContent = disciplina.nome;
                if (selectedDisciplinaId && disciplina.id == selectedDisciplinaId) {
                    option.selected = true;
                }
                this.disciplinaSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Erro ao carregar disciplinas:', error);
            this.disciplinaSelect.innerHTML = '<option value="">Erro ao carregar disciplinas</option>';
        }
    }
    
    async loadProfessoresPorTurmaDisciplina(turmaId, disciplinaId, selectedProfessorId = null) {
        try {
            const response = await fetch(`/planejamentos/professores-por-turma-disciplina?turma_id=${turmaId}&disciplina_id=${disciplinaId}`);
            const data = await response.json();
            
            this.professorSelect.innerHTML = '<option value="">Selecione o Professor</option>';
            data.forEach(professor => {
                const option = document.createElement('option');
                option.value = professor.id;
                option.textContent = professor.nome;
                if (selectedProfessorId && professor.id == selectedProfessorId) {
                    option.selected = true;
                }
                this.professorSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Erro ao carregar professores:', error);
            this.professorSelect.innerHTML = '<option value="">Erro ao carregar professores</option>';
        }
    }
    
    async loadUltimoPeriodoPlanejamento(turmaId, disciplinaId, professorId) {
        try {
            const response = await fetch(`/planejamentos/ultimo-periodo-planejamento?turma_id=${turmaId}&disciplina_id=${disciplinaId}&professor_id=${professorId}`);
            const data = await response.json();
            
            if (data.ultimo_periodo) {
                this.dataInicioInput.value = data.ultimo_periodo;
                this.calculateDataFim();
            } else {
                this.dataInicioInput.value = '';
                this.dataFimDisplay.textContent = '';
                this.dataFimText.value = '';
            }
        } catch (error) {
            console.error('Erro ao carregar último período de planejamento:', error);
            this.dataInicioInput.value = '';
            this.dataFimDisplay.textContent = '';
            this.dataFimText.value = '';
        }
    }
    
    onGrupoChange() {
        const grupoId = this.grupoSelect.value;
        const turnoId = this.turnoSelect.value;
        if (grupoId && turnoId) {
            this.loadTurmasPorGrupoTurno(grupoId, turnoId);
            this.loadDisciplinasPorTurma(grupoId); // Assumindo que disciplina depende do grupo
        } else {
            this.turmaSelect.innerHTML = '<option value="">Selecione a Turma</option>';
            this.disciplinaSelect.innerHTML = '<option value="">Selecione a Disciplina</option>';
        }
    }
    
    onDisciplinaChange() {
        const turmaId = this.turmaSelect.value;
        const disciplinaId = this.disciplinaSelect.value;
        if (turmaId && disciplinaId) {
            this.loadProfessoresPorTurmaDisciplina(turmaId, disciplinaId);
        } else {
            this.professorSelect.innerHTML = '<option value="">Selecione o Professor</option>';
        }
    }
    
    onProfessorChange() {
        const turmaId = this.turmaSelect.value;
        const disciplinaId = this.disciplinaSelect.value;
        const professorId = this.professorSelect.value;
        if (turmaId && disciplinaId && professorId) {
            this.loadUltimoPeriodoPlanejamento(turmaId, disciplinaId, professorId);
        } else {
            this.dataInicioInput.value = '';
            this.dataFimDisplay.textContent = '';
            this.dataFimText.value = '';
        }
    }
    
    showStep(step) {
        // Validar step
        if (step < 1 || step > this.totalSteps) {
            return;
        }
        
        // Mapeamento dos IDs das etapas
        const stepIds = {
            1: 'etapa-modalidade',
            2: 'etapa-unidade', 
            3: 'etapa-turno',
            4: 'etapa-nivel-ensino',
            5: 'etapa-turma',
            6: 'etapa-grupo',
            7: 'etapa-disciplina',
            8: 'etapa-professor',
            9: 'etapa-periodo',
            10: 'etapa-informacoes'
        };
        
        // Esconder todas as etapas
        for (let i = 1; i <= this.totalSteps; i++) {
            const stepElement = document.getElementById(stepIds[i]);
            if (stepElement) {
                stepElement.classList.add('hidden');
            }
        }
        
        // Mostrar etapa atual
        const currentStepElement = document.getElementById(stepIds[step]);
        if (currentStepElement) {
            currentStepElement.classList.remove('hidden');
        }
        
        // Atualizar progress bar
        const progress = (step / this.totalSteps) * 100;
        if (this.progressBar) {
            this.progressBar.style.width = progress + '%';
        }
        
        if (this.stepCounter) {
            this.stepCounter.textContent = `Etapa ${step} de ${this.totalSteps}`;
        }
        
        // Atualizar step labels
        this.updateStepLabels(step);
        
        // Atualizar stepper visual
        this.updateStepperVisual(step);
        
        // Controlar botões
        this.updateNavigationButtons(step);
        
        // Focar no primeiro campo da etapa
        this.focusFirstField(step);
        
        this.currentStep = step;
    }
    
    updateStepLabels(currentStep) {
        const stepLabels = document.querySelectorAll('.step-label');
        stepLabels.forEach((label, index) => {
            label.classList.remove('active', 'completed');
            
            if (index < currentStep - 1) {
                label.classList.add('completed');
            } else if (index === currentStep - 1) {
                label.classList.add('active');
            }
        });
    }

    updateStepperVisual(currentStep) {
        // Títulos das etapas para mobile
        const stepTitles = {
            1: 'Modalidade da Educação Básica',
            2: 'Unidade Escolar',
            3: 'Turno',
            4: 'Nível de Ensino',
            5: 'Turma',
            6: 'Tipo de Professor',
            7: 'Período do Planejamento',
            8: 'Informações Adicionais'
        };

        // Atualizar stepper desktop
        for (let i = 1; i <= 7; i++) { // Apenas 7 etapas no stepper visual
            const stepIndicator = document.getElementById(`step-indicator-${i}`);
            const stepCircle = stepIndicator?.querySelector('.step-circle');
            const stepTitle = stepIndicator?.querySelector('.step-title');
            const stepDescription = stepIndicator?.querySelector('.step-description');
            const connector = document.getElementById(`connector-${i}`);

            if (stepCircle && stepTitle && stepDescription) {
                // Reset classes
                stepCircle.className = 'flex items-center justify-center w-10 h-10 rounded-full font-semibold text-sm step-circle';
                stepTitle.className = 'text-sm font-medium step-title';
                stepDescription.className = 'text-xs step-description';

                if (i < currentStep) {
                    // Etapa completada
                    stepCircle.classList.add('bg-green-600', 'text-white');
                    stepTitle.classList.add('text-gray-900');
                    stepDescription.classList.add('text-gray-500');
                    stepCircle.innerHTML = '<i class="fas fa-check"></i>';
                } else if (i === currentStep) {
                    // Etapa atual
                    stepCircle.classList.add('bg-indigo-600', 'text-white');
                    stepTitle.classList.add('text-gray-900');
                    stepDescription.classList.add('text-gray-500');
                    stepCircle.textContent = i;
                } else {
                    // Etapa futura
                    stepCircle.classList.add('bg-gray-300', 'text-gray-600');
                    stepTitle.classList.add('text-gray-500');
                    stepDescription.classList.add('text-gray-400');
                    stepCircle.textContent = i;
                }
            }

            // Atualizar conectores
            if (connector) {
                connector.className = 'h-0.5 step-connector';
                if (i < currentStep) {
                    connector.classList.add('bg-green-600');
                } else {
                    connector.classList.add('bg-gray-300');
                }
            }
        }

        // Atualizar stepper mobile
        const currentStepMobile = document.getElementById('current-step-mobile');
        const currentStepTitleMobile = document.getElementById('current-step-title-mobile');
        const progressBarMobile = document.getElementById('progress-bar-mobile');

        if (currentStepMobile) {
            currentStepMobile.textContent = `Etapa ${currentStep} de 7`;
        }

        if (currentStepTitleMobile) {
            currentStepTitleMobile.textContent = stepTitles[currentStep] || '';
        }

        if (progressBarMobile) {
            const progress = (currentStep / 7) * 100; // 7 etapas principais
            progressBarMobile.style.width = `${progress}%`;
        }
    }
    
    updateNavigationButtons(step) {
        if (this.prevBtn) {
            this.prevBtn.classList.toggle('hidden', step === 1);
        }
        
        if (step === this.totalSteps) {
            if (this.nextBtn) this.nextBtn.classList.add('hidden');
            if (this.submitBtn) this.submitBtn.classList.remove('hidden');
            if (this.optionalSections) this.optionalSections.classList.remove('hidden');
        } else {
            if (this.nextBtn) this.nextBtn.classList.remove('hidden');
            if (this.submitBtn) this.submitBtn.classList.add('hidden');
            if (this.optionalSections) this.optionalSections.classList.add('hidden');
        }
    }
    
    focusFirstField(step) {
        setTimeout(() => {
            const stepElement = document.getElementById(`step${step}`);
            if (stepElement) {
                const firstInput = stepElement.querySelector('select, input, textarea');
                if (firstInput && !firstInput.readOnly) {
                    firstInput.focus();
                }
            }
        }, 100);
    }
    
    nextStep() {
        if (this.validateStep(this.currentStep)) {
            if (this.currentStep < this.totalSteps) {
                this.showStep(this.currentStep + 1);
            }
        }
    }
    
    prevStep() {
        if (this.currentStep > 1) {
            this.showStep(this.currentStep - 1);
        }
    }
    
    validateStep(step) {
        const validation = this.getStepValidation(step);
        
        if (!validation.isValid) {
            this.showValidationError(validation.message, validation.field);
            return false;
        }
        
        return true;
    }
    
    getStepValidation(step) {
        switch(step) {
            case 1:
                return {
                    isValid: this.modalidadeSelect.value !== '',
                    message: 'Por favor, selecione a modalidade de ensino.',
                    field: this.modalidadeSelect
                };
            case 2:
                return { isValid: true }; // Unidade escolar é readonly
            case 3:
                // Verificar se algum radio button de turno está selecionado (novos cards dinâmicos)
                const turnoRadios = document.querySelectorAll('input[name="turno_radio"]');
                const turnoSelecionado = turnoRadios.length > 0 ? 
                    Array.from(turnoRadios).some(radio => radio.checked) : 
                    (this.turnoSelect && this.turnoSelect.value !== '');
                
                return {
                    isValid: turnoSelecionado,
                    message: 'Por favor, selecione o turno.',
                    field: turnoRadios.length > 0 ? turnoRadios[0] : this.turnoSelect
                };
            case 4:
                return this.validateNivelEnsino();
            case 5:
                return this.validateTurma();
            case 6:
                return this.validateTipoProfessor();
            case 7:
                return this.validateGrupo();
            case 8:
                return this.validateDisciplina();
            case 9:
                return this.validateProfessor();
            case 10:
                return this.validatePeriodo();
            default:
                return { isValid: true };
        }
    }
    
    showValidationError(message, field = null) {
        // Mostrar erro visual no campo
        if (field) {
            this.highlightFieldError(field);
        }
        
        // Mostrar notificação
        this.showNotification(message, 'error');
    }
    
    highlightFieldError(field) {
        field.classList.add('border-red-500', 'ring-red-500');
        field.classList.remove('border-gray-300');
        
        // Remover destaque após alguns segundos
        setTimeout(() => {
            this.clearFieldError(field);
        }, 3000);
    }
    
    clearFieldError(field) {
        field.classList.remove('border-red-500', 'ring-red-500');
        field.classList.add('border-gray-300');
    }
    
    clearTiposProfessor() {
        if (this.tipoProfessorSelect) {
            this.tipoProfessorSelect.innerHTML = '<option value="">Selecione o tipo de professor...</option>';
            this.tipoProfessorSelect.value = '';
        }
    }

    showNotification(message, type = 'info') {
        // Criar elemento de notificação
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;
        
        // Definir cores baseado no tipo
        switch(type) {
            case 'error':
                notification.classList.add('bg-red-100', 'border-red-500', 'text-red-700');
                break;
            case 'success':
                notification.classList.add('bg-green-100', 'border-green-500', 'text-green-700');
                break;
            default:
                notification.classList.add('bg-blue-100', 'border-blue-500', 'text-blue-700');
        }
        
        notification.innerHTML = `
            <div class="flex items-center">
                <div class="flex-1">
                    <p class="text-sm font-medium">${message}</p>
                </div>
                <button class="ml-3 text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animar entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Remover automaticamente após 5 segundos
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }, 5000);
    }
    
    onModalidadeChange() {
        console.log('🎯 Modalidade selecionada:', this.modalidadeSelect ? this.modalidadeSelect.value : null, 
                   'Texto:', this.modalidadeSelect ? this.modalidadeSelect.options[this.modalidadeSelect.selectedIndex]?.text : null);
        
        this.clearTurnos();
        this.clearTiposProfessor();
        
        const modalidade = this.modalidadeSelect ? this.modalidadeSelect.value : null;
        
        // Limpar seleção de turno
        if (this.turnoSelect) {
            this.turnoSelect.value = '';
        }
        
        if (modalidade) {
            // Carregar tipos de professor
            this.loadTiposProfessor(modalidade);
            
            // Carregar turnos disponíveis
            this.loadTurnosDisponiveis(modalidade);
            
            // Avançar automaticamente para a próxima etapa
            if (this.currentStep === 1) {
                setTimeout(() => {
                    this.nextStep();
                }, 300); // Pequeno delay para melhor UX
            }
        }
    }
    
    onTurnoChange() {
        // Obter o valor do turno selecionado (novos radio buttons dinâmicos ou input hidden)
        let turno = this.turnoSelect ? this.turnoSelect.value : '';
        // Se não houver valor no input hidden, verificar os novos radio buttons dinâmicos
        if (!turno) {
            const turnoRadios = document.querySelectorAll('input[name="turno_radio"]');
            const selectedRadio = Array.from(turnoRadios).find(radio => radio.checked);
            turno = selectedRadio ? selectedRadio.value : '';
        }
        
        // Limpar níveis de ensino
        this.clearSelect(this.nivelEnsinoSelect, 'Selecione o nível de ensino...');
        // Limpar grupos educacionais
        this.clearSelect(this.grupoSelect, 'Selecione o grupo educacional...');
        
        if (this.modalidadeSelect.value && turno) {
            this.loadNiveisEnsino(this.modalidadeSelect.value, turno);
            this.loadGruposPorModalidadeTurno(this.modalidadeSelect.value, turno);
        }
    }
    
    onNivelEnsinoChange() {
        const nivelEnsinoId = this.nivelEnsinoSelect.value;
        
        // Limpar turmas
        this.clearSelect(this.turmaSelect, 'Selecione a turma...');
        
        if (nivelEnsinoId && this.turnoSelect.value) {
            this.loadTurmas(nivelEnsinoId, this.turnoSelect.value);
        }
    }
    
    clearSelect(selectElement, placeholder) {
        if (selectElement) {
            selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        }
    }
    
    async loadTurmas(nivelEnsinoId, turno, selectedTurmaId = null) {
        try {
            this.showLoadingState(this.turmaSelect);
            
            const anoLetivo = new Date().getFullYear();
            const response = await fetch(`/planejamentos/turmas-by-nivel-ensino?nivel_ensino_id=${encodeURIComponent(nivelEnsinoId)}&turno=${encodeURIComponent(turno)}&ano_letivo=${anoLetivo}`);
            
            if (!response.ok) {
                throw new Error('Erro ao carregar turmas');
            }
            
            const data = await response.json();
            
            this.clearSelect(this.turmaSelect, 'Selecione a turma...');
            
            data.forEach(turma => {
                const option = document.createElement('option');
                option.value = turma.id;
                option.textContent = turma.text || `${turma.codigo} - ${turma.nome}`;
                
                if (selectedTurmaId && turma.id == selectedTurmaId) {
                    option.selected = true;
                }
                
                this.turmaSelect.appendChild(option);
            });
            
            this.hideLoadingState(this.turmaSelect);
            
        } catch (error) {
            console.error('Erro ao carregar turmas:', error);
            this.hideLoadingState(this.turmaSelect);
            this.showNotification('Erro ao carregar turmas. Tente novamente.', 'error');
        }
    }
    
    async loadNiveisEnsino(modalidade, turno, selectedNivelId = null) {
        try {
            this.showLoadingState(this.nivelEnsinoSelect);
            
            const response = await fetch(`/api/planejamentos/niveis-por-modalidade/${encodeURIComponent(modalidade)}`);
            
            if (!response.ok) {
                throw new Error('Erro ao carregar níveis de ensino');
            }
            
            const result = await response.json();
            
            this.clearSelect(this.nivelEnsinoSelect, 'Selecione o nível de ensino...');
            
            // O endpoint retorna um objeto com niveis_bncc e niveis_personalizados
            const todosNiveis = [];
            
            // Adicionar níveis BNCC
            if (result.niveis_bncc && result.niveis_bncc.length > 0) {
                result.niveis_bncc.forEach(nivel => {
                    todosNiveis.push({
                        id: nivel.id,
                        nome: `${nivel.nome} (BNCC)`,
                        tipo: 'bncc'
                    });
                });
            }
            
            // Adicionar níveis personalizados
            if (result.niveis_personalizados && result.niveis_personalizados.length > 0) {
                result.niveis_personalizados.forEach(nivel => {
                    todosNiveis.push({
                        id: nivel.id,
                        nome: `${nivel.nome} (Personalizado)`,
                        tipo: 'personalizado'
                    });
                });
            }
            
            // Adicionar opções ao select
            todosNiveis.forEach(nivel => {
                const option = document.createElement('option');
                option.value = nivel.id;
                option.textContent = nivel.nome;
                
                if (selectedNivelId && nivel.id == selectedNivelId) {
                    option.selected = true;
                }
                
                this.nivelEnsinoSelect.appendChild(option);
            });
            
            this.hideLoadingState(this.nivelEnsinoSelect);
            
        } catch (error) {
            console.error('Erro ao carregar níveis de ensino:', error);
            this.hideLoadingState(this.nivelEnsinoSelect);
            this.showNotification('Erro ao carregar níveis de ensino. Tente novamente.', 'error');
        }
    }

    async loadGruposPorModalidadeTurno(modalidade, turno, selectedGrupoId = null) {
        try {
            if (!this.grupoSelect) return;
            
            this.showLoadingState(this.grupoSelect);
            
            const response = await fetch(`/planejamentos/get-grupos-por-modalidade-turno?modalidade_id=${encodeURIComponent(modalidade)}&turno_id=${encodeURIComponent(turno)}`);
            
            if (!response.ok) {
                throw new Error('Erro ao carregar grupos');
            }
            
            const data = await response.json();
            
            this.clearSelect(this.grupoSelect, 'Selecione o grupo...');
            
            data.forEach(grupo => {
                const option = document.createElement('option');
                option.value = grupo.id;
                option.textContent = grupo.nome;
                
                if (selectedGrupoId && grupo.id == selectedGrupoId) {
                    option.selected = true;
                }
                
                this.grupoSelect.appendChild(option);
            });
            
            this.hideLoadingState(this.grupoSelect);
            
        } catch (error) {
            console.error('Erro ao carregar grupos:', error);
            this.hideLoadingState(this.grupoSelect);
            this.showNotification('Erro ao carregar grupos. Tente novamente.', 'error');
        }
    }
    
    async loadTurnosDisponiveis(modalidade) {
        const loadingElement = document.getElementById('turnos-loading');
        const containerElement = document.getElementById('turnos-container');
        const emptyElement = document.getElementById('turnos-empty');
        
        if (!loadingElement || !containerElement || !emptyElement) {
            return;
        }
        
        // Mostrar loading
        loadingElement.classList.remove('hidden');
        containerElement.classList.add('hidden');
        emptyElement.classList.add('hidden');
        
        try {
            const url = `/planejamentos/turnos-disponiveis?modalidade=${encodeURIComponent(modalidade)}`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Erro na resposta do servidor:', response.status, response.statusText, errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const turnos = await response.json();
            console.log('Turnos recebidos:', turnos);
            
            // Limpar container
            containerElement.innerHTML = '';
            
            if (turnos.length === 0) {
                // Mostrar mensagem de vazio
                loadingElement.classList.add('hidden');
                emptyElement.classList.remove('hidden');
            } else {
                // Renderizar cards de turno
                this.renderTurnoCards(turnos, containerElement);
                
                // Mostrar container
                loadingElement.classList.add('hidden');
                containerElement.classList.remove('hidden');
            }
            
        } catch (error) {
            console.error('Erro ao carregar turnos disponíveis:', error);
            this.showNotification('Erro ao carregar turnos disponíveis', 'error');
            
            // Mostrar mensagem de erro
            loadingElement.classList.add('hidden');
            emptyElement.classList.remove('hidden');
        }
    }
    
    renderTurnoCards(turnos, container) {
        
        turnos.forEach(turno => {
            const cardHtml = `
                <div class="turno-card relative">
                    <input type="radio" name="turno_radio" id="turno_${turno.value}" value="${turno.value}" class="sr-only peer">
                    <label for="turno_${turno.value}" class="block p-6 bg-white border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:shadow-md peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:shadow-lg transition-all duration-200 group">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center group-hover:from-blue-600 group-hover:to-blue-700 peer-checked:from-green-500 peer-checked:to-green-600 transition-all duration-200">
                                <i class="fas fa-clock text-white text-lg"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">${turno.label}</h3>
                            <p class="text-sm text-gray-600">Disponível</p>
                        </div>
                        <div class="absolute top-3 right-3 opacity-0 peer-checked:opacity-100 transition-opacity duration-200">
                            <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-xs"></i>
                            </div>
                        </div>
                    </label>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', cardHtml);
        });
        
        // Adicionar event listeners aos novos radio buttons
        const radioButtons = container.querySelectorAll('input[name="turno_radio"]');
        
        radioButtons.forEach((radio) => {
            radio.addEventListener('change', (e) => {
                if (e.target.checked) {
                    // Atualizar input hidden
                    if (this.turnoSelect) {
                        this.turnoSelect.value = e.target.value;
                    }
                    
                    // Chamar método de mudança de turno
                    this.onTurnoChange();
                }
            });
        });
    }
    
    clearTurnos() {
        const loadingElement = document.getElementById('turnos-loading');
        const containerElement = document.getElementById('turnos-container');
        const emptyElement = document.getElementById('turnos-empty');
        
        if (loadingElement) loadingElement.classList.add('hidden');
        if (containerElement) {
            containerElement.classList.add('hidden');
            containerElement.innerHTML = '';
        }
        if (emptyElement) emptyElement.classList.add('hidden');
    }
    
    async loadTiposProfessor(modalidade, selectedTipo = null) {
        try {
            const container = document.getElementById('tipos-professor-container');
            if (!container) {
                return;
            }
            
            // Mostrar loading
            container.innerHTML = '<div class="col-span-full text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Carregando tipos de professor...</div>';
            
            const response = await fetch(`/planejamentos/tipos-professor-by-modalidade?modalidade=${encodeURIComponent(modalidade)}`);
            
            if (!response.ok) {
                throw new Error('Erro ao carregar tipos de professor');
            }
            
            const data = await response.json();
            
            // Limpar container
            container.innerHTML = '';
            
            // Criar radio buttons para cada tipo
            data.forEach(tipo => {
                const radioDiv = document.createElement('div');
                radioDiv.className = 'relative';
                
                const radioInput = document.createElement('input');
                radioInput.type = 'radio';
                radioInput.name = 'tipo_professor_radio';
                radioInput.id = `tipo_professor_${tipo.value}`;
                radioInput.value = tipo.value;
                radioInput.className = 'sr-only peer';
                
                if (selectedTipo && tipo.value === selectedTipo) {
                    radioInput.checked = true;
                    this.tipoProfessorSelect.value = tipo.value;
                }
                
                const label = document.createElement('label');
                label.htmlFor = `tipo_professor_${tipo.value}`;
                label.className = 'flex items-center justify-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 peer-checked:border-blue-600 peer-checked:bg-blue-50 transition-all';
                label.innerHTML = `<div class="text-center"><div class="text-sm font-medium text-gray-900">${tipo.label}</div></div>`;
                
                // Adicionar evento de mudança
                radioInput.addEventListener('change', () => {
                    if (radioInput.checked) {
                        this.tipoProfessorSelect.value = tipo.value;
                    }
                });
                
                radioDiv.appendChild(radioInput);
                radioDiv.appendChild(label);
                container.appendChild(radioDiv);
            });
            
        } catch (error) {
            const container = document.getElementById('tipos-professor-container');
            if (container) {
                container.innerHTML = '<div class="col-span-full text-center py-4 text-red-600"><i class="fas fa-exclamation-triangle mr-2"></i>Erro ao carregar tipos de professor</div>';
            }
            this.showNotification('Erro ao carregar tipos de professor. Tente novamente.', 'error');
        }
    }
    
    showLoadingState(selectElement) {
        if (selectElement) {
            selectElement.disabled = true;
            selectElement.innerHTML = '<option value="">Carregando...</option>';
        }
    }
    
    hideLoadingState(selectElement) {
        if (selectElement) {
            selectElement.disabled = false;
        }
    }
    
    calculateDataFim() {
        const numeroDias = parseInt(this.numeroDiasSelect.value);
        const dataInicioValue = this.dataInicioInput.value;
        
        if (numeroDias && dataInicioValue) {
            const dataInicio = new Date(dataInicioValue);
            const dataFim = new Date(dataInicio);
            
            // Adicionar dias (subtraindo 1 porque o primeiro dia conta)
            dataFim.setDate(dataFim.getDate() + numeroDias - 1);
            
            // Formatar data para exibição
            const dataFimFormatted = dataFim.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            
            if (this.dataFimText) {
                this.dataFimText.textContent = dataFimFormatted;
            }
            
            if (this.dataFimDisplay) {
                this.dataFimDisplay.classList.remove('hidden');
            }
        } else {
            if (this.dataFimDisplay) {
                this.dataFimDisplay.classList.add('hidden');
            }
        }
    }
    
    // Método público para validar todo o formulário
    validateForm() {
        for (let step = 1; step <= 4; step++) {
            const validation = this.getStepValidation(step);
            if (!validation.isValid) {
                this.showStep(step);
                this.showValidationError(validation.message, validation.field);
                return false;
            }
        }
        return true;
    }

    validateNivelEnsino() {
        const nivelEnsino = this.nivelEnsinoSelect ? this.nivelEnsinoSelect.value : null;
        return {
            isValid: !!nivelEnsino,
            message: nivelEnsino ? '' : 'Por favor, selecione um nível de ensino.',
            field: 'nivel_ensino'
        };
    }

    validateTurma() {
        const turma = this.turmaSelect ? this.turmaSelect.value : null;
        return {
            isValid: !!turma,
            message: turma ? '' : 'Por favor, selecione uma turma.',
            field: 'turma'
        };
    }

    validateTipoProfessor() {
        const tipoProfessor = document.querySelector('input[name="tipo_professor"]:checked');
        return {
            isValid: !!tipoProfessor,
            message: tipoProfessor ? '' : 'Por favor, selecione um tipo de professor.',
            field: 'tipo_professor'
        };
    }
}

// Inicializar quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos na página de planejamento
    if (document.getElementById('planejamentoForm')) {
        window.planejamentoSteps = new PlanejamentoSteps();
        
        // Interceptar submit do formulário para validação final
        const form = document.getElementById('planejamentoForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (!window.planejamentoSteps.validateForm()) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    }
});