@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => route('dashboard')],
    ['title' => 'Recorrências', 'url' => route('admin.recorrencias.index')]
]" />

<x-card>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Recorrências</h1>
            <p class="mt-1 text-sm text-gray-600">Listagem e gerenciamento de recorrências de cobrança</p>
        </div>
    </div>

    <x-collapsible-filter 
        title="Filtros de Recorrências" 
        :action="route('admin.recorrencias.index')" 
        :clear-route="route('admin.recorrencias.index')"
        target="recorrencias-table-wrapper"
    >
        <x-filter-field 
            name="status" 
            label="Status" 
            type="select"
            empty-option="Todos"
            :options="$statusOptions"
        />

        <x-filter-field 
            name="method" 
            label="Método" 
            type="select"
            empty-option="Todos"
            :options="$methodOptions"
        />

        <div>
            <x-date-filter-with-arrows 
                title="Período (início)" 
                name="data_inicio"
                label="De"
                :value="request('de')"
                dataFimName="data_fim"
                :dataFimValue="request('ate')"
            />
            <input type="hidden" name="de" id="de_hidden" value="{{ request('de') }}">
            <input type="hidden" name="ate" id="ate_hidden" value="{{ request('ate') }}">
        </div>
    </x-collapsible-filter>

    <div id="recorrencias-table-wrapper" class="relative">
        <x-loading-overlay message="Atualizando recorrências..." />
        <div data-ajax-content>
        @if($recorrencias->count() > 0)
            <x-table 
                :headers="[
                    ['label' => 'Responsável', 'sort' => null],
                    ['label' => 'Descrição', 'sort' => 'description'],
                    ['label' => 'Mensalidade', 'sort' => 'amount_cents'],
                    ['label' => 'Status', 'sort' => 'status'],
                    ['label' => 'Início', 'sort' => 'start_at'],
                    ['label' => 'Fim', 'sort' => 'end_at'],
                    ['label' => 'Último faturamento', 'sort' => 'last_invoice_date'],
                ]" 
                :actions="true" 
                striped 
                hover 
                responsive 
                sortable 
                :currentSort="request('sort')" 
                :currentDirection="request('direction', 'desc')"
            >
                @foreach($recorrencias as $rec)
                    <x-table-row :index="$loop->index">
                        <x-table-cell>
                            <span class="text-sm text-gray-700">{{ optional($rec->payer)->nome_completo ?? '-' }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm text-gray-700">{{ $rec->description ?? '-' }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm font-semibold text-gray-900">R$ {{ number_format(($rec->amount_cents ?? 0) / 100, 2, ',', '.') }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            @php
                                $statusStyles = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'paused' => 'bg-yellow-100 text-yellow-800',
                                    'canceled' => 'bg-gray-100 text-gray-800',
                                    'ended' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusStyles[$rec->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($rec->status ?? '-') }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm text-gray-700">{{ $rec->start_at ? \Carbon\Carbon::parse($rec->start_at)->format('d/m/Y') : '-' }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm text-gray-700">{{ $rec->end_at ? \Carbon\Carbon::parse($rec->end_at)->format('d/m/Y') : '-' }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm text-gray-700">{{ $rec->last_invoice_date ? \Carbon\Carbon::parse($rec->last_invoice_date)->format('d/m/Y') : '-' }}</span>
                        </x-table-cell>
                        <x-table-cell align="right">
                            @permission('finance.admin')
                            <div class="flex items-center justify-end space-x-2">
                                <x-button color="secondary" size="sm"
                                    title="Visualizar detalhes"
                                    onclick="openRecDetails(this)"
                                    data-id="{{ $rec->id }}"
                                    data-start="{{ $rec->start_at }}"
                                    data-end="{{ $rec->end_at }}"
                                    data-day="{{ $rec->day_of_month }}"
                                    data-amount="{{ ($rec->amount_cents ?? 0) / 100 }}"
                                    data-status="{{ $rec->status }}"
                                    data-method-id="{{ $rec->charge_method_id }}">
                                    <i class="fas fa-eye"></i>
                                </x-button>
                                <x-button color="info" size="sm"
                                    title="Alterar método de cobrança"
                                    onclick="openRecMethod(this)"
                                    data-id="{{ $rec->id }}"
                                    data-method-id="{{ $rec->charge_method_id }}">
                                    <i class="fas fa-exchange-alt"></i>
                                </x-button>
                                @if($rec->status === 'active')
                                    <x-button color="warning" size="sm"
                                        title="Pausar recorrência"
                                        onclick="openRecAction(this, 'pause')"
                                        data-id="{{ $rec->id }}">
                                        <i class="fas fa-pause"></i>
                                    </x-button>
                                @endif
                                @if($rec->status === 'paused')
                                    <x-button color="success" size="sm"
                                        title="Retomar recorrência"
                                        onclick="openRecAction(this, 'resume')"
                                        data-id="{{ $rec->id }}">
                                        <i class="fas fa-play"></i>
                                    </x-button>
                                @endif
                                @if(in_array($rec->status, ['active','paused']))
                                    <x-button color="danger" size="sm"
                                        title="Cancelar recorrência"
                                        onclick="openRecAction(this, 'cancel')"
                                        data-id="{{ $rec->id }}">
                                        <i class="fas fa-times"></i>
                                    </x-button>
                                @endif
                            </div>
                            @endpermission
                        </x-table-cell>
                    </x-table-row>
                @endforeach
            </x-table>

            <div class="mt-6">
                {{ $recorrencias->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-sync-alt text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma recorrência encontrada</h3>
                <p class="text-gray-600 mb-4">
                    @if(request()->hasAny(['status','method','de','ate']))
                        Nenhuma recorrência corresponde aos filtros aplicados.
                    @else
                        Não há recorrências cadastradas ainda.
                    @endif
                </p>
            </div>
        @endif
        </div>
    </div>

    <!-- Modais Globais -->
    <x-modal id="rec-details-modal" title="Detalhes da Recorrência" maxWidth="max-w-2xl">
        <div id="rec-details-content" class="space-y-3 text-sm text-gray-700">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-gray-500">Início</span>
                    <span id="rec-det-start" class="font-medium">-</span>
                </div>
                <div>
                    <span class="block text-gray-500">Fim</span>
                    <span id="rec-det-end" class="font-medium">-</span>
                </div>
                <div>
                    <span class="block text-gray-500">Dia de Cobrança</span>
                    <span id="rec-det-day" class="font-medium">-</span>
                </div>
                <div>
                    <span class="block text-gray-500">Valor</span>
                    <span id="rec-det-amount" class="font-medium">-</span>
                </div>
                <div>
                    <span class="block text-gray-500">Status</span>
                    <span id="rec-det-status" class="font-medium">-</span>
                </div>
                <div>
                    <span class="block text-gray-500">Método</span>
                    <span id="rec-det-method" class="font-medium">-</span>
                </div>
            </div>
        </div>
    </x-modal>

    <x-modal id="rec-method-modal" title="Alterar Método de Cobrança" maxWidth="max-w-lg" :footer="true">
        <form id="rec-method-form" onsubmit="event.preventDefault(); saveRecMethod();">
            <input type="hidden" id="rec-method-id" name="subscription_id" value="">
            <label class="block text-sm font-medium text-gray-700 mb-2">Novo método</label>
            <select id="rec-method-select" name="charge_method_id" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @foreach($methodList as $m)
                    <option value="{{ $m->id }}">{{ $m->method }}</option>
                @endforeach
            </select>
        </form>
        <x-slot name="footer">
            <x-button color="secondary" onclick="closeModal('rec-method-modal')">Cancelar</x-button>
            <x-button color="primary" onclick="saveRecMethod()"><i class="fas fa-save mr-2"></i>Salvar</x-button>
        </x-slot>
    </x-modal>

    <x-modal id="rec-action-modal" title="Confirmar ação" maxWidth="max-w-md" :footer="true">
        <div id="rec-action-message" class="text-sm text-gray-700">Tem certeza que deseja realizar esta ação?</div>
        <x-slot name="footer">
            <x-button color="secondary" onclick="closeModal('rec-action-modal')">Cancelar</x-button>
            <x-button id="rec-action-confirm" color="danger"><i class="fas fa-check mr-2"></i>Confirmar</x-button>
        </x-slot>
    </x-modal>
    
    @superadmin
    <div id="rec-logs" class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-3">
        <div class="text-sm font-semibold text-gray-800 mb-2">Logs das ações</div>
        <ul id="rec-logs-list" class="space-y-1 text-xs text-gray-700"></ul>
    </div>
    @endsuperadmin
</x-card>

@once
    <x-alert-system />
@endonce

<script>
function showTableLoading() {
    const container = document.getElementById('recorrencias-table-wrapper');
    if (!container) return;
    const overlay = container.querySelector('[data-loading-overlay]');
    if (overlay) overlay.classList.remove('hidden');
    container.classList.add('pointer-events-none');
}

function hideTableLoading() {
    const container = document.getElementById('recorrencias-table-wrapper');
    if (!container) return;
    const overlay = container.querySelector('[data-loading-overlay]');
    if (overlay) overlay.classList.add('hidden');
    container.classList.remove('pointer-events-none');
}

function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}


function appendLog(action, payload) {
    const list = document.getElementById('rec-logs-list');
    if (!list) return;
    const li = document.createElement('li');
    const ts = new Date().toLocaleString('pt-BR');
    li.className = 'border border-gray-200 bg-white rounded-md px-3 py-2';
    li.textContent = `[${ts}] ${action}: ${JSON.stringify(payload)}`;
    list.prepend(li);
}

async function updateRecorrenciasContainer(url) {
    const container = document.getElementById('recorrencias-table-wrapper');
    if (!container) { window.location.href = url; return; }
    const content = container.querySelector('[data-ajax-content]') || container;
    showTableLoading();
    try {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        });
        const text = await response.text();
        const doc = new DOMParser().parseFromString(text, 'text/html');
        const newContainer = doc.getElementById('recorrencias-table-wrapper') || doc.querySelector('[data-target="recorrencias-table-wrapper"]');
        if (newContainer) {
            const newContent = newContainer.querySelector('[data-ajax-content]') || newContainer;
            content.innerHTML = newContent.innerHTML;
            window.history.replaceState(null, '', url);
        } else {
            window.location.href = url;
        }
    } catch (e) {
        console.error('Erro AJAX na atualização de recorrências', e);
        window.location.href = url;
    } finally {
        hideTableLoading();
    }
}

function initRecorrenciasAjaxBindings() {
    const container = document.getElementById('recorrencias-table-wrapper');
    if (!container) return;
    container.addEventListener('click', function(e) {
        const anchor = e.target.closest('a');
        if (!anchor) return;
        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#')) return;
        const isPagination = href.includes('page=');
        const isSorting = href.includes('sort=') || href.includes('direction=');
        if (!isPagination && !isSorting) return;
        e.preventDefault();
        updateRecorrenciasContainer(href);
    });
}

function syncPeriodoInputs() {
    const deHidden = document.getElementById('de_hidden');
    const ateHidden = document.getElementById('ate_hidden');
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');

    if (deHidden && dataInicio) {
        deHidden.value = dataInicio.value || '';
    }
    if (ateHidden) {
        ateHidden.value = dataFim ? (dataFim.value || '') : '';
    }
}

// Ações e Modais
function formatCurrencyBRL(value) {
    try { return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(value)); } catch { return `R$ ${Number(value).toFixed(2)}`; }
}

function openRecDetails(btn) {
    const d = btn.dataset;
    document.getElementById('rec-det-start').textContent = d.start ? new Date(d.start).toLocaleDateString('pt-BR') : '-';
    document.getElementById('rec-det-end').textContent = d.end ? new Date(d.end).toLocaleDateString('pt-BR') : '-';
    document.getElementById('rec-det-day').textContent = d.day || '-';
    document.getElementById('rec-det-amount').textContent = formatCurrencyBRL(d.amount || 0);
    document.getElementById('rec-det-status').textContent = d.status ? (d.status.charAt(0).toUpperCase() + d.status.slice(1)) : '-';
    document.getElementById('rec-det-method').textContent = d.methodId || '-';
    showModal('rec-details-modal');
}

function openRecMethod(btn) {
    const d = btn.dataset;
    document.getElementById('rec-method-id').value = d.id;
    const select = document.getElementById('rec-method-select');
    if (select && d.methodId) { select.value = d.methodId; }
    showModal('rec-method-modal');
}

async function saveRecMethod() {
    const subId = document.getElementById('rec-method-id').value;
    const methodId = document.getElementById('rec-method-select').value;
    try {
            const res = await fetch(`/admin/recorrencias/${subId}/method`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ charge_method_id: parseInt(methodId, 10) })
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) { throw new Error(data.message || 'Falha ao atualizar método'); }
            closeModal('rec-method-modal');
            if (window.alertSystem) {
                window.alertSystem.success(data.message || 'Método de cobrança atualizado com sucesso');
            }
            appendLog('update-method', { subscriptionId: subId, methodId, result: 'success' });
            updateRecorrenciasContainer(window.location.href);
        } catch (e) {
            if (window.alertSystem) {
                window.alertSystem.error(e.message || 'Erro ao atualizar método de cobrança');
            }
            appendLog('update-method', { subscriptionId: subId, methodId, error: e.message || 'Erro desconhecido' });
        }
}

