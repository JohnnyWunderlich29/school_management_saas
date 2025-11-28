@extends('corporativo.layout')

@section('title', 'Gerenciamento de Planos')
@section('page-title', 'Gerenciamento de Planos')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Editar Plano</h1>
                    <p class="mt-2 text-gray-600">Atualize os dados do plano e os módulos inclusos</p>
                </div>
                <a href="{{ route('corporativo.plans.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-medium transition-colors">Voltar</a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('corporativo.plans.update', $plan) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" name="name" value="{{ old('name', $plan->name) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Slug (opcional)</label>
                        <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Preço</label>
                        <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ordem</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Máx. Usuários</label>
                        <input type="number" name="max_users" value="{{ old('max_users', $plan->max_users) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Máx. Alunos</label>
                        <input type="number" name="max_students" value="{{ old('max_students', $plan->max_students) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700">Ativo</label>
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700">Plano de Teste</label>
                        <input type="checkbox" name="is_trial" value="1" {{ old('is_trial', $plan->is_trial) ? 'checked' : '' }}>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dias de Teste</label>
                        <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Descrição</label>
                    <textarea name="description" rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">{{ old('description', $plan->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Módulos Inclusos</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($modules as $module)
                            <label class="flex items-center gap-2 p-3 border border-gray-200 rounded-lg hover:border-purple-400 transition-colors">
                                <input type="checkbox" name="modules[]" value="{{ $module->id }}" {{ in_array($module->id, old('modules', $selectedModules)) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-800">{{ $module->display_name ?? $module->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('corporativo.plans.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded-lg">Cancelar</a>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection