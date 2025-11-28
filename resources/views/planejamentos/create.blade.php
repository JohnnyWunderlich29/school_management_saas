@extends('layouts.app')

@section('title', 'Criar Planejamento de Aula')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-6">
        <x-breadcrumbs :items="[
            ['title' => 'Planejamentos', 'url' => route('planejamentos.index')],
            ['title' => 'Novo Planejamento', 'url' => '#']
        ]" />

        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Criar Planejamento de Aula</h1>
                    <p class="mt-2 text-sm text-gray-600">Siga as etapas para criar um novo planejamento de aula</p>
                </div>
            </div>
        </div>

        <!-- Redirecionamento para o Wizard -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
            </div>
            
            <h3 class="text-lg font-medium text-gray-900 mb-2">Assistente de Criação de Planejamento</h3>
            <p class="text-gray-600 mb-6">
                Use nosso assistente passo a passo para criar seu planejamento de aula de forma rápida e organizada.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('planejamentos.wizard') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                    Iniciar Assistente
                </a>
                
                <a href="{{ route('planejamentos.index') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>

        <!-- Informações sobre o processo -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="text-lg font-medium text-blue-900 mb-3">Como funciona o assistente:</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-medium">
                            1
                        </div>
                    </div>
                    <div class="ml-3">
                        <h5 class="text-sm font-medium text-blue-900">Configuração Básica</h5>
                        <p class="text-sm text-blue-700">Selecione modalidade e nível de ensino</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-medium">
                            2
                        </div>
                    </div>
                    <div class="ml-3">
                        <h5 class="text-sm font-medium text-blue-900">Unidade e Turno</h5>
                        <p class="text-sm text-blue-700">Defina escola e período de aulas</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-medium">
                            3
                        </div>
                    </div>
                    <div class="ml-3">
                        <h5 class="text-sm font-medium text-blue-900">Turma e Disciplina</h5>
                        <p class="text-sm text-blue-700">Escolha turma e matéria específica</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-medium">
                            4
                        </div>
                    </div>
                    <div class="ml-3">
                        <h5 class="text-sm font-medium text-blue-900">Período e Duração</h5>
                        <p class="text-sm text-blue-700">Configure datas e duração do planejamento</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-medium">
                            5
                        </div>
                    </div>
                    <div class="ml-3">
                        <h5 class="text-sm font-medium text-blue-900">Conteúdo Pedagógico</h5>
                        <p class="text-sm text-blue-700">Adicione objetivos, metodologia e recursos</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white text-sm font-medium">
                            6
                        </div>
                    </div>
                    <div class="ml-3">
                        <h5 class="text-sm font-medium text-blue-900">Revisão e Finalização</h5>
                        <p class="text-sm text-blue-700">Revise e salve seu planejamento</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection