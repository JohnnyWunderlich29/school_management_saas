@extends('layouts.app')

@section('title', 'Empréstimos - Biblioteca Digital')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Biblioteca', 'url' => route('biblioteca.index')],
    ['title' => 'Empréstimos', 'url' => '#']
]" />

<x-card>
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Gestão de Empréstimos</h1>
            <p class="mt-1 text-sm text-gray-600">Controle de empréstimos de itens da biblioteca</p>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row">
            <x-button color="primary" class="w-full sm:w-auto" x-data="{}" @click="$dispatch('open-modal', 'novo-emprestimo-modal')">
                <i class="fas fa-plus mr-2"></i>
                <span class="hidden md:inline">Novo Empréstimo</span>
                <span class="md:hidden">Novo</span>
            </x-button>
            <x-button color="secondary" class="w-full sm:w-auto" x-data="{}" @click="$dispatch('open-modal', 'config-emprestimos-modal')">
                <i class="fas fa-cog mr-2"></i>
                <span class="hidden md:inline">Configurações</span>
                <span class="md:hidden">Config</span>
            </x-button>
        </div>
    </div>

    <x-collapsible-filter 
        title="Filtros de Empréstimos" 
        :action="route('biblioteca.emprestimos.index')" 
        :clear-route="route('biblioteca.emprestimos.index')"
        target="emprestimos-list-wrapper"
    >
        <x-filter-field 
            name="status" 
            label="Status" 
            type="select"
            empty-option="Todos"
            :options="['ativo' => 'Ativo', 'devolvido' => 'Devolvido']"
        />

        <x-filter-field 
            name="usuario_id" 
            label="Usuário" 
            type="select"
            empty-option="Todos"
            :options="$usuarios->pluck('name','id')->toArray()"
        />

        <div>
            <label for="vencidos" class="block text-sm font-medium text-gray-700 mb-1">Somente vencidos</label>
            <div class="flex items-center h-[42px]">
                <input type="checkbox" name="vencidos" id="vencidos" value="1" 
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                       {{ request('vencidos') ? 'checked' : '' }}>
                <span class="ml-2 text-sm text-gray-600">Mostrar apenas empréstimos com previsão vencida</span>
            </div>
        </div>
    </x-collapsible-filter>

    <div id="emprestimos-list-wrapper" class="relative">
        <x-loading-overlay message="Atualizando empréstimos..." />
        <div data-ajax-content>
            <x-table class="hidden md:block" :headers="['Usuário', 'Item', 'Empréstimo', 'Prevista', 'Status', 'Multa']" :actions="true">
                @forelse($emprestimos as $index => $emprestimo)
                    <x-table-row :striped="true" :index="$index">
                        <x-table-cell>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $emprestimo->usuario->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $emprestimo->usuario->email }}</div>
                                </div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <div>
                                <div class="font-medium text-gray-900">{{ $emprestimo->item->titulo }}</div>
                                <div class="text-gray-500 text-xs">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ ucfirst($emprestimo->item->tipo) }}</span>
                                    @if($emprestimo->item->autores)
                                        <span class="ml-1">- {{ $emprestimo->item->autores }}</span>
                                    @endif
                                </div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>{{ $emprestimo->data_emprestimo->format('d/m/Y H:i') }}</x-table-cell>
                        <x-table-cell>
                            @php($vencido = $emprestimo->data_prevista->isPast() && $emprestimo->status === 'ativo')
                            <span class="{{ $vencido ? 'text-red-600 font-semibold' : '' }}">{{ $emprestimo->data_prevista->format('d/m/Y') }}</span>
                            @if($vencido)
                                <div class="text-xs text-red-600 mt-1 flex items-center"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $emprestimo->data_prevista->diffForHumans() }}</div>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            @switch($emprestimo->status)
                                @case('devolvido')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Devolvido</span>
                                    @break
                                @case('ativo')
                                    @if($emprestimo->data_prevista->isPast())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Vencido</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Ativo</span>
                                    @endif
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($emprestimo->status) }}</span>
                            @endswitch
                        </x-table-cell>
                        <x-table-cell>
                            @if(($emprestimo->multa_calculada ?? 0) > 0)
                                <span class="text-red-600 font-semibold">R$ {{ number_format($emprestimo->multa_calculada, 2, ',', '.') }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex justify-end space-x-2">
                                <x-button color="primary" size="sm" title="Ver detalhes" onclick="openDetalhesEmprestimo({{ $emprestimo->id }})">
                                    <i class="fas fa-eye"></i>
                                </x-button>

                                @if($emprestimo->status === 'ativo')
                                    <x-button color="success" size="sm" title="Devolver" onclick="openDevolverEmprestimoWithUrl('{{ route('biblioteca.emprestimos.devolver', $emprestimo) }}')">
                                        <i class="fas fa-undo"></i>
                                    </x-button>

                                    @if($emprestimo->data_prevista >= now())
                                        <x-button color="warning" size="sm" title="Renovar" onclick="openRenovarEmprestimoWithUrl('{{ route('biblioteca.emprestimos.renovar', $emprestimo) }}')">
                                            <i class="fas fa-sync"></i>
                                        </x-button>
                                    @endif
                                @endif
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhum empréstimo encontrado.
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <!-- Layout mobile com cards -->
            <div class="md:hidden space-y-4">
                @forelse($emprestimos as $emprestimo)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                                <i class="fas fa-user text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-base">{{ $emprestimo->usuario->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $emprestimo->usuario->email }}</p>
                            </div>
                            <div class="ml-2">
                                @switch($emprestimo->status)
                                    @case('devolvido')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Devolvido</span>
                                        @break
                                    @case('ativo')
                                        @if($emprestimo->data_prevista->isPast())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Vencido</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Ativo</span>
                                        @endif
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($emprestimo->status) }}</span>
                                @endswitch
                            </div>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Item:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $emprestimo->item->titulo }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Empréstimo:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $emprestimo->data_emprestimo->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Prevista:</span>
                                @php($vencido = $emprestimo->data_prevista->isPast() && $emprestimo->status === 'ativo')
                                <span class="text-sm font-medium {{ $vencido ? 'text-red-600' : 'text-gray-900' }}">{{ $emprestimo->data_prevista->format('d/m/Y') }}</span>
                            </div>
                            @if(($emprestimo->multa_calculada ?? 0) > 0)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Multa:</span>
                                    <span class="text-sm font-medium text-red-600">R$ {{ number_format($emprestimo->multa_calculada, 2, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 flex gap-2">
                            <x-button color="primary" class="flex-1" onclick="openDetalhesEmprestimo({{ $emprestimo->id }})">
                                <i class="fas fa-eye mr-2"></i>
                                Detalhes
                            </x-button>

                            @if($emprestimo->status === 'ativo')
                                <x-button color="success" class="flex-1" onclick="openDevolverEmprestimoWithUrl('{{ route('biblioteca.emprestimos.devolver', $emprestimo) }}')">
                                    <i class="fas fa-undo mr-2"></i>
                                    Devolver
                                </x-button>

                                @if($emprestimo->data_prevista >= now())
                                    <x-button color="warning" class="flex-1" onclick="openRenovarEmprestimoWithUrl('{{ route('biblioteca.emprestimos.renovar', $emprestimo) }}')">
                                        <i class="fas fa-sync mr-2"></i>
                                        Renovar
                                    </x-button>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-inbox text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500">Nenhum empréstimo encontrado.</p>
                    </div>
                @endforelse
            </div>

            <!-- Paginação -->
            <div class="mt-6">
                {{ $emprestimos->links('components.pagination') }}
            </div>
        </div>
    </div>
</x-card>

<!-- Modal Novo Empréstimo (padrão Biblioteca) -->
<x-modal name="novo-emprestimo-modal" title="Novo Empréstimo" maxWidth="w-11/12 md:w-3/4 lg:w-1/2">
    <form id="form-novo-emprestimo" action="{{ route('biblioteca.emprestimos.store') }}" method="POST" class="space-y-4">
        @csrf
        <div id="politica-vigente-resumo" class="bg-gray-50 border border-gray-200 rounded p-3 text-sm text-gray-700">
            <!-- Política vigente será exibida aqui -->
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="usuario_id_modal" class="block text-sm font-medium text-gray-700 mb-1">Usuário *</label>
                <select id="usuario_id_modal" name="usuario_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">Selecione um usuário...</option>
                    @if(isset($funcionarios) && $funcionarios->count())
                        <optgroup label="Funcionários">
                            @foreach($funcionarios as $f)
                                <option value="funcionario:{{ $f->id }}" data-group="funcionarios">{{ $f->nome }} {{ $f->sobrenome }} (Funcionário)</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if(isset($alunos) && $alunos->count())
                        <optgroup label="Alunos">
                            @foreach($alunos as $a)
                                <option value="aluno:{{ $a->id }}" data-group="alunos">{{ $a->nome }} {{ $a->sobrenome }} @if(!empty($a->matricula)) - {{ $a->matricula }} @endif (Aluno)</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
                <p class="text-xs text-gray-500 mt-1" id="info-elegiveis">Permitidos: Funcionários e Alunos</p>
            </div>
            <div>
                <label for="item_id" class="block text-sm font-medium text-gray-700 mb-1">Item *</label>
                <select id="item_id" name="item_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">Selecione um item...</option>
                    @foreach($itensDisponiveis as $item)
                        <option value="{{ $item->id }}">{{ $item->titulo }} ({{ ucfirst($item->tipo) }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="data_devolucao_prevista" class="block text-sm font-medium text-gray-700 mb-1">Data Prevista Devolução</label>
                <input type="date" id="data_devolucao_prevista" name="data_devolucao_prevista" class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Deixe em branco para usar a política padrão. <span id="info-politica-padrao" class="text-gray-600"></span></p>
            </div>
            <div>
                <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea id="observacoes" name="observacoes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <x-button type="button" color="secondary" onclick="closeModal('novo-emprestimo-modal')">Cancelar</x-button>
            <x-button type="submit" color="primary">
                <i class="fas fa-save mr-1"></i>
                Criar Empréstimo
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Modal de Configurações de Empréstimos -->
<x-modal name="config-emprestimos-modal" title="Configurações de Empréstimos" maxWidth="w-11/12 md:w-3/4 lg:w-1/2">
    <form id="form-config-emprestimos" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quem pode fazer empréstimo</label>
                <div class="space-y-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="cfg-elegiveis-funcionarios" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                        <span class="ml-2 text-sm text-gray-700">Funcionários</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="cfg-elegiveis-alunos" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                        <span class="ml-2 text-sm text-gray-700">Alunos</span>
                    </label>
                </div>
            </div>
            <div>
                <label for="cfg-limite-por-usuario" class="block text-sm font-medium text-gray-700 mb-1">Limite de empréstimos por usuário</label>
                <input type="number" id="cfg-limite-por-usuario" min="1" class="w-full border rounded px-3 py-2" value="3">
                <p class="text-xs text-gray-500 mt-1">Define quantos itens simultâneos cada usuário pode manter emprestado.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="cfg-prazo-padrao-dias" class="block text-sm font-medium text-gray-700 mb-1">Prazo padrão de empréstimo (dias)</label>
                <input type="number" id="cfg-prazo-padrao-dias" min="1" class="w-full border rounded px-3 py-2" value="7">
                <p class="text-xs text-gray-500 mt-1">Usado para sugerir a data prevista quando deixado em branco.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Multas pendentes</label>
                <label class="inline-flex items-center">
                    <input type="checkbox" id="cfg-bloquear-multas" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                    <span class="ml-2 text-sm text-gray-700">Bloquear empréstimos com multas pendentes</span>
                </label>
                <p class="text-xs text-gray-500 mt-1">Aplicado por escola. Atualização de multas e bloqueios são refletidos na criação.</p>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <x-button type="button" color="secondary" onclick="closeModal('config-emprestimos-modal')">Cancelar</x-button>
            <x-button type="button" color="primary" onclick="salvarConfigEmprestimos(event)">
                <i class="fas fa-save mr-1"></i>
                Salvar Configurações
            </x-button>
        </div>
    </form>
    <div class="mt-4 bg-indigo-50 border border-indigo-100 rounded p-3">
        <div class="text-sm text-indigo-800">As configurações acima definem a política vigente. Elas serão usadas para filtrar elegibilidade e sugerir prazos no modal de criação de empréstimo.</div>
    </div>
</x-modal>

<!-- Modal Detalhes do Empréstimo -->
<x-modal name="detalhes-emprestimo-modal" title="Detalhes do Empréstimo" maxWidth="w-11/12 md:w-3/4 lg:w-1/2">
    <div id="detalhes-emprestimo-container">
        <div class="py-6 text-center text-gray-500">
            <i class="fas fa-spinner fa-pulse mr-2"></i>Carregando detalhes...
        </div>
    </div>
    <div class="hidden" id="detalhes-emprestimo-actions"></div>
    <script>
        async function openDetalhesEmprestimo(id) {
            const container = document.getElementById('detalhes-emprestimo-container');
            container.innerHTML = '<div class="py-6 text-center text-gray-500"><i class="fas fa-spinner fa-pulse mr-2"></i>Carregando detalhes...</div>';
            try {
                const resp = await fetch(`{{ url('/biblioteca/emprestimos') }}/${id}?partial=1`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const html = await resp.text();
                container.innerHTML = html;
                showModal('detalhes-emprestimo-modal');
            } catch (e) {
                container.innerHTML = '<div class="py-6 text-center text-red-600">Erro ao carregar detalhes.</div>';
                showModal('detalhes-emprestimo-modal');
            }
        }
        function openDevolverEmprestimoWithUrl(url) {
            const form = document.getElementById('form-devolver-emprestimo');
            form.action = url;
            showModal('devolver-emprestimo-modal');
        }
        function openRenovarEmprestimoWithUrl(url) {
            const form = document.getElementById('form-renovar-emprestimo');
            form.action = url;
            showModal('renovar-emprestimo-modal');
        }
    </script>
</x-modal>

<script>
    // Autoabre o modal de detalhes se a URL tiver ?detalhes={id}
    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const detalhesId = params.get('detalhes');
        if (detalhesId) {
            // Usa o mesmo fluxo de fetch parcial para preencher e abrir o modal
            openDetalhesEmprestimo(detalhesId);
        }
    });
    // Função utilitária para abrir detalhes por ID (fallback de compatibilidade)
    function verDetalhes(emprestimoId) {
        openDetalhesEmprestimo(emprestimoId);
    }
</script>

<!-- Modal Confirmar Devolução -->
<x-modal name="devolver-emprestimo-modal" title="Confirmar Devolução" maxWidth="w-11/12 md:w-1/4">
    <form id="form-devolver-emprestimo" method="POST" class="space-y-4">
        @csrf
        @method('PATCH')
        <p class="text-sm text-gray-700">Confirma a devolução deste empréstimo?</p>
        <div class="flex justify-end gap-2">
            <x-button type="button" color="secondary" onclick="closeModal('devolver-emprestimo-modal')">Cancelar</x-button>
            <x-button type="submit" color="success">
                <i class="fas fa-undo mr-1"></i>
                Confirmar
            </x-button>
        </div>
    </form>
</x-modal>

<!-- Modal Confirmar Renovação -->
<x-modal name="renovar-emprestimo-modal" title="Confirmar Renovação" maxWidth="w-11/12 md:w-1/4">
    <form id="form-renovar-emprestimo" method="POST" class="space-y-4">
        @csrf
        @method('PATCH')
        <p class="text-sm text-gray-700">Confirma a renovação deste empréstimo?</p>
        <div class="flex justify-end gap-2">
            <x-button type="button" color="secondary" onclick="closeModal('renovar-emprestimo-modal')">Cancelar</x-button>
            <x-button type="submit" color="warning">
                <i class="fas fa-sync mr-1"></i>
                Confirmar
            </x-button>
        </div>
    </form>
</x-modal>

@endsection

@push('scripts')
<script>
// Configurações de Empréstimos (carregadas do backend)
window.bibliotecaEmprestimosConfig = {
    elegiveisFuncionarios: true,
    elegiveisAlunos: true,
    limitePorUsuario: 3,
    prazoPadraoDias: 7,
    bloquearMultasPendentes: false,
};

function loadBibliotecaPolitica() {
    return fetch('/biblioteca/politicas', { headers: { 'Accept': 'application/json' } })
        .then(r => {
            if (r.status === 401) {
                window.location.href = '/login';
                return Promise.reject(new Error('Não autenticado'));
            }
            return r.json();
        })
        .then(data => {
            window.bibliotecaEmprestimosConfig = {
                elegiveisFuncionarios: !!data.permitir_funcionarios,
                elegiveisAlunos: !!data.permitir_alunos,
                limitePorUsuario: data.max_emprestimos_por_usuario ?? 3,
                prazoPadraoDias: data.prazo_padrao_dias ?? 7,
                bloquearMultasPendentes: !!data.bloquear_por_multas,
            };
            // Preenche UI e aplica políticas
            document.getElementById('cfg-elegiveis-funcionarios').checked = window.bibliotecaEmprestimosConfig.elegiveisFuncionarios;
            document.getElementById('cfg-elegiveis-alunos').checked = window.bibliotecaEmprestimosConfig.elegiveisAlunos;
            document.getElementById('cfg-limite-por-usuario').value = window.bibliotecaEmprestimosConfig.limitePorUsuario;
            document.getElementById('cfg-prazo-padrao-dias').value = window.bibliotecaEmprestimosConfig.prazoPadraoDias;
            document.getElementById('cfg-bloquear-multas').checked = window.bibliotecaEmprestimosConfig.bloquearMultasPendentes;
            atualizarInfoPoliticaUI();
            aplicarPoliticaNoModalNovoEmprestimo();
        })
        .catch(() => {
            // Mantém defaults se falhar
            atualizarInfoPoliticaUI();
        });
}

function salvarConfigEmprestimos(event) {
    if (event) event.preventDefault();
    const cfg = {
        elegiveisFuncionarios: document.getElementById('cfg-elegiveis-funcionarios').checked,
        elegiveisAlunos: document.getElementById('cfg-elegiveis-alunos').checked,
        limitePorUsuario: parseInt(document.getElementById('cfg-limite-por-usuario').value || '3', 10),
        prazoPadraoDias: parseInt(document.getElementById('cfg-prazo-padrao-dias').value || '7', 10),
        bloquearMultasPendentes: document.getElementById('cfg-bloquear-multas').checked,
    };
    window.bibliotecaEmprestimosConfig = cfg;

    const payload = {
        permitir_funcionarios: cfg.elegiveisFuncionarios,
        permitir_alunos: cfg.elegiveisAlunos,
        max_emprestimos_por_usuario: cfg.limitePorUsuario,
        prazo_padrao_dias: cfg.prazoPadraoDias,
        bloquear_por_multas: cfg.bloquearMultasPendentes,
    };

    fetch('/biblioteca/politicas', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    })
        .then(r => {
            if (r.status === 401) {
                window.location.href = '/login';
                return Promise.reject(new Error('Não autenticado'));
            }
            return r.json();
        })
        .then(() => {
            atualizarInfoPoliticaUI();
            closeModal('config-emprestimos-modal');
        })
        .catch(() => {
            atualizarInfoPoliticaUI();
            closeModal('config-emprestimos-modal');
        });
}

function formatarDataISO(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function atualizarInfoPoliticaUI() {
    const infoElegiveis = document.getElementById('info-elegiveis');
    if (infoElegiveis) {
        const grupos = [];
        if (window.bibliotecaEmprestimosConfig.elegiveisFuncionarios) grupos.push('Funcionários');
        if (window.bibliotecaEmprestimosConfig.elegiveisAlunos) grupos.push('Alunos');
        infoElegiveis.textContent = `Permitidos: ${grupos.length ? grupos.join(' e ') : 'Nenhum grupo (ajuste as configurações)'}`;
    }

    const infoPolitica = document.getElementById('info-politica-padrao');
    if (infoPolitica) {
        const dias = window.bibliotecaEmprestimosConfig.prazoPadraoDias || 7;
        const hoje = new Date();
        const prevista = new Date(hoje);
        prevista.setDate(hoje.getDate() + dias);
        infoPolitica.textContent = `Prazo padrão: ${dias} dias. Sugerido: ${formatarDataISO(prevista)}. ` +
            (window.bibliotecaEmprestimosConfig.bloquearMultasPendentes ? 'Bloqueia empréstimos com multas pendentes.' : 'Permite empréstimos com multas pendentes.');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadBibliotecaPolitica();
});

// Normaliza os grupos para comparação (ex.: Funcionário/Staff => funcionarios)
function normalizarGrupo(str) {
    if (!str) return 'indefinido';
    const s = String(str).toLowerCase();
    if (/(funcion|funcionário|colaborador|staff)/.test(s)) return 'funcionarios';
    if (/(servidor|professor|docente|administrativo|coordenador|gestor|teacher)/.test(s)) return 'funcionarios';
    if (/(aluno|student|estudante)/.test(s)) return 'alunos';
    return s;
}

function aplicarFiltroElegibilidadeUsuarios() {
    const select = document.getElementById('usuario_id_modal');
    if (!select) return;
    const permitirFunc = !!window.bibliotecaEmprestimosConfig.elegiveisFuncionarios;
    const permitirAlunos = !!window.bibliotecaEmprestimosConfig.elegiveisAlunos;

    // Cacheia a lista original de usuários no primeiro uso
    if (!window._allUsuarioOptions) {
        window._allUsuarioOptions = Array.from(select.options)
            .filter(opt => opt.value !== '')
            .map(opt => {
                const raw = opt.getAttribute('data-group') || '';
                const norm = normalizarGrupo(raw);
                return {
                    value: opt.value,
                    text: opt.textContent,
                    group: raw,
                    normGroup: norm,
                };
            });
    }

    // Se ambos estão permitidos, restaura a lista completa
    let permitidos;
    if (permitirFunc && permitirAlunos) {
        permitidos = window._allUsuarioOptions.slice();
    } else {
        // Reconstroi a lista apenas com permitidos
        permitidos = window._allUsuarioOptions.filter(opt => {
            const grupo = opt.normGroup;
            return (
                (grupo === 'funcionarios' && permitirFunc) ||
                (grupo === 'alunos' && permitirAlunos) ||
                (grupo === 'indefinido' && (permitirFunc || permitirAlunos))
            );
        });
    }

    const selectedBefore = select.value;
    select.innerHTML = '<option value="">Selecione um usuário...</option>';

    // Agrupa por perfis para separar visualmente
    const gruposMap = new Map();
    const labelPorGrupo = {
        funcionarios: 'Funcionários',
        alunos: 'Alunos',
        indefinido: 'Outros'
    };

    permitidos.forEach(opt => {
        const key = opt.normGroup in labelPorGrupo ? opt.normGroup : 'indefinido';
        if (!gruposMap.has(key)) gruposMap.set(key, []);
        gruposMap.get(key).push(opt);
    });

    // Ordem de exibição: Funcionários, Alunos, Outros
    ['funcionarios', 'alunos', 'indefinido'].forEach(key => {
        const lista = gruposMap.get(key);
        if (!lista || lista.length === 0) return;
        const groupEl = document.createElement('optgroup');
        groupEl.label = labelPorGrupo[key];
        lista.forEach(opt => {
            const o = document.createElement('option');
            o.value = opt.value;
            o.textContent = opt.text;
            o.setAttribute('data-group', opt.group);
            groupEl.appendChild(o);
        });
        select.appendChild(groupEl);
    });

    // Se o selecionado anterior não estiver mais presente, limpa seleção
    if (!permitidos.find(o => o.value === selectedBefore)) {
        select.value = '';
    }
}

function sugerirDataPrevistaSeVazia() {
    const input = document.getElementById('data_devolucao_prevista');
    if (!input) return;
    const valor = input.value;
    if (!valor) {
        const dias = window.bibliotecaEmprestimosConfig.prazoPadraoDias || 7;
        const hoje = new Date();
        const prevista = new Date(hoje);
        prevista.setDate(hoje.getDate() + dias);
        input.value = formatarDataISO(prevista);
    }
}

function atualizarResumoPolitica() {
    const el = document.getElementById('politica-vigente-resumo');
    if (!el) return;
    const grupos = [];
    if (window.bibliotecaEmprestimosConfig.elegiveisFuncionarios) grupos.push('Funcionários');
    if (window.bibliotecaEmprestimosConfig.elegiveisAlunos) grupos.push('Alunos');
    const dias = window.bibliotecaEmprestimosConfig.prazoPadraoDias || 7;
    const hoje = new Date();
    const prevista = new Date(hoje);
    prevista.setDate(hoje.getDate() + dias);
    const sugerida = formatarDataISO(prevista);
    const multas = window.bibliotecaEmprestimosConfig.bloquearMultasPendentes ? 'Bloqueia empréstimos com multas pendentes.' : 'Permite empréstimos com multas pendentes.';
    el.innerHTML = `
        <div class="font-medium text-gray-800 mb-1">Política vigente</div>
        <div class="text-gray-700">Elegibilidade: ${grupos.length ? grupos.join(' e ') : 'Nenhum grupo (ajuste as configurações)'} | Limite/usuário: ${window.bibliotecaEmprestimosConfig.limitePorUsuario} | Prazo padrão: ${dias} dias | Sugerida: ${sugerida}</div>
        <div class="text-gray-600 mt-1">${multas}</div>
    `;
}

function aplicarPoliticaNoModalNovoEmprestimo() {
    atualizarInfoPoliticaUI();
    aplicarFiltroElegibilidadeUsuarios();
    sugerirDataPrevistaSeVazia();
    atualizarResumoPolitica();
}

// Atualiza quando o modal é aberto via evento
document.addEventListener('open-modal', function(e) {
    const id = typeof e.detail === 'string' ? e.detail : (e.detail && e.detail.id);
    if (id === 'novo-emprestimo-modal') {
        setTimeout(aplicarPoliticaNoModalNovoEmprestimo, 50);
    }
});

function verDetalhes(emprestimoId) {
    fetch(`/biblioteca/emprestimos/${emprestimoId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detalhesEmprestimoContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('detalhesEmprestimoModal')).show();
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar detalhes do empréstimo');
        });
}

function devolverItem(emprestimoId) {
    if (confirm('Confirma a devolução deste item?')) {
        fetch(`/biblioteca/emprestimos/${emprestimoId}/devolver`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao devolver item');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao devolver item');
        });
    }
}

function renovarEmprestimo(emprestimoId) {
    if (confirm('Confirma a renovação deste empréstimo?')) {
        fetch(`/biblioteca/emprestimos/${emprestimoId}/renovar`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao renovar empréstimo');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao renovar empréstimo');
        });
    }
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
    
    const rows = document.querySelectorAll('.emprestimo-row');
    
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

// Event listeners para filtros
const filtroStatusEl = document.getElementById('filtroStatus');
if (filtroStatusEl) filtroStatusEl.addEventListener('change', filtrarTabela);
const filtroUsuarioEl = document.getElementById('filtroUsuario');
if (filtroUsuarioEl) filtroUsuarioEl.addEventListener('input', filtrarTabela);
const filtroItemEl = document.getElementById('filtroItem');
if (filtroItemEl) filtroItemEl.addEventListener('input', filtrarTabela);
</script>
@endpush