@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Comunicados', 'route' => 'comunicados.index'],
    ['title' => $comunicado->titulo]
]" />

<div class="max-w-4xl mx-auto">
    <x-card>
        <!-- Cabeçalho -->
        <div class="border-b border-gray-200 pb-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @switch($comunicado->tipo)
                                @case('urgente')
                                    bg-red-100 text-red-800
                                    @break
                                @case('evento')
                                    bg-purple-100 text-purple-800
                                    @break
                                @case('reuniao')
                                    bg-blue-100 text-blue-800
                                    @break
                                @case('aviso')
                                    bg-yellow-100 text-yellow-800
                                    @break
                                @default
                                    bg-gray-100 text-gray-800
                            @endswitch">
                            {{ ucfirst($comunicado->tipo) }}
                        </span>
                        
                        @if($comunicado->requer_confirmacao)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Requer Confirmação
                            </span>
                        @endif
                    </div>
                    
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $comunicado->titulo }}</h1>
                    
                    <div class="flex flex-wrap items-center text-sm text-gray-500 space-x-4">
                        <div class="flex items-center">
                            <i class="fas fa-user mr-1"></i>
                            {{ $comunicado->autor->name }}
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $comunicado->created_at->format('d/m/Y H:i') }}
                        </div>
                        @if($comunicado->destinatario_tipo === 'turma_especifica' && $comunicado->turma)
                            <div class="flex items-center">
                                <i class="fas fa-users mr-1"></i>
                                {{ $comunicado->turma->nome }}
                            </div>
                        @else
                            <div class="flex items-center">
                                <i class="fas fa-bullhorn mr-1"></i>
                                @switch($comunicado->destinatario_tipo)
                                    @case('todos')
                                        Todos
                                        @break
                                    @case('pais')
                                        Pais/Responsáveis
                                        @break
                                    @case('professores')
                                        Professores
                                        @break
                                    @default
                                        {{ ucfirst($comunicado->destinatario_tipo) }}
                                @endswitch
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="flex space-x-2 mt-4 md:mt-0">
                    @can('update', $comunicado)
                        <x-button href="{{ route('comunicados.edit', $comunicado) }}" color="secondary" size="sm">
                            <i class="fas fa-edit mr-1"></i>
                            Editar
                        </x-button>
                    @endcan
                    
                    @can('delete', $comunicado)
                        <form action="{{ route('comunicados.destroy', $comunicado) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <x-button type="submit" color="danger" size="sm" onclick="return confirm('Tem certeza que deseja excluir este comunicado?')">
                                <i class="fas fa-trash mr-1"></i>
                                Excluir
                            </x-button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
        
        <!-- Data do Evento -->
        @if($comunicado->data_evento)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                    <div>
                        <h3 class="text-sm font-medium text-blue-900">Data do Evento</h3>
                        <p class="text-sm text-blue-700">{{ \Carbon\Carbon::parse($comunicado->data_evento)->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Conteúdo -->
        <div class="prose max-w-none mb-6">
            <div class="text-gray-900 leading-relaxed whitespace-pre-line">{{ $comunicado->conteudo }}</div>
        </div>
        
        <!-- Status de Publicação -->
        <div class="border-t border-gray-200 pt-6">
            @if($comunicado->isPublicado())
                <div class="flex items-center text-green-600">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span class="text-sm font-medium">Publicado em {{ $comunicado->publicado_em->format('d/m/Y H:i') }}</span>
                </div>
            @else
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-yellow-600">
                        <i class="fas fa-clock mr-2"></i>
                        <span class="text-sm font-medium">Rascunho - Não publicado</span>
                    </div>
                    @can('update', $comunicado)
                        <form action="{{ route('comunicados.publicar', $comunicado) }}" method="POST" class="inline">
                            @csrf
                            <x-button type="submit" color="primary" size="sm">
                                <i class="fas fa-paper-plane mr-1"></i>
                                Publicar Agora
                            </x-button>
                        </form>
                    @endcan
                </div>
            @endif
        </div>
        
        <!-- Confirmação de Recebimento -->
        @if($comunicado->requer_confirmacao && $comunicado->isPublicado())
            <div class="border-t border-gray-200 pt-6 mt-6">
                @if(!$foiConfirmado && !auth()->user()->hasRole('admin'))
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-orange-600 mr-2"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-orange-900">Confirmação Necessária</h3>
                                    <p class="text-sm text-orange-700">Este comunicado requer confirmação de recebimento.</p>
                                </div>
                            </div>
                            <button onclick="confirmarComunicado({{ $comunicado->id }})" 
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-check mr-1"></i>
                                Confirmar Recebimento
                            </button>
                        </div>
                    </div>
                @elseif($foiConfirmado)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <div>
                                <h3 class="text-sm font-medium text-green-900">Recebimento Confirmado</h3>
                                <p class="text-sm text-green-700">Você confirmou o recebimento deste comunicado.</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                @can('view', $comunicado)
                    @if($comunicado->confirmacoes->count() > 0)
                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-medium text-gray-900">Confirmações de Recebimento</h3>
                                @can('update', $comunicado)
                                    <x-button href="{{ route('comunicados.relatorio-confirmacoes', $comunicado) }}" color="secondary" size="sm">
                                        <i class="fas fa-download mr-1"></i>
                                        Relatório
                                    </x-button>
                                @endcan
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($comunicado->confirmacoes->take(6) as $confirmacao)
                                        <div class="flex items-center text-sm">
                                            <i class="fas fa-user-check text-green-600 mr-2"></i>
                                            <span class="text-gray-900">{{ $confirmacao->usuario->name }}</span>
                                            <span class="text-gray-500 ml-auto">{{ $confirmacao->created_at->format('d/m H:i') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                @if($comunicado->confirmacoes->count() > 6)
                                    <div class="mt-3 text-center">
                                        <span class="text-sm text-gray-500">e mais {{ $comunicado->confirmacoes->count() - 6 }} confirmações...</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endcan
            </div>
        @endif
    </x-card>
</div>

<script>
function confirmarComunicado(comunicadoId) {
    if (!confirm('Tem certeza que deseja confirmar o recebimento deste comunicado?')) {
        return;
    }
    
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    
    // Desabilitar botão e mostrar loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Confirmando...';
    
    fetch(`/comunicados/${comunicadoId}/confirmar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recarregar a página para mostrar o status atualizado
            location.reload();
        } else {
            alert('Erro ao confirmar comunicado: ' + data.message);
            button.disabled = false;
            button.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao confirmar comunicado. Tente novamente.');
        button.disabled = false;
        button.innerHTML = originalText;
    });
}
</script>

@endsection