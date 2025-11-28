@extends('layouts.app')

@section('title', 'Registro Rápido de Presenças')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
@endpush

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Presenças', 'url' => route('presencas.index')],
    ['title' => 'Registro Rápido', 'url' => '#']
]" />

<x-card>
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Registro Rápido de Presenças</h1>
            <p class="mt-1 text-sm text-gray-600">Registre presenças de forma rápida e prática</p>
        </div>
        <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
            <x-button href="{{ route('presencas.index') }}" color="secondary" class="w-full sm:justify-center">
                <i class="fas fa-arrow-left mr-1"></i> 
                <span class="hidden md:inline">Voltar para Presenças</span>
                <span class="md:hidden">Voltar</span>
            </x-button>
        </div>
    </div>

</x-card>

<!-- Informativo sobre data fixa -->
<x-card class="mb-6">
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-info-circle text-blue-500"></i>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-semibold text-blue-800 mb-2">
                    Data de Registro: {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                </h3>
                <div class="text-sm text-blue-700 space-y-1">
                    <p>A data e hora são fixas e não podem ser alteradas. Os registros serão salvos automaticamente.</p>
                    <p class="font-medium"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">P</span>Presente | <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mr-2">F</span>Falta</p>
                </div>
            </div>
        </div>
    </div>
</x-card>

    <x-card class="mb-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('presencas.registro-rapido.store') }}" method="POST" id="registroRapidoForm">
            @csrf
            
    <!-- Filtros -->
    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-end mb-6">
        <div class="w-full lg:flex-1 lg:min-w-64">
            <label for="filtro_sala" class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Sala</label>
            <select id="filtro_sala" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todas as Salas</option>
                @foreach($salas as $sala)
                    <option value="{{ $sala->id }}" {{ request('sala_id') == $sala->id ? 'selected' : '' }}>{{ $sala->nome_completo }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <button type="button" id="marcarTodosPresentes" 
                    class="bg-green-500 hover:bg-green-600 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 w-full sm:w-auto">
                <i class="fas fa-check mr-2"></i>Marcar Todos Presentes
            </button>
            <button type="button" id="marcarTodosFaltosos" 
                    class="bg-red-500 hover:bg-red-600 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 w-full sm:w-auto">
                <i class="fas fa-times mr-2"></i>Marcar Todos Faltosos
            </button>
        </div>
    </div>
    </x-card>

    @if($alunos->count() > 0)
        <x-card>

        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-gray-200">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Lista de Alunos</h3>
                        <p class="text-sm text-gray-600">{{ $alunos->count() }} alunos encontrados</p>
                    </div>
                </div>
                <button type="button" id="toggleSaidaMaisCedo" 
                        class="bg-orange-500 hover:bg-orange-600 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 w-full sm:w-auto">
                    <i class="fas fa-clock mr-2"></i>Registrar Saída Mais Cedo
                </button>
            </div>
                
            <!-- Layout Desktop - Cards -->
            <div class="hidden md:block space-y-4">
                @foreach($alunos as $aluno)
                    <div class="aluno-item bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition-all duration-200" data-sala-id="{{ $aluno->sala_id }}">
                        
                        @if(in_array($aluno->id, $presencasHoje))
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white mr-4 shadow-sm">
                                        <span class="text-sm font-medium">
                                            {{ strtoupper(substr($aluno->nome, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $aluno->nome_completo }}
                                        </div>
                                        @if($aluno->sala)
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $aluno->sala->nome_completo }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        <i class="fas fa-check mr-1.5"></i>Registrado
                                    </span>
                                    <button type="button" class="saida-mais-cedo-btn bg-orange-500 hover:bg-orange-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                                            data-aluno-id="{{ $aluno->id }}" data-aluno-nome="{{ $aluno->nome_completo }}">
                                        <i class="fas fa-clock mr-1"></i>Saída
                                    </button>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="presencas[{{ $loop->index }}][aluno_id]" value="{{ $aluno->id }}">
                            <input type="hidden" name="presencas[{{ $loop->index }}][presente]" value="" class="presenca-hidden-input" data-index="{{ $loop->index }}">
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center text-white mr-4 shadow-sm">
                                        <span class="text-sm font-medium">
                                            {{ strtoupper(substr($aluno->nome, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $aluno->nome_completo }}
                                        </div>
                                        @if($aluno->sala)
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $aluno->sala->nome_completo }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        <i class="fas fa-clock mr-1.5"></i>Pendente
                                    </span>
                                    <div class="flex space-x-2">
                                        <button type="button" 
                                                class="presenca-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                                data-aluno-index="{{ $loop->index }}"
                                                data-presente="1"
                                                title="Marcar como presente">
                                            <i class="fas fa-check mr-1"></i>Presente
                                        </button>
                                        <button type="button" 
                                                class="presenca-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                                data-aluno-index="{{ $loop->index }}"
                                                data-presente="0"
                                                title="Marcar como faltoso">
                                            <i class="fas fa-times mr-1"></i>Falta
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="justificativa-container mt-3" style="display: none;">
                                <textarea name="presencas[{{ $loop->index }}][justificativa]" 
                                          placeholder="Justificativa da falta..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          rows="2"></textarea>
                            </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

            <!-- Layout Mobile - Cards -->
            <div class="md:hidden space-y-4">
                @foreach($alunos as $aluno)
                    <div class="aluno-item bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md transition-all duration-200" data-sala-id="{{ $aluno->sala_id }}">
                        
                        @if(in_array($aluno->id, $presencasHoje))
                            <!-- Aluno já registrado -->
                            <div class="flex items-start space-x-3">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                                    <span class="text-sm font-medium">
                                        {{ strtoupper(substr($aluno->nome, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-base font-medium text-gray-900 truncate">
                                                {{ $aluno->nome_completo }}
                                            </h4>
                                            @if($aluno->sala)
                                                <p class="text-sm text-gray-500 mt-1">
                                                    <i class="fas fa-door-open mr-1"></i>{{ $aluno->sala->nome_completo }}
                                                </p>
                                            @endif
                                            <div class="mt-3">
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                                    <i class="fas fa-check mr-1.5"></i>Registrado
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="button" class="saida-mais-cedo-btn w-full bg-orange-500 hover:bg-orange-600 text-white py-3 px-4 rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                                                data-aluno-id="{{ $aluno->id }}" data-aluno-nome="{{ $aluno->nome_completo }}">
                                            <i class="fas fa-clock mr-2"></i>Registrar Saída Mais Cedo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Aluno não registrado -->
                            <input type="hidden" name="presencas[{{ $loop->index }}][aluno_id]" value="{{ $aluno->id }}">
                            <input type="hidden" name="presencas[{{ $loop->index }}][presente]" value="" class="presenca-hidden-input" data-index="{{ $loop->index }}">
                            
                            <div class="flex items-start space-x-3">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                                    <span class="text-sm font-medium">
                                        {{ strtoupper(substr($aluno->nome, 0, 2)) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-base font-medium text-gray-900 truncate">
                                                {{ $aluno->nome_completo }}
                                            </h4>
                                            @if($aluno->sala)
                                                <p class="text-sm text-gray-500 mt-1">
                                                    <i class="fas fa-door-open mr-1"></i>{{ $aluno->sala->nome_completo }}
                                                </p>
                                            @endif
                                            <div class="mt-3">
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                    <i class="fas fa-clock mr-1.5"></i>Pendente
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Botões de Presença/Ausência Mobile -->
                                    <div class="mt-4 grid grid-cols-2 gap-3">
                                        <button type="button" 
                                                class="presenca-btn bg-green-500 hover:bg-green-600 text-white py-3 px-4 rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center justify-center"
                                                data-aluno-index="{{ $loop->index }}"
                                                data-presente="1"
                                                title="Marcar como presente">
                                            <i class="fas fa-check mr-2"></i>Presente
                                        </button>
                                        <button type="button" 
                                                class="presenca-btn bg-red-500 hover:bg-red-600 text-white py-3 px-4 rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 flex items-center justify-center"
                                                data-aluno-index="{{ $loop->index }}"
                                                data-presente="0"
                                                title="Marcar como faltoso">
                                            <i class="fas fa-times mr-2"></i>Ausente
                                        </button>
                                    </div>
                                    
                                    <div class="justificativa-container mt-4" style="display: none;">
                                        <textarea name="presencas[{{ $loop->index }}][justificativa]" 
                                                  placeholder="Justificativa da falta..."
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                  rows="3"></textarea>
                                    </div>
                                </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-center items-center mt-8">
                <x-button type="button" color="secondary" onclick="window.location.href='{{ route('presencas.index') }}'" class="w-full sm:w-auto">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span class="hidden sm:inline">Voltar para Lista de Presenças</span>
                    <span class="sm:hidden">Voltar</span>
                </x-button>
            </div>
        </form>
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-user-slash text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Nenhum aluno encontrado</h3>
                <p class="text-gray-600 max-w-md mx-auto">Não há alunos disponíveis para registro de presença ou você não tem acesso a nenhuma sala.</p>
            </div>
        </x-card>
    @endif
</div>

<!-- Modal para Saída Mais Cedo -->
<el-dialog>
    <dialog id="saidaMaisCedoModal" aria-labelledby="saida-dialog-title" class="fixed inset-0 size-auto max-h-none max-w-none overflow-y-auto bg-transparent backdrop:bg-transparent">
        <el-dialog-backdrop class="fixed inset-0 bg-gray-500/75 transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in"></el-dialog-backdrop>
        
        <div tabindex="0" class="flex min-h-full items-end justify-center p-4 text-center focus:outline-none sm:items-center sm:p-0">
            <el-dialog-panel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all data-closed:translate-y-4 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in sm:my-8 sm:w-full sm:max-w-lg data-closed:sm:translate-y-0 data-closed:sm:scale-95">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-orange-100 sm:mx-0 sm:size-10">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 text-orange-600">
                                <path d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 id="saida-dialog-title" class="text-mobile-title text-gray-900">Registrar Saída Mais Cedo</h3>
                            <div class="mt-4 space-y-4">
                                <form id="saidaMaisCedoForm">
                                    @csrf
                                    <input type="hidden" id="saidaAlunoId" name="aluno_id">
                                    <input type="hidden" id="saidaData" name="data" value="{{ $dataAtual }}">
                                    
                                    <div>
                                        <label class="block text-mobile-body text-gray-700 mb-2">Aluno</label>
                                        <p id="saidaAlunoNome" class="text-gray-900 font-medium bg-gray-50 p-2 rounded-md"></p>
                                    </div>
                                    
                                    <div>
                                        <label for="saidaHora" class="block text-mobile-body text-gray-700 mb-2">Hora da Saída</label>
                                        <input type="time" id="saidaHora" name="hora_saida" required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    </div>
                                    
                                    <div>
                                        <label for="saidaJustificativa" class="block text-mobile-body text-gray-700 mb-2">Justificativa</label>
                                        <textarea id="saidaJustificativa" name="justificativa" required rows="3"
                                                  placeholder="Motivo da saída mais cedo..."
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit" form="saidaMaisCedoForm" class="inline-flex w-full justify-center rounded-md bg-orange-600 px-3 py-2 text-mobile-button text-white shadow-xs hover:bg-orange-500 sm:ml-3 sm:w-auto">
                        <i class="fas fa-save mr-2"></i>Registrar Saída
                    </button>
                    <button type="button" id="cancelarSaida" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-mobile-button text-gray-900 shadow-xs inset-ring inset-ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Cancelar
                    </button>
                </div>
            </el-dialog-panel>
        </div>
    </dialog>
</el-dialog>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Função para mostrar/ocultar campo de justificativa
    function toggleJustificativa() {
        const radios = document.querySelectorAll('input[type="radio"][name*="[presente]"]');
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const container = this.closest('.aluno-item');
                const justificativaContainer = container.querySelector('.justificativa-container');
                
                if (this.value === '0' && this.checked) {
                    justificativaContainer.style.display = 'block';
                    justificativaContainer.querySelector('textarea').required = true;
                } else {
                    justificativaContainer.style.display = 'none';
                    justificativaContainer.querySelector('textarea').required = false;
                }
            });
        });
    }
    
    // Filtro por sala
    const filtroSala = document.getElementById('filtro_sala');
    filtroSala.addEventListener('change', function() {
        const salaId = this.value;
        const alunoItems = document.querySelectorAll('.aluno-item');
        
        alunoItems.forEach(item => {
            if (salaId === '' || item.dataset.salaId === salaId) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Botões "Marcar Todos Presentes" e "Marcar Todos Faltosos"
    const marcarTodosPresentes = document.getElementById('marcarTodosPresentes');
    const marcarTodosFaltosos = document.getElementById('marcarTodosFaltosos');

    // Garantir que os botões sejam sempre visíveis
    if (marcarTodosPresentes) {
        marcarTodosPresentes.style.opacity = '1';
        marcarTodosPresentes.style.visibility = 'visible';
        marcarTodosPresentes.addEventListener('click', function() {
            const alunosVisiveis = document.querySelectorAll('.aluno-item:not([style*="display: none"]) .presenca-btn[data-presente="1"]');
            alunosVisiveis.forEach(btn => btn.click());
        });
    }

    if (marcarTodosFaltosos) {
        marcarTodosFaltosos.style.opacity = '1';
        marcarTodosFaltosos.style.visibility = 'visible';
        marcarTodosFaltosos.addEventListener('click', function() {
            const alunosVisiveis = document.querySelectorAll('.aluno-item:not([style*="display: none"]) .presenca-btn[data-presente="0"]');
            alunosVisiveis.forEach(btn => btn.click());
        });
    }
    
    // Modal de saída mais cedo
    const saidaModal = document.getElementById('saidaMaisCedoModal');
    const saidaForm = document.getElementById('saidaMaisCedoForm');
    
    // Abrir modal de saída
    document.querySelectorAll('.saida-mais-cedo-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const alunoId = this.dataset.alunoId;
            const alunoNome = this.dataset.alunoNome;
            
            document.getElementById('saidaAlunoId').value = alunoId;
            document.getElementById('saidaAlunoNome').textContent = alunoNome;
            document.getElementById('saidaHora').value = new Date().toTimeString().slice(0, 5);
            
            saidaModal.showModal();
        });
    });
    
    // Fechar modal de saída
    document.getElementById('cancelarSaida').addEventListener('click', function() {
        saidaModal.close();
    });
    
    // Submeter formulário de saída
    saidaForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('{{ route("presencas.saida-mais-cedo") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Saída registrada com sucesso!');
                saidaModal.close();
                location.reload(); // Recarregar para atualizar a interface
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao registrar saída.');
        });
    });
    
    // Nota: Validação removida pois o salvamento agora é automático
    
    // Inicializar eventos
    toggleJustificativa();
    
    // Adicionar event listeners para os botões de presença
     document.querySelectorAll('.presenca-btn').forEach(button => {
         button.addEventListener('click', function() {
             const alunoIndex = this.dataset.alunoIndex;
             const presente = this.dataset.presente;
             const alunoIdInput = document.querySelector(`input[name="presencas[${alunoIndex}][aluno_id]"]`);
             
             if (!alunoIdInput) return;
             
             const alunoId = alunoIdInput.value;
             const dataAtual = '{{ $dataAtual }}';
             
             // Desabilitar botão temporariamente
             this.disabled = true;
             
             // Salvar automaticamente via AJAX
             const formData = new FormData();
             formData.append('aluno_id', alunoId);
             formData.append('data', dataAtual);
             formData.append('presente', presente);
             
             fetch('{{ route("presencas.store.individual") }}', {
                 method: 'POST',
                 body: formData,
                 headers: {
                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                 }
             })
             .then(response => response.json())
             .then(data => {
                 if (data.success) {
                     // Atualizar visual dos botões
                     const container = this.parentElement;
                     const buttons = container.querySelectorAll('.presenca-btn');
                     
                     // Resetar todos os botões para o estado padrão (cinza)
                     buttons.forEach(btn => {
                         if (btn.getAttribute('data-presente') === '1') {
                             // Botão de presença
                             btn.className = 'presenca-btn w-6 h-6 bg-gray-200 rounded flex items-center justify-center text-gray-600 text-xs hover:bg-green-400 hover:text-white transition-colors';
                         } else {
                             // Botão de ausência
                             btn.className = 'presenca-btn w-6 h-6 bg-gray-200 rounded flex items-center justify-center text-gray-600 text-xs hover:bg-red-400 hover:text-white transition-colors';
                         }
                         btn.disabled = false;
                     });
                     
                     // Destacar o botão selecionado
                     if (presente === '1') {
                         this.className = 'presenca-btn w-6 h-6 bg-green-400 rounded flex items-center justify-center text-white text-xs transition-colors';
                     } else {
                         this.className = 'presenca-btn w-6 h-6 bg-red-400 rounded flex items-center justify-center text-white text-xs transition-colors';
                     }
                     
                     // Mostrar/ocultar justificativa
                     const justificativaContainer = this.closest('.aluno-item').querySelector('.justificativa-container');
                     if (justificativaContainer) {
                         if (presente === '0') {
                             justificativaContainer.style.display = 'block';
                         } else {
                             justificativaContainer.style.display = 'none';
                         }
                     }
                     
                     // Mostrar mensagem de sucesso
                     const successMsg = document.createElement('div');
                     successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                     successMsg.textContent = 'Presença registrada automaticamente!';
                     document.body.appendChild(successMsg);
                     
                     setTimeout(() => {
                         successMsg.remove();
                     }, 3000);
                 } else {
                     alert('Erro: ' + data.message);
                     this.disabled = false;
                 }
             })
             .catch(error => {
                 console.error('Erro:', error);
                 alert('Erro ao registrar presença.');
                 this.disabled = false;
             });
         });
     });
});
</script>
@endsection