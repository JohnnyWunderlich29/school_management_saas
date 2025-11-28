@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Grupos Educacionais', 'url' => route('admin.grupos.index')],
    ['title' => $grupo->nome, 'url' => '#']
]" />

    <x-card>
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $grupo->nome }}</h1>
                <p class="mt-1 text-sm text-gray-600">Detalhes do grupo educacional</p>
            </div>
            <div class="flex space-x-2">
                <x-button href="{{ route('admin.grupos.edit', $grupo) }}" color="secondary">
                    <i class="fas fa-edit mr-1"></i> Editar
                </x-button>
                <x-button href="{{ route('admin.grupos.index') }}" color="primary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Informações Básicas -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informações Básicas</h3>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nome</dt>
                        <dd class="text-sm text-gray-900">{{ $grupo->nome }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Código</dt>
                        <dd class="text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $grupo->codigo }}
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Modalidade de Ensino</dt>
                        <dd class="text-sm text-gray-900">{{ $grupo->modalidadeEnsino->nome }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="text-sm text-gray-900">
                            @if($grupo->ativo)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Ativo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> Inativo
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Detalhes Educacionais -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detalhes Educacionais</h3>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Faixa Etária</dt>
                        <dd class="text-sm text-gray-900">{{ $grupo->faixa_etaria ?: $grupo->ano_serie_formatado ?: 'Não informado' }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Ano/Série</dt>
                        <dd class="text-sm text-gray-900">{{ $grupo->ano_serie ?: 'Não informado' }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Ordem de Exibição</dt>
                        <dd class="text-sm text-gray-900">{{ $grupo->ordem ?: 'Não definida' }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Data de Criação</dt>
                        <dd class="text-sm text-gray-900">{{ $grupo->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($grupo->descricao)
            <div class="mt-6 bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Descrição</h3>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $grupo->descricao }}</p>
            </div>
        @endif

        <!-- Estatísticas -->
        <div class="mt-6 bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Estatísticas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $grupo->salas()->count() }}</div>
                    <div class="text-sm text-gray-600">Salas Associadas</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">
                        {{ $grupo->salas()->whereHas('alunos')->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Salas com Alunos</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">
                        {{ $grupo->salas()->withCount('alunos')->get()->sum('alunos_count') }}
                    </div>
                    <div class="text-sm text-gray-600">Total de Alunos</div>
                </div>
            </div>
        </div>

        @if($grupo->salas()->count() > 0)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Salas Associadas</h3>
                
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        @foreach($grupo->salas()->with('turno')->get() as $sala)
                            <li class="px-6 py-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-door-open text-gray-400"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $sala->nome }}</div>
                                            <div class="text-sm text-gray-500">
                                                Turno: {{ $sala->turno->nome ?? 'Não definido' }}
                                                @if($sala->turma)
                                                    | Turma: {{ $sala->turma->nome }} - {{ $sala->turma->codigo }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $sala->alunos()->count() }} alunos
                                        </span>
                                        <a href="{{ route('salas.show', $sala) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 text-sm">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </x-card>
@endsection