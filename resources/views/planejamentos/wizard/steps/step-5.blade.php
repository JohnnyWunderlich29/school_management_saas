<!-- Etapa 5: Conteúdo Pedagógico -->
<form id="step-5-form">
<script>
    // Em edição, o controller passa $diarios já normalizados
    window.diariosServidor = @json($diarios ?? []);
</script>

<script>
    window.wizardData = window.wizardData || {};
    @if(isset($planejamento) && $planejamento)
        window.wizardData.planejamento_id = {{ $planejamento->id }};
        // Sinalizar modo edição para evitar inicialização de rascunho duplicada
        window.isEditingPlanejamento = true;
        // Disponibilizar período do planejamento para fallback na Step 5
        window.planejamentoPeriodo = {
            inicio: @json($planejamento->data_inicio ? (\Carbon\Carbon::parse($planejamento->data_inicio)->format('Y-m-d')) : null),
            fim: @json($planejamento->data_fim ? (\Carbon\Carbon::parse($planejamento->data_fim)->format('Y-m-d')) : null)
        };
    @endif

    // Persistência defensiva: inicializar modo edição e evitar init-draft duplicado
    (function(){
        try {
            const url = new URL(window.location.href);
            const editParam = url.searchParams.get('edit');

            // 1) Preferir ID na URL (?edit=ID)
            if (editParam && !isNaN(parseInt(editParam, 10))) {
                window.wizardData = window.wizardData || {};
                window.wizardData.planejamento_id = parseInt(editParam, 10);
                window.isEditingPlanejamento = true;
            }

            // 2) Caso não esteja definido e estiver em contexto de edição explícito (URL com ?edit), sincronizar do storage
            if (!window.wizardData.planejamento_id && (editParam && !isNaN(parseInt(editParam, 10)))) {
                const storedId = localStorage.getItem('wizard_planejamento_id');
                if (storedId && !isNaN(parseInt(storedId, 10))) {
                    window.wizardData = window.wizardData || {};
                    window.wizardData.planejamento_id = parseInt(storedId, 10);
                    window.isEditingPlanejamento = true;
                }
            }

            // 3) Se já há um planejamento em contexto, considerar init-draft concluído
            if (window.wizardData.planejamento_id) {
                window._initDraftDone = true;
            }

            console.log('[Step5] Modo edição?', !!window.isEditingPlanejamento, 'planejamento_id:', window.wizardData?.planejamento_id || null);
        } catch (e) {
            console.warn('[Step5] Falha ao recuperar planejamento_id do storage/URL', e);
        }
    })();
