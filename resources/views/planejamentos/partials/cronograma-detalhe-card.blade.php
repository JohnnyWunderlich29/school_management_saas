<x-card class="shadow-lg border-0">
    @php
        // Fallback defensivo caso o $maps não seja injetado pelo View Composer
        $maps = is_array($maps ?? null) ? $maps : [
            'campos' => [],
            'saberes' => [],
            'objetivos' => [],
        ];
    @endphp
    <div class="flex flex-col items-center justify-between mb-4 md:flex-row">
        <div class="self-start">
            <div class="text-sm text-gray-500 mb-1">{{ $planejamento->turma->nome ?? 'Turma' }} • {{ $planejamento->disciplina->nome ?? 'Disciplina' }}</div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Cronograma Diário</h1>
            <p class="mt-1 text-sm text-gray-600">Período: {{ optional($planejamento->data_inicio)->format('d/m/Y') }} @if($planejamento->data_fim) - {{ $planejamento->data_fim->format('d/m/Y') }} @endif</p>
        </div>
        <div class="flex items-center gap-2 mt-4 md:mt-0">
            <a href="{{ $prevDate ? route('planejamentos.cronograma', ['planejamento' => $planejamento->id, 'data' => $prevDate]) : '#' }}"
               class="inline-flex items-center px-3 py-2 {{ $prevDate ? 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }} rounded-md"
               title="Dia anterior" {{ $prevDate ? '' : 'aria-disabled=true' }} data-ajax-cronograma="true">
                <i class="fas fa-chevron-left mr-1"></i>
            </a>
            <div class="px-3 py-2 bg-gray-100 rounded-md text-gray-900 font-medium">
                {{ $data->format('d/m/Y') }}
            </div>
            <a href="{{ $nextDate ? route('planejamentos.cronograma', ['planejamento' => $planejamento->id, 'data' => $nextDate]) : '#' }}"
               class="inline-flex items-center px-3 py-2 {{ $nextDate ? 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }} rounded-md"
               title="Próximo dia" {{ $nextDate ? '' : 'aria-disabled=true' }} data-ajax-cronograma="true">
                <i class="fas fa-chevron-right ml-1"></i>
            </a>
        </div>
    </div>

    @if($diario)
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center px-2 py-1 rounded-full {{ $diario->planejado ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        <i class="fas fa-clipboard-list mr-1"></i>
                        {{ $diario->planejado ? 'Planejado' : 'Sem detalhamento' }}
                    </span>
                </div>

                <!-- Metodologia destacada como principal -->
                <div class="mb-4 p-4 border-2 border-amber-300 bg-amber-50 rounded-lg">
                    <h3 class="text-sm font-semibold text-amber-900 mb-2">Metodologia (principal)</h3>
                    @php $met = $diario->metodologia; @endphp
                    @if(is_array($met))
                        <ul class="list-disc list-inside text-sm text-gray-800 space-y-1">
                            @foreach($met as $m)
                                <li>{{ $m }}</li>
                            @endforeach
                        </ul>
                    @elseif(is_string($met) && strlen($met))
                        <p class="text-sm text-gray-800">{{ $met }}</p>
                    @else
                        <p class="text-sm text-gray-600">Sem metodologia definida.</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Campos de Experiência</h3>
                        @php
                            $campos = is_array($diario->campos_experiencia) ? $diario->campos_experiencia : [];
                            $camposNomes = collect($campos)->map(fn($id) => data_get($maps['campos'], $id, $id))->filter()->values()->toArray();
                        @endphp
                        @if(count($camposNomes))
                            <div class="flex flex-wrap gap-2">
                                @foreach($camposNomes as $nome)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-indigo-100 text-indigo-800 text-xs">{{ $nome }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Nenhum campo selecionado.</p>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Saberes e Conhecimentos</h3>
                        @php
                            $saberes = is_array($diario->saberes_conhecimentos) ? $diario->saberes_conhecimentos : [];
                            $saberesNomes = collect($saberes)->map(fn($id) => data_get($maps['saberes'], $id, $id))->filter()->values()->toArray();
                        @endphp
                        @if(count($saberesNomes))
                            <div class="flex flex-wrap gap-2">
                                @foreach($saberesNomes as $nome)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full bg-teal-100 text-teal-800 text-xs">{{ $nome }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Nenhum saber cadastrado.</p>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Objetivos de Aprendizagem</h3>
                        @php
                            $objs = is_array($diario->objetivos_aprendizagem) ? $diario->objetivos_aprendizagem : [];
                            $objsNomes = collect($objs)->map(fn($id) => data_get($maps['objetivos'], $id, $id))->filter()->values()->toArray();
                        @endphp
                        @if(count($objsNomes))
                            <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                @foreach($objsNomes as $label)
                                    <li>{{ $label }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500">Nenhum objetivo definido.</p>
                        @endif
                    </div>

                    

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-2">Recursos</h3>
                        @php $recPre = is_array($diario->recursos_predefinidos) ? $diario->recursos_predefinidos : []; $recPers = $diario->recursos_personalizados; @endphp
                        @if(count($recPre))
                            <ul class="list-disc list-inside text-sm text-gray-700">
                                @foreach($recPre as $r)
                                    <li>{{ is_array($r) ? (data_get($r, 'nome') ?? json_encode($r)) : $r }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if(is_string($recPers) && strlen($recPers))
                            <p class="text-sm text-gray-700 mt-2">{{ $recPers }}</p>
                        @elseif(!count($recPre))
                            <p class="text-sm text-gray-500">Sem recursos definidos.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                <span class="text-sm text-yellow-800">Nenhum planejamento detalhado para esta data.</span>
            </div>
        </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('planejamentos.show', $planejamento) }}#secao-cronograma"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
            <i class="fas fa-list mr-2"></i>
            Voltar para o planejamento
        </a>
    </div>
</x-card>