@extends('layouts.app')

@section('title', 'Editar Template - ' . $template->nome_template)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['title' => 'Dashboard', 'url' => route('dashboard')],
        ['title' => 'Funcionários', 'url' => route('funcionarios.index')],
        ['title' => $funcionario->nome, 'url' => route('funcionarios.show', $funcionario)],
        ['title' => 'Templates', 'url' => route('funcionarios.templates.index', $funcionario)],
        ['title' => $template->nome_template, 'url' => route('funcionarios.templates.show', [$funcionario, $template])],
        ['title' => 'Editar']
    ]" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-3xl font-bold text-gray-900">Editar Template de Escala</h1>
            <p class="text-gray-600 mt-1">Funcionário: <span class="font-semibold text-gray-900">{{ $funcionario->nome }}</span></p>
        </div>
        <a href="{{ route('funcionarios.templates.show', [$funcionario, $template]) }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Voltar
        </a>
    </div>

    <form method="POST" action="{{ route('funcionarios.templates.update', [$funcionario, $template]) }}" class="space-y-8">
        @csrf
        @method('PUT')
        
        <!-- Informações Básicas -->
        <x-card>
            <x-slot name="header">
                <h3 class="text-lg font-semibold text-gray-900">Informações Básicas</h3>
            </x-slot>
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-3">
                    <label for="nome_template" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Template <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nome_template"
                           name="nome_template" 
                           value="{{ old('nome_template', $template->nome_template) }}"
                           placeholder="Ex: Escala Padrão, Escala Reduzida, etc."
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('nome_template') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror">
                    @error('nome_template')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-4">Status</label>
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="ativo" 
                               name="ativo" 
                               value="1" 
                               {{ old('ativo', $template->ativo) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="ativo" class="ml-3 text-sm text-gray-700">
                            Template Ativo
                        </label>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Apenas um template pode estar ativo por vez</p>
                </div>
            </div>
        </x-card>

        <!-- Configuração Semanal -->
        <x-card title="Configuração Semanal" subtitle="Configure os horários para cada dia da semana">
            <x-slot name="headerActions">
                <button type="button" 
                        onclick="aplicarParaTodos()"
                        class="inline-flex items-center px-3 py-2 border border-blue-300 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-copy mr-2"></i> Aplicar primeiro dia a todos
                </button>
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
                    @php
                        // Verificar se algum período está configurado para determinar se o dia está ativo
                        $temPeriodoConfigurado = ($template->{$dia.'_inicio'} && $template->{$dia.'_fim'}) ||
                                               ($template->{$dia.'_manha2_inicio'} && $template->{$dia.'_manha2_fim'}) ||
                                               ($template->{$dia.'_tarde_inicio'} && $template->{$dia.'_tarde_fim'}) ||
                                               ($template->{$dia.'_tarde2_inicio'} && $template->{$dia.'_tarde2_fim'});
                        
                        $diaAtivo = old("dias.{$dia}.ativo", $temPeriodoConfigurado);
                        $horaEntrada = old("dias.{$dia}.manha_inicio", $template->{$dia.'_inicio'});
                        $horaSaida = old("dias.{$dia}.manha_fim", $template->{$dia.'_fim'});
                        $tipo = old("dias.{$dia}.manha_tipo", $template->{$dia.'_tipo'});
                        
                        // Períodos adicionais
                        $manha2Ativo = old("dias.{$dia}.manha2_ativo", $template->{$dia.'_manha2_inicio'} && $template->{$dia.'_manha2_fim'});
                        $tardeAtivo = old("dias.{$dia}.tarde_ativo", $template->{$dia.'_tarde_inicio'} && $template->{$dia.'_tarde_fim'});
                        $tarde2Ativo = old("dias.{$dia}.tarde2_ativo", $template->{$dia.'_tarde2_inicio'} && $template->{$dia.'_tarde2_fim'});
                    @endphp
                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       id="dia_{{ $dia }}" 
                                       class="dia-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                       name="dias[{{ $dia }}][ativo]" 
                                       value="1" 
                                       {{ $diaAtivo ? 'checked' : '' }}
                                       onchange="toggleDiaConfig('{{ $dia }}')">
                                <label class="ml-3 text-sm font-medium text-gray-900 cursor-pointer" for="dia_{{ $dia }}">
                                    {{ $diaLabel }}
                                </label>
                            </div>
                        </div>
                        
                        <div class="p-4 dia-config" id="config_{{ $dia }}" style="{{ $diaAtivo ? '' : 'display: none;' }}">
                            <div class="space-y-4">
                                <!-- Período Manhã -->
                                <div class="bg-blue-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-medium text-blue-900">Período Manhã</h4>
                                        <div class="flex items-center">
                                            @php
                                                $manhaAtivo = old("dias.{$dia}.manha_ativo", $template->{$dia.'_inicio'} && $template->{$dia.'_fim'});
                                            @endphp
                                            <input type="checkbox" 
                                                   id="manha_ativo_{{ $dia }}" 
                                                   name="dias[{{ $dia }}][manha_ativo]" 
                                                   value="1" 
                                                   {{ $manhaAtivo ? 'checked' : '' }}
                                                   onchange="togglePeriodo('{{ $dia }}', 'manha')"
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="manha_ativo_{{ $dia }}" class="ml-2 text-xs text-gray-700">Ativar</label>
                                        </div>
                                    </div>
                                    <div id="periodo_manha_{{ $dia }}" style="{{ $manhaAtivo ? '' : 'display: none;' }}">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Entrada</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][manha_inicio]" 
                                                       value="{{ $horaEntrada ? \Carbon\Carbon::parse($horaEntrada)->format('H:i') : '' }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Saída</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][manha_fim]" 
                                                       value="{{ $horaSaida ? \Carbon\Carbon::parse($horaSaida)->format('H:i') : '' }}">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" name="dias[{{ $dia }}][manha_tipo]">
                                                @foreach($tiposTrabalho as $tipoValue => $tipoLabel)
                                                    <option value="{{ $tipoValue }}" {{ $tipo == $tipoValue ? 'selected' : '' }}>
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
                                        <h4 class="text-sm font-medium text-yellow-900">Período Manhã (Opcional)</h4>
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   id="manha2_ativo_{{ $dia }}" 
                                                   name="dias[{{ $dia }}][manha2_ativo]" 
                                                   value="1" 
                                                   {{ $manha2Ativo ? 'checked' : '' }}
                                                   onchange="togglePeriodo('{{ $dia }}', 'manha2')"
                                                   class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded">
                                            <label for="manha2_ativo_{{ $dia }}" class="ml-2 text-xs text-gray-700">Ativar</label>
                                        </div>
                                    </div>
                                    <div id="periodo_manha2_{{ $dia }}" style="{{ $manha2Ativo ? '' : 'display: none;' }}">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Entrada</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][manha2_inicio]" 
                                                       value="{{ old("dias.{$dia}.manha2_inicio", $template->{$dia.'_manha2_inicio'} ? \Carbon\Carbon::parse($template->{$dia.'_manha2_inicio'})->format('H:i') : '') }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Saída</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][manha2_fim]" 
                                                       value="{{ old("dias.{$dia}.manha2_fim", $template->{$dia.'_manha2_fim'} ? \Carbon\Carbon::parse($template->{$dia.'_manha2_fim'})->format('H:i') : '') }}">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent" name="dias[{{ $dia }}][manha2_tipo]">
                                                @foreach($tiposTrabalho as $tipoValue => $tipoLabel)
                                                    <option value="{{ $tipoValue }}" {{ old("dias.{$dia}.manha2_tipo", $template->{$dia.'_manha2_tipo'}) == $tipoValue ? 'selected' : '' }}>
                                                        {{ $tipoLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Período Tarde -->
                                <div class="bg-orange-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-medium text-orange-900">Período Tarde</h4>
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   id="tarde_ativo_{{ $dia }}" 
                                                   name="dias[{{ $dia }}][tarde_ativo]" 
                                                   value="1" 
                                                   {{ $tardeAtivo ? 'checked' : '' }}
                                                   onchange="togglePeriodo('{{ $dia }}', 'tarde')"
                                                   class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                            <label for="tarde_ativo_{{ $dia }}" class="ml-2 text-xs text-gray-700">Ativar</label>
                                        </div>
                                    </div>
                                    <div id="periodo_tarde_{{ $dia }}" style="{{ $tardeAtivo ? '' : 'display: none;' }}">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Entrada</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][tarde_inicio]" 
                                                       value="{{ old("dias.{$dia}.tarde_inicio", $template->{$dia.'_tarde_inicio'} ? \Carbon\Carbon::parse($template->{$dia.'_tarde_inicio'})->format('H:i') : '') }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Saída</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][tarde_fim]" 
                                                       value="{{ old("dias.{$dia}.tarde_fim", $template->{$dia.'_tarde_fim'} ? \Carbon\Carbon::parse($template->{$dia.'_tarde_fim'})->format('H:i') : '') }}">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" name="dias[{{ $dia }}][tarde_tipo]">
                                                @foreach($tiposTrabalho as $tipoValue => $tipoLabel)
                                                    <option value="{{ $tipoValue }}" {{ old("dias.{$dia}.tarde_tipo", $template->{$dia.'_tarde_tipo'}) == $tipoValue ? 'selected' : '' }}>
                                                        {{ $tipoLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Período Tarde (Opcional) -->
                                <div class="bg-green-50 p-3 rounded-lg">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-medium text-green-900">Período Tarde (Opcional)</h4>
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   id="tarde2_ativo_{{ $dia }}" 
                                                   name="dias[{{ $dia }}][tarde2_ativo]" 
                                                   value="1" 
                                                   {{ $tarde2Ativo ? 'checked' : '' }}
                                                   onchange="togglePeriodo('{{ $dia }}', 'tarde2')"
                                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                            <label for="tarde2_ativo_{{ $dia }}" class="ml-2 text-xs text-gray-700">Ativar</label>
                                        </div>
                                    </div>
                                    <div id="periodo_tarde2_{{ $dia }}" style="{{ $tarde2Ativo ? '' : 'display: none;' }}">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Entrada</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][tarde2_inicio]" 
                                                       value="{{ old("dias.{$dia}.tarde2_inicio", $template->{$dia.'_tarde2_inicio'} ? \Carbon\Carbon::parse($template->{$dia.'_tarde2_inicio'})->format('H:i') : '') }}">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-700 mb-1">Saída</label>
                                                <input type="time" 
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                                                       name="dias[{{ $dia }}][tarde2_fim]" 
                                                       value="{{ old("dias.{$dia}.tarde2_fim", $template->{$dia.'_tarde2_fim'} ? \Carbon\Carbon::parse($template->{$dia.'_tarde2_fim'})->format('H:i') : '') }}">
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipo</label>
                                            <select class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" name="dias[{{ $dia }}][tarde2_tipo]">
                                                @foreach($tiposTrabalho as $tipoValue => $tipoLabel)
                                                    <option value="{{ $tipoValue }}" {{ old("dias.{$dia}.tarde2_tipo", $template->{$dia.'_tarde2_tipo'}) == $tipoValue ? 'selected' : '' }}>
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
        
        <!-- Botões de Ação -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mt-8">
            <a href="{{ route('funcionarios.templates.show', [$funcionario, $template]) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
            
            <button type="submit" 
                    class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-save mr-2"></i>Salvar Alterações
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

// Função para toggle de períodos individuais
function togglePeriodo(dia, periodo) {
    const checkbox = document.getElementById(`${periodo}_ativo_${dia}`);
    const config = document.getElementById(`periodo_${periodo}_${dia}`);
    
    if (checkbox && config) {
        if (checkbox.checked) {
            config.style.display = 'block';
        } else {
            config.style.display = 'none';
        }
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
                        } else if (field.name.includes('manha2_ativo')) {
                            togglePeriodo(dia, 'manha2');
                        } else if (field.name.includes('tarde_ativo')) {
                            togglePeriodo(dia, 'tarde');
                        } else if (field.name.includes('tarde2_ativo')) {
                            togglePeriodo(dia, 'tarde2');
                        }
                    } else {
                        field.value = valores[fieldName];
                    }
                }
            });
        }
    });
}

// Inicializar configurações ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    // Verificar estado inicial dos checkboxes de dias
    document.querySelectorAll('.dia-checkbox').forEach(checkbox => {
        const dia = checkbox.id.replace('dia_', '');
        toggleDiaConfig(dia);
        
        // Verificar estado inicial dos períodos
        ['manha', 'manha2', 'tarde', 'tarde2'].forEach(periodo => {
            togglePeriodo(dia, periodo);
        });
    });
});
</script>
@endpush