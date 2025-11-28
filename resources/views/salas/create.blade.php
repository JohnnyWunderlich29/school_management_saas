@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Salas', 'url' => route('salas.index')],
    ['title' => 'Nova Sala', 'url' => '#']
]" />

<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="{{ route('salas.index') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Nova Sala</h1>
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
            <form action="{{ route('salas.store') }}" method="POST">
                @csrf
                
                <!-- Seção: Informações Básicas -->
                <div class="mb-8">
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Informações Básicas
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-2">
                            <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome da Sala <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nome" 
                                   name="nome" 
                                   value="{{ old('nome') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Ex: Sala Infantil A"
                                   required>
                        </div>

                        <!-- Campo código removido - gerado automaticamente pelo sistema -->

                        <div>
                            <label for="capacidade" class="block text-sm font-medium text-gray-700 mb-2">
                                Capacidade <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="capacidade" 
                                   name="capacidade" 
                                   value="{{ old('capacidade') }}" 
                                   min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Ex: 30"
                                   required>
                            @error('capacidade')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="tipo" 
                                   name="tipo" 
                                   value="{{ old('tipo') }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Ex: Berçário, Maternal, Fundamental I, Laboratório, etc."
                                   required>
                            @error('tipo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Digite o tipo da sala conforme a necessidade da sua escola</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição <span class="text-gray-500">(opcional)</span>
                            </label>
                            <textarea id="descricao"
                                      name="descricao"
                                      rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Descrição da sala...">{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>



                <!-- Seção: Gestão e Status -->
                <div class="mb-8">
                    <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-users-cog text-purple-600 mr-2"></i>
                        Gestão e Status
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-2">
                            <div class="flex items-center">
                                <input type="checkbox" name="ativo" id="ativo" value="1" checked
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="ativo" class="ml-2 block text-sm text-gray-900">
                                    Sala ativa
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-8">
                    <x-button href="{{ route('salas.index') }}" color="secondary" class="w-full sm:w-auto">
                    <span class="hidden sm:inline">Cancelar</span>
                    <span class="sm:hidden">Cancelar</span>
                </x-button>
                <x-button type="submit" color="primary" class="w-full sm:w-auto">
                    <i class="fas fa-plus mr-1"></i> 
                    <span class="hidden sm:inline">Criar Sala</span>
                    <span class="sm:hidden">Criar</span>
                </x-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Converter código para maiúsculo automaticamente
    // Código JavaScript para o campo código removido
</script>
@endpush