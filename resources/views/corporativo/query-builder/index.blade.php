@extends('corporativo.layout')

@section('title', 'Query Builder - Painel Corporativo')
@section('page-title', 'Construtor de Consultas')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Painel de Consulta -->
    <div class="lg:col-span-3">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h3 class="text-lg font-medium text-gray-900">Construtor de Consultas SQL</h3>
                        @if(Auth::user()->escola)
                        <div class="flex items-center space-x-2 px-3 py-1 bg-blue-50 border border-blue-200 rounded-md">
                            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                            <span class="text-sm font-medium text-blue-700">{{ Auth::user()->escola->nome ?? 'Sistema Corporativo' }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="clearQuery()" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                            Limpar
                        </button>
                        <button onclick="saveQuery()" class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            Salvar
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Seletor de Tabela -->
                <div class="mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tabela Principal</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="table-search-input" 
                                    placeholder="Digite para buscar uma tabela..." 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    autocomplete="off"
                                >
                                <input type="hidden" id="table-select" name="table-select">
                                
                                <!-- Dropdown com as opções filtradas -->
                                <div id="table-dropdown" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg mt-1 max-h-60 overflow-y-auto shadow-lg hidden">
                                    <div class="p-2 text-sm text-gray-500 border-b">Selecione uma tabela:</div>
                                    <div id="table-options">
                                        @foreach($tableNames as $table)
                                            <div class="table-option px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm" data-value="{{ $table }}">
                                                {{ $table }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Joins</label>
                            <button type="button" id="add-join-btn" class="px-3 py-2 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors" disabled>
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Adicionar JOIN
                            </button>
                        </div>
                    </div>
                    
                    <!-- Container para JOINs -->
                    <div id="joins-container" class="mt-4 space-y-3 hidden">
                        <h5 class="text-sm font-medium text-gray-700">Relacionamentos (JOINs)</h5>
                        <div id="joins-list" class="space-y-2">
                            <!-- JOINs serão adicionados aqui dinamicamente -->
                        </div>
                    </div>
                </div>
                
                <!-- Construtor Visual -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Construtor Visual</h4>
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <!-- Colunas -->
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Colunas</label>
                            <select id="columns-select" multiple class="w-full h-24 border border-gray-300 rounded px-2 py-1 text-sm">
                                <option value="*">* (Todas)</option>
                            </select>
                        </div>
                        
                        <!-- Condições WHERE -->
                        <div class="col-span-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Condições WHERE</label>
                            <div id="where-conditions" class="space-y-2">
                                <div class="flex space-x-1">
                                    <input type="text" placeholder="Coluna" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm">
                                    <select class="border border-gray-300 rounded px-2 py-1 text-sm">
                                        <option value="=">=</option>
                                        <option value="!=">!=</option>
                                        <option value=">">></option>
                                        <option value="<"><</option>
                                        <option value="LIKE">LIKE</option>
                                    </select>
                                    <input type="text" placeholder="Valor" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm">
                                </div>
                            </div>
                            <button onclick="addWhereCondition()" class="mt-2 text-xs text-blue-600 hover:text-blue-800">+ Adicionar condição</button>
                        </div>
                        
                        <!-- Ordenação -->
                        <div class="col-span-1 col-start-6">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Ordenação</label>
                            <div class="space-y-2">
                                <select id="order-column" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                    <option value="">Selecione coluna...</option>
                                </select>
                                <select id="order-direction" class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                    <option value="ASC">Crescente (ASC)</option>
                                    <option value="DESC">Decrescente (DESC)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Limite -->
                    <div class="mt-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Limite de Resultados</label>
                        <input type="number" id="limit-input" placeholder="Ex: 100" class="w-32 border border-gray-300 rounded px-2 py-1 text-sm">
                    </div>
                </div>
                
                <!-- Editor SQL -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">Consulta SQL</label>
                        <div class="flex items-center space-x-2">
                            <div class="flex space-x-1">
                                <button onclick="setOperation('SELECT')" class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">Consultar</button>
                                <button onclick="setOperation('DELETE')" class="px-3 py-1 text-xs border border-red-300 text-red-700 rounded hover:bg-red-50 transition-colors">Deletar</button>
                                <button onclick="setOperation('ALTER')" class="px-3 py-1 text-xs border border-amber-300 text-amber-700 rounded hover:bg-amber-50 transition-colors">Alterar</button>
                            </div>
                            <button onclick="previewQuery()" class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">Preview</button>
                        </div>
                    </div>
                    <textarea id="sql-editor" rows="8" class="w-full border border-gray-300 rounded-lg px-3 py-2 font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="SELECT * FROM tabela WHERE condicao = 'valor'"></textarea>
                </div>
                
                <!-- Botões de Ação -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex space-x-3">
                        <button onclick="executeQuery()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m6-10V7a3 3 0 00-3-3H6a3 3 0 00-3 3v1"></path>
                            </svg>
                            Executar Consulta
                        </button>
                        <button onclick="validateQuery()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Validar
                        </button>
                    </div>
                    <div id="query-status" class="text-sm text-gray-500"></div>
                </div>
                
                <!-- Resultados -->
                <div id="results-container" class="hidden">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-lg font-medium text-gray-900">Resultados da Consulta</h4>
                        <button onclick="exportResults()" class="px-3 py-1 text-sm bg-green-50 text-green-600 border border-green-200 rounded hover:bg-green-100 transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar CSV
                        </button>
                    </div>
                    <div id="results-table" class="overflow-x-auto bg-white border border-gray-200 rounded-lg">
                        <!-- Tabela de resultados será inserida aqui -->
                    </div>
                    <div id="results-info" class="mt-2 text-sm text-gray-600"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Painel Lateral -->
    <div class="lg:col-span-1">
        <!-- Informações da Tabela -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-4 py-3 border-b border-gray-200">
                <h4 class="text-sm font-medium text-gray-900">Informações da Tabela</h4>
            </div>
            <div id="table-info" class="p-4 text-sm text-gray-600">
                Selecione uma tabela para ver suas informações
            </div>
        </div>
        
        <!-- Consultas Salvas -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-4 py-3 border-b border-gray-200">
                <h4 class="text-sm font-medium text-gray-900">Consultas Salvas</h4>
            </div>
            <div class="p-4">
                @if($favorites && $favorites->count() > 0)
                    <div class="space-y-2">
                        @foreach($favorites as $favorite)
                            <div class="p-2 border border-gray-200 rounded cursor-pointer hover:bg-gray-50" onclick="loadSavedQuery('{{ $favorite->query }}', '{{ $favorite->name }}')">
                                <div class="font-medium text-sm">{{ $favorite->name }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ Str::limit($favorite->query, 50) }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Nenhuma consulta salva</p>
                @endif
            </div>
        </div>
        
        <!-- Exemplos -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b border-gray-200">
                <h4 class="text-sm font-medium text-gray-900">Exemplos de Consultas</h4>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    <div class="cursor-pointer p-2 border border-gray-200 rounded hover:bg-gray-50" onclick="loadExample('users')">
                        <div class="text-sm font-medium">Listar Usuários</div>
                        <div class="text-xs text-gray-500">SELECT * FROM users LIMIT 10</div>
                    </div>
                    <div class="cursor-pointer p-2 border border-gray-200 rounded hover:bg-gray-50" onclick="loadExample('escolas')">
                        <div class="text-sm font-medium">Escolas Ativas</div>
                        <div class="text-xs text-gray-500">SELECT * FROM escolas WHERE ativo = 1</div>
                    </div>
                    <div class="cursor-pointer p-2 border border-gray-200 rounded hover:bg-gray-50" onclick="loadExample('join')">
                        <div class="text-sm font-medium">Usuários com Escolas</div>
                        <div class="text-xs text-gray-500">SELECT u.*, e.nome FROM users u LEFT JOIN escolas e ON u.escola_id = e.id LIMIT 10</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Variáveis globais
let currentTable = '';
let tableColumns = [];
let joinCounter = 0;
let availableTables = @json($tableNames);
const IS_SUPER_ADMIN = {{ (auth()->check() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin()) ? 'true' : 'false' }};

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    initializeQueryBuilder();
});

