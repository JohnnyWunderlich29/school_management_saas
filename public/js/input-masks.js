/**
 * Máscaras de entrada para formulários
 * Este arquivo contém todas as máscaras de entrada utilizadas no sistema
 */

// Função para aplicar máscara de CPF
function applyCpfMask(input) {
    if (!input) return;
    
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        e.target.value = value;
    });
}

// Função para aplicar máscara de RG
function applyRgMask(input) {
    if (!input) return;
    
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 9) {
            value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3-$4');
        }
        e.target.value = value;
    });
}

// Função para aplicar máscara de telefone
function applyPhoneMask(input) {
    if (!input) return;
    
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            if (value.length < 14) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            }
        }
        e.target.value = value;
    });
}

// Função para aplicar máscara de CEP
function applyCepMask(input) {
    if (!input) return;
    
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{5})(\d{3})/, '$1-$2');
        e.target.value = value;
    });
}

// Função para aplicar máscara de CNPJ
function applyCnpjMask(input) {
    if (!input) return;
    
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        e.target.value = value;
    });
}

// Função para aplicar máscara de data (dd/mm/yyyy)
function applyDateMask(input) {
    if (!input) return;
    
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.replace(/(\d{2})(\d)/, '$1/$2');
        }
        if (value.length >= 5) {
            value = value.replace(/(\d{2})\/(\d{2})(\d)/, '$1/$2/$3');
        }
        if (value.length > 10) {
            value = value.substring(0, 10);
        }
        e.target.value = value;
    });
    
    // Validação básica de data
    input.addEventListener('blur', function(e) {
        const value = e.target.value;
        if (value && value.length === 10) {
            const parts = value.split('/');
            const day = parseInt(parts[0]);
            const month = parseInt(parts[1]);
            const year = parseInt(parts[2]);
            
            if (day < 1 || day > 31 || month < 1 || month > 12 || year < 1900) {
                e.target.setCustomValidity('Data inválida. Use o formato dd/mm/yyyy');
            } else {
                e.target.setCustomValidity('');
            }
        }
    });
}

// Função para aplicar máscara de moeda (Real)
function applyCurrencyMask(input) {
    if (!input) return;
    
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = (value / 100).toFixed(2) + '';
        value = value.replace('.', ',');
        value = value.replace(/(\d)(\d{3})(\d{3}),/, '$1.$2.$3,');
        value = value.replace(/(\d)(\d{3}),/, '$1.$2,');
        e.target.value = 'R$ ' + value;
    });
}

// Função principal para inicializar todas as máscaras
function initializeInputMasks() {
    // Máscaras de CPF
    const cpfInputs = document.querySelectorAll('input[name="cpf"], input[data-mask="cpf"]');
    cpfInputs.forEach(input => applyCpfMask(input));
    
    // Máscaras de RG
    const rgInputs = document.querySelectorAll('input[name="rg"], input[data-mask="rg"]');
    rgInputs.forEach(input => applyRgMask(input));
    
    // Máscaras de telefone
    const phoneInputs = document.querySelectorAll('input[name="telefone"], input[name="telefone_principal"], input[name="telefone_secundario"], input[name="phone"], input[data-mask="phone"]');
    phoneInputs.forEach(input => applyPhoneMask(input));
    
    // Máscaras de CEP
    const cepInputs = document.querySelectorAll('input[name="cep"], input[data-mask="cep"]');
    cepInputs.forEach(input => applyCepMask(input));
    
    // Máscaras de CNPJ
    const cnpjInputs = document.querySelectorAll('input[name="cnpj"], input[data-mask="cnpj"]');
    cnpjInputs.forEach(input => applyCnpjMask(input));
    
    // Máscaras de data (apenas para campos com data-mask="date")
    const dateInputs = document.querySelectorAll('input[data-mask="date"]');
    dateInputs.forEach(input => applyDateMask(input));
    
    // Máscaras de moeda
    const currencyInputs = document.querySelectorAll('input[data-mask="currency"]');
    currencyInputs.forEach(input => applyCurrencyMask(input));
}

// Inicializar máscaras quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', initializeInputMasks);

// Exportar funções para uso individual se necessário
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        applyCpfMask,
        applyRgMask,
        applyPhoneMask,
        applyCepMask,
        applyCnpjMask,
        applyDateMask,
        applyCurrencyMask,
        initializeInputMasks
    };
}