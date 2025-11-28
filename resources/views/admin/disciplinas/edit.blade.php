@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Disciplinas', 'url' => route('admin.disciplinas.index')],
    ['title' => 'Editar Disciplina', 'url' => '#']
]" />

    <x-card>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Editar Disciplina</h1>
            <p class="mt-1 text-sm text-gray-600">Edite as informações da disciplina "{{ $disciplina->nome }}"</p>
        </div>

        <form action="{{ route('admin.disciplinas.update', $disciplina) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="nome" 
                        label="Nome da Disciplina" 
                        placeholder="Ex: Matemática"
                        value="{{ old('nome', $disciplina->nome) }}"
                        required
                    />
                </div>

                <div>
                    <x-input 
                        name="codigo" 
                        label="Código" 
                        placeholder="Ex: MAT"
                        value="{{ old('codigo', $disciplina->codigo) }}"
                        required
                    />
                </div>
            </div>

            <div>
                <x-select 
                    name="area_conhecimento" 
                    label="Área de Conhecimento" 
                    required
                    empty-option="Selecione uma área"
                    :options="array_combine($areasConhecimento, $areasConhecimento)"
                    :selected="old('area_conhecimento', $disciplina->area_conhecimento)"
                />
            </div>

            <div>
                <x-textarea 
                    name="descricao" 
                    label="Descrição" 
                    placeholder="Descrição detalhada da disciplina"
                    rows="3"
                >{{ old('descricao', $disciplina->descricao) }}</x-textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input 
                        name="cor_hex" 
                        label="Cor da Disciplina" 
                        type="color"
                        placeholder="#3B82F6"
                        value="{{ old('cor_hex', $disciplina->cor_hex) }}"
                    />
                    <p class="mt-1 text-xs text-gray-500">Cor para identificação visual da disciplina</p>
                </div>

                <div>
                    <x-input 
                        name="ordem" 
                        label="Ordem de Exibição" 
                        type="number"
                        placeholder="1"
                        min="1"
                        value="{{ old('ordem', $disciplina->ordem) }}"
                    />
                </div>

                <div class="flex items-center space-x-4 pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="obrigatoria" value="1" 
                               {{ old('obrigatoria', $disciplina->obrigatoria) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Obrigatória</span>
                    </label>
                    
                    <label class="flex items-center">
                        <input type="checkbox" name="ativo" value="1" 
                               {{ old('ativo', $disciplina->ativo) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Ativo</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t">
                <x-button href="{{ route('admin.configuracoes.index', ['tab' => 'disciplinas']) }}" color="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" color="primary">
                    <i class="fas fa-save mr-1"></i> Atualizar Disciplina
                </x-button>
            </div>
        </form>
    </x-card>
@endsection