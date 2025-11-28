@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="container mx-auto py-6">
            <x-breadcrumbs :items="[['title' => 'Planejamentos', 'url' => route('planejamentos.index')], ['title' => 'Cronograma Diário', 'url' => '#']]" />

            <x-card class="shadow-lg border-0">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Cronograma Diário</h1>
                        <p class="mt-1 text-sm text-gray-600">Visualize os planejamentos do dia selecionado</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('planejamentos.cronograma-dia', ['data' => $prevDate]) }}"
                           class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                           title="Dia anterior">
                            <i class="fas fa-chevron-left mr-1"></i>
                            Anterior
                        </a>
                        <div class="px-3 py-2 bg-gray-100 rounded-md text-gray-900 font-medium">
                            {{ $data->format('d/m/Y') }}
                        </div>
                        <a href="{{ route('planejamentos.cronograma-dia', ['data' => $nextDate]) }}"
                           class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                           title="Próximo dia">
                            Próximo
                            <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>

                @php
                    // Fallback defensivo caso o $maps não seja injetado pelo View Composer
                    $maps = is_array($maps ?? null) ? $maps : [
                        'campos' => [],
                        'saberes' => [],
                        'objetivos' => [],
                    ];
                @endphp

                @if($planejamentos->count())
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($planejamentos as $pl)
                            @php
                                $diario = $pl->diarios->first();
                                $campos = is_array(data_get($diario, 'campos_experiencia')) ? data_get($diario, 'campos_experiencia') : [];
                                $saberes = is_array(data_get($diario, 'saberes_conhecimentos')) ? data_get($diario, 'saberes_conhecimentos') : [];
                                $objs = is_array(data_get($diario, 'objetivos_aprendizagem')) ? data_get($diario, 'objetivos_aprendizagem') : [];
                                $planejado = $diario && ((count($campos) + count($saberes) + count($objs)) > 0);
                            @endphp
                            <div class="bg-white rounded-lg border {{ $planejado ? 'border-green-200' : 'border-gray-200' }} shadow-sm">
                                <div class="p-4 sm:p-5">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <div class="text-sm text-gray-500 mb-1">{{ $pl->turma->nome ?? 'Turma' }} • {{ $pl->disciplina->nome ?? 'Disciplina' }}</div>
                                            <h2 class="text-lg font-semibold text-gray-900">{{ $pl->titulo ?: 'Planejamento #' . $pl->id }}</h2>
                                            <div class="mt-2 text-sm text-gray-600">
                                                Período: {{ optional($pl->data_inicio)->format('d/m/Y') }}
                                                @if($pl->data_fim) - {{ $pl->data_fim->format('d/m/Y') }} @endif
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full {{ $planejado ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                <i class="fas fa-clipboard-list mr-1"></i>
                                                {{ $planejado ? 'Planejado' : 'Sem detalhamento' }}
                                            </span>
                                            <a href="{{ route('planejamentos.cronograma', ['planejamento' => $pl->id, 'data' => $data->format('Y-m-d')]) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md">
                                                <i class="fas fa-calendar-day mr-1"></i>
                                                Ver Cronograma Diário
                                            </a>
                                        </div>
                                    </div>

                                    @if($diario)
                                        @php
                                            $camposNomes = collect($campos)->map(fn($id) => data_get($maps['campos'], $id, $id))->filter()->values()->toArray();
                                            $saberesNomes = collect($saberes)->map(fn($id) => data_get($maps['saberes'], $id, $id))->filter()->values()->toArray();
                                            $objsNomes = collect($objs)->map(fn($id) => data_get($maps['objetivos'], $id, $id))->filter()->values()->toArray();
                                            $metodologia = data_get($diario, 'metodologia');
                                            $mostrarChip = function(array $arr, string $color) {
                                                $list = collect($arr);
                                                $primeiros = $list->take(3);
                                                $resto = max($list->count() - 3, 0);
                                                $html = '';
                                                foreach ($primeiros as $nome) {
                                                    $html .= '<span class="inline-flex items-center px-2 py-1 rounded-full ' . $color . ' text-xs">' . e($nome) . '</span>';
                                                }
                                                if ($resto > 0) {
                                                    $html .= '<span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-800 text-xs">+' . $resto . '</span>';
                                                }
                                                return $html;
                                            };
                                        @endphp

                                        <div class="mt-3 space-y-2">
                                            <!-- Metodologia destacada como principal -->
                                            <div class="p-3 border-2 border-amber-300 bg-amber-50 rounded-lg">
                                                <div class="inline-flex items-center px-2 py-1 rounded-md bg-amber-100 text-amber-800 text-xs font-medium mb-2">
                                                    <i class="fas fa-lightbulb mr-1"></i> Metodologia (principal)
                                                </div>
                                                @if(is_array($metodologia))
                                                    <ul class="list-disc list-inside text-sm text-gray-800 space-y-1">
                                                        @foreach($metodologia as $m)
                                                            <li>{{ $m }}</li>
                                                        @endforeach
                                                    </ul>
                                                @elseif(is_string($metodologia) && strlen($metodologia))
                                                    <p class="text-sm text-gray-800">{{ $metodologia }}</p>
                                                @else
                                                    <p class="text-xs text-gray-600">Sem metodologia definida.</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-indigo-50 text-indigo-700 text-xs font-medium">
                                                    <i class="fas fa-shapes mr-1"></i> Campos
                                                </span>
                                                {!! $mostrarChip($camposNomes, 'bg-indigo-100 text-indigo-800') !!}
                                                @if(empty($camposNomes))
                                                    <span class="text-xs text-gray-500">Nenhum.</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-teal-50 text-teal-700 text-xs font-medium">
                                                    <i class="fas fa-brain mr-1"></i> Saberes
                                                </span>
                                                {!! $mostrarChip($saberesNomes, 'bg-teal-100 text-teal-800') !!}
                                                @if(empty($saberesNomes))
                                                    <span class="text-xs text-gray-500">Nenhum.</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="inline-flex items-center px-2 py-1 rounded-md bg-purple-50 text-purple-700 text-xs font-medium">
                                                    <i class="fas fa-bullseye mr-1"></i> Objetivos
                                                </span>
                                                {!! $mostrarChip($objsNomes, 'bg-purple-100 text-purple-800') !!}
                                                @if(empty($objsNomes))
                                                    <span class="text-xs text-gray-500">Nenhum.</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-3 text-sm text-gray-500">Sem planejamento detalhado para o dia.</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <span class="text-sm text-yellow-800">Nenhum planejamento disponível para esta data.</span>
                        </div>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection