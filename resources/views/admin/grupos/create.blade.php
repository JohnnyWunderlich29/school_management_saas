@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Grupos Educacionais', 'url' => route('admin.grupos.index')],
    ['title' => 'Novo Grupo', 'url' => '#']
]" />

    <x-card>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Novo Grupo Educacional</h1>
            <p class="mt-1 text-sm text-gray-600">Cadastre um novo grupo educacional</p>
        </div>

        <form action="{{ route('admin.grupos.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <x-input 
                    name="nome" 
                    label="Nome do Grupo" 
                    placeholder="Ex: Educação Infantil I"
                    required
                />
                <p class="mt-1 text-sm text-gray-500">O código será gerado automaticamente com base no nome do grupo.</p>
            </div>

            <div>
                <x-select
                    name="modalidade_ensino_id"
                    label="Modalidade de Ensino *"
                    placeholder="Selecione uma modalidade"
                    :options="$modalidades->pluck('nome', 'id')->toArray()"
                    :selected="old('modalidade_ensino_id')"
                    required
                />
            </div>

            <div>
                <x-input 
                    name="idades" 
                    label="Faixa Etária" 
                    placeholder="Ex: 0-3 anos"
                />
            </div>

            <div>
                <x-textarea 
                    name="descricao" 
                    label="Descrição" 
                    placeholder="Descrição detalhada do grupo educacional"
                    rows="3"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="ordem" 
                        label="Ordem de Exibição" 
                        type="number"
                        placeholder="1"
                        min="1"
                    />
                </div>

                <div class="flex items-center space-x-4 pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="ativo" value="1" checked 
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
                    <i class="fas fa-save mr-1"></i> Salvar Grupo
                </x-button>
            </div>
        </form>
    </x-card>
@endsection