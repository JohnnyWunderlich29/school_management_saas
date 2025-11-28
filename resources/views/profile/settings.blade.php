@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Perfil', 'url' => route('profile.show')],
    ['title' => 'Configurações', 'url' => '#']
]" />

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Configurações</h1>
            <p class="text-gray-600">Gerencie suas preferências e configurações da conta</p>
        </div>
        <x-button
            color="secondary"
            href="{{ route('profile.show') }}"
        >
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar ao Perfil
        </x-button>
    </div>

    <form method="POST" action="{{ route('profile.settings.update') }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2" style="gap: 10px;">
            <x-card>
                <x-slot name="title">
                    <div class="flex items-center">
                        <i class="fas fa-bell text-indigo-600 mr-2"></i>
                        Notificações
                    </div>
                </x-slot>
                
                @php
                    $preferences = $user->preferences ?? [];
                @endphp
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h6 class="font-medium text-gray-900">Notificações por Email</h6>
                            <p class="text-sm text-gray-500">Receba atualizações importantes por email</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="email_notifications" value="1" class="sr-only peer" {{ ($preferences['email_notifications'] ?? true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h6 class="font-medium text-gray-900">Notificações do Sistema</h6>
                            <p class="text-sm text-gray-500">Receba alertas sobre atividades do sistema</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="system_notifications" value="1" class="sr-only peer" {{ ($preferences['system_notifications'] ?? true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div>
                            <h6 class="font-medium text-gray-900">Relatórios Semanais</h6>
                            <p class="text-sm text-gray-500">Receba resumos semanais de atividades</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="weekly_reports" value="1" class="sr-only peer" {{ ($preferences['weekly_reports'] ?? false) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            </x-card>

            <x-card>
                <x-slot name="title">
                    <div class="flex items-center">
                        <i class="fas fa-palette text-indigo-600 mr-2"></i>
                        Aparência
                    </div>
                </x-slot>
                
                <div class="space-y-4">
                    <div>
                        <h6 class="font-medium text-gray-900 mb-2">Tema</h6>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="theme" value="light" class="text-indigo-600 focus:ring-indigo-500" {{ ($preferences['theme'] ?? 'light') === 'light' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Claro</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="theme" value="dark" class="text-indigo-600 focus:ring-indigo-500" {{ ($preferences['theme'] ?? 'light') === 'dark' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Escuro</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="theme" value="auto" class="text-indigo-600 focus:ring-indigo-500" {{ ($preferences['theme'] ?? 'light') === 'auto' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Automático</span>
                            </label>
                        </div>
                    </div>
                    
                    <div>
                        <h6 class="font-medium text-gray-900 mb-2">Idioma</h6>
                        <select name="language" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="pt-BR" {{ ($preferences['language'] ?? 'pt-BR') === 'pt-BR' ? 'selected' : '' }}>Português (Brasil)</option>
                            <option value="en-US" {{ ($preferences['language'] ?? 'pt-BR') === 'en-US' ? 'selected' : '' }}>English (US)</option>
                            <option value="es-ES" {{ ($preferences['language'] ?? 'pt-BR') === 'es-ES' ? 'selected' : '' }}>Español</option>
                        </select>
                    </div>
                </div>
            </x-card>
        </div>

    <div class="mt-8">
        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-shield-alt text-indigo-600 mr-2"></i>
                    Segurança
                </div>
            </x-slot>
            
            <div class="space-y-6">
                <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-key text-yellow-600 mr-3"></i>
                        <div>
                            <h6 class="font-medium text-gray-900">Autenticação de Dois Fatores</h6>
                            <p class="text-sm text-gray-500">Adicione uma camada extra de segurança à sua conta</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="two_factor_enabled" value="1" class="sr-only peer" {{ ($preferences['two_factor_enabled'] ?? false) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-600"></div>
                    </label>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-lock text-yellow-600 mr-3"></i>
                        <div>
                            <h6 class="font-medium text-gray-900">Alterar Senha</h6>
                            <p class="text-sm text-gray-500">Atualize sua senha de acesso</p>
                        </div>
                    </div>
                    <x-button color="warning" size="sm" href="{{ route('profile.edit') }}">
                        <i class="fas fa-key mr-1"></i>
                        Alterar
                    </x-button>
                </div>
            </div>
        </x-card>
    </div>

        <div class="mt-8 flex justify-end">
            <x-button type="submit" color="primary">
                <i class="fas fa-save mr-2"></i>
                Salvar Configurações
            </x-button>
        </div>
    </form>

    @auth
    @php
        $u = auth()->user();
        $isAdmin = $u->isSuperAdmin() || $u->temCargo('Suporte');
        $hasPermission = method_exists($u, 'can') ? $u->can('onboarding.habilitar') : false;
        $canSee = $isAdmin || $hasPermission;
    @endphp
    @if($canSee)
    <div class="mt-8">
        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-life-ring text-indigo-600 mr-2"></i>
                    Onboarding
                </div>
            </x-slot>

            @php
                $prefs = $u->preferences ?? [];
                $closed = (bool) ($prefs['onboarding_closed'] ?? false);
                $owner = method_exists($u, 'isSchoolOwner') ? $u->isSchoolOwner() : false;
            @endphp

            <div class="space-y-4">
                <p class="text-sm text-gray-600">Controle a visibilidade da barra de primeiros passos para sua escola.</p>

                @if($isAdmin)
                    <form method="POST" action="{{ route('onboarding.reopen') }}">
                        @csrf
                        <x-button type="submit" color="primary">
                            <i class="fas fa-toggle-on mr-2"></i>
                            Ligar barra de onboarding (escola atual)
                        </x-button>
                    </form>
                    <p class="text-xs text-gray-500">Esta ação reabre a barra para o dono da escola selecionada.</p>
                @else
                    @if($owner)
                        @if($closed)
                            <form method="POST" action="{{ route('onboarding.reopen') }}">
                                @csrf
                                <x-button type="submit" color="primary">
                                    <i class="fas fa-toggle-on mr-2"></i>
                                    Ligar barra de onboarding
                                </x-button>
                            </form>
                        @else
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                    <div>
                                        <h6 class="font-medium text-gray-900">Barra de onboarding ativa</h6>
                                        <p class="text-sm text-gray-500">A barra está visível enquanto houver passos pendentes.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </x-card>
    </div>
    @endif
    @endauth

    @if(session('success'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection