@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Usuários', 'url' => route('usuarios.index')],
    ['title' => 'Editar Usuário', 'url' => '#']
]" />

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('usuarios.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Informações do Usuário -->
        <div>
            <h4 class="text-md font-semibold text-gray-800 mb-4">Informações do Usuário</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <x-input
                        type="text"
                        name="name"
                        label="Nome Completo *"
                        :value="old('name', $user->name)"
                        required
                        placeholder="Digite o nome completo do usuário"
                    />
                </div>

                <div class="md:col-span-2">
                    <x-input
                        type="email"
                        name="email"
                        label="Email *"
                        :value="old('email', $user->email)"
                        required
                        placeholder="Digite o email do usuário"
                    />
                </div>

                <div>
                    <x-input
                        type="password"
                        name="password"
                        label="Nova Senha (deixe em branco para manter a atual)"
                        placeholder="Digite a nova senha (opcional)"
                    />
                </div>

                <div>
                    <x-input
                        type="password"
                        name="password_confirmation"
                        label="Confirmar Nova Senha"
                        placeholder="Confirme a nova senha"
                    />
                </div>
            </div>
        </div>

        <!-- Cargos no Sistema -->
        <div>
            <h4 class="text-md font-semibold text-gray-800 mb-4">Cargos no Sistema</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($cargos as $cargo)
                    <div class="flex items-start space-x-3">
                        <input 
                            type="checkbox" 
                            name="cargos[]" 
                            value="{{ $cargo->id }}" 
                            id="cargo_{{ $cargo->id }}"
                            class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            {{ in_array($cargo->id, old('cargos', $user->cargos->pluck('id')->toArray())) ? 'checked' : '' }}
                        >
                        <label for="cargo_{{ $cargo->id }}" class="text-sm text-gray-700">
                            <span class="font-medium">{{ $cargo->nome }}</span>
                            @if($cargo->descricao)
                                <span class="block text-gray-500 text-xs mt-1">{{ $cargo->descricao }}</span>
                            @endif
                        </label>
                    </div>
                @endforeach
            </div>
            @error('cargos')
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <!-- Botões -->
        <div class="flex justify-end space-x-3 pt-4 border-t">
            <x-button href="{{ route('usuarios.index') }}" color="secondary">
                <i class="fas fa-times mr-1"></i> Cancelar
            </x-button>
            <x-button type="submit" color="primary">
                <i class="fas fa-save mr-1"></i> Atualizar Usuário
            </x-button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Script específico para a página de edição de usuários
    console.log('Página de edição de usuários carregada');
});
</script>
@endpush