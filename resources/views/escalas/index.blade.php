@extends('layouts.app')

@section('content')
    <x-card>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Escalas</h1>
                <p class="mt-1 text-sm text-gray-600">Gerenciamento de escalas de funcionários</p>
            </div>
            <div class="flex space-x-2">
                <x-button href="{{ route('escalas.create') }}" color="primary">
                    <i class="fas fa-plus mr-1"></i> Nova Escala
                </x-button>
            </div>
        </div>

        <x-collapsible-filter 
            title="Filtros de Escalas" 
            :action="route('escalas.index')" 
            :clear-route="route('escalas.index')"
        >
            <x-filter-field 
                title="Periodo"
                name="funcionario_id" 
                label="Funcionário" 
                type="select"
                empty-option="Todos os funcionários"
                :options="$funcionarios->pluck('nome', 'id')->map(function($nome, $id) use ($funcionarios) {
                    $funcionario = $funcionarios->find($id);
                    return $funcionario->nome . ' ' . $funcionario->sobrenome;
                })"
            />
            
            <x-date-filter-with-arrows 
                name="data_inicio" 
                label="Data Início" 
                :value="request('data_inicio', now()->startOfMonth()->format('Y-m-d'))" 
                data-fim-name="data_fim"
                :data-fim-value="request('data_fim', now()->endOfMonth()->format('Y-m-d'))" 
            />
            
            <x-filter-field 
                name="tipo_escala" 
                label="Tipo de Escala" 
                type="select"
                empty-option="Todos os tipos"
                :options="['Normal' => 'Normal', 'Extra' => 'Extra', 'Substituição' => 'Substituição']"
            />
            
            <x-filter-field 
                name="status" 
                label="Status" 
                type="select"
                empty-option="Todos os status"
                :options="['Agendada' => 'Agendada', 'Ativa' => 'Ativa', 'Concluída' => 'Concluída']"
            />
            
            <x-filter-field 
                name="tipo_atividade" 
                label="Tipo de Atividade" 
                type="select"
                empty-option="Todos os tipos"
                :options="['em_sala' => 'Em Sala', 'pl' => 'PL (Planejamento)', 'ausente' => 'Ausente']"
            />
            
            <x-filter-field 
                name="sala_id" 
                label="Sala" 
                type="select"
                empty-option="Todas as salas"
                :options="$salas->pluck('nome_completo', 'id')"
            />
        </x-collapsible-filter>

        <!-- Desktop Table View -->
        <div class="md:block">
            <x-table :headers="['Funcionário', 'Data', 'Horários', 'Total Horas', 'Ações']" :actions="false">
                @forelse($escalasAgrupadas as $index => $grupo)
                    <x-table-row :striped="true" :index="$index">
                        <x-table-cell>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-purple-500 mr-3">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $grupo->funcionario->nome }} {{ $grupo->funcionario->sobrenome }}</div>
                                    <div class="text-gray-500 text-xs">{{ $grupo->funcionario->cargo }}</div>
                                </div>
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="font-medium text-gray-900">{{ $grupo->data->format('d/m/Y') }}</div>
                            <div class="text-gray-500 text-xs">
                                @php
                                    $diasSemana = [
                                        'Sunday' => 'Domingo',
                                        'Monday' => 'Segunda',
                                        'Tuesday' => 'Terça',
                                        'Wednesday' => 'Quarta',
                                        'Thursday' => 'Quinta',
                                        'Friday' => 'Sexta',
                                        'Saturday' => 'Sábado'
                                    ];
                                @endphp
                                {{ $diasSemana[$grupo->data->format('l')] }}
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="space-y-2">
                                @foreach($grupo->escalas as $escala)
                                    <div class="flex items-center space-x-2 p-2 rounded-lg border {{ $escala->tipo_atividade === 'ausente' ? 'border-red-200 bg-red-50' : ($escala->tipo_atividade === 'pl' ? 'border-purple-200 bg-purple-50' : 'border-green-200 bg-green-50') }}">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium text-sm">
                                                    {{ \Carbon\Carbon::parse($escala->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($escala->hora_fim)->format('H:i') }}
                                                </span>
                                                @php
                                                    $atividadeClasses = [
                                                        'em_sala' => 'bg-green-500',
                                                        'pl' => 'bg-purple-500',
                                                        'ausente' => 'bg-red-500',
                                                    ][$escala->tipo_atividade] ?? 'bg-gray-500';
                                                @endphp
                                                <div class="w-3 h-3 rounded-full {{ $atividadeClasses }}" title="{{ ucfirst($escala->tipo_atividade) }}"></div>
                                            </div>
                                            <div class="text-xs text-gray-600 mt-1">
                                                @if($escala->sala)
                                                    <i class="fas fa-door-open mr-1"></i>{{ $escala->sala->codigo }}
                                                @else
                                                    <i class="fas fa-minus mr-1"></i>Sem sala
                                                @endif
                                                <span class="ml-2">
                                                    <i class="fas fa-tag mr-1"></i>{{ $escala->tipo_escala }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex space-x-1">
                                            <a href="{{ route('escalas.show', $escala->id) }}" class="text-indigo-600 hover:text-indigo-900 text-xs" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('escalas.edit', $escala->id) }}" class="text-yellow-600 hover:text-yellow-900 text-xs" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('escalas.destroy', $escala->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta escala?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-table-cell>
                        <x-table-cell>
                            <div class="text-center">
                                <span class="font-semibold text-lg text-gray-900">{{ number_format($grupo->total_horas, 1) }}h</span>
                                <div class="text-xs text-gray-500">{{ $grupo->escalas->count() }} período(s)</div>
                            </div>
                        </x-table-cell>
                        <x-table-cell align="right">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('escalas.create') }}?funcionario_id={{ $grupo->funcionario->id }}&data={{ $grupo->data->format('Y-m-d') }}" class="text-green-600 hover:text-green-900" title="Adicionar Horário">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Nenhuma escala encontrada.
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        <!-- Mobile Card Layout -->
        <div class="md:hidden space-y-4">
            @forelse($escalasAgrupadas as $grupo)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <!-- Header do card com funcionário -->
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-500 mr-3">
                            <i class="fas fa-user-tie text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 text-base">{{ $grupo->funcionario->nome }} {{ $grupo->funcionario->sobrenome }}</h3>
                            <p class="text-sm text-gray-600">{{ $grupo->funcionario->cargo }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-purple-600">{{ number_format($grupo->total_horas, 1) }}h</div>
                            <div class="text-xs text-gray-500">{{ $grupo->escalas->count() }} período(s)</div>
                        </div>
                    </div>

                    <!-- Data e dia da semana -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg text-center">
                        <div class="text-lg font-semibold text-gray-900">{{ $grupo->data->format('d/m/Y') }}</div>
                        <div class="text-sm text-gray-600">
                            @php
                                $diasSemana = [
                                    'Sunday' => 'Domingo',
                                    'Monday' => 'Segunda',
                                    'Tuesday' => 'Terça',
                                    'Wednesday' => 'Quarta',
                                    'Thursday' => 'Quinta',
                                    'Friday' => 'Sexta',
                                    'Saturday' => 'Sábado'
                                ];
                            @endphp
                            {{ $diasSemana[$grupo->data->format('l')] }}
                        </div>
                    </div>

                    <!-- Lista de horários -->
                    <div class="space-y-3 mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock mr-1"></i>Horários do Dia
                        </h4>
                        @foreach($grupo->escalas as $escala)
                            <div class="p-3 rounded-lg border {{ $escala->tipo_atividade === 'ausente' ? 'border-red-200 bg-red-50' : ($escala->tipo_atividade === 'pl' ? 'border-purple-200 bg-purple-50' : 'border-green-200 bg-green-50') }}">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-medium text-base">
                                            {{ \Carbon\Carbon::parse($escala->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($escala->hora_fim)->format('H:i') }}
                                        </span>
                                        @php
                                            $atividadeClasses = [
                                                'em_sala' => 'bg-green-500',
                                                'pl' => 'bg-purple-500',
                                                'ausente' => 'bg-red-500',
                                            ][$escala->tipo_atividade] ?? 'bg-gray-500';
                                            $atividadeLabels = [
                                                'em_sala' => 'Em Sala',
                                                'pl' => 'Planejamento',
                                                'ausente' => 'Ausente',
                                            ];
                                        @endphp
                                        <div class="w-4 h-4 rounded-full {{ $atividadeClasses }}" title="{{ $atividadeLabels[$escala->tipo_atividade] ?? ucfirst($escala->tipo_atividade) }}"></div>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full {{ $escala->tipo_atividade === 'ausente' ? 'bg-red-100 text-red-700' : ($escala->tipo_atividade === 'pl' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700') }}">
                                        {{ $atividadeLabels[$escala->tipo_atividade] ?? ucfirst($escala->tipo_atividade) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm text-gray-600">
                                    <div class="flex items-center space-x-3">
                                        <span>
                                            @if($escala->sala)
                                                <i class="fas fa-door-open mr-1"></i>{{ $escala->sala->codigo }}
                                            @else
                                                <i class="fas fa-minus mr-1"></i>Sem sala
                                            @endif
                                        </span>
                                        <span>
                                            <i class="fas fa-tag mr-1"></i>{{ $escala->tipo_escala }}
                                        </span>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('escalas.show', $escala->id) }}" class="p-2 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('escalas.edit', $escala->id) }}" class="p-2 text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-lg transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('escalas.destroy', $escala->id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta escala?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Botão de adicionar horário -->
                    <div class="pt-3 border-t border-gray-200">
                        <a href="{{ route('escalas.create') }}?funcionario_id={{ $grupo->funcionario->id }}&data={{ $grupo->data->format('Y-m-d') }}" 
                           class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-lg font-medium text-sm min-h-[48px] flex items-center justify-center transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Adicionar Horário
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <i class="fas fa-calendar-times text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-600">Nenhuma escala encontrada.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $escalasAgrupadas->links('components.pagination') }}
        </div>
    </x-card>
@endsection