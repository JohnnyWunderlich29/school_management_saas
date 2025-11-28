@extends('layouts.app')

@section('title', 'Recebimentos')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => route('dashboard')],
    ['title' => 'Receitas', 'url' => route('admin.recebimentos.index')]
]" />

<x-card>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Receitas</h1>
            <p class="mt-1 text-sm text-gray-600">Listagem e gerenciamento de faturas de clientes</p>
        </div>
    </div>

    <x-collapsible-filter 
        title="Filtros de Recebimentos" 
        :action="route('admin.recebimentos.index')" 
        :clear-route="route('admin.recebimentos.index')"
        target="recebimentos-table-wrapper"
    >
        <x-filter-field 
            name="status" 
            label="Status" 
            type="select"
            empty-option="Todos"
            value="pending"
            :options="$statusOptions"
        />

        <x-filter-field 
            name="gateway" 
            label="Gateway" 
            type="select"
            empty-option="Todos"
            :options="$gatewayOptions"
        />

        <div>
            <x-date-filter-with-arrows 
                title="Período" 
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

    <div id="recebimentos-table-wrapper" class="relative">
        <x-loading-overlay message="Atualizando recebimentos..." />
        <div data-ajax-content>
        @if($recebimentos->count() > 0)
            <x-table 
                :headers="[
                    ['label' => 'Vencimento', 'sort' => 'due_date'],
                    ['label' => 'Fatura', 'sort' => 'number'],
                    ['label' => 'Pagador', 'sort' => 'payer'],
                    ['label' => 'Gateway', 'sort' => 'gateway_alias'],
                    ['label' => 'Valor', 'sort' => 'total_cents'],
                    ['label' => 'Status', 'sort' => 'status'],
                    ['label' => 'Ações'],
                ]" 
                :actions="false" 
                striped 
                hover 
                responsive 
                sortable 
                :currentSort="request('sort')" 
                :currentDirection="request('direction', 'desc')"
            >
                @foreach($recebimentos as $rec)
                    <x-table-row :index="$loop->index">
                        <x-table-cell>
                            <div class="text-sm text-gray-700">
                                {{ $rec->due_date ? \Illuminate\Support\Carbon::parse($rec->due_date)->format('d/m/Y') : '-' }}
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm text-gray-700">#{{ $rec->number ?? '-' }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            @if(!empty($rec->payer_id) && !empty($rec->payer_nome))
                                <a href="{{ route('responsaveis.show', $rec->payer_id) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium" title="Ver cadastro do responsável">{{ trim(($rec->payer_nome ?? '') . ' ' . ($rec->payer_sobrenome ?? '')) }}</a>
                            @else
                                <span class="text-sm text-gray-500">-</span>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                <span>{{ $rec->gateway_alias ?? '-' }}</span>
                                <button type="button" class="text-indigo-600 hover:text-indigo-800" title="Abrir boleto" onclick="openBoleto({{ $rec->id }})">
                                    <i class="fas fa-barcode"></i>
                                </button>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm text-gray-700">
                                R$ {{ number_format(($rec->total_cents ?? 0)/100, 2, ',', '.') }}
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm font-medium">
                                @php $statusClass = match($rec->status) {
                                    'paid' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'overdue' => 'bg-red-100 text-red-800',
                                    'canceled' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                }; @endphp
                                <span id="inv-status-{{ $rec->id }}" data-status="{{ $rec->status }}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                    {{ ['pending'=>'Pendente','paid'=>'Pago','overdue'=>'Vencida','canceled'=>'Cancelado','failed'=>'Falhou'][$rec->status] ?? ($rec->status ?? '-') }}
                                </span>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            @permission('finance.admin')
                            <div class="flex items-center gap-2">
                                <x-button size="sm" color="secondary" title="Visualizar fatura" onclick="viewInvoiceDetails({{ $rec->id }})">
                                    <i class="fas fa-eye"></i>
                                </x-button>

                                @if(in_array($rec->status, ['pending', 'overdue']))
                                    <!-- Reenviar cobrança -->
                                    <x-button size="sm" color="info" title="Reenviar cobrança" onclick="openInvAction(this, 'resend', {{ $rec->id }})">
                                        <i class="fas fa-paper-plane"></i>
                                    </x-button>

                                    <!-- Marcar como pago -->
                                    <x-button size="sm" color="success" title="Marcar como pago" onclick="openInvAction(this, 'mark-paid', {{ $rec->id }})">
                                        <i class="fas fa-check-circle"></i>
                                    </x-button>

                                    <!-- Cancelar fatura -->
                                    <x-button size="sm" color="danger" title="Cancelar fatura" onclick="openInvAction(this, 'cancel', {{ $rec->id }})">
                                        <i class="fas fa-times"></i>
                                    </x-button>
                                @endif

                                @if($rec->status === 'paid')
                                    <x-button size="sm" color="warning" title="Estornar pagamento" onclick="openInvAction(this, 'refund', {{ $rec->id }})">
                                        <i class="fas fa-undo"></i>
                                    </x-button>
                                @endif
                            </div>
                            @endpermission
                        </x-table-cell>
                    </x-table-row>
                @endforeach
            </x-table>

            <div class="mt-6">
                {{ $recebimentos->links('components.pagination') }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-file-invoice-dollar text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma fatura encontrada</h3>
                <p class="text-gray-600 mb-4">
                    @if(request()->hasAny(['status','gateway','de','ate']))
                        Nenhuma fatura corresponde aos filtros aplicados.
                    @else
                        Não há faturas registradas ainda.
                    @endif
                </p>
            </div>
        @endif
        </div>
    </div>
</x-card>

<!-- Modais no padrão de Recorrências -->
<x-modal id="inv-details-modal" title="Detalhes da Fatura" maxWidth="max-w-2xl">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <div class="text-gray-500">Número</div>
            <div id="inv-det-number" class="font-medium text-gray-900">-</div>
        </div>
        <div>
            <div class="text-gray-500">Status</div>
            <div id="inv-det-status" class="font-medium text-gray-900">-</div>
        </div>
        <div>
            <div class="text-gray-500">Valor</div>
            <div id="inv-det-total" class="font-medium text-gray-900">-</div>
        </div>
        <div>
            <div class="text-gray-500">Vencimento</div>
            <div id="inv-det-due" class="font-medium text-gray-900">-</div>
        </div>
        <div>
            <div class="text-gray-500">Gateway</div>
            <div id="inv-det-gateway" class="font-medium text-gray-900">-</div>
        </div>
        <div>
            <div class="text-gray-500">Status no Gateway</div>
            <div id="inv-det-gateway-status" class="font-medium text-gray-900">-</div>
        </div>
    </div>
    <div id="inv-det-extra" class="mt-4 space-y-2"></div>
</x-modal>

<x-modal id="inv-action-modal" title="Confirmar ação" maxWidth="max-w-md">
    <p id="inv-action-message" class="text-sm text-gray-600"></p>
    <x-slot name="footer">
        <div class="flex justify-end gap-2">
            <x-button variant="secondary" onclick="closeModal('inv-action-modal')">Cancelar</x-button>
            <x-button id="inv-action-confirm" color="danger"><i class="fas fa-check mr-2"></i>Confirmar</x-button>
        </div>
    </x-slot>
</x-modal>

<!-- Novo modal: Marcar como pago -->
<x-modal id="inv-pay-modal" title="Marcar fatura como paga" maxWidth="max-w-lg">
    <x-slot name="header">
        <h3 class="text-lg font-medium text-gray-900">
            Marcar fatura como paga - <span id="pay-modal-desc" class="font-semibold">&nbsp;</span>
        </h3>
    </x-slot>

    <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input id="pay-inv-number" name="pay-inv-number" label="Fatura" disabled class="bg-gray-100" />
            <x-input id="pay-payer-input" name="pay-payer-input" label="Pagador" disabled class="bg-gray-100" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-input id="pay-date" name="pay-date" type="datetime-local" label="Data do pagamento" required />
            <x-select id="pay-method" name="pay-method" label="Método" :options="['cash' => 'Dinheiro', 'pix' => 'PIX', 'transfer' => 'Transferência', 'boleto' => 'Boleto', 'credit_card' => 'Cartão de crédito']" selected="cash" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input id="pay-amount" name="pay-amount" label="Valor pago" placeholder="Ex.: 1.234,56" />
                <small id="pay-amount-hint" class="text-xs text-gray-500"></small>
            </div>
        </div>

        <div>
            <x-textarea id="pay-description" name="pay-description" label="Descrição" rows="3" placeholder="Observações do pagamento" />
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex justify-end gap-2">
            <x-button variant="secondary" onclick="closeModal('inv-pay-modal')">Cancelar</x-button>
            <x-button id="pay-confirm" color="success"><i class="fas fa-check mr-2"></i>Confirmar pagamento</x-button>
        </div>
    </x-slot>
</x-modal>
@superadmin
<x-card class="mt-6">
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Console de Requests</h3>
        </div>
    </x-slot>
    <div id="request-logs" class="text-sm font-mono whitespace-pre-wrap bg-gray-50 p-3 rounded border border-gray-200 h-48 overflow-auto"></div>
</x-card>
@endsuperadmin

@push('scripts')
<script>
let requestLogEl = null;
// Determinar o school_id para chamadas à API financeira
const SCHOOL_ID = (function() {
    try {
        return parseInt(@json(optional(auth()->user())->escola_id ?? optional(auth()->user())->school_id ?? session('escola_atual') ?? 0), 10) || null;
    } catch (e) {
        return null;
    }
})();
function appendLog(title, payload) {
    if (!requestLogEl) requestLogEl = document.getElementById('request-logs');
    if (!requestLogEl) return;
    const time = new Date().toLocaleTimeString();
    const body = payload ? JSON.stringify(payload) : '';
    requestLogEl.textContent += `[${time}] ${title}${body ? ' ' + body : ''}\n`;
    requestLogEl.scrollTop = requestLogEl.scrollHeight;
}

// Mapeamentos de status em PT-BR e classes de badge
const STATUS_LABEL_PT = {
    pending: 'Pendente',
    paid: 'Pago',
    overdue: 'Vencida',
    canceled: 'Cancelado',
    failed: 'Falhou'
};
const STATUS_CLASS = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
    canceled: 'bg-gray-100 text-gray-800',
    failed: 'bg-red-100 text-red-800'
};

