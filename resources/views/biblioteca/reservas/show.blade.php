<div class="space-y-6">
    <x-card title="Informações da Reserva">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">ID</span>
                        <span class="text-sm font-medium text-gray-900">{{ $reserva->id }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Status</span>
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
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Data da Reserva</span>
                        <span class="text-sm font-medium text-gray-900">{{ $reserva->data_reserva->format('d/m/Y H:i:s') }}</span>
                    </div>
                    @if($reserva->status === 'ativa')
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Posição na Fila</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ $reserva->posicao_fila ?? 'N/A' }}º</span>
                        </div>
                    @endif
                    @if($reserva->expires_at)
                        @php($expirada = $reserva->expires_at->isPast())
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Expiração</span>
                            <div class="text-right">
                                <div class="text-sm font-medium {{ $expirada ? 'text-red-600' : 'text-gray-900' }}">{{ $reserva->expires_at->format('d/m/Y H:i') }}</div>
                                @unless($expirada)
                                    <div class="text-xs text-gray-500">{{ $reserva->expires_at->diffForHumans() }}</div>
                                @endunless
                            </div>
                        </div>
                    @endif
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Prioridade</span>
                        @switch($reserva->prioridade)
                            @case('alta')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Alta</span>
                                @break
                            @case('urgente')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Urgente</span>
                                @break
                            @default
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Normal</span>
                        @endswitch
                    </div>
                </div>
            </div>
            <div>
                <x-user-summary :user="$reserva->usuario" />
            </div>
        </div>
    </x-card>

    <x-card title="Item Reservado">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            <div class="md:col-span-2 flex justify-center md:justify-start">
                @if($reserva->item->capa_url)
                    <img src="{{ $reserva->item->capa_url }}" alt="Capa" class="rounded shadow w-20 h-28 object-cover">
                @else
                    <div class="w-20 h-28 bg-gray-100 rounded flex items-center justify-center">
                        <i class="fas fa-book text-gray-400 text-2xl"></i>
                    </div>
                @endif
            </div>
            <div class="md:col-span-10">
                <div class="flex items-center justify-between">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $reserva->item->titulo }}</h4>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ ucfirst($reserva->item->tipo) }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div class="space-y-2">
                        @if($reserva->item->autores)
                            <div class="text-sm"><span class="text-gray-600">Autores:</span> <span class="font-medium text-gray-900">{{ $reserva->item->autores }}</span></div>
                        @endif
                        @if($reserva->item->editora)
                            <div class="text-sm"><span class="text-gray-600">Editora:</span> <span class="font-medium text-gray-900">{{ $reserva->item->editora }}</span></div>
                        @endif
                        @if($reserva->item->isbn)
                            <div class="text-sm"><span class="text-gray-600">ISBN:</span> <span class="font-medium text-gray-900">{{ $reserva->item->isbn }}</span></div>
                        @endif
                    </div>
                    <div class="space-y-2">
                        <div class="text-sm"><span class="text-gray-600">Localização:</span> <span class="font-medium text-gray-900">{{ $reserva->item->localizacao ?? 'Não informada' }}</span></div>
                        <div class="text-sm flex items-center gap-2">
                            <span class="text-gray-600">Disponibilidade:</span>
                            @if(($reserva->item->quantidade_disponivel ?? 0) > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $reserva->item->quantidade_disponivel }} disponível(is)</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Indisponível</span>
                            @endif
                        </div>
                        @php($emprestimosAtivos = $reserva->item->emprestimos()->where('status', 'ativo')->count())
                        @if($emprestimosAtivos > 0)
                            <div class="text-sm"><span class="text-gray-600">Empréstimos Ativos:</span> 
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ $emprestimosAtivos }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    <x-card title="Histórico">
        @php
            $timelineItems = [
                ['title' => 'Reserva Criada', 'date' => $reserva->data_reserva, 'markerColor' => 'blue'],
            ];
            if ($reserva->data_processamento) {
                $timelineItems[] = ['title' => 'Reserva Processada', 'date' => $reserva->data_processamento, 'markerColor' => 'green'];
            }
            if ($reserva->data_cancelamento) {
                $timelineItems[] = ['title' => 'Reserva Cancelada', 'date' => $reserva->data_cancelamento, 'markerColor' => 'gray', 'description' => $reserva->motivo_cancelamento ? ('Motivo: ' . $reserva->motivo_cancelamento) : null];
            }
        @endphp
        <x-timeline :items="$timelineItems" />
    </x-card>
</div>