<form id="edit-grupo-form" method="POST" action="{{ route('admin.grupos.update', $grupo) }}">
    @csrf
    @method('PUT')
    
    <div class="space-y-4">
        <!-- Nome -->
        <div>
            <label for="edit_nome" class="block text-sm font-medium text-gray-700 mb-1">
                Nome do Grupo <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="edit_nome" 
                   name="nome" 
                   value="{{ $grupo->nome }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   required>
        </div>

        <!-- Código -->
        <div>
            <label for="edit_codigo" class="block text-sm font-medium text-gray-700 mb-1">
                Código <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="edit_codigo" 
                   name="codigo" 
                   value="{{ $grupo->codigo }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   required>
        </div>

        <!-- Modalidade de Ensino -->
        <div>
            <label for="edit_modalidade_ensino_id" class="block text-sm font-medium text-gray-700 mb-1">
                Modalidade de Ensino <span class="text-red-500">*</span>
            </label>
            <select id="edit_modalidade_ensino_id" 
                    name="modalidade_ensino_id" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
                <option value="">Selecione uma modalidade de ensino</option>
                @foreach($modalidades as $modalidade)
                    <option value="{{ $modalidade->id }}" {{ $grupo->modalidade_ensino_id == $modalidade->id ? 'selected' : '' }}>
                        {{ $modalidade->nome }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Idades -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="edit_idade_minima" class="block text-sm font-medium text-gray-700 mb-1">
                    Idade Mínima
                </label>
                <input type="number" 
                       id="edit_idade_minima" 
                       name="idade_minima" 
                       value="{{ $grupo->idade_minima }}"
                       min="0" 
                       max="25"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label for="edit_idade_maxima" class="block text-sm font-medium text-gray-700 mb-1">
                    Idade Máxima
                </label>
                <input type="number" 
                       id="edit_idade_maxima" 
                       name="idade_maxima" 
                       value="{{ $grupo->idade_maxima }}"
                       min="0" 
                       max="25"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Descrição -->
        <div>
            <label for="edit_descricao" class="block text-sm font-medium text-gray-700 mb-1">
                Descrição
            </label>
            <textarea id="edit_descricao" 
                      name="descricao" 
                      rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ $grupo->descricao }}</textarea>
        </div>

        <!-- Ordem -->
        <div>
            <label for="edit_ordem" class="block text-sm font-medium text-gray-700 mb-1">
                Ordem
            </label>
            <input type="number" 
                   id="edit_ordem" 
                   name="ordem" 
                   value="{{ $grupo->ordem }}"
                   min="1"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Status Ativo -->
        <div class="flex items-center">
            <input type="checkbox" 
                   id="edit_ativo" 
                   name="ativo" 
                   value="1"
                   {{ $grupo->ativo ? 'checked' : '' }}
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="edit_ativo" class="ml-2 block text-sm text-gray-900">
                Grupo ativo
            </label>
        </div>
    </div>

    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
        <x-button type="button" color="secondary" @click="$dispatch('close-modal')">
            Cancelar
        </x-button>
        <x-button type="submit" color="primary">
            Atualizar Grupo
        </x-button>
    </div>
</form>

<script>
document.getElementById('edit-grupo-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    submitButton.disabled = true;
    submitButton.textContent = 'Atualizando...';
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            document.dispatchEvent(new CustomEvent('close-modal'));
            
            // Recarregar a página para mostrar as alterações
            window.location.reload();
        } else {
            // Mostrar erros
            if (data.errors) {
                let errorMessage = 'Erros encontrados:\n';
                Object.values(data.errors).forEach(errors => {
                    errors.forEach(error => {
                        errorMessage += '- ' + error + '\n';
                    });
                });
                alert(errorMessage);
            } else {
                alert(data.message || 'Erro ao atualizar grupo');
            }
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar grupo');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
});
</script>