@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-card>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Detalhes da Escala</h2>
            <div class="flex space-x-2">
                <x-button href="{{ route('escalas.edit', $escala->id) }}" color="warning">
                    <i class="fas fa-edit mr-1"></i> Editar
                </x-button>
                <x-button href="{{ route('escalas.index') }}" color="secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Informações Básicas -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-calendar-alt mr-2"></i>Informações da Escala
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Funcionário</label>
                        <p class="text-gray-900">{{ $escala->funcionario->nome }} {{ $escala->funcionario->sobrenome }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Data</label>
                        <p class="text-gray-900">{{ $escala->data_formatada ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Dia da Semana</label>
                        <p class="text-gray-900">
                            @if($escala->data)
                                @php
                                    $diasSemana = [
                                        'Sunday' => 'Domingo',
                                        'Monday' => 'Segunda-feira',
                                        'Tuesday' => 'Terça-feira',
                                        'Wednesday' => 'Quarta-feira',
                                        'Thursday' => 'Quinta-feira',
                                        'Friday' => 'Sexta-feira',
                                        'Saturday' => 'Sábado'
                                    ];
                                @endphp
                                {{ $diasSemana[$escala->data->format('l')] ?? $escala->data->format('l') }}
                            @else
                                Não informado
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Tipo de Atividade</label>
                        @php
                            $atividadeLabels = [
                                'em_sala' => 'Em Sala',
                                'pl' => 'PL (Planejamento)',
                                'ausente' => 'Ausente',
                            ];
                            $atividadeClasses = [
                                'em_sala' => 'bg-green-100 text-green-800',
                                'pl' => 'bg-purple-100 text-purple-800',
                                'ausente' => 'bg-gray-100 text-gray-800',
                            ][$escala->tipo_atividade] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $atividadeClasses }}">
                            {{ $atividadeLabels[$escala->tipo_atividade] ?? $escala->tipo_atividade }}
                        </span>
                    </div>
                    @if($escala->sala)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Sala</label>
                        <div class="flex items-center mt-1">
                            <div class="w-8 h-8 rounded bg-indigo-100 flex items-center justify-center text-indigo-500 mr-3">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $escala->sala->codigo }}</div>
                                <div class="text-gray-500 text-sm">{{ $escala->sala->nome }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Horários -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-clock mr-2"></i>Horários
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Hora de Início</label>
                        <p class="text-gray-900">{{ $escala->hora_inicio ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Hora de Fim</label>
                        <p class="text-gray-900">{{ $escala->hora_fim ?? 'Não informado' }}</p>
                    </div>
                    @if($escala->hora_inicio && $escala->hora_fim)
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Carga Horária</label>
                        @php
                            $inicio = \Carbon\Carbon::createFromFormat('H:i', $escala->hora_inicio);
                            $fim = \Carbon\Carbon::createFromFormat('H:i', $escala->hora_fim);
                            $cargaHoraria = $inicio->diffInHours($fim);
                            $minutos = $inicio->diffInMinutes($fim) % 60;
                        @endphp
                        <p class="text-gray-900">{{ $cargaHoraria }}h{{ $minutos > 0 ? ' ' . $minutos . 'min' : '' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informações do Funcionário -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-user-tie mr-2"></i>Dados do Funcionário
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Cargo</label>
                        <p class="text-gray-900">{{ $escala->funcionario->cargo ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Departamento</label>
                        <p class="text-gray-900">{{ $escala->funcionario->departamento ?? 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Telefone</label>
                        <p class="text-gray-900">{{ $escala->funcionario->telefone ?? 'Não informado' }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="block text-sm font-medium text-gray-600">Ver Funcionário:</label>
                        <x-button href="{{ route('funcionarios.show', $escala->funcionario->id) }}" color="primary" size="sm">
                            <i class="fas fa-eye mr-1"></i> Detalhes
                        </x-button>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-info-circle mr-2"></i>Status
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Status da Escala</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $escala->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas {{ $escala->ativo ? 'fa-check' : 'fa-times' }} mr-1"></i>
                            {{ $escala->ativo ? 'Ativa' : 'Inativa' }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Data de Criação</label>
                        <p class="text-gray-900">{{ $escala->created_at ? $escala->created_at->format('d/m/Y H:i') : 'Não informado' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Última Atualização</label>
                        <p class="text-gray-900">{{ $escala->updated_at ? $escala->updated_at->format('d/m/Y H:i') : 'Não informado' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Observações -->
        @if($escala->observacoes)
        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-sticky-note mr-2"></i>Observações
            </h3>
            <p class="text-gray-900">{{ $escala->observacoes }}</p>
        </div>
        @endif

        <!-- Presenças Relacionadas -->
        <div class="mt-6 bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-clipboard-check mr-2"></i>Presenças Registradas
            </h3>
            @if($escala->presencas && $escala->presencas->count() > 0)
                <div class="space-y-2">
                    @foreach($escala->presencas as $presenca)
                    <div class="flex items-center justify-between p-3 bg-white rounded border">
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ $presenca->data ? $presenca->data->format('d/m/Y') : 'Data não informada' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Entrada: {{ $presenca->hora_entrada ?? 'Não registrada' }} | 
                                Saída: {{ $presenca->hora_saida ?? 'Não registrada' }}
                            </p>
                        </div>
                        <x-button href="{{ route('presencas.show', [
                            'sala_id' => optional($escala->sala)->id,
                            'data' => optional($presenca->data)->format('Y-m-d')
                        ]) }}" color="primary" size="sm">
                            <i class="fas fa-eye"></i>
                        </x-button>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 italic">Nenhuma presença registrada para esta escala</p>
            @endif
        </div>
    </x-card>
</div>
@endsection