function initializeQueryBuilder() {
    const tableSelect = document.getElementById('table-select');
    const addJoinBtn = document.getElementById('add-join-btn');
    
    // Inicializar o filtro de busca de tabelas
    initializeTableSearch();
    
    // Função para quando uma tabela é selecionada (mantém funcionalidade existente)
    function onTableSelected(tableName) {
        currentTable = tableName;
        tableSelect.value = tableName; // Atualiza o campo hidden
        
        if (currentTable) {
            loadTableColumns(currentTable);
            loadTableInfo(currentTable);
            addJoinBtn.disabled = false;
        } else {
            addJoinBtn.disabled = true;
            document.getElementById('table-info').innerHTML = 'Selecione uma tabela para ver suas informações';
        }
        updateAllColumns();
    }
    
    // Expor a função globalmente para uso no filtro
    window.onTableSelected = onTableSelected;
    
    addJoinBtn.addEventListener('click', addJoinRow);
}

function initializeTableSearch() {
    const searchInput = document.getElementById('table-search-input');
    const dropdown = document.getElementById('table-dropdown');
    const tableOptions = document.getElementById('table-options');
    const hiddenSelect = document.getElementById('table-select');
    
    // Mostrar dropdown quando o input recebe foco
    searchInput.addEventListener('focus', function() {
        dropdown.classList.remove('hidden');
        filterTables(''); // Mostrar todas as tabelas inicialmente
    });
    
    // Filtrar tabelas conforme o usuário digita
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterTables(searchTerm);
    });
    
    // Esconder dropdown quando clicar fora
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative')) {
            dropdown.classList.add('hidden');
        }
    });
    
    // Adicionar event listeners para as opções de tabela
    tableOptions.addEventListener('click', function(event) {
        if (event.target.classList.contains('table-option')) {
            const selectedTable = event.target.getAttribute('data-value');
            searchInput.value = selectedTable;
            hiddenSelect.value = selectedTable;
            dropdown.classList.add('hidden');
            
            // Chamar a função de seleção de tabela
            if (window.onTableSelected) {
                window.onTableSelected(selectedTable);
            }
        }
    });
    
    // Navegação por teclado
    searchInput.addEventListener('keydown', function(event) {
        const visibleOptions = tableOptions.querySelectorAll('.table-option:not(.hidden)');
        let currentIndex = -1;
        
        // Encontrar opção atualmente selecionada
        visibleOptions.forEach((option, index) => {
            if (option.classList.contains('bg-blue-100')) {
                currentIndex = index;
            }
        });
        
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            currentIndex = Math.min(currentIndex + 1, visibleOptions.length - 1);
            highlightOption(visibleOptions, currentIndex);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            currentIndex = Math.max(currentIndex - 1, 0);
            highlightOption(visibleOptions, currentIndex);
        } else if (event.key === 'Enter') {
            event.preventDefault();
            if (currentIndex >= 0 && visibleOptions[currentIndex]) {
                visibleOptions[currentIndex].click();
            }
        } else if (event.key === 'Escape') {
            dropdown.classList.add('hidden');
        }
    });
}

