@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Modalidades de Ensino', 'url' => '#']
]" />

    <x-card>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Modalidades de Ensino</h1>
                <p class="mt-1 text-sm text-gray-600">Gerenciamento de modalidades de ensino</p>
            </div>
            <x-button href="{{ route('admin.modalidades.create') }}" color="primary">
                <i class="fas fa-plus mr-1"></i> Nova Modalidade
            </x-button>
        </div>

        <x-collapsible-filter 
            title="Filtros de Modalidades" 
            :action="route('admin.modalidades.index')" 
            :clear-route="route('admin.modalidades.index')"
        >
            <x-filter-field 
                name="nome" 
                label="Nome" 
                type="text"
                placeholder="Buscar por nome..."
            />
            
            <x-filter-field 
                name="codigo" 
                label="Código" 
                type="text"
                placeholder="Buscar por código..."
            />
            
            <x-filter-field 
                name="ativo" 
                label="Status" 
                type="select"
                empty-option="Todos"
                :options="['true' => 'Ativo', 'false' => 'Inativo']"
            />
        </x-collapsible-filter>

        @if($modalidades->count() > 0)
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Código
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nome
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Descrição
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Salas Vinculadas
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </x-slot>

                @foreach($modalidades as $modalidade)
                    <x-table-row>
                        <x-table-cell>
                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                                {{ $modalidade->codigo }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="font-medium text-gray-900">
                                {{ $modalidade->nome }}
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm text-gray-600 max-w-xs truncate">
                                {{ $modalidade->descricao ?? '-' }}
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $modalidade->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $modalidade->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm text-gray-600">
                                {{ \App\Models\Sala::whereHas('turmas.grupo', function($query) use ($modalidade) { $query->where('modalidade_ensino_id', $modalidade->id); })->count() }} salas
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="flex items-center space-x-2">
                                <x-button 
                                    href="{{ route('admin.modalidades.show', $modalidade) }}" 
                                    color="secondary" 
                                    size="sm"
                                    title="Visualizar"
                                >
                                    <i class="fas fa-eye"></i>
                                </x-button>
                                
                                <x-button 
                                    href="{{ route('admin.modalidades.edit', $modalidade) }}" 
                                    color="warning" 
                                    size="sm"
                                    title="Editar"
                                >
                                    <i class="fas fa-edit"></i>
                                </x-button>

                                <form method="POST" action="{{ route('admin.modalidades.toggle-status', $modalidade) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <x-button 
                                        type="submit" 
                                        color="{{ $modalidade->ativo ? 'warning' : 'success' }}" 
                                        size="sm"
                                        title="{{ $modalidade->ativo ? 'Desativar' : 'Ativar' }}"
                                        onclick="return confirm('Tem certeza que deseja {{ $modalidade->ativo ? 'desativar' : 'ativar' }} esta modalidade?')"
                                    >
                                        <i class="fas fa-{{ $modalidade->ativo ? 'pause' : 'play' }}"></i>
                                    </x-button>
                                </form>

                                @if(\App\Models\Sala::whereHas('turmas.grupo', function($query) use ($modalidade) { $query->where('modalidade_ensino_id', $modalidade->id); })->count() == 0)
                                    <form method="POST" action="{{ route('admin.modalidades.destroy', $modalidade) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-button 
                                            type="submit" 
                                            color="danger" 
                                            size="sm"
                                            title="Excluir"
                                            onclick="return confirm('Tem certeza que deseja excluir esta modalidade? Esta ação não pode ser desfeita.')"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </x-button>
                                    </form>
                                @endif
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @endforeach
            </x-table>

            <div class="mt-6">
                {{ $modalidades->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-graduation-cap text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma modalidade encontrada</h3>
                <p class="text-gray-600 mb-4">
                    @if(request()->hasAny(['nome', 'codigo', 'ativo']))
                        Nenhuma modalidade corresponde aos filtros aplicados.
                    @else
                        Comece criando sua primeira modalidade de ensino.
                    @endif
                </p>
                <x-button href="{{ route('admin.modalidades.create') }}" color="primary">
                    <i class="fas fa-plus mr-1"></i> Nova Modalidade
                </x-button>
            </div>
        @endif
    </x-card>
@endsection