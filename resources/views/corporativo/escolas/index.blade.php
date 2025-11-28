@extends('corporativo.layout')

@section('title', 'Gerenciamento de Escolas')
@section('page-title', 'Gerenciamento de Escolas')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gerenciamento de Escolas</h1>
                    <p class="mt-2 text-gray-600">Gerencie todas as escolas do sistema</p>
                </div>
                <button onclick="openCreateModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nova Escola
                </button>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total de Escolas</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_escolas'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Escolas Ativas</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['escolas_ativas'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Escolas Inativas</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['escolas_inativas'] }}</p>
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
                        <p class="text-sm font-medium text-gray-600">Em Dia</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['escolas_em_dia'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Inadimplentes</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['escolas_inadimplentes'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <input type="text" id="search" name="search" placeholder="Nome da escola..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status" name="status" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Todos</option>
                        <option value="ativa">Ativa</option>
                        <option value="inativa">Inativa</option>
                    </select>
                </div>
                <div>
                    <label for="pagamento" class="block text-sm font-medium text-gray-700 mb-2">Pagamento</label>
                    <select id="pagamento" name="pagamento" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Todos</option>
                        <option value="em_dia">Em Dia</option>
                        <option value="inadimplente">Inadimplente</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="applyFilters()" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                        Filtrar
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Escolas -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Lista de Escolas</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Escola</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plano</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensalidade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuários</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Funcionários</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagamento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criada em</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="escolas-table-body" class="bg-white divide-y divide-gray-200">
                        <!-- Conteúdo será carregado via JavaScript -->
                    </tbody>
                </table>
            </div>
            <div id="pagination-container" class="px-6 py-4 border-t border-gray-200">
                <!-- Paginação será carregada via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de Visualização -->
<div id="viewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detalhes da Escola</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="viewModalContent" class="space-y-4">
                <!-- Conteúdo será carregado dinamicamente -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Editar Escola</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="editForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="edit_nome" class="block text-sm font-medium text-gray-700">Nome da Escola</label>
                        <input type="text" id="edit_nome" name="nome" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label for="edit_codigo" class="block text-sm font-medium text-gray-700">Código</label>
                        <input type="text" id="edit_codigo" name="codigo" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label for="edit_valor_mensalidade" class="block text-sm font-medium text-gray-700">Valor Mensalidade</label>
                        <input type="number" id="edit_valor_mensalidade" name="valor_mensalidade" step="0.01"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label for="edit_plano" class="block text-sm font-medium text-gray-700">Plano</label>
                        <select id="edit_plano" name="plano"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="basico">Básico</option>
                            <option value="intermediario">Intermediário</option>
                            <option value="avancado">Avançado</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="edit_ativo" name="ativo" class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Escola Ativa</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="edit_em_dia" name="em_dia" class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Pagamento em Dia</span>
                    </label>
                </div>

                <!-- Seção de Módulos -->
                <div class="border-t pt-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Módulos Ativos</h4>
                    <div id="modulesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Módulos serão carregados dinamicamente -->
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-6 border-t">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Inativação -->
<div id="inactivateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Confirmar Inativação</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Tem certeza que deseja inativar a escola <span id="inactivateEscolaName" class="font-medium"></span>?
                    Esta ação pode ser revertida posteriormente.
                </p>
            </div>
            <div class="flex justify-center space-x-3 px-4 py-3">
                <button onclick="closeInactivateModal()" 
                        class="px-4 py-2 bg-white text-gray-500 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    Cancelar
                </button>
                <button onclick="confirmInactivate()" 
                        class="px-4 py-2 bg-red-600 text-white border border-transparent rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Inativar Escola
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript para carregar e filtrar escolas
document.addEventListener('DOMContentLoaded', function() {
    loadEscolas();
});

function loadEscolas(page = 1) {
    const search = document.getElementById('search').value;
    const status = document.getElementById('status').value;
    const pagamento = document.getElementById('pagamento').value;
    
    const params = new URLSearchParams({
        page: page,
        search: search,
        status: status,
        pagamento: pagamento
    });
    
    fetch(`{{ route('corporativo.escolas.api') }}?${params}`)
        .then(response => response.json())
        .then(data => {
            updateTable(data.escolas);
            updatePagination(data.pagination);
        })
        .catch(error => console.error('Erro:', error));
}

function updateTable(escolas) {
    const tbody = document.getElementById('escolas-table-body');
    tbody.innerHTML = '';
    
    escolas.forEach(escola => {
        const planoLabel = (escola.plano || '-')
            .replace(/^trial$/i, 'Trial')
            .replace(/^basico$/i, 'Básico')
            .replace(/^premium$/i, 'Premium')
            .replace(/^enterprise$/i, 'Enterprise')
            .replace(/^intermediario$/i, 'Intermediário')
            .replace(/^avancado$/i, 'Avançado');

        const mensalidade = (escola.valor_mensalidade != null)
            ? `R$ ${Number(escola.valor_mensalidade).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
            : '-';

        const row = `
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div>
                            <div class="text-sm font-medium text-gray-900">${escola.nome}</div>
                            <div class="text-xs text-gray-500">Código: ${escola.codigo || '-'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${planoLabel}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${mensalidade}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${escola.ativa ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${escola.ativa ? 'Ativa' : 'Inativa'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${escola.usuarios_count || 0}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${escola.funcionarios_count || 0}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${escola.pagamento_em_dia ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${escola.pagamento_em_dia ? 'Em Dia' : 'Inadimplente'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${new Date(escola.created_at).toLocaleDateString('pt-BR')}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onclick="viewEscola(${escola.id})" class="text-purple-600 hover:text-purple-900 mr-3">Visualizar</button>
                    <button onclick="editEscola(${escola.id})" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                    <button onclick="inactivateEscola(${escola.id})" class="text-red-600 hover:text-red-900">Inativar</button>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

function updatePagination(pagination) {
    // Implementação básica de paginação
    const paginationContainer = document.getElementById('pagination-container');
    if (!paginationContainer) return;
    
    let paginationHtml = '';
    
    if (pagination && pagination.total > pagination.per_page) {
        paginationHtml = '<div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">';
        paginationHtml += '<div class="flex flex-1 justify-between sm:hidden">';
        
        // Botão anterior (mobile)
        if (pagination.current_page > 1) {
            paginationHtml += `<button onclick="loadEscolas(${pagination.current_page - 1})" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Anterior</button>`;
        }
        
        // Botão próximo (mobile)
        if (pagination.current_page < pagination.last_page) {
            paginationHtml += `<button onclick="loadEscolas(${pagination.current_page + 1})" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Próximo</button>`;
        }
        
        paginationHtml += '</div>';
        paginationHtml += '<div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">';
        paginationHtml += `<div><p class="text-sm text-gray-700">Mostrando <span class="font-medium">${pagination.from || 0}</span> a <span class="font-medium">${pagination.to || 0}</span> de <span class="font-medium">${pagination.total}</span> resultados</p></div>`;
        paginationHtml += '<div><nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">';
        
        // Botão anterior (desktop)
        if (pagination.current_page > 1) {
            paginationHtml += `<button onclick="loadEscolas(${pagination.current_page - 1})" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">Anterior</button>`;
        }
        
        // Números das páginas
        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === pagination.current_page) {
                paginationHtml += `<button class="relative z-10 inline-flex items-center bg-purple-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600">${i}</button>`;
            } else if (i === 1 || i === pagination.last_page || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
                paginationHtml += `<button onclick="loadEscolas(${i})" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">${i}</button>`;
            } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
                paginationHtml += '<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>';
            }
        }
        
        // Botão próximo (desktop)
        if (pagination.current_page < pagination.last_page) {
            paginationHtml += `<button onclick="loadEscolas(${pagination.current_page + 1})" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">Próximo</button>`;
        }
        
        paginationHtml += '</nav></div></div></div>';
    }
    
    paginationContainer.innerHTML = paginationHtml;
}

function applyFilters() {
    loadEscolas(1);
}

function openCreateModal() {
    // Implementar modal de criação
    alert('Modal de criação de escola será implementado');
}

// Funções dos modais já foram redefinidas acima

function confirmInactivate() {
    if (!currentEscolaId) return;
    
    fetch(`/api/escolas/${currentEscolaId}/inactivate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeInactivateModal();
            loadEscolas();
            alert('Escola inativada com sucesso!');
        } else {
            alert('Erro ao inativar escola: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao inativar escola');
    });
}

// Variáveis globais para controle dos modais
let currentEscolaId = null;
let allModules = [];

// Funções do Modal de Visualização
function viewEscola(id) {
    currentEscolaId = id;
    fetch(`/api/escolas/${id}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const escola = data.data;
                displayEscolaDetails(escola);
                document.getElementById('viewModal').classList.remove('hidden');
            } else {
                console.error('Dados da escola não encontrados:', data);
                alert('Escola não encontrada');
            }
        })
        .catch(error => {
            console.error('Erro ao carregar detalhes da escola:', error);
            alert('Erro ao carregar detalhes da escola');
        });
}

function displayEscolaDetails(escola) {
    const content = document.getElementById('viewModalContent');
    content.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nome da Escola</label>
                <p class="mt-1 text-sm text-gray-900">${escola.nome}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Código</label>
                <p class="mt-1 text-sm text-gray-900">${escola.codigo}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <p class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${escola.ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${escola.ativo ? 'Ativa' : 'Inativa'}
                    </span>
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Pagamento</label>
                <p class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${escola.em_dia ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                        ${escola.em_dia ? 'Em dia' : 'Inadimplente'}
                    </span>
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Valor Mensalidade</label>
                <p class="mt-1 text-sm text-gray-900">R$ ${escola.valor_mensalidade || 'N/A'}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Plano</label>
                <p class="mt-1 text-sm text-gray-900">${escola.plano || 'N/A'}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Usuários</label>
                <p class="mt-1 text-sm text-gray-900">${escola.users_count || 0}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Funcionários</label>
                <p class="mt-1 text-sm text-gray-900">${escola.funcionarios_count || 0}</p>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Criada em</label>
                <p class="mt-1 text-sm text-gray-900">${new Date(escola.created_at).toLocaleDateString('pt-BR')}</p>
            </div>
        </div>
        <div class="mt-6 border-t pt-4">
            <h4 class="text-md font-medium text-gray-900 mb-3">Módulos Ativos</h4>
            <div id="viewModulesContainer">
                <p class="text-sm text-gray-500">Carregando módulos...</p>
            </div>
        </div>
    `;
    
    // Carregar módulos da escola
    loadEscolaModules(escola.id, 'viewModulesContainer', true);
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
    currentEscolaId = null;
}

// Funções do Modal de Edição
function editEscola(id) {
    currentEscolaId = id;

    // Carregar dados da escola, módulos disponíveis e módulos ativos em paralelo
    Promise.all([
        fetch(`/api/escolas/${id}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            }),
        fetch('/api/escolas/available-modules', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            }),
        fetch(`/api/escolas/${id}/modules`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Resposta não é JSON válido');
                }
                return response.json();
            }),
        fetch(`{{ route('corporativo.plans.api') }}?active=1`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
    ])
    .then(([escolaData, availableModulesData, activeModulesData, plansData]) => {

        if (escolaData.success && escolaData.data) {
            const escola = escolaData.data;
            const availableModules = availableModulesData.modules || [];
            const activeModules = activeModulesData.modules || [];
            const plans = (plansData && plansData.plans) ? plansData.plans : [];
            
            // Criar array de todos os módulos com status de ativo/inativo
            allModules = availableModules.map(module => {
                const isActive = activeModules.some(activeModule => activeModule.id === module.id);
                return {
                    ...module,
                    is_active: isActive
                };
            });
            
            populatePlansSelect(plans, escola.plano);
            populateEditForm(escola);
            loadEscolaModules(escola.id, 'modulesContainer', false);
            document.getElementById('editModal').classList.remove('hidden');
        } else {
            console.error('Dados da escola não encontrados:', escolaData);
            alert('Escola não encontrada');
        }
    })
    .catch(error => {
        console.error('Erro ao carregar dados para edição:', error);
        alert('Erro ao carregar dados da escola');
    });
}

function populateEditForm(escola) {
    document.getElementById('edit_nome').value = escola.nome;
    document.getElementById('edit_codigo').value = escola.codigo;
    document.getElementById('edit_valor_mensalidade').value = escola.valor_mensalidade || '';
    document.getElementById('edit_ativo').checked = escola.ativo;
    document.getElementById('edit_em_dia').checked = escola.em_dia;
}

function populatePlansSelect(plans, selectedSlug) {
    const select = document.getElementById('edit_plano');
    if (!select) return;
    select.innerHTML = '';
    if (!Array.isArray(plans)) return;

    plans.forEach(p => {
        const option = document.createElement('option');
        option.value = p.slug; // salvar slug no campo 'plano'
        const price = (p.price != null) ? ` - R$ ${Number(p.price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '';
        option.textContent = `${p.name}${price}`;
        select.appendChild(option);
    });
    // Selecionar o plano atual da escola
    if (selectedSlug) {
        select.value = selectedSlug;
    }
}

function loadEscolaModules(escolaId, containerId, readOnly = false) {
    fetch(`/api/escolas/${escolaId}/modules`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById(containerId);
            const modules = data.modules || [];
            
            if (readOnly) {
                // Modo visualização
                if (modules.length === 0) {
                    container.innerHTML = '<p class="text-sm text-gray-500">Nenhum módulo ativo</p>';
                } else {
                    container.innerHTML = modules.map(module => `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mr-2 mb-2">
                            ${module.name}
                        </span>
                    `).join('');
                }
            } else {
                // Modo edição
                const activeModuleIds = modules.map(m => m.id);
                container.innerHTML = allModules.map(module => `
                    <label class="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                        <input type="checkbox" name="modules[]" value="${module.id}" 
                               ${activeModuleIds.includes(module.id) ? 'checked' : ''}
                               class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50">
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">${module.name}</div>
                            <div class="text-xs text-gray-500">${module.description || ''}</div>
                        </div>
                    </label>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Erro ao carregar módulos:', error);
            document.getElementById(containerId).innerHTML = '<p class="text-sm text-red-500">Erro ao carregar módulos</p>';
        });
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    currentEscolaId = null;
    allModules = [];
}

// Funções do Modal de Inativação
function inactivateEscola(id) {
    currentEscolaId = id;
    
    // Buscar nome da escola
    fetch(`{{ route('corporativo.escolas.api') }}?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.escolas && Array.isArray(data.escolas) && data.escolas.length > 0) {
                const escola = data.escolas[0];
                document.getElementById('inactivateEscolaName').textContent = escola.nome;
                document.getElementById('inactivateModal').classList.remove('hidden');
            } else {
                console.error('Dados da escola não encontrados:', data);
                alert('Escola não encontrada');
            }
        })
        .catch(error => {
            console.error('Erro ao carregar dados da escola:', error);
            alert('Erro ao carregar dados da escola');
        });
}

function closeInactivateModal() {
    document.getElementById('inactivateModal').classList.add('hidden');
    currentEscolaId = null;
}

// Função para atualizar módulos
function updateModules(activeModules, inactiveModules) {
    const activeContainer = document.getElementById('activeModules');
    const inactiveContainer = document.getElementById('inactiveModules');
    
    activeContainer.innerHTML = '';
    inactiveContainer.innerHTML = '';
    
    activeModules.forEach(module => {
        const moduleDiv = document.createElement('div');
        moduleDiv.className = 'flex items-center justify-between p-2 bg-green-50 border border-green-200 rounded';
        moduleDiv.innerHTML = `
            <span class="text-green-800">${module.name}</span>
            <button type="button" onclick="moveModule(${module.id}, 'inactive')" 
                    class="text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        `;
        activeContainer.appendChild(moduleDiv);
    });
    
    inactiveModules.forEach(module => {
        const moduleDiv = document.createElement('div');
        moduleDiv.className = 'flex items-center justify-between p-2 bg-gray-50 border border-gray-200 rounded';
        moduleDiv.innerHTML = `
            <span class="text-gray-600">${module.name}</span>
            <button type="button" onclick="moveModule(${module.id}, 'active')" 
                    class="text-green-600 hover:text-green-800">
                <i class="fas fa-plus"></i>
            </button>
        `;
        inactiveContainer.appendChild(moduleDiv);
    });
}

