@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Perfil', 'url' => route('profile.show')],
    ['title' => 'Dados da Escola', 'url' => '#']
]" />

<x-card>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dados da Escola</h1>
        <p class="text-gray-600">Informações institucionais da sua escola</p>
    </div>

    <!-- Estatísticas Rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Usuários</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $escola->users_count }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-user-graduate text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Alunos Ativos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['alunos_ativos'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-chalkboard-teacher text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Funcionários</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['funcionarios_ativos'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-door-open text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Salas Ativas</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['salas_ativas'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Informações Básicas -->
        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-school text-indigo-600 mr-2"></i>
                    Informações Básicas
                </div>
            </x-slot>
            
            <div class="space-y-4">
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Nome:</span>
                    <span class="text-gray-900">{{ $escola->nome }}</span>
                </div>
                @if($escola->razao_social)
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Razão Social:</span>
                    <span class="text-gray-900">{{ $escola->razao_social }}</span>
                </div>
                @endif
                @if($escola->cnpj)
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">CNPJ:</span>
                    <span class="text-gray-900">{{ $escola->cnpj }}</span>
                </div>
                @endif
                @if($escola->email)
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Email:</span>
                    <span class="text-gray-900">{{ $escola->email }}</span>
                </div>
                @endif
                @if($escola->telefone)
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Telefone:</span>
                    <span class="text-gray-900">{{ $escola->telefone }}</span>
                </div>
                @endif
                @if($escola->celular)
                <div class="flex justify-between py-2">
                    <span class="font-medium text-gray-700">Celular:</span>
                    <span class="text-gray-900">{{ $escola->celular }}</span>
                </div>
                @endif
            </div>
        </x-card>

        <!-- Endereço -->
        @if($escola->endereco || $escola->cidade)
        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-map-marker-alt text-indigo-600 mr-2"></i>
                    Endereço
                </div>
            </x-slot>
            
            <div class="space-y-4">
                @if($escola->endereco)
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Logradouro:</span>
                    <span class="text-gray-900">{{ $escola->endereco }}{{ $escola->numero ? ', ' . $escola->numero : '' }}</span>
                </div>
                @endif
                @if($escola->complemento)
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Complemento:</span>
                    <span class="text-gray-900">{{ $escola->complemento }}</span>
                </div>
                @endif
                @if($escola->bairro)
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Bairro:</span>
                    <span class="text-gray-900">{{ $escola->bairro }}</span>
                </div>
                @endif
                @if($escola->cidade)
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Cidade:</span>
                    <span class="text-gray-900">{{ $escola->cidade }}{{ $escola->estado ? ' - ' . $escola->estado : '' }}</span>
                </div>
                @endif
                @if($escola->cep)
                <div class="flex justify-between py-2">
                    <span class="font-medium text-gray-700">CEP:</span>
                    <span class="text-gray-900">{{ $escola->cep }}</span>
                </div>
                @endif
            </div>
        </x-card>
        @endif

        <!-- Plano e Status -->
        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-cog text-indigo-600 mr-2"></i>
                    Plano e Status
                </div>
            </x-slot>
            
            <div class="space-y-4">
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Plano:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($escola->plano === 'enterprise') bg-purple-100 text-purple-800
                        @elseif($escola->plano === 'premium') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($escola->plano) }}
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($escola->ativo) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                        @if($escola->ativo) Ativa @else Inativa @endif
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Situação Financeira:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($escola->em_dia) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                        @if($escola->em_dia) Em dia @else Inadimplente @endif
                    </span>
                </div>
                @if($escola->max_usuarios)
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Limite de Usuários:</span>
                    <span class="text-gray-900">{{ $escola->users_count }}/{{ $escola->max_usuarios }}</span>
                </div>
                @endif
                @if($escola->max_alunos)
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Limite de Alunos:</span>
                    <span class="text-gray-900">{{ $stats['total_alunos'] }}/{{ $escola->max_alunos }}</span>
                </div>
                @endif
                @if($escola->valor_mensalidade)
                <div class="flex justify-between py-2">
                    <span class="font-medium text-gray-700">Valor Mensalidade:</span>
                    <span class="text-gray-900">R$ {{ number_format($escola->valor_mensalidade, 2, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </x-card>

        <!-- Informações do Sistema -->
        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                    Informações do Sistema
                </div>
            </x-slot>
            
            <div class="space-y-4">
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Criada em:</span>
                    <span class="text-gray-900">{{ $escola->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-200">
                    <span class="font-medium text-gray-700">Última atualização:</span>
                    <span class="text-gray-900">{{ $escola->updated_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($escola->data_vencimento)
                <div class="flex justify-between py-2">
                    <span class="font-medium text-gray-700">Vencimento:</span>
                    <span class="text-gray-900 @if($escola->data_vencimento->isPast()) text-red-600 @endif">
                        {{ $escola->data_vencimento->format('d/m/Y') }}
                        @if($escola->data_vencimento->isPast())
                            <i class="fas fa-exclamation-triangle ml-1"></i>
                        @endif
                    </span>
                </div>
                @endif
            </div>
        </x-card>
    </div>

    @if($escola->descricao)
    <!-- Descrição -->
    <div class="mt-8">
        <x-card>
            <x-slot name="title">
                <div class="flex items-center">
                    <i class="fas fa-file-alt text-indigo-600 mr-2"></i>
                    Descrição
                </div>
            </x-slot>
            
            <div class="prose max-w-none">
                <p class="text-gray-700">{{ $escola->descricao }}</p>
            </div>
        </x-card>
    </div>
    @endif
</x-card>
@endsection