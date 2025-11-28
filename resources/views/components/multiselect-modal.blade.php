@props([
    'title' => 'Selecionar Itens',
    'items' => [],
    'selectedItems' => [],
    'itemKey' => 'id',
    'itemLabel' => 'name',
    'inputName' => 'selected_items',
    'modalId' => 'multiselect-modal',
    'searchPlaceholder' => 'Buscar...',
    'buttonText' => 'Selecionar',
    'buttonColor' => 'primary'
])

<!-- Botão para abrir o modal -->
<div class="mb-2">
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $title }}</label>
    <x-button 
            type="button" 
            color="primary"
            onclick="showModal('{{ $modalId }}')"
            class="w-full justify-between"
        >
        <span>Selecionar {{ $title }}</span>
        <span id="{{ $modalId }}-count" class="bg-white bg-opacity-20 px-2 py-1 rounded text-xs">
            {{ is_countable($selectedItems) ? count($selectedItems) : 0 }}
        </span>
    </x-button>
</div>

<!-- Modal usando estrutura padrão do sistema -->
<div class="fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full z-50 hidden" id="{{ $modalId }}" data-input-name="{{ $inputName }}">
    <div class="relative mx-auto mt-16 mb-8 p-5 border w-[70%] max-w-4xl shadow-lg rounded-md bg-white" style="margin-left: 15%; margin-right: 15%;">
        <!-- Modal Header -->
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-bold text-gray-900">{{ $title }}</h3>
            <button type="button" onclick="closeModal('{{ $modalId }}')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="mt-4">
            <!-- Campo de busca -->
            <div class="mb-4">
                <input type="text" 
                       id="{{ $modalId }}-search"
                       placeholder="{{ $searchPlaceholder }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       autocomplete="off">
            </div>

            <!-- Contador de selecionados -->
            <div class="mb-4 text-sm text-gray-600">
                <span id="{{ $modalId }}-selected-count">{{ is_countable($selectedItems) ? count($selectedItems) : 0 }}</span> item(s) selecionado(s)
            </div>

            <!-- Lista de itens -->
            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-md">
                @foreach($items as $item)
                    <div class="item-row p-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer" 
                         data-search="{{ strtolower($item[$itemLabel]) }}">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="{{ $inputName }}[{{ $item[$itemKey] }}]"
                                   value="{{ $item[$itemKey] }}"
                                   class="item-checkbox mr-3 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                   @if(in_array($item[$itemKey], (array) $selectedItems)) checked @endif>
                            <span class="text-gray-900">{{ $item[$itemLabel] }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex flex-wrap gap-2 mt-6 flex justify-end space-x-3 pt-4 border-t">
            <x-button type="button" 
                      onclick="clearAllSelections('{{ $modalId }}')"
                      color="danger">
                Limpar Tudo
            </x-button>
            <x-button type="button" 
                      onclick="selectAllVisible('{{ $modalId }}')"
                      color="primary">
                Selecionar Visíveis
            </x-button>
            <x-button type="button" 
                      onclick="closeModal('{{ $modalId }}')"
                      color="secondary">
                Cancelar
            </x-button>
            <x-button type="button" 
                      onclick="closeModal('{{ $modalId }}')"
                      color="primary">
                Confirmar
            </x-button>
        </div>
    </div>
</div>

<script>
// Função para mostrar modal
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Previne scroll da página
    updateSelectedCount(modalId);
    
    // Focar no input de busca após um pequeno delay
    setTimeout(() => {
        const searchInput = document.getElementById(`${modalId}-search`);
        if (searchInput) {
            searchInput.focus();
        }
    }, 100);
}

// Função para fechar modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // Restaura scroll da página
    updateCounter(modalId);
}

// Função para alternar seleção ao clicar no item
function toggleSelection(modalId, itemKey, itemLabel) {
    const modal = document.getElementById(modalId);
    const checkbox = modal.querySelector(`input[name*="[${itemKey}]"], input[value="${itemKey}"]`);
    if (checkbox) {
        checkbox.checked = !checkbox.checked;
        updateSelectedCount(modalId);
    }
}

