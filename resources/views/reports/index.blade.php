@extends('layouts.app')

@section('content')
    <x-card>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Relatórios</h1>
                <p class="mt-1 text-sm text-gray-600">Gerencie e visualize relatórios do sistema</p>
            </div>
            @can('create', \App\Models\Report::class)
            <x-button href="{{ route('reports.create') }}" color="primary" class="w-full sm:w-auto text-mobile-button">
                <i class="fas fa-plus mr-1"></i> Novo Relatório
            </x-button>
            @endcan
        </div>

        <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-8">
        <!-- Total de Relatórios -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-3 sm:p-5 bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-blue-100 bg-opacity-30 p-2 sm:p-3">
                        <i class="fas fa-file-alt text-white text-sm sm:text-xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-5 min-w-0">
                        <h3 class="text-xs sm:text-sm font-medium text-blue-100 truncate">Total</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-lg sm:text-2xl font-semibold text-white">{{ $stats['total'] }}</p>
                            <p class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-blue-100 hidden sm:block">relatórios</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Relatórios Concluídos -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-3 sm:p-5 bg-gradient-to-r from-green-500 to-green-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-green-100 bg-opacity-30 p-2 sm:p-3">
                        <i class="fas fa-check-circle text-white text-sm sm:text-xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-5 min-w-0">
                        <h3 class="text-xs sm:text-sm font-medium text-green-100 truncate">Concluídos</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-lg sm:text-2xl font-semibold text-white">{{ $stats['completed'] }}</p>
                            <p class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-green-100 hidden sm:block">prontos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Relatórios em Processamento -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-3 sm:p-5 bg-gradient-to-r from-yellow-500 to-yellow-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-yellow-100 bg-opacity-30 p-2 sm:p-3">
                        <i class="fas fa-spinner text-white text-sm sm:text-xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-5 min-w-0">
                        <h3 class="text-xs sm:text-sm font-medium text-yellow-100 truncate">Processando</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-lg sm:text-2xl font-semibold text-white">{{ $stats['processing'] }}</p>
                            <p class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-yellow-100 hidden sm:block">em andamento</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Relatórios Pendentes -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-3 sm:p-5 bg-gradient-to-r from-red-500 to-red-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-red-100 bg-opacity-30 p-2 sm:p-3">
                        <i class="fas fa-clock text-white text-sm sm:text-xl"></i>
                    </div>
                    <div class="ml-3 sm:ml-5 min-w-0">
                        <h3 class="text-xs sm:text-sm font-medium text-red-100 truncate">Pendentes</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-lg sm:text-2xl font-semibold text-white">{{ $stats['pending'] }}</p>
                            <p class="ml-1 sm:ml-2 text-xs sm:text-sm font-medium text-red-100 hidden sm:block">aguardando</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Filtros colapsáveis -->
        <x-collapsible-filter 
            title="Filtros de Relatórios" 
            :action="route('reports.index')" 
            :clear-route="route('reports.index')"
            target="reports-list-wrapper"
        >
            <x-filter-field 
                name="type" 
                label="Tipo de Relatório" 
                type="select"
                empty-option="Todos os tipos"
                :options="[
                    'attendance' => 'Presenças',
                    'schedule' => 'Escalas',
                    'performance' => 'Performance',
                    'financial' => 'Financeiro'
                ]"
            />
            
            <x-filter-field 
                name="status" 
                label="Status" 
                type="select"
                empty-option="Todos os status"
                :options="[
                    'pending' => 'Pendente',
                    'processing' => 'Processando',
                    'completed' => 'Concluído',
                    'failed' => 'Falhou'
                ]"
            />
        </x-collapsible-filter>
        <!-- Lista de Relatórios -->
        <div id="reports-list-wrapper" class="relative">
            <x-loading-overlay message="Atualizando relatórios..." />
            <div data-ajax-content>
        <!-- Desktop Table -->
        <div class="hidden lg:block">
            <x-table 
                :headers="[
                    ['label' => 'Nome', 'sort' => 'name'],
                    ['label' => 'Tipo', 'sort' => 'type'],
                    ['label' => 'Formato', 'sort' => 'format'],
                    ['label' => 'Status', 'sort' => 'status'],
                    ['label' => 'Criado em', 'sort' => 'created_at'],
                    ['label' => 'Tamanho', 'sort' => 'file_size'],
                ]" 
                :actions="true"
                striped
                hover
                responsive
                sortable
                :currentSort="request('sort')"
                :currentDirection="request('direction', 'desc')"
            >
                @forelse($reports as $index => $report)
                    <x-table-row :striped="true" :index="$index">
                        <x-table-cell>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $report->name }}</div>
                                    <div class="text-gray-500 text-xs">ID: {{ $report->id }}</div>
                                </div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            @switch($report->type)
                                @case('attendance')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Presenças
                                    </span>
                                    @break
                                @case('schedule')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        Escalas
                                    </span>
                                    @break
                                @case('performance')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Performance
                                    </span>
                                    @break
                                @case('financial')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Financeiro
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($report->type) }}
                                    </span>
                            @endswitch
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ strtoupper($report->format) }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            @switch($report->status)
                                @case('pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i> Pendente
                                    </span>
                                    @break
                                @case('processing')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> Processando
                                    </span>
                                    @break
                                @case('completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i> Concluído
                                    </span>
                                    @break
                                @case('failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i> Falhou
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($report->status) }}
                                    </span>
                            @endswitch
                        </x-table-cell>
                        <x-table-cell>{{ $report->created_at->format('d/m/Y H:i') }}</x-table-cell>
                        <x-table-cell>
                            @if($report->file_size)
                                {{ number_format($report->file_size / 1024, 2) }} KB
                            @else
                                -
                            @endif
                        </x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex justify-end space-x-2">
                                @if($report->status === 'completed' && $report->file_path)
                                    <a href="{{ route('reports.download', $report) }}" class="text-green-600 hover:text-green-900" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                                
                                <a href="{{ route('reports.show', $report) }}" class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                <button type="button" 
                                        class="text-red-600 hover:text-red-900" 
                                        onclick="deleteReport({{ $report->id }})" 
                                        title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            <div class="py-8">
                                <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                                <h5 class="text-lg font-medium text-gray-600 mb-2">Nenhum relatório encontrado</h5>
                                <p class="text-gray-500 mb-4">Você ainda não possui relatórios gerados.</p>
                                <x-button href="{{ route('reports.create') }}" color="primary" size="sm">
                                    <i class="fas fa-plus mr-1"></i> Criar Primeiro Relatório
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden space-y-3">
            @forelse($reports as $report)
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
                    <!-- Header do Card -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center flex-1 min-w-0">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-file-alt text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-3 min-w-0 flex-1">
                                <h3 class="text-sm font-medium text-gray-900 truncate">{{ $report->name }}</h3>
                                <p class="text-xs text-gray-500 mt-1">ID: {{ $report->id }}</p>
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="ml-2 flex-shrink-0">
                            @switch($report->status)
                                @case('pending')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-clock mr-1"></i> Pendente
                                    </span>
                                    @break
                                @case('processing')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> Processando
                                    </span>
                                    @break
                                @case('completed')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i> Concluído
                                    </span>
                                    @break
                                @case('failed')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i> Falhou
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($report->status) }}
                                    </span>
                            @endswitch
                        </div>
                    </div>
                    
                    <!-- Informações do Card -->
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-gray-500">Tipo:</span>
                            @switch($report->type)
                                @case('attendance')
                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        Presenças
                                    </span>
                                    @break
                                @case('schedule')
                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                        Escalas
                                    </span>
                                    @break
                                @case('performance')
                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        Performance
                                    </span>
                                    @break
                                @case('financial')
                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Financeiro
                                    </span>
                                    @break
                                @default
                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($report->type) }}
                                    </span>
                            @endswitch
                        </div>
                        <div>
                            <span class="text-gray-500">Formato:</span>
                            <span class="ml-1 font-medium text-gray-900">{{ strtoupper($report->format) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Criado:</span>
                            <span class="ml-1 text-gray-900">{{ $report->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Tamanho:</span>
                            <span class="ml-1 text-gray-900">
                                @if($report->file_size)
                                    {{ number_format($report->file_size / 1024, 2) }} KB
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <!-- Ações do Card -->
                    <div class="mt-4 pt-3 border-t border-gray-200">
                        <div class="flex space-x-2">
                            @if($report->status === 'completed' && $report->file_path)
                                <a href="{{ route('reports.download', $report) }}" 
                                   class="flex-1 flex items-center justify-center px-3 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 min-h-[40px]">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                            @endif
                            <a href="{{ route('reports.show', $report) }}" 
                               class="flex-1 flex items-center justify-center px-3 py-2 text-sm bg-green-600 text-white rounded-md hover:bg-green-700 min-h-[40px]">
                                <i class="fas fa-eye mr-1"></i> Visualizar
                            </a>
                            <button type="button" 
                                    class="flex items-center justify-center px-3 py-2 text-sm bg-red-600 text-white rounded-md hover:bg-red-700 min-h-[40px]" 
                                    onclick="deleteReport({{ $report->id }})">
                                <i class="fas fa-trash mr-1"></i> Excluir
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-8 text-center">
                    <div class="text-gray-500">
                        <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                        <h5 class="text-lg font-medium text-gray-600 mb-2">Nenhum relatório encontrado</h5>
                        <p class="text-gray-500 mb-4">Você ainda não possui relatórios gerados.</p>
                        <x-button href="{{ route('reports.create') }}" color="primary" size="sm">
                            <i class="fas fa-plus mr-1"></i> Criar Primeiro Relatório
                        </x-button>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Paginação -->
        <div class="mt-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                    Mostrando {{ $reports->firstItem() ?? 0 }} a {{ $reports->lastItem() ?? 0 }} de {{ $reports->total() }} resultados
                </div>
                <div class="flex justify-center sm:justify-end">
                    {{ $reports->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
            </div> <!-- data-ajax-content -->
        </div> <!-- reports-list-wrapper -->
    </x-card>

<!-- Modal de Confirmação de Exclusão -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-4 sm:p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Confirmar Exclusão</h3>
            <div class="mt-2 px-4 sm:px-7 py-3">
                <p class="text-sm text-gray-500">
                    Tem certeza que deseja excluir este relatório? Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-0 justify-center sm:space-x-3 mt-4">
                <button onclick="closeDeleteModal()" class="w-full sm:w-auto px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300 min-h-[44px]">
                    Cancelar
                </button>
                <form id="deleteForm" method="POST" class="w-full sm:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 min-h-[44px]">
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let reportToDelete = null;

function deleteReport(reportId) {
    reportToDelete = reportId;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteForm').action = `/reports/${reportId}`;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    reportToDelete = null;
}

// Auto-refresh para relatórios em processamento
document.addEventListener('DOMContentLoaded', function() {
    const hasProcessing = @json($reports->where('status', 'processing')->count() > 0);
    
    if (hasProcessing) {
        setTimeout(function() {
            location.reload();
        }, 30000); // Refresh a cada 30 segundos
    }

    initReportsAjaxBindings();
    window.addEventListener('popstate', function() {
        updateReportsContainer(window.location.href, false);
    });
});

// AJAX helpers para ordenação e paginação
function showReportsLoading() {
    const wrapper = document.getElementById('reports-list-wrapper');
    if (!wrapper) return;
    const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
    if (overlay) overlay.classList.remove('hidden');
    wrapper.classList.add('pointer-events-none');
}

function hideReportsLoading() {
    const wrapper = document.getElementById('reports-list-wrapper');
    if (!wrapper) return;
    const overlay = wrapper.querySelector('[data-loading-overlay]') || wrapper.querySelector('.loading-overlay');
    if (overlay) overlay.classList.add('hidden');
    wrapper.classList.remove('pointer-events-none');
}

async function updateReportsContainer(url, pushState = true) {
    showReportsLoading();
    try {
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
        const text = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(text, 'text/html');
        const newWrapper = doc.getElementById('reports-list-wrapper');
        const wrapper = document.getElementById('reports-list-wrapper');
        if (newWrapper && wrapper) {
            const targetContent = wrapper.querySelector('[data-ajax-content]') || wrapper;
            const newTargetContent = newWrapper.querySelector('[data-ajax-content]') || newWrapper;
            targetContent.innerHTML = newTargetContent.innerHTML;
            if (pushState) window.history.pushState(null, '', url);
            initReportsAjaxBindings();
        } else {
            window.location.href = url;
        }
    } catch (e) {
        console.error('Erro ao atualizar relatórios via AJAX', e);
        window.location.href = url;
    } finally {
        hideReportsLoading();
    }
}

function initReportsAjaxBindings() {
    const wrapper = document.getElementById('reports-list-wrapper');
    if (!wrapper) return;
    const ajaxArea = wrapper.querySelector('[data-ajax-content]');
    if (!ajaxArea) return;

    // Interceptar ordenação nos cabeçalhos da tabela
    const sortLinks = ajaxArea.querySelectorAll('thead a[href]');
    sortLinks.forEach(link => {
        if (link.dataset.ajaxBound === '1') return;
        link.dataset.ajaxBound = '1';
        link.addEventListener('click', function(e) {
            e.preventDefault();
            updateReportsContainer(this.href);
        });
    });

    // Interceptar paginação
    const paginationLinks = ajaxArea.querySelectorAll('nav[aria-label="Pagination Navigation"] a[href]');
    paginationLinks.forEach(link => {
        if (link.dataset.ajaxBound === '1') return;
        link.dataset.ajaxBound = '1';
        link.addEventListener('click', function(e) {
            e.preventDefault();
            updateReportsContainer(this.href);
        });
    });
}
</script>
@endpush