function filterTables(searchTerm) {
    const tableOptions = document.getElementById('table-options');
    const options = tableOptions.querySelectorAll('.table-option');
    let visibleCount = 0;
    
    options.forEach(option => {
        const tableName = option.getAttribute('data-value').toLowerCase();
        if (tableName.includes(searchTerm)) {
            option.classList.remove('hidden');
            visibleCount++;
        } else {
            option.classList.add('hidden');
        }
        // Remover highlight ao filtrar
        option.classList.remove('bg-blue-100');
    });
    
    // Mostrar mensagem se nenhuma tabela for encontrada
    let noResultsMsg = tableOptions.querySelector('.no-results');
    if (visibleCount === 0 && searchTerm !== '') {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('div');
            noResultsMsg.className = 'no-results px-3 py-2 text-sm text-gray-500 italic';
            noResultsMsg.textContent = 'Nenhuma tabela encontrada';
            tableOptions.appendChild(noResultsMsg);
        }
        noResultsMsg.classList.remove('hidden');
    } else if (noResultsMsg) {
        noResultsMsg.classList.add('hidden');
    }
}

function highlightOption(options, index) {
    options.forEach((option, i) => {
        if (i === index) {
            option.classList.add('bg-blue-100');
            option.scrollIntoView({ block: 'nearest' });
        } else {
            option.classList.remove('bg-blue-100');
        }
    });
}

