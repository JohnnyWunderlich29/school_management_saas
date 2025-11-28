
<div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Modalidades de Ensino</h2>
            <p class="mt-1 text-sm text-gray-600">Gerencie as modalidades de ensino do sistema</p>
        </div>
        <x-button href="{{ route('admin.modalidades.create') }}" color="primary">
            <i class="fas fa-plus mr-1"></i> Nova Modalidade
        </x-button>
    </div>

    <!-- Filtros -->
    <x-collapsible-filter 
        title="Filtros de Modalidades" 
        :action="route('admin.configuracoes.index', ['tab' => 'modalidades'])" 
        :clear-route="route('admin.configuracoes.index', ['tab' => 'modalidades'])"
    >
        <x-filter-field 
            name="search" 
            label="Buscar" 
            type="text"
            placeholder="Buscar por nome ou descrição..."
        />
        
        <x-filter-field 
            name="ativo" 
            label="Status" 
            type="select"
            empty-option="Todos os status"
            :options="['1' => 'Ativas', '0' => 'Inativas']"
        />
    </x-collapsible-filter>

    <!-- Layout Desktop - Tabela -->
    <div class="hidden md:block">
        <x-table :headers="['ID', 'Nome', 'Descrição', 'Salas', 'Status']" :actions="true">
            @forelse($modalidades as $index => $modalidade)
                <x-table-row :striped="true" :index="$index">
                    <x-table-cell>{{ $modalidade->id }}</x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $modalidade->nome }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-sm text-gray-900">
                            {{ Str::limit($modalidade->descricao, 50) ?: 'Sem descrição' }}
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @php
                            $salasCount = \App\Models\Sala::whereHas('turmas.grupo', function($query) use ($modalidade) {
                                $query->where('modalidade_ensino_id', $modalidade->id);
                            })->count();
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $salasCount }} {{ $salasCount == 1 ? 'sala' : 'salas' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $modalidade->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $modalidade->ativo ? 'Ativa' : 'Inativa' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center space-x-2">
                            @permission('modalidades.visualizar')
                                <x-button 
                                    href="{{ route('admin.modalidades.show', $modalidade) }}" 
                                    color="secondary" 
                                    size="sm"
                                    title="Visualizar Modalidade"
                                >
                                    <i class="fas fa-eye"></i>
                                </x-button>
                            @endpermission
                            
                            @permission('modalidades.editar')
                                <x-button 
                                    href="{{ route('admin.modalidades.edit', $modalidade) }}" 
                                    color="warning" 
                                    size="sm"
                                    title="Editar Modalidade"
                                >
                                    <i class="fas fa-edit"></i>
                                </x-button>
                            @endpermission
                            
                            @permission('modalidades.excluir')
                                <form action="{{ route('admin.modalidades.destroy', $modalidade) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta modalidade?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button 
                                        type="submit" 
                                        color="danger" 
                                        size="sm"
                                        title="Excluir Modalidade"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </x-button>
                                </form>
                            @endpermission
                        </div>
                    </x-table-cell>
                </x-table-row>
            @empty
                <x-table-row>
                    <x-table-cell colspan="6">
                        <div class="text-center py-8">
                            <i class="fas fa-graduation-cap text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma modalidade encontrada</h3>
                            <p class="text-gray-600 mb-4">Comece criando sua primeira modalidade de ensino.</p>
                            <x-button href="{{ route('admin.modalidades.create') }}" color="primary">
                                <i class="fas fa-plus mr-1"></i> Nova Modalidade
                            </x-button>
                        </div>
                    </x-table-cell>
                </x-table-row>
            @endforelse
        </x-table>
    </div>

    <!-- Layout Mobile - Cards -->
    <div class="md:hidden space-y-4">
        @forelse($modalidades as $modalidade)
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-start flex-1 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3 flex-shrink-0">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-medium text-gray-900 truncate">{{ $modalidade->nome }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $modalidade->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} ml-2 flex-shrink-0">
                                    {{ $modalidade->ativo ? 'Ativa' : 'Inativa' }}
                                </span>
                            </div>
                            @if($modalidade->descricao)
                                <p class="text-sm text-gray-500 mt-1">{{ Str::limit($modalidade->descricao, 80) }}</p>
                            @endif
                            <div class="flex items-center mt-2 space-x-4">
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-door-open mr-1"></i>
                                    @php
                                            $salasCount = \App\Models\Sala::whereHas('turmas.grupo', function($query) use ($modalidade) {
                                                $query->where('modalidade_ensino_id', $modalidade->id);
                                            })->count();
                                        @endphp
                                    {{ $salasCount }} {{ $salasCount == 1 ? 'sala' : 'salas' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4 pt-4 border-t border-gray-100">
                    @permission('modalidades.visualizar')
                        <x-button 
                            href="{{ route('admin.modalidades.show', $modalidade) }}" 
                            color="secondary" 
                            size="sm"
                        >
                            <i class="fas fa-eye mr-1"></i> Ver
                        </x-button>
                    @endpermission
                    
                    @permission('modalidades.editar')
                        <x-button 
                            href="{{ route('admin.modalidades.edit', $modalidade) }}" 
                            color="warning" 
                            size="sm"
                        >
                            <i class="fas fa-edit mr-1"></i> Editar
                        </x-button>
                    @endpermission
                    
                    @permission('modalidades.excluir')
                        <form action="{{ route('admin.modalidades.destroy', $modalidade) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta modalidade?')">
                            @csrf
                            @method('DELETE')
                            <x-button 
                                type="submit" 
                                color="danger" 
                                size="sm"
                            >
                                <i class="fas fa-trash mr-1"></i> Excluir
                            </x-button>
                        </form>
                    @endpermission
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <i class="fas fa-graduation-cap text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma modalidade encontrada</h3>
                <p class="text-gray-600 mb-4">Comece criando sua primeira modalidade de ensino.</p>
                <x-button href="{{ route('admin.modalidades.create') }}" color="primary">
                    <i class="fas fa-plus mr-1"></i> Nova Modalidade
                </x-button>
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    @if($modalidades->hasPages())
        <div class="mt-6">
            {{ $modalidades->appends(request()->query())->links('components.pagination') }}
        </div>
    @endif
