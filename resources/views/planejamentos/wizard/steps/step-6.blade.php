<!-- Etapa 6: RevisÃ£o e FinalizaÃ§Ã£o -->
<form id="step-6-form">
    <div class="space-y-6">
        <div class="border-b border-gray-200 pb-4">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <i class="fas fa-check-circle text-blue-600 mr-2"></i>
                Revisão e Finalização
            </h3>
            <p class="text-gray-600 mt-1">Revise todas as informações antes de finalizar o planejamento</p>
        </div>

        <!-- Indicador de Status do Planejamento -->
        <div id="status-indicator" class="mb-6" style="display: none;">
            <div class="flex items-center justify-center">
                <div id="status-badge" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium">
                    <i id="status-icon" class="mr-2"></i>
                    <span id="status-text">Status do Planejamento</span>
                </div>
            </div>
        </div>

        <script>
            // Declaração global da variável radioAcaoFinalizacao para ser acessível em todo o escopo
            var radioAcaoFinalizacao;
            document.addEventListener('DOMContentLoaded', function() {
                radioAcaoFinalizacao = document.querySelectorAll('input[name="acao_finalizacao"]');
            });
        </script>

        @if(isset($planejamento) && $planejamento)
            <script>
                window.wizardData = window.wizardData || {};
                window.wizardData.planejamento_id = @json($planejamento->id);
                window.wizardData.titulo = @json($planejamento->titulo ?? null);
                window.wizardData.escola_id = @json($planejamento->escola_id);
                window.wizardData.turno_id = @json($planejamento->turno_id);
                window.wizardData.turma_id = @json($planejamento->turma_id);
                window.wizardData.disciplina_id = @json($planejamento->disciplina_id);
                window.wizardData.professor_id = @json($planejamento->professor_id);
                window.wizardData.modalidade_ensino_id = @json($planejamento->modalidade_id ?? null);
                window.wizardData.nivel_ensino_id = @json($planejamento->nivel_ensino_id ?? null);
                window.wizardData.tipo_periodo = @json($planejamento->tipo_periodo ?? null);
                window.wizardData.numero_dias = @json($planejamento->numero_dias ?? null);
                window.wizardData.data_inicio = @json($planejamento->data_inicio ? (\Carbon\Carbon::parse($planejamento->data_inicio)->format('Y-m-d')) : null);
                window.wizardData.data_fim = @json($planejamento->data_fim ? (\Carbon\Carbon::parse($planejamento->data_fim)->format('Y-m-d')) : null);
                window.wizardData.carga_horaria_aula = @json($planejamento->carga_horaria_aula ?? null);
                window.wizardData.status = @json($planejamento->status_efetivo ?? 'rascunho');
                window.wizardData.status_formatado = @json($planejamento->status_efetivo_formatado ?? 'Rascunho');
            </script>
            <input type="hidden" name="planejamento_id" value="{{ $planejamento->id }}">
            @if($planejamento->titulo)
                <input type="hidden" name="titulo" value="{{ $planejamento->titulo }}">
            @endif
            @if($planejamento->escola_id)
                <input type="hidden" name="escola_id" value="{{ $planejamento->escola_id }}">
            @endif
            @if($planejamento->turno_id)
                <input type="hidden" name="turno_id" value="{{ $planejamento->turno_id }}">
            @endif
            @if($planejamento->turma_id)
                <input type="hidden" name="turma_id" value="{{ $planejamento->turma_id }}">
            @endif
            @if($planejamento->disciplina_id)
                <input type="hidden" name="disciplina_id" value="{{ $planejamento->disciplina_id }}">
            @endif
            @if($planejamento->professor_id)
                <input type="hidden" name="professor_id" value="{{ $planejamento->professor_id }}">
            @endif
            @if($planejamento->modalidade_id)
                <input type="hidden" name="modalidade_ensino_id" value="{{ $planejamento->modalidade_id }}">
            @endif
            @if($planejamento->nivel_ensino_id)
                <input type="hidden" name="nivel_ensino_id" value="{{ $planejamento->nivel_ensino_id }}">
            @endif
            @if($planejamento->tipo_periodo)
                <input type="hidden" name="tipo_periodo" value="{{ $planejamento->tipo_periodo }}">
            @endif
            @if($planejamento->numero_dias)
                <input type="hidden" name="numero_dias" value="{{ $planejamento->numero_dias }}">
            @endif
            @if($planejamento->data_inicio)
                <input type="hidden" name="data_inicio" value="{{ \Carbon\Carbon::parse($planejamento->data_inicio)->format('Y-m-d') }}">
            @endif
            @if($planejamento->data_fim)
                <input type="hidden" name="data_fim" value="{{ \Carbon\Carbon::parse($planejamento->data_fim)->format('Y-m-d') }}">
            @endif
            @if(!is_null($planejamento->carga_horaria_aula))
                <input type="hidden" name="carga_horaria_aula" value="{{ $planejamento->carga_horaria_aula }}">
            @endif
        @endif

        <!-- Resumo Geral -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                <i class="fas fa-clipboard-list mr-2"></i>
                Resumo do Planejamento
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- InformaÃ§Ã£es BÃ¡sicas -->
                <div class="space-y-3">
                    <h5 class="font-medium text-blue-800 border-b border-blue-200 pb-1">Informações Básicas</h5>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Título:</span>
                            <span id="resumo-titulo" class="font-medium text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Modalidade:</span>
                            <span id="resumo-modalidade" class="font-medium text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nível de Ensino:</span>
                            <span id="resumo-nivel" class="font-medium text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Unidade Escolar:</span>
                            <span id="resumo-escola" class="font-medium text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Turno:</span>
                            <span id="resumo-turno" class="font-medium text-gray-900">-</span>
                        </div>
                    </div>
                </div>

                <!-- Turma e Disciplina -->
                <div class="space-y-3">
                    <h5 class="font-medium text-blue-800 border-b border-blue-200 pb-1">Turma e Disciplina</h5>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Turma:</span>
                            <span id="resumo-turma" class="font-medium text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Disciplina:</span>
                            <span id="resumo-disciplina" class="font-medium text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Professor:</span>
                            <span id="resumo-professor" class="font-medium text-gray-900">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nº de Alunos:</span>
                            <span id="resumo-alunos" class="font-medium text-gray-900">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PerÃ­odo e DuraÃ§Ã£o -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h5 class="font-medium text-green-800 mb-3 flex items-center">
                <i class="fas fa-calendar mr-2"></i>
                Período e Duração
            </h5>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Tipo de Período:</span>
                    <div id="resumo-tipo-periodo" class="font-medium text-gray-900">-</div>
                </div>
                <div>
                    <span class="text-gray-600">Total de Aulas:</span>
                    <div id="resumo-total-aulas" class="font-medium text-gray-900">-</div>
                </div>
                <div>
                    <span class="text-gray-600">Carga Horária:</span>
                    <div id="resumo-carga-horaria" class="font-medium text-gray-900">-</div>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-green-200">
                <span class="text-gray-600 text-sm">Período:</span>
                <div id="resumo-periodo-detalhado" class="font-medium text-gray-900">-</div>
            </div>
        </div>

        <!-- Conteúdo Pedagógico -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <h5 class="font-medium text-purple-800 mb-3 flex items-center">
                <i class="fas fa-book mr-2"></i>
                Conteúdo Pedagógico
            </h5>

            <div class="space-y-4">
                <!-- Campos de Experiência -->
                <div>
                    <span class="text-gray-600 text-sm font-medium">Campos de Experiência:</span>
                    <div id="resumo-campos-experiencia" class="mt-1 text-sm text-gray-900">-</div>
                </div>

                <!-- Saberes e Conhecimentos -->
                <div>
                    <span class="text-gray-600 text-sm font-medium">Saberes e Conhecimentos:</span>
                    <div id="resumo-saberes"
                        class="mt-1 text-sm text-gray-900 bg-white p-2 rounded border max-h-20 overflow-y-auto">-</div>
                </div>

                <!-- Objetivos de Aprendizagem -->
                <div>
                    <span class="text-gray-600 text-sm font-medium">Objetivos de Aprendizagem:</span>
                    <div id="resumo-objetivos-aprendizagem" class="mt-1 text-sm text-gray-900">-</div>
                </div>
            </div>
        </div>

        <!-- Verificações -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h5 class="font-medium text-yellow-800 mb-3 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Verificações
            </h5>

            <div id="lista-validacoes" class="space-y-2">
                <!-- Preenchido via JavaScript -->
            </div>
        </div>

        <!-- Conflitos Detectados -->
        <div id="secao-conflitos" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
            <h5 class="font-medium text-red-800 mb-3 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                Conflitos Detectados
            </h5>

            <div id="lista-conflitos" class="space-y-2">
                <!-- Preenchido via JavaScript -->
            </div>

            <div class="mt-3 p-3 bg-red-100 border border-red-300 rounded">
                <p class="text-sm text-red-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Resolva os conflitos antes de finalizar o planejamento.
                </p>
            </div>
        </div>

        <!-- Opções de Finalização -->
        <div class="border-t border-gray-200 pt-6">
            <h5 class="font-medium text-gray-800 mb-4">Como deseja finalizar?</h5>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Salvar como Rascunho -->
                <label id="opcao-rascunho"
                    class="relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm focus:outline-none hover:border-gray-400">
                    <input type="radio" name="acao_finalizacao" value="rascunho" class="sr-only" checked>
                    <span class="flex flex-1">
                        <span class="flex flex-col">
                            <span class="block text-sm font-medium text-gray-900 flex items-center">
                                <i class="fas fa-save text-gray-600 mr-2"></i>
                                Salvar como Rascunho
                            </span>
                            <span class="mt-1 flex items-center text-sm text-gray-500">
                                Poderá editar posteriormente
                            </span>
                        </span>
                    </span>
                </label>

                <input type="hidden" id="campos_anteriores" name="campos_anteriores" value="">

                <!-- Finalizar para Revisão -->
                <label id="opcao-revisao"
                    class="relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm focus:outline-none hover:border-blue-500">
                    <input type="radio" name="acao_finalizacao" value="revisao" class="sr-only">
                    <span class="flex flex-1">
                        <span class="flex flex-col">
                            <span class="block text-sm font-medium text-gray-900 flex items-center">
                                <i class="fas fa-eye text-blue-600 mr-2"></i>
                                Enviar para Revisão
                            </span>
                            <span class="mt-1 flex items-center text-sm text-gray-500">
                                Aguardará aprovação da coordenação
                            </span>
                        </span>
                    </span>
                </label>

                <!-- Finalizar e Aprovar -->
                @if(auth()->check() && isset($planejamento) && auth()->id() !== ($planejamento->user_id ?? null))
                @permission('planejamentos.aprovar')
                <label
                    class="relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm focus:outline-none hover:border-green-500">
                    <input type="radio" name="acao_finalizacao" value="aprovar" class="sr-only">
                    <span class="flex flex-1">
                        <span class="flex flex-col">
                            <span class="block text-sm font-medium text-gray-900 flex items-center">
                                <i class="fas fa-check text-green-600 mr-2"></i>
                                Finalizar e Aprovar
                            </span>
                            <span class="mt-1 flex items-center text-sm text-gray-500">
                                Planejamento ficará ativo
                            </span>
                        </span>
                    </span>
                </label>
                @endpermission
                @endif
            </div>
        </div>

        <!-- Observações Finais -->
        <div>
            <label for="observacoes_finais" class="block text-sm font-medium text-gray-700 mb-2">
                Observações Finais
            </label>
            <textarea name="observacoes_finais" id="observacoes_finais" rows="3"
                placeholder="Adicione observações ou comentários sobre este planejamento..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 resize-none">{{ old('observacoes_finais', $planejamento->observacoes_finais ?? '') }}</textarea>
            @error('observacoes_finais')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Termos e Condições -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <label class="flex items-start">
                <input type="checkbox" id="checkbox_termos" name="aceita_termos" value="1"
                    class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <div class="ml-3 text-sm text-gray-700">
                    <span class="font-medium">Declaro que:</span>
                    <ul class="mt-1 space-y-1 text-xs">
                        <li>As informações fornecidas são verdadeiras e precisas</li>
                        <li>O planejamento está alinhado com a BNCC e diretrizes pedagógicas</li>
                        <li>Tenho autorização para criar este planejamento</li>
                        <li>Estou ciente das responsabilidades pedagógicas envolvidas</li>
                    </ul>
                </div>
            </label>
            <!-- Removido campo oculto duplicado 'aceitar_termos_hidden' para evitar conflito -->
            <p id="erro_termos" class="text-red-500 text-xs mt-1" style="display: none;">É necessário aceitar os termos e condições.</p>
            @error('aceitar_termos')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Ações de Finalização -->
        <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
            <button type="button" id="btn-preview"
                class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 flex items-center justify-center">
                <i class="fas fa-eye mr-2"></i>
                Visualizar Preview
            </button>

            <button type="button" id="btn-exportar"
                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center justify-center">
                <i class="fas fa-download mr-2"></i>
                Exportar PDF
            </button>
        </div>

        <!-- Modal de Preview -->
        <div id="modal-preview"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Preview do Planejamento</h3>
                    <button type="button" id="fechar-preview" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="conteudo-preview" class="max-h-96 overflow-y-auto border border-gray-200 rounded p-4">
                    <!-- Conteúdo do preview será carregado aqui -->
                </div>
            </div>
        </div>
    </div>

    @if(auth()->check() && isset($planejamento) && auth()->id() !== ($planejamento->user_id ?? null))
    @permission('planejamentos.aprovar')
    <div class="mt-4 flex flex-col sm:flex-row gap-3">
        <button type="button" id="btn-solicitar-correcao" class="flex-1 bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 flex items-center justify-center">
            <i class="fas fa-undo mr-2"></i>
            Solicitar correção
        </button>
        <button type="button" id="btn-aprovar" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center justify-center">
            <i class="fas fa-check mr-2"></i>
            Finalizar e aprovar
        </button>
    </div>
    @endpermission
    @endif

