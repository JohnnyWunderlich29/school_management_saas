@extends('layouts.app')

@section('title', 'Calendário de Escalas')

@section('content')
    <div class="w-full mx-auto">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <h2 class="text-2xl font-bold text-gray-900">Calendário de Escalas</h2>
                        <p class="mt-1 text-sm text-gray-600">Visualize as escalas dos funcionários em formato de calendário
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <x-button href="{{ route('funcionarios.index') }}" color="secondary">
                            <i class="fas fa-users mr-1"></i> Funcionários
                        </x-button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex h-screen">
                <!-- Sidebar - Lista de Funcionários -->
                <aside class="hidden md:block w-80 bg-gray-50 border-r border-gray-200 overflow-y-auto"
                    id="sidebarFuncionarios">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-users mr-2"></i>Funcionários
                        </h3>

                        <!-- Filtros -->
                        <div class="mb-4">
                            <div class="relative">
                                <input type="text" id="searchFuncionario" placeholder="Buscar funcionário..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Funcionários -->
                        <div class="space-y-2" id="funcionariosList">
                            @forelse($funcionarios as $funcionario)
                                <div class="funcionario-item p-3 rounded-lg border border-gray-200 hover:bg-white cursor-pointer transition-colors duration-200 bg-white"
                                    data-funcionario-id="{{ $funcionario->id }}"
                                    data-funcionario-nome="{{ $funcionario->nome }}">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($funcionario->nome, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $funcionario->nome }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ $funcionario->cargo ?? 'Sem cargo' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <i class="fas fa-users text-gray-300 text-3xl mb-3"></i>
                                    <p class="text-gray-500">Nenhum funcionário encontrado</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </aside>

                <!-- Main Content - Calendário -->
                <main class="flex-1 overflow-hidden">
                    <!-- Botão Mobile para mostrar funcionários -->
                    <div class="sm:hidden bg-white border-b border-gray-200 p-4">
                        <button id="toggleFuncionarios"
                            class="w-full flex items-center justify-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                            <i class="fas fa-users mr-2"></i>
                            <span
                                id="funcionarioAtual">{{ $funcionarios->first()->nome ?? 'Selecionar Funcionário' }}</span>
                            <i class="fas fa-chevron-down ml-2"></i>
                        </button>
                    </div>

                    <!-- Modal/Overlay Mobile para lista de funcionários -->
                    <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden sm:hidden">
                        <div class="absolute inset-x-0 top-0 bg-white max-h-96 overflow-y-auto">
                            <div class="p-4 border-b border-gray-200">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        <i class="fas fa-users mr-2"></i>Funcionários
                                    </h3>
                                    <button id="closeMobileList" class="text-gray-500 hover:text-gray-700">
                                        <i class="fas fa-times text-xl"></i>
                                    </button>
                                </div>

                                <!-- Filtro Mobile -->
                                <div class="mb-4">
                                    <div class="relative">
                                        <input type="text" id="searchFuncionarioMobile"
                                            placeholder="Buscar funcionário..."
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lista Mobile -->
                                <div class="space-y-2" id="funcionariosListMobile">
                                    @forelse($funcionarios as $funcionario)
                                        <div class="funcionario-item p-3 rounded-lg border border-gray-200 hover:bg-blue-50 cursor-pointer transition-colors duration-200 bg-white"
                                            data-funcionario-id="{{ $funcionario->id }}"
                                            data-funcionario-nome="{{ $funcionario->nome }}" data-is-mobile="true">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div
                                                        class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                        {{ strtoupper(substr($funcionario->nome, 0, 2)) }}
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        {{ $funcionario->nome }}</p>
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ $funcionario->cargo ?? 'Sem cargo' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-8">
                                            <i class="fas fa-users text-gray-300 text-3xl mb-3"></i>
                                            <p class="text-gray-500">Nenhum funcionário encontrado</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class=" h-full flex flex-col">
                        <!-- Header do Calendário -->
                        <div class="px-6 py-4 border-b border-gray-200 bg-white">
                            <div class="flex flex-col md:flex-row justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800" id="funcionarioSelecionado">
                                        {{ $funcionarios->first()->nome ?? 'Selecione um funcionário' }}
                                    </h3>
                                    <p class="text-sm text-gray-600" id="cargoFuncionario">
                                        {{ $funcionarios->first()->cargo->nome ?? '' }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <!-- Navegação de Mês -->
                                    <div class="flex items-center space-x-2">
                                        <button type="button" id="mesAnterior"
                                            class="p-2 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <span class="text-lg font-medium text-gray-900 min-w-[200px] text-center"
                                            id="mesAtual">
                                            {{ now()->locale('pt_BR')->isoFormat('MMMM [de] YYYY') }}
                                        </span>
                                        <button type="button" id="proximoMes"
                                            class="p-2 text-gray-400 hover:text-gray-600">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calendário -->
                        <div class="flex-1 overflow-auto p-6">
                            <!-- Loading Spinner -->
                            <div id="loadingSpinner" class="hidden flex items-center justify-center py-12">
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                                    <p class="text-gray-600 text-sm">Carregando escalas...</p>
                                </div>
                            </div>

                            <!-- Mensagem para Selecionar Funcionário -->
                            <div id="mensagemSelecionarFuncionario" class="flex items-center justify-center py-16">
                                <div class="text-center max-w-md">
                                    <div
                                        class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-user-plus text-blue-600 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Selecione um Funcionário</h3>
                                    <p class="text-gray-600 mb-6">Para visualizar as escalas, selecione um funcionário na
                                        lista ao lado.</p>
                                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                        <button id="btnSelecionarPrimeiro"
                                            class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-user mr-2"></i>
                                            Selecionar Primeiro Funcionário
                                        </button>
                                        <!-- REMOVIDO: btnMostrarListaMobile - conflitava com toggleFuncionarios -->
                                    </div>
                                </div>
                            </div>

                            <!-- Div informativa para Manhã Opcional -->
            <div id="divManhaOpcional" class="hidden mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-sun text-yellow-600 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-yellow-800">Período Manhã (Opcional)</h4>
                        <p class="text-sm text-yellow-700">Este funcionário possui escalas no período da manhã.</p>
                    </div>
                </div>
            </div>

            <!-- Versão Desktop - Tabela -->
            <div id="calendarioDesktop"
                class="hidden md:block bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div id="calendarioTableDesktop" class="overflow-x-auto">
                    <!-- Será preenchido via JavaScript -->
                </div>
            </div>

                            <!-- Versão Mobile - Cards -->
                            <div id="calendarioMobile"
                                class="block md:hidden bg-white rounded-lg border border-gray-200 overflow-hidden">
                                <!-- Navegação de Semanas Mobile -->
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <button type="button" id="semanaAnterior"
                                            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>

                                        <div class="text-center">
                                            <h4 id="tituloSemana" class="text-sm font-semibold text-gray-800">Semana 1
                                            </h4>
                                            <div class="flex items-center justify-center space-x-1 mt-1">
                                                <div id="indicadorSemana1" class="w-2 h-2 rounded-full bg-blue-500"></div>
                                                <div id="indicadorSemana2" class="w-2 h-2 rounded-full bg-gray-300"></div>
                                                <div id="indicadorSemana3" class="w-2 h-2 rounded-full bg-gray-300"></div>
                                                <div id="indicadorSemana4" class="w-2 h-2 rounded-full bg-gray-300"></div>
                                            </div>
                                        </div>

                                        <button type="button" id="proximaSemana"
                                            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>

                                <div id="calendarioContent" class="p-4">
                                    <!-- Será preenchido via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Modal para Detalhes da Escala -->
    <div id="modalDetalhes" class="fixed inset-0 bg-gray-600/50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Detalhes da Escala</h3>
                </div>
                <div class="px-6 py-4" id="modalContent">
                    <!-- Conteúdo será preenchido via JavaScript -->
                </div>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                    <button type="button" id="fecharModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')

    
    <script>
        // Função route() para JavaScript
        window.route = function(name, params = {}) {
            const routes = {
                'templates.gerar-escalas.form': '/funcionarios/{funcionario}/templates/gerar-escalas'
            };
            
            let url = routes[name];
            if (url && params) {
                Object.keys(params).forEach(key => {
                    url = url.replace(`{${key}}`, params[key]);
                });
            }
            return url;
        };
        // Variáveis globais
        let funcionarioAtual = null;
        let mesAtual = new Date();
        let escalasData = {};
        let debounceTimers = {}; // Para implementar debounce
        let cacheEscalas = new Map(); // Cache de escalas
        let cacheFuncionarios = null; // Cache de funcionários
        const CACHE_DURATION = 5 * 60 * 1000; // 5 minutos em milliseconds

        // Inicialização do sistema

        // Função principal de inicialização
        function inicializarCalendario() {
            // Evitar inicialização dupla
            if (window.calendarioInicializado) {
                return;
            }

            // Verificar se elementos críticos existem
            const calendarioDesktop = document.getElementById('calendarioDesktop');
            const calendarioMobile = document.getElementById('calendarioMobile');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const funcionarioSelect = document.getElementById('funcionarioSelect');
            const csrfToken = document.querySelector('meta[name="csrf-token"]');

            if (!calendarioDesktop || !calendarioMobile) {
                if (typeof AlertService !== 'undefined') {
                    AlertService.error('Erro crítico: Elementos do calendário não encontrados!');
                }
                return;
            }

            if (!csrfToken) {
                if (typeof AlertService !== 'undefined') {
                    AlertService.error('Erro crítico: Token CSRF não encontrado!');
                }
                return;
            }

            // Marcar como inicializado
            window.calendarioInicializado = true;

            // Inicializar eventos e funcionalidade mobile
            inicializarEventos();
            inicializarMobile();

            // Aguardar um pouco para garantir que todos os elementos estejam renderizados
            // Função recursiva para verificar se os elementos estão prontos
            function verificarElementosEInicializar(tentativas = 0) {
                const maxTentativas = 50; // Máximo 5 segundos (50 * 100ms)
                const funcionariosDisponiveis = document.querySelectorAll('[data-funcionario-id]');
                
                // Se encontrou funcionários ou excedeu tentativas, prosseguir
                if (funcionariosDisponiveis.length > 0 || tentativas >= maxTentativas) {
                    // Verificação do funcionário selecionado
                    if (funcionarioAtual && funcionarioAtual !== null && funcionarioAtual !== 'null' &&
                        funcionarioAtual !== '') {
                        // Marcar o funcionário como selecionado na interface
                        const funcionarioElement = document.querySelector(
                            `[data-funcionario-id="${funcionarioAtual}"]`);
                        if (funcionarioElement) {
                            funcionarioElement.classList.add('bg-blue-50', 'border-blue-300');
                            funcionarioElement.classList.remove('bg-white', 'border-gray-200');
                        }

                        mostrarMensagemSelecao(false);
                        carregarEscalasComLoading(funcionarioAtual);
                    } else {
                        @if ($funcionarios->count() > 0)
                            // Selecionar automaticamente o funcionário ID 11 se disponível
                            const funcionario11 = document.querySelector('[data-funcionario-id="11"]');
                            if (funcionario11) {
                                // Aguardar um pouco mais antes de simular o clique
                                setTimeout(() => {
                                    funcionario11.click();
                                }, 50);
                            } else {
                                // Se funcionário 11 não existe, selecionar o primeiro disponível
                                const primeiroFuncionario = document.querySelector('[data-funcionario-id]');
                                if (primeiroFuncionario) {
                                    setTimeout(() => {
                                        primeiroFuncionario.click();
                                    }, 50);
                                } else {
                                    mostrarMensagemSelecao(true);
                                    document.getElementById('funcionarioSelecionado').textContent = 'Selecione um funcionário';
                                    const funcionarioAtualElement = document.getElementById('funcionarioAtual');
                                    if (funcionarioAtualElement) {
                                        funcionarioAtualElement.textContent = 'Selecionar Funcionário';
                                    }
                                }
                            }
                        @else
                            ocultarLoadingEMostrarMensagem('Nenhum funcionário disponível no sistema');
                        @endif
                    }
                } else {
                    // Aguardar mais um pouco e tentar novamente
                    setTimeout(() => verificarElementosEInicializar(tentativas + 1), 100);
                }
            }
            
            // Iniciar verificação
            verificarElementosEInicializar();
        }

        // Solução robusta baseada nas melhores práticas para problemas de timing
        // Implementa verificação de document.readyState conforme MDN Web Docs
        function iniciarSistema() {
            // Verificar se o DOM já está pronto ou se precisa aguardar
            if (document.readyState !== 'loading') {
                // DOM já está pronto (interactive ou complete)
                inicializarCalendario();
            } else {
                // DOM ainda está carregando, aguardar DOMContentLoaded
                document.addEventListener('DOMContentLoaded', inicializarCalendario);
            }
        }

        // Executar inicialização
        iniciarSistema();

        // Eventos
        function inicializarEventos() {
            // Event delegation consolidado para todos os cliques
            document.addEventListener('click', function(e) {
                // Verificar se o clique foi em um funcionário
                const funcionarioItem = e.target.closest('.funcionario-item');
                if (funcionarioItem) {
                    e.preventDefault();
                    e.stopPropagation();
                    selecionarFuncionario(funcionarioItem);
                    return;
                }

                // Navegação de mês
                const btnAnterior = e.target.closest('#mesAnterior');
                if (btnAnterior) {
                    e.preventDefault();
                    navegarMes(-1);
                    return;
                }

                const btnProximo = e.target.closest('#proximoMes');
                if (btnProximo) {
                    e.preventDefault();
                    navegarMes(1);
                    return;
                }

                // Fechar modal
                if (e.target.id === 'fecharModal') {
                    const modal = document.getElementById('modalDetalhes');
                    if (modal) modal.classList.add('hidden');
                    return;
                }

                // Selecionar primeiro funcionário
                if (e.target.id === 'btnSelecionarPrimeiro') {
                    const primeiroFuncionario = document.querySelector('.funcionario-item');
                    if (primeiroFuncionario) {
                        selecionarFuncionario(primeiroFuncionario);
                    }
                    return;
                }
            });

            // Event delegation para busca de funcionários
            document.addEventListener('input', function(e) {
                if (e.target.id === 'searchFuncionario' || e.target.id === 'searchFuncionarioMobile') {
                    filtrarFuncionariosComDebounce(e.target.value);
                }
            });

            // Verificar se funcionários existem
            const funcionarioItems = document.querySelectorAll('.funcionario-item');
        }

        // Mostrar/Ocultar mensagem de seleção
        function mostrarMensagemSelecao(mostrar = true) {
            const mensagem = document.getElementById('mensagemSelecionarFuncionario');
            const calendarioDesktop = document.getElementById('calendarioDesktop');
            const calendarioMobile = document.getElementById('calendarioMobile');

            if (mostrar) {
                mensagem?.classList.remove('hidden');
                calendarioDesktop?.classList.add('hidden');
                calendarioMobile?.classList.add('hidden');
            } else {
                mensagem?.classList.add('hidden');
                calendarioDesktop?.classList.remove('hidden');
                calendarioMobile?.classList.remove('hidden');
            }
        }

        // Seleção de funcionário unificada
        function selecionarFuncionario(elemento) {
            const isMobile = elemento.dataset.isMobile === 'true';

            // Remove seleção anterior de todos os itens
            document.querySelectorAll('.funcionario-item').forEach(item => {
                item.classList.remove('bg-blue-50', 'border-blue-300');
                item.classList.add('bg-white', 'border-gray-200');
            });

            // Adiciona seleção atual
            elemento.classList.remove('bg-white', 'border-gray-200');
            elemento.classList.add('bg-blue-50', 'border-blue-300');

            // Atualiza dados
            funcionarioAtual = elemento.dataset.funcionarioId;
            const nomeFuncionario = elemento.dataset.funcionarioNome;

            // Oculta mensagem de seleção e mostra calendário
            mostrarMensagemSelecao(false);

            // Atualiza interface
            const funcionarioSelecionadoElement = document.getElementById('funcionarioSelecionado');
            if (funcionarioSelecionadoElement) {
                funcionarioSelecionadoElement.textContent = nomeFuncionario;
            }

            // Se for mobile, atualiza o botão e fecha o overlay
            if (isMobile) {
                const funcionarioAtualElement = document.getElementById('funcionarioAtual');
                if (funcionarioAtualElement) {
                    funcionarioAtualElement.textContent = nomeFuncionario;
                }

                // Fechar overlay mobile
                const mobileOverlay = document.getElementById('mobileOverlay');
                if (mobileOverlay) {
                    mobileOverlay.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

            // Carrega escalas com loading
            carregarEscalasComLoading(funcionarioAtual);
        }

        // Atualizar mês
        function atualizarMes() {
            const meses = [
                'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ];

            document.getElementById('mesAtual').textContent =
                `${meses[mesAtual.getMonth()]} de ${mesAtual.getFullYear()}`;
        }

        // Navegação de mês com debounce
        function navegarMes(direcao) {
            // Cancelar timer anterior se existir
            if (debounceTimers.navegacao) {
                clearTimeout(debounceTimers.navegacao);
            }

            // Criar novo timer com debounce de 300ms
            debounceTimers.navegacao = setTimeout(() => {
                mesAtual.setMonth(mesAtual.getMonth() + direcao);
                atualizarMes();
                if (funcionarioAtual) {
                    carregarEscalasComLoading(funcionarioAtual);
                }
            }, 300);
        }

        // Carregar escalas com loading state e cache
        function carregarEscalasComLoading(funcionarioId) {
            if (!funcionarioId) {
                if (typeof AlertService !== 'undefined') {
                    AlertService.error('Nenhum funcionário selecionado!');
                }
                ocultarLoadingEMostrarMensagem('Selecione um funcionário para visualizar as escalas');
                return;
            }

            const mesParam = mesAtual.toISOString().slice(0, 7);
            const cacheKey = `${funcionarioId}-${mesParam}`;

            // Verificar cache primeiro
            const dadosCache = obterDoCache('escalas', cacheKey);
            if (dadosCache) {
                renderizarCalendario(dadosCache);
                return;
            }

            // Mostrar loading
            mostrarLoading();

            // Fazer requisição AJAX para buscar escalas
            fetch(`/api/funcionarios/${funcionarioId}/escalas?mes=${mesParam}`, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    // Verificar se a resposta indica erro de autenticação
                    if (response.status === 401) {
                        throw new Error('401 - Não autorizado');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Converter objeto agrupado em array de escalas
                        const escalasArray = [];
                        Object.values(data.escalas).forEach(escalasDoDia => {
                            escalasDoDia.forEach(escala => {
                                // Converter data string para Date mantendo fuso horário local
                                // A API retorna "2025-09-04", precisamos criar Date sem conversão UTC
                                const [ano, mes, dia] = escala.data.split('-').map(Number);
                                escala.data = new Date(ano, mes - 1, dia); // mes - 1 porque Date usa 0-11 para meses
                                escalasArray.push(escala);
                            });
                        });

                        // Se não há escalas, usar dados simulados
                        if (escalasArray.length === 0) {
                            if (typeof AlertService !== 'undefined') {
                                AlertService.info('Nenhuma escala encontrada. Exibindo dados simulados.');
                            }
                            const escalasSimuladas = gerarEscalasSimuladas();
                            renderizarCalendario(escalasSimuladas);
                        } else {
                            if (typeof AlertService !== 'undefined') {
                                AlertService.success(`${escalasArray.length} escala(s) carregada(s) com sucesso!`);
                            }
                            // Salvar no cache
                            salvarNoCache('escalas', cacheKey, escalasArray);
                            renderizarCalendario(escalasArray);
                        }
                    } else {
                        if (typeof AlertService !== 'undefined') {
                            AlertService.warning('API retornou erro. Usando dados simulados.');
                        }
                        // Fallback para dados simulados se a API não estiver disponível
                        const escalasSimuladas = gerarEscalasSimuladas();
                        renderizarCalendario(escalasSimuladas);
                    }
                })
                .catch(error => {
                    // Verificar se é erro de autenticação (401)
                    if (error.message && error.message.includes('401')) {
                        if (typeof AlertService !== 'undefined') {
                            AlertService.error('Sessão expirada. Redirecionando para login...');
                        }
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 2000);
                        return;
                    }

                    if (typeof AlertService !== 'undefined') {
                        AlertService.error('Erro ao carregar escalas. Exibindo dados simulados.');
                    }
                    // Fallback para dados simulados em caso de erro
                    const escalasSimuladas = gerarEscalasSimuladas();
                    renderizarCalendario(escalasSimuladas);
                });
        }

        // Funções auxiliares para loading states
        function mostrarLoading() {
            document.getElementById('loadingSpinner').classList.remove('hidden');
            document.getElementById('calendarioDesktop').classList.add('hidden');
            document.getElementById('calendarioMobile').classList.add('hidden');
        }

        function ocultarLoadingEMostrarMensagem(mensagem) {
            document.getElementById('loadingSpinner').classList.add('hidden');
            document.getElementById('calendarioDesktop').classList.add('hidden');
            document.getElementById('calendarioMobile').classList.add('hidden');
            document.getElementById('calendarioContent').innerHTML = `<p class="text-center text-gray-500">${mensagem}</p>`;
        }

        function mostrarErro(mensagem) {
            // Criar elemento de erro se não existir
            let errorElement = document.getElementById('errorMessage');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.id = 'errorMessage';
                errorElement.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
                document.querySelector('.flex-1.overflow-auto.p-6').prepend(errorElement);
            }

            errorElement.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span>${mensagem}</span>
            <button onclick="this.parentElement.parentElement.style.display='none'" class="ml-auto text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
            errorElement.style.display = 'block';

            // Auto-hide após 5 segundos
            setTimeout(() => {
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
            }, 5000);
        }

        // Gerar escalas simuladas (para demonstração)
        function gerarEscalasSimuladas() {
            const escalas = [];
            const diasMes = new Date(mesAtual.getFullYear(), mesAtual.getMonth() + 1, 0).getDate();

            for (let dia = 1; dia <= diasMes; dia++) {
                const data = new Date(mesAtual.getFullYear(), mesAtual.getMonth(), dia);
                const diaSemana = data.getDay();

                // Simula escalas apenas em dias úteis
                if (diaSemana >= 1 && diaSemana <= 5) {
                    escalas.push({
                        data: data,
                        horario_inicio: '07:00',
                        horario_fim: '11:00',
                        tipo_atividade: 'em_sala',
                        data_formatada: data.toLocaleDateString('pt-BR'),
                        observacoes: 'Escala simulada'
                    });
                }
            }

            return escalas;
        }

        // Renderizar calendário
        function renderizarCalendario(escalas) {
            // Verificar se os elementos existem
            const loadingSpinner = document.getElementById('loadingSpinner');
            const calendarioDesktop = document.getElementById('calendarioDesktop');
            const calendarioMobile = document.getElementById('calendarioMobile');

            if (!loadingSpinner || !calendarioDesktop || !calendarioMobile) {
                if (typeof AlertService !== 'undefined') {
                    AlertService.error('Elementos do calendário não encontrados no DOM!');
                }
                return;
            }

            renderizarCalendarioDesktop(escalas);
            renderizarCalendarioMobile(escalas);

            // Ocultar loading e mostrar calendários
            loadingSpinner.classList.add('hidden');
            calendarioDesktop.classList.remove('hidden');
            calendarioMobile.classList.remove('hidden');
        }

        // Renderizar versão desktop (tabela)
        function renderizarCalendarioDesktop(escalas) {
            // Obter dias do mês atual
            const diasMes = new Date(mesAtual.getFullYear(), mesAtual.getMonth() + 1, 0).getDate();
            const primeiroDia = new Date(mesAtual.getFullYear(), mesAtual.getMonth(), 1);

            // Criar array com os dias do mês
            const diasCalendario = [];
            const hoje = new Date();
            const diaHoje = hoje.getDate();
            const mesHoje = hoje.getMonth();
            const anoHoje = hoje.getFullYear();

            for (let dia = 1; dia <= diasMes; dia++) {
                const data = new Date(mesAtual.getFullYear(), mesAtual.getMonth(), dia);
                const isHoje = (dia === diaHoje && mesAtual.getMonth() === mesHoje && mesAtual.getFullYear() === anoHoje);

                const escalasDoDia = escalas.filter(e => {
                    let escalaData;
                    if (e.data instanceof Date) {
                        escalaData = e.data;
                    } else {
                        // Converter string de data mantendo fuso horário local
                        const [ano, mes, dia] = e.data.split('-').map(Number);
                        escalaData = new Date(ano, mes - 1, dia);
                    }
                    const mesmoMes = escalaData.getMonth() === mesAtual.getMonth();
                    const mesmoAno = escalaData.getFullYear() === mesAtual.getFullYear();
                    const mesmoDia = escalaData.getDate() === dia;
                    
                    return mesmoMes && mesmoAno && mesmoDia;
                });
                
                diasCalendario.push({
                    numero: dia,
                    data: data,
                    diaSemana: data.getDay(),
                    escalas: escalasDoDia,
                    isHoje: isHoje
                });
            }

            let html = '';

            // Container da tabela
            html += `
        <div class="overflow-x-auto" style="max-width: 100%;">
            <div class="min-w-max hidden md:block">
    `;

            // Cabeçalho da tabela com todos os dias do mês
            html += `
        <div class="border-b-2 border-gray-300 mb-4">
            <div class="flex bg-gray-50">
                <div class="flex-shrink-0 p-2 bg-gray-100 rounded text-center font-medium text-gray-700 border border-gray-200" style="width: 150px; min-width: 150px;">
                    Período
                </div>
    `;

            // Gerar cabeçalho para todos os dias
            diasCalendario.forEach(dia => {
                const nomesDias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                const isWeekend = dia.diaSemana === 0 || dia.diaSemana === 6;

                let bgClass = 'bg-white';
                let textClass = 'text-gray-800';
                let borderClass = 'border-gray-200';

                if (dia.isHoje) {
                    bgClass = 'bg-blue-100';
                    textClass = 'text-blue-800';
                    borderClass = 'border-blue-300';
                } else if (isWeekend) {
                    bgClass = 'bg-red-50';
                    textClass = 'text-red-600';
                }

                html += `
            <div class="text-center p-2 ${bgClass} rounded flex-shrink-0 border ${borderClass}" style="width: 150px; min-width: 150px;" ${dia.isHoje ? 'data-dia-hoje="true"' : ''}>
                <div class="text-xs font-medium text-gray-600">${nomesDias[dia.diaSemana]}</div>
                <div class="text-lg font-bold ${textClass}">${dia.numero}${dia.isHoje ? ' <span class="text-xs bg-blue-500 text-white px-1 rounded">HOJE</span>' : ''}</div>
            </div>
        `;
            });

            html += `</div></div>`;

            // Obter períodos únicos das escalas usando o campo periodo da API
            const periodosUnicos = new Set();
            const periodosPorTurno = {};
            
            escalas.forEach(escala => {
                const periodo = escala.periodo || 'manha'; // fallback para manhã
                const horaInicio = escala.horario_inicio || escala.hora_inicio;
                const horaFim = escala.horario_fim || escala.hora_fim;
                
                if (horaInicio && horaFim) {
                    // Extrair apenas a parte da hora (remover data se presente)
                    const horaInicioLimpa = horaInicio.split(' ')[1] || horaInicio;
                    const horaFimLimpa = horaFim.split(' ')[1] || horaFim;
                    const horario = `${horaInicioLimpa}-${horaFimLimpa}`;
                    
                    periodosUnicos.add(horario);
                    
                    // Mapear período para nome de exibição
                    let turnoNome = 'Manhã';
                    if (periodo === 'tarde') {
                        turnoNome = 'Tarde';
                    } else if (periodo === 'noite') {
                        turnoNome = 'Noite';
                    } else if (periodo === 'madrugada') {
                        turnoNome = 'Madrugada';
                    }
                    
                    if (!periodosPorTurno[turnoNome]) {
                        periodosPorTurno[turnoNome] = [];
                    }
                    
                    // Evitar duplicatas
                    const jaExiste = periodosPorTurno[turnoNome].some(p => p.horario === horario);
                    if (!jaExiste) {
                        const horaInicioNum = parseInt(horaInicioLimpa.split(':')[0]);
                        periodosPorTurno[turnoNome].push({
                            horario: horario,
                            horaInicio: horaInicioNum
                        });
                    }
                }
            });
            
            // Converter para array de turnos ordenados
            const periodos = Object.keys(periodosPorTurno)
                .sort((a, b) => {
                    const ordem = { 'Madrugada': 1, 'Manhã': 2, 'Tarde': 3, 'Noite': 4 };
                    return ordem[a] - ordem[b];
                })
                .map(turno => {
                    const horariosTurno = periodosPorTurno[turno].sort((a, b) => a.horaInicio - b.horaInicio);
                    const primeiroHorario = horariosTurno[0].horario;
                    const ultimoHorario = horariosTurno[horariosTurno.length - 1].horario;
                    
                    return {
                        nome: turno,
                        horarios: horariosTurno.map(p => p.horario),
                        horarioExibicao: horariosTurno.length === 1 ? primeiroHorario : 
                                       `${primeiroHorario.split('-')[0]}-${ultimoHorario.split('-')[1]}`,
                        tipo: turno
                    };
                });
            
            // Se não há períodos, não mostrar nada
            if (periodos.length === 0) {
                document.getElementById('calendarioTableDesktop').innerHTML = '<p class="text-center text-gray-500 p-4">Nenhuma escala encontrada para este período.</p>';
                // Ocultar div de manhã opcional
                const divManhaOpcional = document.getElementById('divManhaOpcional');
                if (divManhaOpcional) {
                    divManhaOpcional.classList.add('hidden');
                }
                return;
            }

            // Verificar se há escalas de manhã e mostrar/ocultar div informativa
            const temEscalasManha = periodos.some(periodo => periodo.nome === 'Manhã');
            const divManhaOpcional = document.getElementById('divManhaOpcional');
            if (divManhaOpcional) {
                if (temEscalasManha) {
                    divManhaOpcional.classList.remove('hidden');
                } else {
                    divManhaOpcional.classList.add('hidden');
                }
            }

            periodos.forEach(periodo => {
                html += `
            <div class="flex min-h-[100px]">
                <div class="flex-shrink-0 p-2 bg-gray-50 flex items-center justify-center border border-gray-200" style="width: 150px; min-width: 150px;">
                    <div class="text-xs font-medium text-gray-700 text-center">
                        <div>${periodo.nome}</div>
                        <div class="text-gray-500">${periodo.horarioExibicao}</div>
                    </div>
                </div>
        `;

                // Gerar células para todos os dias do período
                diasCalendario.forEach(dia => {
                    const isWeekend = dia.diaSemana === 0 || dia.diaSemana === 6;
                    let conteudoCelula = '';

                    // Verificar se há escalas para qualquer horário deste turno neste dia
                    const escalasDoPeriodo = dia.escalas.filter(escala => {
                        // A API retorna horario_inicio e horario_fim, não hora_inicio e hora_fim
                        const horaInicio = escala.horario_inicio || escala.hora_inicio;
                        const horaFim = escala.horario_fim || escala.hora_fim;
                        
                        if (!horaInicio || !horaFim) return false;
                        
                        const horaInicioLimpa = horaInicio.split(' ')[1] || horaInicio;
                        const horaFimLimpa = horaFim.split(' ')[1] || horaFim;
                        const horarioEscala = `${horaInicioLimpa}-${horaFimLimpa}`;

                        return periodo.horarios.includes(horarioEscala);
                    });

                    // Exibir escalas tanto em dias úteis quanto fins de semana
                    if (escalasDoPeriodo.length > 0) {
                        const bgColor = isWeekend ? 'bg-red-100 hover:bg-red-200' : 'bg-blue-100 hover:bg-blue-200';
                        const textColor = isWeekend ? 'text-red-800' : 'text-blue-800';
                        const subtextColor = isWeekend ? 'text-red-600' : 'text-blue-600';
                        
                        const linhas = escalasDoPeriodo.map(escala => {
                            const horaInicioRaw = (escala.horario_inicio || escala.hora_inicio);
                            const horaFimRaw = (escala.horario_fim || escala.hora_fim);
                            const horaInicio = (horaInicioRaw && horaInicioRaw.includes(' ')) ? horaInicioRaw.split(' ')[1] : horaInicioRaw || '';
                            const horaFim = (horaFimRaw && horaFimRaw.includes(' ')) ? horaFimRaw.split(' ')[1] : horaFimRaw || '';
                            const sala = escala.sala_nome || '';
                            const disc = escala.disciplina_nome || '';
                            return `${horaInicio}-${horaFim} - ${sala} - ${disc}`;
                        });
                        const linhasUnicas = [...new Set(linhas)];
                        const linhasHtml = linhasUnicas.map(l => `<div class=\"text-[11px] ${subtextColor}\">${l}</div>`).join('');
                        conteudoCelula = `
                     <div class="${bgColor} rounded p-1 cursor-pointer transition-colors h-full w-full" 
                          onclick="mostrarDetalhes('${dia.data.toISOString()}', '${periodo.nome}')">
                         ${linhasHtml}
                     </div>
                 `;
                    }

                    html += `
                <div class="p-1 ${isWeekend ? 'bg-gray-50' : 'bg-white'} flex items-center flex-shrink-0 border border-gray-200" style="width: 150px; min-width: 150px;">
                    ${conteudoCelula}
                </div>
            `;
                });

                html += `</div>`;
            });

            html += `</div></div>`;

            document.getElementById('calendarioTableDesktop').innerHTML = html;
        }

        // Renderizar versão mobile (cards)
        function renderizarCalendarioMobile(escalas) {
            const container = document.getElementById('calendarioContent');
            if (!container) return;

            // Obter dias do mês atual
            const diasMes = new Date(mesAtual.getFullYear(), mesAtual.getMonth() + 1, 0).getDate();
            const primeiroDia = new Date(mesAtual.getFullYear(), mesAtual.getMonth(), 1);

            // Criar array com os dias do mês
            const diasCalendario = [];
            const hoje = new Date();
            const diaHoje = hoje.getDate();
            const mesHoje = hoje.getMonth();
            const anoHoje = hoje.getFullYear();

            for (let dia = 1; dia <= diasMes; dia++) {
                const data = new Date(mesAtual.getFullYear(), mesAtual.getMonth(), dia);
                const escalasNoDia = escalas.filter(escala => {
                    let escalaData;
                    if (escala.data instanceof Date) {
                        escalaData = escala.data;
                    } else {
                        // Converter string de data mantendo fuso horário local
                        const [ano, mes, dia] = escala.data.split('-').map(Number);
                        escalaData = new Date(ano, mes - 1, dia);
                    }
                    return escalaData.getDate() === dia &&
                        escalaData.getMonth() === mesAtual.getMonth() &&
                        escalaData.getFullYear() === mesAtual.getFullYear();
                });

                diasCalendario.push({
                    numero: dia,
                    data: data,
                    diaSemana: data.getDay(),
                    escalas: escalasNoDia,
                    isHoje: dia === diaHoje && mesAtual.getMonth() === mesHoje && mesAtual.getFullYear() === anoHoje
                });
            }

            // Dividir dias por semanas (7 dias cada)
            const semanas = [];
            for (let i = 0; i < diasCalendario.length; i += 7) {
                semanas.push(diasCalendario.slice(i, i + 7));
            }

            // Renderizar todas as semanas
            const nomesDias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            let html = '';

            semanas.forEach((semana, indexSemana) => {
                html += `<div class="semana-mobile" data-semana="${indexSemana}">`;

                semana.forEach(dia => {
                    const isWeekend = dia.diaSemana === 0 || dia.diaSemana === 6;
                    let bgClass = 'bg-white';
                    let textClass = 'text-gray-800';
                    let borderClass = 'border-gray-200';

                    if (dia.isHoje) {
                        bgClass = 'bg-blue-50';
                        textClass = 'text-blue-800';
                        borderClass = 'border-blue-300';
                    } else if (isWeekend) {
                        bgClass = 'bg-gray-50';
                        textClass = 'text-gray-600';
                    }

                    html += `
                <div class="${bgClass} rounded-lg border ${borderClass} p-4 mb-3">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="text-lg font-semibold ${textClass}">${dia.numero}</h3>
                            <p class="text-sm text-gray-500">${nomesDias[dia.diaSemana]}</p>
                            ${dia.isHoje ? '<span class="text-xs bg-blue-500 text-white px-2 py-1 rounded">HOJE</span>' : ''}
                        </div>
                    </div>
                    
                    <div class="space-y-2">
            `;

                    if (dia.escalas.length > 0) {
                        const bgColor = isWeekend ? 'bg-orange-100' : 'bg-blue-100';
                        const hoverColor = isWeekend ? 'hover:bg-orange-200' : 'hover:bg-blue-200';
                        const textColor = isWeekend ? 'text-orange-800' : 'text-blue-800';
                        const subColor = isWeekend ? 'text-orange-600' : 'text-blue-600';
                        const iconColor = isWeekend ? 'text-orange-400' : 'text-blue-400';
                        const blocos = dia.escalas.map(escala => {
                            const horaInicio = escala.horario_inicio || '00:00';
                            const horaFim = escala.horario_fim || '00:00';
                            const sala = escala.sala_nome || '';
                            const disc = escala.disciplina_nome || '';
                            const linha = `${horaInicio}-${horaFim} - ${sala} - ${disc}`;
                            return `
                            <div class="${bgColor} rounded p-3 mb-2 cursor-pointer ${hoverColor} transition-colors" onclick="mostrarDetalhes('${dia.data.toISOString()}', '${linha}')">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <div class="text-sm font-medium ${textColor}">${linha}</div>
                                    </div>
                                    <i class="fas fa-chevron-right ${iconColor}"></i>
                                </div>
                            </div>
                            `;
                        }).join('');
                        html += blocos;
                    } else {
                        html += `
                        <div class="text-center py-4 text-gray-400">
                            <i class="fas fa-calendar-times text-2xl mb-2"></i>
                            <p class="text-sm">Sem escalas</p>
                        </div>
                `;
                    }

                    html += `
                     </div>
                 </div>
             `;
                });

                html += `</div>`;
            });

            container.innerHTML = html;

            // Inicializar navegação de semanas após renderizar
            setTimeout(() => {
                if (typeof atualizarSemana === 'function') {
                    atualizarSemana();
                }
            }, 100);
        }

        // Mostrar detalhes
        function mostrarDetalhes(data, periodo) {
            const modal = document.getElementById('modalDetalhes');
            const content = document.getElementById('modalContent');


            content.innerHTML = `
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Data</label>
                <p class="text-sm text-gray-900">${new Date(data).toLocaleDateString('pt-BR')}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Período</label>
                <p class="text-sm text-gray-900">${periodo}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Atividade</label>
                <p class="text-sm text-gray-900">Em Sala</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Observações</label>
                <p class="text-sm text-gray-900">-</p>
            </div>
        </div>
    `;

            modal.classList.remove('hidden');
        }

        // Filtrar funcionários unificado com debounce
        function filtrarFuncionariosComDebounce(termo) {
            // Cancelar timer anterior se existir
            if (debounceTimers.busca) {
                clearTimeout(debounceTimers.busca);
            }

            // Criar novo timer com debounce de 300ms
            debounceTimers.busca = setTimeout(() => {
                filtrarFuncionarios(termo);
            }, 300);
        }

        // Filtrar funcionários unificado
        function filtrarFuncionarios(termo) {
            const items = document.querySelectorAll('.funcionario-item');
            const termoLower = termo.toLowerCase();

            items.forEach(item => {
                const nome = item.dataset.funcionarioNome.toLowerCase();
                if (nome.includes(termoLower)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Sistema de cleanup para evitar memory leaks
        let eventListeners = [];

        function adicionarEventListener(elemento, evento, handler) {
            if (elemento) {
                elemento.addEventListener(evento, handler);
                eventListeners.push({
                    elemento,
                    evento,
                    handler
                });
            }
        }

        function limparEventListeners() {
            eventListeners.forEach(({
                elemento,
                evento,
                handler
            }) => {
                if (elemento) {
                    elemento.removeEventListener(evento, handler);
                }
            });
            eventListeners = [];
        }

        // Limpar timers ativos
        function limparTimers() {
            Object.values(debounceTimers).forEach(timer => {
                if (timer) {
                    clearTimeout(timer);
                }
            });
            debounceTimers = {};
        }

        // Funcionalidade Mobile - REMOVIDO DOMContentLoaded duplicado
        // Esta funcionalidade agora é inicializada no DOMContentLoaded principal
        function inicializarMobile() {
            // Limpar recursos anteriores
            limparEventListeners();
            limparTimers();

            const toggleBtn = document.getElementById('toggleFuncionarios');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const closeBtn = document.getElementById('closeMobileList');
            const funcionarioAtualElement = document.getElementById('funcionarioAtual');
            const searchMobile = document.getElementById('searchFuncionarioMobile');

            // Verificar se elementos mobile existem

            // Navegação de semanas mobile
            let semanaAtual = 0;
            const totalSemanas = 4;

            // Função para detectar semana atual baseada na data de hoje
            function detectarSemanaAtual() {
                const hoje = new Date();
                const diaHoje = hoje.getDate();

                // Calcular qual semana baseado no dia do mês
                if (diaHoje <= 7) {
                    return 0; // Semana 1
                } else if (diaHoje <= 14) {
                    return 1; // Semana 2
                } else if (diaHoje <= 21) {
                    return 2; // Semana 3
                } else {
                    return 3; // Semana 4
                }
            }

            function atualizarSemana() {
                // Ocultar todas as semanas
                const todasSemanas = document.querySelectorAll('.semana-mobile');
                todasSemanas.forEach(semana => {
                    semana.classList.add('hidden');
                });

                // Mostrar semana atual
                const semanaAtiva = document.querySelector(`[data-semana="${semanaAtual}"]`);
                if (semanaAtiva) {
                    semanaAtiva.classList.remove('hidden');
                }

                // Atualizar título
                const tituloSemana = document.getElementById('tituloSemana');
                if (tituloSemana) {
                    tituloSemana.textContent = `Semana ${semanaAtual + 1}`;
                }

                // Atualizar indicadores
                for (let i = 1; i <= totalSemanas; i++) {
                    const indicador = document.getElementById(`indicadorSemana${i}`);
                    if (indicador) {
                        if (i === semanaAtual + 1) {
                            indicador.classList.remove('bg-gray-300');
                            indicador.classList.add('bg-blue-500');
                        } else {
                            indicador.classList.remove('bg-blue-500');
                            indicador.classList.add('bg-gray-300');
                        }
                    }
                }

                // Atualizar botões (desabilitar se necessário)
                const btnAnterior = document.getElementById('semanaAnterior');
                const btnProxima = document.getElementById('proximaSemana');

                if (btnAnterior) {
                    if (semanaAtual === 0) {
                        btnAnterior.classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        btnAnterior.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }

                if (btnProxima) {
                    if (semanaAtual === totalSemanas - 1) {
                        btnProxima.classList.add('opacity-50', 'cursor-not-allowed');
                    } else {
                        btnProxima.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }
            }

            // Função de inicialização
            function inicializar() {
                // Inicializar com a semana atual
                semanaAtual = detectarSemanaAtual();

                // Configurar event listeners dos botões
                configurarBotoes();

                // Aguardar um pouco para garantir que o DOM foi renderizado
                setTimeout(() => {
                    atualizarSemana();

                    // Scroll automático para o dia atual na versão desktop
                    const diaAtualDesktop = document.querySelector('.hidden.sm\\:block [data-dia-hoje="true"]');
                    if (diaAtualDesktop) {
                        diaAtualDesktop.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                            inline: 'center'
                        });
                    }
                }, 500);
            }

            // Função para configurar os botões
            function configurarBotoes() {
                // Botão semana anterior
                const btnSemanaAnterior = document.getElementById('semanaAnterior');
                if (btnSemanaAnterior) {
                    btnSemanaAnterior.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (semanaAtual > 0) {
                            semanaAtual--;
                            atualizarSemana();
                        }
                    });
                }

                // Botão próxima semana
                const btnProximaSemana = document.getElementById('proximaSemana');
                if (btnProximaSemana) {
                    btnProximaSemana.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (semanaAtual < totalSemanas - 1) {
                            semanaAtual++;
                            atualizarSemana();
                        }
                    });
                }
            }

            // REMOVIDO: DOMContentLoaded duplicado - inicialização agora é feita no DOMContentLoaded principal
            // A função inicializar() será chamada diretamente se necessário

            // Event listeners dos botões agora são configurados na função configurarBotoes()

            // Abrir lista mobile
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    if (mobileOverlay) {
                        mobileOverlay.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                });
            }

            // Fechar lista mobile
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    mobileOverlay.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                });
            }

            // Fechar ao clicar no overlay
            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', function(e) {
                    if (e.target === mobileOverlay) {
                        mobileOverlay.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                });
            }

            // Busca mobile
            if (searchMobile) {
                searchMobile.addEventListener('input', function() {
                    filtrarFuncionariosMobile(this.value);
                });
            }

            // Event listeners para funcionários mobile já são tratados pela função unificada selecionarFuncionario
            // Os elementos mobile usam a mesma classe .funcionario-item com data-is-mobile="true"

            // Configurar botão Gerar Escalas com cleanup
            const btnGerarEscalas = document.getElementById('btnGerarEscalas');
            if (btnGerarEscalas) {
                const gerarEscalasHandler = function(e) {
                    e.preventDefault();

                    if (funcionarioAtual) {
                        const url = window.route('templates.gerar-escalas.form', funcionarioAtual);
                        window.location.href = url;
                    } else {
                        // Verificar se há funcionários disponíveis
                        const primeiroFuncionario = document.querySelector('.funcionario-item');
                        if (primeiroFuncionario) {
                            if (typeof window.alertSystem !== 'undefined' && window.alertSystem.warning) {
                                window.alertSystem.warning('Selecione um funcionário primeiro para gerar escalas.');
                            } else {
                                alert('Selecione um funcionário primeiro para gerar escalas.');
                            }
                        } else {
                            if (typeof window.alertSystem !== 'undefined' && window.alertSystem.error) {
                                window.alertSystem.error('Nenhum funcionário disponível para gerar escalas.');
                            } else {
                                alert('Nenhum funcionário disponível para gerar escalas.');
                            }
                        }
                    }
                };
                adicionarEventListener(btnGerarEscalas, 'click', gerarEscalasHandler);
            }
        }

        // Sistema de Cache
        function salvarNoCache(tipo, chave, dados) {
            try {
                const cacheData = {
                    dados: dados,
                    timestamp: Date.now()
                };
                localStorage.setItem(`cache_${tipo}_${chave}`, JSON.stringify(cacheData));

                // Também manter em memória para acesso mais rápido
                if (tipo === 'escalas') {
                    cacheEscalas.set(chave, cacheData);
                }
            } catch (error) {
                // Erro ao salvar no cache
            }
        }

        function obterDoCache(tipo, chave) {
            try {
                // Verificar cache em memória primeiro
                if (tipo === 'escalas' && cacheEscalas.has(chave)) {
                    const cacheData = cacheEscalas.get(chave);
                    if (Date.now() - cacheData.timestamp < CACHE_DURATION) {
                        return cacheData.dados;
                    } else {
                        cacheEscalas.delete(chave);
                    }
                }

                // Verificar localStorage
                const cacheString = localStorage.getItem(`cache_${tipo}_${chave}`);
                if (cacheString) {
                    const cacheData = JSON.parse(cacheString);

                    // Verificar se não expirou
                    if (Date.now() - cacheData.timestamp < CACHE_DURATION) {
                        // Restaurar para cache em memória
                        if (tipo === 'escalas') {
                            cacheEscalas.set(chave, cacheData);
                        }
                        return cacheData.dados;
                    } else {
                        // Cache expirado, remover
                        localStorage.removeItem(`cache_${tipo}_${chave}`);
                    }
                }
            } catch (error) {
                // Erro ao obter do cache
            }

            return null;
        }

        function limparCache(tipo = null) {
            try {
                if (tipo) {
                    // Limpar tipo específico
                    const keys = Object.keys(localStorage);
                    keys.forEach(key => {
                        if (key.startsWith(`cache_${tipo}_`)) {
                            localStorage.removeItem(key);
                        }
                    });

                    if (tipo === 'escalas') {
                        cacheEscalas.clear();
                    }
                } else {
                    // Limpar todo o cache
                    const keys = Object.keys(localStorage);
                    keys.forEach(key => {
                        if (key.startsWith('cache_')) {
                            localStorage.removeItem(key);
                        }
                    });

                    cacheEscalas.clear();
                    cacheFuncionarios = null;
                }
            } catch (error) {
                // Erro ao limpar cache
            }
        }

        // Função para invalidar cache quando necessário
        function invalidarCacheEscalas(funcionarioId = null) {
            if (funcionarioId) {
                // Invalidar cache específico do funcionário
                const keys = Array.from(cacheEscalas.keys());
                keys.forEach(key => {
                    if (key.startsWith(`${funcionarioId}-`)) {
                        cacheEscalas.delete(key);
                        localStorage.removeItem(`cache_escalas_${key}`);
                    }
                });
            } else {
                // Invalidar todo o cache de escalas
                limparCache('escalas');
            }
        }

        // Cleanup ao sair da página
        window.addEventListener('beforeunload', function() {
            limparEventListeners();
            limparTimers();
        });

        // Limpar cache expirado periodicamente (a cada 10 minutos)
        setInterval(() => {
            const agora = Date.now();

            // Limpar cache em memória
            for (const [chave, dados] of cacheEscalas.entries()) {
                if (agora - dados.timestamp >= CACHE_DURATION) {
                    cacheEscalas.delete(chave);
                }
            }

            // Limpar localStorage
            try {
                const keys = Object.keys(localStorage);
                keys.forEach(key => {
                    if (key.startsWith('cache_')) {
                        const cacheData = JSON.parse(localStorage.getItem(key));
                        if (agora - cacheData.timestamp >= CACHE_DURATION) {
                            localStorage.removeItem(key);
                        }
                    }
                });
            } catch (error) {
                // Erro na limpeza automática do cache
            }
        }, 10 * 60 * 1000); // 10 minutos
    </script>
@endpush
