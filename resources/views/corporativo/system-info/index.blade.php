@extends('corporativo.layout')

@section('title', 'Informações do Sistema - Sistema Corporativo')
@section('page-title', 'Informações do Sistema')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Informações do Laravel -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Framework & Versões</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Laravel</span>
                    <span class="text-sm text-gray-900">{{ app()->version() }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">PHP</span>
                    <span class="text-sm text-gray-900">{{ PHP_VERSION }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Ambiente</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                        @if(app()->environment('production')) bg-red-100 text-red-800
                        @elseif(app()->environment('staging')) bg-yellow-100 text-yellow-800
                        @else bg-green-100 text-green-800
                        @endif">
                        {{ strtoupper(app()->environment()) }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Debug Mode</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                        @if(config('app.debug')) bg-yellow-100 text-yellow-800
                        @else bg-green-100 text-green-800
                        @endif">
                        {{ config('app.debug') ? 'ATIVADO' : 'DESATIVADO' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Timezone</span>
                    <span class="text-sm text-gray-900">{{ config('app.timezone') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Locale</span>
                    <span class="text-sm text-gray-900">{{ config('app.locale') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Informações do Banco de Dados -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Banco de Dados</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Driver</span>
                    <span class="text-sm text-gray-900">{{ config('database.default') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Host</span>
                    <span class="text-sm text-gray-900">{{ config('database.connections.'.config('database.default').'.host') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Porta</span>
                    <span class="text-sm text-gray-900">{{ config('database.connections.'.config('database.default').'.port') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Database</span>
                    <span class="text-sm text-gray-900">{{ config('database.connections.'.config('database.default').'.database') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Status da Conexão</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        CONECTADO
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Total de Tabelas</span>
                    <span class="text-sm text-gray-900">{{ count($systemInfo['tables']) }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Informações do Servidor -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Servidor</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Sistema Operacional</span>
                    <span class="text-sm text-gray-900">{{ PHP_OS }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Servidor Web</span>
                    <span class="text-sm text-gray-900">{{ $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Memória PHP</span>
                    <span class="text-sm text-gray-900">{{ ini_get('memory_limit') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Max Upload</span>
                    <span class="text-sm text-gray-900">{{ ini_get('upload_max_filesize') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Max Execution Time</span>
                    <span class="text-sm text-gray-900">{{ ini_get('max_execution_time') }}s</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Uptime do Sistema</span>
                    <span class="text-sm text-gray-900">{{ $systemInfo['uptime'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cache e Performance -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Cache & Performance</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Cache Driver</span>
                    <span class="text-sm text-gray-900">{{ config('cache.default') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Session Driver</span>
                    <span class="text-sm text-gray-900">{{ config('session.driver') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Queue Driver</span>
                    <span class="text-sm text-gray-900">{{ config('queue.default') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Mail Driver</span>
                    <span class="text-sm text-gray-900">{{ config('mail.default') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">OPcache</span>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                        @if(function_exists('opcache_get_status') && opcache_get_status()) bg-green-100 text-green-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ function_exists('opcache_get_status') && opcache_get_status() ? 'ATIVO' : 'INATIVO' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabelas do Sistema -->
<div class="mt-6 bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Tabelas do Sistema</h3>
            <button onclick="refreshTables()" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                Atualizar
            </button>
        </div>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($systemInfo['tables'] as $table)
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-medium text-gray-900">{{ $table }}</h4>
                    <button onclick="showTableInfo('{{ $table }}')" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-600 mt-1">Clique no ícone para mais detalhes</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Extensões PHP -->
<div class="mt-6 bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Extensões PHP Carregadas</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-2">
            @foreach($systemInfo['php_extensions'] as $extension)
            <div class="inline-flex px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">
                {{ $extension }}
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Ações do Sistema -->
<div class="mt-6 bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Ações do Sistema</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button onclick="clearCache()" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                <span class="text-sm font-medium">Limpar Cache</span>
            </button>
            
            <button onclick="optimizeSystem()" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span class="text-sm font-medium">Otimizar Sistema</span>
            </button>
            
            <button onclick="runMaintenance()" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-sm font-medium">Manutenção</span>
            </button>
            
            <button onclick="generateReport()" class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm font-medium">Gerar Relatório</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal para informações da tabela -->
<div id="tableModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-96 overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900" id="tableModalTitle">Informações da Tabela</h3>
                    <button onclick="closeTableModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="px-6 py-4" id="tableModalContent">
                <!-- Conteúdo será carregado via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar informações da tabela
function showTableInfo(tableName) {
    document.getElementById('tableModalTitle').textContent = `Tabela: ${tableName}`;
    document.getElementById('tableModalContent').innerHTML = `
        <div class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-2 text-gray-600">Carregando informações...</p>
        </div>
    `;
    document.getElementById('tableModal').classList.remove('hidden');
    
    // Fazer requisição para obter informações da tabela
    fetch(`{{ route('corporativo.table.info', ['tableName' => '__TABLE_NAME__']) }}`.replace('__TABLE_NAME__', tableName), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const tableInfo = data.data;
            let columnsHtml = '';
            
            if (tableInfo.columns && tableInfo.columns.length > 0) {
                columnsHtml = tableInfo.columns.map(col => {
                    if (typeof col === 'object') {
                        // Para SQLite
                        if (col.name) {
                            return `<tr><td class="px-3 py-2 text-sm">${col.name}</td><td class="px-3 py-2 text-sm">${col.type || 'N/A'}</td><td class="px-3 py-2 text-sm">${col.notnull ? 'NO' : 'YES'}</td></tr>`;
                        }
                        // Para MySQL
                        if (col.Field) {
                            return `<tr><td class="px-3 py-2 text-sm">${col.Field}</td><td class="px-3 py-2 text-sm">${col.Type}</td><td class="px-3 py-2 text-sm">${col.Null}</td></tr>`;
                        }
                    }
                    return '';
                }).join('');
            }
            
            document.getElementById('tableModalContent').innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Informações Gerais</h4>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <p class="text-sm"><strong>Nome:</strong> ${tableInfo.name}</p>
                            <p class="text-sm"><strong>Engine:</strong> ${tableInfo.engine}</p>
                            <p class="text-sm"><strong>Charset:</strong> ${tableInfo.charset}</p>
                            <p class="text-sm"><strong>Registros:</strong> ${tableInfo.row_count}</p>
                        </div>
                    </div>
                    
                    ${columnsHtml ? `
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Colunas</h4>
                        <div class="bg-gray-50 rounded-lg p-4 overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left px-3 py-2 text-sm font-medium">Nome</th>
                                        <th class="text-left px-3 py-2 text-sm font-medium">Tipo</th>
                                        <th class="text-left px-3 py-2 text-sm font-medium">Nulo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${columnsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
        } else {
            document.getElementById('tableModalContent').innerHTML = `
                <div class="text-center py-4">
                    <p class="text-red-600">Erro ao carregar informações: ${data.message}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        document.getElementById('tableModalContent').innerHTML = `
            <div class="text-center py-4">
                <p class="text-red-600">Erro ao carregar informações: ${error.message}</p>
            </div>
        `;
    });
}

// Fechar modal
function closeTableModal() {
    document.getElementById('tableModal').classList.add('hidden');
}

// Ações do sistema
function clearCache() {
    if (confirm('Tem certeza que deseja limpar o cache do sistema?')) {
        performSystemAction('{{ route("corporativo.clear.cache") }}', 'Limpando cache...');
    }
}

function optimizeSystem() {
    if (confirm('Tem certeza que deseja otimizar o sistema?')) {
        performSystemAction('{{ route("corporativo.optimize.system") }}', 'Otimizando sistema...');
    }
}

function runMaintenance() {
    if (confirm('Tem certeza que deseja executar a manutenção do sistema?')) {
        performSystemAction('{{ route("corporativo.run.maintenance") }}', 'Executando manutenção...');
    }
}

function generateReport() {
    performSystemAction('{{ route("corporativo.generate.report") }}', 'Gerando relatório...');
}

// Função auxiliar para executar ações do sistema
function performSystemAction(url, loadingMessage) {
    // Mostrar loading
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'system-loading';
    loadingDiv.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center';
    loadingDiv.innerHTML = `
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                <span class="text-gray-700">${loadingMessage}</span>
            </div>
        </div>
    `;
    document.body.appendChild(loadingDiv);
    
    // Fazer requisição AJAX
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Remover loading
        document.body.removeChild(loadingDiv);
        
        if (data.success) {
            // Mostrar mensagem de sucesso
            showNotification(data.message, 'success');
            
            // Se for geração de relatório e tiver URL de download
            if (data.download_url) {
                setTimeout(() => {
                    window.open(data.download_url, '_blank');
                }, 1000);
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        // Remover loading
        document.body.removeChild(loadingDiv);
        showNotification('Erro ao executar ação: ' + error.message, 'error');
    });
}

// Função para mostrar notificações
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remover após 5 segundos
    setTimeout(() => {
        if (document.body.contains(notification)) {
            document.body.removeChild(notification);
        }
    }, 5000);
}

function refreshTables() {
    location.reload();
}

// Fechar modal clicando fora
document.getElementById('tableModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTableModal();
    }
});
</script>
@endsection