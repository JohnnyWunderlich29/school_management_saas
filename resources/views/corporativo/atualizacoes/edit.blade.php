@extends('corporativo.layout')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Editar Atualização</h1>
        <a href="{{ route('corporativo.atualizacoes.index') }}" class="text-sm text-gray-600 hover:text-gray-800">Voltar</a>
    </div>

    <div class="bg-white shadow rounded p-6">
        <form action="{{ route('corporativo.atualizacoes.update', $update) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700">Título</label>
                <input type="text" name="title" value="{{ old('title', $update->title) }}" class="mt-1 w-full border rounded px-3 py-2 text-sm" required>
                @error('title')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Texto</label>
                <textarea name="body" rows="8" class="mt-1 w-full border rounded px-3 py-2 text-sm" required>{{ old('body', $update->body) }}</textarea>
                @error('body')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Imagem (opcional)</label>
                @if($update->image_path)
                    <div class="mb-2">
                        <img src="/{{ $update->image_path }}" alt="Imagem atual" class="max-h-40 rounded border">
                    </div>
                @endif
                <input type="file" name="image" accept="image/*" class="mt-1 w-full text-sm">
                @error('image')<div class="text-red-600 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="pt-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i> Atualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection