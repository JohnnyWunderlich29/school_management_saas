@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Presenças', 'url' => route('presencas.index')],
    ['title' => 'Editar Presença']
]" />

<div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ route('presencas.update', $presenca->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Informações da Presença -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Informações da Presença</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <x-select
                                name="aluno_id"
                                label="Aluno *"
                                required
                            >
                                <option value="">Selecione um aluno</option>
                                @foreach($alunos as $aluno)
                                    <option value="{{ $aluno->id }}" {{ (old('aluno_id') ?? $presenca->aluno_id) == $aluno->id ? 'selected' : '' }}>
                                        {{ $aluno->nome }} {{ $aluno->sobrenome }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div>
                            <x-input
                                type="date"
                                name="data"
                                label="Data *"
                                :value="old('data') ?? $presenca->data"
                                required
                            />
                        </div>

                        <div>
                            <x-select
                                name="presente"
                                label="Status *"
                                required
                            >
                                <option value="">Selecione o status</option>
                                <option value="1" {{ (old('presente') ?? $presenca->presente) == '1' ? 'selected' : '' }}>Presente</option>
                                <option value="0" {{ (old('presente') ?? $presenca->presente) == '0' ? 'selected' : '' }}>Ausente</option>
                            </x-select>
                        </div>

                        <div>
                            <x-select
                                name="funcionario_id"
                                label="Registrado por *"
                                required
                            >
                                <option value="">Selecione um funcionário</option>
                                @foreach($funcionarios as $funcionario)
                                    <option value="{{ $funcionario->id }}" {{ (old('funcionario_id') ?? $presenca->funcionario_id) == $funcionario->id ? 'selected' : '' }}>
                                        {{ $funcionario->nome }} {{ $funcionario->sobrenome }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                </div>

                <!-- Horários (apenas se presente) -->
                <div id="horariosContainer" class="{{ $presenca->presente ? '' : 'hidden' }}">
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Horários</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input
                                type="time"
                                name="hora_entrada"
                                label="Horário de Entrada"
                                :value="old('hora_entrada') ?? ($presenca->hora_entrada ? \Carbon\Carbon::parse($presenca->hora_entrada)->format('H:i') : '')"
                            />
                        </div>

                        <div>
                            <x-input
                                type="time"
                                name="hora_saida"
                                label="Horário de Saída"
                                :value="old('hora_saida') ?? ($presenca->hora_saida ? \Carbon\Carbon::parse($presenca->hora_saida)->format('H:i') : '')"
                            />
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Observações</h4>
                    <div>
                        <x-textarea
                            name="observacao"
                            label="Observação"
                            :value="old('observacao') ?? $presenca->observacao"
                            rows="3"
                            placeholder="Adicione uma observação se necessário"
                        />
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <x-button href="{{ route('presencas.index') }}" color="secondary">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </x-button>
                    <x-button type="submit" color="primary">
                        <i class="fas fa-save mr-1"></i> Atualizar Presença
                    </x-button>
                </div>
            </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const presenteSelect = document.querySelector('select[name="presente"]');
    const horariosContainer = document.getElementById('horariosContainer');
    
    // Função para mostrar/ocultar os campos de horário baseado no status de presença
    function toggleHorarios() {
        if (presenteSelect.value === '1') {
            horariosContainer.classList.remove('hidden');
        } else {
            horariosContainer.classList.add('hidden');
        }
    }
    
    // Adicionar event listener para mudanças no select
    presenteSelect.addEventListener('change', toggleHorarios);
    
    // Executar a função na inicialização
    toggleHorarios();
});
</script>
@endpush

@endsection