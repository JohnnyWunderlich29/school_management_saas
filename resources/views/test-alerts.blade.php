@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Teste do Sistema de Alertas</h1>
        <p class="text-gray-600 mt-2">Teste todos os tipos de alertas implementados no sistema</p>
    </div>

    <!-- Botões para testar alertas dinâmicos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Alertas Dinâmicos (JavaScript)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button onclick="window.alertSystem.success('Operação realizada com sucesso!')" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-check mr-2"></i>Sucesso
            </button>
            
            <button onclick="window.alertSystem.error('Ocorreu um erro na operação!')" 
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>Erro
            </button>
            
            <button onclick="window.alertSystem.warning('Atenção: Esta ação não pode ser desfeita!')" 
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-exclamation-triangle mr-2"></i>Aviso
            </button>
            
            <button onclick="window.alertSystem.info('Informação importante sobre o sistema')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-info-circle mr-2"></i>Info
            </button>
        </div>
        
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <button onclick="window.alertSystem.validation('Dados inválidos encontrados', ['Nome é obrigatório', 'Email deve ser válido', 'Senha deve ter pelo menos 8 caracteres'])" 
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-exclamation-circle mr-2"></i>Validação com Erros
            </button>
            
            <button onclick="window.alertSystem.systemError('Erro interno do servidor')" 
                    class="bg-red-800 hover:bg-red-900 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-bug mr-2"></i>Erro do Sistema
            </button>
        </div>
        
        <div class="mt-4">
            <button onclick="window.alertSystem.accessDenied('Você não tem permissão para acessar esta área')" 
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-lock mr-2"></i>Acesso Negado
            </button>
        </div>
    </div>

    <!-- Alertas com opções avançadas -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Alertas Avançados</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button onclick="window.alertSystem.show('Alerta persistente que não desaparece automaticamente', 'info', { persistent: true, timeout: 0 })" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-thumbtack mr-2"></i>Alerta Persistente
            </button>
            
            <button onclick="window.alertSystem.show('Alerta com timeout personalizado (10s)', 'warning', { timeout: 10000 })" 
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-clock mr-2"></i>Timeout 10s
            </button>
            
            <button onclick="window.alertSystem.show('Alerta não dispensável', 'error', { dismissible: false, timeout: 5000 })" 
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-ban mr-2"></i>Não Dispensável
            </button>
            
            <button onclick="window.alertSystem.show('Alerta com ações customizadas', 'info', { actions: [{ label: 'Ir para Dashboard', action: 'redirect', url: '/dashboard', class: 'bg-blue-600 hover:bg-blue-700 text-white' }, { label: 'Recarregar', action: 'reload', class: 'bg-gray-600 hover:bg-gray-700 text-white' }] })" 
                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-cogs mr-2"></i>Com Ações
            </button>
        </div>
    </div>

    <!-- Controles do sistema -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Controles do Sistema</h2>
        <div class="flex flex-wrap gap-4">
            <button onclick="window.alertSystem.dismissAll()" 
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-times-circle mr-2"></i>Fechar Todos
            </button>
            
            <button onclick="window.alertSystem.dismissNonPersistent()" 
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-eraser mr-2"></i>Fechar Não Persistentes
            </button>
        </div>
        
        <div class="mt-4 text-sm text-gray-600">
            <p><strong>Dicas:</strong></p>
            <ul class="list-disc list-inside space-y-1 mt-2">
                <li>Pressione <kbd class="bg-gray-100 px-2 py-1 rounded text-xs">ESC</kbd> para fechar alertas não persistentes</li>
                <li>Alertas persistentes têm um indicador azul no canto superior direito</li>
                <li>Alertas de erro do sistema são sempre persistentes</li>
                <li>Clique no X para fechar alertas individuais (se dispensáveis)</li>
            </ul>
        </div>
    </div>

    <!-- Demonstração de alertas do servidor -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Alertas do Servidor (Simulação)</h2>
        <p class="text-gray-600 mb-4">Estes alertas simulam mensagens vindas do servidor via AlertService:</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <form action="{{ route('test.alert') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="type" value="success">
                <input type="hidden" name="message" value="Dados salvos com sucesso!">
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-server mr-2"></i>Sucesso do Servidor
                </button>
            </form>
            
            <form action="{{ route('test.alert') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="type" value="error">
                <input type="hidden" name="message" value="Erro ao processar solicitação">
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-server mr-2"></i>Erro do Servidor
                </button>
            </form>
        </div>
    </div>
</div>
@endsection