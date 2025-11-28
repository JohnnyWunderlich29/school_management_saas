@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Perfil', 'url' => route('profile.show')],
    ['title' => 'Editar', 'url' => '#']
]" />

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Perfil</h1>
            <p class="text-gray-600">Atualize suas informações pessoais</p>
        </div>
        <x-button
            color="secondary"
            href="{{ route('profile.show') }}"
        >
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar
        </x-button>
    </div>

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <x-card>
                <x-slot name="title">
                    <div class="flex items-center">
                        <i class="fas fa-user text-indigo-600 mr-2"></i>
                        Informações Básicas
                    </div>
                </x-slot>
                
                <div class="space-y-6">
                    <div>
                        <x-input
                            label="Nome"
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
                            required
                        />
                    </div>
                    
                    <div>
                        <x-input
                            label="Email"
                            name="email"
                            type="email"
                            value="{{ old('email', $user->email) }}"
                            required
                        />
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-slot name="title">
                    <div class="flex items-center">
                        <i class="fas fa-lock text-indigo-600 mr-2"></i>
                        Alterar Senha
                    </div>
                </x-slot>
                
                <div class="space-y-6">
                    <div>
                        <x-input
                            label="Senha Atual"
                            name="current_password"
                            type="password"
                            placeholder="Digite sua senha atual"
                        />
                        <p class="text-sm text-gray-500 mt-1">Obrigatório apenas se você quiser alterar a senha</p>
                    </div>
                    
                    <div>
                        <x-input
                            label="Nova Senha"
                            name="password"
                            type="password"
                            placeholder="Digite a nova senha"
                        />
                    </div>
                    
                    <div>
                        <x-input
                            label="Confirmar Nova Senha"
                            name="password_confirmation"
                            type="password"
                            placeholder="Confirme a nova senha"
                        />
                    </div>
                </div>
            </x-card>
        </div>

        <div class="mt-8 flex justify-end space-x-3">
            <x-button
                color="secondary"
                href="{{ route('profile.show') }}"
            >
                <i class="fas fa-times mr-2"></i>
                Cancelar
            </x-button>
            
            <x-button
                type="submit"
                color="primary"
            >
                <i class="fas fa-save mr-2"></i>
                Salvar Alterações
            </x-button>
        </div>
    </form>
</div>
@endsection