@extends('layouts.app')

@section('title', 'Visualizar Planejamento')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <x-breadcrumbs :items="[
            ['title' => 'Planejamentos', 'url' => route('planejamentos.index')],
            ['title' => $planejamento->titulo ?: 'Planejamento #' . $planejamento->id, 'url' => '#'],
        ]" />
        <x-card>
            <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 py-3">
                    <!-- Linha 1: Título e Status -->
                    <div class="flex items-center justify-between">
                        <h1 class="text-base sm:text-xl font-semibold text-gray-900 mt-1 break-words">
                            {{ $planejamento->titulo ?: 'Planejamento #' . $planejamento->id }}</h1>
                        @include('planejamentos.components.status-badge', [
                            'status' => $planejamento->status_efetivo,
                        ])
                    </div>

                    <!-- Linha 2: Botões de Ação -->
                    <div class="flex flex-wrap items-center gap-2">
                        @permission('planejamentos.aprovar')
                            @if (in_array($planejamento->status_efetivo, ['revisao', 'finalizado']))
                                <!-- Ações de Aprovação (Permissão planejamentos.aprovar e status Revisão) -->
                                <x-button type="button" id="btn-aprovar" color="primary" class="sm:justify-center">
                                    <i class="fas fa-thumbs-up mr-1"></i>
                                    Aprovar
                                </x-button>
                                <x-button type="button" id="btn-rejeitar" color="danger" class="sm:justify-center">
                                    <i class="fas fa-thumbs-down mr-1"></i>
                                    Rejeitar
                                </x-button>
                            @endif
                        @endpermission

                        <!-- Voltar padrão -->
                        <x-button href="{{ route('planejamentos.index') }}" color="secondary" class="sm:justify-center">
                            <i class="fas fa-arrow-left mr-1"></i>
                            <span class="hidden md:inline">Voltar para Planejamentos</span>
                            <span class="md:hidden">Voltar</span>
                        </x-button>

                        @permission('planejamentos.editar')
                            @if (in_array($planejamento->status_efetivo, ['rascunho', 'revisao', 'rejeitado']))
                                <button id="btn-editar"
                                    onclick="window.location.href='{{ route('planejamentos.wizard', ['edit' => $planejamento->id]) }}'"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    title="Continuar planejamento">
                                    <i class="fas fa-edit mr-2"></i>
                                    Continuar planejamento
                                </button>
                            @endif
                        @endpermission


                        <!-- Dropdown de Mais Ações -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>

                            <div x-show="open" @click.away="open = false"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                                <div class="py-1">
                                    <a href="{{ route('planejamentos.export', ['planejamento' => $planejamento, 'format' => 'pdf']) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                                    </a>
                                    <a href="{{ route('planejamentos.export', ['planejamento' => $planejamento, 'format' => 'excel']) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                                    </a>
                                    <a href="{{ route('planejamentos.export', ['planejamento' => $planejamento, 'format' => 'docx']) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-file-word mr-2"></i>Exportar DOCX
                                    </a>
                                    <button type="button"
                                        @click="navigator.clipboard.writeText('{{ route('planejamentos.show', $planejamento) }}'); open=false; window.alertSystem?.success('Link copiado para a área de transferência!')"
                                        class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-share mr-2"></i>Compartilhar
                                    </button>
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-copy mr-2"></i>Duplicar
                                    </a>
                                    <a href="{{ route('historico.modelo', ['modelo' => 'planejamento', 'id' => $planejamento->id]) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-history mr-2"></i>Histórico
                                    </a>
                                    <a href="#" onclick="window.print()"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-print mr-2"></i>Imprimir
                                    </a>
                                    @can('delete', $planejamento)
                                        <div class="border-t border-gray-100"></div>
                                        <form action="{{ route('planejamentos.destroy', $planejamento) }}" method="POST"
                                            onsubmit="return confirm('Tem certeza que deseja excluir este planejamento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-full text-left block px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                <i class="fas fa-trash mr-2"></i>Excluir
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- Layout Principal -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
            <div class="flex flex-col lg:flex-row gap-4 lg:gap-6">
                <!-- Sidebar de Navegação -->
                <div class="hidden lg:block w-64 flex-shrink-0">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-6">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-sm font-medium text-gray-900">Navegação</h3>
                        </div>

                        <nav class="p-2">
                            <ul class="space-y-1">
                                <li>
                                    <a href="#secao-informacoes"
                                        class="nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-100 active">
                                        <i class="fas fa-info-circle mr-3 text-blue-500"></i>
                                        Informações Gerais
                                    </a>
                                </li>
                                <li>
                                    <a href="#secao-configuracao"
                                        class="nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog mr-3 text-gray-400"></i>
                                        Configuração
                                    </a>
                                </li>
                                <li>
                                    <a href="#secao-periodo"
                                        class="nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-calendar mr-3 text-gray-400"></i>
                                        Período e Duração
                                    </a>
                                </li>
                                <li>
                                    <a href="#secao-conteudo"
                                        class="nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-book mr-3 text-gray-400"></i>
                                        Conteúdo Pedagógico
                                    </a>
                                </li>
                                <li>
                                    <a href="#secao-metodologia"
                                        class="nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-chalkboard-teacher mr-3 text-gray-400"></i>
                                        Metodologia
                                    </a>
                                </li>
                                <li>
                                    <a href="#secao-avaliacao"
                                        class="nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-clipboard-check mr-3 text-gray-400"></i>
                                        Avaliação
                                    </a>
                                </li>
                                <li>
                                    <a href="#secao-recursos"
                                        class="nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-tools mr-3 text-gray-400"></i>
                                        Recursos
                                    </a>
                                </li>
                                <li>
                                    <a href="#secao-observacoes"
                                        class="nav-link flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sticky-note mr-3 text-gray-400"></i>
                                        Observações
                                    </a>
                                </li>
                            </ul>
                        </nav>

                        <!-- Progresso -->
                        <div class="p-4 border-t border-gray-200">
                            <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                                <span>Completude</span>
                                <span id="progresso-percentual">85%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div id="barra-progresso" class="bg-blue-600 h-2 rounded-full" style="width: 85%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navegação Mobile -->
                <div class="lg:hidden mb-4 -mx-1 overflow-x-auto">
                    <div class="flex space-x-2 px-1">
                        <a href="#secao-informacoes"
                            class="nav-link inline-flex items-center px-3 py-2 text-sm rounded-full border border-gray-300 bg-white text-gray-700">
                            <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                            Informações
                        </a>
                        <a href="#secao-configuracao"
                            class="nav-link inline-flex items-center px-3 py-2 text-sm rounded-full border border-gray-300 bg-white text-gray-700">
                            <i class="fas fa-cog mr-2 text-gray-400"></i>
                            Configuração
                        </a>
                        <a href="#secao-periodo"
                            class="nav-link inline-flex items-center px-3 py-2 text-sm rounded-full border border-gray-300 bg-white text-gray-700">
                            <i class="fas fa-calendar mr-2 text-gray-400"></i>
                            Período
                        </a>
                        <a href="#secao-conteudo"
                            class="nav-link inline-flex items-center px-3 py-2 text-sm rounded-full border border-gray-300 bg-white text-gray-700">
                            <i class="fas fa-book mr-2 text-gray-400"></i>
                            Conteúdo
                        </a>
                        <a href="#secao-metodologia"
                            class="nav-link inline-flex items-center px-3 py-2 text-sm rounded-full border border-gray-300 bg-white text-gray-700">
                            <i class="fas fa-chalkboard-teacher mr-2 text-gray-400"></i>
                            Metodologia
                        </a>
                        <a href="#secao-avaliacao"
                            class="nav-link inline-flex items-center px-3 py-2 text-sm rounded-full border border-gray-300 bg-white text-gray-700">
                            <i class="fas fa-clipboard-check mr-2 text-gray-400"></i>
                            Avaliação
                        </a>
                        <a href="#secao-recursos"
                            class="nav-link inline-flex items-center px-3 py-2 text-sm rounded-full border border-gray-300 bg-white text-gray-700">
                            <i class="fas fa-tools mr-2 text-gray-400"></i>
                            Recursos
                        </a>
                        <a href="#secao-observacoes"
                            class="nav-link inline-flex items-center px-3 py-2 text-sm rounded-full border border-gray-300 bg-white text-gray-700">
                            <i class="fas fa-sticky-note mr-2 text-gray-400"></i>
                            Observações
                        </a>
                    </div>
                </div>

                <!-- Conteúdo Principal -->
                <div class="flex-1">
                    <div class="space-y-6">
                        <!-- Seção: Informações Gerais -->
                        <section id="secao-informacoes" class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                        Informações Gerais
                                    </h2>
                                    <button class="btn-editar-secao text-gray-400 hover:text-gray-600"
                                        data-secao="informacoes">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                @include('planejamentos.view.sections.informacoes', [
                                    'planejamento' => $planejamento,
                                ])
                            </div>
                        </section>

                        <!-- Seção: Configuração -->
                        <section id="secao-configuracao" class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-cog text-gray-500 mr-2"></i>
                                        Configuração
                                    </h2>
                                    <button class="btn-editar-secao text-gray-400 hover:text-gray-600"
                                        data-secao="configuracao">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                @include('planejamentos.view.sections.configuracao', [
                                    'planejamento' => $planejamento,
                                ])
                            </div>
                        </section>

                        <!-- Seção: Período e Duração -->
                        <section id="secao-periodo" class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-calendar text-gray-500 mr-2"></i>
                                        Período e Duração
                                    </h2>
                                    <button class="btn-editar-secao text-gray-400 hover:text-gray-600"
                                        data-secao="periodo">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                @include('planejamentos.view.sections.periodo', [
                                    'planejamento' => $planejamento,
                                ])
                            </div>
                        </section>

                        <!-- Seção: Cronograma Diário -->
                        <section id="secao-cronograma" class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-calendar-day text-blue-500 mr-2"></i>
                                        Cronograma Diário
                                    </h2>
                                    <button class="btn-editar-secao text-gray-400 hover:text-gray-600"
                                        data-secao="cronograma">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button id="toggle-cronograma" type="button"
                                        class="inline-flex items-center px-3 py-1 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                                        <i id="cronograma-toggle-icon" class="fas fa-chevron-down mr-2"></i>
                                        <span id="cronograma-toggle-text">Mostrar</span>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                @include('planejamentos.view.sections.cronograma', [
                                    'planejamento' => $planejamento,
                                ])
                            </div>
                        </section>

                        <!-- Seção: Conteúdo Pedagógico -->
                        <section id="secao-conteudo" class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-book text-gray-500 mr-2"></i>
                                        Conteúdo Pedagógico
                                    </h2>
                                    <button class="btn-editar-secao text-gray-400 hover:text-gray-600"
                                        data-secao="conteudo">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                @include('planejamentos.view.sections.conteudo', [
                                    'planejamento' => $planejamento,
                                ])
                            </div>
                        </section>

                        <!-- Seção: Metodologia -->
                        <section id="secao-metodologia" class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-chalkboard-teacher text-gray-500 mr-2"></i>
                                        Metodologia
                                    </h2>
                                    <button class="btn-editar-secao text-gray-400 hover:text-gray-600"
                                        data-secao="metodologia">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                @include('planejamentos.view.sections.metodologia', [
                                    'planejamento' => $planejamento,
                                ])
                            </div>
                        </section>

                        <!-- Seção: Avaliação -->
                        <section id="secao-avaliacao" class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-clipboard-check text-gray-500 mr-2"></i>
                                        Avaliação
                                    </h2>
                                    <button class="btn-editar-secao text-gray-400 hover:text-gray-600"
                                        data-secao="avaliacao">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                @include('planejamentos.view.sections.avaliacao', [
                                    'planejamento' => $planejamento,
                                ])
                            </div>
                        </section>

                        <!-- Seção: Recursos -->
                        <section id="secao-recursos" class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-tools text-gray-500 mr-2"></i>
                                        Recursos Necessários
                                    </h2>
                                    <button class="btn-editar-secao text-gray-400 hover:text-gray-600"
                                        data-secao="recursos">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                @include('planejamentos.view.sections.recursos', [
                                    'planejamento' => $planejamento,
                                ])
                            </div>
                        </section>

                        <!-- Seção: Observações -->
                        <section id="secao-observacoes" class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-4 sm:p-6 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-sticky-note text-gray-500 mr-2"></i>
                                        Observações
                                    </h2>
                                    <button class="btn-editar-secao text-gray-400 hover:text-gray-600"
                                        data-secao="observacoes">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                @include('planejamentos.view.sections.observacoes', [
                                    'planejamento' => $planejamento,
                                ])
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Aprovação -->
        <x-modal name="modal-aprovar-planejamento" title="Aprovar Planejamento" :closable="true">
            <div class="space-y-3">
                <p class="text-sm text-gray-600">Você pode registrar uma observação para esta aprovação (opcional).</p>
                <label class="block text-sm font-medium text-gray-700">Observações</label>
                <textarea id="observacoes-aprovacao" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Digite suas observações (opcional)..."></textarea>
            </div>
            @slot('footer')
                <x-button type="button" color="secondary" id="btn-cancelar-aprovacao">Cancelar</x-button>
                <x-button type="button" color="primary" id="btn-confirmar-aprovacao">Aprovar</x-button>
            @endslot
        </x-modal>

        <!-- Modal de Rejeição -->
        <x-modal name="modal-rejeitar-planejamento" title="Rejeitar Planejamento" :closable="true">
            <div class="space-y-3">
                <p class="text-sm text-gray-600">Informe as observações da rejeição (opcional). Se nenhum texto for
                    informado, será registrado "Nenhuma observação".</p>
                <label class="block text-sm font-medium text-gray-700">Observações</label>
                <textarea id="observacoes-rejeicao" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Digite suas observações..."></textarea>
            </div>
            @slot('footer')
                <x-button type="button" color="secondary" id="btn-cancelar-rejeicao">Cancelar</x-button>
                <x-button type="button" color="danger" id="btn-confirmar-rejeicao">Rejeitar</x-button>
            @endslot
        </x-modal>
    </div>

    <!-- Modal de Edição Inline -->
    <div id="modal-edicao" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modal-titulo" class="text-lg font-medium text-gray-900">Editar Seção</h3>
                <button type="button" id="fechar-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="form-edicao" class="space-y-4">
                <div id="conteudo-edicao">
                    <!-- Conteúdo do formulário será carregado aqui -->
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" id="cancelar-edicao"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navegação suave
            const navLinks = document.querySelectorAll('.nav-link');
            const sections = document.querySelectorAll('section[id^="secao-"]');

            // Scroll suave para seções
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetSection = document.getElementById(targetId);

                    if (targetSection) {
                        targetSection.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Atualizar navegação ativa baseada no scroll
            function updateActiveNav() {
                let current = '';

                sections.forEach(section => {
                    const sectionTop = section.offsetTop - 100;
                    const sectionHeight = section.offsetHeight;

                    if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.classList.remove('active', 'bg-blue-50', 'text-blue-700');
                    link.classList.add('text-gray-700');

                    const icon = link.querySelector('i');
                    icon.classList.remove('text-blue-500');
                    icon.classList.add('text-gray-400');

                    if (link.getAttribute('href') === '#' + current) {
                        link.classList.add('active', 'bg-blue-50', 'text-blue-700');
                        link.classList.remove('text-gray-700');

                        icon.classList.add('text-blue-500');
                        icon.classList.remove('text-gray-400');
                    }
                });
            }

            // Event listener para scroll
            window.addEventListener('scroll', updateActiveNav);

            // Inicializar navegação ativa
            updateActiveNav();

            // Edição inline
            const modal = document.getElementById('modal-edicao');
            const modalTitulo = document.getElementById('modal-titulo');
            const conteudoEdicao = document.getElementById('conteudo-edicao');
            const formEdicao = document.getElementById('form-edicao');

            // Botões de editar seção
            document.querySelectorAll('.btn-editar-secao').forEach(btn => {
                btn.addEventListener('click', function() {
                    const secao = this.getAttribute('data-secao');
                    abrirModalEdicao(secao);
                });
            });

            // Fechar modal
            document.getElementById('fechar-modal').addEventListener('click', fecharModal);
            document.getElementById('cancelar-edicao').addEventListener('click', fecharModal);

            // Fechar modal clicando fora
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    fecharModal();
                }
            });

            function abrirModalEdicao(secao) {
                const titulos = {
                    'informacoes': 'Editar Informações Gerais',
                    'configuracao': 'Editar Configuração',
                    'periodo': 'Editar Período e Duração',
                    'conteudo': 'Editar Conteúdo Pedagógico',
                    'metodologia': 'Editar Metodologia',
                    'avaliacao': 'Editar Avaliação',
                    'recursos': 'Editar Recursos',
                    'observacoes': 'Editar Observações'
                };

                modalTitulo.textContent = titulos[secao] || 'Editar Seção';

                // Carregar formulário da seção
                carregarFormularioEdicao(secao);

                modal.classList.remove('hidden');
            }

            function fecharModal() {
                modal.classList.add('hidden');
                conteudoEdicao.innerHTML = '';
            }

            function carregarFormularioEdicao(secao) {
                // Aqui você carregaria o formulário específico para cada seção
                // Por enquanto, vamos mostrar um exemplo genérico

                const formularios = {
                    'informacoes': `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                        <input type="text" name="titulo" value="{{ $planejamento->titulo }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="descricao" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ $planejamento->descricao }}</textarea>
                    </div>
                </div>
            `,
                    'configuracao': `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modalidade de Ensino</label>
                        <select name="modalidade_ensino" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="presencial">Presencial</option>
                            <option value="ead">EAD</option>
                            <option value="hibrido">Híbrido</option>
                        </select>
                    </div>
                </div>
            `
                };

                conteudoEdicao.innerHTML = formularios[secao] ||
                    '<p class="text-gray-500">Formulário não disponível para esta seção.</p>';
            }

            // Submissão do formulário de edição
            formEdicao.addEventListener('submit', function(e) {
                e.preventDefault();

                // Aqui você implementaria a lógica de salvamento via AJAX
                console.log('Salvando alterações...');

                // Simular salvamento
                setTimeout(() => {
                    fecharModal();
                    // Atualizar a seção na página
                    // location.reload(); // ou atualizar apenas a seção específica
                }, 1000);
            });

            // Botões de ação do header
            // Ajuste: manter rota do wizard para continuar planejamento
            document.getElementById('btn-editar')?.addEventListener('click', function() {
                window.location.href =
                    '{{ route('planejamentos.wizard', ['edit' => $planejamento->id]) }}';
            });

            document.getElementById('btn-exportar')?.addEventListener('click', function() {
                // Implementar exportação
                window.alertSystem?.info('Funcionalidade de exportação será implementada em breve.');
            });

            document.getElementById('btn-compartilhar')?.addEventListener('click', function() {
                // Implementar compartilhamento
                window.alertSystem?.info('Funcionalidade de compartilhamento será implementada em breve.');
            });

            // Aprovação/Rejeição (somente coordenador/admin e status finalizado)
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            function getFriendlyErrorMessage(action, response, result) {
                const raw = (result && (result.error || result.message)) || '';
                const status = response?.status || 0;
                // Mensagens específicas por status
                if (status === 403) {
                    // Pode ser falta de permissão ou não vinculado à sala
                    return raw ||
                        'Acesso negado. Verifique se você é administrador ou coordenador vinculado à sala desta turma.';
                }
                if (status === 404) {
                    // Planejamento detalhado não existe
                    return 'Este planejamento não possui o detalhado concluído. Peça ao professor para completar todas as etapas no detalhado (wizard) e finalizar antes de ' +
                        action + ' o planejamento.';
                }
                if (status === 422) {
                    // Status não permitido ou validação
                    if (/finalizados?/i.test(raw) || /revis[aã]o/i.test(raw)) {
                        return 'O planejamento precisa estar em Revisão ou Finalizado para esta ação.';
                    }
                    return raw || 'Dados inválidos para esta ação. Revise as informações e tente novamente.';
                }
                if (status >= 500) {
                    return raw || 'Erro interno ao processar a ação. Tente novamente em alguns minutos.';
                }
                // Fallback genérico
                return raw ||
                    `Erro ao ${action} planejamento. Verifique permissões e que o status esteja em Revisão ou Finalizado.`;
            }

            // Abrir modal de aprovação
            document.getElementById('btn-aprovar')?.addEventListener('click', function() {
                showModal('modal-aprovar-planejamento');
            });

            // Ações do modal de aprovação
            document.getElementById('btn-cancelar-aprovacao')?.addEventListener('click', function() {
                closeModal('modal-aprovar-planejamento');
            });

            document.getElementById('btn-confirmar-aprovacao')?.addEventListener('click', async function() {
                const campo = document.getElementById('observacoes-aprovacao');
                const observacoes = (campo?.value || '').trim();
                try {
                    const response = await fetch(
                        `{{ route('planejamentos.aprovar', $planejamento) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: JSON.stringify({
                                observacoes_aprovacao: observacoes || null
                            })
                        });
                    let result = {};
                    const isJson = response.headers.get('content-type')?.includes('application/json');
                    if (isJson) {
                        result = await response.json();
                    }
                    if (response.ok) {
                        closeModal('modal-aprovar-planejamento');
                        window.alertSystem?.success('Planejamento aprovado com sucesso!');
                        location.reload();
                    } else {
                        const msg = getFriendlyErrorMessage('aprovar', response, result);
                        window.alertSystem?.error(msg);
                    }
                } catch (e) {
                    console.error(e);
                    window.alertSystem?.error('Erro de conexão ao aprovar');
                }
            });

            // Abrir modal de rejeição
            document.getElementById('btn-rejeitar')?.addEventListener('click', function() {
                showModal('modal-rejeitar-planejamento');
            });

            // Ações do modal de rejeição
            document.getElementById('btn-cancelar-rejeicao')?.addEventListener('click', function() {
                closeModal('modal-rejeitar-planejamento');
            });
            document.getElementById('btn-confirmar-rejeicao')?.addEventListener('click', async function() {
                const campo = document.getElementById('observacoes-rejeicao');
                const motivo = (campo?.value || '').trim() || 'Nenhuma observação';
                try {
                    const response = await fetch(
                        `{{ route('planejamentos.rejeitar', $planejamento) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            body: JSON.stringify({
                                observacoes_aprovacao: motivo
                            })
                        });
                    let result = {};
                    const isJson = response.headers.get('content-type')?.includes('application/json');
                    if (isJson) {
                        result = await response.json();
                    }
                    if (response.ok) {
                        closeModal('modal-rejeitar-planejamento');
                        window.alertSystem?.success('Planejamento rejeitado com sucesso!');
                        location.reload();
                    } else {
                        const msg = getFriendlyErrorMessage('rejeitar', response, result);
                        window.alertSystem?.error(msg);
                    }
                } catch (e) {
                    console.error(e);
                    window.alertSystem?.error('Erro de conexão ao rejeitar');
                }
            });
        });
    </script>
@endpush
