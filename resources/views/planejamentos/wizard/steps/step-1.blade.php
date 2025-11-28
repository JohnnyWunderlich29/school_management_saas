<!-- Etapa 1: Configuração Básica -->
<form id="step-1-form">
<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-medium text-gray-900 flex items-center">
            <i class="fas fa-cog text-blue-600 mr-2"></i>
            Configuração Básica
        </h3>
        <p class="text-gray-600 mt-1">Defina as informações fundamentais do planejamento</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Modalidade de Ensino -->
        <div>
            <label for="modalidade_ensino_id" class="block text-sm font-medium text-gray-700 mb-2">
                Modalidade de Ensino <span class="text-red-500">*</span>
            </label>
            <select name="modalidade_ensino_id" id="modalidade_ensino_id" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione uma modalidade</option>
                
                @if($modalidades_bncc->count() > 0)
                    <optgroup label="📚 Modalidades BNCC">
                        @foreach($modalidades_bncc as $modalidade)
                            <option value="{{ $modalidade->id }}" 
                                    {{ (old('modalidade_ensino_id', $planejamento->modalidade_ensino_id ?? '') == $modalidade->id) ? 'selected' : '' }}>
                                {{ $modalidade->codigo }} - {{ $modalidade->nome }}
                            </option>
                        @endforeach
                    </optgroup>
                @endif
                
                @if($modalidades_personalizadas->count() > 0)
                    <optgroup label="🏫 Modalidades Personalizadas">
                        @foreach($modalidades_personalizadas as $modalidade)
                            <option value="{{ $modalidade->id }}" 
                                    {{ (old('modalidade_ensino_id', $planejamento->modalidade_ensino_id ?? '') == $modalidade->id) ? 'selected' : '' }}>
                                {{ $modalidade->codigo }} - {{ $modalidade->nome }}
                            </option>
                        @endforeach
                    </optgroup>
                @endif
            </select>
            @error('modalidade_ensino_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nível de Ensino -->
        <div>
            <label for="nivel_ensino_id" class="block text-sm font-medium text-gray-700 mb-2">
                Nível de Ensino <span class="text-red-500">*</span>
            </label>
            <select name="nivel_ensino_id" id="nivel_ensino_id" required 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <option value="">Primeiro selecione uma modalidade</option>
            </select>
            @error('nivel_ensino_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Título do Planejamento -->
    <div>
        <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
            Título do Planejamento
        </label>
        <input type="text" name="titulo" id="titulo" 
               value="{{ old('titulo', $planejamento->titulo ?? '') }}"
               placeholder="Ex: Planejamento de Matemática - 1º Bimestre"
               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        <p class="text-gray-500 text-xs mt-1">Se não informado, será gerado automaticamente</p>
        @error('titulo')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Descrição -->
    <!--
    <div>
        <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
            Descrição
        </label>
        <textarea name="descricao" id="descricao" rows="3" 
                  placeholder="Descreva brevemente os objetivos e conteúdos deste planejamento..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('descricao', $planejamento->descricao ?? '') }}</textarea>
        @error('descricao')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
    -->
    <!-- Informações Adicionais -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
            </div>
            <div class="ml-3">
                <h4 class="text-sm font-medium text-blue-800">Dicas para esta etapa:</h4>
                <ul class="text-sm text-blue-700 mt-1 space-y-1">
                    <li>• A modalidade e nível de ensino determinarão as opções disponíveis nas próximas etapas</li>
                    <li>• Um título descritivo ajuda na organização e busca dos planejamentos</li>
                    <li>• A descrição pode incluir objetivos gerais e metodologias a serem utilizadas</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</form>


<script>
console.log('Script carregado - iniciando configuração');

function initializeModalidadeNivelLogic() {
    console.log('Inicializando lógica de modalidade e nível');
    
    // Buscar elementos
    const modalidadeSelect = document.getElementById('modalidade_ensino_id');
    const nivelSelect = document.getElementById('nivel_ensino_id');
    
    console.log('Todos os elementos encontrados com sucesso!');
    
    // Adicionar event listener para mudança de modalidade
    modalidadeSelect.addEventListener('change', function() {
        const modalidadeId = this.value;
        console.log('Modalidade selecionada:', modalidadeId);
        
        if (modalidadeId) {
            // Mostrar loading
            nivelSelect.innerHTML = '<option value="">Carregando níveis...</option>';
            console.log('Fazendo fetch para:', `/api/planejamentos/niveis-por-modalidade/${modalidadeId}`);
            
            fetch(`/api/planejamentos/niveis-por-modalidade/${modalidadeId}`)
                .then(response => {
                    console.log('Response recebida:', response);
                    return response.json();
                })
                .then(data => {
                    console.log('Dados recebidos:', data);
                    nivelSelect.innerHTML = '<option value="">Selecione um nível</option>';
                    
                    // Adicionar níveis BNCC se existirem
                    if (data.niveis_bncc && data.niveis_bncc.length > 0) {
                        const optgroupBncc = document.createElement('optgroup');
                        optgroupBncc.label = '📚 Níveis BNCC';
                        
                        data.niveis_bncc.forEach(nivel => {
                            const option = document.createElement('option');
                            option.value = nivel.id;
                            option.textContent = nivel.nome;
                            optgroupBncc.appendChild(option);
                        });
                        
                        nivelSelect.appendChild(optgroupBncc);
                    }
                    
                    // Adicionar níveis personalizados se existirem
                    if (data.niveis_personalizados && data.niveis_personalizados.length > 0) {
                        const optgroupPersonalizados = document.createElement('optgroup');
                        optgroupPersonalizados.label = '🏫 Níveis Personalizados';
                        
                        data.niveis_personalizados.forEach(nivel => {
                            const option = document.createElement('option');
                            option.value = nivel.id;
                            option.textContent = nivel.nome;
                            optgroupPersonalizados.appendChild(option);
                        });
                        
                        nivelSelect.appendChild(optgroupPersonalizados);
                    }
                    
                    // Se não há níveis disponíveis
                    if ((!data.niveis_bncc || data.niveis_bncc.length === 0) && 
                        (!data.niveis_personalizados || data.niveis_personalizados.length === 0)) {
                        nivelSelect.innerHTML = '<option value="">Nenhum nível disponível para esta modalidade</option>';
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar níveis:', error);
                    nivelSelect.innerHTML = '<option value="">Erro ao carregar níveis</option>';
                });
        } else {
            nivelSelect.innerHTML = '<option value="">Primeiro selecione uma modalidade</option>';
        }
    });
    
    // Se há um planejamento sendo editado, carregar os níveis da modalidade selecionada
    @if(isset($planejamento) && $planejamento->modalidade_ensino_id)
        console.log('Carregando dados para edição...');
        const modalidadeInicial = '{{ $planejamento->modalidade_ensino_id }}';
        const nivelInicial = '{{ $planejamento->nivel_ensino_id }}';
        
        console.log('Modalidade inicial:', modalidadeInicial, 'Nível inicial:', nivelInicial);
        
        if (modalidadeInicial) {
            fetch(`/api/planejamentos/niveis-por-modalidade/${modalidadeInicial}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Dados para edição recebidos:', data);
                    nivelSelect.innerHTML = '<option value="">Selecione um nível</option>';
                    
                    // Adicionar níveis BNCC se existirem
                    if (data.niveis_bncc && data.niveis_bncc.length > 0) {
                        const optgroupBncc = document.createElement('optgroup');
                        optgroupBncc.label = '📚 Níveis BNCC';
                        
                        data.niveis_bncc.forEach(nivel => {
                            const option = document.createElement('option');
                            option.value = nivel.id;
                            option.textContent = nivel.nome;
                            if (nivel.id == nivelInicial) option.selected = true;
                            optgroupBncc.appendChild(option);
                        });
                        
                        nivelSelect.appendChild(optgroupBncc);
                    }
                    
                    // Adicionar níveis personalizados se existirem
                    if (data.niveis_personalizados && data.niveis_personalizados.length > 0) {
                        const optgroupPersonalizados = document.createElement('optgroup');
                        optgroupPersonalizados.label = '🏫 Níveis Personalizados';
                        
                        data.niveis_personalizados.forEach(nivel => {
                            const option = document.createElement('option');
                            option.value = nivel.id;
                            option.textContent = nivel.nome;
                            if (nivel.id == nivelInicial) option.selected = true;
                            optgroupPersonalizados.appendChild(option);
                        });
                        
                        nivelSelect.appendChild(optgroupPersonalizados);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar níveis para edição:', error);
                });
        }
    @endif
}

// Verificar se o DOM já está carregado ou aguardar o carregamento
if (document.readyState === 'loading') {
    console.log('DOM ainda carregando, aguardando DOMContentLoaded...');
    document.addEventListener('DOMContentLoaded', initializeModalidadeNivelLogic);
} else {
    console.log('DOM já carregado, executando imediatamente...');
    initializeModalidadeNivelLogic();
}

console.log('Script carregado - configuração finalizada');
</script>