@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Perfil', 'url' => '#']
]" />

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Meu Perfil</h1>
            <p class="text-gray-600">Visualize e gerencie suas informações pessoais</p>
        </div>
        <x-button
            color="primary"
            href="{{ route('profile.edit') }}"
        >
            <i class="fas fa-edit mr-2"></i>
            Editar Perfil
        </x-button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-user text-indigo-600 mr-2"></i>
                    Informações Pessoais
                </div>
            </x-slot>
            
            <div class="space-y-4">
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Nome:</span>
                    <span class="text-gray-900">{{ $user->name }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Email:</span>
                    <span class="text-gray-900">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Membro desde:</span>
                    <span class="text-gray-900">{{ $user->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="font-medium text-gray-700">Última atualização:</span>
                    <span class="text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </x-card>

        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-briefcase text-indigo-600 mr-2"></i>
                    Cargos e Permissões
                </div>
            </x-slot>
            
            @if($user->cargos->count() > 0)
                <div class="space-y-4">
                    <div>
                        <h6 class="font-medium text-gray-900 mb-2">Cargos Atribuídos:</h6>
                        <div class="flex flex-wrap gap-2">
                            @foreach($user->cargos as $cargo)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $cargo->nome }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    
                    <div>
                        <h6 class="font-medium text-gray-900 mb-2">Total de Permissões:</h6>
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $user->todasPermissoes()->count() }}</div>
                            <p class="text-sm text-blue-800">Permissões Ativas</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-briefcase text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-500">Nenhum cargo atribuído.</p>
                </div>
            @endif
        </x-card>
    </div>

    @if($user->cargos->count() > 0)
        <div class="mt-8">
            <x-card>
                <x-slot name="title">
                    <div class="flex items-center">
                        <i class="fas fa-key text-indigo-600 mr-2"></i>
                        Detalhes das Permissões
                    </div>
                </x-slot>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($user->todasPermissoes()->groupBy('modulo') as $modulo => $permissoes)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h6 class="font-medium text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-folder text-indigo-600 mr-2"></i>
                                {{ ucfirst($modulo) }}
                            </h6>
                            <div class="space-y-2">
                                @foreach($permissoes as $permissao)
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $permissao->nome }}
                                        </span>
                                        @if($permissao->descricao)
                                            <p class="text-sm text-gray-500 mt-1">{{ $permissao->descricao }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    @endif
</div>
@endsection