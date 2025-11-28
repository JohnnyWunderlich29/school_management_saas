@extends('layouts.app')

@section('content')
    @if(auth()->check() && is_null(auth()->user()->welcome_seen_at))
        <x-modal name="welcome-modal" :show="true" title="Bem-vindo ao Sistema de Gerenciamento Escolar! 🎉">
            <div class="space-y-4">
                <div class="text-gray-700 space-y-3">
                    <p class="text-sm leading-relaxed">
                        É um prazer recebê-lo nesta jornada que vai transformar a gestão da sua instituição de ensino. Estamos genuinamente felizes por você estar aqui e prontos para acompanhar cada passo seu rumo a uma rotina mais organizada, eficiente e conectada.
                    </p>
                    
                    <p class="text-sm leading-relaxed">
                        Esta plataforma foi cuidadosamente desenvolvida pensando nas necessidades reais de diretores, coordenadores, professores, secretários, alunos e responsáveis. Nosso objetivo? Unir todos em um único ambiente digital seguro, intuitivo e colaborativo, eliminando planilhas perdidas, e-mails confusos e comunicações fragmentadas.
                    </p>
                    
                    <p class="text-sm leading-relaxed">
                        Seja para registrar uma nota às pressas, enviar um comunicado urgente sobre suspensão de aulas, consultar o histórico de um aluno ou planejar o calendário letivo do próximo semestre — tudo está a poucos cliques, de forma simples e segura.
                    </p>
                    
                    <p class="text-sm font-medium text-indigo-600">
                        Estamos aqui por você!
                    </p>
                </div>
                
                <div class="pt-2 flex justify-end">
                    <button id="welcome-dismiss-btn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium">
                        Começar
                    </button>
                </div>
            </div>
        </x-modal>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.getElementById('welcome-dismiss-btn');
                if (!btn) return;
                btn.addEventListener('click', async function() {
                    try {
                        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                        const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
                        await fetch("{{ route('welcome.dismiss') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            }
                        });
                    } catch (e) {
                        console.error('Falha ao registrar boas-vindas vistas:', e);
                    } finally {
                        // Fecha o modal na interface imediatamente
                        if (typeof closeModal === 'function') {
                            closeModal('welcome-modal');
                        } else {
                            const overlay = document.querySelector('[x-data]');
                            if (overlay) overlay.classList.add('hidden');
                        }
                    }
                });
            });
        </script>
    @endif
    <!-- Header Section -->
    <x-card class="mb-6 md:mb-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
            <div>
                <h1 class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900">Dashboard Analítico</h1>
                <p class="mt-1 md:mt-2 text-sm md:text-base text-gray-600">Visão geral e análise de dados do sistema escolar
                </p>
            </div>
            <div class="flex items-center space-x-3 gap-2">
                <div class="text-right">
                    <p class="text-xs md:text-sm text-gray-500">Última atualização</p>
                    <p class="text-xs md:text-sm font-medium text-gray-900">{{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Alunos -->
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-4 md:p-5 bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-blue-100 bg-opacity-30 p-2 md:p-3">
                        <i class="fas fa-user-graduate text-white text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-3 md:ml-5 min-w-0 flex-1">
                        <h3 class="text-xs md:text-sm font-medium text-blue-100 truncate">Alunos</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-xl md:text-2xl font-semibold text-white">{{ $totalAlunos ?? 0 }}</p>
                            <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-blue-100 hidden sm:block">cadastrados
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @permission('alunos.listar')
                <div class="px-4 md:px-5 py-3 bg-gray-50">
                    <a href="{{ route('alunos.index') }}"
                        class="text-sm text-blue-600 hover:text-blue-500 font-medium flex items-center group">
                        Ver todos
                        <i class="fas fa-arrow-right ml-1 text-xs group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            @endpermission
        </div>

        <!-- Responsáveis -->
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-4 md:p-5 bg-gradient-to-r from-green-500 to-green-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-green-100 bg-opacity-30 p-2 md:p-3">
                        <i class="fas fa-users text-white text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-3 md:ml-5 min-w-0 flex-1">
                        <h3 class="text-xs md:text-sm font-medium text-green-100 truncate">Responsáveis</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-xl md:text-2xl font-semibold text-white">{{ $totalResponsaveis ?? 0 }}</p>
                            <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-green-100 hidden sm:block">
                                registrados</p>
                        </div>
                    </div>
                </div>
            </div>
            @permission('responsaveis.listar')
                <div class="px-4 md:px-5 py-3 bg-gray-50">
                    <a href="{{ route('responsaveis.index') }}"
                        class="text-sm text-green-600 hover:text-green-500 font-medium flex items-center group">
                        Ver todos
                        <i class="fas fa-arrow-right ml-1 text-xs group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            @endpermission
        </div>

        <!-- Funcionários -->
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-4 md:p-5 bg-gradient-to-r from-purple-500 to-purple-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-purple-100 bg-opacity-30 p-2 md:p-3">
                        <i class="fas fa-user-tie text-white text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-3 md:ml-5 min-w-0 flex-1">
                        <h3 class="text-xs md:text-sm font-medium text-purple-100 truncate">Funcionários</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-xl md:text-2xl font-semibold text-white">{{ $totalFuncionarios ?? 0 }}</p>
                            <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-purple-100 hidden sm:block">ativos
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            @permission('funcionarios.listar')
                <div class="px-4 md:px-5 py-3 bg-gray-50">
                    <a href="{{ route('funcionarios.index') }}"
                        class="text-sm text-purple-600 hover:text-purple-500 font-medium flex items-center group">
                        Ver todos
                        <i class="fas fa-arrow-right ml-1 text-xs group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            @endpermission
        </div>

        <!-- Salas -->
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-4 md:p-5 bg-gradient-to-r from-orange-500 to-orange-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-orange-100 bg-opacity-30 p-2 md:p-3">
                        <i class="fas fa-door-open text-white text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-3 md:ml-5 min-w-0 flex-1">
                        <h3 class="text-xs md:text-sm font-medium text-orange-100 truncate">Salas</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-xl md:text-2xl font-semibold text-white">{{ $totalSalas ?? 0 }}</p>
                            <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-orange-100 hidden sm:block">
                                disponíveis</p>
                        </div>
                    </div>
                </div>
            </div>
            @permission('salas.listar')
                <div class="px-4 md:px-5 py-3 bg-gray-50">
                    <a href="{{ route('salas.index') }}"
                        class="text-sm text-orange-600 hover:text-orange-500 font-medium flex items-center group">
                        Ver todas
                        <i class="fas fa-arrow-right ml-1 text-xs group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            @endpermission
        </div>

        <!-- Presenças -->
        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
            <div class="p-4 md:p-5 bg-gradient-to-r from-yellow-500 to-yellow-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-yellow-100 bg-opacity-30 p-2 md:p-3">
                        <i class="fas fa-clipboard-check text-white text-lg md:text-xl"></i>
                    </div>
                    <div class="ml-3 md:ml-5 min-w-0 flex-1">
                        <h3 class="text-xs md:text-sm font-medium text-yellow-100 truncate">Presenças Hoje</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-xl md:text-2xl font-semibold text-white">{{ $presencasHoje ?? 0 }}</p>
                            <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-yellow-100 hidden sm:block">
                                registradas</p>
                        </div>
                    </div>
                </div>
            </div>
            @permission('presencas.listar')
                <div class="px-4 md:px-5 py-3 bg-gray-50">
                    <a href="{{ route('presencas.index') }}"
                        class="text-sm text-yellow-600 hover:text-yellow-500 font-medium flex items-center group">
                        Ver todos
                        <i class="fas fa-arrow-right ml-1 text-xs group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            @endpermission
        </div>
    </div>

    @if (function_exists('moduleEnabled') ? moduleEnabled('financeiro_module') : (config('features.modules.financeiro_module') ?? true))
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
                <input id="financeMonth" type="month" class="text-sm border rounded-md px-2 py-1 bg-white text-gray-900" value="{{ now()->format('Y-m') }}" title="Selecione o mês para atualizar os gráficos do Financeiro">
                </div>
                <button id="btnPersonalizarCards" type="button" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                    <i class="fas fa-sliders-h mr-2"></i>
                    Personalizar cards
                </button>
            </div>
        </div>

        <div id="financeCardsGrid" class="mt-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" data-storage-key="dashboard.finance.cards">
            <!-- Card: Receitas -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]" data-card-key="finance-receitas">
                <div class="p-4 md:p-5 bg-gradient-to-r from-emerald-500 to-emerald-600 min-h-[88px] flex items-center max-h-[112px]" data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-emerald-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-coins text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-emerald-100 truncate">
Receitas (mês)
<span class="ml-2 inline-block" data-total-for="receitas-total">- Total R$ {{ number_format(($receitasTotalMesCents ?? 0) / 100, 2, ',', '.') }}</span>
                            </h3>
                            <div class="mt-1 flex items-baseline space-x-4">
                                <div>
<p class="text-xl md:text-2xl font-semibold text-white" data-total-for="receitas-recebido">R$ {{ number_format(($receitasRecebidasMesCents ?? 0) / 100, 2, ',', '.') }}</p>
                                    <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-emerald-100 hidden sm:block">recebidas</p>
                                </div>
                                <div class="border-l border-emerald-200 pl-4">
                                    <p class="text-xl md:text-2xl font-semibold text-white" data-total-for="receitas-pendentes">R$ {{ number_format(($receitasPendentesMesCents ?? 0) / 100, 2, ',', '.') }}</p>
                                    <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-emerald-100 hidden sm:block">pendentes</p>
                                </div>
                            </div>
                        </div>
                        <div class="ml-2 hidden sm:flex items-center text-emerald-50/90">
                            <label for="receitasBase" class="text-[11px] mr-1">Base:</label>
                            <select id="receitasBase" class="text-[11px] bg-emerald-600/10 border border-emerald-200/40 text-emerald-50 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-emerald-200/60">
                                <option value="due_date" selected>due_date</option>
                                <option value="paid_at">paid_at</option>
                            </select>
                        </div>
                        <button type="button" class="ml-2 text-emerald-50/80 hover:text-white" title="KPIs Total/Pendentes podem alternar a base (due_date | paid_at) no seletor. Recebido e sparkline usam paid_at.">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-emerald-50 hover:text-white" data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">

                    <div class="flex-1 relative">
                        <canvas id="sparkReceitas" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkReceitas">Sem dados no mês selecionado</p>
                </div>
            </div>

            <!-- Card: Recebimentos pendentes -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[360px]" data-card-key="finance-recebimentos-pendentes">
                <div class="p-4 md:p-5 bg-gradient-to-r from-indigo-500 to-indigo-600 min-h-[88px] flex items-center" data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-indigo-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-hand-holding-usd text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-indigo-100 truncate">Receitas pendentes</h3>
                            <div class="mt-1 flex items-baseline space-x-4">
                                <div>
                                    <p class="text-sm md:text-base font-semibold text-white">A vencer: <span data-total-for="pendentes-avencer">R$ {{ number_format(($totalPendentesAVencerCents ?? 0) / 100, 2, ',', '.') }}</span></p>
                                </div>
                                <div class="border-l border-indigo-200 pl-4">
                                    <p class="text-sm md:text-base font-semibold text-white">Vencidos: <span data-total-for="pendentes-vencidos">R$ {{ number_format(($totalPendentesVencidasCents ?? 0) / 100, 2, ',', '.') }}</span></p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.recebimentos.index') }}" class="text-xs md:text-sm text-indigo-50 hover:text-white underline underline-offset-2 mr-2">Ver todos</a>
                        <button class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-indigo-50 hover:text-white" data-action="hide-card" title="Ocultar">
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
                                    <div class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                                        <div class="text-xs text-gray-700">
                                            <span class="inline-block w-14 text-gray-500">{{ optional($inv->due_date)->format('d/m') }}</span>

                                            @php $__payer = trim(($inv->payer_nome ?? '').' '.($inv->payer_sobrenome ?? '')); @endphp
                                            @if(!empty($__payer))
                                                <span class="ml-2 text-gray-500">— {{ $__payer }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-semibold text-gray-900">R$ {{ number_format(($inv->total_cents ?? 0) / 100, 2, ',', '.') }}</div>
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
                                    <div class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                                        <div class="text-xs text-gray-700">
                                            <span class="inline-block w-14 text-gray-500">{{ optional($inv->due_date)->format('d/m') }}</span>
                                            @php $__payer = trim(($inv->payer_nome ?? '').' '.($inv->payer_sobrenome ?? '')); @endphp
                                            @if(!empty($__payer))
                                                <span class="ml-2 text-gray-500">— {{ $__payer }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-semibold text-gray-900">R$ {{ number_format(($inv->total_cents ?? 0) / 100, 2, ',', '.') }}</div>
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
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[360px]" data-card-key="finance-despesas-pendentes">
                <div class="p-4 md:p-5 bg-gradient-to-r from-rose-500 to-rose-600 min-h-[88px] flex items-center" data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-rose-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-wallet text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-rose-100 truncate">Despesas pendentes</h3>
                            <div class="mt-1 flex items-baseline space-x-4">
                                <div>
                                    <p class="text-sm md:text-base font-semibold text-white">A vencer: <span data-total-for="despesas-pend-avencer">R$ {{ number_format(($totalDespesasPendAVencer ?? 0), 2, ',', '.') }}</span></p>
                                </div>
                                <div class="border-l border-rose-200 pl-4">
                                    <p class="text-sm md:text-base font-semibold text-white">Vencidas: <span data-total-for="despesas-pend-vencidas">R$ {{ number_format(($totalDespesasPendVencidas ?? 0), 2, ',', '.') }}</span></p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.despesas.index') }}" class="text-xs md:text-sm text-rose-50 hover:text-white underline underline-offset-2 mr-2">Ver todos</a>
                        <button class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-rose-50 hover:text-white" data-action="hide-card" title="Ocultar">
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
                                    <div class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                                        <div class="text-xs text-gray-700">
                                            <span class="inline-block w-14 text-gray-500">{{ optional($d->data)->format('d/m') }}</span>
                                            <span class="ml-2">{{ $d->descricao }}</span>
                                            @if(!empty($d->categoria))
                                                <span class="ml-2 text-gray-500">— {{ $d->categoria }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-semibold text-gray-900">R$ {{ number_format(($d->valor ?? 0), 2, ',', '.') }}</div>
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
                                    <div class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                                        <div class="text-xs text-gray-700">
                                            <span class="inline-block w-14 text-gray-500">{{ optional($d->data)->format('d/m') }}</span>
                                            <span class="ml-2">{{ $d->descricao }}</span>
                                            @if(!empty($d->categoria))
                                                <span class="ml-2 text-gray-500">— {{ $d->categoria }}</span>
                                            @endif
                                        </div>
                                        <div class="text-xs font-semibold text-gray-900">R$ {{ number_format(($d->valor ?? 0), 2, ',', '.') }}</div>
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
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]" data-card-key="finance-despesas">
                <div class="p-4 md:p-5 bg-gradient-to-r from-rose-500 to-rose-600 min-h-[88px] flex items-center" data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-rose-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-file-invoice-dollar text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-rose-100 truncate">
                                Despesas (mês)
                                <span class="ml-2 inline-block" data-total-for="despesas-total">Total - R$ {{ number_format(($despesaMensalLiquidadas ?? 0) + ($despesaMensalPendentes ?? 0), 2, ',', '.') }}</span>
                            </h3>
                            <div class="mt-1 flex items-baseline space-x-4">
                                <div>
                                    <p class="text-xl md:text-2xl font-semibold text-white" data-total-for="despesas">R$ {{ number_format($despesaMensalLiquidadas ?? 0, 2, ',', '.') }}</p>
                                    <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-rose-100 hidden sm:block">liquidadas</p>
                                </div>
                                <div class="border-l border-rose-200 pl-4">
                                    <p class="text-xl md:text-2xl font-semibold text-white" data-total-for="despesas-pendentes">R$ {{ number_format($despesaMensalPendentes ?? 0, 2, ',', '.') }}</p>
                                    <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-rose-100 hidden sm:block">pendentes</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="ml-2 text-rose-50/80 hover:text-white" title="Despesas diárias (Despesa.valor) somadas por data.">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-rose-50 hover:text-white" data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="flex-1 relative">
                        <canvas id="sparkDespesas" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkDespesas">Sem dados no mês selecionado</p>
                </div>
            </div>

            <!-- Card: Inadimplência -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]" data-card-key="finance-inadimplencia">
                <div class="p-4 md:p-5 bg-gradient-to-r from-orange-500 to-orange-600 min-h-[88px] flex items-center" data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-orange-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-exclamation-circle text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-orange-100 truncate">Inadimplência (mês)</h3>
                            <div class="mt-1 flex items-baseline space-x-4">
                                <div>
                                    <p class="text-xl md:text-2xl font-semibold text-white">{{ $taxaInadimplenciaPercentual ?? 0 }}%</p>
                                    <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-orange-100 hidden sm:block">taxa</p>
                                </div>
                                <div class="border-l border-orange-200 pl-4">
                                    <p class="text-xl md:text-2xl font-semibold text-white">R$ {{ number_format(($valorInadimplenciaCents ?? 0) / 100, 2, ',', '.') }}</p>
                                    <p class="ml-1 md:ml-2 text-xs md:text-sm font-medium text-orange-100 hidden sm:block">em aberto</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="ml-2 text-orange-50/80 hover:text-white" title="Faturas vencidas não pagas por dia (Invoice.status != paid/canceled).">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-orange-50 hover:text-white" data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="flex-1 relative">
                        <canvas id="sparkInadimplencia" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkInadimplencia">Sem dados no mês selecionado</p>
                </div>
            </div>

            <!-- Card: Tickets Abertos -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]" data-card-key="finance-tickets">
                <div class="p-4 md:p-5 bg-gradient-to-r from-indigo-500 to-indigo-600 min-h-[88px] flex items-center" data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-indigo-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-life-ring text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-indigo-100 truncate">Tickets abertos</h3>
                            <div class="mt-1 flex items-baseline">
                                <p class="text-xl md:text-2xl font-semibold text-white" data-total-for="tickets">{{ $ticketsAbertosCount ?? 0 }}</p>
                                <p class="ml-2 text-xs md:text-sm font-medium text-indigo-100 hidden sm:block">suporte</p>
                            </div>
                        </div>
                        <button type="button" class="ml-2 text-indigo-50/80 hover:text-white" title="Conversas de suporte ativas criadas por dia (Conversa.tipo = suporte, ativo = true), filtradas por escola.">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-indigo-50 hover:text-white" data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="flex-1 relative">
                        <canvas id="sparkTickets" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkTickets">Sem dados no mês selecionado</p>
                </div>
            </div>

            <!-- Card: MRR -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]" data-card-key="finance-mrr">
                <div class="p-4 md:p-5 bg-gradient-to-r from-teal-500 to-teal-600 min-h-[88px] flex items-center" data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-teal-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-sync-alt text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-teal-100 truncate">MRR</h3>
                            <div class="mt-1 flex items-baseline">
                                <p class="text-xl md:text-2xl font-semibold text-white">R$ {{ number_format(($mrrCents ?? 0) / 100, 2, ',', '.') }}</p>
                                <p class="ml-2 text-xs md:text-sm font-medium text-teal-100 hidden sm:block">recorrente</p>
                            </div>
                        </div>
                        <button type="button" class="ml-2 text-teal-50/80 hover:text-white" title="Receita recorrente mensal de assinaturas ativas (Subscription.amount_cents). Este gráfico não é vinculado ao mês selecionado.">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-teal-50 hover:text-white" data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="flex-1 relative">
                        <canvas id="sparkMrr" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkMrr">Sem dados no mês selecionado</p>
                </div>
            </div>

            <!-- Card: Método Predominante -->
            <div class="bg-gray-50 rounded-lg shadow overflow-hidden group flex flex-col h-[200px]" data-card-key="finance-metodo">
                <div class="p-4 md:p-5 bg-gradient-to-r from-cyan-500 to-cyan-600 min-h-[88px] flex items-center" data-card-header draggable="true">
                    <div class="flex items-center w-full">
                        <div class="flex-shrink-0 rounded-md bg-cyan-100 bg-opacity-30 p-2 md:p-3">
                            <i class="fas fa-credit-card text-white text-lg md:text-xl"></i>
                        </div>
                        <div class="ml-3 md:ml-5 min-w-0 flex-1">
                            <h3 class="text-xs md:text-sm font-medium text-cyan-100 truncate">Método predominante</h3>
                            <div class="mt-1 flex items-baseline">
                                <p class="text-xl md:text-2xl font-semibold text-white">{{ $metodoPredominanteLabel ?? '—' }}</p>
                            </div>
                        </div>
                        <button type="button" class="ml-2 text-cyan-50/80 hover:text-white" title="Tendência de receitas líquidas; método predominante do mês via Payments.method.">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button class="ml-2 opacity-0 group-hover:opacity-100 transition-opacity text-cyan-50 hover:text-white" data-action="hide-card" title="Ocultar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="px-4 md:px-5 py-3 bg-gray-50 flex-1 flex flex-col min-h-0">
                    <div class="flex-1 relative">
                        <canvas id="sparkMetodo" class="absolute inset-0 w-full h-full"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 hidden" data-no-data-for="sparkMetodo">Sem dados no mês selecionado</p>
                </div>
            </div>
            <!-- Card: Espaço em branco -->
            <div class="bg-white rounded-lg border border-dashed border-gray-300 overflow-hidden group flex flex-col h-[200px]" data-card-key="finance-blank">
                <div class="px-4 py-2 bg-gray-50 flex items-center justify-between" data-card-header draggable="true">
                    <div class="flex items-center text-gray-400 text-xs">
                        <i class="fas fa-border-none mr-2"></i>
                        <span>Espaço em branco</span>
                    </div>
                    <button class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-gray-600" data-action="hide-card" title="Ocultar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="flex-1 bg-white"></div>
            </div>
        </div>
    </x-card>

    <!-- Modal de Personalização (componente) -->
    <x-personalize-modal id="modalPersonalizarCards" title="Personalizar cards" errorId="modalPrefError">
        <x-slot name="body">
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-coins text-emerald-600 mr-2"></i><span class="text-sm font-medium text-gray-900">Receitas (mês)</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-emerald-600" data-card-key="finance-receitas">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-file-invoice-dollar text-rose-600 mr-2"></i><span class="text-sm font-medium text-gray-900">Despesas (mês)</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-rose-600" data-card-key="finance-despesas">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-exclamation-circle text-orange-600 mr-2"></i><span class="text-sm font-medium text-gray-900">Inadimplência (mês)</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-orange-600" data-card-key="finance-inadimplencia">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-life-ring text-indigo-600 mr-2"></i><span class="text-sm font-medium text-gray-900">Tickets abertos</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-indigo-600" data-card-key="finance-tickets">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-sync-alt text-teal-600 mr-2"></i><span class="text-sm font-medium text-gray-900">MRR</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-teal-600" data-card-key="finance-mrr">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-credit-card text-cyan-600 mr-2"></i><span class="text-sm font-medium text-gray-900">Método predominante</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-cyan-600" data-card-key="finance-metodo">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-border-none text-gray-500 mr-2"></i><span class="text-sm font-medium text-gray-900">Espaço em branco</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-gray-500" data-card-key="finance-blank">
            </label>
        </x-slot>
        <x-slot name="footerRight">
            <button class="px-4 py-2 text-sm rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200" data-action="close-modal">Fechar</button>
            <button class="px-4 py-2 text-sm rounded-md bg-yellow-100 text-yellow-800 hover:bg-yellow-200" data-action="restore-default">Restaurar padrão</button>
            <button class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700 flex items-center gap-2" data-action="save-preferences">
                <span class="save-text">Salvar</span>
                <span class="save-loading hidden"><i class="fas fa-spinner fa-spin"></i></span>
            </button>
        </x-slot>
    </x-personalize-modal>
    @endif
    <!-- Quick Actions Section -->
    <x-card class="mb-6 md:mb-8">
        <x-slot name="title">
            <div class="flex items-center">
                <i class="fas fa-bolt text-indigo-600 mr-2"></i>
                Ações Rápidas
            </div>
        </x-slot>

        <x-slot name="subtitle">
            Acesso rápido às principais funcionalidades do sistema
        </x-slot>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <!-- Configuração Educacional -->
            @php
                $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
            @endphp
            @if ($escolaId)
                <a href="{{ route('admin.configuracao-educacional.show', ['escola' => $escolaId]) }}"
                    title="{{ auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte') ? 'Configuração da escola selecionada (ID: ' . $escolaId . ')' : 'Configuração da sua escola' }}"
                @else <a href="#"
                    onclick="alert('{{ auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte') ? 'Selecione uma escola no seletor acima para acessar as configurações' : 'Usuário não possui escola associada' }}')"
                    title="Nenhuma escola selecionada" @endif
                    class="group flex items-center p-4 bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg hover:from-indigo-100 hover:to-purple-100 hover:border-indigo-300 transition-all duration-200 hover:shadow-md">
                    <div
                        class="flex-shrink-0 w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center group-hover:bg-indigo-600 transition-colors">
                        <i class="fas fa-cogs text-white text-lg"></i>
                    </div>
                    <div class="ml-4 min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">
                            Configuração Educacional
                        </p>
                        <p class="text-xs text-gray-600 group-hover:text-indigo-600 transition-colors">
                            Modalidades e níveis de ensino
                        </p>
                    </div>
                    <div class="flex-shrink-0 ml-2">
                        <i
                            class="fas fa-arrow-right text-gray-400 group-hover:text-indigo-500 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </a>

                <!-- Placeholder para futuras ações rápidas -->
                <div class="flex items-center p-4 bg-gray-50 border border-gray-200 rounded-lg opacity-50">
                    <div class="flex-shrink-0 w-10 h-10 bg-gray-300 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-gray-500 text-lg"></i>
                    </div>
                    <div class="ml-4 min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-500">
                            Mais ações em breve
                        </p>
                        <p class="text-xs text-gray-400">
                            Funcionalidades adicionais
                        </p>
                    </div>
                </div>
        </div>
    </x-card>

    <!-- Analytics Section -->
    <x-card class="mb-8">
        <x-slot name="title">
            <div class="flex items-center">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                Análise de Dados
            </div>
        </x-slot>

        <x-slot name="subtitle">
            @if(request('inicio') && request('fim'))
                Período: {{ \Carbon\Carbon::parse(request('inicio'))->format('d/m/Y') }} a {{ \Carbon\Carbon::parse(request('fim'))->format('d/m/Y') }}
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
                <x-date-filter-with-arrows
                    title=" "
                    name="data_inicio"
                    label="Início"
                    :value="old('data_inicio', $analyticsInicio ?? request('inicio'))"
                    dataFimName="data_fim"
                    :dataFimValue="old('data_fim', $analyticsFim ?? request('fim'))"
                />

                <input type="hidden" id="inicio_hidden" name="inicio" value="{{ $analyticsInicio ?? request('inicio') }}">
                <input type="hidden" id="fim_hidden" name="fim" value="{{ $analyticsFim ?? request('fim') }}">
                
                <!-- DESATIVADO MOSTRAR PERIODO SELECIONADO -->
                <!--{{--@if(isset($analyticsDias))
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ (int)($analyticsDias) }} dias
                        @if(!empty($analyticsClamped) && $analyticsClamped)
                            &nbsp;(limitado a 6 meses)
                        @endif
                    </span>
                @endif --}}-->
            </div>
            <div class="flex items-center gap-2">
                <button id="aplicarPeriodo" type="button" class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-blue-600 rounded hover:bg-blue-50 text-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Aplicar
                </button>
                <a href="{{ route('dashboard', ['clear_periodo' => 1]) }}" id="limparPeriodo" class="text-blue-600 hover:text-blue-500 text-sm">Limpar</a>
            </div>
        </div>
        <div id="analyticsMetricsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
            <!-- Taxa de Presença -->
            <div class="bg-white p-4 md:p-6 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow" data-card-key="analytics-metric-presenca">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-500 rounded-lg flex items-center justify-center cursor-move" data-drag-handle draggable="true">
                        <i class="fas fa-chart-line text-white text-base md:text-lg"></i>
                    </div>
                    <span id="metricTaxaPresencaBadge"
                        class="inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if (isset($dadosAnaliticos['taxaPresencaGeral']) && $dadosAnaliticos['taxaPresencaGeral'] >= 90)
                            bg-green-100 text-green-800
                        @elseif(isset($dadosAnaliticos['taxaPresencaGeral']) && $dadosAnaliticos['taxaPresencaGeral'] >= 75)
                            bg-yellow-100 text-yellow-800
                        @else
                            bg-red-100 text-red-800
                        @endif">
                        @if (isset($dadosAnaliticos['taxaPresencaGeral']) && $dadosAnaliticos['taxaPresencaGeral'] >= 90)
                            <i class="fas fa-arrow-up mr-1"></i><span class="hidden sm:inline">Excelente</span><span class="sm:hidden">Exc</span>
                        @elseif(isset($dadosAnaliticos['taxaPresencaGeral']) && $dadosAnaliticos['taxaPresencaGeral'] >= 75)
                            <i class="fas fa-minus mr-1"></i>Bom
                        @else
                            <i class="fas fa-arrow-down mr-1"></i><span class="hidden sm:inline">Atenção</span><span class="sm:hidden">Atç</span>
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
            <div class="bg-white p-4 md:p-6 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow" data-card-key="analytics-metric-alertas">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-red-500 rounded-lg flex items-center justify-center cursor-move" data-drag-handle draggable="true">
                        <i class="fas fa-exclamation-triangle text-white text-base md:text-lg"></i>
                    </div>
                    <span id="metricAlertasBadge"
                        class="inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium 
                        @if (isset($dadosAnaliticos['alertasBaixaFrequencia']) && $dadosAnaliticos['alertasBaixaFrequencia']->count() > 0)
                            bg-red-100 text-red-800
                        @else
                            bg-green-100 text-green-800
                        @endif">
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
            <div class="bg-white p-4 md:p-6 rounded-lg border border-gray-200 shadow hover:shadow-md transition-shadow" data-card-key="analytics-metric-professores">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 md:w-12 md:h-12 bg-green-500 rounded-lg flex items-center justify-center cursor-move" data-drag-handle draggable="true">
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
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
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
                        <i class="fas fa-chalkboard-teacher text-purple-600 mr-2 cursor-move" data-drag-handle draggable="true"></i>
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
                        <i class="fas fa-exclamation-triangle text-red-600 mr-2 cursor-move" data-drag-handle draggable="true"></i>
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
                                    <p class="text-xs text-gray-600 mb-1">{{ $alerta['grupo_id'] ?? 'Turma não informada' }}
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
                                                    <div class="text-sm font-medium text-gray-900">{{ $alerta->nome ?? 'Sem nome' }}
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
                        <i class="fas fa-chart-pie text-orange-600 mr-2 cursor-move" data-drag-handle draggable="true"></i>
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
                        <i class="fas fa-calendar-check text-blue-600 mr-2 cursor-move" data-drag-handle draggable="true"></i>
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
        </div>
        </div>

    </x-card>

    <!-- Modal Personalizar Analytics (componente) -->
    <x-personalize-modal id="modalPersonalizarAnalyticsCards" title="Personalizar cards" errorId="modalAnalyticsPrefError">
        <x-slot name="body">
            <!-- Métricas (verde) -->
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Taxa de Presença</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-emerald-600" data-card-key="analytics-metric-presenca" checked>
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Alertas de Frequência</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-emerald-600" data-card-key="analytics-metric-alertas" checked>
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Professores Ativos</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-emerald-600" data-card-key="analytics-metric-professores" checked>
            </label>
            <!-- Gráficos (ciano) -->
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Presenças por Dia</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-cyan-600" data-card-key="analytics-chart-dia" checked>
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Top 5 Salas</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-cyan-600" data-card-key="analytics-chart-sala" checked>
            </label>
            <!-- Tabelas (cinza) -->
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Professores Ativos (tabela)</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-gray-500" data-card-key="analytics-table-professores" checked>
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Alertas de Frequência (tabela)</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-gray-500" data-card-key="analytics-table-alertas" checked>
            </label>
            <!-- Recentes (índigo) -->
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Atividades Recentes</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-indigo-600" data-card-key="analytics-recentes-atividades" checked>
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Estatísticas Rápidas</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-indigo-600" data-card-key="analytics-recentes-estatisticas" checked>
            </label>
            <!-- Extras (teal) -->
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Últimos Alunos</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-teal-600" data-card-key="analytics-extra-ultimos-alunos" checked>
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <span class="text-sm font-medium text-gray-900">Presenças de Hoje</span>
                <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-teal-600" data-card-key="analytics-extra-presencas-hoje" checked>
            </label>
        </x-slot>
        <x-slot name="footerLeft">
            <button class="text-xs text-blue-700 hover:underline" data-action="restore-analytics-grid" data-grid-id="analyticsMetricsGrid">Restaurar Métricas</button>
            <button class="text-xs text-blue-700 hover:underline" data-action="restore-analytics-grid" data-grid-id="analyticsChartsGrid">Restaurar Gráficos</button>
            <button class="text-xs text-blue-700 hover:underline" data-action="restore-analytics-grid" data-grid-id="analyticsTablesGrid">Restaurar Tabelas</button>
            <button class="text-xs text-blue-700 hover:underline" data-action="restore-analytics-grid" data-grid-id="analyticsRecentGrid">Restaurar Recentes</button>
            <button class="text-xs text-blue-700 hover:underline" data-action="restore-analytics-grid" data-grid-id="analyticsExtraGrid">Restaurar Extras</button>
            <button class="ml-2 px-2 py-1 text-xs rounded-md bg-yellow-100 text-yellow-800 hover:bg-yellow-200" data-action="restore-analytics-all">Restaurar tudo (Analytics)</button>
        </x-slot>
        <x-slot name="footerRight">
            <button class="px-4 py-2 text-sm rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200" data-action="close-modal">Fechar</button>
            <button class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700 flex items-center gap-2" data-action="save-analytics-preferences">
                <span class="save-text">Salvar</span>
                <span class="save-loading hidden"><i class="fas fa-spinner fa-spin"></i></span>
            </button>
        </x-slot>
    </x-personalize-modal>


