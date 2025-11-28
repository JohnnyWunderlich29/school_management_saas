@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Salas', 'url' => route('salas.index')],
    ['title' => $sala->nome, 'url' => '#']
]" />

<div class="max-w-4xl mx-auto">
    <x-card>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Detalhes da Sala</h2>
            <div class="flex space-x-2">
                @permission('salas.editar')
                    <x-button href="{{ route('salas.edit', $sala->id) }}" color="warning">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </x-button>
                @endpermission
                <x-button href="{{ route('salas.index') }}" color="secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informações Básicas -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-door-open mr-2"></i>Informações Básicas
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nome</label>
                        <p class="text-gray-900">{{ $sala->nome }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Código</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $sala->codigo }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Escola</label>
                        <p class="text-gray-900">{{ $sala->escola->nome ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Capacidade</label>
                        <p class="text-gray-900">
                            <i class="fas fa-users mr-1 text-blue-500"></i>
                            {{ $sala->capacidade ?? 'Não informado' }} {{ $sala->capacidade ? 'pessoas' : '' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Tipo</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-tag mr-1"></i>
                            {{ $sala->tipo ?? 'Não informado' }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Status</label>
                        <div class="flex items-center">
                            <i class="fas fa-{{ $sala->ativo ? 'check-circle text-green-500' : 'times-circle text-red-500' }} mr-2"></i>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sala->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $sala->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status e Informações Adicionais -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>Status e Informações
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Status</label>
                        @if($sala->ativo)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Ativa
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>
                                Inativa
                            </span>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Data de Criação</label>
                        <p class="text-gray-900">{{ $sala->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Última Atualização</label>
                        <p class="text-gray-900">{{ $sala->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($sala->descricao)
            <!-- Descrição -->
            <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-file-alt mr-2"></i>Descrição
                </h3>
                <p class="text-gray-700">{{ $sala->descricao }}</p>
            </div>
        @endif



        <!-- Ações -->
        <div class="mt-6 flex justify-end space-x-3">
            @permission('salas.editar')
                <form action="{{ route('salas.toggle-status', $sala) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <x-button
                        type="submit"
                        color="{{ $sala->ativo ? 'secondary' : 'success' }}"
                        onclick="return confirm('Tem certeza que deseja {{ $sala->ativo ? 'desativar' : 'ativar' }} esta sala?')"
                    >
                        <i class="fas fa-{{ $sala->ativo ? 'pause' : 'play' }} mr-1"></i>
                        {{ $sala->ativo ? 'Desativar' : 'Ativar' }} Sala
                    </x-button>
                </form>
            @endpermission
            
            @permission('salas.excluir')
                <form action="{{ route('salas.destroy', $sala) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <x-button
                        type="submit"
                        color="danger"
                        onclick="return confirm('Tem certeza que deseja excluir esta sala? Esta ação não pode ser desfeita.')"
                    >
                        <i class="fas fa-trash mr-1"></i> Excluir Sala
                    </x-button>
                </form>
            @endpermission
        </div>
    </x-card>
</div>
@endsection