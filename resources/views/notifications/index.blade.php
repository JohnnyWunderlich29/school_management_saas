@extends('layouts.app')

@section('title', 'Notificações')

@section('content')
    <x-card>
        <!-- Header -->
        <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
            <div>
                <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Notificações</h1>
                <p class="mt-1 text-sm text-gray-600">Gerencie suas notificações do sistema</p>
            </div>
            <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
                @can('usuarios.editar')
                <x-button href="{{ route('notifications.create') }}" color="primary" class="w-full sm:justify-center">
                    <i class="fas fa-plus mr-1"></i> 
                    <span class="hidden md:inline">Nova Notificação</span>
                    <span class="md:hidden">Nova</span>
                </x-button>
                @endcan
            </div>
        </div>

        <!-- Cards de Estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Card Total de Notificações -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5 bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-blue-100 bg-opacity-30 p-3">
                        <i class="fas fa-bell text-white text-xl"></i>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-sm font-medium text-blue-100">Total de Notificações</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-2xl font-semibold text-white">{{ $stats['total'] }}</p>
                            <p class="ml-2 text-sm font-medium text-blue-100">registradas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card Não Lidas -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5 bg-gradient-to-r from-red-500 to-red-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-red-100 bg-opacity-30 p-3">
                        <i class="fas fa-exclamation-circle text-white text-xl"></i>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-sm font-medium text-red-100">Não Lidas</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-2xl font-semibold text-white">{{ $stats['unread'] }}</p>
                            <p class="ml-2 text-sm font-medium text-red-100">pendentes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card Hoje -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5 bg-gradient-to-r from-green-500 to-green-600">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-md bg-green-100 bg-opacity-30 p-3">
                        <i class="fas fa-calendar-day text-white text-xl"></i>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-sm font-medium text-green-100">Hoje</h3>
                        <div class="mt-1 flex items-baseline">
                            <p class="text-2xl font-semibold text-white">{{ $stats['today'] }}</p>
                            <p class="ml-2 text-sm font-medium text-green-100">recebidas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Filtros -->
        <x-collapsible-filter 
            title="Filtros de Notificações" 
            :action="route('notifications.index')" 
            :clear-route="route('notifications.index')"
        >
            <x-filter-field 
                name="type" 
                label="Tipo" 
                type="select"
                :options="[
                    '' => 'Todos os tipos',
                    'info' => 'Informação',
                    'warning' => 'Aviso',
                    'error' => 'Erro',
                    'success' => 'Sucesso',
                    'announcement' => 'Anúncio'
                ]"
            />
            
            <x-filter-field 
                name="status" 
                label="Status" 
                type="select"
                :options="[
                    '' => 'Todos',
                    'unread' => 'Não lidas',
                    'read' => 'Lidas'
                ]"
            />
            
            <x-filter-field 
                name="date_from" 
                label="Data inicial" 
                type="date"
            />
            
            <x-filter-field 
                name="date_to" 
                label="Data final" 
                type="date"
            />
        </x-collapsible-filter>

        <!-- Ações em Massa -->
        @if($notifications->count() > 0)
        <div class="mb-6">
            <!-- Layout para Desktop -->
            <div class="hidden md:flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Selecionar todas</span>
                    </label>
                    <span id="selectedCount" class="text-sm text-gray-600">0 selecionadas</span>
                </div>
                
                <div class="flex space-x-2">
                    <x-button id="markSelectedAsRead" color="primary" size="sm" disabled>
                        <i class="fas fa-check mr-1"></i> Marcar como Lidas
                    </x-button>
                    <x-button id="deleteSelected" color="danger" size="sm" disabled>
                        <i class="fas fa-trash mr-1"></i> Excluir Selecionadas
                    </x-button>
                    <x-button id="markAllAsRead" color="success" size="sm">
                        <i class="fas fa-check-double mr-1"></i> Marcar Todas como Lidas
                    </x-button>
                </div>
            </div>
            
            <!-- Layout para Mobile -->
            <div class="md:hidden space-y-4">
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" id="selectAllMobile" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Selecionar todas</span>
                    </label>
                    <span id="selectedCountMobile" class="text-sm text-gray-600">0 selecionadas</span>
                </div>
                
                <!-- Botões de ação otimizados para mobile -->
                <div class="space-y-2">
                    <button type="button" class="w-full flex items-center justify-center rounded-lg font-medium focus:outline-none transition-colors py-3 px-4 text-sm bg-green-600 text-white hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 min-h-[48px]" id="markAllAsReadMobile">
                        <i class="fas fa-check-double mr-2"></i> Marcar Todas como Lidas
                    </button>
                    
                    <div class="flex space-x-2">
                        <button type="button" class="flex-1 flex items-center justify-center rounded-lg font-medium focus:outline-none transition-colors py-3 px-4 text-sm bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 opacity-50 cursor-not-allowed min-h-[48px]" id="markSelectedAsReadMobile" disabled="">
                            <i class="fas fa-check mr-2"></i> Marcar Selecionadas
                        </button>
                        <button type="button" class="flex-1 flex items-center justify-center rounded-lg font-medium focus:outline-none transition-colors py-3 px-4 text-sm bg-red-600 text-white hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 opacity-50 cursor-not-allowed min-h-[48px]" id="deleteSelectedMobile" disabled="">
                            <i class="fas fa-trash mr-2"></i> Excluir Selecionadas
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Lista de Notificações para Desktop -->
        <x-table class="hidden md:block" :headers="['', 'Tipo', 'Título', 'Mensagem', 'Data', 'Status']" :actions="true">
            @forelse($notifications as $index => $notification)
                <x-table-row :striped="true" :index="$index" data-id="{{ $notification->id }}">
                    <x-table-cell>
                        <input type="checkbox" class="notification-checkbox" value="{{ $notification->id }}">
                    </x-table-cell>
                    
                    <x-table-cell>
                        @switch($notification->type)
                            @case('info')
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-info-circle text-blue-600"></i>
                                </div>
                                @break
                            @case('warning')
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                                </div>
                                @break
                            @case('error')
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-times-circle text-red-600"></i>
                                </div>
                                @break
                            @case('success')
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                                @break
                            @case('announcement')
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-bullhorn text-purple-600"></i>
                                </div>
                                @break
                            @default
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-bell text-gray-600"></i>
                                </div>
                        @endswitch
                    </x-table-cell>
                    
                    <x-table-cell>
                        <div class="flex items-center">
                            <div>
                                @if($notification->action_url)
                                    <div class="font-medium text-gray-900 cursor-pointer hover:text-blue-600 transition-colors" 
                                         onclick="handleNotificationClick({{ $notification->id }}, '{{ $notification->action_url }}')">
                                        {{ $notification->title }}
                                    </div>
                                @else
                                    <div class="font-medium text-gray-900">{{ $notification->title }}</div>
                                @endif
                                @if(!$notification->read_at)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                        Nova
                                    </span>
                                @endif
                            </div>
                        </div>
                    </x-table-cell>
                    
                    <x-table-cell>
                        <div class="text-sm text-gray-600 max-w-xs truncate">
                            {{ $notification->message }}
                        </div>
                    </x-table-cell>
                    
                    <x-table-cell>
                        <div class="text-sm text-gray-500">
                            <div>{{ $notification->formatted_date }}</div>
                            <div class="text-xs">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </x-table-cell>
                    
                    <x-table-cell>
                        @if($notification->read_at)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i> Lida
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i> Não lida
                            </span>
                        @endif
                    </x-table-cell>
                    
                    <x-table-cell class="text-right">
                        <div class="flex items-center justify-end space-x-2">
                            @if(!$notification->read_at)
                                <button onclick="markAsRead({{ $notification->id }})" 
                                        class="text-blue-600 hover:text-blue-800" 
                                        title="Marcar como lida">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                            
                            <button onclick="deleteNotification({{ $notification->id }})" 
                                    class="text-red-600 hover:text-red-800" 
                                    title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </x-table-cell>
                </x-table-row>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-12">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-bell-slash text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma notificação encontrada</h3>
                        <p class="text-gray-600">Não há notificações para exibir no momento.</p>
                        
                        @if(request()->hasAny(['type', 'status']))
                            <a href="{{ route('notifications.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium mt-4">
                                <i class="fas fa-times mr-2"></i>
                                Limpar filtros
                            </a>
                        @endif
                    </td>
                </tr>
            @endforelse
        </x-table>
        
        <!-- Layout Mobile Otimizado com Cards -->
        <div class="md:hidden space-y-4">
            @forelse($notifications as $notification)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm @if(!$notification->read_at) border-l-4 border-l-blue-500 @endif">
                    <!-- Header do card com checkbox -->
                    <div class="flex items-start mb-3">
                        <input type="checkbox" class="notification-checkbox mt-1 mr-3" value="{{ $notification->id }}">
                        
                        @switch($notification->type)
                            @case('info')
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                                </div>
                                @break
                            @case('warning')
                                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
                                </div>
                                @break
                            @case('error')
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-times-circle text-red-600 text-lg"></i>
                                </div>
                                @break
                            @case('success')
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-600 text-lg"></i>
                                </div>
                                @break
                            @case('announcement')
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-bullhorn text-purple-600 text-lg"></i>
                                </div>
                                @break
                            @default
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-bell text-gray-600 text-lg"></i>
                                </div>
                        @endswitch
                        
                        <div class="flex-1 min-w-0">
                            @if($notification->action_url)
                                <h3 class="font-semibold text-gray-900 text-base leading-tight cursor-pointer hover:text-blue-600 transition-colors" 
                                    onclick="handleNotificationClick({{ $notification->id }}, '{{ $notification->action_url }}')">
                                    {{ $notification->title }}
                                </h3>
                            @else
                                <h3 class="font-semibold text-gray-900 text-base leading-tight">{{ $notification->title }}</h3>
                            @endif
                            @if(!$notification->read_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                    Nova
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Mensagem -->
                    <div class="mb-3">
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $notification->message }}</p>
                    </div>
                    
                    <!-- Informações em grid -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-600 mb-1">Data</div>
                            <div class="text-sm font-medium text-gray-900">{{ $notification->formatted_date }}</div>
                            <div class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="text-xs text-gray-600 mb-1">Status</div>
                            @if($notification->read_at)
                                <div class="text-sm font-medium text-green-700">
                                    <i class="fas fa-check mr-1"></i> Lida
                                </div>
                            @else
                                <div class="text-sm font-medium text-yellow-700">
                                    <i class="fas fa-clock mr-1"></i> Não lida
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Botões de ação com touch targets otimizados -->
                    <div class="flex space-x-2">
                        @if(!$notification->read_at)
                            <button onclick="markAsRead({{ $notification->id }})" 
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                                <i class="fas fa-check mr-2"></i>
                                Marcar como Lida
                            </button>
                        @endif
                        
                        <button onclick="deleteNotification({{ $notification->id }})" 
                                class="@if(!$notification->read_at) flex-shrink-0 @else flex-1 @endif bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                            <i class="fas fa-trash @if($notification->read_at) mr-2 @endif"></i>
                            @if($notification->read_at) Excluir @endif
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-bell-slash text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma notificação encontrada</h3>
                    <p class="text-gray-600">Não há notificações para exibir no momento.</p>
                    
                    @if(request()->hasAny(['type', 'status']))
                        <a href="{{ route('notifications.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium mt-4">
                            <i class="fas fa-times mr-2"></i>
                            Limpar filtros
                        </a>
                    @endif
                </div>
            @endforelse
        </div>
        
        @if($notifications->hasPages())
            <div class="mt-4">
                {{ $notifications->links('components.pagination') }}
            </div>
        @endif
    </x-card>

