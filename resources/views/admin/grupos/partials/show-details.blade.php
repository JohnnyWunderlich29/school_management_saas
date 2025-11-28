<div class="space-y-4">
    <!-- Informações Básicas -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Informações Básicas</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <p class="mt-1 text-sm text-gray-900">{{ $grupo->nome }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Código</label>
                <p class="mt-1 text-sm text-gray-900">{{ $grupo->codigo }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Modalidade de Ensino</label>
                <p class="mt-1 text-sm text-gray-900">{{ $grupo->modalidadeEnsino->nome ?? 'Não informado' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <p class="mt-1">
                    @if($grupo->ativo)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>
                            Ativo
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times-circle mr-1"></i>
                            Inativo
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Faixa Etária -->
    @if($grupo->idade_minima || $grupo->idade_maxima)
    <div class="bg-blue-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Faixa Etária</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Idade Mínima</label>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $grupo->idade_minima ? $grupo->idade_minima . ' anos' : 'Não informado' }}
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Idade Máxima</label>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $grupo->idade_maxima ? $grupo->idade_maxima . ' anos' : 'Não informado' }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Descrição -->
    @if($grupo->descricao)
    <div class="bg-yellow-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Descrição</h3>
        <p class="text-sm text-gray-900">{{ $grupo->descricao }}</p>
    </div>
    @endif

    <!-- Informações Adicionais -->
    <div class="bg-purple-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Informações Adicionais</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Ordem</label>
                <p class="mt-1 text-sm text-gray-900">{{ $grupo->ordem ?? 'Não definida' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Salas Associadas</label>
                <p class="mt-1 text-sm text-gray-900">{{ $grupo->salas->count() }} sala(s)</p>
            </div>
        </div>
    </div>

    <!-- Salas Associadas -->
    @if($grupo->salas->count() > 0)
    <div class="bg-green-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Salas Associadas</h3>
        
        <div class="space-y-2">
            @foreach($grupo->salas as $sala)
                <div class="flex items-center justify-between bg-white p-2 rounded border">
                    <div>
                        <span class="font-medium text-gray-900">{{ $sala->nome }}</span>
                        @if($sala->turno)
                            <span class="text-sm text-gray-500">- {{ $sala->turno->nome }}</span>
                        @endif
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full {{ $sala->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $sala->ativo ? 'Ativa' : 'Inativa' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Datas -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Histórico</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Criado em</label>
                <p class="mt-1 text-sm text-gray-900">{{ $grupo->created_at->format('d/m/Y H:i') }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Última atualização</label>
                <p class="mt-1 text-sm text-gray-900">{{ $grupo->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="flex justify-end mt-6 pt-4 border-t">
    <x-button type="button" color="secondary" @click="$dispatch('close-modal')">
        Fechar
    </x-button>
</div>