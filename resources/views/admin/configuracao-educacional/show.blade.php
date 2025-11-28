@extends('layouts.app')

@section('title', 'Configurações Educacionais - ' . $escola->nome)

@section('content')
    <div class="min-h-screen bg-gray-50 py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Cabeçalho -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                            <div>
                                <h1 class="text-xl font-semibold text-gray-900">Configurações Educacionais</h1>
                                <p class="text-sm text-gray-500 mt-1">{{ $escola->nome }}</p>
                            </div>
                        </div>
                        <div class="flex items-center flex-wrap gap-3 sm:space-x-3">
                            @if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte'))
                                <a href="{{ route('corporativo.configuracao-educacional.index') }}"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Voltar ao Painel Corporativo
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Voltar ao Dashboard
                                </a>
                            @endif
                            <!-- Botão Templates BNCC destacado no cabeçalho -->
                            <button type="button" onclick="openTemplatesBnccModal()"
                                class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                                title="Usar templates pré-configurados da BNCC">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Templates BNCC
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas -->
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ session('success') }}
                            </p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button type="button"
                                    class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-50 focus:ring-green-600"
                                    onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                                    <span class="sr-only">Dismiss</span>
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                {{ session('error') }}
                            </p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button type="button"
                                    class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600"
                                    onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                                    <span class="sr-only">Dismiss</span>
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tabs de navegação -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-4 sm:space-x-8 px-2 sm:px-6 overflow-x-auto" aria-label="Tabs">
                        <button onclick="showTab('modalidades')" id="modalidades-tab"
                            class="tab-button border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center flex-shrink-0 active-tab">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z">
                                </path>
                            </svg>
                            Modalidades de Ensino
                            <span
                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $escola->modalidadeConfigs->count() }}
                            </span>
                        </button>
                        <button onclick="showTab('niveis')" id="niveis-tab"
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center flex-shrink-0">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                            Níveis de Ensino
                            <span
                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $escola->nivelConfigs->count() }}
                            </span>
                        </button>
                        <button onclick="showTab('disciplinas')" id="disciplinas-tab"
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center flex-shrink-0">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                            Disciplinas e Cargas Horárias
                            <span
                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ \App\Models\Disciplina::where('ativo', true)->count() }}
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Conteúdo das tabs -->
                <div class="p-6">
                    <!-- Tab Modalidades -->
                    <div id="modalidades" class="tab-content">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Formulário de Adicionar Modalidade -->
                            <div class="lg:col-span-1">
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900">Adicionar Modalidade</h3>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <form
                                            action="{{ route('admin.configuracao-educacional.store-modalidade', $escola) }}"
                                            method="POST" class="space-y-6">
                                            @csrf

                                            <!-- Modalidade de Ensino -->
                                            <div>
                                                <label for="modalidade_ensino_id"
                                                    class="block text-sm font-medium text-gray-700 mb-2">
                                                    Modalidade de Ensino
                                                </label>
                                                <select name="modalidade_ensino_id" id="modalidade_ensino_id"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    required>
                                                    <option value="">Selecione uma modalidade</option>

                                                    @if ($modalidadesPadrao->count() > 0)
                                                        <optgroup label="━━━━━ Modalidades Comuns (BNCC) ━━━━━">
                                                            @foreach ($modalidadesPadrao as $modalidade)
                                                                <option value="{{ $modalidade->id }}">
                                                                    {{ $modalidade->nome }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endif

                                                    @if ($modalidadesPersonalizadas->count() > 0)
                                                        <optgroup label="━━━━━ Modalidades Personalizadas ━━━━━">
                                                            @foreach ($modalidadesPersonalizadas as $modalidade)
                                                                <option value="{{ $modalidade->id }}">
                                                                    {{ $modalidade->nome }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endif
                                                </select>
                                            </div>

                                            <!-- Status Ativo -->
                                            <div class="flex items-center">
                                                <input type="hidden" name="ativo" value="0">
                                                <input type="checkbox" id="ativo_modalidade" name="ativo"
                                                    value="1" checked
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <label for="ativo_modalidade" class="ml-2 block text-sm text-gray-900">
                                                    Ativo
                                                </label>
                                            </div>

                                            <!-- Capacidades -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="capacidade_minima_turma"
                                                        class="block text-sm font-medium text-gray-700 mb-2">
                                                        Capacidade Mínima
                                                    </label>
                                                    <input type="number" name="capacidade_minima_turma"
                                                        id="capacidade_minima_turma" min="1"
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label for="capacidade_maxima_turma"
                                                        class="block text-sm font-medium text-gray-700 mb-2">
                                                        Capacidade Máxima
                                                    </label>
                                                    <input type="number" name="capacidade_maxima_turma"
                                                        id="capacidade_maxima_turma" min="1"
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                </div>
                                            </div>

                                            <!-- Turnos Permitidos -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-3">Turnos
                                                    Permitidos</label>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div class="flex items-center">
                                                        <input type="hidden" name="turno_matutino" value="0">
                                                        <input type="checkbox" id="turno_matutino" name="turno_matutino"
                                                            value="1"
                                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                        <label for="turno_matutino"
                                                            class="ml-2 block text-sm text-gray-900">
                                                            Matutino
                                                        </label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input type="hidden" name="turno_vespertino" value="0">
                                                        <input type="checkbox" id="turno_vespertino"
                                                            name="turno_vespertino" value="1"
                                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                        <label for="turno_vespertino"
                                                            class="ml-2 block text-sm text-gray-900">
                                                            Vespertino
                                                        </label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input type="hidden" name="turno_noturno" value="0">
                                                        <input type="checkbox" id="turno_noturno" name="turno_noturno"
                                                            value="1"
                                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                        <label for="turno_noturno"
                                                            class="ml-2 block text-sm text-gray-900">
                                                            Noturno
                                                        </label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input type="hidden" name="turno_integral" value="0">
                                                        <input type="checkbox" id="turno_integral" name="turno_integral"
                                                            value="1"
                                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                        <label for="turno_integral"
                                                            class="ml-2 block text-sm text-gray-900">
                                                            Integral
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Observações -->
                                            <div>
                                                <label for="observacoes_modalidade"
                                                    class="block text-sm font-medium text-gray-700 mb-2">
                                                    Observações
                                                </label>
                                                <textarea name="observacoes" id="observacoes_modalidade" rows="3" maxlength="1000"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                    placeholder="Observações adicionais..."></textarea>
                                            </div>

                                            <!-- Botão Salvar -->
                                            <button type="submit"
                                                class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12">
                                                    </path>
                                                </svg>
                                                Salvar Configuração
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de Modalidades Configuradas -->
                            <div class="lg:col-span-2">
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                </path>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900">Modalidades Configuradas</h3>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        @if ($escola->modalidadeConfigs->count() > 0)
                                            <div class="hidden sm:block overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Modalidade
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Status
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Capacidade
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Turnos
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Ações
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach ($escola->modalidadeConfigs as $config)
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <div>
                                                                        <div class="text-sm font-medium text-gray-900">
                                                                            {{ $config->modalidadeEnsino->nome }}
                                                                        </div>
                                                                        @if ($config->observacoes)
                                                                            <div class="text-sm text-gray-500">
                                                                                {{ Str::limit($config->observacoes, 50) }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    @if ($config->ativo)
                                                                        <span
                                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                            Ativo
                                                                        </span>
                                                                    @else
                                                                        <span
                                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                            Inativo
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    @if ($config->capacidade_minima_turma || $config->capacidade_maxima_turma)
                                                                        {{ $config->capacidade_minima_turma ?? 'N/A' }} -
                                                                        {{ $config->capacidade_maxima_turma ?? 'N/A' }}
                                                                    @else
                                                                        <span class="text-gray-500">Não definida</span>
                                                                    @endif
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    @php
                                                                        $turnos = [];
                                                                        if ($config->permite_turno_matutino) {
                                                                            $turnos[] = 'M';
                                                                        }
                                                                        if ($config->permite_turno_vespertino) {
                                                                            $turnos[] = 'V';
                                                                        }
                                                                        if ($config->permite_turno_noturno) {
                                                                            $turnos[] = 'N';
                                                                        }
                                                                        if ($config->permite_turno_integral) {
                                                                            $turnos[] = 'I';
                                                                        }
                                                                    @endphp
                                                                    @if (count($turnos) > 0)
                                                                        {{ implode(', ', $turnos) }}
                                                                    @else
                                                                        <span class="text-gray-500">Nenhum</span>
                                                                    @endif
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                                    <form
                                                                        action="{{ route('admin.configuracao-educacional.destroy-modalidade', [$escola, $config]) }}"
                                                                        method="POST" class="inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            onclick="return confirm('Tem certeza que deseja remover esta configuração?')"
                                                                            class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                                            title="Remover">
                                                                            <svg class="w-4 h-4" fill="none"
                                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                                </path>
                                                                            </svg>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="sm:hidden space-y-4">
                                                @foreach ($escola->modalidadeConfigs as $config)
                                                    <div class="border rounded-lg p-4 shadow-sm bg-white">
                                                        <div class="flex items-start justify-between">
                                                            <div>
                                                                <div class="text-base font-medium text-gray-900">{{ $config->modalidadeEnsino->nome }}</div>
                                                                @if ($config->observacoes)
                                                                    <details class="mt-1">
                                                                        <summary class="text-xs text-gray-500 cursor-pointer">Observações</summary>
                                                                        <div class="mt-1 text-sm text-gray-600 break-words">{{ $config->observacoes }}</div>
                                                                    </details>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                @if ($config->ativo)
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Ativo</span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inativo</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="mt-3 space-y-2">
                                                            <div class="flex items-center justify-between text-sm">
                                                                <span class="text-gray-500">Capacidade</span>
                                                                <span>
                                                                    @if ($config->capacidade_minima_turma || $config->capacidade_maxima_turma)
                                                                        {{ $config->capacidade_minima_turma ?? 'N/A' }} - {{ $config->capacidade_maxima_turma ?? 'N/A' }}
                                                                    @else
                                                                        Não definida
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center justify-between text-sm">
                                                                <span class="text-gray-500">Turnos</span>
                                                                @php
                                                                    $hasTurno = $config->permite_turno_matutino || $config->permite_turno_vespertino || $config->permite_turno_noturno || $config->permite_turno_integral;
                                                                @endphp
                                                                @if ($hasTurno)
                                                                    <div class="flex flex-wrap gap-1 justify-end">
                                                                        @if ($config->permite_turno_matutino)
                                                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">M</span>
                                                                        @endif
                                                                        @if ($config->permite_turno_vespertino)
                                                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">V</span>
                                                                        @endif
                                                                        @if ($config->permite_turno_noturno)
                                                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800">N</span>
                                                                        @endif
                                                                        @if ($config->permite_turno_integral)
                                                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-orange-100 text-orange-800">I</span>
                                                                        @endif
                                                                    </div>
                                                                @else
                                                                    <span>Nenhum</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="mt-4 flex justify-end">
                                                            <form action="{{ route('admin.configuracao-educacional.destroy-modalidade', [$escola, $config]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta configuração?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm inline-flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                    </svg>
                                                                    Remover
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-12">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                                    </path>
                                                </svg>
                                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma modalidade
                                                    configurada</h3>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    Nenhuma modalidade configurada para esta escola.
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Níveis -->
                    <div id="niveis" class="tab-content hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Formulário de Adicionar Nível -->
                            <div class="lg:col-span-1">
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-900">Adicionar Nível</h3>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <form action="{{ route('admin.configuracao-educacional.store-nivel', $escola) }}"
                                            method="POST" class="space-y-6">
                                            @csrf

                                            <!-- Nível de Ensino -->
                                            <div>
                                                <label for="nivel_ensino_id"
                                                    class="block text-sm font-medium text-gray-700 mb-2">
                                                    Nível de Ensino
                                                </label>
                                                <select name="nivel_ensino_id" id="nivel_ensino_id"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                                    required>
                                                    <option value="">Selecione um nível</option>
                                                    @foreach ($niveisDisponiveis as $nivel)
                                                        <option value="{{ $nivel->id }}">{{ $nivel->nome }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Status Ativo -->
                                            <div class="flex items-center">
                                                <input type="hidden" name="ativo" value="0">
                                                <input type="checkbox" id="ativo_nivel" name="ativo" value="1"
                                                    checked
                                                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                <label for="ativo_nivel" class="ml-2 block text-sm text-gray-900">
                                                    Ativo
                                                </label>
                                            </div>

                                            <!-- Capacidades -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="capacidade_minima_turma_nivel"
                                                        class="block text-sm font-medium text-gray-700 mb-2">
                                                        Capacidade Mínima
                                                    </label>
                                                    <input type="number" name="capacidade_minima_turma"
                                                        id="capacidade_minima_turma_nivel" min="1"
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label for="capacidade_maxima_turma_nivel"
                                                        class="block text-sm font-medium text-gray-700 mb-2">
                                                        Capacidade Máxima
                                                    </label>
                                                    <input type="number" name="capacidade_maxima_turma"
                                                        id="capacidade_maxima_turma_nivel" min="1"
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                                </div>
                                            </div>

                                            <!-- Turnos Permitidos -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-3">Turnos
                                                    Permitidos</label>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div class="flex items-center">
                                                        <input type="hidden" name="turno_matutino" value="0">
                                                        <input type="checkbox" id="turno_matutino_nivel"
                                                            name="turno_matutino" value="1"
                                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                        <label for="turno_matutino_nivel"
                                                            class="ml-2 block text-sm text-gray-900">
                                                            Matutino
                                                        </label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input type="hidden" name="turno_vespertino" value="0">
                                                        <input type="checkbox" id="turno_vespertino_nivel"
                                                            name="turno_vespertino" value="1"
                                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                        <label for="turno_vespertino_nivel"
                                                            class="ml-2 block text-sm text-gray-900">
                                                            Vespertino
                                                        </label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input type="hidden" name="turno_noturno" value="0">
                                                        <input type="checkbox" id="turno_noturno_nivel"
                                                            name="turno_noturno" value="1"
                                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                        <label for="turno_noturno_nivel"
                                                            class="ml-2 block text-sm text-gray-900">
                                                            Noturno
                                                        </label>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <input type="hidden" name="turno_integral" value="0">
                                                        <input type="checkbox" id="turno_integral_nivel"
                                                            name="turno_integral" value="1"
                                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                                        <label for="turno_integral_nivel"
                                                            class="ml-2 block text-sm text-gray-900">
                                                            Integral
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Configurações de Carga Horária -->
                                            <div class="grid grid-cols-3 gap-4">
                                                <div>
                                                    <label for="carga_horaria_semanal"
                                                        class="block text-sm font-medium text-gray-700 mb-2">
                                                        Carga Horária/Semana
                                                    </label>
                                                    <input type="number" name="carga_horaria_semanal"
                                                        id="carga_horaria_semanal" min="1"
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label for="numero_aulas_dia"
                                                        class="block text-sm font-medium text-gray-700 mb-2">
                                                        Aulas/Dia
                                                    </label>
                                                    <input type="number" name="numero_aulas_dia" id="numero_aulas_dia"
                                                        min="1"
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label for="duracao_aula_minutos"
                                                        class="block text-sm font-medium text-gray-700 mb-2">
                                                        Duração Aula (min)
                                                    </label>
                                                    <input type="number" name="duracao_aula_minutos"
                                                        id="duracao_aula_minutos" min="30"
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                                </div>
                                            </div>

                                            <!-- Idades -->
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label for="idade_minima"
                                                        class="block text-sm font-medium text-gray-700 mb-2">
                                                        Idade Mínima
                                                    </label>
                                                    <input type="number" name="idade_minima" id="idade_minima"
                                                        min="0"
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label for="idade_maxima"
                                                        class="block text-sm font-medium text-gray-700 mb-2">
                                                        Idade Máxima
                                                    </label>
                                                    <input type="number" name="idade_maxima" id="idade_maxima"
                                                        min="0"
                                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                                </div>
                                            </div>

                                            <!-- Observações -->
                                            <div>
                                                <label for="observacoes_nivel"
                                                    class="block text-sm font-medium text-gray-700 mb-2">
                                                    Observações
                                                </label>
                                                <textarea name="observacoes" id="observacoes_nivel" rows="3" maxlength="1000"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                                    placeholder="Observações adicionais..."></textarea>
                                            </div>



                                            <!-- Botão Salvar -->
                                            <button type="submit"
                                                class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12">
                                                    </path>
                                                </svg>
                                                Salvar Configuração
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Lista de Níveis Configurados -->
                            <div class="lg:col-span-2">
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                </path>
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-900">Níveis Configurados</h3>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        @if ($escola->nivelConfigs->count() > 0)
                                            <div class="hidden sm:block overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Nível
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Status
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Capacidade
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Turnos
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Carga Horária
                                                            </th>
                                                            <th scope="col"
                                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                                Ações
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach ($escola->nivelConfigs as $config)
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <div>
                                                                        <div class="text-sm font-medium text-gray-900">
                                                                            {{ $config->nivelEnsino->nome }}
                                                                        </div>
                                                                        @if ($config->idade_minima || $config->idade_maxima)
                                                                            <div class="text-sm text-gray-500">
                                                                                Idade: {{ $config->idade_minima ?? 'N/A' }}
                                                                                - {{ $config->idade_maxima ?? 'N/A' }} anos
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    @if ($config->ativo)
                                                                        <span
                                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                            Ativo
                                                                        </span>
                                                                    @else
                                                                        <span
                                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                            Inativo
                                                                        </span>
                                                                    @endif
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    @if ($config->capacidade_minima_turma || $config->capacidade_maxima_turma)
                                                                        {{ $config->capacidade_minima_turma ?? 'N/A' }} -
                                                                        {{ $config->capacidade_maxima_turma ?? 'N/A' }}
                                                                    @else
                                                                        <span class="text-gray-500">Não definida</span>
                                                                    @endif
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    @php
                                                                        $turnos = [];
                                                                        if ($config->permite_turno_matutino) {
                                                                            $turnos[] = 'M';
                                                                        }
                                                                        if ($config->permite_turno_vespertino) {
                                                                            $turnos[] = 'V';
                                                                        }
                                                                        if ($config->permite_turno_noturno) {
                                                                            $turnos[] = 'N';
                                                                        }
                                                                        if ($config->permite_turno_integral) {
                                                                            $turnos[] = 'I';
                                                                        }
                                                                    @endphp
                                                                    @if (count($turnos) > 0)
                                                                        {{ implode(', ', $turnos) }}
                                                                    @else
                                                                        <span class="text-gray-500">Nenhum</span>
                                                                    @endif
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                                    @if ($config->carga_horaria_semanal)
                                                                        {{ $config->carga_horaria_semanal }}h/sem
                                                                        @if ($config->duracao_aula_minutos)
                                                                            <div class="text-sm text-gray-500">
                                                                                {{ $config->duracao_aula_minutos }}min/aula
                                                                            </div>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-gray-500">Não definida</span>
                                                                    @endif
                                                                </td>
                                                                <td
                                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                                    <form
                                                                        action="{{ route('admin.configuracao-educacional.destroy-nivel', [$escola, $config]) }}"
                                                                        method="POST" class="inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit"
                                                                            onclick="return confirm('Tem certeza que deseja remover esta configuração?')"
                                                                            class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                                                            title="Remover">
                                                                            <svg class="w-4 h-4" fill="none"
                                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                                </path>
                                                                            </svg>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="sm:hidden space-y-4">
                                                @foreach ($escola->nivelConfigs as $config)
                                                    <div class="border rounded-lg p-4 shadow-sm bg-white">
                                                        <div class="flex items-start justify-between">
                                                            <div>
                                                                <div class="text-base font-medium text-gray-900">{{ $config->nivelEnsino->nome }}</div>
                                                                @if ($config->idade_minima || $config->idade_maxima)
                                                                    <div class="text-sm text-gray-500">Idade: {{ $config->idade_minima ?? 'N/A' }} - {{ $config->idade_maxima ?? 'N/A' }} anos</div>
                                                                @endif
                                                                @if ($config->observacoes)
                                                                    <details class="mt-1">
                                                                        <summary class="text-xs text-gray-500 cursor-pointer">Observações</summary>
                                                                        <div class="mt-1 text-sm text-gray-600 break-words">{{ $config->observacoes }}</div>
                                                                    </details>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                @if ($config->ativo)
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Ativo</span>
                                                                @else
                                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inativo</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="mt-3 space-y-2">
                                                            <div class="flex items-center justify-between text-sm">
                                                                <span class="text-gray-500">Capacidade</span>
                                                                <span>
                                                                    @if ($config->capacidade_minima_turma || $config->capacidade_maxima_turma)
                                                                        {{ $config->capacidade_minima_turma ?? 'N/A' }} - {{ $config->capacidade_maxima_turma ?? 'N/A' }}
                                                                    @else
                                                                        Não definida
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="flex items-center justify-between text-sm">
                                                                <span class="text-gray-500">Turnos</span>
                                                                @php
                                                                    $hasTurno = $config->permite_turno_matutino || $config->permite_turno_vespertino || $config->permite_turno_noturno || $config->permite_turno_integral;
                                                                @endphp
                                                                @if ($hasTurno)
                                                                    <div class="flex flex-wrap gap-1 justify-end">
                                                                        @if ($config->permite_turno_matutino)
                                                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">M</span>
                                                                        @endif
                                                                        @if ($config->permite_turno_vespertino)
                                                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">V</span>
                                                                        @endif
                                                                        @if ($config->permite_turno_noturno)
                                                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800">N</span>
                                                                        @endif
                                                                        @if ($config->permite_turno_integral)
                                                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-orange-100 text-orange-800">I</span>
                                                                        @endif
                                                                    </div>
                                                                @else
                                                                    <span>Nenhum</span>
                                                                @endif
                                                            </div>
                                                            <div class="flex items-center justify-between text-sm">
                                                                <span class="text-gray-500">Carga Horária</span>
                                                                <span>
                                                                    @if ($config->carga_horaria_semanal || $config->duracao_aula_minutos)
                                                                        {{ $config->carga_horaria_semanal ?? 'N/A' }} h/sem • {{ $config->duracao_aula_minutos ?? 'N/A' }} min/aula
                                                                    @else
                                                                        Não definida
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="mt-4 flex justify-end">
                                                            <form action="{{ route('admin.configuracao-educacional.destroy-nivel', [$escola, $config]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta configuração?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm inline-flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                    </svg>
                                                                    Remover
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-12">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                                    </path>
                                                </svg>
                                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum nível configurado
                                                </h3>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    Nenhum nível configurado para esta escola.
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Disciplinas -->
                    <div id="disciplinas" class="tab-content hidden">
                        <div class="space-y-6">
                            <!-- Cabeçalho da seção -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Disciplinas e Cargas Horárias</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Configure as disciplinas, suas cores, cargas horárias e ordem de exibição por nível
                                        de ensino.
                                    </p>
                                </div>
                                <button onclick="loadDisciplinas()"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                        </path>
                                    </svg>
                                    Atualizar
                                </button>
                            </div>

                            <!-- Filtros -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="filtro-area" class="block text-sm font-medium text-gray-700 mb-1">
                                            Área de Conhecimento
                                        </label>
                                        <select id="filtro-area" onchange="filtrarDisciplinas()"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                            <option value="">Todas as áreas</option>
                                            <option value="Campos de Experiência">Campos de Experiência</option>
                                            <option value="Linguagens">Linguagens</option>
                                            <option value="Matemática">Matemática</option>
                                            <option value="Ciências da Natureza">Ciências da Natureza</option>
                                            <option value="Ciências Humanas">Ciências Humanas</option>
                                            <option value="Ensino Religioso">Ensino Religioso</option>
                                            <option value="Formação Técnica e Profissional">Formação Técnica e Profissional
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="filtro-nivel" class="block text-sm font-medium text-gray-700 mb-1">
                                            Nível de Ensino
                                        </label>
                                        <select id="filtro-nivel" onchange="filtrarDisciplinas()"
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                                            <option value="">Todos os níveis</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Loading -->
                            <div id="disciplinas-loading" class="flex items-center justify-center py-8">
                                <svg class="animate-spin h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="ml-2 text-gray-600">Carregando disciplinas...</span>
                            </div>

                            <!-- Conteúdo das disciplinas -->
                            <div id="disciplinas-content" class="hidden">
                                <!-- Será preenchido via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Modal Templates BNCC -->
<div id="templatesBnccModal"
    class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-4 md:p-5 border w-11/12 max-w-full sm:max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header do Modal -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900">Templates Pré-configurados BNCC</h3>
                </div>
                <button onclick="closeTemplatesBnccModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Descrição -->
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Selecione os níveis de ensino que deseja configurar para esta escola.
                    O sistema criará apenas as configurações que ainda não existem.
                </p>
            </div>

            <!-- Filtros por Modalidade -->
            <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z">
                        </path>
                    </svg>
                    Filtrar por Modalidade
                </h4>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="filtrarPorModalidade('todas')"
                        class="modalidade-filter active px-3 py-1 text-xs font-medium rounded-full border transition-colors duration-200 bg-blue-100 text-blue-800 border-blue-300"
                        data-modalidade="todas">
                        Todas
                    </button>
                    <button type="button" onclick="filtrarPorModalidade('EI')"
                        class="modalidade-filter px-3 py-1 text-xs font-medium rounded-full border transition-colors duration-200 bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200"
                        data-modalidade="EI">
                        Educação Infantil
                    </button>
                    <button type="button" onclick="filtrarPorModalidade('EF1')"
                        class="modalidade-filter px-3 py-1 text-xs font-medium rounded-full border transition-colors duration-200 bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200"
                        data-modalidade="EF1">
                        Ens. Fund. - Anos Iniciais
                    </button>
                    <button type="button" onclick="filtrarPorModalidade('EF2')"
                        class="modalidade-filter px-3 py-1 text-xs font-medium rounded-full border transition-colors duration-200 bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200"
                        data-modalidade="EF2">
                        Ens. Fund. - Anos Finais
                    </button>
                    <button type="button" onclick="filtrarPorModalidade('EM')"
                        class="modalidade-filter px-3 py-1 text-xs font-medium rounded-full border transition-colors duration-200 bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200"
                        data-modalidade="EM">
                        Ensino Médio
                    </button>
                    <button type="button" onclick="filtrarPorModalidade('EJA')"
                        class="modalidade-filter px-3 py-1 text-xs font-medium rounded-full border transition-colors duration-200 bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200"
                        data-modalidade="EJA">
                        EJA
                    </button>
                </div>
            </div>

            <!-- Conteúdo dos Templates -->
            <div class="mt-6 max-h-96 overflow-y-auto">
                <div id="templatesContainer">
                    <!-- Templates serão carregados aqui via JavaScript -->
                    <div class="flex items-center justify-center py-8">
                        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="ml-2 text-gray-600">Carregando templates...</span>
                    </div>
                </div>
            </div>

            <!-- Footer do Modal -->
            <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
                <button onclick="closeTemplatesBnccModal()"
                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    Cancelar
                </button>
                <button id="applyTemplatesBtn" onclick="applySelectedTemplates()"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    Aplicar Templates Selecionados
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Disciplina -->
<div id="editarDisciplinaModal"
    class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-4 md:p-5 border w-11/12 max-w-full md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Cabeçalho do Modal -->
            <div class="flex items-center justify-between pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900" id="modal-disciplina-title">
                    Editar Disciplina
                </h3>
                <button onclick="fecharModalDisciplina()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Formulário -->
            <form id="formEditarDisciplina" class="mt-6 space-y-6">
                <input type="hidden" id="disciplina-id" name="disciplina_id">

                <!-- Informações Básicas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="disciplina-nome" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome da Disciplina *
                        </label>
                        <input type="text" id="disciplina-nome" name="nome" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="disciplina-codigo" class="block text-sm font-medium text-gray-700 mb-2">
                            Código
                        </label>
                        <input type="text" id="disciplina-codigo" name="codigo"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                    </div>
                </div>

                <!-- Cor e Área -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="disciplina-cor" class="block text-sm font-medium text-gray-700 mb-2">
                            Cor da Disciplina
                        </label>
                        <div class="flex items-center space-x-3">
                            <input type="color" id="disciplina-cor" name="cor_hex"
                                class="h-10 w-16 border border-gray-300 rounded cursor-pointer">
                            <input type="text" id="disciplina-cor-hex" name="cor_hex_text" placeholder="#6b7280"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="disciplina-area" class="block text-sm font-medium text-gray-700 mb-2">
                            Área de Conhecimento
                        </label>
                        <select id="disciplina-area" name="area_conhecimento"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            <option value="">Selecione uma área</option>
                            <option value="Campos de Experiência">Campos de Experiência</option>
                            <option value="Linguagens">Linguagens</option>
                            <option value="Matemática">Matemática</option>
                            <option value="Ciências da Natureza">Ciências da Natureza</option>
                            <option value="Ciências Humanas">Ciências Humanas</option>
                            <option value="Ensino Religioso">Ensino Religioso</option>
                            <option value="Formação Técnica e Profissional">Formação Técnica e Profissional</option>
                        </select>
                    </div>
                </div>

                <!-- Cargas Horárias por Nível -->
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Cargas Horárias por Nível de Ensino</h4>
                    <div id="niveis-carga-horaria" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Será preenchido via JavaScript -->
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t">
                    <button type="button" onclick="fecharModalDisciplina()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Função para mostrar/esconder tabs
        function showTab(tabName) {
            // Esconder todas as tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Remover classe ativa de todos os botões
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active-tab', 'border-indigo-500', 'text-indigo-600', 'border-green-500',
                    'text-green-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });

            // Mostrar tab selecionada
            document.getElementById(tabName).classList.remove('hidden');

            // Ativar botão correspondente
            const activeButton = document.getElementById(tabName + '-tab');
            activeButton.classList.remove('border-transparent', 'text-gray-500');
            activeButton.classList.add('active-tab');

            if (tabName === 'modalidades') {
                activeButton.classList.add('border-indigo-500', 'text-indigo-600');
            } else {
                activeButton.classList.add('border-green-500', 'text-green-600');
            }

            // Carregar dados específicos da aba
            if (tabName === 'disciplinas') {
                loadDisciplinas();
            }
        }

        $(document).ready(function() {
            // Ativar tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Validação de capacidade
            $('input[name="capacidade_minima_turma"], input[name="capacidade_minima_turma_nivel"]').on('change',
                function() {
                    var minimo = parseInt($(this).val());
                    var maximoInput = $(this).closest('.grid').find(
                        'input[name="capacidade_maxima_turma"], input[name="capacidade_maxima_turma_nivel"]'
                        );
                    var maximo = parseInt(maximoInput.val());

                    if (minimo && maximo && minimo > maximo) {
                        alert('A capacidade mínima não pode ser maior que a máxima.');
                        $(this).val('');
                    }
                });

            $('input[name="capacidade_maxima_turma"], input[name="capacidade_maxima_turma_nivel"]').on('change',
                function() {
                    var maximo = parseInt($(this).val());
                    var minimoInput = $(this).closest('.grid').find(
                        'input[name="capacidade_minima_turma"], input[name="capacidade_minima_turma_nivel"]'
                        );
                    var minimo = parseInt(minimoInput.val());

                    if (minimo && maximo && maximo < minimo) {
                        alert('A capacidade máxima não pode ser menor que a mínima.');
                        $(this).val('');
                    }
                });

            // Validação de idade
            $('input[name="idade_minima"]').on('change', function() {
                var minimo = parseInt($(this).val());
                var maximoInput = $('input[name="idade_maxima"]');
                var maximo = parseInt(maximoInput.val());

                if (minimo && maximo && minimo > maximo) {
                    alert('A idade mínima não pode ser maior que a máxima.');
                    $(this).val('');
                }
            });

            $('input[name="idade_maxima"]').on('change', function() {
                var maximo = parseInt($(this).val());
                var minimoInput = $('input[name="idade_minima"]');
                var minimo = parseInt(minimoInput.val());

                if (minimo && maximo && maximo < minimo) {
                    alert('A idade máxima não pode ser menor que a mínima.');
                    $(this).val('');
                }
            });
        });

        // Funções para o modal de templates BNCC
        function openTemplatesBnccModal() {
            document.getElementById('templatesBnccModal').classList.remove('hidden');
            loadTemplatesBncc();
        }

        function closeTemplatesBnccModal() {
            document.getElementById('templatesBnccModal').classList.add('hidden');
        }

        function loadTemplatesBncc() {
            fetch('{{ route('admin.configuracao-educacional.templates-bncc', $escola) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderTemplatesBncc(data.templates);
                    } else {
                        alert('Erro ao carregar templates: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar templates:', error);
                    alert('Erro ao carregar templates BNCC');
                });
        }



        function applySelectedTemplates() {
            const selectedTemplates = Array.from(document.querySelectorAll('input[name="templates[]"]:checked'))
                .map(input => input.value);

            if (selectedTemplates.length === 0) {
                if (window.alertSystem) {
                    window.alertSystem.warning('Selecione pelo menos um template para aplicar.');
                } else {
                    alert('Selecione pelo menos um template para aplicar.');
                }
                return;
            }

            const submitButton = document.getElementById('applyTemplatesBtn');
            submitButton.disabled = true;
            submitButton.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Aplicando...
    `;

            fetch('{{ route('admin.configuracao-educacional.aplicar-templates-bncc', $escola) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        templates: selectedTemplates
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Exibir mensagem de sucesso com AlertService
                        if (window.alertSystem) {
                            let successMessage = data.message || 'Templates aplicados com sucesso!';

                            // Adicionar detalhes se houver
                            if (data.details) {
                                if (data.details.created && data.details.created.length > 0) {
                                    successMessage += ` Criados: ${data.details.created.length} níveis.`;
                                }
                                if (data.details.existing && data.details.existing.length > 0) {
                                    successMessage += ` ${data.details.existing.length} já existiam.`;
                                }
                            }

                            window.alertSystem.success(successMessage, {
                                timeout: 5000
                            });
                        } else {
                            alert(data.message);
                        }

                        closeTemplatesBnccModal();
                        // Aguardar um pouco antes de recarregar para mostrar o alerta
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        // Exibir erros com AlertService
                        if (window.alertSystem) {
                            let errorMessage = data.message || 'Erro ao aplicar templates';
                            let errors = [];

                            // Processar erros detalhados se houver
                            if (data.errors && Array.isArray(data.errors)) {
                                errors = data.errors;
                            } else if (data.details && data.details.errors && Array.isArray(data.details.errors)) {
                                errors = data.details.errors;
                            }

                            if (errors.length > 0) {
                                window.alertSystem.validation(errorMessage, errors, {
                                    timeout: 10000
                                });
                            } else {
                                window.alertSystem.error(errorMessage, {
                                    timeout: 8000
                                });
                            }
                        } else {
                            alert(data.message || 'Erro ao aplicar templates');
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao aplicar templates:', error);
                    if (window.alertSystem) {
                        window.alertSystem.error(
                            'Erro de conexão ao aplicar templates. Verifique sua conexão e tente novamente.', {
                                timeout: 8000
                            });
                    } else {
                        alert('Erro ao aplicar templates');
                    }
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = 'Aplicar Templates Selecionados';
                });
        }

        // Variável global para armazenar todos os templates
        let todosOsTemplates = {};

        // Função para filtrar templates por modalidade
        function filtrarPorModalidade(modalidadeSelecionada) {
            // Atualizar botões de filtro
            document.querySelectorAll('.modalidade-filter').forEach(btn => {
                btn.classList.remove('active', 'bg-blue-100', 'text-blue-800', 'border-blue-300');
                btn.classList.add('bg-gray-100', 'text-gray-700', 'border-gray-300');
            });

            const btnAtivo = document.querySelector(`[data-modalidade="${modalidadeSelecionada}"]`);
            if (btnAtivo) {
                btnAtivo.classList.remove('bg-gray-100', 'text-gray-700', 'border-gray-300');
                btnAtivo.classList.add('active', 'bg-blue-100', 'text-blue-800', 'border-blue-300');
            }

            // Filtrar e renderizar templates
            if (modalidadeSelecionada === 'todas') {
                renderTemplatesBncc(todosOsTemplates);
            } else {
                const templatesFiltrados = filtrarTemplatesPorModalidade(todosOsTemplates, modalidadeSelecionada);
                renderTemplatesBncc(templatesFiltrados);
            }
        }

        // Função auxiliar para filtrar templates por modalidade específica
        function filtrarTemplatesPorModalidade(templates, modalidade) {
            const templatesFiltrados = {};

            Object.keys(templates).forEach(categoria => {
                Object.keys(templates[categoria]).forEach(subcategoria => {
                    const templatesSubcategoria = templates[categoria][subcategoria].filter(template => {
                        return template.modalidades_compativeis &&
                            template.modalidades_compativeis.includes(modalidade);
                    });

                    if (templatesSubcategoria.length > 0) {
                        if (!templatesFiltrados[categoria]) {
                            templatesFiltrados[categoria] = {};
                        }
                        templatesFiltrados[categoria][subcategoria] = templatesSubcategoria;
                    }
                });
            });

            return templatesFiltrados;
        }

        // Modificar a função renderTemplatesBncc para incluir dados de modalidade
        function renderTemplatesBncc(templates) {
            const container = document.getElementById('templatesContainer');
            container.innerHTML = '';

            // Verificar se há templates para exibir
            if (Object.keys(templates).length === 0) {
                container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 text-gray-500">
                <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-medium">Nenhum template encontrado</p>
                <p class="text-sm">Tente selecionar uma modalidade diferente</p>
            </div>
        `;
                return;
            }

            Object.keys(templates).forEach(categoria => {
                    const categoriaDiv = document.createElement('div');
                    categoriaDiv.className = 'mb-6';

                    categoriaDiv.innerHTML = `
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                ${categoria}
            </h3>
        `;

                    Object.keys(templates[categoria]).forEach(subcategoria => {
                            const subcategoriaDiv = document.createElement('div');
                            subcategoriaDiv.className = 'ml-4 mb-4';

                            subcategoriaDiv.innerHTML = `
                <h4 class="text-md font-medium text-gray-700 mb-3">${subcategoria}</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    ${templates[categoria][subcategoria].map(template => `
                            <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer template-item" 
                                   data-modalidades='${JSON.stringify(template.modalidades_compativeis || [])}'>
                                <input type="checkbox" 
                                       name="templates[]" 
                                       value="${template.id}"
                                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <div class="ml-3 flex-1">
                                    <div class="text-sm font-medium text-gray-900">${template.nome}</div>
                                    <div class="text-xs text-gray-500">${template.descricao}</div>
                                    <div class="text-xs text-blue-600 mt-1">
                                        ${template.idade_formatada} • ${template.carga_horaria_semanal}h/semana
                                    </div>
                                    ${template.modalidades_compativeis && template.modalidades_compativeis.length > 0 ? `
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        ${template.modalidades_compativeis.map(mod => {
                                            const modalidadeNomes = {
                                                'EI': 'Ed. Infantil',
                                                'EF1': 'Fund. I',
                                                'EF2': 'Fund. II', 
                                                'EM': 'Médio',
                                                'EJA': 'EJA'
                                            };
                                            return `<span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">${modalidadeNomes[mod] || mod}</span>`;
                                        }).join('')}
                                    </div>
                                ` : ''}
                                </div>
                            </label>
                        `).join('')}
                </div>
            `;

                        categoriaDiv.appendChild(subcategoriaDiv);
                    });

                container.appendChild(categoriaDiv);
            });
        }

        // Modificar a função loadTemplatesBncc para armazenar os templates globalmente
        function loadTemplatesBncc() {
            fetch('{{ route('admin.configuracao-educacional.templates-bncc', $escola) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        todosOsTemplates = data.templates;
                        renderTemplatesBncc(todosOsTemplates);
                    } else {
                        alert('Erro ao carregar templates: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar templates:', error);
                    alert('Erro ao carregar templates BNCC');
                });
        }

        // Funções para gerenciar disciplinas
        function loadDisciplinas() {
            document.getElementById('disciplinas-loading').classList.remove('hidden');
            document.getElementById('disciplinas-content').classList.add('hidden');

            fetch('{{ route('admin.configuracao-educacional.disciplinas', $escola) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderDisciplinas(data.disciplinas, data.niveis);
                        populateNiveisFilter(data.niveis);
                    } else {
                        alert('Erro ao carregar disciplinas: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar disciplinas:', error);
                    alert('Erro ao carregar disciplinas');
                })
                .finally(() => {
                    document.getElementById('disciplinas-loading').classList.add('hidden');
                    document.getElementById('disciplinas-content').classList.remove('hidden');
                });
        }

        function renderDisciplinas(disciplinas, niveis) {
            const container = document.getElementById('disciplinas-content');

            if (!disciplinas || disciplinas.length === 0) {
                container.innerHTML = `
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma disciplina encontrada</h3>
                <p class="mt-1 text-sm text-gray-500">Não há disciplinas configuradas para esta escola.</p>
            </div>
        `;
                return;
            }

            // Agrupar disciplinas por área de conhecimento
            const disciplinasPorArea = {};
            disciplinas.forEach(disciplina => {
                const area = disciplina.area_conhecimento || 'Sem área';
                if (!disciplinasPorArea[area]) {
                    disciplinasPorArea[area] = [];
                }
                disciplinasPorArea[area].push(disciplina);
            });

            let html = '';
            Object.keys(disciplinasPorArea).forEach(area => {
                html += `
            <div class="disciplina-area mb-8" data-area="${area}">
                <h4 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <div class="w-4 h-4 rounded mr-2" style="background-color: ${getAreaColor(area)}"></div>
                    ${area}
                    <span class="ml-2 text-sm text-gray-500">(${disciplinasPorArea[area].length} disciplinas)</span>
                </h4>
                <div class="grid gap-4">
        `;

                disciplinasPorArea[area].forEach(disciplina => {
                    html += renderDisciplinaCard(disciplina, niveis);
                });

                html += `
                </div>
            </div>
        `;
            });

            container.innerHTML = html;
        }

        function renderDisciplinaCard(disciplina, niveis) {
            return `
        <div class="disciplina-card bg-white border border-gray-200 rounded-lg p-4 shadow-sm" data-disciplina-id="${disciplina.id}" data-area="${disciplina.area_conhecimento}">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center">
                    <div class="w-6 h-6 rounded mr-3" style="background-color: ${disciplina.cor_hex || '#6b7280'}"></div>
                    <div>
                        <h5 class="font-medium text-gray-900">${disciplina.nome}</h5>
                        <p class="text-sm text-gray-500">${disciplina.codigo || ''}</p>
                    </div>
                </div>
                <button onclick="editarDisciplina(${disciplina.id})" 
                        class="text-purple-600 hover:text-purple-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-2">
                ${disciplina.niveis_ensino && disciplina.niveis_ensino.length > 0 ? disciplina.niveis_ensino.map(nivel => `
                        <div class="disciplina-nivel flex items-center justify-between p-2 bg-gray-50 rounded" data-nivel-id="${nivel.nivel_ensino_id}">
                            <span class="text-sm font-medium text-gray-700">${nivel.nivel_ensino.nome}</span>
                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                <span>${nivel.carga_horaria_semanal}h/sem</span>
                                <span class="text-gray-400">•</span>
                                <span>${nivel.carga_horaria_anual}h/ano</span>
                                ${nivel.obrigatoria ? '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Obrigatória</span>' : '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Eletiva</span>'}
                            </div>
                        </div>
                    `).join('') : '<div class="text-sm text-gray-500 italic">Nenhuma carga horária configurada</div>'}
            </div>
        </div>
    `;
        }

        function getAreaColor(area) {
            const cores = {
                'Campos de Experiência': '#f59e0b',
                'Linguagens': '#3b82f6',
                'Matemática': '#ef4444',
                'Ciências da Natureza': '#10b981',
                'Ciências Humanas': '#8b5cf6',
                'Ensino Religioso': '#f97316',
                'Formação Técnica e Profissional': '#6b7280'
            };
            return cores[area] || '#6b7280';
        }

        function populateNiveisFilter(niveis) {
            const select = document.getElementById('filtro-nivel');
            select.innerHTML = '<option value="">Todos os níveis</option>';

            niveis.forEach(nivel => {
                const option = document.createElement('option');
                option.value = nivel.id;
                option.textContent = nivel.nome;
                select.appendChild(option);
            });
        }

        function filtrarDisciplinas() {
            const areaFiltro = document.getElementById('filtro-area').value;
            const nivelFiltro = document.getElementById('filtro-nivel').value;

            // Filtrar por área
            document.querySelectorAll('.disciplina-area').forEach(area => {
                const areaName = area.dataset.area;
                if (!areaFiltro || areaName === areaFiltro) {
                    area.style.display = 'block';
                } else {
                    area.style.display = 'none';
                }
            });

            // Filtrar por nível (dentro das áreas visíveis)
            if (nivelFiltro) {
                document.querySelectorAll('.disciplina-card').forEach(card => {
                    const temNivel = card.querySelector(`[data-nivel-id="${nivelFiltro}"]`);
                    if (temNivel) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            } else {
                document.querySelectorAll('.disciplina-card').forEach(card => {
                    card.style.display = 'block';
                });
            }
        }

        function editarDisciplina(disciplinaId) {
            // Buscar dados da disciplina
            fetch(`{{ route('admin.configuracao-educacional.disciplinas', $escola) }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const disciplina = data.disciplinas.find(d => d.id == disciplinaId);
                        if (disciplina) {
                            preencherModalDisciplina(disciplina, data.niveis);
                            document.getElementById('editarDisciplinaModal').classList.remove('hidden');
                        } else {
                            alert('Disciplina não encontrada');
                        }
                    } else {
                        alert('Erro ao carregar dados da disciplina');
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar disciplina:', error);
                    alert('Erro ao carregar dados da disciplina');
                });
        }

        function preencherModalDisciplina(disciplina, niveis) {
            // Preencher campos básicos
            document.getElementById('disciplina-id').value = disciplina.id;
            document.getElementById('disciplina-nome').value = disciplina.nome;
            document.getElementById('disciplina-codigo').value = disciplina.codigo || '';
            document.getElementById('disciplina-cor').value = disciplina.cor_hex || '#6b7280';
            document.getElementById('disciplina-cor-hex').value = disciplina.cor_hex || '#6b7280';
            document.getElementById('disciplina-area').value = disciplina.area_conhecimento || '';

            // Sincronizar campos de cor
            document.getElementById('disciplina-cor').addEventListener('input', function() {
                document.getElementById('disciplina-cor-hex').value = this.value;
            });

            document.getElementById('disciplina-cor-hex').addEventListener('input', function() {
                if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                    document.getElementById('disciplina-cor').value = this.value;
                }
            });

            // Preencher cargas horárias por nível
            const container = document.getElementById('niveis-carga-horaria');
            container.innerHTML = '';

            niveis.forEach(nivel => {
                const disciplinaNivel = disciplina.disciplina_niveis ? disciplina.disciplina_niveis.find(dn => dn
                    .nivel_ensino_id == nivel.id) : null;
                const cargaSemanal = disciplinaNivel ? disciplinaNivel.carga_horaria_semanal : 0;
                const cargaAnual = disciplinaNivel ? disciplinaNivel.carga_horaria_anual : 0;
                const obrigatoria = disciplinaNivel ? disciplinaNivel.obrigatoria : false;

                const nivelHtml = `
            <div class="border border-gray-200 rounded-lg p-4" data-nivel-id="${nivel.id}">
                <div class="flex items-center justify-between mb-3">
                    <h5 class="font-medium text-gray-900">${nivel.nome}</h5>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="nivel_${nivel.id}_obrigatoria" 
                               ${obrigatoria ? 'checked' : ''}
                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-sm text-gray-700">Obrigatória</span>
                    </label>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Carga Semanal (horas)
                        </label>
                        <input type="number" 
                               name="nivel_${nivel.id}_carga_semanal" 
                               value="${cargaSemanal}"
                               min="0" 
                               step="0.5"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Carga Anual (horas)
                        </label>
                        <input type="number" 
                               name="nivel_${nivel.id}_carga_anual" 
                               value="${cargaAnual}"
                               min="0"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                    </div>
                </div>
            </div>
        `;

                container.insertAdjacentHTML('beforeend', nivelHtml);
            });
        }

        function fecharModalDisciplina() {
            document.getElementById('editarDisciplinaModal').classList.add('hidden');
            document.getElementById('formEditarDisciplina').reset();
        }

        // Adicionar event listener para o formulário
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('formEditarDisciplina').addEventListener('submit', function(e) {
                e.preventDefault();
                salvarDisciplina();
            });
        });

        function salvarDisciplina() {
            const form = document.getElementById('formEditarDisciplina');
            const formData = new FormData(form);
            const disciplinaId = formData.get('disciplina_id');

            // Preparar dados da disciplina
            const disciplinaData = {
                nome: formData.get('nome'),
                codigo: formData.get('codigo'),
                cor_hex: formData.get('cor_hex'),
                area_conhecimento: formData.get('area_conhecimento')
            };

            // Preparar dados dos níveis
            const niveisData = [];
            document.querySelectorAll('#niveis-carga-horaria [data-nivel-id]').forEach(nivelDiv => {
                const nivelId = nivelDiv.dataset.nivelId;
                const cargaSemanal = nivelDiv.querySelector(`[name="nivel_${nivelId}_carga_semanal"]`).value;
                const cargaAnual = nivelDiv.querySelector(`[name="nivel_${nivelId}_carga_anual"]`).value;
                const obrigatoria = nivelDiv.querySelector(`[name="nivel_${nivelId}_obrigatoria"]`).checked;

                if (cargaSemanal > 0 || cargaAnual > 0) {
                    niveisData.push({
                        nivel_ensino_id: nivelId,
                        carga_horaria_semanal: parseFloat(cargaSemanal) || 0,
                        carga_horaria_anual: parseFloat(cargaAnual) || 0,
                        obrigatoria: obrigatoria
                    });
                }
            });

            // Salvar disciplina
            const requests = [
                // Atualizar dados básicos da disciplina
                fetch(`{{ route('admin.configuracao-educacional.update-disciplina', $escola) }}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        disciplina_id: disciplinaId,
                        ...disciplinaData
                    })
                })
            ];

            // Adicionar requests para atualizar cargas horárias dos níveis
            niveisData.forEach(nivelData => {
                requests.push(
                    fetch(`{{ route('admin.configuracao-educacional.update-disciplina-nivel', $escola) }}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            disciplina_id: disciplinaId,
                            ...nivelData
                        })
                    })
                );
            });

            Promise.all(requests)
                .then(responses => Promise.all(responses.map(r => r.json())))
                .then(results => {
                    const allSuccess = results.every(result => result.success);
                    if (allSuccess) {
                        fecharModalDisciplina();
                        loadDisciplinas(); // Recarregar a lista
                        alert('Disciplina atualizada com sucesso!');
                    } else {
                        const errors = results.filter(r => !r.success).map(r => r.message).join('\n');
                        alert('Erro ao salvar disciplina:\n' + errors);
                    }
                })
                .catch(error => {
                    console.error('Erro ao salvar disciplina:', error);
                    alert('Erro ao salvar disciplina');
                });
        }
    </script>
@endpush
