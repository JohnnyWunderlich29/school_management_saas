@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Administração', 'url' => '#'],
    ['title' => 'Disciplinas', 'url' => '#']
]" />

    <x-card>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Disciplinas</h1>
                <p class="mt-1 text-sm text-gray-600">Gerenciamento de disciplinas por modalidade</p>
            </div>
            <x-button href="{{ route('admin.disciplinas.create') }}" color="primary">
                <i class="fas fa-plus mr-1"></i> Nova Disciplina
            </x-button>
        </div>

        <x-collapsible-filter 
            title="Filtros de Disciplinas" 
            :action="route('admin.disciplinas.index')" 
            :clear-route="route('admin.disciplinas.index')"
        >
            <x-filter-field 
                name="search" 
                label="Buscar" 
                type="text"
                placeholder="Buscar por nome, código ou descrição..."
            />
            
            <x-filter-field 
                name="modalidade_ensino_id" 
                label="Modalidade de Ensino" 
                type="select"
                empty-option="Todas as modalidades"
                :options="$modalidades->pluck('nome', 'id')->toArray()"
            />
            
            <x-filter-field 
                name="area_conhecimento" 
                label="Área de Conhecimento" 
                type="select"
                empty-option="Todas as áreas"
                :options="array_combine($areasConhecimento, $areasConhecimento)"
            />
            
            <x-filter-field 
                name="obrigatoria" 
                label="Obrigatória" 
                type="select"
                empty-option="Todas"
                :options="['1' => 'Sim', '0' => 'Não']"
            />
            
            <x-filter-field 
                name="ativo" 
                label="Status" 
                type="select"
                empty-option="Todos"
                :options="['1' => 'Ativo', '0' => 'Inativo']"
            />
        </x-collapsible-filter>

        @if($disciplinas->count() > 0)
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nome
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Código
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Modalidade
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Área
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Obrigatória
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </x-slot>

                @foreach($disciplinas as $disciplina)
                    <x-table-row>
                        <x-table-cell>
                            <div class="flex items-center">
                                @if($disciplina->cor_hex)
                                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $disciplina->cor_hex }}"></div>
                                @endif
                                <div class="text-sm font-medium text-gray-900">{{ $disciplina->nome }}</div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $disciplina->codigo }}
                            </span>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm text-gray-900">-</div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-sm text-gray-900">{{ $disciplina->area_conhecimento }}</div>
                        </x-table-cell>
                        <x-table-cell>
                            @if($disciplina->obrigatoria)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    Sim
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Não
                                </span>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            @if($disciplina->ativo)
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
                                <a href="{{ route('admin.disciplinas.show', $disciplina) }}" 
                                   class="text-indigo-600 hover:text-indigo-900 text-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.disciplinas.edit', $disciplina) }}" 
                                   class="text-yellow-600 hover:text-yellow-900 text-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" 
                                        class="text-red-600 hover:text-red-900 text-sm"
                                        onclick="confirmDelete({{ $disciplina->id }}, '{{ $disciplina->nome }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <form id="delete-form-{{ $disciplina->id }}" 
                                      action="{{ route('admin.disciplinas.destroy', $disciplina) }}" 
                                      method="POST" 
                                      class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @endforeach
            </x-table>

            <div class="mt-6">
                {{ $disciplinas->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-book text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma disciplina encontrada</h3>
                <p class="text-gray-500 mb-4">Não há disciplinas cadastradas com os filtros aplicados.</p>
                <x-button href="{{ route('admin.disciplinas.create') }}" color="primary">
                    <i class="fas fa-plus mr-1"></i> Criar Primeira Disciplina
                </x-button>
            </div>
        @endif
    </x-card>

    <!-- Modal de Confirmação -->
    <x-confirmation-modal 
        id="delete-confirmation-modal"
        title="Confirmar Exclusão"
        message="Tem certeza que deseja excluir esta disciplina?"
        confirm-text="Excluir"
        cancel-text="Cancelar"
        confirm-color="red"
    />
@endsection

@push('scripts')
<script>
function confirmDelete(disciplinaId, disciplinaNome) {
    showConfirmation({
        title: 'Confirmar Exclusão',
        message: `Tem certeza que deseja excluir a disciplina "${disciplinaNome}"? Esta ação não pode ser desfeita.`,
        confirmText: 'Excluir',
        cancelText: 'Cancelar',
        confirmColor: 'red',
        callback: () => {
            document.getElementById(`delete-form-${disciplinaId}`).submit();
        }
    });
}
</script>
@endpush