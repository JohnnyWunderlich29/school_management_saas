@extends('layouts.app')

@section('title', 'Grade de Aulas')

@section('content')
    <style>
        .grid-view {
            display: none;
        }

        .list-view {
            display: block;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 100px repeat(7, 1fr);
            gap: 1px;
            background-color: #e5e7eb;
            border: 1px solid #e5e7eb;
            overflow-x: auto;
        }

        .grid-header {
            background-color: #f3f4f6;
            padding: 12px 8px;
            font-weight: 600;
            text-align: center;
            border-right: 1px solid #e5e7eb;
            min-width: 120px;
        }

        .time-slot {
            background-color: #f9fafb;
            padding: 8px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            border-right: 1px solid #e5e7eb;
            min-width: 100px;
        }

        .grid-cell {
            background-color: white;
            min-height: 60px;
            padding: 4px;
            position: relative;
            border-right: 1px solid #e5e7eb;
            min-width: 120px;
        }

        .aula-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px;
            border-radius: 4px;
            font-size: 11px;
            margin-bottom: 2px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .aula-card:hover {
            transform: scale(1.02);
        }

        .aula-actions {
            display: flex;
            gap: 4px;
            margin-top: 4px;
        }

        .aula-actions a,
        .aula-actions button {
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 10px;
        }

        /* Tooltip Styles */
        .tooltip {
            position: absolute;
            background-color: #1f2937;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .tooltip.show {
            opacity: 1;
        }

        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #1f2937 transparent transparent transparent;
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }

        .toast.success {
            background-color: #10b981;
        }

        .toast.error {
            background-color: #ef4444;
        }

        .toast.info {
            background-color: #3b82f6;
        }

        /* Conflict Warning */
        .conflict-warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 8px 12px;
            border-radius: 4px;
            margin-top: 8px;
            font-size: 14px;
        }

        /* Responsive Improvements */
        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 80px repeat(7, minmax(100px, 1fr));
                font-size: 12px;
            }
            
            .grid-header, .time-slot {
                padding: 8px 4px;
                font-size: 11px;
            }
            
            .aula-card {
                font-size: 10px;
                padding: 4px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <x-breadcrumbs :items="[['title' => 'Acadêmico', 'url' => '#'], ['title' => 'Grade de Aulas', 'url' => '#']]" />

        <x-card>
        <!-- Main Content -->

            <!-- Page Header -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Grade de Aulas</h1>
                        <p class="mt-2 text-sm text-gray-600">Gerencie e visualize a grade de horários das turmas</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- Campo de busca rápida -->
                        <div class="relative">
                            <input type="text" id="quick-search" placeholder="Buscar aulas..." 
                                class="w-full sm:w-auto pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                data-tooltip="Busque por turma, professor, disciplina ou sala">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                        
                        @can('grade_aulas.criar')
                            <a href="{{ route('grade-aulas.create') }}"
                                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                                data-tooltip="Atalho: Ctrl+N">
                                <i class="fas fa-plus mr-2"></i>
                                <span class="hidden sm:inline">Nova Aula</span>
                                <span class="sm:hidden">Nova</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <x-collapsible-filter 
                title="Filtros de Grade de Aulas" 
                :action="route('grade-aulas.index')" 
                :clear-route="route('grade-aulas.index')"
                target="grade-list-wrapper"
            >
                <x-filter-field 
                    name="turma_id" 
                    label="Turma" 
                    type="select"
                    empty-option="Todas as turmas"
                    :options="$turmas->pluck('nome', 'id')"
                />
                
                <x-filter-field 
                    name="professor_id" 
                    label="Professor" 
                    type="select"
                    empty-option="Todos os professores"
                    :options="$professores->mapWithKeys(function($professor) {
                        return [$professor->id => $professor->nome . ' ' . $professor->sobrenome];
                    })"
                />
                
                <x-filter-field 
                    name="disciplina_id" 
                    label="Disciplina" 
                    type="select"
                    empty-option="Todas as disciplinas"
                    :options="$disciplinas->pluck('nome', 'id')"
                />
                
                <x-filter-field 
                    name="dia_semana" 
                    label="Dia da Semana" 
                    type="select"
                    empty-option="Todos os dias"
                    :options="[
                        'segunda' => 'Segunda-feira',
                        'terca' => 'Terça-feira',
                        'quarta' => 'Quarta-feira',
                        'quinta' => 'Quinta-feira',
                        'sexta' => 'Sexta-feira',
                        'sabado' => 'Sábado',
                        'domingo' => 'Domingo'
                    ]"
                />
                
                <x-filter-field 
                    name="tipo_aula" 
                    label="Tipo de Aula" 
                    type="select"
                    empty-option="Todos os tipos"
                    :options="[
                        'anual' => 'Aula Anual',
                        'periodo' => 'Aula de Período'
                    ]"
                />
                
                <x-filter-field 
                    name="tipo_periodo" 
                    label="Tipo de Período" 
                    type="select"
                    empty-option="Todos os tipos de período"
                    :options="[
                        'substituicao' => 'Substituição',
                        'reforco' => 'Reforço',
                        'recuperacao' => 'Recuperação',
                        'curso_intensivo' => 'Curso Intensivo',
                        'outro' => 'Outro'
                    ]"
                />
                
                <x-filter-field 
                    name="permite_substituicao" 
                    label="Permite Substituição" 
                    type="select"
                    empty-option="Todos"
                    :options="[
                        '1' => 'Sim',
                        '0' => 'Não'
                    ]"
                />
            </x-collapsible-filter>



            <!-- View Toggle Buttons -->
            <x-card class="mb-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-eye text-gray-500"></i>
                        <span class="text-sm font-medium text-gray-700">Modo de Visualização:</span>
                    </div>
                    <div class="flex rounded-md shadow-sm">
                        <button id="list-btn" onclick="toggleView('list')"
                            class="relative inline-flex items-center px-4 py-2 rounded-l-md border border-gray-300 bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                            data-tooltip="Visualização em lista (Ctrl+L)">
                            <i class="fas fa-list mr-2"></i>
                            <span class="hidden sm:inline">Lista</span>
                        </button>
                        <button id="grid-btn" onclick="toggleView('grid')"
                            class="relative inline-flex items-center px-4 py-2 -ml-px rounded-r-md border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                            data-tooltip="Visualização em grade (Ctrl+G)">
                            <i class="fas fa-th mr-2"></i>
                            <span class="hidden sm:inline">Grade</span>
                        </button>
                    </div>
                </div>
            </x-card>

            <!-- Content Views -->
            <style>
              .expand-panel {
                overflow: hidden;
                max-height: 0;
                opacity: 0;
                transform: translateY(-6px);
                transition: max-height 300ms ease, opacity 220ms ease, transform 220ms ease;
              }
              .expand-panel.expand-open {
                opacity: 1;
                transform: translateY(0);
              }
            </style>
            <div id="list-view">
                <!-- List View -->
                <x-card>
                    <x-slot name="title">
                        <div class="flex items-center justify-between">
                            <span>Lista de Aulas</span>
                            <div class="flex items-center space-x-2 text-sm text-gray-500">
                                <i class="fas fa-list"></i>
                                <span>Visualização em Lista</span>
                            </div>
                        </div>
                    </x-slot>

                    @if ($turmasPaginadas->count() > 0)
                        <div id="grade-list-wrapper" class="relative">
                            <x-loading-overlay message="Atualizando turmas..." />
                            <div data-ajax-content>
                                <div class="space-y-4">
                                    @foreach ($turmasPaginadas as $turma)
                                        <details class="group bg-white border border-gray-200 rounded-lg shadow-sm" data-turma-id="{{ $turma->id }}">
                                            <summary class="cursor-pointer px-4 py-3 flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                        <i class="fas fa-users-class text-indigo-600 text-sm"></i>
                                                    </div>
                                                    <div>
                                                        <span class="text-sm font-medium text-gray-900">Turma {{ $turma->nome }}</span>
                                                        <span class="ml-2 text-xs text-gray-500" id="aula-count-{{ $turma->id }}"></span>
                                                    </div>
                                                </div>
                                                <i class="fas fa-chevron-down text-gray-500 transition-transform duration-200 group-open:rotate-180"></i>
                                            </summary>
                                            <div class="px-4 pb-4 expand-panel">
                                                <div id="turma-schedule-{{ $turma->id }}" class="p-4 text-sm text-gray-500" data-loaded="0">
                                                    Clique para carregar a grade da turma...
                                                </div>
                                            </div>
                                        </details>
                                    @endforeach
                                </div>

                                <!-- Pagination -->
                                <div class="mt-4">
                                    {{ $turmasPaginadas->links('components.pagination') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i class="fas fa-users text-gray-400 text-6xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma turma encontrada</h3>
                            <p class="text-gray-500 mb-6">Não há turmas ativas com os filtros selecionados.</p>
                        </div>
                    @endif
                </x-card>
            </div>

            <!-- Weekly Grid View -->
            <div id="grid-view" class="hidden">
                <x-card>
                    <x-slot name="title">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-lg sm:text-xl font-semibold">Grade Semanal</span>
                                <button 
                                    onclick="openLegendModal()" 
                                    class="text-blue-500 hover:text-blue-700 transition-colors duration-200"
                                    title="Ver legenda dos indicadores"
                                >
                                    <i class="fas fa-question-circle text-lg"></i>
                                </button>
                            </div>
                            <div class="flex items-center space-x-2 text-sm text-gray-500">
                                <i class="fas fa-calendar-week"></i>
                                <span class="hidden sm:inline">Visualização em Grade</span>
                                <span class="sm:hidden">Grade</span>
                            </div>
                        </div>
                    </x-slot>

                    <!-- Mobile Warning -->
                    <div class="block lg:hidden mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <p class="text-sm text-yellow-800">
                                Para melhor visualização da grade semanal, recomendamos usar um dispositivo com tela maior ou rotacionar para modo paisagem.
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <div class="min-w-[800px]">
                            <!-- Days Header -->
                            @php $diaAtualIso = \Carbon\Carbon::now()->dayOfWeekIso; @endphp
                            <div class="grid grid-cols-8 gap-1 mb-4">
                                <div class="p-2 lg:p-3 text-center font-medium text-gray-500 text-xs lg:text-sm">Horário</div>
                                @foreach (['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'] as $dia)
                                    <div class="p-2 lg:p-3 text-center font-medium text-gray-900 rounded-lg {{ $loop->iteration === $diaAtualIso ? 'bg-amber-100 ring-2 ring-amber-300' : 'bg-gray-100' }}">
                                        <span class="hidden sm:inline">{{ $dia }}</span>
                                        <span class="sm:hidden">{{ substr($dia, 0, 3) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Time Slots Grid -->
                            @foreach ($tempoSlots as $slot)
                                <div class="grid grid-cols-8 gap-1 mb-2">
                                    <!-- Time Column -->
                                    <div class="p-2 lg:p-3 text-center text-xs lg:text-sm font-medium text-gray-600 bg-gray-50 rounded-lg">
                                        <div class="font-semibold">{{ \Carbon\Carbon::parse($slot->hora_inicio)->format('H:i') }}</div>
                                        <div class="text-xs text-gray-500 hidden sm:block">{{ \Carbon\Carbon::parse($slot->hora_fim)->format('H:i') }}</div>
                                    </div>

                                    <!-- Days Columns -->
                                    @foreach (['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'] as $dia)
                                        @php
                                            $aulasDoDia = $gradeAulas->filter(function ($aula) use ($slot, $dia) {
                                                return $aula->tempo_slot_id == $slot->id && $aula->dia_semana == $dia;
                                            });
                                        @endphp
                                        <div
                                            class="min-h-[60px] lg:min-h-[80px] p-1 lg:p-2 border border-gray-200 rounded-lg hover:bg-gray-50 {{ $loop->iteration === $diaAtualIso ? 'bg-amber-50 ring-1 ring-amber-300' : 'bg-white' }}">
                                            @foreach ($aulasDoDia as $aula)
                                                <div class="mb-1 lg:mb-2 p-1 lg:p-2 bg-indigo-100 rounded-md text-xs relative">
                                                    <!-- Indicadores de Tipo de Aula -->
                                                    <div class="absolute top-1 right-1 flex space-x-1">
                                                        @if($aula->tipo_aula === 'anual')
                                                            <span class="inline-block w-2 h-2 bg-green-500 rounded-full" title="Aula Anual"></span>
                                                        @elseif($aula->tipo_aula === 'periodo')
                                                            <span class="inline-block w-2 h-2 bg-orange-500 rounded-full" title="Aula de Período"></span>
                                                            @if($aula->tipo_periodo === 'substituicao')
                                                                <span class="inline-block w-2 h-2 bg-yellow-500 rounded-full" title="Substituição"></span>
                                                            @elseif($aula->tipo_periodo === 'reforco')
                                                                <span class="inline-block w-2 h-2 bg-blue-500 rounded-full" title="Reforço"></span>
                                                            @elseif($aula->tipo_periodo === 'recuperacao')
                                                                <span class="inline-block w-2 h-2 bg-red-500 rounded-full" title="Recuperação"></span>
                                                            @elseif($aula->tipo_periodo === 'curso_intensivo')
                                                                <span class="inline-block w-2 h-2 bg-purple-500 rounded-full" title="Curso Intensivo"></span>
                                                            @elseif($aula->tipo_periodo === 'outro')
                                                                <span class="inline-block w-2 h-2 bg-gray-500 rounded-full" title="Outro"></span>
                                                            @endif
                                                        @endif
                                                        @if($aula->permite_substituicao)
                                                            <span class="inline-block w-2 h-2 bg-indigo-500 rounded-full" title="Permite Substituição"></span>
                                                        @endif
                                                    </div>
                                                    
                                                    <div class="font-medium text-indigo-900 truncate">{{ $aula->turma->nome }}</div>
                                                    <div class="text-indigo-700 truncate hidden sm:block">{{ $aula->disciplina->nome }}</div>
                                                    <div class="text-indigo-600 truncate hidden lg:block">{{ $aula->professor->nome }}</div>
                                                    <div class="text-indigo-500 truncate hidden lg:block">{{ $aula->sala->nome }}</div>
                                                    <div class="mt-1 flex space-x-1 justify-center lg:justify-start">
                                                        @can('grade_aulas.visualizar')
                                                            <button onclick="openDetailsModal({{ $aula->id }})"
                                                                class="text-blue-600 hover:text-blue-800" title="Visualizar">
                                                                <i class="fas fa-eye text-xs"></i>
                                                            </button>
                                                        @endcan
                                                        @can('grade_aulas.editar')
                                                            <button type="button" onclick="openEditModal({{ $aula->id }})"
                                                                class="text-indigo-600 hover:text-indigo-800" title="Editar">
                                                                <i class="fas fa-edit text-xs"></i>
                                                            </button>
                                                        @endcan
                                                        @can('grade_aulas.excluir')
                                                            <button onclick="deleteAulaFromGrid({{ $aula->id }}, '{{ $aula->turma->nome }}', '{{ $aula->disciplina->nome }}')"
                                                                class="text-red-600 hover:text-red-800" title="Excluir">
                                                                <i class="fas fa-trash text-xs"></i>
                                                            </button>
                                                        @endcan
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Statistics Cards -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 stats-grid">
                <x-card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Total de Aulas</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $totalAulas }}</div>
                        </div>
                    </div>
                </x-card>

                <x-card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chalkboard-teacher text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Professores Ativos</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $professoresAtivos }}</div>
                        </div>
                    </div>
                </x-card>

                <x-card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Turmas Ativas</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $turmasAtivas }}</div>
                        </div>
                    </div>
                </x-card>

                <x-card>
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-door-open text-purple-600"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Salas em Uso</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $salasEmUso }}</div>
                        </div>
                    </div>
                </x-card>
            </div>

        </x-card>
    </div>

    <!-- Modal Nova Aula -->
    <div id="createAulaModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Header do Modal -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Nova Aula</h3>
                    <button onclick="closeCreateAulaModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Formulário -->
                <form id="createAulaForm" action="{{ route('grade-aulas.store') }}" method="POST" class="mt-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Turma -->
                        <div>
                            <x-select name="turma_id" label="Turma" required="true" :options="$turmas->pluck('nome', 'id')->toArray()"
                                placeholder="Selecione uma turma" />
                        </div>

                        <!-- Professor -->
                        <div>
                            <x-select name="funcionario_id" label="Professor" required="true" :options="$professores->pluck('nome', 'id')->toArray()"
                                placeholder="Selecione um professor" />
                        </div>

                        <!-- Disciplina -->
                        <div>
                            <x-select name="disciplina_id" label="Disciplina" required="true" :options="$disciplinas->pluck('nome', 'id')->toArray()"
                                placeholder="Selecione uma disciplina" />
                        </div>

                        <!-- Sala -->
                        <div>
                            <x-select name="sala_id" label="Sala" required="true" :options="$salas->pluck('nome', 'id')->toArray()"
                                placeholder="Selecione uma sala" />
                        </div>

                        <!-- Dia da Semana -->
                        <div>
                            <x-select name="dia_semana" label="Dia da Semana" required="true" :options="[
                                'segunda' => 'Segunda-feira',
                                'terca' => 'Terça-feira',
                                'quarta' => 'Quarta-feira',
                                'quinta' => 'Quinta-feira',
                                'sexta' => 'Sexta-feira',
                                'sabado' => 'Sábado',
                            ]"
                                placeholder="Selecione o dia" />
                        </div>

                        <!-- Tempo Slot -->
                        <div>
                            <x-select name="tempo_slot_id" label="Horário" required="true" :options="$tempoSlots
                                    ->mapWithKeys(function ($slot) {
                                        return [$slot->id => \Carbon\Carbon::parse($slot->hora_inicio)->format('H:i') . ' - ' . \Carbon\Carbon::parse($slot->hora_fim)->format('H:i')];
                                    })->toArray()"
                                placeholder="Selecione o horário" />
                        </div>
                    </div>

                    <!-- Observações -->
                    <div class="mt-6">
                        <x-textarea name="observacoes" label="Observações"
                            placeholder="Observações sobre a aula (opcional)" rows="3" />
                    </div>

                    <!-- Botões -->
                    <div class="flex items-center justify-end pt-6 border-t mt-6 space-x-3">
                        <x-button type="button" color="gray" onclick="closeCreateAulaModal()">
                            Cancelar
                        </x-button>

                        <x-button type="submit" color="indigo">
                            <i class="fas fa-save mr-2"></i>
                            Salvar Aula
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <!-- Modal de Detalhes da Aula -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-4 mx-auto p-0 w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 max-w-4xl">
            <div class="bg-white rounded-lg shadow-xl border border-gray-200">
                <!-- Cabeçalho do Modal -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-eye text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-semibold text-gray-900">Detalhes da Aula</h3>
                            <p class="text-sm text-gray-500">Informações completas da aula</p>
                        </div>
                    </div>
                    <button onclick="closeDetailsModal()" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-all duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Conteúdo do Modal -->
                <div id="detailsModalContent" class="px-6 py-4 max-h-96 overflow-y-auto">
                    <!-- Conteúdo será carregado via AJAX -->
                    <div class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                            <p class="mt-4 text-gray-600 font-medium">Carregando detalhes...</p>
                        </div>
                    </div>
                </div>

                <!-- Rodapé do Modal com Ações -->
                <div id="detailsModalActions" class="flex items-center justify-end px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg space-x-3">
                    <button onclick="closeDetailsModal()" 
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Fechar
                    </button>
                    
                    @can('grade_aulas.editar')
                    <button id="editAulaBtn" onclick="editAulaFromModal()" 
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        Editar
                    </button>
                    @endcan
                    
                    @can('grade_aulas.excluir')
                    <button id="deleteAulaBtn" onclick="deleteAulaFromModal()" 
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>
                        Excluir
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição da Aula -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-4 mx-auto p-0 w-11/12 md:w-3/4 lg:w-2/3 xl:w-1/2 max-w-4xl">
            <div class="bg-white rounded-lg shadow-xl border border-gray-200">
                <!-- Cabeçalho do Modal -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-edit text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-semibold text-gray-900">Editar Aula</h3>
                            <p class="text-sm text-gray-500">Modifique as informações da aula</p>
                        </div>
                    </div>
                    <button onclick="closeEditModal()" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-all duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Conteúdo do Modal -->
                <div id="editModalContent" class="px-6 py-4 max-h-96 overflow-y-auto">
                    <!-- Conteúdo será carregado via AJAX -->
                    <div class="flex items-center justify-center py-12">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                            <p class="mt-4 text-gray-600 font-medium">Carregando formulário...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal da Legenda dos Indicadores -->
    <div id="legendModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-4 mx-auto p-0 w-11/12 md:w-3/4 lg:w-1/2 max-w-2xl">
            <div class="bg-white rounded-lg shadow-xl border border-gray-200">
                <!-- Cabeçalho do Modal -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-info-circle text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-semibold text-gray-900">Legenda dos Indicadores</h3>
                            <p class="text-sm text-gray-500">Entenda os códigos de cores da grade</p>
                        </div>
                    </div>
                    <button onclick="closeLegendModal()" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-all duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <!-- Conteúdo do Modal -->
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div class="space-y-3">
                            <h4 class="font-semibold text-gray-700 text-base">Tipo de Aula:</h4>
                            <div class="flex items-center space-x-3">
                                <span class="inline-block w-4 h-4 bg-green-500 rounded-full"></span>
                                <span>Aula Anual</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="inline-block w-4 h-4 bg-orange-500 rounded-full"></span>
                                <span>Aula de Período</span>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <h4 class="font-semibold text-gray-700 text-base">Tipo de Período:</h4>
                            <div class="flex items-center space-x-3">
                                <span class="inline-block w-4 h-4 bg-yellow-500 rounded-full"></span>
                                <span>Substituição</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="inline-block w-4 h-4 bg-blue-500 rounded-full"></span>
                                <span>Reforço</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="inline-block w-4 h-4 bg-red-500 rounded-full"></span>
                                <span>Recuperação</span>
                            </div>
                        </div>
                        
                        <div class="space-y-3 md:col-span-2">
                            <h4 class="font-semibold text-gray-700 text-base">Outros:</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="flex items-center space-x-3">
                                    <span class="inline-block w-4 h-4 bg-purple-500 rounded-full"></span>
                                    <span>Curso Intensivo</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-block w-4 h-4 bg-gray-500 rounded-full"></span>
                                    <span>Outro</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-block w-4 h-4 bg-indigo-500 rounded-full"></span>
                                    <span>Permite Substituição</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rodapé do Modal -->
                <div class="flex items-center justify-end px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                    <button onclick="closeLegendModal()" 
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Permissões do usuário para ações em aulas
        const CAN_VIEW_AULA = {!! auth()->user()->temPermissao('grade_aulas.visualizar') ? 'true' : 'false' !!};
        const CAN_EDIT_AULA = {!! auth()->user()->temPermissao('grade_aulas.editar') ? 'true' : 'false' !!};
        const CAN_DELETE_AULA = {!! auth()->user()->temPermissao('grade_aulas.excluir') ? 'true' : 'false' !!};

        // Loading state management - Atualizado para mostrar loading apenas no conteúdo
        function showLoading() {
            // Remove loading anterior se existir
            hideLoading();
            
            // Cria overlay apenas para o conteúdo principal
            const contentArea = document.querySelector('#list-view, #grid-view') || document.querySelector('.max-w-full');
            if (contentArea) {
                const loadingOverlay = document.createElement('div');
                loadingOverlay.id = 'content-loading-overlay';
                loadingOverlay.className = 'absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center z-40';
                loadingOverlay.innerHTML = `
                    <div class="bg-white rounded-lg p-6 flex items-center space-x-3 shadow-lg border">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                        <span class="text-gray-700">Atualizando dados...</span>
                    </div>
                `;
                
                // Garante que o container pai tenha position relative
                if (getComputedStyle(contentArea).position === 'static') {
                    contentArea.style.position = 'relative';
                }
                
                contentArea.appendChild(loadingOverlay);
            }
        }

        function hideLoading() {
            const loadingOverlay = document.getElementById('content-loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.remove();
            }
        }



        // Toast notifications
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const bgClass = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            toast.className = 'fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transform transition-all duration-300 translate-x-full ' + bgClass;
            
            const iconClass = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            toast.innerHTML = '<div class="flex items-center space-x-2">' +
                '<i class="fas ' + iconClass + '"></i>' +
                '<span>' + message + '</span>' +
                '</div>';
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            
            // Auto remove
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Funções do Modal Nova Aula
        function openCreateAulaModal() {
            document.getElementById('createAulaModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Focus no primeiro campo
            setTimeout(() => {
                const firstInput = document.querySelector('#createAulaModal select, #createAulaModal input');
                if (firstInput) firstInput.focus();
            }, 100);
        }

        function closeCreateAulaModal() {
            document.getElementById('createAulaModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            // Limpar formulário
            document.getElementById('createAulaForm').reset();
            clearConflictWarnings();
        }

        // Verificação de conflitos em tempo real
        function checkConflicts() {
            const form = document.getElementById('createAulaForm');
            const funcionarioId = form.querySelector('[name="funcionario_id"]')?.value;
            const salaId = form.querySelector('[name="sala_id"]')?.value;
            const diaSemana = form.querySelector('[name="dia_semana"]')?.value;
            const tempoSlotId = form.querySelector('[name="tempo_slot_id"]')?.value;

            // Limpar avisos anteriores
            clearConflictWarnings();

            if (funcionarioId && salaId && diaSemana && tempoSlotId) {
                // Mostrar loading
                showConflictLoading();

                // Fazer requisição AJAX para verificar conflitos
                fetch('{{ route("grade-aulas.verificar.conflitos") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        funcionario_id: funcionarioId,
                        sala_id: salaId,
                        dia_semana: diaSemana,
                        tempo_slot_id: tempoSlotId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showConflictResults(data.conflitos, data.tem_conflitos);
                    } else {
                        showConflictError('Erro ao verificar conflitos. Tente novamente.');
                    }
                })
                .catch(error => {
                    console.error('Erro na verificação de conflitos:', error);
                    showConflictError('Erro de conexão. Verifique sua internet e tente novamente.');
                });
            }
        }

        function showConflictLoading() {
            const form = document.getElementById('createAulaForm');
            const warning = document.createElement('div');
            warning.id = 'conflict-warning';
            warning.className = 'mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md';
            warning.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Verificando disponibilidade...</h3>
                        <div class="mt-1 text-sm text-blue-700">
                            Aguarde enquanto verificamos conflitos de horário.
                        </div>
                    </div>
                </div>
            `;
            form.appendChild(warning);
        }

        function showConflictResults(conflitos, temConflitos) {
            const form = document.getElementById('createAulaForm');
            const warning = document.createElement('div');
            warning.id = 'conflict-warning';
            
            if (temConflitos) {
                warning.className = 'mt-4 p-3 bg-red-50 border border-red-200 rounded-md';
                let conflitosHtml = '';
                
                conflitos.forEach(conflito => {
                    const icon = conflito.severidade === 'error' ? 'fas fa-times-circle text-red-400' : 'fas fa-exclamation-triangle text-yellow-400';
                    const colorClass = conflito.severidade === 'error' ? 'text-red-700' : 'text-yellow-700';
                    
                    conflitosHtml += `
                        <div class="flex items-start mt-2 first:mt-0">
                            <i class="${icon} mt-0.5 mr-2"></i>
                            <span class="text-sm ${colorClass}">${conflito.mensagem}</span>
                        </div>
                    `;
                });

                warning.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Conflitos detectados</h3>
                            <div class="mt-1">
                                ${conflitosHtml}
                            </div>
                        </div>
                    </div>
                `;
            } else {
                warning.className = 'mt-4 p-3 bg-green-50 border border-green-200 rounded-md';
                warning.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Horário disponível</h3>
                            <div class="mt-1 text-sm text-green-700">
                                Nenhum conflito detectado para este horário.
                            </div>
                        </div>
                    </div>
                `;
            }
            
            form.appendChild(warning);
        }

        function showConflictError(message) {
            const form = document.getElementById('createAulaForm');
            const warning = document.createElement('div');
            warning.id = 'conflict-warning';
            warning.className = 'mt-4 p-3 bg-red-50 border border-red-200 rounded-md';
            warning.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Erro na verificação</h3>
                        <div class="mt-1 text-sm text-red-700">
                            ${message}
                        </div>
                    </div>
                </div>
            `;
            form.appendChild(warning);
        }

        function clearConflictWarnings() {
            const warning = document.getElementById('conflict-warning');
            if (warning) warning.remove();
        }

        // Função de debounce para evitar muitas requisições
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Versão com debounce da verificação de conflitos
        const debouncedCheckConflicts = debounce(checkConflicts, 500);

        // Funções globais que precisam estar disponíveis imediatamente
        function toggleView(view) {
            const listView = document.getElementById('list-view');
            const gridView = document.getElementById('grid-view');
            const listBtn = document.getElementById('list-btn');
            const gridBtn = document.getElementById('grid-btn');

            // Salvar preferência no localStorage
            localStorage.setItem('gradeAulasView', view);

            if (view === 'list') {
                listView.classList.remove('hidden');
                gridView.classList.add('hidden');
                listBtn.classList.add('bg-indigo-600', 'text-white');
                listBtn.classList.remove('bg-white', 'text-gray-700');
                gridBtn.classList.remove('bg-indigo-600', 'text-white');
                gridBtn.classList.add('bg-white', 'text-gray-700');
            } else {
                listView.classList.add('hidden');
                gridView.classList.remove('hidden');
                gridBtn.classList.add('bg-indigo-600', 'text-white');
                gridBtn.classList.remove('bg-white', 'text-gray-700');
                listBtn.classList.remove('bg-indigo-600', 'text-white');
                listBtn.classList.add('bg-white', 'text-gray-700');
            }

            showToast('Visualização alterada para ' + (view === 'list' ? 'lista' : 'grade'), 'info');
        }

        // Variável global para armazenar o ID da aula atual
        let currentAulaId = null;

        // Funções para o Modal de Detalhes
        function openDetailsModal(aulaId) {
            currentAulaId = aulaId;
            document.getElementById('detailsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadAulaDetails(aulaId);
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            currentAulaId = null;
        }



        // Fechar modal ao clicar fora dele
        // Consolidando todos os event listeners DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            // Modal de criação de aula
            const modal = document.getElementById('createAulaModal');
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeCreateAulaModal();
                }
            });

            // Atalhos de teclado e fechamento de modais com ESC
            document.addEventListener('keydown', function(e) {
                // Fechar modais com ESC
                if (e.key === 'Escape') {
                    if (!modal.classList.contains('hidden')) {
                        closeCreateAulaModal();
                    }
                    if (!document.getElementById('detailsModal').classList.contains('hidden')) {
                        closeDetailsModal();
                    }
                    if (!document.getElementById('editModal').classList.contains('hidden')) {
                        closeEditModal();
                    }
                }
                
                // Ctrl/Cmd + N para nova aula
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    @can('grade_aulas.criar')
                        window.location.href = "{{ route('grade-aulas.create') }}";
                    @endcan
                }
                
                // Ctrl/Cmd + L para alternar para lista
                if ((e.ctrlKey || e.metaKey) && e.key === 'l') {
                    e.preventDefault();
                    toggleView('list');
                }
                
                // Ctrl/Cmd + G para alternar para grade
                if ((e.ctrlKey || e.metaKey) && e.key === 'g') {
                    e.preventDefault();
                    toggleView('grid');
                }
            });

            // Adicionar verificação de conflitos aos campos do modal
            const conflictFields = ['funcionario_id', 'sala_id', 'dia_semana', 'tempo_slot_id'];
            conflictFields.forEach(fieldName => {
                const field = document.querySelector('#createAulaModal [name="' + fieldName + '"]');
                if (field) {
                    field.addEventListener('change', () => {
                        clearConflictWarnings();
                        debouncedCheckConflicts();
                    });
                }
            });

            // Restaurar preferência de visualização
            const savedView = localStorage.getItem('gradeAulasView');
            if (savedView && (savedView === 'list' || savedView === 'grid')) {
                toggleView(savedView);
            }



            // Fechar modal de detalhes ao clicar fora
            document.getElementById('detailsModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeDetailsModal();
                }
            });

            // Fechar modal de edição ao clicar fora
            document.getElementById('editModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeEditModal();
                }
            });

            // Inicializar busca rápida
            initQuickSearch();

            // Inicializar tooltips
            initializeTooltips();

            // Inicializar expansão de turmas com carregamento via AJAX (versão animada)
            if (typeof window.initTurmaExpansionAnimated === 'function') {
                window.initTurmaExpansionAnimated();
            }
        });





        // Inicializar tooltips
        function initializeTooltips() {
            const tooltipElements = document.querySelectorAll('[data-tooltip]');
            tooltipElements.forEach(element => {
                element.addEventListener('mouseenter', showTooltip);
                element.addEventListener('mouseleave', hideTooltip);
            });
        }

        function showTooltip(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg pointer-events-none';
            tooltip.textContent = e.target.getAttribute('data-tooltip');
            tooltip.id = 'tooltip';
            
            document.body.appendChild(tooltip);
            
            const rect = e.target.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
        }

        function hideTooltip() {
            const tooltip = document.getElementById('tooltip');
            if (tooltip) tooltip.remove();
        }

        // Removida função duplicada initTurmaExpansion para evitar recursão

        // Renderização da agenda semanal da turma (lista por dia)
        function renderTurmaSchedule(data) {
            try {
                const diasOrdem = ['segunda','terca','quarta','quinta','sexta','sabado','domingo'];
                const aulas = Array.isArray(data.aulas) ? data.aulas.slice() : [];

                // Agrupar por dia
                const porDia = diasOrdem.reduce((acc, dia) => { acc[dia] = []; return acc; }, {});
                aulas.forEach(a => {
                    const dia = (a.dia_semana || '').toLowerCase();
                    if (porDia[dia]) porDia[dia].push(a);
                });

                // Ordenar por horário dentro de cada dia
                diasOrdem.forEach(dia => {
                    porDia[dia].sort((a, b) => {
                        const ha = a.tempo_slot?.hora_inicio || a.tempoSlot?.hora_inicio || '';
                        const hb = b.tempo_slot?.hora_inicio || b.tempoSlot?.hora_inicio || '';
                        return ha.localeCompare(hb);
                    });
                });

                // Construir HTML
                let html = `
                    <div class="space-y-4">
                        <!--<div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900">Agenda semanal da turma ${data.turma?.nome || ''}</h4>
                            <span class="text-xs text-gray-500">{{-- ${aulas.length} --}}aulas</span>
                        </div>-->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-3">
                `;

                const todayIso = ((new Date()).getDay() === 0 ? 7 : (new Date()).getDay());
                diasOrdem.forEach((dia, idx) => {
                    const label = getDiaSemanaText(dia);
                    const isToday = (idx + 1) === todayIso;
                    html += `
                        <div class="border rounded-lg ${isToday ? 'ring-2 ring-amber-400 bg-amber-50' : ''}">
                            <div class="px-3 py-2 border-b text-sm font-medium ${isToday ? 'bg-amber-100 text-amber-900' : 'bg-gray-50 text-gray-700'}">${label}</div>
                            <div class="p-3 space-y-2">
                    `;

                    if (porDia[dia].length === 0) {
                        html += `<p class="text-xs text-gray-500">Sem aulas</p>`;
                    } else {
                        porDia[dia].forEach(a => {
                            const slot = a.tempo_slot || a.tempoSlot || {};
                            const inicio = slot.hora_inicio_formatada || slot.hora_inicio || '';
                            const fim = slot.hora_fim_formatada || slot.hora_fim || '';
                            const disciplina = a.disciplina?.nome || 'Disciplina';
                            const professor = a.professor?.nome || 'Professor';
                            const sala = a.sala?.nome || 'Sala';
                            const turmaNome = data.turma?.nome || 'Turma';
                            const turmaNomeEsc = String(turmaNome).replace(/'/g, "\\'");
                            const disciplinaEsc = String(disciplina).replace(/'/g, "\\'");
                            let actions = '';
                            if (CAN_VIEW_AULA) {
                                actions += `
                                    <button class="text-indigo-600 hover:text-indigo-800 text-xs" title="Ver" onclick="openDetailsModal(${a.id})">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                `;
                            }
                            if (CAN_EDIT_AULA) {
                                actions += `
                                    <button class="text-amber-600 hover:text-amber-800 text-xs" title="Editar" onclick="openEditModal(${a.id})">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                `;
                            }
                            if (CAN_DELETE_AULA) {
                                actions += `
                                    <button class="text-red-600 hover:text-red-800 text-xs" title="Excluir" onclick="deleteAulaFromGrid(${a.id}, '${turmaNomeEsc}', '${disciplinaEsc}')">
                                        <i class="fas fa-trash-alt"></i> Excluir
                                    </button>
                                `;
                            }

                            html += `
                                <div class="p-2 rounded border bg-white">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">${inicio} - ${fim}</span>
                                        <span class="text-[10px] text-gray-500">#${a.id}</span>
                                    </div>
                                    <div class="mt-1 text-sm font-medium text-gray-900">${disciplina}</div>
                                    <div class="mt-0.5 text-xs text-gray-600">
                                        <i class="fas fa-user-tie mr-1"></i>${professor} 
                                        <span class="mx-1">·</span>
                                        <i class="fas fa-door-open mr-1"></i>${sala}
                                    </div>
                                    ${actions ? `<div class="mt-2 flex items-center gap-2">${actions}</div>` : ''}
                                </div>
                            `;
                        });
                    }

                    html += `</div></div>`;
                });

                html += `</div></div>`;
                return html;
            } catch (e) {
                console.error('Erro ao renderizar agenda da turma:', e);
                return `<div class="text-center py-6 text-red-600">Erro ao montar a agenda.</div>`;
            }
        }

        // Confirmação de exclusão melhorada
        function confirmDelete(aulaId, turma, disciplina) {
            if (confirm('Tem certeza que deseja excluir a aula de ' + disciplina + ' da turma ' + turma + '?\n\nEsta ação não pode ser desfeita.')) {
                showLoading();
                return true;
            }
            return false;
        }

        // Busca rápida
        function initQuickSearch() {
            const searchInput = document.getElementById('quick-search');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const searchTerm = this.value.toLowerCase();
                        const rows = document.querySelectorAll('#list-view tbody tr');
                        
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            if (text.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }, 300);
                });
            }
        }



        function loadAulaDetails(aulaId) {
            fetch('/grade-aulas/' + aulaId, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const content = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-blue-900 mb-2">
                                    <i class="fas fa-users mr-2"></i>Informações da Turma
                                </h4>
                                <p><strong>Turma:</strong> ${data.turma.nome}</p>
                                <p><strong>Modalidade:</strong> ${data.turma.grupo?.modalidade_ensino?.nome || 'N/A'}</p>
                                <p><strong>Grupo:</strong> ${data.turma.grupo?.nome || 'N/A'}</p>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-green-900 mb-2">
                                    <i class="fas fa-book mr-2"></i>Disciplina
                                </h4>
                                <p><strong>Nome:</strong> ${data.disciplina.nome}</p>
                                <p><strong>Código:</strong> ${data.disciplina.codigo || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-purple-900 mb-2">
                                    <i class="fas fa-chalkboard-teacher mr-2"></i>Professor
                                </h4>
                                <p><strong>Nome:</strong> ${data.funcionario.nome}</p>
                                <p><strong>Cargo:</strong> ${data.funcionario.cargo}</p>
                            </div>
                            
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <h4 class="font-semibold text-orange-900 mb-2">
                                    <i class="fas fa-clock mr-2"></i>Horário e Local
                                </h4>
                                <p><strong>Dia:</strong> ${getDiaSemanaText(data.dia_semana)}</p>
                                <p><strong>Horário:</strong> ${data.tempo_slot.hora_inicio_formatada} - ${data.tempo_slot.hora_fim_formatada}</p>
                                <p><strong>Sala:</strong> ${data.sala.nome}</p>
                                <p><strong>Capacidade:</strong> ${data.sala.capacidade} alunos</p>
                            </div>
                        </div>
                    </div>
                    
                    ${data.observacoes ? `
                        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-gray-900 mb-2">
                                <i class="fas fa-sticky-note mr-2"></i>Observações
                            </h4>
                            <p class="text-gray-700">${data.observacoes}</p>
                        </div>
                    ` : ''}
                `;
                
                document.getElementById('detailsModalContent').innerHTML = content;
            })
            .catch(error => {
                console.error('Erro ao carregar detalhes:', error);
                document.getElementById('detailsModalContent').innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
                        <p class="text-red-600">Erro ao carregar os detalhes da aula.</p>
                    </div>
                `;
            });
        }

        // Funções para o Modal de Edição
        function openEditModal(aulaId) {
            currentAulaId = aulaId;
            document.getElementById('editModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            loadEditForm(aulaId);
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            currentAulaId = null;
        }

        // Funções para o Modal da Legenda
        function openLegendModal() {
            document.getElementById('legendModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLegendModal() {
            document.getElementById('legendModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function editAulaFromModal() {
            if (currentAulaId) {
                closeDetailsModal();
                openEditModal(currentAulaId);
            }
        }

        function deleteAulaFromModal() {
            if (currentAulaId) {
                // Buscar dados da aula para confirmação
                fetch('/grade-aulas/' + currentAulaId, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (confirmDelete(currentAulaId, data.turma.nome, data.disciplina.nome)) {
                        // Criar formulário para delete
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '/grade-aulas/' + currentAulaId;
                        
                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';
                        
                        const methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'DELETE';
                        
                        form.appendChild(csrfToken);
                        form.appendChild(methodField);
                        document.body.appendChild(form);
                        
                        closeDetailsModal();
                        form.submit();
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar dados da aula:', error);
                    showToast('Erro ao processar exclusão', 'error');
                });
            }
        }

        function loadEditForm(aulaId) {
            fetch('/grade-aulas/' + aulaId + '/edit-modal', {
                method: 'GET',
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                // O HTML retornado já é apenas o formulário
                document.getElementById('editModalContent').innerHTML = html;
                
                // Adicionar event listener para submissão do formulário
                const form = document.querySelector('#editModalContent form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        submitEditForm(this);
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao carregar formulário:', error);
                document.getElementById('editModalContent').innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
                        <p class="text-red-600">Erro ao carregar o formulário de edição.</p>
                    </div>
                `;
            });
        }

        function submitEditForm(form) {
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    closeEditModal();
                    showToast('Aula atualizada com sucesso!', 'success');
                    // Recarregar a página ou atualizar o conteúdo
                    location.reload();
                } else {
                    throw new Error('Erro na atualização');
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar aula:', error);
                showToast('Erro ao atualizar a aula', 'error');
            });
        }

        // Função auxiliar para converter dia da semana (suporta número e string)
        function getDiaSemanaText(dia) {
            const diasNum = {
                1: 'Segunda-feira',
                2: 'Terça-feira',
                3: 'Quarta-feira',
                4: 'Quinta-feira',
                5: 'Sexta-feira',
                6: 'Sábado',
                7: 'Domingo'
            };
            const diasStr = {
                'segunda': 'Segunda-feira',
                'terca': 'Terça-feira',
                'terça': 'Terça-feira',
                'quarta': 'Quarta-feira',
                'quinta': 'Quinta-feira',
                'sexta': 'Sexta-feira',
                'sabado': 'Sábado',
                'sábado': 'Sábado',
                'domingo': 'Domingo'
            };
            if (typeof dia === 'string') {
                return diasStr[dia.toLowerCase()] || 'N/A';
            }
            return diasNum[dia] || 'N/A';
        }

        // Função para deletar aula da grade
         function deleteAulaFromGrid(aulaId, turmaNome, disciplinaNome) {
             if (confirmDelete(aulaId, turmaNome, disciplinaNome)) {
                 // Criar formulário para delete
                 const form = document.createElement('form');
                 form.method = 'POST';
                 form.action = '/grade-aulas/' + aulaId;
                 
                 const csrfToken = document.createElement('input');
                 csrfToken.type = 'hidden';
                 csrfToken.name = '_token';
                 csrfToken.value = '{{ csrf_token() }}';
                 
                 const methodField = document.createElement('input');
                 methodField.type = 'hidden';
                 methodField.name = '_method';
                 methodField.value = 'DELETE';
                 
                 form.appendChild(csrfToken);
                 form.appendChild(methodField);
                 document.body.appendChild(form);
                 
                 form.submit();
             }
         }


    </script>
@endsection

<!-- Overrides: quick search, cache por turma e badges de período -->
<script>
(function() {
  window.turmaScheduleCache = window.turmaScheduleCache || {};

  window.initQuickSearch = function() {
    const searchInput = document.getElementById('quick-search');
    if (!searchInput) return;
    let t;
    searchInput.addEventListener('input', function() {
      clearTimeout(t);
      t = setTimeout(() => {
        const term = (searchInput.value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('#list-view details[data-turma-id]');
        cards.forEach(card => {
          const header = card.querySelector(':scope > summary');
          const text = (header?.textContent || '').toLowerCase();
          card.style.display = term === '' || text.includes(term) ? '' : 'none';
        });
      }, 200);
    });
  };

  window.initTurmaExpansionAnimated = function() {
    const turmaDetails = document.querySelectorAll('#list-view details[data-turma-id]');
    turmaDetails.forEach(detailsEl => {
      // evita múltiplas vinculações do mesmo listener
      if (detailsEl.dataset.expansionBound === '1') return;
      detailsEl.dataset.expansionBound = '1';
      detailsEl.addEventListener('toggle', function() {
        const panelEl = detailsEl.querySelector(':scope > div.expand-panel');
        const contentEl = panelEl;

        // Fechamento suave
        if (!detailsEl.open) {
          if (panelEl) {
            const current = panelEl.scrollHeight;
            panelEl.style.maxHeight = current + 'px';
            // anima de altura atual -> 0
            requestAnimationFrame(() => {
              panelEl.style.maxHeight = '0px';
            });
            // após transição de altura, remove classe aberta
            const onCloseEnd = function(e) {
              if (e.propertyName !== 'max-height') return;
              panelEl.classList.remove('expand-open');
              // mantém max-height em 0 via CSS base
              panelEl.style.maxHeight = '';
              panelEl.removeEventListener('transitionend', onCloseEnd);
            };
            panelEl.addEventListener('transitionend', onCloseEnd);
          }
          return;
        }
        const turmaId = detailsEl.getAttribute('data-turma-id');

        // animação de abertura suave do painel
        if (panelEl) {
          panelEl.classList.add('expand-open');
          panelEl.style.maxHeight = '0px';
          requestAnimationFrame(() => {
            panelEl.style.maxHeight = panelEl.scrollHeight + 'px';
          });
          // remove maxHeight após transição para não cortar conteúdo dinâmico
           const onEnd = function(e) {
             if (e.propertyName !== 'max-height') return;
             if (detailsEl.open) {
               panelEl.style.maxHeight = 'none';
             }
             panelEl.removeEventListener('transitionend', onEnd);
           };
           panelEl.addEventListener('transitionend', onEnd);
        }

        const cached = window.turmaScheduleCache[turmaId];
        if (cached) {
          if (contentEl) contentEl.innerHTML = cached.html;
          const countEl = document.getElementById('aula-count-' + turmaId);
          if (countEl) countEl.textContent = (cached.count || 0) + ' aulas';
          detailsEl.dataset.loaded = 'true';
          if (panelEl) {
            // ajustar altura após conteúdo cacheado
            panelEl.style.maxHeight = panelEl.scrollHeight + 'px';
            // garante remoção de maxHeight após completar animação
            const onEndCached = function(e) {
              if (e.propertyName !== 'max-height') return;
              if (detailsEl.open) {
                panelEl.style.maxHeight = 'none';
              }
              panelEl.removeEventListener('transitionend', onEndCached);
            };
            panelEl.addEventListener('transitionend', onEndCached);
          }
          return;
        }

        if (detailsEl.dataset.loaded) return;

        if (contentEl) {
          contentEl.innerHTML = '<div class="text-center py-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-3"></div>Carregando agenda da turma...</div>';
          if (panelEl) {
            panelEl.style.maxHeight = panelEl.scrollHeight + 'px';
          }
        }

        fetch('/grade-aulas/por-turma?turma_id=' + encodeURIComponent(turmaId), {
          headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest' }
        })
        .then(resp => resp.json())
        .then(data => {
          if (!data.success) throw new Error(data.message || 'Falha ao carregar');
          const aulasCount = Array.isArray(data.aulas) ? data.aulas.length : 0;
          const countEl = document.getElementById('aula-count-' + turmaId);
          if (countEl) countEl.textContent = aulasCount + ' aulas';

          const rendered = window.renderTurmaSchedule ? window.renderTurmaSchedule(data) : '';
          if (contentEl) contentEl.innerHTML = rendered;

          window.turmaScheduleCache[turmaId] = { html: rendered, count: aulasCount };
          detailsEl.dataset.loaded = 'true';
          if (panelEl) {
            // anima mudança de altura para conteúdo final
            panelEl.style.maxHeight = panelEl.scrollHeight + 'px';
            const onEndLoaded = function(e) {
              if (e.propertyName !== 'max-height') return;
              if (detailsEl.open) {
                panelEl.style.maxHeight = 'none';
              }
              panelEl.removeEventListener('transitionend', onEndLoaded);
            };
            panelEl.addEventListener('transitionend', onEndLoaded);
          }
        })
        .catch(err => {
          console.error('Erro ao carregar aulas da turma:', err);
          if (contentEl) {
            contentEl.innerHTML = '<div class="text-center py-6"><i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i><p class="text-red-600">Erro ao carregar a agenda da turma.</p></div>';
          }
          if (panelEl) {
            panelEl.style.maxHeight = panelEl.scrollHeight + 'px';
            const onEndErr = function(e) {
              if (e.propertyName !== 'max-height') return;
              if (detailsEl.open) {
                panelEl.style.maxHeight = 'none';
              }
              panelEl.removeEventListener('transitionend', onEndErr);
            };
            panelEl.addEventListener('transitionend', onEndErr);
          }
        });
      });
    });
  };
})();
</script>
<script>
(function() {
  // Fallback simples caso não exista um renderizador prévio
  function renderTurmaScheduleFallback(data) {
    try {
      const diasOrdem = ['segunda','terca','quarta','quinta','sexta','sabado','domingo'];
      const aulas = Array.isArray(data?.aulas) ? data.aulas.slice() : [];

      const porDia = diasOrdem.reduce((acc, dia) => { acc[dia] = []; return acc; }, {});
      aulas.forEach(a => {
        const dia = (a?.dia_semana || '').toLowerCase();
        if (porDia[dia]) porDia[dia].push(a);
      });
      diasOrdem.forEach(dia => {
        porDia[dia].sort((a, b) => {
          const ha = a?.tempo_slot?.hora_inicio || a?.tempoSlot?.hora_inicio || '';
          const hb = b?.tempo_slot?.hora_inicio || b?.tempoSlot?.hora_inicio || '';
          return ha.localeCompare(hb);
        });
      });

      let html = `
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-gray-900">Agenda semanal da turmaaa ${data?.turma?.nome || ''}</h4>
            <span class="text-xs text-gray-500">${aulas.length} aulas</span>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
      `;

      diasOrdem.forEach(dia => {
        const label = (typeof window.getDiaSemanaText === 'function') ? window.getDiaSemanaText(dia) : dia;
        html += `
          <div class="border rounded-lg">
            <div class="px-3 py-2 bg-gray-50 border-b text-sm font-medium text-gray-700">${label}</div>
            <div class="p-3 space-y-2">
        `;

        if (porDia[dia].length === 0) {
          html += `<p class="text-xs text-gray-500">Sem aulas</p>`;
        } else {
          porDia[dia].forEach(a => {
            const slot = a?.tempo_slot || a?.tempoSlot || {};
            const inicio = slot?.hora_inicio_formatada || slot?.hora_inicio || '';
            const fim = slot?.hora_fim_formatada || slot?.hora_fim || '';
            const disciplina = a?.disciplina?.nome || 'Disciplina';
            const professor = a?.professor?.nome || 'Professor';
            const sala = a?.sala?.nome || 'Sala';

            html += `
              <div class="p-2 rounded border bg-white">
                <div class="flex items-center justify-between">
                  <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">${inicio} - ${fim}</span>
                  <span class="text-[10px] text-gray-500">#${a?.id || ''}</span>
                </div>
                <div class="mt-1 text-sm font-medium text-gray-900">${disciplina}</div>
                <div class="mt-0.5 text-xs text-gray-600">
                  <i class="fas fa-user-tie mr-1"></i>${professor}
                  <span class="mx-1">·</span>
                  <i class="fas fa-door-open mr-1"></i>${sala}
                </div>
              </div>
            `;
          });
        }

        html += `</div></div>`;
      });

      html += `</div></div>`;
      return html;
    } catch (e) {
      console.error('Fallback renderTurmaSchedule erro:', e);
      return `<div class="text-center py-6 text-red-600">Erro ao montar a agenda.</div>`;
    }
  }

  function badgeConfig(periodo) {
    switch ((periodo || '').toLowerCase()) {
      case 'substituicao': return { label: 'Substituição', cls: 'bg-yellow-100 text-yellow-700 border-yellow-200' };
      case 'reforco': return { label: 'Reforço', cls: 'bg-blue-100 text-blue-700 border-blue-200' };
      case 'recuperacao': return { label: 'Recuperação', cls: 'bg-red-100 text-red-700 border-red-200' };
      case 'curso_intensivo': return { label: 'Intensivo', cls: 'bg-purple-100 text-purple-700 border-purple-200' };
      case 'outro': return { label: 'Outro', cls: 'bg-gray-100 text-gray-700 border-gray-200' };
      default: return null;
    }
  }

  // Hook pós-render para inserir badges na lista
  const originalRenderTurmaSchedule = window.renderTurmaSchedule;
  window.renderTurmaSchedule = function(data) {
    const html = (typeof originalRenderTurmaSchedule === 'function')
      ? originalRenderTurmaSchedule(data)
      : renderTurmaScheduleFallback(data);
    // cria um container temporário para manipular DOM
    const tmp = document.createElement('div');
    tmp.innerHTML = html;

    // Remover quaisquer <script> embutidos no HTML retornado (evita redeclaração de Ziggy e erros de appendChild)
    // Isso garante que conteúdo dinâmico de partials não re-injete scripts na página
    tmp.querySelectorAll('script').forEach(function(s){
      try { s.remove(); } catch(_) {}
    });

    if (Array.isArray(data?.aulas)) {
      data.aulas.forEach(a => {
        if (a?.tipo_aula === 'periodo' && a?.tipo_periodo) {
          const cfg = badgeConfig(a.tipo_periodo);
          if (!cfg) return;
          // localizar o card pela tag do id (#123)
          const idSpans = tmp.querySelectorAll('span');
          let targetCard = null;
          idSpans.forEach(s => {
            if (!targetCard && (s.textContent || '').trim() === '#' + String(a.id)) {
              targetCard = s.closest('.p-2');
            }
          });
          if (targetCard) {
            const badge = document.createElement('span');
            badge.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border ' + cfg.cls;
            badge.textContent = cfg.label;
            const titleEl = targetCard.querySelector('.mt-1.text-sm.font-medium.text-gray-900');
            if (titleEl) {
              const wrap = document.createElement('span');
              wrap.className = 'ml-2';
              wrap.appendChild(badge);
              titleEl.appendChild(wrap);
            } else {
              // fallback: adiciona no topo do card
              targetCard.insertAdjacentElement('afterbegin', badge);
            }
          }
        }
      });
    }

    return tmp.innerHTML;
  };
})();
</script>
