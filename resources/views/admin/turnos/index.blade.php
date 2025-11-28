@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Turnos', 'url' => '#']
]" />

    <x-card>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Turnos</h1>
                <p class="mt-1 text-sm text-gray-600">Gerenciamento de turnos escolares</p>
            </div>
            <x-button href="{{ route('admin.turnos.create') }}" color="primary">
                <i class="fas fa-plus mr-1"></i> Novo Turno
            </x-button>
        </div>

        <x-collapsible-filter 
            title="Filtros de Turnos" 
            :action="route('admin.turnos.index')" 
            :clear-route="route('admin.turnos.index')"
        >
            <x-filter-field 
                name="search" 
                label="Buscar" 
                type="text"
                placeholder="Buscar por nome, código ou descrição..."
            />
            
            <x-filter-field 
                name="ativo" 
                label="Status" 
                type="select"
                empty-option="Todos"
                :options="['1' => 'Ativo', '0' => 'Inativo']"
            />
        </x-collapsible-filter>

        @if($turnos->count() > 0)
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nome
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Código
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Horário
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Salas
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </x-slot>

                @foreach($turnos as $turno)
                    <x-table-row>
                        <x-table-cell>
                            <div class="text-sm font-medium text-gray-900">{{ $turno->nome }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $turno->codigo }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm text-gray-900">
                                @if($turno->hora_inicio && $turno->hora_fim)
                                    {{ $turno->hora_inicio }} - {{ $turno->hora_fim }}
                                @else
                                    <span class="text-gray-400">Não definido</span>
                                @endif
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            @if($turno->ativo)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Ativo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inativo
                                </span>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            @php
                                $salasCount = \App\Models\Sala::where('turno', $turno->codigo)->count();
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $salasCount }} salas
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.turnos.show', $turno) }}" class="btn btn-sm btn-outline-primary" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.turnos.tempo-slots.index', $turno) }}" class="btn btn-sm btn-outline-info" title="Tempo Slots">
                                    <i class="fas fa-clock"></i>
                                </a>
                                <a href="{{ route('admin.turnos.edit', $turno) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $turno->id }}" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @endforeach
            </x-table>

            <div class="mt-6">
                {{ $turnos->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-clock text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum turno encontrado</h3>
                <p class="text-gray-500 mb-4">Não há turnos cadastrados com os filtros aplicados.</p>
                <x-button href="{{ route('admin.turnos.create') }}" color="primary">
                    <i class="fas fa-plus mr-1"></i> Criar Primeiro Turno
                </x-button>
            </div>
        @endif
    </x-card>
@endsection