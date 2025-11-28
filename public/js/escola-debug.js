/**
 * Sistema de Debug para Escola SaaS
 * Monitora operações relacionadas à troca de escola e contexto atual
 */

(function() {
    'use strict';
    
    // Configuração do debug
    const DEBUG_CONFIG = {
        enabled: true,
        logToConsole: true,
        logToStorage: false,
        prefix: '[ESCOLA-DEBUG]'
    };
    
    // Função para log com timestamp
    function debugLog(message, data = null) {
        if (!DEBUG_CONFIG.enabled) return;
        
        const timestamp = new Date().toLocaleTimeString();
        const logMessage = `${DEBUG_CONFIG.prefix} [${timestamp}] ${message}`;
        
        if (DEBUG_CONFIG.logToConsole) {
            if (data) {
                console.group(logMessage);
                console.log(data);
                console.groupEnd();
            } else {
                console.log(logMessage);
            }
        }
        
        if (DEBUG_CONFIG.logToStorage) {
            const logs = JSON.parse(localStorage.getItem('escola-debug-logs') || '[]');
            logs.push({ timestamp, message, data });
            localStorage.setItem('escola-debug-logs', JSON.stringify(logs.slice(-100))); // Manter apenas os últimos 100 logs
        }
    }
    
    // Função para obter dados do usuário das meta tags
    function getUserData() {
        const metaTag = document.querySelector('meta[name="user-data"]');
        if (metaTag) {
            try {
                return JSON.parse(metaTag.getAttribute('content'));
            } catch (e) {
                debugLog('Erro ao parsear dados do usuário', e);
                return null;
            }
        }
        return null;
    }
    
    // Função para verificar contexto inicial
    function checkInitialContext() {
        const userData = getUserData();
        if (userData) {
            debugLog('Contexto inicial da página', {
                usuario: {
                    id: userData.id,
                    nome: userData.name,
                    email: userData.email,
                    escola_id: userData.escola_id,
                    is_super_admin: userData.is_super_admin,
                    has_suporte: userData.has_suporte
                },
                sessao: {
                    escola_atual: userData.session_escola,
                },
                pagina: {
                    url: userData.current_url,
                    timestamp: new Date().toISOString()
                }
            });
            
            // Verificar se há inconsistência entre escola do usuário e sessão
            if (userData.is_super_admin && userData.session_escola) {
                debugLog('Super Admin - Escola da sessão ativa', {
                    escola_sessao: userData.session_escola,
                    escola_usuario: userData.escola_id
                });
            } else if (!userData.is_super_admin && userData.escola_id !== userData.session_escola) {
                debugLog('⚠️ POSSÍVEL INCONSISTÊNCIA - Escola do usuário diferente da sessão', {
                    escola_usuario: userData.escola_id,
                    escola_sessao: userData.session_escola
                });
            }
        } else {
            debugLog('❌ Dados do usuário não encontrados nas meta tags');
        }
    }
    
    // Monitorar mudanças na sessão (storage events)
    function monitorSessionChanges() {
        window.addEventListener('storage', function(e) {
            if (e.key && e.key.includes('escola')) {
                debugLog('Mudança detectada no storage relacionada à escola', {
                    key: e.key,
                    oldValue: e.oldValue,
                    newValue: e.newValue
                });
            }
        });
    }
    
    // Interceptar requisições AJAX para monitorar chamadas relacionadas à escola
    function interceptAjaxRequests() {
        // Interceptar XMLHttpRequest
        const originalXHROpen = XMLHttpRequest.prototype.open;
        const originalXHRSend = XMLHttpRequest.prototype.send;
        
        XMLHttpRequest.prototype.open = function(method, url, ...args) {
            this._debugUrl = url;
            this._debugMethod = method;
            return originalXHROpen.apply(this, [method, url, ...args]);
        };
        
        XMLHttpRequest.prototype.send = function(data) {
            const url = this._debugUrl;
            const method = this._debugMethod;
            
            if (url && (url.includes('escola-switch') || url.includes('aluno') || url.includes('funcionario') || url.includes('dashboard'))) {
                debugLog(`🌐 Requisição ${method} para ${url}`, {
                    url: url,
                    method: method,
                    data: data,
                    timestamp: new Date().toISOString()
                });
                
                this.addEventListener('load', function() {
                    debugLog(`✅ Resposta recebida para ${method} ${url}`, {
                        status: this.status,
                        statusText: this.statusText,
                        response: this.responseText ? this.responseText.substring(0, 200) + '...' : 'Sem resposta'
                    });
                });
                
                this.addEventListener('error', function() {
                    debugLog(`❌ Erro na requisição ${method} ${url}`, {
                        status: this.status,
                        statusText: this.statusText
                    });
                });
            }
            
            return originalXHRSend.apply(this, arguments);
        };
        
        // Interceptar fetch
        const originalFetch = window.fetch;
        window.fetch = function(url, options = {}) {
            if (typeof url === 'string' && (url.includes('escola-switch') || url.includes('aluno') || url.includes('funcionario') || url.includes('dashboard'))) {
                debugLog(`🌐 Fetch para ${url}`, {
                    url: url,
                    method: options.method || 'GET',
                    options: options,
                    timestamp: new Date().toISOString()
                });
            }
            
            // Corrigido: usando window como contexto para evitar 'Illegal invocation'
            return originalFetch.call(window, url, options).then(response => {
                if (typeof url === 'string' && (url.includes('escola-switch') || url.includes('aluno') || url.includes('funcionario') || url.includes('dashboard'))) {
                    debugLog(`✅ Resposta fetch para ${url}`, {
                        status: response.status,
                        statusText: response.statusText,
                        ok: response.ok
                    });
                }
                return response;
            }).catch(error => {
                if (typeof url === 'string' && (url.includes('escola-switch') || url.includes('aluno') || url.includes('funcionario') || url.includes('dashboard'))) {
                    debugLog(`❌ Erro fetch para ${url}`, error);
                }
                throw error;
            });
        };
    }
    
    // Monitorar mudanças no DOM relacionadas à escola
    function monitorDOMChanges() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1 && node.classList) { // Element node with classList
                            // Verificar se há elementos relacionados à escola
                            if (node.classList.contains('escola-switcher') || node.id === 'escola-switcher') {
                                debugLog('🔄 Elemento escola-switcher adicionado ao DOM', {
                                    element: node,
                                    innerHTML: node.innerHTML.substring(0, 200) + '...'
                                });
                            }
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Comandos globais para debug
    function setupGlobalCommands() {
        window.escolaDebug = {
            // Verificar contexto atual
            checkContext: function() {
                checkInitialContext();
            },
            
            // Obter dados do usuário
            getUserData: function() {
                const data = getUserData();
                console.log('Dados do usuário:', data);
                return data;
            },
            
            // Verificar escola atual
            getCurrentSchool: function() {
                fetch('/escola-switch/current')
                    .then(response => response.json())
                    .then(data => {
                        debugLog('🏫 Escola atual do servidor', data);
                        console.log('Escola atual:', data);
                    })
                    .catch(error => {
                        debugLog('❌ Erro ao obter escola atual', error);
                    });
            },
            
            // Listar todas as escolas
            getAllSchools: function() {
                fetch('/escola-switch/')
                    .then(response => response.json())
                    .then(data => {
                        debugLog('🏫 Todas as escolas disponíveis', data);
                        console.log('Escolas disponíveis:', data);
                    })
                    .catch(error => {
                        debugLog('❌ Erro ao obter escolas', error);
                    });
            },
            
            // Alternar debug
            toggle: function() {
                DEBUG_CONFIG.enabled = !DEBUG_CONFIG.enabled;
                debugLog(`Debug ${DEBUG_CONFIG.enabled ? 'ativado' : 'desativado'}`);
            },
            
            // Limpar logs
            clearLogs: function() {
                localStorage.removeItem('escola-debug-logs');
                console.clear();
                debugLog('Logs limpos');
            },
            
            // Obter logs salvos
            getLogs: function() {
                const logs = JSON.parse(localStorage.getItem('escola-debug-logs') || '[]');
                console.table(logs);
                return logs;
            },
            
            // Simular troca de escola (apenas para teste)
            testSwitchSchool: function(escolaId) {
                if (!escolaId) {
                    console.log('Uso: escolaDebug.testSwitchSchool(escolaId)');
                    return;
                }
                
                debugLog(`🧪 Testando troca para escola ID: ${escolaId}`);
                
                fetch('/escola-switch/switch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ escola_id: escolaId })
                })
                .then(response => response.json())
                .then(data => {
                    debugLog('✅ Resultado do teste de troca', data);
                    console.log('Resultado:', data);
                })
                .catch(error => {
                    debugLog('❌ Erro no teste de troca', error);
                });
            }
        };
        
        // Adicionar comandos ao console
        debugLog('🛠️ Comandos disponíveis no console:', {
            'escolaDebug.checkContext()': 'Verificar contexto atual',
            'escolaDebug.getUserData()': 'Obter dados do usuário',
            'escolaDebug.getCurrentSchool()': 'Verificar escola atual',
            'escolaDebug.getAllSchools()': 'Listar todas as escolas',
            'escolaDebug.toggle()': 'Alternar debug on/off',
            'escolaDebug.clearLogs()': 'Limpar logs',
            'escolaDebug.getLogs()': 'Obter logs salvos',
            'escolaDebug.testSwitchSchool(id)': 'Testar troca de escola'
        });
    }
    
    // Inicializar quando o DOM estiver pronto
    function init() {
        debugLog('🚀 Sistema de debug inicializado');
        
        checkInitialContext();
        monitorSessionChanges();
        interceptAjaxRequests();
        monitorDOMChanges();
        setupGlobalCommands();
        
        debugLog('✅ Todos os monitores ativados');
    }
    
    // Inicializar quando o DOM estiver carregado
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();