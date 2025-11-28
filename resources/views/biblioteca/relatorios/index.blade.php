@extends('layouts.app')

@section('title', 'Relatórios - Biblioteca Digital')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Biblioteca', 'url' => route('biblioteca.index')],
    ['title' => 'Relatórios', 'url' => '#']
]" />

<x-card tone="report">
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Relatórios da Biblioteca</h1>
            <p class="mt-1 text-sm text-gray-600">Análises, filtros e exportações com o mesmo padrão da tela de Empréstimos</p>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-indigo-50 to-white border border-indigo-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-semibold text-gray-900">{{ $estatisticas['total_emprestimos'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Empréstimos Ativos</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <x-icon name="book-open" class="h-5 w-5" />
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-indigo-50 to-white border border-indigo-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-semibold text-gray-900">{{ $estatisticas['total_reservas'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Reservas Ativas</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <x-icon name="bookmark" class="h-5 w-5" />
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-indigo-50 to-white border border-indigo-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-semibold text-gray-900">{{ $estatisticas['emprestimos_atrasados'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Empréstimos Atrasados</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <x-icon name="exclamation-triangle" class="h-5 w-5" />
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-br from-indigo-50 to-white border border-indigo-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl font-semibold text-gray-900">{{ $estatisticas['total_itens'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Itens no Acervo</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <x-icon name="rectangle-stack" class="h-5 w-5" />
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Relatório -->
    <x-collapsible-filter 
        title="Selecionar filtros" 
        :action="route('biblioteca.relatorios.index')" 
        :clear-route="route('biblioteca.relatorios.index')"
        target="relatorio-content-wrapper"
    >
        <x-filter-field 
            name="tipo_relatorio" 
            label="Tipo de Relatório" 
            type="select"
            empty-option="Selecione"
            :options="[
                'emprestimos' => 'Empréstimos',
                'reservas' => 'Reservas',
                'multas' => 'Multas',
                'usuarios_ativos' => 'Usuários Mais Ativos',
                'itens_populares' => 'Itens Mais Populares',
                'estatisticas_gerais' => 'Estatísticas Gerais',
            ]"
            :value="request('tipo_relatorio', 'estatisticas_gerais')"
        />

        <x-filter-field 
            name="data_inicio" 
            label="Data Início" 
            type="date"
            :value="request('data_inicio', date('Y-m-01'))"
        />

        <x-filter-field 
            name="data_fim" 
            label="Data Fim" 
            type="date"
            :value="request('data_fim', date('Y-m-d'))"
        />

        <x-filter-field 
            name="status" 
            label="Status" 
            type="select"
            empty-option="Todos"
            :options="[
                '' => 'Todos',
                'ativo' => 'Ativo',
                'devolvido' => 'Devolvido',
                'atrasado' => 'Atrasado',
                'cancelado' => 'Cancelado',
            ]"
            :value="request('status')"
        />

        <x-filter-field 
            name="habilitado_emprestimo" 
            label="Habilitado para empréstimo" 
            type="select"
            empty-option="Todos"
            :options="[
                '1' => 'Sim',
                '0' => 'Não'
            ]"
            :value="request('habilitado_emprestimo')"
        />

        <div class="col-span-full">
            <div class="flex gap-2">
                <x-button color="primary" type="button" onclick="gerarRelatorio()">
                    <x-icon name="chart-bar" class="h-5 w-5 mr-2" />Gerar Relatório
                </x-button>
                <x-button color="success" type="button" onclick="exportarRelatorio('excel')">
                    <x-icon name="arrow-down-tray" class="h-5 w-5 mr-2" />Exportar Excel
                </x-button>
                <x-button color="danger" type="button" onclick="exportarRelatorio('pdf')">
                    <x-icon name="document-text" class="h-5 w-5 mr-2" />Exportar PDF
                </x-button>
            </div>
        </div>
    </x-collapsible-filter>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-card tone="report">
            <h5 class="text-base font-semibold text-gray-900 mb-2">Empréstimos por Mês</h5>
            <div class="h-64">
                <canvas id="graficoEmprestimos"></canvas>
            </div>
        </x-card>
        <x-card tone="report">
            <h5 class="text-base font-semibold text-gray-900 mb-2">Tipos de Itens Mais Emprestados</h5>
            <div class="h-64">
                <canvas id="graficoTiposItens"></canvas>
            </div>
        </x-card>
    </div>

    <!-- Resultados do Relatório -->
    <div id="relatorio-content-wrapper" class="relative mt-6">
        <x-loading-overlay message="Gerando relatório..." />
        <x-card tone="report">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-base md:text-lg font-semibold text-gray-900"><x-icon name="rectangle-stack" class="h-5 w-5 mr-2 inline" />Resultados do Relatório</h2>
            </div>
            <div>
                <div id="relatorioContent" data-ajax-content>
                    <div class="text-center py-6 text-gray-500">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-3">
                            <x-icon name="chart-bar" class="h-8 w-8 text-gray-400" />
                        </div>
                        <p>Selecione os filtros e clique em "Gerar Relatório" para visualizar os dados.</p>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Top Usuários e Top Itens -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <x-card tone="report">
            <h5 class="text-base font-semibold text-gray-900 mb-3">Top 10 Usuários Mais Ativos</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empréstimos</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservas</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topUsuarios ?? [] as $index => $usuario)
                            <tr class="odd:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mr-2">
                                            {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $usuario->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $usuario->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-sm"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ $usuario->emprestimos_count ?? 0 }}</span></td>
                                <td class="px-4 py-2 text-sm"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ $usuario->reservas_count ?? 0 }}</span></td>
                            </tr>
                        @empty
                            <tr class="odd:bg-gray-50">
                                <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">Nenhum dado disponível</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
        <x-card tone="report">
            <h5 class="text-base font-semibold text-gray-900 mb-3">Top 10 Itens Mais Emprestados</h5>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empréstimos</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topItens ?? [] as $index => $item)
                            <tr class="odd:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $index + 1 }}</td>
                                <td class="px-4 py-2">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->titulo }}</div>
                                        @if($item->autores)
                                            <div class="text-xs text-gray-500">{{ $item->autores }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-sm"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($item->tipo) }}</span></td>
                                <td class="px-4 py-2 text-sm"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $item->emprestimos_count ?? 0 }}</span></td>
                            </tr>
                        @empty
                            <tr class="odd:bg-gray-50">
                                <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">Nenhum dado disponível</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-card>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de Empréstimos por Mês
