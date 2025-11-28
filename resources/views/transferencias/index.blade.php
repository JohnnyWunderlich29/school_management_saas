@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Transferências', 'url' => '#']
]" />

<x-card>
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Solicitações de Transferência</h1>
            <p class="mt-1 text-sm text-gray-600">Gerenciamento de transferências de alunos</p>
        </div>
        <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
            <x-button href="{{ route('transferencias.create') }}" color="primary" class="w-full sm:justify-center">
                <i class="fas fa-plus mr-1"></i> 
                <span class="hidden md:inline">Nova Solicitação</span>
                <span class="md:hidden">Nova</span>
            </x-button>
        </div>
    </div>

    <x-collapsible-filter 
        title="Filtros de Transferências" 
        :action="route('transferencias.index')" 
        :clear-route="route('transferencias.index')"
    >
        <x-filter-field 
            name="status" 
            label="Status" 
            type="select"
            empty-option="Todos"
            :options="['pendente' => 'Pendente', 'aprovada' => 'Aprovada', 'rejeitada' => 'Rejeitada']"
        />
        
        <x-filter-field 
            name="aluno" 
            label="Aluno" 
            type="text"
            placeholder="Buscar por nome..."
        />
    </x-collapsible-filter>

    <x-card>
        <!-- Tabela responsiva com melhor UX mobile -->
        <div>
            <x-table class="hidden md:block" :headers="['Aluno', 'Turma Origem', 'Turma Destino', 'Solicitante', 'Status', 'Data Solicitação', 'Motivo']" :actions="true">
            @forelse($transferencias as $index => $transferencia)
                <x-table-row :striped="true" :index="$index">
                    <x-table-cell>
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $transferencia->aluno->nome_completo }}</div>
                                <div class="text-gray-500 text-xs">{{ $transferencia->aluno->cpf }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    <x-table-cell>
                        @if($transferencia->turmaOrigem)
                            <div class="text-xs text-gray-900 font-medium">{{ $transferencia->turmaOrigem->nome }}</div>
                        @else
                            <span class="text-gray-400">Sem turma</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        <div class="text-xs text-gray-900 font-medium">{{ $transferencia->turmaDestino->nome }}</div>
                    </x-table-cell>
                    <x-table-cell>{{ $transferencia->solicitante->name }}</x-table-cell>
                    <x-table-cell>
                        @switch($transferencia->status)
                            @case('pendente')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pendente
                                </span>
                                @break
                            @case('aprovada')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Aprovada
                                </span>
                                @break
                            @case('rejeitada')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Rejeitada
                                </span>
                                @break
                            @case('cancelada')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Cancelada
                                </span>
                                @break
                        @endswitch
                    </x-table-cell>
                    <x-table-cell>{{ $transferencia->data_solicitacao->format('d/m/Y H:i') }}</x-table-cell>
                    <x-table-cell>
                        @if($transferencia->motivo)
                            <span title="{{ $transferencia->motivo }}">
                                {{ Str::limit($transferencia->motivo, 30) }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </x-table-cell>
                    <x-table-cell align="right">
                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('transferencias.show', $transferencia) }}" 
                               class="text-indigo-600 hover:text-indigo-900" title="Ver detalhes">
                                <i class="fas fa-eye"></i>
                            </a>
                            
                            @if($transferencia->status === 'pendente')
                                <button type="button" class="text-green-600 hover:text-green-900" title="Aprovar" x-data="{}" @click="$dispatch('open-modal', 'aprovar-transferencia-{{ $transferencia->id }}')">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="text-red-600 hover:text-red-900" title="Rejeitar" x-data="{}" @click="$dispatch('open-modal', 'rejeitar-transferencia-{{ $transferencia->id }}')">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button type="button" class="text-red-600 hover:text-red-900" title="Excluir" x-data="{}" @click="$dispatch('open-modal', 'excluir-transferencia-{{ $transferencia->id }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </x-table-cell>
                </x-table-row>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Nenhuma transferência encontrada.
                    </td>
                </tr>
            @endforelse
            </x-table>
            
            <!-- Layout mobile otimizado com cards -->
            <div class="md:hidden gap-2">
                @forelse($transferencias as $transferencia)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm mb-4">
                        <!-- Header do card -->
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                                <i class="fas fa-user-graduate text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-base">{{ $transferencia->aluno->nome_completo }}</h3>
                                <p class="text-sm text-gray-500">{{ $transferencia->aluno->cpf }}</p>
                            </div>
                            <div class="ml-2">
                                @switch($transferencia->status)
                                    @case('pendente')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pendente
                                        </span>
                                        @break
                                    @case('aprovada')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Aprovada
                                        </span>
                                        @break
                                    @case('rejeitada')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Rejeitada
                                        </span>
                                        @break
                                    @case('cancelada')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Cancelada
                                        </span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        
                        <!-- Informações das salas em grid -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-blue-50 rounded-lg p-3">
                                <div class="text-xs text-blue-600 mb-1">Turma Origem</div>
                                @if($transferencia->turmaOrigem)
                                    <div class="font-medium text-blue-800">{{ $transferencia->turmaOrigem->nome }}</div>
                                @else
                                    <div class="text-gray-400 text-sm">Sem turma</div>
                                @endif
                            </div>
                            <div class="bg-green-50 rounded-lg p-3">
                                <div class="text-xs text-green-600 mb-1">Turma Destino</div>
                                <div class="font-medium text-green-800">{{ $transferencia->turmaDestino->nome }}</div>
                            </div>
                        </div>
                        
                        <!-- Informações adicionais -->
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Solicitante:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $transferencia->solicitante->name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Data:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $transferencia->data_solicitacao->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($transferencia->motivo)
                                <div class="bg-gray-50 rounded p-2">
                                    <div class="text-xs text-gray-600 mb-1">Motivo:</div>
                                    <div class="text-sm text-gray-900">{{ $transferencia->motivo }}</div>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Botões de ação com touch targets otimizados -->
                        <div class="flex space-x-2">
                            <a href="{{ route('transferencias.show', $transferencia) }}" 
                               class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                                <i class="fas fa-eye mr-2"></i>
                                Ver Detalhes
                            </a>
                            
                            @if($transferencia->status === 'pendente')
                                <button type="button" 
                                   class="bg-green-600 hover:bg-green-700 text-white py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors" 
                                   title="Aprovar" x-data="{}" @click="$dispatch('open-modal', 'aprovar-transferencia-{{ $transferencia->id }}')">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" 
                                   class="bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors" 
                                   title="Rejeitar" x-data="{}" @click="$dispatch('open-modal', 'rejeitar-transferencia-{{ $transferencia->id }}')">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button type="button" 
                                        class="bg-red-600 hover:bg-red-700 text-white py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors" 
                                        title="Excluir" x-data="{}" @click="$dispatch('open-modal', 'excluir-transferencia-{{ $transferencia->id }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-inbox text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500">Nenhuma transferência encontrada.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-4">
            {{ $transferencias->links('components.pagination') }}
        </div>
    </x-card>
