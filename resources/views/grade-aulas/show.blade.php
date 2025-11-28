@extends('layouts.app')

@section('title', 'Detalhes da Aula')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-4 sm:mb-0">
                        <h1 class="text-2xl font-bold text-gray-900">Detalhes da Aula</h1>
                        <p class="text-sm text-gray-600 mt-1">{{ $gradeAula->turma->nome }} - {{ $gradeAula->disciplina->nome }}</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        @can('grade_aulas.editar')
                            <button onclick="openEditModal()" 
                                    class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="fas fa-edit mr-2"></i>
                                Editar
                            </button>
                        @endcan
                        <a href="{{ route('grade-aulas.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Conteúdo Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informações Básicas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Informações Básicas
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Turma</label>
                                    <p class="text-gray-900 font-medium">{{ $gradeAula->turma->nome }}</p>
                                    <p class="text-sm text-gray-500">{{ $gradeAula->turma->grupo->modalidadeEnsino->nome ?? 'N/A' }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Disciplina</label>
                                    <p class="text-gray-900 font-medium">{{ $gradeAula->disciplina->nome }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Professor</label>
                                    <p class="text-gray-900 font-medium">{{ $gradeAula->funcionario->nome }}</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Sala</label>
                                    <p class="text-gray-900 font-medium">{{ $gradeAula->sala->nome }}</p>
                                    <p class="text-sm text-gray-500">Capacidade: {{ $gradeAula->sala->capacidade }} alunos</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $gradeAula->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        <i class="fas fa-{{ $gradeAula->ativo ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                        {{ $gradeAula->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informações de Horário -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-clock text-purple-600 mr-2"></i>
                            Horário e Período
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Dia da Semana</label>
                                    <p class="text-gray-900 font-medium">{{ $gradeAula->dia_semana_formatado }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Horário</label>
                                    <p class="text-gray-900 font-medium">{{ \Carbon\Carbon::parse($gradeAula->tempoSlot->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($gradeAula->tempoSlot->hora_fim)->format('H:i') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Período</label>
                                    <p class="text-gray-900 font-medium">{{ $gradeAula->periodo_formatado }}</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                @if($gradeAula->data_inicio || $gradeAula->data_fim)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Vigência</label>
                                        <p class="text-gray-900 font-medium">
                                            @if($gradeAula->data_inicio)
                                                De {{ $gradeAula->data_inicio->format('d/m/Y') }}
                                            @endif
                                            @if($gradeAula->data_fim)
                                                até {{ $gradeAula->data_fim->format('d/m/Y') }}
                                            @endif
                                        </p>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Criado em</label>
                                    <p class="text-gray-900 font-medium">{{ $gradeAula->created_at->format('d/m/Y H:i') }}</p>
                                </div>

                                @if($gradeAula->updated_at != $gradeAula->created_at)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-600 mb-1">Última atualização</label>
                                        <p class="text-gray-900 font-medium">{{ $gradeAula->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if($gradeAula->observacoes)
                    <!-- Observações -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                                Observações
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                                    <p class="text-blue-800">{{ $gradeAula->observacoes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Verificações de Conflito -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-shield-alt text-red-600 mr-2"></i>
                            Verificações
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <!-- Conflito de Professor -->
                        <div class="border rounded-lg p-4 {{ $gradeAula->temConflitoProfesor($gradeAula->funcionario_id, $gradeAula->dia_semana, $gradeAula->tempo_slot_id, $gradeAula->data_inicio, $gradeAula->data_fim, $gradeAula->id) ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50' }}">
                            <div class="flex items-start">
                                <i class="fas fa-user-tie {{ $gradeAula->temConflitoProfesor($gradeAula->funcionario_id, $gradeAula->dia_semana, $gradeAula->tempo_slot_id, $gradeAula->data_inicio, $gradeAula->data_fim, $gradeAula->id) ? 'text-red-600' : 'text-green-600' }} mt-1 mr-3"></i>
                                <div>
                                    <h3 class="font-medium {{ $gradeAula->temConflitoProfesor($gradeAula->funcionario_id, $gradeAula->dia_semana, $gradeAula->tempo_slot_id, $gradeAula->data_inicio, $gradeAula->data_fim, $gradeAula->id) ? 'text-red-900' : 'text-green-900' }} mb-1">
                                        Conflito de Professor
                                    </h3>
                                    @if($gradeAula->temConflitoProfesor($gradeAula->funcionario_id, $gradeAula->dia_semana, $gradeAula->tempo_slot_id, $gradeAula->data_inicio, $gradeAula->data_fim, $gradeAula->id))
                                        <p class="text-sm text-red-700">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Existe conflito de horário para este professor
                                        </p>
                                    @else
                                        <p class="text-sm text-green-700">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Nenhum conflito detectado
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Conflito de Sala -->
                        <div class="border rounded-lg p-4 {{ $gradeAula->temConflitoSala($gradeAula->sala_id, $gradeAula->dia_semana, $gradeAula->tempo_slot_id, $gradeAula->data_inicio, $gradeAula->data_fim, $gradeAula->id) ? 'border-red-200 bg-red-50' : 'border-green-200 bg-green-50' }}">
                            <div class="flex items-start">
                                <i class="fas fa-door-open {{ $gradeAula->temConflitoSala($gradeAula->sala_id, $gradeAula->dia_semana, $gradeAula->tempo_slot_id, $gradeAula->data_inicio, $gradeAula->data_fim, $gradeAula->id) ? 'text-red-600' : 'text-green-600' }} mt-1 mr-3"></i>
                                <div>
                                    <h3 class="font-medium {{ $gradeAula->temConflitoSala($gradeAula->sala_id, $gradeAula->dia_semana, $gradeAula->tempo_slot_id, $gradeAula->data_inicio, $gradeAula->data_fim, $gradeAula->id) ? 'text-red-900' : 'text-green-900' }} mb-1">
                                        Conflito de Sala
                                    </h3>
                                    @if($gradeAula->temConflitoSala($gradeAula->sala_id, $gradeAula->dia_semana, $gradeAula->tempo_slot_id, $gradeAula->data_inicio, $gradeAula->data_fim, $gradeAula->id))
                                        <p class="text-sm text-red-700">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Existe conflito de horário para esta sala
                                        </p>
                                    @else
                                        <p class="text-sm text-green-700">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Nenhum conflito detectado
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-cogs text-gray-600 mr-2"></i>
                            Ações
                        </h2>
                    </div>
                    <div class="p-6 space-y-3">
                        @can('grade_aulas.editar')
                            <button onclick="openEditModal()" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar Aula
                        </button>
                        @endcan
                        
                        @can('grade_aulas.excluir')
                            <form method="POST" action="{{ route('grade-aulas.destroy', $gradeAula) }}" class="w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors" 
                                        onclick="return confirm('Tem certeza que deseja excluir esta aula?')">
                                    <i class="fas fa-trash mr-2"></i>
                                    Excluir Aula
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal de Edição -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <!-- Cabeçalho do Modal -->
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-lg font-semibold text-gray-900">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Editar Aula na Grade
            </h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Formulário de Edição -->
        <form method="POST" action="{{ route('grade-aulas.update', $gradeAula) }}" class="p-4">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Primeira linha: Turma e Disciplina -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Turma -->
                    <div>
                        <label for="turma_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Turma <span class="text-red-500">*</span>
                        </label>
                        <select name="turma_id" id="turma_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione uma turma</option>
                            @foreach($turmas as $turma)
                                <option value="{{ $turma->id }}" {{ old('turma_id', $gradeAula->turma_id) == $turma->id ? 'selected' : '' }}>
                                    {{ $turma->nome }} - {{ $turma->grupo->modalidadeEnsino->nome ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('turma_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Disciplina -->
                    <div>
                        <label for="disciplina_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Disciplina <span class="text-red-500">*</span>
                        </label>
                        <select name="disciplina_id" id="disciplina_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione uma disciplina</option>
                            @foreach($disciplinas as $disciplina)
                                <option value="{{ $disciplina->id }}" {{ old('disciplina_id', $gradeAula->disciplina_id) == $disciplina->id ? 'selected' : '' }}>
                                    {{ $disciplina->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('disciplina_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Segunda linha: Professor e Sala -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Professor -->
                    <div>
                        <label for="funcionario_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Professor <span class="text-red-500">*</span>
                        </label>
                        <select name="funcionario_id" id="funcionario_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione um professor</option>
                            @foreach($professores as $professor)
                                <option value="{{ $professor->id }}" {{ old('funcionario_id', $gradeAula->funcionario_id) == $professor->id ? 'selected' : '' }}>
                                    {{ $professor->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('funcionario_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sala -->
                    <div>
                        <label for="sala_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Sala <span class="text-red-500">*</span>
                        </label>
                        <select name="sala_id" id="sala_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione uma sala</option>
                            @foreach($salas as $sala)
                                <option value="{{ $sala->id }}" {{ old('sala_id', $gradeAula->sala_id) == $sala->id ? 'selected' : '' }}>
                                    {{ $sala->nome }} (Cap: {{ $sala->capacidade }})
                                </option>
                            @endforeach
                        </select>
                        @error('sala_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Terceira linha: Dia, Horário e Status -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Dia da Semana -->
                    <div>
                        <label for="dia_semana" class="block text-sm font-medium text-gray-700 mb-2">
                            Dia da Semana <span class="text-red-500">*</span>
                        </label>
                        <select name="dia_semana" id="dia_semana" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione o dia</option>
                            <option value="segunda" {{ old('dia_semana', $gradeAula->dia_semana) == 'segunda' ? 'selected' : '' }}>Segunda-feira</option>
                            <option value="terca" {{ old('dia_semana', $gradeAula->dia_semana) == 'terca' ? 'selected' : '' }}>Terça-feira</option>
                            <option value="quarta" {{ old('dia_semana', $gradeAula->dia_semana) == 'quarta' ? 'selected' : '' }}>Quarta-feira</option>
                            <option value="quinta" {{ old('dia_semana', $gradeAula->dia_semana) == 'quinta' ? 'selected' : '' }}>Quinta-feira</option>
                            <option value="sexta" {{ old('dia_semana', $gradeAula->dia_semana) == 'sexta' ? 'selected' : '' }}>Sexta-feira</option>
                            <option value="sabado" {{ old('dia_semana', $gradeAula->dia_semana) == 'sabado' ? 'selected' : '' }}>Sábado</option>
                        </select>
                        @error('dia_semana')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tempo Slot -->
                    <div>
                        <label for="tempo_slot_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Horário <span class="text-red-500">*</span>
                        </label>
                        <select name="tempo_slot_id" id="tempo_slot_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione o horário</option>
                            @foreach($tempoSlots as $slot)
                                <option value="{{ $slot->id }}" {{ old('tempo_slot_id', $gradeAula->tempo_slot_id) == $slot->id ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($slot->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->hora_fim)->format('H:i') }}
                                </option>
                            @endforeach
                        </select>
                        @error('tempo_slot_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="ativo" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="ativo" id="ativo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="1" {{ old('ativo', $gradeAula->ativo) == '1' ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ old('ativo', $gradeAula->ativo) == '0' ? 'selected' : '' }}>Inativo</option>
                        </select>
                        @error('ativo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Quarta linha: Datas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Data de Início -->
                    <div>
                        <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">Data de Início</label>
                        <input type="date" name="data_inicio" id="data_inicio" 
                               value="{{ old('data_inicio', $gradeAula->data_inicio?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        @error('data_inicio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data de Fim -->
                    <div>
                        <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-2">Data de Fim</label>
                        <input type="date" name="data_fim" id="data_fim" 
                               value="{{ old('data_fim', $gradeAula->data_fim?->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        @error('data_fim')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Observações -->
                <div>
                    <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                    <textarea name="observacoes" id="observacoes" rows="3" 
                              placeholder="Observações sobre esta aula..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('observacoes', $gradeAula->observacoes) }}</textarea>
                    @error('observacoes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alerta de Conflitos -->
                <div id="conflitos-alert" class="hidden bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h6 class="text-sm font-medium text-yellow-800">Conflitos Detectados:</h6>
                            <ul id="lista-conflitos" class="mt-1 text-sm text-yellow-700"></ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões do Modal -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                <button type="button" onclick="closeEditModal()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors duration-200">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors duration-200">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Atualizar Aula
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fechar modal ao clicar fora dele
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditModal();
    }
});

// Verificação de conflitos
document.addEventListener('DOMContentLoaded', function() {
    const camposConflito = ['funcionario_id', 'sala_id', 'dia_semana', 'tempo_slot_id'];
    
    camposConflito.forEach(campo => {
        const element = document.getElementById(campo);
        if (element) {
            element.addEventListener('change', verificarConflitos);
        }
    });

    function verificarConflitos() {
        const funcionarioId = document.getElementById('funcionario_id').value;
        const salaId = document.getElementById('sala_id').value;
        const diaSemana = document.getElementById('dia_semana').value;
        const tempoSlotId = document.getElementById('tempo_slot_id').value;

        if (funcionarioId && salaId && diaSemana && tempoSlotId) {
            console.log('Verificando conflitos...', {
                funcionarioId, salaId, diaSemana, tempoSlotId
            });
        }
    }
});
</script>
@endsection