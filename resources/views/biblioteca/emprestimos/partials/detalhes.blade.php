<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Usuário</h3>
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div class="font-medium text-gray-900">{{ $emprestimo->usuario->name }}</div>
                    @if($emprestimo->usuario->email)
                        <div class="text-gray-500 text-xs">{{ $emprestimo->usuario->email }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Item</h3>
            <div>
                <div class="font-medium text-gray-900">{{ $emprestimo->item->titulo }}</div>
                <div class="text-gray-500 text-xs">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ ucfirst($emprestimo->item->tipo) }}</span>
                    @if($emprestimo->item->autores)
                        <span class="ml-1">- {{ $emprestimo->item->autores }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Data Empréstimo</h3>
            <div class="text-sm text-gray-900">{{ $emprestimo->data_emprestimo?->format('d/m/Y H:i') }}</div>
        </div>
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Data Prevista</h3>
            @php($vencido = $emprestimo->status === 'ativo' && $emprestimo->data_prevista?->isPast())
            <div class="text-sm {{ $vencido ? 'text-red-600 font-semibold' : 'text-gray-900' }}">{{ $emprestimo->data_prevista?->format('d/m/Y') }}</div>
            @if($vencido)
                <div class="text-xs text-red-600 mt-1 flex items-center"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $emprestimo->data_prevista->diffForHumans() }}</div>
            @endif
        </div>
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Data Devolução</h3>
            @if($emprestimo->data_devolucao)
                <div class="text-sm text-gray-900">{{ $emprestimo->data_devolucao->format('d/m/Y H:i') }}</div>
            @else
                <div class="text-sm text-gray-500">-</div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Status</h3>
            @switch($emprestimo->status)
                @case('devolvido')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Devolvido</span>
                    @break
                @case('ativo')
                    @if($emprestimo->data_prevista?->isPast())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Vencido</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Ativo</span>
                    @endif
                    @break
                @default
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($emprestimo->status) }}</span>
            @endswitch
        </div>
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Multa</h3>
            @if(($emprestimo->multa_calculada ?? 0) > 0)
                <div class="text-sm text-red-600 font-semibold">R$ {{ number_format($emprestimo->multa_calculada, 2, ',', '.') }}</div>
            @else
                <div class="text-sm text-gray-500">-</div>
            @endif
        </div>
        <div>
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Observações</h3>
            @if(!empty($emprestimo->observacoes ?? ''))
                <div class="text-sm text-gray-900">{{ $emprestimo->observacoes }}</div>
            @else
                <div class="text-sm text-gray-500">-</div>
            @endif
        </div>
    </div>

    <div class="flex justify-end gap-2 pt-2">
        <x-button type="button" color="secondary" onclick="closeModal('detalhes-emprestimo-modal')">Fechar</x-button>
        @if($emprestimo->status === 'ativo')
            <x-button type="button" color="success" onclick="openDevolverEmprestimoWithUrl('{{ route('biblioteca.emprestimos.devolver', $emprestimo) }}')">
                <i class="fas fa-undo mr-1"></i>
                Devolver
            </x-button>
            @if($emprestimo->data_prevista && $emprestimo->data_prevista >= now())
                <x-button type="button" color="warning" onclick="openRenovarEmprestimoWithUrl('{{ route('biblioteca.emprestimos.renovar', $emprestimo) }}')">
                    <i class="fas fa-sync mr-1"></i>
                    Renovar
                </x-button>
            @endif
        @endif
    </div>
</div>