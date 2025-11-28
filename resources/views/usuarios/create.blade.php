@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Usuários', 'url' => route('usuarios.index')],
    ['title' => 'Criar Usuário', 'url' => '#']
]" />

<div class="bg-white rounded-lg shadow-sm p-6">
    <form action="{{ route('usuarios.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Seleção de Funcionário -->
        <div>
            <h4 class="text-md sm:text-lg font-semibold text-gray-800 mb-4">Selecionar Funcionário</h4>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <x-select
                        name="funcionario_id"
                        label="Funcionário *"
                        required
                        id="funcionario_select"
                    >
                        <option value="">Selecione um funcionário</option>
                        @foreach($funcionarios as $funcionario)
                            <option value="{{ $funcionario->id }}" 
                                    data-nome="{{ $funcionario->nome_completo }}"
                                    data-email="{{ $funcionario->email }}"
                                    {{ old('funcionario_id') == $funcionario->id ? 'selected' : '' }}>
                                {{ $funcionario->nome_completo }} - {{ $funcionario->cargo }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
            </div>
        </div>

        <!-- Informações do Usuário -->
        <div>
            <h4 class="text-md sm:text-lg font-semibold text-gray-800 mb-4">Informações do Usuário</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input
                        type="text"
                        name="name"
                        label="Nome Completo *"
                        :value="old('name')"
                        required
                        readonly
                        id="name_input"
                        placeholder="Selecione um funcionário para preencher automaticamente"
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-input
                        type="email"
                        name="email"
                        label="Email *"
                        :value="old('email')"
                        required
                        readonly
                        id="email_input"
                        placeholder="Selecione um funcionário para preencher automaticamente"
                    />
                </div>

                <div>
                    <x-input
                        type="password"
                        name="password"
                        label="Senha *"
                        required
                        placeholder="Digite a senha do usuário"
                    />
                </div>

                <div>
                    <x-input
                        type="password"
                        name="password_confirmation"
                        label="Confirmar Senha *"
                        required
                        placeholder="Confirme a senha"
                    />
                </div>
            </div>
        </div>

        <!-- Cargos no Sistema -->
        <div>
            <h4 class="text-md sm:text-lg font-semibold text-gray-800 mb-4">Cargos no Sistema</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($cargos as $cargo)
                    <div class="flex items-start space-x-3">
                        <input 
                            type="checkbox" 
                            name="cargos[]" 
                            value="{{ $cargo->id }}" 
                            id="cargo_{{ $cargo->id }}"
                            class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            {{ in_array($cargo->id, old('cargos', [])) ? 'checked' : '' }}
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
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t">
            <x-button href="{{ route('usuarios.index') }}" color="secondary" class="w-full sm:w-auto">
                <i class="fas fa-times mr-1"></i> 
                <span class="hidden sm:inline">Cancelar</span>
                <span class="sm:hidden">Cancelar</span>
            </x-button>
            <x-button type="submit" color="primary" class="w-full sm:w-auto">
                <i class="fas fa-save mr-1"></i> 
                <span class="hidden sm:inline">Criar Usuário</span>
                <span class="sm:hidden">Criar</span>
            </x-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const funcionarioSelect = document.getElementById('funcionario_select');
    const nameInput = document.getElementById('name_input');
    const emailInput = document.getElementById('email_input');
    
    funcionarioSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (selectedOption.value) {
            // Preencher os campos com os dados do funcionário selecionado
            nameInput.value = selectedOption.dataset.nome || '';
            emailInput.value = selectedOption.dataset.email || '';
        } else {
            // Limpar os campos se nenhum funcionário for selecionado
            nameInput.value = '';
            emailInput.value = '';
        }
    });
});
</script>
@endpush

@endsection