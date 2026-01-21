@if (function_exists('moduleEnabled')
        ? moduleEnabled('financeiro_module')
        : config('features.modules.financeiro_module') ?? true)
    <!-- Financeiro: Cards personalizáveis -->
    <x-card class="mb-6 md:mb-8">
        <div class="flex flex-col items-start md:flex-row justify-between">
            <div>
                <h2 class="text-lg md:text-xl font-semibold text-gray-900">Financeiro</h2>
                <p class="text-sm text-gray-600">Receitas e despesas do mês selecionado</p>
            </div>
            <div class="flex flex-col items-center gap-3 md:flex-row md:items-start">
                <div>
                    <label for="financeMonth" class="text-sm text-gray-700">Mês:</label>
                    <input id="financeMonth" type="month"
                        class="text-sm border rounded-md px-2 py-1 bg-white text-gray-900"
                        value="{{ now()->format('Y-m') }}"
                        title="Selecione o mês para atualizar os gráficos do Financeiro">
                </div>
                <button id="btnPersonalizarCards" type="button"
                    class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                    <i class="fas fa-sliders-h mr-2"></i>
                    Personalizar cards
                </button>
            </div>
        </div>

        <div id="financeCardsGrid" class="mt-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4"
            data-storage-key="dashboard.finance.cards">
            <!-- Card: Receitas -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]"
                data-card-key="finance-receitas">
                <div class="p-4 md:p-5 bg-gradient-to-r from-emerald-500 to-emerald-600 min-h-[88px] flex items-center max-h-[112px]"
                    data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-emerald-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-coins text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-emerald-100 truncate">
                                Receitas (mês)
                                <span class="ml-2 inline-block" data-total-for="receitas-total">- Total R$
                                    {{ number_format(($receitasTotalMesCents ?? 0) / 100, 2, ',', '.') }}</span>
                            </h3>
                            <div class="mt-1 flex items-baseline space-x-4">
                                <div>
                                    <p class="text-xl md:text-2xl font-semibold text-white"
                                        data-total-for="receitas-recebido">R$
                                        {{ number_format(($receitasRecebidasMesCents ?? 0) / 100, 2, ',', '.') }}</p>
                                    <p
                                        class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-emerald-100 hidden sm:block">
                                        recebidas</p>
                                </div>
                                <div class="border-l border-emerald-200 pl-4">
                                    <p class="text-xl md:text-2xl font-semibold text-white"
                                        data-total-for="receitas-pendentes">R$
                                        {{ number_format(($receitasPendentesMesCents ?? 0) / 100, 2, ',', '.') }}</p>
                                    <p
                                        class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-emerald-100 hidden sm:block">
                                        pendentes</p>
                                </div>
                            </div>
                        </div>
                        <div class="ml-2 hidden sm:flex items-center text-emerald-50/90">
                            <label for="receitasBase" class="text-[11px] mr-1">Base:</label>
                            <select id="receitasBase"
                                class="text-[11px] bg-emerald-600/10 border border-emerald-200/40 text-emerald-50 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-emerald-200/60">
                                <option value="due_date" selected>due_date</option>
                                <option value="paid_at">paid_at</option>
                            </select>
                        </div>
                        <button type="button" class="ml-2 text-emerald-50/80 hover:text-white"
                            title="KPIs Total/Pendentes podem alternar a base (due_date | paid_at) no seletor. Recebido e sparkline usam paid_at.">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-emerald-50 hover:text-white"
                            data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">

                    <div class="flex-1 relative">
                        <canvas id="sparkReceitas" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkReceitas">Sem dados no mês
                        selecionado</p>
                </div>
            </div>

            <!-- Card: Recebimentos pendentes -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[360px]"
                data-card-key="finance-recebimentos-pendentes">
                <div class="p-4 md:p-5 bg-gradient-to-r from-indigo-500 to-indigo-600 min-h-[88px] flex items-center"
                    data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-indigo-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-hand-holding-usd text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-indigo-100 truncate">Receitas pendentes</h3>
                            <div class="mt-1 flex items-baseline space-x-4">
                                <div>
                                    <p class="text-sm md:text-base font-semibold text-white">A vencer: <span
                                            data-total-for="pendentes-avencer">R$
                                            {{ number_format(($totalPendentesAVencerCents ?? 0) / 100, 2, ',', '.') }}</span>
                                    </p>
                                </div>
                                <div class="border-l border-indigo-200 pl-4">
                                    <p class="text-sm md:text-base font-semibold text-white">Vencidos: <span
                                            data-total-for="pendentes-vencidos">R$
                                            {{ number_format(($totalPendentesVencidasCents ?? 0) / 100, 2, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.recebimentos.index') }}"
                            class="text-xs md:text-sm text-indigo-50 hover:text-white underline underline-offset-2 mr-2">Ver
                            todos</a>
                        <button
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-indigo-50 hover:text-white"
                            data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 min-h-0">
                        <div class="min-h-0 flex flex-col">
                            <p class="text-xs font-medium text-gray-700 mb-1">A vencer</p>
                            <div id="listaPendentesAVencer" class="flex-1 overflow-auto">
                                @forelse(($pendentesAVencer ?? []) as $inv)
                                    <div
                                        class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                                        <div class="text-xs text-gray-700 flex items-center">
                                            <span
                                                class="inline-block w-14 text-gray-500">{{ optional($inv->due_date)->format('d/m') }}</span>

                                            @php $__payer = trim(($inv->payer_nome ?? '').' '.($inv->payer_sobrenome ?? '')); @endphp
                                            @if (!empty($__payer))
                                                <span class="ml-2 text-gray-500">— {{ $__payer }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-semibold text-gray-900">R$
                                            {{ number_format(($inv->total_cents ?? 0) / 100, 2, ',', '.') }}</div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-500">Nada a vencer</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="min-h-0 flex flex-col">
                            <p class="text-xs font-medium text-gray-700 mb-1">Vencidos</p>
                            <div id="listaPendentesVencidas" class="flex-1 overflow-auto">
                                @forelse(($pendentesVencidas ?? []) as $inv)
                                    <div
                                        class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                                        <div class="text-xs text-gray-700">
                                            <span
                                                class="inline-block w-14 text-gray-500">{{ optional($inv->due_date)->format('d/m') }}</span>
                                            @php $__payer = trim(($inv->payer_nome ?? '').' '.($inv->payer_sobrenome ?? '')); @endphp
                                            @if (!empty($__payer))
                                                <span class="ml-2 text-gray-500">— {{ $__payer }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-semibold text-gray-900">R$
                                            {{ number_format(($inv->total_cents ?? 0) / 100, 2, ',', '.') }}</div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-500">Sem vencidos</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Despesas pendentes -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[360px]"
                data-card-key="finance-despesas-pendentes">
                <div class="p-4 md:p-5 bg-gradient-to-r from-rose-500 to-rose-600 min-h-[88px] flex items-center"
                    data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-rose-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-wallet text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-rose-100 truncate">Despesas pendentes</h3>
                            <div class="mt-1 flex items-baseline space-x-4">
                                <div>
                                    <p class="text-sm md:text-base font-semibold text-white">A vencer: <span
                                            data-total-for="despesas-pend-avencer">R$
                                            {{ number_format($totalDespesasPendAVencer ?? 0, 2, ',', '.') }}</span>
                                    </p>
                                </div>
                                <div class="border-l border-rose-200 pl-4">
                                    <p class="text-sm md:text-base font-semibold text-white">Vencidas: <span
                                            data-total-for="despesas-pend-vencidas">R$
                                            {{ number_format($totalDespesasPendVencidas ?? 0, 2, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.despesas.index') }}"
                            class="text-xs md:text-sm text-rose-50 hover:text-white underline underline-offset-2 mr-2">Ver
                            todos</a>
                        <button
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-rose-50 hover:text-white"
                            data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 min-h-0">
                        <div class="min-h-0 flex flex-col">
                            <p class="text-xs font-medium text-gray-700 mb-1">A vencer</p>
                            <div id="listaDespesasPendAVencer" class="flex-1 overflow-auto">
                                @forelse(($despesasPendAVencer ?? []) as $d)
                                    <div
                                        class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                                        <div class="text-xs text-gray-700">
                                            <span
                                                class="inline-block w-14 text-gray-500">{{ optional($d->data)->format('d/m') }}</span>
                                            <span class="ml-2">{{ $d->descricao }}</span>
                                            @if (!empty($d->categoria))
                                                <span class="ml-2 text-gray-500">— {{ $d->categoria }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-semibold text-gray-900">R$
                                            {{ number_format($d->valor ?? 0, 2, ',', '.') }}</div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-500">Nada a vencer</p>
                                @endforelse
                            </div>
                        </div>
                        <div class="min-h-0 flex flex-col">
                            <p class="text-xs font-medium text-gray-700 mb-1">Vencidas</p>
                            <div id="listaDespesasPendVencidas" class="flex-1 overflow-auto">
                                @forelse(($despesasPendVencidas ?? []) as $d)
                                    <div
                                        class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                                        <div class="text-xs text-gray-700 flex items-center">
                                            <span
                                                class="inline-block w-14 text-gray-500">{{ optional($d->data)->format('d/m') }}</span>
                                            <span class="ml-2">{{ $d->descricao }}</span>
                                            @if (!empty($d->categoria))
                                                <span class="ml-2 text-gray-500">— {{ $d->categoria }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-semibold text-gray-900">R$
                                            {{ number_format($d->valor ?? 0, 2, ',', '.') }}</div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-500">Sem vencidas</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Despesas -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]"
                data-card-key="finance-despesas">
                <div class="p-4 md:p-5 bg-gradient-to-r from-rose-500 to-rose-600 min-h-[88px] flex items-center"
                    data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-rose-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-file-invoice-dollar text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-rose-100 truncate">
                                Despesas (mês)
                                <span class="ml-2 inline-block" data-total-for="despesas-total">Total - R$
                                    {{ number_format(($despesaMensalLiquidadas ?? 0) + ($despesaMensalPendentes ?? 0), 2, ',', '.') }}</span>
                            </h3>
                            <div class="mt-1 flex items-baseline space-x-4">
                                <div>
                                    <p class="text-xl md:text-2xl font-semibold text-white" data-total-for="despesas">
                                        R$ {{ number_format($despesaMensalLiquidadas ?? 0, 2, ',', '.') }}</p>
                                    <p
                                        class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-rose-100 hidden sm:block">
                                        liquidadas</p>
                                </div>
                                <div class="border-l border-rose-200 pl-4">
                                    <p class="text-xl md:text-2xl font-semibold text-white"
                                        data-total-for="despesas-pendentes">R$
                                        {{ number_format($despesaMensalPendentes ?? 0, 2, ',', '.') }}</p>
                                    <p
                                        class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-rose-100 hidden sm:block">
                                        pendentes</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="ml-2 text-rose-50/80 hover:text-white"
                            title="Despesas diárias (Despesa.valor) somadas por data.">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-rose-50 hover:text-white"
                            data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="flex-1 relative">
                        <canvas id="sparkDespesas" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkDespesas">Sem dados no mês
                        selecionado</p>
                </div>
            </div>

            <!-- Card: Inadimplência -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]"
                data-card-key="finance-inadimplencia">
                <div class="p-4 md:p-5 bg-gradient-to-r from-orange-500 to-orange-600 min-h-[88px] flex items-center"
                    data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-orange-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-exclamation-circle text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-orange-100 truncate">Inadimplência (mês)
                            </h3>
                            <div class="mt-1 flex items-baseline space-x-4">
                                <div>
                                    <p class="text-xl md:text-2xl font-semibold text-white">
                                        {{ $taxaInadimplenciaPercentual ?? 0 }}%</p>
                                    <p
                                        class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-orange-100 hidden sm:block">
                                        taxa</p>
                                </div>
                                <div class="border-l border-orange-200 pl-4">
                                    <p class="text-xl md:text-2xl font-semibold text-white">R$
                                        {{ number_format(($valorInadimplenciaCents ?? 0) / 100, 2, ',', '.') }}</p>
                                    <p
                                        class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-orange-100 hidden sm:block">
                                        em aberto</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="ml-2 text-orange-50/80 hover:text-white"
                            title="Faturas vencidas não pagas por dia (Invoice.status != paid/canceled).">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-orange-50 hover:text-white"
                            data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="flex-1 relative">
                        <canvas id="sparkInadimplencia" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkInadimplencia">Sem dados no
                        mês selecionado</p>
                </div>
            </div>

            <!-- Card: Tickets Abertos -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]"
                data-card-key="finance-tickets">
                <div class="p-4 md:p-5 bg-gradient-to-r from-indigo-500 to-indigo-600 min-h-[88px] flex items-center"
                    data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-indigo-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-life-ring text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-indigo-100 truncate">Tickets abertos</h3>
                            <div class="mt-1 flex items-baseline">
                                <p class="text-xl md:text-2xl font-semibold text-white" data-total-for="tickets">
                                    {{ $ticketsAbertosCount ?? 0 }}</p>
                                <p class="ml-2 text-xs md:text-sm font-medium text-indigo-100 hidden sm:block">suporte
                                </p>
                            </div>
                        </div>
                        <button type="button" class="ml-2 text-indigo-50/80 hover:text-white"
                            title="Conversas de suporte ativas criadas por dia (Conversa.tipo = suporte, ativo = true), filtradas por escola.">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-indigo-50 hover:text-white"
                            data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="flex-1 relative">
                        <canvas id="sparkTickets" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkTickets">Sem dados no mês
                        selecionado</p>
                </div>
            </div>

            <!-- Card: MRR -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]"
                data-card-key="finance-mrr">
                <div class="p-4 md:p-5 bg-gradient-to-r from-teal-500 to-teal-600 min-h-[88px] flex items-center"
                    data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-teal-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-sync-alt text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-teal-100 truncate">MRR</h3>
                            <div class="mt-1 flex items-baseline">
                                <p class="text-xl md:text-2xl font-semibold text-white">R$
                                    {{ number_format(($mrrCents ?? 0) / 100, 2, ',', '.') }}</p>
                                <p class="ml-2 text-xs md:text-sm font-medium text-teal-100 hidden sm:block">recorrente
                                </p>
                            </div>
                        </div>
                        <button type="button" class="ml-2 text-teal-50/80 hover:text-white"
                            title="Receita recorrente mensal de assinaturas ativas (Subscription.amount_cents). Este gráfico não é vinculado ao mês selecionado.">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-teal-50 hover:text-white"
                            data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="flex-1 relative">
                        <canvas id="sparkMrr" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkMrr">Sem dados no mês
                        selecionado</p>
                </div>
            </div>

            <!-- Card: Método Predominante -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]"
                data-card-key="finance-metodo">
                <div class="p-4 md:p-5 bg-gradient-to-r from-cyan-500 to-cyan-600 min-h-[88px] flex items-center"
                    data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-cyan-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-credit-card text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-cyan-100 truncate">Método predominante</h3>
                            <div class="mt-1 flex items-baseline">
                                <p class="text-xl md:text-2xl font-semibold text-white">
                                    {{ $metodoPredominanteLabel ?? '—' }}</p>
                            </div>
                        </div>
                        <button type="button" class="ml-2 text-cyan-50/80 hover:text-white"
                            title="Tendência de receitas líquidas; método predominante do mês via Payments.method.">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button
                            class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-cyan-50 hover:text-white"
                            data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="flex-1 relative">
                        <canvas id="sparkMetodo" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkMetodo">Sem dados no mês
                        selecionado</p>
                </div>
            </div>
            <!-- Card: Espaço em branco -->
            <div class="bg-white rounded-lg border border-dashed border-gray-300 overflow-hidden group flex flex-col h-[200px]"
                data-card-key="finance-blank">
                <div class="px-4 py-2 bg-gray-50 flex items-center justify-between" data-card-header draggable="true">
                    <div class="flex items-center text-gray-400 text-xs">
                        <i class="fas fa-border-none mr-2"></i>
                        <span>Espaço em branco</span>
                    </div>
                    <button
                        class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-gray-600"
                        data-action="hide-card" title="Ocultar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="flex-1 bg-white"></div>
            </div>
        </div>
    </x-card>
@endif