const ctxEmprestimosEl = document.getElementById('graficoEmprestimos');
if (ctxEmprestimosEl) {
const ctxEmprestimos = ctxEmprestimosEl.getContext('2d');
const graficoEmprestimos = new Chart(ctxEmprestimos, {
    type: 'line',
    data: {
        labels: {!! json_encode($dadosGraficos['meses'] ?? []) !!},
        datasets: [{
            label: 'Empréstimos',
            data: {!! json_encode($dadosGraficos['emprestimos'] ?? []) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
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
}

// Gráfico de Tipos de Itens
const ctxTiposEl = document.getElementById('graficoTiposItens');
if (ctxTiposEl) {
const ctxTipos = ctxTiposEl.getContext('2d');
const graficoTipos = new Chart(ctxTipos, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($dadosGraficos['tipos_labels'] ?? []) !!},
        datasets: [{
            data: {!! json_encode($dadosGraficos['tipos_dados'] ?? []) !!},
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
}

function gerarRelatorio() {
    const form = document.getElementById('autoFilterForm');
    if (!form) { return; }
    const formData = new FormData(form);
    const wrapper = document.getElementById('relatorio-content-wrapper');
    const overlay = wrapper ? (wrapper.querySelector('[data-loading-overlay]') || null) : null;
    const content = document.getElementById('relatorioContent');
    if (overlay) overlay.classList.remove('hidden');
    if (content) content.innerHTML = '';

    fetch("{{ route('biblioteca.relatorios.gerar') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        if (overlay) overlay.classList.add('hidden');
        if (content) content.innerHTML = html;
    })
    .catch(error => {
        if (overlay) overlay.classList.add('hidden');
        if (content) content.innerHTML = '<div class="text-center py-4 text-red-600">Erro ao gerar relatório: ' + error.message + '</div>';
    });
}

function exportarRelatorio(formato) {
    const form = document.getElementById('autoFilterForm');
    if (!form) { return; }
    const formData = new FormData(form);
    formData.append('formato', formato);

    fetch("{{ route('biblioteca.relatorios.exportar') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        }
        throw new Error('Erro ao exportar relatório');
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = `relatorio_biblioteca_${new Date().toISOString().split('T')[0]}.${formato === 'excel' ? 'xlsx' : 'pdf'}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    })
    .catch(error => {
        alert('Erro ao exportar relatório: ' + error.message);
    });
}

// Atualizar campos de status baseado no tipo de relatório
const tipoRelatorioEl = document.getElementById('tipo_relatorio');
if (tipoRelatorioEl) {
tipoRelatorioEl.addEventListener('change', function() {
    const statusSelect = document.getElementById('status');
    const tipo = this.value;
    
    // Limpar opções atuais
    statusSelect.innerHTML = '<option value="">Todos</option>';
    
    // Adicionar opções baseadas no tipo
    if (tipo === 'emprestimos') {
        statusSelect.innerHTML += `
            <option value="ativo">Ativo</option>
            <option value="devolvido">Devolvido</option>
            <option value="atrasado">Atrasado</option>
            <option value="renovado">Renovado</option>
        `;
    } else if (tipo === 'reservas') {
        statusSelect.innerHTML += `
            <option value="ativa">Ativa</option>
            <option value="processada">Processada</option>
            <option value="cancelada">Cancelada</option>
            <option value="expirada">Expirada</option>
        `;
    } else if (tipo === 'multas') {
        statusSelect.innerHTML += `
            <option value="pendente">Pendente</option>
            <option value="paga">Paga</option>
            <option value="cancelada">Cancelada</option>
        `;
    }
});
}
</script>
@endpush