function setStatusBadge(invoiceId, newStatus) {
    const el = document.getElementById(`inv-status-${invoiceId}`);
    if (!el) return;
    el.classList.remove('bg-yellow-100','text-yellow-800','bg-green-100','text-green-800','bg-red-100','text-red-800','bg-gray-100','text-gray-800');
    const cls = STATUS_CLASS[newStatus] || 'bg-gray-100 text-gray-800';
    cls.split(' ').forEach(c => el.classList.add(c));
    el.textContent = STATUS_LABEL_PT[newStatus] || (newStatus ? newStatus : '-');
    el.setAttribute('data-status', newStatus || '');
}

// Função para visualizar detalhes da fatura
async function viewInvoiceDetails(invoiceId) {
    currentInvoiceId = invoiceId;
    const numberEl = document.getElementById('inv-det-number');
    const statusEl = document.getElementById('inv-det-status');
    const totalEl = document.getElementById('inv-det-total');
    const dueEl = document.getElementById('inv-det-due');
    const gatewayEl = document.getElementById('inv-det-gateway');
    const gwStatusEl = document.getElementById('inv-det-gateway-status');
    const extraEl = document.getElementById('inv-det-extra');

    [numberEl, statusEl, totalEl, dueEl, gatewayEl, gwStatusEl].forEach(el => el && (el.textContent = '-'));
    if (extraEl) extraEl.innerHTML = '';

    showModal('inv-details-modal');

    try {
        const response = await fetch(`/admin/recebimentos/${invoiceId}/details`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });
        if (!response.ok) {
            const text = await response.text().catch(() => '');
            console.error('HTTP error ao buscar detalhes da fatura', response.status, text.slice(0, 300));
            throw new Error('HTTP error');
        }
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const html = await response.text().catch(() => '');
            console.error('Resposta não-JSON recebida nos detalhes da fatura:\n', html.slice(0, 300));
            throw new Error('Resposta não-JSON do endpoint de detalhes');
        }
        const data = await response.json();

        if (numberEl) numberEl.textContent = data.number ? `#${data.number}` : '-';
        if (statusEl) statusEl.textContent = STATUS_LABEL_PT[data.status] || (data.status || '-');
        if (totalEl) totalEl.textContent = data.total_cents != null ? formatCurrencyBRL(data.total_cents) : '-';
        if (dueEl) dueEl.textContent = data.due_date || '-';
        if (gatewayEl) gatewayEl.textContent = data.gateway_alias || '-';
        if (gwStatusEl) gwStatusEl.textContent = data.gateway_status || '-';

        if (extraEl) {
            let html = '';
            if (data.boleto_url) {
                html += `<div><a href="${data.boleto_url}" target="_blank" class="text-blue-600 hover:text-blue-800">Ver Boleto</a></div>`;
            }
            if (data.pix_qr_code) {
                html += `<div><strong>PIX QR Code:</strong><br><img src="data:image/png;base64,${data.pix_qr_code}" class="mt-2 max-w-xs"></div>`;
            }
            extraEl.innerHTML = html;
        }
        appendLog('Visualizou detalhes', { invoice: data.number, status: data.status });
    } catch (error) {
        window.alertSystem?.error('Erro ao carregar detalhes da fatura.');
        console.error('Erro:', error);
    }
}