// Função para atualizar contador no botão
function updateCounter(modalId) {
    const checkboxes = document.querySelectorAll(`#${modalId} .item-checkbox:checked`);
    const counter = document.getElementById(`${modalId}-count`);
    const count = checkboxes.length;
    counter.textContent = count;
}

// Função para atualizar contador de selecionados no modal
function updateSelectedCount(modalId) {
    const checkboxes = document.querySelectorAll(`#${modalId} .item-checkbox:checked`);
    const counter = document.getElementById(`${modalId}-selected-count`);
    counter.textContent = checkboxes.length;
    
    // Atualizar inputs hidden para envio no formulário
    const modal = document.getElementById(modalId);
    const inputName = modal.getAttribute('data-input-name');
    
    if (inputName) {
        // Remover inputs hidden existentes
        const existingInputs = document.querySelectorAll(`input[name="${inputName}[]"]`);
        existingInputs.forEach(input => input.remove());
        
        // Criar novos inputs hidden para os itens selecionados
        const form = modal.closest('form') || document.querySelector('form');
        if (form) {
            checkboxes.forEach(checkbox => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = `${inputName}[]`;
                hiddenInput.value = checkbox.value;
                form.appendChild(hiddenInput);
            });
        }
        
        console.log(`Disciplinas selecionadas para ${inputName}:`, Array.from(checkboxes).map(cb => cb.value));
    }
}

// Função para busca
function setupSearch(modalId) {
    const searchInput = document.getElementById(`${modalId}-search`);
    const modal = document.getElementById(modalId);
    
    if (searchInput && modal) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const items = modal.querySelectorAll('.item-row');
            
            items.forEach(item => {
                const searchData = item.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
}

// Função para limpar todas as seleções
function clearAllSelections(modalId) {
    const modal = document.getElementById(modalId);
    const checkboxes = modal.querySelectorAll('.item-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    updateSelectedCount(modalId);
}

// Função para selecionar todos os itens visíveis
function selectAllVisible(modalId) {
    const modal = document.getElementById(modalId);
    const visibleRows = modal.querySelectorAll('.item-row[style="display: block"], .item-row:not([style*="display: none"])');
    
    visibleRows.forEach(row => {
        const checkbox = row.querySelector('.item-checkbox');
        if (checkbox) {
            checkbox.checked = true;
        }
    });
    
    updateSelectedCount(modalId);
}

// Configurar eventos quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Configurar busca para todos os modais multiselect
    const modals = document.querySelectorAll('[id*="multiselect"], [id*="disciplinas"]');
    modals.forEach(modal => {
        const modalId = modal.id;
        setupSearch(modalId);
        
        // Configurar eventos de checkbox
        const checkboxes = modal.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedCount(modalId);
            });
        });
        
        // Permitir clique na linha inteira, mas não interferir com inputs
        const rows = modal.querySelectorAll('.item-row');
        rows.forEach(row => {
            row.addEventListener('click', function(e) {
                // Não interferir com checkboxes ou inputs diretamente
                if (e.target.type !== 'checkbox' && e.target.tagName !== 'INPUT' && !e.target.closest('input')) {
                    const checkbox = this.querySelector('.item-checkbox');
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        // Disparar evento change para manter consistência
                        checkbox.dispatchEvent(new Event('change'));
                    }
                }
            });
        });
        
        // Garantir que os labels funcionem corretamente
        const labels = modal.querySelectorAll('.item-row label');
        labels.forEach(label => {
            label.addEventListener('click', function(e) {
                e.stopPropagation(); // Evitar duplo clique
            });
        });
    });
});

// Fechar modal ao clicar fora dele
document.addEventListener('click', function(e) {
    // Verificar se clicou no overlay (fundo do modal)
    if (e.target.classList.contains('bg-black') && e.target.classList.contains('bg-opacity-60')) {
        const modal = e.target;
        const modalId = modal.id;
        
        if (modalId) {
            closeModal(modalId);
        }
    }
    
    // Verificar se clicou em um modal que está visível
    const modals = document.querySelectorAll('[id*="multiselect"], [id*="disciplinas"]');
    modals.forEach(modal => {
        if (!modal.classList.contains('hidden') && e.target === modal) {
            closeModal(modal.id);
        }
    });
});
</script>