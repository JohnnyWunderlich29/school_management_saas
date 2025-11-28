/**
 * Sistema de Alertas JavaScript
 * Gerencia alertas dinâmicos e interações
 */

;(function() {
    if (typeof window !== 'undefined' && window.AlertSystem && window.alertSystem) { return; }

class AlertSystem {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Aguarda o DOM estar pronto
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.container = document.getElementById('alert-container');
        if (!this.container) {
            console.warn('Alert container not found. Creating one...');
            this.createContainer();
        }
        
        // Anima alertas existentes
        this.animateExistingAlerts();
        
        // Configura listeners globais
        this.setupGlobalListeners();
    }

    createContainer() {
        this.container = document.createElement('div');
        this.container.id = 'alert-container';
        this.container.className = 'fixed top-4 right-4 z-50 max-w-sm space-y-3';
        document.body.appendChild(this.container);
    }

    /**
     * Mostra um alerta dinamicamente
     * @param {string} message - Mensagem do alerta
     * @param {string} type - Tipo do alerta (success, error, warning, info)
    * @param {object} options - Opções adicionais
    */
   show(message, type = 'info', options = {}) {
        const alertId = `alert_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        let timeout = options.timeout !== undefined ? options.timeout : 5000; // Padrão de 5s
        // Aumentar o tempo de exibição para erros
        if (type === 'error' && options.timeout === undefined) {
            timeout = 8000; // Erros ficam visíveis por 8s
        }
        const dismissible = options.dismissible !== false;
        const actions = options.actions || [];
        const errors = options.errors || [];
        const persistent = options.persistent || false;
        
        const alertHtml = this.createAlertHTML(alertId, message, type, timeout, dismissible, actions, errors, persistent);
        
        if (this.container) {
            this.container.insertAdjacentHTML('beforeend', alertHtml);
            this.animateIn(alertId);
            
            // Auto-remove se não for persistente e tiver timeout
            if (!persistent && timeout > 0) {
                setTimeout(() => {
                    this.dismiss(alertId);
                }, timeout);
            }
        }
        
        return alertId;
    }

    /**
     * Cria o HTML do alerta
     */
    createAlertHTML(alertId, message, type, timeout, dismissible, actions, errors, persistent) {
        const typeClasses = {
            'success': 'bg-green-50 border-l-4 border-green-400 text-green-800',
            'error': 'bg-red-50 border-l-4 border-red-400 text-red-800',
            'warning': 'bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800',
            'info': 'bg-blue-50 border-l-4 border-blue-400 text-blue-800',
            'validation': 'bg-red-50 border-l-4 border-red-400 text-red-800',
            'access_denied': 'bg-orange-50 border-l-4 border-orange-400 text-orange-800',
            'system_error': 'bg-red-50 border-l-4 border-red-400 text-red-800'
        };
        
        const typeIcons = {
            'success': 'fas fa-check-circle text-green-400',
            'error': 'fas fa-exclamation-circle text-red-400',
            'warning': 'fas fa-exclamation-triangle text-yellow-400',
            'info': 'fas fa-info-circle text-blue-400',
            'validation': 'fas fa-exclamation-circle text-red-400',
            'access_denied': 'fas fa-lock text-orange-400',
            'system_error': 'fas fa-bug text-red-400'
        };
        
        let errorsHtml = '';
        if (errors.length > 0) {
            errorsHtml = '<ul class="mt-2 text-xs list-disc list-inside space-y-1">';
            errors.forEach(error => {
                errorsHtml += `<li>${this.escapeHtml(error)}</li>`;
            });
            errorsHtml += '</ul>';
        }
        
        let actionsHtml = '';
        if (actions.length > 0) {
            actionsHtml = '<div class="mt-3 flex flex-wrap gap-2">';
            actions.forEach(action => {
                const actionClass = action.class || 'bg-gray-600 hover:bg-gray-700 text-white';
                actionsHtml += `<button class="px-3 py-1 text-xs rounded-md font-medium transition-colors duration-200 ${actionClass}" onclick="window.alertSystem.handleAction('${action.action}', '${action.url || ''}', '${alertId}')">${this.escapeHtml(action.label)}</button>`;
            });
            actionsHtml += '</div>';
        }
        
        const dismissButton = dismissible ? `
            <div class="ml-3 flex-shrink-0">
                <button class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none transition-colors duration-200" onclick="window.alertSystem.dismiss('${alertId}')">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        ` : '';
        
        const persistentIndicator = persistent ? '<div class="absolute top-1 right-1 w-2 h-2 bg-blue-500 rounded-full"></div>' : '';
        
        return `
            <div id="${alertId}" class="alert-item transform translate-x-full transition-all duration-300 ease-in-out" data-timeout="${timeout}" data-dismissible="${dismissible}" data-persistent="${persistent}">
                <div class="${typeClasses[type]} rounded-lg shadow-lg p-4 relative">
                    ${persistentIndicator}
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="${typeIcons[type]} text-lg"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium">${this.escapeHtml(message)}</p>
                            ${errorsHtml}
                            ${actionsHtml}
                        </div>
                        ${dismissButton}
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Anima a entrada do alerta
     */
    animateIn(alertId) {
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.classList.remove('translate-x-full');
            }
        }, 100);
    }

    /**
     * Remove um alerta
     */
    dismiss(alertId) {
        const alert = document.getElementById(alertId);
        if (alert) {
            alert.classList.add('translate-x-full');
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 300);
        }
    }

    /**
     * Remove todos os alertas
     */
    dismissAll() {
        const alerts = this.container?.querySelectorAll('.alert-item') || [];
        alerts.forEach(alert => {
            this.dismiss(alert.id);
        });
    }

    /**
     * Remove apenas alertas não persistentes
     */
    dismissNonPersistent() {
        const alerts = this.container?.querySelectorAll('.alert-item') || [];
        alerts.forEach(alert => {
            if (alert.dataset.persistent !== 'true') {
                this.dismiss(alert.id);
            }
        });
    }

    /**
     * Lida com ações de alerta
     */
   handleAction(action, url, alertId) {
        switch(action) {
            case 'reload':
                this.dismiss(alertId);
                window.location.reload();
                break;
            case 'back':
                this.dismiss(alertId);
                window.history.back();
                break;
            case 'login':
                this.dismiss(alertId);
                window.location.href = url || '/login';
                break;
            case 'report':
                this.reportIssue(alertId);
                break;
            case 'retry':
                // Implementar lógica de retry se necessário
                this.dismiss(alertId);
                window.location.reload();
                break;
            case 'contact':
                // Implementar contato com suporte
                console.log('Contatar suporte');
                this.dismiss(alertId);
                break;
            default:
                // Se for uma URL, navega. Senão, assume que é um callback.
                if (url && (url.startsWith('http') || url.startsWith('/l') || url.startsWith('tel:') || url.startsWith('mailto:'))) {
                    window.location.href = url;
                } else if (typeof window[action] === 'function') {
                    // Executa uma função global se existir
                    window[action]();
                }
                this.dismiss(alertId);
        }
    }

    /**
     * Reporta um problema
     */
    reportIssue(alertId) {
        const alert = document.getElementById(alertId);
        if (alert) {
            const alertData = {
                id: alertId,
                timestamp: new Date().toISOString(),
                url: window.location.href,
                userAgent: navigator.userAgent
            };
            
            // Aqui você pode implementar o envio do relatório
            console.log('Relatório de problema:', alertData);
            
            this.show('Problema reportado com sucesso!', 'success', { timeout: 3000 });
        }
    }

    /**
     * Anima alertas existentes na página
     */
    animateExistingAlerts() {
        const existingAlerts = document.querySelectorAll('.alert-item');
        existingAlerts.forEach((alert, index) => {
            setTimeout(() => {
                alert.classList.remove('translate-x-full');
                
                // Auto-remove se tem timeout e não é persistente
                const timeout = parseInt(alert.dataset.timeout);
                const persistent = alert.dataset.persistent === 'true';
                
                if (!persistent && timeout > 0) {
                    setTimeout(() => {
                        this.dismiss(alert.id);
                    }, timeout);
                }
            }, 100 + (index * 100));
        });
    }

    /**
     * Configura listeners globais
     */
    setupGlobalListeners() {
        // Listener para tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.dismissNonPersistent();
            }
        });

        // Listener para mudanças de página (SPA)
        window.addEventListener('beforeunload', () => {
            this.dismissAll();
        });
    }

    /**
     * Escapa HTML para prevenir XSS
     */
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, (m) => map[m]);
    }

    // Métodos de conveniência
    success(message, options = {}) {
        return this.show(message, 'success', options);
    }

    error(message, options = {}) {
        return this.show(message, 'error', options);
    }

    warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    info(message, options = {}) {
        return this.show(message, 'info', options);
    }

    validation(message, errors = [], options = {}) {
        return this.show(message, 'validation', { ...options, errors });
    }

    accessDenied(message, options = {}) {
        return this.show(message, 'access_denied', {
            ...options,
            actions: [
                { label: 'Fazer Login', action: 'login', class: 'bg-blue-600 hover:bg-blue-700 text-white' },
                { label: 'Voltar', action: 'back', class: 'bg-gray-600 hover:bg-gray-700 text-white' }
            ]
        });
    }

    systemError(message, options = {}) {
        return this.show(message, 'system_error', {
            ...options,
            persistent: true,
            actions: [
                { label: 'Recarregar', action: 'reload', class: 'bg-blue-600 hover:bg-blue-700 text-white' },
                { label: 'Reportar', action: 'report', class: 'bg-red-600 hover:bg-red-700 text-white' }
            ]
        });
    }
}

// Inicializa o sistema de alertas globalmente
window.alertSystem = new AlertSystem();

// Compatibilidade com código existente
window.AlertSystem = {
    show: (message, type, options) => window.alertSystem.show(message, type, options),
    dismiss: (alertId) => window.alertSystem.dismiss(alertId),
    success: (message, options) => window.alertSystem.success(message, options),
    error: (message, options) => window.alertSystem.error(message, options),
    warning: (message, options) => window.alertSystem.warning(message, options),
    info: (message, options) => window.alertSystem.info(message, options),
    validation: (message, errors, options) => window.alertSystem.validation(message, errors, options)
};

// Funções globais para compatibilidade
window.dismissAlert = (alertId) => window.alertSystem.dismiss(alertId);
window.handleAlertAction = (action, url, alertId) => window.alertSystem.handleAction(action, url, alertId);

// Exporta para uso em módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AlertSystem;
}
})();