// Helpers e fluxo de ações
let currentInvAction = { id: null, action: null };

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function formatCurrencyBRL(valueCents) {
    const val = (Number(valueCents || 0) / 100).toFixed(2).replace('.', ',');
    return 'R$ ' + val.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

async function openBoleto(invoiceId) {
    try {
        const response = await fetch(`/admin/recebimentos/${invoiceId}/details`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) throw new Error('Resposta não é JSON');
        const data = await response.json();
        if (data && data.boleto_url) {
            window.open(data.boleto_url, '_blank', 'noopener');
            if (typeof appendLog === 'function') {
                appendLog('Abriu boleto', { invoice: data.number || invoiceId });
            }
        } else {
            window.alertSystem?.info?.('Boleto indisponível para esta fatura.');
        }
    } catch (err) {
        console.error('Erro ao abrir boleto:', err);
        window.alertSystem?.error?.('Não foi possível abrir o boleto.');
    }
}

function openInvAction(el, action, invoiceId) {
    currentInvoiceId = invoiceId;
    currentInvAction = { id: invoiceId, action };
    if (action === 'mark-paid') {
        prefillPayModal(invoiceId).then(() => showModal('inv-pay-modal'));
        return;
    }
    const msgMap = {
        'resend': 'Deseja reenviar a cobrança desta fatura?',
        'cancel': 'Deseja cancelar esta fatura? Esta ação não pode ser desfeita.',
        'refund': 'Deseja estornar o pagamento desta fatura?'
    };
    const msgEl = document.getElementById('inv-action-message');
    if (msgEl) msgEl.textContent = msgMap[action] || 'Confirmar ação?';
    const btn = document.getElementById('inv-action-confirm');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>Confirmar';
        btn.disabled = false;
        const remove = ['bg-red-600','hover:bg-red-700','bg-green-600','hover:bg-green-700','bg-yellow-500','hover:bg-yellow-600','bg-blue-600','hover:bg-blue-700'];
        btn.classList.remove(...remove);
        const addMap = {
            'resend': ['bg-blue-600','hover:bg-blue-700'],
            'cancel': ['bg-red-600','hover:bg-red-700'],
            'refund': ['bg-yellow-500','hover:bg-yellow-600']
        };
        const add = addMap[action] || ['bg-red-600','hover:bg-red-700'];
        btn.classList.add(...add);
    }
    showModal('inv-action-modal');
}

