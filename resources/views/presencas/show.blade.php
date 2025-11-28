@extends('layouts.app')

@section('title', 'Presenças do Dia')

@section('content')
<x-card>

    <div class="flex flex-col justify-between mb-6 md:flex-row md:items-center">
        <div class="flex flex-col flex-nowrap">
            <h1 class="text-2xl font-bold text-gray-900">Presenças - @if($sala) {{ $sala->nome }} @endif</h1>
            <p class="text-gray-600 mt-1">{{ \Carbon\Carbon::parse($data)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($data)->locale('pt_BR')->dayName }}</p>
        </div>
        <div class="flex flex-col gap-3 w-full mt-2 md:flex-row md:justify-end">
            <x-button href="{{ route('presencas.lancar', ['data' => $data, 'sala_id' => $sala->id ?? '']) }}" color="primary">
                <i class="fas fa-check-circle mr-2"></i>Lançar Presenças
            </x-button>
            <x-button href="{{ route('presencas.index') }}" color="secondary">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </x-button>
        </div>
    </div>

    <!-- Filtros unificados -->
    <x-collapsible-filter 
        title="Filtros" 
        :action="route('presencas.show')"
        :clear-route="route('presencas.show')"
        :expanded="false"
    >
        <x-date-filter-with-arrows 
            name="data" 
            label="Data" 
            :value="request('data', $data)" 
        />
        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('coordenador'))
            <x-filter-field 
                name="sala_id" 
                label="Sala" 
                type="select"
                empty-option="Selecione uma sala"
                :options="$salasOptions"
                :value="request('sala_id', $sala->id)"
            />
        @else
            <input type="hidden" name="sala_id" value="{{ $sala->id }}">
        @endif
        <x-filter-field 
            name="turma_id" 
            label="Turma" 
            type="select"
            empty-option="Todas as turmas"
            :options="$turmasOptionsDia"
            :value="request('turma_id', $filtroTurmaId)"
        />
        @php
            $tempoOptions = collect($temposDia ?? [])
                ->mapWithKeys(function($o){ return [$o => 'Tempo '.$o]; })
                ->toArray();
        @endphp
        <x-filter-field 
            name="tempo_aula" 
            label="Tempo" 
            type="select"
            empty-option="Todos os tempos"
            :options="$tempoOptions"
            :value="request('tempo_aula', $filtroTempo)"
        />
    </x-collapsible-filter>


        <!-- Removido: Acontecimentos do Dia. Agora integrado no Resumo por Turma -->

        @if(isset($resumoPorTurma) && $resumoPorTurma->count() > 0)
            <div class="mb-6">
                <h3 class="text-mobile-title text-gray-900 mb-3"><i class="fas fa-chart-pie mr-2"></i>Resumo por Turma</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($resumoPorTurma as $r)
                        <div class="flex flex-col p-4 bg-white border rounded-lg shadow-sm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-medium text-gray-900">Turma {{ $r['turma'] }}</div>
                                    <div class="text-xs text-gray-500">Esperado: {{ $r['expected'] }} reg.</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-blue-700">{{ $r['percentual'] }}%</div>
                                    <div class="text-xs text-gray-500">
                                        <span class="text-green-700">{{ $r['presentes'] }}P</span>
                                        · <span class="text-red-700">{{ $r['ausentes'] }}A</span>
                                        · <span class="text-gray-700">{{ $r['nao_registrados'] }}NR</span>
                                    </div>
                                </div>
                            </div>
                            @php
                                $aulasTurma = ($resumoAulas ?? collect())
                                    ->where('turma_id', $r['turma_id'])
                                    ->sortBy('ordem');
                            @endphp
                            @if($aulasTurma->count())
                                <div class="mt-3 space-y-2">
                                    @foreach($aulasTurma as $aula)
                                        <a class="block" href="{{ route('presencas.show', array_filter([
                                            'sala_id' => $sala->id,
                                            'data' => $data,
                                            'turma_id' => $r['turma_id'] ?? null,
                                            'tempo_aula' => $aula['ordem'] ?? null,
                                        ])) }}">
                                            <div class="flex flex-col p-3 gap-3 bg-gray-50 border rounded hover:bg-gray-100 transition md:items-center md:flex-row justify-between">
                                                <div>
                                                    <div class="text-sm text-gray-900 font-medium">
                                                        {{ $aula['disciplina'] ?? 'Aula' }} @if(!empty($aula['ordem'])) • Tempo {{ $aula['ordem'] }} @endif
                                                    </div>
                                                    <div class="text-xs text-gray-600">
                                                        @if(!empty($aula['professor']))
                                                            <i class="fas fa-chalkboard-teacher mr-1"></i>{{ $aula['professor'] }}
                                                        @endif
                                                        @if(!empty($aula['hora_inicio']) || !empty($aula['hora_fim']))
                                                            <span class="ml-2">{{ $aula['hora_inicio'] }} - {{ $aula['hora_fim'] }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" title="Total">
                                                        <i class="fas fa-users mr-1"></i>{{ $aula['total'] ?? 0 }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" title="Presentes">
                                                        <i class="fas fa-check mr-1"></i>{{ $aula['presentes'] ?? 0 }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" title="Ausentes">
                                                        <i class="fas fa-times mr-1"></i>{{ $aula['ausentes'] ?? 0 }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800" title="Não registrados">
                                                        <i class="fas fa-question mr-1"></i>{{ $aula['nao_registrados'] ?? 0 }}
                                                    </span>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-3 p-3 bg-gray-50 border border-dashed rounded text-xs text-gray-600">
                                    Nenhuma aula para esta turma na data.
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Header com estatísticas - responsivo -->
        
        <div class="mb-4">
            <h3 class="text-mobile-title text-gray-900 mb-3">Lista de Alunos</h3>
            <!-- Desktop: horizontal -->
            <div class="hidden md:flex space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check mr-1"></i>{{ $presentes }} Presentes
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <i class="fas fa-times mr-1"></i>{{ $ausentes }} Ausentes
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    <i class="fas fa-question mr-1"></i>{{ $naoRegistrados }} Não Registrados
                </span>
            </div>
            <!-- Mobile: grid 3 colunas -->
            <div class="md:hidden grid grid-cols-3 gap-2">
                <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <i class="fas fa-check mr-1"></i>{{ $presentes }}
                </span>
                <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <i class="fas fa-times mr-1"></i>{{ $ausentes }}
                </span>
                <span class="inline-flex items-center justify-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    <i class="fas fa-question mr-1"></i>{{ $naoRegistrados }}
                </span>
            </div>
        </div>
        
        <!-- Tabela Desktop (somente leitura, com colunas por tempo) -->
        @php
            $headers = array_merge(
                array_merge(
                    ['Aluno'],
                    collect($temposDia ?? [])->map(function($t){ return 'T'.$t; })->all()
                ),
                ['Status','Horário Entrada','Horário Saída','Justificativa']
            );
        @endphp
        <x-table 
            class="hidden md:block" 
            :headers="$headers" 
            :actions="false"
        >
            @forelse($alunos as $index => $aluno)
                @php
                    $presencaAluno = $presencas->where('aluno_id', $aluno->id)->first();
                @endphp
                <x-table-row :striped="true" :index="$index">
                    <x-table-cell>
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-3">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $aluno->nome }} {{ $aluno->sobrenome }}</div>
                            </div>
                        </div>
                    </x-table-cell>
                    @foreach(($temposDia ?? []) as $t)
                        @php $pt = $presencasPorTempo[$aluno->id][$t] ?? null; @endphp
                        <x-table-cell>
                            @if($pt)
                                @if($pt->presente)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" title="Tempo {{ $t }}: Presente">
                                        <i class="fas fa-check"></i>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" title="Tempo {{ $t }}: Ausente">
                                        <i class="fas fa-times"></i>
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800" title="Tempo {{ $t }}: Não registrado">
                                    <i class="fas fa-question"></i>
                                </span>
                            @endif
                        </x-table-cell>
                    @endforeach
                    <x-table-cell>
                        @if($presencaAluno)
                            @if($presencaAluno->presente)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Presente
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>Ausente
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-question mr-1"></i>Não Registrado
                            </span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        {{ $presencaAluno && $presencaAluno->presente ? $presencaAluno->hora_entrada ?? '-' : '-' }}
                    </x-table-cell>
                    <x-table-cell>
                        {{ $presencaAluno && $presencaAluno->presente ? $presencaAluno->hora_saida ?? '-' : '-' }}
                    </x-table-cell>
                    <x-table-cell>
                        {{ $presencaAluno && !$presencaAluno->presente ? $presencaAluno->justificativa ?? '-' : '-' }}
                    </x-table-cell>
                    <!-- Sem ações de edição nesta visão -->
                </x-table-row>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Nenhum aluno encontrado.
                    </td>
                </tr>
            @endforelse
        </x-table>
        
        <!-- Layout Mobile com Cards (ajustado ao padrão do sistema) -->
        <div class="md:hidden space-y-4">
            @forelse($alunos as $aluno)
                @php
                    $presencaAluno = $presencas->where('aluno_id', $aluno->id)->first();
                @endphp
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <!-- Header do card -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-start space-x-3 flex-1 min-w-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white flex-shrink-0 shadow-sm">
                                <span class="text-sm font-medium">{{ strtoupper(substr($aluno->nome, 0, 2)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-medium text-gray-900 truncate">{{ $aluno->nome }} {{ $aluno->sobrenome }}</h4>
                            </div>
                        </div>
                        <!-- Status badge -->
                        <div class="ml-2">
                            @if($presencaAluno)
                                @if($presencaAluno->presente)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check mr-1"></i>Presente
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times mr-1"></i>Ausente
                                    </span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-question mr-1"></i>Não Registrado
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Informações de horário e justificativa -->
                    @if($presencaAluno)
                        <div class="grid grid-cols-2 gap-3 mb-3 text-sm">
                            @if($presencaAluno->presente)
                                <div>
                                    <span class="text-gray-500 block">Entrada:</span>
                                    <span class="font-medium">{{ $presencaAluno->hora_entrada ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Saída:</span>
                                    <span class="font-medium">{{ $presencaAluno->hora_saida ?? '-' }}</span>
                                </div>
                            @else
                                <div class="col-span-2">
                                    <span class="text-gray-500 block">Justificativa:</span>
                                    <span class="font-medium">{{ $presencaAluno->justificativa ?? 'Sem justificativa' }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                    
                    @if(($temposDia ?? []) && count($temposDia))
                        <div class="flex flex-wrap gap-2 mb-2">
                            @foreach($temposDia as $t)
                                @php $pt = $presencasPorTempo[$aluno->id][$t] ?? null; @endphp
                                @if($pt)
                                    @if($pt->presente)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">T{{ $t }} <i class="fas fa-check ml-1"></i></span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">T{{ $t }} <i class="fas fa-times ml-1"></i></span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">T{{ $t }} <i class="fas fa-question ml-1"></i></span>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <!-- Sem ações de edição nesta visão -->
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-user-graduate text-3xl mb-2"></i>
                    <p>Nenhum aluno encontrado.</p>
                </div>
            @endforelse
        </div>


</x-card>

@endsection