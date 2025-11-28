@extends('layouts.app')

@section('title', 'Gerar Escalas Automáticas')

@section('content')
    <div class="w-full mx-auto">
        <!-- Breadcrumbs -->
        <x-breadcrumbs :items="[
            ['title' => 'Dashboard', 'url' => route('dashboard')],
            ['title' => 'Funcionários', 'url' => route('funcionarios.index')],
            ['title' => 'Templates', 'url' => route('funcionarios.templates.index', request('funcionario_id') ?: 1)],
            ['title' => 'Gerar Escalas'],
        ]" />

        <x-card>
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Gerar Escalas Automáticas</h2>
                    <p class="mt-1 text-sm text-gray-600">Configure o período e os parâmetros para geração automática de
                        escalas</p>
                </div>
                <x-button href="{{ route('funcionarios.templates.index', request('funcionario_id') ?: 1) }}"
                    color="secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </x-button>
            </div>

            <form method="POST" action="{{ route('templates.gerar-escalas', request('funcionario_id') ?: 1) }}"
                id="formGerarEscalas">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Seleção de Funcionário e Template -->
                    <div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-user-check mr-2"></i>Seleção de Template
                            </h3>

                            <div class="space-y-3">
                                <div>
                                    <label for="funcionario_id"
                                        class="block text-sm font-medium text-gray-700 mb-1">Funcionário <span
                                            class="text-red-500">*</span></label>
                                    <select name="funcionario_id" id="funcionario_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('funcionario_id') border-red-500 @enderror"
                                        required>
                                        <option value="">Selecione um funcionário</option>
                                        @foreach ($funcionarios as $funcionario)
                                            <option value="{{ $funcionario->id }}"
                                                {{ old('funcionario_id', request('funcionario_id')) == $funcionario->id ? 'selected' : '' }}
                                                data-templates='@json($funcionario->templates->where('ativo', true)->values())'>
                                                {{ $funcionario->nome }} - {{ $funcionario->cargo->nome ?? 'Sem cargo' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('funcionario_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="template_id" class="block text-sm font-medium text-gray-700 mb-1">Template
                                        <span class="text-red-500">*</span></label>
                                    <select name="template_id" id="template_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('template_id') border-red-500 @enderror"
                                        required disabled>
                                        <option value="">Primeiro selecione um funcionário</option>
                                    </select>
                                    @error('template_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">Apenas templates ativos são exibidos</p>
                                </div>

                                <!-- Preview do Template Selecionado -->
                                <div id="templatePreview" class="hidden">
                                    <div class="border-t border-gray-200 mt-4 pt-4">
                                        <h6 class="text-sm font-medium text-gray-500 mb-3">Preview do Template</h6>
                                        <div id="templateInfo"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuração do Período -->
                    <div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-calendar-alt mr-2"></i>Configuração do Período
                            </h3>

                            <div class="space-y-3">
                                <div>
                                    <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">Data de
                                        Início <span class="text-red-500">*</span></label>
                                    <input type="date" name="data_inicio" id="data_inicio"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('data_inicio') border-red-500 @enderror"
                                        value="{{ old('data_inicio', now()->startOfMonth()->format('Y-m-d')) }}" required>
                                    @error('data_inicio')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">Data de Fim
                                        <span class="text-red-500">*</span></label>
                                    <input type="date" name="data_fim" id="data_fim"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('data_fim') border-red-500 @enderror"
                                        value="{{ old('data_fim', now()->endOfMonth()->format('Y-m-d')) }}" required>
                                    @error('data_fim')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Botões de Período Rápido -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Períodos Rápidos</label>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button"
                                            class="px-3 py-1 text-sm border border-indigo-300 text-indigo-600 rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            onclick="setPeriodo('mes-atual')">
                                            Mês Atual
                                        </button>
                                        <button type="button"
                                            class="px-3 py-1 text-sm border border-indigo-300 text-indigo-600 rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            onclick="setPeriodo('proximo-mes')">
                                            Próximo Mês
                                        </button>
                                        <button type="button"
                                            class="px-3 py-1 text-sm border border-indigo-300 text-indigo-600 rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            onclick="setPeriodo('trimestre')">
                                            Trimestre
                                        </button>
                                        <button type="button"
                                            class="px-3 py-1 text-sm border border-indigo-300 text-indigo-600 rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                            onclick="setPeriodo('anual')">
                                            Anual
                                        </button>
                                    </div>
                                </div>

                                <!-- Resumo do Período -->
                                <div id="periodoResumo" class="bg-blue-50 border border-blue-200 rounded-md p-3 hidden">
                                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                                    <span id="periodoTexto" class="text-blue-800"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Opções de Geração -->
                    <div class="lg:col-span-2">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <i class="fas fa-cogs mr-2"></i>Opções de Geração
                                </h3>
                                <button type="button"
                                    class="px-3 py-1 text-sm border border-indigo-300 text-indigo-600 rounded-md hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    onclick="toggleOpcoesAvancadas()">
                                    <i class="fas fa-cog mr-1"></i> Opções Avançadas
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="flex items-start">
                                        <input type="checkbox" name="sobrescrever_existentes" id="sobrescrever_existentes"
                                            value="1"
                                            class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            {{ old('sobrescrever_existentes') ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <label for="sobrescrever_existentes" class="text-sm font-medium text-gray-700">
                                                Sobrescrever escalas existentes
                                            </label>
                                            <p class="text-xs text-gray-500">Se marcado, escalas já existentes no período
                                                serão substituídas</p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex items-start">
                                        <input type="checkbox" name="incluir_feriados" id="incluir_feriados"
                                            value="1"
                                            class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            {{ old('incluir_feriados', true) ? 'checked' : '' }}>
                                        <div class="ml-3">
                                            <label for="incluir_feriados" class="text-sm font-medium text-gray-700">
                                                Incluir feriados
                                            </label>
                                            <p class="text-xs text-gray-500">Se marcado, escalas serão geradas mesmo em
                                                feriados</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Opções Avançadas (Colapsável) -->
                            <div class="hidden" id="opcoesAvancadas">
                                <div class="border-t border-gray-200 mt-4 pt-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="observacoes_padrao"
                                                class="block text-sm font-medium text-gray-700 mb-1">Observações
                                                Padrão</label>
                                            <textarea name="observacoes_padrao" id="observacoes_padrao"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 @error('observacoes_padrao') border-red-500 @enderror"
                                                rows="3" placeholder="Observações que serão adicionadas a todas as escalas geradas">{{ old('observacoes_padrao') }}</textarea>
                                            @error('observacoes_padrao')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-3">Notificações</label>
                                            <div class="space-y-3">
                                                <div class="flex items-start">
                                                    <input type="checkbox" name="notificar_funcionario"
                                                        id="notificar_funcionario" value="1"
                                                        class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                        {{ old('notificar_funcionario', true) ? 'checked' : '' }}>
                                                    <label for="notificar_funcionario" class="ml-3 text-sm text-gray-700">
                                                        Notificar funcionário por email
                                                    </label>
                                                </div>

                                                <div class="flex items-start">
                                                    <input type="checkbox" name="notificar_sistema"
                                                        id="notificar_sistema" value="1"
                                                        class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                        {{ old('notificar_sistema', true) ? 'checked' : '' }}>
                                                    <div class="flex flex-col">
                                                        <label for="notificar_sistema" class="ml-3 text-sm text-gray-700">
                                                            Enviar notificação pelo sistema
                                                        </label>
                                                        <p class="ml-3 text-xs text-gray-500">Notificação interna do
                                                            sistema para o funcionário</p>
                                                    </div>
                                                </div>

                                                <div class="flex items-start">
                                                    <input type="checkbox" name="enviar_relatorio" id="enviar_relatorio"
                                                        value="1"
                                                        class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                        {{ old('enviar_relatorio') ? 'checked' : '' }}>
                                                    <label for="enviar_relatorio" class="ml-3 text-sm text-gray-700">
                                                        Enviar relatório de geração
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumo da Geração -->
                    <div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-chart-bar mr-2"></i>Resumo
                            </h3>

                            <div id="resumoGeracao">
                                <div class="text-center text-gray-500 py-8">
                                    <i class="fas fa-info-circle text-4xl mb-3"></i>
                                    <p class="mb-0">Selecione um funcionário e template para ver o resumo</p>
                                </div>
                            </div>

                            <div class="space-y-3 mt-4">
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    id="btnGerar" disabled>
                                    <i class="fas fa-calendar-plus mr-2"></i> Gerar Escalas
                                </button>

                                <button type="button"
                                    class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    onclick="previewEscalas()" id="btnPreview" disabled>
                                    <i class="fas fa-eye mr-2"></i> Visualizar Preview
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Modal de Preview -->
            <div id="modalPreview"
                class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
                    <div class="flex justify-between items-center pb-3">
                        <h3 class="text-lg font-bold text-gray-900">Preview das Escalas</h3>
                        <button type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                            onclick="fecharModal()">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="border-t border-gray-200 pt-4">
                        <div id="previewContent">
                            <div class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600">
                                </div>
                                <p class="mt-2 text-gray-600">Gerando preview...</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            onclick="fecharModal()">Cancelar</button>
                        <button type="button"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            onclick="confirmarGeracao()">Confirmar Geração</button>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    @push('scripts')
        <script>
            // Variáveis globais
            let funcionarios = @json($funcionarios->keyBy('id'));
            let templateSelecionado = null;

            // Inicialização
            document.addEventListener('DOMContentLoaded', function() {
                // Pré-seleciona funcionário se estiver na URL
                const funcionarioIdFromUrl = '{{ request('funcionario_id') }}';
                if (funcionarioIdFromUrl) {
                    document.getElementById('funcionario_id').value = funcionarioIdFromUrl;
                }

                // Se há funcionário pré-selecionado, carrega os templates
                const funcionarioId = document.getElementById('funcionario_id').value;
                if (funcionarioId) {
                    carregarTemplates(funcionarioId);

                    // Se há template pré-selecionado, aguarda carregar templates e depois seleciona
                    const templateId = '{{ request('template_id') }}';
                    if (templateId) {
                        setTimeout(() => {
                            document.getElementById('template_id').value = templateId;
                            carregarTemplatePreview(templateId);
                        }, 100);
                    }
                }

                // Atualiza resumo do período
                atualizarResumo();
            });

            // Event listeners
            document.getElementById('funcionario_id').addEventListener('change', function() {
                const funcionarioId = this.value;
                carregarTemplates(funcionarioId);
                atualizarResumo();
            });

            document.getElementById('template_id').addEventListener('change', function() {
                const templateId = this.value;
                carregarTemplatePreview(templateId);
                atualizarResumo();
            });

            document.getElementById('data_inicio').addEventListener('change', atualizarResumo);
            document.getElementById('data_fim').addEventListener('change', atualizarResumo);

            // Função para toggle das opções avançadas
            function toggleOpcoesAvancadas() {
                const opcoes = document.getElementById('opcoesAvancadas');
                opcoes.classList.toggle('hidden');
            }

            // Funções
            function carregarTemplates(funcionarioId) {
                const templateSelect = document.getElementById('template_id');
                templateSelect.innerHTML = '<option value="">Carregando...</option>';
                templateSelect.disabled = true;

                if (!funcionarioId) {
                    templateSelect.innerHTML = '<option value="">Primeiro selecione um funcionário</option>';
                    return;
                }

                const funcionario = funcionarios[funcionarioId];
                const templates = JSON.parse(document.querySelector(`option[value="${funcionarioId}"]`).dataset.templates);

                templateSelect.innerHTML = '<option value="">Selecione um template</option>';

                if (templates.length === 0) {
                    templateSelect.innerHTML += '<option value="" disabled>Nenhum template ativo encontrado</option>';
                } else {
                    templates.forEach(template => {
                        const option = document.createElement('option');
                        option.value = template.id;
                        option.textContent = template.nome_template;
                        option.dataset.template = JSON.stringify(template);
                        templateSelect.appendChild(option);
                    });
                }

                templateSelect.disabled = false;

                // Limpa preview anterior
                document.getElementById('templatePreview').classList.add('hidden');
                templateSelecionado = null;
            }

            function carregarTemplatePreview(templateId) {
                const templateSelect = document.getElementById('template_id');
                const selectedOption = templateSelect.querySelector(`option[value="${templateId}"]`);

                if (!selectedOption || !selectedOption.dataset.template) {
                    document.getElementById('templatePreview').classList.add('d-none');
                    templateSelecionado = null;
                    return;
                }

                templateSelecionado = JSON.parse(selectedOption.dataset.template);

                // Monta preview do template
                const diasSemana = {
                    'segunda': 'Seg',
                    'terca': 'Ter',
                    'quarta': 'Qua',
                    'quinta': 'Qui',
                    'sexta': 'Sex',
                    'sabado': 'Sáb',
                    'domingo': 'Dom'
                };

                let previewHtml = '<div class="grid grid-cols-4 md:grid-cols-7 gap-1">';

                Object.keys(diasSemana).forEach(dia => {
                    const ativo = templateSelecionado[dia + '_inicio'] && templateSelecionado[dia + '_fim'];
                    const entrada = templateSelecionado[dia + '_inicio'];
                    const saida = templateSelecionado[dia + '_fim'];

                    previewHtml += `
            <div class="${ativo ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200'} border rounded-md p-2 text-center">
                <div class="text-xs font-semibold text-gray-700">${diasSemana[dia]}</div>
                ${ativo ? `<div class="text-xs text-indigo-600">${entrada || '--'} às ${saida || '--'}</div>` : '<div class="text-xs text-gray-400">--</div>'}
            </div>
        `;
                });

                previewHtml += '</div>';

                document.getElementById('templateInfo').innerHTML = previewHtml;
                document.getElementById('templatePreview').classList.remove('hidden');
            }

            function setPeriodo(tipo) {
                const hoje = new Date();
                let inicio, fim;

                switch (tipo) {
                    case 'mes-atual':
                        inicio = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
                        fim = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
                        break;
                    case 'proximo-mes':
                        inicio = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 1);
                        fim = new Date(hoje.getFullYear(), hoje.getMonth() + 2, 0);
                        break;
                    case 'trimestre':
                        const trimestre = Math.floor(hoje.getMonth() / 3);
                        inicio = new Date(hoje.getFullYear(), trimestre * 3, 1);
                        fim = new Date(hoje.getFullYear(), (trimestre + 1) * 3, 0);
                        break;
                    case 'anual':
                        inicio = new Date(hoje.getFullYear(), 0, 1);
                        fim = new Date(hoje.getFullYear(), 11, 31);
                        break;
                }

                document.getElementById('data_inicio').value = inicio.toISOString().split('T')[0];
                document.getElementById('data_fim').value = fim.toISOString().split('T')[0];

                atualizarResumo();
            }

            function atualizarResumo() {
                const funcionarioId = document.getElementById('funcionario_id').value;
                const templateId = document.getElementById('template_id').value;
                const dataInicio = document.getElementById('data_inicio').value;
                const dataFim = document.getElementById('data_fim').value;

                // Atualiza resumo do período
                if (dataInicio && dataFim) {
                    const inicio = new Date(dataInicio);
                    const fim = new Date(dataFim);
                    const dias = Math.ceil((fim - inicio) / (1000 * 60 * 60 * 24)) + 1;

                    document.getElementById('periodoTexto').textContent =
                        `Período de ${dias} dias (${inicio.toLocaleDateString('pt-BR')} a ${fim.toLocaleDateString('pt-BR')})`;
                    document.getElementById('periodoResumo').classList.remove('hidden');
                } else {
                    document.getElementById('periodoResumo').classList.add('hidden');
                }

                // Atualiza resumo da geração
                const resumoDiv = document.getElementById('resumoGeracao');
                const btnGerar = document.getElementById('btnGerar');
                const btnPreview = document.getElementById('btnPreview');

                if (funcionarioId && templateId && dataInicio && dataFim && templateSelecionado) {
                    // Calcula estatísticas
                    const inicio = new Date(dataInicio);
                    const fim = new Date(dataFim);
                    const totalDias = Math.ceil((fim - inicio) / (1000 * 60 * 60 * 24)) + 1;

                    let diasTrabalho = 0;
                    const diasSemana = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];

                    for (let d = new Date(inicio); d <= fim; d.setDate(d.getDate() + 1)) {
                        const diaSemana = diasSemana[d.getDay()];
                        if (templateSelecionado[diaSemana + '_inicio'] && templateSelecionado[diaSemana + '_fim']) {
                            diasTrabalho++;
                        }
                    }

                    const funcionario = funcionarios[funcionarioId];

                    resumoDiv.innerHTML = `
            <div class="space-y-3">
                <div>
                    <div class="text-sm font-medium text-gray-700">Funcionário:</div>
                    <div class="text-indigo-600">${funcionario.nome}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Template:</div>
                    <div class="text-indigo-600">${templateSelecionado.nome_template}</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Período:</div>
                    <div class="text-gray-600">${totalDias} dias</div>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700">Escalas a gerar:</div>
                    <div class="text-green-600 font-semibold">${diasTrabalho} escalas</div>
                </div>
            </div>
        `;

                    btnGerar.disabled = false;
                    btnPreview.disabled = false;
                } else {
                    resumoDiv.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-info-circle text-4xl mb-3"></i>
                <p class="mb-0">Selecione um funcionário e template para ver o resumo</p>
            </div>
        `;

                    btnGerar.disabled = true;
                    btnPreview.disabled = true;
                }
            }

            function previewEscalas() {
                const formData = new FormData(document.getElementById('formGerarEscalas'));
                formData.append('preview', '1');

                // Mostra modal
                document.getElementById('modalPreview').classList.remove('hidden');

                // Carrega preview via AJAX
                const funcionarioId = document.getElementById('funcionario_id').value || {{ request('funcionario_id') ?: 1 }};
                fetch(`{{ route('templates.gerar-escalas', ':funcionario') }}`.replace(':funcionario', funcionarioId), {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('previewContent').innerHTML = data.preview;
                        } else {
                            document.getElementById('previewContent').innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
                        <div class="text-red-800">${data.message || 'Erro ao gerar preview'}</div>
                    </div>
                </div>
            `;
                        }
                    })
                    .catch(error => {
                        document.getElementById('previewContent').innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
                    <div class="text-red-800">Erro ao carregar preview: ${error.message}</div>
                </div>
            </div>
        `;
                    });
            }

            function fecharModal() {
                document.getElementById('modalPreview').classList.add('hidden');
            }

            function confirmarGeracao() {
                // Fecha modal e submete formulário
                fecharModal();
                document.getElementById('formGerarEscalas').submit();
            }
        </script>
    @endpush
@endsection