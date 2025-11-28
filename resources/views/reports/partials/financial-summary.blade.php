<!-- Resumo do Relatório Financeiro -->
<div class="row">
    <div class="col-md-3">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Custo Total Estimado
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            R$ {{ number_format($data['custo_total_estimado'] ?? 0, 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                            Custo Médio/Dia
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            R$ {{ number_format($data['custo_medio_dia'] ?? 0, 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                            Total de Horas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $data['total_horas_trabalhadas'] ?? 0 }}h
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            Custo por Hora
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            R$ 25,00
                        </div>
                        <small class="text-muted">(Estimativa)</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calculator fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Período -->
<div class="mt-4">
    <div class="card bg-gradient-primary text-white">
        <div class="card-body text-center">
            <h5 class="card-title text-white mb-1">
                <i class="fas fa-calendar-alt"></i> Período Analisado
            </h5>
            <h4 class="text-white font-weight-bold">{{ $data['periodo'] ?? 'N/A' }}</h4>
        </div>
    </div>
</div>

<!-- Análise Detalhada -->
<div class="mt-4">
    <h6 class="font-weight-bold text-primary mb-3">Análise Financeira</h6>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card border-left-success">
                <div class="card-body">
                    <h6 class="card-title text-success">
                        <i class="fas fa-chart-line"></i> Projeção Mensal
                    </h6>
                    @php
                        $custoMensal = ($data['custo_medio_dia'] ?? 0) * 30;
                    @endphp
                    <h4 class="text-success">R$ {{ number_format($custoMensal, 2, ',', '.') }}</h4>
                    <small class="text-muted">Baseado na média diária</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-left-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning">
                        <i class="fas fa-chart-bar"></i> Projeção Anual
                    </h6>
                    @php
                        $custoAnual = ($data['custo_medio_dia'] ?? 0) * 365;
                    @endphp
                    <h4 class="text-warning">R$ {{ number_format($custoAnual, 2, ',', '.') }}</h4>
                    <small class="text-muted">Baseado na média diária</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Breakdown de Custos -->
<div class="mt-4">
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-pie-chart"></i> Breakdown de Custos
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="h2 text-primary">100%</div>
                        <div class="text-muted">Custos de Pessoal</div>
                        <small class="text-muted">Baseado em horas trabalhadas</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="h2 text-success">
                            {{ $data['total_horas_trabalhadas'] > 0 ? round(($data['custo_total_estimado'] / $data['total_horas_trabalhadas']), 2) : 0 }}
                        </div>
                        <div class="text-muted">R$/Hora Média</div>
                        <small class="text-muted">Custo efetivo por hora</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        @php
                            $diasPeriodo = 30; // Estimativa
                            $horasPorDia = ($data['total_horas_trabalhadas'] ?? 0) / $diasPeriodo;
                        @endphp
                        <div class="h2 text-warning">{{ round($horasPorDia, 1) }}</div>
                        <div class="text-muted">Horas/Dia Média</div>
                        <small class="text-muted">Carga horária diária</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resumo Executivo -->
<div class="mt-4">
    <div class="card bg-light">
        <div class="card-body">
            <h6 class="card-title text-primary">
                <i class="fas fa-file-invoice-dollar"></i> Resumo Executivo
            </h6>
            <p class="card-text">
                Durante o período de <strong>{{ $data['periodo'] ?? 'N/A' }}</strong>, 
                foram trabalhadas <strong>{{ $data['total_horas_trabalhadas'] ?? 0 }} horas</strong>, 
                resultando em um custo estimado de 
                <strong>R$ {{ number_format($data['custo_total_estimado'] ?? 0, 2, ',', '.') }}</strong>.
            </p>
            <p class="card-text">
                O custo médio diário foi de 
                <strong>R$ {{ number_format($data['custo_medio_dia'] ?? 0, 2, ',', '.') }}</strong>, 
                com uma taxa horária estimada de <strong>R$ 25,00</strong>.
            </p>
            
            @if(($data['custo_medio_dia'] ?? 0) > 0)
                <div class="alert alert-info mt-3" role="alert">
                    <i class="fas fa-info-circle"></i>
                    <strong>Projeção:</strong> 
                    Com base nestes dados, o custo mensal estimado seria de 
                    <strong>R$ {{ number_format(($data['custo_medio_dia'] ?? 0) * 30, 2, ',', '.') }}</strong> 
                    e o custo anual de 
                    <strong>R$ {{ number_format(($data['custo_medio_dia'] ?? 0) * 365, 2, ',', '.') }}</strong>.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Disclaimer -->
<div class="mt-3">
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Importante:</strong> 
        Os valores apresentados são estimativas baseadas em uma taxa horária fixa de R$ 25,00. 
        Para cálculos precisos, considere os salários reais e encargos trabalhistas.
    </div>
</div>