@endsection

@push('scripts')
<script>
// Variáveis globais
let selectedNotifications = [];

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    initializeCheckboxes();
    initializeButtons();
});

// Inicializar checkboxes
function initializeCheckboxes() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const selectAllCheckboxMobile = document.getElementById('selectAllMobile');
    const notificationCheckboxes = document.querySelectorAll('.notification-checkbox');
    
    // Selecionar/deselecionar todas - Desktop
    selectAllCheckbox?.addEventListener('change', function() {
        notificationCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        // Sincronizar com mobile
        if (selectAllCheckboxMobile) {
            selectAllCheckboxMobile.checked = this.checked;
        }
        updateSelectedNotifications();
    });
    
    // Selecionar/deselecionar todas - Mobile
    selectAllCheckboxMobile?.addEventListener('change', function() {
        notificationCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        // Sincronizar com desktop
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = this.checked;
        }
        updateSelectedNotifications();
    });
    
    // Atualizar seleção individual
    notificationCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedNotifications();
            
            // Atualizar estado do "selecionar todas"
            const allChecked = Array.from(notificationCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(notificationCheckboxes).some(cb => cb.checked);
            
            // Sincronizar ambos os checkboxes "selecionar todas"
            [selectAllCheckbox, selectAllCheckboxMobile].forEach(checkbox => {
                if (checkbox) {
                    checkbox.checked = allChecked;
                    checkbox.indeterminate = someChecked && !allChecked;
                }
            });
        });
    });
}

