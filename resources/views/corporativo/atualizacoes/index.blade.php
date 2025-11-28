@extends('corporativo.layout')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Atualizações do Sistema</h1>
        <a href="{{ route('corporativo.atualizacoes.create') }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i> Nova atualização
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white shadow rounded">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">Título</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">Criado por</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600">Data</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($updates as $update)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-800">{{ $update->title }}</td>
                    <td class="px-4 py-2 text-sm text-gray-600">{{ optional($update->creator)->name ?? '—' }}</td>
                    <td class="px-4 py-2 text-sm text-gray-600">{{ $update->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-2 text-sm text-right">
                        <a href="{{ route('corporativo.atualizacoes.edit', $update) }}" class="inline-flex items-center px-2 py-1 text-indigo-600 hover:text-indigo-800"><i class="fas fa-edit mr-1"></i> Editar</a>
                        <form action="{{ route('corporativo.atualizacoes.destroy', $update) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-2 py-1 text-red-600 hover:text-red-800" onclick="return confirm('Excluir esta atualização?')">
                                <i class="fas fa-trash mr-1"></i> Excluir
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Nenhuma atualização cadastrada ainda.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $updates->links('components.pagination') }}
    </div>
</div>
@endsection