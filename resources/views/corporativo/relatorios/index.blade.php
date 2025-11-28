@extends('corporativo.layout')

@section('title', 'Relatórios Consolidados')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Relatórios Consolidados</h1>
            <p class="mt-2 text-gray-600">Visão geral de todas as escolas do sistema</p>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                    <select id="periodo" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="7">Últimos 7 dias</option>
                        <option value="30" selected>Últimos 30 dias</option>
                        <option value="90">Últimos 90 dias</option>
                        <option value="365">Último ano</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Escola</label>
                    <select id="escola" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas as escolas</option>
                        @foreach($escolas as $escola)
                        <option value="{{ $escola->id }}">{{ $escola->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Plano</label>
                    <select id="plano" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos os planos</option>
                        <option value="basico">Básico</option>
                        <option value="premium">Premium</option>
                        <option value="enterprise">Enterprise</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Base de cálculo</label>
                    <select id="base" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="estimado" {{ ($base ?? 'estimado') === 'estimado' ? 'selected' : '' }}>Estimado (Plano + Módulos)</option>
                        <option value="plano" {{ ($base ?? '') === 'plano' ? 'selected' : '' }}>Somente Plano</option>
                        <option value="modulos" {{ ($base ?? '') === 'modulos' ? 'selected' : '' }}>Somente Módulos</option>
                        <option value="mensalidade" {{ ($base ?? '') === 'mensalidade' ? 'selected' : '' }}>Mensalidade (campo)</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="aplicarFiltros()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Aplicar Filtros
                    </button>
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

        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total de Escolas</p>
                        <p class="text-2xl font-bold text-gray-900" id="total-escolas">{{ $stats['total_escolas'] }}</p>
                        <p class="text-xs text-green-600">{{ $stats['escolas_ativas'] }} ativas</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total de Usuários</p>
                        <p class="text-2xl font-bold text-gray-900" id="total-usuarios">{{ $stats['total_usuarios'] }}</p>
                        <p class="text-xs text-blue-600">{{ $stats['novos_usuarios_mes'] }} novos este mês</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Receita Mensal</p>
                        <p class="text-2xl font-bold text-gray-900" id="receita-mensal">R$ {{ number_format($stats['receita_mensal'], 2, ',', '.') }}</p>
                        <p class="text-xs text-green-600">{{ $stats['escolas_em_dia'] }} escolas em dia</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Inadimplentes</p>
                        <p class="text-2xl font-bold text-gray-900" id="inadimplentes">{{ $stats['escolas_inadimplentes'] }}</p>
                        <p class="text-xs text-red-600">R$ {{ number_format($stats['valor_inadimplencia'], 2, ',', '.') }} em atraso</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Gráfico de Crescimento -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Crescimento de Escolas</h3>
                <div class="h-64">
                    <canvas id="crescimentoChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de Receita -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Receita Mensal</h3>
                <div class="h-64">
                    <canvas id="receitaChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Distribuição por Planos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Distribuição por Planos</h3>
                <div class="space-y-4">
                    @foreach($stats['por_plano'] as $plano => $dados)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-3
                                @if($plano === 'enterprise') bg-purple-500
                                @elseif($plano === 'premium') bg-blue-500
                                @else bg-gray-500
                                @endif"></div>
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst($plano) }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-900">{{ $dados->total }}</span>
                            <span class="text-xs text-gray-500 block">R$ {{ number_format($dados->receita, 2, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Top 5 Escolas por Usuários -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top 5 - Mais Usuários</h3>
                <div class="space-y-3">
                    @foreach($stats['top_usuarios'] as $escola)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $escola->nome }}</p>
                            <p class="text-xs text-gray-500">{{ $escola->plano }}</p>
                        </div>
                        <span class="text-sm font-bold text-blue-600">{{ $escola->users_count }} usuários</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Escolas Recentes -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Escolas Recentes</h3>
                <div class="space-y-3">
                    @foreach($stats['escolas_recentes'] as $escola)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $escola->nome }}</p>
                            <p class="text-xs text-gray-500">{{ $escola->created_at->format('d/m/Y') }}</p>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($escola->ativo) bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ $escola->ativo ? 'Ativa' : 'Inativa' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tabela de Escolas -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Todas as Escolas</h3>
                <div class="flex space-x-2">
                    <button onclick="exportarRelatorio()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Exportar Excel
                    </button>
                    <button onclick="gerarPDF()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Gerar PDF
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Escola</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plano</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuários</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receita</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criada em</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="tabela-escolas">
                        @foreach($escolas as $escola)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $escola->nome }}</div>
                                    <div class="text-sm text-gray-500">{{ $escola->cnpj }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @if($escola->plano === 'enterprise') bg-purple-100 text-purple-800
                                    @elseif($escola->plano === 'premium') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($escola->plano) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $escola->users_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $__base = $base ?? 'estimado';
                                    switch ($__base) {
                                        case 'plano':
                                            $valor = (float) $escola->getPlanoPreco();
                                            break;
                                        case 'modulos':
                                            $valor = (float) $escola->getTotalModulesPrice();
                                            break;
                                        case 'mensalidade':
                                            $valor = (float) ($escola->valor_mensalidade ?? 0);
                                            break;
                                        case 'estimado':
                                        default:
                                            $valor = (float) ($escola->getPlanoPreco() + $escola->getTotalModulesPrice());
                                            break;
                                    }
                                @endphp
                                R$ {{ number_format($valor, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($escola->ativo) bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ $escola->ativo ? 'Ativa' : 'Inativa' }}
                                    </span>
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($escola->em_dia) bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ $escola->em_dia ? 'Em dia' : 'Inadimplente' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $escola->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('corporativo.escolas.detalhes', $escola->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Ver</a>
                                <button onclick="editarEscola({{ $escola->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                                @if(!$escola->ativo)
                                <button onclick="ativarEscola({{ $escola->id }})" class="text-green-600 hover:text-green-900">Ativar</button>
                                @else
                                <button onclick="desativarEscola({{ $escola->id }})" class="text-red-600 hover:text-red-900">Desativar</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dados para os gráficos
const crescimentoData = @json($stats['crescimento_mensal'] ?? []);
const receitaData = @json($stats['receita_mensal_historico'] ?? []);

// Gráfico de Crescimento
const ctxCrescimento = document.getElementById('crescimentoChart').getContext('2d');
new Chart(ctxCrescimento, {
    type: 'line',
    data: {
        labels: crescimentoData.map(item => item.mes),
        datasets: [{
            label: 'Novas Escolas',
            data: crescimentoData.map(item => item.count),
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Gráfico de Receita
const ctxReceita = document.getElementById('receitaChart').getContext('2d');
new Chart(ctxReceita, {
    type: 'bar',
    data: {
        labels: receitaData.map(item => item.mes),
        datasets: [{
            label: 'Receita (R$)',
            data: receitaData.map(item => item.valor),
            backgroundColor: 'rgba(34, 197, 94, 0.8)',
            borderColor: 'rgb(34, 197, 94)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'R$ ' + value.toLocaleString('pt-BR');
                    }
                }
            }
        }
    }
});

// Funções
function aplicarFiltros() {
    const periodo = document.getElementById('periodo').value;
    const escola = document.getElementById('escola').value;
    const plano = document.getElementById('plano').value;
    const base = document.getElementById('base').value;
    
    // Construir URL com parâmetros
    const params = new URLSearchParams();
    if (periodo) params.append('periodo', periodo);
    if (escola) params.append('escola', escola);
    if (plano) params.append('plano', plano);
    if (base) params.append('base', base);
    
    window.location.href = '{{ route("corporativo.relatorios") }}?' + params.toString();
}

function exportarRelatorio() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    window.location.href = '{{ route("corporativo.relatorios") }}?' + params.toString();
}

function gerarPDF() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'pdf');
    window.open('{{ route("corporativo.relatorios") }}?' + params.toString(), '_blank');
}

function editarEscola(id) {
    window.location.href = '{{ route("corporativo.escolas") }}?edit=' + id;
}

function ativarEscola(id) {
    if (confirm('Tem certeza que deseja ativar esta escola?')) {
        fetch(`/api/escolas/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao ativar escola: ' + data.message);
            }
        });
    }
}

function desativarEscola(id) {
    if (confirm('Tem certeza que deseja desativar esta escola?')) {
        fetch(`/api/escolas/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao desativar escola: ' + data.message);
            }
        });
    }
}
</script>
@endsection