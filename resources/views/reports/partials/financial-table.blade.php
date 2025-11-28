<!-- Tabela de Dados Financeiros -->
<div class="space-y-6">
    <!-- Resumo Estatístico -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-900">Receita Total</p>
                    <p class="text-2xl font-bold text-green-600">R$ {{ number_format($data['receita_total'] ?? 0, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-red-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-minus-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-900">Despesas Totais</p>
                    <p class="text-2xl font-bold text-red-600">R$ {{ number_format($data['despesas_total'] ?? 0, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-900">Lucro Líquido</p>
                    @php
                        $lucro = ($data['receita_total'] ?? 0) - ($data['despesas_total'] ?? 0);
                    @endphp
                    <p class="text-2xl font-bold {{ $lucro >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                        R$ {{ number_format($lucro, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-percentage text-purple-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-purple-900">Margem de Lucro</p>
                    @php
                        $margem = ($data['receita_total'] ?? 0) > 0 ? (($lucro / ($data['receita_total'] ?? 1)) * 100) : 0;
                    @endphp
                    <p class="text-2xl font-bold {{ $margem >= 0 ? 'text-purple-600' : 'text-red-600' }}">
                        {{ number_format($margem, 1, ',', '.') }}%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Receitas -->
    @if(isset($data['receitas']) && count($data['receitas']) > 0)
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 bg-green-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-plus-circle mr-2 text-green-600"></i>
                    Receitas
                </h3>
                <p class="text-sm text-gray-600 mt-1">Total de {{ count($data['receitas']) }} registros de receita</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Descrição
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Categoria
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Valor
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['receitas'] as $index => $receita)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $receita['data'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $receita['descricao'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $receita['categoria'] ?? 'Geral' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                    R$ {{ number_format($receita['valor'], 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($receita['status'] === 'Recebido') bg-green-100 text-green-800
                                        @elseif($receita['status'] === 'Pendente') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $receita['status'] ?? 'Pendente' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Tabela de Despesas -->
    @if(isset($data['despesas']) && count($data['despesas']) > 0)
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-red-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-minus-circle mr-2 text-red-600"></i>
                    Despesas
                </h3>
                <p class="text-sm text-gray-600 mt-1">Total de {{ count($data['despesas']) }} registros de despesa</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Descrição
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Categoria
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Valor
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($data['despesas'] as $index => $despesa)
                            <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-gray-100 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $despesa['data'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $despesa['descricao'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $despesa['categoria'] ?? 'Geral' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                    R$ {{ number_format($despesa['valor'], 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($despesa['status'] === 'Pago') bg-green-100 text-green-800
                                        @elseif($despesa['status'] === 'Pendente') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $despesa['status'] ?? 'Pendente' }}
                                    </span>
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
                <i class="fas fa-chart-pie text-2xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Dados financeiros não disponíveis</h3>
            <p class="text-gray-600">Não foram encontrados registros financeiros para o período selecionado.</p>
        </div>
    @endif
</div>