@extends('layouts.app')

@section('title', 'Tempo Slot - ' . $tempoSlot->nome)

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.turnos.index') }}">Turnos</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.turnos.show', $turno) }}">{{ $turno->nome }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.turnos.tempo-slots.index', $turno) }}">Tempo Slots</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $tempoSlot->nome }}</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $tempoSlot->nome }}</h1>
            <p class="text-muted mb-0">Turno: {{ $turno->nome }} ({{ $turno->codigo }})</p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.turnos.tempo-slots.edit', [$turno, $tempoSlot]) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="fas fa-trash"></i> Excluir
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Informações Principais -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informações Básicas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Nome:</dt>
                                <dd class="col-sm-8">{{ $tempoSlot->nome }}</dd>
                                
                                <dt class="col-sm-4">Tipo:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-{{ $tempoSlot->tipo === 'aula' ? 'primary' : ($tempoSlot->tipo === 'intervalo' ? 'warning' : 'info') }} fs-6">
                                        <i class="fas fa-{{ $tempoSlot->tipo === 'aula' ? 'chalkboard-teacher' : ($tempoSlot->tipo === 'intervalo' ? 'coffee' : 'clock') }}"></i>
                                        {{ $tempoSlot->tipo_formatado }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-{{ $tempoSlot->ativo ? 'success' : 'secondary' }} fs-6">
                                        <i class="fas fa-{{ $tempoSlot->ativo ? 'check-circle' : 'times-circle' }}"></i>
                                        {{ $tempoSlot->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-4">Ordem:</dt>
                                <dd class="col-sm-8">{{ $tempoSlot->ordem }}º</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Hora Início:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge bg-light text-dark fs-6">
                                        <i class="fas fa-clock"></i>
                                        {{ $tempoSlot->hora_inicio }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-5">Hora Fim:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge bg-light text-dark fs-6">
                                        <i class="fas fa-clock"></i>
                                        {{ $tempoSlot->hora_fim }}
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-5">Duração:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge bg-info fs-6">
                                        <i class="fas fa-hourglass-half"></i>
                                        {{ $tempoSlot->duracao_minutos }} minutos
                                    </span>
                                </dd>
                                
                                <dt class="col-sm-5">Criado em:</dt>
                                <dd class="col-sm-7">{{ $tempoSlot->created_at->format('d/m/Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            @if($tempoSlot->descricao)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Descrição</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $tempoSlot->descricao }}</p>
                </div>
            </div>
            @endif

            <!-- Horários Relacionados -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Contexto no Turno</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Tempo Slot Anterior</h6>
                            @if($tempoSlotAnterior)
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-2">{{ $tempoSlotAnterior->ordem }}º</span>
                                    <div>
                                        <strong>{{ $tempoSlotAnterior->nome }}</strong><br>
                                        <small class="text-muted">{{ $tempoSlotAnterior->hora_inicio }} - {{ $tempoSlotAnterior->hora_fim }}</small>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0">Primeiro tempo slot do turno</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Próximo Tempo Slot</h6>
                            @if($proximoTempoSlot)
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary me-2">{{ $proximoTempoSlot->ordem }}º</span>
                                    <div>
                                        <strong>{{ $proximoTempoSlot->nome }}</strong><br>
                                        <small class="text-muted">{{ $proximoTempoSlot->hora_inicio }} - {{ $proximoTempoSlot->hora_fim }}</small>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted mb-0">Último tempo slot do turno</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Informações do Turno -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Turno Associado</h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nome:</dt>
                        <dd class="col-sm-8">{{ $turno->nome }}</dd>
                        
                        <dt class="col-sm-4">Código:</dt>
                        <dd class="col-sm-8">{{ $turno->codigo }}</dd>
                        
                        <dt class="col-sm-4">Horário:</dt>
                        <dd class="col-sm-8">{{ $turno->hora_inicio }} às {{ $turno->hora_fim }}</dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $turno->ativo ? 'success' : 'secondary' }}">
                                {{ $turno->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </dd>
                    </dl>
                    <a href="{{ route('admin.turnos.show', $turno) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye"></i> Ver Turno
                    </a>
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Estatísticas do Turno</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-0">{{ $turno->tempoSlots->count() }}</h4>
                                <small class="text-muted">Total Slots</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-0">{{ $turno->tempoSlots->where('ativo', true)->count() }}</h4>
                            <small class="text-muted">Slots Ativos</small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <h5 class="text-primary mb-0">{{ $turno->tempoSlots->where('tipo', 'aula')->count() }}</h5>
                            <small class="text-muted">Aulas</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-warning mb-0">{{ $turno->tempoSlots->where('tipo', 'intervalo')->count() }}</h5>
                            <small class="text-muted">Intervalos</small>
                        </div>
                        <div class="col-4">
                            <h5 class="text-info mb-0">{{ $turno->tempoSlots->where('tipo', 'outro')->count() }}</h5>
                            <small class="text-muted">Outros</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações Rápidas -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Ações Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.turnos.tempo-slots.create', $turno) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Novo Tempo Slot
                        </a>
                        <a href="{{ route('admin.turnos.tempo-slots.index', $turno) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list"></i> Listar Todos
                        </a>
                        @if($tempoSlot->ativo)
                            <form action="{{ route('admin.turnos.tempo-slots.update', [$turno, $tempoSlot]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="ativo" value="0">
                                <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                                    <i class="fas fa-pause"></i> Desativar
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.turnos.tempo-slots.update', [$turno, $tempoSlot]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="ativo" value="1">
                                <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-play"></i> Ativar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o tempo slot <strong>{{ $tempoSlot->nome }}</strong>?</p>
                <p class="text-danger"><small><i class="fas fa-exclamation-triangle"></i> Esta ação não pode ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('admin.turnos.tempo-slots.destroy', [$turno, $tempoSlot]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection