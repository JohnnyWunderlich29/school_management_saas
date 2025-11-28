<!-- Resumo do Relatório de Presenças -->
<div class="row">
    <div class="col-md-3">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total de Registros
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['total_registros'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-list fa-2x text-gray-300"></i>
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
                            Presentes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['presentes'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-left-danger h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Ausentes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['ausentes'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times fa-2x text-gray-300"></i>
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
                            Taxa de Presença
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['taxa_presenca'] ?? 0 }}%</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-percentage fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($data['detalhes']) && count($data['detalhes']) > 0)
    <div class="mt-4">
        <h6 class="font-weight-bold text-primary mb-3">Últimos Registros (Primeiros 10)</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Data</th>
                        <th>Aluno</th>
                        <th>Funcionário</th>
                        <th>Status</th>
                        <th>Entrada</th>
                        <th>Saída</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($data['detalhes'], 0, 10) as $detalhe)
                        <tr>
                            <td>{{ $detalhe['data'] }}</td>
                            <td>{{ $detalhe['aluno'] }}</td>
                            <td>{{ $detalhe['funcionario'] }}</td>
                            <td>
                                @if($detalhe['presente'] === 'Sim')
                                    <span class="badge badge-success">Presente</span>
                                @else
                                    <span class="badge badge-danger">Ausente</span>
                                @endif
                            </td>
                            <td>{{ $detalhe['hora_entrada'] ?? '-' }}</td>
                            <td>{{ $detalhe['hora_saida'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if(count($data['detalhes']) > 10)
            <p class="text-muted text-center mt-2">
                <small>Mostrando 10 de {{ count($data['detalhes']) }} registros. Baixe o relatório completo para ver todos os dados.</small>
            </p>
        @endif
    </div>
@endif