</script>
<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <i class="fas fa-book text-blue-600 mr-2"></i>
            Conteúdo Pedagógico
        </h3>
        <p class="text-gray-600 mt-1">Defina os campos de experiência, saberes e objetivos de aprendizagem</p>
    </div>
    
    <!-- Navegação entre dias -->
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
        <div class="p-4 border-b border-gray-200">
            <h4 class="text-md font-medium text-gray-900 flex items-center">
                <i class="fas fa-calendar-day text-blue-600 mr-2"></i>
                Planejamento Diário
                <span id="dias-planejados-contador" class="ml-2 text-sm bg-blue-100 text-blue-800 py-0.5 px-2 rounded-full">0/0 dias</span>
            </h4>
            <p class="text-sm text-gray-600 mt-1">Navegue entre os dias do período para planejar cada aula</p>
        </div>
        
        <div class="p-4">
            <div class="flex items-center justify-between gap-2 mb-4">
                <button type="button" id="dia-anterior" class="w-12 h-12 md:w-auto md:h-auto md:px-6 md:py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-full md:rounded-lg flex items-center justify-center text-lg font-medium shadow-lg transition-all">
                    <i class="fas fa-chevron-left"></i>
                    <span class="hidden md:inline ml-2">Anterior</span>
                </button>
                
                <div class="text-center py-2 flex-grow">
                    <span id="dia-atual-display" class="text-xl font-bold text-blue-700">Dia 1</span>
                    <span class="mx-2 text-gray-400">|</span>
                    <span id="data-atual-display" class="text-base text-gray-600">01/01/2023</span>
                </div>
                
                <button type="button" id="proximo-dia" class="w-12 h-12 md:w-auto md:h-auto md:px-6 md:py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-full md:rounded-lg flex items-center justify-center text-lg font-medium shadow-lg transition-all">
                    <span class="hidden md:inline mr-2">Próximo</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <div class="overflow-x-auto pb-1 scrollbar-thin scrollbar-thumb-blue-500 scrollbar-track-blue-100">
                <div id="dias-calendario" class="flex space-x-2 pb-2 px-1 min-w-full snap-x snap-mandatory">
                    <!-- Será preenchido via JavaScript -->
                </div>
            </div>
            <div class="text-center text-xs text-gray-500 mt-1">
                <span class="hidden md:inline">Deslize para ver mais dias</span>
                <span class="md:hidden">← Arraste para navegar →</span>
            </div>
        </div>
    </div>

    <!-- Ações de cópia entre dias -->
    <div class="p-4 pt-0 flex justify-end">
        <button type="button" id="btn-copiar-dia" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center">
            <i class="fas fa-copy mr-2"></i>
            Copiar conteúdo
        </button>
    </div>

    <!-- Modal de cópia de dia -->
    <div id="copy-day-modal" class="fixed inset-0 bg-black bg-opacity-40 z-50 hidden">
      <div class="max-w-xl mx-auto mt-24 bg-white rounded-lg shadow-lg border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h4 class="text-md font-medium text-gray-900">
            Copiar conteúdo do <span id="copy-day-source-label">dia</span>
          </h4>
          <button type="button" id="copy-day-cancel" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="p-4 space-y-3">
          <p class="text-sm text-gray-600">Selecione os dias destino para aplicar uma cópia idêntica.</p>
          <div class="flex gap-2">
            <button type="button" id="copy-day-select-all" class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded">Selecionar todos</button>
            <button type="button" id="copy-day-clear" class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded">Limpar</button>
          </div>
          <div id="copy-days-list" class="max-h-64 overflow-y-auto border border-gray-200 rounded p-2"></div>
          <label class="flex items-center text-sm text-gray-700">
            <input type="checkbox" id="copy-overwrite" class="mr-2" checked>
            Sobrescrever se já houver conteúdo no dia destino
          </label>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-2">
          <button type="button" id="copy-day-apply" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">
            Copiar
          </button>
        </div>
      </div>
    </div>

    <!-- Campos de Experiência -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">
            Campos de Experiência <span class="text-red-500">*</span>
        </label>
        <div class="space-y-3">
            @php
                // Determinar pré-seleção: prioriza old(), depois atributo do planejamento (campos_experiencia)
                $camposSelecionados = [];
                $oldCampos = old('campos_experiencia');
                if ($oldCampos) {
                    $camposSelecionados = is_array($oldCampos) ? $oldCampos : [$oldCampos];
                } elseif (isset($planejamento) && $planejamento && $planejamento->campos_experiencia) {
                    $val = $planejamento->campos_experiencia;
                    if (is_string($val)) {
                        $decoded = json_decode($val, true);
                        $camposSelecionados = is_array($decoded) ? $decoded : [];
                    } elseif (is_array($val)) {
                        $camposSelecionados = $val;
                    }
                }
            @endphp

            @foreach($campos_experiencia as $campo)
                <label class="flex items-start p-4 border border-gray-200 rounded-lg hover:border-blue-300 cursor-pointer transition-colors">
                    <input type="checkbox" name="campos_experiencia[]" value="{{ $campo->id }}" 
                           {{ in_array($campo->id, $camposSelecionados) ? 'checked' : '' }}
                           class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <div class="ml-3 flex-1">
                        <div class="text-sm font-medium text-gray-900">{{ $campo->nome }}</div>
                        <div class="text-sm text-gray-600">{{ $campo->descricao }}</div>
                    </div>
                </label>
            @endforeach
        </div>
        @error('campos_experiencia')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Seleção de Saberes e Conhecimentos -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">
            Seleção de Saberes e Conhecimentos
        </label>
        
        <!-- Filtros/Busca de Saberes -->
        <div class="mb-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label for="busca-saber" class="block text-xs font-medium text-gray-700 mb-1">
                        Buscar Saber
                    </label>
                    <input type="text" id="busca-saber" placeholder="Digite para buscar..."
                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Lista de Saberes -->
        <div id="lista-saberes" class="space-y-3 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-3">
            <div class="text-center text-gray-500 py-6">
                <i class="fas fa-search text-xl mb-2"></i>
                <p>Selecione os campos de experiência para ver os saberes disponíveis</p>
            </div>
        </div>

        <!-- Saberes Selecionados -->
        <div id="saberes-selecionados" class="mt-3 hidden">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Saberes Selecionados:</h4>
            <div id="lista-saberes-selecionados" class="space-y-2"></div>
        </div>
    </div>

    <!-- Saberes e Conhecimentos (Descrição) -->
    <div>
        <label for="saberes_conhecimentos" class="block text-sm font-medium text-gray-700 mb-2">
            Saberes e Conhecimentos <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <textarea name="saberes_conhecimentos" id="saberes_conhecimentos" rows="6"
                      placeholder="Descreva os saberes e conhecimentos que serÃ£o trabalhados neste planejamento..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 resize-none">{{ old('saberes_conhecimentos', $planejamento->saberes_conhecimentos ?? '') }}</textarea>
            <div class="absolute bottom-2 right-2 text-xs text-gray-400">
                <span id="saberes-count">0</span>/1000 caracteres
            </div>
        </div>
        @error('saberes_conhecimentos')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
        
        <!-- SugestÃµes de Saberes -->
        <div class="mt-2">
            <button type="button" id="btn-sugestoes-saberes" 
                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                <i class="fas fa-lightbulb mr-1"></i>
                Ver sugestões de saberes
            </button>
            <div id="sugestoes-saberes" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="text-sm text-blue-800 font-medium mb-2">Sugestões baseadas nos campos selecionados:</div>
                <div id="lista-sugestoes-saberes" class="space-y-1 text-sm text-blue-700">
                    <!-- Preenchido via JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Objetivos de Aprendizagem -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">
            Objetivos de Aprendizagem <span class="text-red-500">*</span>
        </label>
        
        <!-- Filtros para Objetivos -->
        <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="filtro-faixa-etaria" class="block text-xs font-medium text-gray-700 mb-1">
                        Faixa Etária
                    </label>
                    <select id="filtro-faixa-etaria" 
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas as idades</option>
                        <option value="0-1">0 a 1 ano</option>
                        <option value="1-3">1 a 3 anos</option>
                        <option value="4-5">4 a 5 anos</option>
                    </select>
                </div>
                
                <div>
                    <label for="filtro-campo" class="block text-xs font-medium text-gray-700 mb-1">
                        Campo de Experiência
                    </label>
                    <select id="filtro-campo" 
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os campos</option>
                        <!-- Preenchido via JavaScript baseado nos campos selecionados -->
                    </select>
                </div>
                
                <div>
                    <label for="busca-objetivo" class="block text-xs font-medium text-gray-700 mb-1">
                        Buscar Objetivo
                    </label>
                    <input type="text" id="busca-objetivo" placeholder="Digite para buscar..."
                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <!-- Lista de Objetivos -->
        <div id="lista-objetivos" class="space-y-3 max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-4">
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-search text-2xl mb-2"></i>
                <p>Selecione os campos de experiência para ver os objetivos disponíveis</p>
            </div>
        </div>

        <!-- Objetivos Selecionados -->
        <div id="objetivos-selecionados" class="mt-4 hidden">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Objetivos Selecionados:</h4>
            <div id="lista-objetivos-selecionados" class="space-y-2">
                <!-- Preenchido via JavaScript -->
            </div>
        </div>

        @error('objetivos_aprendizagem')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Objetivos EspecÃ­ficos -->
    <div>
        <label for="objetivos_especificos" class="block text-sm font-medium text-gray-700 mb-2">
            Objetivos Específicos
        </label>
        @php
            $oldObj = old('objetivos_especificos');
            if (is_array($oldObj)) {
                $objetivosEspecificosTexto = implode("\n", $oldObj);
            } elseif (is_string($oldObj) && strlen($oldObj)) {
                $objetivosEspecificosTexto = $oldObj;
            } else {
                $val = $planejamento->objetivos_especificos ?? '';
                $objetivosEspecificosTexto = is_array($val) ? implode("\n", $val) : ($val ?: '');
            }
        @endphp
        <div class="relative">
            <textarea name="objetivos_especificos" id="objetivos_especificos" rows="4"
                      placeholder="Liste objetivos específicos (um por linha) para este planejamento..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 resize-none">{{ $objetivosEspecificosTexto }}</textarea>
            <div class="absolute bottom-2 right-2 text-xs text-gray-400">
                <span id="objetivos-especificos-count">0</span>/1000 caracteres
            </div>
        </div>
        <!-- container para inputs hidden gerados -->
        <div id="objetivos-especificos-hidden" class="hidden"></div>
    </div>

    <!-- Metodologia -->
    <div>
        <label for="metodologia" class="block text-sm font-medium text-gray-700 mb-2">
            Metodologia
        </label>
        <div class="relative">
            <textarea name="metodologia" id="metodologia" rows="4"
                      placeholder="Descreva a metodologia que serÃ¡ utilizada para desenvolver este planejamento..."
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 resize-none">{{ old('metodologia', $planejamento->metodologia ?? '') }}</textarea>
            <div class="absolute bottom-2 right-2 text-xs text-gray-400">
                <span id="metodologia-count">0</span>/1500 caracteres
            </div>
        </div>
        @error('metodologia')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Recursos Necessários -->
    <div>
        <label for="recursos" class="block text-sm font-medium text-gray-700 mb-2">
            Recursos Necessários
        </label>
        <div class="space-y-3">
            <!-- Recursos Predefinidos -->
            <div>
                <div class="text-sm text-gray-600 mb-2">Recursos Comuns:</div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @php
                        $recursosComuns = [
                            'Livros didÃ¡ticos', 'Materiais de arte', 'Jogos educativos', 'Computador/Tablet',
                            'Projetor', 'Quadro', 'Materiais reciclÃ¡veis', 'Instrumentos musicais',
                            'Brinquedos', 'Fantasias', 'Materiais de construÃ§Ã£o', 'Plantas/Sementes'
                        ];
                        $recursosSelecionados = old('recursos_predefinidos', $planejamento->recursos_predefinidos ?? []);
                    @endphp
                    
                    @foreach($recursosComuns as $recurso)
                        <label class="flex items-center text-sm">
                            <input type="checkbox" name="recursos_predefinidos[]" value="{{ $recurso }}" 
                                   {{ in_array($recurso, $recursosSelecionados) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 mr-2">
                            {{ $recurso }}
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Recursos Personalizados -->
            <div>
                <label for="recursos_personalizados" class="block text-sm text-gray-600 mb-2">
                    Outros Recursos:
                </label>
                <textarea name="recursos_personalizados" id="recursos_personalizados" rows="3"
                          placeholder="Liste outros recursos especÃ­ficos que serÃ£o necessÃ¡rios..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 resize-none">{{ old('recursos_personalizados', $planejamento->recursos_personalizados ?? '') }}</textarea>
            </div>
        </div>
        @error('recursos')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- AvaliaÃ§Ã£o removida nesta etapa -->

    <!-- Resumo do ConteÃºdo -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-green-800 mb-2">Resumo do Conteúdo Pedagógico</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-green-700">
            <div>
                <span class="font-medium">Campos de Experiência:</span>
                <span id="resumo-campos">0 selecionados</span>
            </div>
            <div>
                <span class="font-medium">Objetivos de Aprendizagem:</span>
                <span id="resumo-objetivos">0 selecionados</span>
            </div>
            <div>
                <span class="font-medium">Recursos:</span>
                <span id="resumo-recursos">0 selecionados</span>
            </div>
            <div>
                <span class="font-medium">Saberes Selecionados:</span>
                <span id="resumo-saberes">0 selecionados</span>
            </div>
        </div>
    </div>

    <!-- Dicas -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-lightbulb text-gray-600 mt-0.5"></i>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-gray-800">Dicas para esta etapa:</h4>
                <ul class="text-sm text-gray-700 mt-1 space-y-1">
                    <li>Selecione campos de experiência alinhados com a faixa etária da turma</li>
                    <li>Escolha objetivos específicos e mensuráveis</li>
                    <li>Descreva metodologias ativas e participativas</li>
                    <li>Considere recursos disponíveis na escola</li>
                    <li>Planeje avaliações formativas e contínuas</li>
                </ul>
            </div>
        </div>
    </div>
</div>
    <!-- Botão de Marcar como Planejado (Parte inferior) -->
    <div class="mt-8 flex justify-center">
        <button type="button" id="salvar-dia" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-lg font-medium rounded-lg flex items-center shadow-md transition-all">
            <i class="fas fa-check mr-2"></i> Marcar como planejado
        </button>
    </div>
</form>

@stack('scripts')
<script>
(function() {
    console.log('[Step5] Script da Etapa 5 carregado');
    function initStep5() {
    console.log('[Step5] Init disparado', { readyState: document.readyState });
    // Inicializar seletores antes de carregar dados para evitar avisos de funções não definidas
    // (funções são hoisted, então podem ser chamadas antes da sua declaração)
    setupSaberesConhecimentos();
    setupObjetivosAprendizagem();
    
    // Inicializar navegaÃ§Ã£o entre dias
    setupNavegacaoDias();
    
    function setupNavegacaoDias() {
        console.log('[Step5] setupNavegacaoDias inicializado');
        const btnDiaAnterior = document.getElementById('dia-anterior');
        const btnProximoDia = document.getElementById('proximo-dia');
        const diaAtualDisplay = document.getElementById('dia-atual-display');
        const dataAtualDisplay = document.getElementById('data-atual-display');
        const diasCalendario = document.getElementById('dias-calendario');
        const diasPlanejadosContador = document.getElementById('dias-planejados-contador');
        
        let diaAtual = 1;
        let totalDias = 5; // Valor padrão, será atualizado com dados reais
        let dataInicio = new Date(); // Será atualizado com dados reais
        let dataFim = null; // Será atualizado com dados reais, quando disponível
        let diasUteis = []; // Array para armazenar apenas os dias úteis

        // Converte string YYYY-MM-DD para Date usando componentes locais (evita deslocamento de timezone)
        function parseYMD(ymd) {
            const [y, m, d] = ymd.split('-').map(n => parseInt(n, 10));
            return new Date(y, m - 1, d);
        }

        function formatDateKey(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }
        
        // Carregar dados do planejamento
         async function carregarDadosPlanejamento() {
             // Obter dados da etapa 4 do wizard
             const wizard = window.planejamentoWizard;
             window.wizardData = window.wizardData || {};
             const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
             if (wizard && wizard.formData && wizard.formData[4]) {
                 const dadosEtapa4 = wizard.formData[4];
                 
                 // Obter nÃºmero de dias do perÃ­odo
                 if (dadosEtapa4.numero_dias) {
                     totalDias = parseInt(dadosEtapa4.numero_dias);
                 }

                 // Obter datas de inÃ­cio/fim
                 if (dadosEtapa4.data_inicio) {
                     // Usar parser local para evitar que 'YYYY-MM-DD' seja interpretado em UTC
                     dataInicio = parseYMD(dadosEtapa4.data_inicio);
                 }
                 if (dadosEtapa4.data_fim) {
                     dataFim = parseYMD(dadosEtapa4.data_fim);
                 }

                 console.log('[Step5] Dados carregados da etapa 4:', { totalDias, dataInicio, dataFim });
             } else {
                 console.log('[Step5] Dados da etapa 4 não encontrados; aplicando fallback do período/diários existentes');
                 // Fallback: utilizar período do planejamento em edição, quando disponível
                 const periodo = window.planejamentoPeriodo || {};
                 const inicioStr = periodo.inicio;
                 const fimStr = periodo.fim;
                 if (inicioStr) {
                     dataInicio = parseYMD(inicioStr);
                 }
                 if (fimStr) {
                     dataFim = parseYMD(fimStr);
                 }

                 // Se não houver período, inferir pelas datas dos diários pré-carregados
                 if ((!inicioStr || !fimStr) && Array.isArray(window.diariosServidor) && window.diariosServidor.length) {
                     const datas = window.diariosServidor
                         .map(d => d.data)
                         .filter(Boolean)
                         .map(parseYMD)
                         .sort((a, b) => a.getTime() - b.getTime());
                     if (datas.length) {
                         // Fallback mais amplo: usar o mês completo do primeiro diário
                         const ref = datas[0];
                         const ano = ref.getFullYear();
                         const mes = ref.getMonth(); // 0-based
                         // Primeiro dia do mês
                         dataInicio = new Date(ano, mes, 1);
                         // Último dia do mês
                         dataFim = new Date(ano, mes + 1, 0);
                     }
                 }

                 // Definir totalDias apenas quando não houver dataFim definida
                 totalDias = dataFim ? 0 : 20; // Quando há fim, usar intervalo; senão, padrão 20 dias úteis
             }
             
             // Evitar criação de rascunhos no modo edição — só inicializar quando NÃO estiver editando
             try {
                 const urlNow = new URL(window.location.href);
                 const editParamNow = urlNow.searchParams.get('edit');
                 const hasEditParam = !!(editParamNow && !isNaN(parseInt(editParamNow, 10)));
                 const hasWizardId = !!(window.wizardData && window.wizardData.planejamento_id);
                 const isEditing = !!window.isEditingPlanejamento || hasEditParam || hasWizardId;

                if (!isEditing && !hasWizardId && !window._initDraftDone) {
                    const payloadInit = {
                        // Se houver um planejamento em contexto, enviar para atualizar em vez de criar
                        planejamento_id: hasWizardId ? window.wizardData.planejamento_id : (hasEditParam ? parseInt(editParamNow, 10) : null),
                        1: wizard.formData?.[1] || {},
                        2: wizard.formData?.[2] || {},
                        3: wizard.formData?.[3] || {},
                        4: wizard.formData?.[4] || {},
                    };
                     const resp = await fetch('/planejamentos/wizard/init-draft', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': csrf || ''
                         },
                         body: JSON.stringify(payloadInit)
                     });
                    const dataInit = await resp.json();
                     if (dataInit.success && dataInit.planejamento_id) {
                         window.wizardData.planejamento_id = dataInit.planejamento_id;
                         window._initDraftDone = true;
                        // Marcar como edição daqui em diante e persistir no storage
                        try {
                            window.isEditingPlanejamento = true;
                            localStorage.setItem('wizard_planejamento_id', String(dataInit.planejamento_id));
                            // Atualizar URL com parâmetro edit para evitar novas inicializações em reloads
                            const url = new URL(window.location.href);
                            url.searchParams.set('edit', String(dataInit.planejamento_id));
                            history.replaceState(null, '', url.toString());
                        } catch (e) {
                            console.warn('[Step5] Falha ao persistir planejamento_id no storage/URL', e);
                        }
                         console.log('[Step5] Rascunho inicializado', dataInit);
                     } else {
                         console.warn('[Step5] Falha ao inicializar rascunho', dataInit);
                     }
                 }
             } catch (e) {
                 console.error('[Step5] Erro ao inicializar rascunho', e);
             }

             // Calcular dias úteis (excluindo sábados e domingos)
             calcularDiasUteis();
             
             // Carregar planejamentos salvos
             carregarPlanejamentosDiarios();
             
             atualizarInterface();
             renderizarCalendario();

            // Removido: upsert inicial com planejado=false para evitar sobrescrever status "planejado"
         }
         
         // Função para calcular dias úteis (excluindo sábados e domingos)
         function calcularDiasUteis() {
             diasUteis = [];
             let dataAtual = new Date(dataInicio);
             let limite = dataFim ? new Date(dataFim) : null;
             let diasProcessados = 0;

             // Normalizar hora para meia-noite
             dataAtual.setHours(0, 0, 0, 0);
             if (limite) limite.setHours(0, 0, 0, 0);

             while (true) {
                 const diaSemana = dataAtual.getDay();
                 const ehFinalDeSemana = diaSemana === 0 || diaSemana === 6; // 0 = domingo, 6 = sÃ¡bado

                 if (!ehFinalDeSemana) {
                     diasUteis.push({
                         indice: diasUteis.length + 1,
                         dataOriginal: new Date(dataAtual),
                         diaSemana: diaSemana
                     });
                 }

                 // Parar quando atingir limite de fim ou total de dias quando nÃ£o hÃ¡ fim
                 if (limite) {
                     if (dataAtual.getTime() >= limite.getTime()) break;
                 } else if (diasUteis.length >= totalDias) {
                     break;
                 }

                 // AvanÃ§ar para o prÃ³ximo dia
                 dataAtual.setDate(dataAtual.getDate() + 1);
                 diasProcessados++;
                 if (diasProcessados > 400) {
                     console.error('[Step5] Erro ao calcular dias úteis: loop muito longo');
                     break;
                 }
             }

             console.log('[Step5] Dias úteis calculados:', diasUteis);
             return diasUteis;
         }
         
         // Array para armazenar o status de cada dia (planejado ou não)
         let statusDias = {};
         
         // Função para carregar planejamentos diários salvos
function carregarPlanejamentosDiarios() {
    const wizard = window.planejamentoWizard;
    if (wizard && wizard.formData && wizard.formData[5] && wizard.formData[5].planejamentos_diarios) {
        statusDias = wizard.formData[5].planejamentos_diarios;
    } else {
        statusDias = {};
    }

    if (!wizard.formData[5]) {
        wizard.formData[5] = {};
    }

    if (!wizard.formData[5].dados_por_dia) {
        wizard.formData[5].dados_por_dia = {};
    }

    // Pré-carregar diários vindos do servidor (modo edição)
        if (window.diariosServidor && Array.isArray(window.diariosServidor) && window.diariosServidor.length) {
            window.diariosServidor.forEach(d => {
                const dateKey = d.data;
                statusDias[dateKey] = true;
                wizard.formData[5].dados_por_dia[dateKey] = {
                    campos_experiencia: d.campos_experiencia || [],
                    saberes_conhecimentos: d.saberes_conhecimentos || [],
                    objetivos_especificos: d.objetivos_especificos || '',
                    objetivos_aprendizagem: d.objetivos_aprendizagem || [],
                    metodologia: d.metodologia || '',
                    recursos_predefinidos: d.recursos_predefinidos || [],
                    recursos_personalizados: d.recursos_personalizados || ''
                };
            });
            wizard.formData[5].planejamentos_diarios = statusDias;
            console.log('[Step5] Diários pré-carregados do servidor:', window.diariosServidor);
        }
}

// Função para salvar o planejamento do dia atualÃ§Ã£o para salvar o planejamento do dia atual
         async function salvarPlanejamentoDiario() {
             const wizard = window.planejamentoWizard;
             if (!diasUteis.length) return;
             const diaUtil = diasUteis[diaAtual - 1];
             // Preferir a chave de data do botão atualmente selecionado para evitar qualquer dessincronização
             const currentTile = document.getElementById('dias-calendario')?.children?.[diaAtual - 1] || null;
             const dateKey = (currentTile && currentTile.dataset && currentTile.dataset.dateKey)
                 ? currentTile.dataset.dateKey
                 : formatDateKey(diaUtil.dataOriginal);

             // Não tentar salvar sem planejamento_id válido
             if (!window.wizardData?.planejamento_id) {
                 console.warn('[Step5] Sem planejamento_id — cancelando salvamento do dia', { dateKey });
                 return;
             }

             // Marcar o dia atual como planejado
             statusDias[dateKey] = true;

             // Salvar os dados do formulÃ¡rio para o dia atual
             if (wizard) {
                 if (!wizard.formData[5]) {
                     wizard.formData[5] = {};
                 }

                 if (!wizard.formData[5].dados_por_dia) {
                     wizard.formData[5].dados_por_dia = {};
                 }

                 // Capturar os dados do formulÃ¡rio atual
                 const dadosDia = {
                     campos_experiencia: Array.from(document.querySelectorAll('input[name="campos_experiencia[]"]:checked')).map(cb => parseInt(cb.value)),
                     // Salvar seleções de saberes como array de IDs
                     saberes_conhecimentos: typeof window.getSaberesSelecionados === 'function'
                         ? window.getSaberesSelecionados().map(id => parseInt(id))
                         : Array.from(document.querySelectorAll('input[name="saberes_selecionados[]"]:checked')).map(cb => parseInt(cb.value)),
                     objetivos_especificos: document.getElementById('objetivos_especificos').value,
                     objetivos_aprendizagem: Array.from(document.querySelectorAll('input[name=\"objetivos_aprendizagem[]\"]:checked')).map(cb => parseInt(cb.value)),
                     metodologia: document.getElementById('metodologia').value,
                     recursos_predefinidos: Array.from(document.querySelectorAll('input[name="recursos_predefinidos[]"]:checked')).map(cb => cb.value),
                     recursos_personalizados: document.getElementById('recursos_personalizados').value
                 };

                 // Salvar dados do dia atual por chave de data
                 wizard.formData[5].dados_por_dia[dateKey] = dadosDia;

                 // Salvar status de todos os dias
                 wizard.formData[5].planejamentos_diarios = statusDias;

                 console.log('[Step5] Planejamento do dia salvo (local):', { dateKey, dia: diaAtual, exibido: diaUtil.dataOriginal.toLocaleDateString('pt-BR'), statusDias, dadosDia });
             }

             // Persistir no servidor (upsert do diÃ¡rio)
             try {
                 await upsertDiarioServidor(dateKey, wizard.formData?.[5]?.dados_por_dia?.[dateKey] || {}, true);
                 console.log('[Step5] Planejamento do dia salvo (servidor):', { dateKey });
             } catch (e) {
                 console.error('[Step5] Erro ao salvar diário no servidor', e);
                 // Feedback básico ao usuário
                 alert('Não foi possível salvar este dia. Verifique o período do planejamento e tente novamente.');
             }

             renderizarCalendario();
         }
        
        // FunÃ§Ã£o para carregar os dados do dia selecionado
        function carregarDadosDia() {
            const wizard = window.planejamentoWizard;
            if (!diasUteis.length) return limparFormulario();
            const diaUtil = diasUteis[diaAtual - 1];
            // Usar preferencialmente a chave de data do botão selecionado para evitar qualquer drift
            const currentTile = document.getElementById('dias-calendario')?.children?.[diaAtual - 1] || null;
            const dateKey = (currentTile && currentTile.dataset && currentTile.dataset.dateKey)
                ? currentTile.dataset.dateKey
                : formatDateKey(diaUtil.dataOriginal);
            // Log de depuração para verificar correspondência
            console.log('[Step5] carregarDadosDia -> chave usada', { dia: diaAtual, dateKey, exibido: diaUtil.dataOriginal.toLocaleDateString('pt-BR') });
            if (wizard && wizard.formData[5] && wizard.formData[5].dados_por_dia && wizard.formData[5].dados_por_dia[dateKey]) {
                const dadosDia = wizard.formData[5].dados_por_dia[dateKey];
                
                // Limpar todos os campos primeiro
                limparFormulario();
                
                // Preencher campos com os dados salvos
                if (dadosDia.campos_experiencia) {
                    dadosDia.campos_experiencia.forEach(id => {
                        const checkbox = document.querySelector(`input[name="campos_experiencia[]"][value="${id}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
                
                if (dadosDia.saberes_conhecimentos) {
                    if (Array.isArray(dadosDia.saberes_conhecimentos)) {
                        // Restaurar seleção de saberes
                        if (typeof window.setSaberesSelecionados === 'function') {
                            window.setSaberesSelecionados(dadosDia.saberes_conhecimentos);
                        } else {
                            // Fallback: marcar checkboxes diretamente
                            document.querySelectorAll('input[name="saberes_selecionados[]"]').forEach(cb => {
                                cb.checked = dadosDia.saberes_conhecimentos.includes(parseInt(cb.value));
                            });
                        }
                    } else {
                        // Conteúdo textual legado
                        document.getElementById('saberes_conhecimentos').value = dadosDia.saberes_conhecimentos;
                    }
                }
                
                if (dadosDia.objetivos_especificos) {
                    document.getElementById('objetivos_especificos').value = dadosDia.objetivos_especificos;
                }
                
                if (dadosDia.metodologia) {
                    const metodologiaEl = document.getElementById('metodologia');
                    if (metodologiaEl) {
                        metodologiaEl.value = dadosDia.metodologia;
                        console.log('[Step5] Metodologia aplicada ao DOM', { valor: metodologiaEl.value });
                    } else {
                        console.warn('[Step5] Campo metodologia não encontrado no DOM');
                    }
                }
                
                if (dadosDia.recursos_predefinidos) {
                    dadosDia.recursos_predefinidos.forEach(recurso => {
                        const checkbox = document.querySelector(`input[name="recursos_predefinidos[]"][value="${recurso}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
                
                if (dadosDia.recursos_personalizados) {
                    document.getElementById('recursos_personalizados').value = dadosDia.recursos_personalizados;
                }
                
                // Restaurar objetivos de aprendizagem
                if (Array.isArray(dadosDia.objetivos_aprendizagem)) {
                    const objetivosCheckboxes = document.querySelectorAll('input[name="objetivos_aprendizagem[]"]');
                    objetivosCheckboxes.forEach(cb => {
                        cb.checked = dadosDia.objetivos_aprendizagem.includes(parseInt(cb.value));
                    });
                    console.log('[Step5] Objetivos marcados pré-render', { count: objetivosCheckboxes.length, selecionados: dadosDia.objetivos_aprendizagem });
                }
                
                // Disparar eventos para atualizar contadores e listas
                document.querySelectorAll('textarea').forEach(textarea => {
                    textarea.dispatchEvent(new Event('input'));
                });
                
                document.querySelectorAll('input[name="campos_experiencia[]"]').forEach(checkbox => {
                    checkbox.dispatchEvent(new Event('change'));
                });
                
                // Aplicar seleção de objetivos após renderização por mudança de campos
                if (Array.isArray(dadosDia.objetivos_aprendizagem)) {
                    if (typeof window.setObjetivosSelecionados === 'function') {
                        console.log('[Step5] Reaplicando seleção de objetivos após render', dadosDia.objetivos_aprendizagem);
                        window.setObjetivosSelecionados(dadosDia.objetivos_aprendizagem);
                    } else {
                        console.warn('[Step5] setObjetivosSelecionados não definido ainda');
                    }
                }
                console.log('[Step5] Dados do dia carregados:', { dia: diaAtual, dadosDia });
            } else {
                // Se nÃ£o houver dados salvos para este dia, limpar o formulÃ¡rio
                limparFormulario();
                console.log('[Step5] Nenhum dado encontrado para o dia:', diaAtual);
            }
        }
        
        // FunÃ§Ã£o para limpar o formulÃ¡rio
        function limparFormulario() {
            // Desmarcar todos os checkboxes
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
            
            // Limpar todos os campos de texto
            document.querySelectorAll('textarea').forEach(textarea => {
                textarea.value = '';
                textarea.dispatchEvent(new Event('input'));
            });
            
            console.log('[Step5] FormulÃ¡rio limpo para novo preenchimento');
        }
        
        function atualizarInterface() {
            // Obter o dia Ãºtil atual
            if (diaAtual > 0 && diaAtual <= diasUteis.length) {
                const diaUtil = diasUteis[diaAtual - 1];
                
                // Atualizar exibiÃ§Ã£o do dia
                diaAtualDisplay.textContent = `Dia ${diaAtual}`;
                
                // Formatar data do dia Ãºtil
                const dataFormatada = diaUtil.dataOriginal.toLocaleDateString('pt-BR');
                dataAtualDisplay.textContent = dataFormatada;
            }
            
            // Atualizar contador
            const diasPlanejados = Object.values(statusDias).filter(Boolean).length;
            diasPlanejadosContador.textContent = `${diasPlanejados}/${diasUteis.length} dias`;
            
            // Habilitar/desabilitar botÃµes
            btnDiaAnterior.disabled = diaAtual <= 1;
            btnDiaAnterior.classList.toggle('opacity-50', diaAtual <= 1);
            btnProximoDia.disabled = diaAtual >= diasUteis.length;
            btnProximoDia.classList.toggle('opacity-50', diaAtual >= diasUteis.length);
            
            // Carregar dados do dia selecionado
            carregarDadosDia();
        }

        function renderizarCalendario() {
             diasCalendario.innerHTML = '';
             
             // Renderizar apenas os dias úteis
             diasUteis.forEach((diaUtil, index) => {
                 const numeroDia = index + 1; // Índice do dia útil (começando em 1)
                 const dataBtn = diaUtil.dataOriginal;
                 
                 // Verificar se o dia está planejado
                 const planejado = !!statusDias[formatDateKey(dataBtn)];
                 
                 // Definir a classe de cor com base no status
                 let corClasse;
                 if (numeroDia === diaAtual) {
                     corClasse = 'bg-blue-600 text-white';
                 } else if (planejado) {
                     corClasse = 'bg-green-500 text-white hover:bg-green-600';
                 } else {
                     corClasse = 'bg-gray-200 hover:bg-gray-300 text-gray-700';
                 }
                 
                 const btn = document.createElement('button');
                 btn.type = 'button';
                 btn.className = `flex flex-col items-center justify-center min-w-20 h-16 md:w-24 md:h-16 rounded-lg ${corClasse} shadow-md transition-all snap-center`;
                 // Armazenar a chave de data diretamente no botão
                 btn.dataset.dateKey = formatDateKey(dataBtn);
                 btn.innerHTML = `
                     <span class="text-xs md:text-sm">${dataBtn.getDate()}/${dataBtn.getMonth() + 1}</span>
                     <span class="text-xs md:text-sm font-bold">Dia ${numeroDia}</span>
                 `;
                 
                 btn.onclick = () => {
                     diaAtual = numeroDia;
                     atualizarInterface();
                     renderizarCalendario();
                 };
                 
                 diasCalendario.appendChild(btn);
             });
             
             // Atualizar contador de dias planejados
             const diasPlanejados = Object.values(statusDias).filter(Boolean).length;
             diasPlanejadosContador.textContent = `${diasPlanejados}/${diasUteis.length} dias`;
         }

         // FunÃ§Ã£o auxiliar para upsert no servidor
         async function upsertDiarioServidor(dateKey, dadosDia, planejar = false) {
             const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
             // Calcular dia da semana a partir da chave de data
             let diaSemanaCalc = null;
             try {
                 const [y, m, d] = dateKey.split('-').map(n => parseInt(n, 10));
                 diaSemanaCalc = new Date(y, m - 1, d).getDay();
             } catch (_) {
                 diaSemanaCalc = null;
             }
             const payload = {
                 planejamento_id: window.wizardData?.planejamento_id,
                 data: dateKey,
                 dia_semana: diaSemanaCalc,
                 planejado: !!planejar,
                 campos_experiencia: dadosDia?.campos_experiencia || Array.from(document.querySelectorAll('input[name="campos_experiencia[]"]:checked')).map(cb => parseInt(cb.value)),
                 // Enviar saberes e conhecimentos como texto (textarea)
                 saberes_conhecimentos: (typeof dadosDia?.saberes_conhecimentos === 'string' && dadosDia?.saberes_conhecimentos)
                     ? dadosDia.saberes_conhecimentos
                     : (document.getElementById('saberes_conhecimentos')?.value || ''),
                 objetivos_especificos: dadosDia?.objetivos_especificos || document.getElementById('objetivos_especificos')?.value || null,
                 objetivos_aprendizagem: dadosDia?.objetivos_aprendizagem || Array.from(document.querySelectorAll('input[name="objetivos_aprendizagem[]"]:checked')).map(cb => parseInt(cb.value)),
                 metodologia: dadosDia?.metodologia || document.getElementById('metodologia')?.value || null,
                 recursos_predefinidos: dadosDia?.recursos_predefinidos || Array.from(document.querySelectorAll('input[name="recursos_predefinidos[]"]:checked')).map(cb => cb.value),
                 recursos_personalizados: dadosDia?.recursos_personalizados || document.getElementById('recursos_personalizados')?.value || null,
             };
             if (!payload.planejamento_id) return false;
             const resp = await fetch('/planejamentos/wizard/diario/upsert', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'X-CSRF-TOKEN': csrf || ''
                 },
                 body: JSON.stringify(payload)
             });
             const result = await resp.json();
             if (!result?.success) {
                 console.warn('[Step5] Upsert diário não confirmado', result);
                 return false;
             }
             return true;
         }
        
        // Event listeners
         btnDiaAnterior.addEventListener('click', () => {
             if (diaAtual > 1) {
                 // Salvar automaticamente antes de trocar de dia
                 salvarPlanejamentoDiario();
                 diaAtual--;
                 atualizarInterface();
                 renderizarCalendario();
             }
         });
         
         btnProximoDia.addEventListener('click', () => {
             if (diaAtual < diasUteis.length) {
                 // Salvar automaticamente antes de trocar de dia
                 salvarPlanejamentoDiario();
                 diaAtual++;
                 atualizarInterface();
                 renderizarCalendario();
             }
         });

         // Copiar conteúdo do dia atual para múltiplos dias (modal)
         const modalCopy = document.getElementById('copy-day-modal');
         const copyList = document.getElementById('copy-days-list');
         const copySourceLabel = document.getElementById('copy-day-source-label');
         const btnCopyOpen = document.getElementById('btn-copiar-dia');
         const btnCopyApply = document.getElementById('copy-day-apply');
         const btnCopyCancel = document.getElementById('copy-day-cancel');
         const btnCopySelectAll = document.getElementById('copy-day-select-all');
         const btnCopyClear = document.getElementById('copy-day-clear');
         const chkOverwrite = document.getElementById('copy-overwrite');

         function getCurrentDateKey() {
             const currentTile = document.getElementById('dias-calendario')?.children?.[diaAtual - 1] || null;
             const dateKey = (currentTile && currentTile.dataset && currentTile.dataset.dateKey)
                 ? currentTile.dataset.dateKey
                 : formatDateKey(diasUteis[diaAtual - 1].dataOriginal);
             return dateKey;
         }

         function captureDadosFromDOM() {
             return {
                 campos_experiencia: Array.from(document.querySelectorAll('input[name="campos_experiencia[]"]:checked')).map(cb => parseInt(cb.value)),
                 saberes_conhecimentos: document.getElementById('saberes_conhecimentos')?.value || '',
                 objetivos_especificos: document.getElementById('objetivos_especificos').value,
                 objetivos_aprendizagem: Array.from(document.querySelectorAll('input[name="objetivos_aprendizagem[]"]:checked')).map(cb => parseInt(cb.value)),
                 metodologia: document.getElementById('metodologia').value,
                 recursos_predefinidos: Array.from(document.querySelectorAll('input[name="recursos_predefinidos[]"]:checked')).map(cb => cb.value),
                 recursos_personalizados: document.getElementById('recursos_personalizados').value
             };
         }

         function buildCopyDaysList() {
             copyList.innerHTML = '';
             diasUteis.forEach((du, index) => {
                 const numeroDia = index + 1;
                 if (numeroDia === diaAtual) return; // pular dia atual
                 const dateKey = formatDateKey(du.dataOriginal);
                 const label = `${du.dataOriginal.getDate()}/${String(du.dataOriginal.getMonth()+1).padStart(2,'0')} — Dia ${numeroDia}`;
                 const item = document.createElement('label');
                 item.className = 'flex items-center justify-between p-2 border border-gray-200 rounded mb-1';
                 item.innerHTML = `
                    <div class="flex items-center">
                        <input type="checkbox" class="mr-2 copy-day-target" data-date-key="${dateKey}">
                        <span class="text-sm text-gray-800">${label}</span>
                    </div>
                    <span class="text-xs ${statusDias[dateKey] ? 'text-green-600' : 'text-gray-400'}">
                        ${statusDias[dateKey] ? 'Planejado' : 'Não planejado'}
                    </span>
                 `;
                 copyList.appendChild(item);
             });
         }

         function openCopyModal() {
             const sourceKey = getCurrentDateKey();
             const du = diasUteis[diaAtual - 1];
             copySourceLabel.textContent = `Dia ${diaAtual} — ${du.dataOriginal.toLocaleDateString('pt-BR')}`;
             buildCopyDaysList();
             modalCopy.classList.remove('hidden');
         }

         function closeCopyModal() {
             modalCopy.classList.add('hidden');
         }

         btnCopyOpen?.addEventListener('click', openCopyModal);
         btnCopyCancel?.addEventListener('click', closeCopyModal);

         btnCopySelectAll?.addEventListener('click', () => {
             copyList.querySelectorAll('.copy-day-target').forEach(cb => cb.checked = true);
         });
         btnCopyClear?.addEventListener('click', () => {
             copyList.querySelectorAll('.copy-day-target').forEach(cb => cb.checked = false);
         });

         btnCopyApply?.addEventListener('click', async () => {
             // obter dados fonte
             const sourceKey = getCurrentDateKey();
             const wizard = window.planejamentoWizard;
             const dadosSource = (wizard?.formData?.[5]?.dados_por_dia?.[sourceKey]) 
                 ? JSON.parse(JSON.stringify(wizard.formData[5].dados_por_dia[sourceKey]))
                 : captureDadosFromDOM();

             const targets = Array.from(copyList.querySelectorAll('.copy-day-target'))
                 .filter(cb => cb.checked)
                 .map(cb => cb.getAttribute('data-date-key'));

             if (!targets.length) {
                 alert('Selecione ao menos um dia destino.');
                 return;
             }

             // Aplicar localmente e persistir
             btnCopyApply.disabled = true;
             btnCopyApply.textContent = 'Copiando...';

             if (!wizard.formData[5]) wizard.formData[5] = {};
             if (!wizard.formData[5].dados_por_dia) wizard.formData[5].dados_por_dia = {};

             for (const tk of targets) {
                 // se não sobrescrever e já há conteúdo, pule
                 if (!chkOverwrite.checked && wizard.formData[5].dados_por_dia[tk]) continue;

                 wizard.formData[5].dados_por_dia[tk] = JSON.parse(JSON.stringify(dadosSource));
                 statusDias[tk] = true;

                 try {
                     await upsertDiarioServidor(tk, wizard.formData[5].dados_por_dia[tk], true);
                 } catch (e) {
                     console.warn('[Step5] Falha ao copiar para', tk, e);
                 }
             }

             btnCopyApply.disabled = false;
             btnCopyApply.textContent = 'Copiar';
             closeCopyModal();
             atualizarInterface();
             renderizarCalendario();
             alert('Conteúdo copiado para os dias selecionados.');
         });
         
         // BotÃ£o para salvar o planejamento do dia atual
         document.getElementById('salvar-dia').addEventListener('click', () => {
             salvarPlanejamentoDiario();
             
             // Feedback visual
             const btn = document.getElementById('salvar-dia');
             const textoOriginal = btn.innerHTML;
             btn.innerHTML = '<i class="fas fa-check-double mr-1"></i> Planejamento salvo!';
             btn.classList.remove('bg-green-500', 'hover:bg-green-600');
             btn.classList.add('bg-blue-500', 'hover:bg-blue-600');
             
             setTimeout(() => {
                 btn.innerHTML = textoOriginal;
                 btn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                 btn.classList.add('bg-green-500', 'hover:bg-green-600');
             }, 2000);
         });
        
        // Inicializar
        carregarDadosPlanejamento();
    }
    // Contadores de caracteres
    setupCharacterCounter('saberes_conhecimentos', 'saberes-count', 1000);
    setupCharacterCounter('metodologia', 'metodologia-count', 1500);
    setupCharacterCounter('objetivos_especificos', 'objetivos-especificos-count', 1000);

    // SugestÃµes de saberes
    setupSugestoesSaberes();

    // (Chamadas movidas para o início do initStep5 para garantir disponibilidade dos helpers)

    // Resumo dinÃ¢mico
    setupResumoConteudo();

    function setupCharacterCounter(textareaId, counterId, maxLength) {
        const textarea = document.getElementById(textareaId);
        const counter = document.getElementById(counterId);
        
        // Adicionar evento de blur para salvar automaticamente quando o usuÃ¡rio sair do campo
        textarea.addEventListener('blur', function() {
            // Salvar automaticamente ao finalizar interaÃ§Ã£o com o campo
            // Chamar via botÃ£o para garantir escopo
            const btnSalvar = document.getElementById('salvar-dia');
            if (btnSalvar) btnSalvar.click();
        });
        
        if (textarea && counter) {
            function updateCounter() {
                const length = textarea.value.length;
                counter.textContent = length;
                
                if (length > maxLength) {
                    counter.classList.add('text-red-500');
                    counter.classList.remove('text-gray-400');
                } else {
                    counter.classList.remove('text-red-500');
                    counter.classList.add('text-gray-400');
                }
            }
            
            textarea.addEventListener('input', updateCounter);
            updateCounter(); // Inicial
        }
    }

    function setupSugestoesSaberes() {
        console.log('[Step5] setupSugestoesSaberes inicializado');
        const btnSugestoes = document.getElementById('btn-sugestoes-saberes');
        const divSugestoes = document.getElementById('sugestoes-saberes');
        const listaSugestoes = document.getElementById('lista-sugestoes-saberes');
        const camposCheckboxes = document.querySelectorAll('input[name="campos_experiencia[]"]');

        btnSugestoes.addEventListener('click', function() {
            const camposSelecionados = Array.from(camposCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => parseInt(cb.value));

            if (camposSelecionados.length === 0) {
                listaSugestoes.innerHTML = `
                    <div class="text-center text-gray-500 py-4">
                        <i class="fas fa-info-circle text-lg mb-2"></i>
                        <p>Selecione pelo menos um campo de experiência para ver as sugestões</p>
                    </div>
                `;
                divSugestoes.classList.toggle('hidden');
                return;
            }

            // Mostrar loading
            listaSugestoes.innerHTML = `
                <div class="text-center text-gray-500 py-4">
                    <i class="fas fa-spinner fa-spin text-lg mb-2"></i>
                    <p>Carregando sugestões...</p>
                </div>
            `;
            divSugestoes.classList.remove('hidden');

            // Fazer requisição para a API
            const params = new URLSearchParams();
            camposSelecionados.forEach(campo => {
                params.append('campos_experiencia[]', campo);
            });
            console.log('[Step5] Sugestões: params', params.toString());

            fetch(`{{ route('api.planejamentos.sugestoes-saberes') }}?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.sugestoes && data.sugestoes.length > 0) {
                        listaSugestoes.innerHTML = data.sugestoes.map(sugestao => `
                            <button type="button" 
                                    onclick="adicionarSugestao('${sugestao.replace(/'/g, "\\'")}')"
                                    class="w-full text-left p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors">
                                <i class="fas fa-plus text-blue-600 mr-2"></i>
                                ${sugestao}
                            </button>
                        `).join('');
                    } else {
                        listaSugestoes.innerHTML = `
                            <div class="text-center text-gray-500 py-4">
                                <i class="fas fa-search text-lg mb-2"></i>
                                <p>Nenhuma sugestão encontrada para os campos selecionados</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar sugestões:', error);
                    listaSugestoes.innerHTML = `
                        <div class="text-center text-red-500 py-4">
                            <i class="fas fa-exclamation-triangle text-lg mb-2"></i>
                            <p>Erro ao carregar sugestões. Tente novamente.</p>
                        </div>
                    `;
                });
        });

        // Atualizar sugestões automaticamente quando campos de experiência mudarem
        camposCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Se o dropdown de sugestões estiver aberto, recarregar
                if (!divSugestoes.classList.contains('hidden')) {
                    btnSugestoes.click(); // Fechar
                    setTimeout(() => {
                        btnSugestoes.click(); // Reabrir com novos dados
                    }, 100);
                }
            });
        });
    }

    function setupSaberesConhecimentos() {
        console.log('[Step5] setupSaberesConhecimentos inicializado');
        const camposCheckboxes = document.querySelectorAll('input[name="campos_experiencia[]"]');
        const listaSaberes = document.getElementById('lista-saberes');
        const buscaSaber = document.getElementById('busca-saber');
        let saberesCarregados = [];
        let saberesSelecionados = [];

        function carregarSaberes() {
            const camposSelecionados = Array.from(camposCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => parseInt(cb.value));

            if (camposSelecionados.length === 0) {
                listaSaberes.innerHTML = `
                    <div class="text-center text-gray-500 py-6">
                        <i class="fas fa-search text-xl mb-2"></i>
                        <p>Selecione os campos de experiência para ver os saberes disponíveis</p>
                    </div>
                `;
                saberesCarregados = [];
                return;
            }

            listaSaberes.innerHTML = `
                <div class="text-center text-gray-500 py-6">
                    <i class="fas fa-spinner fa-spin text-xl mb-2"></i>
                    <p>Carregando saberes e conhecimentos...</p>
                </div>
            `;

            const params = new URLSearchParams();
            camposSelecionados.forEach(campo => params.append('campos_experiencia[]', campo));
            if (buscaSaber.value.trim()) params.append('busca', buscaSaber.value.trim());
            console.log('[Step5] carregarSaberes: params', params.toString());

            fetch(`{{ route('api.planejamentos.saberes-conhecimentos') }}` + `?${params.toString()}`)
                .then(resp => resp.json())
                .then(data => {
                    if (data.success && data.saberes) {
                        saberesCarregados = data.saberes;
                        renderizarSaberes(saberesCarregados);
                    } else {
                        listaSaberes.innerHTML = `
                            <div class="text-center text-gray-500 py-6">
                                <i class="fas fa-search text-xl mb-2"></i>
                                <p>Nenhum saber encontrado com os filtros aplicados</p>
                            </div>
                        `;
                        saberesCarregados = [];
                    }
                })
                .catch(err => {
                    console.error('Erro ao carregar saberes:', err);
                    listaSaberes.innerHTML = `
                        <div class="text-center text-red-500 py-6">
                            <i class="fas fa-exclamation-triangle text-xl mb-2"></i>
                            <p>Erro ao carregar saberes. Tente novamente.</p>
                        </div>
                    `;
                    saberesCarregados = [];
                });
        }

        function renderizarSaberes(saberes) {
            if (!saberes.length) {
                listaSaberes.innerHTML = `
                    <div class="text-center text-gray-500 py-6">
                        <i class="fas fa-search text-xl mb-2"></i>
                        <p>Nenhum saber encontrado com os filtros aplicados</p>
                    </div>
                `;
                return;
            }

            listaSaberes.innerHTML = saberes.map(saber => `
                <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:border-blue-300 cursor-pointer transition-colors ${saberesSelecionados.includes(saber.id) ? 'bg-blue-50 border-blue-300' : ''}">
                    <input type="checkbox" name="saberes_selecionados[]" value="${saber.id}"
                           ${saberesSelecionados.includes(saber.id) ? 'checked' : ''}
                           class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           onchange="toggleSaber(${saber.id})">
                    <div class="ml-3 flex-1">
                        <div class="text-sm font-medium text-gray-900">${saber.titulo}</div>
                        <div class="text-xs text-gray-500 mt-1">${saber.campo_experiencia ? saber.campo_experiencia.nome : 'Campo nÃ£o definido'}</div>
                        ${saber.descricao ? `<div class=\"text-sm text-gray-600\">${saber.descricao}</div>` : ''}
                    </div>
                </label>
            `).join('');
        }

        window.toggleSaber = function(id) {
            const idx = saberesSelecionados.indexOf(id);
            if (idx > -1) {
                saberesSelecionados.splice(idx, 1);
            } else {
                saberesSelecionados.push(id);
            }
            atualizarSaberesSelecionados();
        };

        function atualizarSaberesSelecionados() {
            const container = document.getElementById('saberes-selecionados');
            const lista = document.getElementById('lista-saberes-selecionados');
            if (!saberesSelecionados.length) {
                container.classList.add('hidden');
                return;
            }
            container.classList.remove('hidden');
            lista.innerHTML = saberesSelecionados.map(id => {
                const s = saberesCarregados.find(x => x.id === id);
                return s ? `
                    <div class="flex items-center justify-between p-2 bg-blue-50 border border-blue-200 rounded">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-blue-900">${s.titulo}</div>
                            <div class="text-xs text-blue-700">${s.campo_experiencia ? s.campo_experiencia.nome : ''}</div>
                        </div>
                        <button type="button" onclick="toggleSaber(${s.id})" class="ml-2 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                ` : '';
            }).join('');
        }

        // Expor helpers globais para integração com salvamento/carregamento
        window.getSaberesSelecionados = function() {
            return saberesSelecionados.slice();
        };
        window.setSaberesSelecionados = function(arr) {
            saberesSelecionados = Array.isArray(arr) ? arr.map(v => parseInt(v)) : [];
            atualizarSaberesSelecionados();
            // Re-render para refletir checkboxes
            renderizarSaberes(saberesCarregados);
        };

        // Eventos
        camposCheckboxes.forEach(cb => cb.addEventListener('change', carregarSaberes));
        let timeoutBuscaSaber;
        buscaSaber.addEventListener('input', () => {
            clearTimeout(timeoutBuscaSaber);
            timeoutBuscaSaber = setTimeout(carregarSaberes, 500);
        });
        console.log('[Step5] setupSaberesConhecimentos: eventos registrados');

        // Inicializar
        carregarSaberes();
    }

    function setupObjetivosAprendizagem() {
        console.log('[Step5] setupObjetivosAprendizagem inicializado');
        const camposCheckboxes = document.querySelectorAll('input[name="campos_experiencia[]"]');
        const listaObjetivos = document.getElementById('lista-objetivos');
        const filtroFaixaEtaria = document.getElementById('filtro-faixa-etaria');
        const filtroCampo = document.getElementById('filtro-campo');
        const buscaObjetivo = document.getElementById('busca-objetivo');

        let objetivosCarregados = [];
        let objetivosSelecionados = [];
        // Helpers globais para integração com carregamento/salvamento
        window.getObjetivosSelecionados = function() {
            return objetivosSelecionados.slice();
        };
        window.setObjetivosSelecionados = function(arr) {
            objetivosSelecionados = Array.isArray(arr) ? arr.map(v => parseInt(v)) : [];
            // Atualiza painel e re-renderiza para refletir seleção
            console.log('[Step5] setObjetivosSelecionados chamado', { selecionados: objetivosSelecionados, carregados: objetivosCarregados.length });
            atualizarObjetivosSelecionados();
            renderizarObjetivos(objetivosCarregados);
        };

        function atualizarFiltrosCampos() {
            const camposSelecionados = Array.from(camposCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => parseInt(cb.value));

            // Atualizar dropdown de campos
            filtroCampo.innerHTML = '<option value="">Todos os campos</option>';
            
            // Buscar nomes dos campos dos objetivos carregados
            const camposUnicos = [...new Set(objetivosCarregados.map(obj => obj.campo_experiencia))];
            camposUnicos.forEach(campo => {
                if (campo && campo.id && camposSelecionados.includes(campo.id)) {
                    const option = document.createElement('option');
                    option.value = campo.id;
                    option.textContent = campo.nome;
                    filtroCampo.appendChild(option);
                }
            });

            carregarObjetivos();
        }

        function carregarObjetivos() {
            const camposSelecionados = Array.from(camposCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => parseInt(cb.value));

            if (camposSelecionados.length === 0) {
                listaObjetivos.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-search text-2xl mb-2"></i>
                        <p>Selecione os campos de experiência para ver os objetivos disponíveis</p>
                    </div>
                `;
                objetivosCarregados = [];
                return;
            }

            // Mostrar loading
            listaObjetivos.innerHTML = `
                <div class="text-center text-gray-500 py-8">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p>Carregando objetivos de aprendizagem...</p>
                </div>
            `;

            // Preparar parÃ¢metros para a API
            const params = new URLSearchParams();
            camposSelecionados.forEach(campo => {
                params.append('campos_experiencia[]', campo);
            });

            if (filtroFaixaEtaria.value) {
                params.append('faixa_etaria', filtroFaixaEtaria.value);
            }

            if (buscaObjetivo.value.trim()) {
                params.append('busca', buscaObjetivo.value.trim());
            }
            console.log('[Step5] carregarObjetivos: params', params.toString());

            // Fazer requisiÃ§Ã£o para a API
            fetch(`{{ route('api.planejamentos.objetivos-aprendizagem') }}?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.objetivos) {
                        objetivosCarregados = data.objetivos;
                        renderizarObjetivos(data.objetivos);
                    } else {
                        listaObjetivos.innerHTML = `
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-search text-2xl mb-2"></i>
                                <p>Nenhum objetivo encontrado com os filtros aplicados</p>
                            </div>
                        `;
                        objetivosCarregados = [];
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar objetivos:', error);
                    listaObjetivos.innerHTML = `
                        <div class="text-center text-red-500 py-8">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p>Erro ao carregar objetivos. Tente novamente.</p>
                        </div>
                    `;
                    objetivosCarregados = [];
                });
        }

        function renderizarObjetivos(objetivos) {
            // Aplicar filtro de campo se selecionado
            let objetivosFiltrados = objetivos;
            const campoFiltro = filtroCampo.value;
            if (campoFiltro) {
                objetivosFiltrados = objetivos.filter(obj => 
                    obj.campo_experiencia && obj.campo_experiencia.id === parseInt(campoFiltro)
                );
            }

            if (objetivosFiltrados.length === 0) {
                listaObjetivos.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        <i class="fas fa-search text-2xl mb-2"></i>
                        <p>Nenhum objetivo encontrado com os filtros aplicados</p>
                    </div>
                `;
                return;
            }

            listaObjetivos.innerHTML = objetivosFiltrados.map(objetivo => `
                <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:border-blue-300 cursor-pointer transition-colors ${objetivosSelecionados.includes(objetivo.id) ? 'bg-blue-50 border-blue-300' : ''}">
                    <input type="checkbox" name="objetivos_aprendizagem[]" value="${objetivo.id}" 
                           ${objetivosSelecionados.includes(objetivo.id) ? 'checked' : ''}
                           class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                           onchange="toggleObjetivo(${objetivo.id})">
                    <div class="ml-3 flex-1">
                        <div class="text-sm font-medium text-gray-900">${objetivo.codigo || 'Sem cÃ³digo'}</div>
                        <div class="text-sm text-gray-600">${objetivo.descricao}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            ${objetivo.campo_experiencia ? objetivo.campo_experiencia.nome : 'Campo nÃ£o definido'} â€¢ 
                            Faixa etÃ¡ria: ${objetivo.faixa_etaria || 'NÃ£o definida'}
                        </div>
                    </div>
                </label>
            `).join('');
        }

        // Event listeners
        camposCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', atualizarFiltrosCampos);
        });

        filtroFaixaEtaria.addEventListener('change', carregarObjetivos);
        filtroCampo.addEventListener('change', () => {
            // Para filtro de campo, apenas re-renderizar os objetivos jÃ¡ carregados
            renderizarObjetivos(objetivosCarregados);
        });
        
        // Debounce para busca
        let timeoutBusca;
        buscaObjetivo.addEventListener('input', () => {
            clearTimeout(timeoutBusca);
            timeoutBusca = setTimeout(() => {
                carregarObjetivos();
            }, 500); // Aguarda 500ms apÃ³s parar de digitar
        });

        // FunÃ§Ã£o global para toggle de objetivos
        window.toggleObjetivo = function(objetivoId) {
            const index = objetivosSelecionados.indexOf(objetivoId);
            if (index > -1) {
                objetivosSelecionados.splice(index, 1);
            } else {
                objetivosSelecionados.push(objetivoId);
            }
            atualizarObjetivosSelecionados();
        };

        function atualizarObjetivosSelecionados() {
            const container = document.getElementById('objetivos-selecionados');
            const lista = document.getElementById('lista-objetivos-selecionados');

            if (objetivosSelecionados.length === 0) {
                container.classList.add('hidden');
                return;
            }

            container.classList.remove('hidden');
            
            const objetivosTexto = objetivosSelecionados.map(id => {
                const objetivo = objetivosCarregados.find(obj => obj.id === id);
                return objetivo ? `
                    <div class="flex items-center justify-between p-2 bg-blue-50 border border-blue-200 rounded">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-blue-900">${objetivo.codigo || 'Sem cÃ³digo'}</div>
                            <div class="text-xs text-blue-700">${objetivo.descricao}</div>
                        </div>
                        <button type="button" onclick="toggleObjetivo(${objetivo.id})" 
                                class="ml-2 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                ` : '';
            }).join('');

            lista.innerHTML = objetivosTexto;
        }

        // Inicializar
        atualizarFiltrosCampos();
    }

    function setupResumoConteudo() {
        function atualizarResumo() {
            // Campos de experiÃªncia
            const camposSelecionados = document.querySelectorAll('input[name="campos_experiencia[]"]:checked').length;
            const resumoCampos = document.getElementById('resumo-campos');
            if (resumoCampos) {
                resumoCampos.textContent = `${camposSelecionados} selecionados`;
            }

            // Objetivos (serÃ¡ atualizado pela funÃ§Ã£o de objetivos)
            const objetivosSelecionados = document.querySelectorAll('input[name="objetivos_aprendizagem[]"]:checked').length;
            const resumoObjetivos = document.getElementById('resumo-objetivos');
            if (resumoObjetivos) {
                resumoObjetivos.textContent = `${objetivosSelecionados} selecionados`;
            }

            // Recursos
            const recursosSelecionados = document.querySelectorAll('input[name="recursos_predefinidos[]"]:checked').length;
            const resumoRecursos = document.getElementById('resumo-recursos');
            if (resumoRecursos) {
                resumoRecursos.textContent = `${recursosSelecionados} selecionados`;
            }

            // Saberes selecionados
            const saberesSelecionados = document.querySelectorAll('input[name="saberes_selecionados[]"]:checked').length;
            const resumoSaberes = document.getElementById('resumo-saberes');
            if (resumoSaberes) {
                resumoSaberes.textContent = `${saberesSelecionados} selecionados`;
            }
        }

        // Atualizar resumo quando houver mudanÃ§as
        document.addEventListener('change', atualizarResumo);
        window.atualizarResumo = atualizarResumo; // Disponibilizar globalmente para poder remover na etapa 6
        atualizarResumo(); // Inicial
    }

    // FunÃ§Ã£o global para adicionar sugestÃ£o
    window.adicionarSugestao = function(sugestao) {
        const textarea = document.getElementById('saberes_conhecimentos');
        const valorAtual = textarea.value;
        const novoValor = valorAtual ? `${valorAtual}\nâ€¢ ${sugestao}` : `â€¢ ${sugestao}`;
        textarea.value = novoValor;
        
        // Trigger do evento para atualizar contador
        textarea.dispatchEvent(new Event('input'));
    };

    // Gerar inputs hidden a partir de objetivos especÃ­ficos (um por linha)
    const objetivosEspecificos = document.getElementById('objetivos_especificos');
    const objetivosHiddenContainer = document.getElementById('objetivos-especificos-hidden');
    if (objetivosEspecificos && objetivosHiddenContainer) {
        function atualizarHiddenObjetivos() {
            objetivosHiddenContainer.innerHTML = '';
            const linhas = objetivosEspecificos.value.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
            linhas.forEach(linha => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'objetivos_especificos[]';
                input.value = linha;
                objetivosHiddenContainer.appendChild(input);
            });
        }
        objetivosEspecificos.addEventListener('input', atualizarHiddenObjetivos);
        atualizarHiddenObjetivos();
    }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStep5);
    } else {
        initStep5();
    }
})();
</script>