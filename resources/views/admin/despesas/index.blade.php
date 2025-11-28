@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Despesas', 'url' => '#']
]" />

    <x-card>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Despesas</h1>
                <p class="mt-1 text-sm text-gray-600">Listagem e gerenciamento de despesas</p>
            </div>
            <x-button color="primary" onclick="showModal('create-despesa-modal')">
                <i class="fas fa-plus mr-1"></i> Nova Despesa
            </x-button>
        </div>

        <x-collapsible-filter 
            title="Filtros de Despesas" 
            :action="route('admin.despesas.index')" 
            :clear-route="route('admin.despesas.index')"
            target="despesas-table-wrapper"
        >
            <x-filter-field 
                name="status" 
                label="Status" 
                type="select"
                empty-option="Todos"
                value="pendente"
                :options="$statusOptions"
            />

            <x-filter-field 
                name="categoria" 
                label="Categoria" 
                type="text"
                placeholder="Buscar por categoria..."
            />

            <x-filter-field 
                name="descricao" 
                label="Descrição" 
                type="text"
                placeholder="Buscar por descrição..."
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
                <!-- Campos ocultos que mapeiam para os nomes esperados pelo controller -->
                <input type="hidden" name="de" id="de_hidden" value="{{ request('de') }}">
                <input type="hidden" name="ate" id="ate_hidden" value="{{ request('ate') }}">
            </div>
        </x-collapsible-filter>
        <div id="despesas-table-wrapper" class="relative">
            <x-loading-overlay message="Atualizando despesas..." />
            <div data-ajax-content>
        @if($despesas->count() > 0)
            <x-table 
                :headers="[
                    ['label' => 'Data', 'sort' => 'data'],
                    ['label' => 'Descrição', 'sort' => 'descricao'],
                    ['label' => 'Categoria', 'sort' => 'categoria'],
                    ['label' => 'Valor', 'sort' => 'valor'],
                    ['label' => 'Status', 'sort' => 'status'],
                ]" 
                :actions="true" 
                striped 
                hover 
                responsive 
                sortable 
                :currentSort="request('sort')" 
                :currentDirection="request('direction', 'desc')"
            >
                @foreach($despesas as $despesa)
                    <x-table-row :index="$loop->index">
                        <x-table-cell>
                            <span class="text-sm text-gray-700">{{ $despesa->data ? \Carbon\Carbon::parse($despesa->data)->format('d/m/Y') : '-' }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="font-medium text-gray-900">{{ $despesa->descricao }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm text-gray-700">{{ $despesa->categoria ?? '-' }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm font-semibold text-gray-900">R$ {{ number_format((float) $despesa->valor, 2, ',', '.') }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            @php
                                $statusStyles = [
                                    'pendente' => 'bg-yellow-100 text-yellow-800',
                                    'liquidada' => 'bg-green-100 text-green-800',
                                    'cancelada' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusStyles[$despesa->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($despesa->status) }}
                            </span>
                        </x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex justify-end items-center space-x-2">
                                <x-button color="warning" size="sm" title="Editar" onclick="openEditDespesa({{ $despesa->id }})">
                                    <i class="fas fa-edit"></i>
                                </x-button>
                                @if($despesa->status !== 'cancelada')
                                    <x-button color="danger" size="sm" title="Cancelar" onclick="confirmCancelDespesa({{ $despesa->id }})">
                                        <i class="fas fa-ban"></i>
                                    </x-button>
                                @endif
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @endforeach
            </x-table>

            <div class="mt-6">
                {{ $despesas->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-receipt text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma despesa encontrada</h3>
                <p class="text-gray-600 mb-4">
                    @if(request()->hasAny(['status','categoria','descricao','de','ate']))
                        Nenhuma despesa corresponde aos filtros aplicados.
                    @else
                        Comece criando sua primeira despesa.
                    @endif
                </p>
                <x-button color="primary" onclick="showModal('create-despesa-modal')">
                    <i class="fas fa-plus mr-1"></i> Nova Despesa
                </x-button>
            </div>
        @endif
            </div>
        </div>
    </x-card>

    <!-- Modal: Nova Despesa -->
    <x-modal id="create-despesa-modal" title="Nova Despesa" maxWidth="max-w-2xl">
        <form method="POST" action="{{ route('admin.despesas.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="create_descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                <x-input type="text" id="create_descricao" name="descricao" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ex.: Compra de materiais" />
            </div>
            <div>
                <label for="create_categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
                <x-input type="text" id="create_categoria" name="categoria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ex.: Materiais" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="create_data" class="block text-sm font-medium text-gray-700">Data</label>
                    <x-input type="date" id="create_data" name="data" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <div>
                    <label for="create_valor" class="block text-sm font-medium text-gray-700">Valor</label>
                    <x-input type="number" step="0.01" min="0" id="create_valor" name="valor" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="0,00" />
                </div>
                <div>
                    <label for="create_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <x-select id="create_status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="pendente" selected>Pendente</option>
                        <option value="liquidada">Liquidada</option>
                    </x-select>
                </div>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <x-button color="secondary" type="button" onclick="closeModal('create-despesa-modal')">Fechar</x-button>
                <x-button color="primary" type="submit"><i class="fas fa-save mr-1"></i> Salvar</x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal: Editar Despesa -->
    <x-modal id="edit-despesa-modal" title="Editar Despesa" maxWidth="max-w-2xl">
        <form id="editDespesaForm" class="space-y-4" onsubmit="submitDespesaUpdate(event)">
            <x-input type="hidden" id="edit_update_url" value="" />
            <div>
                <label for="edit_descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                <x-input type="text" id="edit_descricao" name="descricao" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
                <label for="edit_categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
                <x-input type="text" id="edit_categoria" name="categoria" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="edit_data" class="block text-sm font-medium text-gray-700">Data</label>
                    <x-input type="date" id="edit_data" name="data" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <div>
                    <label for="edit_valor" class="block text-sm font-medium text-gray-700">Valor</label>
                    <x-input type="number" step="0.01" min="0" id="edit_valor" name="valor" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <div>
                    <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="edit_status" name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="pendente">Pendente</option>
                        <option value="liquidada">Liquidada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <x-button color="secondary" type="button" onclick="closeModal('edit-despesa-modal')">Fechar</x-button>
                <x-button color="primary" type="submit"><i class="fas fa-save mr-1"></i> Salvar Alterações</x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal de confirmação para cancelamento com motivo -->
    <x-confirmation-modal 
        id="despesa-cancel-modal"
        title="Cancelar Despesa"
        message="Informe o motivo do cancelamento e confirme."
        confirmText="Cancelar"
        cancelText="Fechar"
        confirmColor="red"
        :showInput="true"
        inputLabel="Motivo do cancelamento"
        inputPlaceholder="Descreva o motivo"
        :inputRequired="true"
    />

    <script>
        // ===== AJAX: Ordenação & Paginação no wrapper =====
        function showTableLoading() {
            const container = document.getElementById('despesas-table-wrapper');
            if (!container) return;
            const overlay = container.querySelector('[data-loading-overlay]');
            if (overlay) overlay.classList.remove('hidden');
            container.classList.add('pointer-events-none');
        }

        function hideTableLoading() {
            const container = document.getElementById('despesas-table-wrapper');
            if (!container) return;
            const overlay = container.querySelector('[data-loading-overlay]');
            if (overlay) overlay.classList.add('hidden');
            container.classList.remove('pointer-events-none');
        }

        async function updateDespesasContainer(url) {
            const container = document.getElementById('despesas-table-wrapper');
            if (!container) { window.location.href = url; return; }
            const content = container.querySelector('[data-ajax-content]') || container;
            showTableLoading();
            try {
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                });
                const text = await response.text();
                const doc = new DOMParser().parseFromString(text, 'text/html');
                const newContainer = doc.getElementById('despesas-table-wrapper') || doc.querySelector('[data-target="despesas-table-wrapper"]');
                if (newContainer) {
                    const newContent = newContainer.querySelector('[data-ajax-content]') || newContainer;
                    content.innerHTML = newContent.innerHTML;
                    window.history.replaceState(null, '', url);
                } else {
                    window.location.href = url;
                }
            } catch (e) {
                console.error('Erro AJAX na atualização de despesas', e);
                window.location.href = url;
            } finally {
                hideTableLoading();
            }
        }

        function initDespesaAjaxBindings() {
            const container = document.getElementById('despesas-table-wrapper');
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
                updateDespesasContainer(href);
            });
        }

        // Sincronizar inputs de período com nomes esperados pelo controller
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
            // Inicial sync
            syncPeriodoInputs();
            // Bind interceptors
            initDespesaAjaxBindings();

            // Atualizar mapeamento quando datas mudarem
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');
            if (dataInicio) dataInicio.addEventListener('change', syncPeriodoInputs);
            if (dataFim) dataFim.addEventListener('change', syncPeriodoInputs);
        });

        // Abrir modal de edição e carregar dados via AJAX
        async function openEditDespesa(id) {
            try {
                const url = "{{ route('admin.despesas.modal-edit', ':id') }}".replace(':id', id);
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const result = await response.json();
                if (!result.success) {
                    alert(result.message || 'Erro ao carregar despesa.');
                    return;
                }

                const data = result.data;
                document.getElementById('edit_descricao').value = data.descricao || '';
                document.getElementById('edit_categoria').value = data.categoria || '';
                document.getElementById('edit_data').value = data.data || '';
                document.getElementById('edit_valor').value = data.valor || '';
                document.getElementById('edit_status').value = data.status || 'pendente';
                document.getElementById('edit_update_url').value = data.update_url;

                showModal('edit-despesa-modal');
            } catch (e) {
                console.error(e);
                alert('Erro inesperado ao abrir modal de edição.');
            }
        }

        // Submeter atualização via AJAX
        async function submitDespesaUpdate(event) {
            event.preventDefault();
            const form = document.getElementById('editDespesaForm');
            const updateUrl = document.getElementById('edit_update_url').value;
            if (!updateUrl) {
                alert('URL de atualização não definida.');
                return;
            }

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const formData = new FormData();
            formData.append('descricao', document.getElementById('edit_descricao').value);
            formData.append('categoria', document.getElementById('edit_categoria').value);
            formData.append('data', document.getElementById('edit_data').value);
            formData.append('valor', document.getElementById('edit_valor').value);
            formData.append('status', document.getElementById('edit_status').value);
            formData.append('_method', 'PUT');

            try {
                const response = await fetch(updateUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token },
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    closeModal('edit-despesa-modal');
                    window.location.reload();
                } else {
                    alert(result.message || 'Erro ao atualizar despesa.');
                }
            } catch (e) {
                console.error(e);
                alert('Erro inesperado ao atualizar despesa.');
            }
        }

        // Cancelar despesa com confirmação e motivo
        function confirmCancelDespesa(id) {
            showConfirmation({
                callback: function(reason) {
                    if (!reason || !reason.trim()) {
                        alert('Informe o motivo do cancelamento.');
                        return;
                    }
                    sendCancelDespesa(id, reason);
                }
            });
        }

        async function sendCancelDespesa(id, reason) {
            try {
                const url = "{{ route('admin.despesas.cancel', ':id') }}".replace(':id', id);
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ reason })
                });
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Erro ao cancelar despesa.');
                }
            } catch (e) {
                console.error(e);
                alert('Erro inesperado ao cancelar despesa.');
            }
        }
    </script>
@endsection