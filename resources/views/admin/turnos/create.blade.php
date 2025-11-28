@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Turnos', 'url' => route('admin.turnos.index')],
    ['title' => 'Novo Turno', 'url' => '#']
]" />

    <x-card>
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Novo Turno</h1>
            <p class="mt-1 text-sm text-gray-600">Cadastre um novo turno escolar</p>
        </div>

        <form action="{{ route('admin.turnos.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="nome" 
                        label="Nome do Turno" 
                        placeholder="Ex: Matutino"
                        required
                    />
                </div>

                <div>
                    <x-input 
                        name="codigo" 
                        label="Código" 
                        placeholder="Ex: MAT"
                        required
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="hora_inicio" 
                        label="Hora de Início" 
                        type="time"
                        placeholder="07:00"
                    />
                </div>

                <div>
                    <x-input 
                        name="hora_fim" 
                        label="Hora de Fim" 
                        type="time"
                        placeholder="12:00"
                    />
                </div>
            </div>

            <div>
                <x-textarea 
                    name="descricao" 
                    label="Descrição" 
                    placeholder="Descrição detalhada do turno"
                    rows="3"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="ordem" 
                        label="Ordem de Exibição" 
                        type="number"
                        placeholder="1"
                        min="1"
                    />
                </div>

                <div class="flex items-center space-x-4 pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="ativo" value="1" checked 
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Turno ativo</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t">
                <x-button href="{{ route('admin.configuracoes.index', ['tab' => 'turnos']) }}" color="secondary">
                    Cancelar
                </x-button>
                <x-button type="submit" color="primary">
                    <i class="fas fa-save mr-1"></i> Salvar Turno
                </x-button>
            </div>
        </form>
    </x-card>
@endsection