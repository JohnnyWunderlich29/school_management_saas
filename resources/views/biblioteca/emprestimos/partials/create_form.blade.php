<form id="formEmprestimoModal" action="{{ route('biblioteca.emprestimos.store') }}" method="POST" class="space-y-4">
    @csrf
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
            <p class="text-xs text-gray-500 mt-1">Deixe em branco para usar política padrão.</p>
        </div>
        <div>
            <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>
            Criar Empréstimo
        </button>
    </div>
</form>