@if(count($conflitos) > 0)
    <div class="space-y-4">
        @foreach($conflitos as $conflito)
            <x-card class="border-l-4 {{ $conflito['severidade'] === 'critica' ? 'border-red-500' : ($conflito['severidade'] === 'alta' ? 'border-orange-500' : 'border-yellow-500') }}">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <!-- Informações do Conflito -->
                    <div class="flex-1">
                        <div class="flex items-start gap-3">
                            <!-- Ícone de Severidade -->
                            <div class="flex-shrink-0 mt-1">
                                @if($conflito['severidade'] === 'critica')
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                                    </div>
                                @elseif($conflito['severidade'] === 'alta')
                                    <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-exclamation-circle text-orange-600"></i>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-exclamation text-yellow-600"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Detalhes -->
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="text-lg font-medium text-gray-900">{{ $conflito['titulo'] }}</h4>
                                    
                                    <!-- Badge de Tipo -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $conflito['tipo'] === 'horario' ? 'bg-blue-100 text-blue-800' : 
                                           ($conflito['tipo'] === 'professor' ? 'bg-purple-100 text-purple-800' : 
                                           ($conflito['tipo'] === 'sala' ? 'bg-green-100 text-green-800' : 
                                           ($conflito['tipo'] === 'turma' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800'))) }}">
                                        {{ ucfirst($conflito['tipo']) }}
                                    </span>

                                    <!-- Badge de Severidade -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $conflito['severidade'] === 'critica' ? 'bg-red-100 text-red-800' : 
                                           ($conflito['severidade'] === 'alta' ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($conflito['severidade']) }}
                                    </span>
                                </div>

                                <p class="text-gray-600 mb-3">{{ $conflito['descricao'] }}</p>

                                <!-- Informações Detalhadas -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    @if(isset($conflito['data_hora']))
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                                            <span>{{ \Carbon\Carbon::parse($conflito['data_hora'])->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif

                                    @if(isset($conflito['professor']))
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-user mr-2 text-gray-400"></i>
                                            <span>{{ $conflito['professor'] }}</span>
                                        </div>
                                    @endif

                                    @if(isset($conflito['sala']))
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-door-open mr-2 text-gray-400"></i>
                                            <span>{{ $conflito['sala'] }}</span>
                                        </div>
                                    @endif

                                    @if(isset($conflito['turma']))
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-users mr-2 text-gray-400"></i>
                                            <span>{{ $conflito['turma'] }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Planejamentos Envolvidos -->
                                @if(isset($conflito['planejamentos']) && count($conflito['planejamentos']) > 0)
                                    <div class="mt-4">
                                        <h5 class="text-sm font-medium text-gray-700 mb-2">Planejamentos Envolvidos:</h5>
                                        <div class="space-y-2">
                                            @foreach($conflito['planejamentos'] as $planejamento)
                                                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">{{ $planejamento['titulo'] }}</p>
                                                            <p class="text-xs text-gray-600">{{ $planejamento['turma'] }} - {{ $planejamento['disciplina'] }}</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-2">
                                                        <a href="{{ route('planejamentos.show', $planejamento['id']) }}" 
                                                           class="text-blue-600 hover:text-blue-800 text-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('planejamentos.edit', $planejamento['id']) }}" 
                                                           class="text-gray-600 hover:text-gray-800 text-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Sugestões de Resolução -->
                                @if(isset($conflito['sugestoes']) && count($conflito['sugestoes']) > 0)
                                    <div class="mt-4">
                                        <h5 class="text-sm font-medium text-gray-700 mb-2">Sugestões de Resolução:</h5>
                                        <ul class="space-y-1">
                                            @foreach($conflito['sugestoes'] as $sugestao)
                                                <li class="flex items-start gap-2 text-sm text-gray-600">
                                                    <i class="fas fa-lightbulb text-yellow-500 mt-0.5 flex-shrink-0"></i>
                                                    <span>{{ $sugestao }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-2 lg:w-48">
                        <x-button color="primary" size="sm" class="w-full justify-center" onclick="resolverConflito('{{ $conflito['id'] }}')">
                            <i class="fas fa-tools mr-2"></i>
                            Resolver
                        </x-button>

                        @if(isset($conflito['pode_ignorar']) && $conflito['pode_ignorar'])
                            <x-button color="secondary" size="sm" class="w-full justify-center" onclick="ignorarConflito('{{ $conflito['id'] }}')">
                                <i class="fas fa-eye-slash mr-2"></i>
                                Ignorar
                            </x-button>
                        @endif

                        <div class="relative">
                            <x-button color="gray" size="sm" class="w-full justify-center" onclick="toggleMenuConflito('{{ $conflito['id'] }}')">
                                <i class="fas fa-ellipsis-v"></i>
                            </x-button>
                            
                            <div id="menu-conflito-{{ $conflito['id'] }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                <div class="py-1">
                                    <a href="#" onclick="verDetalhesConflito('{{ $conflito['id'] }}')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Ver Detalhes
                                    </a>
                                    <a href="#" onclick="exportarConflito('{{ $conflito['id'] }}')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-download mr-2"></i>
                                        Exportar
                                    </a>
                                    <a href="#" onclick="marcarComoResolvido('{{ $conflito['id'] }}')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-check mr-2"></i>
                                        Marcar como Resolvido
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timeline de Resolução (se existir) -->
                @if(isset($conflito['timeline']) && count($conflito['timeline']) > 0)
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h5 class="text-sm font-medium text-gray-700 mb-3">Histórico de Resolução:</h5>
                        <div class="space-y-2">
                            @foreach($conflito['timeline'] as $evento)
                                <div class="flex items-center gap-3 text-sm">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                    <span class="text-gray-600">{{ $evento['data'] }}</span>
                                    <span class="text-gray-900">{{ $evento['acao'] }}</span>
                                    @if(isset($evento['usuario']))
                                        <span class="text-gray-600">por {{ $evento['usuario'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-card>
        @endforeach
    </div>

    <!-- Paginação (se necessário) -->
    @if(isset($paginacao) && $paginacao['total_paginas'] > 1)
        <div class="mt-6 flex justify-center">
            <nav class="flex items-center gap-2">
                @if($paginacao['pagina_atual'] > 1)
                    <button onclick="carregarPagina({{ $paginacao['pagina_atual'] - 1 }})" 
                            class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50">
                        Anterior
                    </button>
                @endif

                @for($i = 1; $i <= $paginacao['total_paginas']; $i++)
                    <button onclick="carregarPagina({{ $i }})" 
                            class="px-3 py-2 text-sm {{ $i === $paginacao['pagina_atual'] ? 'bg-blue-600 text-white' : 'text-gray-600 hover:text-gray-900 border border-gray-300 hover:bg-gray-50' }} rounded-md">
                        {{ $i }}
                    </button>
                @endfor

                @if($paginacao['pagina_atual'] < $paginacao['total_paginas'])
                    <button onclick="carregarPagina({{ $paginacao['pagina_atual'] + 1 }})" 
                            class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50">
                        Próxima
                    </button>
                @endif
            </nav>
        </div>
    @endif
@else
    <x-card>
        <div class="text-center py-12">
            <div class="w-24 h-24 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-shield-alt text-green-600 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum conflito encontrado</h3>
            <p class="text-gray-600">
                Todos os planejamentos estão em conformidade com os critérios verificados.
            </p>
        </div>
    </x-card>
@endif

<script>
// Toggle menu de conflito
function toggleMenuConflito(conflitoId) {
    const menu = document.getElementById(`menu-conflito-${conflitoId}`);
    
    // Fechar outros menus
    document.querySelectorAll('[id^="menu-conflito-"]').forEach(m => {
        if (m.id !== `menu-conflito-${conflitoId}`) {
            m.classList.add('hidden');
        }
    });
    
    menu.classList.toggle('hidden');
}

// Ignorar conflito
function ignorarConflito(conflitoId) {
    if (confirm('Tem certeza que deseja ignorar este conflito?')) {
        fetch(`{{ route("planejamentos.conflitos.ignorar", ":id") }}`.replace(':id', conflitoId), {
            method: 'POST',
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
                window.AlertService.error('Erro ao ignorar conflito.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            window.AlertService.error('Erro ao ignorar conflito.');
        });
    }
}

// Ver detalhes do conflito
function verDetalhesConflito(conflitoId) {
    resolverConflito(conflitoId);
}

// Exportar conflito
function exportarConflito(conflitoId) {
    window.open(`{{ route("planejamentos.conflitos.exportar", ":id") }}`.replace(':id', conflitoId), '_blank');
}

// Marcar como resolvido
function marcarComoResolvido(conflitoId) {
    if (confirm('Tem certeza que deseja marcar este conflito como resolvido?')) {
        fetch(`{{ route("planejamentos.conflitos.resolver", ":id") }}`.replace(':id', conflitoId), {
            method: 'POST',
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
                window.AlertService.error('Erro ao resolver conflito.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            window.AlertService.error('Erro ao resolver conflito.');
        });
    }
}

// Carregar página
function carregarPagina(pagina) {
    const url = new URL(window.location);
    url.searchParams.set('page', pagina);
    window.location.href = url.toString();
}

// Fechar menus ao clicar fora
document.addEventListener('click', function(event) {
    if (!event.target.closest('[onclick^="toggleMenuConflito"]')) {
        document.querySelectorAll('[id^="menu-conflito-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});
</script>