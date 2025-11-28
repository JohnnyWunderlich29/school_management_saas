@php($itens = $itens ?? collect())
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Autor</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empréstimos</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservas</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($itens as $index => $item)
                <tr class="odd:bg-gray-50">
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $index + 1 }}</td>
                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $item->titulo }}</td>
                    <td class="px-4 py-2"><div class="text-xs text-gray-500">{{ $item->autores ?? '—' }}</div></td>
                    <td class="px-4 py-2"><span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">{{ ucfirst($item->tipo ?? '—') }}</span></td>
                    <td class="px-4 py-2"><span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">{{ $item->emprestimos_count ?? 0 }}</span></td>
                    <td class="px-4 py-2"><span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-800">{{ $item->reservas_count ?? 0 }}</span></td>
                </tr>
            @empty
                <tr class="odd:bg-gray-50">
                    <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">Nenhum dado disponível</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($itens, 'links'))
    <div class="mt-3 flex justify-center">
        {{ $itens->links() }}
    </div>
@endif