<div class="table-responsive">
    <table class="table table-sm table-striped">
        <thead class="table-dark">
            <tr>
                <th>Data</th>
                <th>Dia da Semana</th>
                <th>Horário</th>
                <th>Tipo</th>
                <th>Observações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($escalas as $escala)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($escala['data'])->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($escala['data'])->locale('pt_BR')->dayName }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($escala['hora_inicio'])->format('H:i') }} - 
                        {{ \Carbon\Carbon::parse($escala['hora_fim'])->format('H:i') }}
                    </td>
                    <td>
                        <span class="badge bg-{{ $escala['tipo_atividade'] === 'em_sala' ? 'primary' : 'secondary' }}">
                            {{ $escala['tipo_atividade'] === 'em_sala' ? 'Em Sala' : 'PL' }}
                        </span>
                    </td>
                    <td>{{ $escala['observacoes'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Nenhuma escala será gerada com as configurações atuais.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(count($escalas) > 0)
    <div class="mt-3">
        <div class="row">
            <div class="col-md-6">
                <h6>Resumo da Geração</h6>
                <ul class="list-unstyled small">
                    <li><strong>Funcionário:</strong> {{ $funcionario->nome }}</li>
                    <li><strong>Template:</strong> {{ $template->nome }}</li>
                    <li><strong>Período:</strong> {{ $dataInicio->format('d/m/Y') }} a {{ $dataFim->format('d/m/Y') }}</li>
                    <li><strong>Total de escalas:</strong> {{ count($escalas) }}</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Distribuição por Tipo</h6>
                @php
                    $tiposCount = collect($escalas)->groupBy('tipo_atividade')->map->count();
                @endphp
                <ul class="list-unstyled small">
                    @foreach($tiposCount as $tipo => $count)
                        <li>
                            <span class="badge bg-{{ $tipo === 'em_sala' ? 'primary' : 'secondary' }} me-1">
                                {{ $tipo === 'em_sala' ? 'Em Sala' : 'PL' }}
                            </span>
                            {{ $count }} {{ $count === 1 ? 'escala' : 'escalas' }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif