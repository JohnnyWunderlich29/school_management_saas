@extends('layouts.app')

@section('title', 'Template: ' . $template->nome_template)

@section('content')
<div class="w-full mx-auto">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['title' => 'Funcionários', 'url' => route('funcionarios.index')],
        ['title' => $funcionario->nome, 'url' => route('funcionarios.show', $funcionario)],
        ['title' => 'Templates', 'url' => route('funcionarios.templates.index', $funcionario)],
        ['title' => $template->nome_template]
    ]" />

    <x-card>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $template->nome_template }}</h2>
                <p class="mt-1 text-sm text-gray-600">Funcionário: <strong>{{ $funcionario->nome }}</strong></p>
            </div>
            <div class="flex space-x-2">
                @can('funcionarios.editar')
                    <x-button href="{{ route('funcionarios.templates.edit', [$funcionario, $template]) }}" color="warning">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </x-button>
                    
                    <form method="POST" action="{{ route('funcionarios.templates.toggle-ativo', [$funcionario, $template]) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <x-button type="submit" color="{{ $template->ativo ? 'warning' : 'success' }}">
                            <i class="fas {{ $template->ativo ? 'fa-pause' : 'fa-play' }} mr-1"></i>
                            {{ $template->ativo ? 'Desativar' : 'Ativar' }}
                        </x-button>
                    </form>
                @endcan
                
                <x-button href="{{ route('funcionarios.templates.index', $funcionario) }}" color="secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Voltar
                </x-button>
            </div>
        </div>

    <!-- Layout responsivo com grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Informações do Template -->
        <div class="lg:col-span-1">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-clipboard-list mr-2"></i>Informações do Template
                </h3>
                
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Status</label>
                        @if($template->ativo)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check mr-1"></i> Ativo
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times mr-1"></i> Inativo
                            </span>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Criado em</label>
                        <p class="text-gray-900">{{ $template->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    
                    @if($template->updated_at != $template->created_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-600">Última atualização</label>
                            <p class="text-gray-900">{{ $template->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                    
                    @php
                        $diasConfigurados = $template->getDiasConfigurados();
                    @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Dias configurados</label>
                        <div class="mt-1">
                            @if(count($diasConfigurados) > 0)
                                @foreach($diasConfigurados as $nomeDia => $dia)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1 mb-1">
                                        {{ ucfirst($nomeDia) }}
                                    </span>
                                @endforeach
                            @else
                                <p class="text-gray-900">Nenhum dia configurado</p>
                            @endif
                        </div>
                    </div>
                    
                    @can('escalas.criar')
                        <div class="pt-4 border-t border-gray-200">
                            <x-button href="{{ route('templates.gerar-escalas.form', $funcionario) }}?template_id={{ $template->id }}" color="primary" class="w-full justify-center">
                                <i class="fas fa-calendar-plus mr-2"></i> Gerar Escalas
                            </x-button>
                        </div>
                    @endcan
                </div>
            </div>
        </div>
                
        <!-- Configuração Semanal -->
        <div class="lg:col-span-2">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-calendar-week mr-2"></i>Configuração Semanal
                </h3>
                
                @php
                    $diasSemana = [
                        'segunda' => 'Segunda-feira',
                        'terca' => 'Terça-feira', 
                        'quarta' => 'Quarta-feira',
                        'quinta' => 'Quinta-feira',
                        'sexta' => 'Sexta-feira',
                        'sabado' => 'Sábado',
                        'domingo' => 'Domingo'
                    ];
                @endphp
                
                <div class="grid grid-cols-1 md:grid-cols-1 xl:grid-cols-2 gap-4">
                    @foreach($diasSemana as $dia => $diaLabel)
                        @php
                            // Verificar se pelo menos um período está configurado
                            $periodos = [
                                'manha' => ['inicio' => $dia.'_inicio', 'fim' => $dia.'_fim', 'tipo' => $dia.'_tipo', 'label' => 'Manhã'],
                                'manha2' => ['inicio' => $dia.'_manha2_inicio', 'fim' => $dia.'_manha2_fim', 'tipo' => $dia.'_manha2_tipo', 'label' => 'Manhã (Opcional)'],
                                'tarde' => ['inicio' => $dia.'_tarde_inicio', 'fim' => $dia.'_tarde_fim', 'tipo' => $dia.'_tarde_tipo', 'label' => 'Tarde'],
                                'tarde2' => ['inicio' => $dia.'_tarde2_inicio', 'fim' => $dia.'_tarde2_fim', 'tipo' => $dia.'_tarde2_tipo', 'label' => 'Tarde (Opcional)']
                            ];
                            
                            $diaAtivo = false;
                            foreach($periodos as $periodo) {
                                if($template->{$periodo['inicio']} && $template->{$periodo['fim']}) {
                                    $diaAtivo = true;
                                    break;
                                }
                            }
                        @endphp
                        <div class="border rounded-lg {{ $diaAtivo ? 'border-blue-200 bg-blue-50' : 'border-gray-200 bg-gray-50' }}">
                            <div class="px-4 py-3 {{ $diaAtivo ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600' }} rounded-t-lg">
                                <h4 class="font-medium text-sm flex items-center">
                                    <i class="fas {{ $diaAtivo ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                                    {{ $diaLabel }}
                                </h4>
                            </div>
                            @if($diaAtivo)
                                <div class="p-4 space-y-4">
                                    @foreach($periodos as $periodoKey => $periodo)
                                        @php
                                            $periodoAtivo = $template->{$periodo['inicio']} && $template->{$periodo['fim']};
                                        @endphp
                                        @if($periodoAtivo)
                                            <div class="border-l-4 border-blue-400 pl-3 py-2 bg-white rounded-r">
                                                <h5 class="font-medium text-gray-800 text-xs mb-2">{{ $periodo['label'] }}</h5>
                                                <div class="grid grid-cols-2 gap-2 text-xs">
                                                    <div>
                                                        <span class="font-medium text-gray-600">Entrada:</span>
                                                        <p class="text-blue-600 font-medium">{{ $template->{$periodo['inicio']} ?? '--:--' }}</p>
                                                    </div>
                                                    <div>
                                                        <span class="font-medium text-gray-600">Saída:</span>
                                                        <p class="text-blue-600 font-medium">{{ $template->{$periodo['fim']} ?? '--:--' }}</p>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <span class="font-medium text-gray-600">Tipo:</span>
                                                        @php
                                            $tipo = $template->{$periodo['tipo']};
                                            $tipoLabel = match($tipo) {
                                                'Normal' => 'Normal',
                                                'Extra' => 'Extra',
                                                'Substituição' => 'Substituição',
                                                'PL' => 'PL (Planejamento)',
                                                default => $tipo ?? 'N/A'
                                            };
                                            $tipoBadge = match($tipo) {
                                                'Normal' => 'bg-green-100 text-green-700',
                                                'Extra' => 'bg-blue-100 text-blue-700',
                                                'Substituição' => 'bg-yellow-100 text-yellow-700',
                                                'PL' => 'bg-purple-100 text-purple-700',
                                                default => 'bg-gray-100 text-gray-700'
                                            };
                                        @endphp
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $tipoBadge }}">
                                                            {{ $tipoLabel }}
                                                        </span>
                                                    </div>
                                                    @php
                                                        try {
                                                            $entradaValue = $template->{$periodo['inicio']};
                                                            $saidaValue = $template->{$periodo['fim']};
                                                            
                                                            // Validar se os valores não são nulos ou vazios
                                                            if (empty($entradaValue) || empty($saidaValue)) {
                                                                throw new Exception('Valores de horário vazios');
                                                            }
                                                            
                                                            // Se o valor já contém data, extrair apenas a hora
                                                            if (strlen($entradaValue) > 5) {
                                                                $entradaValue = date('H:i', strtotime($entradaValue));
                                                            }
                                                            if (strlen($saidaValue) > 5) {
                                                                $saidaValue = date('H:i', strtotime($saidaValue));
                                                            }
                                                            
                                                            // Validar formato H:i
                                                            if (!preg_match('/^\d{2}:\d{2}$/', $entradaValue) || !preg_match('/^\d{2}:\d{2}$/', $saidaValue)) {
                                                                throw new Exception('Formato de horário inválido');
                                                            }
                                                            
                                                            $entrada = \Carbon\Carbon::createFromFormat('H:i', $entradaValue);
                                                            $saida = \Carbon\Carbon::createFromFormat('H:i', $saidaValue);
                                                            $almocoInicio = null;
                                                            $almocoFim = null;
                                                        } catch (Exception $e) {
                                                            $entrada = null;
                                                            $saida = null;
                                                            $almocoInicio = null;
                                                            $almocoFim = null;
                                                        }
                                                            
                                                        $totalMinutos = 0;
                                                        if ($entrada && $saida) {
                                                            $totalMinutos = $saida->diffInMinutes($entrada);
                                                            if ($almocoInicio && $almocoFim) {
                                                                $almocoMinutos = $almocoFim->diffInMinutes($almocoInicio);
                                                                $totalMinutos -= $almocoMinutos;
                                                            }
                                                        }
                                                        
                                                        $horas = intval($totalMinutos / 60);
                                                        $minutos = $totalMinutos % 60;
                                                    @endphp
                                                    <div class="col-span-2 pt-1 border-t border-gray-100 mt-2">
                                                        <div class="flex items-center text-xs text-gray-500">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Duração: {{ $horas }}h{{ $minutos > 0 ? sprintf('%02d', $minutos) : '' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="p-6 text-center text-gray-500">
                                    <i class="fas fa-ban text-2xl mb-2"></i>
                                    <p class="text-sm">Dia não configurado</p>
                                </div>
                            @endif
                         </div>
                     @endforeach
                 </div>
                 
                 @if(count($diasConfigurados) == 0)
                     <div class="text-center py-8">
                         <i class="fas fa-exclamation-triangle text-4xl text-yellow-500 mb-4"></i>
                         <h3 class="text-gray-600 font-medium mb-2">Nenhum dia configurado</h3>
                         <p class="text-gray-500 mb-4">Este template não possui nenhum dia da semana configurado.</p>
                         @can('funcionarios.editar')
                             <x-button href="{{ route('funcionarios.templates.edit', [$funcionario, $template]) }}" color="blue">
                                 <i class="fas fa-edit mr-2"></i> Configurar Dias
                             </x-button>
                         @endcan
                     </div>
                 @endif
             </div>
         </div>
            </div>
            
        <!-- Resumo Semanal -->
        @if(count($diasConfigurados) > 0)
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-chart-bar mr-2"></i>Resumo Semanal
                </h3>
                
                @php
                    $totalHorasSemana = 0;
                    $diasTrabalho = count($diasConfigurados);
                    $tiposTrabalho = [];
                @endphp
                
                @foreach($diasConfigurados as $nomeDia => $dia)
                    @php
                        // Calcular horas para todos os períodos do dia
                        $periodosDia = [
                            ['inicio' => $nomeDia.'_inicio', 'fim' => $nomeDia.'_fim', 'tipo' => $nomeDia.'_tipo'],
                            ['inicio' => $nomeDia.'_manha2_inicio', 'fim' => $nomeDia.'_manha2_fim', 'tipo' => $nomeDia.'_manha2_tipo'],
                            ['inicio' => $nomeDia.'_tarde_inicio', 'fim' => $nomeDia.'_tarde_fim', 'tipo' => $nomeDia.'_tarde_tipo'],
                            ['inicio' => $nomeDia.'_tarde2_inicio', 'fim' => $nomeDia.'_tarde2_fim', 'tipo' => $nomeDia.'_tarde2_tipo']
                        ];
                        
                        foreach($periodosDia as $periodoDia) {
                            $entrada = $template->{$periodoDia['inicio']};
                            $saida = $template->{$periodoDia['fim']};
                            $tipo = $template->{$periodoDia['tipo']};
                            
                            if ($entrada && $saida) {
                                try {
                                    // Validar se os valores não são nulos ou vazios
                                    if (empty($entrada) || empty($saida)) {
                                        throw new Exception('Valores de horário vazios');
                                    }
                                    
                                    // Se o valor já contém data, extrair apenas a hora
                                    if (strlen($entrada) > 5) {
                                        $entrada = date('H:i', strtotime($entrada));
                                    }
                                    if (strlen($saida) > 5) {
                                        $saida = date('H:i', strtotime($saida));
                                    }
                                    
                                    // Validar formato H:i
                                    if (!preg_match('/^\d{2}:\d{2}$/', $entrada) || !preg_match('/^\d{2}:\d{2}$/', $saida)) {
                                        throw new Exception('Formato de horário inválido');
                                    }
                                    
                                    $entradaCarbon = \Carbon\Carbon::createFromFormat('H:i', $entrada);
                                    $saidaCarbon = \Carbon\Carbon::createFromFormat('H:i', $saida);
                                $almocoInicio = null;
                                    $almocoFim = null;
                                    
                                    $minutosPeriodo = $saidaCarbon->diffInMinutes($entradaCarbon);
                                    if ($almocoInicio && $almocoFim) {
                                        $almocoMinutos = $almocoFim->diffInMinutes($almocoInicio);
                                        $minutosPeriodo -= $almocoMinutos;
                                    }
                                    
                                    $totalHorasSemana += $minutosPeriodo;
                                } catch (Exception $e) {
                                    // Ignorar erro e continuar
                                }
                            }
                            
                            if ($tipo && !in_array($tipo, $tiposTrabalho)) {
                                $tiposTrabalho[] = $tipo;
                            }
                        }
                    @endphp
                @endforeach
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 mx-auto mb-3">
                            <i class="fas fa-calendar-week text-xl"></i>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 mb-1">{{ $diasTrabalho }}</div>
                        <div class="text-sm text-gray-600">Dias de trabalho</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-500 mx-auto mb-3">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        @php
                            $horasSemanais = intval($totalHorasSemana / 60);
                            $minutosSemanais = $totalHorasSemana % 60;
                        @endphp
                        <div class="text-2xl font-bold text-gray-900 mb-1">{{ $horasSemanais }}h{{ $minutosSemanais > 0 ? sprintf('%02d', $minutosSemanais) : '' }}</div>
                        <div class="text-sm text-gray-600">Carga horária semanal</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center text-purple-500 mx-auto mb-3">
                            <i class="fas fa-calculator text-xl"></i>
                        </div>
                        @php
                            $mediaHorasDia = $diasTrabalho > 0 ? $totalHorasSemana / $diasTrabalho : 0;
                            $mediaHoras = intval($mediaHorasDia / 60);
                            $mediaMinutos = intval($mediaHorasDia % 60);
                        @endphp
                        <div class="text-2xl font-bold text-gray-900 mb-1">{{ $mediaHoras }}h{{ $mediaMinutos > 0 ? sprintf('%02d', $mediaMinutos) : '' }}</div>
                        <div class="text-sm text-gray-600">Média por dia</div>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-500 mx-auto mb-3">
                            <i class="fas fa-briefcase text-xl"></i>
                        </div>
                        <div class="mb-2 space-x-1">
                            @foreach($tiposTrabalho as $tipo)
                                @php
                                    $tipoLabel = match($tipo) {
                                        'Normal' => 'Normal',
                                        'Extra' => 'Extra',
                                        'Substituição' => 'Substituição',
                                        'PL' => 'PL (Planejamento)',
                                        default => $tipo ?? 'N/A'
                                    };
                                    $tipoBadge = match($tipo) {
                                        'Normal' => 'bg-green-100 text-green-800',
                                        'Extra' => 'bg-blue-100 text-blue-800',
                                        'Substituição' => 'bg-yellow-100 text-yellow-800',
                                        'PL' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $tipoBadge }}">{{ $tipoLabel }}</span>
                            @endforeach
                        </div>
                        <div class="text-sm text-gray-600">Tipos de trabalho</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-card>
</div>
@endsection