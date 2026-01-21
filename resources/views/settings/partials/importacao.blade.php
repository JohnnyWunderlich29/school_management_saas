<div class="space-y-6" x-data="importCenter()">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Central de Importação</h2>
            <p class="text-sm text-gray-600 mt-1">Importe dados de alunos de forma massiva via arquivo CSV.</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Seção Alunos -->
                <div
                    class="border rounded-xl p-6 hover:border-indigo-300 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-start mb-4">
                            <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600 mr-4">
                                <i class="fas fa-user-graduate text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Alunos</h3>
                                <p class="text-sm text-gray-500">Cadastre múltiplos alunos de uma só vez vinculando-os a
                                    salas e turmas.</p>
                            </div>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-xs font-semibold text-gray-700 uppercase mb-2">Instruções:</h4>
                                <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                                    <li>Separador ponto-e-vírgula (;)</li>
                                    <li>Data: <span class="font-bold">DD/MM/YYYY</span></li>
                                    <li>Obrigatórios: nome, sobrenome, data_nasc.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('settings.importacao.template', ['type' => 'alunos']) }}"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                            <i class="fas fa-download mr-2"></i> Modelo
                        </a>
                        <label
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 cursor-pointer transition">
                            <i class="fas fa-upload mr-2"></i> Subir
                            <input type="file" class="hidden" accept=".csv"
                                @change="handleFileUpload($event, 'alunos')">
                        </label>
                    </div>
                </div>

                <!-- Seção Responsáveis -->
                <div
                    class="border rounded-xl p-6 hover:border-indigo-300 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-start mb-4">
                            <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600 mr-4">
                                <i class="fas fa-users text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Responsáveis</h3>
                                <p class="text-sm text-gray-500">Importe os responsáveis pelos alunos para posterior
                                    vinculação.</p>
                            </div>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-xs font-semibold text-gray-700 uppercase mb-2">Instruções:</h4>
                                <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                                    <li>Separador ponto-e-vírgula (;)</li>
                                    <li>Obrigatórios: nome, sobrenome, cpf, tel, parentesco</li>
                                    <li>CPF único por escola</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('settings.importacao.template', ['type' => 'responsaveis']) }}"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                            <i class="fas fa-download mr-2"></i> Modelo
                        </a>
                        <label
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 cursor-pointer transition">
                            <i class="fas fa-upload mr-2"></i> Subir
                            <input type="file" class="hidden" accept=".csv"
                                @change="handleFileUpload($event, 'responsaveis')">
                        </label>
                    </div>
                </div>

                <!-- Seção Despesas -->
                <div
                    class="border rounded-xl p-6 hover:border-indigo-300 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-start mb-4">
                            <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600 mr-4">
                                <i class="fas fa-receipt text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Despesas</h3>
                                <p class="text-sm text-gray-500">Importe despesas avulsas ou configure recorrências
                                    automáticas.</p>
                            </div>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h4 class="text-xs font-semibold text-gray-700 uppercase mb-2">Instruções:</h4>
                                <ul class="text-xs text-gray-600 space-y-1 list-disc list-inside">
                                    <li>Separador ponto-e-vírgula (;)</li>
                                    <li>Obrigatórios: descricao, data, valor</li>
                                    <li>Recorrência: Informe 'Sim' e a frequência (mensal, etc)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('settings.importacao.template', ['type' => 'despesas']) }}"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                            <i class="fas fa-download mr-2"></i> Modelo
                        </a>
                        <label
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 cursor-pointer transition">
                            <i class="fas fa-upload mr-2"></i> Subir
                            <input type="file" class="hidden" accept=".csv"
                                @change="handleFileUpload($event, 'despesas')">
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Preview -->
    <x-modal name="preview-import-modal" title="Pré-visualização da Importação" maxWidth="w-11/12 md:w-3/4 lg:w-4/5">
        <div class="p-1">
            <p class="text-sm text-gray-600 mb-4">Confira os dados abaixo antes de confirmar a importação para o
                sistema.</p>

            <!-- Área de Erros no Modal -->
            <div x-show="validationErrors.length > 0" class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    <h4 class="text-sm font-bold text-red-800">Erros encontrados no arquivo:</h4>
                </div>
                <div class="max-h-40 overflow-y-auto pr-2 custom-scrollbar">
                    <ul class="text-xs text-red-700 list-disc list-inside space-y-1">
                        <template x-for="(error, i) in validationErrors" :key="i">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>
            </div>

            <div class="overflow-x-auto border rounded-lg max-h-96">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <template x-for="header in previewHeaders" :key="header">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    x-text="header"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="(row, index) in previewData" :key="index">
                            <tr class="hover:bg-gray-50">
                                <template x-for="header in previewHeaders" :key="header">
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
                                        <span x-text="row[header] || '-'"></span>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="previewData.length === 0" class="py-12 text-center">
                <p class="text-gray-500">Nenhum dado encontrado no arquivo.</p>
            </div>
        </div>

        <x-slot name="footer">
            <x-button color="secondary" onclick="closeModal('preview-import-modal')">Cancelar</x-button>
            <x-button color="primary" @click="confirmImport()" x-bind:disabled="isImporting">
                <span x-show="!isImporting"><i class="fas fa-check-circle mr-2"></i> Confirmar Importação</span>
                <span x-show="isImporting"><i class="fas fa-spinner fa-spin mr-2"></i> Processando...</span>
            </x-button>
        </x-slot>
    </x-modal>
