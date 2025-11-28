<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Empréstimo</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Devolução Prevista</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Devolução</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dias de Atraso</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($emprestimos as $emprestimo)
            <tr class="odd:bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700">{{ $emprestimo->id }}</td>
                <td class="px-4 py-2">
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $emprestimo->usuario->name }}</div>
                        <div class="text-xs text-gray-500">{{ $emprestimo->usuario->email }}</div>
                    </div>
                </td>
                <td class="px-4 py-2">
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $emprestimo->item->titulo }}</div>
                        <div class="text-xs text-gray-500">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">{{ ucfirst($emprestimo->item->tipo) }}</span>
                            @if($emprestimo->item->autores)
                                <span class="ml-1">- {{ $emprestimo->item->autores }}</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-2 text-sm text-gray-700">{{ $emprestimo->data_emprestimo->format('d/m/Y') }}</td>
                <td class="px-4 py-2 text-sm text-gray-700">
                    <span class="@if($emprestimo->status === 'ativo' && $emprestimo->data_prevista->isPast()) text-red-600 font-semibold @endif">
                        {{ $emprestimo->data_prevista->format('d/m/Y') }}
                    </span>
                </td>
                <td class="px-4 py-2 text-sm text-gray-700">
                    @if($emprestimo->data_devolucao)
                        {{ $emprestimo->data_devolucao->format('d/m/Y') }}
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-4 py-2">
                    @switch($emprestimo->status)
                        @case('ativo')
                            @if($emprestimo->data_prevista->isPast())
                                <x-status-chip status="atrasado" label="Atrasado" />
                            @else
                                <x-status-chip status="ativo" />
                            @endif
                            @break
                        @case('devolvido')
                            <x-status-chip status="devolvido" />
                            @break
                        @case('renovado')
                            <x-status-chip status="renovado" />
                            @break
                        @default
                            <x-status-chip :status="$emprestimo->status" />
                    @endswitch
                </td>
                <td class="px-4 py-2">
                    @if($emprestimo->status === 'ativo' && $emprestimo->data_prevista->isPast())
                        <x-status-chip status="atrasado" :label="($emprestimo->data_prevista->diffInDays(now()) . ' dias')" />
                    @elseif($emprestimo->status === 'devolvido' && $emprestimo->data_devolucao && $emprestimo->data_devolucao->gt($emprestimo->data_prevista))
                        <x-status-chip status="expirada" :label="($emprestimo->data_prevista->diffInDays($emprestimo->data_devolucao) . ' dias')" />
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr class="odd:bg-gray-50">
                <td colspan="8" class="text-center py-4">
                    <div class="text-gray-500">
                        <x-icon name="book-open" class="h-12 w-12 mb-3 text-gray-400" />
                        <p>Nenhum empréstimo encontrado para os filtros selecionados</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($emprestimos->hasPages())
    <div class="mt-3 flex justify-center">
        {{ $emprestimos->links() }}
    </div>
@endif

<div class="mt-3">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-center">
            <div class="text-2xl font-semibold text-gray-900">{{ $emprestimos->total() }}</div>
            <div class="text-sm text-gray-600">Total de Empréstimos</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-center">
            <div class="text-2xl font-semibold text-gray-900">{{ $emprestimos->where('status', 'ativo')->count() }}</div>
            <div class="text-sm text-gray-600">Empréstimos Ativos</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-center">
            <div class="text-2xl font-semibold text-gray-900">{{ $emprestimos->where('status', 'devolvido')->count() }}</div>
            <div class="text-sm text-gray-600">Devolvidos</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-center">
            <div class="text-2xl font-semibold text-gray-900">{{ $emprestimos->where('status', 'ativo')->filter(function($emp) { return $emp->data_prevista->isPast(); })->count() }}</div>
            <div class="text-sm text-gray-600">Atrasados</div>
        </div>
    </div>
</div>