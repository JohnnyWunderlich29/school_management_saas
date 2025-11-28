@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Comunicados']
]" />

<x-card>
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Comunicados</h1>
            <p class="mt-1 text-sm text-gray-600">Visualize e gerencie comunicados da escola</p>
        </div>
        <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
            @can('create', App\Models\Comunicado::class)
                <x-button color="primary" class="w-full sm:justify-center" onclick="showModal('comunicado-create-modal')">
                    <i class="fas fa-plus mr-1"></i> 
                    <span class="hidden md:inline">Novo Comunicado</span>
                    <span class="md:hidden">Novo</span>
                </x-button>
            @endcan
        </div>
    </div>

    <!-- Abas de Status -->
    <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg mb-6">
        <a href="{{ route('comunicados.index', array_merge(request()->query(), ['status' => 'publicados'])) }}"
           class="flex-1 text-center py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200 {{ $status === 'publicados' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
            <i class="fas fa-eye mr-2"></i>
            Publicados
        </a>
        <a href="{{ route('comunicados.index', array_merge(request()->query(), ['status' => 'rascunhos'])) }}"
           class="flex-1 text-center py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200 {{ $status === 'rascunhos' ? 'bg-white text-yellow-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
            <i class="fas fa-edit mr-2"></i>
            Rascunhos
        </a>
    </div>

    <x-collapsible-filter 
        title="Filtros de Comunicados" 
        :action="route('comunicados.index')" 
        :clear-route="route('comunicados.index')"
    >
        <x-filter-field 
            name="tipo" 
            label="Tipo" 
            type="select"
            empty-option="Todos os tipos"
            :options="collect($tipos)->mapWithKeys(fn($tipo) => [$tipo => ucfirst($tipo)])->toArray()"
        />
        
        <x-filter-field 
            name="busca" 
            label="Buscar"
            type="text"
            placeholder="Título do comunicado..."
        />
        
        <x-filter-field 
            name="destinatario_tipo" 
            label="Destinatário" 
            type="select"
            empty-option="Todos os destinatários"
            :options="['todos' => 'Todos', 'pais' => 'Pais/Responsáveis', 'professores' => 'Professores', 'turma_especifica' => 'Turma Específica']"
        />
    </x-collapsible-filter>
                    
    @if($comunicados->count() > 0)
        <!-- Grid responsivo para comunicados -->
        <div id="comunicados-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach($comunicados as $comunicado)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer comunicado-card {{ $comunicado->tipo === 'urgente' ? 'ring-2 ring-red-500 ring-opacity-50' : '' }} {{ $status === 'rascunhos' ? 'border-l-4 border-l-yellow-400' : '' }}" 
                     onclick="window.location.href='{{ route('comunicados.show', $comunicado) }}'">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3
                                    {{ $comunicado->tipo === 'urgente' ? 'bg-red-100 text-red-600' : 
                                       ($comunicado->tipo === 'evento' ? 'bg-blue-100 text-blue-600' : 
                                       ($comunicado->tipo === 'informativo' ? 'bg-green-100 text-green-600' : 
                                       ($comunicado->tipo === 'reuniao' ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-600'))) }}">
                                    <i class="{{ $comunicado->icone_tipo }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-medium text-gray-900 truncate">{{ $comunicado->titulo }}</h3>
                                    <p class="text-xs text-gray-500 capitalize">{{ $comunicado->tipo }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col flex-wrap items-end space-y-1">
                                <div class="flex flex-wrap items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $comunicado->classe_tipo }}">
                                        {{ ucfirst($comunicado->tipo) }}
                                    </span>
                                    @if($status === 'rascunhos')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-edit mr-1"></i>
                                            Rascunho
                                        </span>
                                    @endif
                                </div>
                                @if($comunicado->requer_confirmacao && isset($comunicado->foi_confirmado))
                                    @if($comunicado->foi_confirmado)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i> Confirmado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i> Pendente
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 line-clamp-3">{{ Str::limit(strip_tags($comunicado->conteudo), 120) }}</p>
                        </div>
                        
                        @if($comunicado->isEvento())
                            <div class="mb-3 p-2 bg-blue-50 rounded-lg">
                                <div class="flex items-center text-xs text-blue-600">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $comunicado->data_hora_evento_formatada }}
                                </div>
                                @if($comunicado->local_evento)
                                    <div class="flex items-center text-xs text-gray-500 mt-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ $comunicado->local_evento }}
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        @if($comunicado->turma)
                            <div class="mb-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-graduation-cap mr-1"></i>
                                                        {{ $comunicado->turma->nome }}
                                </span>
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-500">
                                <div class="flex items-center">
                                    <i class="fas fa-user mr-1"></i>
                                    {{ $comunicado->autor->name }}
                                </div>
                                <div class="flex items-center mt-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $comunicado->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="event.stopPropagation(); window.location.href='{{ route('comunicados.show', $comunicado) }}'" 
                                        class="text-gray-400 hover:text-blue-500 transition-colors">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                @can('update', $comunicado)
                                    <button onclick="event.stopPropagation(); window.location.href='{{ route('comunicados.edit', $comunicado) }}'" 
                                            class="text-gray-400 hover:text-gray-600 transition-colors">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                @endcan
                                @if($status === 'rascunhos' && $comunicado->publicado_em === null)
                                    @can('update', $comunicado)
                                        <button onclick="event.stopPropagation(); publicarComunicado({{ $comunicado->id }})" 
                                                class="text-gray-400 hover:text-blue-500 transition-colors">
                                            <i class="fas fa-paper-plane text-sm"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                        </div>
                        
                        @if($comunicado->requer_confirmacao && !$comunicado->foi_confirmado)
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <button onclick="event.stopPropagation(); confirmarComunicado({{ $comunicado->id }})" 
                                        class="w-full bg-yellow-50 hover:bg-yellow-100 text-yellow-800 font-medium py-2 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-check mr-1"></i>
                                    Confirmar Recebimento
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Paginação -->
        <div class="flex justify-center">
            {{ $comunicados->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-bullhorn text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum comunicado encontrado</h3>
            <p class="text-gray-500 mb-6">
                @if(request('tipo'))
                    Não há comunicados do tipo "{{ request('tipo') }}" no momento.
                @elseif($status === 'rascunhos')
                    Não há rascunhos no momento.
                @else
                    Não há comunicados publicados no momento.
                @endif
            </p>
            @if($status === 'rascunhos')
                <p class="text-gray-400 text-sm mb-6">Crie um novo comunicado e salve como rascunho para vê-lo aqui.</p>
            @endif
            @can('create', App\Models\Comunicado::class)
                <x-button color="primary" onclick="showModal('comunicado-create-modal')">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Primeiro Comunicado
                </x-button>
            @endcan
        </div>
    @endif
</x-card>

@can('create', App\Models\Comunicado::class)
    <!-- Modal de Criação de Comunicado -->
    <x-modal name="comunicado-create-modal" title="Novo Comunicado" maxWidth="w-11/12 md:w-5/6 lg:w-2/3">
        <form id="comunicadoCreateForm" action="{{ route('comunicados.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-red-500">*</span></label>
                    <input type="text" id="titulo" name="titulo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-red-600" data-error-for="titulo"></p>
                </div>
                <div>
                    <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                    <select id="tipo" name="tipo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione o tipo</option>
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo }}">{{ ucfirst($tipo) }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-red-600" data-error-for="tipo"></p>
                </div>
                <div>
                    <label for="destinatario_tipo" class="block text-sm font-medium text-gray-700 mb-1">Destinatário <span class="text-red-500">*</span></label>
                    <select id="destinatario_tipo" name="destinatario_tipo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione o destinatário</option>
                        <option value="todos">Todos</option>
                        <option value="pais">Pais/Responsáveis</option>
                        <option value="professores">Professores</option>
                        <option value="turma_especifica">Turma Específica</option>
                    </select>
                    <p class="mt-1 text-xs text-red-600" data-error-for="destinatario_tipo"></p>
                </div>
                <div id="turma-field" class="hidden">
                    <label for="turma_id" class="block text-sm font-medium text-gray-700 mb-1">Turma <span class="text-red-500">*</span></label>
                    <select id="turma_id" name="turma_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione a turma</option>
                        @foreach($turmas as $turma)
                            <option value="{{ $turma->id }}">{{ $turma->nome }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-red-600" data-error-for="turma_id"></p>
                </div>
            </div>

            <div>
                <label for="conteudo" class="block text-sm font-medium text-gray-700 mb-1">Conteúdo <span class="text-red-500">*</span></label>
                <textarea id="conteudo" name="conteudo" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                <p class="mt-1 text-xs text-red-600" data-error-for="conteudo"></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="data_evento" class="block text-sm font-medium text-gray-700 mb-1">Data do Evento</label>
                    <input type="date" id="data_evento" name="data_evento" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-red-600" data-error-for="data_evento"></p>
                </div>
                <div>
                    <label for="hora_evento" class="block text-sm font-medium text-gray-700 mb-1">Hora do Evento</label>
                    <input type="time" id="hora_evento" name="hora_evento" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-red-600" data-error-for="hora_evento"></p>
                </div>
                <div>
                    <label for="local_evento" class="block text-sm font-medium text-gray-700 mb-1">Local do Evento</label>
                    <input type="text" id="local_evento" name="local_evento" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Sala, auditório...">
                    <p class="mt-1 text-xs text-red-600" data-error-for="local_evento"></p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="inline-flex items-center">
                    <input type="checkbox" id="requer_confirmacao" name="requer_confirmacao" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Requer confirmação de recebimento</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="checkbox" id="publicar_agora" name="publicar_agora" value="1" checked class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">Publicar imediatamente</span>
                </label>
            </div>

            <div class="flex justify-end space-x-3 pt-2">
                <x-button type="button" color="secondary" onclick="closeModal('comunicado-create-modal')">Cancelar</x-button>
                <x-button type="submit" color="primary"><i class="fas fa-save mr-2"></i>Criar Comunicado</x-button>
            </div>
        </form>
    </x-modal>
@endcan

<!-- Modal de Confirmação -->
<div id="confirmacaoModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="fecharModal()"></div>
        
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-check text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Confirmar Recebimento
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Confirme que você recebeu e leu este comunicado.
                            </p>
                        </div>
                        <div class="mt-4">
                            <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-2">
                                Observações (opcional)
                            </label>
                            <textarea 
                                id="observacoes" 
                                rows="3" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                                placeholder="Adicione alguma observação se necessário..."
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button 
                    type="button" 
                    id="confirmarBtn"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                >
                    <i class="fas fa-check mr-2"></i>
                    Confirmar
                </button>
                <button 
                    type="button" 
                    onclick="fecharModal()"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.comunicado-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.comunicado-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.comunicado-card.border-danger {
    border-width: 2px !important;
    animation: pulse-border 2s infinite;
}

@keyframes pulse-border {
    0% { border-color: #dc3545; }
    50% { border-color: #ff6b7a; }
    100% { border-color: #dc3545; }
}

.badge {
    font-size: 0.75rem;
}

.card-footer {
    border-top: 1px solid rgba(0,0,0,0.125);
}
</style>
@endpush

@push('scripts')
<script>
let comunicadoParaConfirmar = null;

function confirmarComunicado(comunicadoId) {
    comunicadoParaConfirmar = comunicadoId;
    document.getElementById('observacoes').value = '';
    document.getElementById('confirmacaoModal').classList.remove('hidden');
}

function fecharModal() {
    document.getElementById('confirmacaoModal').classList.add('hidden');
    comunicadoParaConfirmar = null;
}

document.getElementById('confirmarBtn').addEventListener('click', function() {
    if (!comunicadoParaConfirmar) return;
    
    const observacoes = document.getElementById('observacoes').value;
    
    fetch(`/comunicados/${comunicadoParaConfirmar}/confirmar`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ observacoes: observacoes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fecharModal();
            location.reload();
        } else {
            alert('Erro ao confirmar comunicado: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao confirmar comunicado.');
    });
});

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('confirmacaoModal').classList.contains('hidden')) {
        fecharModal();
    }
});

// Clique no card para abrir comunicado
document.querySelectorAll('.comunicado-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (!e.target.closest('button') && !e.target.closest('a')) {
            const comunicadoId = this.querySelector('a[href*="comunicados"]').href.split('/').pop();
            window.location.href = `/comunicados/${comunicadoId}`;
        }
    });
});

