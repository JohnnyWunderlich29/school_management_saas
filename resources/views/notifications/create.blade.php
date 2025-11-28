@extends('layouts.app')

@section('title', 'Nova Notificação')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Nova Notificação</h1>
            <p class="text-gray-600 mt-1">Crie uma nova notificação para os usuários</p>
        </div>
        
        <a href="{{ route('notifications.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Voltar
        </a>
    </div>

    <!-- Formulário -->
    <div class="bg-white rounded-lg shadow-md">
        <form action="{{ route('notifications.store') }}" method="POST" class="p-6">
            @csrf
            
            <!-- Tipo de Notificação -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Notificação <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type" required 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror">
                        <option value="">Selecione o tipo</option>
                        <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>Informação</option>
                        <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Sucesso</option>
                        <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Aviso</option>
                        <option value="error" {{ old('type') === 'error' ? 'selected' : '' }}>Erro</option>
                        <option value="announcement" {{ old('type') === 'announcement' ? 'selected' : '' }}>Anúncio</option>
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="is_global" class="block text-sm font-medium text-gray-700 mb-2">
                        Destinatários
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="is_global" value="1" 
                                   {{ old('is_global', '0') === '1' ? 'checked' : '' }}
                                   class="text-blue-600 focus:ring-blue-500" onchange="toggleUserSelection()">
                            <span class="ml-2 text-sm text-gray-700">Notificação Global (todos os usuários)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="is_global" value="0" 
                                   {{ old('is_global', '0') === '0' ? 'checked' : '' }}
                                   class="text-blue-600 focus:ring-blue-500" onchange="toggleUserSelection()">
                            <span class="ml-2 text-sm text-gray-700">Usuário Específico</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Seleção de Usuário -->
            <div id="userSelection" class="mb-6 {{ old('is_global', '0') === '1' ? 'hidden' : '' }}">
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Usuário <span class="text-red-500">*</span>
                </label>
                <select name="user_id" id="user_id" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('user_id') border-red-500 @enderror">
                    <option value="">Selecione o usuário</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Título -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Título <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       maxlength="255" placeholder="Digite o título da notificação"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Mensagem -->
            <div class="mb-6">
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    Mensagem <span class="text-red-500">*</span>
                </label>
                <textarea name="message" id="message" rows="4" required
                          placeholder="Digite a mensagem da notificação"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                @error('message')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Ação (Opcional) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="action_text" class="block text-sm font-medium text-gray-700 mb-2">
                        Texto do Botão de Ação (Opcional)
                    </label>
                    <input type="text" name="action_text" id="action_text" value="{{ old('action_text') }}"
                           maxlength="50" placeholder="Ex: Ver detalhes, Acessar"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('action_text') border-red-500 @enderror">
                    @error('action_text')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="action_url" class="block text-sm font-medium text-gray-700 mb-2">
                        URL de Ação (Opcional)
                    </label>
                    <input type="url" name="action_url" id="action_url" value="{{ old('action_url') }}"
                           placeholder="https://exemplo.com/pagina"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('action_url') border-red-500 @enderror">
                    @error('action_url')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Preview -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Preview da Notificação</h3>
                <div id="notificationPreview" class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <i id="previewIcon" class="fas fa-bell text-gray-400 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <h4 id="previewTitle" class="font-semibold text-gray-900">Título da notificação</h4>
                            <p id="previewMessage" class="text-gray-600 mt-1">Mensagem da notificação</p>
                            <div class="mt-2">
                                <span id="previewType" class="inline-block bg-gray-200 text-gray-800 px-2 py-1 rounded-full text-xs">Tipo</span>
                                <span id="previewGlobal" class="hidden inline-block bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs ml-2">
                                    <i class="fas fa-globe mr-1"></i>Global
                                </span>
                            </div>
                            <div id="previewAction" class="mt-3 hidden">
                                <a href="#" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    <span id="previewActionText">Ver mais</span>
                                    <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Botões -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('notifications.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Enviar Notificação
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Alternar seleção de usuário
function toggleUserSelection() {
    const isGlobal = document.querySelector('input[name="is_global"]:checked').value === '1';
    const userSelection = document.getElementById('userSelection');
    const userSelect = document.getElementById('user_id');
    
    if (isGlobal) {
        userSelection.classList.add('hidden');
        userSelect.removeAttribute('required');
        userSelect.value = '';
    } else {
        userSelection.classList.remove('hidden');
        userSelect.setAttribute('required', 'required');
    }
    
    updatePreview();
}

// Atualizar preview em tempo real
function updatePreview() {
    const type = document.getElementById('type').value || 'info';
    const title = document.getElementById('title').value || 'Título da notificação';
    const message = document.getElementById('message').value || 'Mensagem da notificação';
    const actionText = document.getElementById('action_text').value;
    const actionUrl = document.getElementById('action_url').value;
    const isGlobal = document.querySelector('input[name="is_global"]:checked').value === '1';
    
    // Atualizar ícone
    const iconElement = document.getElementById('previewIcon');
    const iconClasses = {
        'info': 'fas fa-info-circle text-blue-600',
        'success': 'fas fa-check-circle text-green-600',
        'warning': 'fas fa-exclamation-triangle text-yellow-600',
        'error': 'fas fa-times-circle text-red-600',
        'announcement': 'fas fa-bullhorn text-purple-600'
    };
    iconElement.className = iconClasses[type] || iconClasses['info'];
    
    // Atualizar conteúdo
    document.getElementById('previewTitle').textContent = title;
    document.getElementById('previewMessage').textContent = message;
    document.getElementById('previewType').textContent = type.charAt(0).toUpperCase() + type.slice(1);
    
    // Mostrar/ocultar badge global
    const globalBadge = document.getElementById('previewGlobal');
    if (isGlobal) {
        globalBadge.classList.remove('hidden');
        globalBadge.classList.add('inline-block');
    } else {
        globalBadge.classList.add('hidden');
        globalBadge.classList.remove('inline-block');
    }
    
    // Mostrar/ocultar ação
    const actionElement = document.getElementById('previewAction');
    const actionTextElement = document.getElementById('previewActionText');
    
    if (actionText && actionUrl) {
        actionElement.classList.remove('hidden');
        actionTextElement.textContent = actionText;
    } else {
        actionElement.classList.add('hidden');
    }
}

// Inicializar eventos
document.addEventListener('DOMContentLoaded', function() {
    // Eventos para atualizar preview
    document.getElementById('type').addEventListener('change', updatePreview);
    document.getElementById('title').addEventListener('input', updatePreview);
    document.getElementById('message').addEventListener('input', updatePreview);
    document.getElementById('action_text').addEventListener('input', updatePreview);
    document.getElementById('action_url').addEventListener('input', updatePreview);
    
    // Eventos para radio buttons
    document.querySelectorAll('input[name="is_global"]').forEach(radio => {
        radio.addEventListener('change', updatePreview);
    });
    
    // Preview inicial
    updatePreview();
});
</script>
@endpush