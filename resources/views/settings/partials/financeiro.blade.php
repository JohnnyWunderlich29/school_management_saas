<div class="space-y-6">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-4.418 0-8 1.79-8 4s3.582 4 8 4 8-1.79 8-4-3.582-4-8-4zm0 8c-4.418 0-8 1.79-8 4v2h16v-2c0-2.21-3.582-4-8-4zM4 8V6c0-2.21 3.582-4 8-4s8 1.79 8 4v2" />
                </svg>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Financeiro</h2>
                    <p class="text-sm text-gray-600 mt-1">Gerencie configurações e gateways</p>
                </div>
            </div>
        </div>
        <div class="border-b border-gray-200">
            <!-- Mobile Sub-Tab Selector -->
            <div class="sm:hidden px-4 py-3">
                <label for="fin-tabs-mobile" class="sr-only">Selecionar Sub-aba</label>
                <x-select name="fin-tabs-mobile" id="fin-tabs-mobile" onchange="showFinanceTab(this.value)"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="tab-fin-settings">Formas de Cobrança</option>
                    <option value="tab-fin-gateways">Gateways</option>
                    <option value="tab-fin-dunning">Envio de Cobranças</option>
                    <option value="tab-fin-automation">Automação</option>
                </x-select>
            </div>

            <!-- Desktop Sub-Tabs -->
            <nav class="hidden sm:flex -mb-px space-x-4 sm:space-x-8 px-2 sm:px-6 overflow-x-auto" aria-label="Tabs">
                <button onclick="showFinanceTab('tab-fin-settings')" id="fin-settings-tab"
                    class="fintab-btn whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">Formas de
                    Cobrança</button>
                <button onclick="showFinanceTab('tab-fin-gateways')" id="fin-gateways-tab"
                    class="fintab-btn whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">Gateways</button>
                <button onclick="showFinanceTab('tab-fin-dunning')" id="fin-dunning-tab"
                    class="fintab-btn whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">Envio de
                    Cobranças</button>
                <button onclick="showFinanceTab('tab-fin-automation')" id="fin-automation-tab"
                    class="fintab-btn whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm text-indigo-600">Automação</button>
            </nav>
        </div>
    </div>

    <!-- Tab: Formas de Cobrança -->
    <div id="tab-fin-settings" class="fintab-content hidden" x-data="chargeSettings()" x-init="load();
    window.addEventListener('charge-methods:updated', () => load())">
        <div class="">
            <div class="mb-6 flex flex-col items-start justify-between md:flex-row">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Formas de Cobrança</h3>
                    <p class="mt-1 text-sm text-gray-600">Selecione e edite taxas por método</p>
                </div>
                <div class="flex flex-col mt-2 items-center gap-2 md:flex-row">
                    <x-button color="primary" type="button" onclick="openModal('create-charge-modal')"
                        class="w-full sm:mt-2">
                        <i class="fas fa-plus mr-1"></i> Nova Forma
                    </x-button>
                    <div class="flex">
                        <x-select name="filter_alias" x-model="filterAlias" class="text-sm">
                            <option value="">Todos os gateways</option>
                            <template x-for="g in (gwNames||[])" :key="g.alias">
                                <option :value="g.alias" x-text="(g.name||g.alias)"></option>
                            </template>
                        </x-select>
                        <x-input name="search_text" placeholder="Buscar método (pix, boleto, cartão)"
                            x-model="searchText" />
                    </div>
                </div>
            </div>

            <template x-if="error">
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded" x-text="error"></div>
            </template>

            <div x-show="loading" class="text-sm text-gray-600">Carregando formas…</div>

            <div x-show="!loading">
                <template x-for="(list, alias) in grouped()" :key="alias">
                    <div class="mb-6 border rounded-lg">
                        <div class="px-4 py-3 border-b flex items-center justify-between">
                            <div>
                                <div class="text-base font-semibold text-gray-900" x-text="gwNameFor(alias)"></div>
                                <div class="text-xs text-gray-500" x-text="alias.toUpperCase()"></div>
                            </div>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <template x-for="cm in list" :key="cm.id">
                                <div class="border rounded-lg p-4 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="font-medium text-gray-900" x-text="iconFor(cm.method)"></div>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="cm.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                x-text="cm.active ? 'Ativa' : 'Inativa'"></span>
                                        </div>
                                        <div class="text-xs text-gray-600 space-y-1">
                                            <div><span class="text-gray-500">Multa:</span> <span
                                                    x-text="(cm.penalty_policy && cm.penalty_policy.fine_percent) ? (cm.penalty_policy.fine_percent + '%') : '-' "></span>
                                            </div>
                                            <div><span class="text-gray-500">Juros diário:</span> <span
                                                    x-text="(cm.penalty_policy && cm.penalty_policy.daily_interest_percent) ? (cm.penalty_policy.daily_interest_percent + '%') : '-' "></span>
                                            </div>
                                            <div><span class="text-gray-500">Carência:</span> <span
                                                    x-text="(cm.penalty_policy && cm.penalty_policy.grace_days) ? (cm.penalty_policy.grace_days + ' dia(s)') : '-' "></span>
                                            </div>
                                            <div x-show="cm.penalty_policy && cm.penalty_policy.max_interest_percent">
                                                <span class="text-gray-500">Juros máx.:</span> <span
                                                    x-text="cm.penalty_policy.max_interest_percent + '%'"></span>
                                            </div>
                                            <div x-show="cm.penalty_policy && cm.penalty_policy.is_default"><span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Padrão</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 text-right">
                                        <x-button color="primary" type="button" @click="openEdit(cm)">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </x-button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="Object.keys(grouped()).length === 0 && !loading">
                    <div class="text-sm text-gray-600">Nenhuma forma cadastrada. Clique em "Nova Forma" para criar.
                    </div>
                </template>
            </div>

            <!-- Modal de Edição da Forma -->
            <!-- Modal de Edição da Forma -->
            <div x-show="isEditOpen"
                class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div
                    class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                    <template x-if="editing">
                        <div class="mt-3">
                            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200">
                                <h3 class="text-lg font-medium text-gray-900">Editar Forma — <span
                                        x-text="gwNameFor(editing && editing.gateway_alias)"></span></h3>
                                <button type="button" class="text-gray-400 hover:text-gray-600"
                                    @click="closeEdit()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <form class="space-y-4" @submit.prevent="submitEdit($event)">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Ativa</label>
                                        <input type="checkbox" x-model="editing.active"
                                            class="rounded border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Padrão do
                                            Sistema</label>
                                        <input type="checkbox" x-model="editing.penalty_policy.is_default"
                                            class="rounded border-gray-300">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Multa (%)</label>
                                        <x-input type="number" step="0.01" min="0" max="100"
                                            x-model.number="editing.penalty_policy.fine_percent"
                                            inputmode="decimal"></x-input>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Juros diário (%)</label>
                                        <x-input type="number" step="0.01" min="0" max="100"
                                            x-model.number="editing.penalty_policy.daily_interest_percent"
                                            inputmode="decimal" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Carência (dias)</label>
                                        <x-input type="number" min="0" max="365"
                                            x-model.number="editing.penalty_policy.grace_days" inputmode="numeric" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Juros máximo (%)</label>
                                        <x-input type="number" step="0.01" min="0" max="100"
                                            x-model.number="editing.penalty_policy.max_interest_percent"
                                            inputmode="decimal" />
                                    </div>
                                </div>
                                <template x-if="error">
                                    <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2"
                                        x-text="error"></div>
                                </template>
                                <div class="flex items-center justify-end gap-3 mt-6">
                                    <x-button type="button" color="secondary"
                                        @click="closeEdit()">Cancelar</x-button>
                                    <x-button type="submit" color="primary" x-bind:disabled="savingEdit">
                                        <i class="fas fa-save mr-1"></i> Salvar
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab: Automação -->
    <div id="tab-fin-automation" class="fintab-content hidden">
        <form method="POST" action="{{ route('finance.settings.save') }}">
            @csrf
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Automação de Faturamento</h3>
                            <p class="text-sm text-gray-600">Gere faturas automaticamente antes do vencimento</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="automation_active" value="1"
                                @checked(isset($automation) && $automation->active) class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Ativo</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="automation_days_advance" class="block text-sm font-medium text-gray-700">Gerar
                                fatura com antecedência (dias)</label>
                            <input type="number" min="1" max="60" id="automation_days_advance"
                                name="automation_days_advance"
                                value="{{ old('automation_days_advance', $automation->days_advance ?? 5) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Número de dias antes do vencimento para gerar a
                                cobrança
                                automaticamente.</p>
                        </div>
                        <div class="flex items-center">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="automation_consolidate" value="1"
                                    @checked(isset($automation) && $automation->consolidate_default)
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 font-medium">Consolidar faturas por pagador
                                    (Padrão)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-button type="submit" color="primary">
                        <i class="fas fa-save mr-1"></i> Salvar Automação
                    </x-button>
                </div>
            </div>
        </form>
    </div>

    <div id="tab-fin-gateways" class="fintab-content hidden">
        <div class="">
            <div class="mb-6 flex flex-col items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Gateways Financeiros</h3>
                    <p class="mt-1 text-sm text-gray-600">Integrações, webhook e credenciais da escola atual</p>
                </div>
                <div class="flex flex-col mt-2 items-center gap-2">
                    @isset($financeEnv)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $financeEnv === 'production' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            Ambiente: {{ strtoupper($financeEnv) }}
                        </span>
                    @endisset
                    <x-button color="primary" type="button" onclick="openModal('create-gateway-modal')"
                        class="w-full mt-2">
                        <i class="fas fa-plus mr-1"></i> Novo Gateway
                    </x-button>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Lista desktop -->
            <div class="mb-6 overflow-x-auto hidden md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Gateway</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Indicadores</th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($gateways as $gw)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">{{ $gw->alias }}</div>
                                    <div class="text-sm text-gray-500">{{ $gw->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $gw->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $gw->active ? 'Ativo' : 'Inativo' }}
                                        </span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $gw->webhook_secret ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $gw->webhook_secret ? 'Webhook configurado' : 'Webhook não definido' }}
                                        </span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ !empty($gw->credentials) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ !empty($gw->credentials) ? 'Credenciais setadas' : 'Credenciais ausentes' }}
                                        </span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($gw->environment ?? 'production') === 'production' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($gw->environment ?? 'production') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <x-button color="primary" type="button"
                                        onclick="openModal('edit-gateway-{{ $gw->id }}')">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </x-button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-4" colspan="3">Nenhum gateway cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Lista mobile -->
            <div class="md:hidden space-y-3">
                @forelse ($gateways as $gw)
                    <div class="flex flex-col border rounded-lg p-4">
                        <div class="flex flex-col items-start justify-between md:flex-row">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $gw->alias }}</div>
                                <div class="text-sm text-gray-500">{{ $gw->name }}</div>
                            </div>
                            <x-button color="primary" type="button"
                                onclick="openModal('edit-gateway-{{ $gw->id }}')" class="w-full mt-2">
                                <i class="fas fa-edit mr-1"></i> Editar
                            </x-button>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $gw->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $gw->active ? 'Ativo' : 'Inativo' }}
                            </span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $gw->webhook_secret ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700' }}">
                                {{ $gw->webhook_secret ? 'Webhook configurado' : 'Webhook não definido' }}
                            </span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ !empty($gw->credentials) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">
                                {{ !empty($gw->credentials) ? 'Credenciais setadas' : 'Credenciais ausentes' }}
                            </span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($gw->environment ?? 'production') === 'production' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($gw->environment ?? 'production') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-600">Nenhum gateway cadastrado.</div>
                @endforelse
            </div>

            <!-- Modal: Criar Gateway (x-modal) -->
            <x-modal name="create-gateway-modal" title="Novo Gateway">
                <form id="create-gateway-form" method="POST" action="{{ route('finance.gateways.create') }}"
                    class="space-y-4" x-data="createGatewayForm()" @submit.prevent="submit($event)">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input name="alias" id="alias" label="Alias" type="text" required
                                placeholder="ex: asaas" />
                        </div>
                        <div>
                            <x-input name="name" id="name" label="Nome" type="text"
                                placeholder="ex: Asaas Pix/Boleto" />
                        </div>
                        <div>
                            <x-select name="environment" id="environment" label="Ambiente" :options="['homolog' => 'Homologação', 'production' => 'Produção']"
                                selected="production" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-select name="active" id="active" label="Ativo" :options="['1' => 'Sim', '0' => 'Não']"
                                selected="1" />
                        </div>
                        <div>
                            <x-input name="webhook_secret" id="webhook_secret" label="Webhook Secret" type="text"
                                placeholder="Segredo do webhook (opcional)" />
                        </div>
                    </div>

                    <!-- Provedor e Credenciais (UX guiada) -->
                    <div x-data="gatewayCredentials()" class="space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-select name="provider_ui" label="Provedor" x-model="provider" :options="['asaas' => 'Asaas', 'nupay' => 'NuPay']"
                                    selected="asaas" />
                            </div>
                        </div>

                        <template x-if="provider === 'asaas'">
                            <div class="space-y-2">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <x-input name="asaas_api_key_ui" label="API Key" type="password"
                                            x-model="asaas.api_key" x-ref="asaas_api_key_input" placeholder="***"
                                            autocomplete="off" required class="pr-10">
                                            <button type="button"
                                                class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700"
                                                @click="$refs.asaas_api_key_input.type = $refs.asaas_api_key_input.type === 'password' ? 'text' : 'password'">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </x-input>
                                    </div>
                                    <div>
                                        <x-select name="asaas_environment_ui" label="Ambiente"
                                            x-model="asaas.environment" :options="['production' => 'Produção', 'sandbox' => 'Sandbox']" selected="production" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <x-input name="asaas_webhook_token_ui" label="Webhook Token (opcional)"
                                            type="text" x-model="asaas.webhook_token"
                                            placeholder="Token do webhook para validação" />
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Instruções Webhook (Asaas): cadastre a
                                    URL <code>{{ url('/api/v1/webhooks/gateway') }}/<span
                                            class='text-gray-700 font-semibold'>asaas</span></code> com método
                                    POST; autentique via token acima se desejar.</p>

                                <div class="flex items-center gap-2">
                                    <x-button type="button" color="secondary" x-data="{}"
                                        @click="testCredentials()" x-bind:disabled="test.loading">
                                        <i class="fas fa-vial mr-1"></i>
                                        <span x-show="!test.loading">Testar credenciais</span>
                                        <span x-show="test.loading">Testando...</span>
                                    </x-button>
                                    <template x-if="test.ok === true">
                                        <span class="text-green-700 text-sm" x-text="test.message"></span>
                                    </template>
                                    <template x-if="test.ok === false">
                                        <span class="text-red-700 text-sm" x-text="test.message"></span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="provider === 'nupay'">
                            <div class="space-y-2">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <x-input name="nupay_api_key_ui" label="API Key" type="password"
                                            x-model="nupay.api_key" x-ref="nupay_api_key_input" placeholder="***"
                                            autocomplete="off" required class="pr-10">
                                            <button type="button"
                                                class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700"
                                                @click="$refs.nupay_api_key_input.type = $refs.nupay_api_key_input.type === 'password' ? 'text' : 'password'">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </x-input>
                                    </div>
                                    <div>
                                        <x-select name="nupay_environment_ui" label="Ambiente"
                                            x-model="nupay.environment" :options="['production' => 'Produção', 'sandbox' => 'Sandbox']" selected="production" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="md:col-span-2">
                                        <x-input name="nupay_merchant_id_ui" label="Merchant ID (opcional)"
                                            type="text" x-model="nupay.merchant_id"
                                            placeholder="Identificador do vendedor/merchant" />
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Instruções Webhook (NuPay): cadastre a
                                    URL <code>{{ url('/api/v1/webhooks/gateway') }}/<span
                                            class='text-gray-700 font-semibold'>nupay</span></code> com método
                                    POST. Assinatura será validada conforme configuração.</p>
                            </div>
                        </template>

                        <!-- Credenciais como JSON (hidden) para envio -->
                        <input type="hidden" name="credentials_json" x-bind:value="computeJson()" />
                    </div>
                    <template x-if="error">
                        <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2"
                            x-text="error"></div>
                    </template>
                    <template x-if="msg">
                        <div class="text-sm text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2"
                            x-text="msg"></div>
                    </template>
                </form>
                <x-slot name="footer">
                    <x-button type="button" color="secondary"
                        onclick="closeModal('create-gateway-modal')">Cancelar</x-button>
                    <x-button type="submit" color="primary" x-bind:disabled="saving" form="create-gateway-form">
                        <i class="fas fa-plus mr-1"></i> Criar Gateway
                    </x-button>
                </x-slot>
            </x-modal>

            <!-- Modais: Editar Gateway (x-modal) -->
            @foreach ($gateways as $gw)
                <x-modal name="edit-gateway-{{ $gw->id }}" title="Editar Gateway: {{ $gw->alias }}">
                    <form id="edit-gateway-form-{{ $gw->id }}" method="POST"
                        action="{{ route('finance.gateways.update', ['id' => $gw->id]) }}" class="space-y-4"
                        x-data="editGatewayForm('{{ $gw->id }}')" @submit.prevent="submit($event)">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input name="name" id="name_{{ $gw->id }}" label="Nome" type="text"
                                    :value="old('name', $gw->name)" />
                            </div>
                            <div>
                                <x-select name="environment" id="environment_{{ $gw->id }}" label="Ambiente"
                                    :options="['homolog' => 'Homologação', 'production' => 'Produção']" selected="{{ $gw->environment }}"
                                    help="Eventos de ambiente diferente são ignorados."></x-select>
                            </div>
                            <div>
                                <x-select name="active" label="Ativo" :options="['1' => 'Sim', '0' => 'Não']"
                                    selected="{{ $gw->active ? '1' : '0' }}"></x-select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input name="webhook_secret" id="webhook_secret_{{ $gw->id }}"
                                    label="Webhook Secret" type="text" placeholder="Atualizar segredo (opcional)"
                                    help="Não exibimos o segredo atual por segurança." />
                            </div>
                        </div>

                        <!-- Provedor e Credenciais (UX guiada) -->
                        <div x-data="editGatewayCredentials('{{ $gw->id }}', '{{ $gw->credentials['api_key'] ?? '' }}', '{{ $gw->environment }}', '{{ $gw->alias }}', @json($gw->credentials))" class="space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-select name="provider_ui" label="Provedor" x-model="provider"
                                        :options="['asaas' => 'Asaas', 'nupay' => 'NuPay']" selected="asaas"></x-select>
                                </div>
                            </div>

                            <template x-if="provider === 'asaas'">
                                <div class="space-y-2">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="md:col-span-2">
                                            <x-input name="asaas_api_key_ui_{{ $gw->id }}" label="API Key"
                                                type="password" x-model="asaas.api_key"
                                                x-ref="asaas_api_key_input_{{ $gw->id }}" placeholder="***"
                                                autocomplete="off" required class="pr-10">
                                                <button type="button"
                                                    class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700"
                                                    @click="$refs.asaas_api_key_input_{{ $gw->id }}.type = $refs.asaas_api_key_input_{{ $gw->id }}.type === 'password' ? 'text' : 'password'">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </x-input>
                                        </div>
                                        <div>
                                            <x-select name="asaas_environment_ui_{{ $gw->id }}"
                                                label="Ambiente" x-model="asaas.environment" :options="[
                                                    'production' => 'Produção',
                                                    'sandbox' => 'Sandbox',
                                                ]"
                                                selected="{{ $gw->environment === 'homolog' ? 'sandbox' : 'production' }}" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="md:col-span-2">
                                            <x-input name="asaas_webhook_token_ui_{{ $gw->id }}"
                                                label="Webhook Token (opcional)" type="text"
                                                x-model="asaas.webhook_token"
                                                placeholder="Token do webhook para validação" />
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Instruções Webhook (Asaas):
                                        cadastre a URL <code>{{ url('/api/v1/webhooks/gateway') }}/<span
                                                class='text-gray-700 font-semibold'>{{ $gw->alias }}</span></code>
                                        com método POST; autentique via token acima se desejar.</p>

                                    <div class="flex items-center gap-2">
                                        <x-button type="button" color="secondary" x-data="{}"
                                            @click="testCredentials()" x-bind:disabled="test.loading">
                                            <i class="fas fa-vial mr-1"></i>
                                            <span x-show="!test.loading">Testar credenciais</span>
                                            <span x-show="test.loading">Testando...</span>
                                        </x-button>
                                        <template x-if="test.ok === true">
                                            <span class="text-green-700 text-sm" x-text="test.message"></span>
                                        </template>
                                        <template x-if="test.ok === false">
                                            <span class="text-red-700 text-sm" x-text="test.message"></span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="provider === 'nupay'">
                                <div class="space-y-2">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="md:col-span-2">
                                            <x-input name="nupay_api_key_ui_{{ $gw->id }}" label="API Key"
                                                type="password" x-model="nupay.api_key"
                                                x-ref="nupay_api_key_input_{{ $gw->id }}" placeholder="***"
                                                autocomplete="off" required class="pr-10">
                                                <button type="button"
                                                    class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700"
                                                    @click="$refs.nupay_api_key_input_{{ $gw->id }}.type = $refs.nupay_api_key_input_{{ $gw->id }}.type === 'password' ? 'text' : 'password'">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </x-input>
                                        </div>
                                        <div>
                                            <x-select name="nupay_environment_ui_{{ $gw->id }}"
                                                label="Ambiente" x-model="nupay.environment" :options="[
                                                    'production' => 'Produção',
                                                    'sandbox' => 'Sandbox',
                                                ]"
                                                selected="{{ $gw->environment === 'homolog' ? 'sandbox' : 'production' }}" />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="md:col-span-2">
                                            <x-input name="nupay_merchant_id_ui_{{ $gw->id }}"
                                                label="Merchant ID (opcional)" type="text"
                                                x-model="nupay.merchant_id"
                                                placeholder="Identificador do vendedor/merchant" />
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Instruções Webhook (NuPay):
                                        cadastre a URL <code>{{ url('/api/v1/webhooks/gateway') }}/<span
                                                class='text-gray-700 font-semibold'>{{ $gw->alias }}</span></code>
                                        com método POST.</p>

                                    <div class="flex items-center gap-2">
                                        <x-button type="button" color="secondary" x-data="{}"
                                            @click="testCredentials()" x-bind:disabled="test.loading">
                                            <i class="fas fa-vial mr-1"></i>
                                            <span x-show="!test.loading">Testar credenciais</span>
                                            <span x-show="test.loading">Testando...</span>
                                        </x-button>
                                        <template x-if="test.ok === true">
                                            <span class="text-green-700 text-sm" x-text="test.message"></span>
                                        </template>
                                        <template x-if="test.ok === false">
                                            <span class="text-red-700 text-sm" x-text="test.message"></span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Credenciais como JSON (hidden) para envio -->
                            <input type="hidden" name="credentials_json" x-bind:value="computeJson()" />
                        </div>
                        <template x-if="error">
                            <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2"
                                x-text="error"></div>
                        </template>
                        <template x-if="msg">
                            <div class="text-sm text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2"
                                x-text="msg"></div>
                        </template>
                    </form>
                    <x-slot name="footer">
                        <x-button type="button" color="secondary"
                            onclick="closeModal('edit-gateway-{{ $gw->id }}')">Cancelar</x-button>
                        <x-button type="submit" color="primary" x-bind:disabled="saving"
                            form="edit-gateway-form-{{ $gw->id }}">
                            <i class="fas fa-save mr-1"></i> Salvar alterações
                        </x-button>
                    </x-slot>
                </x-modal>
            @endforeach
        </div>
    </div>