// Função para publicar comunicado
function publicarComunicado(comunicadoId) {
    if (confirm('Tem certeza que deseja publicar este comunicado? Ele ficará visível para todos os destinatários.')) {
        fetch(`/comunicados/${comunicadoId}/publicar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar mensagem de sucesso
                const alertDiv = document.createElement('div');
                alertDiv.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
                alertDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>${data.message}</span>
                    </div>
                `;
                document.body.appendChild(alertDiv);
                
                // Remover o alerta após 3 segundos
                setTimeout(() => {
                    alertDiv.remove();
                }, 3000);
                
                // Recarregar a página para atualizar a listagem
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao publicar comunicado. Tente novamente.');
        });
    }
}

// Exibir/ocultar campo de turma
document.addEventListener('DOMContentLoaded', function() {
    const destinatarioSelect = document.getElementById('destinatario_tipo');
    const turmaField = document.getElementById('turma-field');
    const turmaSelect = document.getElementById('turma_id');
    if (destinatarioSelect) {
        const toggleTurmaField = () => {
            if (destinatarioSelect.value === 'turma_especifica') {
                turmaField.classList.remove('hidden');
                if (turmaSelect) turmaSelect.required = true;
            } else {
                turmaField.classList.add('hidden');
                if (turmaSelect) {
                    turmaSelect.required = false;
                    turmaSelect.value = '';
                }
            }
        };
        destinatarioSelect.addEventListener('change', toggleTurmaField);
        toggleTurmaField();
    }
});

