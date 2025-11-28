
<div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Disciplinas</h2>
            <p class="mt-1 text-sm text-gray-600">Gerencie as disciplinas do sistema</p>
        </div>
        <x-button href="{{ route('admin.disciplinas.create') }}" color="primary">
            <i class="fas fa-plus mr-1"></i> Nova Disciplina
        </x-button>
    </div>

    <!-- Filtros -->
    <x-collapsible-filter 
        title="Filtros de Disciplinas" 
        :action="route('admin.configuracoes.index', ['tab' => 'disciplinas'])" 
        :clear-route="route('admin.configuracoes.index', ['tab' => 'disciplinas'])"
    >
        <x-filter-field 
            name="search" 
            label="Buscar" 
            type="text"
            placeholder="Buscar por nome, código ou descrição..."
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
        <x-table :headers="['ID', 'Nome', 'Código', 'Status']" :actions="true">
            @forelse($disciplinas as $index => $disciplina)
                <x-table-row :striped="true" :index="$index">
                    <x-table-cell>{{ $disciplina->id }}</x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-500 mr-3">
                                <i class="fas fa-book"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $disciplina->nome }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="font-mono text-sm text-gray-600">{{ $disciplina->codigo }}</span>
                    </x-table-cell>

                    <x-table-cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $disciplina->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $disciplina->ativo ? 'Ativa' : 'Inativa' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center space-x-2">
                            @permission('disciplinas.visualizar')
                                <x-button 
                                    href="{{ route('admin.disciplinas.show', $disciplina) }}" 
                                    color="secondary" 
                                    size="sm"
                                    title="Visualizar Disciplina"
                                >
                                    <i class="fas fa-eye"></i>
                                </x-button>
                            @endpermission
                            
                            @permission('disciplinas.editar')
                                <x-button 
                                    href="{{ route('admin.disciplinas.edit', $disciplina) }}" 
                                    color="warning" 
                                    size="sm"
                                    title="Editar Disciplina"
                                >
                                    <i class="fas fa-edit"></i>
                                </x-button>
                            @endpermission
                            
                            @permission('disciplinas.excluir')
                                <form action="{{ route('admin.disciplinas.destroy', $disciplina) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta disciplina?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button 
                                        type="submit" 
                                        color="danger" 
                                        size="sm"
                                        title="Excluir Disciplina"
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
                    <x-table-cell colspan="7">
                        <div class="text-center py-8">
                            <i class="fas fa-book text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma disciplina encontrada</h3>
                            <p class="text-gray-600 mb-4">Comece criando sua primeira disciplina.</p>
                            <x-button href="{{ route('admin.disciplinas.create') }}" color="primary">
                                <i class="fas fa-plus mr-1"></i> Nova Disciplina
                            </x-button>
                        </div>
                    </x-table-cell>
                </x-table-row>
            @endforelse
        </x-table>
    </div>

    <!-- Layout Mobile - Cards -->
    <div class="md:hidden space-y-4">
        @forelse($disciplinas as $disciplina)
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-start flex-1 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-500 mr-3 flex-shrink-0">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-medium text-gray-900 truncate">{{ $disciplina->nome }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $disciplina->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} ml-2 flex-shrink-0">
                                    {{ $disciplina->ativo ? 'Ativa' : 'Inativa' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                <span class="font-mono">{{ $disciplina->codigo }}</span>
                            </p>
                            <div class="flex flex-col space-y-1 mt-2">
                                @if($disciplina->area_conhecimento)
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-tag mr-1"></i>
                                        {{ $disciplina->area_conhecimento }}
                                    </span>
                                @endif
                            </div>
                            @if($disciplina->descricao)
                                <p class="text-sm text-gray-500 mt-2">{{ Str::limit($disciplina->descricao, 80) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4 pt-4 border-t border-gray-100">
                    @permission('disciplinas.visualizar')
                        <x-button 
                            href="{{ route('admin.disciplinas.show', $disciplina) }}" 
                            color="secondary" 
                            size="sm"
                        >
                            <i class="fas fa-eye mr-1"></i> Ver
                        </x-button>
                    @endpermission
                    
                    @permission('disciplinas.editar')
                        <x-button 
                            href="{{ route('admin.disciplinas.edit', $disciplina) }}" 
                            color="warning" 
                            size="sm"
                        >
                            <i class="fas fa-edit mr-1"></i> Editar
                        </x-button>
                    @endpermission
                    
                    @permission('disciplinas.excluir')
                        <form action="{{ route('admin.disciplinas.destroy', $disciplina) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta disciplina?')">
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
                <i class="fas fa-book text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma disciplina encontrada</h3>
                <p class="text-gray-600 mb-4">Comece criando sua primeira disciplina.</p>
                <x-button href="{{ route('admin.disciplinas.create') }}" color="primary">
                    <i class="fas fa-plus mr-1"></i> Nova Disciplina
                </x-button>
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    @if($disciplinas->hasPages())
        <div class="mt-6">
            {{ $disciplinas->appends(request()->query())->links('components.pagination') }}
        </div>
    @endif
