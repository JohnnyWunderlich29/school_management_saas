<x-table class="hidden md:block" :headers="['Usuário', 'Item', 'Reserva', 'Fila', 'Status', 'Expiração']" :actions="true">
    @forelse($reservas as $index => $reserva)
        <x-table-row :striped="true" :index="$index">
            <x-table-cell>
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">{{ $reserva->usuario->name }}</div>
                        <div class="text-gray-500 text-xs">{{ $reserva->usuario->email }}</div>
                    </div>
                </div>
            </x-table-cell>
            <x-table-cell>
                <div>
                    <div class="font-medium text-gray-900">{{ $reserva->item->titulo }}</div>
                    <div class="text-gray-500 text-xs">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ ucfirst($reserva->item->tipo) }}</span>
                        @if($reserva->item->autores)
                            <span class="ml-1">- {{ $reserva->item->autores }}</span>
                        @endif
                    </div>
                </div>
            </x-table-cell>
            <x-table-cell>{{ $reserva->data_reserva->format('d/m/Y H:i') }}</x-table-cell>
            <x-table-cell>
                @if($reserva->status === 'ativa')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ $reserva->posicao_fila ?? 'N/A' }}º na fila</span>
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </x-table-cell>
            <x-table-cell>
                @switch($reserva->status)
                    @case('processada')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Processada</span>
                        @break
                    @case('cancelada')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">Cancelada</span>
                        @break
                    @case('expirada')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Expirada</span>
                        @break
                    @default
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Ativa</span>
                @endswitch
            </x-table-cell>
            <x-table-cell>
                @if($reserva->status === 'ativa' && $reserva->expires_at)
                    @php($expirada = $reserva->expires_at->isPast())
                    <span class="{{ $expirada ? 'text-red-600 font-semibold' : '' }}">{{ $reserva->expires_at->format('d/m/Y') }}</span>
                    @unless($expirada)
                        <div class="text-xs text-gray-500 mt-1">{{ $reserva->expires_at->diffForHumans() }}</div>
                    @endunless
                @else
                    <span class="text-gray-400">-</span>
                @endif
            </x-table-cell>
            <x-table-cell align="right">
                <div class="flex justify-end space-x-2">
                    <x-button color="primary" size="sm" title="Ver detalhes" onclick="verDetalhes({{ $reserva->id }})">
                        <i class="fas fa-eye"></i>
                    </x-button>

                    @if($reserva->status === 'ativa')
                        <x-button color="success" size="sm" title="Processar" onclick="openProcessarModal({{ $reserva->id }})">
                            <i class="fas fa-check"></i>
                        </x-button>
                        
                        <x-button color="danger" size="sm" title="Cancelar" onclick="openCancelarModal({{ $reserva->id }})">
                            <i class="fas fa-times"></i>
                        </x-button>
                    @endif
                </div>
            </x-table-cell>
        </x-table-row>
    @empty
        <tr>
            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                Nenhuma reserva encontrada.
            </td>
        </tr>
    @endforelse
</x-table>

<!-- Layout mobile com cards -->
<div class="md:hidden space-y-4">
    @forelse($reservas as $reserva)
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="flex items-center mb-4">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                    <i class="fas fa-user text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 text-base">{{ $reserva->usuario->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $reserva->usuario->email }}</p>
                </div>
                <div class="ml-2">
                    @switch($reserva->status)
                        @case('processada')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Processada</span>
                            @break
                        @case('cancelada')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800">Cancelada</span>
                            @break
                        @case('expirada')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Expirada</span>
                            @break
                        @default
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Ativa</span>
                    @endswitch
                </div>
            </div>

            <div class="space-y-2 mb-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Item:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $reserva->item->titulo }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Reserva:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $reserva->data_reserva->format('d/m/Y H:i') }}</span>
                </div>
                @if($reserva->status === 'ativa')
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Fila:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $reserva->posicao_fila ?? 'N/A' }}º</span>
                    </div>
                    @if($reserva->expires_at)
                        @php($expirada = $reserva->expires_at->isPast())
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Expiração:</span>
                            <span class="text-sm font-medium {{ $expirada ? 'text-red-600' : 'text-gray-900' }}">{{ $reserva->expires_at->format('d/m/Y') }}</span>
                        </div>
                    @endif
                @endif
            </div>

            <div class="flex space-x-2">
                <x-button color="primary" class="flex-1" onclick="verDetalhes({{ $reserva->id }})">
                    <i class="fas fa-eye mr-2"></i>
                    Ver Detalhes
                </x-button>

                @if($reserva->status === 'ativa')
                    <x-button color="success" class="flex-1" onclick="openProcessarModal({{ $reserva->id }})">
                        <i class="fas fa-check mr-2"></i>
                        Processar
                    </x-button>
                    
                    <x-button color="danger" class="flex-1" onclick="openCancelarModal({{ $reserva->id }})">
                        <i class="fas fa-times mr-2"></i>
                        Cancelar
                    </x-button>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-8">
            <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-bookmark text-2xl text-gray-400"></i>
            </div>
            <p class="text-gray-500">Nenhuma reserva encontrada.</p>
        </div>
    @endforelse
</div>

<!-- Paginação -->
<div class="mt-6">
    {{ $reservas->links('components.pagination') }}
</div>