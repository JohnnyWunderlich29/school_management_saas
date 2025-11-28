@php
    $alunosSource = isset($alunos) ? $alunos : (isset($responsavel) ? $responsavel->alunos : collect());
    $responsavelSource = isset($responsavel) ? $responsavel : collect();
    $studentsForBilling = $alunosSource
        ->map(function ($a) {
            return [
                'id' => $a->id,
                'nome' => $a->nome,
                'sobrenome' => $a->sobrenome,
            ];
        })
        ->values()
        ->toArray();
    $schoolIdForBilling = isset($schoolId)
        ? $schoolId
        : optional(Auth::user())->escola_id ??
            (optional(Auth::user())->school_id ??
                (session('escola_atual') ?? (isset($responsavel) ? $responsavel->escola_id : null)));
@endphp
<style>
    [x-cloak] {
        display: none !important
    }
</style>
<div id="guardianBillingRoot" x-data="guardianBilling({ schoolId: {{ json_encode($schoolIdForBilling) }}, responsavelId: {{ json_encode($responsavel->id ?? null) }} })" x-init="init()">
    <!-- Mensalidade e Cobranças (Modal) -->
    <div x-show="$store.billing && $store.billing.createSubscriptionOpen" x-cloak
        @keydown.escape.window="closeCreateSubscription()"
        @click.self="$store.billing && ($store.billing.createSubscriptionOpen = false)"
        x-effect="$store.billing && $store.billing.createSubscriptionOpen ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden')"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
        style="margin-top:0 !important">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl my-6 overflow-y-auto"
            style="max-height: calc(100vh - 3rem)">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h5 class="font-semibold">Mensalidade e Cobranças</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700"
                    @click="$store.billing && ($store.billing.createSubscriptionOpen = false)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4">
                <div class="p-4 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Descrição</label>
                                    <input type="text" class="mt-1 w-full border rounded px-2 py-2 text-sm"
                                        x-model="form.description" placeholder="Opcional">
                                    <p class="text-xs text-gray-500 mt-1">Padrão: <span
                                            x-text="defaultDescription()"></span>
                                    </p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Dependente</label>
                                <select class="mt-1 w-full border rounded px-2 py-2 text-sm" name="student_id"
                                    id="student_id" x-model="form.studentId">
                                    <option value="">Selecione...</option>
                                    @foreach ($alunosSource as $s)
                                        <option value="{{ $s->id }}">
                                            {{ trim(($s->nome ?? '') . ' ' . ($s->sobrenome ?? '')) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Período</label>
                                    <select class="mt-1 w-full border rounded px-2 py-2 text-sm"
                                        x-model="form.periodicity" @change="filterPlanOptions()">
                                        <option value="monthly">Mensal</option>
                                        <option value="bimonthly">Bimestral</option>
                                        <option value="annual">Anual</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Dia do Vencimento</label>
                                    <input type="number" min="1" max="31"
                                        class="mt-1 w-full border rounded px-2 py-2 text-sm"
                                        x-bind:class="(!form.dayOfMonth || parseInt(form.dayOfMonth) < 1 || parseInt(form
                                            .dayOfMonth) > 31) ?
                                        'border-gray-500' : ''"
                                        x-model="form.dayOfMonth" placeholder="Ex.: 10">
                                    <p class="text-xs text-gray-500 mt-1"
                                        x-show="!form.dayOfMonth || parseInt(form.dayOfMonth) < 1 || parseInt(form.dayOfMonth) > 31">

                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 border rounded p-3">
                            <h4 class="font-semibold text-gray-800 mb-2">Resumo</h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total dos Itens</span>
                                    <span x-text="centsToCurrency(baseTotalCents())"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Desconto Aplicável</span>
                                    <span
                                        x-text="discountAppliesOn(currentDueDate()) ? (Math.round(discountPercent()) + '%') : '—'"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Desconto no pagamento adiantado</span>
                                    <span
                                        x-text="(form.earlyDiscountActive && form.earlyDiscountValue && form.earlyDiscountDays) ? (Math.round(earlyDiscountPercent()) + '% até ' + formatDateBr(earlyLimitDate(currentDueDate(), form.earlyDiscountDays))) : '—'"></span>
                                </div>
                                <div class="flex justify-between font-medium">
                                    <span class="text-gray-800">Total da Fatura</span>
                                    <span x-text="centsToCurrency(netTotalCentsFor(currentDueDate()))"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-6 gap-4">
                        <div class="col-span-3">
                            @if (isset($billingPlans) && count($billingPlans) > 0)
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Plano de Cobrança</label>
                                    <select class="mt-1 w-full border rounded px-2 py-2 text-sm" name="billing_plan_id"
                                        id="billing_plan_id" x-model="form.billingPlanId">
                                        <option value="">Selecione...</option>
                                        @foreach ($billingPlans ?? [] as $p)
                                            <option value="{{ $p->id }}" data-amount="{{ $p->amount_cents }}"
                                                data-day="{{ $p->day_of_month }}"
                                                data-periodicity="{{ $p->periodicity }}">
                                                {{ $p->name }} —
                                                {{ number_format($p->amount_cents / 100, 2, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Método de Pagamento</label>
                                <select class="mt-1 w-full border rounded px-2 py-2 text-sm" name="charge_method_id"
                                    id="charge_method_id" x-model="form.chargeMethodId">
                                    <option value="">Selecione...</option>
                                    @foreach ($chargeMethods ?? [] as $m)
                                        @if ($m->active)
                                            <option value="{{ $m->id }}">{{ strtoupper($m->method) }}
                                                ({{ $m->gateway_alias }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-span-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Data de Início</label>
                                    <input type="date" class="mt-1 w-full border rounded px-2 py-2 text-sm"
                                        x-model="form.startAt" placeholder="Opcional">
                                    <p class="text-xs text-gray-500 mt-1">Vazio: usa a data de criação.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Data de Fim</label>
                                    <input type="date" class="mt-1 w-full border rounded px-2 py-2 text-sm"
                                        x-model="form.endAt" placeholder="Opcional">
                                    <p class="text-xs text-gray-500 mt-1">Opcional para cessar cobranças automáticas.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-3">
                            <h4 class="font-semibold text-gray-800">Desconto</h4>
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                                <div class="col-span-1">
                                    <label class="block text-sm font-medium text-gray-600">Tipo</label>
                                    <button type="button" class="mt-1 border rounded px-3 py-2 text-sm"
                                        :class="form.discountType === 'percent' ? 'bg-blue-50 border-blue-300 text-blue-700' :
                                            'bg-green-50 border-green-300 text-green-700'"
                                        @click="toggleDiscountType()"
                                        x-text="form.discountType==='percent' ? '%' : 'R$'"></button>
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-600">Valor do Desconto</label>
                                    <input type="text" class="mt-1 w-full border rounded px-2 py-2 text-sm"
                                        x-model="form.discountValue"
                                        x-bind:placeholder="form.discountType === 'percent' ? 'Ex.: 10' : 'Ex.: 50,00'"
                                        @input="onDiscountValueInput($event)">
                                    <p class="text-xs text-gray-500 mt-1"
                                        x-text="form.discountType==='percent' ? 'Percentual entre 0 e 100.' : 'Informe em reais; convertido para centavos automaticamente.'">
                                    </p>
                                </div>
                                <div class="col-span-3">
                                    <label class="block text-sm font-medium text-gray-600">Vencimento do
                                        Desconto</label>
                                    <input type="date" class="mt-1 w-full border rounded px-2 py-2 text-sm"
                                        x-model="form.discountExpiresAt">
                                </div>
                            </div>
                        </div>
                        <div class="col-span-3">
                            <div>
                                <h5 class="font-semibold text-gray-800">Desconto no pagamento adiantado</h5>
                                <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                                    <div class="col-span-2">
                                        <label class="block text-sm col-span-2 font-medium text-gray-600">Valor
                                            (%)</label>
                                        <input type="text" class="mt-1 w-full border rounded px-2 py-2 text-sm"
                                            x-model="form.earlyDiscountValue" placeholder="Ex.: 5"
                                            @input="onPercentInput('earlyDiscountValue', $event)">
                                    </div>
                                    <div class="col-span-3">
                                        <label class="block text-sm font-medium text-gray-600">Dias de
                                            antecipação</label>
                                        <input type="number" min="1" max="30"
                                            class="mt-1 w-full border rounded px-2 py-2 text-sm"
                                            x-model="form.earlyDiscountDays" placeholder="Ex.: 5"
                                            @input="onDaysInput('earlyDiscountDays', $event)">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-sm font-medium text-gray-600">Até o dia</label>
                                        <input type="text"
                                            class="mt-1 w-full border rounded px-2 py-2 text-sm bg-gray-100"
                                            :value="(earlyLimitDate(currentDueDate(), form.earlyDiscountDays) || '').split('-')[
                                                2]"
                                            readonly>
                                    </div>
                                    <div class="col-span-2 flex items-center">
                                        <input id="earlyDiscountActive" type="checkbox" class="mr-2"
                                            x-model="form.earlyDiscountActive">
                                        <label for="earlyDiscountActive"
                                            class="text-sm font-medium text-gray-600">Ativo?</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-6">
                            <h4 class="font-semibold text-gray-800 mb-2">Itens da Fatura</h4>
                            <div class="space-y-2">
                                <template x-for="(it, idx) in form.items" :key="idx">
                                    <div class="grid grid-cols-8 items-center gap-2">
                                        <input type="text" class="col-span-4 border rounded px-2 py-2 text-sm"
                                            placeholder="Descrição" x-model="it.name">
                                        <input type="number" min="1" step="1" inputmode="numeric"
                                            pattern="\\d*"
                                            class="col-span-1 border rounded px-2 py-2 text-sm text-right"
                                            placeholder="Qtd" x-model="it.qty"
                                            @input="it.qty = clampQty($event.target.value)">
                                        <input type="text" class="col-span-1 border rounded px-2 py-2 text-sm"
                                            placeholder="R$ 0,00" required x-model="it.amount"
                                            @input="onCurrencyInput($event, it, 'amount')"
                                            title="Informe o valor em reais; será convertido para centavos.">
                                        <div class="flex col-span-2 gap-2">
                                            <div class="text-left text-xs text-gray-500"
                                                x-text="itemQtyFormula(it)"></div>
                                            <button type="button" class="text-red-600 text-sm"
                                                @click="removeItem(idx)" x-show="form.items.length>1">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>


                                    </div>
                                </template>
                                <button type="button" class="mt-2 text-sm text-blue-600 hover:text-blue-800"
                                    @click="addItem()">
                                    <i class="fas fa-plus mr-1"></i>Adicionar item
                                </button>
                                <p class="text-xs text-gray-500 mt-1">Valores em reais; convertidos automaticamente
                                    para centavos. O campo de valor é obrigatório.</p>
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                                <div class="flex items-center">
                                    <input id="chargeActive" type="checkbox" class="mr-2"
                                        x-model="form.chargeActive">
                                    <label for="chargeActive" class="text-sm font-medium text-gray-600">Assinatura
                                        Ativa?</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-2 justify-end">
                        <x-button type="button" color="success" @click="saveSubscription()"
                            x-bind:disabled="loading"
                            x-bind:title="loading ? 'Salvando assinatura...' : 'Salvar assinatura'">
                            <i class="fas fa-save mr-1"></i>Salvar Assinatura Mensal
                        </x-button>
                    </div>
                    <div class="mt-3">
                        <p class="text-xs text-gray-500" x-show="!form.studentId">Aluno é opcional; você pode
                            prosseguir sem
                            selecionar.</p>
                        <p class="text-sm text-green-600" x-text="message" x-show="message"></p>
                        <p class="text-sm text-yellow-600 flex items-center" x-show="processingInvoices.length > 0">
                            <i class="fas fa-spinner fa-spin mr-1"></i>
                            <span
                                x-text="'Processando geração de boletos para ' + processingInvoices.length + ' fatura(s)...'"></span>
                        </p>
                        <p class="text-sm text-yellow-600" x-text="info"
                            x-show="info && !processingInvoices.length"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>
        <x-card>
            <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
                <div>
                    <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Recorrências</h1>
                    <p class="mt-1 text-sm text-gray-600">Gerenciamento de Recorrências</p>
                </div>
                <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
                    <!-- Assinaturas -->

                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-gray-800"></h4>
                        <div class="flex items-center gap-2">
                            <x-button type="button" color="success"
                                @click="$store.billing && ($store.billing.createSubscriptionOpen = true)">
                                <i class="fas fa-plus mr-1"></i>Nova Recorrência
                            </x-button>
                            <x-button type="button" color="primary" @click="openSummary()"
                                x-bind:disabled="selectedSubscriptions.length === 0">
                                <i class="fas fa-file-invoice mr-1"></i>Gerar Fatura
                                <span x-show="selectedSubscriptions.length>0" class="ml-1">(<span
                                        x-text="selectedSubscriptions.length"></span>)</span>
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-xs text-red-600 mt-1" x-show="hasEmptyChargeDayInSelection()">
                Algumas assinaturas selecionadas estão sem “Dia de cobrança”.
                Defina o “Dia do vencimento” acima ou edite a assinatura.
            </p>
            <template x-if="subscriptions.length === 0">
                <p class="text-sm text-gray-500 italic">Nenhuma assinatura encontrada.</p>
            </template>
            <div class="overflow-x-auto" x-show="subscriptions.length > 0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" x-model="selectAll"
                                    @change="toggleSelectAll($event.target.checked)">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
                                @click="sortSubscriptionsBy('description')">
                                <span class="inline-flex items-center gap-1">
                                    <span>Descrição</span>
                                    <i :class="subscriptionsSortKey === 'description' ? (subscriptionsSortDir === 'asc' ?
                                        'fas fa-sort-up' : 'fas fa-sort-down') : 'fas fa-sort'"
                                        class="text-gray-400"></i>
                                </span>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
                                @click="sortSubscriptionsBy('status')">
                                <span class="inline-flex items-center gap-1">
                                    <span>Status</span>
                                    <i :class="subscriptionsSortKey === 'status' ? (subscriptionsSortDir === 'asc' ?
                                        'fas fa-sort-up' : 'fas fa-sort-down') : 'fas fa-sort'"
                                        class="text-gray-400"></i>
                                </span>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
                                @click="sortSubscriptionsBy('day_of_month')">
                                <span class="inline-flex items-center gap-1">
                                    <span>Dia Cobrança</span>
                                    <i :class="subscriptionsSortKey === 'day_of_month' ? (subscriptionsSortDir === 'asc' ?
                                        'fas fa-sort-up' : 'fas fa-sort-down') : 'fas fa-sort'"
                                        class="text-gray-400"></i>
                                </span>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
                                @click="sortSubscriptionsBy('amount_cents')">
                                <span class="inline-flex items-center gap-1 justify-end w-full">
                                    <span>Valor</span>
                                    <i :class="subscriptionsSortKey === 'amount_cents' ? (subscriptionsSortDir === 'asc' ?
                                        'fas fa-sort-up' : 'fas fa-sort-down') : 'fas fa-sort'"
                                        class="text-gray-400"></i>
                                </span>
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="sub in sortedSubscriptions()" :key="sub.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <input type="checkbox" x-model="selectedSubscriptions" :value="sub.id">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    x-text="sub.description || sub.notes || '-'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="sub.status === 'active' ? 'bg-green-100 text-green-800' : (sub
                                            .status === 'paused' ? 'bg-yellow-100 text-yellow-800' :
                                            'bg-red-100 text-red-800')"
                                        x-text="sub.status"></span>
                                    <div class="mt-1 flex items-center gap-2" x-show="sub.status === 'paused'">
                                        <div class="text-xs text-gray-500"
                                            x-text="extractPausedUntil(sub.notes) ? ('Pausada até ' + formatDateBr(extractPausedUntil(sub.notes))) : 'Pausada'">
                                        </div>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-800"
                                            x-show="pauseWarningType(sub) === 'indefinite'">Indefinida</span>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-800"
                                            x-show="pauseWarningType(sub) === 'soon'">Retoma em breve</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                    x-text="sub.day_of_month ?? '-'"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right"
                                    x-text="centsToCurrency(sub.amount_cents || 0)"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    <div class="flex justify-end items-center gap-1">
                                        <x-button type="button" color="primary" size="xs"
                                            @click="openEdit(sub)" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </x-button>
                                        <x-button type="button" color="primary" size="xs"
                                            class="bg-red-600 hover:bg-red-700" x-show="sub.status !== 'canceled'"
                                            @click="openInactivate(sub)" title="Inativar">
                                            <i class="fas fa-ban"></i>
                                        </x-button>
                                        <x-button type="button" color="primary" size="xs"
                                            class="bg-yellow-600 hover:bg-yellow-700" x-show="sub.status === 'active'"
                                            @click="openPause(sub)" title="Pausar">
                                            <i class="fas fa-pause"></i>
                                        </x-button>
                                        <x-button type="button" color="primary" size="xs"
                                            class="bg-green-600 hover:bg-green-700" x-show="sub.status !== 'active'"
                                            @click="openActivate(sub)" title="Ativar">
                                            <i class="fas fa-play"></i>
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

    </div>


    <!-- Modal resumo -->
    <div x-show="summaryOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
        style="margin-top:0 !important">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl my-6 overflow-y-auto"
            style="max-height: calc(100vh - 3rem)">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h5 class="font-semibold">Resumo de Faturas</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click="closeSummary()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="p-4 space-y-3">
                <template x-if="selectedSubscriptions.length === 0">
                    <p class="text-sm text-gray-500">Nenhuma assinatura selecionada.</p>
                </template>
                <template x-if="selectedSubscriptions.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-600">
                                    <th class="py-2 px-2">Descrição</th>
                                    <th class="py-2 px-2">Vencimento</th>
                                    <th class="py-2 px-2">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="it in summaryList()" :key="it.id">
                                    <tr class="border-t">
                                        <td class="py-2 px-2" x-text="it.description"></td>
                                        <td class="py-2 px-2" x-text="formatDateBr(it.due_date) || '-'">
                                        </td>
                                        <td class="py-2 px-2" x-text="centsToCurrency(it.total_cents || 0)"></td>
                                    </tr>
                                </template>
                                <tr x-show="hasMoreInvoices()">
                                    <td colspan="5" class="px-6 py-4 text-center">
                                        <x-button type="button" color="primary" @click="loadMoreInvoices()"><i
                                                class="fas fa-ellipsis-h"></i> Carregue mais</x-button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <template x-if="summaryList().length > 1">
                        <div class="mt-3 flex justify-end items-center gap-2">
                            <span class="text-gray-600">Soma dos totais:</span>
                            <span class="font-semibold" x-text="centsToCurrency(summaryTotalCents())"></span>
                        </div>
                    </template>
                </template>
            </div>
            <div class="px-4 py-3 border-t flex items-center justify-end gap-2">
                <x-button type="button" color="secondary" @click="closeSummary()"><i class="fas fa-times mr-2"></i>
                    Cancelar</x-button>
                <x-button type="button" color="primary" @click="confirmGenerate()"
                    x-bind:disabled="selectedSubscriptions.length === 0"><i class="fas fa-check mr-2"></i> Confirmar
                    geração</x-button>
            </div>
        </div>
    </div>
    <!-- Modais de ações de assinatura -->
    <!-- Modal editar assinatura -->
    <div x-show="editOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
        style="margin-top:0 !important">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl my-6 overflow-y-auto"
            style="max-height: calc(100vh - 3rem)">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h5 class="font-semibold">Editar Assinatura</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click="closeEdit()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descrição</label>
                    <input type="text" class="mt-1 block w-full border rounded px-2 py-1"
                        x-model="editForm.description">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dia de cobrança</label>
                        <input type="number" min="1" max="31"
                            class="mt-1 block w-full border rounded px-2 py-1" x-model.number="editForm.day_of_month">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Desconto (%)</label>
                        <input type="number" min="0" max="100" step="0.01"
                            class="mt-1 block w-full border rounded px-2 py-1"
                            x-model.number="editForm.discount_percent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Valor total</label>
                        <div class="mt-1 font-semibold"
                            x-text="centsToCurrency((editForm.items || []).reduce((s,it)=> s + (amountToCents(it.amount) * (parseInt(it.qty)||1)), 0))">
                        </div>
                    </div>
                </div>
                <!-- Datas de ciclo da assinatura -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data de Início</label>
                        <input type="date" class="mt-1 block w-full border rounded px-2 py-1"
                            x-model="editForm.start_at">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data de Fim</label>
                        <input type="date" class="mt-1 block w-full border rounded px-2 py-1"
                            x-model="editForm.end_at">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Data do último
                            faturamento</label>
                        <input type="date" class="mt-1 block w-full border rounded px-2 py-1 bg-gray-100"
                            x-model="editForm.last_billed_at" readonly disabled>
                        <p class="mt-1 text-xs text-gray-500">Gerenciado pelo sistema; atualizado ao gerar
                            fatura.</p>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Itens</label>
                        <button type="button" class="text-blue-600 hover:underline"
                            @click="editForm.items.push({name:'Novo item', qty:1, amount:'0,00'})"><i
                                class="fas fa-plus mr-1"></i>Adicionar item</button>
                    </div>
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-600">
                                    <th class="py-2 px-2">Nome</th>
                                    <th class="py-2 px-2">Qtd</th>
                                    <th class="py-2 px-2">Valor</th>
                                    <th class="py-2 px-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(it, idx) in editForm.items" :key="idx">
                                    <tr class="border-t">
                                        <td class="py-2 px-2"><input type="text"
                                                class="w-full border rounded px-2 py-1" x-model="it.name">
                                        </td>
                                        <td class="py-2 px-2"><input type="number" min="1"
                                                class="w-24 border rounded px-2 py-1" x-model.number="it.qty"></td>
                                        <td class="py-2 px-2"><input type="text"
                                                class="w-32 border rounded px-2 py-1" x-model="it.amount"
                                                placeholder="0,00"></td>
                                        <td class="py-2 px-2 text-right"><button type="button"
                                                class="text-red-600 hover:underline"
                                                @click="editForm.items.splice(idx,1)"><i
                                                    class="fas fa-trash mr-1"></i>Remover</button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 border-t flex items-center justify-end gap-2">
                <x-button type="button" color="secondary" @click="closeEdit()"><i class="fas fa-times mr-2"></i>
                    Cancelar</x-button>
                <x-button type="button" color="primary" @click="confirmEdit()"><i class="fas fa-save mr-2"></i>
                    Salvar
                    alterações</x-button>
            </div>
        </div>
    </div>
    <!-- Modal inativar assinatura -->
    <div x-show="inactivateOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
        style="margin-top:0 !important">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg my-6 overflow-y-auto"
            style="max-height: calc(100vh - 3rem)">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h5 class="font-semibold">Inativar Assinatura</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click="closeInactivate()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="p-4 space-y-3">
                <p class="text-sm text-gray-700">Tem certeza que deseja inativar a assinatura <span
                        class="font-semibold"
                        x-text="(inactivateTarget?.description || inactivateTarget?.notes || '#'+(inactivateTarget?.id || ''))"></span>?
                </p>
            </div>
            <div class="px-4 py-3 border-t flex items-center justify-end gap-2">
                <x-button type="button" color="secondary" @click="closeInactivate()"><i
                        class="fas fa-times mr-2"></i> Cancelar</x-button>
                <x-button type="button" color="danger" @click="confirmInactivate()"><i class="fas fa-ban mr-2"></i>
                    Inativar</x-button>
            </div>
        </div>
    </div>
    <!-- Modal pausar assinatura -->
    <div x-show="pauseOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
        style="margin-top:0 !important">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg my-6 overflow-y-auto"
            style="max-height: calc(100vh - 3rem)">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h5 class="font-semibold">Pausar Assinatura</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click="closePause()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="p-4 space-y-3">
                <p class="text-sm text-gray-700">Informe uma data para retomar automaticamente (opcional).
                    Deixe em branco para pausa indefinida.</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pausa até</label>
                    <input type="date" class="mt-1 block w-48 border rounded px-2 py-1" x-model="pauseUntil">
                </div>
            </div>
            <div class="px-4 py-3 border-t flex items-center justify-end gap-2">
                <x-button type="button" color="secondary" @click="closePause()"><i class="fas fa-times mr-2"></i>
                    Cancelar</x-button>
                <x-button type="button" color="warning" @click="confirmPause()"><i class="fas fa-pause mr-2"></i>
                    Pausar</x-button>
            </div>
        </div>
    </div>
    <!-- Modal ativar assinatura -->
    <div x-show="activateOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
        style="margin-top:0 !important">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-lg my-6 overflow-y-auto"
            style="max-height: calc(100vh - 3rem)">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h5 class="font-semibold">Ativar Assinatura</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click="closeActivate()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="p-4 space-y-3">
                <p class="text-sm text-gray-700">Deseja ativar novamente a assinatura <span class="font-semibold"
                        x-text="(activateTarget?.description || activateTarget?.notes || '#'+(activateTarget?.id || ''))"></span>?
                </p>
            </div>
            <div class="px-4 py-3 border-t flex items-center justify-end gap-2">
                <x-button type="button" color="secondary" @click="closeActivate()"><i
                        class="fas fa-times mr-2"></i> Cancelar</x-button>
                <x-button type="button" color="success" @click="confirmActivate()"><i class="fas fa-play mr-2"></i>
                    Ativar</x-button>
            </div>
        </div>
    </div>
    </x-card>

    <x-card class="mt-6" title="Faturas">
        <div class="rounded-lg">
            <div class="space-y-6">
                <!-- Faturas -->
                <div>
                    <template x-if="invoices.length === 0">
                        <p class="text-sm text-gray-500 italic">Nenhuma fatura encontrada.</p>
                    </template>

                    <div class="flex items-center gap-2 mb-2">
                        <label class="text-sm text-gray-600">Filtrar Status</label>
                        <select class="border rounded px-2 py-1 text-sm" x-model="invoiceStatusFilter">
                            <option value="" selected>Todos</option>
                            <option value="pending">Em processamento</option>
                            <option value="paid">Pago</option>
                            <option value="paid_manual">Pago - Baixa Manual</option>
                            <option value="canceled">Cancelado</option>
                            <option value="others">Outros</option>
                        </select>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
                                        @click="sortInvoicesBy('id')">
                                        <span class="inline-flex items-center gap-1">
                                            <span>#ID</span>
                                            <i :class="invoicesSortKey === 'id' ? (invoicesSortDir === 'asc' ?
                                                'fas fa-sort-up' : 'fas fa-sort-down') : 'fas fa-sort'"
                                                class="text-gray-400"></i>
                                        </span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
                                        @click="sortInvoicesBy('status')">
                                        <span class="inline-flex items-center gap-1">
                                            <span>Status/Descrição</span>
                                            <i :class="invoicesSortKey === 'status' ? (invoicesSortDir === 'asc' ?
                                                'fas fa-sort-up' : 'fas fa-sort-down') : 'fas fa-sort'"
                                                class="text-gray-400"></i>
                                        </span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
                                        @click="sortInvoicesBy('due_date')">
                                        <span class="inline-flex items-center gap-1">
                                            <span>Vencimento</span>
                                            <i :class="invoicesSortKey === 'due_date' ? (invoicesSortDir === 'asc' ?
                                                'fas fa-sort-up' : 'fas fa-sort-down') : 'fas fa-sort'"
                                                class="text-gray-400"></i>
                                        </span>
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
                                        @click="sortInvoicesBy('total_cents')">
                                        <span class="inline-flex items-center gap-1 justify-end w-full">
                                            <span>Total</span>
                                            <i :class="invoicesSortKey === 'total_cents' ? (invoicesSortDir === 'asc' ?
                                                'fas fa-sort-up' : 'fas fa-sort-down') : 'fas fa-sort'"
                                                class="text-gray-400"></i>
                                        </span>
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" x-init="console.log('[Billing] Table Rendering, Invoices:', invoices, 'Filtered:', filteredInvoices())">
                                <tr x-show="filteredInvoices().length === 0">
                                    <td colspan="5" class="px-6 py-4 text-sm text-gray-500 italic text-center">
                                        Nenhuma
                                        fatura encontrada.</td>
                                </tr>
                                <template x-for="inv in paginatedInvoices()" :key="inv.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="inv.id">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex flex-col">
                                                <span class="text-gray-600 mt-1"
                                                    x-text="subscriptionDescription(inv)"></span>
                                                <span :class="statusBadgeClass(inv.status)"
                                                    x-text="(inv.status === 'paid' && String(inv.gateway_status||'').toLowerCase().includes('manual')) ? 'Pago - Baixa Manual' : statusLabel(inv.status)"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                            x-text="formatDateBr(inv.due_date) || '-'"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right"
                                            x-text="centsToCurrency(inv.total_cents || 0)"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            <x-button type="button" color="secondary"
                                                @click="openInvoiceHistory(inv)"><i
                                                    class="fas fa-history"></i></x-button>
                                            <x-button type="button" color="primary" class="ml-2"
                                                x-bind:disabled="!hasBoletoData(inv)"
                                                @click="openInvoiceBoletoDirect(inv)"><i
                                                    class="fas fa-file-invoice-dollar"></i></x-button>
                                            <x-button type="button" color="secondary" class="ml-2"
                                                @click="openInvoiceEdit(inv)"><i class="fas fa-edit"></i></x-button>
                                            <x-button type="button" color="danger" class="ml-2"
                                                x-bind:disabled="!canCancelInvoice(inv)"
                                                @click="openInvoiceCancel(inv)"><i class="fas fa-ban"></i></x-button>
                                            <x-button type="button" color="success" class="ml-2"
                                                x-bind:disabled="inv.status === 'paid' || inv.status === 'canceled'"
                                                @click="openManualSettle(inv)"><i
                                                    class="fas fa-money-check-alt"></i></x-button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal histórico da fatura -->
                <div x-show="invoiceHistoryOpen" x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
                    style="margin-top:0 !important">
                    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl">
                        <div class="px-4 py-3 border-b flex items-center justify-between">
                            <h5 class="font-semibold">Histórico da Fatura</h5>
                            <button type="button" class="text-gray-500 hover:text-gray-700"
                                @click="closeInvoiceHistory()"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="p-4 space-y-4">
                            <template x-if="invoiceHistoryTarget">
                                <div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                                        <div>
                                            <div class="text-sm text-gray-500">ID</div>
                                            <div class="font-medium" x-text="invoiceHistoryTarget.id"></div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500">Status</div>
                                            <div class="font-medium"><span
                                                    :class="statusBadgeClass(invoiceHistoryTarget.status)"
                                                    x-text="statusLabel(invoiceHistoryTarget.status)"></span></div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500">Vencimento</div>
                                            <div class="font-medium"
                                                x-text="formatDateBr(invoiceHistoryTarget.due_date)">
                                            </div>

                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-500">Total</div>
                                            <div class="font-medium"
                                                x-text="centsToCurrency(invoiceHistoryTarget.total_cents || 0)"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-500">Linha Digitável</div>
                                        <div class="font-mono text-sm break-all"
                                            x-text="invoiceHistoryTarget.linha_digitavel || '-' "></div>
                                    </div>
                                    <div class="mt-2 space-x-3">
                                        <template x-if="invoiceHistoryTarget.boleto_url">
                                            <a :href="invoiceHistoryTarget.boleto_url" target="_blank"
                                                class="text-blue-600 hover:underline">Abrir boleto</a>
                                        </template>
                                        <template x-if="invoiceHistoryTarget.pix_qr_code">
                                            <a :href="invoiceHistoryTarget.pix_qr_code" target="_blank"
                                                class="text-blue-600 hover:underline">Abrir QR PIX</a>
                                        </template>
                                        <template x-if="invoiceHistoryTarget.pix_code">
                                            <span class="text-gray-700">Código PIX: <span class="font-mono text-sm"
                                                    x-text="invoiceHistoryTarget.pix_code"></span></span>
                                        </template>
                                    </div>
                                    <div class="mt-4">
                                        <div class="text-sm text-gray-500 mb-2">Eventos</div>
                                        <ul class="space-y-2">
                                            <template x-for="ev in computeInvoiceHistory(invoiceHistoryTarget)"
                                                :key="ev.label + String(ev.at || '')">
                                                <li class="flex items-center justify-between border rounded px-3 py-2">
                                                    <span x-text="ev.label"></span>
                                                    <span class="text-xs text-gray-500"
                                                        x-text="formatDateTimeBr(ev.at)"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="px-4 py-3 border-t flex items-center justify-end gap-2">
                            <x-button type="button" color="secondary" x-bind:disabled="invoiceGatewaySyncing"
                                @click="syncInvoiceGateway()">
                                <i class="fas fa-sync mr-2"></i>
                                <span x-show="!invoiceGatewaySyncing">Consultar status no gateway</span>
                                <span x-show="invoiceGatewaySyncing">Consultando...</span>
                            </x-button>
                            <x-button type="button" color="primary" x-bind:disabled="invoiceHistoryRefreshing"
                                @click="refreshInvoiceHistory()">
                                <i class="fas fa-sync mr-2"></i>
                                <span x-show="!invoiceHistoryRefreshing">Atualizar</span>
                                <span x-show="invoiceHistoryRefreshing">Atualizando...</span>
                            </x-button>
                            <x-button type="button" color="secondary" @click="closeInvoiceHistory()"><i
                                    class="fas fa-times mr-2"></i> Fechar</x-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Modal boleto da fatura -->
    <div x-show="invoiceBoletoOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
        style="margin-top:0 !important">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-xl">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h5 class="font-semibold">Boleto / PIX da Fatura</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click="closeInvoiceBoleto()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="p-4 space-y-4">
                <template x-if="invoiceBoletoTarget">
                    <div>
                        <div class="mb-3">
                            <div class="text-sm text-gray-500">ID</div>
                            <div class="font-medium" x-text="invoiceBoletoTarget.id"></div>
                        </div>
                        <template x-if="hasBoletoData(invoiceBoletoTarget)">
                            <div class="space-y-3">
                                <div>
                                    <div class="text-sm text-gray-500">Link do Boleto</div>
                                    <a :href="invoiceBoletoTarget.boleto_url" target="_blank" rel="noopener"
                                        class="text-blue-600 hover:underline">Abrir boleto</a>
                                </div>
                                <div x-show="invoiceBoletoTarget.linha_digitavel">
                                    <div class="text-sm text-gray-500">Linha Digitável</div>
                                    <div class="font-mono text-sm" x-text="invoiceBoletoTarget.linha_digitavel"></div>
                                </div>
                                <div x-show="invoiceBoletoTarget.barcode">
                                    <div class="text-sm text-gray-500">Código de Barras</div>
                                    <div class="font-mono text-sm" x-text="invoiceBoletoTarget.barcode"></div>
                                </div>
                                <div x-show="invoiceBoletoTarget.pix_qr_code">
                                    <div class="text-sm text-gray-500">PIX QR Code</div>
                                    <img :src="invoiceBoletoTarget.pix_qr_code" class="max-h-48" alt="PIX QR" />
                                </div>
                                <div x-show="invoiceBoletoTarget.pix_code">
                                    <div class="text-sm text-gray-500">Copia e Cola PIX</div>
                                    <div class="font-mono text-sm break-words" x-text="invoiceBoletoTarget.pix_code">
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="!hasBoletoData(invoiceBoletoTarget)">
                            <div class="text-sm text-gray-600">
                                Boleto/PIX ainda não disponível. Sincronize com o gateway para obter os dados.
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            <div class="px-4 py-3 border-t flex items-center justify-end space-x-2">
                <x-button type="button" color="secondary" @click="closeInvoiceBoleto()"><i
                        class="fas fa-times mr-2"></i> Fechar</x-button>
                <x-button type="button" color="primary" x-bind:disabled="invoiceGatewaySyncing"
                    @click="syncInvoiceGateway(invoiceBoletoTarget)">
                    <i class="fas fa-sync mr-2"></i>
                    <span x-show="!invoiceGatewaySyncing">Sincronizar Gateway</span>
                    <span x-show="invoiceGatewaySyncing">Sincronizando...</span>
                </x-button>
            </div>
        </div>
    </div>

    <!-- Modal editar fatura -->
    <div x-show="invoiceEditOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
        style="margin-top:0 !important">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-xl">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h5 class="font-semibold">Editar Fatura</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click="closeInvoiceEdit()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="p-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input type="text" class="grid-span-2" label="Descrição"
                        x-model="invoiceEditForm.description" help="Opcional" />
                    <x-input type="date" label="Vencimento" required help="Obrigatório"
                        x-model="invoiceEditForm.due_date" />
                    <x-input type="text" label="Total" required
                        help="Informe o valor em reais; será convertido para centavos." placeholder="R$ 0,00"
                        x-model="invoiceEditForm.total_display"
                        x-on:input="invoiceEditForm.total_display = formatCurrencyBr($event.target.value); invoiceEditForm.total_cents = amountToCents(invoiceEditForm.total_display)"
                        x-on:blur="invoiceEditForm.total_display = formatCurrencyBr(invoiceEditForm.total_cents)" />
                    <x-input type="text" label="Moeda" help="Apenas leitura" x-model="invoiceEditForm.currency"
                        disabled />

                </div>

                <div class="mt-4">
                    <div class="text-sm font-semibold text-gray-700 mb-2">Itens da fatura</div>
                    <div x-show="invoiceItemsForEdit().length">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Item</th>
                                        <th class="px-3 py-2 text-right">Qtd</th>
                                        <th class="px-3 py-2 text-right">Valor</th>
                                        <th class="px-3 py-2 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="(it, iidx) in invoiceItemsForEdit()" :key="iidx">
                                        <tr>
                                            <td class="px-3 py-2"
                                                x-text="it.name || it.description || ('Item ' + (iidx+1))"></td>
                                            <td class="px-3 py-2 text-right"
                                                x-text="parseInt(it.qty || it.quantity || 1)"></td>
                                            <td class="px-3 py-2 text-right"
                                                x-text="(function(){ const q=(parseInt(it.qty||it.quantity)||1); const uc=(it.amount_cents!=null?parseInt(it.amount_cents):(it.amount?amountToCents(it.amount):(it.total_cents!=null?Math.round(parseInt(it.total_cents)/q):0))); return centsToCurrency(uc); })()">
                                            </td>
                                            <td class="px-3 py-2 text-right"
                                                x-text="(function(){ const q=(parseInt(it.qty||it.quantity)||1); const tc=(it.total_cents!=null?parseInt(it.total_cents):(it.amount_cents!=null?parseInt(it.amount_cents)*q:(it.amount?amountToCents(it.amount)*q:0))); return centsToCurrency(tc); })()">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div x-show="!invoiceItemsForEdit().length" class="text-xs text-gray-500">Sem itens detalhados na
                        fatura.</div>
                </div>

                <div class="text-xs text-gray-500">Campos ligados ao gateway (como alias e charge_id) não são
                    editáveis.</div>
                <div x-show="Object.keys(invoiceEditErrors||{}).length" class="text-sm text-red-600"
                    x-text="Object.values(invoiceEditErrors||{}).join(', ')"></div>
            </div>
            <div class="px-4 py-3 border-t flex items-center justify-end space-x-2">
                <x-button type="button" color="secondary" @click="closeInvoiceEdit()"><i
                        class="fas fa-times mr-2"></i> Cancelar</x-button>
                <x-button type="button" color="primary" x-bind:disabled="invoiceEditSaving"
                    @click="submitInvoiceEdit()">
                    <i class="fas fa-save mr-2"></i>
                    <span x-show="!invoiceEditSaving">Salvar</span>
                    <span x-show="invoiceEditSaving">Salvando...</span>
                </x-button>
            </div>
        </div>
    </div>

    <!-- Modal cancelar fatura -->
    <div x-show="invoiceCancelOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
        style="margin-top:0 !important">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-xl">
            <div class="px-4 py-3 border-b">
                <h5 class="font-semibold">Cancelar Fatura</h5>
            </div>
            <div class="p-4 space-y-3">
                <div class="text-sm text-gray-700">Atenção: esta operação é irreversível e a fatura será cancelada.
                </div>
                <div class="text-sm" x-show="invoiceCancelTarget">
                    Fatura <span class="font-semibold"
                        x-text="invoiceCancelTarget.number || invoiceCancelTarget.id"></span>
                    — Status atual: <span :class="statusBadgeClass(invoiceCancelTarget.status)"
                        x-text="statusLabel(invoiceCancelTarget.status)"></span>
                </div>
                <div x-show="invoiceCancelError" class="text-sm text-red-600" x-text="invoiceCancelError"></div>
            </div>
            <div class="px-4 py-3 border-t flex items-center justify-end space-x-2">
                <x-button type="button" color="secondary" @click="closeInvoiceCancel()"><i
                        class="fas fa-times mr-1"></i> Fechar</x-button>
                <x-button type="button" color="danger"
                    x-bind:disabled="invoiceCancelProcessing || !canCancelInvoice(invoiceCancelTarget)"
                    @click="confirmCancelInvoice()">
                    <span x-show="!invoiceCancelProcessing"><i class="fas fa-ban mr-1"></i> Confirmar
                        Cancelamento</span>
                    <span x-show="invoiceCancelProcessing">Cancelando...</span>
                </x-button>
            </div>
        </div>
    </div>

    <!-- Modal liquidar manual -->
    <div x-show="invoiceManualSettleOpen" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 mt-0"
        style="margin-top:0 !important">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-xl">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <h5 class="font-semibold">Baixa Manual da Fatura</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700" @click="closeManualSettle()"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="p-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input type="text" label="Valor pago" required
                        help="Informe o valor em reais; será convertido para centavos." placeholder="R$ 0,00"
                        x-model="invoiceManualSettleForm.amount_paid_display"
                        x-on:input="invoiceManualSettleForm.amount_paid_display = formatCurrencyBr($event.target.value); invoiceManualSettleForm.amount_paid_cents = amountToCents(invoiceManualSettleForm.amount_paid_display)"
                        x-on:blur="invoiceManualSettleForm.amount_paid_display = formatCurrencyBr(invoiceManualSettleForm.amount_paid_cents)" />
                    <x-input type="datetime-local" label="Pago em" x-model="invoiceManualSettleForm.paid_at" />
                    <x-select name="method" label="Método" x-model="invoiceManualSettleForm.method">
                        <option value="cash">Dinheiro</option>
                        <option value="transfer">Transferência</option>
                    </x-select>
                    <x-input type="text" label="Referência" x-model="invoiceManualSettleForm.settlement_ref"
                        placeholder="Opcional" />
                </div>
                <div x-show="Object.keys(invoiceManualSettleErrors||{}).length" class="text-sm text-red-600"
                    x-text="Object.values(invoiceManualSettleErrors||{}).join(', ')"></div>
            </div>
            <div class="px-4 py-3 border-t flex items-center justify-end space-x-2">
                <x-button type="button" color="secondary" @click="closeManualSettle()"><i
                        class="fas fa-times mr-1"></i> Cancelar</x-button>
                <x-button type="button" color="success" x-bind:disabled="invoiceManualSettleSaving"
                    @click="submitManualSettle()">
                    <span x-show="!invoiceManualSettleSaving"><i class="fas fa-money-check-alt mr-1"></i>
                        Liquidar</span>
                    <span x-show="invoiceManualSettleSaving">Processando...</span>
                </x-button>
            </div>
        </div>
    </div>

