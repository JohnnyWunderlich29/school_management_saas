<!-- Seção: Avaliação (alinhada ao modelo) -->
<div class="space-y-6">
    <!-- Métodos de Avaliação -->
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-green-900 mb-3 flex items-center">
            <i class="fas fa-clipboard-check mr-2"></i>
            Métodos de Avaliação
        </h3>

        @php
            $metodos = null;
            if (isset($planejamento->avaliacao_metodos)) {
                $metodos = is_string($planejamento->avaliacao_metodos)
                    ? json_decode($planejamento->avaliacao_metodos, true)
                    : $planejamento->avaliacao_metodos;
            }
            $metodos = $metodos ?? [];
        @endphp

        @if(count($metodos) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($metodos as $metodo)
                @if(is_array($metodo))
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-700">
                        <i class="fas fa-check-circle mr-1"></i>
                        {{ $metodo['nome'] ?? ($metodo['descricao'] ?? 'Método') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-700">
                        <i class="fas fa-check-circle mr-1"></i>
                        {{ $metodo }}
                    </span>
                @endif
            @endforeach
        </div>
        @else
        <div class="text-sm text-gray-500 italic">Nenhum método de avaliação definido</div>
        @endif
    </div>

    <!-- Resumo da Avaliação -->
    <div class="bg-gradient-to-r from-gray-50 to-green-50 border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
            <i class="fas fa-chart-pie mr-2"></i>
            Resumo da Avaliação
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">
                    {{ isset($planejamento->avaliacao_metodos) ? (is_string($planejamento->avaliacao_metodos) ? count(json_decode($planejamento->avaliacao_metodos, true) ?? []) : count($planejamento->avaliacao_metodos ?? [])) : 0 }}
                </div>
                <div class="text-xs text-gray-600">Métodos</div>
            </div>
        </div>

        <!-- Indicador de Completude -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="text-xs text-gray-600 mb-2">Completude da Avaliação:</div>

            @php
                $completude = 0;
                $total = 1;
                if (isset($planejamento->avaliacao_metodos)) {
                    $lista = is_string($planejamento->avaliacao_metodos) ? (json_decode($planejamento->avaliacao_metodos, true) ?? []) : ($planejamento->avaliacao_metodos ?? []);
                    if (is_array($lista) && count($lista) > 0) {
                        $completude++;
                    }
                }
                $percentual = ($completude / $total) * 100;
            @endphp

            <div class="flex items-center">
                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                    <div class="bg-gradient-to-r from-green-400 to-blue-500 h-2 rounded-full transition-all duration-300" 
                         style="width: {{ $percentual }}%"></div>
                </div>
                <span class="text-sm font-medium text-gray-700">{{ number_format($percentual, 0) }}%</span>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                {{ $completude }} de {{ $total }} seção preenchida
            </div>
        </div>
    </div>
</div>