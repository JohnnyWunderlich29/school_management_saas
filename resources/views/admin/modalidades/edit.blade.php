@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Modalidades de Ensino', 'url' => route('admin.modalidades.index')],
    ['title' => $modalidade->nome, 'url' => route('admin.modalidades.show', $modalidade)],
    ['title' => 'Editar', 'url' => '#']
]" />

<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="{{ route('admin.modalidades.show', $modalidade) }}" class="text-gray-600 hover:text-gray-800 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Editar Modalidade: {{ $modalidade->nome }}</h1>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6">
            <form action="{{ route('admin.modalidades.update', $modalidade) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="codigo" class="block text-sm font-medium text-gray-700 mb-2">
                            Código
                        </label>
                        <input type="text" 
                               id="codigo" 
                               name="codigo" 
                               value="{{ old('codigo', $modalidade->codigo) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               readonly>
                        <p class="text-xs text-gray-500 mt-1">Código gerado automaticamente (não pode ser alterado)</p>
                    </div>

                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nome" 
                               name="nome" 
                               value="{{ old('nome', $modalidade->nome) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: Educação Básica"
                               maxlength="100"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Nome da modalidade de ensino</p>
                    </div>

                    <div>
                        <label for="nivel" class="block text-sm font-medium text-gray-700 mb-2">
                            Nível/Etapa
                        </label>
                        <input type="text" 
                               id="nivel" 
                               name="nivel" 
                               value="{{ old('nivel', $modalidade->nivel) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: Educação Infantil"
                               maxlength="100">
                        <p class="text-xs text-gray-500 mt-1">Nível ou etapa da modalidade de ensino (opcional)</p>
                    </div>

                    <div class="md:col-span-2">
                        <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                            Descrição
                        </label>
                        <textarea id="descricao" 
                                  name="descricao" 
                                  rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Descrição detalhada da modalidade de ensino..."
                                  maxlength="500">{{ old('descricao', $modalidade->descricao) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Descrição opcional da modalidade (máximo 500 caracteres)</p>
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="ativo" 
                                   name="ativo" 
                                   value="1"
                                   {{ old('ativo', $modalidade->ativo) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="ativo" class="ml-2 block text-sm text-gray-900">
                                Modalidade ativa
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Modalidades inativas não aparecerão nas opções de seleção</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-8">
                    <a href="{{ route('admin.configuracoes.index', ['tab' => 'modalidades']) }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>


    </div>
</div>
@endsection