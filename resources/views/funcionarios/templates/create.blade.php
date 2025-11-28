@extends('layouts.app')

@section('title', 'Novo Template de Escala - ' . $funcionario->nome)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Funcionários', 'url' => route('funcionarios.index')],
        ['title' => $funcionario->nome, 'url' => route('funcionarios.show', $funcionario)],
        ['title' => 'Templates', 'url' => route('funcionarios.templates.index', $funcionario)],
        ['title' => 'Novo Template']
    ]" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl font-bold text-gray-900">Novo Template de Escala</h1>
            <p class="text-gray-600 mt-1">Funcionário: <span class="font-semibold text-gray-900">{{ $funcionario->nome }}</span></p>
        </div>
        <a href="{{ route('funcionarios.templates.index', $funcionario) }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('funcionarios.templates.store', $funcionario) }}" class="space-y-8">
        @csrf
        
        <!-- Informações Básicas -->
        <x-card title="Informações Básicas">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <x-input 
                        name="nome_template" 
                        label="Nome do Template" 
                        :value="old('nome_template')" 
                        placeholder="Ex: Escala Padrão, Escala Reduzida, etc."
                        required
                    />
                </div>
                <div class="flex items-center">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   name="ativo" 
                                   id="ativo" 
                                   value="1" 
                                   {{ old('ativo', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="ativo" class="font-medium text-gray-700">Template Ativo</label>
                            <p class="text-gray-500">Apenas um template pode estar ativo por vez</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Configuração Semanal -->
        <x-card>
            <x-slot name="title">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Configuração Semanal</h3>
                        <p class="mt-1 text-sm text-gray-500">Configure os horários para cada dia da semana - 4 períodos disponíveis</p>
                    </div>
                    <button type="button" 
                            onclick="aplicarParaTodos()" 
                            class="mt-3 sm:mt-0 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-copy mr-2"></i>
                        Aplicar primeiro dia a todos
                    </button>
                </div>
            </x-slot>
            
            @php
                $diasSemana = [
                    'segunda' => 'Segunda-feira',
                    'terca' => 'Terça-feira', 
                    'quarta' => 'Quarta-feira',
                    'quinta' => 'Quinta-feira',
                    'sexta' => 'Sexta-feira',
                    'sabado' => 'Sábado',
                    'domingo' => 'Domingo'
                ];
                $tiposTrabalho = [
                    'Normal' => 'Normal',
                    'Extra' => 'Extra',
                    'Substituição' => 'Substituição',
                    'PL' => 'PL (Planejamento)'
                ];
            @endphp
            
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($diasSemana as $dia => $diaLabel)
                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       class="dia-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                                       id="dia_{{ $dia }}" 
                                       name="dias[{{ $dia }}][ativo]"
                                       value="1"
                                       {{ old("dias.{$dia}.ativo") ? 'checked' : '' }}
                                       onchange="toggleDiaConfig('{{ $dia }}')">
                                <label class="ml-3 text-sm font-medium text-gray-900 cursor-pointer" for="dia_{{ $dia }}">
                                    {{ $diaLabel }}
                                </label>
                            </div>
                        </div>
                        
                        <div class="p-4 dia-config" id="config_{{ $dia }}" style="{{ old("dias.{$dia}.ativo") ? '' : 'display: none;' }}">
                            <div class="space-y-4">
                                <!-- Período Manhã -->
                                <div class="bg-blue-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-medium text-blue-900">Manhã</h4>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" 
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                                   id="manha_{{ $dia }}"
                                                   name="dias[{{ $dia }}][manha_ativo]"
                                                   value="1"
                                                   {{ old("dias.{$dia}.manha_ativo") ? 'checked' : '' }}
                                                   onchange="togglePeriodo('{{ $dia }}', 'manha')">
                                            <span class="ml-2 text-xs text-blue-700">Ativar</span>
                                        </label>
                                    </div>
                                    <div class="periodo-fields" id="manha_fields_{{ $dia }}" style="{{ old("dias.{$dia}.manha_ativo") ? '' : 'display: none;' }}">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Entrada</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][manha_inicio]" 
                                                       value="{{ old("dias.{$dia}.manha_inicio", '08:00') }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Saída</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][manha_fim]" 
                                                       value="{{ old("dias.{$dia}.manha_fim", '12:00') }}">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" name="dias[{{ $dia }}][manha_tipo]">
                                                @foreach($tiposTrabalho as $tipoValue => $tipoLabel)
                                                    <option value="{{ $tipoValue }}" {{ old("dias.{$dia}.manha_tipo", 'Normal') == $tipoValue ? 'selected' : '' }}>
                                                        {{ $tipoLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Período Manhã (Opcional) -->
                                <div class="bg-yellow-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-medium text-yellow-900">Manhã (Opcional)</h4>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" 
                                                   class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded" 
                                                   id="manha2_{{ $dia }}"
                                                   name="dias[{{ $dia }}][manha2_ativo]"
                                                   value="1"
                                                   {{ old("dias.{$dia}.manha2_ativo") ? 'checked' : '' }}
                                                   onchange="togglePeriodo('{{ $dia }}', 'manha2')">
                                            <span class="ml-2 text-xs text-yellow-700">Ativar</span>
                                        </label>
                                    </div>
                                    <div class="periodo-fields" id="manha2_fields_{{ $dia }}" style="{{ old("dias.{$dia}.manha2_ativo") ? '' : 'display: none;' }}">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Entrada</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][manha2_inicio]" 
                                                       value="{{ old("dias.{$dia}.manha2_inicio") }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Saída</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][manha2_fim]" 
                                                       value="{{ old("dias.{$dia}.manha2_fim") }}">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" name="dias[{{ $dia }}][manha2_tipo]">
                                                @foreach($tiposTrabalho as $tipoValue => $tipoLabel)
                                                    <option value="{{ $tipoValue }}" {{ old("dias.{$dia}.manha2_tipo", 'Normal') == $tipoValue ? 'selected' : '' }}>
                                                        {{ $tipoLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Período Tarde -->
                                <div class="bg-green-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-medium text-green-900">Tarde</h4>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" 
                                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded" 
                                                   id="tarde_{{ $dia }}"
                                                   name="dias[{{ $dia }}][tarde_ativo]"
                                                   value="1"
                                                   {{ old("dias.{$dia}.tarde_ativo") ? 'checked' : '' }}
                                                   onchange="togglePeriodo('{{ $dia }}', 'tarde')">
                                            <span class="ml-2 text-xs text-green-700">Ativar</span>
                                        </label>
                                    </div>
                                    <div class="periodo-fields" id="tarde_fields_{{ $dia }}" style="{{ old("dias.{$dia}.tarde_ativo") ? '' : 'display: none;' }}">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Entrada</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][tarde_inicio]" 
                                                       value="{{ old("dias.{$dia}.tarde_inicio", '13:00') }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Saída</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][tarde_fim]" 
                                                       value="{{ old("dias.{$dia}.tarde_fim", '17:00') }}">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" name="dias[{{ $dia }}][tarde_tipo]">
                                                @foreach($tiposTrabalho as $tipoValue => $tipoLabel)
                                                    <option value="{{ $tipoValue }}" {{ old("dias.{$dia}.tarde_tipo", 'Normal') == $tipoValue ? 'selected' : '' }}>
                                                        {{ $tipoLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Período Tarde (Opcional) -->
                                <div class="bg-purple-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-medium text-purple-900">Tarde (Opcional)</h4>
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" 
                                                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded" 
                                                   id="tarde2_{{ $dia }}"
                                                   name="dias[{{ $dia }}][tarde2_ativo]"
                                                   value="1"
                                                   {{ old("dias.{$dia}.tarde2_ativo") ? 'checked' : '' }}
                                                   onchange="togglePeriodo('{{ $dia }}', 'tarde2')">
                                            <span class="ml-2 text-xs text-purple-700">Ativar</span>
                                        </label>
                                    </div>
                                    <div class="periodo-fields" id="tarde2_fields_{{ $dia }}" style="{{ old("dias.{$dia}.tarde2_ativo") ? '' : 'display: none;' }}">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Entrada</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][tarde2_inicio]" 
                                                       value="{{ old("dias.{$dia}.tarde2_inicio") }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Saída</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][tarde2_fim]" 
                                                       value="{{ old("dias.{$dia}.tarde2_fim") }}">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" name="dias[{{ $dia }}][tarde2_tipo]">
                                                @foreach($tiposTrabalho as $tipoValue => $tipoLabel)
                                                    <option value="{{ $tipoValue }}" {{ old("dias.{$dia}.tarde2_tipo", 'Normal') == $tipoValue ? 'selected' : '' }}>
                                                        {{ $tipoLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        <!-- Ações -->
        <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
            <a href="{{ route('funcionarios.templates.index', $funcionario) }}" 
               class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <i class="fas fa-times mr-2"></i> Cancelar
            </a>
            <button type="submit" 
                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                <i class="fas fa-save mr-2"></i> Salvar Template
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleDiaConfig(dia) {
    const checkbox = document.getElementById(`dia_${dia}`);
    const config = document.getElementById(`config_${dia}`);
    
    if (checkbox.checked) {
        config.style.display = 'block';
    } else {
        config.style.display = 'none';
    }
}

function togglePeriodo(dia, periodo) {
    const checkbox = document.getElementById(`${periodo}_${dia}`);
    const fields = document.getElementById(`${periodo}_fields_${dia}`);
    
    if (checkbox.checked) {
        fields.style.display = 'block';
    } else {
        fields.style.display = 'none';
    }
}

// Função para aplicar configuração a todos os dias
function aplicarParaTodos() {
    // Encontrar o primeiro dia configurado (checkbox marcado)
    const primeiroCheckbox = document.querySelector('.dia-checkbox:checked');
    if (!primeiroCheckbox) {
        alert('Configure pelo menos um dia primeiro!');
        return;
    }
    
    const primeiroDia = primeiroCheckbox.id.replace('dia_', '');
    const primeiroConfig = document.getElementById(`config_${primeiroDia}`);
    const inputs = primeiroConfig.querySelectorAll('input, select');
    const valores = {};
    
    // Capturar valores do primeiro dia configurado
    inputs.forEach(input => {
        if (input.type === 'checkbox') {
            valores[input.name] = input.checked;
        } else {
            valores[input.name] = input.value;
        }
    });
    
    // Aplicar aos outros dias
    document.querySelectorAll('.dia-checkbox').forEach(checkbox => {
        const dia = checkbox.id.replace('dia_', '');
        if (dia !== primeiroDia) {
            checkbox.checked = true;
            toggleDiaConfig(dia);
            
            // Aplicar valores aos campos correspondentes
            Object.keys(valores).forEach(fieldName => {
                const newFieldName = fieldName.replace(`[${primeiroDia}]`, `[${dia}]`);
                const field = document.querySelector(`[name="${newFieldName}"]`);
                if (field) {
                    if (field.type === 'checkbox') {
                        field.checked = valores[fieldName];
                        // Trigger toggle functions para os períodos
                        if (field.name.includes('manha_ativo')) {
                            togglePeriodo(dia, 'manha');
                        } else if (field.name.includes('manha_opcional_ativo')) {
                            togglePeriodo(dia, 'manha_opcional');
                        } else if (field.name.includes('tarde_ativo')) {
                            togglePeriodo(dia, 'tarde');
                        } else if (field.name.includes('tarde_opcional_ativo')) {
                            togglePeriodo(dia, 'tarde_opcional');
                        }
                    } else {
                        field.value = valores[fieldName];
                    }
                }
            });
        }
    });
}
</script>
@endpush