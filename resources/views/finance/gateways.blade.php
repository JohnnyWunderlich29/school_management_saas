@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @php($currentSchoolId = optional(Auth::user())->escola_id ?? optional(Auth::user())->school_id ?? session('escola_atual'))
        <x-breadcrumbs :items="[
            ['title' => 'Financeiro', 'url' => route('finance.settings', $currentSchoolId ? ['school_id' => $currentSchoolId] : [])],
            ['title' => 'Gateways', 'url' => '#']
        ]" />

        <div class="mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gateways Financeiros</h1>
                <p class="mt-1 text-sm text-gray-600">Gerencie integrações, segredos de webhook e credenciais</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $financeEnv === 'production' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    Ambiente: {{ strtoupper($financeEnv) }}
                </span>
                <x-button href="{{ route('finance.settings', $currentSchoolId ? ['school_id' => $currentSchoolId] : []) }}" color="secondary">
                    <i class="fas fa-cog mr-1"></i> Configurações
                </x-button>
                <x-button color="primary" x-data="{}" @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-gateway-modal' }))">
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

        <x-card class="mb-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Indicadores</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
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
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $gw->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $gw->active ? 'Ativo' : 'Inativo' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $gw->webhook_secret ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $gw->webhook_secret ? 'Webhook configurado' : 'Webhook não definido' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ !empty($gw->credentials) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700' }}">
                                    {{ !empty($gw->credentials) ? 'Credenciais setadas' : 'Credenciais ausentes' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $gw->environment === 'production' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ ucfirst($gw->environment) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <x-button color="primary" x-data="{}" @click="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'edit-gateway-{{ $gw->id }}' }))">
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
        </x-card>

        <!-- Modal: Criar Gateway -->
        <x-modal name="create-gateway-modal" title="Novo Gateway" :show="false" maxWidth="max-w-3xl">
            <form method="POST" action="{{ route('finance.gateways.create') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input name="alias" id="alias" label="Alias" type="text" required value="asaas" readonly help="Definido automaticamente conforme o provedor selecionado." />
                    </div>
                    <div>
                        <x-input name="name" id="name" label="Nome" type="text" placeholder="ex: Gerencianet Pix/Boleto" />
                    </div>
                    <div>
                        <x-select name="environment" id="environment" label="Ambiente" :options="['homolog' => 'Homologação', 'production' => 'Produção']" selected="production" />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-select name="active" id="active" label="Ativo" :options="['1' => 'Sim', '0' => 'Não']" selected="1" />
                    </div>
                    <div>
                        <x-input name="webhook_secret" id="webhook_secret" label="Webhook Secret" type="text" placeholder="Segredo do webhook (opcional)" />
                    </div>
                </div>

                <!-- Provedor e Credenciais (UX guiada) -->
                <div x-data="{
                        provider: 'asaas',
                        asaas: { api_key: '', environment: 'production', webhook_token: '' },
                        test: { loading: false, message: '', ok: null },
                        computeJson() {
                            if (!this.asaas.api_key) return '';
                            return JSON.stringify({
                                provider: this.provider,
                                api_key: this.asaas.api_key,
                                environment: this.asaas.environment,
                                webhook_token: this.asaas.webhook_token || null,
                            });
                        },
                        async testCredentials() {
                            this.test.loading = true; this.test.message=''; this.test.ok = null;
                            try {
                                const url = window.location.origin + '/finance/gateways/test';
                                const resp = await fetch(url, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        provider: this.provider,
                                        environment: this.asaas.environment,
                                        api_key: this.asaas.api_key,
                                        school_id: @json(Auth::user()->escola_id ?? Auth::user()->school_id ?? session('escola_atual')),
                                    })
                                });
                                const data = await resp.json();
                                if ((resp.ok && data.ok) || (resp.status === 200 && data.ok)) {
                                    this.test.ok = true;
                                    const accName = (data.account && (data.account.name || data.account.tradingName)) || '';
                                    this.test.message = 'Credenciais válidas' + (accName ? ` — Conta: ${accName}` : '');
                                } else {
                                    this.test.ok = false;
                                    this.test.message = (data.message)
                                        || (data.errors ? (typeof data.errors === 'string' ? data.errors : JSON.stringify(data.errors)) : null)
                                        || (data.error && (data.error.message || data.error))
                                        || `Falha ao validar (status ${resp.status})`;
                                }
                            } catch (e) {
                                this.test.ok = false;
                                this.test.message = 'Erro de conexão: ' + (e && e.message ? e.message : 'desconhecido');
                            } finally {
                                this.test.loading = false;
                            }
                        }
                    }" class="space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-select name="provider_ui" label="Provedor" x-model="provider" :options="['asaas' => 'Asaas']" selected="asaas" />
                        </div>
                    </div>

                    <template x-if="provider === 'asaas'">
                        <div class="space-y-2">
