
<div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Grupos Educacionais</h2>
            <p class="mt-1 text-sm text-gray-600">Gerencie os grupos educacionais do sistema</p>
        </div>
        <x-button type="button" color="primary" onclick="openCreateGrupoModal()">
            <i class="fas fa-plus mr-1"></i> Novo Grupo
        </x-button>
    </div>

    <!-- Filtros -->
    <x-collapsible-filter 
        title="Filtros de Grupos" 
        :action="route('admin.configuracoes.index', ['tab' => 'grupos'])" 
        :clear-route="route('admin.configuracoes.index', ['tab' => 'grupos'])"
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
        <x-table :headers="['ID', 'Nome', 'Código', 'Descrição', 'Salas', 'Status']" :actions="true">
            @forelse($grupos as $index => $grupo)
                <x-table-row :striped="true" :index="$index">
                    <x-table-cell>{{ $grupo->id }}</x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-500 mr-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $grupo->nome }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="font-mono text-sm text-gray-600">{{ $grupo->codigo }}</span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-sm text-gray-900">
                            {{ Str::limit($grupo->descricao, 50) ?: 'Sem descrição' }}
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @php
                            $salasCount = \App\Models\Sala::whereHas('gradeAulas.turma', function($query) use ($grupo) {
                                $query->where('grupo_id', $grupo->id);
                            })->distinct()->count();
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $salasCount }} {{ $salasCount == 1 ? 'sala' : 'salas' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $grupo->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $grupo->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <div class="flex items-center space-x-2">
                            @permission('grupos.visualizar')
                                <x-button 
                                    href="{{ route('admin.grupos.show', $grupo) }}" 
                                    color="secondary" 
                                    size="sm"
                                    title="Visualizar Grupo"
                                >
                                    <i class="fas fa-eye"></i>
                                </x-button>
                            @endpermission
                            
                            @permission('grupos.editar')
                                <x-button 
                                    href="{{ route('admin.grupos.edit', $grupo) }}" 
                                    color="warning" 
                                    size="sm"
                                    title="Editar Grupo"
                                >
                                    <i class="fas fa-edit"></i>
                                </x-button>
                            @endpermission
                            
                            @permission('grupos.excluir')
                                <form action="{{ route('admin.grupos.destroy', $grupo) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este grupo?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button 
                                        type="submit" 
                                        color="danger" 
                                        size="sm"
                                        title="Excluir Grupo"
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
                            <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum grupo encontrado</h3>
                            <p class="text-gray-600 mb-4">Comece criando seu primeiro grupo educacional.</p>
                            <x-button href="{{ route('admin.grupos.create') }}" color="primary">
                                <i class="fas fa-plus mr-1"></i> Novo Grupo
                            </x-button>
                        </div>
                    </x-table-cell>
                </x-table-row>
            @endforelse
        </x-table>
    </div>

    <!-- Layout Mobile - Cards -->
    <div class="md:hidden space-y-4">
        @forelse($grupos as $grupo)
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex items-start flex-1 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-500 mr-3 flex-shrink-0">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-medium text-gray-900 truncate">{{ $grupo->nome }}</h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $grupo->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} ml-2 flex-shrink-0">
                                    {{ $grupo->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                <span class="font-mono">{{ $grupo->codigo }}</span>
                            </p>
                            @if($grupo->descricao)
                                <p class="text-sm text-gray-500 mt-1">{{ Str::limit($grupo->descricao, 80) }}</p>
                            @endif
                            <div class="flex items-center mt-2 space-x-4">
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-door-open mr-1"></i>
                                    @php
                                        $salasCount = \App\Models\Sala::whereHas('gradeAulas.turma', function($query) use ($grupo) {
                                            $query->where('grupo_id', $grupo->id);
                                        })->distinct()->count();
                                    @endphp
                                    {{ $salasCount }} {{ $salasCount == 1 ? 'sala' : 'salas' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4 pt-4 border-t border-gray-100">
                    @permission('grupos.visualizar')
                        <x-button 
                            href="{{ route('admin.grupos.show', $grupo) }}" 
                            color="secondary" 
                            size="sm"
                        >
                            <i class="fas fa-eye mr-1"></i> Ver
                        </x-button>
                    @endpermission
                    
                    @permission('grupos.editar')
                        <x-button 
                            href="{{ route('admin.grupos.edit', $grupo) }}" 
                            color="warning" 
                            size="sm"
                        >
                            <i class="fas fa-edit mr-1"></i> Editar
                        </x-button>
                    @endpermission
                    
                    @permission('grupos.excluir')
                        <form action="{{ route('admin.grupos.destroy', $grupo) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este grupo?')">
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
                <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum grupo encontrado</h3>
                <p class="text-gray-600 mb-4">Comece criando seu primeiro grupo educacional.</p>
                <x-button type="button" color="primary" onclick="openCreateGrupoModal()">
                    <i class="fas fa-plus mr-1"></i> Novo Grupo
                </x-button>
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    @if($grupos->hasPages())
        <div class="mt-6">
            {{ $grupos->appends(request()->query())->links('components.pagination') }}
        </div>
    @endif

<!-- Modal de Criação de Grupo -->
<div id="create-grupo-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header do Modal -->
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Novo Grupo Educacional</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeCreateGrupoModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Formulário -->
            <form id="create-grupo-form" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Grupo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nome" 
                               name="nome" 
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: Educação Infantil">
                        <div id="error-nome" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="modalidade_ensino_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Modalidade de Ensino <span class="text-red-500">*</span>
                        </label>
                        <select id="modalidade_ensino_id" 
                                name="modalidade_ensino_id" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Carregando modalidades...</option>
                        </select>
                        <div id="error-modalidade_ensino_id" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="idade_minima" class="block text-sm font-medium text-gray-700 mb-2">
                            Idade Mínima
                        </label>
                        <input type="number" 
                               id="idade_minima" 
                               name="idade_minima" 
                               min="0" 
                               max="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: 3">
                        <div id="error-idade_minima" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="idade_maxima" class="block text-sm font-medium text-gray-700 mb-2">
                            Idade Máxima
                        </label>
                        <input type="number" 
                               id="idade_maxima" 
                               name="idade_maxima" 
                               min="0" 
                               max="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Ex: 5">
                        <div id="error-idade_maxima" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição
                    </label>
                    <textarea id="descricao" 
                              name="descricao" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Descrição opcional do grupo educacional"></textarea>
                    <div id="error-descricao" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ordem" class="block text-sm font-medium text-gray-700 mb-2">
                            Ordem de Exibição
                        </label>
                        <input type="number" 
                               id="ordem" 
                               name="ordem" 
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="1">
                        <div id="error-ordem" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   id="ativo" 
                                   name="ativo" 
                                   value="1" 
                                   checked
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Grupo ativo</span>
                        </label>
                    </div>
                </div>

                <!-- Aviso sobre código automático -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                O código do grupo será gerado automaticamente com base no nome fornecido.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" 
                            onclick="closeCreateGrupoModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancelar
                    </button>
                    <button type="submit" 
                            id="submit-create-grupo"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-save mr-1"></i> Criar Grupo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
