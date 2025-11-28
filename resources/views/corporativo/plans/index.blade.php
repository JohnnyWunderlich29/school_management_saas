@extends('corporativo.layout')

@section('title', 'Gerenciamento de Planos')
@section('page-title', 'Gerenciamento de Planos')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 text-green-700 border border-green-200 rounded">{{ session('success') }}</div>
        @endif

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Gerenciamento de Planos</h1>
                    <p class="mt-2 text-gray-600">Configure os planos e seus módulos</p>
                </div>
                <a href="{{ route('corporativo.plans.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Novo Plano
                </a>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total de Planos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $plans->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Planos Ativos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $plans->where('is_active', true)->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-gray-100 rounded-lg">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Planos Inativos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $plans->where('is_active', false)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Planos -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Lista de Planos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plano</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Máx. Usuários</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Máx. Alunos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trial</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordem</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulos</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($plans as $plan)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900">
                                    <div class="font-medium">{{ $plan->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $plan->slug }}</div>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700">R$ {{ number_format($plan->price ?? 0, 2, ',', '.') }}</td>
                                <td class="px-6 py-3 text-sm text-gray-700">{{ $plan->max_users ?? '-' }}</td>
                                <td class="px-6 py-3 text-sm text-gray-700">{{ $plan->max_students ?? '-' }}</td>
                                <td class="px-6 py-3 text-sm">
                                    @if($plan->is_trial)
                                        <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">{{ $plan->trial_days }} dias</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Não</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700">{{ $plan->sort_order ?? '-' }}</td>
                                <td class="px-6 py-3 text-sm">
                                    @if($plan->is_active)
                                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Ativo</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-600">Inativo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700">{{ $plan->modules_count }}</td>
                                <td class="px-6 py-3 text-sm text-right">
                                    <a href="{{ route('corporativo.plans.edit', $plan) }}" class="inline-flex items-center px-3 py-1.5 text-purple-700 bg-purple-50 hover:bg-purple-100 rounded mr-2">Editar</a>
                                    <form action="{{ route('corporativo.plans.toggle', $plan) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 {{ $plan->is_active ? 'text-gray-700 bg-gray-100 hover:bg-gray-200' : 'text-green-700 bg-green-50 hover:bg-green-100' }} rounded">
                                            {{ $plan->is_active ? 'Inativar' : 'Ativar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-6 text-center text-sm text-gray-500">Nenhum plano cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>
@endsection