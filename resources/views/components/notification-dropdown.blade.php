<div class="relative" x-data="{
    open: false,
    notifications: [],
    count: 0,

    markAsRead(notificationId) {
        fetch('/notifications/' + notificationId + '/read', {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.notifications = this.notifications.filter(n => n.id !== notificationId);
                    this.count = this.notifications.length;
                }
            });
    },

    markAllAsRead() {
        fetch('/notifications/mark-all-read', {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.notifications = [];
                    this.count = 0;
                }
            });
    },

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInMinutes = Math.floor((now - date) / (1000 * 60));

        if (diffInMinutes < 1) {
            return 'Agora mesmo';
        } else if (diffInMinutes < 60) {
            return diffInMinutes + ' min atrás';
        } else if (diffInMinutes < 1440) {
            const hours = Math.floor(diffInMinutes / 60);
            return hours + 'h atrás';
        } else {
            const days = Math.floor(diffInMinutes / 1440);
            return days + 'd atrás';
        }
    }
}" x-init="open = false; console.log('🔔 Iniciando componente de notificações...');

fetch('/notifications/unread', {
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').getAttribute('content') : ''
        }
    })
    .then(function(response) {
        console.log('📡 Response status:', response.status);
        console.log('📡 Response ok:', response.ok);

        if (!response.ok) {
            console.error('❌ Erro na resposta:', response.status, response.statusText);
            throw new Error('HTTP ' + response.status + ': ' + response.statusText);
        }
        return response.json();
    })
    .then(function(data) {
        notifications = data.notifications || [];
        count = data.count || 0;
        console.log('📝 Notificações carregadas:', this.notifications);
    }.bind(this))
    .catch(function(error) {
        console.error('❌ Erro ao carregar notificações:', error);
        // Se for erro de token CSRF, tentar recarregar a página após um delay
        if (error.message.includes('419') || error.message.includes('CSRF')) {
            console.log('🔄 Erro de CSRF detectado no carregamento inicial, recarregando página em 2 segundos...');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    });

setInterval(function() {
    fetch('/notifications/unread', {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').getAttribute('content') : ''
            }
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ': ' + response.statusText);
            }
            return response.json();
        })
        .then(function(data) {
            notifications = data.notifications || [];
            count = data.count || 0;
        }.bind(this))
        .catch(function(error) {
            console.error('❌ Erro ao atualizar notificações:', error);
            // Se for erro de token CSRF, tentar recarregar a página após um delay
            if (error.message.includes('419') || error.message.includes('CSRF')) {
                console.log('🔄 Erro de CSRF detectado, recarregando página em 2 segundos...');
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        });
}.bind(this), 5000);">
    <!-- Botão de Notificações -->
    <button @click="open = !open"
        class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg">
        <i class="fas fa-bell text-xl"></i>

        <!-- Badge de contagem -->
        <span x-show="count > 0" x-text="count"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
        </span>

        <!-- Badge quando não há notificações (cinza claro) -->
        <span x-show="count === 0"
            class="absolute -top-1 -right-1 bg-gray-300 text-gray-600 text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium">
            0
        </span>
    </button>

    <!-- Dropdown de Notificações -->
    <div x-show="open" @click.away="open = false" style="display: none; width: 300px;" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 z-50">

        <!-- Cabeçalho -->
        <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-900">Notificações</h3>
            <button @click="markAllAsRead()" x-show="notifications.length > 0"
                class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                Marcar todas como lidas
            </button>
        </div>

        <!-- Lista de Notificações -->
        <div class="max-h-96 overflow-y-auto">
            <div x-show="notifications || notifications.length === 0">
                <template x-for="notification in notifications" :key="notification.id">
                    <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                        @click="markAsRead(notification.id)">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 mr-2">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                                <p class="text-xs text-gray-400 mt-1" x-text="formatDate(notification.created_at)"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <div x-show="!notifications || notifications.length === 0" class="px-4 py-8 text-center text-gray-500">
                <i class="fas fa-bell-slash text-2xl mb-2"></i>
                <p class="text-sm">Nenhuma notificação nova</p>
            </div>


        </div>

        <!-- Rodapé -->
        <div class="px-4 py-3 border-t border-gray-200">
            <a href="{{ route('notifications.index') }}"
                class="block text-center text-sm text-blue-600 hover:text-blue-800 font-medium">
                <span x-show="count <= 5">Ver todas as notificações</span>
                <span x-show="count > 5" x-text="`Ver todas as ${count} notificações`"></span>
            </a>
        </div>
    </div>
</div>
