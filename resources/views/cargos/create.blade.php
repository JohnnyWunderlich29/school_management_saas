@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Cargos', 'url' => route('cargos.index')],
    ['title' => 'Novo Cargo']
]" />

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Novo Cargo</h1>
        <p class="text-gray-600">Crie um novo cargo e defina suas permissões</p>
    </div>

    <x-card>
        <form action="{{ route('cargos.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Informações Básicas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <x-input
                        type="text"
                        name="nome"
                        label="Nome do Cargo *"
                        :value="old('nome')"
                        required
                        placeholder="Nome do cargo"
                    />
                </div>

                <div>
                    <x-select
                        name="ativo"
                        label="Status"
                    >
                        <option value="1" {{ old('ativo', '1') == '1' ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ old('ativo', '1') == '0' ? 'selected' : '' }}>Inativo</option>
                    </x-select>
                </div>
                <div>
                    <x-select
                        name="tipo_cargo"
                        label="Tipo de Cargo *"
                        :options="[
                            'professor' => 'Professor',
                            'coordenador' => 'Coordenador',
                            'administrador' => 'Administrador',
                            'outro' => 'Outro'
                        ]"
                        :selected="old('tipo_cargo', 'outro')"
                        required
                    />
                </div>
            </div>

            @if(!empty($templates))
            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Templates de cargos</label>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($templates as $tpl)
                        <button type="button" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md text-sm font-medium hover:bg-indigo-200 transition apply-template"
                            data-nome="{{ $tpl['nome'] }}"
                            data-tipo="{{ $tpl['tipo'] }}"
                            data-permissoes='@json($tpl['permissoes'])'>
                            {{ $tpl['nome'] }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                <x-textarea
                    name="descricao"
                    id="descricao"
                    rows="3"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('descricao') border-red-300 @enderror"
                    placeholder="Descrição do cargo"
                >{{ old('descricao') }}</x-textarea>
                @error('descricao')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Permissões</label>
                
                <div class="mb-4 flex flex-wrap gap-2 sm:gap-3">
                    <button type="button" id="expand-all" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md text-sm font-medium hover:bg-indigo-200 transition">
                        Expandir todos
                    </button>
                    <button type="button" id="collapse-all" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition">
                        Recolher todos
                    </button>
                    <button type="button" id="select-all" class="px-3 py-1 bg-green-100 text-green-700 rounded-md text-sm font-medium hover:bg-green-200 transition">
                        Selecionar todos
                    </button>
                    <button type="button" id="unselect-all" class="px-3 py-1 bg-red-100 text-red-700 rounded-md text-sm font-medium hover:bg-red-200 transition">
                        Desmarcar todos
                    </button>
                </div>
                
                <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                    @foreach($permissoes->groupBy('modulo') as $modulo => $permissoesModulo)
                        <div class="border border-gray-200 rounded-lg overflow-hidden accordion-item" data-module="{{ $modulo }}">
                            <div class="bg-gray-50 px-4 py-3 flex justify-between items-center cursor-pointer accordion-header">
                                <div class="flex items-center">
                                    <h6 class="font-medium text-gray-900">{{ ucfirst($modulo) }}</h6>
                                    <span class="ml-2 text-xs bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full">
                                        {{ count($permissoesModulo) }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center">
                                        <input
                                            type="checkbox"
                                            class="select-all-module focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                            data-module="{{ $modulo }}"
                                        >
                                        <label class="ml-2 text-sm text-gray-700">Todos</label>
                                    </div>
                                    <span class="text-gray-400 accordion-icon">▼</span>
                                </div>
                            </div>
                            <div class="accordion-content hidden">
                                <div class="p-4 bg-white">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach($permissoesModulo as $permissao)
                                            <div class="relative flex items-start p-2 hover:bg-gray-50 rounded-md transition-colors">
                                                <div class="flex items-center h-5">
                                                    <input
                                                        type="checkbox"
                                                        name="permissoes[]"
                                                        value="{{ $permissao->id }}"
                                                        id="permissao_{{ $permissao->id }}"
                                                        class="permission-checkbox focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                                        data-module="{{ $modulo }}"
                                                        {{ in_array($permissao->id, old('permissoes', [])) ? 'checked' : '' }}
                                                    >
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="permissao_{{ $permissao->id }}" class="font-medium text-gray-700">
                                                        {{ $permissao->nome }}
                                                    </label>
                                                    @if($permissao->descricao)
                                                        <p class="text-gray-500 text-xs">{{ $permissao->descricao }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('permissoes')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col sm:flex-row justify-between pt-6 space-y-3 sm:space-y-0 sm:space-x-3">
                <x-button
                    type="button"
                    color="secondary"
                    href="{{ route('cargos.index') }}"
                    class="w-full sm:w-auto"
                >
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="hidden sm:inline">Voltar</span>
                    <span class="sm:hidden">Cancelar</span>
                </x-button>
                
                <x-button
                    type="submit"
                    color="primary"
                    class="w-full sm:w-auto"
                >
                    <i class="fas fa-save mr-2"></i>
                    <span class="hidden sm:inline">Salvar Cargo</span>
                    <span class="sm:hidden">Salvar</span>
                </x-button>
            </div>
        </form>
    </x-card>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.apply-template').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const nome = this.dataset.nome;
            const tipo = this.dataset.tipo;
            const permissoes = JSON.parse(this.dataset.permissoes || '[]');

            const nomeInput = document.querySelector('input[name="nome"]');
            const tipoSelect = document.querySelector('select[name="tipo_cargo"]');
            if (nomeInput) nomeInput.value = nome;
            if (tipoSelect) tipoSelect.value = tipo;

            if (Array.isArray(permissoes)) {
                permissoes.forEach(function(id) {
                    const cb = document.querySelector(`#permissao_${id}`);
                    if (cb) cb.checked = true;
                });
            }
        });
    });
    // Funcionalidade para selecionar todas as permissões de um módulo
    document.querySelectorAll('.select-all-module').forEach(function(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const module = this.dataset.module;
            const moduleCheckboxes = document.querySelectorAll(`input[data-module="${module}"].permission-checkbox`);
            
            moduleCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        });
    });

    // Atualizar o checkbox "Todos" quando permissões individuais são alteradas
    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const module = this.dataset.module;
            const moduleCheckboxes = document.querySelectorAll(`input[data-module="${module}"].permission-checkbox`);
            const selectAllCheckbox = document.querySelector(`input[data-module="${module}"].select-all-module`);
            
            const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
            const noneChecked = Array.from(moduleCheckboxes).every(cb => !cb.checked);
            
            if (allChecked) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else if (noneChecked) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        });
    });
});
</script>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Accordion functionality
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.accordion-icon');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.textContent = '▲';
            } else {
                content.classList.add('hidden');
                icon.textContent = '▼';
            }
        });
    });

    // Expand all button
    document.getElementById('expand-all').addEventListener('click', function() {
        document.querySelectorAll('.accordion-content').forEach(content => {
            content.classList.remove('hidden');
        });
        document.querySelectorAll('.accordion-icon').forEach(icon => {
            icon.textContent = '▲';
        });
    });

    // Collapse all button
    document.getElementById('collapse-all').addEventListener('click', function() {
        document.querySelectorAll('.accordion-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.querySelectorAll('.accordion-icon').forEach(icon => {
            icon.textContent = '▼';
        });
    });

    // Select all button
    document.getElementById('select-all').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        document.querySelectorAll('.select-all-module').forEach(checkbox => {
            checkbox.checked = true;
        });
    });

    // Unselect all button
    document.getElementById('unselect-all').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        document.querySelectorAll('.select-all-module').forEach(checkbox => {
            checkbox.checked = false;
        });
    });

    // Module "Todos" checkbox functionality
    document.querySelectorAll('.select-all-module').forEach(selectAllCheckbox => {
        selectAllCheckbox.addEventListener('change', function() {
            const module = this.getAttribute('data-module');
            const moduleCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
            
            moduleCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    });

    // Individual permission checkbox functionality
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const module = this.getAttribute('data-module');
            const moduleCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
            const selectAllCheckbox = document.querySelector(`.select-all-module[data-module="${module}"]`);
            
            const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(moduleCheckboxes).some(cb => cb.checked);
            
            if (allChecked) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else if (someChecked) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
        });
    });

    // Initialize module checkboxes state
    document.querySelectorAll('.select-all-module').forEach(selectAllCheckbox => {
        const module = selectAllCheckbox.getAttribute('data-module');
        const moduleCheckboxes = document.querySelectorAll(`.permission-checkbox[data-module="${module}"]`);
        
        const allChecked = Array.from(moduleCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(moduleCheckboxes).some(cb => cb.checked);
        
        if (allChecked) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (someChecked) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    });
});
</script>
@endpush