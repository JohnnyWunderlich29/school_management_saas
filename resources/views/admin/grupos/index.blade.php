@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Grupos Educacionais', 'url' => '#']
]" />

    <x-card>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Grupos Educacionais</h1>
                <p class="mt-1 text-sm text-gray-600">Gerenciamento de grupos educacionais por modalidade</p>
            </div>
            <x-button 
                color="primary"
                x-data="{}"
                @click="$dispatch('open-modal', 'add-grupo-modal')"
            >
                <i class="fas fa-plus mr-1"></i> Novo Grupo
            </x-button>
        </div>

        <x-collapsible-filter 
            title="Filtros de Grupos" 
            :action="route('admin.grupos.index')" 
            :clear-route="route('admin.grupos.index')"
        >
            <x-filter-field 
                name="search" 
                label="Buscar" 
                type="text"
                placeholder="Buscar por nome, código ou descrição..."
            />
            
            <!-- Filtro de modalidade removido conforme solicitado -->
            
            <x-filter-field 
                name="ativo" 
                label="Status" 
                type="select"
                empty-option="Todos"
                :options="['1' => 'Ativo', '0' => 'Inativo']"
            />
        </x-collapsible-filter>

        @if($grupos->count() > 0)
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nome
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Código
                    </th>
                    <!-- Coluna de modalidade removida -->
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Idades
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ano/Série
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </x-slot>

                @foreach($grupos as $grupo)
                    <x-table-row>
                        <x-table-cell>
                            <div class="text-sm font-medium text-gray-900">{{ $grupo->nome }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $grupo->codigo }}
                            </span>
                        </x-table-cell>
                        <!-- Célula de modalidade removida -->
                        <x-table-cell>
                            <div class="text-sm text-gray-900">{{ $grupo->faixa_etaria ?: $grupo->ano_serie_formatado }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm text-gray-900">{{ $grupo->ano_serie }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            @if($grupo->ativo)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Ativo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inativo
                                </span>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            <div class="flex items-center space-x-2">
                                <x-button 
                                    @click="$dispatch('open-modal', {id: 'show-grupo-modal', grupo: {{ $grupo->id }}})"
                                    color="primary" 
                                    size="sm"
                                    title="Visualizar Grupo"
                                >
                                    <i class="fas fa-eye"></i>
                                </x-button>
                                <x-button 
                                    @click="$dispatch('open-modal', {id: 'edit-grupo-modal', grupo: {{ $grupo->id }}})"
                                    color="warning" 
                                    size="sm"
                                    title="Editar Grupo"
                                >
                                    <i class="fas fa-edit"></i>
                                </x-button>
                                <form action="{{ route('admin.grupos.destroy', $grupo) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este grupo?')">
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
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @endforeach
            </x-table>

            <div class="mt-6">
                {{ $grupos->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum grupo encontrado</h3>
                <p class="text-gray-500 mb-4">Não há grupos cadastrados com os filtros aplicados.</p>
                <x-button 
                    color="primary"
                    x-data="{}"
                    @click="$dispatch('open-modal', 'add-grupo-modal')"
                >
                    <i class="fas fa-plus mr-1"></i> Criar Primeiro Grupo
                </x-button>
            </div>
        @endif
    </x-card>

    <!-- Modal para adicionar novo grupo -->
    <x-modal name="add-grupo-modal" :show="false" maxWidth="md" title="Adicionar Novo Grupo">
        <form id="add-grupo-form" class="p-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <x-input-label for="nome" value="Nome" />
                    <x-input id="nome" name="nome" type="text" class="mt-1 block w-full" required />
                    <div id="nome-error" class="mt-2 text-sm text-red-600 hidden"></div>
                    <p class="mt-1 text-sm text-gray-500">O código do grupo será gerado automaticamente</p>
                </div>

                <div>
                    <x-input-label for="modalidade_ensino_id" value="Modalidade de Ensino" />
                    <select id="modalidade_ensino_id" name="modalidade_ensino_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                        <option value="">Carregando modalidades...</option>
                    </select>
                    <div id="modalidade_ensino_id-error" class="mt-2 text-sm text-red-600 hidden"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="idade_minima" value="Idade Mínima" />
                        <x-input id="idade_minima" name="idade_minima" type="number" min="0" max="25" class="mt-1 block w-full" />
                        <div id="idade_minima-error" class="mt-2 text-sm text-red-600 hidden"></div>
                    </div>
                    <div>
                        <x-input-label for="idade_maxima" value="Idade Máxima" />
                        <x-input id="idade_maxima" name="idade_maxima" type="number" min="0" max="25" class="mt-1 block w-full" />
                        <div id="idade_maxima-error" class="mt-2 text-sm text-red-600 hidden"></div>
                    </div>
                </div>

                <div>
                    <x-input-label for="descricao" value="Descrição" />
                    <x-textarea id="descricao" name="descricao" class="mt-1 block w-full" rows="3" />
                    <div id="descricao-error" class="mt-2 text-sm text-red-600 hidden"></div>
                </div>

                <div>
                    <x-input-label for="ordem" value="Ordem" />
                    <x-input id="ordem" name="ordem" type="number" min="1" class="mt-1 block w-full" />
                    <div id="ordem-error" class="mt-2 text-sm text-red-600 hidden"></div>
                </div>

                <div class="flex items-center">
                    <input id="ativo" name="ativo" type="checkbox" value="1" checked class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="ativo" class="ml-2 block text-sm text-gray-900">Grupo ativo</label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t mt-6">
                <x-button type="button" color="secondary" @click="$dispatch('close-modal', 'add-grupo-modal')">
                    Cancelar
                </x-button>
                <x-button type="submit" color="primary" id="save-grupo-btn">
                    <i class="fas fa-save mr-1"></i> Salvar Grupo
                </x-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal para editar grupo -->
    <x-modal name="edit-grupo-modal" :show="false" maxWidth="md" title="Editar Grupo">
        <div id="edit-grupo-content" class="p-6">
            <!-- Conteúdo será carregado via AJAX -->
            <div class="flex justify-center items-center py-8">
                <i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i>
                <span class="ml-2 text-gray-600">Carregando...</span>
            </div>
        </div>
    </x-modal>

    <!-- Modal para visualizar grupo -->
    <x-modal name="show-grupo-modal" :show="false" maxWidth="md" title="Detalhes do Grupo">
        <div id="show-grupo-content" class="p-6">
            <!-- Conteúdo será carregado via AJAX -->
            <div class="flex justify-center items-center py-8">
                <i class="fas fa-spinner fa-spin text-gray-400 text-2xl"></i>
                <span class="ml-2 text-gray-600">Carregando...</span>
            </div>
        </div>
    </x-modal>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carregar modalidades de ensino quando o modal de criação for aberto
    document.addEventListener('modal-opened', function(event) {
        if (event.detail.name === 'add-grupo-modal') {
            loadModalidadesEnsino();
        }
        
        if (event.detail.name === 'edit-grupo-modal' && event.detail.data && event.detail.data.grupo) {
            loadEditGrupo(event.detail.data.grupo);
        }
        
        if (event.detail.name === 'show-grupo-modal' && event.detail.data && event.detail.data.grupo) {
            loadShowGrupo(event.detail.data.grupo);
        }
    });

    // Tratamento do formulário de criação
    const addGrupoForm = document.getElementById('add-grupo-form');
    if (addGrupoForm) {
        addGrupoForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Limpar erros anteriores
            clearErrors();
            
            // Desabilitar botão de envio
            const submitBtn = document.getElementById('save-grupo-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';
            
            const formData = new FormData(addGrupoForm);
            
            fetch('{{ route("admin.grupos.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fechar modal e recarregar página
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: { name: 'add-grupo-modal' } }));
                    location.reload();
                } else {
                    // Mostrar erros de validação
                    if (data.errors) {
                        showErrors(data.errors);
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao salvar grupo:', error);
                alert('Erro ao salvar grupo. Tente novamente.');
            })
            .finally(() => {
                // Reabilitar botão
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    function clearErrors() {
        const errorElements = document.querySelectorAll('[id$="-error"]');
        errorElements.forEach(element => {
            element.classList.add('hidden');
            element.textContent = '';
        });
    }

    function showErrors(errors) {
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(field + '-error');
            if (errorElement) {
                errorElement.textContent = errors[field][0];
                errorElement.classList.remove('hidden');
            }
        });
    }

    function loadModalidadesEnsino() {
        fetch('/admin/grupos/modalidades-ensino')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('modalidade_ensino_id');
                if (select) {
                    select.innerHTML = '<option value="">Selecione uma modalidade de ensino</option>';
                    data.forEach(modalidade => {
                        const option = document.createElement('option');
                        option.value = modalidade.id;
                        option.textContent = modalidade.nome;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao carregar modalidades:', error);
                const select = document.getElementById('modalidade_ensino_id');
                if (select) {
                    select.innerHTML = '<option value="">Erro ao carregar modalidades</option>';
                }
            });
    }

    function loadEditGrupo(grupoId) {
        fetch(`/admin/grupos/${grupoId}/edit-modal`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('edit-grupo-content').innerHTML = html;
            })
            .catch(error => {
                console.error('Erro ao carregar formulário de edição:', error);
                document.getElementById('edit-grupo-content').innerHTML = 
                    '<div class="text-center py-8"><p class="text-red-600">Erro ao carregar o formulário</p></div>';
            });
    }

    function loadShowGrupo(grupoId) {
        fetch(`/admin/grupos/${grupoId}/show-modal`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('show-grupo-content').innerHTML = html;
            })
            .catch(error => {
                console.error('Erro ao carregar detalhes do grupo:', error);
                document.getElementById('show-grupo-content').innerHTML = 
                    '<div class="text-center py-8"><p class="text-red-600">Erro ao carregar os detalhes</p></div>';
            });
    }
});
</script>
@endpush

@endsection