<!-- Seção: Metodologia -->
<div class="space-y-6">
    <!-- Descrição da Metodologia -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-900 mb-3 flex items-center">
            <i class="fas fa-chalkboard-teacher mr-2"></i>
            Descrição da Metodologia
        </h3>
        
        @if($planejamento->metodologia)
        <div class="bg-white p-4 rounded border border-blue-200">
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e($planejamento->metodologia)) !!}
            </div>
        </div>
        
        <div class="mt-3 text-xs text-blue-600">
            <i class="fas fa-info-circle mr-1"></i>
            {{ str_word_count($planejamento->metodologia) }} palavras • 
            {{ strlen($planejamento->metodologia) }} caracteres
        </div>
        @else
        <div class="text-sm text-gray-500 italic">Metodologia não definida</div>
        @endif
    </div>

    <!-- Encaminhamentos Metodológicos (Detalhado) -->
    @php
        $encaminhamentos = optional($planejamento->planejamentoDetalhado)->encaminhamentos_metodologicos;
    @endphp
    @if(!empty($encaminhamentos))
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-900 mb-3 flex items-center">
            <i class="fas fa-route mr-2"></i>
            Encaminhamentos Metodológicos
        </h3>
        <div class="bg-white p-4 rounded border border-blue-200">
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e($encaminhamentos)) !!}
            </div>
        </div>
        <div class="mt-3 text-xs text-blue-600">
            <i class="fas fa-info-circle mr-1"></i>
            {{ str_word_count($encaminhamentos) }} palavras
        </div>
    </div>
    @endif

    

    

    

    

    

    <!-- Resumo da Metodologia -->
    <div class="bg-gradient-to-r from-gray-50 to-blue-50 border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
            <i class="fas fa-chart-bar mr-2"></i>
            Resumo da Metodologia
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">
                    {{ $planejamento->metodologia ? str_word_count($planejamento->metodologia) : 0 }}
                </div>
                <div class="text-xs text-gray-600">Palavras na Metodologia</div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">
                    {{ !empty($encaminhamentos) ? str_word_count($encaminhamentos) : 0 }}
                </div>
                <div class="text-xs text-gray-600">Palavras nos Encaminhamentos</div>
            </div>
        </div>
        
        <!-- Indicador de Completude -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="text-xs text-gray-600 mb-2">Completude da Metodologia:</div>
            
            @php
                $completude = 0;
                $total = 2;
                if ($planejamento->metodologia) $completude++;
                if (!empty($encaminhamentos)) $completude++;
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