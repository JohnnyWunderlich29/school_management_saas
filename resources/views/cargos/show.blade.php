@extends('layouts.app')

@section('title', 'Detalhes do Cargo')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Cargos', 'url' => route('cargos.index')],
    ['title' => $cargo->nome, 'url' => '#']
]" />

<x-card>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header responsivo -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-4 sm:space-y-0">
        <div class="flex-1">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Detalhes do Cargo</h1>
            <p class="text-gray-600 text-sm sm:text-base">Informações completas do cargo {{ $cargo->nome }}</p>
        </div>
        <!-- Botões responsivos -->
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
            @permission('cargos.editar')
                <x-button
                    color="warning"
                    href="{{ route('cargos.edit', $cargo) }}"
                    class="min-h-[48px] flex items-center justify-center"
                >
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </x-button>
            @endpermission
            <x-button
                color="secondary"
                href="{{ route('cargos.index') }}"
                class="min-h-[48px] flex items-center justify-center"
            >
                <i class="fas fa-arrow-left mr-2"></i>
                Voltar
            </x-button>
        </div>
    </div>

    <!-- Card principal com informações do cargo -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8 mb-6 sm:mb-8">
        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-briefcase text-indigo-600 mr-2"></i>
                    Informações do Cargo
                </div>
            </x-slot>
            
            <!-- Layout mobile otimizado -->
            <div class="space-y-3 sm:space-y-4">
                <div class="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700 text-sm sm:text-base">ID:</span>
                    <span class="text-gray-900 text-sm sm:text-base mt-1 sm:mt-0">{{ $cargo->id }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700 text-sm sm:text-base">Nome:</span>
                    <span class="text-gray-900 text-sm sm:text-base mt-1 sm:mt-0 font-medium">{{ $cargo->nome }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700 text-sm sm:text-base">Descrição:</span>
                    <span class="text-gray-900 text-sm sm:text-base mt-1 sm:mt-0">{{ $cargo->descricao ?: 'Não informada' }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700 text-sm sm:text-base">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1 sm:mt-0 self-start sm:self-auto {{ $cargo->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $cargo->ativo ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700 text-sm sm:text-base">Criado em:</span>
                    <span class="text-gray-900 text-sm sm:text-base mt-1 sm:mt-0">{{ $cargo->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-between py-2">
                    <span class="font-medium text-gray-700 text-sm sm:text-base">Atualizado em:</span>
                    <span class="text-gray-900 text-sm sm:text-base mt-1 sm:mt-0">{{ $cargo->updated_at->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>
        </x-card>

        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-users text-indigo-600 mr-2"></i>
                    Usuários com este Cargo
                    <span class="ml-2 bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $cargo->users->count() }}</span>
                </div>
            </x-slot>
            
            @if($cargo->users->count() > 0)
                <!-- Layout desktop -->
                <div class="hidden sm:block space-y-4">
                    @foreach($cargo->users as $user)
                        <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center hover:bg-gray-100 transition-colors">
                            <div>
                                <h6 class="font-medium text-gray-900">{{ $user->name }}</h6>
                                <p class="text-sm text-gray-600">{{ $user->email }}</p>
                            </div>
                            @permission('usuarios.visualizar')
                                <a href="{{ route('usuarios.show', $user) }}" class="text-indigo-600 hover:text-indigo-900 p-2">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endpermission
                        </div>
                    @endforeach
                </div>
                
                <!-- Layout mobile -->
                <div class="sm:hidden space-y-3">
                    @foreach($cargo->users as $user)
                        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mr-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h6 class="font-medium text-gray-900 truncate">{{ $user->name }}</h6>
                                    <p class="text-sm text-gray-600 truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                            @permission('usuarios.visualizar')
                                <div class="flex justify-end">
                                    <a href="{{ route('usuarios.show', $user) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm flex items-center min-h-[44px]">
                                        <i class="fas fa-eye mr-2"></i>Ver Detalhes
                                    </a>
                                </div>
                            @endpermission
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-users text-gray-400 text-2xl sm:text-3xl mb-3"></i>
                    <p class="text-gray-500 text-sm sm:text-base">Nenhum usuário possui este cargo.</p>
                </div>
            @endif
        </x-card>
    </div>

    <div class="mt-8 mb-8">
        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-key text-indigo-600 mr-2"></i>
                    Permissões do Cargo
                    <span class="ml-2 bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $cargo->permissoes->count() }}</span>
                </div>
            </x-slot>
            
            @if($cargo->permissoes->count() > 0)
                <!-- Layout desktop -->
                <div class="hidden sm:grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($cargo->permissoes->groupBy('modulo') as $modulo => $permissoes)
                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                            <h6 class="font-medium text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-folder text-indigo-600 mr-2"></i>
                                {{ ucfirst($modulo) }}
                            </h6>
                            <div class="space-y-3">
                                @foreach($permissoes as $permissao)
                                    <div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
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
                
                <!-- Layout mobile -->
                <div class="sm:hidden space-y-4">
                    @foreach($cargo->permissoes->groupBy('modulo') as $modulo => $permissoes)
                        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mr-3">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <h6 class="font-medium text-gray-900 text-lg">{{ ucfirst($modulo) }}</h6>
                            </div>
                            <div class="space-y-3">
                                @foreach($permissoes as $permissao)
                                    <div class="bg-indigo-50 rounded-lg p-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mb-2">
                                            {{ $permissao->nome }}
                                        </span>
                                        @if($permissao->descricao)
                                            <p class="text-sm text-gray-600">{{ $permissao->descricao }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-key text-gray-400 text-2xl sm:text-3xl mb-3"></i>
                    <p class="text-gray-500 text-sm sm:text-base">Este cargo não possui permissões atribuídas.</p>
                </div>
            @endif
        </x-card>
    </div>

    @if($cargo->permissoes->count() > 0)
        <div class="mt-8 mb-8">
            <x-card>
                <x-slot name="title">
                    <div class="flex items-center">
                        <i class="fas fa-chart-bar text-indigo-600 mr-2"></i>
                        Resumo de Permissões
                    </div>
                </x-slot>
                
                <!-- Grid responsivo para estatísticas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    <div class="bg-blue-50 rounded-lg p-4 sm:p-6 text-center">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 mx-auto mb-2 sm:mb-3">
                            <i class="fas fa-key text-lg sm:text-xl"></i>
                        </div>
                        <div class="text-2xl sm:text-3xl font-bold text-blue-600">{{ $cargo->permissoes->count() }}</div>
                        <p class="text-xs sm:text-sm text-blue-800 mt-1">Total de Permissões</p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-4 sm:p-6 text-center">
                        <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mx-auto mb-2 sm:mb-3">
                            <i class="fas fa-folder text-lg sm:text-xl"></i>
                        </div>
                        <div class="text-2xl sm:text-3xl font-bold text-indigo-600">{{ $cargo->permissoes->groupBy('modulo')->count() }}</div>
                        <p class="text-xs sm:text-sm text-indigo-800 mt-1">Módulos Cobertos</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 sm:p-6 text-center">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 mx-auto mb-2 sm:mb-3">
                            <i class="fas fa-users text-lg sm:text-xl"></i>
                        </div>
                        <div class="text-2xl sm:text-3xl font-bold text-green-600">{{ $cargo->users->count() }}</div>
                        <p class="text-xs sm:text-sm text-green-800 mt-1">Usuários Atribuídos</p>
                    </div>
                    <div class="{{ $cargo->ativo ? 'bg-green-50' : 'bg-gray-50' }} rounded-lg p-4 sm:p-6 text-center">
                        <div class="w-12 h-12 rounded-full {{ $cargo->ativo ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center mx-auto mb-2 sm:mb-3">
                            <i class="fas {{ $cargo->ativo ? 'fa-check-circle' : 'fa-times-circle' }} text-lg sm:text-xl"></i>
                        </div>
                        <div class="text-lg sm:text-2xl font-bold {{ $cargo->ativo ? 'text-green-600' : 'text-gray-600' }}">
                            {{ $cargo->ativo ? 'ATIVO' : 'INATIVO' }}
                        </div>
                        <p class="text-xs sm:text-sm {{ $cargo->ativo ? 'text-green-800' : 'text-gray-800' }} mt-1">Status do Cargo</p>
                    </div>
                </div>
            </x-card>
        </div>
    @endif
</div>
</x-card>
@endsection