function addJoinRow() {
    joinCounter++;
    const joinsContainer = document.getElementById('joins-container');
    const joinsList = document.getElementById('joins-list');
    
    joinsContainer.classList.remove('hidden');
    
    const joinRow = document.createElement('div');
    joinRow.className = 'flex items-center space-x-2 p-3 bg-white border border-gray-200 rounded-lg';
    joinRow.id = `join-row-${joinCounter}`;
    
    joinRow.innerHTML = `
        <select class="join-type border border-gray-300 rounded px-2 py-1 text-sm">
            <option value="INNER">INNER JOIN</option>
            <option value="LEFT">LEFT JOIN</option>
            <option value="RIGHT">RIGHT JOIN</option>
            <option value="FULL">FULL JOIN</option>
        </select>
        <select class="join-table border border-gray-300 rounded px-2 py-1 text-sm flex-1" onchange="loadJoinColumns(this, ${joinCounter})">
            <option value="">Selecione tabela...</option>
            ${availableTables.map(table => `<option value="${table}">${table}</option>`).join('')}
        </select>
        <span class="text-sm text-gray-500">ON</span>
        <select class="join-left-column border border-gray-300 rounded px-2 py-1 text-sm flex-1">
            <option value="">Coluna esquerda...</option>
        </select>
        <span class="text-sm text-gray-500">=</span>
        <select class="join-right-column border border-gray-300 rounded px-2 py-1 text-sm flex-1">
            <option value="">Coluna direita...</option>
        </select>
        <button onclick="removeJoinRow(${joinCounter})" class="text-red-600 hover:text-red-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    joinsList.appendChild(joinRow);
    updateAllColumns();
}

function removeJoinRow(joinId) {
    const joinRow = document.getElementById(`join-row-${joinId}`);
    if (joinRow) {
        joinRow.remove();
        updateAllColumns();
        
        // Esconder container se não há mais joins
        const joinsList = document.getElementById('joins-list');
        if (joinsList.children.length === 0) {
            document.getElementById('joins-container').classList.add('hidden');
        }
    }
}

function loadJoinColumns(selectElement, joinId) {
    const tableName = selectElement.value;
    if (!tableName) return;
    
    fetch(`{{ url('corporativo/table-columns') }}/${tableName}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const joinRow = document.getElementById(`join-row-${joinId}`);
                const rightColumnSelect = joinRow.querySelector('.join-right-column');
                
                rightColumnSelect.innerHTML = '<option value="">Coluna direita...</option>';
                data.data.forEach(column => {
                    rightColumnSelect.innerHTML += `<option value="${tableName}.${column.name}">${tableName}.${column.name}</option>`;
                });
                
                updateAllColumns();
            }
        })
        .catch(error => {
            console.error('Erro ao carregar colunas:', error);
        });
}

function updateMainTableColumns() {
    if (!currentTable) return;
    
    const leftColumnSelects = document.querySelectorAll('.join-left-column');
    leftColumnSelects.forEach(select => {
        select.innerHTML = '<option value="">Coluna esquerda...</option>';
        tableColumns.forEach(column => {
            select.innerHTML += `<option value="${currentTable}.${column.name}">${currentTable}.${column.name}</option>`;
        });
    });
}

function updateAllColumns() {
    updateMainTableColumns();
    updateColumnsSelect();
    updateOrderByOptions();
    updateWhereOptions();
}

function loadTableColumns(tableName) {
    fetch(`{{ url('corporativo/table-columns') }}/${tableName}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tableColumns = data.data;
                updateAllColumns();
            }
        })
        .catch(error => {
            console.error('Erro ao carregar colunas:', error);
        });
}

function loadTableInfo(tableName) {
    fetch(`{{ url('corporativo/table-columns') }}/${tableName}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let info = `<div class="space-y-2">`;
                info += `<div class="font-medium text-gray-900">${tableName}</div>`;
                info += `<div class="text-xs text-gray-500">${data.data.length} colunas</div>`;
                info += `<div class="space-y-1">`;
                
                data.data.forEach(column => {
                    const badges = [];
                    if (column.primary) badges.push('<span class="px-1 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded">PK</span>');
                    if (!column.nullable) badges.push('<span class="px-1 py-0.5 text-xs bg-red-100 text-red-800 rounded">NOT NULL</span>');
                    
                    info += `<div class="flex items-center justify-between text-xs">
                        <span class="font-mono">${column.name}</span>
                        <div class="flex space-x-1">${badges.join('')}</div>
                    </div>`;
                });
                
                info += `</div></div>`;
                document.getElementById('table-info').innerHTML = info;
            }
        })
        .catch(error => {
            console.error('Erro ao carregar informações da tabela:', error);
        });
}

