<!-- Analytics Section -->
<x-card class="mb-8">
    <x-slot name="title">
        <div class="flex items-center">
            <i class="fas fa-chart-line text-blue-600 mr-2"></i>
            Análise de Dados
        </div>
    </x-slot>

    <x-slot name="subtitle">
        @if (request('inicio') && request('fim'))
            Período: {{ \Carbon\Carbon::parse(request('inicio'))->format('d/m/Y') }} a
            {{ \Carbon\Carbon::parse(request('fim'))->format('d/m/Y') }}
        @else
            Métricas e insights no período selecionado
        @endif
    </x-slot>

    <x-slot name="headerActions">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
            <i class="fas fa-clock mr-1"></i>
            Tempo real
        </span>
        <button data-open-modal="modalPersonalizarAnalyticsCards"
            class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
            <i class="fas fa-sliders-h mr-2"></i>
            Personalizar cards
        </button>
    </x-slot>

    <!-- Botão Mobile (Analytics) -->
    <div class="sm:hidden mt-2 flex justify-end">
        <button id="btnPersonalizarAnalyticsCards" data-open-modal="modalPersonalizarAnalyticsCards"
            class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
            <i class="fas fa-sliders-h mr-2"></i>
            Personalizar cards
        </button>
    </div>

    <!-- Filtro de período (Analytics) - novo componente -->
    <div class="flex flex-col items-center justify-end mb-2 md:flex-row">
        <div class="flex items-center gap-3">
            <x-date-filter-with-arrows title=" " name="data_inicio" label="Início" :value="old('data_inicio', $analyticsInicio ?? request('inicio'))"
                dataFimName="data_fim" :dataFimValue="old('data_fim', $analyticsFim ?? request('fim'))" />

            <input type="hidden" id="inicio_hidden" name="inicio" value="{{ $analyticsInicio ?? request('inicio') }}">
            <input type="hidden" id="fim_hidden" name="fim" value="{{ $analyticsFim ?? request('fim') }}">
        </div>
        <div class="flex items-center gap-2">
            <button id="aplicarPeriodo" type="button"
                class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded hover:bg-blue-50 text-sm">
                <i class="fas fa-sync-alt mr-1"></i> Aplicar
            </button>
            <a href="{{ route('dashboard', ['clear_periodo' => 1]) }}" id="limparPeriodo"
                class="text-blue-600 hover:text-blue-500 text-sm">Limpar</a>
        </div>
    </div>
    <div id="analyticsMetricsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Taxa de Presença -->
        <div class="bg-white p-4 md:p-6 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow"
            data-card-key="analytics-metric-presenca">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-500 rounded-lg flex items-center justify-center cursor-move"
                    data-drag-handle draggable="true">
                    <i class="fas fa-chart-line text-white text-base md:text-lg"></i>
                </div>
                <span id="metricTaxaPresencaBadge"
                    class="inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium 
                    @if (isset($dadosAnaliticos['taxaPresencaGeral']) && $dadosAnaliticos['taxaPresencaGeral'] >= 90) bg-green-100 text-green-800
                    @elseif(isset($dadosAnaliticos['taxaPresencaGeral']) && $dadosAnaliticos['taxaPresencaGeral'] >= 75)
                        bg-yellow-100 text-yellow-800
                    @else
                        bg-red-100 text-red-800 @endif">
                    @if (isset($dadosAnaliticos['taxaPresencaGeral']) && $dadosAnaliticos['taxaPresencaGeral'] >= 90)
                        <i class="fas fa-arrow-up mr-1"></i><span class="hidden sm:inline">Excelente</span><span
                            class="sm:hidden">Exc</span>
                    @elseif(isset($dadosAnaliticos['taxaPresencaGeral']) && $dadosAnaliticos['taxaPresencaGeral'] >= 75)
                        <i class="fas fa-minus mr-1"></i>Bom
                    @else
                        <i class="fas fa-arrow-down mr-1"></i><span class="hidden sm:inline">Atenção</span><span
                            class="sm:hidden">Atç</span>
                    @endif
                </span>
            </div>
            <div>
                <h3 class="text-xs md:text-sm font-medium text-blue-900 mb-1">Taxa de Presença</h3>
                <p id="metricTaxaPresencaValue" class="text-2xl md:text-3xl font-bold text-blue-900">
                    {{ $dadosAnaliticos['taxaPresencaGeral'] ?? 0 }}%</p>
                <div class="w-full bg-blue-200 rounded-full h-2 mt-3">
                    <div id="metricTaxaPresencaBar" class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                        style="width: {{ $dadosAnaliticos['taxaPresencaGeral'] ?? 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <div class="bg-white p-4 md:p-6 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow"
            data-card-key="analytics-metric-alertas">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-red-500 rounded-lg flex items-center justify-center cursor-move"
                    data-drag-handle draggable="true">
                    <i class="fas fa-exclamation-triangle text-white text-base md:text-lg"></i>
                </div>
                <span id="metricAlertasBadge"
                    class="inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium 
                    @if (isset($dadosAnaliticos['alertasBaixaFrequencia']) && $dadosAnaliticos['alertasBaixaFrequencia']->count() > 0) bg-red-100 text-red-800
                    @else
                        bg-green-100 text-green-800 @endif">
                    @if (isset($dadosAnaliticos['alertasBaixaFrequencia']) && $dadosAnaliticos['alertasBaixaFrequencia']->count() > 0)
                        <i class="fas fa-bell mr-1"></i>Ativo
                    @else
                        <i class="fas fa-check mr-1"></i>OK
                    @endif
                </span>
            </div>
            <div>
                <h3 class="text-xs md:text-sm font-medium text-red-900 mb-1">Alertas de Frequência</h3>
                <p id="metricAlertasValue" class="text-2xl md:text-3xl font-bold text-red-900">
                    {{ isset($dadosAnaliticos['alertasBaixaFrequencia']) ? $dadosAnaliticos['alertasBaixaFrequencia']->count() : 0 }}
                </p>
                <p class="text-xs md:text-sm text-red-700 mt-1">alunos com baixa frequência</p>
            </div>
        </div>

        <!-- Professores Ativos -->
        <div class="bg-white p-4 md:p-6 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow"
            data-card-key="analytics-metric-professores">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-500 rounded-lg flex items-center justify-center cursor-move"
                    data-drag-handle draggable="true">
                    <i class="fas fa-chalkboard-teacher text-white text-base md:text-lg"></i>
                </div>
                <span id="metricProfessoresBadge"
                    class="inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-users mr-1"></i>Ativos
                </span>
            </div>
            <div>
                <h3 class="text-xs md:text-sm font-medium text-green-900 mb-1">Professores Ativos</h3>
                <p id="metricProfessoresValue" class="text-2xl md:text-3xl font-bold text-green-900">
                    {{ isset($dadosAnaliticos['totalProfessoresComAtividade']) ? $dadosAnaliticos['totalProfessoresComAtividade'] : 0 }}
                </p>
                <p class="text-xs md:text-sm text-green-700 mt-1">com atividade recente</p>
            </div>
        </div>
    </div>

    <!-- Charts -->

    <div id="analyticsChartsGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Presenças por Dia -->
        <x-card data-card-key="analytics-chart-dia">
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-chart-line text-blue-600 mr-2 cursor-move" data-drag-handle draggable="true"></i>
                    <span class="hidden sm:inline">Presenças por Dia</span>
                    <span class="sm:hidden">Presenças</span>
                </div>
            </x-slot>

            <x-slot name="subtitle">
                Série diária de presenças
            </x-slot>

            <x-slot name="headerActions">
                <span
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="fas fa-calendar mr-1"></i>
                    Período
                </span>
            </x-slot>

            <div class="h-64 md:h-80 relative">
                <canvas id="presencasPorDiaChart"></canvas>
                <p id="presencasPorDiaEmptyMsg" class="text-sm text-gray-500 mt-2 hidden">
                    Sem presenças registradas no período para a escola selecionada.
                </p>
            </div>
        </x-card>

        <!-- Presenças por Sala -->
        <x-card data-card-key="analytics-chart-sala">
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-chart-bar text-green-600 mr-2 cursor-move" data-drag-handle draggable="true"></i>
                    <span class="hidden sm:inline">Top 5 Salas</span>
                    <span class="sm:hidden">Top Salas</span>
                </div>
            </x-slot>

            <x-slot name="subtitle">
                Por atividade
            </x-slot>

            <x-slot name="headerActions">
                <span
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-trophy mr-1"></i>
                    Ranking
                </span>
            </x-slot>

            <div class="h-64 md:h-80 relative">
                <canvas id="presencasPorSalaChart"></canvas>
            </div>
        </x-card>
    </div>

    <!-- Data Tables -->
    <div class="flex items-center justify-end mb-2">

    </div>
    <div id="analyticsTablesGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Professores -->
        <x-card data-card-key="analytics-table-professores">
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-chalkboard-teacher text-purple-600 mr-2 cursor-move" data-drag-handle
                        draggable="true"></i>
                    <span class="hidden sm:inline">Professores Ativos</span>
                    <span class="sm:hidden">Professores</span>
                </div>
            </x-slot>

            <x-slot name="subtitle">
                <span class="hidden sm:inline">Últimas atividades</span>
                <span class="sm:hidden">Atividades</span>
            </x-slot>

            <x-slot name="headerActions">
                <a href="{{ route('funcionarios.index') }}"
                    class="inline-flex items-center px-2 md:px-3 py-1 md:py-1.5 border border-transparent text-xs md:text-sm font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200 transition-colors">
                    <i class="fas fa-eye mr-1"></i>
                    <span class="hidden sm:inline">Ver todos</span>
                    <span class="sm:hidden">Ver</span>
                </a>
            </x-slot>

            <div class="overflow-x-auto" id="professoresAtivosContainer">
                @if (isset($dadosAnaliticos['desempenhoProfessores']) && $dadosAnaliticos['desempenhoProfessores']->count() > 0)
                    <!-- Mobile Cards (visible on small screens) -->
                    <div class="block sm:hidden space-y-3" id="professoresAtivosMobile">
                        @foreach ($dadosAnaliticos['desempenhoProfessores']->take(3) as $professor)
                            <div class="bg-gray-50 p-3 rounded-lg border">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-900 text-sm">{{ $professor->nome }}</h4>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-circle text-green-400 mr-1" style="font-size: 6px;"></i>
                                        Ativo
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 mb-1">{{ $professor->cargo ?? 'Professor' }}</p>
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    Última atividade: {{ $professor->ultima_atividade ?? 'Hoje' }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Desktop Table (hidden on small screens) -->
                    <table class="hidden sm:table min-w-full divide-y divide-gray-200" id="professoresAtivosTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Professor
                                </th>
                                <th
                                    class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                    Cargo
                                </th>
                                <th
                                    class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($dadosAnaliticos['desempenhoProfessores']->take(5) as $professor)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 md:h-10 md:w-10">
                                                <div
                                                    class="h-8 w-8 md:h-10 md:w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                    <i class="fas fa-user text-purple-600 text-xs md:text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3 md:ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $professor->nome }}
                                                </div>
                                                <div class="text-xs md:text-sm text-gray-500 md:hidden">
                                                    {{ $professor->cargo ?? 'Professor' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-3 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                        {{ $professor->cargo ?? 'Professor' }}
                                    </td>
                                    <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle text-green-400 mr-1" style="font-size: 6px;"></i>
                                            Ativo
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-8 md:py-12">
                        <i class="fas fa-chalkboard-teacher text-gray-400 text-3xl md:text-4xl mb-4"></i>
                        <p class="text-gray-500 text-sm md:text-base">Nenhum professor ativo encontrado</p>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Alertas -->
        <x-card data-card-key="analytics-table-alertas">
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2 cursor-move" data-drag-handle
                        draggable="true"></i>
                    <span class="hidden sm:inline">Alertas de Frequência</span>
                    <span class="sm:hidden">Alertas</span>
                </div>
            </x-slot>

            <x-slot name="subtitle">
                <span class="hidden sm:inline">Alunos com baixa frequência</span>
                <span class="sm:hidden">Baixa frequência</span>
            </x-slot>

            <x-slot name="headerActions">
                <a href="{{ route('alunos.index') }}"
                    class="inline-flex items-center px-2 md:px-3 py-1 md:py-1.5 border border-transparent text-xs md:text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 transition-colors">
                    <i class="fas fa-eye mr-1"></i>
                    <span class="hidden sm:inline">Ver todos</span>
                    <span class="sm:hidden">Ver</span>
                </a>
            </x-slot>

            <div class="overflow-x-auto" id="alertasFrequenciaContainer">
                @if (isset($dadosAnaliticos['alertasBaixaFrequencia']) && $dadosAnaliticos['alertasBaixaFrequencia']->count() > 0)
                    <!-- Mobile Cards (visible on small screens) -->
                    <div class="block sm:hidden space-y-3" id="alertasFrequenciaMobile">
                        @foreach ($dadosAnaliticos['alertasBaixaFrequencia']->take(3) as $alerta)
                            <div class="bg-red-50 p-3 rounded-lg border border-red-200">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-medium text-gray-900 text-sm">{{ $alerta->nome }}</h4>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ number_format($alerta->frequencia ?? 0, 1) }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 mb-1">
                                    {{ $alerta['grupo_id'] ?? 'Turma não informada' }}
                                </p>
                                <p class="text-xs text-red-600">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Frequência baixa
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <!-- Desktop Table (hidden on small screens) -->
                    <table class="hidden sm:table min-w-full divide-y divide-gray-200" id="alertasFrequenciaTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aluno
                                </th>
                                <th
                                    class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                    Turma
                                </th>
                                <th
                                    class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Frequência
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($dadosAnaliticos['alertasBaixaFrequencia']->take(5) as $alerta)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 md:h-10 md:w-10">
                                                <div
                                                    class="h-8 w-8 md:h-10 md:w-10 rounded-full bg-red-100 flex items-center justify-center">
                                                    <i
                                                        class="fas fa-user-graduate text-red-600 text-xs md:text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3 md:ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $alerta->nome ?? 'Sem nome' }}
                                                </div>
                                                <div class="text-xs md:text-sm text-gray-500 md:hidden">
                                                    {{ $alerta->turma->nome ?? 'Turma não informada' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="px-3 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
                                        {{ $alerta->turma->nome ?? 'Não informada' }}
                                    </td>
                                    <td class="px-3 md:px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ number_format($alerta->frequencia ?? 0, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-8 md:py-12">
                        <i class="fas fa-check-circle text-green-400 text-3xl md:text-4xl mb-4"></i>
                        <p class="text-gray-500 text-sm md:text-base">Nenhum alerta de frequência</p>
                        <p class="text-gray-400 text-xs md:text-sm mt-1">Todas as frequências estão normais</p>
                    </div>
                @endif
            </div>
        </x-card>
    </div>

    <!-- Recent Activity -->
    <div class="flex items-center justify-end mb-2">

    </div>
    <div id="analyticsRecentGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <!-- Atividades Recentes -->
        <x-card data-card-key="analytics-recentes-atividades">
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-clock text-indigo-600 mr-2 cursor-move" data-drag-handle draggable="true"></i>
                    <span class="hidden sm:inline">Atividades Recentes</span>
                    <span class="sm:hidden">Atividades</span>
                </div>
            </x-slot>

            <x-slot name="subtitle">
                <span class="hidden sm:inline">Últimas 24 horas</span>
                <span class="sm:hidden">24h</span>
            </x-slot>

            <x-slot name="headerActions">
                <a href="#"
                    class="inline-flex items-center px-2 md:px-3 py-1 md:py-1.5 border border-transparent text-xs md:text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors">
                    <i class="fas fa-history mr-1"></i>
                    <span class="hidden sm:inline">Ver todas</span>
                    <span class="sm:hidden">Ver</span>
                </a>
            </x-slot>

            <div class="space-y-3 md:space-y-4">
                @if (isset($atividadesRecentes) && count($atividadesRecentes) > 0)
                    @foreach ($atividadesRecentes as $atividade)
                        <div
                            class="flex items-start space-x-3 p-3 md:p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <i
                                        class="fas fa-{{ $atividade['icon'] ?? 'bell' }} text-indigo-600 text-xs md:text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $atividade['titulo'] ?? 'Atividade' }}
                                </p>
                                <p class="text-xs md:text-sm text-gray-600 mt-1">
                                    {{ $atividade['descricao'] ?? 'Descrição da atividade' }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $atividade['tempo'] ?? 'Agora' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-8 md:py-12">
                        <i class="fas fa-clock text-gray-400 text-3xl md:text-4xl mb-4"></i>
                        <p class="text-gray-500 text-sm md:text-base">Nenhuma atividade recente</p>
                        <p class="text-gray-400 text-xs md:text-sm mt-1">As atividades aparecerão aqui</p>
                    </div>
                @endif

            </div>
        </x-card>

        <!-- Estatísticas Rápidas -->
        <x-card data-card-key="analytics-recentes-estatisticas">
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-chart-pie text-orange-600 mr-2 cursor-move" data-drag-handle
                        draggable="true"></i>
                    <span class="hidden sm:inline">Estatísticas Rápidas</span>
                    <span class="sm:hidden">Estatísticas</span>
                </div>
            </x-slot>

            <x-slot name="subtitle">
                <span class="hidden sm:inline">Resumo do sistema</span>
                <span class="sm:hidden">Resumo</span>
            </x-slot>

            <div class="space-y-4 md:space-y-6">
                <!-- Total de Alunos -->
                <div class="flex items-center justify-between p-3 md:p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center">
                        <div
                            class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-user-graduate text-blue-600 text-xs md:text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-blue-900">Total de Alunos</p>
                            <p class="text-xs text-blue-700">Ativos no sistema</p>
                        </div>
                    </div>
                    <span class="text-xl md:text-2xl font-bold text-blue-900">{{ $totalAlunos ?? 0 }}</span>
                </div>

                <!-- Total de Professores -->
                <div class="flex items-center justify-between p-3 md:p-4 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <div
                            class="w-8 h-8 md:w-10 md:h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-chalkboard-teacher text-green-600 text-xs md:text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-green-900">Total de Professores</p>
                            <p class="text-xs text-green-700">Ativos no sistema</p>
                        </div>
                    </div>
                    <span class="text-xl md:text-2xl font-bold text-green-900">{{ $totalProfessores ?? 0 }}</span>
                </div>

                <!-- Total de Salas -->
                <div class="flex items-center justify-between p-3 md:p-4 bg-purple-50 rounded-lg">
                    <div class="flex items-center">
                        <div
                            class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-door-open text-purple-600 text-xs md:text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-purple-900">Total de Salas</p>
                            <p class="text-xs text-purple-700">Disponíveis</p>
                        </div>
                    </div>
                    <span class="text-xl md:text-2xl font-bold text-purple-900">{{ $totalSalas ?? 0 }}</span>
                </div>

                <!-- Taxa de Ocupação -->
                <div class="flex items-center justify-between p-3 md:p-4 bg-orange-50 rounded-lg">
                    <div class="flex items-center">
                        <div
                            class="w-8 h-8 md:w-10 md:h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-percentage text-orange-600 text-xs md:text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-orange-900">Taxa de Ocupação</p>
                            <p class="text-xs text-orange-700">Salas em uso</p>
                        </div>
                    </div>
                    <span class="text-xl md:text-2xl font-bold text-orange-900">{{ $taxaOcupacao ?? 0 }}%</span>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Recent Activity -->
    <div class="flex items-center justify-end mb-2 mt-6">

    </div>
    <div id="analyticsExtraGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Últimos Alunos -->
        <x-card data-card-key="analytics-extra-ultimos-alunos">
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-user-plus text-green-600 mr-2 cursor-move" data-drag-handle draggable="true"></i>
                    Últimos Alunos Cadastrados
                </div>
            </x-slot>

            <x-slot name="subtitle">
                Cadastros recentes
            </x-slot>

            <div class="space-y-4">
                @forelse($ultimosAlunos as $aluno)
                    <div
                        class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3 gap-2">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-graduate text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $aluno->nome }}</p>
                                <p class="text-sm text-gray-600">{{ $aluno->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Novo
                        </span>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i class="fas fa-user-plus text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-600">Nenhum aluno cadastrado recentemente</p>
                    </div>
                @endforelse
            </div>
        </x-card>

        <!-- Presenças de Hoje -->
        <x-card data-card-key="analytics-extra-presencas-hoje">
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-calendar-check text-blue-600 mr-2 cursor-move" data-drag-handle
                        draggable="true"></i>
                    Presenças de Hoje
                </div>
            </x-slot>

            <x-slot name="subtitle">
                Registros do dia
            </x-slot>

            <div class="space-y-4">
                @forelse($presencasHojeDetalhes as $presenca)
                    <div
                        class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3 gap-2">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-check text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $presenca->aluno->nome }}</p>
                                <p class="text-sm text-gray-600">{{ $presenca->created_at->format('H:i') }}</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Presente
                        </span>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-600">Nenhuma presença registrada hoje</p>
                    </div>
                @endforelse
            </div>
        </x-card>
    </div>
</x-card>
