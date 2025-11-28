@extends('layouts.app')

@section('title', 'Novo Tempo Slot - ' . $turno->nome)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.turnos.index') }}">Turnos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.turnos.show', $turno) }}">{{ $turno->nome }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.turnos.tempo-slots.index', $turno) }}">Tempo Slots</a></li>
            <li class="breadcrumb-item active" aria-current="page">Novo</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Novo Tempo Slot</h1>
            <p class="text-muted mb-0">Turno: {{ $turno->nome }} ({{ $turno->hora_inicio }} às {{ $turno->hora_fim }})</p>
        </div>
    </div>

    <!-- Formulário -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações do Tempo Slot</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.turnos.tempo-slots.store', $turno) }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Nome -->
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nome') is-invalid @enderror" 
                                       id="nome" 
                                       name="nome" 
                                       value="{{ old('nome') }}" 
                                       placeholder="Ex: 1ª Aula, Intervalo, Recreio"
                                       required>
                                @error('nome')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tipo -->
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                                <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                                    <option value="">Selecione o tipo</option>
                                    @foreach($tipos as $valor => $label)
                                        <option value="{{ $valor }}" {{ old('tipo') == $valor ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Hora Início -->
                            <div class="col-md-4 mb-3">
                                <label for="hora_inicio" class="form-label">Hora Início <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control @error('hora_inicio') is-invalid @enderror" 
                                       id="hora_inicio" 
                                       name="hora_inicio" 
                                       value="{{ old('hora_inicio') }}" 
                                       required>
                                @error('hora_inicio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Hora Fim -->
                            <div class="col-md-4 mb-3">
                                <label for="hora_fim" class="form-label">Hora Fim <span class="text-danger">*</span></label>
                                <input type="time" 
                                       class="form-control @error('hora_fim') is-invalid @enderror" 
                                       id="hora_fim" 
                                       name="hora_fim" 
                                       value="{{ old('hora_fim') }}" 
                                       required>
                                @error('hora_fim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Duração (opcional) -->
                            <div class="col-md-4 mb-3">
                                <label for="duracao_minutos" class="form-label">Duração (minutos)</label>
                                <input type="number" 
                                       class="form-control @error('duracao_minutos') is-invalid @enderror" 
                                       id="duracao_minutos" 
                                       name="duracao_minutos" 
                                       value="{{ old('duracao_minutos') }}" 
                                       min="1"
                                       placeholder="Calculado automaticamente">
                                <div class="form-text">Deixe em branco para calcular automaticamente</div>
                                @error('duracao_minutos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Ordem -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ordem" class="form-label">Ordem <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('ordem') is-invalid @enderror" 
                                       id="ordem" 
                                       name="ordem" 
                                       value="{{ old('ordem', 1) }}" 
                                       min="1"
                                       required>
                                <div class="form-text">Ordem de exibição do tempo slot</div>
                                @error('ordem')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="ativo" 
                                           name="ativo" 
                                           value="1" 
                                           {{ old('ativo', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ativo">
                                        Ativo
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Descrição -->
                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control @error('descricao') is-invalid @enderror" 
                                      id="descricao" 
                                      name="descricao" 
                                      rows="3" 
                                      placeholder="Descrição opcional do tempo slot">{{ old('descricao') }}</textarea>
                            @error('descricao')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Botões -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.turnos.tempo-slots.index', $turno) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Tempo Slot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar com informações -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Informações do Turno</h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nome:</dt>
                        <dd class="col-sm-8">{{ $turno->nome }}</dd>
                        
                        <dt class="col-sm-4">Código:</dt>
                        <dd class="col-sm-8">{{ $turno->codigo }}</dd>
                        
                        <dt class="col-sm-4">Horário:</dt>
                        <dd class="col-sm-8">{{ $turno->hora_inicio }} às {{ $turno->hora_fim }}</dd>
                        
                        <dt class="col-sm-4">Duração:</dt>
                        <dd class="col-sm-8">{{ $turno->duracao_formatada }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Dicas</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-lightbulb text-warning"></i>
                            <small>Use nomes descritivos como "1ª Aula", "Intervalo", "Recreio"</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-info"></i>
                            <small>A duração será calculada automaticamente se não informada</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-sort-numeric-up text-success"></i>
                            <small>A ordem define a sequência dos períodos</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const horaInicio = document.getElementById('hora_inicio');
    const horaFim = document.getElementById('hora_fim');
    const duracaoMinutos = document.getElementById('duracao_minutos');

    function calcularDuracao() {
        if (horaInicio.value && horaFim.value && !duracaoMinutos.value) {
            const inicio = new Date('2000-01-01 ' + horaInicio.value);
            const fim = new Date('2000-01-01 ' + horaFim.value);
            
            if (fim > inicio) {
                const diffMs = fim - inicio;
                const diffMinutos = Math.floor(diffMs / (1000 * 60));
                duracaoMinutos.placeholder = diffMinutos + ' minutos (calculado)';
            }
        }
    }

    horaInicio.addEventListener('change', calcularDuracao);
    horaFim.addEventListener('change', calcularDuracao);
});
</script>
@endsection