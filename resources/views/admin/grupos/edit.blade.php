@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Grupos Educacionais', 'url' => route('admin.grupos.index')],
    ['title' => 'Editar Grupo', 'url' => '#']
]" />

    <x-card>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Editar Grupo Educacional</h1>
            <p class="mt-1 text-sm text-gray-600">Edite as informações do grupo "{{ $grupo->nome }}"</p>
        </div>

        <form action="{{ route('admin.grupos.update', $grupo) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="nome" 
                        label="Nome do Grupo" 
                        placeholder="Ex: Educação Infantil I"
                        value="{{ old('nome', $grupo->nome) }}"
                        required
                    />
                </div>

                <div>
                    <x-input 
                        name="codigo" 
                        label="Código" 
                        placeholder="Ex: EI1"
                        value="{{ old('codigo', $grupo->codigo) }}"
                        required
                    />
                </div>
            </div>

            <div>
                <x-select
                    name="modalidade_ensino_id"
                    label="Modalidade de Ensino *"
                    placeholder="Selecione uma modalidade"
                    :options="$modalidades->pluck('nome', 'id')->toArray()"
                    :selected="old('modalidade_ensino_id', $grupo->modalidade_ensino_id)"
                    required
                />
            </div>

            <div>
                <x-input 
                    name="idades" 
                    label="Faixa Etária" 
                    placeholder="Ex: 0-3 anos"
                    value="{{ old('idade_minima', $grupo->idade_minima) }}"
                />
            </div>

            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                <textarea 
                    name="descricao" 
                    id="descricao" 
                    placeholder="Descrição detalhada do grupo educacional"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >{{ old('descricao', $grupo->descricao) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="ordem" 
                        label="Ordem de Exibição" 
                        type="number"
                        placeholder="1"
                        min="1"
                        value="{{ old('ordem', $grupo->ordem) }}"
                    />
                </div>

                <div class="flex items-center space-x-4 pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="ativo" value="1" 
                               {{ old('ativo', $grupo->ativo) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Grupo ativo</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t">
                <x-button href="{{ route('admin.configuracoes.index', ['tab' => 'grupos']) }}" color="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" color="primary">
                    <i class="fas fa-save mr-1"></i> Atualizar Grupo
                </x-button>
            </div>
        </form>
    </x-card>
@endsection