<!-- Seção: Período e Duração -->
<div class="space-y-6">
    <!-- Resumo do Período -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
            <i class="fas fa-calendar-alt mr-3"></i>
            Resumo do Período
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $planejamento->total_aulas ?? 0 }}</div>
                <div class="text-sm text-blue-700">Total de Aulas</div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ $planejamento->carga_horaria_total ?? 0 }}h</div>
                <div class="text-sm text-indigo-700">Carga Horária Total</div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $planejamento->duracao_semanas ?? 0 }}</div>
                <div class="text-sm text-purple-700">Semanas de Duração</div>
            </div>
            
            <div class="text-center">
                <div class="text-2xl font-bold text-pink-600">{{ $planejamento->aulas_por_semana ?? 0 }}</div>
                <div class="text-sm text-pink-700">Aulas por Semana</div>
            </div>
        </div>
    </div>

    <!-- Configuração do Período -->
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-4 flex items-center">
            <i class="fas fa-cog mr-2"></i>
            Configuração do Período
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Tipo de Período -->
            <div>
                <span class="text-xs text-gray-600 uppercase tracking-wide">Tipo de Período:</span>
                <div class="mt-1">
                    @php
                        $tipoPeriodo = $planejamento->tipo_periodo ?? 'datas';
                        $tipoLabels = [
                            'dias' => 'Por Número de Dias',
                            'datas' => 'Por Datas Específicas',
                            'bimestre' => 'Por Bimestre/Trimestre'
                        ];
                    @endphp
                    
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($tipoPeriodo === 'dias') bg-blue-100 text-blue-800
                        @elseif($tipoPeriodo === 'datas') bg-green-100 text-green-800
                        @else bg-purple-100 text-purple-800
                        @endif">
                        @if($tipoPeriodo === 'dias')
                            <i class="fas fa-hashtag mr-1"></i>
                        @elseif($tipoPeriodo === 'datas')
                            <i class="fas fa-calendar mr-1"></i>
                        @else
                            <i class="fas fa-calendar-week mr-1"></i>
                        @endif
                        {{ $tipoLabels[$tipoPeriodo] ?? 'Não definido' }}
                    </span>
                </div>
            </div>

            <!-- Status do Período -->
            <div>
                <span class="text-xs text-gray-600 uppercase tracking-wide">Status do Período:</span>
                <div class="mt-1">
                    @php
                        $statusPeriodo = $planejamento->status_periodo ?? 'planejado';
                        $hoje = now();
                        $dataInicio = $planejamento->data_inicio ? \Carbon\Carbon::parse($planejamento->data_inicio) : null;
                        $dataFim = $planejamento->data_fim ? \Carbon\Carbon::parse($planejamento->data_fim) : null;
                        
                        if ($dataInicio && $dataFim) {
                            if ($hoje->lt($dataInicio)) {
                                $statusPeriodo = 'futuro';
                            } elseif ($hoje->between($dataInicio, $dataFim)) {
                                $statusPeriodo = 'em_andamento';
                            } elseif ($hoje->gt($dataFim)) {
                                $statusPeriodo = 'concluido';
                            }
                        }
                    @endphp
                    
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($statusPeriodo === 'futuro') bg-gray-100 text-gray-800
                        @elseif($statusPeriodo === 'em_andamento') bg-yellow-100 text-yellow-800
                        @elseif($statusPeriodo === 'concluido') bg-green-100 text-green-800
                        @else bg-blue-100 text-blue-800
                        @endif">
                        @if($statusPeriodo === 'futuro')
                            <i class="fas fa-clock mr-1"></i> Futuro
                        @elseif($statusPeriodo === 'em_andamento')
                            <i class="fas fa-play mr-1"></i> Em Andamento
                        @elseif($statusPeriodo === 'concluido')
                            <i class="fas fa-check mr-1"></i> Concluído
                        @else
                            <i class="fas fa-calendar-plus mr-1"></i> Planejado
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalhes do Período -->
    @if($tipoPeriodo === 'datas')
    <!-- Por Datas Específicas -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-green-900 mb-3 flex items-center">
            <i class="fas fa-calendar mr-2"></i>
            Período por Datas
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-xs text-green-700 uppercase tracking-wide">Data de Início:</span>
                <div class="text-sm font-medium text-green-900">
                    {{ $planejamento->data_inicio ? \Carbon\Carbon::parse($planejamento->data_inicio)->format('d/m/Y') : 'Não definida' }}
                </div>
                @if($planejamento->data_inicio)
                <div class="text-xs text-green-600">
                    {{ \Carbon\Carbon::parse($planejamento->data_inicio)->dayName }}, 
                    {{ \Carbon\Carbon::parse($planejamento->data_inicio)->format('d \d\e F \d\e Y') }}
                </div>
                @endif
            </div>
            
            <div>
                <span class="text-xs text-green-700 uppercase tracking-wide">Data de Término:</span>
                <div class="text-sm font-medium text-green-900">
                    {{ $planejamento->data_fim ? \Carbon\Carbon::parse($planejamento->data_fim)->format('d/m/Y') : 'Não definida' }}
                </div>
                @if($planejamento->data_fim)
                <div class="text-xs text-green-600">
                    {{ \Carbon\Carbon::parse($planejamento->data_fim)->dayName }}, 
                    {{ \Carbon\Carbon::parse($planejamento->data_fim)->format('d \d\e F \d\e Y') }}
                </div>
                @endif
            </div>
        </div>

        @if($planejamento->data_inicio && $planejamento->data_fim)
        <div class="mt-4 pt-4 border-t border-green-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-green-700">Duração Total:</span>
                    <div class="font-medium text-green-900">
                        {{ \Carbon\Carbon::parse($planejamento->data_inicio)->diffInDays(\Carbon\Carbon::parse($planejamento->data_fim)) + 1 }} dias
                    </div>
                </div>
                
                <div>
                    <span class="text-green-700">Dias Úteis:</span>
                    <div class="font-medium text-green-900">
                        {{ \Carbon\Carbon::parse($planejamento->data_inicio)->diffInWeekdays(\Carbon\Carbon::parse($planejamento->data_fim)) + 1 }} dias
                    </div>
                </div>
                
                <div>
                    <span class="text-green-700">Semanas Completas:</span>
                    <div class="font-medium text-green-900">
                        {{ floor(\Carbon\Carbon::parse($planejamento->data_inicio)->diffInWeeks(\Carbon\Carbon::parse($planejamento->data_fim))) }} semanas
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @elseif($tipoPeriodo === 'dias')
    <!-- Por Número de Dias -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-blue-900 mb-3 flex items-center">
            <i class="fas fa-hashtag mr-2"></i>
            Período por Dias
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-xs text-blue-700 uppercase tracking-wide">Número de Dias:</span>
                <div class="text-sm font-medium text-blue-900">{{ $planejamento->numero_dias ?? 0 }} dias</div>
            </div>
            
            <div>
                <span class="text-xs text-blue-700 uppercase tracking-wide">Data de Início:</span>
                <div class="text-sm font-medium text-blue-900">
                    {{ $planejamento->data_inicio ? \Carbon\Carbon::parse($planejamento->data_inicio)->format('d/m/Y') : 'Não definida' }}
                </div>
            </div>
            
            <div>
                <span class="text-xs text-blue-700 uppercase tracking-wide">Data Estimada de Término:</span>
                <div class="text-sm font-medium text-blue-900">
                    @if($planejamento->data_inicio && $planejamento->numero_dias)
                        {{ \Carbon\Carbon::parse($planejamento->data_inicio)->addDays($planejamento->numero_dias - 1)->format('d/m/Y') }}
                    @else
                        Não calculada
                    @endif
                </div>
            </div>
        </div>
    </div>

    @elseif($tipoPeriodo === 'bimestre')
    <!-- Por Bimestre/Trimestre -->
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-purple-900 mb-3 flex items-center">
            <i class="fas fa-calendar-week mr-2"></i>
            Período por Bimestre/Trimestre
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-xs text-purple-700 uppercase tracking-wide">Período Letivo:</span>
                <div class="text-sm font-medium text-purple-900">{{ $planejamento->periodo_letivo ?? 'Não definido' }}</div>
            </div>
            
            <div>
                <span class="text-xs text-purple-700 uppercase tracking-wide">Ano Letivo:</span>
                <div class="text-sm font-medium text-purple-900">{{ $planejamento->ano_letivo ?? date('Y') }}</div>
            </div>
        </div>

        @if($planejamento->periodo_letivo)
        <div class="mt-4 pt-4 border-t border-purple-200">
            <span class="text-xs text-purple-700 uppercase tracking-wide">Datas do Período:</span>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-purple-700">Início:</span>
                    <div class="font-medium text-purple-900">
                        {{ $planejamento->data_inicio ? \Carbon\Carbon::parse($planejamento->data_inicio)->format('d/m/Y') : 'Não definida' }}
                    </div>
                </div>
                
                <div>
                    <span class="text-purple-700">Término:</span>
                    <div class="font-medium text-purple-900">
                        {{ $planejamento->data_fim ? \Carbon\Carbon::parse($planejamento->data_fim)->format('d/m/Y') : 'Não definida' }}
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif



    <!-- Progresso do Período -->
    @if($planejamento->data_inicio && $planejamento->data_fim)
    <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
            <i class="fas fa-chart-line mr-2"></i>
            Progresso do Período
        </h4>
        
        @php
            $dataInicio = \Carbon\Carbon::parse($planejamento->data_inicio);
            $dataFim = \Carbon\Carbon::parse($planejamento->data_fim);
            $hoje = now();
            
            $totalDias = $dataInicio->diffInDays($dataFim) + 1;
            $diasDecorridos = $hoje->lt($dataInicio) ? 0 : ($hoje->gt($dataFim) ? $totalDias : $dataInicio->diffInDays($hoje) + 1);
            $progresso = $totalDias > 0 ? ($diasDecorridos / $totalDias) * 100 : 0;
        @endphp
        
        <div class="mb-4">
            <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>Progresso do período</span>
                <span>{{ number_format($progresso, 1) }}%</span>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-green-400 to-blue-500 h-2 rounded-full transition-all duration-300" 
                     style="width: {{ $progresso }}%"></div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="text-center">
                <div class="text-lg font-bold text-green-600">{{ $diasDecorridos }}</div>
                <div class="text-gray-600">Dias Decorridos</div>
            </div>
            
            <div class="text-center">
                <div class="text-lg font-bold text-blue-600">{{ max(0, $totalDias - $diasDecorridos) }}</div>
                <div class="text-gray-600">Dias Restantes</div>
            </div>
            
            <div class="text-center">
                <div class="text-lg font-bold text-purple-600">{{ $totalDias }}</div>
                <div class="text-gray-600">Total de Dias</div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle cronograma
    const toggleBtn = document.getElementById('toggle-cronograma');
    const cronogramaContent = document.getElementById('cronograma-content');
    const toggleText = document.getElementById('cronograma-toggle-text');
    const toggleIcon = document.getElementById('cronograma-toggle-icon');

    if (toggleBtn && cronogramaContent) {
        toggleBtn.addEventListener('click', function() {
            if (cronogramaContent.classList.contains('hidden')) {
                cronogramaContent.classList.remove('hidden');
                toggleText.textContent = 'Ocultar';
                toggleIcon.classList.remove('fa-chevron-down');
                toggleIcon.classList.add('fa-chevron-up');
            } else {
                cronogramaContent.classList.add('hidden');
                toggleText.textContent = 'Mostrar';
                toggleIcon.classList.remove('fa-chevron-up');
                toggleIcon.classList.add('fa-chevron-down');
            }
        });
    }
});
</script>
@endpush