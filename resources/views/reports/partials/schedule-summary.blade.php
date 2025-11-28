<!-- Resumo do Relatório de Escalas -->
<div class="row">
    <div class="col-md-6">
        <div class="card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Total de Escalas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['total_escalas'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total de Horas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['total_horas'] ?? 0 }}h</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($data['detalhes']) && count($data['detalhes']) > 0)
    <div class="mt-4">
        <h6 class="font-weight-bold text-primary mb-3">Últimas Escalas (Primeiras 10)</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Data</th>
                        <th>Funcionário</th>
                        <th>Sala</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Horas</th>
                        <th>Atividade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($data['detalhes'], 0, 10) as $detalhe)
                        <tr>
                            <td>{{ $detalhe['data'] }}</td>
                            <td>{{ $detalhe['funcionario'] }}</td>
                            <td>{{ $detalhe['sala'] }}</td>
                            <td>{{ $detalhe['hora_inicio'] }}</td>
                            <td>{{ $detalhe['hora_fim'] }}</td>
                            <td>
                                <span class="badge badge-info">{{ $detalhe['horas_trabalhadas'] }}h</span>
                            </td>
                            <td>{{ $detalhe['tipo_atividade'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if(count($data['detalhes']) > 10)
            <p class="text-muted text-center mt-2">
                <small>Mostrando 10 de {{ count($data['detalhes']) }} escalas. Baixe o relatório completo para ver todos os dados.</small>
            </p>
        @endif
    </div>
@endif

@if(isset($data['total_horas']) && $data['total_horas'] > 0)
    <div class="mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title text-warning">Média de Horas por Escala</h6>
                        <h4 class="text-dark">
                            {{ $data['total_escalas'] > 0 ? round($data['total_horas'] / $data['total_escalas'], 2) : 0 }}h
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title text-info">Horas por Dia (Estimativa)</h6>
                        <h4 class="text-dark">
                            @php
                                $diasPeriodo = 30; // Estimativa baseada em um mês
                                $horasPorDia = $data['total_horas'] / $diasPeriodo;
                            @endphp
                            {{ round($horasPorDia, 1) }}h/dia
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif