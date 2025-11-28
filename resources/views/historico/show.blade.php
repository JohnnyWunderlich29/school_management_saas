@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Detalhes do Histórico</h1>
        <p class="mt-1 text-sm text-gray-600">Informações detalhadas sobre a ação realizada</p>
    </div>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Cabeçalho -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Registro #{{ $historico->id }}</h3>
                            <a href="{{ route('historico.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Voltar
                            </a>
                        </div>
                    </div>

                    <!-- Informações Gerais -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Informações Gerais</h4>
                            <div class="space-y-2">
                                <div>
                                    <span class="font-medium text-gray-600">Data/Hora:</span>
                                    <span class="text-gray-900">{{ $historico->created_at->format('d/m/Y H:i:s') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Usuário:</span>
                                    <span class="text-gray-900">{{ $historico->usuario->name ?? 'Sistema' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Ação:</span>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($historico->acao == 'criado') bg-green-100 text-green-800
                                        @elseif($historico->acao == 'atualizado') bg-yellow-100 text-yellow-800
                                        @elseif($historico->acao == 'excluido') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($historico->acao) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Modelo:</span>
                                    <span class="text-gray-900">{{ $historico->modelo }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">ID do Registro:</span>
                                    <span class="text-gray-900">{{ $historico->modelo_id }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Informações Técnicas</h4>
                            <div class="space-y-2">
                                <div>
                                    <span class="font-medium text-gray-600">IP:</span>
                                    <span class="text-gray-900">{{ $historico->ip_address }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">User Agent:</span>
                                    <span class="text-gray-900 text-sm break-all">{{ $historico->user_agent }}</span>
                                </div>
                                @if($historico->observacoes)
                                    <div>
                                        <span class="font-medium text-gray-600">Observações:</span>
                                        <span class="text-gray-900">{{ $historico->observacoes }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Dados Antigos -->
                    @if($historico->dados_antigos && count($historico->dados_antigos) > 0)
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Dados Anteriores</h4>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ json_encode($historico->dados_antigos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- Dados Novos -->
                    @if($historico->dados_novos && count($historico->dados_novos) > 0)
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Dados Novos</h4>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <pre class="text-sm text-gray-800 whitespace-pre-wrap">{{ json_encode($historico->dados_novos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    <!-- Comparação de Dados (apenas para atualizações) -->
                    @if($historico->acao == 'atualizado' && $historico->dados_antigos && $historico->dados_novos)
                        <div class="mb-8">
                            <h4 class="text-md font-semibold text-gray-700 mb-3">Comparação de Alterações</h4>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($historico->dados_novos as $campo => $valorNovo)
                                        @if(isset($historico->dados_antigos[$campo]) && $historico->dados_antigos[$campo] != $valorNovo)
                                            <div class="border-b border-blue-200 pb-2">
                                                <div class="font-medium text-blue-800">{{ ucfirst(str_replace('_', ' ', $campo)) }}</div>
                                                <div class="text-sm">
                                                    <span class="text-red-600">Antes:</span> 
                                                    <span class="bg-red-100 px-1 rounded">{{ $historico->dados_antigos[$campo] ?? 'N/A' }}</span>
                                                </div>
                                                <div class="text-sm">
                                                    <span class="text-green-600">Depois:</span> 
                                                    <span class="bg-green-100 px-1 rounded">{{ $valorNovo }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection