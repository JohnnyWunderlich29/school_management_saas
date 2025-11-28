@extends('corporativo.layout')

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
                        <label for="search-input" class="block text-sm font-semibold text-slate-700">Buscar</label>
                        <input type="text" id="search-input" placeholder="Buscar relacionamentos..." class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200" onkeyup="searchRelationships()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Visualização em Tabela -->
        <div id="table-view" class="bg-white rounded-xl shadow-lg border border-slate-200">
            <div class="px-8 py-6 border-b border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">Lista de Relacionamentos</h2>
                <p class="text-slate-600 text-sm">Total de relacionamentos encontrados: <span id="total-count">{{ count($relationships) }}</span></p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tabela Origem</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Coluna Origem</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tabela Destino</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Coluna Destino</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Constraint</th>
                        </tr>
                    </thead>
                    <tbody id="relationships-tbody" class="bg-white divide-y divide-slate-200">
                        @forelse($relationships as $relationship)
                            <tr class="relationship-row hover:bg-slate-50 transition-colors duration-200" 
                                data-from-table="{{ $relationship['from_table'] }}" 
                                data-to-table="{{ $relationship['to_table'] }}" 
                                data-type="{{ $relationship['relationship_type'] }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono bg-blue-50 text-blue-800 px-2 py-1 rounded">{{ $relationship['from_table'] }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono bg-green-50 text-green-800 px-2 py-1 rounded">{{ $relationship['from_column'] }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono bg-blue-50 text-blue-800 px-2 py-1 rounded">{{ $relationship['to_table'] }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-sm font-mono bg-green-50 text-green-800 px-2 py-1 rounded">{{ $relationship['to_column'] }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badgeClass = match($relationship['relationship_type']) {
                                            'foreign_key' => 'bg-emerald-100 text-emerald-800',
                                            'inferred' => 'bg-yellow-100 text-yellow-800',
                                            'fallback' => 'bg-slate-100 text-slate-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                        {{ ucfirst($relationship['relationship_type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    <code class="text-xs bg-slate-50 px-2 py-1 rounded">{{ $relationship['constraint_name'] }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <i class="fas fa-database text-4xl text-slate-300"></i>
                                        <p class="text-slate-500 text-lg">Nenhum relacionamento encontrado</p>
                                        <p class="text-slate-400 text-sm">Verifique se existem foreign keys definidas no banco de dados</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Visualização Gráfica (inicialmente oculta) -->
        <div id="graph-view" class="bg-white rounded-xl shadow-lg border border-slate-200 hidden">
            <div class="px-8 py-6 border-b border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">Diagrama de Relacionamentos</h2>
                <p class="text-slate-600 text-sm">Visualização gráfica dos relacionamentos entre tabelas</p>
            </div>
            <div class="p-8">
                <div id="graph-container" class="w-full h-96 border border-slate-200 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-project-diagram text-4xl text-slate-300 mb-4"></i>
                        <p class="text-slate-500">Visualização gráfica em desenvolvimento</p>
                        <p class="text-slate-400 text-sm">Em breve será possível visualizar os relacionamentos em formato de diagrama</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-8 flex items-center space-x-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="text-slate-700 font-medium">Carregando relacionamentos...</span>
    </div>
</div>

<script>
let allRelationships = @json($relationships);
let currentView = 'table';

function refreshRelationships() {
    showLoading();
    
    fetch('{{ route("corporativo.api.database.relationships") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allRelationships = data.data;
                updateRelationshipsTable();
                showNotification('Relacionamentos atualizados com sucesso!', 'success');
            } else {
                showNotification('Erro ao atualizar relacionamentos: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            showNotification('Erro ao conectar com o servidor', 'error');
        })
        .finally(() => {
            hideLoading();
        });
}

function exportRelationships() {
    const csvContent = "data:text/csv;charset=utf-8," 
        + "Tabela Origem,Coluna Origem,Tabela Destino,Coluna Destino,Tipo,Constraint\n"
        + allRelationships.map(rel => 
            `${rel.from_table},${rel.from_column},${rel.to_table},${rel.to_column},${rel.relationship_type},${rel.constraint_name}`
        ).join("\n");
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "database_relationships.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification('Relacionamentos exportados com sucesso!', 'success');
}

function toggleView() {
    const tableView = document.getElementById('table-view');
    const graphView = document.getElementById('graph-view');
    const toggleText = document.getElementById('view-toggle-text');
    
    if (currentView === 'table') {
        tableView.classList.add('hidden');
        graphView.classList.remove('hidden');
        toggleText.textContent = 'Visualização em Tabela';
        currentView = 'graph';
    } else {
        tableView.classList.remove('hidden');
        graphView.classList.add('hidden');
        toggleText.textContent = 'Visualização Gráfica';
        currentView = 'table';
    }
}

function filterRelationships() {
    const tableFilter = document.getElementById('filter-table').value;
    const typeFilter = document.getElementById('filter-type').value;
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    
    let filteredRelationships = allRelationships.filter(rel => {
        const matchesTable = !tableFilter || rel.from_table === tableFilter || rel.to_table === tableFilter;
        const matchesType = !typeFilter || rel.relationship_type === typeFilter;
        const matchesSearch = !searchTerm || 
            rel.from_table.toLowerCase().includes(searchTerm) ||
            rel.to_table.toLowerCase().includes(searchTerm) ||
            rel.from_column.toLowerCase().includes(searchTerm) ||
            rel.to_column.toLowerCase().includes(searchTerm) ||
            rel.constraint_name.toLowerCase().includes(searchTerm);
        
        return matchesTable && matchesType && matchesSearch;
    });
    
    updateRelationshipsTable(filteredRelationships);
}

function searchRelationships() {
    filterRelationships();
}

function updateRelationshipsTable(relationships = allRelationships) {
    const tbody = document.getElementById('relationships-tbody');
    const totalCount = document.getElementById('total-count');
    
    totalCount.textContent = relationships.length;
    
    if (relationships.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center space-y-3">
                        <i class="fas fa-search text-4xl text-slate-300"></i>
                        <p class="text-slate-500 text-lg">Nenhum relacionamento encontrado</p>
                        <p class="text-slate-400 text-sm">Tente ajustar os filtros de busca</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = relationships.map(rel => {
        const badgeClass = {
            'foreign_key': 'bg-emerald-100 text-emerald-800',
            'inferred': 'bg-yellow-100 text-yellow-800',
            'fallback': 'bg-slate-100 text-slate-800'
        }[rel.relationship_type] || 'bg-gray-100 text-gray-800';
        
        return `
            <tr class="relationship-row hover:bg-slate-50 transition-colors duration-200" 
                data-from-table="${rel.from_table}" 
                data-to-table="${rel.to_table}" 
                data-type="${rel.relationship_type}">
                <td class="px-6 py-4 whitespace-nowrap">
                    <code class="text-sm font-mono bg-blue-50 text-blue-800 px-2 py-1 rounded">${rel.from_table}</code>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <code class="text-sm font-mono bg-green-50 text-green-800 px-2 py-1 rounded">${rel.from_column}</code>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <code class="text-sm font-mono bg-blue-50 text-blue-800 px-2 py-1 rounded">${rel.to_table}</code>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <code class="text-sm font-mono bg-green-50 text-green-800 px-2 py-1 rounded">${rel.to_column}</code>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}">
                        ${rel.relationship_type.charAt(0).toUpperCase() + rel.relationship_type.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                    <code class="text-xs bg-slate-50 px-2 py-1 rounded">${rel.constraint_name}</code>
                </td>
            </tr>
        `;
    }).join('');
}

function showLoading() {
    document.getElementById('loading-overlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loading-overlay').classList.add('hidden');
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white font-medium ${
        type === 'success' ? 'bg-emerald-500' : 
        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
    }`;
    notification.textContent = message;
    
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