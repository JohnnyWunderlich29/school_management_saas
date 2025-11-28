@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Disciplinas', 'url' => route('admin.disciplinas.index')],
    ['title' => $disciplina->nome, 'url' => '#']
]" />

    <div class="space-y-6">
        <!-- Cabeçalho -->
        <x-card>
            <div class="flex justify-between items-start">
                <div class="flex items-center space-x-4">
                    @if($disciplina->cor_hex)
                        <div class="w-12 h-12 rounded-lg shadow-sm border" 
                             style="background-color: {{ $disciplina->cor_hex }}"></div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $disciplina->nome }}</h1>
                        <p class="text-sm text-gray-600">Código: {{ $disciplina->codigo }}</p>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $disciplina->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $disciplina->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                            @if($disciplina->obrigatoria)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Obrigatória
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <x-button href="{{ route('admin.disciplinas.edit', $disciplina) }}" color="primary" size="sm">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </x-button>
                    <x-button href="{{ route('admin.disciplinas.index') }}" color="secondary" size="sm">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </x-button>
                </div>
            </div>
        </x-card>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informações Básicas -->
            <div class="lg:col-span-2">
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informações Básicas</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nome</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $disciplina->nome }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Código</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $disciplina->codigo }}</p>
                        </div>



                        <div>
                            <label class="block text-sm font-medium text-gray-700">Área de Conhecimento</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $disciplina->area_conhecimento }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ordem de Exibição</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $disciplina->ordem ?? 'Não definida' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data de Criação</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $disciplina->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($disciplina->descricao)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">Descrição</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $disciplina->descricao }}</p>
                        </div>
                    @endif
                </x-card>
            </div>

            <!-- Estatísticas -->
            <div>
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Estatísticas</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Salas Associadas</span>
                            @php
                                $salasCount = \App\Models\Sala::where('disciplina', $disciplina->codigo)->count();
                            @endphp
                            <span class="text-lg font-semibold text-gray-900">{{ $salasCount }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Salas com Alunos</span>
                            <span class="text-lg font-semibold text-gray-900">{{ $disciplina->salas_com_alunos_count ?? 0 }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total de Alunos</span>
                            <span class="text-lg font-semibold text-gray-900">{{ $disciplina->total_alunos ?? 0 }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Professores</span>
                            <span class="text-lg font-semibold text-gray-900">{{ $disciplina->professores_count ?? 0 }}</span>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Salas Associadas -->
        @if($disciplina->salas && $disciplina->salas->count() > 0)
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Salas Associadas</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sala
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Grupo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Turno
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Turma
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Alunos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($disciplina->salas as $sala)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $sala->nome }}</div>
                                        <div class="text-sm text-gray-500">{{ $sala->codigo }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $sala->grupo->nome ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $sala->turno->nome ?? 'N/A' }}</div>
                                        @if($sala->turno)
                                            <div class="text-sm text-gray-500">
                                                {{ $sala->turno->hora_inicio }} - {{ $sala->turno->hora_fim }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $sala->turma ? $sala->turma->nome . ' - ' . $sala->turma->codigo : 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $sala->alunos_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $sala->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $sala->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @else
            <x-card>
                <div class="text-center py-8">
                    <i class="fas fa-chalkboard-teacher text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma sala associada</h3>
                    <p class="text-gray-600">Esta disciplina ainda não foi associada a nenhuma sala.</p>
                </div>
            </x-card>
        @endif
    </div>
@endsection