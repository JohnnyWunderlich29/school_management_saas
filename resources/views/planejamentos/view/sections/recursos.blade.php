<!-- Seção: Recursos (campos existentes) -->
<div class="space-y-6">
    <!-- Recursos Necessários (Planejamento) -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-900 mb-3 flex items-center">
            <i class="fas fa-tools mr-2"></i>
            Recursos Necessários
        </h3>

        @php
            $recursos_necessarios = is_string($planejamento->recursos_necessarios ?? null)
                ? json_decode($planejamento->recursos_necessarios, true)
                : ($planejamento->recursos_necessarios ?? []);
            $recursos_necessarios = $recursos_necessarios ?? [];
        @endphp

        @if(is_array($recursos_necessarios) && count($recursos_necessarios) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($recursos_necessarios as $recurso)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <i class="fas fa-tag mr-1 text-xs"></i>
                {{ is_array($recurso) ? ($recurso['nome'] ?? $recurso) : $recurso }}
            </span>
            @endforeach
        </div>
        @else
        <div class="text-sm text-gray-500 italic">Nenhum recurso necessário definido</div>
        @endif
    </div>

    <!-- Recursos (Detalhado) -->
    @php $recursosDetalhados = optional($planejamento->planejamentoDetalhado)->recursos; @endphp
    @if(!empty($recursosDetalhados))
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
            <i class="fas fa-list mr-2"></i>
            Recursos (Detalhado)
        </h3>
        <div class="prose prose-sm max-w-none text-gray-700">
            {!! nl2br(e($recursosDetalhados)) !!}
        </div>
    </div>
    @endif

    <!-- Resumo dos Recursos -->
    <div class="bg-gradient-to-r from-gray-50 to-blue-50 border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
            <i class="fas fa-chart-bar mr-2"></i>
            Resumo dos Recursos
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">
                    {{ is_array($recursos_necessarios) ? count($recursos_necessarios) : 0 }}
                </div>
                <div class="text-xs text-gray-600">Itens necessários</div>
            </div>

            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">
                    {{ !empty($recursosDetalhados) ? str_word_count($recursosDetalhados) : 0 }}
                </div>
                <div class="text-xs text-gray-600">Palavras no detalhamento</div>
            </div>
        </div>

        <!-- Indicador de Completude -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="text-xs text-gray-600 mb-2">Completude dos Recursos:</div>

            @php
                $completude = 0;
                $total = 2;
                if (is_array($recursos_necessarios) && count($recursos_necessarios) > 0) $completude++;
                if (!empty($recursosDetalhados)) $completude++;
                $percentual = ($completude / $total) * 100;
            @endphp

            <div class="flex items-center">
                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                    <div class="bg-gradient-to-r from-blue-400 to-green-500 h-2 rounded-full transition-all duration-300" 
                         style="width: {{ $percentual }}%"></div>
                </div>

                <span class="text-sm font-medium text-gray-700">{{ number_format($percentual, 0) }}%</span>
            </div>

            <div class="mt-2 text-xs text-gray-500">
                {{ $completude }} de {{ $total }} seções preenchidas
            </div>
        </div>
    </div>
</div>