// Submissão AJAX do formulário de criação
const createForm = document.getElementById('comunicadoCreateForm');
if (createForm) {
    createForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        // Limpar erros anteriores
        document.querySelectorAll('[data-error-for]').forEach(el => el.textContent = '');

        // Spinner e desabilitar para evitar duplo clique
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHTML = submitBtn ? submitBtn.innerHTML : null;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
        }

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    Object.entries(data.errors).forEach(([field, messages]) => {
                        const el = document.querySelector(`[data-error-for="${field}"]`);
                        if (el) el.textContent = messages[0];
                    });
                } else {
                    alert(data.message || 'Erro ao criar comunicado.');
                }
                return;
            }

            // Sucesso: adicionar card na lista conforme filtro atual
            const comunicado = data.comunicado;
            const grid = document.getElementById('comunicados-grid');
            const currentStatus = window.__comunicadosStatus || '{{ $status }}';
            const isPublicado = !!comunicado.publicado_em;
            const shouldAppend = (currentStatus === 'publicados' && isPublicado) || (currentStatus === 'rascunhos' && !isPublicado);

            if (grid && shouldAppend) {
                const card = document.createElement('div');
                card.className = `bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer comunicado-card ${comunicado.tipo === 'urgente' ? 'ring-2 ring-red-500 ring-opacity-50' : ''} ${currentStatus === 'rascunhos' ? 'border-l-4 border-l-yellow-400' : ''}`;
                card.innerHTML = `
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3 ${comunicado.tipo === 'urgente' ? 'bg-red-100 text-red-600' : (comunicado.tipo === 'evento' ? 'bg-blue-100 text-blue-600' : (comunicado.tipo === 'informativo' ? 'bg-green-100 text-green-600' : (comunicado.tipo === 'reuniao' ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-600')))}">
                                    <i class="${comunicado.icone_tipo}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-medium text-gray-900 truncate">${comunicado.titulo}</h3>
                                    <p class="text-xs text-gray-500 capitalize">${comunicado.tipo}</p>
                                </div>
                            </div>
                            <div class="flex flex-col flex-wrap items-end space-y-1">
                                <div class="flex flex-wrap items-center space-x-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${comunicado.classe_tipo}">
                                        ${comunicado.tipo.charAt(0).toUpperCase() + comunicado.tipo.slice(1)}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 line-clamp-3">${(comunicado.conteudo || '').substring(0,120)}</p>
                        </div>
                        ${comunicado.data_hora_evento_formatada ? `
                        <div class="mb-3 p-2 bg-blue-50 rounded-lg">
                            <div class="flex items-center text-xs text-blue-600">
                                <i class="fas fa-calendar mr-1"></i>
                                ${comunicado.data_hora_evento_formatada}
                            </div>
                            ${comunicado.local_evento ? `<div class="flex items-center text-xs text-gray-500 mt-1"><i class=\"fas fa-map-marker-alt mr-1\"></i>${comunicado.local_evento}</div>` : ''}
                        </div>` : ''}
                        ${comunicado.turma ? `
                        <div class="mb-3">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-graduation-cap mr-1"></i>
                                ${comunicado.turma.nome}
                            </span>
                        </div>` : ''}
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-500">
                                <div class="flex items-center"><i class="fas fa-user mr-1"></i>${comunicado.autor?.name || '{{ auth()->user()->name }}'}</div>
                                <div class="flex items-center mt-1"><i class="fas fa-clock mr-1"></i>agora</div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="event.stopPropagation(); window.location.href='${`/comunicados/${comunicado.id}`}'" class="text-gray-400 hover:text-blue-500 transition-colors">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                                <button onclick="event.stopPropagation(); window.location.href='${`/comunicados/${comunicado.id}/edit`}'" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>`;
                card.addEventListener('click', function(){ window.location.href = `/comunicados/${comunicado.id}`; });
                grid.prepend(card);
            }

            // Toast simples
            const alertDiv = document.createElement('div');
            alertDiv.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
            alertDiv.innerHTML = `<div class="flex items-center"><i class="fas fa-check-circle mr-2"></i><span>${data.message}</span></div>`;
            document.body.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);

            // Fechar modal e resetar formulário
            closeModal('comunicado-create-modal');
            form.reset();
        })
        .catch(err => {
            if (err) {
                console.error(err);
                alert('Erro inesperado ao criar comunicado.');
            }
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }
        });
    });
}