function showTableLoading() {
    const container = document.getElementById('recebimentos-table-wrapper');
    if (!container) return;
    const overlay = container.querySelector('[data-loading-overlay]');
    if (overlay) overlay.classList.remove('hidden');
    container.classList.add('pointer-events-none');
}

function hideTableLoading() {
    const container = document.getElementById('recebimentos-table-wrapper');
    if (!container) return;
    const overlay = container.querySelector('[data-loading-overlay]');
    if (overlay) overlay.classList.add('hidden');
    container.classList.remove('pointer-events-none');
}

async function updateRecebimentosContainer(url) {
    const container = document.getElementById('recebimentos-table-wrapper');
    if (!container) { window.location.href = url; return; }
    const content = container.querySelector('[data-ajax-content]') || container;
    showTableLoading();
    try {
        const response = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            credentials: 'same-origin'
        });
        const text = await response.text();
        const doc = new DOMParser().parseFromString(text, 'text/html');
        const newContainer = doc.getElementById('recebimentos-table-wrapper') || doc.querySelector('[data-target="recebimentos-table-wrapper"]');
        if (newContainer) {
            const newContent = newContainer.querySelector('[data-ajax-content]') || newContainer;
            content.innerHTML = newContent.innerHTML;
            window.history.replaceState(null, '', url);
        } else {
            window.location.href = url;
        }
    } catch (e) {
        console.error('Erro AJAX na atualização de recebimentos', e);
        window.location.href = url;
    } finally {
        hideTableLoading();
    }
}

