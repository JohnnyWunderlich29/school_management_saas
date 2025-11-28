<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Reserva</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validade</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridade</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posição na Fila</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($reservas as $reserva)
            <tr class="odd:bg-gray-50">
                <td class="px-4 py-2 text-sm text-gray-700">{{ $reserva->id }}</td>
                <td class="px-4 py-2">
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $reserva->usuario->name }}</div>
                        <div class="text-xs text-gray-500">{{ $reserva->usuario->email }}</div>
                    </div>
                </td>
                <td class="px-4 py-2">
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $reserva->item->titulo }}</div>
                        <div class="text-xs text-gray-500">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-800">{{ ucfirst($reserva->item->tipo) }}</span>
                            @if($reserva->item->autores)
                                <span class="ml-1">- {{ $reserva->item->autores }}</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-2 text-sm text-gray-700">{{ $reserva->data_reserva->format('d/m/Y H:i') }}</td>
                <td class="px-4 py-2 text-sm text-gray-700">
                    @if($reserva->data_validade)
                        <span class="@if($reserva->status === 'ativa' && $reserva->data_validade->isPast()) text-red-600 font-semibold @endif">
                            {{ $reserva->data_validade->format('d/m/Y H:i') }}
                        </span>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-4 py-2">
                    @switch($reserva->status)
                        @case('ativa')
                            @if($reserva->data_validade && $reserva->data_validade->isPast())
                                <x-status-chip status="expirada" label="Expirada" />
                            @else
                                <x-status-chip status="ativa" />
                            @endif
                            @break
                        @case('processada')
                            <x-status-chip status="processada" />
                            @break
                        @case('cancelada')
                            <x-status-chip status="cancelada" />
                            @break
                        @case('expirada')
                            <x-status-chip status="expirada" />
                            @break
                        @default
                            <x-status-chip :status="$reserva->status" />
                    @endswitch
                </td>
                <td class="px-4 py-2">
                    @switch($reserva->prioridade)
                        @case('alta')
                            <x-status-chip status="alta" label="Alta" />
                            @break
                        @case('media')
                            <x-status-chip status="media" label="Média" />
                            @break
                        @case('baixa')
                            <x-status-chip status="baixa" label="Baixa" />
                            @break
                        @default
                            <x-status-chip status="info" label="Normal" />
                    @endswitch
                </td>
                <td class="px-4 py-2">
                    @if($reserva->posicao_fila)
                        <x-status-chip status="info" :label="($reserva->posicao_fila . 'º')" />
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr class="odd:bg-gray-50">
                <td colspan="8" class="text-center py-4">
                    <div class="text-gray-500">
                        <x-icon name="bookmark" class="h-12 w-12 mb-3 text-gray-400" />
                        <p>Nenhuma reserva encontrada para os filtros selecionados</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($reservas->hasPages())
    <div class="mt-3 flex justify-center">
        {{ $reservas->links() }}
    </div>
@endif

<div class="mt-3">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-center">
            <div class="text-2xl font-semibold text-gray-900">{{ $reservas->total() }}</div>
            <div class="text-sm text-gray-600">Total de Reservas</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-center">
            <div class="text-2xl font-semibold text-gray-900">{{ $reservas->where('status', 'ativa')->count() }}</div>
            <div class="text-sm text-gray-600">Reservas Ativas</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-center">
            <div class="text-2xl font-semibold text-gray-900">{{ $reservas->where('status', 'processada')->count() }}</div>
            <div class="text-sm text-gray-600">Processadas</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm text-center">
            <div class="text-2xl font-semibold text-gray-900">{{ $reservas->whereIn('status', ['expirada', 'cancelada'])->count() }}</div>
            <div class="text-sm text-gray-600">Expiradas/Canceladas</div>
        </div>
    </div>
</div>