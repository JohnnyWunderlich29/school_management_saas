@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Usuários', 'url' => route('usuarios.index')],
    ['title' => $user->name, 'url' => '#']
]" />

<!-- Layout Desktop -->
<x-card>
<div class="hidden md:block">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detalhes do Usuário</h1>
                <p class="text-gray-600">Informações completas do usuário {{ $user->name }}</p>
            </div>
            <div class="flex space-x-3">
                @permission('usuarios.editar')
                    <x-button
                        color="warning"
                        href="{{ route('usuarios.edit', $user) }}"
                    >
                        <i class="fas fa-edit mr-2"></i>
                        Editar
                    </x-button>
                @endpermission
                <x-button
                    color="secondary"
                    href="{{ route('usuarios.index') }}"
                >
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <x-card>
                <x-slot name="title">
                    <div class="flex items-center">
                        <i class="fas fa-user text-indigo-600 mr-2"></i>
                        Informações Básicas
                    </div>
                </x-slot>
                
                <div class="space-y-4">
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">ID:</span>
                        <span class="text-gray-900">{{ $user->id }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Nome:</span>
                        <span class="text-gray-900">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Email:</span>
                        <span class="text-gray-900">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-200">
                        <span class="font-medium text-gray-700">Criado em:</span>
                        <span class="text-gray-900">{{ $user->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="font-medium text-gray-700">Atualizado em:</span>
                        <span class="text-gray-900">{{ $user->updated_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-slot name="title">
                    <div class="flex items-center">
                        <i class="fas fa-briefcase text-indigo-600 mr-2"></i>
                        Cargos Atribuídos
                    </div>
                </x-slot>
                
                @if($user->cargos->count() > 0)
                    <div class="space-y-3">
                        @foreach($user->cargos as $cargo)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h6 class="font-medium text-gray-900">{{ $cargo->nome }}</h6>
                                        @if($cargo->descricao)
                                            <p class="text-sm text-gray-600 mt-1">{{ $cargo->descricao }}</p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cargo->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $cargo->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-briefcase text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">Nenhum cargo atribuído.</p>
                    </div>
                @endif
            </x-card>
        </div>

        <div class="mt-8">
            <x-card>
                <x-slot name="title">
                    <div class="flex items-center">
                        <i class="fas fa-key text-indigo-600 mr-2"></i>
                        Permissões do Usuário
                    </div>
                </x-slot>
                
                @php
                    $todasPermissoes = collect();
                    foreach($user->cargos as $cargo) {
                        $todasPermissoes = $todasPermissoes->merge($cargo->permissoes);
                    }
                    $permissoesUnicas = $todasPermissoes->unique('id');
                @endphp

                @if($permissoesUnicas->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($permissoesUnicas->groupBy('modulo') as $modulo => $permissoes)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h6 class="font-medium text-gray-900 mb-3 flex items-center">
                                    <i class="fas fa-folder text-indigo-600 mr-2"></i>
                                    {{ ucfirst($modulo) }}
                                </h6>
                                <div class="space-y-2">
                                    @foreach($permissoes as $permissao)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $permissao->nome }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-key text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">Este usuário não possui permissões específicas.</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>

<!-- Layout Mobile com Cards -->
<div class="md:hidden">
    <!-- Botão Voltar Mobile -->
    <div class="bg-white border-b border-gray-200 px-4 py-3">
        <a href="{{ route('usuarios.index') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900">
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar para Lista
        </a>
    </div>

    <div class="p-4 space-y-4">
        <!-- Card: Informações Básicas -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Ativo
                </span>
            </div>
            
            <div class="space-y-3">
                <div class="flex items-center text-sm">
                    <i class="fas fa-envelope text-gray-400 mr-2 w-4"></i>
                    <span class="text-gray-600">{{ $user->email }}</span>
                </div>
                
                <div class="flex items-center text-sm">
                    <i class="fas fa-calendar text-gray-400 mr-2 w-4"></i>
                    <span class="text-gray-600">Criado em {{ $user->created_at->format('d/m/Y') }}</span>
                </div>
                
                <div class="flex items-center text-sm">
                    <i class="fas fa-id-badge text-gray-400 mr-2 w-4"></i>
                    <span class="text-gray-600">ID: {{ $user->id }}</span>
                </div>
            </div>
        </div>

        <!-- Card: Cargos -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Cargos Atribuídos</h3>
            
            @if($user->cargos->count() > 0)
                <div class="space-y-2">
                    @foreach($user->cargos as $cargo)
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $cargo->nome }}</p>
                                    @if($cargo->descricao)
                                        <p class="text-sm text-gray-600 mt-1">{{ $cargo->descricao }}</p>
                                    @endif
                                </div>
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $cargo->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $cargo->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6">
                    <i class="fas fa-briefcase text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-500 text-sm">Nenhum cargo atribuído</p>
                </div>
            @endif
        </div>

        <!-- Card: Permissões -->
        @if($user->cargos->count() > 0)
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Permissões</h3>
                
                @php
                    $todasPermissoes = collect();
                    foreach($user->cargos as $cargo) {
                        $todasPermissoes = $todasPermissoes->merge($cargo->permissoes);
                    }
                    $permissoesUnicas = $todasPermissoes->unique('id');
                @endphp

                @if($permissoesUnicas->count() > 0)
                    <div class="space-y-3">
                        @foreach($permissoesUnicas->groupBy('modulo') as $modulo => $permissoes)
                            <div class="bg-gray-50 rounded-lg p-3">
                                <h6 class="font-medium text-gray-900 mb-2 flex items-center">
                                    <i class="fas fa-folder text-indigo-600 mr-2"></i>
                                    {{ ucfirst($modulo) }}
                                </h6>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($permissoes as $permissao)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $permissao->nome }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <i class="fas fa-key text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500 text-sm">Nenhuma permissão encontrada</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- Card: Ações -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Ações</h3>
            
            <div class="space-y-3">
                @permission('usuarios.editar')
                    <a href="{{ route('usuarios.edit', $user) }}" class="w-full inline-flex items-center justify-center px-4 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <i class="fas fa-edit mr-2"></i>
                        Editar Usuário
                    </a>
                @endpermission
            </div>
        </div>
    </div>
</div>
</x-card>
@endsection