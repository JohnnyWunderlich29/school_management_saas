@extends('layouts.app')

@section('title', 'Conversa - ' . $conversa->titulo)

@section('content')
    <div class="flex flex-col md:flex-row" x-data="{ openSidebar: false }">
        <div class="hidden md:flex flex-row bg-white shadow rounded-lg overflow-hidden md:mr-5">
            <!-- Sidebar de Conversas -->
            <div class="w-80 bg-gray-50 border-r border-gray-200 flex flex-col bg-white">
                <!-- Header do Sidebar -->
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Conversas</h2>
                        <button type="button" onclick="abrirModalNovaConversa()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Nova
                        </button>
                    </div>
                    <!-- Barra de Pesquisa -->
                    <div class="relative">
                        <input type="text" placeholder="Buscar conversas..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Lista de Conversas -->
                <div id="conversas-list" class="flex-1 overflow-y-auto">
                    @forelse($todasConversas ?? [] as $conv)
                        <div class="p-4 border-b border-gray-100 hover:bg-gray-100 cursor-pointer {{ $conv->id == $conversa->id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}"
                            onclick="navegarParaConversa('{{ route('conversas.show', $conv->id) }}')">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                        <span
                                            class="text-white font-medium text-sm">{{ substr($conv->titulo, 0, 2) }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $conv->titulo }}</h3>
                                        <span class="text-xs text-gray-500">{{ $conv->updated_at->format('H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 truncate mt-1">{{ $conv->participantes->count() }}
                                        participantes</p>
                                    @if ($conv->mensagens->count() > 0)
                                        <p class="text-xs text-gray-500 truncate mt-1">
                                            {{ Str::limit($conv->mensagens->last()->conteudo, 50) }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500">
                            <p>Nenhuma conversa encontrada</p>
                        </div>
                    @endforelse
                </div> <!-- Fim mensagens-list -->
            </div>
        </div>

        <!-- Área Principal do Chat -->
        <x-card class="flex-1 flex flex-col md:ml-5 ml-0">
            <div>
                <!-- Header do Chat -->
                <div class="bg-white border-b border-gray-200  md:px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <button class="md:hidden p-2 text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100" @click="openSidebar = true" title="Conversas">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                </svg>
                            </button>
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-medium text-sm">{{ substr($conversa->titulo, 0, 2) }}</span>
                            </div>
                            <div>
                                <h1 class="text-lg font-semibold text-gray-900">{{ $conversa->titulo }}</h1>
                                <p class="text-sm text-gray-500 flex items-center">
                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                    {{ $conversa->participantes->count() }} participantes
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <!-- Busca de mensagens -->
                            <button onclick="toggleBuscaMensagens()"
                                class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
                                title="Buscar mensagens">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>

                            <!-- Dropdown de ações -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
                                    title="Mais opções">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z">
                                        </path>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200">
                                    <div class="py-1">
                                        <button onclick="abrirModalAdicionarParticipante()"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Adicionar Participante
                                        </button>

                                        <button onclick="verParticipantes()"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                            Ver Participantes
                                        </button>

                                        <div class="border-t border-gray-100"></div>

                                        @if ($conversa->criador_id == auth()->id() || $conversa->isParticipante(auth()->id()))
                                            <button onclick="finalizarConversa()"
                                                class="flex items-center w-full px-4 py-2 text-sm text-orange-600 hover:bg-orange-50">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Finalizar Conversa
                                            </button>
                                        @endif

                                        @if ($conversa->criador_id == auth()->id())
                                            <button onclick="excluirConversa()"
                                                class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                                Excluir Conversa
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barra de busca de mensagens (oculta por padrão) -->
                <div id="barra-busca-mensagens" class="hidden border-b border-gray-200 p-3 bg-gray-50">
                    <div class="flex items-center space-x-2">
                        <div class="flex-1 relative">
                            <input type="text" id="input-busca-mensagens" placeholder="Buscar mensagens..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <button onclick="toggleBuscaMensagens()"
                            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
                            title="Fechar busca">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Área de mensagens -->
                @if(!isset($conversa) || !$conversa->id)
                <!-- Mensagem quando não há conversa selecionada -->
                <div class="flex flex-col items-center justify-center h-full p-8">
                    <div class="text-center">
                        <div class="mb-6">
                            <svg class="w-20 h-20 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Nenhuma conversa selecionada</h3>
                        <p class="text-gray-600 mb-6">Selecione uma conversa na barra lateral ou crie uma nova conversa para começar.</p>
                        <button onclick="abrirModalNovaConversa()" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-plus mr-2"></i> Iniciar nova conversa
                        </button>
                    </div>
                </div>
                @else
                <div class="flex-1 overflow-y-auto md:p-6 md:space-y-4 max-h-[calc(100vh-200px)]" id="mensagens-container"
                    >
                    <!-- Loading indicator para mensagens antigas -->
                    <div id="loading-mensagens" class="hidden text-center py-4">
                        <div
                            class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-white transition ease-in-out duration-150">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                @endif
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Carregando mensagens...
                        </div>
                    </div>

                    <!-- Container das mensagens -->
                    <div id="mensagens-list" class="max-h-full">
                        @forelse($mensagens as $mensagem)
                            <div
                                class="flex {{ $mensagem->remetente_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                                <div
                                    class="flex items-start space-x-2 max-w-[85%] sm:max-w-xs lg:max-w-md {{ $mensagem->remetente_id == auth()->id() ? 'flex-row-reverse space-x-reverse' : '' }}">
                                    <!-- Avatar -->
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                            <span class="text-gray-600 text-xs font-medium">
                                                {{ substr($mensagem->remetente->name ?? 'U', 0, 2) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Mensagem -->
                                    <div
                                        class="flex flex-col {{ $mensagem->remetente_id == auth()->id() ? 'items-end' : 'items-start' }}">
                                        <!-- Bubble da mensagem -->
                                        <div
                                            class="px-4 py-2 rounded-lg {{ $mensagem->remetente_id == auth()->id() ? 'bg-blue-500 text-white rounded-tr-none' : 'bg-gray-100 text-gray-900 rounded-tl-none' }}">
                                            <p class="text-sm">{{ $mensagem->conteudo }}</p>
                                            @if ($mensagem->arquivo_path)
                                                <div class="mt-2">
                                                    <a href="{{ Storage::url($mensagem->arquivo_path) }}" target="_blank"
                                                        class="flex items-center space-x-2 {{ $mensagem->remetente_id == auth()->id() ? 'text-blue-100' : 'text-gray-700' }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                            </path>
                                                        </svg>
                                                        <span
                                                            class="text-xs">{{ $mensagem->arquivo_nome ?? basename($mensagem->arquivo_path) }}</span>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Timestamp e status -->
                                        <div class="flex items-center space-x-1 mt-1">
                                            <span
                                                class="text-xs text-gray-500">{{ $mensagem->created_at->format('H:i') }}</span>
                                            @if ($mensagem->remetente_id == auth()->id())
                                                <span class="text-xs text-gray-400">Enviado</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                        </path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma mensagem</h3>
                                    <p class="mt-1 text-sm text-gray-500">Seja o primeiro a enviar uma mensagem nesta
                                        conversa.
                                    </p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Indicador de digitação -->
                    <div id="indicador-digitacao"
                        class="hidden px-4 py-2 text-sm text-gray-500 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center space-x-2">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                    style="animation-delay: 0.1s">
                                </div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
                                    style="animation-delay: 0.2s">
                                </div>
                            </div>
                            <span id="texto-digitacao">Alguém está digitando...</span>
                        </div>
                    </div>

                    <!-- Área de Input -->
                    <div class="bg-white border-t border-gray-200 pt-2 md:p-4">
                        <form action="{{ route('conversas.enviar-mensagem', $conversa->id) }}" method="POST"
                            enctype="multipart/form-data" id="form-mensagem">
                            @csrf
                            <div class="flex items-end space-x-3">
                                <!-- Botão de anexo com dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button type="button" @click="open = !open"
                                        class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
                                        title="Anexar arquivo">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                            </path>
                                        </svg>
                                    </button>

                                    <div x-show="open" @click.away="open = false" x-transition
                                        class="absolute bottom-full mb-2 left-0 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200">
                                        <div class="py-1">
                                            <button type="button" onclick="selecionarTipoArquivo('image/*')"
                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                Imagem
                                            </button>
                                            <button type="button" onclick="selecionarTipoArquivo('video/*')"
                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                Vídeo
                                            </button>
                                            <button type="button" onclick="selecionarTipoArquivo('audio/*')"
                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z">
                                                    </path>
                                                </svg>
                                                Áudio
                                            </button>
                                            <button type="button" onclick="selecionarTipoArquivo('.pdf,.doc,.docx,.txt')"
                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                                Documento
                                            </button>
                                        </div>
                                    </div>

                                    <input type="file" id="arquivo" name="arquivo" class="hidden">
                                </div>

                                <!-- Campo de texto -->
                                <div class="flex-1">
                                    <textarea name="conteudo" id="mensagem-input" placeholder="Digite sua mensagem..."
                                        class="w-full resize-none border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        rows="1" maxlength="1000" oninput="indicarDigitacao()" onkeydown="handleEnterKey(event)" required></textarea>
                                </div>

                                <!-- Botão de envio -->
                                <button type="submit"
                                    class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    id="btn-enviar">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Preview do arquivo selecionado -->
                            <div id="preview-arquivo" class="hidden mt-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div id="icone-arquivo">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <span id="nome-arquivo" class="text-sm text-gray-600 block"></span>
                                            <span id="tamanho-arquivo" class="text-xs text-gray-400"></span>
                                        </div>
                                    </div>
                                    <button type="button" onclick="removerArquivo()"
                                        class="text-red-500 hover:text-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div id="preview-imagem" class="hidden mt-2">
                                    <img id="img-preview" src="" alt="Preview"
                                        class="max-w-full h-32 object-cover rounded">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
        </x-card>
    </div>

    <!-- Drawer Mobile Conversas -->
    <div class="md:hidden">
        <div
            class="fixed inset-0 z-40"
            x-show="openSidebar"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div
                class="absolute inset-0 bg-black bg-opacity-50"
                @click="openSidebar = false"
            ></div>
        </div>
        <div
            class="fixed inset-y-0 left-0 z-50 w-11/12 max-w-sm bg-white border-r border-gray-200 flex flex-col"
            x-show="openSidebar"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="-translate-x-full opacity-0"
        >
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Conversas</h2>
                <button class="p-2 text-gray-500 hover:text-gray-700" @click="openSidebar = false" title="Fechar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto" id="conversas-list-mobile">
                @forelse($todasConversas ?? [] as $conv)
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-100 cursor-pointer {{ $conv->id == $conversa->id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}"
                        onclick="navegarParaConversa('{{ route('conversas.show', $conv->id) }}')" @click="openSidebar = false">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm">{{ substr($conv->titulo, 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-gray-900 truncate">{{ $conv->titulo }}</h3>
                                    <span class="text-xs text-gray-500">{{ $conv->updated_at->format('H:i') }}</span>
                                </div>
                                <p class="text-sm text-gray-600 truncate mt-1">{{ $conv->participantes->count() }} participantes</p>
                                @if ($conv->mensagens->count() > 0)
                                    <p class="text-xs text-gray-500 truncate mt-1">{{ Str::limit($conv->mensagens->last()->conteudo, 50) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500">
                        <p>Nenhuma conversa encontrada</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Participante -->
    <div id="modal-adicionar-participante"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Adicionar Participante</h3>
                <button onclick="fecharModalAdicionarParticipante()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <input type="text" id="busca-usuario" placeholder="Buscar usuário por nome ou email..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div id="resultados-busca" class="max-h-60 overflow-y-auto mb-4">
                <!-- Resultados da busca aparecerão aqui -->
            </div>

            <div class="flex justify-end space-x-3">
                <button onclick="fecharModalAdicionarParticipante()"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Ver Participantes -->
    <div id="modal-ver-participantes"
        class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Participantes da Conversa</h3>
                <button onclick="fecharModalVerParticipantes()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <div id="lista-participantes" class="max-h-60 overflow-y-auto">
                <!-- Lista de participantes aparecerá aqui -->
            </div>
        </div>
    </div>

    <!-- Modal Confirmação -->
    <div id="modal-confirmacao" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-semibold text-gray-900" id="modal-confirmacao-titulo">Confirmar Ação</h3>
                    <p class="text-gray-600" id="modal-confirmacao-texto">Tem certeza que deseja realizar esta ação?</p>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button onclick="fecharModalConfirmacao()"
                    class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button id="btn-confirmar-acao" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <script>
        // Auto-resize textarea
        document.querySelector('textarea[name="conteudo"]').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        // Indicador de digitação
        let timeoutDigitacao;

        function indicarDigitacao() {
            sendTypingIndicator();
            clearTimeout(timeoutDigitacao);
            timeoutDigitacao = setTimeout(() => {
                // Parar indicador de digitação após 3 segundos
            }, 3000);
        }

        // Interceptar submit do formulário para enviar via AJAX
        document.getElementById('form-mensagem').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const btnEnviar = document.getElementById('btn-enviar');
            const textarea = document.getElementById('mensagem-input');

            // Desabilitar botão durante envio
            btnEnviar.disabled = true;

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Limpar formulário
                        textarea.value = '';
                        document.getElementById('arquivo').value = '';
                        document.getElementById('preview-arquivo').classList.add('hidden');

                        // Adicionar a nova mensagem diretamente
                        if (data.mensagem) {
                            const mensagesList = document.getElementById('mensagens-list');
                            const mensagemHtml = createMessageHtml(data.mensagem);
                            mensagesList.insertAdjacentHTML('beforeend', mensagemHtml);

                            // Atualizar contadores
                            lastMessageId = data.mensagem.id;
                            lastMessageCount++;

                            scrollToBottom();
                        }
                    } else {
                        alert('Erro ao enviar mensagem: ' + (data.message || 'Erro desconhecido'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao enviar mensagem');
                })
                .finally(() => {
                    // Reabilitar botão
                    btnEnviar.disabled = false;
                });
        });

        // Função para lidar com a tecla Enter
        function handleEnterKey(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();

                const textarea = event.target;
                const conteudo = textarea.value.trim();

                if (conteudo) {
                    // Simular clique no botão de envio
                    document.getElementById('btn-enviar').click();
                }
            }
        }

        // Seleção de tipo de arquivo
        function selecionarTipoArquivo(accept) {
            const input = document.getElementById('arquivo');
            input.accept = accept;
            input.click();
        }

        // Preview de arquivo melhorado
        document.getElementById('arquivo').addEventListener('change', function() {
            const preview = document.getElementById('preview-arquivo');
            const nomeArquivo = document.getElementById('nome-arquivo');
            const tamanhoArquivo = document.getElementById('tamanho-arquivo');
            const iconeArquivo = document.getElementById('icone-arquivo');
            const previewImagem = document.getElementById('preview-imagem');
            const imgPreview = document.getElementById('img-preview');

            if (this.files.length > 0) {
                const arquivo = this.files[0];
                nomeArquivo.textContent = arquivo.name;

                // Formatar tamanho do arquivo
                const tamanho = arquivo.size;
                let tamanhoFormatado;
                if (tamanho < 1024) {
                    tamanhoFormatado = tamanho + ' B';
                } else if (tamanho < 1024 * 1024) {
                    tamanhoFormatado = (tamanho / 1024).toFixed(1) + ' KB';
                } else {
                    tamanhoFormatado = (tamanho / (1024 * 1024)).toFixed(1) + ' MB';
                }
                tamanhoArquivo.textContent = tamanhoFormatado;

                // Mostrar preview de imagem
                if (arquivo.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imgPreview.src = e.target.result;
                        previewImagem.classList.remove('hidden');
                    };
                    reader.readAsDataURL(arquivo);

                    // Ícone de imagem
                    iconeArquivo.innerHTML = `
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            `;
                } else {
                    previewImagem.classList.add('hidden');

                    // Ícone padrão de arquivo
                    iconeArquivo.innerHTML = `
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            `;
                }

                preview.classList.remove('hidden');
            } else {
                preview.classList.add('hidden');
                previewImagem.classList.add('hidden');
            }
        });

        function removerArquivo() {
            document.getElementById('arquivo').value = '';
            document.getElementById('preview-arquivo').classList.add('hidden');
            document.getElementById('preview-imagem').classList.add('hidden');
        }

        // Funções dos modais
        function toggleBuscaMensagens() {
            const barra = document.getElementById('barra-busca-mensagens');
            const input = document.getElementById('input-busca-mensagens');

            if (barra.classList.contains('hidden')) {
                barra.classList.remove('hidden');
                input.focus();
            } else {
                barra.classList.add('hidden');
                input.value = '';
                // Limpar resultados da busca
            }
        }

        function abrirModalAdicionarParticipante() {
            document.getElementById('modal-adicionar-participante').classList.remove('hidden');
            document.getElementById('busca-usuario').focus();
        }

        function fecharModalAdicionarParticipante() {
            document.getElementById('modal-adicionar-participante').classList.add('hidden');
            document.getElementById('busca-usuario').value = '';
            document.getElementById('resultados-busca').innerHTML = '';
        }

        function verParticipantes() {
            document.getElementById('modal-ver-participantes').classList.remove('hidden');
            carregarParticipantes();
        }

        function fecharModalVerParticipantes() {
            document.getElementById('modal-ver-participantes').classList.add('hidden');
        }

        function finalizarConversa() {
            mostrarModalConfirmacao(
                'Finalizar Conversa',
                'Tem certeza que deseja finalizar esta conversa? Esta ação não pode ser desfeita.',
                () => {
                    fetch(`/conversas/{{ $conversa->id }}/finalizar`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.href = '/conversas';
                            } else {
                                alert('Erro ao finalizar conversa: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao finalizar conversa');
                        });
                }
            );
        }

        function excluirConversa() {
            mostrarModalConfirmacao(
                'Excluir Conversa',
                'Tem certeza que deseja excluir esta conversa? Todas as mensagens serão perdidas permanentemente.',
                () => {
                    fetch(`/conversas/{{ $conversa->id }}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.href = '/conversas';
                            } else {
                                alert('Erro ao excluir conversa: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao excluir conversa');
                        });
                }
            );
        }

        function mostrarModalConfirmacao(titulo, texto, callback) {
            document.getElementById('modal-confirmacao-titulo').textContent = titulo;
            document.getElementById('modal-confirmacao-texto').textContent = texto;
            document.getElementById('btn-confirmar-acao').onclick = () => {
                fecharModalConfirmacao();
                callback();
            };
            document.getElementById('modal-confirmacao').classList.remove('hidden');
        }

        function fecharModalConfirmacao() {
            document.getElementById('modal-confirmacao').classList.add('hidden');
        }

        // Busca de usuários
        let timeoutBusca;
        document.getElementById('busca-usuario').addEventListener('input', function() {
            clearTimeout(timeoutBusca);
            const termo = this.value.trim();

            if (termo.length < 2) {
                document.getElementById('resultados-busca').innerHTML = '';
                return;
            }

            timeoutBusca = setTimeout(() => {
                fetch(`/api/usuarios/buscar?q=${encodeURIComponent(termo)}&conversa_id={{ $conversa->id }}`, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('resultados-busca');
                        container.innerHTML = '';

                        if (data.length === 0) {
                            container.innerHTML =
                                '<p class="text-gray-500 text-sm p-2">Nenhum usuário encontrado</p>';
                            return;
                        }

                        data.forEach(usuario => {
                            const div = document.createElement('div');
                            div.className =
                                'flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg cursor-pointer';
                            div.onclick = () => adicionarParticipanteDirecto(usuario.id, usuario
                                .name);
                            div.innerHTML = `
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                            <span class="text-sm font-medium text-gray-600">${usuario.name.charAt(0).toUpperCase()}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">${usuario.name}</p>
                            <p class="text-xs text-gray-500">${usuario.email}</p>
                        </div>
                    </div>
                    <button class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-600">
                        Adicionar
                    </button>
                `;
                            container.appendChild(div);
                        });
                    })
                    .catch(error => {
                        console.error('Erro na busca:', error);
                        document.getElementById('resultados-busca').innerHTML =
                            '<p class="text-red-500 text-center py-4">Erro ao buscar usuários</p>';
                    });
            }, 300);
        });

        // Função para adicionar participante diretamente
        function adicionarParticipanteDirecto(usuarioId, nomeUsuario) {
            fetch(`/conversas/{{ $conversa->id }}/participantes`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        usuario_id: usuarioId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Fechar modal e limpar busca
                        fecharModalAdicionarParticipante();
                        document.getElementById('busca-usuario').value = '';
                        document.getElementById('resultados-busca').innerHTML = '';

                        // Recarregar lista de participantes
                        carregarParticipantes();

                        // Mostrar notificação de sucesso
                        alert(`${nomeUsuario} foi adicionado à conversa com sucesso!`);
                    } else {
                        alert('Erro ao adicionar participante: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao adicionar participante');
                });
        }

        // Função para buscar usuários (mantida para compatibilidade)
        function buscarUsuarios() {
            const termo = document.getElementById('busca-usuario').value;

            if (termo.length < 2) {
                document.getElementById('resultados-busca').innerHTML = '';
                return;
            }

            fetch(`/api/usuarios/buscar?q=${encodeURIComponent(termo)}&conversa_id={{ $conversa->id }}`)
                .then(response => response.json())
                .then(usuarios => {
                    const container = document.getElementById('resultados-busca');

                    if (usuarios.length === 0) {
                        container.innerHTML = '<p class="text-gray-500 text-center py-4">Nenhum usuário encontrado</p>';
                        return;
                    }

                    container.innerHTML = usuarios.map(usuario => `
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg cursor-pointer" 
                     onclick="adicionarParticipanteDirecto(${usuario.id}, '${usuario.name}')">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                            <span class="text-sm font-medium text-gray-600">${usuario.name.charAt(0).toUpperCase()}</span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">${usuario.name}</div>
                            <div class="text-sm text-gray-500">${usuario.email}</div>
                        </div>
                    </div>
                    <button class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-600">
                        Adicionar
                    </button>
                </div>
            `).join('');
                })
                .catch(error => {
                    console.error('Erro ao buscar usuários:', error);
                    document.getElementById('resultados-busca').innerHTML =
                        '<p class="text-red-500 text-center py-4">Erro ao buscar usuários</p>';
                });
        }

        // Função simplificada para adicionar participante (mantida para compatibilidade)
        function adicionarParticipanteSelecionado() {
            const termo = document.getElementById('busca-usuario').value.trim();
            if (!termo) {
                alert('Digite o nome de um usuário para buscar');
                return;
            }

            // Buscar e adicionar o primeiro resultado
            fetch(`/api/usuarios/buscar?q=${encodeURIComponent(termo)}&conversa_id={{ $conversa->id }}`)
                .then(response => response.json())
                .then(usuarios => {
                    if (usuarios.length > 0) {
                        adicionarParticipanteDirecto(usuarios[0].id, usuarios[0].name);
                    } else {
                        alert('Usuário não encontrado');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao buscar usuário');
                });
        }

        function carregarParticipantes() {
            fetch(`/conversas/{{ $conversa->id }}/participantes`)
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('lista-participantes');
                    container.innerHTML = '';

                    data.forEach(participante => {
                        const div = document.createElement('div');
                        div.className = 'flex items-center justify-between p-3 border-b border-gray-100';
                        div.innerHTML = `
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                        <span class="text-sm font-medium text-gray-600">${participante.name.charAt(0).toUpperCase()}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">${participante.name}</p>
                        <p class="text-xs text-gray-500">${participante.email}</p>
                        ${participante.id === {{ $conversa->criador_id }} ? '<span class="text-xs text-blue-600">Criador</span>' : ''}
                    </div>
                </div>
                ${participante.id !== {{ $conversa->criador_id }} && {{ $conversa->criador_id }} === {{ auth()->id() }} ? 
                    `<button onclick="removerParticipante(${participante.id}, '${participante.name}')" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>` : ''}
            `;
                        container.appendChild(div);
                    });
                })
                .catch(error => {
                    console.error('Erro ao carregar participantes:', error);
                });
        }

        function removerParticipante(usuarioId, nomeParticipante) {
            if (confirm(`Tem certeza que deseja remover ${nomeParticipante} desta conversa?`)) {
                fetch(`/conversas/{{ $conversa->id }}/participantes/${usuarioId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            carregarParticipantes();
                            alert(`${nomeParticipante} foi removido da conversa com sucesso!`);
                        } else {
                            alert('Erro ao remover participante: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao remover participante');
                    });
            }
        }

        // Scroll automático para última mensagem
        function scrollToBottom() {
            const container = document.getElementById('mensagens-container');
            container.scrollTop = container.scrollHeight;
        }

        // Scroll para baixo ao carregar
        window.addEventListener('load', scrollToBottom);

        // Variáveis para controle de digitação
        let typingTimeout;
        let isTyping = false;

        // Atualizar indicadores de digitação
        function updateTypingIndicators(typingUsers) {
            const indicator = document.getElementById('indicador-digitacao');

            if (typingUsers.length > 0) {
                const names = typingUsers.map(user => user.user_name).join(', ');
                const text = typingUsers.length === 1 ?
                    `${names} está digitando...` :
                    `${names} estão digitando...`;

                document.getElementById('texto-digitacao').textContent = text;
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.add('hidden');
            }
        }

        // Enviar indicação de digitação
        function sendTypingIndicator() {
            if (!isTyping) {
                isTyping = true;
                fetch(`/api/conversas/{{ $conversa->id }}/digitando`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .catch(error => console.error('Erro ao enviar indicação de digitação:', error));
            }

            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                isTyping = false;
            }, 3000);
        }

        // Sistema de paginação infinita e auto-refresh
        let currentPage = 1;
        let isLoadingMessages = false;
        let hasMoreMessages = true;
        let autoRefreshInterval;
        let lastMessageCount = {{ $mensagens->count() }};
        let lastMessageId = {{ $mensagens->last()->id ?? 0 }};
        let isRefreshing = false;

        // Paginação infinita com scroll
        function setupInfiniteScroll() {
            const container = document.getElementById('mensagens-container');

            container.addEventListener('scroll', function() {
                // Verificar se chegou ao topo (para carregar mensagens antigas)
                if (container.scrollTop === 0 && hasMoreMessages && !isLoadingMessages) {
                    loadOlderMessages();
                }
            });
        }

        // Carregar mensagens antigas
        function loadOlderMessages() {
            if (isLoadingMessages || !hasMoreMessages) return;

            isLoadingMessages = true;
            const loadingIndicator = document.getElementById('loading-mensagens');
            loadingIndicator.classList.remove('hidden');

            const nextPage = currentPage + 1;

            fetch(`{{ route('conversas.carregar-mensagens', $conversa->id) }}?page=${nextPage}&per_page=10`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.mensagens && data.mensagens.length > 0) {
                        const mensagesList = document.getElementById('mensagens-list');
                        const currentScrollHeight = document.getElementById('mensagens-container').scrollHeight;

                        // Adicionar mensagens no início da lista
                        data.mensagens.forEach(mensagem => {
                            const mensagemHtml = createMessageHtml(mensagem);
                            mensagesList.insertAdjacentHTML('afterbegin', mensagemHtml);
                        });

                        // Manter posição do scroll
                        const newScrollHeight = document.getElementById('mensagens-container').scrollHeight;
                        document.getElementById('mensagens-container').scrollTop = newScrollHeight -
                            currentScrollHeight;

                        currentPage = data.current_page;
                        hasMoreMessages = data.has_more;
                    } else {
                        hasMoreMessages = false;
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar mensagens antigas:', error);
                })
                .finally(() => {
                    isLoadingMessages = false;
                    loadingIndicator.classList.add('hidden');
                });
        }

        // Criar HTML da mensagem
        function createMessageHtml(mensagem) {
            const isOwn = mensagem.is_own;
            const justifyClass = isOwn ? 'justify-end' : 'justify-start';
            const flexClass = isOwn ? 'flex-row-reverse space-x-reverse' : '';
            const itemsClass = isOwn ? 'items-end' : 'items-start';
            const bubbleClass = isOwn ? 'bg-blue-500 text-white rounded-tr-none' :
                'bg-gray-100 text-gray-900 rounded-tl-none';
            const statusHtml = isOwn && mensagem.status ? `<span class="text-xs text-gray-400">${mensagem.status}</span>` :
                '';

            return `
    <div id="mensagem-${mensagem.id}" class="flex ${justifyClass}">
        <div class="flex items-start space-x-2 max-w-xs lg:max-w-md ${flexClass}">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                    <span class="text-gray-600 text-xs font-medium">
                        ${mensagem.remetente.initials}
                    </span>
                </div>
            </div>
            <div class="flex flex-col ${itemsClass}">
                <div class="px-4 py-2 rounded-lg ${bubbleClass}">
                    <p class="text-sm">${mensagem.conteudo}</p>
                </div>
                <div class="flex items-center space-x-1 mt-1">
                    <span class="text-xs text-gray-500">${mensagem.created_at}</span>
                    ${statusHtml}
                </div>
            </div>
        </div>
    </div>
    `;
        }

        // Auto-refresh para novas mensagens
        function startAutoRefresh() {
            autoRefreshInterval = setInterval(() => {
                if (!isRefreshing) {
                    checkForNewMessages();
                    updateConversasList();
                }
            }, 2000); // Reduzido para 2 segundos para melhor responsividade
        }

        function checkForNewMessages() {
            if (isRefreshing) return;

            isRefreshing = true;

            // Verificar novas mensagens
            fetch(`{{ route('conversas.carregar-mensagens', $conversa->id) }}?page=1&per_page=50&latest=true&last_message_id=${lastMessageId}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {

                    if (data.mensagens && data.mensagens.length > 0) {
                        const mensagesList = document.getElementById('mensagens-list');

                        // Adicionar todas as mensagens novas retornadas
                        data.mensagens.forEach(mensagem => {
                            // Verifica se a mensagem já existe no DOM para evitar duplicidade
                            if (!document.getElementById(`mensagem-${mensagem.id}`)) {
                                mensagesList.insertAdjacentHTML('beforeend', createMessageHtml(mensagem));

                                // Atualizar o ID da última mensagem
                                if (mensagem.id > lastMessageId) {
                                    lastMessageId = mensagem.id;
                                }
                            }
                        });

                        lastMessageCount = data.total;
                        scrollToBottom();
                    }

                    // Verificar indicadores de digitação
                    return fetch(`/api/conversas/{{ $conversa->id }}/status`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    });
                })
                .then(response => response.json())
                .then(statusData => {
                    if (statusData.digitando) {
                        updateTypingIndicators(statusData.digitando);
                    } else {
                        updateTypingIndicators([]);
                    }
                })
                .catch(error => {
                    console.log('Auto-refresh pausado temporariamente');
                })
                .finally(() => {
                    isRefreshing = false;
                });
        }

        // Função para atualizar a lista de conversas na sidebar
        function updateConversasList() {
            fetch('{{ route('conversas.lista') }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.conversas) {
                    updateSidebarConversas(data.conversas);
                }
            })
            .catch(error => {
                console.log('Erro ao atualizar lista de conversas:', error);
            });
        }

        // Função para atualizar o HTML da sidebar com as conversas
        function updateSidebarConversas(conversas) {
            const conversasContainer = document.getElementById('conversas-list');
            if (!conversasContainer) return;

            let html = '';
            const conversaAtualId = {{ $conversa->id }};

            conversas.forEach(conv => {
                const isActive = conv.id == conversaAtualId ? 'bg-blue-50 border-l-4 border-l-blue-500' : '';
                const hasUnread = conv.mensagens_nao_lidas > 0;
                const unreadBadge = hasUnread ? `<span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">${conv.mensagens_nao_lidas}</span>` : '';
                
                html += `
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-100 cursor-pointer ${isActive} ${hasUnread ? 'bg-yellow-50' : ''}"
                        onclick="navegarParaConversa('{{ url('/conversas') }}/${conv.id}')">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 relative">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm">${conv.iniciais}</span>
                                </div>
                                ${hasUnread ? '<div class="absolute -top-1 -right-1">' + unreadBadge + '</div>' : ''}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-medium text-gray-900 truncate ${hasUnread ? 'font-bold' : ''}">${conv.titulo}</h3>
                                    <span class="text-xs text-gray-500">${conv.updated_at}</span>
                                </div>
                                <p class="text-sm text-gray-600 truncate mt-1">${conv.participantes_count} participantes</p>
                                ${conv.ultima_mensagem ? `<p class="text-xs ${hasUnread ? 'text-gray-800 font-medium' : 'text-gray-500'} truncate mt-1">${conv.ultima_mensagem}</p>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });

            if (conversas.length === 0) {
                html = `
                    <div class="p-4 text-center text-gray-500">
                        <p>Nenhuma conversa encontrada</p>
                    </div>
                `;
            }

            conversasContainer.innerHTML = html;
        }

        // Verificar status e indicadores de digitação integrado no checkForNewMessages
        // A verificação de digitação será feita junto com as novas mensagens

        // Iniciar auto-refresh e paginação infinita quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            setupInfiniteScroll();
            startAutoRefresh();
            scrollToBottom();
        });

        // Parar auto-refresh quando sair da página
        window.addEventListener('beforeunload', function() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        });

        // Função para navegar para conversa pausando auto-refresh
        function navegarParaConversa(url) {
            // Pausar auto-refresh durante navegação
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }

            // Navegar para a nova conversa
            window.location.href = url;
        }
        
        // Funções para o modal de nova conversa
        function abrirModalNovaConversa() {
            document.getElementById('modalNovaConversa').classList.remove('hidden');
        }
        
        function fecharModalNovaConversa() {
            document.getElementById('modalNovaConversa').classList.add('hidden');
        }
        
        // Controle de exibição dos campos baseado no tipo de conversa
        document.addEventListener('DOMContentLoaded', function() {
            const tipoSelect = document.getElementById('tipo');
            const campoTurma = document.getElementById('campo-turma');
            const campoParticipantes = document.getElementById('campo-participantes');
            
            if (tipoSelect) {
                tipoSelect.addEventListener('change', function() {
                    const tipoSelecionado = this.value;
                    
                    // Esconde todos os campos condicionais
                    campoTurma.style.display = 'none';
                    campoParticipantes.style.display = 'none';
                    
                    // Exibe os campos de acordo com o tipo selecionado
                    if (tipoSelecionado === 'turma') {
                        campoTurma.style.display = 'block';
                    } else if (tipoSelecionado === 'individual' || tipoSelecionado === 'grupo') {
                        campoParticipantes.style.display = 'block';
                    }
                });
            }
            
            // Busca de participantes
            const buscaInput = document.getElementById('busca-participantes');
            const participanteItems = document.querySelectorAll('.participante-item');
            
            if (buscaInput) {
                buscaInput.addEventListener('input', function() {
                    const termo = this.value.toLowerCase().trim();
                    
                    participanteItems.forEach(item => {
                        const nome = item.getAttribute('data-nome');
                        const email = item.getAttribute('data-email');
                        
                        if (nome.includes(termo) || email.includes(termo) || termo === '') {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
            
            // Selecionar todos os participantes
            const selecionarTodos = document.getElementById('selecionar-todos');
            const participanteCheckboxes = document.querySelectorAll('.participante-checkbox');
            const contadorSelecionados = document.getElementById('contador-selecionados');
            
            if (selecionarTodos) {
                selecionarTodos.addEventListener('change', function() {
                    const isChecked = this.checked;
                    
                    participanteCheckboxes.forEach(checkbox => {
                        const item = checkbox.closest('.participante-item');
                        if (item.style.display !== 'none') {
                            checkbox.checked = isChecked;
                        }
                    });
                    
                    atualizarContador();
                });
            }
            
            // Atualizar contador de participantes selecionados
            if (participanteCheckboxes.length > 0) {
                participanteCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', atualizarContador);
                });
            }
            
            function atualizarContador() {
                if (contadorSelecionados) {
                    const selecionados = document.querySelectorAll('.participante-checkbox:checked').length;
                    contadorSelecionados.textContent = selecionados;
                }
            }
            
            // Validação do formulário
            const formNovaConversa = document.getElementById('formNovaConversa');
            
            if (formNovaConversa) {
                formNovaConversa.addEventListener('submit', function(e) {
                    const tipoSelecionado = tipoSelect.value;
                    
                    if (tipoSelecionado === 'turma' && document.getElementById('turma_id').value === '') {
                        e.preventDefault();
                        alert('Por favor, selecione uma turma.');
                        return false;
                    }
                    
                    if ((tipoSelecionado === 'individual' || tipoSelecionado === 'grupo') && 
                        document.querySelectorAll('.participante-checkbox:checked').length === 0) {
                        e.preventDefault();
                        alert('Por favor, selecione pelo menos um participante.');
                        return false;
                    }
                });
            }
        });
    </script>
    
<!-- Modal de Nova Conversa -->
<div id="modalNovaConversa" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4 border-b pb-3">
            <h3 class="text-lg font-semibold text-gray-900">Nova Conversa</h3>
            <button type="button" onclick="fecharModalNovaConversa()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="formNovaConversa" action="{{ route('conversas.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                        Título da Conversa <span class="text-red-500">*</span>
                    </label>
                    <x-input type="text" 
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                           id="titulo" 
                           name="titulo" 
                           required 
                           placeholder="Ex: Reunião de Pais - Turma A"/>
                </div>
                
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Conversa <span class="text-red-500">*</span>
                    </label>
                    <x-select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                            id="tipo" 
                            name="tipo" 
                            required>
                        <option value="">Selecione o tipo...</option>
                        <option value="individual">Individual</option>
                        <option value="grupo">Grupo</option>
                        <option value="turma">Turma</option>
                    </x-select>
                </div>
            </div>
            
            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                <x-textarea class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                          id="descricao" 
                          name="descricao" 
                          rows="3" 
                          placeholder="Descreva o objetivo desta conversa..."></x-textarea>
            </div>
            
            <!-- Campo de turma (aparece quando tipo = turma) -->
            <div id="campo-turma" style="display: none;">
                <label for="turma_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Turma <span class="text-red-500">*</span>
                </label>
                <x-select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                        id="turma_id" 
                        name="turma_id">
                    <option value="">Selecione a turma...</option>
                    @foreach($turmas as $turma)
                        <option value="{{ $turma->id }}">{{ $turma->nome }}</option>
                    @endforeach
                </x-select>
            </div>
            
            <!-- Campo de participantes (aparece quando tipo = individual ou grupo) -->
            <div id="campo-participantes" style="display: none;">
                <label for="participantes" class="block text-sm font-medium text-gray-700 mb-2">
                    Participantes <span class="text-red-500">*</span>
                </label>
                
                <!-- Busca de participantes -->
                <div class="mb-3">
                    <x-input type="text" 
                           id="busca-participantes" 
                           name="busca-participantes"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                           placeholder="Buscar participantes por nome ou email..."/>
                </div>
                
                <!-- Lista de participantes com checkboxes -->
                <div class="border border-gray-300 rounded-md max-h-60 overflow-y-auto bg-white">
                    <div class="p-2">
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input type="checkbox" id="selecionar-todos" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm font-medium text-gray-700">Selecionar todos</span>
                        </label>
                    </div>
                    <div class="border-t border-gray-200"></div>
                    <div id="lista-participantes">
                        @foreach($usuariosDisponiveis as $usuario)
                            <label class="participante-item flex items-center p-2 hover:bg-gray-50 cursor-pointer" 
                                   data-nome="{{ strtolower($usuario->name) }}" 
                                   data-email="{{ strtolower($usuario->email) }}">
                                <x-input type="checkbox" 
                                       name="participantes[]" 
                                       value="{{ $usuario->id }}" 
                                       class="participante-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"></x-input>
                                <div class="ml-3 flex-1">
                                    <div class="text-sm font-medium text-gray-900">{{ $usuario->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $usuario->email }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                
                <!-- Contador de selecionados -->
                <div class="mt-2 text-sm text-gray-600">
                    <span id="contador-selecionados">0</span> participante(s) selecionado(s)
                </div>
            </div>
            
            <!-- Mensagem inicial -->
            <div>
                <label for="mensagem_inicial" class="block text-sm font-medium text-gray-700 mb-2">
                    Mensagem Inicial <span class="text-red-500">*</span>
                </label>
                <x-textarea class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                          id="mensagem_inicial" 
                          name="mensagem_inicial" 
                          rows="4" 
                          required
                          placeholder="Digite a primeira mensagem da conversa..."></x-textarea>
            </div>
            
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <x-input class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" 
                           type="checkbox" 
                           id="ativo" 
                           name="ativo" 
                           value="1" 
                           checked></x-input>
                </div>
                <div class="ml-3 text-sm">
                    <label for="ativo" class="font-medium text-gray-700">
                        Conversa ativa
                    </label>
                    <p class="text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Conversas inativas não permitem novas mensagens.
                    </p>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <button type="button" onclick="fecharModalNovaConversa()" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Criar Conversa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