// Função para mover módulo entre ativo e inativo
function moveModule(moduleId, action) {
    const module = allModules.find(m => m.id == moduleId);
    if (!module) return;
    
    if (action === 'active') {
        module.is_active = true;
    } else {
        module.is_active = false;
    }
    
    const activeModules = allModules.filter(m => m.is_active);
    const inactiveModules = allModules.filter(m => !m.is_active);
    
    updateModules(activeModules, inactiveModules);
}

// Submissão do formulário de edição
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentEscolaId) return;
    
    const formData = new FormData(this);
    
    // Coletar módulos ativos dos checkboxes marcados
    const checkedCheckboxes = document.querySelectorAll('input[name="modules[]"]:checked');
    const activeModuleIds = Array.from(checkedCheckboxes).map(cb => parseInt(cb.value));
    
    const data = {
        nome: formData.get('nome'),
        codigo: formData.get('codigo'),
        valor_mensalidade: formData.get('valor_mensalidade'),
        plano: formData.get('plano'),
        ativo: formData.has('ativo'),
        em_dia: formData.has('em_dia')
    };
    
    // Primeiro atualizar dados da escola
    fetch(`/api/escolas/${currentEscolaId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Depois atualizar módulos
            return fetch(`/api/escolas/${currentEscolaId}/modules`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ modules: activeModuleIds })
            });
        } else {
            throw new Error(data.message || 'Erro ao atualizar escola');
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeEditModal();
            loadEscolas(); // Recarregar lista
            alert('Escola e módulos atualizados com sucesso!');
        } else {
            alert('Erro ao atualizar módulos: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar escola:', error);
        alert('Erro ao atualizar escola: ' + error.message);
    });
});
</script>
@endsection