</div>

<script>
    function importCenter() {
        return {
            previewData: [],
            previewHeaders: [],
            validationErrors: [],
            isImporting: false,
            importType: 'alunos',

            async handleFileUpload(event, type) {
                const file = event.target.files[0];
                if (!file) return;

                this.importType = type;
                const formData = new FormData();
                formData.append('file', file);

                this.validationErrors = []; // Reset errors

                const previewRoutes = {
                    'alunos': '{{ route('settings.importacao.alunos.preview') }}',
                    'responsaveis': '{{ route('settings.importacao.responsaveis.preview') }}',
                    'despesas': '{{ route('settings.importacao.despesas.preview') }}'
                };

                try {
                    const response = await fetch(previewRoutes[type], {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    const result = await response.json().catch(() => ({
                        success: false,
                        message: 'Erro ao processar resposta do servidor.'
                    }));

                    if (response.ok && result.success) {
                        this.previewData = result.data;
                        if (this.previewData.length > 0) {
                            this.previewHeaders = Object.keys(this.previewData[0]);
                        }
                        showModal('preview-import-modal');
                    } else {
                        if (result.errors && result.errors.length > 0) {
                            this.validationErrors = result.errors;
                            showModal('preview-import-modal');
                        } else {
                            window.alertSystem.error(result.message || 'Erro ao processar arquivo.');
                        }
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    window.alertSystem.error('Erro de conexão ou erro inesperado ao carregar o arquivo.');
                } finally {
                    event.target.value = ''; // Reset input
                }
            },

            async confirmImport() {
                if (this.previewData.length === 0) return;

                this.isImporting = true;
                this.validationErrors = []; // Reset errors before import

                const importRoutes = {
                    'alunos': '{{ route('settings.importacao.alunos.import') }}',
                    'responsaveis': '{{ route('settings.importacao.responsaveis.import') }}',
                    'despesas': '{{ route('settings.importacao.despesas.import') }}'
                };

                let bodyData = {};
                if (this.importType === 'alunos') {
                    bodyData = {
                        students: this.previewData
                    };
                } else if (this.importType === 'responsaveis') {
                    bodyData = {
                        responsaveis: this.previewData
                    };
                } else if (this.importType === 'despesas') {
                    bodyData = {
                        despesas: this.previewData
                    };
                }

                try {
                    const response = await fetch(importRoutes[this.importType], {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(bodyData)
                    });

                    const result = await response.json().catch(() => ({
                        success: false,
                        message: 'Erro ao processar resposta do servidor.'
                    }));

                    if (response.ok && result.success) {
                        closeModal('preview-import-modal');
                        window.alertSystem.success(result.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        if (result.errors && result.errors.length > 0) {
                            this.validationErrors = result.errors;
                            window.alertSystem.error(result.message || 'Existem erros que impedem a importação.');
                        } else {
                            window.alertSystem.error(result.message || 'Erro durante a importação.');
                        }
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    window.alertSystem.error('Erro de conexão com o servidor durante a importação.');
                } finally {
                    this.isImporting = false;
                }
            }
        }
    }
</script>
