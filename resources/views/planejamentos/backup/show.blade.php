@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Planejamentos', 'url' => route('planejamentos.index')],
    ['title' => $planejamento->titulo ?: 'Planejamento #' . $planejamento->id, 'url' => '#']
]" />

    <!-- Header responsivo -->
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">
                {{ $planejamento->titulo ?: 'Planejamento #' . $planejamento->id }}
            </h1>
            <p class="mt-1 text-sm text-gray-600">{{ $planejamento->modalidade_formatada }}</p>
        </div>
        <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
            <x-button href="{{ route('planejamentos.index') }}" color="secondary" class="w-full sm:justify-center">
                <i class="fas fa-arrow-left mr-1"></i> 
                <span class="hidden md:inline">Voltar para Planejamentos</span>
                <span class="md:hidden">Voltar</span>
            </x-button>
            
            @permission('planejamentos.editar')
                @if($planejamento->user_id === Auth::id() || Auth::user()->isSuperAdmin())
                    <x-button href="{{ route('planejamentos.detalhado', $planejamento) }}" color="primary" class="w-full sm:justify-center">
                        <i class="fas fa-edit mr-1"></i> 
                        <span class="hidden md:inline">Editar Detalhes</span>
                        <span class="md:hidden">Editar</span>
                    </x-button>
                @endif
            @endpermission
        </div>
    </div>

    <!-- Desktop Layout -->
    <div class="hidden md:block lg:grid lg:grid-cols-3 lg:gap-6">
        <!-- Coluna Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informações Básicas -->
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Informações Básicas
                    </h3>
                </div>
                
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modalidade</label>
                        <div class="text-sm text-gray-900">{{ $planejamento->modalidade_formatada }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidade Escolar</label>
                        <div class="text-sm text-gray-900">{{ $planejamento->unidade_escolar }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                        <div class="text-sm text-gray-900">{{ $planejamento->turno_formatado }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Turma</label>
                        <div class="text-sm text-gray-900">
                            {{ $planejamento->turma ? $planejamento->turma->nome : 'N/A' }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Professor</label>
                        <div class="text-sm text-gray-900">{{ $planejamento->tipo_professor_formatado }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Turma</label>
                        <div class="text-sm text-gray-900">{{ $planejamento->turma ? $planejamento->turma->nome : 'N/A' }}</div>
                    </div>
                </div>
            </x-card>

            <!-- Período -->
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-calendar-alt text-green-600 mr-2"></i>
                        Período do Planejamento
                    </h3>
                </div>
                
                <div class="grid grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Dias</label>
                        <div class="text-sm text-gray-900">{{ $planejamento->numero_dias }} {{ $planejamento->numero_dias == 1 ? 'dia' : 'dias' }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Início</label>
                        <div class="text-sm text-gray-900">
                            {{ $planejamento->data_inicio ? $planejamento->data_inicio->format('d/m/Y') : 'N/A' }}
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Término</label>
                        <div class="text-sm text-gray-900">
                            {{ $planejamento->data_fim ? $planejamento->data_fim->format('d/m/Y') : 'N/A' }}
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Objetivos -->
            @if($planejamento->objetivo_geral || $planejamento->objetivos_especificos)
                <x-card>
                    <div class="border-b border-gray-200 pb-4 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-bullseye text-orange-600 mr-2"></i>
                            Objetivos
                        </h3>
                    </div>
                    
                    @if($planejamento->objetivo_geral)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Objetivo Geral</label>
                            <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg">
                                {{ $planejamento->objetivo_geral }}
                            </div>
                        </div>
                    @endif
                    
                    @if($planejamento->objetivos_especificos && count($planejamento->objetivos_especificos) > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Objetivos Específicos</label>
                            <ul class="space-y-2">
                                @foreach($planejamento->objetivos_especificos as $objetivo)
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5 flex-shrink-0"></i>
                                        <span class="text-sm text-gray-900">{{ $objetivo }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </x-card>
            @endif

            <!-- BNCC -->
            @if($planejamento->competencias_bncc || $planejamento->habilidades_bncc)
                <x-card>
                    <div class="border-b border-gray-200 pb-4 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-graduation-cap text-purple-600 mr-2"></i>
                            Base Nacional Comum Curricular (BNCC)
                        </h3>
                    </div>
                    
                    @if($planejamento->competencias_bncc && count($planejamento->competencias_bncc) > 0)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Competências</label>
                            <ul class="space-y-2">
                                @foreach($planejamento->competencias_bncc as $competencia)
                                    <li class="flex items-start">
                                        <i class="fas fa-star text-yellow-500 mr-2 mt-0.5 flex-shrink-0"></i>
                                        <span class="text-sm text-gray-900">{{ $competencia }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    @if($planejamento->habilidades_bncc && count($planejamento->habilidades_bncc) > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Habilidades</label>
                            <ul class="space-y-2">
                                @foreach($planejamento->habilidades_bncc as $habilidade)
                                    <li class="flex items-start">
                                        <i class="fas fa-cog text-blue-500 mr-2 mt-0.5 flex-shrink-0"></i>
                                        <span class="text-sm text-gray-900">{{ $habilidade }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </x-card>
            @endif

            <!-- Metodologia e Recursos -->
            @if($planejamento->metodologia || $planejamento->recursos_didaticos)
                <x-card>
                    <div class="border-b border-gray-200 pb-4 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-tools text-indigo-600 mr-2"></i>
                            Metodologia e Recursos
                        </h3>
                    </div>
                    
                    @if($planejamento->metodologia)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Metodologia</label>
                            <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg">
                                {{ $planejamento->metodologia }}
                            </div>
                        </div>
                    @endif
                    
                    @if($planejamento->recursos_didaticos)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Recursos Didáticos</label>
                            <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg">
                                {{ $planejamento->recursos_didaticos }}
                            </div>
                        </div>
                    @endif
                </x-card>
            @endif

            <!-- Avaliação -->
            @if($planejamento->avaliacao)
                <x-card>
                    <div class="border-b border-gray-200 pb-4 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-clipboard-check text-teal-600 mr-2"></i>
                            Avaliação
                        </h3>
                    </div>
                    
                    <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg">
                        {{ $planejamento->avaliacao }}
                    </div>
                </x-card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status -->
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-4">
                    <h3 class="text-base font-medium text-gray-900">Status</h3>
                </div>
                
                @php
                    $statusColors = [
                        'rascunho' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                        'finalizado' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'aprovado' => 'bg-green-100 text-green-800 border-green-200'
                    ];
                @endphp
                <div class="flex items-center justify-center p-4 border-2 rounded-lg {{ $statusColors[$planejamento->status] ?? 'bg-gray-100 text-gray-800 border-gray-200' }}">
                    <span class="font-medium">{{ $planejamento->status_formatado }}</span>
                </div>
            </x-card>

            <!-- Informações do Criador -->
            <x-card>
                <div class="border-b border-gray-200 pb-4 mb-4">
                    <h3 class="text-base font-medium text-gray-900">Criado por</h3>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center">
                        <i class="fas fa-user text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-900">{{ $planejamento->user->name }}</span>
                    </div>
                    
                    <div class="flex items-center">
                        <i class="fas fa-calendar text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-900">{{ $planejamento->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    
                    @if($planejamento->updated_at != $planejamento->created_at)
                        <div class="flex items-center">
                            <i class="fas fa-edit text-gray-400 mr-2"></i>
                            <span class="text-sm text-gray-900">Atualizado em {{ $planejamento->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Cronograma -->
            @if($planejamento->cronograma && count($planejamento->cronograma) > 0)
                <x-card>
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <h3 class="text-base font-medium text-gray-900">Cronograma</h3>
                    </div>
                    
                    <div class="space-y-2">
                        @foreach($planejamento->cronograma as $item)
                            <div class="flex items-start">
                                <i class="fas fa-clock text-gray-400 mr-2 mt-0.5 flex-shrink-0"></i>
                                <span class="text-sm text-gray-900">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>
    </div>

    <!-- Mobile Layout -->
    <div class="lg:hidden space-y-6">
        <!-- Status Card -->
        <x-card>
            <div class="flex items-center justify-between">
                <h3 class="text-base font-medium text-gray-900">Status do Planejamento</h3>
                @php
                    $statusColors = [
                        'rascunho' => 'bg-yellow-100 text-yellow-800',
                        'finalizado' => 'bg-blue-100 text-blue-800',
                        'aprovado' => 'bg-green-100 text-green-800'
                    ];
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$planejamento->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $planejamento->status_formatado }}
                </span>
            </div>
        </x-card>

        <!-- Informações Básicas -->
        <x-card>
            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                Informações Básicas
            </h3>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Modalidade:</span>
                        <div class="text-sm text-gray-900">{{ $planejamento->modalidade_formatada }}</div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700">Turno:</span>
                        <div class="text-sm text-gray-900">{{ $planejamento->turno_formatado }}</div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700">Turma:</span>
                        <div class="text-sm text-gray-900">
                            {{ $planejamento->turma ? $planejamento->turma->nome : 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-700">Turma:</span>
                        <div class="text-sm text-gray-900">{{ $planejamento->turma ? $planejamento->turma->nome : 'N/A' }}</div>
                    </div>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-700">Unidade Escolar:</span>
                    <div class="text-sm text-gray-900">{{ $planejamento->unidade_escolar }}</div>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-700">Tipo de Professor:</span>
                    <div class="text-sm text-gray-900">{{ $planejamento->tipo_professor_formatado }}</div>
                </div>
            </div>
        </x-card>

        <!-- Período -->
        <x-card>
            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                <i class="fas fa-calendar-alt text-green-600 mr-2"></i>
                Período
            </h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-700">Duração:</span>
                    <div class="text-sm text-gray-900">{{ $planejamento->numero_dias }} {{ $planejamento->numero_dias == 1 ? 'dia' : 'dias' }}</div>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-700">Início:</span>
                    <div class="text-sm text-gray-900">
                        {{ $planejamento->data_inicio ? $planejamento->data_inicio->format('d/m/Y') : 'N/A' }}
                    </div>
                </div>
            </div>
            
            @if($planejamento->data_fim)
                <div class="mt-4">
                    <span class="text-sm font-medium text-gray-700">Término:</span>
                    <div class="text-sm text-gray-900">{{ $planejamento->data_fim->format('d/m/Y') }}</div>
                </div>
            @endif
        </x-card>

        <!-- Objetivo Geral -->
        @if($planejamento->objetivo_geral)
            <x-card>
                <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-bullseye text-orange-600 mr-2"></i>
                    Objetivo Geral
                </h3>
                
                <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg">
                    {{ $planejamento->objetivo_geral }}
                </div>
            </x-card>
        @endif

        <!-- Objetivos Específicos -->
        @if($planejamento->objetivos_especificos && count($planejamento->objetivos_especificos) > 0)
            <x-card>
                <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-list-ul text-orange-600 mr-2"></i>
                    Objetivos Específicos
                </h3>
                
                <ul class="space-y-2">
                    @foreach($planejamento->objetivos_especificos as $objetivo)
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5 flex-shrink-0"></i>
                            <span class="text-sm text-gray-900">{{ $objetivo }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        <!-- Competências BNCC -->
        @if($planejamento->competencias_bncc && count($planejamento->competencias_bncc) > 0)
            <x-card>
                <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-star text-yellow-500 mr-2"></i>
                    Competências BNCC
                </h3>
                
                <ul class="space-y-2">
                    @foreach($planejamento->competencias_bncc as $competencia)
                        <li class="flex items-start">
                            <i class="fas fa-star text-yellow-500 mr-2 mt-0.5 flex-shrink-0"></i>
                            <span class="text-sm text-gray-900">{{ $competencia }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        <!-- Habilidades BNCC -->
        @if($planejamento->habilidades_bncc && count($planejamento->habilidades_bncc) > 0)
            <x-card>
                <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-cog text-blue-500 mr-2"></i>
                    Habilidades BNCC
                </h3>
                
                <ul class="space-y-2">
                    @foreach($planejamento->habilidades_bncc as $habilidade)
                        <li class="flex items-start">
                            <i class="fas fa-cog text-blue-500 mr-2 mt-0.5 flex-shrink-0"></i>
                            <span class="text-sm text-gray-900">{{ $habilidade }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        <!-- Metodologia -->
        @if($planejamento->metodologia)
            <x-card>
                <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-tools text-indigo-600 mr-2"></i>
                    Metodologia
                </h3>
                
                <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg">
                    {{ $planejamento->metodologia }}
                </div>
            </x-card>
        @endif

        <!-- Recursos Didáticos -->
        @if($planejamento->recursos_didaticos)
            <x-card>
                <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-box text-indigo-600 mr-2"></i>
                    Recursos Didáticos
                </h3>
                
                <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg">
                    {{ $planejamento->recursos_didaticos }}
                </div>
            </x-card>
        @endif

        <!-- Avaliação -->
        @if($planejamento->avaliacao)
            <x-card>
                <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-clipboard-check text-teal-600 mr-2"></i>
                    Avaliação
                </h3>
                
                <div class="text-sm text-gray-900 bg-gray-50 p-4 rounded-lg">
                    {{ $planejamento->avaliacao }}
                </div>
            </x-card>
        @endif

        <!-- Cronograma -->
        @if($planejamento->cronograma && count($planejamento->cronograma) > 0)
            <x-card>
                <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-clock text-gray-600 mr-2"></i>
                    Cronograma
                </h3>
                
                <div class="space-y-2">
                    @foreach($planejamento->cronograma as $item)
                        <div class="flex items-start">
                            <i class="fas fa-clock text-gray-400 mr-2 mt-0.5 flex-shrink-0"></i>
                            <span class="text-sm text-gray-900">{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        <!-- Informações do Criador -->
        <x-card>
            <h3 class="text-base font-medium text-gray-900 mb-4 flex items-center">
                <i class="fas fa-user text-gray-600 mr-2"></i>
                Informações
            </h3>
            
            <div class="space-y-3">
                <div>
                    <span class="text-sm font-medium text-gray-700">Criado por:</span>
                    <div class="text-sm text-gray-900">{{ $planejamento->user->name }}</div>
                </div>
                
                <div>
                    <span class="text-sm font-medium text-gray-700">Criado em:</span>
                    <div class="text-sm text-gray-900">{{ $planejamento->created_at->format('d/m/Y H:i') }}</div>
                </div>
                
                @if($planejamento->updated_at != $planejamento->created_at)
                    <div>
                        <span class="text-sm font-medium text-gray-700">Atualizado em:</span>
                        <div class="text-sm text-gray-900">{{ $planejamento->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                @endif
            </div>
        </x-card>
    </div>

@endsection