</form>

@stack('scripts')
<script>
    (function() {
        'use strict';

        // Fallback para AlertService para evitar erros quando indisponível
        window.AlertService = window.AlertService || {
            success: function(msg){ try { console.log('[Alert] success:', msg); } catch(e) {} },
            error: function(msg){ try { console.error('[Alert] error:', msg); } catch(e) {} },
            info: function(msg){ try { console.log('[Alert] info:', msg); } catch(e) {} }
        };

        // Definições globais de base path e headers para fetch
        const BASE_PREFIX = (document.querySelector('meta[name="app-base-path"]')?.getAttribute('content') || window.__APP_BASE_PATH__ || '').trim();
        function joinUrl(path) {
            const b = BASE_PREFIX.replace(/\/+$/, '');
            const p = String(path || '').replace(/^\/+/, '');
            return b ? `${b}/${p}` : `/${p}`;
        }
        const commonHeaders = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        if (csrfToken) commonHeaders['X-CSRF-TOKEN'] = csrfToken;

        console.log('[Step 6] Inicializando Etapa 6 - Versão Reescrita');

        // Configuração global do wizard
        window.step6Config = {
            requiredFields: [
                'titulo', 'modalidade_ensino_id', 'nivel_ensino_id', 'escola_id',
                'turno_id', 'turma_id', 'disciplina_id', 'professor_id', 'tipo_periodo',
                'campos_experiencia', 'objetivos_aprendizagem', 'aceita_termos'
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

            // Configurar indicador de status
            configurarIndicadorStatus();

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
                    const valor = window.planejamentoWizard.formData[key];
                    // Se a chave for numérica (ex.: 1..6), criar alias stepN
                    const keyStr = String(key);
                    if (/^\d+$/.test(keyStr)) {
                        const stepName = `step${keyStr}`;
                        if (!window.wizardData[stepName]) {
                            window.wizardData[stepName] = {};
                        }
                        // Mesclar dados deste step
                        if (valor && typeof valor === 'object') {
                            Object.assign(window.wizardData[stepName], valor);
                        }
                    } else {
                        // Copiar chaves não numéricas diretamente
                        if (!window.wizardData[key]) {
                            window.wizardData[key] = valor;
                        }
                    }
                });
            }

            // Alias também para possíveis chaves numéricas já presentes em window.wizardData (ex.: wizardData[1])
            Object.keys(window.wizardData).forEach(k => {
                const kStr = String(k);
                if (/^\d+$/.test(kStr)) {
                    const stepName = `step${kStr}`;
                    if (!window.wizardData[stepName]) {
                        window.wizardData[stepName] = {};
                    }
                    const val = window.wizardData[k];
                    if (val && typeof val === 'object') {
                        Object.assign(window.wizardData[stepName], val);
                    }
                }
            });

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

            // Em ediÃ§Ã£o, garantir campo oculto de planejamento_id
            const pid = getWizardDataFromAllSteps('planejamento_id') || (window.wizardData && window.wizardData.planejamento_id) || null;
            if (pid) {
                criarCampoOculto('planejamento_id', pid);
            }

            console.log('[Step 6] Dados transferidos:', window.wizardData);
        }

        function getWizardDataFromAllSteps(campo) {
            // Verificar em todos os steps do wizard
            for (let step = 1; step <= 5; step++) {
                if (window.wizardData[`step${step}`] && window.wizardData[`step${step}`][campo]) {
                    return window.wizardData[`step${step}`][campo];
                }
            }

            // Verificar em chaves numéricas diretas em window.wizardData (ex.: wizardData[1])
            for (let step = 1; step <= 6; step++) {
                if (window.wizardData[step] && window.wizardData[step][campo]) {
                    return window.wizardData[step][campo];
                }
            }

            // Verificar nos steps numéricos dentro de planejamentoWizard.formData
            if (window.planejamentoWizard && window.planejamentoWizard.formData) {
                for (let step = 1; step <= 6; step++) {
                    const fd = window.planejamentoWizard.formData[step];
                    if (fd && fd[campo]) {
                        return fd[campo];
                    }
                }
            }

            // Verificar no formData do planejamentoWizard
            if (window.planejamentoWizard && window.planejamentoWizard.formData && window.planejamentoWizard.formData[campo]) {
                return window.planejamentoWizard.formData[campo];
            }

            // Verificar diretamente no wizardData
            if (window.wizardData[campo]) {
                return window.wizardData[campo];
            }

            // Fallback: tentar obter do DOM (input/select/textarea por name)
            const el = document.querySelector(`[name="${campo}"]`);
            if (el) {
                return el.value;
            }

            return null;
        }

        function criarCampoOculto(nome, valor) {
            const form = document.getElementById('step-6-form');
            if (!form) return;

            // Verificar se o campo jÃ¡ existe
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
            console.log('[Step 6] Configurando validaÃ§Ã£o em tempo real');

            // ValidaÃ§Ã£o do checkbox de termos
            const checkboxTermos = document.getElementById('checkbox_termos');
            if (checkboxTermos) {
                checkboxTermos.addEventListener('change', function() {
                    validarPlanejamento();
                });
            }

            // ValidaÃ§Ã£o de outros campos se necessÃ¡rio
            const form = document.getElementById('step-6-form');
            if (form) {
                form.addEventListener('input', function() {
                    validarPlanejamento();
                });
            }
        }

        function configurarBotoes() {
            console.log('[Step 6] Configurando botÃµes');

            // BotÃ£o Finalizar
            const btnFinalizar = document.getElementById('btn-finalizar');
            if (btnFinalizar) {
                btnFinalizar.addEventListener('click', function(e) {
                    e.preventDefault();
                    enviarDados();
                });
            }

            // BotÃ£o Validar
            const btnValidar = document.getElementById('btn-validar');
            if (btnValidar) {
                btnValidar.addEventListener('click', function(e) {
                    e.preventDefault();
                    validarPlanejamento();
                });
            }

            // BotÃ£o Preview
            const btnPreview = document.getElementById('btn-preview');
            if (btnPreview) {
                btnPreview.addEventListener('click', function(e) {
                    e.preventDefault();
                    mostrarPreview();
                });
            }

            // BotÃ£o Fechar Preview
            const btnFecharPreview = document.getElementById('btn-fechar-preview');
            if (btnFecharPreview) {
                btnFecharPreview.addEventListener('click', function(e) {
                    e.preventDefault();
                    fecharPreview();
                });
            }

            // BotÃ£o Exportar
            const btnExportar = document.getElementById('btn-exportar');
            if (btnExportar) {
                btnExportar.addEventListener('click', function(e) {
                    e.preventDefault();
                    // TODO: Implementar funcionalidade de exportaÃ§Ã£o
                    console.log('[Step 6] ExportaÃ§Ã£o ainda não implementada');
                });
            }
        }


        function validarPlanejamento() {
            console.log('[Step 6] Validando planejamento');
            window.step6Config.validationErrors = [];

            // Validar nivel_ensino_id como nÃºmero inteiro
            if (!validarNivelEnsinoId()) {
                window.step6Config.validationErrors.push('NÃ­vel de ensino Ã© obrigatÃ³rio e deve ser um nÃºmero vÃ¡lido');
            }

            // Validar campos_experiencia como array JSON
            if (!validarCamposExperiencia()) {
                window.step6Config.validationErrors.push('Campos de experiÃªncia devem ser um array vÃ¡lido');
            }

            // Validar objetivos_aprendizagem como array JSON
            if (!validarObjetivosAprendizagem()) {
                window.step6Config.validationErrors.push('Objetivos de aprendizagem devem ser um array vÃ¡lido');
            }

            // Validar saberes_conhecimentos
            if (!validarSaberesConhecimentos()) {
                window.step6Config.validationErrors.push('Saberes e conhecimentos sÃ£o obrigatÃ³rios');
            }



            // Validar planejamento completo
            if (!validarPlanejamentoCompleto()) {
                window.step6Config.validationErrors.push('Dados do planejamento incompletos');
            }

            // Exibir erros de validaÃ§Ã£o
            exibirErrosValidacao();

            // Atualizar estado dos botÃµes
            atualizarEstadoBotoes();

            return window.step6Config.validationErrors.length === 0;
        }

        function validarNivelEnsinoId() {
            const nivelEnsino = getWizardDataFromAllSteps('nivel_ensino_id');
            return nivelEnsino && Number.isInteger(parseInt(nivelEnsino)) && parseInt(nivelEnsino) > 0;
        }

        function validarCamposExperiencia() {
            const camposExp = getWizardDataFromAllSteps('campos_experiencia');
            if (!camposExp) return false;

            try {
                const parsed = typeof camposExp === 'string' ? JSON.parse(camposExp) : camposExp;
                return Array.isArray(parsed) && parsed.length > 0;
            } catch (e) {
                return false;
            }
        }

        function validarObjetivosAprendizagem() {
            const objetivos = getWizardDataFromAllSteps('objetivos_aprendizagem');
            if (!objetivos) return false;

            try {
                const parsed = typeof objetivos === 'string' ? JSON.parse(objetivos) : objetivos;
                return Array.isArray(parsed) && parsed.length > 0;
            } catch (e) {
                return false;
            }
        }

        function validarSaberesConhecimentos() {
            // Não exigir valor global; validação ocorre por diário na Etapa 5 e no backend
            return true;
        }

        function validarPlanejamentoCompleto() {
            const camposObrigatorios = window.step6Config.requiredFields.filter(field => field !== 'aceita_termos');
            
            for (const campo of camposObrigatorios) {
                const valor = getWizardDataFromAllSteps(campo);
                if (!valor || (typeof valor === 'string' && valor.trim() === '')) {
                    console.log(`[Step 6] Campo obrigatÃ³rio ausente: ${campo}`);
                    return false;
                }
            }
            return true;
        }
        // === Validação de diários (Etapa 5) para bloqueio no envio final ===
        function validarDiariosCompletosFrontend() {
    try {
        const esperado = calcularDiasEsperadosDoPeriodo();
        const diariosInfo = coletarDiariosPlanejados();

        // Se não houver como determinar esperado, não bloquear aqui (backend validará)
        const planejados = diariosInfo.planejadosCount;
        let ok = true;
        let msgResumo = '';
        let msgCampos = '';

        if (esperado > 0 && planejados < esperado) {
            const faltam = Math.max(0, esperado - planejados);
            ok = false;
            msgResumo = `Planejamento diário incompleto: faltam ${faltam} de ${esperado} dia(s) planejados.`;
        }

        if (diariosInfo.incompletos && diariosInfo.incompletos.length > 0) {
            ok = false;
            msgCampos = `Dias com campos obrigatórios em falta: ${diariosInfo.incompletos.join('; ')}`;
        }

        return { ok, msgResumo, msgCampos };
    } catch (e) {
        // Em caso de erro, não bloquear no frontend; backend tratará
        return { ok: true };
    }
}

        function calcularDiasEsperadosDoPeriodo() {
            const tipo = getWizardDataFromAllSteps('tipo_periodo');
            const numeroDias = parseInt(getWizardDataFromAllSteps('numero_dias')) || 0;
            const dataInicio = getWizardDataFromAllSteps('data_inicio');
            const dataFim = getWizardDataFromAllSteps('data_fim');

            if (tipo === 'dias' && numeroDias > 0) return numeroDias;

            // Fallback: contar dias úteis entre as datas (seg a sex)
            if (dataInicio && dataFim) {
                const start = new Date(dataInicio);
                const end = new Date(dataFim);
                if (isNaN(start) || isNaN(end) || end < start) return 0;
                let diasUteis = 0;
                const cursor = new Date(start.getTime());
                while (cursor <= end) {
                    const d = cursor.getDay(); // 0=Dom, 6=Sáb
                    if (d !== 0 && d !== 6) diasUteis++;
                    cursor.setDate(cursor.getDate() + 1);
                }
                return diasUteis;
            }
            return 0;
        }

        function coletarDiariosPlanejados() {
    // Tenta extrair dos formatos conhecidos da Etapa 5
    const step5 = (window.wizardData && window.wizardData.step5) || (window.planejamentoWizard && window.planejamentoWizard.formData && window.planejamentoWizard.formData[5]) || {};

    // Formato 1: array de objetos
    const arr = Array.isArray(step5.planejamentos_diarios) ? step5.planejamentos_diarios : [];

    // Formato 2: mapas separados (quando disponível)
    const dailyMap = step5.dailyMap || {};
    const dailyStatus = step5.dailyStatus || {};

    const incompletos = [];
    let planejadosCount = 0;

    if (arr.length > 0) {
        for (const d of arr) {
            const planejado = (!('planejado' in d)) ? true : !!d.planejado;
            if (!planejado) continue;
            planejadosCount++;

            const camposExp = Array.isArray(d.campos_experiencia) ? d.campos_experiencia : [];
            const saberes = (d.saberes_conhecimentos || '').trim();
            const objsApr = Array.isArray(d.objetivos_aprendizagem) ? d.objetivos_aprendizagem : [];

            const faltando = [];
            if (camposExp.length === 0) faltando.push('campos de experiência');
            if (saberes === '') faltando.push('saberes e conhecimentos');
            if (objsApr.length === 0) faltando.push('objetivos de aprendizagem');
            if (faltando.length > 0) {
                incompletos.push(`${(d.data || 'dia sem data')} (${faltando.join(', ')})`);
            }
        }
    } else if (Object.keys(dailyMap).length > 0) {
        for (const [dateKey, v] of Object.entries(dailyMap)) {
            const planejado = !!dailyStatus[dateKey];
            if (!planejado) continue;
            planejadosCount++;

            const camposExp = Array.isArray(v.campos_experiencia) ? v.campos_experiencia : [];
            const saberes = (v.saberes_conhecimentos || '').trim();
            const objsApr = Array.isArray(v.objetivos_aprendizagem) ? v.objetivos_aprendizagem : [];

            const faltando = [];
            if (camposExp.length === 0) faltando.push('campos de experiência');
            if (saberes === '') faltando.push('saberes e conhecimentos');
            if (objsApr.length === 0) faltando.push('objetivos de aprendizagem');
            if (faltando.length > 0) {
                incompletos.push(`${dateKey} (${faltando.join(', ')})`);
            }
        }
    }

    return { planejadosCount, incompletos };
}

        function exibirErrosValidacao() {
            const containerErros = document.getElementById('validation-errors');
            if (!containerErros) return;

            if (window.step6Config.validationErrors.length > 0) {
                containerErros.innerHTML = `
                    <div class="alert alert-danger">
                        <h5>Erros de validação:</h5>
                        <ul>
                            ${window.step6Config.validationErrors.map(erro => `<li>${erro}</li>`).join('')}
                        </ul>
                    </div>
                `;
                containerErros.style.display = 'block';
            } else {
                containerErros.style.display = 'none';
            }
        }

        function atualizarEstadoBotoes() {
            const isValid = window.step6Config.validationErrors.length === 0;
            
            const btnFinalizar = document.getElementById('btn-finalizar');
            if (btnFinalizar) {
                btnFinalizar.disabled = !isValid;
                btnFinalizar.classList.toggle('btn-success', isValid);
                btnFinalizar.classList.toggle('btn-secondary', !isValid);
            }
        }

        function finalizarWizard(finalizar = false) {
            console.log('[Step 6] Finalizando wizard...', { finalizar });

            // Validar antes de finalizar
            if (!validarPlanejamento()) {
                console.log('[Step 6] Validação falhou, não é possível finalizar');
                return false;
            }

            // Se não for finalização, definir como rascunho
            if (!finalizar) {
                const acaoFinalizacao = document.querySelector('input[name="acao_finalizacao"][value="rascunho"]');
                if (acaoFinalizacao) {
                    acaoFinalizacao.checked = true;
                }
            }

            // Chamar função de envio de dados
            enviarDados();
            return false;
        }

        function enviarDados() {
            console.log('[Step 6] Enviando dados via PlanejamentoWizard.finishWizard');

            // Verificar se uma ação de finalização foi selecionada
            const acaoFinalizacao = document.querySelector('input[name="acao_finalizacao"]:checked');
            if (!acaoFinalizacao) {
                window.AlertService.error('Selecione uma ação de finalização (rascunho ou publicar).');
                return false;
            }

            // Exigir aceite de termos
            const checkboxTermos = document.getElementById('checkbox_termos');
            if (checkboxTermos && !checkboxTermos.checked) {
                window.AlertService.error('É necessário aceitar os termos para finalizar.');
                return false;
            }

            // Delegar para o wizard principal (envio JSON), evitando submissÃ£o do formulÃ¡rio
            if (window.wizard && typeof window.wizard.finishWizard === 'function') {
                window.wizard.finishWizard();
            } else if (window.planejamentoWizard && typeof window.planejamentoWizard.finishWizard === 'function') {
                window.planejamentoWizard.finishWizard();
            } else if (typeof window.finalizarWizard === 'function') {
                // Fallback legado
                window.finalizarWizard();
            } else {
                console.warn('[Step 6] Não foi possível localizar a função principal de finalização.');
            }

            return false;
        }

        function configurarFormulario() {
            console.log('[Step 6] Configurando formulário');

            const form = document.getElementById('step-6-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    console.log('[Step 6] Interceptando submit para usar envio JSON do wizard');
                    enviarDados();
                    return false;
                });
            }
        }

        function mostrarPreview() {
            console.log('[Step 6] Mostrando preview');
            
            const modal = document.getElementById('modal-preview');
            if (modal) {
                const htmlPreview = gerarHtmlPreview();
                const previewContent = modal.querySelector('#conteudo-preview');
                if (previewContent) {
                    previewContent.innerHTML = htmlPreview;
                }
                modal.classList.remove('hidden');
            }
        }

        function fecharPreview() {
            console.log('[Step 6] Fechando preview');
            
            const modal = document.getElementById('modal-preview');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function gerarHtmlPreview() {
            console.log('[Step 6] Gerando HTML do preview');

            // Coletar dados de todas as etapas
            const dados = {};
            for (let i = 1; i <= 5; i++) {
                if (window.wizardData && window.wizardData[`step${i}`]) {
                    Object.assign(dados, window.wizardData[`step${i}`]);
                }
            }

            return `
                <div class="preview-planejamento">
                    <h3>Preview do Planejamento</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informações Básicas</h5>
                            <p><strong>Título:</strong> ${dados.titulo || 'Não informado'}</p>
                            <p><strong>Modalidade:</strong> ${dados.modalidade_ensino_nome || 'Não informado'}</p>
                            <p><strong>Nível:</strong> ${dados.nivel_ensino_nome || 'Não informado'}</p>
                            <p><strong>Escola:</strong> ${dados.escola_nome || 'Não informado'}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Detalhes</h5>
                            <p><strong>Turno:</strong> ${dados.turno_nome || 'Não informado'}</p>
                            <p><strong>Turma:</strong> ${dados.turma_nome || 'Não informado'}</p>
                            <p><strong>Disciplina:</strong> ${dados.disciplina_nome || 'Não informado'}</p>
                            <p><strong>Professor:</strong> ${dados.professor_nome || 'Não informado'}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Campos de Experiência</h5>
                            <div id="preview-campos-experiencia">
                                ${dados.campos_experiencia ? JSON.stringify(dados.campos_experiencia) : 'Não informado'}
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Objetivos de Aprendizagem</h5>
                            <div id="preview-objetivos">
                                ${dados.objetivos_aprendizagem ? JSON.stringify(dados.objetivos_aprendizagem) : 'Não informado'}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Declarar e inicializar a variÃ¡vel dados
        const dados = {};
        for (let i = 1; i <= 5; i++) {
            if (window.wizardData && window.wizardData[`step${i}`]) {
                Object.assign(dados, window.wizardData[`step${i}`]);
            }
        }

        console.log('[Step 6] Dados coletados para resumo:', dados);

        // Atualizar elementos do resumo
        const elementos = {
            'resumo-titulo': dados.titulo || 'Não informado',
            'resumo-nivel': dados.nivel_ensino_nome || 'Não informado',
            'resumo-modalidade': dados.modalidade_ensino_nome || 'Não informado',
            'resumo-escola': dados.escola_nome || 'Não informado',
            'resumo-turno': dados.turno_nome || 'Não informado',
            'resumo-turma': dados.turma_nome || 'Não informado',
            'resumo-disciplina': dados.disciplina_nome || 'Não informado',
            'resumo-professor': dados.professor_nome || 'Não informado',
            'resumo-tipo-periodo': dados.tipo_periodo || 'Não informado'
        };

        // Atualizar elementos básicos
        for (const [id, valor] of Object.entries(elementos)) {
            const elemento = document.getElementById(id);
            if (elemento) {
                elemento.textContent = valor;
            }
        }

        // Atualizar campos de experiência
        const camposExperienciaElement = document.getElementById('resumo-campos-experiencia');
        if (camposExperienciaElement) {
            if (dados.campos_experiencia && Array.isArray(dados.campos_experiencia)) {
                camposExperienciaElement.innerHTML = dados.campos_experiencia.map(campo => 
                    `<span class="badge badge-primary mr-1">${campo.nome || campo}</span>`
                ).join('');
            } else {
                camposExperienciaElement.textContent = 'Não informado';
            }
        }

        // Atualizar saberes e conhecimentos
        const saberesElement = document.getElementById('resumo-saberes');
        if (saberesElement) {
            if (dados.saberes_conhecimentos && Array.isArray(dados.saberes_conhecimentos)) {
                saberesElement.innerHTML = dados.saberes_conhecimentos.map(saber => 
                    `<span class="badge badge-info mr-1">${saber.nome || saber}</span>`
                ).join('');
            } else if (typeof dados.saberes_conhecimentos === 'string' && dados.saberes_conhecimentos.trim() !== '') {
                saberesElement.textContent = dados.saberes_conhecimentos.trim();
            } else {
                saberesElement.textContent = 'Não informado';
            }
        }

        // Atualizar objetivos de aprendizagem
        const objetivosElement = document.getElementById('resumo-objetivos-aprendizagem');
        if (objetivosElement) {
            if (dados.objetivos_aprendizagem && Array.isArray(dados.objetivos_aprendizagem)) {
                objetivosElement.innerHTML = dados.objetivos_aprendizagem.map(objetivo => 
                    `<span class="badge badge-success mr-1">${objetivo.nome || objetivo}</span>`
                ).join('');
            } else {
                objetivosElement.textContent = 'Não informado';
            }
        }

        // Removido: criação de campo oculto 'aceita_termos'.
        // O aceite de termos será definido diretamente no envio final.

        // Modificar a função configurarBotoes para usar finalizarWizard
        function configurarBotoes() {
            console.log('[Step6] Configurando botões');

            // Botão Finalizar
            const btnFinalizar = document.querySelector('button[type="submit"]');
            if (btnFinalizar) {
                btnFinalizar.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('[Step6] Botão finalizar clicado');
                    window.finalizarWizard();
                    return false;
                });
            } else {
                console.warn('[Step6] Botão finalizar não encontrado');

                // Tentar encontrar por texto
                const botoes = document.querySelectorAll('button');
                botoes.forEach(botao => {
                    if (botao.textContent.trim().toLowerCase().includes('finalizar')) {
                        botao.addEventListener('click', function(e) {
                            e.preventDefault();
                            console.log('[Step6] Botão finalizar alternativo clicado');
                            window.finalizarWizard();
                            return false;
                        });
                    }
                });
            }

            // Configurar botão de validar
            const btnValidar = document.getElementById('btn-validar');
            if (btnValidar) {
                btnValidar.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Validando planejamento...');
                    const ok = validarPlanejamento();
                    if (ok) {
                        window.AlertService.success('Planejamento validado com sucesso!');
                    } else {
                        window.AlertService.error('Corrija os erros antes de continuar.');
                    }
                    return false;
                });
            } else {
                console.warn('Botão validar não encontrado');
            }

            // Configurar botão de preview
            const btnPreview = document.getElementById('btn-preview');
            if (btnPreview) {
                btnPreview.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Abrindo preview...');
                    document.getElementById('modal-preview').classList.remove('hidden');
                    return false;
                });
            } else {
                console.warn('Botão preview não encontrado');
            }

            // Configurar botÃ£o de fechar preview
            const btnFecharPreview = document.getElementById('fechar-preview');
            if (btnFecharPreview) {
                btnFecharPreview.addEventListener('click', function() {
                    document.getElementById('modal-preview').classList.add('hidden');
                });
            }

            // Configurar botÃ£o de exportar
            const btnExportar = document.getElementById('btn-exportar');
            if (btnExportar) {
                btnExportar.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Exportando PDF...');
                    
                    // Obter o ID do planejamento
                    const planejamentoId = window.planejamentoId || @json($planejamento->id ?? null);
                    
                    if (planejamentoId) {
                        // Construir a URL de exportação
                        const exportUrl = `{{ route('planejamentos.export', ['planejamento' => ':id', 'format' => 'pdf']) }}`.replace(':id', planejamentoId);
                        
                        // Abrir em nova aba para download
                        window.open(exportUrl, '_blank');
                        
                        window.AlertService.success('Download do PDF iniciado!');
                    } else {
                        window.AlertService.error('Erro: ID do planejamento não encontrado. Salve o planejamento primeiro.');
                    }
                    
                    return false;
                });
            } else {
                console.warn('Botão exportar não encontrado');
            }
        }

        // Configurar seleção de ação de finalização sem auto-envio
        if (radioAcaoFinalizacao) radioAcaoFinalizacao.forEach(radio => {
            const label = radio.closest('label');
            if (label) {
                label.addEventListener('click', function() {
                    if (!radio.disabled) {
                        radio.checked = true;
                        console.log(`[Step6] Ação de finalização selecionada: ${radio.value}`);
                        atualizarEstadoBotoes();
                    }
                });
            }
        });

        // Botões do coordenador
        const btnSolicitarCorrecao = document.getElementById('btn-solicitar-correcao');
        if (btnSolicitarCorrecao) {
            btnSolicitarCorrecao.addEventListener('click', async function(e) {
                e.preventDefault();
                const id = window.wizardData?.planejamento_id;
                if (!id) { window.AlertService.error('Planejamento não encontrado.'); return; }
                this.disabled = true;
                this.classList.add('opacity-50','cursor-not-allowed');
                try {
                    const obs = (document.getElementById('observacoes_finais')?.value || '').trim();
                    if (!obs) {
                        window.AlertService.error('Informe observações para rejeitar (campo “Observações Finais”).');
                        throw new Error('Observações obrigatórias para rejeitar.');
                    }
                    const resp = await fetch(joinUrl(`/planejamentos/${id}/rejeitar`), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ observacoes_aprovacao: obs })
                    });
                    let data = {};
                    try { data = await resp.json(); } catch(e) {}
                    if (!resp.ok) {
                        const msg = data?.error || data?.message || `Erro ${resp.status} ao rejeitar.`;
                        window.AlertService.error(msg);
                        return;
                    }
                    window.AlertService.success(data?.message || 'Correção solicitada com sucesso.');
                    window.location.href = joinUrl(`/planejamentos/${id}`);
                } catch (err) {
                    console.error(err);
                    window.AlertService.error(err.message || 'Erro ao solicitar correção.');
                } finally {
                    this.disabled = false;
                    this.classList.remove('opacity-50','cursor-not-allowed');
                }
            });
        }

        const btnAprovar = document.getElementById('btn-aprovar');
        if (btnAprovar) {
            btnAprovar.addEventListener('click', async function(e) {
                e.preventDefault();
                const id = window.wizardData?.planejamento_id;
                if (!id) { window.AlertService.error('Planejamento não encontrado.'); return; }
                this.disabled = true;
                this.classList.add('opacity-50','cursor-not-allowed');
                try {
                    const obs = (document.getElementById('observacoes_finais')?.value || '').trim();
                    const resp = await fetch(joinUrl(`/planejamentos/${id}/aprovar`), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(obs ? { observacoes_aprovacao: obs } : {})
                    });
                    let data = {};
                    try { data = await resp.json(); } catch(e) {}
                    if (!resp.ok) {
                        const msg = data?.error || data?.message || `Erro ${resp.status} ao aprovar.`;
                        window.AlertService.error(msg);
                        return;
                    }
                    window.AlertService.success(data?.message || 'Planejamento aprovado com sucesso.');
                    window.location.href = joinUrl(`/planejamentos/${id}`);
                } catch (err) {
                    console.error(err);
                    window.AlertService.error(err.message || 'Erro ao aprovar planejamento.');
                } finally {
                    this.disabled = false;
                    this.classList.remove('opacity-50','cursor-not-allowed');
                }
            });
        }

        console.log('[Step6] Dados do wizard para resumo:', window.wizardData);

        async function carregarResumo() {
            console.log("[Step6] Carregando resumo do planejamento");

            // Base path e headers comuns para chamadas fetch
            const basePrefix = (document.querySelector('meta[name="app-base-path"]')?.getAttribute('content') || window.__APP_BASE_PATH__ || '').trim();
            function joinUrl(path) {
                const b = basePrefix.replace(/\/+$/, '');
                const p = String(path || '').replace(/^\/+/, '');
                return b ? `${b}/${p}` : `/${p}`;
            }
            const commonHeaders = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            if (csrf) commonHeaders['X-CSRF-TOKEN'] = csrf;

            function getWizardDataFromAllSteps(key) {
                // 1) window.wizardData.stepN
                for (let i = 1; i <= 6; i++) {
                    const stepAlias = window.wizardData && window.wizardData[`step${i}`];
                    if (stepAlias && stepAlias[key] !== undefined && stepAlias[key] !== null && stepAlias[key] !== '') {
                        return stepAlias[key];
                    }
                }
                // 2) window.wizardData[N]
                for (let i = 1; i <= 6; i++) {
                    const stepNum = window.wizardData && window.wizardData[i];
                    if (stepNum && stepNum[key] !== undefined && stepNum[key] !== null && stepNum[key] !== '') {
                        return stepNum[key];
                    }
                }
                // 3) planejamentoWizard.formData[N]
                if (window.planejamentoWizard && window.planejamentoWizard.formData) {
                    for (let i = 1; i <= 6; i++) {
                        const fd = window.planejamentoWizard.formData[i];
                        if (fd && fd[key] !== undefined && fd[key] !== null && fd[key] !== '') {
                            return fd[key];
                        }
                    }
                    // 4) planejamentoWizard.formData[key] direto
                    const direct = window.planejamentoWizard.formData[key];
                    if (direct !== undefined && direct !== null && direct !== '') {
                        return direct;
                    }
                }
                // 5) fallback direto no wizardData
                if (window.wizardData && window.wizardData[key] !== undefined && window.wizardData[key] !== null && window.wizardData[key] !== '') {
                    return window.wizardData[key];
                }
                return null;
            }

            function formatDateBR(ymd) {
                if (!ymd) return '';
                const [y, m, d] = String(ymd).split('-').map(n => parseInt(n, 10));
                if (!y || !m || !d) return '';
                return new Date(y, m - 1, d).toLocaleDateString('pt-BR');
            }

            // Informações Básicas: Título
            const tituloDom = document.querySelector('input[name="titulo"]')?.value;
            const tituloHidden = document.querySelector('input[name="titulo"][type="hidden"]')?.value;
            let tituloWizard = getWizardDataFromAllSteps('titulo');
            const resumoTituloEl = document.getElementById('resumo-titulo');
            if (resumoTituloEl) resumoTituloEl.textContent = tituloDom || tituloHidden || tituloWizard || '-';

            // Modalidade
            const modalidadeTexto = document.querySelector('select[name="modalidade_ensino"] option:checked')?.textContent;
            const modalidadeNomeWizard = getWizardDataFromAllSteps('modalidade_ensino_nome');
            const modalidadeIdWizard = getWizardDataFromAllSteps('modalidade_ensino_id') || getWizardDataFromAllSteps('modalidade_ensino');
            const resumoModalidadeEl = document.getElementById('resumo-modalidade');
            if (resumoModalidadeEl) resumoModalidadeEl.textContent = modalidadeTexto || modalidadeNomeWizard || (modalidadeIdWizard ? `ID ${modalidadeIdWizard}` : '-');

            // Nível de Ensino (se disponível)
            const nivelNomeWizard = getWizardDataFromAllSteps('nivel_ensino_nome');
            const nivelIdWizard = getWizardDataFromAllSteps('nivel_ensino_id') || getWizardDataFromAllSteps('nivel_ensino');
            const resumoNivelEl = document.getElementById('resumo-nivel');
            if (resumoNivelEl) resumoNivelEl.textContent = nivelNomeWizard || (nivelIdWizard ? `ID ${nivelIdWizard}` : '-');

            // Objetivos de Aprendizagem: contar selecionados
            const objetivosSelecionadosDom = document.querySelectorAll('input[name="objetivos_aprendizagem[]"]:checked').length;
            let objetivosWizard = [];
            if (window.wizardData && window.wizardData.step5 && window.wizardData.step5.objetivos_aprendizagem) {
                if (typeof window.wizardData.step5.objetivos_aprendizagem === 'string') {
                    try {
                        objetivosWizard = JSON.parse(window.wizardData.step5.objetivos_aprendizagem);
                    } catch (e) {
                        objetivosWizard = [window.wizardData.step5.objetivos_aprendizagem];
                    }
                } else if (Array.isArray(window.wizardData.step5.objetivos_aprendizagem)) {
                    objetivosWizard = window.wizardData.step5.objetivos_aprendizagem;
                }
            }

            const objetivosCount = objetivosSelecionadosDom || objetivosWizard.length;
            const resumoObjEl = document.getElementById('resumo-objetivos-aprendizagem');
            if (resumoObjEl) resumoObjEl.textContent = objetivosCount > 0 ? `${objetivosCount} objetivos selecionados` : 'Nenhum objetivo selecionado';

            // IDs necessários para mapear nomes
            function getFromHidden(name) {
                const el = document.querySelector(`input[name="${name}"]`);
                if (!el) return null;
                // Considera select/inputs no DOM; Step 6 usa campos ocultos
                return el.value !== undefined && el.value !== null && el.value !== '' ? el.value : null;
            }
            const escolaId = getWizardDataFromAllSteps('escola_id') || getFromHidden('escola_id') || getFromHidden('escola');
            const turnoId = getWizardDataFromAllSteps('turno_id') || getFromHidden('turno_id') || getFromHidden('turno');
            const turmaId = getWizardDataFromAllSteps('turma_id') || getFromHidden('turma_id') || getFromHidden('turma');
            const disciplinaId = getWizardDataFromAllSteps('disciplina_id') || getFromHidden('disciplina_id') || getFromHidden('disciplina');
            const professorId = getWizardDataFromAllSteps('professor_id') || getFromHidden('professor_id') || getFromHidden('professor');
            console.log('[Step6] IDs coletados para resumo:', { escolaId, turnoId, turmaId, disciplinaId, professorId });

            // Período e duração
            const tipoPeriodo = getWizardDataFromAllSteps('tipo_periodo') || getFromHidden('tipo_periodo');
            const dataInicio = getWizardDataFromAllSteps('data_inicio') || getFromHidden('data_inicio') || (window.planejamentoPeriodo?.inicio || null);
            const dataFim = getWizardDataFromAllSteps('data_fim') || getFromHidden('data_fim') || (window.planejamentoPeriodo?.fim || null);
            const numeroDias = parseInt((getWizardDataFromAllSteps('numero_dias') || getFromHidden('numero_dias') || 0), 10);
            const cargaAula = parseFloat((getWizardDataFromAllSteps('carga_horaria_aula') || getFromHidden('carga_horaria_aula') || 0.75));
            console.log('[Step6] Período coletado:', { tipoPeriodo, dataInicio, dataFim, numeroDias, cargaAula });

            const tipoPeriodoEl = document.getElementById('resumo-tipo-periodo');
            if (tipoPeriodoEl) tipoPeriodoEl.textContent = tipoPeriodo ? (tipoPeriodo === 'mensal' ? 'Mensal' : tipoPeriodo) : '-';

            const totalAulasEl = document.getElementById('resumo-total-aulas');
            if (totalAulasEl) totalAulasEl.textContent = numeroDias > 0 ? `${numeroDias}` : '-';

            const cargaHorariaEl = document.getElementById('resumo-carga-horaria');
            if (cargaHorariaEl) cargaHorariaEl.textContent = numeroDias > 0 ? `${(numeroDias * cargaAula).toFixed(1)}h` : '-';

            const periodoDetalhadoEl = document.getElementById('resumo-periodo-detalhado');
            if (periodoDetalhadoEl) periodoDetalhadoEl.textContent = (dataInicio && dataFim) ? `${formatDateBR(dataInicio)} a ${formatDateBR(dataFim)}` : '-';

            // Mapear nomes por ID via APIs
            try {
                if (escolaId) {
                    const res = await fetch(joinUrl(`/api/escolas/${escolaId}`), { credentials: 'same-origin', headers: commonHeaders });
                    if (res.ok) {
                        const escola = await res.json();
                        const el = document.getElementById('resumo-escola');
                        if (el) el.textContent = escola?.nome || `ID ${escolaId}`;
                    }
                }
            } catch (e) { console.warn('STEP-6: falha ao obter escola por ID', e); }

            try {
                if (turnoId) {
                    const res = await fetch(joinUrl(`/api/turnos/${turnoId}`), { credentials: 'same-origin', headers: commonHeaders });
                    if (res.ok) {
                        const turno = await res.json();
                        const el = document.getElementById('resumo-turno');
                        if (el) el.textContent = turno?.nome || `ID ${turnoId}`;
                    }
                }
            } catch (e) { console.warn('STEP-6: falha ao obter turno por ID', e); }

            try {
                if (turmaId) {
                    const res = await fetch(joinUrl(`/api/turmas/${turmaId}`), { credentials: 'same-origin', headers: commonHeaders });
                    if (res.ok) {
                        const turma = await res.json();
                        const el = document.getElementById('resumo-turma');
                        if (el) el.textContent = turma?.nome || turma?.descricao || `ID ${turmaId}`;
                    }
                    // Nº de alunos
                    try {
                        const resAlunos = await fetch(joinUrl(`/api/turmas/${turmaId}/alunos`), { credentials: 'same-origin', headers: commonHeaders });
                        if (resAlunos.ok) {
                            const alunos = await resAlunos.json();
                            const elAlunos = document.getElementById('resumo-alunos');
                            if (elAlunos) elAlunos.textContent = Array.isArray(alunos) ? alunos.length : (alunos?.length || '-');
                        }
                    } catch (e2) { console.warn('STEP-6: falha ao obter alunos da turma', e2); }
                }
            } catch (e) { console.warn('STEP-6: falha ao obter turma por ID', e); }

            try {
                if (disciplinaId && turmaId) {
                    const res = await fetch(joinUrl(`/planejamentos/get-disciplinas-por-turma?turma_id=${encodeURIComponent(turmaId)}`), { credentials: 'same-origin', headers: commonHeaders });
                    if (res.ok) {
                        const disciplinas = await res.json();
                        const encontrada = Array.isArray(disciplinas) ? disciplinas.find(d => String(d.id) === String(disciplinaId)) : null;
                        const el = document.getElementById('resumo-disciplina');
                        if (el) el.textContent = (encontrada && encontrada.nome) ? encontrada.nome : (disciplinaId ? `ID ${disciplinaId}` : '-');
                    }
                }
            } catch (e) { console.warn('STEP-6: falha ao obter disciplina por turma', e); }

            try {
                if (professorId && turmaId && disciplinaId) {
                    const url = joinUrl(`/planejamentos/get-professores-por-turma-disciplina?turma_id=${encodeURIComponent(turmaId)}&disciplina_id=${encodeURIComponent(disciplinaId)}`);
                    const res = await fetch(url, { credentials: 'same-origin', headers: commonHeaders });
                    if (res.ok) {
                        const professores = await res.json();
                        const encontrado = Array.isArray(professores) ? professores.find(p => String(p.id) === String(professorId)) : null;
                        const el = document.getElementById('resumo-professor');
                        if (el) el.textContent = (encontrado && encontrado.name) ? encontrado.name : (professorId ? `ID ${professorId}` : '-');
                    }
                }
            } catch (e) { console.warn('STEP-6: falha ao obter professor por turma/disciplina', e); }
        }

        // Carregar resumo quando a pÃ¡gina estiver pronta
        // carregarResumo(); // desabilitado para evitar conflito com step-6-new.js
    
        // Configurar indicador de status
        function configurarIndicadorStatus() {
            const statusIndicator = document.getElementById('status-indicator');
            const statusBadge = document.getElementById('status-badge');
            const statusIcon = document.getElementById('status-icon');
            const statusText = document.getElementById('status-text');
            
            if (!statusIndicator || !statusBadge || !statusIcon || !statusText) {
                return;
            }
            
            // Obter status do planejamento
            let status = 'rascunho'; // padrão
            if (window.wizardData && window.wizardData.status) {
                status = window.wizardData.status;
            }
            
            // Configurar aparência baseada no status
            let badgeClasses = 'inline-flex items-center px-4 py-2 rounded-full text-sm font-medium ';
            let iconClass = '';
            let statusLabel = '';
            
            // Se o backend já forneceu o texto formatado, usar diretamente
            if (window.wizardData && window.wizardData.status_formatado) {
                statusLabel = window.wizardData.status_formatado;
            }

            switch (status) {
                case 'rascunho':
                    badgeClasses += 'bg-gray-100 text-gray-800 border border-gray-300';
                    iconClass = 'fas fa-edit text-gray-600';
                    statusLabel = statusLabel || 'Rascunho';
                    break;
                case 'finalizado':
                    badgeClasses += 'bg-yellow-100 text-yellow-800 border border-yellow-300';
                    iconClass = 'fas fa-clock text-yellow-600';
                    // Quando não houver detalhado finalizado, o texto deve ser "Aguardando Aprovação"
                    statusLabel = statusLabel || 'Aguardando Aprovação';
                    break;
                case 'aprovado':
                    badgeClasses += 'bg-green-100 text-green-800 border border-green-300';
                    iconClass = 'fas fa-check-circle text-green-600';
                    statusLabel = statusLabel || 'Aprovado';
                    break;
                case 'rejeitado':
                    badgeClasses += 'bg-red-100 text-red-800 border border-red-300';
                    iconClass = 'fas fa-times-circle text-red-600';
                    statusLabel = statusLabel || 'Rejeitado';
                    break;
                default:
                    badgeClasses += 'bg-gray-100 text-gray-800 border border-gray-300';
                    iconClass = 'fas fa-question-circle text-gray-600';
                    statusLabel = statusLabel || 'Status Desconhecido';
            }
            
            // Aplicar estilos e texto
            statusBadge.className = badgeClasses;
            statusIcon.className = iconClass;
            statusText.textContent = statusLabel;
            
            // Controlar visibilidade dos botões baseado no status
            const opcaoRascunho = document.getElementById('opcao-rascunho');
            const opcaoRevisao = document.getElementById('opcao-revisao');
            
            if (status === 'finalizado') {
                // Ocultar opções de rascunho e revisão quando já está em revisão
                if (opcaoRascunho) opcaoRascunho.style.display = 'none';
                if (opcaoRevisao) opcaoRevisao.style.display = 'none';
            } else {
                // Mostrar opções normalmente
                if (opcaoRascunho) opcaoRascunho.style.display = 'block';
                if (opcaoRevisao) opcaoRevisao.style.display = 'block';
            }
            
            // Mostrar o indicador
            statusIndicator.style.display = 'block';
        }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStep6);
    } else {
        initStep6();
    }
})();
</script>