let currentRecAction = { id: null, action: null };
function openRecAction(btn, action) {
    const d = btn.dataset;
    currentRecAction = { id: d.id, action };
    const msgEl = document.getElementById('rec-action-message');
    const confirmBtn = document.getElementById('rec-action-confirm');
    if (action === 'pause') {
        msgEl.textContent = 'Tem certeza que deseja pausar esta recorrência?';
        confirmBtn.classList.remove('bg-red-600','hover:bg-red-700');
        confirmBtn.classList.add('bg-yellow-500','hover:bg-yellow-600');
    } else if (action === 'resume') {
        msgEl.textContent = 'Tem certeza que deseja retomar esta recorrência?';
        confirmBtn.classList.remove('bg-red-600','hover:bg-red-700');
        confirmBtn.classList.add('bg-green-600','hover:bg-green-700');
    } else {
        msgEl.textContent = 'Tem certeza que deseja cancelar esta recorrência?';
        confirmBtn.classList.remove('bg-yellow-500','hover:bg-yellow-600','bg-green-600','hover:bg-green-700');
        confirmBtn.classList.add('bg-red-600','hover:bg-red-700');
    }
    showModal('rec-action-modal');
}

document.addEventListener('DOMContentLoaded', function() {
    syncPeriodoInputs();
    initRecorrenciasAjaxBindings();
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');
    if (dataInicio) dataInicio.addEventListener('change', syncPeriodoInputs);
    if (dataFim) dataFim.addEventListener('change', syncPeriodoInputs);
    const confirmBtn = document.getElementById('rec-action-confirm');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', async function() {
            try {
                const { id, action } = currentRecAction;
                const res = await fetch(`/admin/recorrencias/${id}/${action}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) { throw new Error(data.message || 'Falha ao executar ação'); }
                closeModal('rec-action-modal');
                if (window.alertSystem) {
                    window.alertSystem.success(data.message || 'Ação executada com sucesso');
                }
                appendLog(currentRecAction.action, { subscriptionId: currentRecAction.id, result: 'success', response: data });
                updateRecorrenciasContainer(window.location.href);
            } catch (e) {
                if (window.alertSystem) {
                    window.alertSystem.error(e.message || 'Erro ao executar ação da recorrência');
                }
                appendLog(currentRecAction.action, { subscriptionId: currentRecAction.id, error: e.message || 'Erro desconhecido' });
            }
        });
    }
});
</script>
@endsection