@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Dados do backend
            const presencasPorDia = @json(isset($dadosAnaliticos['presencasPorDia']) ? $dadosAnaliticos['presencasPorDia'] : []);
            const presencasPorSala = @json(isset($dadosAnaliticos['presencasPorSala']) ? $dadosAnaliticos['presencasPorSala'] : []);

            // Configuração comum dos gráficos
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderWidth: 0,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            borderDash: [2, 2]
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                size: 12
                            },
                            maxRotation: 0
                        }
                    }
                }
            };

            // Gráfico de Presenças por Dia
            if (presencasPorDia.length > 0) {
                const ctxDia = document.getElementById('presencasPorDiaChart').getContext('2d');
                // Debug: inspeciona a série recebida do backend
                console.log('presencasPorDia (7d):', presencasPorDia);
                const totalRegistros7d = presencasPorDia.reduce((sum, item) => sum + (Number(item.total) || 0), 0);
                const emptyMsgEl = document.getElementById('presencasPorDiaEmptyMsg');
                if (emptyMsgEl) {
                    emptyMsgEl.classList.toggle('hidden', totalRegistros7d > 0);
                }
                window.presencasPorDiaChart = new Chart(ctxDia, {
                    type: 'line',
                    data: {
                        labels: presencasPorDia.map(item => {
                            const date = new Date(item.data);
                            return date.toLocaleDateString('pt-BR', {
                                weekday: 'short',
                                day: '2-digit'
                            });
                        }),
                        datasets: [
                            {
                                label: 'Presenças (confirmadas)',
                                data: presencasPorDia.map(item => item.presentes),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#3b82f6',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7
                            },
                            {
                                label: 'Registros (total)',
                                data: presencasPorDia.map(item => item.total),
                                borderColor: '#9CA3AF',
                                backgroundColor: 'rgba(156, 163, 175, 0.15)',
                                borderWidth: 2,
                                fill: false,
                                tension: 0.35,
                                pointBackgroundColor: '#9CA3AF',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 1,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderDash: [6, 4]
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            legend: { display: true },
                            tooltip: {
                                ...commonOptions.plugins.tooltip,
                                callbacks: {
                                    label: function(ctx) {
                                        const v = ctx.parsed.y ?? 0;
                                        const label = ctx.dataset.label || '';
                                        if (label.includes('Confirmadas')) return `Confirmadas: ${v}`;
                                        if (label.includes('Registros')) return `Registros: ${v}`;
                                        return `${label}: ${v}`;
                                    },
                                    footer: function(items) {
                                        if (!items || !items.length) return '';
                                        const idx = items[0].dataIndex;
                                        const total = presencasPorDia[idx]?.total ?? 0;
                                        const presentes = presencasPorDia[idx]?.presentes ?? 0;
                                        const ausentes = Math.max(0, total - presentes);
                                        return `Ausentes: ${ausentes}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            ...commonOptions.scales,
                            y: {
                                ...commonOptions.scales.y,
                                ticks: { ...commonOptions.scales.y.ticks, precision: 0, stepSize: 1 }
                            }
                        }
                    }
                });
            }

            // Gráfico de Presenças por Sala
            if (presencasPorSala.length > 0) {
                const ctxSala = document.getElementById('presencasPorSalaChart').getContext('2d');
                window.presencasPorSalaChart = new Chart(ctxSala, {
                    type: 'bar',
                    data: {
                        labels: presencasPorSala.map(item => item.sala || 'Sala sem nome'),
                        datasets: [{
                            label: 'Presenças',
                            data: presencasPorSala.map(item => item.presentes),
                            backgroundColor: [
                                '#10b981',
                                '#3b82f6',
                                '#8b5cf6',
                                '#f59e0b',
                                '#ef4444'
                            ],
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: commonOptions
                });
            }
        });
    </script>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const inicioHidden = document.getElementById('inicio_hidden');
  const fimHidden = document.getElementById('fim_hidden');
  const aplicarBtn = document.getElementById('aplicarPeriodo');
  const limparLink = document.getElementById('limparPeriodo');
  const emptyMsgEl = document.getElementById('presencasPorDiaEmptyMsg');
  const taxaValueEl = document.getElementById('metricTaxaPresencaValue');
  const taxaBarEl = document.getElementById('metricTaxaPresencaBar');
  const alertasValueEl = document.getElementById('metricAlertasValue');
  const professoresValueEl = document.getElementById('metricProfessoresValue');

  function syncPeriodoInputs() {
    const di = document.querySelector('input[name="data_inicio"]');
    const df = document.querySelector('input[name="data_fim"]');
    if (di && df) {
      inicioHidden.value = di.value || df.value || '';
      fimHidden.value = df.value || di.value || '';
    }
  }

  ['change', 'input'].forEach(evt => {
    document.addEventListener(evt, (e) => {
      if (e.target && (e.target.name === 'data_inicio' || e.target.name === 'data_fim')) {
        syncPeriodoInputs();
      }
    });
  });

  function updatePresencasPorDiaChart(data) {
    if (!window.presencasPorDiaChart) return;
    const labels = (data || []).map(item => {
      const date = new Date(item.data);
      return date.toLocaleDateString('pt-BR', { weekday: 'short', day: '2-digit' });
    });
    const presentes = (data || []).map(item => item.presentes || 0);
    const totais = (data || []).map(item => item.total || 0);
    const totalRegistros = totais.reduce((sum, v) => sum + (Number(v) || 0), 0);
    emptyMsgEl?.classList.toggle('hidden', totalRegistros > 0);
    window.presencasPorDiaChart.data.labels = labels;
    window.presencasPorDiaChart.data.datasets[0].data = presentes;
    window.presencasPorDiaChart.data.datasets[1].data = totais;
    window.presencasPorDiaChart.update();
  }

  function updatePresencasPorSalaChart(data) {
    if (!window.presencasPorSalaChart) return;
    const labels = (data || []).map(item => item.sala || 'Sala sem nome');
    const valores = (data || []).map(item => item.presentes || 0);
    window.presencasPorSalaChart.data.labels = labels;
    window.presencasPorSalaChart.data.datasets[0].data = valores;
    window.presencasPorSalaChart.update();
  }

  function updateMetricCards(da) {
    const taxa = Number(da.taxaPresencaGeral ?? 0) || 0;
    if (taxaValueEl) taxaValueEl.textContent = `${taxa}%`;
    if (taxaBarEl) taxaBarEl.style.width = `${taxa}%`;
    const alertasCount = Array.isArray(da.alertasBaixaFrequencia) ? da.alertasBaixaFrequencia.length : Number(da.alertasBaixaFrequenciaCount ?? 0) || 0;
    if (alertasValueEl) alertasValueEl.textContent = `${alertasCount}`;
    const profs = Number(da.totalProfessoresComAtividade ?? 0) || 0;
    if (professoresValueEl) professoresValueEl.textContent = `${profs}`;
    
    // Atualizar badges dinamicamente
    updateBadges(taxa, alertasCount, profs);
  }

  function updateBadges(taxaPresenca, alertasCount, professoresAtivos) {
    // Badge Taxa de Presença
    const taxaBadge = document.getElementById('metricTaxaPresencaBadge');
    if (taxaBadge) {
      let badgeClass, badgeIcon, badgeText;
      if (taxaPresenca >= 90) {
        badgeClass = 'bg-green-100 text-green-800';
        badgeIcon = 'fas fa-arrow-up';
        badgeText = '<span class="hidden sm:inline">Excelente</span><span class="sm:hidden">Exc</span>';
      } else if (taxaPresenca >= 75) {
        badgeClass = 'bg-yellow-100 text-yellow-800';
        badgeIcon = 'fas fa-minus';
        badgeText = 'Bom';
      } else {
        badgeClass = 'bg-red-100 text-red-800';
        badgeIcon = 'fas fa-arrow-down';
        badgeText = '<span class="hidden sm:inline">Atenção</span><span class="sm:hidden">Atç</span>';
      }
      taxaBadge.className = `inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}`;
      taxaBadge.innerHTML = `<i class="${badgeIcon} mr-1"></i>${badgeText}`;
    }

    // Badge Alertas
    const alertasBadge = document.getElementById('metricAlertasBadge');
    if (alertasBadge) {
      let badgeClass, badgeIcon, badgeText;
      if (alertasCount > 0) {
        badgeClass = 'bg-red-100 text-red-800';
        badgeIcon = 'fas fa-bell';
        badgeText = 'Ativo';
      } else {
        badgeClass = 'bg-green-100 text-green-800';
        badgeIcon = 'fas fa-check';
        badgeText = 'OK';
      }
      alertasBadge.className = `inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}`;
      alertasBadge.innerHTML = `<i class="${badgeIcon} mr-1"></i>${badgeText}`;
    }

    // Badge Professores (mantém sempre "Ativos" por enquanto, mas pode ser expandido)
    const professoresBadge = document.getElementById('metricProfessoresBadge');
    if (professoresBadge) {
      // Por enquanto mantém sempre verde "Ativos", mas pode ser expandido com regras futuras
      professoresBadge.className = 'inline-flex items-center px-2 md:px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
      professoresBadge.innerHTML = '<i class="fas fa-users mr-1"></i>Ativos';
    }
  }

  function updateTables(da) {
    updateProfessoresAtivosTable(da.desempenhoProfessores || []);
    updateAlertasFrequenciaTable(da.alertasBaixaFrequencia || []);
  }

  function updateProfessoresAtivosTable(professores) {
    const container = document.getElementById('professoresAtivosContainer');
    if (!container) return;

    if (professores.length === 0) {
      container.innerHTML = `
        <div class="text-center py-8 md:py-12">
          <i class="fas fa-chalkboard-teacher text-gray-400 text-3xl md:text-4xl mb-4"></i>
          <p class="text-gray-500 text-sm md:text-base">Nenhum professor ativo encontrado</p>
        </div>
      `;
      return;
    }

    // Mobile Cards
    const mobileCards = professores.slice(0, 3).map(professor => `
      <div class="bg-gray-50 p-3 rounded-lg border">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-medium text-gray-900 text-sm">${professor.nome || 'Sem nome'}</h4>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <i class="fas fa-circle text-green-400 mr-1" style="font-size: 6px;"></i>
            Ativo
          </span>
        </div>
        <p class="text-xs text-gray-600 mb-1">${professor.cargo || 'Professor'}</p>
        <p class="text-xs text-gray-500">
          <i class="fas fa-clock mr-1"></i>
          Última atividade: ${professor.ultima_atividade || 'Hoje'}
        </p>
      </div>
    `).join('');

    // Desktop Table Rows
    const tableRows = professores.slice(0, 5).map(professor => `
      <tr class="hover:bg-gray-50">
        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
          <div class="flex items-center">
            <div class="flex-shrink-0 h-8 w-8 md:h-10 md:w-10">
              <div class="h-8 w-8 md:h-10 md:w-10 rounded-full bg-purple-100 flex items-center justify-center">
                <i class="fas fa-user text-purple-600 text-xs md:text-sm"></i>
              </div>
            </div>
            <div class="ml-3 md:ml-4">
              <div class="text-sm font-medium text-gray-900">${professor.nome || 'Sem nome'}</div>
              <div class="text-xs md:text-sm text-gray-500 md:hidden">${professor.cargo || 'Professor'}</div>
            </div>
          </div>
        </td>
        <td class="px-3 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
          ${professor.cargo || 'Professor'}
        </td>
        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
            <i class="fas fa-circle text-green-400 mr-1" style="font-size: 6px;"></i>
            Ativo
          </span>
        </td>
      </tr>
    `).join('');

    container.innerHTML = `
      <div class="block sm:hidden space-y-3" id="professoresAtivosMobile">
        ${mobileCards}
      </div>
      <table class="hidden sm:table min-w-full divide-y divide-gray-200" id="professoresAtivosTable">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Professor</th>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Cargo</th>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          ${tableRows}
        </tbody>
      </table>
    `;
  }

  function updateAlertasFrequenciaTable(alertas) {
    const container = document.getElementById('alertasFrequenciaContainer');
    if (!container) return;

    if (alertas.length === 0) {
      container.innerHTML = `
        <div class="text-center py-8 md:py-12">
          <i class="fas fa-check-circle text-green-400 text-3xl md:text-4xl mb-4"></i>
          <p class="text-gray-500 text-sm md:text-base">Nenhum alerta de frequência</p>
          <p class="text-gray-400 text-xs md:text-sm mt-1">Todas as frequências estão normais</p>
        </div>
      `;
      return;
    }

    // Mobile Cards
    const mobileCards = alertas.slice(0, 3).map(alerta => `
      <div class="bg-red-50 p-3 rounded-lg border border-red-200">
        <div class="flex items-center justify-between mb-2">
          <h4 class="font-medium text-gray-900 text-sm">${alerta.nome || 'Sem nome'}</h4>
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
            ${Number(alerta.frequencia || 0).toFixed(1)}%
          </span>
        </div>
        <p class="text-xs text-gray-600 mb-1">${alerta.turma?.nome || alerta.grupo_id || 'Turma não informada'}</p>
        <p class="text-xs text-red-600">
          <i class="fas fa-exclamation-triangle mr-1"></i>
          Frequência baixa
        </p>
      </div>
    `).join('');

    // Desktop Table Rows
    const tableRows = alertas.slice(0, 5).map(alerta => `
      <tr class="hover:bg-gray-50">
        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
          <div class="flex items-center">
            <div class="flex-shrink-0 h-8 w-8 md:h-10 md:w-10">
              <div class="h-8 w-8 md:h-10 md:w-10 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fas fa-user-graduate text-red-600 text-xs md:text-sm"></i>
              </div>
            </div>
            <div class="ml-3 md:ml-4">
              <div class="text-sm font-medium text-gray-900">${alerta.nome || 'Sem nome'}</div>
              <div class="text-xs md:text-sm text-gray-500 md:hidden">${alerta.turma?.nome || 'Turma não informada'}</div>
            </div>
          </div>
        </td>
        <td class="px-3 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden md:table-cell">
          ${alerta.turma?.nome || 'Não informada'}
        </td>
        <td class="px-3 md:px-6 py-4 whitespace-nowrap">
          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            ${Number(alerta.frequencia || 0).toFixed(1)}%
          </span>
        </td>
      </tr>
    `).join('');

    container.innerHTML = `
      <div class="block sm:hidden space-y-3" id="alertasFrequenciaMobile">
        ${mobileCards}
      </div>
      <table class="hidden sm:table min-w-full divide-y divide-gray-200" id="alertasFrequenciaTable">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aluno</th>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Turma</th>
            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequência</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          ${tableRows}
        </tbody>
      </table>
    `;
  }

  aplicarBtn?.addEventListener('click', async () => {
    syncPeriodoInputs();
    const inicio = encodeURIComponent(inicioHidden.value || '');
    const fim = encodeURIComponent(fimHidden.value || '');
    const url = `{{ route('dashboard') }}?inicio=${inicio}&fim=${fim}&ajax=1`;
    try {
      const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const json = await resp.json();
      const da = json?.dadosAnaliticos || {};
      updatePresencasPorDiaChart(da.presencasPorDia || []);
      updatePresencasPorSalaChart(da.presencasPorSala || []);
      updateMetricCards(da);
      updateTables(da);
    } catch (err) {
      console.error('Erro ao atualizar Analytics via AJAX', err);
    }
  });

  limparLink?.addEventListener('click', async (e) => {
    e.preventDefault();
    try {
      const url = `{{ route('dashboard') }}?ajax=1&clear_periodo=1`;
      const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const json = await resp.json();
      const da = json?.dadosAnaliticos || {};
      // Atualizar inputs visíveis do filtro para o novo período
      const di = document.querySelector('input[name="data_inicio"]');
      const df = document.querySelector('input[name="data_fim"]');
      if (di && df && json?.analytics) {
        di.value = json.analytics.inicio || '';
        df.value = json.analytics.fim || '';
        syncPeriodoInputs();
      }
      updatePresencasPorDiaChart(da.presencasPorDia || []);
      updatePresencasPorSalaChart(da.presencasPorSala || []);
      updateMetricCards(da);
      updateTables(da);
    } catch (err) {
      console.error('Erro ao limpar período via AJAX', err);
    }
  });

  syncPeriodoInputs();

  // Atualização automática do Top 5 Salas (presenças) a cada 30s
  let autoRefreshTimerSala;
  function setupAutoRefreshPresencasPorSala() {
    try { if (autoRefreshTimerSala) clearInterval(autoRefreshTimerSala); } catch (_) {}
    autoRefreshTimerSala = setInterval(async () => {
      if (!window.presencasPorSalaChart) return;
      const inicio = encodeURIComponent(inicioHidden?.value || '');
      const fim = encodeURIComponent(fimHidden?.value || '');
      const url = `{{ route('dashboard') }}?inicio=${inicio}&fim=${fim}&ajax=1`;
      try {
        const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!resp.ok) return;
        const json = await resp.json();
        const da = json?.dadosAnaliticos || {};
        const dataSala = Array.isArray(da.presencasPorSala) ? da.presencasPorSala : [];
        updatePresencasPorSalaChart(dataSala);
      } catch (_) { /* silencioso para não poluir console */ }
    }, 30000);
  }

  setupAutoRefreshPresencasPorSala();
});
</script>
@endpush

@push('scripts')
    <script>
        // Analytics: DnD por grid, visibilidade, restauração e persistência (local + backend)
        (function() {
            const csrfToken = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')) || window.csrfToken || '';
            const debounce = (fn, wait) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), wait); }; };

            const manager = {
                grids: {},            // gridId -> { defaultOrder, gridKey }
                cards: {},            // cardKey -> HTMLElement
                state: {              // preferências locais atuais
                    visible: {},
                    orders: {}
                },
                remoteAll: null,      // objeto completo vindo do backend (pode conter outras chaves usadas por outras seções)
                gridIdToKey: {
                    'analyticsMetricsGrid': 'metrics',
                    'analyticsChartsGrid': 'charts',
                    'analyticsTablesGrid': 'tables',
                    'analyticsRecentGrid': 'recent',
                    'analyticsExtraGrid': 'extra'
                },
                storageKeyFor(gridKey) { return `analytics:dnd:${gridKey}`; },
                async loadRemote() {
                    try {
                        const res = await fetch("{{ route('dashboard.preferences.index') }}", { credentials: 'same-origin' });
                        if (!res.ok) return null;
                        const data = await res.json();
                        this.remoteAll = data?.state || null;
                        return this.remoteAll;
                    } catch (_) { return null; }
                },
                async saveRemoteMerged() {
                    try {
                        const all = Object.assign({}, this.remoteAll || {});
                        all.analytics = { visible: this.state.visible, orders: this.state.orders };
                        await fetch("{{ route('dashboard.preferences.save') }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            credentials: 'same-origin',
                            body: JSON.stringify({ state: all })
                        });
                        this.remoteAll = all;
                    } catch (_) {}
                },
                debouncedSave: null,
                applyVisibility() {
                    Object.keys(this.cards).forEach(key => {
                        const el = this.cards[key];
                        const visible = this.state.visible[key] !== false; // default: true
                        el.classList.toggle('hidden', !visible);
                    });
                },
                applyOrderFor(gridId) {
                    const grid = document.getElementById(gridId);
                    if (!grid) return;
                    const gridKey = this.gridIdToKey[gridId] || gridId;
                    const reg = this.grids[gridId];
                    const defaultOrder = reg?.defaultOrder || Array.from(grid.querySelectorAll('[data-card-key]')).map(el => el.dataset.cardKey);
                    const current = (Array.isArray(this.state.orders[gridKey]) ? this.state.orders[gridKey].slice() : []);
                    const base = (current.length ? current : defaultOrder);
                    const final = base.concat(defaultOrder.filter(k => !base.includes(k)));
                    final.forEach(k => {
                        const el = grid.querySelector(`[data-card-key="${k}"]`);
                        if (el) grid.appendChild(el);
                    });
                },
                applyAll() {
                    this.applyVisibility();
                    Object.keys(this.grids).forEach(gridId => this.applyOrderFor(gridId));
                },
                restoreGrid(gridId) {
                    const gridKey = this.gridIdToKey[gridId] || gridId;
                    const reg = this.grids[gridId];
                    if (!reg) return;
                    this.state.orders[gridKey] = reg.defaultOrder.slice();
                    try { localStorage.removeItem(this.storageKeyFor(gridKey)); } catch (_) {}
                    this.applyOrderFor(gridId);
                    this.debouncedSave();
                }
            };

            manager.debouncedSave = debounce(() => manager.saveRemoteMerged(), 500);

            function initAnalyticsDnD(gridId) {
                const grid = document.getElementById(gridId);
                if (!grid) return;
                const items = Array.from(grid.querySelectorAll('[data-card-key]'));
                if (!items.length) return;

                // registrar cards e grid
                items.forEach(el => { manager.cards[el.dataset.cardKey] = el; });
                const gridKey = manager.gridIdToKey[gridId] || gridId;
                const defaultOrder = items.map(el => el.dataset.cardKey);
                manager.grids[gridId] = { defaultOrder, gridKey };

                // ordem inicial: localStorage primeiro
                let order = [];
                try {
                    const saved = JSON.parse(localStorage.getItem(manager.storageKeyFor(gridKey)) || '[]') || [];
                    order = saved.filter(k => defaultOrder.includes(k));
                } catch (_) { order = []; }
                if (!order.length) order = defaultOrder.slice(); else order = order.concat(defaultOrder.filter(k => !order.includes(k)));
                manager.state.orders[gridKey] = order.slice();

                const applyOrder = () => {
                    manager.state.orders[gridKey].forEach(k => {
                        const el = grid.querySelector(`[data-card-key="${k}"]`);
                        if (el) grid.appendChild(el);
                    });
                };
                applyOrder();

                let dragKey = null;
                grid.addEventListener('dragstart', (e) => {
                    const handle = e.target.closest('[data-drag-handle]');
                    if (!handle || !grid.contains(handle)) return;
                    const card = handle.closest('[data-card-key]');
                    if (!card) return;
                    dragKey = card.dataset.cardKey;
                    if (e.dataTransfer) {
                        e.dataTransfer.effectAllowed = 'move';
                        try { e.dataTransfer.setData('text/plain', dragKey); } catch (_) {}
                    }
                });
                grid.addEventListener('dragover', (e) => {
                    if (!dragKey) return;
                    const overCard = e.target.closest('[data-card-key]');
                    if (!overCard || !grid.contains(overCard)) return;
                    e.preventDefault();
                });
                grid.addEventListener('drop', (e) => {
                    if (!dragKey) return;
                    const target = e.target.closest('[data-card-key]');
                    if (!target || !grid.contains(target)) return;
                    e.preventDefault();
                    const targetKey = target.dataset.cardKey;
                    if (dragKey === targetKey) { dragKey = null; return; }
                    const from = manager.state.orders[gridKey].indexOf(dragKey);
                    const to = manager.state.orders[gridKey].indexOf(targetKey);
                    if (from === -1 || to === -1) { dragKey = null; return; }
                    manager.state.orders[gridKey].splice(to, 0, ...manager.state.orders[gridKey].splice(from, 1));
                    try { localStorage.setItem(manager.storageKeyFor(gridKey), JSON.stringify(manager.state.orders[gridKey])); } catch (_) {}
                    applyOrder();
                    dragKey = null;
                    manager.debouncedSave();
                });

                const highlight = (el, on) => {
                    el.classList.toggle('ring-2', on);
                    el.classList.toggle('ring-indigo-400', on);
                    el.classList.toggle('ring-offset-2', on);
                };
                grid.querySelectorAll('[data-card-key]').forEach(el => {
                    el.addEventListener('dragenter', () => { if (dragKey) highlight(el, true); });
                    el.addEventListener('dragleave', () => highlight(el, false));
                    el.addEventListener('drop', () => highlight(el, false));
                });
            }

            document.addEventListener('DOMContentLoaded', async function() {
                // inicializa DnD dos grids
                ['analyticsMetricsGrid','analyticsChartsGrid','analyticsTablesGrid','analyticsRecentGrid','analyticsExtraGrid']
                    .forEach(initAnalyticsDnD);

                // visibilidade: default para todos os cards é true
                Object.keys(manager.cards).forEach(k => { if (typeof manager.state.visible[k] === 'undefined') manager.state.visible[k] = true; });
                manager.applyVisibility();

                // botões de restauração por grid
                document.querySelectorAll('[data-action="restore-analytics-grid"]').forEach(btn => {
                    btn.addEventListener('click', () => manager.restoreGrid(btn.dataset.gridId));
                });

                // carrega do backend e reaplica (merge seguro)
                const remoteAll = await manager.loadRemote();
                const remoteAnalytics = remoteAll && remoteAll.analytics ? remoteAll.analytics : null;
                if (remoteAnalytics) {
                    manager.state.visible = Object.assign({}, manager.state.visible, remoteAnalytics.visible || {});
                    manager.state.orders = Object.assign({}, manager.state.orders, remoteAnalytics.orders || {});
                    manager.applyAll();
                }

                // Modal Personalizar (abrir/fechar/salvar)
                const modal = document.getElementById('modalPersonalizarAnalyticsCards');
                const btnOpen = document.getElementById('btnPersonalizarAnalyticsCards');
                const extraTriggers = document.querySelectorAll('[data-open-modal="modalPersonalizarAnalyticsCards"]');
                const errorEl = document.getElementById('modalAnalyticsPrefError');
                const toggles = modal ? modal.querySelectorAll('.toggle-analytics-card') : [];

                if (modal) {
                    const openModal = () => {
                        modal.classList.remove('hidden'); modal.classList.add('flex');
                        if (errorEl) { errorEl.textContent = ''; errorEl.classList.add('hidden'); }
                        toggles.forEach(t => { const k = t.dataset.cardKey; t.checked = manager.state.visible[k] !== false; });
                    };
                    if (btnOpen) { btnOpen.addEventListener('click', openModal); }
                    extraTriggers.forEach(el => el.addEventListener('click', openModal));
                    modal.querySelectorAll('[data-action="close-modal"]').forEach(btn => btn.addEventListener('click', () => {
                        modal.classList.add('hidden'); modal.classList.remove('flex');
                    }));
                    const btnRestoreAll = modal.querySelector('[data-action="restore-analytics-all"]');
                    if (btnRestoreAll) {
                        btnRestoreAll.addEventListener('click', async () => {
                            const original = btnRestoreAll.textContent;
                            btnRestoreAll.disabled = true;
                            btnRestoreAll.textContent = 'Restaurando...';
                            try {
                                // Visibilidade: tudo visível
                                Object.keys(manager.cards).forEach(k => { manager.state.visible[k] = true; });
                                // Ordem por grid: volta ao padrão e limpa localStorage
                                Object.keys(manager.grids).forEach(gridId => {
                                    const reg = manager.grids[gridId];
                                    manager.state.orders[reg.gridKey] = reg.defaultOrder.slice();
                                    try { localStorage.removeItem(manager.storageKeyFor(reg.gridKey)); } catch (_) {}
                                });
                                manager.applyAll();
                                await manager.saveRemoteMerged();
                                btnRestoreAll.textContent = 'Restaurado!';
                            } catch(_) {
                                if (errorEl) { errorEl.textContent = 'Falha ao restaurar. Tente novamente.'; errorEl.classList.remove('hidden'); }
                                btnRestoreAll.textContent = original;
                            }
                            setTimeout(() => { btnRestoreAll.disabled = false; btnRestoreAll.textContent = original; }, 800);
                        });
                    }
                    const btnSave = modal.querySelector('[data-action="save-analytics-preferences"]');
                    if (btnSave) {
                        btnSave.addEventListener('click', async () => {
                            const saveText = btnSave.querySelector('.save-text');
                            const saveLoading = btnSave.querySelector('.save-loading');
                            btnSave.disabled = true;
                            if (saveLoading) saveLoading.classList.remove('hidden');
                            if (saveText) saveText.textContent = 'Salvando...';
                            toggles.forEach(t => { manager.state.visible[t.dataset.cardKey] = !!t.checked; });
                            manager.applyVisibility();
                            let ok = true;
                            try { await manager.saveRemoteMerged(); } catch (_) { ok = false; }
                            if (saveText) saveText.textContent = ok ? 'Salvo!' : 'Erro ao salvar';
                            if (!ok && errorEl) { errorEl.textContent = 'Falha ao salvar preferências. Verifique sua conexão.'; errorEl.classList.remove('hidden'); }
                            setTimeout(() => {
                                if (saveLoading) saveLoading.classList.add('hidden');
                                btnSave.disabled = false;
                                if (saveText) saveText.textContent = 'Salvar';
                                if (ok) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
                            }, 800);
                        });
                    }
                }
            });
        })();
    </script>
@endpush

@push('scripts')
    <script>
        // Sparklines para cards financeiros (gradientes e tooltips)
        document.addEventListener('DOMContentLoaded', function() {
            const serieReceitas = @json($serieReceitas ?? []);
            const serieReceitasDinheiro = @json($serieReceitasDinheiro ?? []);
            const serieReceitasGateway = @json($serieReceitasGateway ?? []);
            const serieReceitasTotal = @json($serieReceitasTotal ?? []);
            const serieReceitasPendentes = @json($serieReceitasPendentes ?? []);
            const serieDespesas = @json($serieDespesas ?? []);
            const serieDespesasLiquidadas = @json($serieDespesasLiquidadas ?? []);
            const serieDespesasPendentes = @json($serieDespesasPendentes ?? []);
            const serieInadimplencia = @json($serieInadimplencia ?? []);
            const serieTickets = @json($serieTickets ?? []);
            const serieMrr = @json($serieMrr ?? []);

            const fmtBRL = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v || 0);
            const fmtNum = (v) => new Intl.NumberFormat('pt-BR').format(v || 0);
            const fmtDayMonth = (dateStr) => {
                // Formata rótulos em dd/MM ou MM/YYYY conforme padrão
                if (!dateStr) return '';
                // YYYY-MM-DD
                const fullDate = /^\d{4}-\d{2}-\d{2}$/;
                // YYYY-MM
                const yearMonth = /^\d{4}-\d{2}$/;
                if (fullDate.test(dateStr)) {
                    const [y, m, d] = dateStr.split('-');
                    return `${d}/${m}`;
                }
                if (yearMonth.test(dateStr)) {
                    const [y, m] = dateStr.split('-');
                    // Padroniza para dd/MM usando dia 01
                    return `01/${m}`;
                }
                // Já está em dd/MM ou outro formato desconhecido
                return dateStr;
            };

            const baseOpts = {
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(255, 255, 255, 0.92)',
                        titleColor: '#111827',
                        bodyColor: '#111827',
                        borderColor: 'rgba(17, 24, 39, 0.12)',
                        borderWidth: 1,
                        displayColors: false,
                        cornerRadius: 8,
                        padding: 8
                    }
                },
                layout: { padding: 0 },
                scales: { x: { display: false }, y: { display: false } },
                elements: { point: { radius: 0 } },
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' }
            };

            const hexToRgb = (hex) => {
                const h = hex.replace('#','');
                const bigint = parseInt(h, 16);
                const r = (bigint >> 16) & 255;
                const g = (bigint >> 8) & 255;
                const b = bigint & 255;
                return { r, g, b };
            };

            const charts = {};
            const makeSpark = (canvasId, values, colors, format, datasetLabel, labels, description) => {
                const el = document.getElementById(canvasId);
                if (!el) return null;
                const ctx = el.getContext('2d');
                // Gradiente alinhado ao card (left-to-right)
                const width = el.clientWidth || el.width;
                const gradStroke = ctx.createLinearGradient(0, 0, width, 0);
                gradStroke.addColorStop(0, colors[0]);
                gradStroke.addColorStop(1, colors[1]);
                const c0 = hexToRgb(colors[0]);
                const c1 = hexToRgb(colors[1]);
                const gradFill = ctx.createLinearGradient(0, 0, width, 0);
                // Afinar opacidade para ficar mais claro/sutil
                gradFill.addColorStop(0, `rgba(${c0.r}, ${c0.g}, ${c0.b}, 0.14)`);
                gradFill.addColorStop(1, `rgba(${c1.r}, ${c1.g}, ${c1.b}, 0.06)`);
                const opts = JSON.parse(JSON.stringify(baseOpts));
                opts.plugins.tooltip.callbacks = {
                    title: (items) => {
                        const idx = (items && items[0]) ? items[0].dataIndex : 0;
                        const raw = (labels && labels[idx]) ? labels[idx] : '';
                        return fmtDayMonth(raw);
                    },
                    label: (context) => {
                        const v = context.raw;
                        const val = format === 'currency' ? fmtBRL(v) : fmtNum(v);
                        return `${datasetLabel || ''}: ${val}`.trim();
                    },
                    footer: () => description || ''
                };
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: { labels: (labels && labels.length ? labels : values.map((_, i) => i + 1)), datasets: [{ label: datasetLabel || '', data: values, borderColor: gradStroke, backgroundColor: gradFill, borderWidth: 2, tension: 0.3, fill: true }] },
                    options: opts
                });
                charts[canvasId] = chart;
                return chart;
            };

            // Helper: filtra uma série diária pelo mês selecionado (YYYY-MM)
            const selectedMonth = (document.getElementById('financeMonth')?.value || '').slice(0, 7);
            const filterMonthVals = (serie, mapVal) => {
                const arr = Array.isArray(serie) ? serie : [];
                const filt = arr.filter(p => (p.data || '').startsWith(selectedMonth));
                return {
                    values: filt.map(p => mapVal ? mapVal(p) : p.valor || 0),
                    labels: filt.map(p => p.data || '')
                };
            };

            // Receitas (faturas pagas por dia via paid_at) - emerald 500→600
            const r0 = filterMonthVals(serieReceitas, p => (p.valor_cents || 0) / 100);
            makeSpark(
                'sparkReceitas',
                r0.values,
                ['#10b981', '#059669'],
                'currency',
                'Receitas',
                r0.labels,
                'Faturas pagas por dia (Invoices.total_cents via paid_at).'
            );
            // Despesas (valor direto) - rose 500→600
            const d0 = filterMonthVals(serieDespesas, p => (p.valor || 0));
            makeSpark(
                'sparkDespesas',
                d0.values,
                ['#f43f5e', '#e11d48'],
                'currency',
                'Despesas (Total)',
                d0.labels,
                'Despesas diárias (Despesa.valor) somadas por data.'
            );
            // Inadimplência (contagem diária) - orange 500→600
            const i0 = filterMonthVals(serieInadimplencia, p => (p.valor || 0));
            makeSpark(
                'sparkInadimplencia',
                i0.values,
                ['#f59e0b', '#d97706'],
                'number',
                'Inadimplência',
                i0.labels,
                'Faturas vencidas não pagas por dia (Invoice.status != paid/canceled).'
            );
            // Tickets abertos (criados por dia) - indigo 500→600
            const t0 = filterMonthVals(serieTickets, p => (p.valor || 0));
            makeSpark(
                'sparkTickets',
                t0.values,
                ['#6366f1', '#4f46e5'],
                'number',
                'Tickets',
                t0.labels,
                'Conversas de suporte ativas criadas por dia (Conversa.tipo = suporte, ativo = true), filtradas por escola.'
            );
            // MRR (histórico em R$) - teal 500→600
            makeSpark(
                'sparkMrr',
                (serieMrr || []).map(p => (p.valor_cents || 0) / 100),
                ['#14b8a6', '#0d9488'],
                'currency',
                'MRR',
                (serieMrr || []).map(p => p.mes || ''),
                'Receita recorrente mensal de assinaturas ativas (Subscription.amount_cents).'
            );
            // Método predominante (trend de receitas do período) - cyan 500→600
            const m0 = filterMonthVals(serieReceitas, p => (p.valor_cents || 0) / 100);
            makeSpark(
                'sparkMetodo',
                m0.values,
                ['#06b6d4', '#0891b2'],
                'currency',
                'Método',
                m0.labels,
                'Tendência de receitas líquidas; método predominante do mês via Payments.method.'
            );

            // Filtro por mês (YYYY-MM) para atualizar gráficos vinculados
            const monthEl = document.getElementById('financeMonth');
            const baseSelect = document.getElementById('receitasBase');
            let receitasBase = (typeof localStorage !== 'undefined' && localStorage.getItem('receitasBase')) || 'due_date';
            if (baseSelect) {
                try { baseSelect.value = receitasBase; } catch(_) {}
            }
            const original = {
                // Receitas recebidas (por due_date)
                receitas: (serieReceitas || []).map(p => ({ d: p.data || '', v: (p.valor_cents || 0) / 100 })),
                // Total faturado e pendentes (por due_date)
                receitas_total: (serieReceitasTotal || []).map(p => ({ d: p.data || '', v: (p.valor_cents || 0) / 100 })),
                receitas_pend: (serieReceitasPendentes || []).map(p => ({ d: p.data || '', v: (p.valor_cents || 0) / 100 })),
                // Quebra por método (apenas recebidas)
                receitas_dinheiro: (serieReceitasDinheiro || []).map(p => ({ d: p.data || '', v: (p.valor_cents || 0) / 100 })),
                receitas_gateway: (serieReceitasGateway || []).map(p => ({ d: p.data || '', v: (p.valor_cents || 0) / 100 })),
                despesas: (serieDespesas || []).map(p => ({ d: p.data || '', v: (p.valor || 0) })),
                despesas_liq: (serieDespesasLiquidadas || []).map(p => ({ d: p.data || '', v: (p.valor || 0) })),
                despesas_pend: (serieDespesasPendentes || []).map(p => ({ d: p.data || '', v: (p.valor || 0) })),
                inadimplencia: (serieInadimplencia || []).map(p => ({ d: p.data || '', v: (p.valor || 0) })),
                tickets: (serieTickets || []).map(p => ({ d: p.data || '', v: (p.valor || 0) })),
                metodo: (serieReceitas || []).map(p => ({ d: p.data || '', v: (p.valor_cents || 0) / 100 })),
                mrr: (serieMrr || []).map(p => ({ m: p.mes || '', v: (p.valor_cents || 0) / 100 }))
            };

            // Dataset bruto para listas de pendentes (últimos 3 meses)
            const pendentesTodos = @json($pendentesTodos ?? []);
            const despesasPendTodos = @json($despesasPendTodos ?? []);

            const elListaAvencer = document.getElementById('listaPendentesAVencer');
            const elListaVencidas = document.getElementById('listaPendentesVencidas');
            const elDespPendAvencer = document.getElementById('listaDespesasPendAVencer');
            const elDespPendVencidas = document.getElementById('listaDespesasPendVencidas');

            const updateChart = (id, points, datasetLabel) => {
                const c = charts[id];
                if (!c) return;
                c.data.labels = points.map(p => p.d);
                c.data.datasets[0].data = points.map(p => p.v);
                c.data.datasets[0].label = datasetLabel || c.data.datasets[0].label;
                c.update();
            };

            // Helper: gerar pontos para todos os dias do mês selecionado
            const daysInMonth = (ym) => {
                if (!ym) return [];
                const [yStr, mStr] = ym.split('-');
                const y = parseInt(yStr, 10);
                const m = parseInt(mStr, 10);
                const lastDay = new Date(y, m, 0).getDate(); // m é 1-12
                return Array.from({ length: lastDay }, (_, i) => String(i + 1).padStart(2, '0'));
            };

            const normalizeMonthSeries = (ym, arr) => {
                if (!ym) return arr;
                const map = new Map();
                arr.forEach(p => {
                    const d = (p.d || '').slice(0, 10);
                    if (d.startsWith(ym)) map.set(d, p.v || 0);
                });
                return daysInMonth(ym).map(dd => {
                    const key = `${ym}-${dd}`;
                    return { d: key, v: map.has(key) ? map.get(key) : 0 };
                });
            };

            const applyMonthFilter = (month) => {
                // MRR não vinculado ao mês; não altera
                // Normaliza os demais gráficos para todos os dias do mês selecionado (YYYY-MM)
                // Auto-ajuste: se mês selecionado é futuro e base==paid_at, trocar para due_date
                try {
                    const ymSel = (month || '').slice(0,7);
                    const ymNow = new Date().toISOString().slice(0,7);
                    if (ymSel && ymSel > ymNow && receitasBase === 'paid_at') {
                        receitasBase = 'due_date';
                        if (baseSelect) baseSelect.value = 'due_date';
                        if (localStorage) localStorage.setItem('receitasBase', 'due_date');
                    }
                } catch (_) {}

                const r = normalizeMonthSeries(month, original.receitas); // recebidas
                const rTot = normalizeMonthSeries(month, original.receitas_total);
                const rPend = normalizeMonthSeries(month, original.receitas_pend);
                const rDin = normalizeMonthSeries(month, original.receitas_dinheiro);
                const rGtw = normalizeMonthSeries(month, original.receitas_gateway);
                const dl = normalizeMonthSeries(month, original.despesas_liq);
                const dp = normalizeMonthSeries(month, original.despesas_pend);
                // total diário = liq + pend
                const d = (dl || []).map((p, idx) => ({ d: p.d, v: (p.v || 0) + ((dp[idx]?.v) || 0) }));
                const i = normalizeMonthSeries(month, original.inadimplencia);
                const t = normalizeMonthSeries(month, original.tickets);
                const m = normalizeMonthSeries(month, original.metodo);

                updateChart('sparkReceitas', r, 'Receitas');
                updateChart('sparkDespesas', d, 'Despesas');
                updateChart('sparkInadimplencia', i, 'Inadimplência');
                updateChart('sparkTickets', t, 'Tickets');
                updateChart('sparkMetodo', m, 'Método');

                // No-data messages (exibe se todos os valores do mês são zero)
                const toggleNoData = (id, points) => {
                    const el = document.querySelector(`[data-no-data-for="${id}"]`);
                    if (!el) return;
                    const hasAny = (points || []).some(p => (p.v || 0) !== 0);
                    el.classList.toggle('hidden', hasAny);
                };
                toggleNoData('sparkReceitas', r);
                toggleNoData('sparkDespesas', d);
                toggleNoData('sparkInadimplencia', i);
                toggleNoData('sparkTickets', t);
                toggleNoData('sparkMetodo', m);

                // Totais no topo (mês inteiro)
                const sum = (arr) => arr.reduce((acc, p) => acc + (p.v || 0), 0);
                const setTotal = (key, val, type) => {
                    const el = document.querySelector(`[data-total-for="${key}"]`);
                    if (!el) return;
                    el.textContent = type === 'currency' ? fmtBRL(val) : fmtNum(val);
                };
                // Receitas KPIs no header
                // Recebido: alinhar ao mês selecionado somando a série por paid_at (sempre)
                setTotal('receitas-recebido', sum(r), 'currency');
                // Total/Pendentes: alternar base via seletor (due_date | paid_at)
                const base = receitasBase || 'due_date';
                const totalVal = base === 'paid_at' ? sum(r) : sum(rTot);
                setTotal('receitas-total', totalVal, 'currency');
                const pendEl = document.querySelector('[data-total-for="receitas-pendentes"]');
                if (base === 'paid_at') {
                    if (pendEl) {
                        pendEl.textContent = '—';
                        pendEl.setAttribute('title', 'Pendentes não se aplicam quando base = paid_at');
                    }
                } else {
                    setTotal('receitas-pendentes', sum(rPend), 'currency');
                    if (pendEl) pendEl.removeAttribute('title');
                }
                setTotal('receitas-dinheiro', sum(rDin), 'currency');
                setTotal('receitas-gateway', sum(rGtw), 'currency');
                setTotal('despesas', sum(dl), 'currency');
                setTotal('despesas-pendentes', sum(dp), 'currency');
                setTotal('despesas-total', sum(dl) + sum(dp), 'currency');
                setTotal('tickets', sum(t), 'number');

                // Listas de pendentes (filtradas pelo mês selecionado)
                if (Array.isArray(pendentesTodos) && (elListaAvencer || elListaVencidas)) {
                    const ym = (month || '').slice(0, 7);
                    const todayStr = new Date().toISOString().slice(0,10);
                    const startsWithYm = (s) => (s || '').slice(0, 7) === ym;
                    const iso10 = (s) => (s || '').slice(0, 10);
                    // A vencer: restringe por mês e exclui status overdue
                    const avencer = pendentesTodos
                        .filter(rw => startsWithYm(rw.due_date))
                        .filter(rw => (rw.status || '') !== 'overdue')
                        .filter(rw => iso10(rw.due_date) >= todayStr);
                    // Vencidas: inclui todas com due_date < hoje OU status overdue
                    const vencidas = pendentesTodos
                        .filter(rw => (rw.status || '') === 'overdue' || iso10(rw.due_date) < todayStr);

                    const formatDM = (iso) => {
                        const s = (iso || '').slice(0,10);
                        if (!s) return '';
                        const [Y,M,D] = s.split('-');
                        return `${D}/${M}`;
                    };
                    const toItem = (rw) => {
                        const payer = [rw.payer_nome || '', rw.payer_sobrenome || ''].join(' ').trim();
                        const payerHtml = payer ? `<span class=\"ml-2 text-gray-500\"> ${payer}</span>` : '';
                        return `
                            <div class=\"flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0\">
                                <div class=\"text-xs text-gray-700\">
                                    <span class=\"inline-block text-gray-500\">${formatDM(rw.due_date)}</span>
                                    ${payerHtml}
                                </div>
                                <div class=\"text-xs font-semibold text-gray-900\">${fmtBRL((rw.total_cents || 0) / 100)}</div>
                            </div>`;
                    };

                    const limit = 8;
                    if (elListaAvencer) {
                        elListaAvencer.innerHTML = avencer.length
                            ? avencer.slice(0, limit).map(toItem).join('')
                            : '<p class="text-xs text-gray-500">Nada a vencer</p>';
                    }
                    if (elListaVencidas) {
                        elListaVencidas.innerHTML = vencidas.length
                            ? vencidas.slice(0, limit).map(toItem).join('')
                            : '<p class="text-xs text-gray-500">Sem vencidos</p>';
                    }
                    setTotal('pendentes-avencer', avencer.reduce((acc, rw) => acc + ((rw.total_cents || 0) / 100), 0), 'currency');
                    setTotal('pendentes-vencidos', vencidas.reduce((acc, rw) => acc + ((rw.total_cents || 0) / 100), 0), 'currency');
                }

                // Listas de despesas pendentes (filtradas pelo mês selecionado)
                if (Array.isArray(despesasPendTodos) && (elDespPendAvencer || elDespPendVencidas)) {
                    const ym = (month || '').slice(0, 7);
                    const todayStr = new Date().toISOString().slice(0,10);
                    const rows = despesasPendTodos.filter(rw => (rw.data || '').startsWith(ym));
                    const avencer = rows.filter(rw => (rw.data || '') >= todayStr);
                    const vencidas = rows.filter(rw => (rw.data || '') < todayStr);

                    const formatDM = (iso) => {
                        if (!iso) return '';
                        const [Y,M,D] = iso.split('-');
                        return `${D}/${M}`;
                    };
                    const toItem = (rw) => {
                        const catHtml = rw.categoria ? `<span class=\"ml-2 text-gray-500\">— ${rw.categoria}</span>` : '';
                        return `
                            <div class=\"flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0\">
                                <div class=\"text-xs text-gray-700\">
                                    <span class=\"inline-block w-14 text-gray-500\">${formatDM(rw.data)}</span>
                                    <span class=\"ml-2\">${rw.descricao || ''}</span>
                                    ${catHtml}
                                </div>
                                <div class=\"text-xs font-semibold text-gray-900\">${fmtBRL((rw.valor || 0))}</div>
                            </div>`;
                    };

                    const limit = 8;
                    if (elDespPendAvencer) {
                        elDespPendAvencer.innerHTML = avencer.length
                            ? avencer.slice(0, limit).map(toItem).join('')
                            : '<p class="text-xs text-gray-500">Nada a vencer</p>';
                    }
                    if (elDespPendVencidas) {
                        elDespPendVencidas.innerHTML = vencidas.length
                            ? vencidas.slice(0, limit).map(toItem).join('')
                            : '<p class="text-xs text-gray-500">Sem vencidas</p>';
                    }
                    const sumVal = (arr) => arr.reduce((acc, rw) => acc + (rw.valor || 0), 0);
                    setTotal('despesas-pend-avencer', sumVal(avencer), 'currency');
                    setTotal('despesas-pend-vencidas', sumVal(vencidas), 'currency');
                }
            };

            if (monthEl) {
                monthEl.addEventListener('change', (e) => {
                    const m = e.target.value || '';
                    applyMonthFilter(m);
                });
                // aplica filtro inicial
                applyMonthFilter(monthEl.value || '');
            }

            if (baseSelect) {
                baseSelect.addEventListener('change', (e) => {
                    receitasBase = e.target.value || 'due_date';
                    try { if (localStorage) localStorage.setItem('receitasBase', receitasBase); } catch(_) {}
                    const m = (monthEl && monthEl.value) ? monthEl.value : '';
                    applyMonthFilter(m);
                });
            }
        });
    </script>
    <script>
        // Personalização e ordenação de cards financeiros (namespaced em state.finance)
        (function() {
            const grid = document.getElementById('financeCardsGrid');
            if (!grid) return;

            const storageKey = grid.dataset.storageKey || 'dashboard.finance.cards';
            const modal = document.getElementById('modalPersonalizarCards');
            const btnOpen = document.getElementById('btnPersonalizarCards');
            const toggles = modal ? modal.querySelectorAll('.toggle-card') : [];
            const csrfToken = (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')) || window.csrfToken || '';
            const errorEl = modal ? modal.querySelector('#modalPrefError') : null;

            const defaultState = {
                order: ['finance-receitas', 'finance-despesas', 'finance-inadimplencia', 'finance-tickets', 'finance-mrr', 'finance-metodo', 'finance-blank'],
                visible: {
                    'finance-receitas': true,
                    'finance-despesas': true,
                    'finance-inadimplencia': true,
                    'finance-tickets': true,
                    'finance-mrr': true,
                    'finance-metodo': true,
                    'finance-blank': true
                }
            };

            let state = Object.assign({}, defaultState); // representa apenas state.finance
            let remoteAll = null; // objeto completo no backend

            const getState = async () => {
                try {
                    const res = await fetch("{{ route('dashboard.preferences.index') }}", { credentials: 'same-origin' });
                    if (!res.ok) return null;
                    const data = await res.json();
                    remoteAll = data?.state || null;
                    return remoteAll?.finance || null;
                } catch (_) { return null; }
            };

            const setState = async (newState) => {
                try {
                    const all = Object.assign({}, remoteAll || {});
                    all.finance = newState;
                    await fetch("{{ route('dashboard.preferences.save') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ state: all })
                    });
                    remoteAll = all;
                } catch (_) {}
            };

            const loadAndApplyState = async () => {
                const remote = await getState();
                state = Object.assign({}, defaultState, remote || {});
                applyState();
            };

            const cardsByKey = {};
            grid.querySelectorAll('[data-card-key]').forEach(el => { cardsByKey[el.dataset.cardKey] = el; });

            const applyState = () => {
                // visibility
                Object.keys(cardsByKey).forEach(key => {
                    const visible = state.visible[key] !== false;
                    cardsByKey[key].classList.toggle('hidden', !visible);
                });
                // order
                const baseOrder = Array.isArray(state.order) ? state.order.slice() : defaultState.order.slice();
                const allKeys = Object.keys(cardsByKey);
                const finalOrder = [...baseOrder, ...allKeys.filter(k => !baseOrder.includes(k))];
                finalOrder.forEach(key => {
                    const el = cardsByKey[key];
                    if (el) grid.appendChild(el);
                });
                // toggles
                toggles.forEach(t => {
                    const key = t.dataset.cardKey;
                    t.checked = state.visible[key] !== false;
                });
                // clear any residual drop highlight
                Object.values(cardsByKey).forEach(el => el.classList.remove('ring-2','ring-indigo-400','ring-offset-2'));
            };
            loadAndApplyState();

            // Modal events
            if (btnOpen && modal) {
                btnOpen.addEventListener('click', () => { 
                    modal.classList.remove('hidden'); modal.classList.add('flex');
                    if (errorEl) { errorEl.textContent = ''; errorEl.classList.add('hidden'); }
                });
                modal.querySelectorAll('[data-action="close-modal"]').forEach(btn => btn.addEventListener('click', () => {
                    modal.classList.add('hidden'); modal.classList.remove('flex');
                }));
                const btnSave = modal.querySelector('[data-action="save-preferences"]');
                if (btnSave) {
                    btnSave.addEventListener('click', async () => {
                        const saveText = btnSave.querySelector('.save-text');
                        const saveLoading = btnSave.querySelector('.save-loading');
                        btnSave.disabled = true;
                        if (saveLoading) saveLoading.classList.remove('hidden');
                        if (saveText) saveText.textContent = 'Salvando...';
                        toggles.forEach(t => { state.visible[t.dataset.cardKey] = !!t.checked; });
                        let ok = true;
                        try {
                            await setState(state);
                        } catch (e) { ok = false; }
                        applyState();
                        if (saveText) saveText.textContent = ok ? 'Salvo!' : 'Erro ao salvar';
                        if (!ok && errorEl) { errorEl.textContent = 'Falha ao salvar preferências. Verifique sua conexão.'; errorEl.classList.remove('hidden'); }
                        setTimeout(() => {
                            if (saveLoading) saveLoading.classList.add('hidden');
                            btnSave.disabled = false;
                            if (saveText) saveText.textContent = 'Salvar';
                            if (ok) { modal.classList.add('hidden'); modal.classList.remove('flex'); }
                        }, 800);
                    });
                }
                const btnRestore = modal.querySelector('[data-action="restore-default"]');
                if (btnRestore) {
                    btnRestore.addEventListener('click', async () => {
                        const original = btnRestore.textContent;
                        btnRestore.disabled = true;
                        btnRestore.textContent = 'Restaurando...';
                        try {
                            state = Object.assign({}, defaultState);
                            await setState(state);
                            applyState();
                            btnRestore.textContent = 'Restaurado!';
                            setTimeout(() => {
                                btnRestore.disabled = false;
                                btnRestore.textContent = original;
                                modal.classList.add('hidden'); modal.classList.remove('flex');
                            }, 800);
                        } catch (_) {
                            if (errorEl) { errorEl.textContent = 'Falha ao restaurar preferências. Tente novamente.'; errorEl.classList.remove('hidden'); }
                            btnRestore.disabled = false;
                            btnRestore.textContent = original;
                        }
                    });
                }
            }

            // Hide button on card
            grid.querySelectorAll('[data-action="hide-card"]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const card = e.currentTarget.closest('[data-card-key]');
                    const key = card?.dataset.cardKey;
                    if (!key) return;
                    state.visible[key] = false;
                    setState(state);
                    applyState();
                });
            });

            // Drag-and-drop ordering via HTML5 drag events
            let dragKey = null;
            grid.addEventListener('dragstart', (e) => {
                // only allow drag starting from the card header handle
                if (!e.target.closest('[data-card-header]')) return;
                const card = e.target.closest('[data-card-key]');
                if (!card) return;
                dragKey = card.dataset.cardKey;
                e.dataTransfer.effectAllowed = 'move';
            });
            grid.addEventListener('dragover', (e) => { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; });
            // visual feedback: highlight drop target
            Object.values(cardsByKey).forEach(el => {
                el.addEventListener('dragenter', () => {
                    if (!dragKey) return;
                    if (el.classList.contains('hidden')) return;
                    el.classList.add('ring-2','ring-indigo-400','ring-offset-2');
                });
                el.addEventListener('dragleave', () => {
                    el.classList.remove('ring-2','ring-indigo-400','ring-offset-2');
                });
                el.addEventListener('drop', () => {
                    el.classList.remove('ring-2','ring-indigo-400','ring-offset-2');
                });
            });
            grid.addEventListener('drop', (e) => {
                e.preventDefault();
                const target = e.target.closest('[data-card-key]');
                if (!dragKey || !target) return;
                // block drop on hidden cards
                if (target.classList.contains('hidden')) return;
                const targetKey = target.dataset.cardKey;
                if (dragKey === targetKey) return;
                let order = (Array.isArray(state.order) ? state.order.slice() : defaultState.order.slice()).filter(k => cardsByKey[k]);
                const allKeys = Object.keys(cardsByKey);
                order = [...order, ...allKeys.filter(k => !order.includes(k))];
                const fromIdx = order.indexOf(dragKey);
                const toIdx = order.indexOf(targetKey);
                if (fromIdx === -1 || toIdx === -1) return;
                order.splice(toIdx, 0, order.splice(fromIdx, 1)[0]);
                state.order = order;
                setState(state);
                applyState();
            });
        })();
    </script>
@endpush