function initRecebimentosAjaxBindings() {
    const container = document.getElementById('recebimentos-table-wrapper');
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
        updateRecebimentosContainer(href);
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

document.addEventListener('DOMContentLoaded', function() {
    initRecebimentosAjaxBindings();
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');
    if (dataInicio) dataInicio.addEventListener('change', syncPeriodoInputs);
    if (dataFim) dataFim.addEventListener('change', syncPeriodoInputs);

    const confirmBtn = document.getElementById('inv-action-confirm');
    if (confirmBtn) {
        // Listener movido para bindActionConfirm() para evitar duplicidade de requisições.
        // A vinculação agora é feita no final do arquivo em bindActionConfirm().
    }
});
let invoiceDetailsCache = {};

function nowLocalInputValue() {
    const d = new Date();
    const pad = n => String(n).padStart(2, '0');
    const y = d.getFullYear();
    const m = pad(d.getMonth() + 1);
    const day = pad(d.getDate());
    const h = pad(d.getHours());
    const min = pad(d.getMinutes());
    return `${y}-${m}-${day}T${h}:${min}`;
}

function formatDateTimeLocalToSql(dtLocal) {
    // dtLocal formato esperado: YYYY-MM-DDTHH:mm
    if (!dtLocal || typeof dtLocal !== 'string' || !dtLocal.includes('T')) return null;
    const [date, time] = dtLocal.split('T');
    return `${date} ${time}:00`;
}

function parseCurrencyBRLToCents(value) {
    if (value == null) return null;
    let s = String(value).trim();
    s = s.replace(/[^0-9,.-]/g, '');
    // Converte vírgula decimal para ponto
    const parts = s.split(',');
    if (parts.length > 1) {
        s = parts[0].replace(/\./g, '') + '.' + parts[1];
    } else {
        s = s.replace(/\./g, '');
    }
    const num = Number(s);
    if (isNaN(num)) return null;
    return Math.round(num * 100);
}

async function getInvoiceDetails(invoiceId) {
    if (invoiceDetailsCache[invoiceId]) return invoiceDetailsCache[invoiceId];
    const response = await fetch(`/admin/recebimentos/${invoiceId}/details`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    });
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    const data = await response.json();
    invoiceDetailsCache[invoiceId] = data;
    return data;
}

