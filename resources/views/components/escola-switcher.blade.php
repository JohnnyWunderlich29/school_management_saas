<div class="relative" x-data="escolaSwitcher()" x-init="init()" @click.stop>
    <!-- Botão para abrir o seletor -->
    <button @click="toggle()" 
            class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 rounded-md transition-colors duration-200"
            :class="{ 'bg-gray-50': isOpen }">
        <div class="flex items-center space-x-2">
            <span x-text="escolaAtual ? escolaAtual.nome : ' '" class="max-w-48 truncate"></span>
        </div>
        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         @click.away="close()"
         class="absolute left-0 md:right-0 md:left-auto mt-2 bg-white rounded-md shadow-lg ring-grey-300 ring-opacity-5 focus:outline-none z-50" style="width: 300px;">
        
        <div class="py-1">
            <!-- Header -->
            <div class="px-4 py-2 border-b border-gray-200">
                <h3 class="text-sm font-medium text-gray-900">Trocar Escola</h3>
                <p class="text-xs text-gray-500 mt-1">Selecione uma escola para acessar</p>
            </div>
            
            <!-- Campo de filtro -->
            <div x-show="!loading && escolas.length > 5" class="px-4 py-2 border-b border-gray-200">
                <input type="text" 
                       x-model="filtro" 
                       @input="filtrarEscolas()"
                       placeholder="Buscar por nome, ID ou CNPJ..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- Loading -->
            <div x-show="loading" class="px-4 py-3">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
                    <span class="text-sm text-gray-500">Carregando escolas...</span>
                </div>
            </div>
            
            <!-- Lista de escolas -->
            <div x-show="!loading && escolasFiltradas.length > 0" class="max-h-64 overflow-y-auto">
                <template x-for="escola in escolasExibidas" :key="escola.id">
                    <button @click="switchEscola(escola.id)" 
                            class="w-full text-left px-4 py-3 hover:bg-gray-50 transition-colors duration-150 flex items-center justify-between"
                            :class="{ 'bg-blue-50 border-r-2 border-blue-500': escola.id === escolaAtual?.id }">
                        <div class="flex items-center space-x-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900" x-text="escola.nome"></div>
                                <div class="text-xs text-gray-500" x-text="escola.razao_social" x-show="escola.razao_social"></div>
                            </div>
                        </div>
                        <div x-show="escola.id === escolaAtual?.id" class="text-blue-500">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </button>
                </template>
            </div>
            
            <!-- Nenhuma escola disponível -->
            <div x-show="!loading && escolasFiltradas.length === 0 && escolas.length === 0" class="px-4 py-3">
                <p class="text-sm text-gray-500">Nenhuma escola disponível para troca.</p>
            </div>
            
            <!-- Nenhuma escola encontrada no filtro -->
            <div x-show="!loading && escolasFiltradas.length === 0 && escolas.length > 0" class="px-4 py-3">
                <p class="text-sm text-gray-500">Nenhuma escola encontrada com os critérios de busca.</p>
            </div>
            
            <!-- Mostrar mais escolas -->
            <div x-show="!loading && escolasFiltradas.length > 5 && escolasExibidas.length < escolasFiltradas.length" class="px-4 py-2 border-t border-gray-200">
                <button @click="mostrarMais()" class="w-full text-sm text-blue-600 hover:text-blue-800 py-1">
                    Mostrar mais (<span x-text="escolasFiltradas.length - escolasExibidas.length"></span> restantes)
                </button>
            </div>
            
            <!-- Mensagem de erro -->
            <div x-show="error" class="px-4 py-3 border-t border-gray-200">
                <p class="text-sm text-red-600" x-text="error"></p>
            </div>
        </div>
    </div>
</div>

<script>
function escolaSwitcher() {
    return {
        isOpen: false,
        loading: false,
        escolas: [],
        escolasFiltradas: [],
        escolasExibidas: [],
        escolaAtual: null,
        error: null,
        filtro: '',
        limitePorPagina: 5,
        
        init() {
            this.loadEscolaAtual();
        },
        
        toggle() {
            if (!this.isOpen) {
                this.isOpen = true;
                this.loadEscolas();
            } else {
                this.isOpen = false;
            }
        },
        
        close() {
            this.isOpen = false;
        },
        
        async loadEscolas() {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch('/escola-switch/', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    this.escolas = data.escolas;
                    this.filtrarEscolas();
                    
                    // Buscar escola atual da sessão ou do usuário
                    const escolaAtualId = data.escola_atual_sessao || data.escola_atual;
                    if (escolaAtualId) {
                        this.escolaAtual = this.escolas.find(e => e.id === escolaAtualId) || null;
                    }
                } else {
                    this.error = data.message || 'Erro ao carregar escolas';
                }
            } catch (error) {
                this.error = 'Erro de conexão';
                console.error('Erro ao carregar escolas:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async loadEscolaAtual() {
            try {
                // Usando URL absoluta para evitar problemas de caminho relativo
                const baseUrl = window.location.origin;
                const response = await fetch(`${baseUrl}/escola-switch/current`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'same-origin' // Garantir que cookies sejam enviados
                });
                
                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    this.escolaAtual = data.escola;
                }
            } catch (error) {
                console.error('Erro ao carregar escola atual:', error);
                // Não definir erro na interface para não afetar a experiência do usuário
            }
        },
        
        async switchEscola(escolaId) {
            if (escolaId === this.escolaAtual?.id) {
                this.close();
                return;
            }
            
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch('/escola-switch/switch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ escola_id: escolaId })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Atualizar escola atual
                    this.escolaAtual = data.escola;
                    
                    // Atualizar token CSRF se fornecido
                    if (data.csrf_token) {
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        if (csrfMeta) {
                            csrfMeta.setAttribute('content', data.csrf_token);
                            console.log('🔄 Token CSRF atualizado após troca de escola');
                        }
                    }
                    
                    // Mostrar mensagem de sucesso
                    this.showNotification(data.message, 'success');
                    
                    // Fechar dropdown
                    this.close();
                    
                    // Redirecionar para a URL fornecida pelo servidor ou dashboard como fallback
                    setTimeout(() => {
                        const redirectUrl = data.redirect_url || '/dashboard';
                        window.location.href = redirectUrl;
                    }, 1000);
                } else {
                    this.error = data.message || 'Erro ao trocar escola';
                }
            } catch (error) {
                this.error = 'Erro de conexão';
                console.error('Erro ao trocar escola:', error);
            } finally {
                this.loading = false;
            }
        },
        
        filtrarEscolas() {
            if (!this.filtro.trim()) {
                this.escolasFiltradas = [...this.escolas];
            } else {
                const termo = this.filtro.toLowerCase();
                this.escolasFiltradas = this.escolas.filter(escola => {
                    return escola.nome.toLowerCase().includes(termo) ||
                           escola.id.toString().includes(termo) ||
                           (escola.cnpj && escola.cnpj.includes(termo)) ||
                           (escola.razao_social && escola.razao_social.toLowerCase().includes(termo));
                });
            }
            this.atualizarEscolasExibidas();
        },
        
        atualizarEscolasExibidas() {
            this.escolasExibidas = this.escolasFiltradas.slice(0, this.limitePorPagina);
        },
        
        mostrarMais() {
            this.limitePorPagina += 5;
            this.atualizarEscolasExibidas();
        },
        
        showNotification(message, type = 'info') {
            // Criar notificação temporária
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remover após 3 segundos
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }
}
</script>