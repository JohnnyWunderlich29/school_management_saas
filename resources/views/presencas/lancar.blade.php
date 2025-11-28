@extends('layouts.app')

@section('title', 'Lançar Presenças')

@section('content')
<!-- Modal de Justificativa (componente padrão) -->
<x-modal name="justificar-ausencia-modal" title="Justificar Ausência" maxWidth="max-w-md">
    <form id="justificativaForm">
        <input type="hidden" id="justificativa_aluno_id" name="aluno_id">
        <input type="hidden" id="justificativa_data" name="data">
        <input type="hidden" id="justificativa_tempo_aula" name="tempo_aula">
        <div class="mb-4">
            <label for="justificativa" class="block text-sm font-medium text-gray-700 mb-2">Motivo da ausência:</label>
            <textarea id="justificativa" name="justificativa" rows="3" maxlength="255" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full text-sm border-gray-300 rounded-md resize-none" placeholder="Informe o motivo da ausência"></textarea>
            <p class="mt-1 text-xs text-gray-500">Máximo de 255 caracteres</p>
        </div>
    </form>
    <x-slot name="footer">
        <x-button type="button" color="secondary" onclick="cancelJustificativaModal()">Cancelar</x-button>
        <x-button type="button" id="salvarJustificativa" color="primary"><i class="fas fa-save mr-1.5"></i>Salvar</x-button>
    </x-slot>
</x-modal>

<x-card>
    <div class="flex flex-col mb-8 space-y-5 md:flex-row justify-between md:space-y-0 md:items-center">
        <div>
            <h1 class="text-xl md:text-3xl font-semibold text-gray-900">Lançar Presenças</h1>
            <p class="mt-2 text-sm md:text-base text-gray-600">Gerencie a presença dos alunos por período</p>
        </div>
        <div class="flex flex-col gap-2.5 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
            <x-button href="{{ route('presencas.index') }}" color="secondary" class="w-full sm:justify-center">
                <i class="fas fa-arrow-left mr-1"></i> 
                <span class="hidden md:inline">Voltar para Presenças</span>
                <span class="md:hidden">Voltar</span>
            </x-button>
        </div>
    </div>

     <x-collapsible-filter 
         title="Filtros de Lançamento" 
         :action="route('presencas.lancar')" 
         :clear-route="route('presencas.lancar')"
         target="lancarTablesContainer"
     >
         <x-filter-field 
             name="sala_id" 
             label="Sala" 
             type="select"
             emptyOption="Todas as salas"
             :options="$todasSalas->pluck('nome_completo', 'id')"
         />
         
         <x-filter-field 
             name="tempo_aula" 
             label="Tempo de Aula" 
             type="select"
             emptyOption="Todos os tempos"
             :options="[
                 '1' => '1º Tempo',
                 '2' => '2º Tempo',
                 '3' => '3º Tempo',
                 '4' => '4º Tempo',
                 '5' => '5º Tempo',
             ]"
         />
         
         <x-date-filter-with-arrows 
             name="data_inicio" 
             label="Data Início"
             :value="$dataInicio"
             data-fim-name="data_fim"
             :data-fim-value="$dataFim"
         />
     </x-collapsible-filter>

    <!-- Legenda e instruções -->
    <div class="mt-6 mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 md:p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2 md:gap-3">
                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs md:text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                    <i class="fas fa-check mr-1.5"></i> Presente
                </span>
                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs md:text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                    <i class="fas fa-times mr-1.5"></i> Ausente
                </span>
                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs md:text-sm font-medium bg-gray-100 text-gray-800 border border-gray-200">
                    <i class="fas fa-circle mr-1.5 text-[10px]"></i> Pendente
                </span>
                <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs md:text-sm font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
                    <i class="fas fa-ban mr-1.5"></i> Fora da grade
                </span>
            </div>
            <p class="text-xs md:text-sm text-gray-600">Dica: use os botões abaixo de cada data para aplicar o status em todos os alunos daquele dia.</p>
        </div>
    </div>
 

<div id="lancarTablesContainer">
    <div data-ajax-content>
