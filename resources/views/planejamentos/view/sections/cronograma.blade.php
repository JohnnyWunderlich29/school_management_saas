<!-- Seção: Cronograma Diário -->
<div class="space-y-6">


    <div id="cronograma-content" class="hidden mt-4">
        @php
            $diarios = ($planejamento->diarios ?? collect())->sortBy(function ($d) {
                try {
                    return \Carbon\Carbon::parse($d->data)->getTimestamp();
                } catch (\Exception $e) {
                    return 0;
                }
            });
            $diasSemana = [
                'Sunday' => 'Domingo',
                'Monday' => 'Segunda',
                'Tuesday' => 'Terça',
                'Wednesday' => 'Quarta',
                'Thursday' => 'Quinta',
                'Friday' => 'Sexta',
                'Saturday' => 'Sábado',
            ];
        @endphp

        @if ($diarios->count() > 0)
            <div class="space-y-4">
                @foreach ($diarios as $d)
                    @php
                        $dt = null;
                        try {
                            $dt = \Carbon\Carbon::parse($d->data);
                        } catch (\Exception $e) {
                            $dt = null;
                        }
                        $dayLabel = $dt ? $diasSemana[$dt->format('l')] ?? $dt->format('l') : 'Data inválida';
                        $planejado = data_get($d, 'planejado');
                        if (is_null($planejado)) {
                            $campos = data_get($d, 'campos_experiencia', []);
                            $saberes = data_get($d, 'saberes', []);
                            $objs = data_get($d, 'objetivos', []);
                            $planejado =
                                (is_array($campos) && count($campos) > 0) ||
                                (is_array($saberes) && count($saberes) > 0) ||
                                (is_array($objs) && count($objs) > 0);
                        }
                        $camposCnt = is_array(data_get($d, 'campos_experiencia'))
                            ? count(data_get($d, 'campos_experiencia'))
                            : 0;
                        $saberesCnt = is_array(data_get($d, 'saberes')) ? count(data_get($d, 'saberes')) : 0;
                        $objsCnt = is_array(data_get($d, 'objetivos')) ? count(data_get($d, 'objetivos')) : 0;
                    @endphp
                    <div
                        class="p-4 rounded-lg border {{ $planejado ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-gray-50' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="text-sm text-gray-600">
                                    {{ $dt ? $dt->format('d/m/Y') : $d->data ?? 'N/A' }}
                                    <span class="ml-2">• {{ $dayLabel }}</span>
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $planejado ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    <i class="fas {{ $planejado ? 'fa-check-circle' : 'fa-minus-circle' }} mr-1"></i>
                                    {{ $planejado ? 'Planejado' : 'Não planejado' }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                            @if ($camposCnt > 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                                    <i class="fas fa-shapes mr-1"></i> Campos de Experiência: {{ $camposCnt }}
                                </span>
                            @endif
                            @if ($saberesCnt > 0)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full bg-indigo-100 text-indigo-800">
                                    <i class="fas fa-lightbulb mr-1"></i> Saberes: {{ $saberesCnt }}
                                </span>
                            @endif
                            @if ($objsCnt > 0)
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full bg-purple-100 text-purple-800">
                                    <i class="fas fa-bullseye mr-1"></i> Objetivos: {{ $objsCnt }}
                                </span>
                            @endif
                            @if ($camposCnt === 0 && $saberesCnt === 0 && $objsCnt === 0)
                                <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-800">
                                    <i class="fas fa-info-circle mr-1"></i> Sem conteúdo detalhado
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-4 rounded-lg border border-yellow-200 bg-yellow-50">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                    <span class="text-sm text-yellow-800">Nenhum planejamento diário cadastrado para este
                        planejamento.</span>
                </div>
            </div>
        @endif
    </div>
</div>
