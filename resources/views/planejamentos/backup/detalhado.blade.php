@extends('layouts.app')

@section('title', 'Planejamento Detalhado - BNCC')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="planejamentoDetalhado()">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-clipboard-list text-blue-600 mr-3"></i>
                    Planejamento Detalhado - BNCC
                </h1>
                <p class="text-gray-600 mt-1">
                    Período: {{ $planejamento->data_inicio->format('d/m/Y') }} a {{ $planejamento->data_fim->format('d/m/Y') }}
                    ({{ $planejamento->numero_dias }} {{ $planejamento->numero_dias == 1 ? 'dia' : 'dias' }})
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="px-3 py-1 rounded-full text-sm font-medium"
                      :class="{
                          'bg-yellow-100 text-yellow-800': status === 'rascunho',
                          'bg-blue-100 text-blue-800': status === 'finalizado',
                          'bg-green-100 text-green-800': status === 'aprovado',
                          'bg-red-100 text-red-800': status === 'reprovado'
                      }"
                      x-text="getStatusText(status)">
                </span>
                <button @click="autoSave()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
                        :disabled="saving"
                        :class="{ 'opacity-50 cursor-not-allowed': saving }">
                    <i class="fas fa-save mr-2"></i>
                    <span x-text="saving ? 'Salvando...' : 'Salvar'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Formulário -->
    <form @submit.prevent="submitForm()">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Coluna Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Campos de Experiência -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-puzzle-piece text-purple-600 mr-2"></i>
                        Campos de Experiência
                    </h2>
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($camposExperiencia as $campo)
                        <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="checkbox" 
                                   value="{{ $campo->id }}"
                                   x-model="camposExperienciaSelecionados"
                                   @change="loadObjetivosAprendizagem()"
                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $campo->nome }}</div>
                                <div class="text-sm text-gray-600 mt-1">{{ $campo->descricao }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Saberes e Conhecimentos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-lightbulb text-yellow-600 mr-2"></i>
                        Saberes e Conhecimentos
                    </h2>
                    <textarea x-model="saberesConhecimentos"
                              @input="autoSave()"
                              placeholder="Descreva os saberes e conhecimentos que serão trabalhados..."
                              class="w-full h-32 px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                </div>

                <!-- Objetivos de Aprendizagem -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" x-show="objetivosDisponiveis.length > 0">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-target text-green-600 mr-2"></i>
                        Objetivos de Aprendizagem e Desenvolvimento
                    </h2>
                    <div class="space-y-4">
                        <template x-for="campo in objetivosDisponiveis" :key="campo.id">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="font-medium text-gray-900 mb-3" x-text="campo.nome"></h3>
                                <div class="space-y-2">
                                    <template x-for="objetivo in campo.objetivos" :key="objetivo.id">
                                        <label class="flex items-start space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                            <input type="checkbox" 
                                                   :value="objetivo.id"
                                                   x-model="objetivosAprendizagemSelecionados"
                                                   @change="autoSave()"
                                                   class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900" x-text="objetivo.codigo"></div>
                                                <div class="text-sm text-gray-600" x-text="objetivo.descricao"></div>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Encaminhamentos Metodológicos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-route text-blue-600 mr-2"></i>
                        Encaminhamentos Metodológicos
                    </h2>
                    <textarea x-model="encaminhamentosMetodologicos"
                              @input="autoSave()"
                              placeholder="Descreva as estratégias e metodologias que serão utilizadas..."
                              class="w-full h-32 px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                </div>

                <!-- Recursos -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-tools text-orange-600 mr-2"></i>
                        Recursos
                    </h2>
                    <textarea x-model="recursos"
                              @input="autoSave()"
                              placeholder="Liste os recursos materiais e didáticos necessários..."
                              class="w-full h-32 px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Informações do Planejamento -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Modalidade:</span>
                            <span class="text-gray-600">{{ $planejamento->modalidade_formatada }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Turma:</span>
                            <span class="text-gray-600">{{ $planejamento->turma->nome ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Professor:</span>
                            <span class="text-gray-600">{{ $planejamento->user->name }}</span>
                        </div>
                    </div>
                </div>

                <!-- Registros e Anotações -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-sticky-note text-pink-600 mr-2"></i>
                        Registros e Anotações
                    </h3>
                    <textarea x-model="registrosAnotacoes"
                              @input="autoSave()"
                              placeholder="Faça suas anotações e registros..."
                              class="w-full h-40 px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none text-sm"></textarea>
                </div>

                <!-- Ações -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Status e Ações</h3>
                    
                    <!-- Status do Planejamento -->
                    <div class="mb-4 p-3 rounded-md" :class="{
                        'bg-yellow-50 border border-yellow-200': status === 'rascunho',
                        'bg-blue-50 border border-blue-200': status === 'finalizado',
                        'bg-green-50 border border-green-200': status === 'aprovado'
                    }">
                        <div class="flex items-center">
                            <i class="fas" :class="{
                                'fa-edit text-yellow-600': status === 'rascunho',
                                'fa-clock text-blue-600': status === 'finalizado',
                                'fa-check-circle text-green-600': status === 'aprovado'
                            }"></i>
                            <span class="ml-2 text-sm font-medium" :class="{
                                'text-yellow-800': status === 'rascunho',
                                'text-blue-800': status === 'finalizado',
                                'text-green-800': status === 'aprovado'
                            }" x-text="getStatusText(status)"></span>
                        </div>
                        <div x-show="observacoes_aprovacao" class="mt-2 text-sm text-gray-600">
                            <strong>Observações:</strong> <span x-text="observacoes_aprovacao"></span>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <!-- Botão Finalizar (apenas para professores) -->
                        <button type="button" 
                                @click="finalizar()"
                                :disabled="status === 'finalizado' || status === 'aprovado'"
                                class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                            <i class="fas fa-check mr-2"></i>
                            Finalizar Planejamento
                        </button>
                        
                        @if(Auth::user()->isAdminOrCoordinator())
                        <!-- Botões de Aprovação (apenas para coordenadores) -->
                        <div x-show="status === 'finalizado'" class="space-y-2">
                            <button type="button" 
                                    @click="aprovar()"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                <i class="fas fa-thumbs-up mr-2"></i>
                                Aprovar Planejamento
                            </button>
                            <button type="button" 
                                    @click="rejeitar()"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                <i class="fas fa-thumbs-down mr-2"></i>
                                Rejeitar Planejamento
                            </button>
                        </div>
                        @endif
                        
                        <a href="{{ route('planejamentos.index') }}"
                           class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 text-center block">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Voltar à Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function planejamentoDetalhado() {
    return {
        // Estado do formulário
        camposExperienciaSelecionados: @json($planejamentoDetalhado->campos_experiencia_selecionados ?? []),
        saberesConhecimentos: @json($planejamentoDetalhado->saberes_conhecimentos ?? ''),
        objetivosAprendizagemSelecionados: @json($planejamentoDetalhado->objetivos_aprendizagem_selecionados ?? []),
        encaminhamentosMetodologicos: @json($planejamentoDetalhado->encaminhamentos_metodologicos ?? ''),
        recursos: @json($planejamentoDetalhado->recursos ?? ''),
        registrosAnotacoes: @json($planejamentoDetalhado->registros_anotacoes ?? ''),
        status: @json($planejamentoDetalhado->status ?? 'rascunho'),
        observacoes_aprovacao: @json($planejamentoDetalhado->observacoes_aprovacao ?? null),
        
        // Estado da aplicação
        saving: false,
        objetivosDisponiveis: [],
        autoSaveTimeout: null,
        
        // Dados dos objetivos de aprendizagem
        objetivosData: @json($objetivosAprendizagem),
        
        init() {
            this.loadObjetivosAprendizagem();
            
            // Auto-save a cada 30 segundos
            setInterval(() => {
                if (!this.saving) {
                    this.autoSave();
                }
            }, 30000);
        },
        
        loadObjetivosAprendizagem() {
            this.objetivosDisponiveis = [];
            
            this.camposExperienciaSelecionados.forEach(campoId => {
                const objetivos = this.objetivosData[campoId];
                if (objetivos && objetivos.length > 0) {
                    this.objetivosDisponiveis.push({
                        id: campoId,
                        nome: objetivos[0].campo_experiencia.nome,
                        objetivos: objetivos
                    });
                }
            });
        },
        
        async autoSave() {
            if (this.saving) return;
            
            // Debounce
            clearTimeout(this.autoSaveTimeout);
            this.autoSaveTimeout = setTimeout(async () => {
                await this.saveData();
            }, 1000);
        },
        
        async saveData() {
            this.saving = true;
            
            try {
                const formData = {
                    campos_experiencia_selecionados: this.camposExperienciaSelecionados,
                    saberes_conhecimentos: this.saberesConhecimentos,
                    objetivos_aprendizagem_selecionados: this.objetivosAprendizagemSelecionados,
                    encaminhamentos_metodologicos: this.encaminhamentosMetodologicos,
                    recursos: this.recursos,
                    registros_anotacoes: this.registrosAnotacoes,
                    status: this.status
                };
                
                const url = @json($planejamentoDetalhado->id) 
                    ? `{{ route('planejamentos.detalhado.update', $planejamento) }}`
                    : `{{ route('planejamentos.detalhado.store', $planejamento) }}`;
                    
                const method = @json($planejamentoDetalhado->id) ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.error || 'Erro ao salvar');
                }
                
                // Mostrar feedback visual sutil
                this.showSaveSuccess();
                
            } catch (error) {
                console.error('Erro ao salvar:', error);
                this.showSaveError(error.message);
            } finally {
                this.saving = false;
            }
        },
        
        async finalizar() {
            if (confirm('Tem certeza que deseja finalizar este planejamento? Após finalizado, não será possível editá-lo.')) {
                this.status = 'finalizado';
                await this.saveData();
                
                // Mostrar mensagem de sucesso
                window.alertSystem.success('Planejamento finalizado com sucesso!');
            }
        },
        
        async aprovar() {
            const self = this;
            
            window.showConfirmation({
                title: 'Aprovar Planejamento',
                message: 'Tem certeza que deseja aprovar este planejamento? Esta ação não poderá ser desfeita.',
                confirmText: 'Aprovar',
                cancelText: 'Cancelar',
                confirmColor: 'green',
                callback: async function() {
                    try {
                        const response = await fetch(`{{ route('planejamentos.aprovar', $planejamento) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok) {
                            self.status = 'aprovado';
                            self.observacoes_aprovacao = null;
                            
                            window.showConfirmation({
                                title: 'Sucesso',
                                message: 'Planejamento aprovado com sucesso!',
                                confirmText: 'OK',
                                confirmColor: 'green',
                                callback: () => location.reload()
                            });
                        } else {
                            window.showConfirmation({
                                title: 'Erro',
                                message: result.error || result.message || 'Erro ao aprovar planejamento',
                                confirmText: 'OK',
                                confirmColor: 'red'
                            });
                        }
                    } catch (error) {
                        console.error('Erro ao aprovar:', error);
                        window.showConfirmation({
                            title: 'Erro',
                            message: 'Erro de conexão ao aprovar planejamento',
                            confirmText: 'OK',
                            confirmColor: 'red'
                        });
                    }
                }
            });
        },
        
        async rejeitar() {
            const self = this;
            
            window.showConfirmation({
                title: 'Rejeitar Planejamento',
                message: 'Informe o motivo da rejeição do planejamento. O professor poderá fazer as correções necessárias.',
                confirmText: 'Rejeitar',
                cancelText: 'Cancelar',
                confirmColor: 'red',
                showInput: true,
                inputLabel: 'Motivo da rejeição *',
                inputPlaceholder: 'Digite o motivo da rejeição...',
                inputRequired: true,
                callback: async function(observacao) {
                    if (!observacao || !observacao.trim()) {
                        return;
                    }
                    
                    try {
                        const response = await fetch(`{{ route('planejamentos.rejeitar', $planejamento) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                observacoes_aprovacao: observacao.trim()
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok) {
                            self.status = 'rascunho';
                            self.observacoes_aprovacao = observacao.trim();
                            
                            window.showConfirmation({
                                title: 'Sucesso',
                                message: 'Planejamento rejeitado com sucesso! O professor poderá fazer as correções necessárias.',
                                confirmText: 'OK',
                                confirmColor: 'green',
                                callback: () => location.reload()
                            });
                        } else {
                            window.showConfirmation({
                                title: 'Erro',
                                message: result.error || result.message || 'Erro ao rejeitar planejamento',
                                confirmText: 'OK',
                                confirmColor: 'red'
                            });
                        }
                    } catch (error) {
                        console.error('Erro ao rejeitar:', error);
                        window.showConfirmation({
                            title: 'Erro',
                            message: 'Erro de conexão ao rejeitar planejamento',
                            confirmText: 'OK',
                            confirmColor: 'red'
                        });
                    }
                }
            });
        },
        
        getStatusText(status) {
            const statusMap = {
                'rascunho': 'Rascunho',
                'finalizado': 'Finalizado',
                'aprovado': 'Aprovado',
                'reprovado': 'Reprovado'
            };
            return statusMap[status] || status;
        },
        
        showSaveSuccess() {
            // Implementar feedback visual de sucesso
            const button = document.querySelector('[x-text="saving ? \'Salvando...\' : \'Salvar\'"');
            if (button) {
                const originalText = button.textContent;
                button.textContent = 'Salvo!';
                button.classList.add('bg-green-600');
                button.classList.remove('bg-blue-600');
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('bg-green-600');
                    button.classList.add('bg-blue-600');
                }, 2000);
            }
        },
        
        showSaveError(message) {
            window.alertSystem.error('Erro ao salvar: ' + message);
        }
    }
}
</script>

<!-- Incluir o componente de modal de confirmação -->
<x-confirmation-modal />
@endsection