</x-card>


@endsection

@push('scripts')
<script>
// Modais substituem confirmação por alert; sem JS de exclusão direta
</script>
@endpush

@foreach($transferencias as $transferencia)
    @if($transferencia->status === 'pendente')
        <!-- Modal Aprovar -->
        <x-modal name="aprovar-transferencia-{{ $transferencia->id }}" title="Aprovar Transferência" :closable="true" maxWidth="max-w-md">
            <form action="{{ route('transferencias.aprovar', $transferencia->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <p class="text-sm text-gray-600">Tem certeza que deseja aprovar a transferência do aluno <span class="font-medium">{{ $transferencia->aluno->nome_completo }}</span>?</p>
                <div>
                    <label for="observacoes_aprovador_{{ $transferencia->id }}" class="block text-sm font-medium text-gray-700 mb-2">Observações (opcional)</label>
                    <textarea id="observacoes_aprovador_{{ $transferencia->id }}" name="observacoes_aprovador" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Digite observações sobre a aprovação..."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t">
                    <x-button type="button" color="secondary" x-data="{}" @click="$dispatch('close-modal')">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </x-button>
                    <x-button type="submit" color="primary" class="bg-green-600 hover:bg-green-700 text-white">
                        <i class="fas fa-check mr-1"></i> Aprovar
                    </x-button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Rejeitar -->
        <x-modal name="rejeitar-transferencia-{{ $transferencia->id }}" title="Rejeitar Transferência" :closable="true" maxWidth="max-w-md">
            <form action="{{ route('transferencias.rejeitar', $transferencia->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')

                <p class="text-sm text-gray-600">Tem certeza que deseja rejeitar a transferência do aluno <span class="font-medium">{{ $transferencia->aluno->nome_completo }}</span>?</p>
                <div>
                    <label for="motivo_rejeicao_{{ $transferencia->id }}" class="block text-sm font-medium text-gray-700 mb-2">Motivo da Rejeição *</label>
                    <textarea id="motivo_rejeicao_{{ $transferencia->id }}" name="motivo_rejeicao" rows="3" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Digite o motivo da rejeição..."></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t">
                    <x-button type="button" color="secondary" x-data="{}" @click="$dispatch('close-modal')">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </x-button>
                    <x-button type="submit" color="primary" class="bg-red-600 hover:bg-red-700 text-white">
                        <i class="fas fa-times mr-1"></i> Rejeitar
                    </x-button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Excluir -->
        <x-modal name="excluir-transferencia-{{ $transferencia->id }}" title="Excluir Transferência" :closable="true" maxWidth="max-w-sm">
            <form action="/transferencias/{{ $transferencia->id }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <p class="text-sm text-gray-600">Esta ação é permanente. Tem certeza que deseja excluir a solicitação de transferência do aluno <span class="font-medium">{{ $transferencia->aluno->nome_completo }}</span>?</p>

                <div class="flex justify-end space-x-3 pt-3 border-t">
                    <x-button type="button" color="secondary" x-data="{}" @click="$dispatch('close-modal')">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </x-button>
                    <x-button type="submit" color="primary" class="border border-red-300 text-red-700 hover:bg-red-50">
                        <i class="fas fa-trash mr-1"></i> Excluir
                    </x-button>
                </div>
            </form>
        </x-modal>
    @endif
@endforeach