async function prefillPayModal(invoiceId) {
    try {
        const data = await getInvoiceDetails(invoiceId);
        // Fatura
        const invEl = document.getElementById('pay-inv-number');
        if (invEl) invEl.value = data.number ? `#${data.number}` : `#${invoiceId}`;

        // Pagador (nome + sobrenome)
        const payerName = [data.payer_nome, data.payer_sobrenome].filter(Boolean).join(' ').trim();
        const payerInput = document.getElementById('pay-payer-input');
        if (payerInput) payerInput.value = payerName || '-';

        // Título com descrição
        const desc = data.descricao || data.description || '';
        const titleDesc = document.getElementById('pay-modal-desc');
        if (titleDesc) titleDesc.textContent = desc || '';

        // Defaults dos campos
        const payDateEl = document.getElementById('pay-date');
        if (payDateEl) payDateEl.value = nowLocalInputValue();
        const payMethodEl = document.getElementById('pay-method');
        if (payMethodEl) payMethodEl.value = 'cash';

        const payAmountEl = document.getElementById('pay-amount');
        const hintEl = document.getElementById('pay-amount-hint');
        const totalCents = Number(data.total_cents || 0);
        if (payAmountEl) payAmountEl.value = totalCents ? formatCurrencyBRL(totalCents) : '';
        if (hintEl) hintEl.textContent = totalCents ? `Valor sugerido: ${formatCurrencyBRL(totalCents)}` : '';

        // Limpa descrição
        const payDescEl = document.getElementById('pay-description');
        if (payDescEl) payDescEl.value = '';
        appendLog('Abriu modal de pagamento', { invoice: data.number || invoiceId });
    } catch (e) {
        console.error('Erro ao prefill do modal de pagamento', e);
        window.alertSystem?.error('Não foi possível carregar os detalhes da fatura.');
    }
}

function openInvAction(el, action, invoiceId) {
    currentInvoiceId = invoiceId;
    currentInvAction = { id: invoiceId, action };
    if (action === 'mark-paid') {
        prefillPayModal(invoiceId).then(() => showModal('inv-pay-modal'));
        return;
    }
    const msgMap = {
        'resend': 'Deseja reenviar a cobrança desta fatura?',
        'cancel': 'Deseja cancelar esta fatura? Esta ação não pode ser desfeita.',
        'refund': 'Deseja estornar o pagamento desta fatura?'
    };
    const msgEl = document.getElementById('inv-action-message');
    if (msgEl) msgEl.textContent = msgMap[action] || 'Confirmar ação?';
    const btn = document.getElementById('inv-action-confirm');
    if (btn) {
        btn.innerHTML = '<i class="fas fa-check mr-2"></i>Confirmar';
        btn.disabled = false;
        const remove = ['bg-red-600','hover:bg-red-700','bg-green-600','hover:bg-green-700','bg-yellow-500','hover:bg-yellow-600','bg-blue-600','hover:bg-blue-700'];
        btn.classList.remove(...remove);
        const addMap = {
            'resend': ['bg-blue-600','hover:bg-blue-700'],
            'cancel': ['bg-red-600','hover:bg-red-700'],
            'refund': ['bg-yellow-500','hover:bg-yellow-600']
        };
        const add = addMap[action] || ['bg-red-600','hover:bg-red-700'];
        btn.classList.add(...add);
    }
    showModal('inv-action-modal');
}

