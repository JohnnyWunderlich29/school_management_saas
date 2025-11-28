<div class="modules-section" x-data="modulesManager()" x-init="loadModules()">
    <!-- Header da Seção -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Aplicativos Disponíveis</h2>
            <p class="text-sm text-gray-600 mt-1">Gerencie os módulos da sua escola</p>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Valor Total -->
            <div class="text-right">
                <p class="text-sm text-gray-500">Valor Total Mensal</p>
                <p class="text-lg font-bold text-green-600" x-text="formatCurrency(totalMonthlyValue)">R$ 0,00</p>
            </div>
            <!-- Botão de Atualizar -->
            <button @click="loadModules()" 
                    :disabled="loading"
                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-2 rounded-md transition-colors duration-200 flex items-center">
                <i class="fas fa-sync-alt mr-2" :class="{ 'animate-spin': loading }"></i>
                Atualizar
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="flex items-center justify-center py-12">
        <div class="text-center">
            <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Carregando módulos...</p>
        </div>
    </div>

    <!-- Error State -->
    <div x-show="error && !loading" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
            <i class="fas fa-exclamation-triangle text-red-400 mr-3 mt-0.5"></i>
            <div>
                <h3 class="text-sm font-medium text-red-800">Erro ao carregar módulos</h3>
                <p class="text-sm text-red-700 mt-1" x-text="error"></p>
                <button @click="loadModules()" class="text-sm text-red-600 hover:text-red-500 underline mt-2">
                    Tentar novamente
                </button>
            </div>
        </div>
    </div>

    <!-- Filtros e Categorias -->
    <div x-show="!loading && !error" class="mb-6">
        <div class="flex flex-wrap gap-2">
            <button @click="selectedCategory = 'all'" 
                    :class="selectedCategory === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                    class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                Todos
            </button>
            <template x-for="(categoryName, categoryKey) in categories" :key="categoryKey">
                <button @click="selectedCategory = categoryKey" 
                        :class="selectedCategory === categoryKey ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200"
                        x-text="categoryName">
                </button>
            </template>
        </div>
    </div>

    <!-- Módulos Essenciais -->
    <div x-show="!loading && !error && coreModules.length > 0" class="mb-2">
        <h3 class="text-md font-semibold text-gray-900">Módulos Essenciais</h3>
    </div>
    <div x-show="!loading && !error && coreModules.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <template x-for="module in coreModules" :key="module.id">
            <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200 overflow-hidden">
                <!-- Header do Card -->
                <div class="p-4 border-b border-gray-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl" 
                                     :style="`background-color: ${module.color || '#6B7280'}`">
                                    <i :class="module.icon || 'fas fa-puzzle-piece'"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 truncate" x-text="module.display_name"></h3>
                                <p class="text-sm text-gray-500 mt-1" x-text="module.category_display || 'Geral'"></p>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="`bg-${module.status_color}-100 text-${module.status_color}-800`"
                                  x-text="module.status_description">
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Conteúdo do Card -->
                <div class="p-4">
                    <p class="text-gray-600 text-sm mb-4" x-text="module.description"></p>
                    
                    <!-- Recursos -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Principais recursos:</h4>
                        <ul class="space-y-1">
                            <template x-for="(feature, index) in (module.features || []).slice(0, 3)" :key="index">
                                <li class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                    <span x-text="feature"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                    
                    <!-- Preço -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-2xl font-bold text-gray-900" x-text="module.formatted_price || 'Gratuito'"></span>
                            <span class="text-sm text-gray-500" x-show="module.formatted_price">/mês</span>
                        </div>
                        <div x-show="module.is_core">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Essencial
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Ações -->
                <div class="px-4 pb-4">
                    <template x-if="!module.is_contracted">
                        <button @click="contractModule(module.id)" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                            Contratar Módulo
                        </button>
                    </template>
                    
                    <template x-if="module.is_contracted && !module.is_active">
                        <button @click="toggleModule(module.id)" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                            Ativar Módulo
                        </button>
                    </template>
                </div>
                
                <!-- Footer com informações de contrato -->
                <div x-show="module.is_contracted" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>Contratado em: <span x-text="module.contracted_at"></span></span>
                        <span x-show="module.expires_at">Expira em: <span x-text="module.expires_at"></span></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Outros Módulos -->
    <div x-show="!loading && !error && filteredOtherModules.length > 0" class="mb-2">
        <h3 class="text-md font-semibold text-gray-900">Outros Módulos</h3>
    </div>
    <div x-show="!loading && !error && filteredOtherModules.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="module in filteredOtherModules" :key="module.id">
            <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow duration-200 overflow-hidden">
                <!-- Header do Card -->
                <div class="p-4 border-b border-gray-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl" 
                                     :style="`background-color: ${module.color || '#6B7280'}`">
                                    <i :class="module.icon || 'fas fa-puzzle-piece'"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 truncate" x-text="module.display_name"></h3>
                                <p class="text-sm text-gray-500 mt-1" x-text="module.category_display || 'Geral'"></p>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  :class="`bg-${module.status_color}-100 text-${module.status_color}-800`"
                                  x-text="module.status_description">
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Conteúdo do Card -->
                <div class="p-4">
                    <p class="text-gray-600 text-sm mb-4" x-text="module.description"></p>
                    
                    <!-- Recursos -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Principais recursos:</h4>
                        <ul class="space-y-1">
                            <template x-for="(feature, index) in (module.features || []).slice(0, 3)" :key="index">
                                <li class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-check text-green-500 mr-2 text-xs"></i>
                                    <span x-text="feature"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                    
                    <!-- Preço -->
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-2xl font-bold text-gray-900" x-text="module.formatted_price || 'Gratuito'"></span>
                            <span class="text-sm text-gray-500" x-show="module.formatted_price">/mês</span>
                        </div>
                        <div x-show="module.is_core">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Essencial
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Ações -->
                <div class="px-4 pb-4">
                    <template x-if="!module.is_contracted">
                        <button @click="contractModule(module.id)" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                            Contratar Módulo
                        </button>
                    </template>
                    
                    <template x-if="module.is_contracted && !module.is_active">
                        <button @click="toggleModule(module.id)" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                            Ativar Módulo
                        </button>
                    </template>
                </div>
                
                <!-- Footer com informações de contrato -->
                <div x-show="module.is_contracted" class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                    <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>Contratado em: <span x-text="module.contracted_at"></span></span>
                        <span x-show="module.expires_at">Expira em: <span x-text="module.expires_at"></span></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="!loading && !error && coreModules.length === 0 && filteredOtherModules.length === 0" class="text-center py-12">
        <i class="fas fa-puzzle-piece text-4xl text-gray-400 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum módulo encontrado</h3>
        <p class="text-gray-600">Não há módulos disponíveis para a categoria selecionada.</p>
    </div>

    <!-- Resumo Financeiro -->
    <div x-show="!loading && !error && modules.length > 0" class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumo Financeiro</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="flex items-center">
                    <i class="fas fa-puzzle-piece text-blue-600 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm text-gray-600">Módulos Ativos</p>
                        <p class="text-xl font-bold text-gray-900" x-text="activeModulesCount">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="flex items-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm text-gray-600">Custo dos Módulos</p>
                        <p class="text-xl font-bold text-gray-900" x-text="formatCurrency(totalModulesPrice)">R$ 0,00</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="flex items-center">
                    <i class="fas fa-calculator text-purple-600 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm text-gray-600">Total Mensal</p>
                        <p class="text-xl font-bold text-gray-900" x-text="formatCurrency(totalMonthlyValue)">R$ 0,00</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function modulesManager() {
    return {
        modules: [],
        categories: {},
        selectedCategory: 'all',
        loading: false,
        error: null,
        totalModulesPrice: 0,
        totalMonthlyValue: 0,

        get coreModules() {
            const list = this.selectedCategory === 'all'
                ? this.modules
                : this.modules.filter(module => module.category === this.selectedCategory);
            return list.filter(module => module.is_core);
        },

        get filteredOtherModules() {
            const list = this.selectedCategory === 'all'
                ? this.modules
                : this.modules.filter(module => module.category === this.selectedCategory);
            return list.filter(module => !module.is_core);
        },

        get activeModulesCount() {
            return this.modules.filter(module => module.is_contracted && module.is_active).length;
        },

        async loadModules() {
            this.loading = true;
            this.error = null;

            try {
                const baseUrl = '/modules';
                const schoolId = window.modulesConfig && window.modulesConfig.schoolId ? window.modulesConfig.schoolId : null;
                const url = schoolId ? `${baseUrl}?school_id=${schoolId}` : baseUrl;
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error('Erro ao carregar módulos');
                }

                const data = await response.json();
                
                if (data.success) {
                    this.modules = data.data.modules;
                    this.categories = data.data.categories;
                    this.totalModulesPrice = data.data.total_modules_price;
                    this.totalMonthlyValue = data.data.total_monthly_value;
                } else {
                    throw new Error(data.message || 'Erro desconhecido');
                }
            } catch (error) {
                this.error = error.message;
                console.error('Erro ao carregar módulos:', error);
            } finally {
                this.loading = false;
            }
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value || 0);
        },

        // Métodos para gerenciar módulos
        async contractModule(moduleId) {
            if (!confirm('Deseja contratar este módulo? O valor será adicionado à sua mensalidade.')) {
                return;
            }

            try {
                const templateUrl = (window.modulesConfig && window.modulesConfig.contractUrl) ? window.modulesConfig.contractUrl : '/modules/:id/contract';
                const baseUrl = templateUrl.replace(':id', moduleId);
                const schoolId = window.modulesConfig && window.modulesConfig.schoolId ? window.modulesConfig.schoolId : null;
                const url = schoolId ? `${baseUrl}?school_id=${schoolId}` : baseUrl;
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    await this.loadModules();
                    this.showNotification('success', data.message);
                } else {
                    this.showNotification('error', data.message);
                }
            } catch (error) {
                this.showNotification('error', 'Erro ao contratar módulo');
                console.error('Erro:', error);
            }
        },

        async cancelModule(moduleId) {
            if (!confirm('Deseja cancelar este módulo? Ele será removido da sua conta.')) {
                return;
            }

            try {
                const templateUrl = (window.modulesConfig && window.modulesConfig.cancelUrl) ? window.modulesConfig.cancelUrl : '/modules/:id/cancel';
                const baseUrl = templateUrl.replace(':id', moduleId);
                const schoolId = window.modulesConfig && window.modulesConfig.schoolId ? window.modulesConfig.schoolId : null;
                const url = schoolId ? `${baseUrl}?school_id=${schoolId}` : baseUrl;
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    await this.loadModules();
                    this.showNotification('success', data.message);
                } else {
                    this.showNotification('error', data.message);
                }
            } catch (error) {
                this.showNotification('error', 'Erro ao cancelar módulo');
                console.error('Erro:', error);
            }
        },

        async toggleModule(moduleId) {
            try {
                const templateUrl = (window.modulesConfig && window.modulesConfig.toggleUrl) ? window.modulesConfig.toggleUrl : '/modules/:id/toggle';
                const baseUrl = templateUrl.replace(':id', moduleId);
                const schoolId = window.modulesConfig && window.modulesConfig.schoolId ? window.modulesConfig.schoolId : null;
                const url = schoolId ? `${baseUrl}?school_id=${schoolId}` : baseUrl;
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    await this.loadModules();
                    this.showNotification('success', data.message);
                } else {
                    this.showNotification('error', data.message);
                }
            } catch (error) {
                this.showNotification('error', 'Erro ao alterar status do módulo');
                console.error('Erro:', error);
            }
        },

        showNotification(type, message) {
            if (type === 'success') {
                alert('✅ ' + message);
            } else {
                alert('❌ ' + message);
            }
        }
    }
}


</script>