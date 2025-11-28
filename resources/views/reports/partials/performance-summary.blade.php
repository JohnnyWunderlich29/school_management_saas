<!-- Resumo do Relatório de Performance -->
<div class="row">
    <div class="col-md-3">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Período Analisado
                        </div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $data['total_dias'] ?? 0 }} dias</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Média Presenças/Dia
                        </div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $data['media_presencas_dia'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Média Escalas/Dia
                        </div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $data['media_escalas_dia'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Período
                        </div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800" style="font-size: 0.8rem;">
                            {{ $data['periodo'] ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card bg-gradient-primary text-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Funcionário Mais Ativo
                        </div>
                        <div class="h6 mb-0 font-weight-bold text-white">
                            {{ $data['funcionario_mais_ativo'] ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-star fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card bg-gradient-success text-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                            Sala Mais Utilizada
                        </div>
                        <div class="h6 mb-0 font-weight-bold text-white">
                            {{ $data['sala_mais_utilizada'] ?? 'N/A' }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-trophy fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Indicadores de Performance -->
<div class="mt-4">
    <h6 class="font-weight-bold text-primary mb-3">Indicadores de Performance</h6>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card border-left-success">
                <div class="card-body">
                    <h6 class="card-title text-success">Atividade Diária</h6>
                    <div class="progress mb-2">
                        @php
                            $atividadeScore = min(100, ($data['media_presencas_dia'] ?? 0) * 10);
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $atividadeScore }}%"></div>
                    </div>
                    <small class="text-muted">Baseado na média de presenças diárias</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-left-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning">Utilização de Escalas</h6>
                    <div class="progress mb-2">
                        @php
                            $escalaScore = min(100, ($data['media_escalas_dia'] ?? 0) * 20);
                        @endphp
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $escalaScore }}%"></div>
                    </div>
                    <small class="text-muted">Baseado na média de escalas diárias</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-left-info">
                <div class="card-body">
                    <h6 class="card-title text-info">Eficiência Geral</h6>
                    <div class="progress mb-2">
                        @php
                            $eficienciaScore = min(100, (($data['media_presencas_dia'] ?? 0) + ($data['media_escalas_dia'] ?? 0)) * 8);
                        @endphp
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $eficienciaScore }}%"></div>
                    </div>
                    <small class="text-muted">Combinação de presenças e escalas</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumo Textual -->
<div class="mt-4">
    <div class="card bg-light">
        <div class="card-body">
            <h6 class="card-title text-primary">Resumo do Período</h6>
            <p class="card-text">
                Durante o período de <strong>{{ $data['periodo'] ?? 'N/A' }}</strong>, 
                foram registradas em média <strong>{{ $data['media_presencas_dia'] ?? 0 }} presenças por dia</strong> 
                e <strong>{{ $data['media_escalas_dia'] ?? 0 }} escalas por dia</strong>.
                
                @if(isset($data['funcionario_mais_ativo']) && $data['funcionario_mais_ativo'] !== 'N/A')
                    O funcionário mais ativo foi <strong>{{ $data['funcionario_mais_ativo'] }}</strong>.
                @endif
                
                @if(isset($data['sala_mais_utilizada']) && $data['sala_mais_utilizada'] !== 'N/A')
                    A sala mais utilizada foi <strong>{{ $data['sala_mais_utilizada'] }}</strong>.
                @endif
            </p>
        </div>
    </div>
</div>