@props([
    'id' => 'toggle-status-modal'
])

<div x-data="toggleStatusModal()" x-init="window.toggleStatusModal = this; if (window.__toggleStatusPending) { this.show(window.__toggleStatusPending); delete window.__toggleStatusPending; }">
    <x-modal :name="$id" :title="''" :closable="true">
        <x-slot name="header">
            <h3 class="text-lg font-medium text-gray-900" x-text="actionTitle + ' ' + entityType"></h3>
        </x-slot>

        <div class="space-y-2">
            <p class="text-sm text-gray-600">
                Tem certeza que deseja <span x-text="actionText"></span>
                <template x-if="entityName">
                    <span>"<span x-text="entityName" class="font-medium"></span>"</span>
                </template>
                <template x-if="!entityName">
                    <span>este <span x-text="entityType"></span></span>
                </template>?
            </p>
            <p class="text-xs text-gray-500" x-show="currentStatus">
                Esta ação pode ser revertida posteriormente.
            </p>
        </div>

        <x-slot name="footer">
            <button type="button"
                    x-on:click="confirm()"
                    class="inline-flex justify-center rounded-md px-4 py-2 text-sm font-semibold text-white transition-colors"
                    :class="buttonColor"
                    x-text="actionTitle"></button>
            <button type="button"
                    x-on:click="closeModal($id)"
                    class="inline-flex justify-center rounded-md px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 ring-1 ring-inset ring-gray-300">
                Cancelar
            </button>
        </x-slot>
    </x-modal>
</div>

<script>
function toggleStatusModal() {
    return {
        entityName: '',
        entityType: 'item',
        currentStatus: true,
        route: '',
        method: 'PATCH',

        show(options = {}) {
            this.entityName = options.entityName || '';
            this.entityType = options.entityType || 'item';
            this.currentStatus = options.currentStatus || true;
            this.route = options.route || '';
            this.method = options.method || 'PATCH';
            showModal('{{ $id }}');
        },

        confirm() {
            if (this.route) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = this.route;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfToken);

                if (this.method !== 'POST') {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = this.method;
                    form.appendChild(methodInput);
                }

                document.body.appendChild(form);
                form.submit();
            }
            closeModal('{{ $id }}');
        },

        get actionText() {
            return this.currentStatus ? 'inativar' : 'ativar';
        },

        get actionTitle() {
            return this.currentStatus ? 'Inativar' : 'Ativar';
        },

        get buttonColor() {
            // Padrão alinhado com /alunos: laranja para inativar, verde para ativar
            return this.currentStatus 
                ? 'bg-orange-500 hover:bg-orange-600 active:bg-orange-700' 
                : 'bg-green-500 hover:bg-green-600 active:bg-green-700';
        }
    };
}

window.confirmToggleStatus = function(entityType, entityName, currentStatus, route, method = 'PATCH') {
    const payload = {
        entityType: entityType,
        entityName: entityName,
        currentStatus: currentStatus,
        route: route,
        method: method
    };

    if (window.toggleStatusModal && typeof window.toggleStatusModal.show === 'function') {
        window.toggleStatusModal.show(payload);
        return;
    }

    // Modal ainda não inicializado: guardar a ação pendente e abrir o modal
    window.__toggleStatusPending = payload;
    if (typeof showModal === 'function') { showModal('{{ $id }}'); }

    // Tentar consumir a pendência quando Alpine inicializar ou via polling curto
    const tryConsume = function() {
        if (window.toggleStatusModal && typeof window.toggleStatusModal.show === 'function' && window.__toggleStatusPending) {
            window.toggleStatusModal.show(window.__toggleStatusPending);
            delete window.__toggleStatusPending;
        } else {
            setTimeout(tryConsume, 50);
        }
    };
    setTimeout(tryConsume, 50);
};

// Consumir pendências assim que Alpine estiver pronto
document.addEventListener('alpine:init', function() {
    if (window.__toggleStatusPending && window.toggleStatusModal && typeof window.toggleStatusModal.show === 'function') {
        window.toggleStatusModal.show(window.__toggleStatusPending);
        delete window.__toggleStatusPending;
    }
});
</script>