@extends('corporativo.layouts.app')

@section('title', 'Relatório de Configurações Educacionais')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('corporativo.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('corporativo.configuracao-educacional.index') }}">Configurações Educacionais</a></li>
                        <li class="breadcrumb-item active">Relatório</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    Relatório de Configurações Educacionais
                </h4>
                <p class="text-muted">Análise detalhada das configurações educacionais de todas as escolas do sistema</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Relatório Detalhado
                        </h5>
                        <div>
                            <a href="{{ route('corporativo.configuracao-educacional.index') }}" class="btn btn-secondary btn-sm mr-2">
                                <i class="fas fa-arrow-left mr-1"></i>
                                Voltar
                            </a>
                            <button onclick="window.print()" class="btn btn-primary btn-sm">
                                <i class="fas fa-print mr-1"></i>
                                Imprimir
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Estatísticas Gerais -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-school"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total de Escolas</span>
                                    <span class="info-box-number">{{ $estatisticas['total_escolas'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-graduation-cap"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Modalidades Configuradas</span>
                                    <span class="info-box-number">{{ $estatisticas['total_modalidades_configuradas'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-layer-group"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Níveis Configurados</span>
                                    <span class="info-box-number">{{ $estatisticas['total_niveis_configurados'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Escolas Sem Configuração</span>
                                    <span class="info-box-number">{{ $estatisticas['escolas_sem_configuracao'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modalidades e Níveis Mais Utilizados -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-graduation-cap mr-1"></i>
                                        Modalidades Mais Utilizadas
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Modalidade</th>
                                                    <th>Escolas</th>
                                                    <th>%</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($estatisticas['modalidades_populares'] as $modalidade)
                                                    <tr>
                                                        <td>{{ $modalidade->nome }}</td>
                                                        <td>{{ $modalidade->total_escolas }}</td>
                                                        <td>{{ round($modalidade->percentual, 1) }}%</td>
                                                        <td>
                                                            @if($modalidade->percentual >= 50)
                                                                <span class="badge badge-success">Alta</span>
                                                            @elseif($modalidade->percentual >= 25)
                                                                <span class="badge badge-warning">Média</span>
                                                            @else
                                                                <span class="badge badge-danger">Baixa</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-layer-group mr-1"></i>
                                        Níveis Mais Utilizados
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Nível</th>
                                                    <th>Escolas</th>
                                                    <th>%</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($estatisticas['niveis_populares'] as $nivel)
                                                    <tr>
                                                        <td>{{ $nivel->nome }}</td>
                                                        <td>{{ $nivel->total_escolas }}</td>
                                                        <td>{{ round($nivel->percentual, 1) }}%</td>
                                                        <td>
                                                            @if($nivel->percentual >= 50)
                                                                <span class="badge badge-success">Alta</span>
                                                            @elseif($nivel->percentual >= 25)
                                                                <span class="badge badge-warning">Média</span>
                                                            @else
                                                                <span class="badge badge-danger">Baixa</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distribuição por Turnos -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-clock mr-1"></i>
                                        Distribuição por Turnos
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Modalidades</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Modalidade</th>
                                                            <th>Manhã</th>
                                                            <th>Tarde</th>
                                                            <th>Noite</th>
                                                            <th>Integral</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($estatisticas['turnos_modalidades'] as $turno)
                                                            <tr>
                                                                <td>{{ $turno->modalidade_nome }}</td>
                                                                <td>{{ $turno->manha }}</td>
                                                                <td>{{ $turno->tarde }}</td>
                                                                <td>{{ $turno->noite }}</td>
                                                                <td>{{ $turno->integral }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Níveis</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Nível</th>
                                                            <th>Manhã</th>
                                                            <th>Tarde</th>
                                                            <th>Noite</th>
                                                            <th>Integral</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($estatisticas['turnos_niveis'] as $turno)
                                                            <tr>
                                                                <td>{{ $turno->nivel_nome }}</td>
                                                                <td>{{ $turno->manha }}</td>
                                                                <td>{{ $turno->tarde }}</td>
                                                                <td>{{ $turno->noite }}</td>
                                                                <td>{{ $turno->integral }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Escolas com Configurações Incompletas -->
                    @if(count($estatisticas['escolas_incompletas']) > 0)
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-exclamation-triangle mr-1 text-warning"></i>
                                            Escolas com Configurações Incompletas
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Escola</th>
                                                        <th>Código</th>
                                                        <th>Modalidades</th>
                                                        <th>Níveis</th>
                                                        <th>Status</th>
                                                        <th>Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($estatisticas['escolas_incompletas'] as $escola)
                                                        <tr>
                                                            <td>{{ $escola->nome }}</td>
                                                            <td>{{ $escola->codigo }}</td>
                                                            <td>{{ $escola->modalidades_count }}</td>
                                                            <td>{{ $escola->niveis_count }}</td>
                                                            <td>
                                                                @if($escola->modalidades_count == 0 && $escola->niveis_count == 0)
                                                                    <span class="badge badge-danger">Não Configurada</span>
                                                                @else
                                                                    <span class="badge badge-warning">Parcialmente Configurada</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('admin.configuracao-educacional.show', $escola->id) }}" 
                                                                   class="btn btn-sm btn-primary" 
                                                                   title="Configurar">
                                                                    <i class="fas fa-cog"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Resumo de Capacidades -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-users mr-1"></i>
                                        Resumo de Capacidades Configuradas
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Modalidades</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Modalidade</th>
                                                            <th>Cap. Média Mín.</th>
                                                            <th>Cap. Média Máx.</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($estatisticas['capacidades_modalidades'] as $capacidade)
                                                            <tr>
                                                                <td>{{ $capacidade->nome }}</td>
                                                                <td>{{ $capacidade->capacidade_media_minima ? round($capacidade->capacidade_media_minima) : 'N/A' }}</td>
                                                                <td>{{ $capacidade->capacidade_media_maxima ? round($capacidade->capacidade_media_maxima) : 'N/A' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Níveis</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Nível</th>
                                                            <th>Cap. Média Mín.</th>
                                                            <th>Cap. Média Máx.</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($estatisticas['capacidades_niveis'] as $capacidade)
                                                            <tr>
                                                                <td>{{ $capacidade->nome }}</td>
                                                                <td>{{ $capacidade->capacidade_media_minima ? round($capacidade->capacidade_media_minima) : 'N/A' }}</td>
                                                                <td>{{ $capacidade->capacidade_media_maxima ? round($capacidade->capacidade_media_maxima) : 'N/A' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rodapé do Relatório -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Relatório gerado em {{ now()->format('d/m/Y H:i:s') }}
                                        <br>
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Este relatório apresenta um resumo das configurações educacionais de todas as escolas do sistema.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
@media print {
    .btn, .card-header .btn {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    
    .info-box {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    
    .progress {
        border: 1px solid #ddd !important;
    }
}
</style>
@endsection