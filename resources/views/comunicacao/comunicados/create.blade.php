@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Comunicados', 'route' => 'comunicados.index'],
    ['title' => 'Novo Comunicado']
]" />

<x-card>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Novo Comunicado</h1>
        <p class="mt-1 text-sm text-gray-600">Crie um novo comunicado para enviar à comunidade escolar</p>
    </div>

    <form action="{{ route('comunicados.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Título -->
        <div>
            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                Título <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="titulo" 
                   name="titulo" 
                   value="{{ old('titulo') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('titulo') border-red-500 @enderror"
                   placeholder="Digite o título do comunicado"
                   required>
            @error('titulo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Tipo -->
        <div>
            <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                Tipo <span class="text-red-500">*</span>
            </label>
            <select id="tipo" 
                    name="tipo" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('tipo') border-red-500 @enderror"
                    required>
                <option value="">Selecione o tipo</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo }}" {{ old('tipo') == $tipo ? 'selected' : '' }}>
                        {{ ucfirst($tipo) }}
                    </option>
                @endforeach
            </select>
            @error('tipo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Destinatário -->
        <div>
            <label for="destinatario_tipo" class="block text-sm font-medium text-gray-700 mb-2">
                Destinatário <span class="text-red-500">*</span>
            </label>
            <select id="destinatario_tipo" 
                    name="destinatario_tipo" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('destinatario_tipo') border-red-500 @enderror"
                    required>
                <option value="">Selecione o destinatário</option>
                <option value="todos" {{ old('destinatario_tipo') == 'todos' ? 'selected' : '' }}>Todos</option>
                <option value="pais" {{ old('destinatario_tipo') == 'pais' ? 'selected' : '' }}>Pais/Responsáveis</option>
                <option value="professores" {{ old('destinatario_tipo') == 'professores' ? 'selected' : '' }}>Professores</option>
                <option value="turma_especifica" {{ old('destinatario_tipo') == 'turma_especifica' ? 'selected' : '' }}>Turma Específica</option>
            </select>
            @error('destinatario_tipo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Turma (condicional) -->
        <div id="turma-field" class="hidden">
            <label for="turma_id" class="block text-sm font-medium text-gray-700 mb-2">
                Turma <span class="text-red-500">*</span>
            </label>
            <select id="turma_id" 
                    name="turma_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('turma_id') border-red-500 @enderror">
                <option value="">Selecione a turma</option>
                @foreach($turmas as $turma)
                    <option value="{{ $turma->id }}" {{ old('turma_id') == $turma->id ? 'selected' : '' }}>
                        {{ $turma->nome }}
                    </option>
                @endforeach
            </select>
            @error('turma_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Conteúdo -->
        <div>
            <label for="conteudo" class="block text-sm font-medium text-gray-700 mb-2">
                Conteúdo <span class="text-red-500">*</span>
            </label>
            <textarea id="conteudo" 
                      name="conteudo" 
                      rows="6"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('conteudo') border-red-500 @enderror"
                      placeholder="Digite o conteúdo do comunicado"
                      required>{{ old('conteudo') }}</textarea>
            @error('conteudo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Data do Evento (opcional) -->
        <div>
            <label for="data_evento" class="block text-sm font-medium text-gray-700 mb-2">
                Data do Evento (opcional)
            </label>
            <input type="datetime-local" 
                   id="data_evento" 
                   name="data_evento" 
                   value="{{ old('data_evento') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('data_evento') border-red-500 @enderror">
            <p class="mt-1 text-xs text-gray-500">Informe se o comunicado se refere a um evento específico</p>
            @error('data_evento')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Opções -->
        <div class="space-y-4">
            <div class="flex items-center">
                <input type="checkbox" 
                       id="requer_confirmacao" 
                       name="requer_confirmacao" 
                       value="1"
                       {{ old('requer_confirmacao') ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="requer_confirmacao" class="ml-2 block text-sm text-gray-700">
                    Requer confirmação de recebimento
                </label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" 
                       id="publicar_agora" 
                       name="publicar_agora" 
                       value="1"
                       {{ old('publicar_agora', true) ? 'checked' : '' }}
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="publicar_agora" class="ml-2 block text-sm text-gray-700">
                    Publicar imediatamente
                </label>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <x-button href="{{ route('comunicados.index') }}" color="secondary">
                Cancelar
            </x-button>
            <x-button type="submit" color="primary">
                <i class="fas fa-save mr-2"></i>
                Criar Comunicado
            </x-button>
        </div>
    </form>
</x-card>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const destinatarioSelect = document.getElementById('destinatario_tipo');
    const turmaField = document.getElementById('turma-field');
    const turmaSelect = document.getElementById('turma_id');

    function toggleTurmaField() {
        if (destinatarioSelect.value === 'turma_especifica') {
            turmaField.classList.remove('hidden');
            turmaSelect.required = true;
        } else {
            turmaField.classList.add('hidden');
            turmaSelect.required = false;
            turmaSelect.value = '';
        }
    }

    destinatarioSelect.addEventListener('change', toggleTurmaField);
    
    // Verificar estado inicial
    toggleTurmaField();
});
</script>
@endpush
@endsection