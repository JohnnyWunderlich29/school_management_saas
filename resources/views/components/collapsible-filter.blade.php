@props([
    'title' => 'Filtros',
    'action' => '',
    'method' => 'GET',
    'clearRoute' => '',
    'expanded' => false,
    'target' => ''
])

<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <!-- Header do filtro colapsável -->
    <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
        <button 
            type="button" 
            class="w-full flex items-center justify-between text-left focus:outline-none rounded-md"
            onclick="toggleFilter()"
            id="filterToggle"
        >
            <div class="flex items-center space-x-1">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700">{{ $title }}</span>
                @if(request()->hasAny(array_keys(request()->query())))
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        {{ count(array_filter(request()->query())) }} ativo(s)
                    </span>
                @endif
            </div>
            <svg 
                class="w-5 h-5 text-gray-400 transform transition-transform duration-300" 
                id="filterIcon"
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
    </div>

    <!-- Conteúdo do filtro -->
    <div 
        class="px-4 py-4 overflow-hidden transition-all duration-300 ease-in-out {{ $expanded ? '' : 'hidden' }}" 
        id="filterContent"
        style="max-height: {{ $expanded ? 'none' : '0px' }}; opacity: {{ $expanded ? '1' : '0' }};"
    >
        <form action="{{ $action }}" method="{{ $method }}" class="space-y-4" id="autoFilterForm" data-target="{{ $target }}">
            <!-- Grid responsivo para os campos de filtro -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                {{ $slot }}
            </div>
            
            <!-- Botões de ação -->
            <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-gray-200">
                <button 
                    type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filtrar
                </button>
                
                @if($clearRoute)
                    <a 
                        href="{{ $clearRoute }}" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Limpar
                    </a>
                @endif
                
                <button 
                    type="button" 
                    onclick="toggleFilter()" 
                    class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:border-gray-400 focus:ring ring-gray-200 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                    Ocultar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleFilter() {
    const content = document.getElementById('filterContent');
    const icon = document.getElementById('filterIcon');

    const isHidden = content.classList.contains('hidden');

    if (isHidden) {
        // Expandir com animação
        content.classList.remove('hidden');
        content.style.maxHeight = '0px';
        content.style.opacity = '0';
        requestAnimationFrame(() => {
            content.style.maxHeight = content.scrollHeight + 'px';
            content.style.opacity = '1';
        });
        // Após animação, permitir altura dinâmica
        setTimeout(() => {
            content.style.maxHeight = 'none';
        }, 300);

        icon.style.transform = 'rotate(180deg)';
        localStorage.setItem('filterExpanded', 'true');
    } else {
        // Colapsar com animação
        content.style.maxHeight = content.scrollHeight + 'px';
        content.style.opacity = '1';
        requestAnimationFrame(() => {
            content.style.maxHeight = '0px';
            content.style.opacity = '0';
        });
        // Após animação, esconder completamente
        setTimeout(() => {
            content.classList.add('hidden');
        }, 300);

        icon.style.transform = 'rotate(0deg)';
        localStorage.setItem('filterExpanded', 'false');
    }
}

// Variável para controlar o timeout do filtro automático
let autoFilterTimeout;

// Função para aplicar filtro automaticamente
function applyAutoFilter() {
    // Permitir chamada imediata via submit interceptado
    const apply = async () => {
        const form = document.getElementById('autoFilterForm');
        if (!form) return;

        // Criar URL com parâmetros atuais
        const formData = new FormData(form);
        const params = new URLSearchParams();
        
        // Adicionar apenas campos com valores
        for (let [key, value] of formData.entries()) {
            if (value && value.trim() !== '') {
                params.append(key, value);
            }
        }
        
        const baseUrl = form.action || window.location.pathname;
        const newUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

        const targetId = form.dataset.target;
        if (targetId) {
            const container = document.getElementById(targetId);
            if (!container) {
                // Fallback: container não encontrado, faz reload completo
                window.location.href = newUrl;
                return;
            }
            // Indicar carregando: overlay se existir, senão reduzir opacidade
            const overlay = container.querySelector('[data-loading-overlay]') || container.querySelector('.loading-overlay');
            if (overlay) overlay.classList.remove('hidden');
            container.classList.add('pointer-events-none');
            try {
                const response = await fetch(newUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });
                const text = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, 'text/html');
                const newContainer = doc.getElementById(targetId);
                if (newContainer) {
                    const targetContent = container.querySelector('[data-ajax-content]') || container;
                    const newTargetContent = newContainer.querySelector('[data-ajax-content]') || newContainer;
                    targetContent.innerHTML = newTargetContent.innerHTML;
                    // Atualiza a URL sem recarregar para manter estado compartilhável
                    window.history.replaceState(null, '', newUrl);
                } else {
                    // Fallback: estrutura não encontrada, faz reload completo
                    window.location.href = newUrl;
                }
            } catch (e) {
                console.error('Erro ao atualizar tabela via AJAX', e);
                window.location.href = newUrl;
            } finally {
                if (overlay) overlay.classList.add('hidden');
                container.classList.remove('pointer-events-none');
            }
        } else {
            // Sem alvo definido: mantém comportamento atual
            window.location.href = newUrl;
        }
    };

    clearTimeout(autoFilterTimeout);
    autoFilterTimeout = setTimeout(apply, 500); // Aguarda 500ms após parar de digitar
}

// Restaurar estado do filtro ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    const isExpanded = localStorage.getItem('filterExpanded') === 'true';
    const hasFilters = {{ request()->hasAny(array_keys(request()->query())) ? 'true' : 'false' }};
    const forceExpanded = {{ $expanded ? 'true' : 'false' }};
    
    // Expandir apenas se forçado pelo componente ou se estava expandido anteriormente pelo usuário
    // Não expandir automaticamente apenas por ter filtros ativos
    if (forceExpanded || (isExpanded && !hasFilters)) {
        const content = document.getElementById('filterContent');
        const icon = document.getElementById('filterIcon');
        
        content.classList.remove('hidden');
        content.style.maxHeight = 'none';
        content.style.opacity = '1';
        icon.style.transform = 'rotate(180deg)';
    } else {
        // Garantir que os filtros iniciem fechados por padrão
        const content = document.getElementById('filterContent');
        const icon = document.getElementById('filterIcon');
        
        content.classList.add('hidden');
        content.style.maxHeight = '0px';
        content.style.opacity = '0';
        icon.style.transform = 'rotate(0deg)';
    }
    
    // Adicionar event listeners para filtro automático
    const form = document.getElementById('autoFilterForm');
    if (form) {
        // Intercepta submissão manual para atualização parcial imediata
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Executa sem delay
            const originalTimeout = autoFilterTimeout;
            clearTimeout(autoFilterTimeout);
            // Usa versão imediata do apply
            (async () => {
                const formData = new FormData(form);
                const params = new URLSearchParams();
                for (let [key, value] of formData.entries()) {
                    if (value && value.trim() !== '') {
                        params.append(key, value);
                    }
                }
                const baseUrl = form.action || window.location.pathname;
                const newUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
                const targetId = form.dataset.target;
                if (targetId) {
                    const container = document.getElementById(targetId);
                    if (!container) { window.location.href = newUrl; return; }
                    const overlay = container.querySelector('[data-loading-overlay]') || container.querySelector('.loading-overlay');
                    if (overlay) overlay.classList.remove('hidden');
                    container.classList.add('pointer-events-none');
                    try {
                        const response = await fetch(newUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            }
                        });
                        const text = await response.text();
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(text, 'text/html');
                        const newContainer = doc.getElementById(targetId);
                        if (newContainer) {
                            const targetContent = container.querySelector('[data-ajax-content]') || container;
                            const newTargetContent = newContainer.querySelector('[data-ajax-content]') || newContainer;
                            targetContent.innerHTML = newTargetContent.innerHTML;
                            window.history.replaceState(null, '', newUrl);
                        } else {
                            window.location.href = newUrl;
                        }
                    } catch (e) {
                        console.error('Erro ao atualizar tabela via AJAX', e);
                        window.location.href = newUrl;
                    } finally {
                        if (overlay) overlay.classList.add('hidden');
                        container.classList.remove('pointer-events-none');
                    }
                } else {
                    window.location.href = newUrl;
                }
            })();
        });
        // Para campos de texto (input, textarea)
        const textInputs = form.querySelectorAll('input[type="text"], input[type="search"], input[type="email"], textarea');
        textInputs.forEach(input => {
            input.addEventListener('input', applyAutoFilter);
        });
        
        // Para campos de seleção (select)
        const selectInputs = form.querySelectorAll('select');
        selectInputs.forEach(select => {
            select.addEventListener('change', applyAutoFilter);
        });
        
        // Para campos de data
        const dateInputs = form.querySelectorAll('input[type="date"], input[type="datetime-local"]');
        dateInputs.forEach(input => {
            input.addEventListener('change', applyAutoFilter);
        });
        
        // Para checkboxes e radio buttons
        const checkboxRadioInputs = form.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        checkboxRadioInputs.forEach(input => {
            input.addEventListener('change', applyAutoFilter);
        });
    }
});
</script>