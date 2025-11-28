@extends('layouts.app')

@section('content')

    <!-- Modal de Registro Rápido -->
    <div id="registroRapidoModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-mobile-title text-gray-900">Registro Rápido de Presença</h3>
                <button id="fecharModalBtn" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="registroRapidoForm" class="space-y-4">
                @csrf
                <div>
                    <label for="aluno_id" class="block text-mobile-body text-gray-700 mb-1">Aluno</label>
                    <select id="aluno_id" name="aluno_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        <option value="">Selecione um aluno</option>
                        @foreach($alunos as $aluno)
                            <option value="{{ $aluno->id }}">{{ $aluno->nome }} {{ $aluno->sobrenome }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="tipo_registro" class="block text-mobile-body text-gray-700 mb-1">Tipo de Registro</label>
                    <select id="tipo_registro" name="tipo_registro" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                        <option value="entrada">Entrada</option>
                        <option value="saida">Saída</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-2 pt-4">
                    <button type="button" id="cancelarRegistroBtn" class="inline-flex items-center px-4 py-2 border border-gray-300 text-mobile-button rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-mobile-button rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Registrar
                    </button>
                </div>
            </form>
            
            <div id="registroResultado" class="mt-4 p-3 rounded-md hidden">
                <p id="registroMensagem"></p>
            </div>
        </div>
    </div>

    <x-card>
        <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
            <div>
                <h1 class="text-mobile-title text-gray-900">Base HTML - Componentes e Padrões de Design</h1>
                <p class="mt-1 text-sm text-gray-600">Biblioteca de componentes e padrões visuais do sistema</p>
            </div>
            <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
                <x-button href="#" color="success" class="w-full sm:justify-center">
                    <i class="fas fa-list-check mr-1"></i> 
                    <span class="hidden md:inline">Registro Rápido</span>
                    <span class="md:hidden">Registro</span>
                </x-button>
                <x-button href="#" color="purple" class="w-full sm:justify-center">
                    <i class="fas fa-calendar-check mr-1"></i> 
                    <span class="hidden md:inline">Lançar Presenças</span>
                    <span class="md:hidden">Lançar</span>
                </x-button>
            </div>
        </div>

        <x-collapsible-filter 
            title="Filtros de Presenças" 
            :action="'#'"
            :clear-route="'#'"
        >
            <x-filter-field 
                name="sala_id" 
                label="Sala" 
                type="select"
                empty-option="Todas as salas"
                :options="$todasSalas->pluck('nome_completo', 'id')"
            />
            
            <x-date-filter-with-arrows 
                name="data_inicio" 
                label="Data Início"
                :value="$dataInicio"
                data-fim-name="data_fim"
                :data-fim-value="$dataFim"
            />
        </x-collapsible-filter>

        <!-- Tabela responsiva com melhor UX mobile -->
        <div>
            <x-table class="hidden md:block" :headers="['Sala', 'Total Alunos', 'Presentes', 'Ausentes', 'Não Registrados', 'Período']" :actions="true">
                @forelse($salasComEstatisticas as $index => $sala)
                    <x-table-row :striped="true" :index="$index">
                        <x-table-cell>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $sala->nome_completo }}</div>
                                    <div class="text-sm text-gray-500">Código: {{ $sala->codigo }}</div>
                                </div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-users mr-1"></i> {{ $sala->total_alunos }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            @if($sala->presentes > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> {{ $sala->presentes }}
                                </span>
                            @else
                                <span class="text-gray-500 text-sm">0</span>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            @if($sala->ausentes > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> {{ $sala->ausentes }}
                                </span>
                            @else
                                <span class="text-gray-500 text-sm">0</span>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            @if($sala->nao_registrados > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $sala->nao_registrados }}
                                </span>
                            @else
                                <span class="text-gray-500 text-sm">0</span>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }}
                                @if($dataInicio !== $dataFim)
                                    - {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}
                                @endif
                            </div>
                        </x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex justify-end space-x-2">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900" title="Ver Alunos da Sala">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="text-green-600 hover:text-green-900" title="Lançar Presenças">
                                    <i class="fas fa-calendar-check"></i>
                                </a>
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhuma sala encontrada para o período selecionado.
                        </td>
                    </tr>
                @endforelse
            </x-table>
            
            <!-- Layout mobile otimizado com cards -->
            <div class="md:hidden gap-2">
                @forelse($salasComEstatisticas as $sala)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <!-- Header do card -->
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                                <i class="fas fa-door-open text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-base">{{ $sala->nome_completo }}</h3>
                                <p class="text-sm text-gray-500">Código: {{ $sala->codigo }}</p>
                            </div>
                        </div>
                        
                        <!-- Estatísticas em grid -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <div class="text-mobile-title text-gray-900">{{ $sala->total_alunos }}</div>
                                <div class="text-xs text-gray-600 mt-1">Total Alunos</div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-3 text-center">
                                <div class="text-mobile-title text-green-700">{{ $sala->presentes }}</div>
                                <div class="text-xs text-green-600 mt-1">Presentes</div>
                            </div>
                            <div class="bg-red-50 rounded-lg p-3 text-center">
                                <div class="text-mobile-title text-red-700">{{ $sala->ausentes }}</div>
                                <div class="text-xs text-red-600 mt-1">Ausentes</div>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-3 text-center">
                                <div class="text-mobile-title text-yellow-700">{{ $sala->nao_registrados }}</div>
                                <div class="text-xs text-yellow-600 mt-1">Não Registrados</div>
                            </div>
                        </div>
                        
                        <!-- Período -->
                        <div class="mb-4 p-2 bg-gray-50 rounded text-center">
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-calendar mr-1"></i>
                                {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }}
                                @if($dataInicio !== $dataFim)
                                    - {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}
                                @endif
                            </div>
                        </div>
                        
                        <!-- Botões de ação com touch targets otimizados -->
                        <div class="flex space-x-2">
                            <a href="#" 
                               class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                                <i class="fas fa-eye mr-2"></i>
                                Ver Alunos
                            </a>
                            <a href="#" 
                               class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                                <i class="fas fa-calendar-check mr-2"></i>
                                Lançar
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-inbox text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500">Nenhuma sala encontrada para o período selecionado.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Seção de Demonstração de Componentes -->
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Biblioteca de Componentes</h2>
            
            <!-- Breadcrumbs -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Breadcrumbs</h3>
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2">
                        <li class="inline-flex items-center">
                            <a href="#" class="text-gray-700 hover:text-indigo-600 inline-flex items-center transition duration-200">
                                <svg class="w-5 h-5 mr-2.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                                </svg>
                                Home
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="#" class="text-gray-700 hover:text-indigo-600 ml-1 md:ml-2 text-sm font-medium transition duration-200">
                                    Alunos
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-gray-500 ml-1 md:ml-2 text-sm font-medium" aria-current="page">Visualizar</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
            
            <!-- Botões -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Botões</h3>
                <div class="flex flex-wrap gap-3">
                    <x-button color="primary">Primário</x-button>
                    <x-button color="secondary">Secundário</x-button>
                    <x-button color="success">Sucesso</x-button>
                    <x-button color="danger">Perigo</x-button>
                    <x-button color="warning">Aviso</x-button>
                    <x-button color="purple">Roxo</x-button>
                    <!-- Botões de ação da tabela -->
                    <a href="#" class="text-indigo-600 hover:text-indigo-900" title="Visualizar">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="#" class="text-yellow-600 hover:text-yellow-900" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="text-red-600 hover:text-red-900" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <!-- Badges e Status -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Badges e Status</h3>
                <div class="space-y-4">
                    <!-- Status de Presença -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Status de Presença</h4>
                        <div class="flex flex-wrap gap-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Presente
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i> Ausente
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-minus-circle mr-1"></i> Não Registrado
                            </span>
                        </div>
                    </div>
                    
                    <!-- Status Geral -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Status Geral</h4>
                        <div class="flex flex-wrap gap-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Ativo
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i> Inativo
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-info-circle mr-1"></i> Informação
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Atenção
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-clock mr-1"></i> Pendente
                            </span>
                        </div>
                    </div>
                    
                    <!-- Tipos de Relatório -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Tipos de Relatório</h4>
                        <div class="flex flex-wrap gap-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Presenças
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                Escalas
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Performance
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                Financeiro
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Cards de Estatísticas -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Cards de Estatísticas</h3>
                
                <!-- Cards Simples -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Cards Simples</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                            <div class="text-mobile-title text-gray-900">25</div>
                            <div class="text-xs text-gray-600 mt-1">Total Alunos</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3 text-center">
                            <div class="text-mobile-title text-green-700">20</div>
                            <div class="text-xs text-green-600 mt-1">Presentes</div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-3 text-center">
                            <div class="text-mobile-title text-red-700">3</div>
                            <div class="text-xs text-red-600 mt-1">Ausentes</div>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-3 text-center">
                            <div class="text-mobile-title text-yellow-700">2</div>
                            <div class="text-xs text-yellow-600 mt-1">Pendentes</div>
                        </div>
                    </div>
                </div>
                
                <!-- Cards com Gradiente (Dashboard) -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Cards com Gradiente</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-5 bg-gradient-to-r from-blue-500 to-blue-600">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 rounded-md bg-blue-100 bg-opacity-30 p-3">
                                        <i class="fas fa-user-graduate text-white text-xl"></i>
                                    </div>
                                    <div class="ml-5">
                                        <h3 class="text-sm font-medium text-blue-100">Alunos</h3>
                                        <div class="mt-1 flex items-baseline">
                                            <p class="text-2xl font-semibold text-white">150</p>
                                            <p class="ml-2 text-sm font-medium text-blue-100">cadastrados</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-5 bg-gradient-to-r from-green-500 to-green-600">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 rounded-md bg-green-100 bg-opacity-30 p-3">
                                        <i class="fas fa-check-circle text-white text-xl"></i>
                                    </div>
                                    <div class="ml-5">
                                        <h3 class="text-sm font-medium text-green-100">Concluídos</h3>
                                        <div class="mt-1 flex items-baseline">
                                            <p class="text-2xl font-semibold text-white">89</p>
                                            <p class="ml-2 text-sm font-medium text-green-100">prontos</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-5 bg-gradient-to-r from-yellow-500 to-yellow-600">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 rounded-md bg-yellow-100 bg-opacity-30 p-3">
                                        <i class="fas fa-spinner text-white text-xl"></i>
                                    </div>
                                    <div class="ml-5">
                                        <h3 class="text-sm font-medium text-yellow-100">Processando</h3>
                                        <div class="mt-1 flex items-baseline">
                                            <p class="text-2xl font-semibold text-white">12</p>
                                            <p class="ml-2 text-sm font-medium text-yellow-100">em andamento</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <div class="p-5 bg-gradient-to-r from-red-500 to-red-600">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 rounded-md bg-red-100 bg-opacity-30 p-3">
                                        <i class="fas fa-clock text-white text-xl"></i>
                                    </div>
                                    <div class="ml-5">
                                        <h3 class="text-sm font-medium text-red-100">Pendentes</h3>
                                        <div class="mt-1 flex items-baseline">
                                            <p class="text-2xl font-semibold text-white">5</p>
                                            <p class="ml-2 text-sm font-medium text-red-100">aguardando</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Avatares e Ícones -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Avatares e Ícones</h3>
                <div class="space-y-4">
                    <!-- Avatares com ícones -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Avatares com Ícones</h4>
                        <div class="flex flex-wrap gap-4">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                                <i class="fas fa-user-graduate text-lg"></i>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-500">
                                <i class="fas fa-user-tie text-lg"></i>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-500">
                                <i class="fas fa-chalkboard-teacher text-lg"></i>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                                <i class="fas fa-door-open text-lg"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Indicadores de Status -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Indicadores de Status</h4>
                        <div class="flex items-center space-x-6">
                            <div class="flex items-center px-3 py-2 bg-green-100 rounded-lg">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-sm font-medium text-green-800">Sistema Online</span>
                            </div>
                            <div class="flex items-center px-3 py-2 bg-red-100 rounded-lg">
                                <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                                <span class="text-sm font-medium text-red-800">Sistema Offline</span>
                            </div>
                            <div class="flex items-center px-3 py-2 bg-yellow-100 rounded-lg">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                                <span class="text-sm font-medium text-yellow-800">Manutenção</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Listas e Cards de Dados -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Listas e Cards de Dados</h3>
                
                <!-- Lista de Professores -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Lista de Professores</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-tie text-blue-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">João Silva</p>
                                    <p class="text-sm text-gray-600">5 escalas • 23 registros</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Ativo
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-tie text-purple-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Maria Santos</p>
                                    <p class="text-sm text-gray-600">3 escalas • 18 registros</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Ativo
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de Alunos Recentes -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Alunos Recentes</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-graduate text-green-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Ana Costa</p>
                                    <p class="text-sm text-gray-600">15/01/2024</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Novo
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Paginação -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Paginação</h3>
                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between py-3">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando
                                <span class="font-medium">1</span>
                                a
                                <span class="font-medium">10</span>
                                de
                                <span class="font-medium">97</span>
                                resultados
                            </p>
                        </div>
                        
                        <div>
                            <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-blue-600 border border-blue-600">
                                    1
                                </span>
                                
                                <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                    2
                                </a>
                                
                                <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">
                                    3
                                </a>
                                
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300">
                                    ...
                                </span>
                                
                                <a href="#" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 5.293a1 1 0 011.414 0L12 8.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </span>
                        </div>
                    </div>
                </nav>
            </div>
            
            <!-- Tipografia -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Tipografia</h3>
                <div class="space-y-4">
                    <h1 class="text-3xl font-bold text-gray-900">Título Principal (H1)</h1>
                    <h2 class="text-2xl font-bold text-gray-900">Título Secundário (H2)</h2>
                    <h3 class="text-xl font-semibold text-gray-800">Título Terciário (H3)</h3>
                    <h4 class="text-lg font-medium text-gray-700">Título Quaternário (H4)</h4>
                    <p class="text-base text-gray-600">Texto parágrafo normal com informações importantes do sistema.</p>
                    <p class="text-sm text-gray-500">Texto pequeno para informações secundárias.</p>
                    <p class="text-xs text-gray-400">Texto muito pequeno para detalhes adicionais.</p>
                    <div class="text-mobile-title text-gray-900">Título Mobile (text-mobile-title)</div>
                    <div class="text-mobile-body text-gray-700">Corpo Mobile (text-mobile-body)</div>
                    <div class="text-mobile-button text-gray-900">Botão Mobile (text-mobile-button)</div>
                </div>
            </div>
            
            <!-- Estados Vazios -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Estados Vazios</h3>
                <div class="space-y-6">
                    <!-- Estado vazio padrão -->
                    <div class="text-center py-8 bg-white border border-gray-200 rounded-lg">
                        <i class="fas fa-user-graduate text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">Nenhum aluno encontrado</p>
                        <p class="text-sm text-gray-400 mt-1">Tente ajustar os filtros ou adicionar novos alunos</p>
                    </div>
                    
                    <!-- Estado vazio com sucesso -->
                    <div class="text-center py-8 bg-white border border-gray-200 rounded-lg">
                        <i class="fas fa-check-circle text-green-400 text-3xl mb-3"></i>
                        <p class="text-gray-500">Nenhum alerta de baixa frequência</p>
                        <p class="text-sm text-gray-400 mt-1">Todos os alunos estão com boa frequência</p>
                    </div>
                </div>
            </div>
        </div>
    </x-card>
@endsection