</div>




<!-- Modal: Nova Forma de Cobrança -->
<div id="create-charge-modal"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Nova Forma de Cobrança</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600"
                    onclick="closeModal('create-charge-modal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="create-charge-form" method="POST" action="{{ url('/api/v1/finance/charge-methods') }}"
                class="space-y-4" x-data="{
                    saving: false,
                    error: null,
                    msg: null,
                    active: true,
                    gatewayAlias: '',
                    schoolId: @json(optional(Auth::user())->escola_id ?? (optional(Auth::user())->school_id ?? session('escola_atual'))),
                    supportedMap: {
                        'asaas': ['boleto', 'pix', 'credit_card'],
                        'nubank': ['boleto', 'pix'],
                        'caixa': ['boleto']
                    },
                    normalizeDecimal(v) { if (v === null || v === undefined) return v; return ('' + v).replace(',', '.'); },
                    selectedMethod: '',
                    multi: false,
                    selectedMethods: [],
                    policy: {
                        fine_percent: '{{ data_get($settings->penalty_policy, 'fine_percent') ?? '' }}',
                        daily_interest_percent: '{{ data_get($settings->penalty_policy, 'daily_interest_percent') ?? '' }}',
                        grace_days: '{{ data_get($settings->penalty_policy, 'grace_days') ?? '' }}',
                        max_interest_percent: '{{ data_get($settings->penalty_policy, 'max_interest_percent') ?? '' }}',
                        is_default: false
                    },
                    onGatewayChange() {
                        const alias = (this.gatewayAlias || '').toLowerCase();
                        const defaults = this.supportedMap[alias] || ['boleto', 'pix', 'credit_card', 'debit_card'];
                        // auto-select first method if none selected
                        if (!this.selectedMethod && defaults.length > 0) {
                            this.selectedMethod = defaults[0];
                        }
                        // reset multi selection when gateway changes
                        this.selectedMethods = [];
                    },
                    methodsForAlias() {
                        const alias = (this.gatewayAlias || '').toLowerCase();
                        return this.supportedMap[alias] || ['boleto', 'pix', 'credit_card', 'debit_card'];
                    },
                    async unsetOtherDefaultsCreated(createdId) {
                        try {
                            const sid = this.schoolId;
                            if (!sid) return;
                            let url = '{{ url('/api/v1/finance/charge-methods') }}' + '?school_id=' + encodeURIComponent(sid);
                            const respList = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                            let dataList = null;
                            try { dataList = await respList.json(); } catch (_) { dataList = null; }
                            const list = (dataList && dataList.data) ? dataList.data : (Array.isArray(dataList) ? dataList : []);
                            const headers = { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' };
                            const ops = [];
                            for (const m of list) {
                                if (!m) continue;
                                if (createdId && m.id === createdId) continue;
                                const isDef = !!(m.penalty_policy && m.penalty_policy.is_default);
                                if (!isDef) continue;
                                const fd = new FormData();
                                fd.append('school_id', m.school_id || sid);
                                fd.append('active', m && m.active ? '1' : '0');
                                if (m && m.gateway_alias) fd.append('gateway_alias', m.gateway_alias);
                                if (m && m.method) fd.append('method', m.method);
                                const pp = m.penalty_policy || {};
                                if (pp.fine_percent !== undefined && pp.fine_percent !== null && pp.fine_percent !== '') fd.append('penalty_policy[fine_percent]', this.normalizeDecimal(pp.fine_percent));
                                if (pp.daily_interest_percent !== undefined && pp.daily_interest_percent !== null && pp.daily_interest_percent !== '') fd.append('penalty_policy[daily_interest_percent]', this.normalizeDecimal(pp.daily_interest_percent));
                                if (pp.grace_days !== undefined && pp.grace_days !== null && pp.grace_days !== '') fd.append('penalty_policy[grace_days]', pp.grace_days);
                                if (pp.max_interest_percent !== undefined && pp.max_interest_percent !== null && pp.max_interest_percent !== '') fd.append('penalty_policy[max_interest_percent]', this.normalizeDecimal(pp.max_interest_percent));
                                fd.append('penalty_policy[is_default]', '0');
                                fd.append('_method', 'PUT');
                                const u = '{{ url('/api/v1/finance/charge-methods') }}' + '/' + encodeURIComponent(m.id);
                                ops.push(fetch(u, { method: 'POST', headers, body: fd }).then(async (r) => { try { return r.ok ? true : await r.json(); } catch (_) { return r.ok; } }));
                            }
                            if (ops.length) await Promise.all(ops);
                        } catch (e) { try { window.finLog({ level: 'warn', message: 'Falha ao desmarcar padrões anteriores (create)', stack: e && e.stack ? e.stack : undefined, context: { action: 'unsetOtherDefaultsCreated' } }); } catch (_) {} }
                    },
                    submit: async function(e) {
                        this.saving = true;
                        this.error = null;
                        this.msg = null;
                        const form = e.target;
                        const buildFd = (methodValue, defaultFlag) => {
                            const fd = new FormData();
                            if (this.schoolId) { fd.append('school_id', this.schoolId); }
                            if (this.gatewayAlias) fd.append('gateway_alias', this.gatewayAlias);
                            if (methodValue) fd.append('method', methodValue);
                            if (this.policy.fine_percent !== '') fd.append('penalty_policy[fine_percent]', this.normalizeDecimal(this.policy.fine_percent));
                            if (this.policy.daily_interest_percent !== '') fd.append('penalty_policy[daily_interest_percent]', this.normalizeDecimal(this.policy.daily_interest_percent));
                            if (this.policy.grace_days !== '') fd.append('penalty_policy[grace_days]', this.policy.grace_days);
                            if (this.policy.max_interest_percent !== '') fd.append('penalty_policy[max_interest_percent]', this.normalizeDecimal(this.policy.max_interest_percent));
                            fd.append('penalty_policy[is_default]', defaultFlag ? '1' : '0');
                            fd.append('active', this.active ? '1' : '0');
                            return fd;
                        };
                        try {
                            const headers = {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            };
                            if (!this.multi) {
                                const fd = buildFd(this.selectedMethod, !!this.policy.is_default);
                                const resp = await fetch(form.action, { method: 'POST', headers, body: fd });
                                const data = await resp.json();
                                if (resp.ok) {
                                    this.msg = (data && data.id) ? 'Forma de cobrança criada' : 'Criado com sucesso';
                                    if (this.policy.is_default) {
                                        await this.unsetOtherDefaultsCreated(data && data.id ? data.id : null);
                                    }
                                    closeModal('create-charge-modal');
                                    window.dispatchEvent(new CustomEvent('charge-methods:updated'));
                                } else {
                                    this.error = (data && (data.message || data.errors)) ? (typeof data.errors === 'string' ? data.errors : JSON.stringify(data.errors)) || data.message : 'Erro';
                                }
                            } else {
                                // Multi-create: sequentially create selected methods
                                const methods = Array.from(this.selectedMethods || []);
                                if (methods.length === 0) {
                                    this.error = 'Selecione pelo menos uma forma para cadastrar.';
                                } else {
                                    let successCount = 0;
                                    let failCount = 0;
                                    let failMsgs = [];
                                    let defaultApplied = false;
                                    let firstDefaultId = null;
                                    for (const m of methods) {
                                        const applyDefaultNow = !!this.policy.is_default && !defaultApplied;
                                        const fd = buildFd(m, applyDefaultNow);
                                        const resp = await fetch(form.action, { method: 'POST', headers, body: fd });
                                        let ok = resp.ok;
                                        let data;
                                        try { data = await resp.json(); } catch (_) { data = null; }
                                        if (ok) {
                                            successCount++;
                                            if (applyDefaultNow) {
                                                defaultApplied = true;
                                                firstDefaultId = data && data.id ? data.id : null;
                                            }
                                        } else {
                                            failCount++;
                                            failMsgs.push((data && data.message) ? data.message : `Falha em ${m} (${resp.status})`);
                                        }
                                    }
                                    if (successCount > 0) {
                                        if (defaultApplied) {
                                            await this.unsetOtherDefaultsCreated(firstDefaultId);
                                        }
                                        this.msg = `Cadastrado(s): ${successCount}.` + (failCount ? ` Falhas: ${failCount}.` : '');
                                        closeModal('create-charge-modal');
                                        window.dispatchEvent(new CustomEvent('charge-methods:updated'));
                                    }
                                    if (failCount > 0) {
                                        this.error = failMsgs.join('\n');
                                    }
                                }
                            }
                        } catch (err) {
                            this.error = err && err.message ? err.message : 'Erro inesperado';
                            try { window.finLog({ level: 'error', message: this.error, stack: err && err.stack ? err.stack : undefined, context: { action: 'createCharge' } }); } catch (_) {}
                        } finally {
                            this.saving = false;
                        }
                    }
                }" @submit.prevent="submit($event)">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gateway</label>
                        <x-select name="gateway_alias" x-model="gatewayAlias" @change="onGatewayChange()"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Selecione o gateway...</option>
                            @foreach ($gateways as $gw)
                                <option value="{{ $gw->alias }}">{{ $gw->alias }} ({{ $gw->name }})
                                </option>
                            @endforeach
                        </x-select>
                    </div>
                    <div>
                        <x-input name="grace_days" label="Dias de tolerância" type="number" min="0"
                            max="30" x-model.number="policy.grace_days" inputmode="numeric" />
                    </div>
                    <div>
                        <x-input name="fine_percent_ui" label="Multa (%)" type="number" step="0.01"
                            min="0" max="20" x-model.number="policy.fine_percent"
                            inputmode="decimal" />
                    </div>
                    <div>
                        <x-input name="daily_interest_percent_ui" label="Juros diário (%)" type="number"
                            step="0.01" min="0" max="10"
                            x-model.number="policy.daily_interest_percent" inputmode="decimal" />
                    </div>
                    <div>
                        <x-input name="max_interest_percent_ui" label="Juros máximo (%)" type="number"
                            step="0.01" min="0" max="100"
                            x-model.number="policy.max_interest_percent" inputmode="decimal" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="plan-active" x-model="active" class="rounded border-gray-300">
                        <label for="plan-active" class="text-sm text-gray-700">Ativo</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="plan-default" x-model="policy.is_default"
                            class="rounded border-gray-300">
                        <label for="plan-default" class="text-sm text-gray-700">Padrão do Sistema</label>
                    </div>
                </div>
                <div class="mt-4">
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Forma de Pagamento</h4>
                    <div class="flex items-center gap-2 mb-2">
                        <input type="checkbox" id="multi-create" x-model="multi" class="rounded border-gray-300">
                        <label for="multi-create" class="text-sm text-gray-700">Cadastrar múltiplas formas</label>
                    </div>
                    <div class="flex flex-wrap gap-3" x-show="methodsForAlias().length > 0 && !multi">
                        <template x-for="m in methodsForAlias()" :key="m">
                            <label class="inline-flex items-center">
                                <input type="radio" name="method_ui" :value="m"
                                    @change="selectedMethod = m" :checked="selectedMethod === m"
                                    class="rounded border-gray-300">
                                <span class="ml-2"
                                    x-text="m === 'credit_card' ? 'Cartão de Crédito' : (m === 'debit_card' ? 'Débito' : m.toUpperCase())"></span>
                            </label>
                        </template>
                    </div>
                    <div class="flex flex-wrap gap-3" x-show="methodsForAlias().length > 0 && multi">
                        <template x-for="m in methodsForAlias()" :key="m">
                            <label class="inline-flex items-center">
                                <input type="checkbox" :value="m" x-model="selectedMethods"
                                    class="rounded border-gray-300">
                                <span class="ml-2"
                                    x-text="m === 'credit_card' ? 'Cartão de Crédito' : (m === 'debit_card' ? 'Débito' : m.toUpperCase())"></span>
                            </label>
                        </template>
                    </div>
                    <p class="text-xs text-gray-500 mt-1" x-show="!gatewayAlias">Selecione um gateway para sugerir
                        métodos suportados.</p>
                </div>
                <template x-if="error">
                    <div class="text-sm text-red-700 bg-red-50 border border-red-200 rounded px-3 py-2"
                        x-text="error"></div>
                </template>
                <template x-if="msg">
                    <div class="text-sm text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2"
                        x-text="msg"></div>
                </template>
                <div class="flex justify-end gap-2 pt-4 border-t border-gray-200">
                    <x-button type="button" color="secondary"
                        onclick="closeModal('create-charge-modal')">Cancelar</x-button>
                    <x-button type="submit" color="primary" x-bind:disabled="saving">
                        <i class="fas fa-save mr-1"></i> Criar Forma
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Finance agendamento de cobranças -->
<div id="tab-fin-dunning" class="fintab-content hidden px-6 py-4">
    @php
        $pre = data_get($settings->dunning_schedule, 'pre_due_offsets', []);
        $pre = is_string($pre) ? explode(',', $pre) : $pre;
        $post = data_get($settings->dunning_schedule, 'overdue_offsets', []);
        $post = is_string($post) ? explode(',', $post) : $post;
    @endphp
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Agendamento de Cobranças</h3>
        <x-button color="secondary" type="button" onclick="openModal('mail-settings-modal')">
            <i class="fas fa-cog mr-1"></i> Configurar E-mail
        </x-button>
    </div>

    <!-- Modal: Configurações de E-mail de Cobrança -->
    <div id="mail-settings-modal"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3" x-data="mailSettingsForm()" x-init="load()">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Configurar envio de Cobranças por E-mail</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600"
                        onclick="closeModal('mail-settings-modal')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <template x-if="error">
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded" x-text="error">
                    </div>
                </template>
                <template x-if="msg">
                    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"
                        x-text="msg"></div>
                </template>

                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                        x-text="verified ? 'DNS verificado' : 'DNS pendente'"></span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="active ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700'"
                        x-text="active ? 'Ativo' : 'Inativo'"></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-select name="provider_ui" label="Provedor" x-model="provider" :options="['mailgun' => 'Mailgun', 'ses' => 'Amazon SES', 'smtp' => 'SMTP Genérico']"
                            selected="mailgun" />
                    </div>
                    <div>
                        <x-input label="Domínio remetente" x-model="sending_domain"
                            placeholder="ex: escola.exemplo.com" />
                    </div>
                </div>

                <!-- Credenciais específicas -->
                <div class="mt-4">
                    <div x-show="provider === 'mailgun'" class="space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-select name="mailgun_region_ui" label="Região" x-model="mailgun.region"
                                :options="['us' => 'US', 'eu' => 'EU']" selected="us" />
                            <x-input label="API Key" x-model="mailgun.api_key" type="password"
                                placeholder="Mailgun API Key" />
                        </div>
                        <p class="text-xs text-gray-500">Após salvar, use "Verificar DNS" para validar
                            SPF/DKIM/DMARC/CNAME.</p>
                    </div>
                    <div x-show="provider === 'ses'" class="space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-select name="ses_region_ui" label="Região" x-model="ses.region" :options="[
                                'us-east-1' => 'us-east-1',
                                'us-west-2' => 'us-west-2',
                                'sa-east-1' => 'sa-east-1',
                                'eu-west-1' => 'eu-west-1',
                            ]"
                                selected="sa-east-1" />
                            <x-input label="Access Key" x-model="ses.access_key" type="text"
                                placeholder="AWS Access Key" />
                            <x-input label="Secret Key" x-model="ses.secret_key" type="password"
                                placeholder="AWS Secret Key" />
                        </div>
                        <p class="text-xs text-gray-500">A verificação de domínio e DKIM é feita no console da AWS SES.
                        </p>
                    </div>
                    <div x-show="provider === 'smtp'" class="space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input label="Host" x-model="smtp.host" placeholder="smtp.exemplo.com" />
                            <x-input label="Porta" x-model="smtp.port" type="number" placeholder="587" />
                            <x-input label="Usuário" x-model="smtp.username" placeholder="usuario" />
                            <x-input label="Senha" x-model="smtp.password" type="password" placeholder="senha" />
                            <x-select name="smtp_encryption_ui" label="Criptografia" x-model="smtp.encryption"
                                :options="['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'Sem criptografia']" selected="tls" />
                        </div>
                        <p class="text-xs text-gray-500">Para SMTP genérico, a verificação de DNS não é automática.</p>
                    </div>
                </div>

                <!-- Ações -->
                <div class="mt-6 flex justify-end gap-2">
                    <x-button type="button" color="secondary"
                        onclick="closeModal('mail-settings-modal')">Cancelar</x-button>
                    <x-button type="button" color="indigo" x-bind:disabled="saving" @click="verifyDNS()">
                        <i class="fas fa-shield-alt mr-1"></i> Verificar DNS
                    </x-button>
                    <x-button type="button" color="primary" x-bind:disabled="saving" @click="save()">
                        <i class="fas fa-save mr-1"></i> Salvar
                    </x-button>
                </div>

                <!-- Resultado da verificação -->
                <template x-if="dnsResult">
                    <div class="mt-4 border rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-gray-900 mb-2">Resultado da verificação</h4>
                        <div class="space-y-2">
                            <template x-if="dnsResult.message">
                                <div class="text-sm text-gray-700" x-text="dnsResult.message"></div>
                            </template>
                            <template x-if="dnsResult.requirements">
                                <div class="space-y-3">
                                    <div>
                                        <div class="text-xs font-semibold text-gray-700">SPF</div>
                                        <div class="text-xs text-gray-600">Nome: <span
                                                x-text="dnsResult.requirements.spf.name"></span></div>
                                        <div class="text-xs text-gray-600">Valor: <span
                                                x-text="dnsResult.requirements.spf.value"></span></div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-gray-700">DKIM</div>
                                        <template x-for="rec in (dnsResult.requirements.dkim || [])"
                                            :key="rec.selector">
                                            <div class="ml-2">
                                                <div class="text-xs text-gray-600">Selector: <span
                                                        x-text="rec.selector"></span></div>
                                                <div class="text-xs text-gray-600">Nome: <span
                                                        x-text="rec.name"></span></div>
                                                <div class="text-xs text-gray-600">Valor: <span
                                                        x-text="rec.value"></span></div>
                                            </div>
                                        </template>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-gray-700">DMARC</div>
                                        <div class="text-xs text-gray-600">Nome: <span
                                                x-text="dnsResult.requirements.dmarc.name"></span></div>
                                        <div class="text-xs text-gray-600">Valor: <span
                                                x-text="dnsResult.requirements.dmarc.value"></span></div>
                                    </div>
                                    <div>
                                        <div class="text-xs font-semibold text-gray-700">CNAME</div>
                                        <template x-for="c in (dnsResult.requirements.cname || [])"
                                            :key="c.alias">
                                            <div class="ml-2">
                                                <div class="text-xs text-gray-600">Alias: <span
                                                        x-text="c.alias"></span></div>
                                                <div class="text-xs text-gray-600">Aponta para: <span
                                                        x-text="c.target"></span></div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('mailSettingsForm', () => ({
                saving: false,
                error: null,
                msg: null,
                provider: 'mailgun',
                sending_domain: '',
                active: false,
                verified: false,
                existingCredentials: {},
                dnsResult: null,
                mailgun: {
                    api_key: '',
                    region: 'us'
                },
                ses: {
                    region: 'sa-east-1',
                    access_key: '',
                    secret_key: ''
                },
                smtp: {
                    host: '',
                    port: 587,
                    username: '',
                    password: '',
                    encryption: 'tls'
                },
                async load() {
                    this.error = null;
                    this.msg = null;
                    this.dnsResult = null;
                    try {
                        const url = window.location.origin + '/finance/mail-settings';
                        const resp = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await resp.json();
                        if (!resp.ok) {
                            this.error = (data && (data.message || data.errors)) ? (typeof data
                                .errors === 'string' ? data.errors : JSON.stringify(data.errors)
                            ) || data.message : `Erro ${resp.status}`;
                            return;
                        }
                        const s = data && data.settings ? data.settings : data;
                        if (s) {
                            this.provider = (s.provider || this.provider);
                            this.sending_domain = (s.sending_domain || '');
                            this.active = !!(s.active === true || s.active === 1 || s.active ===
                                '1');
                            this.verified = !!(s.verified === true || s.verified === 1 || s
                                .verified === '1');
                            this.existingCredentials = s.credentials || {};
                            // Pre-fill masked fields lightly
                            if (this.provider === 'mailgun') {
                                this.mailgun.api_key = (this.existingCredentials.api_key && String(
                                    this.existingCredentials.api_key).includes('***')) ? '' : (
                                    this.existingCredentials.api_key || '');
                                this.mailgun.region = this.existingCredentials.region || this
                                    .mailgun.region;
                            } else if (this.provider === 'ses') {
                                this.ses.region = this.existingCredentials.region || this.ses
                                    .region;
                                this.ses.access_key = (this.existingCredentials.access_key &&
                                    String(this.existingCredentials.access_key).includes('***')
                                ) ? '' : (this.existingCredentials.access_key || '');
                                this.ses.secret_key = (this.existingCredentials.secret_key &&
                                    String(this.existingCredentials.secret_key).includes('***')
                                ) ? '' : (this.existingCredentials.secret_key || '');
                            } else if (this.provider === 'smtp') {
                                this.smtp.host = this.existingCredentials.host || '';
                                this.smtp.port = this.existingCredentials.port || 587;
                                this.smtp.username = this.existingCredentials.username || '';
                                this.smtp.password = '';
                                this.smtp.encryption = this.existingCredentials.encryption || 'tls';
                            }
                        }
                    } catch (e) {
                        this.error = e && e.message ? e.message :
                            'Erro ao carregar configurações de e-mail';
                    }
                },
                computeCredentials() {
                    const prev = this.existingCredentials || {};
                    if (this.provider === 'mailgun') {
                        return {
                            api_key: this.mailgun.api_key || prev.api_key || '',
                            region: this.mailgun.region || prev.region || 'us'
                        };
                    } else if (this.provider === 'ses') {
                        return {
                            region: this.ses.region || prev.region || 'sa-east-1',
                            access_key: this.ses.access_key || prev.access_key || '',
                            secret_key: this.ses.secret_key || prev.secret_key || ''
                        };
                    } else {
                        return {
                            host: this.smtp.host || prev.host || '',
                            port: this.smtp.port || prev.port || 587,
                            username: this.smtp.username || prev.username || '',
                            password: this.smtp.password || prev.password || '',
                            encryption: this.smtp.encryption || prev.encryption || 'tls'
                        };
                    }
                },
                async save() {
                    this.saving = true;
                    this.error = null;
                    this.msg = null;
                    try {
                        const url = window.location.origin + '/finance/mail-settings';
                        const payload = {
                            provider: this.provider,
                            sending_domain: this.sending_domain,
                            credentials: this.computeCredentials()
                        };
                        const resp = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await resp.json();
                        if (resp.ok) {
                            this.msg = (data && data.message) ? data.message :
                                'Configurações salvas';
                            this.active = !!(data && (data.active === true || data.active === 1 ||
                                data.active === '1'));
                            this.verified = !!(data && (data.verified === true || data.verified ===
                                1 || data.verified === '1'));
                            this.existingCredentials = (data && data.credentials) ? data
                                .credentials : this.existingCredentials;
                        } else {
                            this.error = (data && (data.message || data.errors)) ? (typeof data
                                .errors === 'string' ? data.errors : JSON.stringify(data.errors)
                            ) || data.message : `Erro ${resp.status}`;
                        }
                    } catch (e) {
                        this.error = e && e.message ? e.message : 'Erro ao salvar configurações';
                    } finally {
                        this.saving = false;
                    }
                },
                async verifyDNS() {
                    this.saving = true;
                    this.error = null;
                    this.msg = null;
                    this.dnsResult = null;
                    try {
                        const url = window.location.origin + '/finance/mail-settings/verify-dns';
                        const resp = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                provider: this.provider,
                                sending_domain: this.sending_domain,
                                region: this.mailgun.region
                            })
                        });
                        const data = await resp.json();
                        if (resp.ok) {
                            this.dnsResult = data;
                            // Se backend marcar verificado
                            if (data && (data.verified === true || data.verified === 1 || data
                                    .verified === '1')) {
                                this.verified = true;
                            }
                        } else {
                            this.error = (data && (data.message || data.errors)) ? (typeof data
                                .errors === 'string' ? data.errors : JSON.stringify(data.errors)
                            ) || data.message : `Erro ${resp.status}`;
                        }
                    } catch (e) {
                        this.error = e && e.message ? e.message : 'Erro ao verificar DNS';
                    } finally {
                        this.saving = false;
                    }
                }
            }));
        });
    </script>

    <form method="POST" action="{{ route('finance.settings.save') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Ativar agendamento</label>
                <label class="inline-flex items-center mt-2">
                    <input type="checkbox" name="dunning_schedule[enabled]" value="1" class="form-checkbox"
                        {{ data_get($settings->dunning_schedule, 'enabled') ? 'checked' : '' }}>
                    <span class="ml-2 text-gray-700">Ativo</span>
                </label>
            </div>
            @php($tz = old('timezone', $settings->timezone ?? 'America/Sao_Paulo'))
            <x-select name="timezone" label="Timezone">
                <option value="America/Sao_Paulo" @selected($tz === 'America/Sao_Paulo')>America/Sao_Paulo</option>
                <option value="America/Fortaleza" @selected($tz === 'America/Fortaleza')>America/Fortaleza</option>
                <option value="America/Manaus" @selected($tz === 'America/Manaus')>America/Manaus</option>
                <option value="America/Bahia" @selected($tz === 'America/Bahia')>America/Bahia</option>
                <option value="UTC" @selected($tz === 'UTC')>UTC</option>
            </x-select>
            <div>
                <label class="block text-sm font-medium text-gray-700">Dias da semana</label>
                @php($days = data_get($settings->dunning_schedule, 'days_of_week', []))
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach (['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'] as $d)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="dunning_schedule[days_of_week][]"
                                value="{{ $d }}" class="form-checkbox"
                                {{ in_array($d, $days) ? 'checked' : '' }}>
                            <span class="ml-2 capitalize">{{ $d }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Janelas de horário</label>
                @php($windows = data_get($settings->dunning_schedule, 'time_windows', [['start' => '08:00', 'end' => '18:00']]))
                <div id="dunning-windows" class="space-y-2 mt-2">
                    @foreach ($windows as $i => $w)
                        <div class="flex items-center gap-2">
                            <input type="time" name="dunning_schedule[time_windows][{{ $i }}][start]"
                                value="{{ $w['start'] ?? '' }}" class="rounded-md border-gray-300 shadow-sm">
                            <span class="text-gray-500">até</span>
                            <input type="time" name="dunning_schedule[time_windows][{{ $i }}][end]"
                                value="{{ $w['end'] ?? '' }}" class="rounded-md border-gray-300 shadow-sm">
                        </div>
                    @endforeach
                </div>
                <x-button type="button" color="secondary" class="mt-2" onclick="addDunningWindow()"><i
                        class="fas fa-plus mr-1"></i> Adicionar janela</x-button>
            </div>
            <div x-data="{
                selected: {{ json_encode(is_array($pre) ? array_map('intval', $pre) : []) }},
                toggle(day) {
                    if (this.selected.includes(day)) {
                        this.selected = this.selected.filter(d => d !== day);
                    } else {
                        this.selected.push(day);
                        this.selected.sort((a, b) => a - b);
                    }
                }
            }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Lembretes antes do vencimento
                    (dias)</label>
                <div class="grid grid-cols-7 sm:grid-cols-10 gap-2 mb-2">
                    <template x-for="day in 30">
                        <button type="button" @click="toggle(day)"
                            :class="selected.includes(day) ? 'bg-indigo-600 text-white border-indigo-600 shadow-indigo-100' :
                                'bg-white text-gray-700 border-gray-300 hover:border-indigo-400'"
                            class="h-9 w-full text-sm font-medium border rounded-md flex items-center justify-center transition-all duration-200 shadow-sm"
                            x-text="day">
                        </button>
                    </template>
                </div>
                <input type="hidden" name="dunning_schedule[pre_due_offsets]" :value="selected.join(',')">
                <p class="text-xs text-gray-500 mt-1">Selecione em quais dias antes do vencimento os lembretes serão
                    enviados.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Enviar no dia do vencimento</label>
                <label class="inline-flex items-center mt-2">
                    <input type="checkbox" name="dunning_schedule[due_day]" value="1" class="form-checkbox"
                        {{ data_get($settings->dunning_schedule, 'due_day') ? 'checked' : '' }}>
                    <span class="ml-2 text-gray-700">Sim</span>
                </label>
            </div>
            <div x-data="{
                selected: {{ json_encode(is_array($post) ? array_map('intval', $post) : []) }},
                toggle(day) {
                    if (this.selected.includes(day)) {
                        this.selected = this.selected.filter(d => d !== day);
                    } else {
                        this.selected.push(day);
                        this.selected.sort((a, b) => a - b);
                    }
                }
            }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Lembretes após o vencimento (dias)</label>
                <div class="grid grid-cols-7 sm:grid-cols-10 gap-2 mb-2">
                    <template x-for="day in 30">
                        <button type="button" @click="toggle(day)"
                            :class="selected.includes(day) ? 'bg-orange-600 text-white border-orange-600 shadow-orange-100' :
                                'bg-white text-gray-700 border-gray-300 hover:border-orange-400'"
                            class="h-9 w-full text-sm font-medium border rounded-md flex items-center justify-center transition-all duration-200 shadow-sm"
                            x-text="day">
                        </button>
                    </template>
                </div>
                <input type="hidden" name="dunning_schedule[overdue_offsets]" :value="selected.join(',')">
                <p class="text-xs text-gray-500 mt-1">Selecione em quais dias após o vencimento os lembretes serão
                    enviados.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Canais</label>
                @php($channels = data_get($settings->dunning_schedule, 'channels', ['email']))
                <div class="mt-2 flex flex-wrap gap-3">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="dunning_schedule[channels][]" value="email"
                            class="form-checkbox" {{ in_array('email', $channels) ? 'checked' : '' }}>
                        <span class="ml-2">Email</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="dunning_schedule[channels][]" value="whatsapp"
                            class="form-checkbox" {{ in_array('whatsapp', $channels) ? 'checked' : '' }}>
                        <span class="ml-2">WhatsApp</span>
                    </label>
                </div>
            </div>

        </div>
        <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-end gap-3">
            <div class="md:w-64">
                <x-input name="test_email" label="E-mail para teste" type="email"
                    placeholder="email@exemplo.com" />
            </div>
            <x-button type="button" color="secondary" onclick="testDunningEmail()">
                <i class="fas fa-paper-plane mr-1"></i> Testar Envio
            </x-button>
            <x-button type="submit" color="primary"><i class="fas fa-save mr-1"></i> Salvar agendamento</x-button>
        </div>
        <script>
            function testDunningEmail() {
                const input = document.querySelector('input[name="test_email"]');
                const email = input ? input.value.trim() : '';
                if (!email) {
                    if (window.alertSystem) {
                        window.alertSystem.warning('Informe um e-mail para teste.');
                    } else {
                        alert('Informe um e-mail para teste.');
                    }
                    return;
                }
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const url = '{{ route('finance.settings.test_dunning_email') }}';
                const btn = event?.currentTarget;
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('opacity-50');
                }
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            test_email: email
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            if (window.alertSystem) {
                                window.alertSystem.success(data.message || 'E-mail de teste disparado com sucesso.');
                            } else {
                                alert(data.message || 'E-mail de teste disparado com sucesso.');
                            }
                        } else {
                            const message = data.message || 'Falha ao disparar e-mail de teste.';
                            if (window.alertSystem) {
                                window.alertSystem.error(message);
                            } else {
                                alert(message);
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Erro no teste de envio:', err);
                        if (window.alertSystem) {
                            window.alertSystem.error('Erro inesperado ao testar envio.');
                        } else {
                            alert('Erro inesperado ao testar envio.');
                        }
                    })
                    .finally(() => {
                        if (btn) {
                            btn.disabled = false;
                            btn.classList.remove('opacity-50');
                        }
                    });
            }
        </script>
    </form>
