<!-- Tabela de Dados de Escalas -->
<div class="space-y-6">
    <!-- Resumo Estatístico -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-calendar text-blue-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-900">Total de Escalas</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $data['total_escalas'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-clock text-green-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-900">Total de Horas</p>
                    <p class="text-2xl font-bold text-green-600">{{ $data['total_horas'] ?? 0 }}h</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user text-purple-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-purple-900">Funcionários Ativos</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $data['funcionarios_ativos'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-orange-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-door-open text-orange-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-orange-900">Salas Utilizadas</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $data['salas_utilizadas'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações Adicionais -->
    @if(isset($data['funcionario_mais_ativo']) || isset($data['sala_mais_utilizada']))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            @if(isset($data['funcionario_mais_ativo']) && $data['funcionario_mais_ativo'] !== 'N/A')
                <div class="bg-indigo-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-indigo-900 mb-2">
                        <i class="fas fa-star mr-1"></i>
                        Funcionário Mais Ativo
                    </h4>
                    <p class="text-lg font-semibold text-indigo-700">{{ $data['funcionario_mais_ativo'] }}</p>
                </div>
            @endif
            
            @if(isset($data['sala_mais_utilizada']) && $data['sala_mais_utilizada'] !== 'N/A')
                <div class="bg-teal-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-teal-900 mb-2">
                        <i class="fas fa-trophy mr-1"></i>
                        Sala Mais Utilizada
                    </h4>
                    <p class="text-lg font-semibold text-teal-700">{{ $data['sala_mais_utilizada'] }}</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Tabela de Detalhes -->
    @if(isset($data['detalhes']) && count($data['detalhes']) > 0)
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-table mr-2 text-indigo-600"></i>
                    Detalhes das Escalas
                </h3>
                <p class="text-sm text-gray-600 mt-1">Total de {{ count($data['detalhes']) }} escalas encontradas</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Funcionário
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sala
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Turno
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Horário
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Horas
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['detalhes'] as $index => $detalhe)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $detalhe['data'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $detalhe['funcionario'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $detalhe['sala'] ?? 'Não especificada' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($detalhe['turno'] === 'Manhã') bg-yellow-100 text-yellow-800
                                        @elseif($detalhe['turno'] === 'Tarde') bg-orange-100 text-orange-800
                                        @elseif($detalhe['turno'] === 'Noite') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $detalhe['turno'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $detalhe['horario'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $detalhe['horas'] }}h
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(count($data['detalhes']) > 50)
                <div class="px-6 py-4 bg-yellow-50 border-t border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-yellow-600 mr-2"></i>
                        <p class="text-sm text-yellow-800">
                            <strong>Nota:</strong> Mostrando todos os {{ count($data['detalhes']) }} registros. 
                            Para melhor performance, considere aplicar filtros mais específicos ao gerar o relatório.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-inbox text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma escala encontrada</h3>
            <p class="text-gray-600">Não foram encontradas escalas para os filtros aplicados.</p>
        </div>
    @endif
</div>