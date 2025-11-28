@extends('layouts.app')

@section('title', 'Reservas - Biblioteca Digital')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Biblioteca', 'url' => route('biblioteca.index')],
    ['title' => 'Reservas', 'url' => '#']
]" />

<x-card>
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Gestão de Reservas</h1>
            <p class="mt-1 text-sm text-gray-600">Acompanhe reservas e ações de processamento/cancelamento</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <x-button color="primary" class="w-full sm:w-auto" x-data="{}" @click="$dispatch('open-modal', 'nova-reserva-modal')">
                <i class="fas fa-plus mr-2"></i>
                <span class="hidden md:inline">Nova Reserva</span>
                <span class="md:hidden">Nova</span>
            </x-button>
            <x-button color="secondary" class="w-full sm:w-auto" x-data="{}" @click="$dispatch('open-modal', 'config-reservas-modal')">
                <i class="fas fa-cog mr-2"></i>
                <span class="hidden md:inline">Configurações</span>
                <span class="md:hidden">Config</span>
            </x-button>
        </div>
    </div>

    <x-collapsible-filter 
        title="Filtros de Reservas" 
        :action="route('biblioteca.reservas.index')" 
        :clear-route="route('biblioteca.reservas.index')"
        target="reservas-list-wrapper"
    >
        <x-filter-field 
            name="status" 
            label="Status" 
            type="select"
            empty-option="Todos"
            :options="['ativa' => 'Ativa', 'processada' => 'Processada', 'cancelada' => 'Cancelada', 'expirada' => 'Expirada']"
        />

        <x-filter-field 
            name="usuario_id" 
            label="Usuário" 
            type="select"
            empty-option="Todos"
            :options="$usuarios->pluck('name','id')->toArray()"
        />

        <div>
            <label for="expiradas" class="block text-sm font-medium text-gray-700 mb-1">Somente expiradas</label>
            <div class="flex items-center h-[42px]">
                <input type="checkbox" name="expiradas" id="expiradas" value="1" 
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                       {{ request('expiradas') ? 'checked' : '' }}>
                <span class="ml-2 text-sm text-gray-600">Mostrar apenas reservas com prazo expirado</span>
            </div>
        </div>
    </x-collapsible-filter>

    <div id="reservas-list-wrapper" class="relative">
        <x-loading-overlay message="Atualizando reservas..." />
        <div data-ajax-content>
            @include('biblioteca.reservas._list', ['reservas' => $reservas])
        </div>
    </div>
</x-card>

<!-- Modal Nova Reserva (x-modal) -->
<x-modal name="nova-reserva-modal" title="Nova Reserva" maxWidth="w-11/12 md:w-2/4 lg:w-1/3">
    <form id="formNovaReserva" action="{{ route('biblioteca.reservas.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="usuario_id" class="block text-sm font-medium text-gray-700 mb-1">Usuário *</label>
                <select class="w-full border rounded px-3 py-2" id="usuario_id" name="usuario_id" required>
                    <option value="">Selecione um usuário...</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}">{{ $usuario->name }} ({{ $usuario->email }})</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-red-600" data-error-for="usuario_id"></p>
            </div>
            <div>
                <label for="item_id" class="block text-sm font-medium text-gray-700 mb-1">Item *</label>
                <select class="w-full border rounded px-3 py-2" id="item_id" name="item_id" required>
                    <option value="">Selecione um item...</option>
                    @foreach($itensReserva as $item)
                        @php
                            $emprestimosAtivos = ($item->emprestimos_ativos_count ?? 0);
                            $quantidadeFisica = ($item->quantidade_fisica ?? 0);
                            $semUnidades = $emprestimosAtivos >= $quantidadeFisica;
                            $statusDisponivel = in_array($item->status, ['disponivel', 'ativo']);
                            $desativar = $semUnidades || !$item->habilitado_emprestimo || !$statusDisponivel;
                            $disponiveis = max($quantidadeFisica - $emprestimosAtivos, 0);
                        @endphp
                        <option value="{{ $item->id }}" {{ $desativar ? 'disabled' : '' }}>
                            {{ $item->titulo }} ({{ ucfirst($item->tipo) }})
                            - Disponíveis: {{ $disponiveis }} de {{ $quantidadeFisica }}
                            @if(!$item->habilitado_emprestimo)
                                - não habilitado para empréstimo
                            @endif
                            @if(!$statusDisponivel)
                                - status: {{ $item->status }}
                            @endif
                            @if($semUnidades)
                                - sem unidades disponíveis
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-red-600" data-error-for="item_id"></p>
            </div>
        </div>
        <div class="mt-2 text-sm text-red-600" data-error-global></div>
        <div class="flex justify-end gap-2 pt-2">
            <x-button type="button" color="secondary" onclick="closeModal('nova-reserva-modal')">Cancelar</x-button>
            <x-button type="submit" color="primary">Criar Reserva</x-button>
        </div>
    </form>
</x-modal>

<!-- Modal Configurações de Reservas (x-modal) -->
<x-modal name="config-reservas-modal" title="Configurações de Reservas" maxWidth="w-11/12 md:w-2/4 lg:w-1/3">
    <form id="form-config-reservas" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Modo de processamento</label>
            <div class="space-y-2">
                <label class="inline-flex items-center">
                    <input type="radio" name="cfg-modo-processamento" value="automatico" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                    <span class="ml-2 text-sm text-gray-700">Automático (cria empréstimo ao processar)</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="cfg-modo-processamento" value="manual" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Manual (abrir formulário com redirect)</span>
                </label>
            </div>
            <p class="text-xs text-gray-500 mt-1">O modo manual reintroduz o redirect_url e abre o formulário de empréstimo.</p>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <x-button type="button" color="secondary" onclick="closeModal('config-reservas-modal')">Cancelar</x-button>
            <x-button type="button" color="primary" onclick="salvarConfigReservas(event)">
                <i class="fas fa-save mr-1"></i>
                Salvar Configurações
            </x-button>
        </div>
    </form>
    <div class="mt-4 bg-indigo-50 border border-indigo-100 rounded p-3">
        <div class="text-sm text-indigo-800">Esta configuração define como o processamento de reservas se comporta nesta interface.</div>
    </div>
</x-modal>

<!-- Modal Detalhes da Reserva (x-modal) -->
<x-modal name="detalhes-reserva-modal" title="Detalhes da Reserva" maxWidth="w-11/12 md:w-3/4 lg:w-1/2">
    <div id="detalhesReservaContent">
        <div class="py-6 text-center text-gray-500">
            <i class="fas fa-spinner fa-pulse mr-2"></i>Carregando detalhes...
        </div>
    </div>
</x-modal>

<!-- Modal Criar Empréstimo (x-modal) -->
<x-modal name="emprestimo-modal" title="Criar Empréstimo" maxWidth="w-11/12 md:w-4/5 lg:w-3/4">
    <div id="emprestimoErrors" class="mb-2 text-sm text-red-600 hidden"></div>
    <div id="emprestimoModalContent">
        <div class="text-center text-gray-500 py-4">Carregando formulário de empréstimo...</div>
    </div>
    <x-slot name="footer">
        <div class="flex justify-end">
            <x-button type="button" color="secondary" onclick="closeModal('emprestimo-modal')">Fechar</x-button>
        </div>
    </x-slot>
</x-modal>
</div>
 
<!-- Modal Confirmar Processamento (x-modal) -->
<x-modal name="processar-reserva-modal" title="Processar Reserva" maxWidth="sm:w-1/3">
    <p class="text-gray-700">Confirma o processamento desta reserva? O item precisa estar disponível para criar empréstimo.</p>
    <input type="hidden" id="processarReservaId" />
    <x-slot name="footer">
        <div class="flex justify-end gap-2">
            <x-button type="button" color="secondary" onclick="closeModal('processar-reserva-modal')">Cancelar</x-button>
            <x-button type="button" color="success" onclick="confirmarProcessamento()">Confirmar</x-button>
        </div>
    </x-slot>
</x-modal>

<!-- Modal Confirmar Cancelamento (x-modal) -->
<x-modal name="cancelar-reserva-modal" title="Cancelar Reserva" maxWidth="sm:w-1/3">
    <p class="text-gray-700">Confirmar cancelamento desta reserva?</p>
    <div class="mt-3">
        <label for="motivoCancelamento" class="block text-sm font-medium text-gray-700 mb-1">Motivo (opcional)</label>
        <textarea class="w-full border rounded px-3 py-2" id="motivoCancelamento" rows="2"></textarea>
    </div>
    <input type="hidden" id="cancelarReservaId" />
    <x-slot name="footer">
        <div class="flex justify-end gap-2">
            <x-button type="button" color="secondary" onclick="closeModal('cancelar-reserva-modal')">Voltar</x-button>
            <x-button type="button" color="danger" onclick="confirmarCancelamento()">Cancelar Reserva</x-button>
        </div>
    </x-slot>
</x-modal>
@endsection

@push('scripts')
<script>
// Abrir modais de ações
function openProcessarModal(reservaId) {
    document.getElementById('processarReservaId').value = reservaId;
    showModal('processar-reserva-modal');
}

function openCancelarModal(reservaId) {
    document.getElementById('cancelarReservaId').value = reservaId;
    document.getElementById('motivoCancelamento').value = '';
    showModal('cancelar-reserva-modal');
}

// Submeter criação de reserva via AJAX e atualizar lista (com erros inline)
const formNovaReservaEl = document.getElementById('formNovaReserva');
if (formNovaReservaEl) formNovaReservaEl.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    // Limpar mensagens de erro anteriores
    const errorEls = document.querySelectorAll('[data-error-for]');
    errorEls.forEach(el => el.textContent = '');
    const globalErrorEl = document.querySelector('[data-error-global]');
    if (globalErrorEl) globalErrorEl.textContent = '';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const contentType = response.headers.get('content-type') || '';
        if (!response.ok) {
            if (contentType.includes('application/json')) {
                const data = await response.json();
                // Se item está disponível, oferecer empréstimo automático
                const msgLower = (data.message || '').toLowerCase();
                if (msgLower.includes('item está disponível')) {
                    const confirmar = window.confirm('Item está disponível. Deseja realizar o empréstimo automaticamente?');
                    if (confirmar) {
                        const fd2 = new FormData(form);
                        fd2.append('auto_emprestimo', '1');
                        return fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: fd2
                        })
                        .then(r => r.json())
                        .then(data2 => {
                            if (data2.success) {
                                // Fecha modal e limpa formulário
                                closeModal('nova-reserva-modal');
                                form.reset();
                                // Redireciona para detalhes do empréstimo
                                if (data2.emprestimo_id) {
                                    window.location.href = `/biblioteca/emprestimos/${data2.emprestimo_id}`;
                                }
                                // Interrompe cadeia de promises
                                throw new Error('emprestimo-automatico-realizado');
                            } else {
                                // Mostrar erros retornados
                                if (data2.errors) {
                                    Object.entries(data2.errors).forEach(([field, messages]) => {
                                        const el = document.querySelector(`[data-error-for="${field}"]`);
                                        if (el) el.textContent = Array.isArray(messages) ? messages[0] : String(messages);
                                    });
                                }
                                if (globalErrorEl) globalErrorEl.textContent = data2.message || 'Erro ao criar empréstimo automático';
                                throw new Error(data2.message || 'Erro ao criar empréstimo automático');
                            }
                        })
                        .catch(err => { throw err; });
                    }
                }
                // Exibir erros inline por campo
                if (data.errors) {
                    Object.entries(data.errors).forEach(([field, messages]) => {
                        const el = document.querySelector(`[data-error-for="${field}"]`);
                        if (el) el.textContent = Array.isArray(messages) ? messages[0] : String(messages);
                    });
                }
                if (globalErrorEl && data.message) {
                    globalErrorEl.textContent = data.message;
                }
                return Promise.reject(new Error(data.message || 'Erro ao criar reserva'));
            }
            const text = await response.text();
            if (globalErrorEl) globalErrorEl.textContent = text || 'Erro ao criar reserva';
            return Promise.reject(new Error(text || 'Erro ao criar reserva'));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Atualiza a lista com HTML retornado (se disponível) ou recarrega via fetch
            const listWrapper = document.querySelector('[data-ajax-content]');
            if (data.html_list) {
                listWrapper.innerHTML = data.html_list;
            } else {
                // Fallback: buscar lista atual da página
                const url = new URL(window.location.href);
                fetch(url.href, { headers: { 'Accept': 'text/html' } })
                    .then(r => r.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContent = doc.querySelector('[data-ajax-content]');
                        if (newContent) listWrapper.innerHTML = newContent.innerHTML;
                    });
            }

            // Fecha modal
            closeModal('nova-reserva-modal');

            // Limpa formulário
            form.reset();

            // Opcional: abrir detalhes da reserva criada
            if (data.reserva_id) {
                verDetalhes(data.reserva_id);
            }
            // Se um empréstimo foi criado automaticamente, abrir detalhes (redirecionar)
            if (data.emprestimo_id) {
                window.location.href = `/biblioteca/emprestimos/${data.emprestimo_id}`;
            }
        } else {
            if (globalErrorEl) globalErrorEl.textContent = data.message || 'Erro ao criar reserva';
        }
    })
    .catch(err => {
        if (String(err && err.message).includes('emprestimo-automatico-realizado')) {
            // fluxo já tratado acima; nada a fazer
            return;
        }
        console.error('Erro:', err);
    });
});

function verDetalhes(reservaId) {
    fetch(`/biblioteca/reservas/${reservaId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detalhesReservaContent').innerHTML = html;
            showModal('detalhes-reserva-modal');
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar detalhes da reserva');
        });
}

function confirmarProcessamento() {
    const reservaId = document.getElementById('processarReservaId').value;
    // Ler preferência do modo (default: automatico)
    const modo = (localStorage.getItem('bibliotecaFluxoEmprestimo') || 'automatico');
    const url = new URL(window.location.origin + `/biblioteca/reservas/${reservaId}/processar`);
    if (modo === 'manual') {
        url.searchParams.set('modo', 'manual');
    }
    fetch(url.toString(), {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fecha modal de confirmação
            closeModal('processar-reserva-modal');
            // Atualiza lista se vier HTML
            const listWrapper = document.querySelector('[data-ajax-content]');
            if (data.html_list) {
                listWrapper.innerHTML = data.html_list;
            }
            // Se fluxo automático retornou emprestimo_id, abrir detalhes
            if (data.emprestimo_id) {
                fetch(`/biblioteca/emprestimos/${data.emprestimo_id}?partial=1`, { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.text())
                    .then(html => {
                        const modalBody = document.getElementById('emprestimoModalContent');
                        modalBody.innerHTML = html;
                        showModal('emprestimo-modal');
                    })
                    .catch(err => {
                        console.warn('Falha ao abrir detalhes do empréstimo:', err);
                    });
            }
            // Se modo manual: abrir formulário via redirect_url (quando presente)
            if (data.redirect_url) {
                fetch(data.redirect_url, { headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.text())
                    .then(html => {
                        const modalBody = document.getElementById('emprestimoModalContent');
                        modalBody.innerHTML = html;
                        showModal('emprestimo-modal');
                        // Wire submit do formulário dentro do modal
                        wireEmprestimoFormAjax();
                    })
                    .catch(err => {
                        console.warn('Falha ao carregar formulário de empréstimo:', err);
                    });
            }
        } else {
            if (window.AlertSystem && typeof window.AlertSystem.error === 'function') {
                window.AlertSystem.error(data.message || 'Erro ao processar reserva');
            } else {
                console.error(data.message || 'Erro ao processar reserva');
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        if (window.AlertSystem && typeof window.AlertSystem.error === 'function') {
            window.AlertSystem.error('Erro ao processar reserva');
        }
    });
}

function confirmarCancelamento() {
    const reservaId = document.getElementById('cancelarReservaId').value;
    const motivo = document.getElementById('motivoCancelamento').value;
    fetch(`/biblioteca/reservas/${reservaId}/cancelar`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ motivo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualiza lista
            const listWrapper = document.querySelector('[data-ajax-content]');
            if (data.html_list) {
                listWrapper.innerHTML = data.html_list;
            }
            // Fecha modal
            closeModal('cancelar-reserva-modal');
        } else {
            if (window.AlertSystem && typeof window.AlertSystem.error === 'function') {
                window.AlertSystem.error(data.message || 'Erro ao cancelar reserva');
            } else {
                console.error(data.message || 'Erro ao cancelar reserva');
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        if (window.AlertSystem && typeof window.AlertSystem.error === 'function') {
            window.AlertSystem.error('Erro ao cancelar reserva');
        }
    });
}

function limparFiltros() {
    document.getElementById('filtroStatus').value = '';
    document.getElementById('filtroUsuario').value = '';
    document.getElementById('filtroItem').value = '';
    filtrarTabela();
}

function filtrarTabela() {
    const status = document.getElementById('filtroStatus').value.toLowerCase();
    const usuario = document.getElementById('filtroUsuario').value.toLowerCase();
    const item = document.getElementById('filtroItem').value.toLowerCase();
    
    const rows = document.querySelectorAll('.reserva-row');
    
    rows.forEach(row => {
        const rowStatus = row.dataset.status;
        const rowUsuario = row.cells[1].textContent.toLowerCase();
        const rowItem = row.cells[2].textContent.toLowerCase();
        
        const statusMatch = !status || rowStatus.includes(status);
        const usuarioMatch = !usuario || rowUsuario.includes(usuario);
        const itemMatch = !item || rowItem.includes(item);
        
        row.style.display = (statusMatch && usuarioMatch && itemMatch) ? '' : 'none';
    });
}

// Envia o formulário de empréstimo carregado no modal via AJAX
function wireEmprestimoFormAjax() {
    const form = document.getElementById('formEmprestimoModal');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async (response) => {
            const ct = response.headers.get('content-type') || '';
            if (!response.ok) {
                if (ct.includes('application/json')) {
                    const data = await response.json();
                    const errBox = document.getElementById('emprestimoErrors');
                    if (errBox) { errBox.textContent = data.message || 'Erro ao criar empréstimo'; errBox.classList.remove('hidden'); }
                    return Promise.reject(new Error(data.message || 'Erro ao criar empréstimo'));
                }
                const text = await response.text();
                const errBox = document.getElementById('emprestimoErrors');
                if (errBox) { errBox.textContent = text || 'Erro ao criar empréstimo'; errBox.classList.remove('hidden'); }
                return Promise.reject(new Error(text || 'Erro ao criar empréstimo'));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Fecha modal de empréstimo
                closeModal('emprestimo-modal');
                // Atualiza lista de reservas
                const listWrapper = document.querySelector('[data-ajax-content]');
                if (data.html_list) {
                    listWrapper.innerHTML = data.html_list;
                } else {
                    const url = new URL(window.location.href);
                    fetch(url.href, { headers: { 'Accept': 'text/html' } })
                        .then(r => r.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newContent = doc.querySelector('[data-ajax-content]');
                            if (newContent) listWrapper.innerHTML = newContent.innerHTML;
                        });
                }
            } else {
                const errBox = document.getElementById('emprestimoErrors');
                if (errBox) { errBox.textContent = data.message || 'Erro ao criar empréstimo'; errBox.classList.remove('hidden'); }
            }
        })
        .catch(err => {
            console.error('Erro:', err);
            const errBox = document.getElementById('emprestimoErrors');
            if (errBox) { errBox.textContent = err.message || 'Erro ao criar empréstimo'; errBox.classList.remove('hidden'); }
        });
    }, { once: true });
}

// Event listeners para filtros (proteger caso elementos não existam)
const filtroStatusEl = document.getElementById('filtroStatus');
if (filtroStatusEl) filtroStatusEl.addEventListener('change', filtrarTabela);
const filtroUsuarioEl = document.getElementById('filtroUsuario');
if (filtroUsuarioEl) filtroUsuarioEl.addEventListener('input', filtrarTabela);
const filtroItemEl = document.getElementById('filtroItem');
if (filtroItemEl) filtroItemEl.addEventListener('input', filtrarTabela);
</script>
<script>
// Configurações de Reservas: carregar/salvar modo no localStorage
function salvarConfigReservas(event) {
    if (event) event.preventDefault();
    const selecionado = document.querySelector('input[name="cfg-modo-processamento"]:checked');
    const modo = selecionado ? selecionado.value : 'automatico';
    localStorage.setItem('bibliotecaFluxoEmprestimo', modo);
    closeModal('config-reservas-modal');
}

document.addEventListener('DOMContentLoaded', function() {
    const modoAtual = localStorage.getItem('bibliotecaFluxoEmprestimo') || 'automatico';
    const radio = document.querySelector(`input[name="cfg-modo-processamento"][value="${modoAtual}"]`);
    if (radio) radio.checked = true;
});
</script>
@endpush