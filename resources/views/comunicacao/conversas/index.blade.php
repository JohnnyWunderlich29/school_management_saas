@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Conversas']
]" />

<x-card>
    <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Conversas</h1>
            <p class="mt-1 text-sm text-gray-600">Gerencie suas conversas e mensagens</p>
        </div>
        <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
            @can('create', App\Models\Comunicado::class)
            <x-button href="{{ route('conversas.create') }}" color="primary" class="w-full sm:justify-center">
                <i class="fas fa-plus mr-1"></i> 
                <span class="hidden md:inline">Nova Conversa</span>
                <span class="md:hidden">Nova</span>
            </x-button>
            @endcan
        </div>
    </div>

    <x-collapsible-filter 
        title="Filtros de Conversas" 
        :action="route('conversas.index')" 
        :clear-route="route('conversas.index')"
    >
        <x-filter-field 
            name="tipo" 
            label="Tipo" 
            type="select"
            empty-option="Todos os tipos"
            :options="['individual' => 'Individual', 'grupo' => 'Grupo', 'turma' => 'Turma', 'geral' => 'Geral']"
        />
        
        <x-filter-field 
            name="busca" 
            label="Buscar"
            type="text"
            placeholder="Título da conversa..."
        />
    </x-collapsible-filter>

    @if($conversas->count() > 0)
        <!-- Grid responsivo para conversas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @foreach($conversas as $conversa)
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 cursor-pointer conversa-card {{ $conversa->mensagens_nao_lidas > 0 ? 'ring-2 ring-blue-500 ring-opacity-50' : '' }}" 
                     onclick="window.location.href='{{ route('conversas.show', $conversa) }}'">
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center mr-3
                                    {{ $conversa->tipo === 'grupo' ? 'bg-purple-100 text-purple-600' : 
                                       ($conversa->tipo === 'turma' ? 'bg-green-100 text-green-600' : 
                                       ($conversa->tipo === 'geral' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600')) }}">
                                    <i class="fas fa-{{ $conversa->tipo === 'grupo' ? 'users' : ($conversa->tipo === 'turma' ? 'graduation-cap' : ($conversa->tipo === 'geral' ? 'bullhorn' : 'user')) }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-medium text-gray-900 truncate">{{ $conversa->titulo }}</h3>
                                    <p class="text-xs text-gray-500 capitalize">{{ $conversa->tipo }}</p>
                                </div>
                            </div>
                            @if($conversa->mensagens_nao_lidas > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $conversa->mensagens_nao_lidas }}
                                </span>
                            @endif
                        </div>
                        
                        @if($conversa->ultimaMensagem)
                            <div class="mb-3">
                                <p class="text-sm text-gray-600 line-clamp-2">{{ Str::limit(strip_tags($conversa->ultimaMensagem->getConteudoSanitizado()), 80) }}</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $conversa->ultimaMensagem->remetente?->name ?? 'Usuário removido' }} • 
                                    {{ $conversa->ultimaMensagem->created_at->diffForHumans() }}
                                </p>
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center text-xs text-gray-500">
                                <i class="fas fa-users mr-1"></i>
                                {{ $conversa->participantesAtivos->count() }} participantes
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="event.stopPropagation(); arquivarConversa({{ $conversa->id }})" 
                                        class="text-gray-400 hover:text-red-500 transition-colors">
                                    <i class="fas fa-archive text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Paginação -->
        <div class="flex justify-center">
            {{ $conversas->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-comments text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma conversa encontrada</h3>
            <p class="text-gray-500 mb-6">Inicie uma nova conversa para começar a se comunicar.</p>
            <x-button href="{{ route('conversas.create') }}" color="primary">
                <i class="fas fa-plus mr-2"></i>
                Criar Primeira Conversa
            </x-button>
        </div>
    @endif
</x-card>
@endsection

@push('styles')
<style>
.conversa-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.conversa-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.conversa-card.border-primary {
    border-width: 2px !important;
}
</style>
@endpush

@push('scripts')
<script>
function arquivarConversa(conversaId) {
    if (confirm('Tem certeza que deseja arquivar esta conversa?')) {
        fetch(`/conversas/${conversaId}/arquivar`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao arquivar conversa: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao arquivar conversa.');
        });
    }
}

// Clique no card para abrir conversa
document.querySelectorAll('.conversa-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (!e.target.closest('button') && !e.target.closest('a')) {
            // Pegar o href do onclick do próprio card
            const onclickAttr = this.getAttribute('onclick');
            if (onclickAttr) {
                const match = onclickAttr.match(/window\.location\.href='([^']+)'/);
                if (match && match[1]) {
                    window.location.href = match[1];
                }
            }
        }
    });
});
</script>
@endpush