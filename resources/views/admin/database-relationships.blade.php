@extends('admin.layout')

@section('title', 'Relacionamentos do Banco de Dados')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 mb-8">
            <div class="px-8 py-6 border-b border-slate-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="p-3 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg">
                            <i class="fas fa-project-diagram text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">Relacionamentos do Banco</h1>
                            <p class="text-slate-600 text-sm">Visualize e gerencie os relacionamentos entre tabelas</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm" onclick="refreshRelationships()">
                            <i class="fas fa-sync-alt mr-2"></i> Atualizar
                        </button>
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm" onclick="exportRelationships()">
                            <i class="fas fa-download mr-2"></i> Exportar
                        </button>
                        <button type="button" class="inline-flex items-center px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm" onclick="toggleView()">
                            <i class="fas fa-eye mr-2"></i> <span id="view-toggle-text">Visualização Gráfica</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-8">
                <!-- Filtros -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="space-y-2">
                        <label for="filter-table" class="block text-sm font-semibold text-slate-700">Filtrar por Tabela</label>
                        <select id="filter-table" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white" onchange="filterRelationships()">
                            <option value="">Todas as tabelas</option>
                            @foreach($tables as $table)
                                <option value="{{ $table }}">{{ $table }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="filter-type" class="block text-sm font-semibold text-slate-700">Filtrar por Tipo</label>
                        <select id="filter-type" class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white" onchange="filterRelationships()">
                            <option value="">Todos os tipos</option>
                            <option value="foreign_key">Foreign Key</option>
                            <option value="inferred">Inferido</option>
                            <option value="fallback">Fallback</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="search-relationship" class="block text-sm font-semibold text-slate-700">Buscar</label>
                        <div class="relative">
                            <input type="text" id="search-relationship" class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" placeholder="Buscar relacionamento..." oninput="filterRelationships()">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Total de Relacionamentos</p>
                                <p class="text-3xl font-bold" id="total-relationships">{{ count($relationships) }}</p>
                            </div>
                            <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                                <i class="fas fa-link text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-emerald-100 text-sm font-medium">Foreign Keys</p>
                                <p class="text-3xl font-bold" id="foreign-key-count">{{ collect($relationships)->where('relationship_type', 'foreign_key')->count() }}</p>
                            </div>
                            <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                                <i class="fas fa-key text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-cyan-100 text-sm font-medium">Inferidos</p>
                                <p class="text-3xl font-bold" id="inferred-count">{{ collect($relationships)->where('relationship_type', 'inferred')->count() }}</p>
                            </div>
                            <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                                <i class="fas fa-brain text-2xl"></i>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-amber-100 text-sm font-medium">Tabelas</p>
                                <p class="text-3xl font-bold" id="tables-count">{{ count($tables) }}</p>
                            </div>
                            <div class="p-3 bg-white bg-opacity-20 rounded-lg">
                                <i class="fas fa-table text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visualização em Tabela -->
                <div id="table-view" class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full" id="relationships-table">
                            <thead class="bg-gradient-to-r from-slate-800 to-slate-900 text-white">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Tabela Origem</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Coluna Origem</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold">Relacionamento</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Tabela Destino</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Coluna Destino</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold">Tipo</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold">Constraint</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($relationships as $relationship)
                                    <tr class="relationship-row hover:bg-slate-50 transition-colors duration-150" 
                                        data-from-table="{{ $relationship['from_table'] }}" 
                                        data-to-table="{{ $relationship['to_table'] }}" 
                                        data-type="{{ $relationship['relationship_type'] }}">
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800">{{ $relationship['from_table'] }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <code class="px-2 py-1 bg-slate-100 text-slate-800 rounded text-sm">{{ $relationship['from_column'] }}</code>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center">
                                                <i class="fas fa-arrow-right text-blue-500 text-lg"></i>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-800">{{ $relationship['to_table'] }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <code class="px-2 py-1 bg-slate-100 text-slate-800 rounded text-sm">{{ $relationship['to_column'] }}</code>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @php
                                                $typeClass = match($relationship['relationship_type']) {
                                                    'foreign_key' => 'bg-emerald-100 text-emerald-800',
                                                    'inferred' => 'bg-cyan-100 text-cyan-800',
                                                    'fallback' => 'bg-amber-100 text-amber-800',
                                                    default => 'bg-slate-100 text-slate-800'
                                                };
                                                $typeText = match($relationship['relationship_type']) {
                                                    'foreign_key' => 'FK',
                                                    'inferred' => 'Inferido',
                                                    'fallback' => 'Fallback',
                                                    default => 'Desconhecido'
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $typeClass }}">{{ $typeText }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-slate-500">{{ $relationship['constraint_name'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Visualização Gráfica -->
                <div id="graph-view" class="hidden">
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <i class="fas fa-info-circle text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-blue-900">Visualização Gráfica</h3>
                                <p class="text-blue-700 text-sm">Esta funcionalidade mostra um diagrama interativo dos relacionamentos entre tabelas.</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
                        <div id="graph-container" class="h-96 lg:h-[600px] p-4">
                            <!-- O gráfico será renderizado aqui via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalhes do relacionamento -->
<div id="relationshipModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Detalhes do Relacionamento</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors" onclick="closeModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="relationship-details" class="text-sm text-gray-600">
                    <!-- Conteúdo será preenchido via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let allRelationships = @json($relationships);
let currentView = 'table';

// Função para atualizar relacionamentos
function refreshRelationships() {
    showNotification('Atualizando relacionamentos...', 'info');
    
    fetch('{{ route("admin.api.database.relationships") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allRelationships = data.data;
                updateRelationshipsTable();
                updateStatistics();
                showNotification('Relacionamentos atualizados com sucesso!', 'success');
            } else {
                showNotification('Erro ao atualizar relacionamentos: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao atualizar relacionamentos', 'error');
        });
}

// Função para filtrar relacionamentos
function filterRelationships() {
    const tableFilter = document.getElementById('filter-table').value;
    const typeFilter = document.getElementById('filter-type').value;
    const searchTerm = document.getElementById('search-relationship').value.toLowerCase();
    
    const rows = document.querySelectorAll('.relationship-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const fromTable = row.dataset.fromTable;
        const toTable = row.dataset.toTable;
        const type = row.dataset.type;
        const text = row.textContent.toLowerCase();
        
        let show = true;
        
        // Filtro por tabela
        if (tableFilter && fromTable !== tableFilter && toTable !== tableFilter) {
            show = false;
        }
        
        // Filtro por tipo
        if (typeFilter && type !== typeFilter) {
            show = false;
        }
        
        // Filtro por busca
        if (searchTerm && !text.includes(searchTerm)) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
    
    // Atualizar contador
    document.getElementById('total-relationships').textContent = visibleCount;
}

// Função para alternar visualização
function toggleView() {
    const tableView = document.getElementById('table-view');
    const graphView = document.getElementById('graph-view');
    const toggleText = document.getElementById('view-toggle-text');
    
    if (currentView === 'table') {
        tableView.classList.add('hidden');
        graphView.classList.remove('hidden');
        toggleText.textContent = 'Visualização em Tabela';
        currentView = 'graph';
        renderGraph();
    } else {
        tableView.classList.remove('hidden');
        graphView.classList.add('hidden');
        toggleText.textContent = 'Visualização Gráfica';
        currentView = 'table';
    }
}

function closeModal() {
    document.getElementById('relationshipModal').classList.add('hidden');
}

function openModal() {
    document.getElementById('relationshipModal').classList.remove('hidden');
}

// Função para renderizar gráfico
function renderGraph() {
    const container = document.getElementById('graph-container');
    container.innerHTML = '<div class="d-flex justify-content-center align-items-center h-100"><div class="text-center"><i class="fas fa-project-diagram fa-3x text-muted mb-3"></i><p class="text-muted">Gráfico de relacionamentos será implementado aqui</p></div></div>';
}

// Função para exportar relacionamentos
function exportRelationships() {
    const data = allRelationships.map(rel => ({
        'Tabela Origem': rel.from_table,
        'Coluna Origem': rel.from_column,
        'Tabela Destino': rel.to_table,
        'Coluna Destino': rel.to_column,
        'Tipo': rel.relationship_type,
        'Constraint': rel.constraint_name
    }));
    
    const csv = convertToCSV(data);
    downloadCSV(csv, 'relacionamentos-banco.csv');
    showNotification('Relacionamentos exportados com sucesso!', 'success');
}

// Função para converter para CSV
function convertToCSV(data) {
    if (!data.length) return '';
    
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => `"${row[header]}"`).join(','))
    ].join('\n');
    
    return csvContent;
}

