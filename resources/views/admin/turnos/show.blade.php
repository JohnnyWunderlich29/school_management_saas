@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Turnos', 'url' => route('admin.turnos.index')],
    ['title' => $turno->nome, 'url' => '#']
]" />

    <x-card>
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $turno->nome }}</h1>
                <p class="mt-1 text-sm text-gray-600">Detalhes do turno</p>
            </div>
            <div class="flex space-x-2">
                <x-button href="{{ route('admin.turnos.tempo-slots.index', $turno) }}" color="info">
                    <i class="fas fa-clock mr-1"></i> Gerenciar Tempo Slots
                </x-button>
                <x-button href="{{ route('admin.turnos.edit', $turno) }}" color="secondary">
                    <i class="fas fa-edit mr-1"></i> Editar
                </x-button>
                <x-button href="{{ route('admin.turnos.index') }}" color="primary">
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
                        <dd class="text-sm text-gray-900">{{ $turno->nome }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Código</dt>
                        <dd class="text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $turno->codigo }}
                            </span>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="text-sm text-gray-900">
                            @if($turno->ativo)
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
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Ordem de Exibição</dt>
                        <dd class="text-sm text-gray-900">{{ $turno->ordem ?: 'Não definida' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Horários -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Horários</h3>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Hora de Início</dt>
                        <dd class="text-sm text-gray-900">
                            @if($turno->hora_inicio)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-clock mr-1"></i> {{ $turno->hora_inicio }}
                                </span>
                            @else
                                <span class="text-gray-400">Não definido</span>
                            @endif
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Hora de Fim</dt>
                        <dd class="text-sm text-gray-900">
                            @if($turno->hora_fim)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-clock mr-1"></i> {{ $turno->hora_fim }}
                                </span>
                            @else
                                <span class="text-gray-400">Não definido</span>
                            @endif
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Duração</dt>
                        <dd class="text-sm text-gray-900">
                            @if($turno->hora_inicio && $turno->hora_fim)
                                @php
                                    $duracao = $turno->hora_fim->diff($turno->hora_inicio);
                                @endphp
                                {{ $duracao->format('%h horas e %i minutos') }}
                            @else
                                <span class="text-gray-400">Não calculável</span>
                            @endif
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Data de Criação</dt>
                        <dd class="text-sm text-gray-900">{{ $turno->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($turno->descricao)
            <div class="mt-6 bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Descrição</h3>
                <p class="text-sm text-gray-700 leading-relaxed">{{ $turno->descricao }}</p>
            </div>
        @endif

        <!-- Estatísticas -->
        <div class="mt-6 bg-blue-50 rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Estatísticas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $turno->salas()->count() }}</div>
                    <div class="text-sm text-gray-600">Salas Associadas</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">
                        {{ $turno->salas()->whereHas('alunos')->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Salas com Alunos</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">
                        {{ $turno->salas()->withCount('alunos')->get()->sum('alunos_count') }}
                    </div>
                    <div class="text-sm text-gray-600">Total de Alunos</div>
                </div>
            </div>
        </div>

        <!-- Tempo Slots -->
        @if($turno->tempoSlots()->count() > 0)
            <div class="mt-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Tempo Slots</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $turno->tempoSlots()->count() }} slots configurados
                    </span>
                </div>
                
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                        @foreach($turno->tempoSlots()->ordenados()->get() as $slot)
                            <div class="border rounded-lg p-4 hover:shadow-md transition-shadow
                                {{ $slot->tipo === 'aula' ? 'border-blue-200 bg-blue-50' : '' }}
                                {{ $slot->tipo === 'recreio' ? 'border-green-200 bg-green-50' : '' }}
                                {{ $slot->tipo === 'intervalo' ? 'border-yellow-200 bg-yellow-50' : '' }}
                                {{ $slot->tipo === 'almoco' ? 'border-orange-200 bg-orange-50' : '' }}
                            ">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $slot->nome }}</h4>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        {{ $slot->tipo === 'aula' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $slot->tipo === 'recreio' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $slot->tipo === 'intervalo' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $slot->tipo === 'almoco' ? 'bg-orange-100 text-orange-800' : '' }}
                                    ">
                                        <i class="fas {{ $slot->tipo === 'aula' ? 'fa-book' : ($slot->tipo === 'recreio' ? 'fa-playground' : ($slot->tipo === 'almoco' ? 'fa-utensils' : 'fa-coffee')) }} mr-1"></i>
                                        {{ ucfirst($slot->tipo) }}
                                    </span>
                                </div>
                                
                                <div class="space-y-1 text-xs text-gray-600">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $slot->hora_inicio ? $slot->hora_inicio->format('H:i') : 'N/A' }} - 
                                        {{ $slot->hora_fim ? $slot->hora_fim->format('H:i') : 'N/A' }}
                                    </div>
                                    
                                    @if($slot->duracao_minutos)
                                        <div class="flex items-center">
                                            <i class="fas fa-stopwatch mr-1"></i>
                                            {{ $slot->duracao_minutos }} minutos
                                        </div>
                                    @endif
                                    
                                    <div class="flex items-center">
                                        <i class="fas fa-sort-numeric-up mr-1"></i>
                                        Ordem: {{ $slot->ordem }}
                                    </div>
                                    
                                    @if(!$slot->ativo)
                                        <div class="flex items-center text-red-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Inativo
                                        </div>
                                    @endif
                                </div>
                                
                                @if($slot->descricao)
                                    <div class="mt-2 text-xs text-gray-500">
                                        {{ $slot->descricao }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-medium text-yellow-800">Nenhum tempo slot configurado</h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            Este turno não possui tempo slots configurados. Configure os horários de aula e intervalos para melhor organização.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if($turno->salas()->count() > 0)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Salas Associadas</h3>
                
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        @foreach($turno->salas()->with('grupo')->get() as $sala)
                            <li class="px-6 py-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-door-open text-gray-400"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $sala->nome }}</div>
                                            <div class="text-sm text-gray-500">
                                                Grupo: {{ $sala->grupo->nome ?? 'Não definido' }}
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