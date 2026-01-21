@extends('layouts.app')

@section('content')
    <div class="w-full mx-auto">
        <div class="mb-6 flex flex-col justify-between items-center md:flex-row">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Perfil do Aluno</h2>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    @if ($aluno->sala)
                        <a href="{{ route('salas.show', $aluno->sala) }}"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 transition">
                            <i class="fas fa-door-open mr-1"></i>{{ $aluno->sala->nome_completo ?? $aluno->sala->nome }}
                            <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    @endif
                    @if ($aluno->matricula)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-id-card mr-1"></i>{{ $aluno->matricula }}
                        </span>
                    @endif
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        <i
                            class="fas fa-calendar mr-1"></i>{{ $aluno->data_nascimento ? \Carbon\Carbon::parse($aluno->data_nascimento)->age : 'N/A' }}
                        anos
                    </span>
                </div>
            </div>
            <div class="mt-4 flex flex-col w-full sm:flex-row justify-end gap-3 sm:mt-0 sm:w-auto">
                <x-button href="{{ route('alunos.index') }}" color="secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </x-button>
                <x-button href="{{ route('alunos.edit', $aluno->id) }}" color="warning">
                    <i class="fas fa-edit mr-1"></i> Editar
                </x-button>
            </div>
        </div>

        <!-- Navegação por Abas -->
        <div class="bg-white shadow-sm rounded-t-lg border border-gray-200 border-b-0">
            <nav class="flex space-x-4 px-6 overflow-x-auto" aria-label="Tabs">
                <button onclick="showTab('tab-info')" id="btn-tab-info"
                    class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-indigo-500 text-indigo-600 active">
                    <i class="fas fa-user mr-2"></i>Informações
                </button>
                <button onclick="showTab('tab-notas')" id="btn-tab-notas"
                    class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-graduation-cap mr-2"></i>Notas
                </button>
                <button onclick="showTab('tab-anotacoes')" id="btn-tab-anotacoes"
                    class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-clipboard-list mr-2"></i>Anotações
                </button>
            </nav>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="bg-white shadow-sm rounded-b-lg border border-gray-200 p-6 mb-6">
            <!-- ABA INFORMAÇÕES -->
            <div id="tab-info" class="tab-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informações Pessoais -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-id-card mr-2"></i>Dados Pessoais
                        </h3>
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Nome</label>
                                    <p class="text-gray-900 font-medium">{{ $aluno->nome }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase">Sobrenome</label>
                                    <p class="text-gray-900 font-medium">{{ $aluno->sobrenome }}</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">CPF</label>
                                <p class="text-gray-900">{{ $aluno->cpf ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">Data de Nascimento</label>
                                <p class="text-gray-900">
                                    {{ $aluno->data_nascimento ? $aluno->data_nascimento->format('d/m/Y') : 'Não informado' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">Gênero</label>
                                <p class="text-gray-900">{{ $aluno->genero ?? 'Não informado' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contato e Endereço -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-map-marker-alt mr-2"></i>Contato e Endereço
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">Telefone</label>
                                <p class="text-gray-900">{{ $aluno->telefone ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">E-mail</label>
                                <p class="text-gray-900 break-all">{{ $aluno->email ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">Endereço</label>
                                <p class="text-gray-900">{{ $aluno->endereco ?? 'Não informado' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">Cidade/UF</label>
                                <p class="text-gray-900">
                                    {{ $aluno->cidade ?? 'Não informado' }}{{ $aluno->estado ? '/' . $aluno->estado : '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Médica -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-heartbeat mr-2"></i>Saúde
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">Tipo Sanguíneo</label>
                                <p class="text-gray-900">{{ $aluno->tipo_sanguineo ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 uppercase">Alergias</label>
                                <p class="text-gray-900">{{ $aluno->alergias ?? 'Nenhuma informada' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Responsáveis -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            <i class="fas fa-users mr-2"></i>Responsáveis
                        </h3>
                        <div class="space-y-3 text-sm">
                            @forelse($aluno->responsaveis as $resp)
                                <div
                                    class="flex justify-between items-center border-b border-gray-200 pb-2 mb-2 last:border-0">
                                    <div>
                                        <p class="font-medium">{{ $resp->nome }} {{ $resp->sobrenome }}</p>
                                        <p class="text-gray-500 text-xs">{{ $resp->telefone_principal }}</p>
                                    </div>
                                    @if ($resp->pivot->responsavel_principal)
                                        <span
                                            class="bg-blue-100 text-blue-800 text-[10px] px-2 py-0.5 rounded-full uppercase font-bold">Principal</span>
                                    @endif
                                </div>
                            @empty
                                <p class="text-gray-500">Nenhum responsável vinculado.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- ABA NOTAS -->
            <div id="tab-notas" class="tab-content hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Histórico de Notas</h3>
                    @can('notas.lancar')
                        <x-button onclick="openNotaModal()" color="primary" size="sm">
                            <i class="fas fa-plus mr-1"></i> Lançar Nota
                        </x-button>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disciplina</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referência</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Nota</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($aluno->notas()->with('disciplina')->get() as $nota)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                                        {{ $nota->disciplina->nome ?? 'Disciplina removida' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $nota->referencia }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="px-2 py-1 rounded-md font-bold {{ $nota->valor >= 7 ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50' }}">
                                            {{ number_format($nota->valor, 1, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">
                                        {{ $nota->data_lancamento->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        @can('notas.excluir')
                                            <form action="{{ route('notas.destroy', $nota) }}" method="POST"
                                                onsubmit="return confirm('Tem certeza que deseja excluir esta nota?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"><i
                                                        class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">Nenhuma nota lançada
                                        para este aluno.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ABA ANOTAÇÕES -->
            <div id="tab-anotacoes" class="tab-content hidden">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Anotações e Ocorrências</h3>
                    @can('anotacoes.registrar')
                        <x-button onclick="openAnotacaoModal()" color="primary" size="sm">
                            <i class="fas fa-plus mr-1"></i> Nova Anotação
                        </x-button>
                    @endcan
                </div>

                <div class="space-y-4">
                    @forelse($aluno->anotacoes()->latest()->get() as $anotacao)
                        @php
                            $colors = [
                                'comum' => 'bg-gray-50 border-gray-200 text-gray-800',
                                'grave' => 'bg-red-50 border-red-200 text-red-800',
                                'elogio' => 'bg-green-50 border-green-200 text-green-800',
                                'advertencia' => 'bg-orange-50 border-orange-200 text-orange-800',
                            ];
                            $icons = [
                                'comum' => 'fa-info-circle text-gray-400',
                                'grave' => 'fa-exclamation-triangle text-red-500',
                                'elogio' => 'fa-star text-green-500',
                                'advertencia' => 'fa-exclamation-circle text-orange-500',
                            ];
                        @endphp
                        <div class="p-4 border rounded-lg {{ $colors[$anotacao->tipo] ?? $colors['comum'] }}">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center">
                                    <i class="fas {{ $icons[$anotacao->tipo] ?? $icons['comum'] }} text-lg mr-3"></i>
                                    <div>
                                        <h4 class="font-bold">{{ $anotacao->titulo }}</h4>
                                        <p class="text-xs opacity-75">
                                            Em {{ $anotacao->data_ocorrencia->format('d/m/Y') }} por
                                            {{ $anotacao->usuario->name ?? 'Usuário desconhecido' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    @can('anotacoes.excluir')
                                        <form action="{{ route('alunos-anotacoes.destroy', $anotacao) }}" method="POST"
                                            onsubmit="return confirm('Excluir esta anotação?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700"><i
                                                    class="fas fa-times"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                            <div class="mt-3 text-sm leading-relaxed whitespace-pre-wrap">{{ $anotacao->descricao }}</div>
                        </div>
                    @empty
                        <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                            <i class="fas fa-comments text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-500">Sem registros para este aluno.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL LANÇAR NOTA -->
    <x-modal name="modal-nota" title="Lançar Nova Nota">
        <form action="{{ route('notas.store') }}" method="POST" class="p-4 space-y-4">
            @csrf
            <input type="hidden" name="aluno_id" value="{{ $aluno->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Disciplina</label>
                    <select name="disciplina_id" id="nota_disciplina_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Carregando disciplinas...</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data de Lançamento</label>
                    <input type="date" name="data_lancamento" required value="{{ date('Y-m-d') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Referência (Ex: 1º Bimestre)</label>
                    <input type="text" name="referencia" placeholder="Ex: AV1, 1º Bimestre" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nota (0 a 10)</label>
                    <input type="number" name="valor" step="0.1" min="0" max="10" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Observações (Opcional)</label>
                <textarea name="observacoes" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
            </div>

            <div class="flex justify-end pt-4 gap-2">
                <button type="button" onclick="closeModal('modal-nota')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Salvar
                    Nota</button>
            </div>
        </form>
    </x-modal>

    <!-- MODAL NOVA ANOTAÇÃO -->
    <x-modal name="modal-anotacao" title="Registrar Anotação / Ocorrência">
        <form action="{{ route('alunos-anotacoes.store') }}" method="POST" class="p-4 space-y-4">
            @csrf
            <input type="hidden" name="aluno_id" value="{{ $aluno->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de Gravidade</label>
                    <select name="tipo" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="comum">Comum</option>
                        <option value="elogio">Elogio</option>
                        <option value="advertencia">Advertência</option>
                        <option value="grave">Grave / Ocorrência</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data da Ocorrência</label>
                    <input type="date" name="data_ocorrencia" required value="{{ date('Y-m-d') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Título / Assunto</label>
                <input type="text" name="titulo" placeholder="Ex: Reunião com pais, Comportamento em aula" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Descrição Detalhada</label>
                <textarea name="descricao" rows="4" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
            </div>

            <div class="flex justify-end pt-4 gap-2">
                <button type="button" onclick="closeModal('modal-anotacao')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Salvar
                    Registro</button>
            </div>
        </form>
    </x-modal>

    <script>
        function showTab(tabId) {
            // Ocultar todos os conteúdos
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remover classes ativas dos botões
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-indigo-500', 'text-indigo-600', 'active');
                btn.classList.add('border-transparent', 'text-gray-500');
            });

            // Mostrar conteúdo selecionado
            document.getElementById(tabId).classList.remove('hidden');

            // Ativar botão selecionado
            const activeBtn = document.getElementById('btn-' + tabId);
            activeBtn.classList.add('border-indigo-500', 'text-indigo-600', 'active');
            activeBtn.classList.remove('border-transparent', 'text-gray-500');
        }

        function openNotaModal() {
            showModal('modal-nota');
            loadDisciplinas();
        }

        function openAnotacaoModal() {
            showModal('modal-anotacao');
        }

        function loadDisciplinas() {
            const select = document.getElementById('nota_disciplina_id');
            if (select.children.length > 1 && select.children[0].value !== "") return;

            fetch("{{ route('api.alunos.disciplinas', $aluno->id) }}")
                .then(response => response.json())
                .then(data => {
                    select.innerHTML = '<option value="">Selecione a disciplina</option>';
                    if (data.length === 0) {
                        select.innerHTML = '<option value="">Nenhuma disciplina vinculada à turma</option>';
                        return;
                    }
                    data.forEach(disc => {
                        const option = document.createElement('option');
                        option.value = disc.id;
                        option.textContent = disc.nome;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar disciplinas:', error);
                    select.innerHTML = '<option value="">Erro ao carregar</option>';
                });
        }
    </script>
@endsection