</div>
<script>
    function addDunningWindow() {
        const container = document.getElementById('dunning-windows');
        if (!container) return;
        const index = container.querySelectorAll('div.flex.items-center').length;
        const wrap = document.createElement('div');
        wrap.className = 'flex items-center gap-2';
        wrap.innerHTML = `
            <input type="time" name="dunning_schedule[time_windows][${index}][start]" class="rounded-md border-gray-300 shadow-sm">
            <span class="text-gray-500">até</span>
            <input type="time" name="dunning_schedule[time_windows][${index}][end]" class="rounded-md border-gray-300 shadow-sm">
        `;
        container.appendChild(wrap);
    }
</script>
<script>
    function openModal(id) {
        // Preferir evento para x-modal
        try {
            window.dispatchEvent(new CustomEvent('open-modal', {
                detail: id
            }));
        } catch (_) {}
        // Compatibilidade com modais antigos baseados em "hidden"
        const el = document.getElementById(id);
        if (el) el.classList.remove('hidden');
    }

    function closeModal(id) {
        // Preferir evento para x-modal
        try {
            window.dispatchEvent(new CustomEvent('close-modal', {
                detail: id
            }));
        } catch (_) {}
        // Compatibilidade com modais antigos baseados em "hidden"
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    }
    window.showFinanceTab = function(tabId) {
        const next = document.getElementById(tabId);
        if (!next) return;

        // Sincronizar seletor mobile
        const mobileSelect = document.getElementById('fin-tabs-mobile');
        if (mobileSelect) mobileSelect.value = tabId;

        // Encontrar aba atualmente visível
        const current = document.querySelector('.fintab-content:not(.hidden)');

        // Reset estados dos botões
        document.querySelectorAll('.fintab-btn').forEach(el => {
            el.classList.remove('border-indigo-500', 'text-indigo-600');
            el.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700',
                'hover:border-gray-300');
        });

        // Ativar botão da aba selecionada
        const map = {
            'tab-fin-settings': 'fin-settings-tab',
            'tab-fin-gateways': 'fin-gateways-tab',
            'tab-fin-dunning': 'fin-dunning-tab',
            'tab-fin-automation': 'fin-automation-tab'
        };
        const btnId = map[tabId];
        const btn = btnId ? document.getElementById(btnId) : null;
        if (btn) {
            btn.classList.add('border-indigo-500', 'text-indigo-600');
            btn.classList.remove('border-transparent', 'text-gray-500');
        }

        // Esconder todas as outras abas imediatamente
        document.querySelectorAll('.fintab-content').forEach(el => {
            if (el !== current && el !== next) el.classList.add('hidden');
        });

        // Animação de saída da aba atual
        if (current && current !== next) {
            current.classList.add('transition', 'duration-200', 'ease-in', 'opacity-0', 'translate-y-1');
            current.addEventListener('transitionend', function handleExit() {
                current.removeEventListener('transitionend', handleExit);
                current.classList.add('hidden');
                current.classList.remove('transition', 'duration-200', 'ease-in', 'opacity-0',
                    'translate-y-1');
            }, {
                once: true
            });
        }

        // Preparar próxima aba e animar entrada
        next.classList.remove('hidden');
        next.classList.add('transition', 'duration-200', 'ease-out', 'opacity-0', 'translate-y-1');
        // Forçar próximo frame para transição
        requestAnimationFrame(() => {
            next.classList.remove('opacity-0', 'translate-y-1');
        });
        next.addEventListener('transitionend', function handleEnter() {
            next.removeEventListener('transitionend', handleEnter);
            next.classList.remove('transition', 'duration-200', 'ease-out');
        }, {
            once: true
        });
    }
    async function reloadFinanceGatewaysTab() {
        try {
            const url = window.location.pathname + '?tab=financeiro';
            const resp = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const text = await resp.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const newTab = doc.getElementById('tab-fin-gateways');
            const current = document.getElementById('tab-fin-gateways');
            if (newTab && current) {
                current.replaceWith(newTab);
            }
        } catch (e) {
            console.error('Falha ao recarregar Gateways:', e);
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        window.showFinanceTab('tab-fin-gateways');
    });

    async function reloadFinanceSettingsTab() {
        try {
            const url = window.location.pathname + '?tab=financeiro';
            const resp = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const text = await resp.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const newTab = doc.getElementById('tab-fin-settings');
            const current = document.getElementById('tab-fin-settings');
            if (newTab && current) {
                current.replaceWith(newTab);
            }
        } catch (e) {
            console.error('Falha ao recarregar Formas de Cobrança:', e);
        }
    }

    // --- Financeiro logging helpers (client-side) ---
    //
    // This script adds a global `finLog` function to the window object.
    // When called, it appends a log entry to a tray element in the DOM.
    // The tray is created if it doesn't exist, and styled to be fixed at the bottom right of the viewport.
    // Each log entry includes a timestamp, log level, message, and optional stack trace.
    // Logs are stored in an array `__finErrors` on the window object.
    // The tray can be closed by clicking the close button (×) in the header.
    /*
    (function() {
        if (window.finLog) return;
        window.__finErrors = [];

        function ensureTray() {
            var tray = document.getElementById('fin-log-tray');
            if (!tray) {
                tray = document.createElement('div');
                tray.id = 'fin-log-tray';
                tray.style.position = 'fixed';
                tray.style.bottom = '12px';
                tray.style.right = '12px';
                tray.style.zIndex = '9999';
                tray.style.background = 'rgba(17,24,39,0.95)';
                tray.style.color = '#fff';
                tray.style.border = '1px solid #374151';
                tray.style.borderRadius = '8px';
                tray.style.fontSize = '12px';
                tray.style.minWidth = '280px';
                tray.style.maxWidth = '420px';
                tray.style.maxHeight = '240px';
                tray.style.overflow = 'auto';
                tray.style.boxShadow = '0 10px 15px rgba(0,0,0,0.25)';
                var header = document.createElement('div');
                header.style.padding = '8px 10px';
                header.style.borderBottom = '1px solid #374151';
                header.innerText = 'Financeiro – Erros capturados';
                tray.appendChild(header);
                var list = document.createElement('div');
                list.id = 'fin-log-list';
                list.style.padding = '8px 10px';
                tray.appendChild(list);
                document.body.appendChild(tray);
            }
            return tray;
        }
        window.finLog = function(payload) {
            try {
                var entry = {
                    time: new Date().toISOString(),
                    level: payload && payload.level ? payload.level : 'error',
                    message: payload && payload.message ? payload.message : 'Erro',
                    stack: payload && payload.stack ? payload.stack : undefined,
                    context: payload && payload.context ? payload.context : undefined
                };
                window.__finErrors.push(entry);
                var label = '[Financeiro] ' + entry.level.toUpperCase() + ': ' + entry.message;
                if (entry.stack) {
                    console.groupCollapsed(label);
                    console.log('time:', entry.time);
                    if (entry.context) console.log('context:', entry.context);
                    console.log(entry.stack);
                    console.groupEnd();
                } else {
                    var safeErr = (typeof window.__finConsoleOriginalError === 'function') ? window
                        .__finConsoleOriginalError : null;
                    if (safeErr) {
                        try {
                            safeErr(label, entry.context || '');
                        } catch (_) {
                            console.log(label, entry.context || '');
                        }
                    } else {
                        console.log(label, entry.context || '');
                    }
                }
                var tray = ensureTray();
                var list = document.getElementById('fin-log-list');
                if (tray && list) {
                    var row = document.createElement('div');
                    row.style.marginBottom = '6px';
                    row.innerHTML = '<div style="color:#fca5a5">' + entry.time + '</div>' +
                        '<div style="font-weight:600">' + entry.message + '</div>' +
                        (entry.context ? '<div style="color:#93c5fd">' + JSON.stringify(entry.context) +
                            '</div>' : '') +
                        (entry.stack ? '<pre style="white-space:pre-wrap;margin-top:4px">' + entry.stack +
                            '</pre>' : '');
                    list.appendChild(row);
                }
            } catch (_) {
                /* swallow */
    /*
            }
        };
        // Capture global errors
        window.addEventListener('error', function(e) {
            window.finLog({
                level: 'error',
                message: e.message,
                stack: e.error && e.error.stack ? e.error.stack : undefined,
                context: {
                    action: 'window.onerror',
                    filename: e.filename,
                    lineno: e.lineno,
                    colno: e.colno
                }
            });
        });
        window.addEventListener('unhandledrejection', function(e) {
            var r = e && e.reason;
            window.finLog({
                level: 'error',
                message: (r && r.message) ? r.message : String(r),
                stack: r && r.stack ? r.stack : undefined,
                context: {
                    action: 'unhandledrejection'
                }
            });
        });
        // Hook console.error
        if (!window.__finConsoleErrorHooked) {
            window.__finConsoleErrorHooked = true;
            var original = console.error.bind(console);
            window.__finConsoleOriginalError = original;
            console.error = function() {
                try {
                    var parts = Array.prototype.slice.call(arguments).map(function(a) {
                        if (typeof a === 'string') return a;
                        if (a && a.message) return a.message;
                        try {
                            return JSON.stringify(a);
                        } catch (_) {
                            return String(a);
                        }
                    });
                    window.finLog({
                        level: 'error',
                        message: parts.join(' '),
                        context: {
                            action: 'console.error'
                        }
                    });
                } catch (_) {}
                original.apply(console, arguments);
            };
        }
    })();
    */
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chargeSettings', () => ({
            loading: false,
            error: null,
            methods: [],
            schoolId: @json(optional(Auth::user())->escola_id ?? (optional(Auth::user())->school_id ?? session('escola_atual'))),
            gwNames: @json(collect($gateways ?? [])->map(fn($g) => ['alias' => $g->alias, 'name' => $g->name])->values()),
            gwNameFor(alias) {
                const a = (alias || '').toLowerCase();
                const m = (this.gwNames || []).find(x => (x.alias || '').toLowerCase() === a);
                return m ? (m.name || alias) : alias;
            },
            iconFor(m) {
                const s = (m || '').toLowerCase();
                return s === 'pix' ? 'PIX' : (s === 'boleto' ? 'Boleto' : (s === 'credit_card' ?
                    'Cartão de Crédito' : (s === 'debit_card' ? 'Débito' : s.toUpperCase())
                ));
            },
            // Filtros da listagem
            filterAlias: '',
            searchText: '',
            normalizeMethodLabel(m) {
                const s = (m || '');
                return s === 'credit_card' ? 'cartao_credito' : (s === 'debit_card' ? 'debito' : s);
            },
            normalizeDecimal(v) {
                if (v === null || v === undefined) return v;
                return ('' + v).replace(',', '.');
            },
            async load() {
                this.loading = true;
                this.error = null;
                try {
                    let url = '{{ url('/api/v1/finance/charge-methods') }}';
                    const q = [];
                    if (this.schoolId) q.push('school_id=' + encodeURIComponent(this.schoolId));
                    if (q.length) url = url + '?' + q.join('&');
                    const resp = await fetch(url, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await resp.json();
                    if (!resp.ok) {
                        this.error = (data && (data.message || data.errors)) ? (typeof data
                            .errors === 'string' ? data.errors : JSON.stringify(data.errors)
                        ) || data.message : 'Erro ao carregar formas de cobrança';
                        return;
                    }
                    const raw = (data && data.data) ? data.data : (Array.isArray(data) ? data :
                        []);
                    this.methods = raw.map(m => {
                        const pp = m && m.penalty_policy ? m.penalty_policy : {};
                        return Object.assign({}, m, {
                            active: !!(m && (m.active === true || m.active ===
                                1 || m.active === '1')),
                            penalty_policy: Object.assign({}, pp, {
                                is_default: !!(pp && (pp.is_default ===
                                    true || pp.is_default ===
                                    1 || pp.is_default === '1'))
                            })
                        });
                    });
                } catch (e) {
                    this.error = e && e.message ? e.message :
                        'Erro ao carregar formas de cobrança';
                    try {
                        window.finLog({
                            level: 'error',
                            message: this.error,
                            stack: e && e.stack ? e.stack : undefined,
                            context: {
                                action: 'loadChargeMethods'
                            }
                        });
                    } catch (_) {}
                } finally {
                    this.loading = false;
                }
            },
            filteredMethods() {
                const alias = (this.filterAlias || '').toLowerCase();
                const term = (this.searchText || '').toLowerCase();
                const list = Array.from(this.methods || []);
                return list.filter(cm => {
                    const a = (cm.gateway_alias || '').toLowerCase();
                    const m = (cm.method || '').toLowerCase();
                    const label = this.normalizeMethodLabel(m);
                    const hitAlias = !alias || a === alias;
                    const hitSearch = !term || m.includes(term) || label.includes(term);
                    return hitAlias && hitSearch;
                });
            },
            grouped() {
                const g = {};
                this.filteredMethods().forEach(cm => {
                    const a = (cm.gateway_alias || '').toLowerCase();
                    if (!g[a]) g[a] = [];
                    g[a].push(cm);
                });
                return g;
            },
            editing: {
                id: null,
                gateway_alias: '',
                method: '',
                active: false, // Default to false to avoid null access on .active
                school_id: null,
                penalty_policy: {
                    fine_percent: '',
                    daily_interest_percent: '',
                    grace_days: '',
                    max_interest_percent: '',
                    is_default: false
                }
            },
            isEditOpen: false,
            openEdit(cm) {
                this.editing = {
                    id: cm.id,
                    gateway_alias: cm.gateway_alias,
                    method: cm.method,
                    active: !!cm.active,
                    school_id: (cm && cm.school_id) ? cm.school_id : this.schoolId,
                    penalty_policy: (() => {
                        const pp = cm.penalty_policy || {};
                        return Object.assign({
                            fine_percent: '',
                            daily_interest_percent: '',
                            grace_days: '',
                            max_interest_percent: '',
                            is_default: false
                        }, pp, {
                            is_default: !!(pp.is_default === true || pp
                                .is_default === 1 || pp.is_default === '1')
                        });
                    })()
                };
                this.isEditOpen = true;
            },
            closeEdit() {
                this.isEditOpen = false;
                this.editing = null;
            },
            savingEdit: false,
            async unsetOtherDefaults(current) {
                try {
                    const currentId = current && current.id ? current.id : (this.editing && this
                        .editing.id);
                    const sid = (current && current.school_id) ? current.school_id : ((this
                            .editing && this.editing.school_id) ? this.editing.school_id :
                        this.schoolId);
                    if (!currentId || !sid) {
                        return;
                    }
                    const headers = {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    };
                    const list = Array.from(this.methods || []);
                    const ops = [];
                    for (const m of list) {
                        if (!m || m.id === currentId) continue;
                        const isDef = !!(m.penalty_policy && m.penalty_policy.is_default);
                        if (!isDef) continue;
                        const fd = new FormData();
                        fd.append('school_id', (m.school_id || sid));
                        fd.append('active', m && m.active ? '1' : '0');
                        if (m && m.gateway_alias) fd.append('gateway_alias', m.gateway_alias);
                        if (m && m.method) fd.append('method', m.method);
                        const pp = m.penalty_policy || {};
                        if (pp.fine_percent !== undefined && pp.fine_percent !== null && pp
                            .fine_percent !== '') fd.append('penalty_policy[fine_percent]', this
                            .normalizeDecimal(pp.fine_percent));
                        if (pp.daily_interest_percent !== undefined && pp
                            .daily_interest_percent !== null && pp.daily_interest_percent !== ''
                        ) fd.append('penalty_policy[daily_interest_percent]', this
                            .normalizeDecimal(pp.daily_interest_percent));
                        if (pp.grace_days !== undefined && pp.grace_days !== null && pp
                            .grace_days !== '') fd.append('penalty_policy[grace_days]', pp
                            .grace_days);
                        if (pp.max_interest_percent !== undefined && pp.max_interest_percent !==
                            null && pp.max_interest_percent !== '') fd.append(
                            'penalty_policy[max_interest_percent]', this.normalizeDecimal(pp
                                .max_interest_percent));
                        fd.append('penalty_policy[is_default]', '0');
                        fd.append('_method', 'PUT');
                        const url = '{{ url('/api/v1/finance/charge-methods') }}' + '/' +
                            encodeURIComponent(m.id);
                        ops.push(fetch(url, {
                            method: 'POST',
                            headers,
                            body: fd
                        }).then(async (r) => {
                            try {
                                return r.ok ? true : await r.json();
                            } catch (_) {
                                return r.ok;
                            }
                        }));
                    }
                    if (ops.length) {
                        await Promise.all(ops);
                    }
                } catch (e) {
                    try {
                        window.finLog({
                            level: 'warn',
                            message: 'Falha ao desmarcar padrões anteriores',
                            stack: e && e.stack ? e.stack : undefined,
                            context: {
                                action: 'unsetOtherDefaults'
                            }
                        });
                    } catch (_) {}
                }
            },
            async submitEdit(e) {
                this.savingEdit = true;
                this.error = null;
                try {
                    const form = e ? e.target : null;
                    const fd = new FormData();
                    const sid = (this.editing && this.editing.school_id) ? this.editing
                        .school_id : this.schoolId;
                    if (sid) {
                        fd.append('school_id', sid);
                    } else {
                        this.error = 'Escola não encontrada (school_id obrigatório).';
                        this.savingEdit = false;
                        return;
                    }
                    fd.append('active', this.editing && this.editing.active ? '1' : '0');
                    // Garantir que identificadores acompanham a edição
                    if (this.editing && this.editing.gateway_alias) {
                        fd.append('gateway_alias', this.editing.gateway_alias);
                    }
                    if (this.editing && this.editing.method) {
                        fd.append('method', this.editing.method);
                    }
                    if (this.editing && this.editing.penalty_policy) {
                        const pp = this.editing.penalty_policy;
                        if (pp.fine_percent !== '') fd.append('penalty_policy[fine_percent]',
                            this.normalizeDecimal(pp.fine_percent));
                        if (pp.daily_interest_percent !== '') fd.append(
                            'penalty_policy[daily_interest_percent]', this.normalizeDecimal(
                                pp.daily_interest_percent));
                        if (pp.grace_days !== '') fd.append('penalty_policy[grace_days]', pp
                            .grace_days);
                        if (pp.max_interest_percent !== '') fd.append(
                            'penalty_policy[max_interest_percent]', this.normalizeDecimal(pp
                                .max_interest_percent));
                        fd.append('penalty_policy[is_default]', pp.is_default ? '1' : '0');
                    }
                    // Usar POST com _method=PUT para garantir parsing do FormData pelo PHP
                    fd.append('_method', 'PUT');
                    const url = '{{ url('/api/v1/finance/charge-methods') }}' + '/' +
                        encodeURIComponent(this.editing.id);
                    const resp = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: fd
                    });
                    const data = await resp.json();
                    if (!resp.ok) {
                        this.error = (data && (data.message || data.errors)) ? (typeof data
                            .errors === 'string' ? data.errors : JSON.stringify(data.errors)
                        ) || data.message : `Erro ${resp.status}`;
                        return;
                    }
                    if (this.editing && this.editing.penalty_policy && this.editing
                        .penalty_policy.is_default) {
                        await this.unsetOtherDefaults(this.editing);
                    }
                    this.isEditOpen = false;
                    this.editing = null;
                    window.dispatchEvent(new CustomEvent('charge-methods:updated'));
                    await this.load();
                } catch (err) {
                    this.error = err && err.message ? err.message : 'Erro inesperado';
                    try {
                        window.finLog({
                            level: 'error',
                            message: this.error,
                            stack: err && err.stack ? err.stack : undefined,
                            context: {
                                action: 'updateChargeMethod',
                                id: this.editing && this.editing.id
                            }
                        });
                    } catch (_) {}
                } finally {
                    this.savingEdit = false;
                }
            }
        }));
    });
