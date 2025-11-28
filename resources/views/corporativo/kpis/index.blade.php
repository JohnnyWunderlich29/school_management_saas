@extends('corporativo.layout')

@section('title', 'KPIs Corporativos')
@section('page-title', 'KPIs Corporativos')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">KPIs do Negócio</h1>
                    <p class="mt-2 text-gray-600">Visão executiva com métricas, gráficos e ranking</p>
                </div>
                <div class="w-full sm:w-64">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Base de cálculo</label>
                    <select id="base" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="trocarBase()">
                        <option value="estimado" {{ ($base ?? 'estimado') === 'estimado' ? 'selected' : '' }}>Estimado (Plano + Módulos)</option>
                        <option value="plano" {{ ($base ?? '') === 'plano' ? 'selected' : '' }}>Somente Plano</option>
                        <option value="modulos" {{ ($base ?? '') === 'modulos' ? 'selected' : '' }}>Somente Módulos</option>
                        <option value="mensalidade" {{ ($base ?? '') === 'mensalidade' ? 'selected' : '' }}>Mensalidade (campo)</option>
                    </select>
                </div>
            </div>
            <!-- Informativo sobre as bases de cálculo -->
            <div class="mt-4 p-4 rounded border border-blue-200 bg-blue-50 text-sm text-blue-900">
                <div class="mb-2">
                    <span class="font-semibold">Base atual:</span>
                    <span class="ml-1">
                        @switch($base ?? 'estimado')
                            @case('plano') Somente Plano @break
                            @case('modulos') Somente Módulos @break
                            @case('mensalidade') Mensalidade (campo) @break
                            @default Estimado (Plano + Módulos)
                        @endswitch
                    </span>
                </div>
                <ul class="list-disc pl-5 space-y-1">
                    <li><span class="font-medium">Estimado (Plano + Módulos):</span> usa o preço atual do plano somado aos módulos ativos. Ideal para KPIs recorrentes (MRR/ARR) por refletir a configuração vigente.</li>
                    <li><span class="font-medium">Somente Plano:</span> considera apenas o valor do plano da escola. Útil para analisar receita do core sem add-ons.</li>
                    <li><span class="font-medium">Somente Módulos:</span> soma apenas os módulos ativos (add-ons). Útil para medir a penetração e impacto dos módulos.</li>
                    <li><span class="font-medium">Mensalidade (campo):</span> usa o campo persistido <code>valor_mensalidade</code>. Bom para comparação financeira, porém pode ficar desatualizado caso não seja sincronizado com alterações de módulos.</li>
                </ul>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-sm text-gray-500">MRR (Receita Recorrente Mensal)</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">R$ {{ number_format($cards['mrr'] ?? 0, 2, ',', '.') }}</div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-sm text-gray-500">ARR (Receita Recorrente Anual)</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">R$ {{ number_format($cards['arr'] ?? 0, 2, ',', '.') }}</div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-sm text-gray-500">ARPA (Média por Escola Pagante)</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">R$ {{ number_format($cards['arpa'] ?? 0, 2, ',', '.') }}</div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-sm text-gray-500">Taxa de Pagamento</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($cards['paying_rate'] ?? 0, 2, ',', '.') }}%</div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-sm text-gray-500">Inadimplência (Valor)</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">R$ {{ number_format($cards['inadimplencia_valor'] ?? 0, 2, ',', '.') }}</div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-sm text-gray-500">Inadimplência (Taxa)</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($cards['inadimplencia_rate'] ?? 0, 2, ',', '.') }}%</div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-sm text-gray-500">Média de Usuários por Escola</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($cards['avg_users_por_escola'] ?? 0, 2, ',', '.') }}</div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-sm text-gray-500">Escolas Ativas / Pagantes</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ $cards['escolas_pagantes'] ?? 0 }} / {{ $cards['escolas_ativas'] ?? 0 }}</div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-sm text-gray-500">Churn (Aproximado)</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($cards['churn_rate_aprox'] ?? 0, 2, ',', '.') }}%</div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="text-sm text-gray-500">LTV (Aproximado)</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">R$ {{ number_format($cards['ltv_aprox'] ?? 0, 2, ',', '.') }}</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">MRR - Últimos 6 meses</h3>
                </div>
                <div class="h-72">
                    <canvas id="mrrChart"></canvas>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Adoção por Módulo</h3>
                </div>
                <div class="h-72">
                    <canvas id="modulosChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Churn Trend -->
        <div class="grid grid-cols-1 gap-6 mb-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Churn (aprox.) - Últimos 6 meses</h3>
                </div>
                <div class="h-72">
                    <canvas id="churnChart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Distribuição por Planos</h3>
                </div>
                <div class="h-72">
                    <canvas id="planosChart"></canvas>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Notas</h3>
                </div>
                <ul class="list-disc list-inside text-sm text-gray-600 space-y-2">
                    <li>Base atual: <strong>{{ strtoupper($base ?? 'estimado') }}</strong> (seletor no topo direito).</li>
                    <li>MRR calculado conforme base selecionada.</li>
                    <li>Taxas baseadas em status atual de pagamento das escolas.</li>
                    <li>MRR histórico usa aproximação por criação e status atual.</li>
                    <li>Churn e LTV são aproximações com base em inadimplência atual.</li>
                </ul>
            </div>
        </div>

        <!-- Cohort de Ativação -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Cohort de Ativação (últimos 6 meses)</h3>
                <p class="text-sm text-gray-500">Escolas criadas por mês, com taxa de pagantes atual</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mês</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Criadas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagantes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taxa</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach(($cohort_ativacao ?? []) as $cohort)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cohort['mes'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $cohort['total'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $cohort['pagantes'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $rate = $cohort['rate'] ?? 0;
                                        $color = $rate >= 70 ? 'bg-green-100 text-green-800' : ($rate >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $color }}">{{ number_format($rate, 2, ',', '.') }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabela de Top Escolas -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top 10 Escolas por Receita</h3>
                <p class="text-sm text-gray-500">Base: {{ $base ?? 'estimado' }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Escola</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plano</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuários</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receita Mensal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagamento</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($top_escolas as $escola)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $escola->nome }}</div>
                                    <div class="text-xs text-gray-500">Cód: {{ $escola->codigo }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $escola->plano ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $escola->users_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$ {{ number_format($escola->receita_mensal_estimada, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($escola->em_dia)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Em dia</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Inadimplente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Nenhuma escola encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function trocarBase() {
        const base = document.getElementById('base').value;
        const params = new URLSearchParams(window.location.search);
        if (base) params.set('base', base);
        window.location.href = '{{ route('corporativo.kpis') }}' + '?' + params.toString();
    }
    const mrrData = @json($mrr_historico);
    const modulosData = @json($adocao_modulos);
    const planosData = @json($por_plano);
    const churnData = @json($churn_historico);

    // MRR - Line Chart
    const mrrCtx = document.getElementById('mrrChart').getContext('2d');
    new Chart(mrrCtx, {
        type: 'line',
        data: {
            labels: mrrData.labels,
            datasets: [{
                label: 'MRR (R$)',
                data: mrrData.values,
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124, 58, 237, 0.1)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Adoção por Módulo - Bar Chart
    const modCtx = document.getElementById('modulosChart').getContext('2d');
    new Chart(modCtx, {
        type: 'bar',
        data: {
            labels: modulosData.map(m => m.name),
            datasets: [{
                label: 'Escolas utilizando',
                data: modulosData.map(m => m.count),
                backgroundColor: '#10b981',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Churn (aprox.) - Line Chart
    const churnCtx = document.getElementById('churnChart').getContext('2d');
    new Chart(churnCtx, {
        type: 'line',
        data: {
            labels: churnData.labels,
            datasets: [{
                label: 'Churn (%)',
                data: churnData.values,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Distribuição por Planos - Doughnut Chart
    const planosCtx = document.getElementById('planosChart').getContext('2d');
    const planosLabels = Object.keys(planosData);
    const planosCounts = Object.values(planosData).map(v => v.total);
    new Chart(planosCtx, {
        type: 'doughnut',
        data: {
            labels: planosLabels,
            datasets: [{
                data: planosCounts,
                backgroundColor: ['#7c3aed', '#10b981', '#f59e0b', '#ef4444', '#3b82f6']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endsection