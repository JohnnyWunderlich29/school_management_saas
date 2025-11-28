<!-- Seção: Conteúdo Pedagógico -->
<div class="space-y-6">
    <!-- Campos de Experiência -->
    <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-purple-900 mb-3 flex items-center">
            <i class="fas fa-puzzle-piece mr-2"></i>
            Campos de Experiência
        </h3>
        
        @if($planejamento->campos_experiencia)
            @php
                $campos = is_string($planejamento->campos_experiencia) 
                    ? json_decode($planejamento->campos_experiencia, true) 
                    : $planejamento->campos_experiencia;
                $campos = $campos ?? [];

                // Mapear IDs para nomes quando necessário
                $labelsCampos = [];
                if (is_array($campos) && count($campos) > 0) {
                    $ids = array_values(array_filter($campos, function($c){ return is_numeric($c); }));
                    $nomesPorId = [];
                    if (count($ids) > 0) {
                        $nomesPorId = \App\Models\CampoExperiencia::whereIn('id', $ids)->pluck('nome', 'id')->toArray();
                    }
                    foreach ($campos as $c) {
                        if (is_numeric($c)) {
                            $labelsCampos[] = $nomesPorId[$c] ?? (string)$c;
                        } else {
                            $labelsCampos[] = is_array($c) ? ($c['nome'] ?? json_encode($c)) : (string)$c;
                        }
                    }
                }
            @endphp
            
            @if(isset($labelsCampos) && count($labelsCampos) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($labelsCampos as $label)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                    <i class="fas fa-circle mr-2 text-xs"></i>
                    {{ $label }}
                </span>
                @endforeach
            </div>
            @else
            <div class="text-sm text-gray-500 italic">Nenhum campo de experiência selecionado</div>
            @endif
        @else
        <div class="text-sm text-gray-500 italic">Nenhum campo de experiência selecionado</div>
        @endif
    </div>

    <!-- Saberes e Conhecimentos -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-900 mb-3 flex items-center">
            <i class="fas fa-lightbulb mr-2"></i>
            Saberes e Conhecimentos
        </h3>
        
        @if($planejamento->saberes_conhecimentos)
        <div class="bg-white p-4 rounded border border-blue-200">
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e($planejamento->saberes_conhecimentos)) !!}
            </div>
        </div>
        @else
        <div class="text-sm text-gray-500 italic">Saberes e conhecimentos não definidos</div>
        @endif
        
        @if($planejamento->saberes_conhecimentos)
        <div class="mt-3 text-xs text-blue-600">
            <i class="fas fa-info-circle mr-1"></i>
            {{ str_word_count($planejamento->saberes_conhecimentos) }} palavras • 
            {{ strlen($planejamento->saberes_conhecimentos) }} caracteres
        </div>
        @endif
    </div>

    <!-- Objetivos de Aprendizagem -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-green-900 mb-3 flex items-center">
            <i class="fas fa-bullseye mr-2"></i>
            Objetivos de Aprendizagem
        </h3>
        
        @if($planejamento->objetivos_aprendizagem)
            @php
                $objetivos = is_string($planejamento->objetivos_aprendizagem) 
                    ? json_decode($planejamento->objetivos_aprendizagem, true) 
                    : $planejamento->objetivos_aprendizagem;
                $objetivos = $objetivos ?? [];

                // Detectar IDs e buscar detalhes dos objetivos
                $idsObjetivos = array_values(array_filter($objetivos, function($o){ return is_numeric($o); }));
                $objetivosDetalhes = [];
                if (count($idsObjetivos) > 0) {
                    $objetivosDetalhes = \App\Models\ObjetivoAprendizagem::with('campoExperiencia')
                        ->whereIn('id', $idsObjetivos)
                        ->orderBy('codigo')
                        ->get();
                }
            @endphp
            
            @if(isset($objetivosDetalhes) && count($objetivosDetalhes) > 0)
            <div class="space-y-3">
                @foreach($objetivosDetalhes as $index => $obj)
                <div class="bg-white p-4 rounded border border-green-200">
                    <div class="flex items-start">
                        <div class="w-6 h-6 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium mr-3 mt-0.5">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-green-900 mb-1">{{ $obj->codigo ?? 'Código não informado' }}</div>
                            <div class="text-sm text-gray-700">{{ $obj->descricao ?? 'Descrição não informada' }}</div>
                            @if($obj->campoExperiencia)
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-purple-100 text-purple-700">
                                    <i class="fas fa-tag mr-1"></i>
                                    {{ $obj->campoExperiencia->nome }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @elseif(count($objetivos) > 0)
            <div class="space-y-3">
                @foreach($objetivos as $index => $objetivo)
                <div class="bg-white p-4 rounded border border-green-200">
                    <div class="flex items-start">
                        <div class="w-6 h-6 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-xs font-medium mr-3 mt-0.5">
                            {{ $index + 1 }}
                        </div>
                        
                        <div class="flex-1">
                            @if(is_array($objetivo))
                            <div class="text-sm font-medium text-green-900 mb-1">{{ $objetivo['codigo'] ?? 'Código não informado' }}</div>
                            <div class="text-sm text-gray-700">{{ $objetivo['descricao'] ?? 'Descrição não informada' }}</div>
                            
                            @if(isset($objetivo['faixa_etaria']))
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-700">
                                    <i class="fas fa-child mr-1"></i>
                                    {{ $objetivo['faixa_etaria'] }}
                                </span>
                            </div>
                            @endif
                            
                            @if(isset($objetivo['campo']))
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-purple-100 text-purple-700">
                                    <i class="fas fa-tag mr-1"></i>
                                    {{ $objetivo['campo'] }}
                                </span>
                            </div>
                            @endif
                            @else
                            <div class="text-sm text-gray-700">{{ $objetivo }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-sm text-gray-500 italic">Nenhum objetivo de aprendizagem definido</div>
            @endif
        @else
        <div class="text-sm text-gray-500 italic">Nenhum objetivo de aprendizagem definido</div>
        @endif
    </div>

    <!-- Resumo do Conteúdo -->
    <div class="bg-gradient-to-r from-gray-50 to-blue-50 border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
            <i class="fas fa-chart-pie mr-2"></i>
            Resumo do Conteúdo Pedagógico
        </h3>
        
        @php
            // Contagem segura: aceita string JSON ou array
            $camposExpResumo = 0;
            if ($planejamento->campos_experiencia) {
                $camposTmp = is_string($planejamento->campos_experiencia)
                    ? (json_decode($planejamento->campos_experiencia, true) ?? [])
                    : ($planejamento->campos_experiencia ?? []);
                $camposExpResumo = is_array($camposTmp) ? count($camposTmp) : 0;
            }

            $objetivosResumo = 0;
            if ($planejamento->objetivos_aprendizagem) {
                $objetivosTmp = is_string($planejamento->objetivos_aprendizagem)
                    ? (json_decode($planejamento->objetivos_aprendizagem, true) ?? [])
                    : ($planejamento->objetivos_aprendizagem ?? []);
                $objetivosResumo = is_array($objetivosTmp) ? count($objetivosTmp) : 0;
            }
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">
                    {{ $camposExpResumo }}
                </div>
                <div class="text-xs text-gray-600">Campos de Experiência</div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">
                    {{ $objetivosResumo }}
                </div>
                <div class="text-xs text-gray-600">Objetivos de Aprendizagem</div>
            </div>
        </div>
        
        <!-- Indicadores de Completude -->
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="text-xs text-gray-600 mb-2">Completude do Conteúdo:</div>
            
            @php
                $completude = 0;
                $total = 3;
                
                // Campos de Experiência pode ser string JSON ou array
                $camposExp = is_string($planejamento->campos_experiencia)
                    ? (json_decode($planejamento->campos_experiencia, true) ?? [])
                    : ($planejamento->campos_experiencia ?? []);
                if (is_array($camposExp) && count($camposExp) > 0) $completude++;
                if ($planejamento->saberes_conhecimentos) $completude++;
                // Objetivos de Aprendizagem pode ser string JSON ou array
                $objetivosComp = is_string($planejamento->objetivos_aprendizagem)
                    ? (json_decode($planejamento->objetivos_aprendizagem, true) ?? [])
                    : ($planejamento->objetivos_aprendizagem ?? []);
                if (is_array($objetivosComp) && count($objetivosComp) > 0) $completude++;
                
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
