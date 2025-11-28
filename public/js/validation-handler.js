/**
 * Sistema de tratamento padronizado de erros de validação para requisições AJAX
 */

;(function() {
    if (typeof window !== 'undefined' && window.ValidationHandler) { return; }

class ValidationHandler {
    /**
     * Trata resposta de erro 422 (validação) de requisições AJAX
     * @param {Object} xhr - Objeto XMLHttpRequest ou resposta fetch
     * @param {Function} customHandler - Handler customizado opcional
     */
    static handleValidationError(xhr, customHandler = null) {
        if (xhr.status !== 422) {
            return false;
        }

        try {
            const response = xhr.responseJSON || JSON.parse(xhr.responseText);
            const hasProcessed = response && Array.isArray(response.processed_errors);
            const errorsObj = (response && response.errors && typeof response.errors === 'object') ? response.errors : null;
            
            // Se há erros processados, usar o sistema de alertas
            if (hasProcessed) {
                // Usar o sistema de alertas existente
                if (window.alertSystem) {
                    const message = response.message || 'Por favor, corrija os seguintes erros:';
                    window.alertSystem.validation(message, response.processed_errors);
                } else {
                    // Fallback: mostrar erros como lista
                    this.showErrorList(response.processed_errors, response.message);
                }
            }
            
            // Se há handler customizado, executar
            if (customHandler && typeof customHandler === 'function') {
                customHandler(errorsObj || {}, response.processed_errors);
            } else {
                // Destacar campos com erro
                if (errorsObj) {
                    this.highlightFieldErrors(errorsObj);
                } else if (hasProcessed) {
                    // Sem objeto errors: exibir lista como fallback
                    this.showErrorList(response.processed_errors, response.message);
                } else {
                    // Último fallback: usar message se existir
                    const msgs = [];
                    if (response && typeof response.message === 'string' && response.message.trim()) {
                        msgs.push(response.message.trim());
                    }
                    if (msgs.length) {
                        this.showErrorList(msgs, 'Erros de validação:');
                    }
                }
            }
            
            return true;
        } catch (error) {
            console.error('Erro ao processar resposta de validação:', error);
            return false;
        }
    }

    /**
     * Destaca campos com erro no formulário
     * @param {Object} errors - Objeto com erros por campo
     */
    static highlightFieldErrors(errors) {
        if (!errors || typeof errors !== 'object') {
            return;
        }
        // Limpar erros anteriores
        document.querySelectorAll('.border-red-500, .ring-red-500').forEach(field => {
            field.classList.remove('border-red-500', 'ring-red-500');
            field.classList.add('border-gray-300');
        });

        // Destacar campos com erro
        Object.keys(errors).forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.classList.add('border-red-500', 'ring-red-500');
                field.classList.remove('border-gray-300');
                
                // Remover destaque após alguns segundos
                setTimeout(() => {
                    field.classList.remove('border-red-500', 'ring-red-500');
                    field.classList.add('border-gray-300');
                }, 5000);
            }
        });
    }

    /**
     * Mostra lista de erros como fallback
     * @param {Array} errors - Lista de erros
     * @param {String} message - Mensagem principal
     */
    static showErrorList(errors, message = 'Erros de validação:') {
        const lines = Array.isArray(errors) ? errors : [String(errors || '')].filter(Boolean);
        const title = message || 'Erros de validação:';
    
        // Preferir AlertSystem quando disponível
        if (window.alertSystem && typeof window.alertSystem.validation === 'function') {
            window.alertSystem.validation(title, lines);
            return;
        }
        if (window.alertSystem && typeof window.alertSystem.error === 'function') {
            // Fallback para um único toast de erro quando não houver método validation
            const bullet = lines.length ? `\n\n• ${lines.join('\n• ')}` : '';
            window.alertSystem.error(`${title}${bullet}`);
            return;
        }
    
        // Último fallback: logar em console (evitar alert() nativo)
        const bullet = lines.length ? `\n\n• ${lines.join('\n• ')}` : '';
        console.error(`${title}${bullet}`);
    }

    /**
     * Configura handler global para requisições jQuery AJAX
     */
    static setupGlobalHandlers() {
        // Handler global para jQuery
        if (window.$ && $.ajaxSetup) {
            $(document).ajaxError(function(event, xhr, settings) {
                if (xhr.status === 422) {
                    ValidationHandler.handleValidationError(xhr);
                }
            });
        }

        // Handler global para fetch (se necessário)
        if (window.fetch) {
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                return originalFetch.apply(this, args)
                    .then(response => {
                        if (response.status === 422) {
                            response.clone().json().then(data => {
                                ValidationHandler.handleValidationError({
                                    status: 422,
                                    responseJSON: data
                                });
                            });
                        }
                        return response;
                    });
            };
        }
    }
}

// Inicializar handlers globais quando o documento estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        ValidationHandler.setupGlobalHandlers();
    });
} else {
    ValidationHandler.setupGlobalHandlers();
}

// Exportar para uso global
window.ValidationHandler = ValidationHandler;
})();