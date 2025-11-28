@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Turnos', 'url' => route('admin.turnos.index')],
    ['title' => 'Editar Turno', 'url' => '#']
]" />

    <x-card>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Editar Turno</h1>
            <p class="mt-1 text-sm text-gray-600">Edite as informações do turno "{{ $turno->nome }}"</p>
        </div>

        <form action="{{ route('admin.turnos.update', $turno) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="nome" 
                        label="Nome do Turno" 
                        placeholder="Ex: Matutino"
                        value="{{ old('nome', $turno->nome) }}"
                        required
                    />
                </div>

                <div>
                    <x-input 
                        name="codigo" 
                        label="Código" 
                        placeholder="Ex: MAT"
                        value="{{ old('codigo', $turno->codigo) }}"
                        required
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="hora_inicio" 
                        label="Hora de Início" 
                        type="time"
                        placeholder="07:00"
                        value="{{ old('hora_inicio', $turno->hora_inicio) }}"
                    />
                </div>

                <div>
                    <x-input 
                        name="hora_fim" 
                        label="Hora de Fim" 
                        type="time"
                        placeholder="12:00"
                        value="{{ old('hora_fim', $turno->hora_fim) }}"
                    />
                </div>
            </div>

            <div>
                <x-textarea 
                    name="descricao" 
                    label="Descrição" 
                    placeholder="Descrição detalhada do turno"
                    rows="3"
                >{{ old('descricao', $turno->descricao) }}</x-textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="ordem" 
                        label="Ordem de Exibição" 
                        type="number"
                        placeholder="1"
                        min="1"
                        value="{{ old('ordem', $turno->ordem) }}"
                    />
                </div>

                <div class="flex items-center space-x-4 pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="ativo" value="1" 
                               {{ old('ativo', $turno->ativo) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Turno ativo</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t">
                <x-button href="{{ route('admin.configuracoes.index', ['tab' => 'turnos']) }}" color="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" color="primary">
                    <i class="fas fa-save mr-1"></i> Atualizar Turno
                </x-button>
            </div>
        </form>
    </x-card>
@endsection