// Alternar Publicados/Rascunhos sem recarregar página
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa status global para uso dinâmico
    window.__comunicadosStatus = '{{ $status }}';

    const tabPublicados = document.querySelector('a[href*="status=publicados"]');
    const tabRascunhos = document.querySelector('a[href*="status=rascunhos"]');

    function attach(tabEl, status) {
        if (!tabEl) return;
        tabEl.addEventListener('click', function(e) {
            e.preventDefault();
            const url = tabEl.getAttribute('href');
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newGrid = doc.querySelector('#comunicados-grid');
                    const grid = document.getElementById('comunicados-grid');
                    if (newGrid && grid) {
                        grid.innerHTML = newGrid.innerHTML;
                    }

                    // Atualiza status global
                    window.__comunicadosStatus = status;

                    // Atualiza classes visuais das abas
                    [tabPublicados, tabRascunhos].forEach(el => {
                        if (!el) return;
                        el.classList.remove('bg-white','text-blue-600','text-yellow-600','shadow-sm');
                        el.classList.add('text-gray-600');
                    });
                    // Define ativa
                    tabEl.classList.remove('text-gray-600');
                    tabEl.classList.add('bg-white', status === 'publicados' ? 'text-blue-600' : 'text-yellow-600', 'shadow-sm');
                })
                .catch(err => {
                    console.error('Erro ao alternar status:', err);
                    alert('Não foi possível alternar o filtro agora.');
                });
        });
    }

    attach(tabPublicados, 'publicados');
    attach(tabRascunhos, 'rascunhos');
});
</script>
@endpush