@extends('layouts.app')

@section('content')
    <x-breadcrumbs :items="[
        ['title' => 'Planejamentos', 'url' => route('planejamentos.index')],
        ['title' => isset($planejamento) ? 'Continuar Planejamento' : 'Wizard de Planejamento', 'url' => '#'],
    ]" />

    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                <i class="fas fa-magic text-blue-600 mr-3"></i>
                {{ isset($planejamento) ? 'Continuar Planejamento' : 'Wizard Inteligente de Planejamento' }}
            </h1>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                {{ isset($planejamento) ? 'Atualize as informações do seu planejamento de forma guiada' : 'Crie seu planejamento de aula de forma rápida e intuitiva seguindo nosso assistente inteligente' }}
            </p>
        </div>

        <!-- Wizard Container -->
        <div id="wizard-container" class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Progress Bar -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progresso</span>
                    <span class="text-sm text-gray-500" id="step-counter">Etapa 1 de 6</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 16.67%"
                        id="progress-bar"></div>
                </div>
            </div>

            <!-- Step Navigation -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <nav class="flex space-x-8 overflow-x-auto">
                    @php
                        $steps = [
                            1 => ['title' => 'Básico', 'icon' => 'fas fa-cog', 'description' => 'Configuração inicial'],
                            2 => ['title' => 'Escola', 'icon' => 'fas fa-school', 'description' => 'Unidade e turno'],
                            3 => ['title' => 'Turma', 'icon' => 'fas fa-users', 'description' => 'Turma e disciplina'],
                            4 => [
                                'title' => 'Período',
                                'icon' => 'fas fa-calendar',
                                'description' => 'Duração e cronograma',
                            ],
                            5 => [
                                'title' => 'Conteúdo',
                                'icon' => 'fas fa-book',
                                'description' => 'Objetivos e metodologia',
                            ],
                            6 => ['title' => 'Revisão', 'icon' => 'fas fa-check', 'description' => 'Finalização'],
                        ];
                    @endphp

                    @php $initialStep = isset($planejamento) ? 5 : 1; @endphp

                    @foreach ($steps as $stepNumber => $step)
                        <div class="flex items-center space-x-3 step-nav {{ $stepNumber === $initialStep ? 'active' : '' }}"
                            data-step="{{ $stepNumber }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center step-icon">
                                <i class="{{ $step['icon'] }} text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium step-title">{{ $step['title'] }}</p>
                                <p class="text-xs text-gray-500 step-description">{{ $step['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </nav>
            </div>

            <!-- Step Content -->
            <div class="p-6" id="step-content">
                <!-- Conteúdo será carregado dinamicamente -->
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Carregando wizard...</p>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div class="flex justify-between items-center gap-2 flex-wrap">
                    <div>
                        <button type="button" id="btn-previous"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-arrow-left mr-2"></i>
                            <span class="hidden md:block">Anterior</span>
                        </button>
                    </div>

                    <div class="flex space-x-3 flex-wrap">

                        <button type="button" id="btn-next"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span class="hidden md:block">Próximo</span>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            class PlanejamentoWizard {
                constructor() {
                    this.currentStep = {{ isset($planejamento) ? 5 : 1 }};
                    this.maxSteps = 6;
                    this.planejamento = @json($planejamento ?? null);

                    // Tentar recuperar planejamento do localStorage se não veio do PHP
                    if (!this.planejamento) {
                        const storedId = localStorage.getItem('wizard_planejamento_id');
                        if (storedId && !isNaN(parseInt(storedId))) {
                            // Validate the stored ID before using it
                            const parsedId = parseInt(storedId);
                            console.log('[Wizard] Found stored planejamento ID:', parsedId);

                            // Only use if we're on the wizard page with an edit parameter
                            const urlParams = new URLSearchParams(window.location.search);
                            if (urlParams.has('edit') && urlParams.get('edit') == parsedId) {
                                this.planejamento = {
                                    id: parsedId
                                };
                                console.log('[Wizard] Using stored planejamento ID from URL match');
                            } else {
                                // Clear stale localStorage if URL doesn't match
                                console.warn('[Wizard] Clearing stale localStorage ID (no URL match):', parsedId);
                                localStorage.removeItem('wizard_planejamento_id');
                            }
                        }
                    }

                    this.formData = {};

                    this.init();
                }

                init() {
                    this.bindEvents();
                    this.loadStep(this.currentStep);
                }

                bindEvents() {
                    document.getElementById('btn-next').addEventListener('click', () => this.nextStep());
                    document.getElementById('btn-previous').addEventListener('click', () => this.previousStep());

                }

                async loadStep(step) {
                    try {
                        let url = `{{ route('planejamentos.wizard.step', ['step' => '__STEP__']) }}`.replace('__STEP__',
                            step);

                        // Adicionar ID do planejamento se estiver editando
                        if (this.planejamento && this.planejamento.id) {
                            url += `?edit=${this.planejamento.id}`;
                        } else if (step == 5) {
                            // Fallback: se estamos indo para a etapa 5 e não temos ID de planejamento, 
                            // passar o nível de ensino da etapa 1 para permitir a filtragem de Campos de Experiência
                            const step1Data = this.formData[1];
                            if (step1Data && step1Data.nivel_ensino_id) {
                                url += `?nivel_ensino_id=${step1Data.nivel_ensino_id}`;
                            }
                        }

                        console.log('[Wizard] Loading step:', step, 'URL:', url);

                        const response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            credentials: 'same-origin'
                        });

                        if (!response.ok) {
                            console.error('[Wizard] HTTP Error:', response.status, response.statusText);

                            // Try to get error message from response
                            let errorMessage = `Erro ${response.status}: ${response.statusText}`;
                            try {
                                const errorText = await response.text();
                                console.error('[Wizard] Error response:', errorText);

                                // Check if it's a redirect to login
                                if (response.status === 401 || errorText.includes('login')) {
                                    errorMessage = 'Sessão expirada. Faça login novamente.';
                                    window.location.href = '/login';
                                    return;
                                }

                                // Check for specific error messages
                                if (errorText.includes('não encontrado') || errorText.includes('not found')) {
                                    errorMessage = 'Planejamento não encontrado. Iniciando novo planejamento...';
                                    // Clear localStorage and reload without edit parameter
                                    localStorage.removeItem('wizard_planejamento_id');
                                    this.planejamento = null;
                                    setTimeout(() => {
                                        window.location.href = '{{ route('planejamentos.wizard') }}';
                                    }, 2000);
                                }
                            } catch (e) {
                                console.error('[Wizard] Failed to parse error response:', e);
                            }

                            throw new Error(errorMessage);
                        }

                        const html = await response.text();
                        console.log('[Wizard] loadStep status:', response.status);
                        console.log('[Wizard] loadStep html length:', html?.length || 0);

                        // Se vier vazio, mostrar feedback em vez de deixar o loader
                        if (!html || html.trim().length === 0) {
                            console.warn('[Wizard] HTML da etapa veio vazio');
                            document.getElementById('step-content').innerHTML = `
                    <div class=\"text-center py-8\">
                        <p class=\"text-red-600 font-semibold mb-2\">Não foi possível carregar o conteúdo da etapa.</p>
                        <p class=\"text-gray-600 mb-4\">O servidor retornou resposta vazia. Tente novamente.</p>
                        <button type=\"button\" class=\"px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded\"
                                onclick=\"window.planejamentoWizard && window.planejamentoWizard.loadStep(${step})\">
                            Recarregar etapa
                        </button>
                    </div>
                `;
                            return;
                        }

                        document.getElementById('step-content').innerHTML = html;

                        // Executar scripts que foram carregados dinamicamente
                        this.executeScripts();

                        this.updateUI();
                        this.populateFormData();

                        // Capturar alterações da etapa atual para manter this.formData atualizado
                        const currentForm = document.querySelector('#step-content form');
                        if (currentForm) {
                            const updateData = () => {
                                try {
                                    const data = this.collectStepData();
                                    if (this.currentStep === 5) {
                                        const existente = this.formData[5] || {};
                                        this.formData[5] = {
                                            ...existente,
                                            ...data
                                        };
                                    } else {
                                        this.formData[this.currentStep] = data;
                                    }
                                    console.log('[Wizard] formData atualizado (step', this.currentStep, '):', data);
                                } catch (e) {
                                    console.warn('[Wizard] Falha ao coletar dados da etapa', this.currentStep, e);
                                }
                            };
                            currentForm.addEventListener('input', updateData);
                            currentForm.addEventListener('change', updateData);
                            // Atualização inicial
                            updateData();
                        }

                    } catch (error) {
                        console.error('[Wizard] Error loading step:', error);
                        document.getElementById('step-content').innerHTML = `
                <div class=\"text-center py-8\">
                    <div class=\"mb-4\">
                        <i class=\"fas fa-exclamation-triangle text-red-500 text-5xl\"></i>
                    </div>
                    <p class=\"text-red-600 font-semibold mb-2\">Erro ao carregar etapa</p>
                    <p class=\"text-gray-600 mb-4\">${error?.message || 'Tente novamente mais tarde.'}</p>
                    <div class=\"space-x-2\">
                        <button type=\"button\" class=\"px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded\"
                                onclick=\"window.planejamentoWizard && window.planejamentoWizard.loadStep(${step})\">
                            Tentar novamente
                        </button>
                        <button type=\"button\" class=\"px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded\"
                                onclick=\"localStorage.removeItem('wizard_planejamento_id'); window.location.href='{{ route('planejamentos.wizard') }}'\">
                            Reiniciar wizard
                        </button>
                    </div>
                </div>
            `;
                        this.showError(error?.message || 'Erro ao carregar etapa. Tente novamente.');
                    }
                }

                executeScripts() {
                    // Executar scripts que foram carregados dinamicamente
                    const scripts = document.getElementById('step-content').querySelectorAll('script');
                    scripts.forEach((script, index) => {
                        if (script.src) {
                            // Script externo - criar novo elemento
                            const newScript = document.createElement('script');
                            newScript.src = script.src;
                            newScript.async = false; // Manter ordem de execução
                            document.head.appendChild(newScript);
                        } else {
                            // Script inline - validar levemente e executar
                            try {
                                const scriptContent = script.textContent.trim();

                                // Validação básica de sintaxe (apenas informativa)
                                if (!scriptContent) {
                                    console.warn(`
                        Script inline $ {
                            index + 1
                        }
                        está vazio`);
                                    return;
                                }

                                // Verificar balanceamento básico de chaves (n�o bloquear execu��o)
                                const openBraces = (scriptContent.match(/\{/g) || []).length;
                                const closeBraces = (scriptContent.match(/\}/g) || []).length;

                                if (openBraces !== closeBraces) {
                                    console.warn(`
                        Script inline $ {
                            index + 1
                        }
                        tem chaves possivelmente desbalanceadas: $ {
                            openBraces
                        }
                        aberturas, $ {
                            closeBraces
                        }
                        fechamentos`);
                                    console.warn('Conteúdo do script:', scriptContent);
                                }

                                // Tentar validar sintaxe usando Function constructor (n�o bloquear execu��o)
                                try {
                                    new Function(scriptContent);
                                } catch (syntaxError) {
                                    console.warn(`
                        Poss� vel erro de sintaxe no script inline $ {
                            index + 1
                        }: `, syntaxError);
                                    console.warn('Conteúdo do script:', scriptContent);
                                    // Prosseguir com a execu��o para permitir que o navegador avalie corretamente
                                }

                                const newScript = document.createElement('script');
                                newScript.type = 'text/javascript';
                                newScript.textContent = scriptContent;

                                // Remover o script antigo para evitar duplicação
                                script.remove();

                                // Adicionar o novo script ao head para execução
                                document.head.appendChild(newScript);

                                // Remover o script do head após execução para limpeza
                                setTimeout(() => {
                                    if (newScript.parentNode) {
                                        newScript.parentNode.removeChild(newScript);
                                    }
                                }, 100);

                            } catch (error) {
                                console.error(`
                        Erro ao executar script inline $ {
                            index + 1
                        }: `, error);
                                console.error('Conteúdo do script:', script.textContent);
                            }
                        }
                    });
                }

                updateUI() {
                    // Atualizar contador de etapas
                    document.getElementById('step-counter').textContent = `
                        Etapa $ {
                            this.currentStep
                        }
                        de $ {
                            this.maxSteps
                        }
                        `;

                    // Atualizar barra de progresso
                    const progress = (this.currentStep / this.maxSteps) * 100;
                    document.getElementById('progress-bar').style.width = `
                        $ {
                            progress
                        } % `;

                    // Atualizar navegação de etapas
                    document.querySelectorAll('.step-nav').forEach((nav, index) => {
                        const stepNumber = index + 1;
                        nav.classList.toggle('active', stepNumber === this.currentStep);
                        nav.classList.toggle('completed', stepNumber < this.currentStep);
                    });

                    // Atualizar botões
                    const isEditing = !!(this.planejamento && this.planejamento.id);
                    // Em modo edição, bloquear voltar para etapas 1-4 (permite voltar de 6 para 5)
                    document.getElementById('btn-previous').disabled = (this.currentStep === 1) || (isEditing && this
                        .currentStep <= 5);

                    const nextBtn = document.getElementById('btn-next');
                    if (this.currentStep === this.maxSteps) {
                        nextBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Salvar e fechar';
                        nextBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        nextBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                    } else {
                        nextBtn.innerHTML = 'Próximo <i class="fas fa-arrow-right ml-2"></i>';
                        nextBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                        nextBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    }
                }

                populateFormData() {
                    if (this.planejamento) {
                        // Preencher campos com dados existentes
                        const form = document.querySelector('#step-content form');
                        if (form) {
                            Object.keys(this.planejamento).forEach(key => {
                                const field = form.querySelector(` [name = "${key}"] `);
                                if (field) {
                                    field.value = this.planejamento[key];
                                }
                            });
                        }
                    }
                }

                async nextStep() {
                    if (this.currentStep === this.maxSteps) {
                        await this.finishWizard();
                    } else {
                        if (await this.validateStep()) {
                            this.currentStep++;
                            await this.loadStep(this.currentStep);
                        }
                    }
                }

                async previousStep() {
                    const isEditing = !!(this.planejamento && this.planejamento.id);
                    if (!isEditing) {
                        if (this.currentStep > 1) {
                            this.currentStep--;
                            await this.loadStep(this.currentStep);
                        }
                        return;
                    }

                    // Em edi��o: s� permite voltar de 6 para 5. Bloqueia voltar para 1-4.
                    if (this.currentStep === 6) {
                        this.currentStep = 5;
                        await this.loadStep(this.currentStep);
                    }
                }

                async validateStep() {
                    try {
                        const formData = this.collectStepData();

                        const response = await fetch('{{ route('planejamentos.wizard.validate-step') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                step: this.currentStep,
                                data: formData
                            })
                        });

                        const result = await response.json();

                        if (!result.success) {
                            this.showValidationErrors(result.errors);
                            return false;
                        }

                        // Salvar dados da etapa
                        // No passo 5, preservar estruturas existentes (dados_por_dia, planejamentos_diarios)
                        if (this.currentStep === 5) {
                            const existing = this.formData[5] || {};
                            this.formData[5] = {
                                ...existing,
                                ...formData
                            };
                        } else {
                            this.formData[this.currentStep] = formData;
                        }
                        return true;

                    } catch (error) {
                        console.error('Erro na validação:', error);
                        this.showError('Erro na validação. Tente novamente.');
                        return false;
                    }
                }

                collectStepData() {
                    const form = document.querySelector('#step-content form');
                    if (!form) return {};

                    const formData = new FormData(form);
                    const data = {};

                    for (let [rawKey, value] of formData.entries()) {
                        // Normalizar chaves que terminam com [] para o nome base
                        const key = rawKey.endsWith('[]') ? rawKey.slice(0, -2) : rawKey;

                        if (Object.prototype.hasOwnProperty.call(data, key)) {
                            // Acumular múltiplos valores em array
                            if (Array.isArray(data[key])) {
                                data[key].push(value);
                            } else {
                                data[key] = [data[key], value];
                            }
                        } else {
                            data[key] = value;
                        }
                    }

                    console.log('DEBUG: collectStepData - step', this.currentStep, 'data:', data);

                    return data;
                }

                async saveDraft() {
                    try {
                        console.log('=== INICIANDO SALVAMENTO DE RASCUNHO ===');
                        let allData;
                        if (this.currentStep === 5) {
                            const existing = this.formData[5] || {};
                            allData = {
                                ...this.formData,
                                5: {
                                    ...existing,
                                    ...this.collectStepData()
                                }
                            };
                        } else {
                            allData = {
                                ...this.formData,
                                [this.currentStep]: this.collectStepData()
                            };
                        }
                        console.log('Dados coletados:', allData);

                        const requestData = {
                            ...allData,
                            save_as_draft: true,
                            planejamento_id: this.planejamento?.id
                        };
                        console.log('Dados da requisição:', requestData);

                        const response = await fetch('{{ route('planejamentos.wizard.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(requestData)
                        });

                        console.log('Response status:', response.status);
                        console.log('Response headers:', response.headers);

                        const result = await response.json();
                        console.log('Response result:', result);

                        if (result.success) {
                            this.showSuccess('Rascunho salvo com sucesso!');
                            if (result.planejamento_id && !this.planejamento) {
                                this.planejamento = {
                                    id: result.planejamento_id
                                };
                            }
                        } else {
                            this.showError(result.message || 'Erro ao salvar rascunho');
                        }

                    } catch (error) {
                        console.error('Erro ao salvar rascunho:', error);
                        this.showError('Erro ao salvar rascunho. Tente novamente.');
                    }
                }

                async finishWizard() {
                    try {
                        // 1) Coletar dados da etapa atual
                        const currentData = this.collectStepData() || {};

                        // 2) Mesclar dados de todas as etapas num único objeto plano
                        const flattened = {};
                        for (let i = 1; i <= 6; i++) {
                            const stepData = this.formData[i];
                            if (stepData && typeof stepData === 'object') {
                                Object.assign(flattened, stepData);
                            }
                        }
                        Object.assign(flattened, currentData);

                        // 2.1) Definir status/save_as_draft conforme ação selecionada na Etapa 6
                        try {
                            const acaoSel = document.querySelector('input[name="acao_finalizacao"]:checked');
                            if (acaoSel) {
                                const acao = acaoSel.value;
                                switch (acao) {
                                    case 'rascunho':
                                        flattened.status = 'rascunho';
                                        flattened.save_as_draft = true;
                                        break;
                                    case 'revisao':
                                        // Em Revisão é representado como 'finalizado' aguardando aprovação
                                        flattened.status = 'finalizado';
                                        flattened.save_as_draft = false;
                                        break;
                                    case 'aprovado':
                                        flattened.status = 'aprovado';
                                        flattened.save_as_draft = false;
                                        break;
                                    case 'rejeitado':
                                        flattened.status = 'rejeitado';
                                        flattened.save_as_draft = false;
                                        break;
                                    case 'reprovado':
                                        flattened.status = 'reprovado';
                                        flattened.save_as_draft = false;
                                        break;
                                    default:
                                        // Default conservador: salvar como rascunho
                                        flattened.save_as_draft = true;
                                        flattened.status = flattened.status || 'rascunho';
                                }
                            } else {
                                // Sem ação selecionada: salvar como rascunho
                                flattened.save_as_draft = true;
                                flattened.status = flattened.status || 'rascunho';
                            }
                        } catch (e) {
                            console.warn('[Wizard] Falha ao definir status/save_as_draft pela ação selecionada:', e);
                            flattened.save_as_draft = flattened.save_as_draft ?? true;
                            flattened.status = flattened.status || 'rascunho';
                        }

                        // 3) Normalizar aceite de termos
                        const termosEl = document.querySelector(
                            'input[name="aceita_termos"], input[name="aceitar_termos"], #checkbox_termos');
                        const termosChecked = termosEl ? !!(termosEl.checked) : false;
                        flattened.aceita_termos = termosChecked ? 1 : 0;
                        // Manter compatibilidade se backend ainda olhar o nome antigo
                        flattened.aceitar_termos = termosChecked ? 1 : 0;
                        // Remover qualquer lixo conhecido
                        delete flattened.aceitar_termos_hidden;

                        // 4) Garantir campos essenciais com fallbacks (edição e stores globais)
                        const required = [
                            'titulo', 'modalidade_ensino_id', 'nivel_ensino_id', 'escola_id',
                            'turno_id', 'turma_id', 'disciplina_id', 'professor_id', 'tipo_periodo'
                        ];
                        const tryGetFromStores = (key) => {
                            // 1) planejamento (edição)
                            if (this.planejamento && this.planejamento[key] != null) return this.planejamento[key];

                            // 1.1) Mapeamentos e derivação a partir do planejamento em edição
                            if (this.planejamento) {
                                // Mapear 'turno' -> 'turno_id'
                                if (key === 'turno_id') {
                                    const t = this.planejamento.turno_id ?? this.planejamento.turno;
                                    if (t != null && String(t) !== '') return t;
                                }
                                // Mapear 'modalidade' -> 'modalidade_ensino_id'
                                if (key === 'modalidade_ensino_id') {
                                    const m = this.planejamento.modalidade_ensino_id ?? this.planejamento.modalidade;
                                    if (m != null && String(m) !== '') return m;
                                }
                                // IDs diretos
                                if (key === 'turma_id' && this.planejamento.turma_id != null) return this.planejamento
                                    .turma_id;
                                // Fallback via relacionamento carregado
                                if (key === 'turma_id' && this.planejamento.turma && this.planejamento.turma.id != null)
                                    return this.planejamento.turma.id;
                                if (key === 'disciplina_id' && this.planejamento.disciplina_id != null) return this
                                    .planejamento.disciplina_id;
                                // Fallback via relacionamento carregado
                                if (key === 'disciplina_id' && this.planejamento.disciplina && this.planejamento
                                    .disciplina.id != null) return this.planejamento.disciplina.id;
                                if (key === 'professor_id') {
                                    const p = this.planejamento.professor_id ?? this.planejamento.user_id;
                                    if (p != null && String(p) !== '') return p;
                                }
                                // Período e duração
                                if (key === 'data_inicio' && this.planejamento.data_inicio) return this.planejamento
                                    .data_inicio;
                                if (key === 'data_fim' && this.planejamento.data_fim) return this.planejamento.data_fim;
                                if (key === 'numero_dias' && this.planejamento.numero_dias) return this.planejamento
                                    .numero_dias;
                                if (key === 'tipo_periodo') {
                                    if (this.planejamento.data_inicio && this.planejamento.data_fim) return 'mensal';
                                    if (this.planejamento.numero_dias) {
                                        // Derivar tipo mais permissivo
                                        if (this.planejamento.numero_dias <= 7) return 'semanal';
                                        return 'mensal';
                                    }
                                }
                                if (key === 'aulas_por_semana' && this.planejamento.aulas_por_semana != null)
                                    return this.planejamento.aulas_por_semana;
                                if (key === 'carga_horaria_aula' && this.planejamento.carga_horaria_aula != null)
                                    return this.planejamento.carga_horaria_aula;
                            }
                            // 2) window.wizardData stepN
                            if (window.wizardData) {
                                for (let i = 1; i <= 6; i++) {
                                    const s = window.wizardData[`
                        step$ {
                            i
                        }
                        `];
                                    if (s && s[key] != null) return s[key];
                                }
                                // 3) numeric keys em wizardData
                                for (let i = 1; i <= 6; i++) {
                                    const s = window.wizardData[i];
                                    if (s && s[key] != null) return s[key];
                                }
                                // 4) direto em wizardData
                                if (window.wizardData[key] != null) return window.wizardData[key];
                            }
                            // 5) planejamentoWizard.formData
                            if (window.planejamentoWizard && window.planejamentoWizard.formData) {
                                for (let i = 1; i <= 6; i++) {
                                    const s = window.planejamentoWizard.formData[i];
                                    if (s && s[key] != null) return s[key];
                                }
                                if (window.planejamentoWizard.formData[key] != null) return window.planejamentoWizard
                                    .formData[key];
                            }

                            // 5.1) Fallback do período vindo da Step 5 (preview)
                            if (key === 'data_inicio' && window.planejamentoPeriodo && window.planejamentoPeriodo
                                .inicio) {
                                return window.planejamentoPeriodo.inicio;
                            }
                            if (key === 'data_fim' && window.planejamentoPeriodo && window.planejamentoPeriodo.fim) {
                                return window.planejamentoPeriodo.fim;
                            }

                            // 6) DOM hidden/input por name
                            const elByName = document.querySelector(` [name = "${key}"] `);
                            if (elByName && elByName.value != null && elByName.value !== '') return elByName.value;
                            // 7) DOM por id
                            const elById = document.getElementById(key);
                            if (elById && elById.value != null && elById.value !== '') return elById.value;

                            // 8) Defaults defensivos para etapa 4
                            if (key === 'aulas_por_semana') return 5;
                            if (key === 'carga_horaria_aula') return 0.75;
                            return undefined;
                        };
                        required.forEach((key) => {
                            const v = flattened[key];
                            if (v === undefined || v === null || v === '') {
                                const alt = tryGetFromStores(key);
                                if (alt !== undefined) flattened[key] = alt;
                            }
                        });

                        // 5) Normalizar arrays conhecidos (se vierem como string JSON)
                        const arrayKeys = ['campos_experiencia', 'objetivos_aprendizagem'];
                        arrayKeys.forEach((k) => {
                            const v = flattened[k];
                            if (typeof v === 'string') {
                                try {
                                    flattened[k] = JSON.parse(v);
                                } catch (_) {}
                            }
                        });

                        // 6) Campos de período obrigatórios
                        const periodKeys = ['aulas_por_semana', 'carga_horaria_aula', 'data_inicio', 'data_fim',
                            'numero_dias', 'bimestre', 'ano_letivo'
                        ];
                        periodKeys.forEach((key) => {
                            const v = flattened[key];
                            if (v === undefined || v === null || v === '') {
                                const alt = tryGetFromStores(key);
                                if (alt !== undefined) flattened[key] = alt;
                            }
                        });

                        // 6.1) Derivar período a partir de dias planejados, se necessário
                        const diaKeys = () => {
                            const diasPlanejados = [];
                            try {
                                if (flattened.planejamentos_diarios && typeof flattened.planejamentos_diarios ===
                                    'object') {
                                    Object.entries(flattened.planejamentos_diarios).forEach(([d, ok]) => {
                                        if (ok) diasPlanejados.push(d);
                                    });
                                }
                                if (diasPlanejados.length === 0 && flattened.dados_por_dia && typeof flattened
                                    .dados_por_dia === 'object') {
                                    Object.keys(flattened.dados_por_dia).forEach((d) => diasPlanejados.push(d));
                                }
                            } catch (_) {}
                            return diasPlanejados;
                        };

                        if ((!flattened.data_inicio || !flattened.data_fim || !flattened.numero_dias) && typeof diaKeys ===
                            'function') {
                            const dias = diaKeys().filter(Boolean).sort();
                            if (dias.length > 0) {
                                flattened.data_inicio = flattened.data_inicio || dias[0];
                                flattened.data_fim = flattened.data_fim || dias[dias.length - 1];
                                // Contagem simples de dias planejados
                                flattened.numero_dias = flattened.numero_dias || dias.length;
                            }
                        }

                        // 6.2) Definir tipo_periodo válido se ainda ausente
                        if (!flattened.tipo_periodo || flattened.tipo_periodo === '') {
                            // Heurística simples baseada em numero_dias
                            const nd = Number(flattened.numero_dias || 0);
                            if (nd > 0 && nd <= 7) {
                                flattened.tipo_periodo = 'semanal';
                            } else if (nd > 7 && nd <= 15) {
                                flattened.tipo_periodo = 'quinzenal';
                            } else if (nd > 15 && nd <= 31) {
                                flattened.tipo_periodo = 'mensal';
                            } else if (nd > 31 && nd <= 62) {
                                flattened.tipo_periodo = 'bimestral';
                            } else if (nd > 62 && nd <= 93) {
                                flattened.tipo_periodo = 'trimestral';
                            } else if (nd > 93 && nd <= 186) {
                                flattened.tipo_periodo = 'semestral';
                            } else if (nd > 186) {
                                flattened.tipo_periodo = 'anual';
                            } else {
                                // Default permissivo
                                flattened.tipo_periodo = 'mensal';
                            }
                        }

                        // 6.3) Mapear turno string -> turno_id via backend se necessário
                        if ((!flattened.turno_id || flattened.turno_id === '') && this.planejamento) {
                            const turnoStr = (this.planejamento.turno || '').toString().toLowerCase();
                            const modalidadeId = flattened.modalidade_ensino_id || this.planejamento.modalidade_ensino_id ||
                                this.planejamento.modalidade;
                            if (turnoStr && modalidadeId) {
                                try {
                                    const turnosUrl = '{{ route('planejamentos.turnos-disponiveis') }}' + ` ? modalidade_id = $ {
                            encodeURIComponent(modalidadeId)
                        }
                        `;
                                    const respTurnos = await fetch(turnosUrl, {
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    });
                                    if (respTurnos.ok) {
                                        const lista = await respTurnos.json();
                                        if (Array.isArray(lista)) {
                                            const match = lista.find(t => (t.value || '').toLowerCase() === turnoStr || (t
                                                .label || '').toLowerCase() === turnoStr);
                                            if (match && match.id) {
                                                flattened.turno_id = match.id;
                                            }
                                        }
                                    }
                                } catch (e) {
                                    console.warn('[Wizard] Falhou ao mapear turno para ID:', e);
                                }
                            }
                        }

                        // 6.4) Normalizar planejamentos_diarios para array de objetos esperado pelo backend
                        try {
                            const dailyMap = (this.formData?.[5]?.dados_por_dia) || flattened.dados_por_dia || {};
                            const dailyStatusObj = (this.formData?.[5]?.planejamentos_diarios) || (typeof flattened
                                .planejamentos_diarios === 'object' && !Array.isArray(flattened.planejamentos_diarios) ?
                                flattened.planejamentos_diarios : {});

                            if (!Array.isArray(flattened.planejamentos_diarios)) {
                                const diarios = Object.entries(dailyMap).map(([dateKey, v]) => {
                                    let dt;
                                    try {
                                        dt = new Date(dateKey);
                                    } catch (_) {
                                        dt = null;
                                    }
                                    return {
                                        data: dateKey,
                                        dia_semana: dt && !isNaN(dt.getDay()) ? dt.getDay() : null,
                                        planejado: !!(dailyStatusObj && dailyStatusObj[dateKey]),
                                        campos_experiencia: Array.isArray(v?.campos_experiencia) ? v
                                            .campos_experiencia : [],
                                        saberes_conhecimentos: (typeof v?.saberes_conhecimentos === 'string') ? v
                                            .saberes_conhecimentos : (Array.isArray(v?.saberes_conhecimentos) ? v
                                                .saberes_conhecimentos : (Array.isArray(v?.saberes_selecionados) ? v
                                                    .saberes_selecionados : '')),
                                        objetivos_especificos: (typeof v?.objetivos_especificos === 'string') ? v
                                            .objetivos_especificos : '',
                                        objetivos_aprendizagem: Array.isArray(v?.objetivos_aprendizagem) ? v
                                            .objetivos_aprendizagem : [],
                                        metodologia: v?.metodologia || '',
                                        recursos_predefinidos: Array.isArray(v?.recursos_predefinidos) ? v
                                            .recursos_predefinidos : [],
                                        recursos_personalizados: v?.recursos_personalizados || ''
                                    };
                                });
                                flattened.planejamentos_diarios = diarios;

                                // Preencher campos obrigatórios por dia com fallback dos campos globais, se necessário
                                const globCampos = Array.isArray(flattened.campos_experiencia) ? flattened
                                    .campos_experiencia : [];
                                const globObjApr = Array.isArray(flattened.objetivos_aprendizagem) ? flattened
                                    .objetivos_aprendizagem : [];
                                const globSaberes = (typeof flattened.saberes_conhecimentos === 'string' && flattened
                                        .saberes_conhecimentos.trim().length > 0) ?
                                    flattened.saberes_conhecimentos :
                                    (Array.isArray(flattened.saberes_conhecimentos) && flattened.saberes_conhecimentos
                                        .length > 0) ?
                                    flattened.saberes_conhecimentos :
                                    (Array.isArray(flattened.saberes_selecionados) && flattened.saberes_selecionados
                                        .length > 0) ?
                                    flattened.saberes_selecionados :
                                    '';
                                flattened.planejamentos_diarios = flattened.planejamentos_diarios.map(d => ({
                                    ...d,
                                    campos_experiencia: (Array.isArray(d.campos_experiencia) && d
                                        .campos_experiencia.length > 0) ? d.campos_experiencia : globCampos,
                                    objetivos_aprendizagem: (Array.isArray(d.objetivos_aprendizagem) && d
                                            .objetivos_aprendizagem.length > 0) ? d.objetivos_aprendizagem :
                                        globObjApr,
                                    saberes_conhecimentos: ((typeof d.saberes_conhecimentos === 'string' && d
                                            .saberes_conhecimentos.trim().length > 0) ||
                                        (Array.isArray(d.saberes_conhecimentos) && d.saberes_conhecimentos
                                            .length > 0)) ? d.saberes_conhecimentos : globSaberes
                                }));
                            }

                            if ((!flattened.numero_dias || Number(flattened.numero_dias) <= 0) && Array.isArray(flattened
                                    .planejamentos_diarios)) {
                                const datasUnicas = [...new Set(flattened.planejamentos_diarios.map(d => d.data).filter(
                                    Boolean))];
                                flattened.numero_dias = datasUnicas.length;
                            }
                        } catch (e) {
                            console.warn('[Wizard] Falha ao normalizar planejamentos_diarios:', e);
                            if (!Array.isArray(flattened.planejamentos_diarios)) {
                                flattened.planejamentos_diarios = [];
                            }
                        }

                        // 7) Incluir planejamento_id (edição)
                        if (this.planejamento?.id) {
                            flattened.planejamento_id = this.planejamento.id;
                        }

                        // 7.1) Fallbacks para turno_id, turma_id e disciplina_id a partir dos stores/DOM
                        try {
                            if (!flattened.turno_id) {
                                flattened.turno_id = this.planejamento?.turno_id ||
                                    this.planejamento?.turno?.id ||
                                    this.formData?.[2]?.turno_id ||
                                    this.formData?.[2]?.turno?.id ||
                                    window?.planejamentoWizard?.formData?.[2]?.turno_id ||
                                    window?.planejamentoWizard?.formData?.[2]?.turno?.id ||
                                    window?.wizardData?.step2?.turno_id ||
                                    window?.wizardData?.step2?.turno?.id ||
                                    window?.wizard?.formData?.[2]?.turno_id ||
                                    window?.planejamento?.turno?.id ||
                                    document.querySelector('[name="turno_id"], #turno_id')?.value ||
                                    flattened.turno_id;
                            }
                            // Se ainda não houver turno_id, obter primeiro turno disponível da modalidade
                            if (!flattened.turno_id) {
                                const modalidadeId = flattened.modalidade_ensino_id || this.planejamento
                                    ?.modalidade_ensino_id || this.planejamento?.modalidade;
                                if (modalidadeId) {
                                    try {
                                        const turnosUrl = '{{ route('planejamentos.turnos-disponiveis') }}' + ` ? modalidade_id =
                            $ {
                                encodeURIComponent(modalidadeId)
                            }
                        `;
                                        const respTurnos = await fetch(turnosUrl, {
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest'
                                            }
                                        });
                                        if (respTurnos.ok) {
                                            const lista = await respTurnos.json();
                                            if (Array.isArray(lista) && lista.length > 0) {
                                                flattened.turno_id = lista[0].id || lista[0].value || lista[0];
                                            }
                                        }
                                    } catch (e) {
                                        console.warn('[Wizard] Fallback turno_id por modalidade falhou:', e);
                                    }
                                }
                            }
                            if (!flattened.turma_id) {
                                flattened.turma_id = this.planejamento?.turma_id ||
                                    this.planejamento?.turma?.id ||
                                    this.formData?.[3]?.turma_id ||
                                    this.formData?.[3]?.turma?.id ||
                                    window?.planejamentoWizard?.formData?.[3]?.turma_id ||
                                    window?.planejamentoWizard?.formData?.[3]?.turma?.id ||
                                    window?.wizardData?.step3?.turma_id ||
                                    window?.wizardData?.step3?.turma?.id ||
                                    window?.wizard?.formData?.[3]?.turma_id ||
                                    window?.planejamento?.turma?.id ||
                                    document.querySelector('[name="turma_id"], #turma_id')?.value ||
                                    flattened.turma_id;
                            }
                            if (!flattened.disciplina_id) {
                                flattened.disciplina_id = this.planejamento?.disciplina_id ||
                                    this.planejamento?.disciplina?.id ||
                                    this.formData?.[3]?.disciplina_id ||
                                    this.formData?.[3]?.disciplina?.id ||
                                    window?.planejamentoWizard?.formData?.[3]?.disciplina_id ||
                                    window?.planejamentoWizard?.formData?.[3]?.disciplina?.id ||
                                    window?.wizardData?.step3?.disciplina_id ||
                                    window?.wizardData?.step3?.disciplina?.id ||
                                    window?.wizard?.formData?.[3]?.disciplina_id ||
                                    window?.planejamento?.disciplina?.id ||
                                    document.querySelector('[name="disciplina_id"], #disciplina_id')?.value ||
                                    flattened.disciplina_id;
                            }
                        } catch (e) {
                            console.warn('[Wizard] Falha ao aplicar fallbacks de IDs:', e);
                        }

                        console.log('[Wizard] DEBUG finishWizard payload', flattened);

                        // 8) Enviar
                        const response = await fetch('{{ route('planejamentos.wizard.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(flattened)
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.showSuccess('Planejamento salvo com sucesso!');
                            setTimeout(() => {
                                const pid = result.planejamento_id || this.planejamento?.id;
                                const viewUrl = pid ?
                                    `
                        {{ route('planejamentos.view', ['planejamento' => '__ID__']) }}`.replace('__ID__', pid) :
                                    '{{ route('planejamentos.index') }}';
                                const shouldGoToIndex = (flattened?.save_as_draft === true) || ((flattened
                                    ?.status || '').toLowerCase() === 'rascunho');
                                const targetUrl = shouldGoToIndex ? '{{ route('planejamentos.index') }}' : (result
                                    .redirect_url || viewUrl);
                                window.location.href = targetUrl;
                            }, 1500);
                        } else {
                            this.showValidationErrors(result.errors);
                        }

                    } catch (error) {
                        console.error('Erro ao finalizar wizard:', error);
                        this.showError('Erro ao salvar planejamento. Tente novamente.');
                    }
                }

                showError(message) {
                    const svc = window.AlertService;
                    if (svc && typeof svc.error === 'function') {
                        svc.error('Erro: ' + message);
                        return;
                    }
                    console.error('[Wizard] Erro:', message);
                    const container = document.querySelector('#step-content');
                    if (container) {
                        container.insertAdjacentHTML('beforeend', ` < div class = "mt-3 p-3 bg-red-100 text-red-800 rounded" > Erro
                            : $ {
                                message
                            } < /div>`);
                    } else {
                        alert('Erro: ' + message);
                    }
                }

                showSuccess(message) {
                    const svc = window.AlertService;
                    if (svc && typeof svc.success === 'function') {
                        svc.success('Sucesso: ' + message);
                        return;
                    }
                    console.log('[Wizard] Sucesso:', message);
                    // Fallback simples
                    const container = document.querySelector('#step-content');
                    if (container) {
                        container.insertAdjacentHTML('beforeend',
                            `<div class="mt-3 p-3 bg-green-100 text-green-800 rounded">Sucesso: ${message}</div>`);
                    }
                }

                showValidationErrors(errors) {
                    if (!errors) {
                        this.showError('Ocorreu um erro desconhecido validation.');
                        return;
                    }
                    // Implementar exibição de erros de validação
                    let message = 'Corrija os seguintes erros:\n';
                    Object.values(errors).forEach(error => {
                        message += '- ' + error + '\n';
                    });
                    alert(message);
                }
            }

            // Inicializar wizard quando a página carregar
            document.addEventListener('DOMContentLoaded', function() {
                window.planejamentoWizard = new PlanejamentoWizard();
            });
        </script>

        <style>
            .step-nav {
                @apply opacity-50 transition-opacity duration-200;
            }

            .step-nav.active {
                @apply opacity-100;
            }

            .step-nav.completed {
                @apply opacity-75;
            }

            .step-nav .step-icon {
                @apply bg-gray-200 text-gray-600;
            }

            .step-nav.active .step-icon {
                @apply bg-blue-600 text-white;
            }

            .step-nav.completed .step-icon {
                @apply bg-green-600 text-white;
            }

            .step-nav .step-title {
                @apply text-gray-600;
            }

            .step-nav.active .step-title {
                @apply text-blue-600 font-semibold;
            }

            .step-nav.completed .step-title {
                @apply text-green-600;
            }
        </style>
    @endpush
@endsection
