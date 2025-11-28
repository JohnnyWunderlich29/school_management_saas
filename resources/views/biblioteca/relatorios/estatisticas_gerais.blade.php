<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600">Total de Empréstimos</div>
        <div class="text-2xl font-semibold text-gray-900">{{ $estatisticas['total_emprestimos'] ?? 0 }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600">Reservas Ativas</div>
        <div class="text-2xl font-semibold text-gray-900">{{ $estatisticas['total_reservas'] ?? 0 }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600">Itens no Acervo</div>
        <div class="text-2xl font-semibold text-gray-900">{{ $estatisticas['total_itens'] ?? 0 }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600">Atrasos</div>
        <div class="text-2xl font-semibold text-gray-900">{{ $estatisticas['emprestimos_atrasados'] ?? 0 }}</div>
    </div>
</div>

<div class="mt-3">
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <h5 class="text-base font-semibold text-gray-900 mb-2">Empréstimos por Tipo de Item</h5>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse(($estatisticasPorTipo ?? []) as $tipo => $qtd)
                        <tr class="odd:bg-gray-50">
                            <td class="px-4 py-2 text-sm text-gray-700">{{ ucfirst($tipo) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $qtd }}</td>
                        </tr>
                    @empty
                        <tr class="odd:bg-gray-50">
                            <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-500">Sem dados para o período</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>