// Atualizar lista de notificações selecionadas
function updateSelectedNotifications() {
    const checkboxes = document.querySelectorAll('.notification-checkbox:checked');
    selectedNotifications = Array.from(checkboxes).map(cb => cb.value);
    
    // Atualizar contadores (desktop e mobile)
    const countElement = document.getElementById('selectedCount');
    const countElementMobile = document.getElementById('selectedCountMobile');
    const countText = `${selectedNotifications.length} selecionadas`;
    
    if (countElement) countElement.textContent = countText;
    if (countElementMobile) countElementMobile.textContent = countText;
    
    // Habilitar/desabilitar botões
    const hasSelection = selectedNotifications.length > 0;
    
    // Botões desktop
    const markSelectedBtn = document.getElementById('markSelectedAsRead');
    const deleteSelectedBtn = document.getElementById('deleteSelected');
    
    if (markSelectedBtn) markSelectedBtn.disabled = !hasSelection;
    if (deleteSelectedBtn) deleteSelectedBtn.disabled = !hasSelection;
    
    // Botões mobile
    const markSelectedBtnMobile = document.getElementById('markSelectedAsReadMobile');
    const deleteSelectedBtnMobile = document.getElementById('deleteSelectedMobile');
    
    if (markSelectedBtnMobile) {
        markSelectedBtnMobile.disabled = !hasSelection;
        if (hasSelection) {
            markSelectedBtnMobile.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            markSelectedBtnMobile.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
    
    if (deleteSelectedBtnMobile) {
        deleteSelectedBtnMobile.disabled = !hasSelection;
        if (hasSelection) {
            deleteSelectedBtnMobile.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            deleteSelectedBtnMobile.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
}

// Inicializar botões
function initializeButtons() {
    // Marcar selecionadas como lidas - Desktop
    document.getElementById('markSelectedAsRead')?.addEventListener('click', function() {
        if (selectedNotifications.length === 0) return;
        
        if (confirm(`Marcar ${selectedNotifications.length} notificações como lidas?`)) {
            markMultipleAsRead(selectedNotifications);
        }
    });
    
    // Marcar selecionadas como lidas - Mobile
    document.getElementById('markSelectedAsReadMobile')?.addEventListener('click', function() {
        if (selectedNotifications.length === 0) return;
        
        if (confirm(`Marcar ${selectedNotifications.length} notificações como lidas?`)) {
            markMultipleAsRead(selectedNotifications);
        }
    });
    
    // Excluir selecionadas - Desktop
    document.getElementById('deleteSelected')?.addEventListener('click', function() {
        if (selectedNotifications.length === 0) return;
        
        if (confirm(`Excluir ${selectedNotifications.length} notificações? Esta ação não pode ser desfeita.`)) {
            deleteMultipleNotifications(selectedNotifications);
        }
    });
    
    // Excluir selecionadas - Mobile
    document.getElementById('deleteSelectedMobile')?.addEventListener('click', function() {
        if (selectedNotifications.length === 0) return;
        
        if (confirm(`Excluir ${selectedNotifications.length} notificações? Esta ação não pode ser desfeita.`)) {
            deleteMultipleNotifications(selectedNotifications);
        }
    });
    
    // Marcar todas como lidas - Desktop
    document.getElementById('markAllAsRead')?.addEventListener('click', function() {
        if (confirm('Marcar todas as suas notificações como lidas?')) {
            markAllAsRead();
        }
    });
    
    // Marcar todas como lidas - Mobile
    document.getElementById('markAllAsReadMobile')?.addEventListener('click', function() {
        if (confirm('Marcar todas as suas notificações como lidas?')) {
            markAllAsRead();
        }
    });
}

// Marcar notificação individual como lida
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Atualizar visualmente a notificação
            const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('bg-white');
                notificationElement.classList.add('bg-gray-50');
                
                // Remover botão "marcar como lida"
                const markButton = notificationElement.querySelector('button[onclick*="markAsRead"]');
                if (markButton) {
                    markButton.remove();
                }
                
                // Remover indicador de não lida
                const indicator = notificationElement.querySelector('.bg-blue-600.rounded-full');
                if (indicator) {
                    indicator.remove();
                }
            }
            
            showToast('Notificação marcada como lida!', 'success');
        } else {
            showToast('Erro ao marcar notificação como lida.', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao marcar notificação como lida.', 'error');
    });
}

// Marcar múltiplas notificações como lidas
function markMultipleAsRead(notificationIds) {
    fetch('/notifications/mark-multiple-read', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notification_ids: notificationIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recarregar a página para atualizar a lista
            location.reload();
        } else {
            showToast('Erro ao marcar notificações como lidas.', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao marcar notificações como lidas.', 'error');
    });
}

// Marcar todas as notificações como lidas
function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast('Erro ao marcar todas as notificações como lidas.', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao marcar todas as notificações como lidas.', 'error');
    });
}