</script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('createGatewayForm', () => ({
            saving: false,
            error: null,
            msg: null,
            async submit(e) {
                this.saving = true;
                this.error = null;
                this.msg = null;
                const form = e.target;
                const fd = new FormData(form);
                try {
                    const resp = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: fd
                    });
                    const data = await resp.json();
                    if (resp.ok) {
                        this.msg = (data && data.message) ? data.message : 'Gateway criado';
                        closeModal('create-gateway-modal');
                        await reloadFinanceGatewaysTab();
                        showFinanceTab('tab-fin-gateways');
                    } else {
                        this.error = (data && (data.message || data.errors)) ? (typeof data
                            .errors === 'string' ? data.errors : JSON.stringify(data.errors)
                        ) || data.message : `Erro ${resp.status}`;
                    }
                } catch (err) {
                    this.error = err && err.message ? err.message : 'Erro inesperado';
                    try {
                        window.finLog({
                            level: 'error',
                            message: this.error,
                            stack: err && err.stack ? err.stack : undefined,
                            context: {
                                action: 'createGateway'
                            }
                        });
                    } catch (_) {}
                } finally {
                    this.saving = false;
                }
            }
        }));

        Alpine.data('gatewayCredentials', () => ({
            provider: 'asaas',
            asaas: {
                api_key: '',
                environment: 'production',
                webhook_token: ''
            },
            nupay: {
                api_key: '',
                environment: 'production',
                merchant_id: ''
            },
            test: {
                loading: false,
                message: '',
                ok: null
            },
            init() {
                this.syncAliasWithProvider();
                this.$watch('provider', () => this.syncAliasWithProvider());
            },
            syncAliasWithProvider() {
                try {
                    const el = document.querySelector('#alias');
                    if (el) el.value = this.provider || el.value;
                } catch (_) {}
            },
            computeJson() {
                if (this.provider === 'asaas') {
                    if (!this.asaas.api_key) return '';
                    return JSON.stringify({
                        provider: this.provider,
                        api_key: this.asaas.api_key,
                        environment: this.asaas.environment,
                        webhook_token: this.asaas.webhook_token || null
                    });
                }
                if (this.provider === 'nupay') {
                    if (!this.nupay.api_key) return '';
                    const obj = {
                        provider: this.provider,
                        api_key: this.nupay.api_key,
                        environment: this.nupay.environment
                    };
                    if (this.nupay.merchant_id) obj.merchant_id = this.nupay.merchant_id;
                    return JSON.stringify(obj);
                }
                return '';
            },
            async testCredentials() {
                this.test.loading = true;
                this.test.message = '';
                this.test.ok = null;
                try {
                    const url = window.location.origin + '/finance/gateways/test';
                    const env = this.provider === 'asaas' ? this.asaas.environment : this.nupay
                        .environment;
                    const apiKey = this.provider === 'asaas' ? this.asaas.api_key : this.nupay
                        .api_key;
                    const resp = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            provider: this.provider,
                            environment: env,
                            api_key: apiKey,
                            school_id: @json(optional(Auth::user())->escola_id ?? (optional(Auth::user())->school_id ?? session('escola_atual')))
                        })
                    });
                    const data = await resp.json();
                    if ((resp.ok && data.ok) || (resp.status === 200 && data.ok)) {
                        this.test.ok = true;
                        const accName = (data.account && (data.account.name || data.account
                            .tradingName)) || '';
                        this.test.message = 'Credenciais válidas' + (accName ?
                            ` — Conta: ${accName}` : '');
                    } else {
                        this.test.ok = false;
                        this.test.message = (data.message) || (data.errors ? (typeof data
                            .errors === 'string' ? data.errors : JSON.stringify(data
                                .errors)) : null) || (data.error && (data.error.message ||
                            data.error)) || `Falha ao validar (status ${resp.status})`;
                    }
                } catch (e) {
                    this.test.ok = false;
                    this.test.message = 'Erro de conexão: ' + (e && e.message ? e.message :
                        'desconhecido');
                } finally {
                    this.test.loading = false;
                }
            }
        }));
    });
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('editGatewayForm', (gwId) => ({
            saving: false,
            error: null,
            msg: null,
            async submit(e) {
                this.saving = true;
                this.error = null;
                this.msg = null;
                const form = e.target;
                const fd = new FormData(form);
                try {
                    const resp = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: fd
                    });
                    const data = await resp.json();
                    if (resp.ok) {
                        this.msg = (data && data.message) ? data.message : 'Gateway atualizado';
                        closeModal(`edit-gateway-${gwId}`);
                        await reloadFinanceGatewaysTab();
                        showFinanceTab('tab-fin-gateways');
                    } else {
                        this.error = (data && (data.message || data.errors)) ? (typeof data
                            .errors === 'string' ? data.errors : JSON.stringify(data.errors)
                        ) || data.message : `Erro ${resp.status}`;
                    }
                } catch (err) {
                    this.error = err && err.message ? err.message : 'Erro inesperado';
                    try {
                        window.finLog({
                            level: 'error',
                            message: this.error,
                            stack: err && err.stack ? err.stack : undefined,
                            context: {
                                action: 'editGateway'
                            }
                        });
                    } catch (_) {}
                } finally {
                    this.saving = false;
                }
            }
        }));

        Alpine.data('editGatewayCredentials', (gwId, apiKey, environment, providerAlias, creds) => ({
            provider: providerAlias || 'asaas',
            asaas: {
                api_key: apiKey,
                environment: environment === 'homolog' ? 'sandbox' : 'production',
                webhook_token: ''
            },
            nupay: {
                api_key: (creds && creds.api_key) ? creds.api_key : '',
                environment: (creds && creds.environment) ? creds.environment : (environment ===
                    'homolog' ? 'sandbox' : 'production'),
                merchant_id: (creds && (creds.merchant_id || creds.seller_id)) ? (creds
                    .merchant_id || creds.seller_id) : ''
            },
            test: {
                loading: false,
                message: '',
                ok: null
            },
            computeJson() {
                if (this.provider === 'asaas') {
                    if (!this.asaas.api_key) return '';
                    return JSON.stringify({
                        provider: this.provider,
                        api_key: this.asaas.api_key,
                        environment: this.asaas.environment,
                        webhook_token: this.asaas.webhook_token || null
                    });
                }
                if (this.provider === 'nupay') {
                    if (!this.nupay.api_key) return '';
                    const obj = {
                        provider: this.provider,
                        api_key: this.nupay.api_key,
                        environment: this.nupay.environment
                    };
                    if (this.nupay.merchant_id) obj.merchant_id = this.nupay.merchant_id;
                    return JSON.stringify(obj);
                }
                return '';
            },
            async testCredentials() {
                this.test.loading = true;
                this.test.message = '';
                this.test.ok = null;
                try {
                    const url = window.location.origin + '/finance/gateways/test';
                    const env = this.provider === 'asaas' ? this.asaas.environment : this.nupay
                        .environment;
                    const apiKey = this.provider === 'asaas' ? this.asaas.api_key : this.nupay
                        .api_key;
                    const resp = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            provider: this.provider,
                            environment: env,
                            api_key: apiKey,
                            school_id: @json(optional(Auth::user())->escola_id ?? (optional(Auth::user())->school_id ?? session('escola_atual')))
                        })
                    });
                    const data = await resp.json();
                    if ((resp.ok && data.ok) || (resp.status === 200 && data.ok)) {
                        this.test.ok = true;
                        const accName = (data.account && (data.account.name || data.account
                            .tradingName)) || '';
                        this.test.message = 'Credenciais válidas' + (accName ?
                            ` — Conta: ${accName}` : '');
                    } else {
                        this.test.ok = false;
                        this.test.message = (data.message) || (data.errors ? (typeof data
                            .errors === 'string' ? data.errors : JSON.stringify(data
                                .errors)) : null) || (data.error && (data.error.message ||
                            data.error)) || `Falha ao validar (status ${resp.status})`;
                    }
                } catch (e) {
                    this.test.ok = false;
                    this.test.message = 'Erro de conexão: ' + (e && e.message ? e.message :
                        'desconhecido');
                } finally {
                    this.test.loading = false;
                }
            }
        }));
    });
</script>
