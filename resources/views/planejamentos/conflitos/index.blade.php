@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Planejamentos', 'url' => route('planejamentos.index')],
    ['title' => 'Gestão de Conflitos', 'url' => '#']
]" />

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestão de Conflitos</h1>
            <p class="text-gray-600">Detecte e resolva conflitos entre planejamentos pedagógicos</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <x-button color="primary" class="inline-flex items-center" onclick="verificarTodosConflitos()">
                <i class="fas fa-search mr-2"></i>
                Verificar Todos
            </x-button>
            
            <x-button color="secondary" class="inline-flex items-center" onclick="gerarRelatorioConflitos()">
                <i class="fas fa-file-alt mr-2"></i>
                Relatório
            </x-button>
        </div>
    </div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total de Conflitos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Conflitos Ativos</p>
                    <p class="text-2xl font-bold text-gray-900" id="total-conflitos">{{ $stats['total_conflitos'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Conflitos Críticos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-fire text-orange-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Críticos</p>
                    <p class="text-2xl font-bold text-gray-900" id="conflitos-criticos">{{ $stats['criticos'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Conflitos Resolvidos -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Resolvidos</p>
                    <p class="text-2xl font-bold text-gray-900" id="conflitos-resolvidos">{{ $stats['resolvidos'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Taxa de Resolução -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percentage text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Taxa Resolução</p>
                    <p class="text-2xl font-bold text-gray-900" id="taxa-resolucao">{{ $stats['taxa_resolucao'] ?? '0%' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Verificação -->
    <x-card>
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                Verificação Personalizada
            </h3>
        </div>

        <form id="form-verificacao" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Período -->
                <div>
                    <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                    <input type="date" name="data_fim" id="data_fim" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Modalidade -->
                <div>
                    <label for="modalidade" class="block text-sm font-medium text-gray-700 mb-1">Modalidade</label>
                    <select name="modalidade" id="modalidade" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas</option>
                        <option value="presencial">Presencial</option>
                        <option value="ead">EAD</option>
                        <option value="hibrido">Híbrido</option>
                    </select>
                </div>

                <!-- Tipo de Conflito -->
                <div>
                    <label for="tipo_conflito" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Conflito</label>
                    <select name="tipo_conflito" id="tipo_conflito" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="horario">Horário</option>
                        <option value="professor">Professor</option>
                        <option value="sala">Sala</option>
                        <option value="turma">Turma</option>
                        <option value="recurso">Recurso</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <x-button type="button" color="primary" class="inline-flex items-center" onclick="verificarConflitosPersonalizado()">
                    <i class="fas fa-search mr-2"></i>
                    Verificar Conflitos
                </x-button>
                
                <x-button type="button" color="secondary" class="inline-flex items-center" onclick="limparFiltros()">
                    <i class="fas fa-times mr-2"></i>
                    Limpar Filtros
                </x-button>
            </div>
        </form>
    </x-card>

    <!-- Loading -->
    <div id="loading-conflitos" class="hidden">
        <x-card>
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Verificando conflitos...</p>
            </div>
        </x-card>
    </div>

    <!-- Lista de Conflitos -->
    <div id="lista-conflitos">
        @if(isset($conflitos) && count($conflitos) > 0)
            @include('planejamentos.conflitos.lista', ['conflitos' => $conflitos])
        @else
            <x-card>
                <div class="text-center py-12">
                    <div class="w-24 h-24 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-alt text-green-600 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum conflito detectado</h3>
                    <p class="text-gray-600 mb-6">
                        Todos os planejamentos estão em conformidade. Execute uma verificação para confirmar.
                    </p>
                    
                    <x-button color="primary" class="inline-flex items-center" onclick="verificarTodosConflitos()">
                        <i class="fas fa-search mr-2"></i>
                        Verificar Agora
                    </x-button>
                </div>
            </x-card>
        @endif
    </div>
</div>

<!-- Modal de Resolução de Conflito -->
<div id="modal-resolucao" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Resolver Conflito</h3>
                    <button onclick="fecharModalResolucao()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div id="conteudo-modal-resolucao">
                    <!-- Conteúdo será carregado dinamicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Verificar todos os conflitos
function verificarTodosConflitos() {
    mostrarLoading();
    
    fetch('{{ route("planejamentos.conflitos.verificar-todos") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        ocultarLoading();
        atualizarListaConflitos(data.conflitos);
        atualizarEstatisticas(data.stats);
    })
    .catch(error => {
        ocultarLoading();
        console.error('Erro ao verificar conflitos:', error);
        window.AlertService.error('Erro ao verificar conflitos. Tente novamente.');
    });
}

// Verificar conflitos personalizado
function verificarConflitosPersonalizado() {
    const form = document.getElementById('form-verificacao');
    const formData = new FormData(form);
    
    mostrarLoading();
    
    fetch('{{ route("planejamentos.conflitos.verificar-personalizado") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        ocultarLoading();
        atualizarListaConflitos(data.conflitos);
        atualizarEstatisticas(data.stats);
    })
    .catch(error => {
        ocultarLoading();
        console.error('Erro ao verificar conflitos:', error);
        window.AlertService.error('Erro ao verificar conflitos. Tente novamente.');
    });
}

// Resolver conflito
function resolverConflito(conflitoId) {
    fetch(`{{ route("planejamentos.conflitos.detalhes", ":id") }}`.replace(':id', conflitoId))
    .then(response => response.text())
    .then(html => {
        document.getElementById('conteudo-modal-resolucao').innerHTML = html;
        document.getElementById('modal-resolucao').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Erro ao carregar detalhes do conflito:', error);
        window.AlertService.error('Erro ao carregar detalhes do conflito.');
    });
}

// Fechar modal de resolução
function fecharModalResolucao() {
    document.getElementById('modal-resolucao').classList.add('hidden');
}

// Gerar relatório de conflitos
function gerarRelatorioConflitos() {
    window.open('{{ route("planejamentos.conflitos.relatorio") }}', '_blank');
}

// Limpar filtros
function limparFiltros() {
    document.getElementById('form-verificacao').reset();
}

// Mostrar loading
function mostrarLoading() {
    document.getElementById('loading-conflitos').classList.remove('hidden');
    document.getElementById('lista-conflitos').classList.add('hidden');
}

// Ocultar loading
function ocultarLoading() {
    document.getElementById('loading-conflitos').classList.add('hidden');
    document.getElementById('lista-conflitos').classList.remove('hidden');
}

// Atualizar lista de conflitos
function atualizarListaConflitos(conflitos) {
    fetch('{{ route("planejamentos.conflitos.lista-ajax") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ conflitos: conflitos })
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('lista-conflitos').innerHTML = html;
    })
    .catch(error => {
        console.error('Erro ao atualizar lista:', error);
    });
}

// Atualizar estatísticas
function atualizarEstatisticas(stats) {
    if (stats) {
        document.getElementById('total-conflitos').textContent = stats.total_conflitos || 0;
        document.getElementById('conflitos-criticos').textContent = stats.criticos || 0;
        document.getElementById('conflitos-resolvidos').textContent = stats.resolvidos || 0;
        document.getElementById('taxa-resolucao').textContent = stats.taxa_resolucao || '0%';
    }
}

// Auto-verificação a cada 5 minutos
setInterval(verificarTodosConflitos, 300000);
</script>
@endpush