+                            @if (empty($gw->credentials['api_key'] ?? null) && !empty($gw->credentials_encrypted))
+                                <div class="rounded-md bg-yellow-50 border border-yellow-200 text-yellow-800 px-3 py-2 text-sm">
+                                    Aviso: As credenciais existentes não puderam ser decodificadas com a APP_KEY atual. Informe novamente a API Key e salve para recriptografar.
+                                </div>
+                            @endif
                             <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                 <div class="md:col-span-2">
                                     <x-input name="asaas_api_key_ui_{{ $gw->id }}" label="API Key (Asaas)" type="password" x-model="asaas.api_key" x-ref="asaas_api_key_input_{{ $gw->id }}" placeholder="***" autocomplete="off" required class="pr-10">
                                         <button type="button" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700"
                                             @click="$refs['asaas_api_key_input_{{ $gw->id }}'].type = $refs['asaas_api_key_input_{{ $gw->id }}'].type === 'password' ? 'text' : 'password'">
                                             <i class="fas fa-eye"></i>
                                         </button>
                                     </x-input>
                                 </div>
                                 <div>
                                     <x-select name="asaas_environment_ui_{{ $gw->id }}" label="Ambiente (Asaas)" x-model="asaas.environment" :options="['production' => 'Produção', 'sandbox' => 'Sandbox']" selected="{{ $gw->environment === 'homolog' ? 'sandbox' : 'production' }}" />
                                 </div>
                             </div>
                             <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                 <div class="md:col-span-2">
                                     <x-input name="asaas_webhook_token_ui" label="Webhook Token (opcional)" type="text" x-model="asaas.webhook_token" placeholder="Token do webhook para validação" />
                                 </div>
                             </div>
                             <p class="mt-1 text-xs text-gray-500">Instruções Webhook (Asaas): cadastre a URL <code>{{ url('/api/v1/webhooks/gateway') }}/<span class='text-gray-700 font-semibold'>ALIAS</span></code> com método POST; autentique via token acima se desejar.</p>

                             <div class="flex items-center gap-2">
                                 <x-button type="button" color="secondary" x-data="{}" @click="testCredentials()" x-bind:disabled="test.loading">
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
                     <x-input type="hidden" name="credentials_json" x-bind:value="computeJson()" />
                 </div>
                 <div class="flex justify-end gap-2">
                     <x-button type="button" color="secondary" @click="window.dispatchEvent(new CustomEvent('close-modal'))">Cancelar</x-button>
                     <x-button type="submit" color="primary">
                         <i class="fas fa-plus mr-1"></i> Criar Gateway
                     </x-button>
                 </div>
             </form>
         </x-modal>

         <!-- Modais: Editar Gateway -->
         @foreach ($gateways as $gw)
         <x-modal name="edit-gateway-{{ $gw->id }}" title="Editar Gateway: {{ $gw->alias }}" :show="false" maxWidth="max-w-3xl">
             <form method="POST" action="{{ route('finance.gateways.update', ['id' => $gw->id]) }}" class="space-y-4">
                 @csrf
                 @method('PUT')
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                     <div>
                         <x-input name="name" id="name_{{ $gw->id }}" label="Nome" type="text" :value="old('name', $gw->name)" />
                     </div>
                     <div>
                         <x-select name="environment" id="environment_{{ $gw->id }}" label="Ambiente" :options="['homolog' => 'Homologação', 'production' => 'Produção']" selected="{{ $gw->environment }}" help="Eventos de ambiente diferente são ignorados." />
                     </div>
                     <div>
                         <x-select name="active" label="Ativo" :options="['1' => 'Sim', '0' => 'Não']" selected="{{ $gw->active ? '1' : '0' }}" />
                     </div>
                 </div>
                 <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                     <div>
                         <x-input name="webhook_secret" id="webhook_secret_{{ $gw->id }}" label="Webhook Secret" type="text" placeholder="Atualizar segredo (opcional)" help="Não exibimos o segredo atual por segurança." />
                     </div>
                 </div>

                 <!-- Provedor e Credenciais (UX guiada) -->
                 <div x-data="{
                         provider: 'asaas',
                         asaas: { api_key: '{{ $gw->credentials['api_key'] ?? '' }}', environment: '{{ $gw->environment === 'homolog' ? 'sandbox' : 'production' }}', webhook_token: '' },
                         test: { loading: false, message: '', ok: null },
                         computeJson() {
                             if (!this.asaas.api_key) return '';
                             return JSON.stringify({
                                 provider: this.provider,
                                 api_key: this.asaas.api_key,
                                 environment: this.asaas.environment,
                                 webhook_token: this.asaas.webhook_token || null,
                             });
                         },
                         async testCredentials() {
                             this.test.loading = true; this.test.message=''; this.test.ok = null;
                             try {
                                 const url = window.location.origin + '/finance/gateways/test';
                                 const resp = await fetch(url, {
                                     method: 'POST',
                                     headers: {
                                         'Content-Type': 'application/json',
                                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                     },
                                     body: JSON.stringify({
                                         provider: this.provider,
                                         environment: this.asaas.environment,
                                         api_key: this.asaas.api_key,
                                         school_id: @json(Auth::user()->escola_id ?? Auth::user()->school_id ?? session('escola_atual')),
                                     })
                                 });
                                 const data = await resp.json();
                                 if ((resp.ok && data.ok) || (resp.status === 200 && data.ok)) {
                                     this.test.ok = true;
                                     const accName = (data.account && (data.account.name || data.account.tradingName)) || '';
                                     this.test.message = 'Credenciais válidas' + (accName ? ` — Conta: ${accName}` : '');
                                 } else {
                                     this.test.ok = false;
                                     this.test.message = (data.message)
                                         || (data.errors ? (typeof data.errors === 'string' ? data.errors : JSON.stringify(data.errors)) : null)
                                         || (data.error && (data.error.message || data.error))
                                         || `Falha ao validar (status ${resp.status})`;
                                 }
                             } catch (e) {
                                 this.test.ok = false;
                                 this.test.message = 'Erro de conexão: ' + (e && e.message ? e.message : 'desconhecido');
                             } finally {
                                 this.test.loading = false;
                             }
                         }
                     }" class="space-y-3">
                     <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                         <div>
                             <x-select name="provider_ui" label="Provedor" x-model="provider" :options="['asaas' => 'Asaas']" selected="asaas" />
                         </div>
                     </div>

                     <template x-if="provider === 'asaas'">
                         <div class="space-y-2">
                             <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                 <div class="md:col-span-2">
                                     <x-input name="asaas_api_key_ui_{{ $gw->id }}" label="API Key (Asaas)" type="password" x-model="asaas.api_key" x-ref="asaas_api_key_input_{{ $gw->id }}" placeholder="***" autocomplete="off" required class="pr-10">
                                         <button type="button" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700"
                                             @click="$refs['asaas_api_key_input_{{ $gw->id }}'].type = $refs['asaas_api_key_input_{{ $gw->id }}'].type === 'password' ? 'text' : 'password'">
                                             <i class="fas fa-eye"></i>
                                         </button>
                                     </x-input>
                                 </div>
                                 <div>
                                     <x-select name="asaas_environment_ui_{{ $gw->id }}" label="Ambiente (Asaas)" x-model="asaas.environment" :options="['production' => 'Produção', 'sandbox' => 'Sandbox']" selected="{{ $gw->environment === 'homolog' ? 'sandbox' : 'production' }}" />
                                 </div>
                             </div>
                             <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                 <div class="md:col-span-2">
                                     <x-input name="asaas_webhook_token_ui_{{ $gw->id }}" label="Webhook Token (opcional)" type="text" x-model="asaas.webhook_token" placeholder="Token do webhook para validação" />
                                 </div>
                             </div>
                             <p class="mt-1 text-xs text-gray-500">Instruções Webhook (Asaas): cadastre a URL <code>{{ url('/api/v1/webhooks/gateway') }}/<span class='text-gray-700 font-semibold'>{{ $gw->alias }}</span></code> com método POST; autentique via token acima se desejar.</p>

                             <div class="flex items-center gap-2">
                                 <x-button type="button" color="secondary" x-data="{}" @click="testCredentials()" x-bind:disabled="test.loading">
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
                     <x-input type="hidden" name="credentials_json" x-bind:value="computeJson()" />
                 </div>
                 <div class="flex justify-end gap-2">
                     <x-button type="button" color="secondary" @click="window.dispatchEvent(new CustomEvent('close-modal'))">Cancelar</x-button>
                     <x-button type="submit" color="primary">
                         <i class="fas fa-save mr-1"></i> Salvar alterações
                     </x-button>
                 </div>
             </form>
         </x-modal>
         @endforeach
     </div>
</div>
@endsection