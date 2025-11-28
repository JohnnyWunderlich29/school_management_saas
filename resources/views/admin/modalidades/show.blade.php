@extends('layouts.app')

@section('content')
    <x-breadcrumbs :items="[
        ['title' => 'Administração', 'url' => '#'],
        ['title' => 'Modalidades de Ensino', 'url' => route('admin.modalidades.index')],
        ['title' => $modalidade->nome, 'url' => '#'],
    ]" />

    <x-card>
        <div class="container mx-auto px-4 py-6">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <a href="{{ route('admin.modalidades.index') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ $modalidade->nome }}</h1>
                            <p class="text-gray-600 mt-1">
                                <span
                                    class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ $modalidade->codigo }}</span>
                                <span
                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $modalidade->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $modalidade->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <x-button href="{{ route('admin.modalidades.edit', $modalidade) }}" color="warning">
                            <i class="fas fa-edit mr-1"></i> Editar
                        </x-button>

                        <form method="POST" action="{{ route('admin.modalidades.toggle-status', $modalidade) }}"
                            class="inline">
                            @csrf
                            @method('PATCH')
                            <x-button type="submit" color="{{ $modalidade->ativo ? 'warning' : 'success' }}"
                                onclick="return confirm('Tem certeza que deseja {{ $modalidade->ativo ? 'desativar' : 'ativar' }} esta modalidade?')">
                                <i class="fas fa-{{ $modalidade->ativo ? 'pause' : 'play' }} mr-1"></i>
                                {{ $modalidade->ativo ? 'Desativar' : 'Ativar' }}
                            </x-button>
                        </form>

                        <form method="POST" action="{{ route('admin.modalidades.destroy', $modalidade) }}"
                            class="inline">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" color="danger"
                                onclick="return confirm('Tem certeza que deseja excluir esta modalidade? Esta ação não pode ser desfeita.')">
                                <i class="fas fa-trash mr-1"></i> Excluir
                            </x-button>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Informações da Modalidade -->
                    <div class="lg:col-span-2">
                        <x-card>
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Informações da Modalidade</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Código</label>
                                    <p class="text-gray-900 font-mono bg-gray-50 px-3 py-2 rounded border">
                                        {{ $modalidade->codigo }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                    <p class="text-gray-900 bg-gray-50 px-3 py-2 rounded border">{{ $modalidade->nome }}
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                    <p class="text-gray-900 bg-gray-50 px-3 py-2 rounded border min-h-[80px]">
                                        {{ $modalidade->descricao ?? 'Nenhuma descrição fornecida.' }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <span
                                        class="inline-flex items-center px-3 py-2 rounded-full text-sm font-medium {{ $modalidade->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i
                                            class="fas fa-{{ $modalidade->ativo ? 'check-circle' : 'times-circle' }} mr-2"></i>
                                        {{ $modalidade->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Criado em</label>
                                    <p class="text-gray-900 bg-gray-50 px-3 py-2 rounded border">
                                        {{ $modalidade->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </x-card>
                    </div>

                    <!-- Estatísticas -->
                    <div>
                        <x-card>
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Estatísticas</h2>

                            <div class="space-y-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-door-open text-blue-600 text-2xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-blue-900">Salas Vinculadas</p>
                                            <p class="text-2xl font-bold text-blue-600">{{ \App\Models\Sala::whereHas('turmas.grupo', function($query) use ($modalidade) { $query->where('modalidade_ensino_id', $modalidade->id); })->count() }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if ($modalidade->salas->count() > 0)
                                    <div class="bg-green-50 p-4 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-green-900">Salas Ativas</p>
                                                <p class="text-2xl font-bold text-green-600">
                                                    {{ $modalidade->salas->where('ativo', true)->count() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </x-card>
                    </div>
                </div>

                <!-- Salas Vinculadas -->
                @if ($modalidade->salas->count() > 0)
                    <div class="mt-6">
                        <x-card>
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Salas Vinculadas</h2>

                            <x-table>
                                <x-slot name="header">
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Código
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nome
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Capacidade
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </x-slot>

                                @foreach (\App\Models\Sala::where('modalidade_ensino_id', $modalidade->id)->get() as $sala)
                                    <x-table-row>
                                        <x-table-cell>
                                            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                                                {{ $sala->codigo }}
                                            </span>
                                        </x-table-cell>
                                        <x-table-cell>
                                            <div class="font-medium text-gray-900">
                                                {{ $sala->nome }}
                                            </div>
                                        </x-table-cell>
                                        <x-table-cell>
                                            <span class="text-sm text-gray-600">
                                                {{ $sala->capacidade ?? '-' }}
                                            </span>
                                        </x-table-cell>
                                        <x-table-cell>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sala->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $sala->ativo ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </x-table-cell>
                                        <x-table-cell>
                                            <x-button href="{{ route('salas.show', $sala) }}" color="secondary"
                                                size="sm" title="Visualizar Sala">
                                                <i class="fas fa-eye"></i>
                                            </x-button>
                                        </x-table-cell>
                                    </x-table-row>
                                @endforeach
                            </x-table>
                        </x-card>
                    </div>
                @else
                    <div class="mt-6">
                        <x-card>
                            <div class="text-center py-8">
                                <i class="fas fa-door-open text-4xl text-gray-400 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma sala vinculada</h3>
                                <p class="text-gray-600">Esta modalidade ainda não possui salas vinculadas.</p>
                            </div>
                        </x-card>
                    </div>
                @endif
            </div>
        </div>
    </x-card>
@endsection
