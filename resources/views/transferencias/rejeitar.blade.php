@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Transferências', 'url' => route('transferencias.index')],
    ['title' => 'Rejeitar Transferência']
]" />

<div class="bg-white rounded-lg shadow-sm p-6">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Rejeitar Transferência</h2>
        
        <!-- Informações da Transferência -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Detalhes da Transferência</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500">Aluno:</span>
                    <p class="text-sm text-gray-900">{{ $transferencia->aluno->nome }} {{ $transferencia->aluno->sobrenome }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Turma Atual:</span>
                    <p class="text-sm text-gray-900">{{ $transferencia->turmaOrigem->nome }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Turma Destino:</span>
                    <p class="text-sm text-gray-900">{{ $transferencia->turmaDestino->nome }}</p>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500">Solicitado por:</span>
                    <p class="text-sm text-gray-900">{{ $transferencia->solicitante->name }}</p>
                </div>
                @if($transferencia->motivo)
                <div class="col-span-2">
                    <span class="text-sm font-medium text-gray-500">Motivo:</span>
                    <p class="text-sm text-gray-900">{{ $transferencia->motivo }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <form action="{{ route('transferencias.rejeitar', $transferencia->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PATCH')
        
        <div class="mb-4">
            <p class="text-sm text-gray-600 mb-4">Tem certeza que deseja rejeitar esta transferência?</p>
            <div>
                <label for="motivo_rejeicao" class="block text-sm font-medium text-gray-700 mb-2">Motivo da Rejeição *</label>
                <textarea class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" id="motivo_rejeicao" name="motivo_rejeicao" rows="3" placeholder="Digite o motivo da rejeição..." required></textarea>
            </div>
        </div>
        
        <!-- Botões de Ação -->
        <div class="flex justify-end space-x-3 pt-4 border-t">
            <x-button href="{{ route('transferencias.index') }}" color="secondary">
                <i class="fas fa-times mr-1"></i> Cancelar
            </x-button>
            <x-button type="submit" color="primary" class="bg-red-600 hover:bg-red-700">
                <i class="fas fa-times mr-1"></i> Rejeitar Transferência
            </x-button>
        </div>
    </form>
</div>
@endsection