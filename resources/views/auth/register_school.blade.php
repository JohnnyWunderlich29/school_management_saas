@php
    $title = 'Cadastro de Escola';
@endphp

@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8 px-4">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
            </div>

            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Ocorreram erros ao enviar o formulário:</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.escola.submit') }}" class="space-y-8">
                    @csrf

                    <!-- Wizard Steps Navigation -->
                    <div class="mb-6">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <div class="step-indicator" data-step="1"></div>
                                    <span class="ml-2 text-sm font-medium text-gray-700">Escola</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <div class="step-indicator" data-step="2"></div>
                                    <span class="ml-2 text-sm font-medium text-gray-700">Plano & Módulos</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <div class="step-indicator" data-step="3"></div>
                                    <span class="ml-2 text-sm font-medium text-gray-700">Administrador</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 h-1 bg-gray-200 rounded">
                            <div id="wizard-progress" class="h-1 bg-indigo-600 rounded" style="width: 33%"></div>
                        </div>
                    </div>

                    <!-- Step 1: Escola -->
                    <section class="wizard-step" data-step="1">
                        <h2 class="text-base font-semibold text-gray-900">Dados da Escola</h2>
                        <p class="text-sm text-gray-500">Informe os dados básicos e de contato.</p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <x-input name="escola_nome" id="escola_nome" label="Nome da Escola" :value="old('escola_nome')"
                                    placeholder="Ex.: Escola Modelo" required />
                            </div>
                            <div>
                                <x-input name="cnpj" id="cnpj" label="CNPJ" :value="old('cnpj')"
                                    placeholder="00.000.000/0000-00" inputmode="numeric" required />
                            </div>
                            <div>
                                <x-input name="escola_email" id="escola_email" label="Email da Escola" type="email"
                                    :value="old('escola_email')" placeholder="contato@escola.com.br" required />
                            </div>
                            <div>
                                <x-input name="telefone" id="telefone" label="Telefone" :value="old('telefone')"
                                    placeholder="(00) 0000-0000" inputmode="numeric" />
                            </div>
                            <div>
                                <x-input name="celular" id="celular" label="Celular" :value="old('celular')"
                                    placeholder="(00) 00000-0000" inputmode="numeric" required />
                            </div>
                            <div>
                                <x-input name="cep" id="cep" label="CEP" :value="old('cep')"
                                    placeholder="00000-000" class="pr-12" inputmode="numeric" required
                                    help="Digite o CEP e clique na lupa para buscar.">
                                    <button type="button" id="btn-buscar-cep"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-indigo-600"
                                        title="Buscar CEP" aria-label="Buscar CEP">
                                        <svg id="icon-cep" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-4.35-4.35m1.1-4.4a7.5 7.5 0 11-15 0 7.5 7.5 0 0115 0z" />
                                        </svg>
                                    </button>
                                </x-input>
                            </div>
                            <div class="md:col-span-2">
                                <x-input name="endereco" id="endereco" label="Endereço" :value="old('endereco')"
                                    placeholder="Rua, Avenida..." />
                            </div>
                            <div>
                                <x-input name="numero" id="numero" label="Número" :value="old('numero')" placeholder="Nº" />
                            </div>
                            <div>
                                <x-input name="complemento" id="complemento" label="Complemento" :value="old('complemento')"
                                    placeholder="Apto, Bloco..." />
                            </div>
                            <div>
                                <x-input name="bairro" id="bairro" label="Bairro" :value="old('bairro')" />
                            </div>
                            <div>
                                <x-input name="cidade" id="cidade" label="Cidade" :value="old('cidade')" />
                            </div>
                            <div>
                                <x-input name="estado" id="estado" label="Estado (UF)" :value="old('estado')"
                                    maxlength="2" placeholder="UF" />
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="button"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-700 focus:outline-none"
                                onclick="nextStep()">Próximo</button>
                        </div>
                    </section>

                    <!-- Step 2: Planos e Módulos -->
                    <section class="wizard-step hidden" data-step="2">
                        <h2 class="text-base font-semibold text-gray-900">Plano & Módulos</h2>
                        <p class="text-sm text-gray-500">Escolha o plano ideal e personalize com módulos opcionais.</p>

                        <!-- Pricing cards: Planos -->
                        <div class="mt-6" role="radiogroup" aria-label="Seleção de plano">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($plans as $plan)
                                    @php
                                        $isRecommended = $plan->is_recommended ?? $loop->iteration === 2;
                                        $planPrice = (float) ($plan->price ?? 0);
                                    @endphp
                                    <label for="plan_{{ $plan->id }}" class="cursor-pointer">
                                        <input id="plan_{{ $plan->id }}" name="plan_id" type="radio"
                                            value="{{ $plan->id }}" class="sr-only peer"
                                            data-price="{{ number_format($planPrice, 2, '.', '') }}"
                                            data-plan-name="{{ $plan->name }}"
                                            {{ (string) old('plan_id') === (string) $plan->id ? 'checked' : '' }} required
                                            aria-describedby="plan_desc_{{ $plan->id }}">
                                        <div
                                            class="relative h-full rounded-2xl ring-1 {{ $isRecommended ? 'ring-indigo-300 bg-indigo-50' : 'ring-gray-200 bg-white' }} p-6 shadow-sm transition peer-checked:ring-2 peer-checked:ring-indigo-600 peer-checked:bg-indigo-50/50">
                                            @if ($isRecommended)
                                                <span
                                                    class="absolute -top-3 left-4 inline-flex items-center rounded-full bg-indigo-600 px-2 py-0.5 text-xs font-medium text-white shadow">Mais
                                                    popular</span>
                                            @endif
                                            @if ($plan->is_trial)
                                                <span
                                                    class="absolute -top-3 right-4 inline-flex items-center rounded-full bg-amber-500 px-2 py-0.5 text-xs font-medium text-white shadow">Trial
                                                    {{ $plan->trial_days }} dias</span>
                                            @endif
                                            <div class="flex flex-col h-full">
                                                <div>
                                                    <h3
                                                        class="text-base font-semibold {{ $isRecommended ? 'text-indigo-900' : 'text-gray-900' }}">
                                                        {{ $plan->name }}</h3>
                                                    <p id="plan_desc_{{ $plan->id }}"
                                                        class="mt-1 text-sm {{ $isRecommended ? 'text-indigo-700' : 'text-gray-500' }}">
                                                        {{ $plan->description }}</p>
                                                    <div class="mt-4 flex items-baseline gap-1">
                                                        <span
                                                            class="text-3xl font-bold {{ $isRecommended ? 'text-indigo-900' : 'text-gray-900' }}">R$
                                                            {{ number_format((float) $plan->price, 2, ',', '.') }}</span>
                                                        <span
                                                            class="text-sm {{ $isRecommended ? 'text-indigo-700' : 'text-gray-500' }}">/mês</span>
                                                    </div>
                                                    <div
                                                        class="mt-1 text-xs {{ $isRecommended ? 'text-indigo-700' : 'text-gray-500' }}">
                                                        Usuários: {{ $plan->max_users ?? '-' }} · Alunos:
                                                        {{ $plan->max_students ?? '-' }}</div>
                                                </div>
                                                <ul
                                                    class="mt-6 space-y-2 text-sm {{ $isRecommended ? 'text-indigo-900' : 'text-gray-700' }}">
                                                    <li class="flex items-start gap-2"><svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="h-4 w-4 text-indigo-600" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg> Inclui módulos essenciais</li>
                                                    <li class="flex items-start gap-2"><svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            class="h-4 w-4 text-indigo-600" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg> Suporte padrão</li>
                                                </ul>
                                                <div class="mt-6">
                                                    <span
                                                        class="select-button-text inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 peer-checked:bg-green-600">Selecionar</span>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Comparativo rápido de features por plano -->
                        <div class="mt-6 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 rounded-xl ring-1 ring-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Feature</th>
                                        @foreach ($plans as $plan)
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">
                                                {{ $plan->name }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-700">Usuários</td>
                                        @foreach ($plans as $plan)
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $plan->max_users ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-gray-700">Alunos</td>
                                        @foreach ($plans as $plan)
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $plan->max_students ?? '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-700">Período trial</td>
                                        @foreach ($plans as $plan)
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $plan->is_trial ? $plan->trial_days . ' dias' : '—' }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Módulos Essenciais (informativo) -->
                        <div class="mt-8">
                            <h3 class="text-sm font-medium text-gray-900">Módulos essenciais incluídos em todos os planos
                            </h3>
                            <ul class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                                @forelse ($essentialModules as $module)
                                    <li class="flex items-center gap-2 text-sm text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>{{ $module->display_name ?? $module->name }}</span>
                                    </li>
                                @empty
                                    <li class="text-sm text-gray-500">Nenhum módulo essencial encontrado.</li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- Módulos Opcionais (addons) -->
                        <div class="mt-8">
                            <h3 class="text-sm font-medium text-gray-900">Módulos opcionais (adicionais)</h3>
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @forelse ($optionalModules as $module)
                                    @php $modulePrice = (float) ($module->price ?? 0); @endphp
                                    <label
                                        class="group cursor-pointer relative rounded-xl ring-1 ring-gray-200 bg-white p-4 shadow-sm hover:shadow transition">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="text-sm font-medium text-gray-900">{{ $module->display_name ?? $module->name }}</span>
                                                    @if ($modulePrice > 0)
                                                        <span class="ml-auto text-xs text-gray-500">R$
                                                            {{ number_format($modulePrice, 2, ',', '.') }}/mês</span>
                                                    @else
                                                        <span class="ml-auto text-xs text-gray-500">Gratuito</span>
                                                    @endif
                                                </div>
                                                @if (!empty($module->description))
                                                    <p id="module_desc_{{ $module->id }}"
                                                        class="mt-1 text-xs text-gray-500">{{ $module->description }}</p>
                                                @endif
                                            </div>
                                            <div class="relative flex items-center">
                                                <input type="checkbox" name="modules[]" id="module_{{ $module->id }}"
                                                    value="{{ $module->id }}" class="sr-only peer"
                                                    data-price="{{ number_format($modulePrice, 2, '.', '') }}"
                                                    @checked(in_array($module->id, old('modules', [])))
                                                    aria-describedby="module_desc_{{ $module->id }}">
                                                <div
                                                    class="w-10 h-6 rounded-full bg-gray-200 transition peer-checked:bg-indigo-600 ring-1 ring-gray-300">
                                                </div>
                                                <span
                                                    class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transform transition peer-checked:translate-x-4"></span>
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <div class="rounded-xl ring-1 ring-gray-200 bg-gray-50 p-4">
                                        <p class="text-sm text-gray-500">Nenhum módulo extra disponível no momento.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Resumo do total dinâmico -->
                        <div id="pricing-summary" class="mt-8 rounded-xl ring-1 ring-gray-200 bg-white p-4"
                            aria-live="polite">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">Resumo</h4>
                                    <p class="text-xs text-gray-500">Total mensal (plano + módulos)</p>
                                </div>
                                <div class="text-right">
                                    <div id="summary-total" class="text-xl font-semibold text-gray-900">R$ 0,00/mês</div>
                                    <div id="summary-breakdown" class="text-xs text-gray-500">Plano: R$ 0,00 + Addons: R$
                                        0,00</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <button type="button"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md shadow hover:bg-gray-200 focus:outline-none"
                                onclick="prevStep()">Voltar</button>
                            <button type="button"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-700 focus:outline-none"
                                onclick="nextStep()">Próximo</button>
                        </div>
                    </section>

                    <!-- Step 3: Administrador -->
                    <section class="wizard-step hidden" data-step="3">
                        <h2 class="text-base font-semibold text-gray-900">Administrador da Escola</h2>
                        <p class="text-sm text-gray-500">Crie o usuário administrador da escola.</p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-input name="admin_name" id="admin_name" label="Nome" :value="old('admin_name')" required />
                            <x-input name="admin_email" id="admin_email" label="Email" type="email"
                                :value="old('admin_email')" required />
                            <x-input name="password" id="password" label="Senha" type="password" required />
                            <x-input name="password_confirmation" id="password_confirmation" label="Confirmar Senha"
                                type="password" required />
                        </div>

                        <div class="mt-6 flex justify-between">
                            <button type="button"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md shadow hover:bg-gray-200 focus:outline-none"
                                onclick="prevStep()">Voltar</button>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Registrar
                                Escola</button>
                        </div>
                    </section>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function updateWizardUI() {
            document.querySelectorAll('.wizard-step').forEach(el => {
                const step = parseInt(el.getAttribute('data-step'));
                el.classList.toggle('hidden', step !== currentStep);
            });
            const progress = document.getElementById('wizard-progress');
            if (progress) progress.style.width = `${Math.round((currentStep/totalSteps)*100)}%`;
            document.querySelectorAll('.step-indicator').forEach(ind => {
                const step = parseInt(ind.getAttribute('data-step'));
                ind.className = 'step-indicator ' + (step <= currentStep ? 'bg-indigo-600' : 'bg-gray-300');
            });
        }

        function nextStep() {
            if (currentStep === 1) {
                if (typeof window.validateStep1 === 'function') {
                    if (!window.validateStep1()) {
                        return;
                    }
                }
            } else if (currentStep === 2) {
                if (typeof window.validateStep2 === 'function') {
                    if (!window.validateStep2()) {
                        return;
                    }
                }
            }
            if (currentStep < totalSteps) {
                currentStep++;
                updateWizardUI();
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateWizardUI();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const style = document.createElement('style');
            style.textContent = `.step-indicator{width:18px;height:18px;border-radius:9999px}`;
            document.head.appendChild(style);
            updateWizardUI();

            // ViaCEP - busca de CEP e preenchimento automático
            const cepInput = document.getElementById('cep');
            const btnBuscarCep = document.getElementById('btn-buscar-cep');
            const iconCep = document.getElementById('icon-cep');
            const enderecoInput = document.getElementById('endereco');
            const bairroInput = document.getElementById('bairro');
            const cidadeInput = document.getElementById('cidade');
            const estadoInput = document.getElementById('estado');
            const telefoneInput = document.getElementById('telefone');
            const celularInput = document.getElementById('celular');
            const cnpjInput = document.getElementById('cnpj');
            const escolaNomeInput = document.getElementById('escola_nome');
            const escolaEmailInput = document.getElementById('escola_email');

            function sanitizeCep(v) {
                return (v || '').toString().replace(/\D/g, '').slice(0, 8);
            }

            function setCepLoading(loading) {
                if (!btnBuscarCep || !iconCep) return;
                btnBuscarCep.disabled = !!loading;
                btnBuscarCep.classList.toggle('opacity-50', !!loading);
                iconCep.classList.toggle('animate-spin', !!loading);
                // trava campos de endereço durante busca
                [enderecoInput, bairroInput, cidadeInput, estadoInput].forEach((el) => {
                    if (!el) return;
                    el.readOnly = !!loading;
                    el.classList.toggle('bg-gray-50', !!loading);
                });
            }

            function ensureCepAlertContainer() {
                const cepWrapper = cepInput?.closest('.mb-4');
                if (!cepWrapper) return null;
                let box = document.getElementById('cep-alert-box');
                if (!box) {
                    box = document.createElement('div');
                    box.id = 'cep-alert-box';
                    box.className = 'mt-2 hidden';
                    cepWrapper.appendChild(box);
                }
                return box;
            }

            function showCepAlert(type, msg) {
                const box = ensureCepAlertContainer();
                if (!box) return;
                if (!msg) {
                    box.classList.add('hidden');
                    box.innerHTML = '';
                    return;
                }
                const styles = type === 'error' ?
                    'rounded-md bg-red-50 p-3 border border-red-200 text-red-800' :
                    'rounded-md bg-yellow-50 p-3 border border-yellow-200 text-yellow-800';
                box.className = 'mt-2 ' + styles;
                // ARIA para acessibilidade
                if (type === 'error') {
                    box.setAttribute('role', 'alert');
                    box.setAttribute('aria-live', 'assertive');
                } else {
                    box.setAttribute('role', 'status');
                    box.setAttribute('aria-live', 'polite');
                }
                box.innerHTML = `<div class="flex">` +
                    `<div class="ml-2 text-sm">${msg}</div>` +
                    `</div>`;
                box.classList.remove('hidden');
            }

            // Utilidades de alerta por campo (erro)
            function ensureFieldAlert(inputEl, id) {
                if (!inputEl) return null;
                const wrap = inputEl.closest('.mb-4') || inputEl.parentElement;
                if (!wrap) return null;
                let box = document.getElementById(id);
                if (!box) {
                    box = document.createElement('div');
                    box.id = id;
                    box.className = 'mt-2 hidden';
                    wrap.appendChild(box);
                }
                return box;
            }

            function showFieldError(inputEl, id, msg) {
                const box = ensureFieldAlert(inputEl, id);
                if (!box) return;
                if (!msg) {
                    box.classList.add('hidden');
                    box.innerHTML = '';
                    inputEl?.removeAttribute('aria-invalid');
                    return;
                }
                box.className = 'mt-2 rounded-md bg-red-50 p-3 border border-red-200 text-red-800';
                box.setAttribute('role', 'alert');
                box.setAttribute('aria-live', 'assertive');
                box.innerHTML = `<div class="text-sm">${msg}</div>`;
                box.classList.remove('hidden');
                if (inputEl) {
                    inputEl.setAttribute('aria-invalid', 'true');
                }
            }

            // Máscaras leves
            function maskCEP(v) {
                const s = sanitizeCep(v);
                if (s.length <= 5) return s;
                return s.slice(0, 5) + '-' + s.slice(5, 8);
            }

            function maskPhone(v) {
                const d = (v || '').replace(/\D/g, '').slice(0, 11);
                if (d.length <= 10) {
                    // (XX) XXXX-XXXX
                    const p1 = d.slice(0, 2),
                        p2 = d.slice(2, 6),
                        p3 = d.slice(6, 10);
                    return (p1 ? `(${p1}` : '') + (p1 && p1.length === 2 ? ') ' : (p1 ? '' : '')) + (p2 || '') + (
                        p3 ? `-${p3}` : '');
                } else {
                    // (XX) XXXXX-XXXX
                    const p1 = d.slice(0, 2),
                        p2 = d.slice(2, 7),
                        p3 = d.slice(7, 11);
                    return (p1 ? `(${p1}` : '') + (p1 && p1.length === 2 ? ') ' : (p1 ? '' : '')) + (p2 || '') + (
                        p3 ? `-${p3}` : '');
                }
            }

            function maskCNPJ(v) {
                const d = (v || '').replace(/\D/g, '').slice(0, 14);
                const p1 = d.slice(0, 2);
                const p2 = d.slice(2, 5);
                const p3 = d.slice(5, 8);
                const p4 = d.slice(8, 12);
                const p5 = d.slice(12, 14);
                let out = '';
                if (p1) out += p1.length === 2 ? `${p1}.` : p1;
                if (p2) out += p2.length === 3 ? `${p2}.` : p2;
                if (p3) out += p3.length === 3 ? `${p3}/` : p3;
                if (p4) out += p4.length === 4 ? `${p4}-` : p4;
                if (p5) out += p5;
                return out;
            }

            // CNPJ - sanitização e validação com dígitos verificadores
            function sanitizeCNPJ(v) {
                return (v || '').replace(/\D/g, '').slice(0, 14);
            }

            function isValidCNPJ(v) {
                const c = sanitizeCNPJ(v);
                if (c.length !== 14) return false;
                if (/^(\d)\1{13}$/.test(c)) return false; // todos dígitos iguais
                const calcDV = (base, pesos) => {
                    let s = 0;
                    for (let i = 0; i < pesos.length; i++) {
                        s += parseInt(base[i], 10) * pesos[i];
                    }
                    const r = s % 11;
                    return (r < 2) ? 0 : 11 - r;
                };
                const dv1 = calcDV(c.slice(0, 12), [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
                const dv2 = calcDV(c.slice(0, 12) + dv1, [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
                return c.endsWith(`${dv1}${dv2}`);
            }

            function validateStep1() {
                let ok = true;
                const focusQueue = [];
                // Nome da Escola
                if (!escolaNomeInput || !escolaNomeInput.value.trim()) {
                    showFieldError(escolaNomeInput, 'escola-nome-alert', 'Informe o Nome da Escola.');
                    ok = false;
                    if (escolaNomeInput) focusQueue.push(escolaNomeInput);
                } else {
                    showFieldError(escolaNomeInput, 'escola-nome-alert', '');
                }
                // CNPJ
                if (!cnpjInput || !isValidCNPJ(cnpjInput.value)) {
                    showFieldError(cnpjInput, 'cnpj-alert-box', 'CNPJ inválido. Verifique os dígitos.');
                    ok = false;
                    if (cnpjInput) focusQueue.push(cnpjInput);
                } else {
                    showFieldError(cnpjInput, 'cnpj-alert-box', '');
                }
                // Email da escola
                const email = escolaEmailInput?.value?.trim() || '';
                const emailOk = /.+@.+\..+/.test(email);
                if (!emailOk) {
                    showFieldError(escolaEmailInput, 'escola-email-alert', 'Informe um email válido da escola.');
                    ok = false;
                    if (escolaEmailInput) focusQueue.push(escolaEmailInput);
                } else {
                    showFieldError(escolaEmailInput, 'escola-email-alert', '');
                }
                // Celular (11 dígitos)
                const celDigits = (celularInput?.value || '').replace(/\D/g, '');
                if (celDigits.length !== 11) {
                    showFieldError(celularInput, 'celular-alert', 'Informe um celular válido com 11 dígitos.');
                    ok = false;
                    if (celularInput) focusQueue.push(celularInput);
                } else {
                    showFieldError(celularInput, 'celular-alert', '');
                }
                // CEP (8 dígitos)
                const cepDigits = (cepInput?.value || '').replace(/\D/g, '');
                if (cepDigits.length !== 8) {
                    showCepAlert('error', 'Informe um CEP válido com 8 dígitos.');
                    ok = false;
                    if (cepInput) focusQueue.push(cepInput);
                } else {
                    showCepAlert(null, '');
                }

                if (!ok && focusQueue.length) {
                    focusQueue[0].focus();
                }
                return ok;
            }
            // disponibiliza no escopo global para o nextStep()
            window.validateStep1 = validateStep1;

            function validateStep2() {
                const planSelected = document.querySelector('input[name="plan_id"]:checked');
                if (!planSelected) {
                    // Tenta focar ou rolar para a área de planos
                    const planSection = document.querySelector('[role="radiogroup"]');
                    if (planSection) planSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    // Mostrar alerta
                    let alertBox = document.getElementById('plan-alert-box');
                    if (!alertBox) {
                        alertBox = document.createElement('div');
                        alertBox.id = 'plan-alert-box';
                        alertBox.className =
                            'mt-4 rounded-md bg-red-50 p-4 border border-red-200 text-red-800 text-sm';
                        planSection.parentNode.insertBefore(alertBox, planSection);
                    }
                    alertBox.innerHTML = '<strong>Atenção:</strong> Por favor, selecione um plano para continuar.';
                    alertBox.classList.remove('hidden');
                    return false;
                }
                const alertBox = document.getElementById('plan-alert-box');
                if (alertBox) alertBox.classList.add('hidden');
                return true;
            }
            window.validateStep2 = validateStep2;

            async function buscarCep() {
                if (!cepInput) return;
                const cep = sanitizeCep(cepInput.value);
                if (cep.length !== 8) {
                    showCepAlert('error', 'CEP inválido. Digite 8 dígitos (ex.: 12345-678).');
                    cepInput.focus();
                    return;
                }
                showCepAlert(null, '');
                setCepLoading(true);
                try {
                    const resp = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!resp.ok) {
                        throw new Error('Falha ao consultar o CEP');
                    }
                    const data = await resp.json();
                    if (data.erro) {
                        showCepAlert('error', 'CEP não encontrado. Verifique e tente novamente.');
                        return;
                    }
                    if (enderecoInput) {
                        enderecoInput.value = data.logradouro || '';
                        if (!data.logradouro) {
                            enderecoInput.placeholder = 'Avenida s/n';
                            showCepAlert('warn', 'Logradouro não informado pelo CEP. Preencha manualmente.');
                            enderecoInput.focus();
                        }
                    }
                    if (bairroInput) bairroInput.value = data.bairro || '';
                    if (cidadeInput) cidadeInput.value = data.localidade || '';
                    if (estadoInput) estadoInput.value = data.uf || '';
                    // Foca no número para completar
                    const numeroInput = document.getElementById('numero');
                    if (numeroInput) {
                        numeroInput.focus();
                    }
                } catch (e) {
                    console.error(e);
                    showCepAlert('error', 'Não foi possível buscar o CEP agora. Tente novamente.');
                } finally {
                    setCepLoading(false);
                }
            }

            if (btnBuscarCep) {
                btnBuscarCep.addEventListener('click', buscarCep);
            }
            if (cepInput) {
                cepInput.addEventListener('keydown', (ev) => {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        buscarCep();
                    }
                });
                cepInput.addEventListener('blur', () => {
                    const v = sanitizeCep(cepInput.value);
                    if (v.length === 8) {
                        buscarCep();
                    }
                });
                cepInput.addEventListener('input', () => {
                    cepInput.value = maskCEP(cepInput.value);
                });
            }

            if (telefoneInput) {
                telefoneInput.addEventListener('input', () => {
                    telefoneInput.value = maskPhone(telefoneInput.value);
                });
            }
            if (celularInput) {
                celularInput.addEventListener('input', () => {
                    celularInput.value = maskPhone(celularInput.value);
                });
            }
            if (cnpjInput) {
                cnpjInput.addEventListener('input', () => {
                    cnpjInput.value = maskCNPJ(cnpjInput.value);
                });
                cnpjInput.addEventListener('blur', () => {
                    if (cnpjInput.value.trim() === '') {
                        showFieldError(cnpjInput, 'cnpj-alert-box', '');
                        return;
                    }
                    if (!isValidCNPJ(cnpjInput.value)) {
                        showFieldError(cnpjInput, 'cnpj-alert-box', 'CNPJ inválido. Verifique os dígitos.');
                    } else {
                        showFieldError(cnpjInput, 'cnpj-alert-box', '');
                    }
                });
            }

            if (estadoInput) {
                estadoInput.addEventListener('input', () => {
                    estadoInput.value = (estadoInput.value || '').toUpperCase().slice(0, 2);
                });
            }

            // Dinâmica de preço: total = plano + addons
            const planRadios = document.querySelectorAll('input[name="plan_id"]');
            const moduleChecks = document.querySelectorAll('input[name="modules[]"]');
            const summaryTotalEl = document.getElementById('summary-total');
            const summaryBreakdownEl = document.getElementById('summary-breakdown');
            const currencyFmt = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });

            function getSelectedPlanPrice() {
                const sel = Array.from(planRadios).find(r => r.checked);
                if (!sel) return 0;
                const p = parseFloat(sel.dataset.price || '0');
                return isNaN(p) ? 0 : p;
            }

            function getAddonsSum() {
                let s = 0;
                moduleChecks.forEach(ch => {
                    if (ch.checked) {
                        const p = parseFloat(ch.dataset.price || '0');
                        if (!isNaN(p)) s += p;
                    }
                });
                return s;
            }

            function updatePricingSummary() {
                if (!summaryTotalEl || !summaryBreakdownEl) return;
                const plan = getSelectedPlanPrice();
                const addons = getAddonsSum();
                const total = plan + addons;
                summaryTotalEl.textContent = `${currencyFmt.format(total)}/mês`;
                summaryBreakdownEl.textContent =
                    `Plano: ${currencyFmt.format(plan)} + Addons: ${currencyFmt.format(addons)}`;

                // Atualiza texto dos botões de seleção
                planRadios.forEach(r => {
                    const btn = r.parentNode.querySelector('.select-button-text');
                    if (btn) {
                        if (r.checked) {
                            btn.textContent = 'Selecionado';
                            btn.classList.add('bg-green-600');
                            btn.classList.remove('bg-indigo-600');
                        } else {
                            btn.textContent = 'Selecionar';
                            btn.classList.remove('bg-green-600');
                            btn.classList.add('bg-indigo-600');
                        }
                    }
                });
            }
            planRadios.forEach(r => r.addEventListener('change', updatePricingSummary));
            moduleChecks.forEach(ch => ch.addEventListener('change', updatePricingSummary));
            // Inicializa resumo ao carregar a etapa
            updatePricingSummary();

            // Validação no submit do formulário (garantia extra)
            const form = document.querySelector('form[action*="register.escola.submit"]') || document.querySelector(
                'form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    if (!validateStep1()) {
                        e.preventDefault();
                        currentStep = 1;
                        updateWizardUI();
                    }
                });
            }
        });
    </script>
@endsection