@if($salas->count() > 0)
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @foreach($salas as $sala)
            @if($sala->alunos->count() > 0)

                    <!-- Header da sala com design melhorado -->
                    <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mr-4 flex-shrink-0">
                            <i class="fas fa-door-open text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg md:text-xl font-semibold text-gray-900">{{ $sala->nome }}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                <i class="fas fa-users mr-1"></i>{{ $sala->alunos->count() }} alunos
                            </p>
                        </div>
                    </div>
                    
                    
                    <!-- Botões para marcar/desmarcar todos (visíveis apenas no mobile) -->
                    <!-- FUNCIONALIDADE LEGADO -->
                    <!--   
                    <div class="md:hidden mb-4 flex flex-wrap gap-2">
                        {{-- @foreach($datas as $data)
                            <div class="flex gap-2">
                                <button type="button" 
                                    class="marcar-todos-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none"
                                    data-data="{{ is_array($data) ? $data['data'] : $data }}"
                                    data-sala-id="{{ $sala->id }}"
                                    data-presente="1">
                                    <i class="fas fa-check mr-1"></i> Todos presentes {{ \Carbon\Carbon::parse(is_array($data) ? $data['data'] : $data)->format('d/m') }}
                                </button>
                                <button type="button" 
                                    class="marcar-todos-btn-ausente bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none"
                                    data-data="{{ is_array($data) ? $data['data'] : $data }}"
                                    data-sala-id="{{ $sala->id }}"
                                    data-presente="0"
                                    data-bs-toggle="modal"
                                    data-bs-target="#justificativaModal">
                                    <i class="fas fa-times mr-1"></i> Todos ausentes {{ \Carbon\Carbon::parse(is_array($data) ? $data['data'] : $data)->format('d/m') }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                    --}}
                    -->
                    
                    <!-- Layout Desktop - Tabela por data com tempos em colunas -->
                    <div class="hidden md:block space-y-6">
                        @foreach($datas as $dataInfo)
                            @php $dataKey = $dataInfo['data']; @endphp
                            <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                                <!-- Cabeçalho da data com ações -->
                                <div class="px-4 py-3 border-b flex items-center justify-between {{ $dataInfo['eh_fim_semana'] ? 'bg-yellow-50' : 'bg-gray-50' }} {{ $dataInfo['eh_hoje'] ? 'ring-1 ring-blue-200 bg-blue-50' : '' }}">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-sm md:text-base {{ $dataInfo['eh_hoje'] ? 'text-blue-700' : 'text-gray-800' }}">{{ \Carbon\Carbon::parse($dataKey)->format('d/m') }}</span>
                                        <span class="text-xs {{ $dataInfo['eh_fim_semana'] ? 'text-orange-600' : 'text-gray-500' }}">{{ \Carbon\Carbon::parse($dataKey)->locale('pt_BR')->dayName }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                            class="marcar-todos-btn bg-green-500 hover:bg-green-600 text-white px-2.5 py-1.5 rounded-md text-[11px] md:text-xs font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1"
                                            data-data="{{ $dataKey }}"
                                            data-sala-id="{{ $sala->id }}"
                                            data-presente="1"
                                            title="Marcar todos presentes no dia {{ \Carbon\Carbon::parse($dataKey)->format('d/m') }}">
                                            <i class="fas fa-check mr-1"></i> Todos P
                                        </button>
                                        <button type="button"
                                            class="marcar-todos-btn-ausente bg-red-500 hover:bg-red-600 text-white px-2.5 py-1.5 rounded-md text-[11px] md:text-xs font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                            data-data="{{ $dataKey }}"
                                            data-sala-id="{{ $sala->id }}"
                                            data-presente="0"
                                            title="Marcar todos ausentes no dia {{ \Carbon\Carbon::parse($dataKey)->format('d/m') }}">
                                            <i class="fas fa-times mr-1"></i> Todos F
                                        </button>
                                    </div>
                                </div>
                                <!-- Tabela por data: Aluno + T1..T5 em colunas -->
                                <table class="min-w-full table-fixed divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wider">Aluno</th>
                                            @for($t=1; $t<=5; $t++)
                                                <th class="px-3 py-3 text-center text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wider w-20">{{ $t }}º Tempo</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($sala->alunos as $aluno)
                                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                                <td class="px-6 py-3 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center shadow-sm">
                                                                <span class="text-sm font-medium text-white">{{ strtoupper(substr($aluno->nome, 0, 2)) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">{{ $aluno->nome }} {{ $aluno->sobrenome }}</div>
                                                            <div class="text-sm text-gray-500">Matrícula: {{ $aluno->matricula ?? $aluno->id }}</div>
                                                            <div class="mt-2">
                                                                <button type="button"
                                                                    class="marcar-turno-f-aluno-btn inline-flex items-center px-2 py-1 rounded text-[11px] font-medium bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-red-500"
                                                                    title="Marcar todo o turno como F para este aluno"
                                                                    data-aluno-id="{{ $aluno->id }}"
                                                                    data-data="{{ $dataKey }}"
                                                                    data-sala-id="{{ $sala->id }}">
                                                                    <i class="fas fa-user-slash mr-1"></i> Marcar turno como F
                                                                </button>
                                                                <button type="button"
                                                                    class="marcar-turno-p-aluno-btn ml-2 inline-flex items-center px-2 py-1 rounded text-[11px] font-medium bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 hover:border-green-300 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-green-500"
                                                                    title="Marcar todo o turno como P para este aluno"
                                                                    data-aluno-id="{{ $aluno->id }}"
                                                                    data-data="{{ $dataKey }}"
                                                                    data-sala-id="{{ $sala->id }}">
                                                                    <i class="fas fa-user-check mr-1"></i> Marcar turno como P
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                @php
                                                    $alunoId = $aluno->id;
                                                    $temposPermitidos = $temposDisponiveis[$sala->id][$dataKey] ?? [];
                                                @endphp
                                                @for($t=1; $t<=5; $t++)
                                                    @php
                                                        $presencaTempo = $presencasFormatadas[$dataKey][$alunoId][$t] ?? null;
                                                        $desabilitado = !in_array($t, $temposPermitidos);
                                                    @endphp
                                                    <td class="px-3 py-3 text-center align-middle">
                                                        @if($presencaTempo)
                                                            <div class="flex items-center justify-center gap-2">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-[12px] font-medium {{ $presencaTempo->presente ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                                                                    <i class="fas fa-{{ $presencaTempo->presente ? 'check' : 'times' }} mr-1"></i>{{ $presencaTempo->presente ? 'P' : 'F' }}
                                                                </span>
                                                                @if($presencaTempo->presente)
                                                                    <button type="button"
                                                                        class="editar-presenca-btn ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-red-500"
                                                                        title="Trocar para F"
                                                                        data-aluno-id="{{ $aluno->id }}"
                                                                        data-data="{{ $dataKey }}"
                                                                        data-tempo-aula="{{ $t }}">
                                                                        <i class="fas fa-exchange-alt mr-1"></i>F
                                                                    </button>
                                                                @else
                                                                    <button type="button"
                                                                        class="trocar-para-p-btn ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 hover:border-green-300 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-green-500"
                                                                        title="Trocar para P"
                                                                        data-aluno-id="{{ $aluno->id }}"
                                                                        data-data="{{ $dataKey }}"
                                                                        data-tempo-aula="{{ $t }}">
                                                                        <i class="fas fa-exchange-alt mr-1"></i>P
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="flex items-center justify-center gap-1">
                                                                <button type="button"
                                                                    class="presenca-btn {{ $desabilitado ? 'opacity-40 cursor-not-allowed border border-gray-200' : 'bg-green-500 hover:bg-green-600' }} text-white px-2 py-1 rounded-md text-[12px] shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1"
                                                                    data-aluno-id="{{ $aluno->id }}"
                                                                    data-data="{{ $dataKey }}"
                                                                    data-sala-id="{{ $sala->id }}"
                                                                    data-tempo-aula="{{ $t }}"
                                                                    data-presente="1"
                                                                    title="Marcar presente"
                                                                    {{ $desabilitado ? 'disabled' : '' }}>
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                                <button type="button"
                                                                    class="presenca-btn-ausencia {{ $desabilitado ? 'opacity-40 cursor-not-allowed border border-gray-200' : 'bg-red-500 hover:bg-red-600' }} text-white px-2 py-1 rounded-md text-[12px] shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                                                    data-aluno-id="{{ $aluno->id }}"
                                                                    data-data="{{ $dataKey }}"
                                                                    data-sala-id="{{ $sala->id }}"
                                                                    data-tempo-aula="{{ $t }}"
                                                                    data-presente="0"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#justificativaModal"
                                                                    title="Marcar faltoso"
                                                                    {{ $desabilitado ? 'disabled' : '' }}>
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>

                        <!-- Layout mobile otimizado com cards -->
                        <div class="md:hidden space-y-4">
                            @foreach($sala->alunos as $aluno)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200 mb-4">
                                    <!-- Header do card -->
                                    <div class="flex items-center mb-4 pb-3 border-b border-gray-100">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white mr-3 flex-shrink-0 shadow-sm">
                                            <span class="text-sm font-medium">
                                                {{ strtoupper(substr($aluno->nome, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 text-base">{{ $aluno->nome }} {{ $aluno->sobrenome }}</h3>
                                            <p class="text-sm text-gray-500">Matrícula: {{ $aluno->id }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Estatísticas em grid (apenas se houver múltiplas datas) -->
                                    @if(count($datas) > 1)
                                        <div class="flex flex-col w-full md:flex-row gap-4 mb-6">
                                            @php
                                                $presentes = 0;
                                                $ausentes = 0;
                                                $naoRegistrados = 0;
                                                foreach($datas as $dataInfo) {
                                                    $dataKey = $dataInfo['data'];
                                                    $alunoId = $aluno->id;
                                                    $temposPermitidos = $temposDisponiveis[$sala->id][$dataKey] ?? [];
                                                    $presencasDoDia = $presencasFormatadas[$dataKey][$alunoId] ?? [];
                                                    // Somar presentes/ausentes considerando registros por tempo
                                                    foreach ($presencasDoDia as $tempoIdx => $presencaObj) {
                                                        $presenteVal = is_array($presencaObj)
                                                            ? ($presencaObj['presente'] ?? null)
                                                            : $presencaObj->presente;
                                                        if ($presenteVal === true || $presenteVal === 1 || $presenteVal === '1') {
                                                            $presentes++;
                                                        } elseif ($presenteVal === false || $presenteVal === 0 || $presenteVal === '0') {
                                                            $ausentes++;
                                                        }
                                                    }
                                                    // Calcular pendentes como tempos permitidos não registrados
                                                    $recordedTemps = array_filter(array_keys($presencasDoDia), function($t){ return is_numeric($t) && $t >= 1 && $t <= 5; });
                                                    $recordedInPermitted = count(array_intersect($temposPermitidos, $recordedTemps));
                                                    $naoRegistrados += max(0, (count($temposPermitidos) - $recordedInPermitted));
                                                }
                                            @endphp
                                            <div class="bg-green-50 rounded-xl p-4 text-center border border-green-100 shadow-sm">
                                                <div class="text-lg font-bold text-green-700 mb-1">{{ $presentes }}</div>
                                                <div class="text-xs font-medium text-green-600">Presentes</div>
                                            </div>
                                            <div class="bg-red-50 rounded-xl p-4 text-center border border-red-100 shadow-sm">
                                                <div class="text-lg font-bold text-red-700 mb-1">{{ $ausentes }}</div>
                                                <div class="text-xs font-medium text-red-600">Ausentes</div>
                                            </div>
                                            <div class="bg-yellow-50 rounded-xl p-4 text-center border border-yellow-100 shadow-sm">
                                                <div class="text-lg font-bold text-yellow-700 mb-1">{{ $naoRegistrados }}</div>
                                                <div class="text-xs font-medium text-yellow-600">Pendentes</div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Botões de ação com touch targets otimizados -->
                                    <div class="space-y-2">
                                        @foreach($datas as $data)
                                            @php
                                                $dataKey = is_array($data) ? $data['data'] : $data;
                                                $alunoId = $aluno->id;
                                                $temposPermitidos = $temposDisponiveis[$sala->id][$dataKey] ?? [];
                                                $carbon = \Carbon\Carbon::parse($dataKey);
                                                $isFimSemana = $carbon->isWeekend();
                                                $isHoje = $carbon->isToday();
                                                $dayName = $carbon->locale('pt_BR')->dayName;
                                            @endphp
                                            
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 bg-gray-50 rounded-lg gap-3">
                                            <div class="text-sm font-medium text-gray-700 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-semibold {{ $isHoje ? 'text-blue-700' : 'text-gray-800' }}">{{ $carbon->format('d/m') }}</span>
                                                    <span class="text-xs {{ $isFimSemana ? 'text-orange-600' : 'text-gray-500' }}">{{ $dayName }}</span>
                                                </div>
                                                <div class="mt-2">
                                                    <button type="button"
                                                        class="marcar-turno-f-aluno-btn inline-flex items-center px-2 py-1 rounded text-[11px] font-medium bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-red-500"
                                                        title="Marcar todo o turno como F para este aluno"
                                                        data-aluno-id="{{ $aluno->id }}"
                                                        data-data="{{ $dataKey }}">
                                                        <i class="fas fa-user-slash mr-1"></i> Marcar turno como F
                                                    </button>
                                                    <button type="button"
                                                        class="marcar-turno-p-aluno-btn mt-1 inline-flex items-center px-2 py-1 rounded text-[11px] font-medium bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 hover:border-green-300 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-green-500"
                                                        title="Marcar todo o turno como P para este aluno"
                                                        data-aluno-id="{{ $aluno->id }}"
                                                        data-data="{{ $dataKey }}">
                                                        <i class="fas fa-user-check mr-1"></i> Marcar turno como P
                                                    </button>
                                                </div>
                                            </div>
                                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 w-full sm:ml-4">
                                                    @for($t=1; $t<=5; $t++)
                                                        @php
                                                            $presencaTempo = $presencasFormatadas[$dataKey][$alunoId][$t] ?? null;
                                                            $desabilitado = !in_array($t, $temposPermitidos);
                                                        @endphp
                                                        <div class="flex flex-col items-center">
                                                            <div class="text-xs leading-tight text-gray-600 mb-1">{{ $t }}º</div>
                                                            @if($presencaTempo)
                                                                <div class="flex items-center gap-2">
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[12px] font-medium {{ $presencaTempo->presente ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                        <i class="fas fa-{{ $presencaTempo->presente ? 'check' : 'times' }} mr-1"></i>{{ $presencaTempo->presente ? 'P' : 'F' }}
                                                                    </span>
                                                                    @if($presencaTempo->presente)
                                                                        <button type="button"
                                                                            class="editar-presenca-btn mt-1 inline-flex items-center px-2 py-1 rounded text-[10px] font-medium bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-red-500"
                                                                            title="Trocar para F"
                                                                            data-aluno-id="{{ $aluno->id }}"
                                                                            data-data="{{ $dataKey }}"
                                                                            data-tempo-aula="{{ $t }}">
                                                                            <i class="fas fa-exchange-alt mr-1"></i>F
                                                                        </button>
                                                                    @else
                                                                        <button type="button"
                                                                            class="trocar-para-p-btn mt-1 inline-flex items-center px-2 py-1 rounded text-[10px] font-medium bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 hover:border-green-300 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-green-500"
                                                                            title="Trocar para P"
                                                                            data-aluno-id="{{ $aluno->id }}"
                                                                            data-data="{{ $dataKey }}"
                                                                            data-tempo-aula="{{ $t }}">
                                                                            <i class="fas fa-exchange-alt mr-1"></i>P
                                                                        </button>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="flex gap-1">
                                                                    <button type="button" 
                                                                        class="presenca-btn {{ $desabilitado ? 'opacity-50 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700' }} text-white text-center py-1 px-2 rounded font-medium text-xs min-h-[28px] flex items-center justify-center transition-colors"
                                                                        data-aluno-id="{{ $aluno->id }}"
                                                                        data-data="{{ $dataKey }}"
                                                                        data-sala-id="{{ $sala->id }}"
                                                                        data-tempo-aula="{{ $t }}"
                                                                        data-presente="1"
                                                                        title="Marcar como presente"
                                                                        {{ $desabilitado ? 'disabled' : '' }}
                                                                    >
                                                                        <i class="fas fa-check"></i>
                                                                    </button>
                                                                    <button type="button" 
                                                                        class="presenca-btn-ausencia {{ $desabilitado ? 'opacity-50 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700' }} text-white text-center py-1 px-2 rounded font-medium text-xs min-h-[28px] flex items-center justify-center transition-colors"
                                                                        data-aluno-id="{{ $aluno->id }}"
                                                                        data-data="{{ $dataKey }}"
                                                                        data-sala-id="{{ $sala->id }}"
                                                                        data-tempo-aula="{{ $t }}"
                                                                        data-presente="0"
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#justificativaModal"
                                                                        title="Marcar como faltoso"
                                                                        {{ $desabilitado ? 'disabled' : '' }}
                                                                    >
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                            </div>
                        @endforeach
                    </div>
                    

            @endif
        @endforeach
    @else
        <x-card>
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 text-gray-300">
                    <i class="fas fa-users text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma sala encontrada</h3>
                <p class="text-gray-600">Não há salas disponíveis para o período selecionado.</p>
            </div>
        </x-card>
@endif
</div>
</div>

</x-card>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar CSRF token para requisições AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    // Flags globais para evitar sobreposição de cliques e recarregamentos
    let isBulkProcessing = false;
    let suppressReload = false;
    // Contexto para operação em massa: marcar todo o turno como F por aluno
    let massAlunoFContext = null;
    // Contexto para operação em massa: marcar todo o turno como P por aluno
    let massAlunoPContext = null;
    // Contexto para operação em massa: marcar TODOS como F por data (abre modal)
    let massDiaFContext = null;

    // Helper para obter sala_id a partir de qualquer slot do aluno/data
    function getSalaIdFor(alunoId, data) {
        const el = document.querySelector(`.presenca-btn[data-aluno-id="${alunoId}"][data-data="${data}"]`);
        return el ? el.getAttribute('data-sala-id') : null;
    }

    // Função utilitária para registrar presença/ausência de forma reutilizável
    async function registrarPresenca({ alunoId, data, presente, tempoAula, escolaId, salaId, updateSlotEl }) {
        const response = await fetch('{{ route("presencas.store.individual") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                aluno_id: alunoId,
                data: data,
                presente: !!presente,
                tempo_aula: parseInt(tempoAula),
                escola_id: escolaId,
                sala_id: salaId ?? getSalaIdFor(alunoId, data)
            })
        });
        if (!response.ok) throw new Error('Falha na requisição: ' + response.status);
        const dataResp = await response.json();
        if (!dataResp.success) throw new Error(dataResp.message || 'Erro desconhecido');
        // Atualizar apenas o slot do tempo clicado
        if (updateSlotEl) {
            const isPresente = !!presente;
            updateSlotEl.innerHTML = `
                <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium ${
                    isPresente ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'
                }">
                    <i class="fas fa-${isPresente ? 'check' : 'times'} mr-1"></i>${isPresente ? 'P' : 'F'}
                </span>
            `;
        }
        return dataResp;
    }
    
    // Adicionar event listeners para os botões de presença (respeita bloqueio e evita recarregamentos em massa)
    document.querySelectorAll('.presenca-btn').forEach(button => {
        button.addEventListener('click', async function() {
            if (isBulkProcessing) return; // bloquear clique individual durante processamento em massa
            const alunoId = this.dataset.alunoId;
            const data = this.dataset.data;
            const presente = this.dataset.presente === '1';
            const tempoAula = parseInt(this.dataset.tempoAula || this.getAttribute('data-tempo-aula'));
            const salaId = this.dataset.salaId || getSalaIdFor(alunoId, data);
            const slotContainer = this.parentElement;
            try {
                await registrarPresenca({
                    alunoId,
                    data,
                    presente,
                    tempoAula,
                    escolaId: {{ auth()->user()->escola_id ?? 'null' }},
                    salaId,
                    updateSlotEl: slotContainer
                });
                // Toast de sucesso
                const successMsg = document.createElement('div');
                successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                successMsg.textContent = 'Presença registrada com sucesso!';
                document.body.appendChild(successMsg);
                setTimeout(() => {
                    successMsg.remove();
                    if (!suppressReload) {
                        window.location.reload();
                    }
                }, 1200);
            } catch (error) {
                console.error('Erro:', error);
                alertSystem.error('Erro ao registrar presença');
            }
        });
    });
    
    // Adicionar event listeners para os botões de marcar todos (processamento sequencial e com bloqueio)
    document.querySelectorAll('.marcar-todos-btn').forEach(button => {
        button.addEventListener('click', async function() {
            if (isBulkProcessing) return;
            isBulkProcessing = true;
            suppressReload = true; // evitar reloads individuais
            const data = this.dataset.data;
            const salaId = this.dataset.salaId;
            const presente = this.dataset.presente === '1';

            // Coletar alvos VÁLIDOS primeiro: apenas botões não desabilitados (tempos de aula permitidos)
            const targetButtons = Array.from(document.querySelectorAll(`.presenca-btn[data-data="${data}"][data-sala-id="${salaId}"]`))
                .filter(btn => !btn.hasAttribute('disabled'));

            // Desabilitar controles durante processamento
            document.querySelectorAll('.marcar-todos-btn, .marcar-todos-btn-ausente').forEach(b => { b.disabled = true; b.classList.add('opacity-60'); });
            document.querySelectorAll('.presenca-btn, .presenca-btn-ausencia').forEach(b => { b.disabled = true; });

            try {
                for (const btn of targetButtons) {
                    const alunoId = btn.dataset.alunoId;
                    const tempoAula = parseInt(btn.dataset.tempoAula || btn.getAttribute('data-tempo-aula'));
                    const slotContainer = btn.parentElement;
                    await registrarPresenca({
                        alunoId,
                        data,
                        presente,
                        tempoAula,
                        escolaId: {{ auth()->user()->escola_id ?? 'null' }},
                        salaId,
                        updateSlotEl: slotContainer
                    });
                }
                // Toast de sucesso geral e recarregar uma única vez
                const successMsg = document.createElement('div');
                successMsg.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50';
                successMsg.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Todos marcados como presentes!';
                document.body.appendChild(successMsg);
                setTimeout(() => { successMsg.remove(); }, 1200);
            } catch (error) {
                console.error('Erro no processamento em massa:', error);
                alertSystem.error('Erro ao marcar todos presentes. Tente novamente.');
            } finally {
                isBulkProcessing = false;
                suppressReload = false;
                // Reabilitar controles (se ainda não recarregou)
                document.querySelectorAll('.marcar-todos-btn, .marcar-todos-btn-ausente').forEach(b => { b.disabled = false; b.classList.remove('opacity-60'); });
                document.querySelectorAll('.presenca-btn, .presenca-btn-ausencia').forEach(b => { b.disabled = false; });
            }
        });
    });

    // Adicionar event listener para "Marcar todos F" (abre modal com justificativa e aplica em massa por data)
    document.querySelectorAll('.marcar-todos-btn-ausente').forEach(button => {
        button.addEventListener('click', function() {
            if (isBulkProcessing) return;
            isBulkProcessing = true;
            suppressReload = true;

            const data = this.dataset.data;
            const salaId = this.dataset.salaId;

            // Coletar alvos válidos: novos F e editar P→F para a data
            const absenceButtons = Array.from(document.querySelectorAll(`.presenca-btn-ausencia[data-data="${data}"]`)).filter(btn => !btn.hasAttribute('disabled'));
            const editButtonsF = Array.from(document.querySelectorAll(`.editar-presenca-btn[data-data="${data}"]`)).filter(btn => !btn.hasAttribute('disabled'));
            const targetButtons = [...absenceButtons, ...editButtonsF];

            if (targetButtons.length === 0) {
                alertSystem.warning('Nenhum tempo disponível para marcar F nesta data.');
                isBulkProcessing = false;
                suppressReload = false;
                return;
            }

            // Preparar itens e indicadores visuais
            const items = targetButtons.map(btn => {
                const alunoId = btn.getAttribute('data-aluno-id') || btn.dataset.alunoId;
                const tempo = btn.getAttribute('data-tempo-aula') || btn.dataset.tempoAula;
                const slotContainer = btn.parentElement;
                if (slotContainer && !slotContainer.hasAttribute('data-original-content')) {
                    slotContainer.setAttribute('data-original-content', slotContainer.innerHTML);
                }
                if (slotContainer) {
                    slotContainer.innerHTML = `
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 animate-pulse">
                            <i class="fas fa-spinner fa-spin mr-1"></i>Processando...
                        </span>
                    `;
                }
                return { alunoId, tempo, slotContainer };
            });

            massDiaFContext = { data, salaId, items };

            // Preencher modal com contexto básico (tempo vazio porque é múltiplo)
            const alunoInput = document.getElementById('justificativa_aluno_id');
            const dataInput = document.getElementById('justificativa_data');
            const tempoInput = document.getElementById('justificativa_tempo_aula');
            if (alunoInput) alunoInput.value = '';
            if (dataInput) dataInput.value = data;
            if (tempoInput) tempoInput.value = '';

            // Desabilitar controles principais durante o fluxo
            document.querySelectorAll('.marcar-todos-btn, .marcar-todos-btn-ausente').forEach(b => { b.disabled = true; b.classList.add('opacity-60'); });
            document.querySelectorAll('.presenca-btn, .presenca-btn-ausencia, .editar-presenca-btn').forEach(b => { b.disabled = true; });

            // Abrir modal de justificativa
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'justificar-ausencia-modal' }));
        });
    });

    // Botão por aluno: marcar todo o turno como F (abre modal para justificativa e aplica em massa)
    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.marcar-turno-f-aluno-btn');
        if (!trigger) return;
        e.preventDefault();

        if (isBulkProcessing) return;
        isBulkProcessing = true;
        suppressReload = true;

        const alunoId = trigger.getAttribute('data-aluno-id');
        const data = trigger.getAttribute('data-data');
        const salaId = trigger.getAttribute('data-sala-id');

        // Coletar TODOS os tempos válidos do aluno na data (mesmo sem nada lançado)
        // União: novos F (presenca-btn-ausencia) + editar P→F (editar-presenca-btn)
        const absenceButtons = Array.from(document.querySelectorAll(`.presenca-btn-ausencia[data-aluno-id="${alunoId}"][data-data="${data}"]`))
            .filter(btn => !btn.hasAttribute('disabled'));
        const editButtonsF = Array.from(document.querySelectorAll(`.editar-presenca-btn[data-aluno-id="${alunoId}"][data-data="${data}"]`))
            .filter(btn => !btn.hasAttribute('disabled'));
        const targetButtons = [...absenceButtons, ...editButtonsF];
        const tempos = Array.from(new Set(targetButtons
            .map(btn => btn.dataset.tempoAula || btn.getAttribute('data-tempo-aula'))
            .filter(Boolean)));

        if (tempos.length === 0) {
            alertSystem.warning('Nenhum tempo de aula disponível para este aluno nesta data.');
            isBulkProcessing = false;
            suppressReload = false;
            return;
        }

        // Mapear slots e adicionar indicador visual de processamento
        // Agrupar TODOS os slots por tempo e atualizar todos com indicador "Processando..."
        const tempoToSlotsF = new Map();
        targetButtons.forEach(btn => {
            const tempo = btn.dataset.tempoAula || btn.getAttribute('data-tempo-aula');
            const slotContainer = btn.parentElement;
            if (!tempoToSlotsF.has(tempo)) tempoToSlotsF.set(tempo, []);
            tempoToSlotsF.get(tempo).push(slotContainer);
        });
        tempoToSlotsF.forEach(slotArr => {
            slotArr.forEach(slotContainer => {
                if (!slotContainer) return;
                if (!slotContainer.hasAttribute('data-original-content')) {
                    const originalContent = slotContainer.innerHTML;
                    slotContainer.setAttribute('data-original-content', originalContent);
                }
                slotContainer.innerHTML = `
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 animate-pulse">
                        <i class="fas fa-spinner fa-spin mr-1"></i>Processando...
                    </span>
                `;
            });
        });
        const slotsMapF = Array.from(tempoToSlotsF, ([tempo, slots]) => ({ tempo, slots }));

        // Preparar contexto e abrir modal para justificativa única
        massAlunoFContext = { alunoId, data, salaId, tempos, slotsMap: slotsMapF };
        // Preencher campos básicos do modal (tempo pode ficar vazio pois será iterado)
        document.getElementById('justificativa_aluno_id').value = alunoId;
        document.getElementById('justificativa_data').value = data;
        document.getElementById('justificativa_tempo_aula').value = '';
        // Abrir modal padrão via evento
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'justificar-ausencia-modal' }));
    });

    // Botão por aluno: marcar todo o turno como P (processamento direto sem justificativa)
    document.addEventListener('click', async function(e) {
        const trigger = e.target.closest('.marcar-turno-p-aluno-btn');
        if (!trigger) return;
        e.preventDefault();

        if (isBulkProcessing) return;
        isBulkProcessing = true;
        suppressReload = true;

        const alunoId = trigger.getAttribute('data-aluno-id');
        const data = trigger.getAttribute('data-data');
        const salaId = trigger.getAttribute('data-sala-id');

        // Coletar TODOS os tempos válidos (mesmo sem nada lançado): union de F→P e P
        const pButtons = Array.from(document.querySelectorAll(`.trocar-para-p-btn[data-aluno-id="${alunoId}"][data-data="${data}"]`));
        const presencaButtons = Array.from(document.querySelectorAll(`.presenca-btn[data-aluno-id="${alunoId}"][data-data="${data}"]`)).filter(btn => !btn.hasAttribute('disabled'));
        const targetButtons = [...pButtons, ...presencaButtons];
        const tempos = Array.from(new Set(targetButtons.map(btn => btn.getAttribute('data-tempo-aula') || btn.dataset.tempoAula).filter(Boolean)));

        if (tempos.length === 0) {
            alertSystem.warning('Nenhum tempo de aula disponível para este aluno nesta data.');
            isBulkProcessing = false;
            suppressReload = false;
            return;
        }

        // Mapear slots e adicionar indicador visual de processamento
        // Agrupar TODOS os slots por tempo e atualizar todos com indicador "Processando..."
        const tempoToSlotsP = new Map();
        targetButtons.forEach(btn => {
            const tempo = btn.getAttribute('data-tempo-aula') || btn.dataset.tempoAula;
            const slotContainer = btn.parentElement;
            if (!tempoToSlotsP.has(tempo)) tempoToSlotsP.set(tempo, []);
            tempoToSlotsP.get(tempo).push(slotContainer);
        });
        tempoToSlotsP.forEach(slotArr => {
            slotArr.forEach(slotContainer => {
                if (!slotContainer) return;
                if (!slotContainer.hasAttribute('data-original-content')) {
                    const originalContent = slotContainer.innerHTML;
                    slotContainer.setAttribute('data-original-content', originalContent);
                }
                slotContainer.innerHTML = `
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 animate-pulse">
                        <i class="fas fa-spinner fa-spin mr-1"></i>Processando...
                    </span>
                `;
            });
        });
        const slotsMapP = Array.from(tempoToSlotsP, ([tempo, slots]) => ({ tempo, slots }));

        massAlunoPContext = { alunoId, data, salaId, tempos, slotsMap: slotsMapP };

        try {
            let processedCount = 0;
            const totalCount = tempos.length;

            for (const t of tempos) {
                const slotEntry = (massAlunoPContext && massAlunoPContext.slotsMap)
                    ? massAlunoPContext.slotsMap.find(x => String(x.tempo) === String(t))
                    : null;
                const slotsArr = slotEntry ? slotEntry.slots : [];

                slotsArr.forEach(slotContainer => {
                    if (slotContainer) {
                        slotContainer.innerHTML = `
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                <i class="fas fa-spinner fa-spin mr-1"></i>Salvando...
                            </span>
                        `;
                    }
                });

                const response = await fetch('{{ route("presencas.store.individual") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        aluno_id: alunoId,
                        data: data,
                        presente: true,
                        tempo_aula: parseInt(t),
                        escola_id: {{ auth()->user()->escola_id ?? 'null' }},
                        sala_id: salaId || getSalaIdFor(alunoId, data)
                    })
                });
                const resp = await response.json();
                if (!resp.success) throw new Error(resp.message || 'Falha ao registrar presença');

                // Atualizar UI para P com botão de editar (P→F)
                slotsArr.forEach(slotContainer => {
                    if (slotContainer) {
                        const isDesktopRow = !!slotContainer.closest('.md\\:table-row');
                        slotContainer.innerHTML = `
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-green-100 text-green-800 border border-green-200">
                                <i class="fas fa-check mr-1"></i>P
                            </span>
                            <button type="button"
                                class="editar-presenca-btn ${isDesktopRow ? 'ml-1' : 'mt-1'} inline-flex items-center px-${isDesktopRow ? '1.5' : '2'} py-${isDesktopRow ? '0.5' : '1'} rounded text-[10px] font-medium bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-red-500"
                                title="Trocar para F"
                                data-aluno-id="${alunoId}"
                                data-data="${data}"
                                data-tempo-aula="${t}">
                                <i class="fas fa-exchange-alt mr-1"></i>F
                            </button>
                        `;
                    }
                });

                processedCount++;
            }

            const successMsg = document.createElement('div');
            successMsg.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50';
            successMsg.innerHTML = `<i class="fas fa-check-circle mr-2"></i>Turno marcado como P! (${processedCount}/${totalCount} tempos processados)`;
            document.body.appendChild(successMsg);
            setTimeout(() => { successMsg.remove(); }, 1500);
        } catch (error) {
            console.error('Erro no processamento em massa por aluno (P):', error);
            // Restaurar conteúdo original em caso de erro
            if (massAlunoPContext && massAlunoPContext.slotsMap) {
                massAlunoPContext.slotsMap.forEach(entry => {
                    const slotsArr = entry.slots || [];
                    slotsArr.forEach(slotContainer => {
                        if (slotContainer && slotContainer.hasAttribute('data-original-content')) {
                            slotContainer.innerHTML = slotContainer.getAttribute('data-original-content');
                            slotContainer.removeAttribute('data-original-content');
                        }
                    });
                });
            }
            alertSystem.error('Erro ao marcar o turno como P para este aluno.');
        } finally {
            massAlunoPContext = null;
            isBulkProcessing = false;
            suppressReload = false;
        }
    });
    // Helper para cancelar o modal e restaurar slots em massa (se houver)
    window.cancelJustificativaModal = function() {
        // Fechar o modal padrão via evento (evita conflitos de showModal/closeModal)
        window.dispatchEvent(new CustomEvent('close-modal'));
        // Limpar texto
        const j = document.getElementById('justificativa');
        if (j) j.value = '';
        // Se houve contexto de operação em massa (F), restaurar slots
        if (massAlunoFContext && massAlunoFContext.slotsMap) {
            massAlunoFContext.slotsMap.forEach(entry => {
                const slotsArr = entry.slots || [];
                slotsArr.forEach(slotContainer => {
                    if (slotContainer && slotContainer.hasAttribute('data-original-content')) {
                        slotContainer.innerHTML = slotContainer.getAttribute('data-original-content');
                        slotContainer.removeAttribute('data-original-content');
                    }
                });
            });
        }
        // Restaurar slots do contexto por dia, se existir
        if (massDiaFContext && massDiaFContext.items) {
            massDiaFContext.items.forEach(item => {
                const slotContainer = item.slotContainer;
                if (slotContainer && slotContainer.hasAttribute('data-original-content')) {
                    slotContainer.innerHTML = slotContainer.getAttribute('data-original-content');
                    slotContainer.removeAttribute('data-original-content');
                }
            });
        }
        // Liberar flags/contexto
        massAlunoFContext = null;
        massDiaFContext = null;
        isBulkProcessing = false;
        suppressReload = false;
        // Reabilitar controles
        document.querySelectorAll('.marcar-todos-btn, .marcar-todos-btn-ausente').forEach(b => { b.disabled = false; b.classList.remove('opacity-60'); });
        document.querySelectorAll('.presenca-btn, .presenca-btn-ausencia, .editar-presenca-btn').forEach(b => { b.disabled = false; });
    };
    // Limpeza automática ao fechar qualquer modal (escape/overlay/X)
    window.addEventListener('close-modal', function() {
        const j = document.getElementById('justificativa');
        if (j) j.value = '';
        if (massAlunoFContext && massAlunoFContext.slotsMap) {
            massAlunoFContext.slotsMap.forEach(entry => {
                const slotsArr = entry.slots || [];
                slotsArr.forEach(slotContainer => {
                    if (slotContainer && slotContainer.hasAttribute('data-original-content')) {
                        slotContainer.innerHTML = slotContainer.getAttribute('data-original-content');
                        slotContainer.removeAttribute('data-original-content');
                    }
                });
            });
            massAlunoFContext = null;
            isBulkProcessing = false;
            suppressReload = false;
        }
        if (massDiaFContext && massDiaFContext.items) {
            massDiaFContext.items.forEach(item => {
                const slotContainer = item.slotContainer;
                if (slotContainer && slotContainer.hasAttribute('data-original-content')) {
                    slotContainer.innerHTML = slotContainer.getAttribute('data-original-content');
                    slotContainer.removeAttribute('data-original-content');
                }
            });
            massDiaFContext = null;
            isBulkProcessing = false;
            suppressReload = false;
            document.querySelectorAll('.marcar-todos-btn, .marcar-todos-btn-ausente').forEach(b => { b.disabled = false; b.classList.remove('opacity-60'); });
            document.querySelectorAll('.presenca-btn, .presenca-btn-ausencia, .editar-presenca-btn').forEach(b => { b.disabled = false; });
        }
    });
    
    // Adicionar event listeners para os botões de ausência com justificativa
    document.querySelectorAll('.presenca-btn-ausencia').forEach(button => {
        button.addEventListener('click', function() {
            const alunoId = this.dataset.alunoId;
            const data = this.dataset.data;
            const tempoAula = this.dataset.tempoAula || this.getAttribute('data-tempo-aula');
            
            // Preencher o modal com os dados
            document.getElementById('justificativa_aluno_id').value = alunoId;
            document.getElementById('justificativa_data').value = data;
            document.getElementById('justificativa_tempo_aula').value = tempoAula;
            
            // Abrir o modal padrão via evento (evita conflito com outras definições showModal)
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'justificar-ausencia-modal' }));
        });
    });

    // Adicionar event listeners para editar de P -> F (abre o mesmo modal)
    document.querySelectorAll('.editar-presenca-btn').forEach(button => {
        button.addEventListener('click', function() {
            const alunoId = this.dataset.alunoId;
            const data = this.dataset.data;
            const tempoAula = this.dataset.tempoAula || this.getAttribute('data-tempo-aula');

            // Preencher o modal com os dados
            document.getElementById('justificativa_aluno_id').value = alunoId;
            document.getElementById('justificativa_data').value = data;
            document.getElementById('justificativa_tempo_aula').value = tempoAula;

            // Abrir o modal padrão via evento (evita conflito com outras definições showModal)
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'justificar-ausencia-modal' }));
        });
    });
    
    // Salvar justificativa (suporta fluxo individual e em massa por aluno)
    document.getElementById('salvarJustificativa').addEventListener('click', async function() {
        const alunoId = document.getElementById('justificativa_aluno_id').value;
        const data = document.getElementById('justificativa_data').value;
        const justificativa = document.getElementById('justificativa').value;
        const tempoAula = document.getElementById('justificativa_tempo_aula').value;
        
            if (!justificativa.trim()) {
            alertSystem.validation('Por favor, informe o motivo da ausência.');
            return;
        }
        
        // Caso seja uma operação em massa por DIA, iterar sobre todos os itens coletados
        if (massDiaFContext && massDiaFContext.data == data) {
            try {
                let processedCount = 0;
                const totalCount = massDiaFContext.items.length;

                for (const item of massDiaFContext.items) {
                    const { alunoId: alunoIdItem, tempo, slotContainer } = item;
                    if (slotContainer) {
                        slotContainer.innerHTML = `
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                <i class="fas fa-spinner fa-spin mr-1"></i>Salvando...
                            </span>
                        `;
                    }
                    const response = await fetch('{{ route("presencas.store.individual") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            aluno_id: alunoIdItem,
                            data: data,
                            presente: false,
                            tempo_aula: parseInt(tempo),
                            justificativa: justificativa,
                            escola_id: {{ auth()->user()->escola_id ?? 'null' }},
                            sala_id: massDiaFContext.salaId || getSalaIdFor(alunoIdItem, data)
                        })
                    });
                    const resp = await response.json();
                    if (!resp.success) throw new Error(resp.message || 'Falha ao registrar ausência');

                    if (slotContainer) {
                        slotContainer.innerHTML = `
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-red-100 text-red-800 border border-red-200">
                                <i class="fas fa-times mr-1"></i>F
                            </span>
                        `;
                    }
                    processedCount++;
                }

                window.dispatchEvent(new CustomEvent('close-modal'));
                const successMsg = document.createElement('div');
                successMsg.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50';
                successMsg.innerHTML = `<i class="fas fa-check-circle mr-2"></i>Todos marcados como F! (${processedCount}/${totalCount})`;
                document.body.appendChild(successMsg);
                setTimeout(() => { successMsg.remove(); }, 1500);
            } catch (error) {
                console.error('Erro no processamento em massa por dia (F):', error);
                if (massDiaFContext && massDiaFContext.items) {
                    massDiaFContext.items.forEach(item => {
                        const slotContainer = item.slotContainer;
                        if (slotContainer && slotContainer.hasAttribute('data-original-content')) {
                            slotContainer.innerHTML = slotContainer.getAttribute('data-original-content');
                            slotContainer.removeAttribute('data-original-content');
                        }
                    });
                }
                alertSystem.error('Erro ao marcar todos como F nesta data.');
            } finally {
                massDiaFContext = null;
                isBulkProcessing = false;
                suppressReload = false;
                document.querySelectorAll('.marcar-todos-btn, .marcar-todos-btn-ausente').forEach(b => { b.disabled = false; b.classList.remove('opacity-60'); });
                document.querySelectorAll('.presenca-btn, .presenca-btn-ausencia, .editar-presenca-btn').forEach(b => { b.disabled = false; });
            }
            return;
        }

        // Caso seja uma operação em massa, iterar sobre todos os tempos válidos do aluno
        if (massAlunoFContext && massAlunoFContext.alunoId == alunoId && massAlunoFContext.data == data) {
            try {
                let processedCount = 0;
                const totalCount = massAlunoFContext.tempos.length;
                
                for (const t of massAlunoFContext.tempos) {
                    // Atualizar indicador visual do slot específico
                    const slotEntry = massAlunoFContext.slotsMap.find(x => String(x.tempo) === String(t));
                    const slotsArr = slotEntry ? slotEntry.slots : [];
                    slotsArr.forEach(slotContainer => {
                        if (slotContainer) {
                            slotContainer.innerHTML = `
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                    <i class="fas fa-spinner fa-spin mr-1"></i>Salvando...
                                </span>
                            `;
                        }
                    });
                    const response = await fetch('{{ route("presencas.store.individual") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            aluno_id: alunoId,
                            data: data,
                            presente: false,
                            tempo_aula: parseInt(t),
                            justificativa: justificativa,
                            escola_id: {{ auth()->user()->escola_id ?? 'null' }},
                            sala_id: (massAlunoFContext && massAlunoFContext.salaId) ? massAlunoFContext.salaId : getSalaIdFor(alunoId, data)
                        })
                    });
                    const resp = await response.json();
                    if (!resp.success) throw new Error(resp.message || 'Falha ao registrar ausência');
                    
                    // Atualizar slot com sucesso
                    slotsArr.forEach(slotContainer => {
                        if (slotContainer) {
                            slotContainer.innerHTML = `
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-red-100 text-red-800 border border-red-200">
                                    <i class="fas fa-times mr-1"></i>F
                                </span>
                            `;
                        }
                    });
                    
                    processedCount++;
                }

                // Finalizar: fechar modal e notificar sem recarregar
                window.dispatchEvent(new CustomEvent('close-modal'));
                const successMsg = document.createElement('div');
                successMsg.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50';
                successMsg.innerHTML = `<i class="fas fa-check-circle mr-2"></i>Turno marcado como F! (${processedCount}/${totalCount} tempos processados)`;
                document.body.appendChild(successMsg);
                setTimeout(() => { successMsg.remove(); }, 1500);
            } catch (error) {
                console.error('Erro no processamento em massa por aluno:', error);
                
                // Restaurar conteúdo original dos slots em caso de erro
                if (massAlunoFContext && massAlunoFContext.slotsMap) {
                    massAlunoFContext.slotsMap.forEach(entry => {
                        const slotsArr = entry.slots || [];
                        slotsArr.forEach(slotContainer => {
                            if (slotContainer && slotContainer.hasAttribute('data-original-content')) {
                                slotContainer.innerHTML = slotContainer.getAttribute('data-original-content');
                                slotContainer.removeAttribute('data-original-content');
                            }
                        });
                    });
                }
                
                alertSystem.error('Erro ao marcar o turno como F para este aluno.');
            } finally {
                massAlunoFContext = null;
                isBulkProcessing = false;
                suppressReload = false;
            }
            return; // Não seguir o fluxo individual
        }

        // Fluxo individual: Fazer requisição AJAX para registrar ausência com justificativa
        try {
            const response = await fetch('{{ route("presencas.store.individual") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    aluno_id: alunoId,
                    data: data,
                    presente: false,
                    tempo_aula: tempoAula ? parseInt(tempoAula) : null,
                    justificativa: justificativa,
                    escola_id: {{ auth()->user()->escola_id ?? 'null' }},
                    sala_id: getSalaIdFor(alunoId, data)
                })
            });
            const dataResp = await response.json();
            if (dataResp.success) {
                // Fechar o modal
                window.dispatchEvent(new CustomEvent('close-modal'));
                
                // Atualizar a interface minimamente e recarregar depois
                const btn = document.querySelector(`.presenca-btn-ausencia[data-aluno-id="${alunoId}"][data-data="${data}"][data-tempo-aula="${tempoAula}"]`);
                if (btn) {
                    const slotContainer = btn.parentElement;
                    if (slotContainer) {
                        slotContainer.innerHTML = `
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-red-100 text-red-800 border border-red-200">
                                <i class="fas fa-times mr-1"></i>F
                            </span>
                        `;
                    }
                }
                
                // Mostrar mensagem de sucesso
                const successMsg = document.createElement('div');
                successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                successMsg.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Ausência justificada com sucesso!';
                document.body.appendChild(successMsg);
                
                // Recarregar a página para mostrar todas as atualizações corretamente
                setTimeout(() => {
                    successMsg.remove();
                    window.location.reload();
                }, 1500);
            } else {
                alertSystem.error('Erro ao registrar ausência: ' + (dataResp.message || 'Erro desconhecido'));
            }
        } catch (error) {
            console.error('Erro:', error);
            alertSystem.error('Erro ao registrar ausência');
        }
    });

    // Handler para botão "Trocar para P" (F → P sem justificativa)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.trocar-para-p-btn')) {
            e.preventDefault();
            
            const btn = e.target.closest('.trocar-para-p-btn');
            const alunoId = btn.getAttribute('data-aluno-id');
            const data = btn.getAttribute('data-data');
            const tempoAula = btn.getAttribute('data-tempo-aula');
            
            // Verificar se já está processando
            if (btn.disabled || isBulkProcessing) {
                return;
            }
            
            // Desabilitar o botão durante o processamento
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>...';
            
            // Fazer requisição AJAX para registrar presença
            fetch('{{ route("presencas.store.individual") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    aluno_id: alunoId,
                    data: data,
                    presente: true,
                    tempo_aula: tempoAula ? parseInt(tempoAula) : null,
                    escola_id: {{ auth()->user()->escola_id ?? 'null' }},
                    sala_id: getSalaIdFor(alunoId, data)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar a interface
                    const slotContainer = btn.parentElement;
                    if (slotContainer) {
                        slotContainer.innerHTML = `
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[11px] font-medium bg-green-100 text-green-800 border border-green-200">
                                <i class="fas fa-check mr-1"></i>P
                            </span>
                            <button type="button"
                                class="editar-presenca-btn ${slotContainer.closest('.md\\:table-row') ? 'ml-1' : 'mt-1'} inline-flex items-center px-${slotContainer.closest('.md\\:table-row') ? '1.5' : '2'} py-${slotContainer.closest('.md\\:table-row') ? '0.5' : '1'} rounded text-[10px] font-medium bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 hover:border-red-300 transition-all duration-150 focus:outline-none focus:ring-1 focus:ring-red-500"
                                title="Trocar para F"
                                data-aluno-id="${alunoId}"
                                data-data="${data}"
                                data-tempo-aula="${tempoAula}">
                                <i class="fas fa-exchange-alt mr-1"></i>F
                            </button>
                        `;
                    }
                    
                    // Mostrar mensagem de sucesso
                    const successMsg = document.createElement('div');
                    successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                    successMsg.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Presença registrada com sucesso!';
                    document.body.appendChild(successMsg);
                    
                    setTimeout(() => {
                        successMsg.remove();
                    }, 3000);
                } else {
                    // Reabilitar o botão em caso de erro
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i>P';
                    alertSystem.error('Erro ao registrar presença: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                // Reabilitar o botão em caso de erro
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exchange-alt mr-1"></i>P';
                alertSystem.error('Erro ao registrar presença');
            });
        }
    });

    // Reanexar handlers após atualização AJAX do conteúdo das tabelas
    function bindLancarScopedHandlers(root) {
        if (!root) return;
        // Botões individuais de presença (P)
        root.querySelectorAll('.presenca-btn:not([data-bound])').forEach(button => {
            button.setAttribute('data-bound', '1');
            button.addEventListener('click', async function() {
                if (isBulkProcessing) return;
                const alunoId = this.dataset.alunoId;
                const data = this.dataset.data;
                const presente = this.dataset.presente === '1';
                const tempoAula = parseInt(this.dataset.tempoAula || this.getAttribute('data-tempo-aula'));
                const salaId = this.dataset.salaId || getSalaIdFor(alunoId, data);
                const slotContainer = this.parentElement;
                try {
                    await registrarPresenca({
                        alunoId,
                        data,
                        presente,
                        tempoAula,
                        escolaId: {{ auth()->user()->escola_id ?? 'null' }},
                        salaId,
                        updateSlotEl: slotContainer
                    });
                    const successMsg = document.createElement('div');
                    successMsg.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50';
                    successMsg.textContent = 'Presença registrada com sucesso!';
                    document.body.appendChild(successMsg);
                    setTimeout(() => {
                        successMsg.remove();
            if (!suppressReload) {
                        // window.location.reload(); // evitar reload; UI já foi atualizada
                    }
                    }, 1200);
                } catch (error) {
                    console.error('Erro:', error);
                    alertSystem.error('Erro ao registrar presença');
                }
            });
        });

        // Botões de ausência com justificativa (abre modal)
        root.querySelectorAll('.presenca-btn-ausencia:not([data-bound])').forEach(button => {
            button.setAttribute('data-bound', '1');
            button.addEventListener('click', function() {
                const alunoId = this.dataset.alunoId;
                const data = this.dataset.data;
                const tempoAula = this.dataset.tempoAula || this.getAttribute('data-tempo-aula');
                document.getElementById('justificativa_aluno_id').value = alunoId;
                document.getElementById('justificativa_data').value = data;
                document.getElementById('justificativa_tempo_aula').value = tempoAula;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'justificar-ausencia-modal' }));
            });
        });

        // Botões editar P->F (abre modal)
        root.querySelectorAll('.editar-presenca-btn:not([data-bound])').forEach(button => {
            button.setAttribute('data-bound', '1');
            button.addEventListener('click', function() {
                const alunoId = this.dataset.alunoId;
                const data = this.dataset.data;
                const tempoAula = this.dataset.tempoAula || this.getAttribute('data-tempo-aula');
                document.getElementById('justificativa_aluno_id').value = alunoId;
                document.getElementById('justificativa_data').value = data;
                document.getElementById('justificativa_tempo_aula').value = tempoAula;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'justificar-ausencia-modal' }));
            });
        });
    }

    const lancarContainer = document.getElementById('lancarTablesContainer');
    if (lancarContainer) {
        const observer = new MutationObserver((mutations) => {
            // Reanexa handlers quando o conteúdo é substituído
            bindLancarScopedHandlers(lancarContainer);
        });
        observer.observe(lancarContainer, { childList: true, subtree: true });
    }
});
</script>
@endsection