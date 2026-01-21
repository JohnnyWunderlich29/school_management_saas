@extends('layouts.app')

@section('content')
    <x-breadcrumbs :items="[
        ['title' => 'Administração', 'url' => '#'],
        ['title' => 'Despesas', 'url' => route('admin.despesas.index')],
        ['title' => 'Recorrências', 'url' => '#'],
    ]" />

    <x-card>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Despesas Recorrentes</h1>
                <p class="mt-1 text-sm text-gray-600">Modelos de despesas que são geradas automaticamente</p>
            </div>
            <x-button color="primary" onclick="showModal('create-recorrencia-modal')">
                <i class="fas fa-plus mr-1"></i> Nova Recorrência
            </x-button>
        </div>

        @if ($recorrencias->count() > 0)
            <x-table :headers="[
                ['label' => 'Descrição'],
                ['label' => 'Categoria'],
                ['label' => 'Valor'],
                ['label' => 'Frequência'],
                ['label' => 'Próxima Geração'],
                ['label' => 'Status'],
            ]" :actions="true" striped hover responsive>
                @foreach ($recorrencias as $recorrencia)
                    <x-table-row :index="$loop->index">
                        <x-table-cell>
                            <div class="font-medium text-gray-900">{{ $recorrencia->descricao }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm text-gray-700">{{ $recorrencia->categoria ?? '-' }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm font-semibold text-gray-900">R$
                                {{ number_format((float) $recorrencia->valor, 2, ',', '.') }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm text-gray-700 capitalize">{{ $recorrencia->frequencia }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <span
                                class="text-sm text-gray-700">{{ $recorrencia->proxima_geracao ? $recorrencia->proxima_geracao->format('d/m/Y') : '-' }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <x-button size="xs" :color="$recorrencia->ativo ? 'success' : 'secondary'"
                                onclick="toggleRecorrencia({{ $recorrencia->id }}, this)">
                                {{ $recorrencia->ativo ? 'Ativo' : 'Inativo' }}
                            </x-button>
                        </x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex justify-end items-center space-x-2">
                                <x-button color="warning" size="sm" title="Editar"
                                    onclick="openEditRecorrencia({{ $recorrencia->id }})">
                                    <i class="fas fa-edit"></i>
                                </x-button>
                                <x-button color="danger" size="sm" title="Excluir"
                                    onclick="confirmDeleteRecorrencia({{ $recorrencia->id }})">
                                    <i class="fas fa-trash"></i>
                                </x-button>
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @endforeach
            </x-table>
        @else
            <div class="text-center py-12">
                <i class="fas fa-redo text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma recorrência configurada</h3>
                <p class="text-gray-600 mb-4">Configure despesas que se repetem para que o sistema as gere automaticamente.
                </p>
                <x-button color="primary" onclick="showModal('create-recorrencia-modal')">
                    <i class="fas fa-plus mr-1"></i> Nova Recorrência
                </x-button>
            </div>
        @endif
    </x-card>

    <!-- Modal: Nova Recorrência -->
    <x-modal id="create-recorrencia-modal" title="Nova Despesa Recorrente" maxWidth="max-w-2xl">
        <form method="POST" action="{{ route('admin.despesas.recorrencias.store') }}" class="space-y-4">
            @csrf
            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                <x-input type="text" id="descricao" name="descricao" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Ex.: Aluguel da Unidade" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
                    <x-input type="text" id="categoria" name="categoria"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Ex.: Fixas" />
                </div>
                <div>
                    <label for="valor" class="block text-sm font-medium text-gray-700">Valor</label>
                    <x-input type="number" step="0.01" min="0" id="valor" name="valor" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="0,00" />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="frequencia" class="block text-sm font-medium text-gray-700">Frequência</label>
                    <x-select id="frequencia" name="frequencia" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="semanal">Semanal</option>
                        <option value="mensal" selected>Mensal</option>
                        <option value="anual">Anual</option>
                    </x-select>
                </div>
                <div>
                    <label for="data_inicio" class="block text-sm font-medium text-gray-700">Data de Início (1ª
                        Geração)</label>
                    <x-input type="date" id="data_inicio" name="data_inicio" required value="{{ date('Y-m-d') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
            </div>
            <div>
                <label for="data_fim" class="block text-sm font-medium text-gray-700">Data de Término (Opcional)</label>
                <x-input type="date" id="data_fim" name="data_fim"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <x-button color="secondary" type="button"
                    onclick="closeModal('create-recorrencia-modal')">Fechar</x-button>
                <x-button color="primary" type="submit"><i class="fas fa-save mr-1"></i> Configurar Recorrência</x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal: Editar Recorrência -->
    <x-modal id="edit-recorrencia-modal" title="Editar Despesa Recorrente" maxWidth="max-w-2xl">
        <form id="edit-recorrencia-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_recorrencia_id" name="id">
            <div>
                <label for="edit_descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                <x-input type="text" id="edit_descricao" name="descricao" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="edit_categoria" class="block text-sm font-medium text-gray-700">Categoria</label>
                    <x-input type="text" id="edit_categoria" name="categoria"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <div>
                    <label for="edit_valor" class="block text-sm font-medium text-gray-700">Valor</label>
                    <x-input type="number" step="0.01" min="0" id="edit_valor" name="valor" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="edit_frequencia" class="block text-sm font-medium text-gray-700">Frequência</label>
                    <x-select id="edit_frequencia" name="frequencia" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="semanal">Semanal</option>
                        <option value="mensal">Mensal</option>
                        <option value="anual">Anual</option>
                    </x-select>
                </div>
                <div>
                    <label for="edit_data_inicio" class="block text-sm font-medium text-gray-700">Data de
                        Início</label>
                    <x-input type="date" id="edit_data_inicio" name="data_inicio" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
            </div>
            <div>
                <label for="edit_data_fim" class="block text-sm font-medium text-gray-700">Data de Término
                    (Opcional)</label>
                <x-input type="date" id="edit_data_fim" name="data_fim"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <x-button color="secondary" type="button"
                    onclick="closeModal('edit-recorrencia-modal')">Fechar</x-button>
                <x-button color="primary" type="submit"><i class="fas fa-save mr-1"></i> Salvar
                    Alterações</x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal: Excluir Recorrência -->
    <x-modal id="recorrencia-delete-modal" title="Excluir Recorrência" maxWidth="max-w-lg">
        <div class="space-y-4">
            <input type="hidden" id="delete_recorrencia_id" value="" />
            <p class="text-sm text-gray-500">Tem certeza que deseja excluir esta recorrência? Novas despesas não serão
                mais geradas automaticamente, mas as que já foram geradas permanecerão no sistema.</p>

            <div class="flex justify-end space-x-2 pt-2">
                <x-button color="secondary" type="button"
                    onclick="closeModal('recorrencia-delete-modal')">Voltar</x-button>
                <x-button color="danger" onclick="executeRecorrenciaDeletion()">Confirmar Exclusão</x-button>
            </div>
        </div>
    </x-modal>

    <script>
        async function toggleRecorrencia(id, btn) {
            const url = "{{ route('admin.despesas.recorrencias.toggle', ':id') }}".replace(':id', id);
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                });
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Erro ao alternar status.');
                }
            } catch (e) {
                console.error(e);
                alert('Erro inesperado.');
            }
        }

        async function openEditRecorrencia(id) {
            try {
                const url = "{{ route('admin.despesas.recorrencias.edit', ':id') }}".replace(':id', id);
                const response = await fetch(url);
                const data = await response.json();

                if (data.success) {
                    const recorrencia = data.data;
                    document.getElementById('edit_recorrencia_id').value = recorrencia.id;
                    document.getElementById('edit_descricao').value = recorrencia.descricao;
                    document.getElementById('edit_categoria').value = recorrencia.categoria || '';
                    document.getElementById('edit_valor').value = recorrencia.valor;
                    document.getElementById('edit_frequencia').value = recorrencia.frequencia;
                    document.getElementById('edit_data_inicio').value = recorrencia.data_inicio;
                    document.getElementById('edit_data_fim').value = recorrencia.data_fim || '';

                    document.getElementById('edit-recorrencia-form').action = recorrencia.update_url;

                    showModal('edit-recorrencia-modal');
                } else {
                    alert(data.message || 'Erro ao carregar dados da recorrência.');
                }
            } catch (e) {
                console.error(e);
                alert('Erro ao carregar modal de edição.');
            }
        }

        function confirmDeleteRecorrencia(id) {
            document.getElementById('delete_recorrencia_id').value = id;
            showModal('recorrencia-delete-modal');
        }

        function executeRecorrenciaDeletion() {
            const id = document.getElementById('delete_recorrencia_id').value;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('admin.despesas.recorrencias.destroy', ':id') }}".replace(':id', id);
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // Interceptar submit do form de edição para usar AJAX se preferir, 
        // mas aqui vamos deixar como submit normal por enquanto ou mudar para AJAX conforme padrão do projeto.
        // O padrão das outras telas de despesa é AJAX. Vamos implementar AJAX no update da recorrência também.
        document.getElementById('edit-recorrencia-form').onsubmit = async function(e) {
            e.preventDefault();
            const form = e.target;
            const url = form.action;
            const formData = new FormData(form);
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                // Converter FormData para JSON para o PUT
                const data = {};
                formData.forEach((value, key) => data[key] = value);

                const response = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Erro ao atualizar recorrência. Verifique os campos.');
                }
            } catch (e) {
                console.error(e);
                alert('Erro inesperado ao salvar.');
            }
        };
    </script>
@endsection
