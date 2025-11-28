<!-- Seção: Observações (alinhada ao modelo) -->
<div class="space-y-6">
    <!-- Observações -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-900 mb-3 flex items-center">
            <i class="fas fa-sticky-note mr-2"></i>
            Observações
        </h3>
        @php
            $observacoes = $planejamento->observacoes ?? null;
        @endphp
        @if(!empty($observacoes))
        <div class="bg-white p-4 rounded border border-blue-200">
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e(is_string($observacoes) ? $observacoes : json_encode($observacoes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) !!}
            </div>
        </div>
        @else
        <div class="text-sm text-gray-500 italic">Nenhuma observação registrada.</div>
        @endif
    </div>

    <!-- Observações Finais -->
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-green-900 mb-3 flex items-center">
            <i class="fas fa-clipboard-list mr-2"></i>
            Observações Finais
        </h3>
        @php
            $observacoesFinais = $planejamento->observacoes_finais ?? null;
        @endphp
        @if(!empty($observacoesFinais))
        <div class="bg-white p-4 rounded border border-green-200">
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e(is_string($observacoesFinais) ? $observacoesFinais : json_encode($observacoesFinais, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) !!}
            </div>
        </div>
        @else
        <div class="text-sm text-gray-500 italic">Nenhuma observação final registrada.</div>
        @endif
    </div>

    <!-- Resumo das Observações -->
    <div class="bg-gradient-to-r from-slate-50 to-gray-50 border border-slate-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
            <i class="fas fa-chart-pie mr-2"></i>
            Resumo das Observações
        </h3>
        @php
            $itensObservacoes = [];
            if (!empty($observacoes)) { $itensObservacoes[] = 'Observações'; }
            if (!empty($observacoesFinais)) { $itensObservacoes[] = 'Observações Finais'; }
        @endphp
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ !empty($observacoes) ? 1 : 0 }}</div>
                <div class="text-xs text-gray-600">Observações</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ !empty($observacoesFinais) ? 1 : 0 }}</div>
                <div class="text-xs text-gray-600">Finais</div>
            </div>
        </div>
        @if (!empty($itensObservacoes))
        <div class="mt-3 text-xs text-gray-600">Seções preenchidas: {{ implode(', ', $itensObservacoes) }}</div>
        @else
        <div class="mt-3 text-xs text-gray-500 italic">Nenhuma observação registrada nas seções.</div>
        @endif
    </div>

    <!-- Completude das Observações -->
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-indigo-900 mb-3 flex items-center">
            <i class="fas fa-percent mr-2"></i>
            Completude das Observações
        </h3>
        @php
            $totalCamposObservacoes = 2;
            $preenchidos = (!empty($observacoes) ? 1 : 0) + (!empty($observacoesFinais) ? 1 : 0);
            $completudeObservacoes = $totalCamposObservacoes > 0 ? round(($preenchidos / $totalCamposObservacoes) * 100) : 0;
        @endphp
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-700">{{ $completudeObservacoes }}%</span>
            <div class="flex-1 ml-3 bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $completudeObservacoes }}%"></div>
            </div>
        </div>
    </div>
</div>
