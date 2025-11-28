@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Escalas', 'url' => route('escalas.index')],
    ['title' => 'Editar Escala']
]" />

<div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ route('escalas.update', $escala->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Informações da Escala -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Informações da Escala</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <x-select
                                name="funcionario_id"
                                label="Funcionário *"
                                required
                            >
                                <option value="">Selecione um funcionário</option>
                                @foreach($funcionarios as $funcionario)
                                    <option value="{{ $funcionario->id }}" {{ (old('funcionario_id') ?? $escala->funcionario_id) == $funcionario->id ? 'selected' : '' }}>
                                        {{ $funcionario->nome }} {{ $funcionario->sobrenome }} - {{ $funcionario->cargo }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div>
                            <x-input
                                type="date"
                                name="data"
                                label="Data *"
                                :value="old('data') ?? $escala->data_input"
                                required
                            />
                            <p class="text-sm text-gray-600 mt-1">Data: {{ $escala->data_formatada }}</p>
                        </div>

                        <div>
                            <x-select
                                name="tipo_escala"
                                label="Tipo de Escala *"
                                required
                            >
                                <option value="">Selecione o tipo</option>
                                <option value="Normal" {{ (old('tipo_escala') ?? $escala->tipo_escala) == 'Normal' ? 'selected' : '' }}>Normal</option>
                                <option value="Extra" {{ (old('tipo_escala') ?? $escala->tipo_escala) == 'Extra' ? 'selected' : '' }}>Extra</option>
                                <option value="Substituição" {{ (old('tipo_escala') ?? $escala->tipo_escala) == 'Substituição' ? 'selected' : '' }}>Substituição</option>
                            </x-select>
                        </div>

                        <div>
                            <x-input
                                type="time"
                                name="hora_inicio"
                                label="Hora de Início *"
                                :value="old('hora_inicio') ?? $escala->hora_inicio"
                                required
                            />
                        </div>

                        <div>
                            <x-input
                                type="time"
                                name="hora_fim"
                                label="Hora de Fim *"
                                :value="old('hora_fim') ?? $escala->hora_fim"
                                required
                            />
                        </div>

                        <div>
                            <x-select
                                name="tipo_atividade"
                                label="Tipo de Atividade *"
                                required
                            >
                                <option value="">Selecione o tipo</option>
                                <option value="em_sala" {{ (old('tipo_atividade') ?? $escala->tipo_atividade) == 'em_sala' ? 'selected' : '' }}>Em Sala</option>
                                <option value="pl" {{ (old('tipo_atividade') ?? $escala->tipo_atividade) == 'pl' ? 'selected' : '' }}>PL (Planejamento)</option>
                                <option value="ausente" {{ (old('tipo_atividade') ?? $escala->tipo_atividade) == 'ausente' ? 'selected' : '' }}>Ausente</option>
                            </x-select>
                        </div>
                        
                        <div>
                            <x-select
                                name="sala_id"
                                label="Sala"
                            >
                                <option value="">Selecione uma sala</option>
                                @foreach($salas as $sala)
                                    <option value="{{ $sala->id }}" {{ (old('sala_id') ?? $escala->sala_id) == $sala->id ? 'selected' : '' }}>
                                        {{ $sala->codigo }} - {{ $sala->nome }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>
                        
                        <div>
                            <x-select
                                name="status"
                                label="Status *"
                                required
                            >
                                <option value="">Selecione o status</option>
                                <option value="Agendada" {{ (old('status') ?? $escala->status) == 'Agendada' ? 'selected' : '' }}>Agendada</option>
                                <option value="Ativa" {{ (old('status') ?? $escala->status) == 'Ativa' ? 'selected' : '' }}>Ativa</option>
                                <option value="Concluída" {{ (old('status') ?? $escala->status) == 'Concluída' ? 'selected' : '' }}>Concluída</option>
                            </x-select>
                        </div>
                    </div>
                </div>

                <!-- Observações -->
                <div>
                    <h4 class="text-md font-semibold text-gray-800 mb-4">Observações</h4>
                    <div>
                        <x-textarea
                            name="observacoes"
                            label="Observações"
                            :value="old('observacoes') ?? $escala->observacoes"
                            placeholder="Observações adicionais sobre a escala"
                            rows="3"
                        />
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <x-button href="{{ route('escalas.index') }}" color="secondary">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </x-button>
                    <x-button type="submit" color="primary">
                        <i class="fas fa-save mr-1"></i> Atualizar Escala
                    </x-button>
                </div>
            </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoAtividadeSelect = document.querySelector('select[name="tipo_atividade"]');
    const salaSelect = document.querySelector('select[name="sala_id"]');
    const salaDiv = salaSelect.closest('div');
    
    function toggleSalaRequired() {
        const isEmSala = tipoAtividadeSelect.value === 'em_sala';
        
        if (isEmSala) {
            salaSelect.setAttribute('required', 'required');
            salaDiv.querySelector('label').innerHTML = 'Sala *';
        } else {
            salaSelect.removeAttribute('required');
            salaDiv.querySelector('label').innerHTML = 'Sala';
            salaSelect.value = '';
        }
    }
    
    tipoAtividadeSelect.addEventListener('change', toggleSalaRequired);
    
    // Executar na inicialização
    toggleSalaRequired();
});
</script>

@endsection