// Submeter pagamento
function bindPayConfirm() {
    const btn = document.getElementById('pay-confirm');
    if (!btn) return;
    btn.addEventListener('click', async () => {
        if (!currentInvAction.id) return;
        const invoiceId = currentInvAction.id;
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
        btn.disabled = true;
        try {
            const dtLocal = document.getElementById('pay-date').value;
            const paidAt = formatDateTimeLocalToSql(dtLocal);
            const description = document.getElementById('pay-description').value || '';
            const amtText = document.getElementById('pay-amount').value;
            const amountCents = parseCurrencyBRLToCents(amtText);
            const method = document.getElementById('pay-method').value || 'cash';

            if (!paidAt) {
                window.alertSystem?.error('Informe a data do pagamento.');
                return;
            }
            if (!amountCents || amountCents <= 0) {
                window.alertSystem?.error('Informe um valor pago válido.');
                return;
            }

            const body = {
                invoice_id: invoiceId,
                amount_paid_cents: amountCents,
                method,
                status: 'confirmed',
                description,
                paid_at: paidAt,
                school_id: SCHOOL_ID
            };

            appendLog('Enviando pagamento', body);
            const response = await fetch(`/api/v1/finance/payments`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(body)
            });

            const contentType = response.headers.get('content-type') || '';
            let data = {};
            if (contentType.includes('application/json')) {
                data = await response.json().catch(() => ({}));
            } else {
                const text = await response.text().catch(() => '');
                console.warn('Resposta não JSON recebida na ação:', text.slice(0, 300));
                window.alertSystem?.error('Não foi possível processar a resposta do servidor.');
                throw new Error('Resposta não JSON');
            }

            if (response.ok && (data.success ?? true)) {
                window.alertSystem?.success(data.message || 'Pagamento registrado com sucesso!');
                setStatusBadge(invoiceId, 'paid');
                closeModal('inv-pay-modal');
                updateRecebimentosContainer(window.location.href);
                appendLog('Pagamento confirmado', { invoice_id: invoiceId });
            } else {
                window.alertSystem?.error(data.message || 'Erro ao registrar pagamento.');
                appendLog('Falha ao confirmar pagamento', { invoice_id: invoiceId, message: data.message });
            }
        } catch (err) {
            console.error('Erro ao enviar pagamento', err);
            window.alertSystem?.error('Erro de conexão ao registrar pagamento.');
        } finally {
            btn.innerHTML = original;
            btn.disabled = false;
        }
    });
}

// Ajustar confirm de ações para não tratar mark-paid aqui
function bindActionConfirm() {
    const confirmBtn = document.getElementById('inv-action-confirm');
    if (!confirmBtn) return;
    confirmBtn.addEventListener('click', async () => {
        if (!currentInvAction.id || !currentInvAction.action) return;
        const { id, action } = currentInvAction;
        if (action === 'mark-paid') {
            // Não processar aqui; este caso usa o inv-pay-modal
            return;
        }
        const originalHtml = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
        confirmBtn.disabled = true;
        try {
            let url = '';
            let body = {};
            if (action === 'cancel') {
                url = `/api/v1/finance/invoices/${id}/cancel`;
                body = { school_id: SCHOOL_ID };
            } else if (action === 'resend') {
                url = `/api/v1/finance/invoices/${id}/resend-email`;
                body = { school_id: SCHOOL_ID };
            } else if (action === 'refund') {
                closeModal('inv-action-modal');
                window.alertSystem?.info('Ação ainda não disponível neste módulo.');
                return;
            } else {
                closeModal('inv-action-modal');
                window.alertSystem?.error('Ação desconhecida.');
                return;
            }
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(body)
            });
            const contentType = response.headers.get('content-type') || '';
            let data = {};
            if (contentType.includes('application/json')) {
                data = await response.json().catch(() => ({}));
            } else {
                const text = await response.text().catch(() => '');
                console.warn('Resposta não JSON recebida na ação:', text.slice(0, 300));
                window.alertSystem?.error('Não foi possível processar a resposta do servidor.');
                throw new Error('Resposta não JSON');
            }
            if (response.ok && (data.success ?? true)) {
                window.alertSystem?.success(data.message || 'Ação realizada com sucesso!');
                if (action === 'cancel') setStatusBadge(id, 'canceled');
                closeModal('inv-action-modal');
                updateRecebimentosContainer(window.location.href);
            } else {
                window.alertSystem?.error(data.message || 'Erro ao realizar a ação.');
            }
        } catch (error) {
            window.alertSystem?.error('Erro de conexão. Tente novamente.');
        } finally {
            confirmBtn.innerHTML = originalHtml;
            confirmBtn.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // ... existing code ...
    bindPayConfirm();
    bindActionConfirm();
});
</script>
@endpush
@endsection