</div>
<script>
    (function() {
        const factory = (opts) => ({
            loading: false,
            error: null,
            message: null,
            info: null,
            processingInvoices: [],
            processingTries: 0,
            processingMaxTries: 12,
            processingIntervalMs: 5000,
            processingIntervalId: null,
            invoiceHistoryOpen: false,
            invoiceHistoryTarget: null,
            invoiceHistoryRefreshing: false,
            invoiceGatewaySyncing: false,
            webhookLog: {},
            // invoice action modals state
            invoiceBoletoOpen: false,
            invoiceBoletoTarget: null,
            invoiceEditOpen: false,
            invoiceEditTarget: null,
            invoiceEditForm: {
                due_date: '',
                total_cents: null,
                total_display: '',
                currency: 'BRL',
                description: ''
            },
            invoiceEditErrors: {},
            invoiceEditSaving: false,
            invoiceCancelOpen: false,
            invoiceCancelTarget: null,
            invoiceCancelProcessing: false,
            invoiceCancelError: null,
            invoiceManualSettleOpen: false,
            invoiceManualSettleTarget: null,
            invoiceManualSettleForm: {
                amount_paid_cents: null,
                paid_at: '',
                method: 'cash',
                status: 'confirmed',
                settlement_ref: ''
            },
            invoiceManualSettleSaving: false,
            invoiceManualSettleErrors: {},
            hasInitRun: false,
            invoiceStatusFilter: "",
            responsavelId: opts.responsavelId || null,
            schoolId: opts.schoolId || null,
            form: {
                studentId: null,
                chargeMethodId: null,
                billingPlanId: null,
                periodicity: 'monthly',
                items: [{
                    name: 'Mensalidade',
                    amount: '',
                    qty: 1
                }],
                discountType: 'percent',
                discountValue: '',
                discountExpiresAt: '',
                earlyDiscountActive: false,
                earlyDiscountValue: '',
                earlyDiscountDays: '',
                dayOfMonth: null,
                invoiceDueDate: '',
                // Datas da assinatura
                startAt: '',
                endAt: '',
                chargeActive: true,
                description: '',
            },
            subscriptions: [],
            invoices: [],
            invoicesLimit: 10,
            invoicesPageInfo: {
                next_page_url: null,
                current_page: 1,
                last_page: 1,
                per_page: 15
            },
            subscriptionsSortKey: 'description',
            subscriptionsSortDir: 'asc',
            invoicesSortKey: 'due_date',
            invoicesSortDir: 'asc',
            currency: 'BRL',
            normalizeDecimal(v) {
                try {
                    if (v === null || v === undefined) return '';
                    let s = String(v);
                    // Remove currency symbols and spaces
                    s = s.replace(/[^\d.,-]/g, '');
                    // If both '.' and ',' exist, assume ',' is decimal separator and remove '.' (thousands)
                    if (s.includes(',') && s.includes('.')) {
                        s = s.replace(/\./g, '');
                    }
                    // Replace comma with dot for parsing
                    s = s.replace(',', '.');
                    // Normalize multiple minus signs
                    if ((s.match(/-/g) || []).length > 1) {
                        s = s.replace(/-/g, '');
                        s = '-' + s;
                    }
                    return s;
                } catch (_) {
                    return '';
                }
            },
            amountToCents(v) {
                const n = parseFloat(this.normalizeDecimal(v));
                return isNaN(n) ? 0 : Math.round(n * 100);
            },
            centsToCurrency(c) {
                const n = (c || 0) / 100;
                return n.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
            },
            formatCurrencyBr(value) {
                try {
                    let s = String(value || '');
                    // Keep only digits
                    s = s.replace(/[^\d]/g, '');
                    if (!s.length) return '';
                    // Ensure at least two digits for cents
                    if (s.length === 1) s = '0' + s;
                    const cents = s.slice(-2);
                    let integer = s.slice(0, -2);
                    integer = integer.replace(/^0+/, '') || '0';
                    // Add thousands separators
                    integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    return `R$ ${integer},${cents}`;
                } catch (_) {
                    return '';
                }
            },
            onCurrencyInput(e, obj, field) {
                const formatted = this.formatCurrencyBr(e.target.value);
                e.target.value = formatted;
                if (obj && field) obj[field] = formatted;
            },
            onPercentInput(field, e) {
                try {
                    let s = String(e.target.value || '');
                    s = s.replace(/[^\d,.-]/g, '');
                    if (s.includes(',') && s.includes('.')) {
                        s = s.replace(/\./g, '');
                    }
                    s = s.replace(',', '.');
                    let v = parseFloat(s);
                    if (isNaN(v)) v = 0;
                    v = Math.max(0, Math.min(100, v));
                    const str = v.toLocaleString('pt-BR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    });
                    e.target.value = str;
                    if (field && this.form.hasOwnProperty(field)) this.form[field] = str;
                } catch (_) {}
            },
            clampDays(d) {
                const n = parseInt(d);
                if (isNaN(n)) return '';
                return Math.max(1, Math.min(30, n));
            },
            onDaysInput(field, e) {
                const v = this.clampDays(e.target.value);
                e.target.value = v;
                if (field && this.form.hasOwnProperty(field)) this.form[field] = v;
            },
            onDiscountValueInput(e) {
                if (this.form.discountType === 'percent') {
                    this.onPercentInput('discountValue', e);
                } else {
                    const formatted = this.formatCurrencyBr(e.target.value);
                    e.target.value = formatted;
                    this.form.discountValue = formatted;
                }
            },
            currentDueDate() {
                const d = parseInt(this.form.dayOfMonth);
                if (this.form.invoiceDueDate) return this.form.invoiceDueDate;
                if (!d || d < 1 || d > 31) return '';
                return this.nextDueDateFromDay(d);
            },
            nextDueDateFromDay(day) {
                try {
                    const pad = (n) => String(n).padStart(2, '0');
                    const daysInMonth = (y, m) => new Date(y, m + 1, 0).getDate();
                    const now = new Date();
                    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    let y = now.getFullYear();
                    let m = now.getMonth();
                    let d = Math.min(parseInt(day), daysInMonth(y, m));
                    let target = new Date(y, m, d);
                    if (target.getTime() <= today.getTime()) {
                        m += 1;
                        if (m > 11) {
                            m = 0;
                            y += 1;
                        }
                        d = Math.min(parseInt(day), daysInMonth(y, m));
                        target = new Date(y, m, d);
                    }
                    const ty = target.getFullYear();
                    const tm = target.getMonth() + 1;
                    const td = target.getDate();
                    return `${ty}-${pad(tm)}-${pad(td)}`;
                } catch (_) {
                    return '';
                }
            },
            init() {
                if (window.__guardianBillingMounted) {
                    console.debug('[Billing] init() global-skipped duplicate');
                    this.hasInitRun = true;
                    return;
                }
                if (this.hasInitRun) {
                    console.debug('[Billing] init() skipped duplicate');
                    return;
                }
                window.__guardianBillingMounted = true;
                this.hasInitRun = true;
                const sEl = document.getElementById('student_id');
                const mEl = document.getElementById('charge_method_id');
                const pEl = document.getElementById('billing_plan_id');
                if (sEl) this.form.studentId = sEl.value || null;
                if (mEl) this.form.chargeMethodId = mEl.value || null;
                if (pEl) this.form.billingPlanId = pEl.value || null;

                // Apply default values using selected plan metadata (amount, day, periodicity)
                if (pEl && pEl.value) {
                    const opt = pEl.selectedOptions && pEl.selectedOptions[0] ? pEl.selectedOptions[0] :
                        null;
                    if (opt) {
                        const amountCentsAttr = opt.getAttribute('data-amount');
                        const dayAttr = opt.getAttribute('data-day');
                        const periodicityAttr = opt.getAttribute('data-periodicity');
                        const amountCents = amountCentsAttr ? parseInt(amountCentsAttr) : 0;
                        const planDay = dayAttr ? parseInt(dayAttr) : null;
                        const planPeriodicity = periodicityAttr || 'monthly';

                        // Sync periodicity from plan
                        this.form.periodicity = planPeriodicity;

                        // If plan has an amount, use it for the first item
                        if (amountCents > 0) {
                            const planName = (opt.textContent || '').split('—')[0].trim();
                            if (!this.form.items.length) {
                                this.form.items.push({
                                    name: planName || 'Mensalidade',
                                    amount: ''
                                });
                            }
                            this.form.items[0].name = planName || this.form.items[0].name;
                            const amount = (amountCents / 100).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            this.form.items[0].amount = amount;
                        }

                        // Prefer plan day if available
                        if (planDay && !this.form.dayOfMonth) {
                            this.form.dayOfMonth = planDay;
                        }
                    }
                }

                // If we have a dayOfMonth, compute the next invoice due date (YYYY-MM-DD)
                if (this.form.dayOfMonth && !this.form.invoiceDueDate) {
                    const pad = (n) => String(n).padStart(2, '0');
                    const daysInMonth = (y, m) => new Date(y, m + 1, 0).getDate();
                    const now = new Date();
                    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                    let y = now.getFullYear();
                    let m = now.getMonth();
                    let d = Math.min(parseInt(this.form.dayOfMonth), daysInMonth(y, m));
                    let target = new Date(y, m, d);
                    if (target.getTime() <= today.getTime()) {
                        m += 1;
                        if (m > 11) {
                            m = 0;
                            y += 1;
                        }
                        d = Math.min(parseInt(this.form.dayOfMonth), daysInMonth(y, m));
                        target = new Date(y, m, d);
                    }
                    const ty = target.getFullYear();
                    const tm = target.getMonth() + 1;
                    const td = target.getDate();
                    this.form.invoiceDueDate = `${ty}-${pad(tm)}-${pad(td)}`;
                }

                this.filterPlanOptions();
                console.debug('[Billing] init()', {
                    schoolId: this.schoolId,
                    responsavelId: this.responsavelId
                });
                this.loadHistory();
            },
            async loadSubscriptionsByPayer() {
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const url =
                        `/api/v1/finance/subscriptions?payer_id=${encodeURIComponent(this.responsavelId)}&school_id=${encodeURIComponent(this.schoolId)}`;
                    console.debug('[Billing] loadSubscriptionsByPayer call', {
                        url,
                        schoolId: this.schoolId,
                        responsavelId: this.responsavelId
                    });
                    const resp = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    let data = null;
                    try {
                        data = await resp.json();
                    } catch (_) {
                        data = null;
                    }
                    console.debug('[Billing] loadSubscriptionsByPayer response', {
                        ok: resp.ok,
                        status: resp.status,
                        data
                    });
                    if (!resp.ok) throw new Error((data && (data.message || JSON.stringify(data
                        .errors))) || 'Falha ao buscar assinaturas');
                    const list = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data :
                    []);
                    this.subscriptions = list;
                    console.debug('[Billing] subscriptions loaded', {
                        count: list.length,
                        ids: list.map(s => s.id)
                    });
                } catch (err) {
                    console.warn('Erro ao carregar assinaturas:', err);
                }
            },
            async loadInvoicesByPayer() {
                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const baseUrl =
                        `/api/v1/finance/invoices?school_id=${encodeURIComponent(this.schoolId)}${this.responsavelId ? `&payer_id=${encodeURIComponent(this.responsavelId)}` : ''}&per_page=100`;
                    console.debug('[Billing] loadInvoicesByPayer call', {
                        url: baseUrl,
                        schoolId: this.schoolId,
                        responsavelId: this.responsavelId
                    });
                    const resp = await fetch(baseUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    let data = null;
                    try {

                        data = await resp.json();
                        console.log(data);
                    } catch (error) {
                        data = null;
                        console.log("Error:" + error);
                    }
                    console.debug('[Billing] loadInvoicesByPayer response', data);
                    if (resp.ok) {

                        const page = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ?
                            data : []);

                        this.invoices = page;
                        this.invoicesPageInfo = {
                            next_page_url: data?.next_page_url || null,
                            current_page: data?.current_page || 1,
                            last_page: data?.last_page || 1,
                            per_page: data?.per_page || page.length,
                        };
                        this.invoicesLimit = this.invoices.length;
                        console.debug('[Billing] invoices loaded', {
                            count: page.length,
                            ids: page.map(i => i.id)
                        });
                    } else {
                        this.invoices = [];
                        console.warn('[Billing] invoices request failed', {
                            status: resp.status,
                            message: data?.message
                        });
                    }
                } catch (err) {
                    console.warn('Erro ao carregar faturas:', err);
                    this.invoices = [];
                }
            },
            async loadHistory() {
                await this.loadSubscriptionsByPayer();
                await this.loadInvoicesByPayer();
            },
            addItem() {
                this.form.items.push({
                    name: '',
                    amount: '',
                    qty: 1
                });
            },
            removeItem(i) {
                if (this.form.items.length > 1) this.form.items.splice(i, 1);
            },
            toggleDiscountType() {
                this.form.discountType = (this.form.discountType === 'percent' ? 'fixed' : 'percent');
            },
            filterPlanOptions() {
                const select = document.getElementById('billing_plan_id');
                if (!select) return;
                const desired = this.form.periodicity;
                let hasVisible = false;
                Array.from(select.options).forEach((opt, idx) => {
                    if (idx === 0) {
                        opt.hidden = false;
                        return;
                    }
                    const p = opt.getAttribute('data-periodicity') || 'monthly';
                    const show = !desired || p === desired;
                    opt.hidden = !show;
                    if (show) hasVisible = true;
                });
                const selectedOpt = select.selectedOptions[0];
                if (selectedOpt && selectedOpt.hidden) {
                    select.value = '';
                    this.form.billingPlanId = null;
                }
                if (!hasVisible) {
                    select.value = '';
                    this.form.billingPlanId = null;
                }
            },
            clampQty(q) {
                const n = parseInt(q);
                if (isNaN(n) || n < 1) return 1;
                return Math.min(9999, n);
            },
            itemQtyTotalCents(it) {
                try {
                    const qty = this.clampQty(it && it.qty !== undefined ? it.qty : 1);
                    const cents = this.amountToCents(it ? it.amount : '');
                    return Math.max(0, qty * cents);
                } catch (_) {
                    return 0;
                }
            },
            itemQtyFormula(it) {
                try {
                    const qty = this.clampQty(it && it.qty !== undefined ? it.qty : 1);
                    const unit = parseFloat(this.normalizeDecimal(it ? it.amount : '')) || 0;
                    const unitStr = unit.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    return `${qty}x${unitStr} = ${this.centsToCurrency(this.itemQtyTotalCents(it))}`;
                } catch (_) {
                    return '';
                }
            },
            baseTotalCents() {
                return this.form.items.reduce((sum, it) => sum + this.itemQtyTotalCents(it), 0);
            },
            discountPercent() {
                const val = parseFloat(this.normalizeDecimal(this.form.discountValue));
                const base = this.baseTotalCents();
                if (!val || base <= 0) return 0;
                if (this.form.discountType === 'percent') return Math.min(100, Math.max(0, val));
                const fixedCents = this.amountToCents(val);
                return Math.max(0, Math.min(100, (fixedCents / base) * 100));
            },
            discountAppliesOn(dateStr) {
                if (!this.form.discountExpiresAt) return false;
                try {
                    const exp = new Date(this.form.discountExpiresAt + 'T23:59:59');
                    const target = dateStr ? new Date(dateStr + 'T00:00:00') : new Date();
                    return target.getTime() <= exp.getTime();
                } catch (_) {
                    return false;
                }
            },
            formatDateBr(dateStr) {
                try {
                    if (!dateStr) return '';
                    const s = String(dateStr);
                    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
                    if (m) {
                        const [, y, mm, dd] = m;
                        return `${dd}/${mm}/${y}`;
                    }
                    const dt = new Date(s);
                    if (!isNaN(dt.getTime())) {
                        const dd = String(dt.getDate()).padStart(2, '0');
                        const mm = String(dt.getMonth() + 1).padStart(2, '0');
                        const yyyy = dt.getFullYear();
                        return `${dd}/${mm}/${yyyy}`;
                    }
                    return '';
                } catch (_) {
                    return '';
                }
            },
            extractPausedUntil(notes) {
                try {
                    if (!notes) return null;
                    if (typeof notes === 'string') {
                        try {
                            const obj = JSON.parse(notes);
                            if (obj && obj.paused_until) return obj.paused_until;
                        } catch (_) {}
                        const m = notes.match(/\[\[paused_until=(\d{4}-\d{2}-\d{2})\]\]/);
                        return m ? m[1] : null;
                    } else if (typeof notes === 'object' && notes.paused_until) {
                        return notes.paused_until;
                    }
                    return null;
                } catch (_) {
                    return null;
                }
            },
            daysUntil(dateStr) {
                try {
                    if (!dateStr) return null;
                    const target = new Date(dateStr + 'T00:00:00');
                    const today = new Date();
                    const start = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                    const diffMs = target.getTime() - start.getTime();
                    return Math.floor(diffMs / (1000 * 60 * 60 * 24));
                } catch (_) {
                    return null;
                }
            },
            pauseWarningType(sub) {
                try {
                    if (!sub || sub.status !== 'paused') return null;
                    const until = this.extractPausedUntil(sub.notes);
                    if (!until) return 'indefinite';
                    const days = this.daysUntil(until);
                    if (days != null && days <= 7) return 'soon';
                    return null;
                } catch (_) {
                    return null;
                }
            },
            buildPauseNotes(origNotes, until) {
                try {
                    const base = (origNotes || '').trim();
                    const tag = until && until.length ? `[[paused_until=${until}]]` : `[[paused_until=]]`;
                    if (!base) return tag;
                    const cleaned = this.cleanPauseNotes(base);
                    return `${cleaned} | ${tag}`;
                } catch (_) {
                    return (until && until.length) ? `[[paused_until=${until}]]` : `[[paused_until=]]`;
                }
            },
            cleanPauseNotes(notes) {
                try {
                    if (!notes) return '';
                    return String(notes)
                        .replace(/\s*\|\s*\[\[paused_until=.*?\]\]\s*/g, '')
                        .replace(/\[\[paused_until=.*?\]\]/g, '')
                        .trim();
                } catch (_) {
                    return '';
                }
            },
            currentStudentName() {
                try {
                    const sEl = document.getElementById('student_id');
                    if (!sEl || !sEl.value) return '';
                    const opt = sEl.selectedOptions && sEl.selectedOptions[0] ? sEl.selectedOptions[0] :
                        null;
                    return opt ? (opt.textContent || '').trim() : '';
                } catch (_) {
                    return '';
                }
            },
            earlyDiscountPercent() {
                const val = parseFloat(this.normalizeDecimal(this.form.earlyDiscountValue));
                if (!val || val <= 0) return 0;
                return Math.min(100, Math.max(0, val));
            },
            earlyLimitDate(dueStr, days) {
                try {
                    const pad = (n) => String(n).padStart(2, '0');
                    const src = dueStr && dueStr.length ? dueStr : this.currentDueDate();
                    const dInt = this.clampDays(days);
                    if (!src || !dInt) return '';
                    const [y, m, d] = src.split('-').map(x => parseInt(x));
                    const dt = new Date(y, m - 1, d);
                    dt.setDate(dt.getDate() - dInt);
                    const ty = dt.getFullYear();
                    const tm = dt.getMonth() + 1;
                    const td = dt.getDate();
                    return `${ty}-${pad(tm)}-${pad(td)}`;
                } catch (_) {
                    return '';
                }
            },
            earlyDiscountAppliesOn(dateStr) {
                if (!this.form.earlyDiscountActive) return false;
                const days = this.clampDays(this.form.earlyDiscountDays);
                const lim = this.earlyLimitDate(this.currentDueDate(), days);
                if (!lim || !days) return false;
                try {
                    const target = dateStr ? new Date(dateStr + 'T00:00:00') : new Date();
                    const limit = new Date(lim + 'T23:59:59');
                    return target.getTime() <= limit.getTime();
                } catch (_) {
                    return false;
                }
            },
            netTotalCentsFor(dateStr) {
                const base = this.baseTotalCents();
                let pct = this.discountAppliesOn(dateStr) ? this.discountPercent() : 0;
                if (this.earlyDiscountAppliesOn(dateStr)) {
                    pct += this.earlyDiscountPercent();
                }
                pct = Math.min(100, Math.max(0, pct));
                const net = Math.round(base * (pct ? (1 - (pct / 100)) : 1));
                return Math.max(0, net);
            },
            defaultDescription() {
                try {
                    const per = this.form.periodicity || 'monthly';
                    const perLabel = per === 'monthly' ? 'Mensal' : (per === 'bimonthly' ? 'Bimestral' : (
                        per === 'annual' ? 'Anual' : per));
                    const items = (this.form.items || []).map(i => (i.name || '').trim()).filter(Boolean)
                        .join(', ');
                    const itemsLabel = items && items.length ? items : 'Itens da fatura';
                    return `${perLabel} - ${itemsLabel}${(function(){const n=this.currentStudentName();return n?` — Aluno: ${n}`:''}).call(this)}`;
                } catch (_) {
                    return 'Itens da fatura';
                }
            },
            sameMonth(a, b) {
                try {
                    if (!a || !b) return false;
                    const pa = String(a).split('-');
                    const pb = String(b).split('-');
                    return pa[0] === pb[0] && pa[1] === pb[1];
                } catch (_) {
                    return false;
                }
            },
            normalizeYmd(val) {
                try {
                    if (!val) return '';
                    const s = String(val);
                    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
                    if (m) return `${m[1]}-${m[2]}-${m[3]}`;
                    const dt = new Date(s);
                    if (!isNaN(dt.getTime())) {
                        const y = dt.getFullYear();
                        const m2 = String(dt.getMonth() + 1).padStart(2, '0');
                        const d2 = String(dt.getDate()).padStart(2, '0');
                        return `${y}-${m2}-${d2}`;
                    }
                    return '';
                } catch (_) {
                    return '';
                }
            },
            latestInvoiceDateFor(subId) {
                try {
                    const list = (this.invoices || []).filter(inv => inv.subscription_id === subId && inv
                        .due_date);
                    if (!list.length) return '';
                    let latest = null;
                    for (const inv of list) {
                        const ds = this.normalizeYmd(inv.due_date);
                        if (!ds) continue;
                        const t = new Date(ds + 'T00:00:00').getTime();
                        if (latest == null || t > latest.t) latest = {
                            ds,
                            t
                        };
                    }
                    return latest ? latest.ds : '';
                } catch (_) {
                    return '';
                }
            },
            alreadyBilledInMonth(subId, due) {
                try {
                    const sub = (this.subscriptions || []).find(s => s.id === subId);
                    if (sub && sub.last_billed_at && this.sameMonth(sub.last_billed_at, due)) return true;
                    const list = (this.invoices || []).filter(inv => inv.subscription_id === subId);
                    return list.some(inv => inv.due_date && this.sameMonth(inv.due_date, due));
                } catch (_) {
                    return false;
                }
            },
            generateNotes(context, dueDate = null) {
                try {
                    const parts = [];
                    const per = this.form.periodicity || 'monthly';
                    const perLabel = per === 'monthly' ? 'Mensal' : (per === 'bimonthly' ? 'Bimestral' : (
                        per === 'annual' ? 'Anual' : per));
                    parts.push(`Período: ${perLabel}`);
                    if (context === 'invoice') {
                        const d = dueDate || this.currentDueDate();
                        parts.push(`Vencimento: ${this.formatDateBr(d)}`);
                    } else {
                        const dom = this.form.dayOfMonth ? parseInt(this.form.dayOfMonth) : null;
                        if (dom) parts.push(`Dia do vencimento: ${dom}`);
                    }
                    // Desconto padrão
                    const discVal = this.normalizeDecimal(this.form.discountValue);
                    if (discVal && parseFloat(discVal) > 0) {
                        const valueLabel = this.form.discountType === 'percent' ?
                            `${parseFloat(discVal).toLocaleString('pt-BR')}%` :
                            this.centsToCurrency(this.amountToCents(discVal));
                        let expLabel = '';
                        if (this.form.discountExpiresAt) {
                            expLabel = ` até ${this.formatDateBr(this.form.discountExpiresAt)}`;
                        }
                        parts.push(`Desconto: ${valueLabel}${expLabel}`);
                    }
                    // Desconto adiantado
                    const earlyVal = this.normalizeDecimal(this.form.earlyDiscountValue);
                    const earlyDays = this.clampDays(this.form.earlyDiscountDays);
                    if (earlyVal && parseFloat(earlyVal) > 0 && earlyDays && this.form
                        .earlyDiscountActive) {
                        const lim = this.earlyLimitDate(dueDate || this.currentDueDate(), earlyDays);
                        const limBr = this.formatDateBr(lim);
                        const label =
                            `${parseFloat(earlyVal).toLocaleString('pt-BR')}% até ${limBr} com ${earlyDays} dias de antecedência`;
                        parts.push(`Desconto adiantado: ${label}`);
                    }
                    return parts.join(' | ');
                } catch (_) {
                    return '';
                }
            },
            async saveSubscription() {
                this.loading = true;
                this.error = null;
                this.message = null;
                try {
                    const sEl = document.getElementById('student_id');
                    const mEl = document.getElementById('charge_method_id');
                    const studentId = (sEl && sEl.value) ? sEl.value : this.form.studentId;
                    const methodId = (mEl && mEl.value) ? mEl.value : this.form.chargeMethodId;
                    const pEl = document.getElementById('billing_plan_id');
                    const planId = (pEl && pEl.value) ? pEl.value : this.form.billingPlanId;
                    if (!methodId) throw new Error('Selecione o método de pagamento');
                    if (!this.form.dayOfMonth || parseInt(this.form.dayOfMonth) < 1 || parseInt(this
                            .form.dayOfMonth) > 31) throw new Error(
                        'Informe o dia do vencimento (1-31)');
                    const body = {
                        payer_id: this.responsavelId,
                        charge_method_id: methodId,
                        billing_plan_id: planId ? parseInt(planId) : null,
                        amount_cents: this.baseTotalCents(),
                        currency: this.currency,
                        day_of_month: (this.form.dayOfMonth ? parseInt(this.form.dayOfMonth) :
                            null),
                        discount_percent: Math.round(this.discountPercent()),
                        // Datas de ciclo
                        start_at: (this.form.startAt || null),
                        end_at: (this.form.endAt || null),
                        active: !!this.form.chargeActive,
                        description: (this.form.description && this.form.description.trim()) ? this
                            .form.description.trim() : this.defaultDescription(),
                        notes: this.generateNotes('subscription'),
                    };
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const resp = await fetch('/api/v1/finance/subscriptions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            ...body,
                            school_id: this.schoolId
                        })
                    });
                    const data = await resp.json();
                    if (!resp.ok) throw new Error((data && (data.message || JSON.stringify(data
                        .errors))) || 'Falha ao salvar assinatura');
                    this.message = 'Assinatura salva';
                    if (window.alertSystem && typeof window.alertSystem.success === 'function') {
                        window.alertSystem.success((data && data.message) ? data.message : this
                            .message);
                    }
                    await this.loadHistory();
                } catch (err) {
                    this.error = err.message || 'Erro ao salvar assinatura';
                } finally {
                    this.loading = false;
                }
            },
            async createInvoice(subscriptionId = null) {
                this.loading = true;
                this.error = null;
                this.message = null;
                try {
                    const due = this.currentDueDate();
                    if (!due) throw new Error(
                        'Informe o dia do vencimento (1-31) ou defina a data da fatura');
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    let subId = subscriptionId;
                    if (!subId) {
                        const subResp = await fetch(
                            `/api/v1/finance/subscriptions?payer_id=${encodeURIComponent(this.responsavelId)}&status=active&school_id=${encodeURIComponent(this.schoolId)}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin'
                            });
                        const subData = await subResp.json();
                        if (!subResp.ok) throw new Error((subData && (subData.message || JSON.stringify(
                            subData.errors))) || 'Falha ao buscar assinaturas');
                        const list = Array.isArray(subData?.data) ? subData.data : (Array.isArray(
                            subData) ? subData : []);
                        if (!list.length) throw new Error(
                            'Crie uma assinatura ativa para gerar a fatura');
                        subId = list[0].id;
                    }
                    const total = this.netTotalCentsFor(due);

                    // Verificação de duplicidade: já existe fatura neste mês?
                    if (this.alreadyBilledInMonth(subId, due)) {
                        const proceed = window.confirm(
                            'Já existe fatura para este mês desta assinatura. Deseja continuar e gerar outra?'
                        );
                        if (!proceed) {
                            this.message = 'Geração cancelada';
                            return;
                        }
                    }
                    const body = {
                        subscription_id: subId,
                        due_date: due,
                        total_cents: total,
                        currency: this.currency,
                        description: (this.form.description && this.form.description.trim()) ? this
                            .form.description.trim() : this.defaultDescription(),
                        notes: this.generateNotes('invoice', due)
                    };
                    const resp = await fetch('/api/v1/finance/invoices', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            ...body,
                            school_id: this.schoolId
                        })
                    });
                    const data = await resp.json();
                    if (!resp.ok) throw new Error((data && (data.message || JSON.stringify(data
                        .errors))) || 'Falha ao criar fatura');
                    this.message = `Fatura criada`;
                    if (window.alertSystem && typeof window.alertSystem.success === 'function') {
                        window.alertSystem.success((data && data.message) ? data.message : this
                            .message);
                    }
                    await this.loadHistory();
                    try {
                        const invoiceId = data?.id;
                        if (invoiceId) {
                            let alias = null;
                            const sub = (this.subscriptions || []).find(s => s.id === subId);
                            if (sub && sub.charge_method_id) {
                                alias = await this.fetchChargeMethodAlias(sub.charge_method_id);
                            }
                            if (alias) {
                                await this.requestGatewayForInvoice(alias, invoiceId, 'boleto');
                                this.info = 'Solicitação enviada ao webhook para 1 fatura.';
                                this.startWebhookMonitoring([invoiceId]);
                            }
                        }
                    } catch (e) {
                        console.warn('Falha ao solicitar webhook para fatura', data?.id, e);
                    }
                } catch (err) {
                    this.error = err.message || 'Erro ao criar fatura';
                } finally {
                    this.loading = false;
                }
            },
            summaryOpen: false,
            openCreateSubscription() {
                if (this.$store?.billing) this.$store.billing.createSubscriptionOpen = true;
            },
            closeCreateSubscription() {
                if (this.$store?.billing) this.$store.billing.createSubscriptionOpen = false;
            },
            selectedSubscriptions: [],
            selectAll: false,
            toggleSelectAll(checked) {
                this.selectedSubscriptions = checked ? (this.subscriptions || []).map(s => s.id) : [];
            },
            openSummary() {
                if (!this.selectedSubscriptions.length) {
                    this.error = 'Selecione ao menos uma assinatura';
                    return;
                }
                this.summaryOpen = true;
            },
            closeSummary() {
                this.summaryOpen = false;
            },
            summaryList() {
                const globalDue = this.currentDueDate();
                return (this.subscriptions || [])
                    .filter(s => this.selectedSubscriptions.map(v => String(v)).includes(String(s.id)))
                    .map(s => {
                        const due = (globalDue && globalDue.length) ? globalDue : (s.day_of_month ? this
                            .nextDueDateFromDay(s.day_of_month) : '');
                        const base = parseInt(s.amount_cents) || 0;
                        let pct = this.discountAppliesOn(due) ? this.discountPercent() : 0;
                        if (this.earlyDiscountAppliesOn(due)) {
                            pct += this.earlyDiscountPercent();
                        }
                        pct = Math.min(100, Math.max(0, pct));
                        const net = Math.round(base * (pct ? (1 - (pct / 100)) : 1));
                        return {
                            id: s.id,
                            description: s.description || s.notes || '-',
                            due_date: due,
                            total_cents: Math.max(0, net)
                        };
                    });
            },
            summaryTotalCents() {
                try {
                    return this.summaryList().reduce((sum, it) => sum + (parseInt(it.total_cents) || 0), 0);
                } catch (_) {
                    return 0;
                }
            },
            async confirmGenerate() {
                this.loading = true;
                this.error = null;
                this.message = null;
                try {
                    const items = this.summaryList();
                    // Validação: todas precisam de data de vencimento
                    if (!items.length) {
                        this.error = 'Selecione ao menos uma assinatura';
                        throw new Error('Lista vazia');
                    }
                    if (items.some(it => !it.due_date || !/^\d{4}-\d{2}-\d{2}/.test(String(it
                            .due_date)))) {
                        this.error = 'Informe o dia do vencimento (1-31) ou defina a data da fatura';
                        throw new Error('Vencimento ausente ou inválido');
                    }
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const successes = [];
                    const failures = [];
                    const webhookRequested = [];
                    for (const it of items) {
                        try {
                            if (this.alreadyBilledInMonth(it.id, it.due_date)) {
                                const proceed = window.confirm(
                                    'Já existe fatura para este mês desta assinatura. Deseja continuar e gerar outra?'
                                );
                                if (!proceed) {
                                    failures.push({
                                        subscription_id: it.id,
                                        reason: 'duplicated'
                                    });
                                    continue;
                                }
                            }
                            const desc = (this.form.description && this.form.description.trim()) ? this
                                .form.description.trim() : this.defaultDescription();
                            const body = {
                                subscription_id: it.id,
                                due_date: it.due_date,
                                total_cents: it.total_cents,
                                currency: this.currency,
                                description: desc,
                                notes: this.generateNotes('invoice', it.due_date)
                            };
                            const resp = await fetch('/api/v1/finance/invoices', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin',
                                body: JSON.stringify({
                                    ...body,
                                    school_id: this.schoolId
                                })
                            });
                            const data = await resp.json().catch(() => null);
                            if (!resp.ok) throw new Error((data && (data.message || JSON.stringify(data
                                .errors))) || 'Falha ao criar fatura');
                            const invoiceId = data?.id;
                            if (!invoiceId) throw new Error('Resposta sem ID da fatura');

                            const sub = (this.subscriptions || []).find(s => s.id === it.id);
                            let alias = null;
                            if (sub && sub.charge_method_id) {
                                alias = await this.fetchChargeMethodAlias(sub.charge_method_id);
                            }
                            if (alias) {
                                try {
                                    await this.requestGatewayForInvoice(alias, invoiceId, 'boleto');
                                } catch (e) {
                                    console.warn('Falha ao solicitar webhook para fatura', invoiceId,
                                        e);
                                }
                                webhookRequested.push(invoiceId);
                            }
                            successes.push(invoiceId);
                        } catch (errSub) {
                            failures.push({
                                subscription_id: it.id,
                                error: errSub.message || String(errSub)
                            });
                        }
                    }
                    await this.loadHistory();
                    if (successes.length) {
                        this.message = `${successes.length} fatura(s) criada(s).`;
                        if (window.alertSystem && typeof window.alertSystem.success === 'function') {
                            window.alertSystem.success(this.message);
                        }
                        if (webhookRequested.length) {
                            this.info =
                                `Solicitação enviada ao webhook para ${webhookRequested.length} fatura(s).`;
                            this.startWebhookMonitoring(webhookRequested);
                            if (window.alertSystem && typeof window.alertSystem.info === 'function') {
                                window.alertSystem.info(this.info);
                            }
                        } else {
                            this.info = null;
                        }
                        if (failures.length) {
                            this.error =
                                'Algumas faturas não foram geradas. Verifique os dados e tente novamente.';
                        }
                    } else {
                        this.error = 'Nenhuma fatura foi gerada. Corrija os erros e tente novamente.';
                    }
                } catch (err) {
                    this.error = err.message || 'Erro ao gerar faturas';
                } finally {
                    this.summaryOpen = false;
                    this.loading = false;
                }
            },
            async fetchChargeMethodAlias(id) {
                try {
                    if (!id) return null;
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const resp = await fetch(
                        `/api/v1/finance/charge-methods/${id}?school_id=${encodeURIComponent(this.schoolId)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                    const data = await resp.json().catch(() => null);
                    if (!resp.ok) return null;
                    const a = String(data?.gateway_alias || '').toLowerCase();
                    return a === 'assas' ? 'asaas' : (a || null);
                } catch (_) {
                    return null;
                }
            },
            async requestGatewayForInvoice(alias, invoiceId, method) {
                try {
                    if (!alias || !invoiceId) return;
                    alias = String(alias || '').toLowerCase();
                    if (alias === 'assas') alias = 'asaas';
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const payload = {
                        event: 'invoice_created',
                        invoice_id: invoiceId,
                        method: method || 'boleto'
                    };
                    this.webhookLog[invoiceId] = {
                        ...(this.webhookLog[invoiceId] || {}),
                        requestedAt: new Date().toISOString(),
                        alias,
                        method: method || 'boleto'
                    };
                    const resp = await fetch(`/api/v1/webhooks/gateway/${encodeURIComponent(alias)}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });
                    let respData = null;
                    try {
                        respData = await resp.json();
                    } catch (_) {
                        respData = null;
                    }
                    const nowIso = new Date().toISOString();
                    const entry = this.webhookLog[invoiceId] || {};
                    if (resp.ok) {
                        entry.gatewayOkAt = nowIso;
                    } else {
                        const msg = (respData && (respData.message || respData.error || JSON.stringify(
                            respData))) || `HTTP ${resp.status}`;
                        entry.gatewayError = {
                            message: msg,
                            at: nowIso
                        };
                    }
                    this.webhookLog[invoiceId] = entry;
                } catch (e) {
                    const entry = this.webhookLog[invoiceId] || {};
                    entry.gatewayError = {
                        message: (e && e.message) ? e.message : String(e),
                        at: new Date().toISOString()
                    };
                    this.webhookLog[invoiceId] = entry;
                }
            },
            startWebhookMonitoring(ids) {
                try {
                    this.processingInvoices = Array.from(new Set(ids)).filter(id => !!id);
                    this.processingTries = 0;
                    if (this.processingIntervalId) {
                        clearInterval(this.processingIntervalId);
                        this.processingIntervalId = null;
                    }
                    if (!this.processingInvoices.length) return;
                    const check = async () => {
                        this.processingTries += 1;
                        await this.loadHistory();
                        (ids || []).forEach(id => {
                            const inv = (this.invoices || []).find(i => i.id === id);
                            if (inv && this.hasBoletoData(inv)) {
                                const entry = this.webhookLog[id] || {};
                                if (!entry.boletoGeneratedAt) {
                                    entry.boletoGeneratedAt = new Date().toISOString();
                                    this.webhookLog[id] = entry;
                                }
                            }
                        });
                        this.processingInvoices = this.processingInvoices.filter(id => {
                            const inv = (this.invoices || []).find(i => i.id === id);
                            return inv ? !this.hasBoletoData(inv) && inv.status !==
                                'paid' && inv.status !== 'canceled' : true;
                        });
                        if (!this.processingInvoices.length || this.processingTries >= this
                            .processingMaxTries) {
                            clearInterval(this.processingIntervalId);
                            this.processingIntervalId = null;
                        }
                    };
                    check();
                    this.processingIntervalId = setInterval(check, this.processingIntervalMs);
                } catch (_) {
                    // no-op
                }
            },
            hasBoletoData(inv) {
                try {
                    return !!(inv?.boleto_url || inv?.linha_digitavel || inv?.barcode || inv?.pix_qr_code ||
                        inv?.pix_code);
                } catch (_) {
                    return false;
                }
            },
            hasEmptyChargeDayInSelection() {
                try {
                    const ids = (this.selectedSubscriptions || []).map(v => String(v));
                    return (this.subscriptions || []).some(s => ids.includes(String(s.id)) && (!s
                        .day_of_month || parseInt(s.day_of_month) < 1));
                } catch (_) {
                    return false;
                }
            },
            formatDateTimeBr(ts) {
                try {
                    if (!ts) return '';
                    const d = new Date(ts);
                    const dd = String(d.getDate()).padStart(2, '0');
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const yyyy = d.getFullYear();
                    const hh = String(d.getHours()).padStart(2, '0');
                    const min = String(d.getMinutes()).padStart(2, '0');
                    return `${dd}/${mm}/${yyyy} ${hh}:${min}`;
                } catch (_) {
                    return '';
                }
            },
            statusBadgeClass(st) {
                const s = String(st || '').toLowerCase();
                if (s === 'pending')
                    return 'text-xs items-center px-2 py-0.5 rounded border bg-yellow-100 text-yellow-800 border-yellow-200';
                if (s === 'paid')
                    return 'text-xs inline-flex items-center px-2 py-0.5 rounded border bg-green-100 text-green-800 border-green-200';
                if (s === 'overdue')
                    return 'text-xs inline-flex items-center px-2 py-0.5 rounded border bg-red-100 text-red-800 border-red-200';
                if (s === 'canceled')
                    return 'text-xs inline-flex items-center px-2 py-0.5 rounded border bg-red-100 text-red-800 border-red-200';
                return 'text-xs inline-flex items-center px-2 py-0.5 rounded border bg-gray-100 text-gray-800 border-gray-200';
            },
            statusLabel(st) {
                const s = String(st || '').toLowerCase();
                if (s === 'pending') return 'Em processamento';
                if (s === 'paid') return 'Pago';
                if (s === 'overdue') return 'Vencida';
                if (s === 'canceled') return 'Cancelado';
                return st || '-';
            },
            subscriptionDescription(inv) {
                try {
                    const sid = inv?.subscription_id;
                    const sub = (this.subscriptions || []).find(s => String(s.id) === String(sid));
                    return sub?.description || sub?.notes || '-';
                } catch (_) {
                    return '-';
                }
            },
            buildAutoInvoiceDescription(inv) {
                try {
                    const sdesc = this.subscriptionDescription(inv);
                    if (sdesc && sdesc !== '-' && String(sdesc).trim().length) return String(sdesc).trim();
                    return this.defaultDescription();
                } catch (_) {
                    return this.defaultDescription();
                }
            },
            invoiceItemsForEdit() {
                try {
                    const tgt = this.invoiceEditTarget;
                    if (tgt && Array.isArray(tgt.items) && tgt.items.length) return tgt.items;
                    if (tgt && tgt.subscription_id) {
                        const sub = (this.subscriptions || []).find(s => String(s.id) === String(tgt
                            .subscription_id));
                        if (sub) {
                            const name = (sub.description && sub.description.trim()) ? sub.description
                            .trim() : 'Mensalidade';
                            const ac = parseInt(sub.amount_cents) || 0;
                            if (ac > 0) {
                                return [{
                                    name,
                                    qty: 1,
                                    amount_cents: ac
                                }];
                            }
                        }
                    }
                    if (tgt) {
                        const total = parseInt(tgt.total_cents) || 0;
                        if (total > 0) {
                            return [{
                                name: 'Fatura',
                                qty: 1,
                                amount_cents: total
                            }];
                        }
                    }
                    return [];
                } catch (_) {
                    return [];
                }
            },
            // Sorting for subscriptions
            sortSubscriptionsBy(key) {
                try {
                    if (this.subscriptionsSortKey === key) {
                        this.subscriptionsSortDir = (this.subscriptionsSortDir === 'asc' ? 'desc' : 'asc');
                    } else {
                        this.subscriptionsSortKey = key;
                        this.subscriptionsSortDir = 'asc';
                    }
                } catch (_) {}
            },
            sortedSubscriptions() {
                try {
                    const list = Array.isArray(this.subscriptions) ? this.subscriptions : [];
                    const key = String(this.subscriptionsSortKey || 'description');
                    const dir = this.subscriptionsSortDir === 'desc' ? -1 : 1;
                    const toNum = (v) => v === null || v === undefined ? (dir === 1 ? Number
                        .MAX_SAFE_INTEGER : Number.MIN_SAFE_INTEGER) : Number(v) || 0;
                    const getter = (sub) => {
                        switch (key) {
                            case 'description':
                                return String(sub?.description || sub?.notes || '');
                            case 'status':
                                return String(sub?.status || '');
                            case 'day_of_month':
                                return sub?.day_of_month ?? null;
                            case 'amount_cents':
                                return sub?.amount_cents ?? null;
                            default:
                                return null;
                        }
                    };
                    return [...list].sort((a, b) => {
                        const av = getter(a);
                        const bv = getter(b);
                        if (typeof av === 'string' || typeof bv === 'string') {
                            return String(av || '').localeCompare(String(bv || '')) * dir;
                        }
                        return (toNum(av) - toNum(bv)) * dir;
                    });
                } catch (_) {
                    return Array.isArray(this.subscriptions) ? this.subscriptions : [];
                }
            },
            filteredInvoices() {
                try {
                    const list = Array.isArray(this.invoices) ? this.invoices : [];
                    console.log('[Billing] Invoices Before Filter:', list, 'Filter:', this
                        .invoiceStatusFilter);
                    const f = String(this.invoiceStatusFilter || '').toLowerCase();
                    if (!f) {
                        console.log('[Billing] No Filter Applied, Returning:', list);
                        return list;
                    }
                    const res = list.filter(inv => {
                        const s = String(inv?.status || '').toLowerCase();
                        if (f === 'others') return (s !== 'pending' && s !== 'paid' && s !==
                            'canceled');
                        if (f === 'paid_manual') return (s === 'paid' && String(inv
                            ?.gateway_status || '').toLowerCase().includes('manual'));
                        return s === f;
                    });
                    console.log('[Billing] Filtered Invoices:', res);
                    return res;
                } catch (error) {
                    console.error('[Billing] Error in filteredInvoices:', error);
                    return [];
                }
            },
            // Sorting for invoices
            sortInvoicesBy(key) {
                try {
                    if (this.invoicesSortKey === key) {
                        this.invoicesSortDir = (this.invoicesSortDir === 'asc' ? 'desc' : 'asc');
                    } else {
                        this.invoicesSortKey = key;
                        this.invoicesSortDir = 'asc';
                    }
                } catch (_) {}
            },
            orderedInvoices() {
                try {
                    const list = Array.isArray(this.filteredInvoices()) ? this.filteredInvoices() : [];
                    const key = String(this.invoicesSortKey || 'due_date');
                    const dir = this.invoicesSortDir === 'desc' ? -1 : 1;
                    const toNum = (v) => v === null || v === undefined ? (dir === 1 ? Number
                        .MAX_SAFE_INTEGER : Number.MIN_SAFE_INTEGER) : Number(v) || 0;
                    const getter = (inv) => {
                        switch (key) {
                            case 'id':
                                return inv?.id ?? null;
                            case 'status':
                                return String(inv?.status || '');
                            case 'due_date':
                                return inv?.due_date ? new Date(inv.due_date).getTime() : null;
                            case 'total_cents':
                                return inv?.total_cents ?? null;
                            case 'subscription_description':
                                return String(this.subscriptionDescription(inv) || '');
                            default:
                                return null;
                        }
                    };
                    return [...list].sort((a, b) => {
                        const av = getter(a);
                        const bv = getter(b);
                        if (typeof av === 'string' || typeof bv === 'string') {
                            return String(av || '').localeCompare(String(bv || '')) * dir;
                        }
                        return (toNum(av) - toNum(bv)) * dir;
                    });
                } catch (_) {
                    return [];
                }
            },
            paginatedInvoices() {
                try {
                    const list = Array.isArray(this.orderedInvoices()) ? this.orderedInvoices() : [];
                    return list.slice(0, this.invoicesLimit || 10);
                } catch (_) {
                    return [];
                }
            },
            hasMoreInvoices() {
                try {
                    const total = Array.isArray(this.orderedInvoices()) ? this.orderedInvoices().length : 0;
                    if (this.invoicesPageInfo && this.invoicesPageInfo.next_page_url) return true;
                    return (this.invoicesLimit || 10) < total;
                } catch (_) {
                    return false;
                }
            },
            async loadMoreInvoices() {
                try {
                    const nextUrl = this.invoicesPageInfo?.next_page_url;
                    if (!nextUrl) {
                        this.invoicesLimit = this.invoices.length;
                        return;
                    }
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const resp = await fetch(nextUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    let data = null;
                    try {
                        data = await resp.json();
                    } catch (_) {
                        data = null;
                    }
                    if (!resp.ok) return;
                    const more = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data :
                    []);
                    this.invoices = [...this.invoices, ...more];
                    this.invoicesPageInfo = {
                        next_page_url: data?.next_page_url || null,
                        current_page: data?.current_page || ((this.invoicesPageInfo?.current_page ||
                            1) + 1),
                        last_page: data?.last_page || (this.invoicesPageInfo?.last_page || 1),
                        per_page: data?.per_page || (this.invoicesPageInfo?.per_page || 15),
                    };
                    this.invoicesLimit = this.invoices.length;
                } catch (_) {}
            },
            openInvoiceHistory(inv) {
                try {
                    this.invoiceHistoryTarget = inv;
                    this.invoiceHistoryOpen = true;
                } catch (_) {}
            },
            closeInvoiceHistory() {
                this.invoiceHistoryOpen = false;
                this.invoiceHistoryTarget = null;
            },
            async refreshInvoiceHistory() {
                try {
                    const id = this.invoiceHistoryTarget?.id;
                    if (!id) return;
                    this.invoiceHistoryRefreshing = true;
                    await this.loadHistory();
                    const inv = (this.invoices || []).find(i => i.id === id);
                    if (inv) {
                        this.invoiceHistoryTarget = inv;
                        const log = this.webhookLog[id] || {};
                        if (this.hasBoletoData(inv) && !log.boletoGeneratedAt) {
                            log.boletoGeneratedAt = new Date().toISOString();
                            this.webhookLog[id] = log;
                        }
                    }
                } catch (_) {} finally {
                    this.invoiceHistoryRefreshing = false;
                }
            },
            async syncInvoiceGateway() {
                try {
                    const id = this.invoiceHistoryTarget?.id;
                    const schoolId = this.schoolId;
                    if (!id || !schoolId) return;
                    this.invoiceGatewaySyncing = true;
                    const url =
                        `/api/v1/finance/invoices/${id}/sync-gateway?school_id=${encodeURIComponent(schoolId)}`;
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    let data = null;
                    try {
                        data = await res.json();
                    } catch (_) {}
                    const msg = (data && (data.message || data.error || (typeof data === 'string' ?
                        data : null))) || (res.ok ? 'Status sincronizado com sucesso' :
                        'Falha ao consultar pagamento no gateway');
                    if (res.ok) {
                        this.message = msg;
                        if (window.alertSystem && typeof window.alertSystem.success === 'function') {
                            window.alertSystem.success(msg);
                        }
                    } else {
                        if (window.alertSystem && typeof window.alertSystem.error === 'function') {
                            window.alertSystem.error(msg);
                        }
                    }
                    let inv = data?.invoice;
                    if (!inv) inv = (this.invoices || []).find(i => i.id === id) || null;
                    if (inv) {
                        this.invoices = (this.invoices || []).map(i => i.id === id ? inv : i);
                        this.invoiceHistoryTarget = inv;
                        const log = this.webhookLog[id] || {};
                        log.alias = String(inv.gateway_alias || log.alias || '').toLowerCase();
                        if (this.hasBoletoData(inv) && !log.boletoGeneratedAt) {
                            log.boletoGeneratedAt = new Date().toISOString();
                        }
                        this.webhookLog[id] = log;
                    }
                } catch (_) {} finally {
                    this.invoiceGatewaySyncing = false;
                }
            },
            computeInvoiceHistory(inv) {
                try {
                    const hist = [];
                    if (!inv) return hist;
                    if (this.invoiceHistoryTarget?.id === inv.id && this.invoiceGatewaySyncing) {
                        hist.push({
                            label: 'Consulta ativa no gateway',
                            at: new Date().toISOString()
                        });
                    }
                    if (inv.created_at) hist.push({
                        label: 'Fatura criada',
                        at: inv.created_at
                    });
                    const id = inv.id;
                    const log = this.webhookLog[id] || {};
                    const aliasStr = log.alias ? ` — ${log.alias}` : '';
                    if (log.requestedAt) hist.push({
                        label: `Solicitação ao gateway${aliasStr} (${log.method || 'boleto'})`,
                        at: log.requestedAt
                    });
                    if (log.gatewayOkAt) hist.push({
                        label: `Webhook confirmado pelo gateway${aliasStr}`,
                        at: log.gatewayOkAt
                    });
                    if (log.gatewayError && log.gatewayError.at) {
                        const msg = String(log.gatewayError.message || 'Erro desconhecido');
                        const short = msg.length > 140 ? (msg.slice(0, 137) + '...') : msg;
                        hist.push({
                            label: `Erro do gateway${aliasStr}: ${short}`,
                            at: log.gatewayError.at
                        });
                    }
                    if (inv.gateway_status) {
                        hist.push({
                            label: `Gateway status: ${String(inv.gateway_status)}`,
                            at: inv.updated_at || inv.created_at || null
                        });
                    }
                    if (inv.gateway_error_code || inv.gateway_error) {
                        const code = inv.gateway_error_code ? String(inv.gateway_error_code) : '-';
                        const msg = inv.gateway_error ? String(inv.gateway_error) : '';
                        hist.push({
                            label: `Erro do gateway${aliasStr} (código: ${code}): ${msg}`,
                            at: inv.updated_at || inv.created_at || null
                        });
                    }
                    const statusLabel = inv.status ? `Status: ${this.statusLabel(inv.status)}` :
                        'Status: —';
                    hist.push({
                        label: statusLabel,
                        at: inv.updated_at || inv.created_at || null
                    });
                    if (this.hasBoletoData(inv)) {
                        const at = log.boletoGeneratedAt || inv.updated_at || null;
                        hist.push({
                            label: 'Boleto/PIX disponível',
                            at
                        });
                    }
                    if (inv.status === 'paid') {
                        hist.push({
                            label: 'Pagamento confirmado',
                            at: inv.updated_at || null
                        });
                    }
                    return hist;
                } catch (_) {
                    return [];
                }
            },
            // Handlers for invoice action modals
            toInputDate(v) {
                try {
                    const d = new Date(v);
                    const yyyy = d.getFullYear();
                    const mm = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                } catch (_) {
                    return '';
                }
            },
            openInvoiceBoleto(inv) {
                this.invoiceBoletoTarget = inv;
                this.invoiceBoletoOpen = true;
            },
            openInvoiceBoletoDirect(inv) {
                if (this.hasBoletoData(inv) && inv.boleto_url) {
                    window.open(inv.boleto_url, '_blank');
                } else {
                    this.openInvoiceBoleto(inv);
                }
            },
            closeInvoiceBoleto() {
                this.invoiceBoletoOpen = false;
                this.invoiceBoletoTarget = null;
            },
            openInvoiceEdit(inv) {
                this.invoiceEditTarget = inv;
                const desc = (inv && inv.description && String(inv.description).trim().length) ?
                    String(inv.description).trim() :
                    this.buildAutoInvoiceDescription(inv);
                this.invoiceEditForm = {
                    due_date: this.toInputDate(inv.due_date),
                    total_cents: inv.total_cents,
                    total_display: this.formatCurrencyBr(inv.total_cents),
                    currency: inv.currency || this.currency,
                    description: desc
                };
                this.invoiceEditErrors = {};
                this.invoiceEditOpen = true;
            },
            closeInvoiceEdit() {
                this.invoiceEditOpen = false;
                this.invoiceEditTarget = null;
            },
            async submitInvoiceEdit() {
                if (!this.invoiceEditTarget) return;
                this.invoiceEditSaving = true;
                this.invoiceEditErrors = {};
                try {
                    const url = `/api/v1/finance/invoices/${this.invoiceEditTarget.id}`;
                    const payload = {
                        school_id: this.schoolId,
                        due_date: this.invoiceEditForm.due_date,
                        total_cents: (typeof this.invoiceEditForm.total_cents === 'number' ? this
                            .invoiceEditForm.total_cents : this.amountToCents(this
                                .invoiceEditForm.total_display)),
                        currency: this.invoiceEditForm.currency,
                        description: this.invoiceEditForm.description
                    };
                    const res = await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.invoiceEditErrors = data.errors || {
                            geral: data.message || 'Falha ao salvar'
                        };
                        return;
                    }
                    const updated = data.invoice || data;
                    const idx = this.invoices.findIndex(i => i.id === updated.id);
                    if (idx >= 0) this.invoices[idx] = updated;
                    this.closeInvoiceEdit();
                } catch (e) {
                    this.invoiceEditErrors = {
                        geral: e.message || 'Erro inesperado'
                    };
                } finally {
                    this.invoiceEditSaving = false;
                }
            },
            syncInvoiceGatewayFor(inv) {
                this.invoiceHistoryTarget = inv;
                return this.syncInvoiceGateway();
            },
            canCancelInvoice(inv) {
                return !!inv && inv.status !== 'paid' && inv.status !== 'canceled';
            },
            openInvoiceCancel(inv) {
                this.invoiceCancelTarget = inv;
                this.invoiceCancelError = null;
                this.invoiceCancelOpen = true;
            },
            closeInvoiceCancel() {
                this.invoiceCancelOpen = false;
                this.invoiceCancelTarget = null;
            },
            async confirmCancelInvoice() {
                if (!this.canCancelInvoice(this.invoiceCancelTarget)) {
                    this.invoiceCancelError = 'Não é possível cancelar esta fatura.';
                    return;
                }
                this.invoiceCancelProcessing = true;
                this.invoiceCancelError = null;
                try {
                    const url = `/api/v1/finance/invoices/${this.invoiceCancelTarget.id}/cancel`;
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            school_id: this.schoolId
                        })
                    });
                    let data = null;
                    try {
                        data = await res.json();
                    } catch (_) {
                        const text = await res.text().catch(() => '');
                        this.invoiceCancelError =
                            `Falha ao cancelar (status ${res.status}). Resposta não-JSON: ${text?.slice(0,200) || 'sem conteúdo'}`;
                        return;
                    }
                    if (!res.ok) {
                        this.invoiceCancelError = data?.message || 'Falha ao cancelar';
                        return;
                    }
                    const updated = data.invoice || this.invoiceCancelTarget;
                    updated.status = 'canceled';
                    const idx = this.invoices.findIndex(i => i.id === updated.id);
                    if (idx >= 0) this.invoices[idx] = updated;
                    this.closeInvoiceCancel();
                } catch (e) {
                    this.invoiceCancelError = e.message || 'Erro inesperado';
                } finally {
                    this.invoiceCancelProcessing = false;
                }
            },
            openManualSettle(inv) {
                this.invoiceManualSettleTarget = inv;
                this.invoiceManualSettleForm = {
                    amount_paid_cents: inv.total_cents,
                    amount_paid_display: this.formatCurrencyBr(inv.total_cents),
                    paid_at: new Date().toISOString().slice(0, 16),
                    method: 'cash',
                    status: 'confirmed',
                    settlement_ref: ''
                };
                this.invoiceManualSettleErrors = {};
                this.invoiceManualSettleOpen = true;
            },
            closeManualSettle() {
                this.invoiceManualSettleOpen = false;
                this.invoiceManualSettleTarget = null;
            },
            async submitManualSettle() {
                if (!this.invoiceManualSettleTarget) return;
                this.invoiceManualSettleSaving = true;
                this.invoiceManualSettleErrors = {};
                try {
                    const url = `/api/v1/finance/payments`;
                    const payload = {
                        school_id: this.schoolId,
                        invoice_id: this.invoiceManualSettleTarget.id,
                        amount_paid_cents: parseInt(this.normalizeDecimal(this
                            .invoiceManualSettleForm.amount_paid_cents)) || 0,
                        paid_at: this.invoiceManualSettleForm.paid_at,
                        method: this.invoiceManualSettleForm.method,
                        status: this.invoiceManualSettleForm.status,
                        settlement_ref: this.invoiceManualSettleForm.settlement_ref,
                        currency: this.invoiceManualSettleTarget.currency || this.currency
                    };
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });
                    let data = null;
                    try {
                        data = await res.json();
                    } catch (_) {
                        const text = await res.text().catch(() => '');
                        this.invoiceManualSettleErrors = {
                            geral: `Falha ao liquidar (status ${res.status}). Resposta não-JSON: ${text?.slice(0,200) || 'sem conteúdo'}`
                        };
                        return;
                    }
                    if (!res.ok) {
                        this.invoiceManualSettleErrors = data.errors || {
                            geral: data.message || 'Falha ao liquidar'
                        };
                        return;
                    }
                    const idx = this.invoices.findIndex(i => i.id === this.invoiceManualSettleTarget
                    .id);
                    if (idx >= 0) {
                        this.invoices[idx].status = 'paid';
                        this.invoices[idx].gateway_status = 'manual';
                    }
                    this.closeManualSettle();
                } catch (e) {
                    this.invoiceManualSettleErrors = {
                        geral: e.message || 'Erro inesperado'
                    };
                } finally {
                    this.invoiceManualSettleSaving = false;
                }
            },
            // Ações de assinatura (modais)
            editOpen: false,
            editTarget: null,
            editForm: {
                description: '',
                day_of_month: null,
                discount_percent: 0,
                items: [],
                start_at: '',
                end_at: '',
                last_billed_at: ''
            },
            async openEdit(sub) {
                this.editTarget = sub;
                // Determinar nome do item e periodicidade via plano, se existir
                let itemName = 'Mensalidade';
                let perLabel = 'Mensal';
                try {
                    if (sub.billing_plan_id) {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '';
                        const resp = await fetch(
                            `/api/v1/finance/plans/${encodeURIComponent(sub.billing_plan_id)}?school_id=${encodeURIComponent(this.schoolId)}`, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                credentials: 'same-origin'
                            });
                        const data = await resp.json().catch(() => null);
                        if (resp.ok && data) {
                            itemName = (data.name || itemName);
                            const p = data.periodicity || 'monthly';
                            perLabel = p === 'monthly' ? 'Mensal' : (p === 'bimonthly' ? 'Bimestral' : (
                                p === 'annual' ? 'Anual' : perLabel));
                        }
                    }
                } catch (_) {}
                const amtStr = ((sub.amount_cents || 0) / 100).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                const aluno = this.currentStudentName();
                const autoDesc = `${perLabel} - ${itemName}${aluno ? ` — Aluno: ${aluno}` : ''}`;
                const start = this.normalizeYmd(sub.start_at);
                const end = this.normalizeYmd(sub.end_at);
                const last = this.latestInvoiceDateFor(sub.id);
                this.editForm = {
                    description: (sub.description && sub.description.trim()) ? sub.description
                        .trim() : autoDesc,
                    day_of_month: sub.day_of_month || null,
                    discount_percent: sub.discount_percent || 0,
                    items: [{
                        name: itemName,
                        qty: 1,
                        amount: amtStr
                    }],
                    start_at: start,
                    end_at: end,
                    last_billed_at: last
                };
                this.editOpen = true;
            },
            closeEdit() {
                this.editOpen = false;
                this.editTarget = null;
                this.editForm = {
                    description: '',
                    day_of_month: null,
                    discount_percent: 0,
                    items: [],
                    start_at: '',
                    end_at: '',
                    last_billed_at: ''
                };
            },
            async confirmEdit() {
                if (!this.editTarget) {
                    this.closeEdit();
                    return;
                }
                this.loading = true;
                this.error = null;
                this.message = null;
                try {
                    const total = (this.editForm.items || []).reduce((s, it) => {
                        const qty = parseInt(it.qty) || 1;
                        return s + (this.amountToCents(it.amount) * qty);
                    }, 0);
                    // Validação simples: fim não pode ser antes do início
                    if (this.editForm.start_at && this.editForm.end_at) {
                        try {
                            const s = new Date(this.editForm.start_at + 'T00:00:00');
                            const e = new Date(this.editForm.end_at + 'T00:00:00');
                            if (e.getTime() < s.getTime()) {
                                throw new Error('Data de fim não pode ser anterior à data de início');
                            }
                        } catch (_) {}
                    }

                    const body = {
                        description: (this.editForm.description || this.editTarget.description ||
                            ''),
                        day_of_month: (this.editForm.day_of_month ? parseInt(this.editForm
                            .day_of_month) : null),
                        discount_percent: Math.round(parseFloat(this.editForm.discount_percent ||
                            0)),
                        amount_cents: total,
                        start_at: (this.editForm.start_at || null),
                        end_at: (this.editForm.end_at || null)
                    };
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const resp = await fetch(
                        `/api/v1/finance/subscriptions/${encodeURIComponent(this.editTarget.id)}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                ...body,
                                school_id: this.schoolId
                            })
                        });
                    const data = await resp.json().catch(() => null);
                    if (!resp.ok) throw new Error((data && (data.message || JSON.stringify(data
                        .errors))) || 'Falha ao atualizar assinatura');
                    this.message = 'Assinatura atualizada';
                    this.updateSubscriptionLocal(this.editTarget.id, body);
                    await this.loadHistory();
                } catch (err) {
                    this.error = err.message || 'Erro ao atualizar assinatura';
                } finally {
                    this.loading = false;
                    this.closeEdit();
                }
            },
            inactivateOpen: false,
            inactivateTarget: null,
            openInactivate(sub) {
                this.inactivateTarget = sub;
                this.inactivateOpen = true;
            },
            closeInactivate() {
                this.inactivateOpen = false;
                this.inactivateTarget = null;
            },
            async confirmInactivate() {
                if (!this.inactivateTarget) {
                    this.closeInactivate();
                    return;
                }
                this.loading = true;
                this.error = null;
                this.message = null;
                try {
                    const today = new Date();
                    const end =
                        `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;
                    const body = {
                        status: 'canceled',
                        end_at: end
                    };
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const resp = await fetch(
                        `/api/v1/finance/subscriptions/${encodeURIComponent(this.inactivateTarget.id)}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                ...body,
                                school_id: this.schoolId
                            })
                        });
                    const data = await resp.json().catch(() => null);
                    if (!resp.ok) throw new Error((data && (data.message || JSON.stringify(data
                        .errors))) || 'Falha ao inativar assinatura');
                    this.message = 'Assinatura inativada';
                    this.updateSubscriptionLocal(this.inactivateTarget.id, {
                        ...body,
                        active: false
                    });
                    await this.loadHistory();
                } catch (err) {
                    this.error = err.message || 'Erro ao inativar assinatura';
                } finally {
                    this.loading = false;
                    this.closeInactivate();
                }
            },
            pauseOpen: false,
            pauseTarget: null,
            pauseUntil: '',
            openPause(sub) {
                this.pauseTarget = sub;
                this.pauseUntil = '';
                this.pauseOpen = true;
            },
            closePause() {
                this.pauseOpen = false;
                this.pauseTarget = null;
                this.pauseUntil = '';
            },
            async confirmPause() {
                if (!this.pauseTarget) {
                    this.closePause();
                    return;
                }
                this.loading = true;
                this.error = null;
                this.message = null;
                try {
                    const until = (this.pauseUntil && this.pauseUntil.length) ? this.pauseUntil : null;
                    const currentNotes = this.pauseTarget.notes || '';
                    const notes = this.buildPauseNotes(currentNotes, until);
                    const body = {
                        status: 'paused',
                        notes
                    };
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const resp = await fetch(
                        `/api/v1/finance/subscriptions/${encodeURIComponent(this.pauseTarget.id)}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                ...body,
                                school_id: this.schoolId
                            })
                        });
                    const data = await resp.json().catch(() => null);
                    if (!resp.ok) throw new Error((data && (data.message || JSON.stringify(data
                        .errors))) || 'Falha ao pausar assinatura');
                    this.message = 'Assinatura pausada';
                    const localChanges = {
                        status: 'paused',
                        notes
                    };
                    this.updateSubscriptionLocal(this.pauseTarget.id, localChanges);
                    await this.loadHistory();
                } catch (err) {
                    this.error = err.message || 'Erro ao pausar assinatura';
                } finally {
                    this.loading = false;
                    this.closePause();
                }
            },
            activateOpen: false,
            activateTarget: null,
            openActivate(sub) {
                this.activateTarget = sub;
                this.activateOpen = true;
            },
            closeActivate() {
                this.activateOpen = false;
                this.activateTarget = null;
            },
            async confirmActivate() {
                if (!this.activateTarget) {
                    this.closeActivate();
                    return;
                }
                this.loading = true;
                this.error = null;
                this.message = null;
                try {
                    const cleanedNotes = this.cleanPauseNotes(this.activateTarget.notes || '');
                    const body = {
                        status: 'active',
                        notes: cleanedNotes
                    };
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') || '';
                    const resp = await fetch(
                        `/api/v1/finance/subscriptions/${encodeURIComponent(this.activateTarget.id)}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                ...body,
                                school_id: this.schoolId
                            })
                        });
                    const data = await resp.json().catch(() => null);
                    if (!resp.ok) throw new Error((data && (data.message || JSON.stringify(data
                        .errors))) || 'Falha ao ativar assinatura');
                    this.message = 'Assinatura ativada';
                    this.updateSubscriptionLocal(this.activateTarget.id, {
                        status: 'active',
                        notes: cleanedNotes,
                        active: true
                    });
                    await this.loadHistory();
                } catch (err) {
                    this.error = err.message || 'Erro ao ativar assinatura';
                } finally {
                    this.loading = false;
                    this.closeActivate();
                }
            },
            updateSubscriptionLocal(id, changes) {
                try {
                    const idx = (this.subscriptions || []).findIndex(s => s.id === id);
                    if (idx >= 0) {
                        this.subscriptions.splice(idx, 1, {
                            ...this.subscriptions[idx],
                            ...changes
                        });
                    }
                } catch (_) {}
            },
        });
        window.guardianBilling = factory;
        document.addEventListener('alpine:init', () => {
            try {
                Alpine.data('guardianBilling', factory);
                if (typeof Alpine.store === 'function') {
                    Alpine.store('billing', {
                        createSubscriptionOpen: false
                    });
                }
            } catch (_) {}
        });
        // Removed eager Alpine.initTree to prevent double initialization
    })();
</script>