function updateColumnsSelect() {
    const columnsSelect = document.getElementById('columns-select');
    columnsSelect.innerHTML = '<option value="*">* (Todas)</option>';
    
    if (currentTable && tableColumns.length > 0) {
        tableColumns.forEach(column => {
            columnsSelect.innerHTML += `<option value="${currentTable}.${column.name}">${currentTable}.${column.name}</option>`;
        });
    }
    
    // Adicionar colunas de joins
    const joinRows = document.querySelectorAll('[id^="join-row-"]');
    joinRows.forEach(row => {
        const tableSelect = row.querySelector('.join-table');
        if (tableSelect.value) {
            const rightColumnSelect = row.querySelector('.join-right-column');
            Array.from(rightColumnSelect.options).forEach(option => {
                if (option.value && option.value !== '') {
                    columnsSelect.innerHTML += `<option value="${option.value}">${option.value}</option>`;
                }
            });
        }
    });
}

function updateOrderByOptions() {
    const orderColumnSelect = document.getElementById('order-column');
    orderColumnSelect.innerHTML = '<option value="">Selecione coluna...</option>';
    
    if (currentTable && tableColumns.length > 0) {
        tableColumns.forEach(column => {
            orderColumnSelect.innerHTML += `<option value="${currentTable}.${column.name}">${currentTable}.${column.name}</option>`;
        });
    }
}

function updateWhereOptions() {
    // Atualizar opções de WHERE se necessário
    // Por enquanto, deixamos como input livre
}

