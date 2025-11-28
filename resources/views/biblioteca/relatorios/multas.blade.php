@php($multas = $multas ?? collect())
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Multa</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($multas as $multa)
                <tr class="odd:bg-gray-50">
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $multa->id }}</td>
                    <td class="px-4 py-2">
                        <div class="text-sm font-medium text-gray-900">{{ $multa->usuario->name ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $multa->usuario->email ?? '' }}</div>
                    </td>
                    <td class="px-4 py-2">
                        <div class="text-sm font-medium text-gray-900">{{ $multa->item->titulo ?? '—' }}</div>
                        @if($multa->item && $multa->item->autores)
                            <div class="text-xs text-gray-500">{{ $multa->item->autores }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ optional($multa->created_at)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">R$ {{ number_format($multa->valor ?? 0, 2, ',', '.') }}</span>
                    </td>
                    <td class="px-4 py-2">
                        @php($status = $multa->status ?? ($multa->paga ? 'paga' : 'pendente'))
                        <x-status-chip :status="$status" />
                    </td>
                </tr>
            @empty
                <tr class="odd:bg-gray-50">
                    <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">Nenhuma multa encontrada para o período selecionado</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($multas, 'links'))
    <div class="mt-3 flex justify-center">
        {{ $multas->links() }}
    </div>
@endif

<div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600">Multas Totais</div>
        <div class="text-2xl font-semibold text-gray-900">R$ {{ number_format(($totais['valor_total'] ?? 0), 2, ',', '.') }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600">Pendentes</div>
        <div class="text-2xl font-semibold text-gray-900">{{ $totais['pendentes'] ?? 0 }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600">Pagas</div>
        <div class="text-2xl font-semibold text-gray-900">{{ $totais['pagas'] ?? 0 }}</div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="text-sm text-gray-600">Canceladas</div>
        <div class="text-2xl font-semibold text-gray-900">{{ $totais['canceladas'] ?? 0 }}</div>
    </div>
</div>