// Excluir notificação individual
function deleteNotification(notificationId) {
    if (!confirm('Excluir esta notificação? Esta ação não pode ser desfeita.')) {
        return;
    }
    
    fetch(`/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remover elemento da página
            const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.remove();
            }
            
            showToast('Notificação excluída com sucesso!', 'success');
            
            // Verificar se não há mais notificações
            const remainingNotifications = document.querySelectorAll('.notification-item');
            if (remainingNotifications.length === 0) {
                location.reload();
            }
        } else {
            showToast('Erro ao excluir notificação.', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao excluir notificação.', 'error');
    });
}

// Excluir múltiplas notificações
function deleteMultipleNotifications(notificationIds) {
    fetch('/notifications/delete-multiple', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notification_ids: notificationIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast('Erro ao excluir notificações.', 'error');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showToast('Erro ao excluir notificações.', 'error');
    });
}

// Lidar com clique na notificação
function handleNotificationClick(notificationId, actionUrl) {
    // Marcar como lida se não estiver lida
    const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
    const isUnread = notificationElement && notificationElement.querySelector('.bg-blue-100');
    
    if (isUnread) {
        // Marcar como lida primeiro
        fetch(`/notifications/${notificationId}/mark-read`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirecionar para a URL de ação
                window.location.href = actionUrl;
            } else {
                // Mesmo se falhar ao marcar como lida, redirecionar
                window.location.href = actionUrl;
            }
        })
        .catch(error => {
            console.error('Erro ao marcar como lida:', error);
            // Mesmo com erro, redirecionar
            window.location.href = actionUrl;
        });
    } else {
        // Se já está lida, apenas redirecionar
        window.location.href = actionUrl;
    }
}

// Mostrar toast de notificação
function showToast(message, type = 'info') {
    // Criar elemento do toast
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg text-white transition-all duration-300 transform translate-x-full`;
    
    // Definir cor baseada no tipo
    switch (type) {
        case 'success':
            toast.classList.add('bg-green-600');
            break;
        case 'error':
            toast.classList.add('bg-red-600');
            break;
        case 'warning':
            toast.classList.add('bg-yellow-600');
            break;
        default:
            toast.classList.add('bg-blue-600');
    }
    
    toast.textContent = message;
    
    // Adicionar ao DOM
    document.body.appendChild(toast);
    
    // Animar entrada
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    // Remover após 3 segundos
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
</script>
@endpush