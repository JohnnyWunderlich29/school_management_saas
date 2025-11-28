@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Cargos', 'url' => route('cargos.index')],
    ['title' => 'Editar Cargo', 'url' => '#']
]" />

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Editar Cargo</h1>
        <p class="text-gray-600">Atualize as informações do cargo {{ $cargo->nome }}</p>
    </div>

    <x-card>
        <form action="{{ route('cargos.update', $cargo) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <x-input
                        type="text"
                        name="nome"
                        label="Nome do Cargo *"
                        :value="old('nome', $cargo->nome)"
                        required
                        placeholder="Nome do cargo"
                    />
                </div>

                <div>
                    <x-select
                        name="ativo"
                        label="Status"
                    >
                        <option value="1" {{ old('ativo', $cargo->ativo) == '1' ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ old('ativo', $cargo->ativo) == '0' ? 'selected' : '' }}>Inativo</option>
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
                        :selected="old('tipo_cargo', $cargo->tipo_cargo)"
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
                >{{ old('descricao', $cargo->descricao) }}</x-textarea>
                @error('descricao')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-4">Permissões</label>
                
                <div class="mb-4 flex flex-wrap gap-2">
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
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                                        {{ in_array($permissao->id, old('permissoes', $cargo->permissoes->pluck('id')->toArray())) ? 'checked' : '' }}
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

            <div class="flex justify-between pt-6">
                <x-button
                    type="button"
                    color="secondary"
                    href="{{ route('cargos.index') }}"
                >
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </x-button>
                
                <x-button
                    type="submit"
                    color="primary"
                >
                    <i class="fas fa-save mr-2"></i>
                    Atualizar
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
    // Inicializar o estado dos checkboxes "Todos" baseado nas permissões já selecionadas
    function updateSelectAllCheckboxes() {
        document.querySelectorAll('.select-all-module').forEach(function(selectAllCheckbox) {
            const module = selectAllCheckbox.dataset.module;
            const moduleCheckboxes = document.querySelectorAll(`input[data-module="${module}"].permission-checkbox`);
            
            const checkedCount = Array.from(moduleCheckboxes).filter(cb => cb.checked).length;
            const totalCount = moduleCheckboxes.length;
            
            if (checkedCount === totalCount) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount > 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
        });
    }
    
    // Inicializar o estado inicial
    updateSelectAllCheckboxes();
    
    // Abrir o primeiro accordion por padrão
    const firstAccordion = document.querySelector('.accordion-item');
    if (firstAccordion) {
        firstAccordion.querySelector('.accordion-content').classList.remove('hidden');
        firstAccordion.querySelector('.accordion-icon').classList.add('rotate-180');
    }

    // Funcionalidade para selecionar todas as permissões de um módulo
    document.querySelectorAll('.select-all-module').forEach(function(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const module = this.dataset.module;
            const moduleCheckboxes = document.querySelectorAll(`input[data-module="${module}"].permission-checkbox`);
            
            moduleCheckboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            this.indeterminate = false;
        });
    });

    // Atualizar o checkbox "Todos" quando permissões individuais são alteradas
    document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const module = this.dataset.module;
            const moduleCheckboxes = document.querySelectorAll(`input[data-module="${module}"].permission-checkbox`);
            const selectAllCheckbox = document.querySelector(`input[data-module="${module}"].select-all-module`);
            
            const checkedCount = Array.from(moduleCheckboxes).filter(cb => cb.checked).length;
            const totalCount = moduleCheckboxes.length;
            
            if (checkedCount === totalCount) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount > 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
        });
    });
    
    // Funcionalidade de accordion para mostrar/esconder permissões
    document.querySelectorAll('.accordion-header').forEach(function(header) {
        header.addEventListener('click', function(e) {
            // Não acionar o accordion se o clique foi no checkbox
            if (e.target.type === 'checkbox' || e.target.tagName === 'LABEL') {
                return;
            }
            
            const accordionItem = this.closest('.accordion-item');
            const content = accordionItem.querySelector('.accordion-content');
            const icon = this.querySelector('.accordion-icon');
            
            content.classList.toggle('hidden');
            if (content.classList.contains('hidden')) {
                icon.textContent = '▼';
            } else {
                icon.textContent = '▲';
            }
        });
    });
    
    // Botões de controle global
    document.getElementById('expand-all').addEventListener('click', function() {
        document.querySelectorAll('.accordion-content').forEach(function(content) {
            content.classList.remove('hidden');
        });
        document.querySelectorAll('.accordion-icon').forEach(function(icon) {
            icon.classList.add('rotate-180');
        });
    });
    
    document.getElementById('collapse-all').addEventListener('click', function() {
        document.querySelectorAll('.accordion-content').forEach(function(content) {
            content.classList.add('hidden');
        });
        document.querySelectorAll('.accordion-icon').forEach(function(icon) {
            icon.classList.remove('rotate-180');
        });
    });
    
    document.getElementById('select-all').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
            checkbox.checked = true;
        });
        document.querySelectorAll('.select-all-module').forEach(function(checkbox) {
            checkbox.checked = true;
            checkbox.indeterminate = false;
        });
    });
    
    document.getElementById('unselect-all').addEventListener('click', function() {
        document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
            checkbox.checked = false;
        });
        document.querySelectorAll('.select-all-module').forEach(function(checkbox) {
            checkbox.checked = false;
            checkbox.indeterminate = false;
        });
    });
});
</script>
@endsection