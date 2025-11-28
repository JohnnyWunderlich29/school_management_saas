(function() {
        'use strict';

        console.log('[Step 6] Inicializando Etapa 6 - Versão Reescrita');

        // Configuração global do wizard
        window.step6Config = {
            requiredFields: [
                'titulo', 'modalidade_ensino_id', 'nivel_ensino_id', 'escola_id',
                'turno_id', 'turma_id', 'disciplina_id', 'professor_id', 'tipo_periodo',
                'campos_experiencia', 'saberes_conhecimentos', 'objetivos_aprendizagem', 'aceitar_termos'
            ],
            validationErrors: []
        };

        function initStep6() {
            console.log('[Step 6] Função initStep6 executada - Nova versão');

            // Inicializar dados do wizard se não existir
            if (!window.wizardData) {
                window.wizardData = {};
            }

            // Transferir dados de etapas anteriores
            transferirDadosEtapasAnteriores();

            // Configurar validação em tempo real
            configurarValidacaoTempoReal();

            // Configurar botões
            configurarBotoes();

            // Carregar resumo automaticamente
            carregarResumo();

            // Configurar formulário
            configurarFormulario();

            // Validação inicial
            validarPlanejamento();
        }

        function transferirDadosEtapasAnteriores() {
            console.log('[Step 6] Transferindo dados de etapas anteriores');

            // Garantir que window.wizardData existe
            if (!window.wizardData) {
                window.wizardData = {};
            }

            // Transferir dados do planejamentoWizard se existir
            if (window.planejamentoWizard && window.planejamentoWizard.formData) {
                Object.keys(window.planejamentoWizard.formData).forEach(key => {
                    if (!window.wizardData[key]) {
                        window.wizardData[key] = window.planejamentoWizard.formData[key];
                    }
                });
            }

            // Criar campos ocultos para dados essenciais
            const camposEssenciais = [
                'titulo', 'modalidade_ensino_id', 'nivel_ensino_id', 'escola_id',
                'turno_id', 'turma_id', 'disciplina_id', 'professor_id', 'tipo_periodo'
            ];

            camposEssenciais.forEach(campo => {
                let valor = getWizardDataFromAllSteps(campo);
                if (valor) {
                    criarCampoOculto(campo, valor);
                }
            });

            console.log('[Step 6] Dados transferidos:', window.wizardData);
        }

        function getWizardDataFromAllSteps(campo) {
            // Buscar em window.wizardData.stepN e window.wizardData[N]
            for (let step = 1; step <= 6; step++) {
                const objStep = (window.wizardData && (window.wizardData[`step${step}`] || window.wizardData[step])) || null;
                if (objStep && objStep[campo] !== undefined && objStep[campo] !== null) {
                    return objStep[campo];
                }
            }
        
            // Buscar em window.planejamentoWizard.formData[N]
            if (window.planejamentoWizard && window.planejamentoWizard.formData) {
                const fd = window.planejamentoWizard.formData;
                // Iterar por chaves (podem ser números ou strings)
                Object.keys(fd).forEach(k => {
                    const v = fd[k];
                    if (!window.__foundValue && v && typeof v === 'object' && v[campo] !== undefined && v[campo] !== null) {
                        window.__foundValue = v[campo];
                    }
                });
                if (window.__foundValue !== undefined && window.__foundValue !== null) {
                    const val = window.__foundValue;
                    window.__foundValue = undefined;
                    return val;
                }
                if (fd[campo] !== undefined && fd[campo] !== null) {
                    return fd[campo];
                }
            }
        
            // Fallback direto
            if (window.wizardData && window.wizardData[campo] !== undefined && window.wizardData[campo] !== null) {
                return window.wizardData[campo];
            }
        
            return null;
        }

        function criarCampoOculto(nome, valor) {
            const form = document.getElementById('step-6-form');
            if (!form) return;

            // Verificar se o campo já existe
            let campo = form.querySelector(`input[name="${nome}"]`);
            if (!campo) {
                campo = document.createElement('input');
                campo.type = 'hidden';
                campo.name = nome;
                form.appendChild(campo);
            }
            campo.value = valor;
            console.log(`[Step 6] Campo oculto criado/atualizado: ${nome} = ${valor}`);
        }

        function configurarValidacaoTempoReal() {
            // Validação do checkbox de termos
            const checkboxTermos = document.getElementById('checkbox_termos');
            if (checkboxTermos) {
                checkboxTermos.addEventListener('change', function() {
                    validarTermos();
                    atualizarBotoesFinalizacao();
                });
            }

            // Validação de outros campos quando mudarem
            const form = document.getElementById('step-6-form');
            if (form) {
                form.addEventListener('input', function() {
                    setTimeout(validarPlanejamento, 100);
                });
            }
        }

        function validarTermos() {
            const checkboxTermos = document.getElementById('checkbox_termos');
            const erroTermos = document.getElementById('erro_termos');
            
            if (!checkboxTermos || !checkboxTermos.checked) {
                if (erroTermos) {
                    erroTermos.style.display = 'block';
                    erroTermos.textContent = 'É obrigatório aceitar os termos e condições para finalizar o planejamento.';
                }
                return false;
            } else {
                if (erroTermos) {
                    erroTermos.style.display = 'none';
                }
                return true;
            }
        }

        function validarNivelEnsino() {
            const nivelEnsinoId = getWizardDataFromAllSteps('nivel_ensino_id');
            
            if (!nivelEnsinoId) {
                console.error('[Validação] nivel_ensino_id não encontrado');
                return false;
            }

            // Validar se é um número inteiro válido
            const nivelNumero = parseInt(nivelEnsinoId, 10);
            if (isNaN(nivelNumero) || nivelNumero <= 0) {
                console.error('[Validação] nivel_ensino_id deve ser um número inteiro positivo:', nivelEnsinoId);
                return false;
            }

            console.log('[Validação] nivel_ensino_id válido:', nivelNumero);
            return true;
        }

        function validarCamposExperiencia() {
            let camposExperiencia = getWizardDataFromAllSteps('campos_experiencia');
            
            // Verificar checkboxes selecionados no DOM
            const checkboxesSelecionados = Array.from(document.querySelectorAll('input[name="campos_experiencia[]"]:checked'));
            if (checkboxesSelecionados.length > 0) {
                camposExperiencia = checkboxesSelecionados.map(cb => cb.value);
            }

            if (!camposExperiencia || (Array.isArray(camposExperiencia) && camposExperiencia.length === 0)) {
                console.error('[Validação] campos_experiencia não encontrado ou vazio');
                return false;
            }

            // Garantir que seja um array
            if (!Array.isArray(camposExperiencia)) {
                if (typeof camposExperiencia === 'string') {
                    try {
                        camposExperiencia = JSON.parse(camposExperiencia);
                        if (!Array.isArray(camposExperiencia)) {
                            camposExperiencia = [camposExperiencia];
                        }
                    } catch (e) {
                        camposExperiencia = [camposExperiencia];
                    }
                } else {
                    camposExperiencia = [camposExperiencia];
                }
            }

            console.log('[Validação] campos_experiencia válido:', camposExperiencia);
            return camposExperiencia;
        }

        function validarObjetivosAprendizagem() {
            let objetivosAprendizagem = getWizardDataFromAllSteps('objetivos_aprendizagem');
            
            // Verificar checkboxes selecionados no DOM
            const checkboxesSelecionados = Array.from(document.querySelectorAll('input[name="objetivos_aprendizagem[]"]:checked'));
            if (checkboxesSelecionados.length > 0) {
                objetivosAprendizagem = checkboxesSelecionados.map(cb => cb.value);
            }

            if (!objetivosAprendizagem || (Array.isArray(objetivosAprendizagem) && objetivosAprendizagem.length === 0)) {
                console.error('[Validação] objetivos_aprendizagem não encontrado ou vazio');
                return false;
            }

            // Garantir que seja um array
            if (!Array.isArray(objetivosAprendizagem)) {
                if (typeof objetivosAprendizagem === 'string') {
                    try {
                        objetivosAprendizagem = JSON.parse(objetivosAprendizagem);
                        if (!Array.isArray(objetivosAprendizagem)) {
                            objetivosAprendizagem = [objetivosAprendizagem];
                        }
                    } catch (e) {
                        objetivosAprendizagem = [objetivosAprendizagem];
                    }
                } else {
                    objetivosAprendizagem = [objetivosAprendizagem];
                }
            }

            console.log('[Validação] objetivos_aprendizagem válido:', objetivosAprendizagem);
            return objetivosAprendizagem;
        }

        function validarSaberesConhecimentos() {
            let saberesConhecimentos = getWizardDataFromAllSteps('saberes_conhecimentos');
            
            // Verificar textarea no DOM
            const textareaSaberes = document.querySelector('textarea[name="saberes_conhecimentos"]');
            if (textareaSaberes && textareaSaberes.value.trim()) {
                saberesConhecimentos = textareaSaberes.value.trim();
            }

            if (!saberesConhecimentos) {
                console.error('[Validação] saberes_conhecimentos não encontrado');
                return false;
            }

            console.log('[Validação] saberes_conhecimentos válido:', saberesConhecimentos);
            return saberesConhecimentos;
        }

        function validarPlanejamento() {
            console.log('[Step 6] Validando planejamento completo');
            
            window.step6Config.validationErrors = [];
            let isValid = true;

            // Validar termos obrigatórios
            if (!validarTermos()) {
                window.step6Config.validationErrors.push('Termos e condições devem ser aceitos');
                isValid = false;
            }

            // Validar nível de ensino
            if (!validarNivelEnsino()) {
                window.step6Config.validationErrors.push('Nível de ensino é obrigatório e deve ser válido');
                isValid = false;
            }

            // Validar campos de experiência
            const camposExperiencia = validarCamposExperiencia();
            if (!camposExperiencia) {
                window.step6Config.validationErrors.push('Campos de experiência são obrigatórios');
                isValid = false;
            }

            // Validar objetivos de aprendizagem
            const objetivosAprendizagem = validarObjetivosAprendizagem();
            if (!objetivosAprendizagem) {
                window.step6Config.validationErrors.push('Objetivos de aprendizagem são obrigatórios');
                isValid = false;
            }

            // Validar saberes e conhecimentos
            const saberesConhecimentos = validarSaberesConhecimentos();
            if (!saberesConhecimentos) {
                window.step6Config.validationErrors.push('Saberes e conhecimentos são obrigatórios');
                isValid = false;
            }

            // Validar campos obrigatórios básicos
            const camposObrigatorios = [
                'titulo', 'modalidade_ensino_id', 'escola_id', 'turno_id', 
                'turma_id', 'disciplina_id', 'professor_id', 'tipo_periodo'
            ];

            camposObrigatorios.forEach(campo => {
                const valor = getWizardDataFromAllSteps(campo);
                if (!valor) {
                    window.step6Config.validationErrors.push(`Campo ${campo} é obrigatório`);
                    isValid = false;
                }
            });

            // Exibir erros de validação
            exibirErrosValidacao();

            // Atualizar estado dos botões
            atualizarBotoesFinalizacao();

            console.log('[Step 6] Validação concluída. Válido:', isValid);
            console.log('[Step 6] Erros encontrados:', window.step6Config.validationErrors);

            return isValid;
        }

        function exibirErrosValidacao() {
            const containerErros = document.getElementById('validation-errors');
            if (!containerErros) return;

            if (window.step6Config.validationErrors.length === 0) {
                containerErros.style.display = 'none';
                return;
            }

            containerErros.style.display = 'block';
            containerErros.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Corrija os seguintes erros antes de finalizar:
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    ${window.step6Config.validationErrors.map(erro => `<li>${erro}</li>`).join('')}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function atualizarBotoesFinalizacao() {
            const isValid = window.step6Config.validationErrors.length === 0;
            
            // Atualizar botões de finalização
            const botoesFinalizacao = document.querySelectorAll('button[type="submit"], .btn-finalizar');
            botoesFinalizacao.forEach(botao => {
                botao.disabled = !isValid;
                if (isValid) {
                    botao.classList.remove('opacity-50', 'cursor-not-allowed');
                    botao.classList.add('hover:bg-blue-700');
                } else {
                    botao.classList.add('opacity-50', 'cursor-not-allowed');
                    botao.classList.remove('hover:bg-blue-700');
                }
            });

            // Atualizar radio buttons de ação
            const radiosAcao = document.querySelectorAll('input[name="acao_finalizacao"]');
            radiosAcao.forEach(radio => {
                radio.disabled = !isValid;
                const label = radio.closest('label');
                if (label) {
                    if (isValid) {
                        label.classList.remove('opacity-50', 'cursor-not-allowed');
                    } else {
                        label.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                }
            });
        }

        function coletarDadosCompletos() {
            console.log('[Step 6] Coletando dados completos do planejamento');

            const dados = {};

            // Coletar dados básicos
            const camposBasicos = [
                'titulo', 'modalidade_ensino_id', 'escola_id', 'turno_id', 
                'turma_id', 'disciplina_id', 'professor_id', 'tipo_periodo'
            ];

            camposBasicos.forEach(campo => {
                dados[campo] = getWizardDataFromAllSteps(campo);
            });

            // Nível de ensino (garantir que seja número inteiro)
            const nivelEnsinoId = getWizardDataFromAllSteps('nivel_ensino_id');
            dados.nivel_ensino_id = parseInt(nivelEnsinoId, 10);

            // Campos de experiência (garantir que seja array JSON)
            const camposExperiencia = validarCamposExperiencia();
            dados.campos_experiencia_selecionados = JSON.stringify(camposExperiencia);

            // Objetivos de aprendizagem (garantir que seja array JSON)
            const objetivosAprendizagem = validarObjetivosAprendizagem();
            dados.objetivos_aprendizagem_selecionados = JSON.stringify(objetivosAprendizagem);

            // Saberes e conhecimentos
            dados.saberes_conhecimentos = validarSaberesConhecimentos();

            // Termos aceitos - verificar estado real do checkbox
            const checkboxTermos = document.querySelector('input[name="aceitar_termos"]');
            dados.aceitar_termos = checkboxTermos ? checkboxTermos.checked : false;

            // Dados de período
            const tipoPeriodo = getWizardDataFromAllSteps('tipo_periodo');
            dados.tipo_periodo = tipoPeriodo;

            if (tipoPeriodo === 'dias') {
                dados.numero_dias = getWizardDataFromAllSteps('numero_dias');
            } else if (tipoPeriodo === 'datas') {
                dados.data_inicio = getWizardDataFromAllSteps('data_inicio');
                dados.data_fim = getWizardDataFromAllSteps('data_fim');
            } else if (tipoPeriodo === 'bimestre') {
                dados.bimestre = getWizardDataFromAllSteps('bimestre');
                dados.ano_letivo = getWizardDataFromAllSteps('ano_letivo');
            }

            // Carga horária
            dados.carga_horaria_total = getWizardDataFromAllSteps('carga_horaria_total');
            dados.total_aulas = getWizardDataFromAllSteps('total_aulas');

            // ID do planejamento se estiver editando
            if (window.wizardData.planejamento_id) {
                dados.planejamento_id = window.wizardData.planejamento_id;
                // Manter compatibilidade caso alguma lógica ainda use 'id'
                dados.id = window.wizardData.planejamento_id;
            }

            // Planejamentos diários (Step 5) - incluir entradas por data
            try {
                const dailyMap = (window.planejamentoWizard && window.planejamentoWizard.formData && window.planejamentoWizard.formData[5] && window.planejamentoWizard.formData[5].dados_por_dia) ? window.planejamentoWizard.formData[5].dados_por_dia : {};
                const dailyStatus = (window.planejamentoWizard && window.planejamentoWizard.formData && window.planejamentoWizard.formData[5] && window.planejamentoWizard.formData[5].planejamentos_diarios) ? window.planejamentoWizard.formData[5].planejamentos_diarios : {};

                const diarios = Object.entries(dailyMap).map(([dateKey, v]) => {
                    const dt = new Date(dateKey);
                    return {
                        data: dateKey,
                        dia_semana: dt.getDay(),
                        planejado: !!dailyStatus[dateKey],
                        campos_experiencia: Array.isArray(v.campos_experiencia) ? v.campos_experiencia : [],
                        saberes_conhecimentos: v.saberes_conhecimentos || '',
                        objetivos_especificos: v.objetivos_especificos || '',
                        objetivos_aprendizagem: Array.isArray(v.objetivos_aprendizagem) ? v.objetivos_aprendizagem : [],
                        metodologia: v.metodologia || '',
                        recursos_predefinidos: Array.isArray(v.recursos_predefinidos) ? v.recursos_predefinidos : [],
                        recursos_personalizados: v.recursos_personalizados || ''
                    };
                });

                dados.planejamentos_diarios = diarios;
            } catch (e) {
                console.warn('[Step 6] Não foi possível montar planejamentos_diarios:', e);
                dados.planejamentos_diarios = [];
            }

            console.log('[Step 6] Dados coletados:', dados);
            return dados;
        }

        function finalizarWizard() {
            console.log('[Step 6] Iniciando finalização do wizard');

            // Validar antes de enviar
            if (!validarPlanejamento()) {
                console.error('[Step 6] Validação falhou. Não é possível finalizar.');
                window.AlertService.error('Corrija os erros de validação antes de finalizar o planejamento.');
                return false;
            }

            // Verificar ação de finalização selecionada
            const acaoSelecionada = document.querySelector('input[name="acao_finalizacao"]:checked');
            if (!acaoSelecionada) {
                window.AlertService.error('Selecione uma ação de finalização.');
                return false;
            }

            const acaoFinalizacao = acaoSelecionada.value;
            console.log('[Step 6] Ação de finalização:', acaoFinalizacao);

            // Coletar dados completos
            const dadosCompletos = coletarDadosCompletos();

            // Definir status baseado na ação
            switch (acaoFinalizacao) {
                case 'rascunho':
                    dadosCompletos.status = 'rascunho';
                    dadosCompletos.save_as_draft = true;
                    break;
                case 'revisao':
                    dadosCompletos.status = 'finalizado';
                    dadosCompletos.save_as_draft = false;
                    break;
                case 'aprovar':
                    dadosCompletos.status = 'finalizado';
                    dadosCompletos.save_as_draft = false;
                    break;
                default:
                    dadosCompletos.status = 'rascunho';
                    dadosCompletos.save_as_draft = true;
            }

            // Enviar dados
            enviarDados(dadosCompletos);
            return false;
        }

        function enviarDados(dados) {
            console.log('[Step 6] Enviando dados para o servidor:', dados);

            // Mostrar loading
            const botaoSubmit = document.querySelector('button[type="submit"]');
            if (botaoSubmit) {
                botaoSubmit.disabled = true;
                botaoSubmit.textContent = 'Salvando...';
            }

            fetch('/planejamentos/wizard/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(dados)
            })
            .then(response => {
                console.log('[Step 6] Resposta recebida:', response);
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || 'Erro ao salvar o planejamento');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('[Step 6] Planejamento salvo com sucesso:', data);
                window.AlertService.success('Planejamento salvo com sucesso!');
                
                // Redirecionar
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.href = '/planejamentos';
                }
            })
            .catch(error => {
                console.error('[Step 6] Erro ao salvar:', error);
                window.AlertService.error('Erro ao salvar o planejamento: ' + error.message);
                
                // Restaurar botão
                if (botaoSubmit) {
                    botaoSubmit.disabled = false;
                    botaoSubmit.textContent = 'Finalizar';
                }
            });
        }

        function configurarFormulario() {
            const form = document.getElementById('step-6-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    finalizarWizard();
                    return false;
                });
            }
        }

        function configurarBotoes() {
            console.log('[Step 6] Configurando botões');

            // Botão Finalizar
            const btnFinalizar = document.querySelector('button[type="submit"]');
            if (btnFinalizar) {
                btnFinalizar.addEventListener('click', function(e) {
                    e.preventDefault();
                    finalizarWizard();
                    return false;
                });
            }

            // Botão Validar
            const btnValidar = document.getElementById('btn-validar');
            if (btnValidar) {
                btnValidar.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isValid = validarPlanejamento();
                    if (isValid) {
                        window.AlertService.success('Planejamento validado com sucesso!');
                    } else {
                        window.AlertService.error('Corrija os erros antes de continuar.');
                    }
                    return false;
                });
            }

            // Botão Preview
            const btnPreview = document.getElementById('btn-preview');
            if (btnPreview) {
                btnPreview.addEventListener('click', function(e) {
                    e.preventDefault();
                    mostrarPreview();
                    return false;
                });
            }

            // Botão Fechar Preview
            const btnFecharPreview = document.getElementById('fechar-preview');
            if (btnFecharPreview) {
                btnFecharPreview.addEventListener('click', function() {
                    fecharPreview();
                });
            }

            // Botão Exportar
            const btnExportar = document.getElementById('btn-exportar');
            if (btnExportar) {
                btnExportar.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.AlertService.info('Funcionalidade de exportação em desenvolvimento.');
                    return false;
                });
            }
        }

        function mostrarPreview() {
            const modal = document.getElementById('modal-preview');
            if (modal) {
                modal.classList.remove('hidden');
                
                // Gerar conteúdo do preview
                const conteudoPreview = document.getElementById('conteudo-preview');
                if (conteudoPreview) {
                    conteudoPreview.innerHTML = gerarHtmlPreview();
                }
            }
        }

        function fecharPreview() {
            const modal = document.getElementById('modal-preview');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function gerarHtmlPreview() {
            const dados = coletarDadosCompletos();
            
            return `
                <div class="space-y-6">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Informações Básicas</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><strong>Título:</strong> ${dados.titulo || 'Não informado'}</div>
                            <div><strong>Nível de Ensino ID:</strong> ${dados.nivel_ensino_id || 'Não informado'}</div>
                            <div><strong>Modalidade ID:</strong> ${dados.modalidade_ensino_id || 'Não informado'}</div>
                            <div><strong>Escola ID:</strong> ${dados.escola_id || 'Não informado'}</div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Conteúdo Pedagógico</h3>
                        <div class="space-y-3">
                            <div><strong>Campos de Experiência:</strong> ${dados.campos_experiencia_selecionados || 'Não informado'}</div>
                            <div><strong>Saberes e Conhecimentos:</strong> ${dados.saberes_conhecimentos || 'Não informado'}</div>
                            <div><strong>Objetivos de Aprendizagem:</strong> ${dados.objetivos_aprendizagem_selecionados || 'Não informado'}</div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <h3 class="text-lg font-semibold mb-4">Status</h3>
                        <div><strong>Termos Aceitos:</strong> ${dados.aceitar_termos ? 'Sim' : 'Não'}</div>
                    </div>
                </div>
            `;
        }

        async function carregarResumo() {
            console.log('[Step 6] Carregando resumo do planejamento');
            
            // Util para formatar datas em pt-BR
            function formatDateBR(ymd) {
                try {
                    if (!ymd) return '-';
                    const d = new Date(ymd);
                    if (!isNaN(d.getTime())) {
                        return d.toLocaleDateString('pt-BR');
                    }
                    const parts = String(ymd).split('-');
                    if (parts.length === 3) {
                        const y = parseInt(parts[0], 10);
                        const m = parseInt(parts[1], 10) - 1;
                        const day = parseInt(parts[2], 10);
                        const dt = new Date(y, m, day);
                        if (!isNaN(dt.getTime())) return dt.toLocaleDateString('pt-BR');
                    }
                    return String(ymd);
                } catch (e) { return String(ymd || '-'); }
            }

            // Título
            const titulo = getWizardDataFromAllSteps('titulo');
            const elementoTitulo = document.getElementById('resumo-titulo');
            if (elementoTitulo) elementoTitulo.textContent = titulo || '-';

            // IDs
            const escolaId = getWizardDataFromAllSteps('escola_id');
            const turnoId = getWizardDataFromAllSteps('turno_id');
            const turmaId = getWizardDataFromAllSteps('turma_id');
            const disciplinaId = getWizardDataFromAllSteps('disciplina_id');
            const professorId = getWizardDataFromAllSteps('professor_id');

            // Nível e modalidade: preferir nomes armazenados no wizard; fallback para ID
            const nivelId = getWizardDataFromAllSteps('nivel_ensino_id');
            const elementoNivel = document.getElementById('resumo-nivel');
            const nivelNomeWizard = getWizardDataFromAllSteps('nivel_ensino_nome');
            if (elementoNivel) elementoNivel.textContent = nivelNomeWizard || (nivelId ? `ID ${nivelId}` : '-');

            const modalidadeId = getWizardDataFromAllSteps('modalidade_ensino_id');
            const elementoModalidade = document.getElementById('resumo-modalidade');
            const modalidadeNomeWizard = getWizardDataFromAllSteps('modalidade_ensino_nome');
            if (elementoModalidade) elementoModalidade.textContent = modalidadeNomeWizard || (modalidadeId ? `ID ${modalidadeId}` : '-');

            // Período e duração
            const tipoPeriodo = getWizardDataFromAllSteps('tipo_periodo');
            const dataInicio = getWizardDataFromAllSteps('data_inicio');
            const dataFim = getWizardDataFromAllSteps('data_fim');
            const numeroDias = parseInt(getWizardDataFromAllSteps('numero_dias') || 0, 10);
            const cargaAula = parseFloat(getWizardDataFromAllSteps('carga_horaria_aula') || 0.75);

            const tipoPeriodoEl = document.getElementById('resumo-tipo-periodo');
            if (tipoPeriodoEl) tipoPeriodoEl.textContent = tipoPeriodo ? (tipoPeriodo === 'mensal' ? 'Mensal' : tipoPeriodo) : '-';

            const totalAulasEl = document.getElementById('resumo-total-aulas');
            if (totalAulasEl) totalAulasEl.textContent = numeroDias > 0 ? `${numeroDias}` : '-';

            const cargaHorariaEl = document.getElementById('resumo-carga-horaria');
            if (cargaHorariaEl) cargaHorariaEl.textContent = numeroDias > 0 ? `${(numeroDias * cargaAula).toFixed(1)}h` : '-';

            const periodoDetalhadoEl = document.getElementById('resumo-periodo-detalhado');
            if (periodoDetalhadoEl) periodoDetalhadoEl.textContent = (dataInicio && dataFim) ? `${formatDateBR(dataInicio)} a ${formatDateBR(dataFim)}` : '-';


            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || null;
            const basePath = document.querySelector('meta[name="app-base-path"]')?.getAttribute('content') || window.__APP_BASE_PATH__ || '';
            function resolveUrl(path) {
                const base = basePath || '';
                if (!base) return path;
                return `${String(base).replace(/\/+$/, '')}/${String(path).replace(/^\/+/, '')}`;
            }
            const commonHeaders = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (csrfToken) { commonHeaders['X-CSRF-TOKEN'] = csrfToken; }

            // Escola
            try {
                const elementoEscola = document.getElementById('resumo-escola');
                if (escolaId && elementoEscola) {
                    const res = await fetch(resolveUrl(`/api/escolas/${escolaId}`), {
                         credentials: 'same-origin',
                         headers: commonHeaders
                     });
                    if (res.ok) {
                        const escola = await res.json();
                        elementoEscola.textContent = escola?.nome || `ID ${escolaId}`;
                    } else {
                        elementoEscola.textContent = `ID ${escolaId}`;
                    }
                }
            } catch (e) { console.warn('STEP-6: falha ao obter escola por ID', e); }

            // Turno
            try {
                const elementoTurno = document.getElementById('resumo-turno');
                if (turnoId && elementoTurno) {
                    const res = await fetch(resolveUrl(`/api/turnos/${turnoId}`), {
                         credentials: 'same-origin',
                         headers: commonHeaders
                     });
                    if (res.ok) {
                        const turno = await res.json();
                        elementoTurno.textContent = turno?.nome || `ID ${turnoId}`;
                    } else {
                        elementoTurno.textContent = `ID ${turnoId}`;
                    }
                }
            } catch (e) { console.warn('STEP-6: falha ao obter turno por ID', e); }

            // Turma + alunos + escola + nivel via turma
            try {
                const elementoTurma = document.getElementById('resumo-turma');
                if (turmaId && elementoTurma) {
                    const res = await fetch(resolveUrl(`/api/turmas/${turmaId}`), {
                         credentials: 'same-origin',
                         headers: commonHeaders
                     });
                    if (res.ok) {
                        const turmaResp = await res.json();
                        const turmaObj = turmaResp?.turma || turmaResp; // a API retorna { success, turma: {...} }
                        elementoTurma.textContent = turmaObj?.nome || turmaObj?.descricao || `ID ${turmaId}`;

                        // Escola
                        const elEscola = document.getElementById('resumo-escola');
                        if (elEscola) elEscola.textContent = turmaObj?.escola?.nome || (escolaId ? `ID ${escolaId}` : '-');

                        // Nível de ensino (refinar com o nome da turma caso não tenha vindo do wizard)
                        if (elementoNivel && !nivelNomeWizard) {
                            elementoNivel.textContent = turmaObj?.nivelEnsino?.nome || (nivelId ? `ID ${nivelId}` : '-');
                        }

                        // Modalidade (tentar via relação do nível da turma caso não tenha vindo do wizard)
                        if (elementoModalidade && !modalidadeNomeWizard) {
                            const nomeModalidade = turmaObj?.nivelEnsino?.modalidade?.nome || turmaObj?.modalidade?.nome;
                            if (nomeModalidade) {
                                elementoModalidade.textContent = nomeModalidade;
                            }
                        }
                    } else {
                        elementoTurma.textContent = `ID ${turmaId}`;
                    }
                    try {
                        const elAlunos = document.getElementById('resumo-alunos');
                        if (elAlunos) {
                            const resAlunos = await fetch(resolveUrl(`/api/turmas/${turmaId}/alunos`), {
                                 credentials: 'same-origin',
                                 headers: commonHeaders
                             });
                            if (resAlunos.ok) {
                                const alunosResp = await resAlunos.json();
                                // API retorna { success, alunos: [...], total: N }
                                const totalAlunos = (typeof alunosResp?.total === 'number')
                                    ? alunosResp.total
                                    : (Array.isArray(alunosResp?.alunos) ? alunosResp.alunos.length : null);
                                elAlunos.textContent = (totalAlunos !== null && totalAlunos !== undefined) ? String(totalAlunos) : '-';
                            }
                        }
                    } catch (e2) { console.warn('STEP-6: falha ao obter alunos da turma', e2); }
                }
            } catch (e) { console.warn('STEP-6: falha ao obter turma por ID', e); }

            // Disciplina por turma
            try {
                const elementoDisciplina = document.getElementById('resumo-disciplina');
                if (turmaId && disciplinaId && elementoDisciplina) {
                    const res = await fetch(resolveUrl(`/planejamentos/get-disciplinas-por-turma?turma_id=${encodeURIComponent(turmaId)}`), {
                         credentials: 'same-origin',
                         headers: commonHeaders
                     });
                    if (res.ok) {
                        const disciplinas = await res.json();
                        const encontrada = Array.isArray(disciplinas) ? disciplinas.find(d => String(d.id) === String(disciplinaId)) : null;
                        elementoDisciplina.textContent = (encontrada && (encontrada.nome || encontrada.name)) ? (encontrada.nome || encontrada.name) : `ID ${disciplinaId}`;
                    } else {
                        elementoDisciplina.textContent = `ID ${disciplinaId}`;
                    }
                }
            } catch (e) { console.warn('STEP-6: falha ao obter disciplina por turma', e); }

            // Professor por turma/disciplina com fallback para nome do wizard
            try {
                const elementoProfessor = document.getElementById('resumo-professor');
                const professorNomeWizard = getWizardDataFromAllSteps('professor_nome');
                if (elementoProfessor) {
                    if (professorNomeWizard) {
                        elementoProfessor.textContent = professorNomeWizard;
                    } else if (professorId && turmaId && disciplinaId) {
                        const urlPrimaria = resolveUrl(`/planejamentos/get-professores-por-turma-disciplina?turma_id=${encodeURIComponent(turmaId)}&disciplina_id=${encodeURIComponent(disciplinaId)}`);
                        try {
                            const res = await fetch(urlPrimaria, {
                                credentials: 'same-origin',
                                headers: commonHeaders
                            });
                            if (res.ok) {
                                const professores = await res.json();
                                const encontrado = Array.isArray(professores) ? professores.find(p => String(p.id) === String(professorId)) : null;
                                elementoProfessor.textContent = (encontrado && (encontrado.nome || encontrado.name)) ? (encontrado.nome || encontrado.name) : (professorId ? `ID ${professorId}` : '-');
                            } else {
                                // Fallback para rota alternativa
                                const urlFallback = resolveUrl(`/planejamentos/get-professores-by-turma-disciplina?turma_id=${encodeURIComponent(turmaId)}&disciplina_id=${encodeURIComponent(disciplinaId)}`);
                                try {
                                    const res2 = await fetch(urlFallback, {
                                        credentials: 'same-origin',
                                        headers: commonHeaders
                                    });
                                    if (res2.ok) {
                                        const professores2 = await res2.json();
                                        const encontrado2 = Array.isArray(professores2) ? professores2.find(p => String(p.id) === String(professorId)) : null;
                                        elementoProfessor.textContent = (encontrado2 && (encontrado2.nome || encontrado2.name)) ? (encontrado2.nome || encontrado2.name) : (professorId ? `ID ${professorId}` : '-');
                                    } else {
                                        elementoProfessor.textContent = (professorId ? `ID ${professorId}` : '-');
                                    }
                                } catch (eFb) {
                                    console.warn('STEP-6: fallback professor falhou', eFb);
                                    elementoProfessor.textContent = (professorId ? `ID ${professorId}` : '-');
                                }
                            }
                        } catch (eFetch) {
                            console.warn('STEP-6: erro ao obter professor por turma/disciplina', eFetch);
                            // Tentar rota alternativa em caso de erro na primária
                            try {
                                const urlFallback2 = resolveUrl(`/planejamentos/get-professores-by-turma-disciplina?turma_id=${encodeURIComponent(turmaId)}&disciplina_id=${encodeURIComponent(disciplinaId)}`);
                                const res3 = await fetch(urlFallback2, {
                                    credentials: 'same-origin',
                                    headers: commonHeaders
                                });
                                if (res3.ok) {
                                    const professores3 = await res3.json();
                                    const encontrado3 = Array.isArray(professores3) ? professores3.find(p => String(p.id) === String(professorId)) : null;
                                    elementoProfessor.textContent = (encontrado3 && (encontrado3.nome || encontrado3.name)) ? (encontrado3.nome || encontrado3.name) : (professorId ? `ID ${professorId}` : '-');
                                } else {
                                    elementoProfessor.textContent = (professorId ? `ID ${professorId}` : '-');
                                }
                            } catch (eFetch2) {
                                elementoProfessor.textContent = (professorId ? `ID ${professorId}` : '-');
                            }
                        }
                    } else {
                        elementoProfessor.textContent = '-';
                    }
                }
            } catch (e) { console.warn('STEP-6: falha ao definir professor', e); }

            console.log('[Step 6] Resumo carregado');
        }

        // Inicializar quando o DOM estiver pronto
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initStep6);
        } else {
            initStep6();
        }

    })();
