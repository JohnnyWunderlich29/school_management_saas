@extends('corporativo.layout')

@section('title', 'Permissões - Sistema Corporativo')
@section('page-title', 'Permissões do Sistema')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Lista de Permissões -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Lista de Permissões</h3>
                    <div class="flex space-x-3">
                        <!-- Filtro por Módulo -->
                        <select id="filter-module" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Todos os Módulos</option>
                            @foreach($modulos as $modulo)
                                <option value="{{ $modulo }}">{{ ucfirst(str_replace('_module', '', $modulo)) }}</option>
                            @endforeach
                        </select>
                        
                        <!-- Campo de Busca -->
                        <div class="relative">
                            <input type="text" id="search-permissions" placeholder="Buscar permissões..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="permissions-table">
                        @foreach($permissions as $permission)
                        <tr class="hover:bg-gray-50 permission-row" data-name="{{ strtolower($permission->nome) }}" data-description="{{ strtolower($permission->descricao ?? '') }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $permission->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $permission->nome }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ ucfirst(str_replace('_module', '', $permission->modulo ?? 'sistema')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ $permission->descricao ?? 'Sem descrição' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $permission->cargos_count ?? 0 }} cargos
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="viewPermissionRoles({{ $permission->id }}, '{{ $permission->nome }}')" class="text-blue-600 hover:text-blue-900 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M9 20H4v-2a3 3 0 015.196-2.121m0 0a5.002 5.002 0 019.608 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-sm text-gray-600">
                    @if ($permissions->total() > 0)
                        Mostrando
                        <span class="font-medium">{{ $permissions->firstItem() }}</span>
                        –
                        <span class="font-medium">{{ $permissions->lastItem() }}</span>
                        de
                        <span class="font-medium">{{ $permissions->total() }}</span>
                        resultados
                    @else
                        Nenhum resultado encontrado
                    @endif
                </div>
                <div>
                    {{ $permissions->onEachSide(1)->links('vendor.pagination.corporativo') }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estatísticas -->
    <div class="space-y-6">
        <!-- Resumo de Permissões -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Resumo</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Total de Permissões</span>
                    <span class="text-sm font-medium text-gray-900">{{ $stats['total'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Módulos do Sistema</span>
                    <span class="text-sm font-medium text-gray-900">{{ $stats['por_modulo']->count() }}</span>
                </div>
            </div>
        </div>
        
        <!-- Permissões por Módulo -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Permissões por Módulo</h3>
            <div class="space-y-3">
                @foreach($stats['por_modulo'] as $modulo)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ ucfirst(str_replace('_module', '', $modulo->modulo)) }}</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                        {{ $modulo->total }} permissões
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Permissões Mais Usadas -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Mais Utilizadas</h3>
            <div class="space-y-3">
                @foreach($stats['mais_usadas'] as $permission)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">{{ $permission->nome }}</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        {{ $permission->cargos_count }} cargos
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Ações Rápidas -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ações Rápidas</h3>
            <div class="space-y-3">
                <button onclick="exportPermissions()" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Exportar Lista
                </button>
                
                <button onclick="refreshPermissions()" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Atualizar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualizar cargos com permissão -->
<div id="permissionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-96 overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Cargos com Permissão</h3>
                    <button onclick="closePermissionModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="px-6 py-4" id="permissionModalContent">
                <!-- Conteúdo será carregado via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
// Filtro de busca e módulo
function filterPermissions() {
    const searchTerm = document.getElementById('search-permissions').value.toLowerCase();
    const moduleFilter = document.getElementById('filter-module').value;
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const nameCell = row.querySelector('td:nth-child(2)');
        const moduleCell = row.querySelector('td:nth-child(3) span');
        const descriptionCell = row.querySelector('td:nth-child(4)');
        
        if (!nameCell || !moduleCell || !descriptionCell) return;
        
        const name = nameCell.textContent.toLowerCase();
        const module = moduleCell.textContent.toLowerCase();
        const description = descriptionCell.textContent.toLowerCase();
        
        const matchesSearch = name.includes(searchTerm) || description.includes(searchTerm);
        const matchesModule = !moduleFilter || module.includes(moduleFilter.toLowerCase().replace('_module', ''));
        
        if (matchesSearch && matchesModule) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Event listeners para filtros
document.getElementById('search-permissions').addEventListener('input', filterPermissions);
document.getElementById('filter-module').addEventListener('change', filterPermissions);

// Visualizar cargos com permissão
function viewPermissionRoles(permissionId, permissionName) {
    document.getElementById('modalTitle').textContent = `Cargos com permissão: ${permissionName}`;
    document.getElementById('permissionModalContent').innerHTML = `
        <div class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-2 text-gray-600">Carregando cargos...</p>
        </div>
    `;
    document.getElementById('permissionModal').classList.remove('hidden');
    
    // Fazer requisição para buscar cargos com esta permissão
    fetch(`/api/permissions/${permissionId}/roles`)
        .then(response => response.json())
        .then(data => {
            let content = `
                <div class="space-y-4">
                    <p><strong>Permissão ID:</strong> ${permissionId}</p>
                    <p><strong>Nome:</strong> ${permissionName}</p>
                    <div class="mt-4">
                        <h4 class="font-medium text-gray-900 mb-2">Cargos com esta permissão:</h4>
            `;
            
            if (data.roles && data.roles.length > 0) {
                content += '<div class="space-y-2">';
                data.roles.forEach(role => {
                    content += `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium">${role.nome}</span>
                            <span class="text-sm text-gray-600">${role.users_count || 0} usuários</span>
                        </div>
                    `;
                });
                content += '</div>';
            } else {
                content += '<div class="bg-gray-50 rounded-lg p-4"><p class="text-sm text-gray-600">Nenhum cargo possui esta permissão.</p></div>';
            }
            
            content += '</div></div>';
            document.getElementById('permissionModalContent').innerHTML = content;
        })
        .catch(error => {
            document.getElementById('permissionModalContent').innerHTML = `
                <div class="space-y-4">
                    <p><strong>Permissão ID:</strong> ${permissionId}</p>
                    <p><strong>Nome:</strong> ${permissionName}</p>
                    <div class="mt-4">
                        <h4 class="font-medium text-gray-900 mb-2">Cargos com esta permissão:</h4>
                        <div class="bg-red-50 rounded-lg p-4">
                            <p class="text-sm text-red-600">Erro ao carregar cargos. Tente novamente.</p>
                        </div>
                    </div>
                </div>
            `;
        });
}

// Exportar permissões
function exportPermissions() {
    window.location.href = '/api/permissions/export';
}

// Atualizar permissões
function refreshPermissions() {
    location.reload();
}

// Fechar modal
function closePermissionModal() {
    document.getElementById('permissionModal').classList.add('hidden');
}

// Fechar modal clicando fora
document.getElementById('permissionModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePermissionModal();
    }
});
</script>
@endsection