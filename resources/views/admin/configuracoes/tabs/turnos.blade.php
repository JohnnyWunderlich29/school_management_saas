
<div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h2 class="text-lg md:text-2xl font-semibold text-gray-900">Turnos</h2>
            <p class="mt-1 text-sm text-gray-600">Gerencie os turnos do sistema</p>
        </div>
        <x-button href="{{ route('admin.turnos.create') }}" color="primary" class="w-full sm:w-auto">
            <i class="fas fa-plus mr-1"></i> Novo Turno
        </x-button>
    </div>

    <!-- Filtros -->
    <x-collapsible-filter 
        title="Filtros de Turnos" 
        :action="route('admin.configuracoes.index', ['tab' => 'turnos'])" 
        :clear-route="route('admin.configuracoes.index', ['tab' => 'turnos'])"
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
            :options="['1' => 'Ativos', '0' => 'Inativos']"
        />
    </x-collapsible-filter>

    <!-- Layout Desktop - Tabela -->
    <div class="hidden md:block">
        <x-table :headers="['ID', 'Nome', 'Código', 'Horário', 'Salas', 'Status']" :actions="true">
            @forelse($turnos as $index => $turno)
                <x-table-row :striped="true" :index="$index">
                    <x-table-cell>{{ $turno->id }}</x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $turno->nome }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="font-mono text-sm text-gray-600">{{ $turno->codigo }}</span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-sm text-gray-900">
                            {{ $turno->hora_inicio }} - {{ $turno->hora_fim }}
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @php
                            $salasCount = \App\Models\Sala::whereHas('gradeAulas.turma', function($query) use ($turno) {
                                $query->where('turno_id', $turno->id);
                            })->distinct()->count();
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $salasCount }} {{ $salasCount == 1 ? 'sala' : 'salas' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $turno->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $turno->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center space-x-2">
                            @permission('turnos.visualizar')
                                <x-button 
                                    href="{{ route('admin.turnos.show', $turno) }}" 
                                    color="secondary" 
                                    size="sm"
                                    title="Visualizar Turno"
                                >
                                    <i class="fas fa-eye"></i>
                                </x-button>
                            @endpermission
                            
                            @permission('turnos.editar')
                                <x-button 
                                    href="{{ route('admin.turnos.edit', $turno) }}" 
                                    color="warning" 
                                    size="sm"
                                    title="Editar Turno"
                                >
                                    <i class="fas fa-edit"></i>
                                </x-button>
                            @endpermission
                            
                            @permission('turnos.excluir')
                                <form action="{{ route('admin.turnos.destroy', $turno) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este turno?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button 
                                        type="submit" 
                                        color="danger" 
                                        size="sm"
                                        title="Excluir Turno"
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
                            <i class="fas fa-clock text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum turno encontrado</h3>
                            <p class="text-gray-600 mb-4">Comece criando seu primeiro turno.</p>
                            <x-button href="{{ route('admin.turnos.create') }}" color="primary">
                                <i class="fas fa-plus mr-1"></i> Novo Turno
                            </x-button>
                        </div>
                    </x-table-cell>
                </x-table-row>
            @endforelse
        </x-table>
    </div>

    <!-- Layout Mobile - Cards -->
    <div class="md:hidden space-y-4">
        @forelse($turnos as $turno)
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-start flex-1 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3 flex-shrink-0">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-medium text-gray-900 truncate">{{ $turno->nome }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $turno->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} ml-2 flex-shrink-0">
                                    {{ $turno->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                <span class="font-mono">{{ $turno->codigo }}</span>
                            </p>
                            <p class="text-sm text-gray-900 mt-1">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $turno->hora_inicio }} - {{ $turno->hora_fim }}
                            </p>
                            @if($turno->descricao)
                                <p class="text-sm text-gray-500 mt-1">{{ Str::limit($turno->descricao, 80) }}</p>
                            @endif
                            <div class="flex items-center mt-2 space-x-4">
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-door-open mr-1"></i>
                                    @php
                                        $salasCount = \App\Models\Sala::whereHas('gradeAulas.turma', function($query) use ($turno) {
                                            $query->where('turno_id', $turno->id);
                                        })->distinct()->count();
                                    @endphp
                                    {{ $salasCount }} {{ $salasCount == 1 ? 'sala' : 'salas' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4 pt-4 border-t border-gray-100">
                    @permission('turnos.visualizar')
                        <x-button 
                            href="{{ route('admin.turnos.show', $turno) }}" 
                            color="secondary" 
                            size="sm"
                        >
                            <i class="fas fa-eye mr-1"></i> Ver
                        </x-button>
                    @endpermission
                    
                    @permission('turnos.editar')
                        <x-button 
                            href="{{ route('admin.turnos.edit', $turno) }}" 
                            color="warning" 
                            size="sm"
                        >
                            <i class="fas fa-edit mr-1"></i> Editar
                        </x-button>
                    @endpermission
                    
                    @permission('turnos.excluir')
                        <form action="{{ route('admin.turnos.destroy', $turno) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este turno?')">
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
                <i class="fas fa-clock text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum turno encontrado</h3>
                <p class="text-gray-600 mb-4">Comece criando seu primeiro turno.</p>
                <x-button href="{{ route('admin.turnos.create') }}" color="primary">
                    <i class="fas fa-plus mr-1"></i> Novo Turno
                </x-button>
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    @if($turnos->hasPages())
        <div class="mt-6">
            {{ $turnos->appends(request()->query())->links('components.pagination') }}
        </div>
    @endif
