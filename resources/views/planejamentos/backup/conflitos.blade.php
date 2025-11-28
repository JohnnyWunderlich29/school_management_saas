<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gerenciar Conflitos de Planejamentos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Verificar Conflitos por Data</h3>
                        
                        <form method="GET" action="{{ route('planejamentos.conflitos') }}" class="flex gap-4 items-end">
                            <div>
                                <label for="data" class="block text-sm font-medium text-gray-700">Data</label>
                                <input type="date" name="data" id="data" value="{{ request('data') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Verificar
                            </button>
                        </form>
                    </div>

                    @if(isset($planejamentos) && $planejamentos->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-900 mb-3">
                                Planejamentos encontrados para {{ \Carbon\Carbon::parse(request('data'))->format('d/m/Y') }}:
                            </h4>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Professor</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turma</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($planejamentos as $planejamento)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $planejamento->id }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $planejamento->data_inicio->format('d/m/Y') }} - {{ $planejamento->data_fim->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $planejamento->user->name ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $planejamento->turma->nome ?? 'N/A' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($planejamento->tipo_professor) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        @if($planejamento->status === 'aprovado') bg-green-100 text-green-800
                                                        @elseif($planejamento->status === 'finalizado') bg-blue-100 text-blue-800
                                                        @elseif($planejamento->status === 'rascunho') bg-yellow-100 text-yellow-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($planejamento->status) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="flex space-x-2">
                                                        <a href="{{ route('planejamentos.show', $planejamento) }}" 
                                                           class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                                        
                                                        @if(auth()->user()->isAdminOrCoordinator())
                                                            <form method="POST" action="{{ route('planejamentos.conflitos.excluir', $planejamento) }}" 
                                                                  class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este planejamento?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @elseif(request('data'))
                        <div class="bg-green-50 border border-green-200 rounded-md p-4">
                            <p class="text-green-800">✅ Nenhum planejamento encontrado para a data {{ \Carbon\Carbon::parse(request('data'))->format('d/m/Y') }}.</p>
                        </div>
                    @endif

                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Verificar Todos os Conflitos</h3>
                        
                        <form method="POST" action="{{ route('planejamentos.conflitos.verificar-todos') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                Verificar Todos os Conflitos
                            </button>
                        </form>
                    </div>

                    @if(isset($conflitos) && count($conflitos) > 0)
                        <div class="mt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-3">Conflitos Encontrados:</h4>
                            
                            @foreach($conflitos as $conflito)
                                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                                    <p class="text-red-800 font-medium">⚠️ Conflito detectado:</p>
                                    <p class="text-red-700">
                                        Planejamento ID {{ $conflito['atual']['id'] }} 
                                        ({{ \Carbon\Carbon::parse($conflito['atual']['data_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($conflito['atual']['data_fim'])->format('d/m/Y') }}) 
                                        sobrepõe com ID {{ $conflito['proximo']['id'] }} 
                                        ({{ \Carbon\Carbon::parse($conflito['proximo']['data_inicio'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($conflito['proximo']['data_fim'])->format('d/m/Y') }})
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @elseif(isset($conflitos))
                        <div class="mt-6 bg-green-50 border border-green-200 rounded-md p-4">
                            <p class="text-green-800">✅ Nenhum conflito encontrado!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>