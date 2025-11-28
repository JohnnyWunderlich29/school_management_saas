<!-- Tabela de Dados de Performance -->
<div class="space-y-6">
    <!-- Resumo Estatístico -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-900">Média Presenças/Dia</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $data['media_presencas_dia'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-calendar-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-900">Média Escalas/Dia</p>
                    <p class="text-2xl font-bold text-green-600">{{ $data['media_escalas_dia'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-percentage text-purple-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-purple-900">Taxa Eficiência</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $data['taxa_eficiencia'] ?? 0 }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-orange-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-clock text-orange-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-orange-900">Período Analisado</p>
                    <p class="text-lg font-bold text-orange-600">{{ $data['periodo'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Destaques -->
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

    <!-- Tabela de Performance por Funcionário -->
    @if(isset($data['performance_funcionarios']) && count($data['performance_funcionarios']) > 0)
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-users mr-2 text-indigo-600"></i>
                    Performance por Funcionário
                </h3>
                <p class="text-sm text-gray-600 mt-1">Análise de desempenho individual</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Funcionário
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Escalas
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Horas Trabalhadas
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Presenças Registradas
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Eficiência
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['performance_funcionarios'] as $index => $funcionario)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $funcionario['nome'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $funcionario['total_escalas'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $funcionario['horas_trabalhadas'] ?? 0 }}h
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $funcionario['presencas_registradas'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $eficiencia = $funcionario['eficiencia'] ?? 0;
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="h-2 rounded-full 
                                                @if($eficiencia >= 80) bg-green-500
                                                @elseif($eficiencia >= 60) bg-yellow-500
                                                @else bg-red-500 @endif" 
                                                style="width: {{ min($eficiencia, 100) }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $eficiencia }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Tabela de Performance por Sala -->
    @if(isset($data['performance_salas']) && count($data['performance_salas']) > 0)
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-door-open mr-2 text-indigo-600"></i>
                    Performance por Sala
                </h3>
                <p class="text-sm text-gray-600 mt-1">Análise de utilização das salas</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sala
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Escalas
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Horas de Uso
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Taxa de Ocupação
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['performance_salas'] as $index => $sala)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $sala['nome'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $sala['total_escalas'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $sala['horas_uso'] ?? 0 }}h
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $ocupacao = $sala['taxa_ocupacao'] ?? 0;
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="h-2 rounded-full 
                                                @if($ocupacao >= 80) bg-green-500
                                                @elseif($ocupacao >= 60) bg-yellow-500
                                                @else bg-red-500 @endif" 
                                                style="width: {{ min($ocupacao, 100) }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $ocupacao }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-chart-line text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Dados de performance não disponíveis</h3>
            <p class="text-gray-600">Não foram encontrados dados suficientes para análise de performance.</p>
        </div>
    @endif
</div>