function addWhereCondition() {
    const whereContainer = document.getElementById('where-conditions');
    const newCondition = document.createElement('div');
    newCondition.className = 'flex space-x-1';
    newCondition.innerHTML = `
        <input type="text" placeholder="Coluna" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm">
        <select class="border border-gray-300 rounded px-2 py-1 text-sm">
            <option value="=">=</option>
            <option value="!=">!=</option>
            <option value=">">></option>
            <option value="<"><</option>
            <option value=">=">>=</option>
            <option value="<="><=</option>
            <option value="LIKE">LIKE</option>
            <option value="IN">IN</option>
            <option value="NOT IN">NOT IN</option>
            <option value="IS NULL">IS NULL</option>
            <option value="IS NOT NULL">IS NOT NULL</option>
        </select>
        <input type="text" placeholder="Valor" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm">
        <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    whereContainer.appendChild(newCondition);
}

function buildQuery() {
    if (!currentTable) {
        alert('Selecione uma tabela principal primeiro.');
        return '';
    }
    
    let query = 'SELECT ';
    
    // Colunas
    const selectedColumns = Array.from(document.getElementById('columns-select').selectedOptions);
    if (selectedColumns.length === 0 || selectedColumns.some(opt => opt.value === '*')) {
        query += '*';
    } else {
        query += selectedColumns.map(opt => opt.value).join(', ');
    }
    
    // FROM
    query += ` FROM ${currentTable}`;
    
    // JOINs
    const joinRows = document.querySelectorAll('[id^="join-row-"]');
    joinRows.forEach(row => {
        const joinType = row.querySelector('.join-type').value;
        const joinTable = row.querySelector('.join-table').value;
        const leftColumn = row.querySelector('.join-left-column').value;
        const rightColumn = row.querySelector('.join-right-column').value;
        
        if (joinTable && leftColumn && rightColumn) {
            query += ` ${joinType} JOIN ${joinTable} ON ${leftColumn} = ${rightColumn}`;
        }
    });
    
    // WHERE
    const whereConditions = [];
    const whereRows = document.querySelectorAll('#where-conditions > div');
    whereRows.forEach(row => {
        const inputs = row.querySelectorAll('input');
        const operator = row.querySelector('select').value;
        
        if (inputs[0].value && operator) {
            let condition = `${inputs[0].value} ${operator}`;
            if (operator !== 'IS NULL' && operator !== 'IS NOT NULL') {
                if (inputs[1].value) {
                    if (operator === 'LIKE') {
                        condition += ` '%${inputs[1].value}%'`;
                    } else if (operator === 'IN' || operator === 'NOT IN') {
                        condition += ` (${inputs[1].value})`;
                    } else {
                        condition += ` '${inputs[1].value}'`;
                    }
                }
            }
            whereConditions.push(condition);
        }
    });
    
    if (whereConditions.length > 0) {
        query += ` WHERE ${whereConditions.join(' AND ')}`;
    }
    
    // ORDER BY
    const orderColumn = document.getElementById('order-column').value;
    const orderDirection = document.getElementById('order-direction').value;
    if (orderColumn) {
        query += ` ORDER BY ${orderColumn} ${orderDirection}`;
    }
    
    // LIMIT
    const limit = document.getElementById('limit-input').value;
    if (limit) {
        query += ` LIMIT ${limit}`;
    }
    
    return query;
}

function setOperation(op) {
    if (op === 'ALTER' && !IS_SUPER_ADMIN) {
        alert('Ação restrita: apenas Super Administradores podem usar ALTER.');
        return;
    }
    if (op === 'DELETE' && !IS_SUPER_ADMIN) {
        alert('Ação restrita: apenas Super Administradores podem usar DELETE.');
        return;
    }
    currentOperation = op;
    const statusEl = document.getElementById('query-status');
    if (statusEl) {
        statusEl.innerHTML = `<span class="text-gray-600">Modo: ${op}</span>`;
    }
}

function buildDeleteQuery() {
    const table = document.getElementById('table-select').value;
    if (!table) {
        alert('Selecione uma tabela para montar o DELETE.');
        return '';
    }
    let whereClauses = [];
    document.querySelectorAll('.where-row').forEach(row => {
        const col = row.querySelector('.where-column').value;
        const op = row.querySelector('.where-operator').value;
        const val = row.querySelector('.where-value').value;
        if (col && op) {
            const v = val ? `'${val}'` : "''";
            whereClauses.push(`${col} ${op} ${v}`);
        }
    });
    let sql = `DELETE FROM ${table}`;
    if (whereClauses.length > 0) {
        sql += ` WHERE ${whereClauses.join(' AND ')}`;
    } else {
        sql += ' WHERE /* ajuste as condições (WHERE) */';
        alert('Atenção: DELETE sem WHERE pode ser perigoso. Edite as condições.');
    }
    return sql + ';';
}

function buildAlterTemplate() {
    const table = document.getElementById('table-select').value;
    if (!table) {
        alert('Selecione uma tabela para montar o ALTER.');
        return '';
    }
    return `ALTER TABLE ${table} \n    ADD COLUMN nova_coluna VARCHAR(255);`;
}

function previewQuery() {
    let sql = '';
    if (currentOperation === 'SELECT') {
        sql = buildQuery();
    } else if (currentOperation === 'DELETE') {
        sql = buildDeleteQuery();
    } else if (currentOperation === 'ALTER') {
        sql = buildAlterTemplate();
    }
    if (!sql) return;
    document.getElementById('sql-editor').value = sql;
}

function executeQuery() {
    const query = document.getElementById('sql-editor').value;
    const upperQuery = query.toUpperCase().trim();
    if (!validateQuerySafety(query)) return;
    if (IS_SUPER_ADMIN && (upperQuery.startsWith('DELETE') || upperQuery.startsWith('ALTER'))) {
        const confirmed = confirm('Tem certeza que deseja executar este comando?');
        if (!confirmed) {
            const statusEl = document.getElementById('query-status');
            if (statusEl) statusEl.innerHTML = '<span class="text-yellow-600">Execução cancelada</span>';
            return;
        }
    }
    document.getElementById('query-status').innerHTML = '<span class="text-blue-600">Executando...</span>';
    fetch("{{ route('corporativo.query.execute') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ query })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const rows = Array.isArray(data.result) ? data.result : [];
            displayResults(rows);
            let status = `Tempo: ${data.executionTime} ms`;
            if (typeof data.rowsAffected === 'number') {
                status += ` | Registros afetados: ${data.rowsAffected}`;
            }
            const uq = query.toUpperCase();
            if (uq.startsWith('ALTER') && typeof data.rowsAffected !== 'number') {
                status += ' | comando DDL executado';
            }
            document.getElementById('query-status').innerHTML = status;
        } else {
            document.getElementById('query-status').innerHTML = `<span class="text-red-600">Erro: ${data.error}</span>`;
        }
    })
    .catch(err => {
        document.getElementById('query-status').innerHTML = `<span class="text-red-600">Erro: ${err.message}</span>`;
    });
}

function validateQuery() {
    const query = document.getElementById('sql-editor').value.trim();
    
    if (!query) {
        query = buildQuery();
        if (!query) return;
        document.getElementById('sql-editor').value = query;
    }
    
    if (!validateQuerySafety(query)) {
        return;
    }
    
    document.getElementById('query-status').innerHTML = '<span class="text-blue-600">Executando consulta...</span>';
    
    fetch('{{ route("corporativo.query.execute") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            query: query,
            description: 'Consulta via Query Builder'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayResults(data.result, data.executionTime, data.rowsReturned);
            let status = `Consulta executada com sucesso (${data.executionTime} ms)`;
            if (typeof data.rowsAffected === 'number') {
                status += ` | Registros afetados: ${data.rowsAffected}`;
            }
            document.getElementById('query-status').innerHTML = `<span class="text-green-600">${status}</span>`;
        } else {
            document.getElementById('query-status').innerHTML = `<span class="text-red-600">Erro: ${data.error}</span>`;
            document.getElementById('results-container').classList.add('hidden');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        document.getElementById('query-status').innerHTML = '<span class="text-red-600">Erro ao executar consulta</span>';
    });
}

function validateQuerySafety(query) {
    const dangerousCommands = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE', 'TRUNCATE'];
    const upperQuery = query.toUpperCase().trim();
    
    // Bloquear múltiplas instruções encadeadas
    if ((upperQuery.match(/;/g) || []).length > 1) {
        alert('Múltiplas instruções na mesma consulta não são permitidas.');
        document.getElementById('query-status').innerHTML = `<span class="text-red-600">Consulta com múltiplas instruções não permitida</span>`;
        return false;
    }

    // Comandos perigosos nunca permitidos
    const blockedAlways = ['DROP', 'UPDATE', 'INSERT', 'CREATE', 'TRUNCATE'];
    for (let cmd of blockedAlways) {
        if (upperQuery.includes(cmd)) {
            alert(`Comando ${cmd} não é permitido por motivos de segurança.`);
            document.getElementById('query-status').innerHTML = `<span class="text-red-600">Comando ${cmd} não permitido</span>`;
            return false;
        }
    }

    // Regras por perfil
    if (IS_SUPER_ADMIN) {
        // Super Admin pode executar SELECT, DELETE e ALTER
        const allowedStarts = ['SELECT', 'DELETE', 'ALTER'];
        const startsAllowed = allowedStarts.some(prefix => upperQuery.startsWith(prefix));
        if (!startsAllowed) {
            alert('Apenas SELECT, DELETE e ALTER são permitidos para Super Administradores.');
            document.getElementById('query-status').innerHTML = `<span class="text-red-600">Comando não permitido</span>`;
            return false;
        }
        return true;
    } else {
        // Demais usuários: apenas SELECT
        if (!upperQuery.startsWith('SELECT')) {
            alert('Apenas consultas SELECT são permitidas.');
            document.getElementById('query-status').innerHTML = `<span class="text-red-600">Apenas SELECT é permitido</span>`;
            return false;
        }
        return true;
    }
}

function validateJoinConfiguration() {
    const joinRows = document.querySelectorAll('[id^="join-row-"]');
    let isValid = true;
    
    joinRows.forEach(row => {
        const joinTable = row.querySelector('.join-table').value;
        const leftColumn = row.querySelector('.join-left-column').value;
        const rightColumn = row.querySelector('.join-right-column').value;
        
        if (joinTable && (!leftColumn || !rightColumn)) {
            isValid = false;
            row.style.borderColor = '#ef4444';
        } else {
            row.style.borderColor = '#d1d5db';
        }
    });
    
    return isValid;
}

function previewQuery() {
    const query = buildQuery();
    if (query) {
        document.getElementById('sql-editor').value = query;
        
        // Highlight syntax (simple)
        const editor = document.getElementById('sql-editor');
        editor.style.backgroundColor = '#f8fafc';
        setTimeout(() => {
            editor.style.backgroundColor = '';
        }, 200);
    }
}

function loadExample(type) {
    let query = '';
    
    switch(type) {
        case 'users':
            query = 'SELECT * FROM users LIMIT 10';
            break;
        case 'escolas':
            query = 'SELECT * FROM escolas WHERE ativo = 1';
            break;
        case 'join':
            query = 'SELECT u.name, u.email, e.nome as escola FROM users u LEFT JOIN escolas e ON u.escola_id = e.id LIMIT 10';
            break;
    }
    
    document.getElementById('sql-editor').value = query;
}

function clearQuery() {
    document.getElementById('sql-editor').value = '';
    document.getElementById('table-select').value = '';
    document.getElementById('table-search-input').value = '';
    document.getElementById('table-dropdown').classList.add('hidden');
    document.getElementById('columns-select').innerHTML = '<option value="*">* (Todas)</option>';
    document.getElementById('order-column').value = '';
    document.getElementById('limit-input').value = '';
    document.getElementById('where-conditions').innerHTML = `
        <div class="flex space-x-1">
            <input type="text" placeholder="Coluna" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm">
            <select class="border border-gray-300 rounded px-2 py-1 text-sm">
                <option value="=">=</option>
                <option value="!=">!=</option>
                <option value=">">></option>
                <option value="<"><</option>
                <option value="LIKE">LIKE</option>
            </select>
            <input type="text" placeholder="Valor" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm">
        </div>
    `;
    document.getElementById('joins-list').innerHTML = '';
    document.getElementById('joins-container').classList.add('hidden');
    document.getElementById('results-container').classList.add('hidden');
    document.getElementById('query-status').innerHTML = '';
    document.getElementById('table-info').innerHTML = 'Selecione uma tabela para ver suas informações';
    
    currentTable = '';
    tableColumns = [];
    joinCounter = 0;
    
    document.getElementById('add-join-btn').disabled = true;
}

function saveQuery() {
    const query = document.getElementById('sql-editor').value.trim();
    if (!query) {
        alert('Digite uma consulta SQL primeiro.');
        return;
    }
    
    const name = prompt('Nome para a consulta:');
    if (!name) return;
    
    // Implementar salvamento da consulta
    alert('Funcionalidade de salvamento será implementada em breve.');
}

function loadSavedQuery(query, name) {
    document.getElementById('sql-editor').value = query;
    document.getElementById('query-status').innerHTML = `<span class="text-blue-600">Consulta "${name}" carregada</span>`;
}

function displayResults(results, executionTime, rowsReturned) {
    const container = document.getElementById('results-container');
    const tableContainer = document.getElementById('results-table');
    const infoContainer = document.getElementById('results-info');
    
    if (!results || results.length === 0) {
        tableContainer.innerHTML = '<div class="p-4 text-center text-gray-500">Nenhum resultado encontrado</div>';
        infoContainer.innerHTML = `Tempo de execução: ${executionTime}ms | 0 registros`;
        container.classList.remove('hidden');
        return;
    }
    
    // Criar tabela
    const columns = Object.keys(results[0]);
    let tableHTML = '<table class="min-w-full divide-y divide-gray-200">';
    
    // Cabeçalho
    tableHTML += '<thead class="bg-gray-50"><tr>';
    columns.forEach(column => {
        tableHTML += `<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${column}</th>`;
    });
    tableHTML += '</tr></thead>';
    
    // Corpo
    tableHTML += '<tbody class="bg-white divide-y divide-gray-200">';
    results.forEach((row, index) => {
        tableHTML += `<tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">`;
        columns.forEach(column => {
            const value = row[column];
            tableHTML += `<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${value !== null ? value : '<span class="text-gray-400">NULL</span>'}</td>`;
        });
        tableHTML += '</tr>';
    });
    tableHTML += '</tbody></table>';
    
    tableContainer.innerHTML = tableHTML;
    infoContainer.innerHTML = `Tempo de execução: ${executionTime}ms | ${rowsReturned} registro(s) retornado(s)`;
    container.classList.remove('hidden');
}

function exportResults() {
    const table = document.querySelector('#results-table table');
    if (!table) {
        alert('Nenhum resultado para exportar.');
        return;
    }
    
    let csv = '';
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        const rowData = Array.from(cols).map(col => {
            let text = col.textContent.trim();
            if (text === 'NULL') text = '';
            return `"${text.replace(/"/g, '""')}"`;
        });
        csv += rowData.join(',') + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `query_results_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
@endsection