// Função para download do CSV
function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Função para atualizar tabela de relacionamentos
function updateRelationshipsTable() {
    // Esta função seria implementada para atualizar a tabela dinamicamente
    location.reload(); // Por enquanto, recarrega a página
}

// Função para atualizar estatísticas
function updateStatistics() {
    const foreignKeyCount = allRelationships.filter(rel => rel.relationship_type === 'foreign_key').length;
    const inferredCount = allRelationships.filter(rel => rel.relationship_type === 'inferred').length;
    
    document.getElementById('total-relationships').textContent = allRelationships.length;
    document.getElementById('foreign-key-count').textContent = foreignKeyCount;
    document.getElementById('inferred-count').textContent = inferredCount;
}

// Função para mostrar notificações
function showNotification(message, type) {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'info': 'alert-info',
        'warning': 'alert-warning'
    }[type] || 'alert-info';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar event listeners para clique nas linhas da tabela
    document.querySelectorAll('.relationship-row').forEach(row => {
        row.addEventListener('click', function() {
            // Implementar modal com detalhes do relacionamento
            console.log('Clicou na linha:', this.dataset);
        });
    });
});
</script>
@endsection

@section('styles')
<style>
.relationship-row {
    cursor: pointer;
    transition: background-color 0.2s;
}

.relationship-row:hover {
    background-color: rgba(0, 123, 255, 0.1) !important;
}

.badge {
    font-size: 0.75em;
}

code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.875em;
}

#graph-container {
    background-color: #f8f9fa;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table th {
    border-top: none;
    font-weight: 600;
}

.alert {
    border: none;
    border-radius: 0.5rem;
}
</style>
@endsection