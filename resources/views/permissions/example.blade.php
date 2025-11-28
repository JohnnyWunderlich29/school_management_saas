@extends('layouts.app')

@section('title', 'Exemplo de Permissões')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Exemplo de Uso das Diretivas de Permissão</h4>
                </div>
                <div class="card-body">
                    <!-- Verificação de Super Administrador -->
                    @superadmin
                        <div class="alert alert-success">
                            <strong>Super Administrador:</strong> Você tem acesso total ao sistema!
                        </div>
                    @endsuperadmin

                    <!-- Verificação de Cargo Específico -->
                    @cargo('Administrador')
                        <div class="alert alert-info">
                            <strong>Administrador:</strong> Você tem privilégios administrativos.
                        </div>
                    @endcargo

                    <!-- Verificação de Permissão Específica -->
                    @permission('alunos.listar')
                        <div class="mb-3">
                            <a href="{{ route('alunos.index') }}" class="btn btn-primary">
                                <i class="fas fa-users"></i> Listar Alunos
                            </a>
                        </div>
                    @endpermission

                    @permission('alunos.criar')
                        <div class="mb-3">
                            <a href="{{ route('alunos.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Criar Novo Aluno
                            </a>
                        </div>
                    @endpermission

                    <!-- Verificação de Múltiplas Permissões (qualquer uma) -->
                    @anypermission('funcionarios.listar', 'funcionarios.criar', 'funcionarios.editar')
                        <div class="mb-3">
                            <h5>Gestão de Funcionários</h5>
                            <div class="btn-group" role="group">
                                @permission('funcionarios.listar')
                                    <a href="{{ route('funcionarios.index') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-list"></i> Listar
                                    </a>
                                @endpermission
                                
                                @permission('funcionarios.criar')
                                    <a href="{{ route('funcionarios.create') }}" class="btn btn-outline-success">
                                        <i class="fas fa-plus"></i> Criar
                                    </a>
                                @endpermission
                            </div>
                        </div>
                    @endanypermission

                    <!-- Verificação de Todas as Permissões -->
                    @allpermissions('escalas.listar', 'escalas.criar')
                        <div class="mb-3">
                            <h5>Gestão Completa de Escalas</h5>
                            <p class="text-muted">Você tem permissão para listar e criar escalas.</p>
                            <div class="btn-group" role="group">
                                <a href="{{ route('escalas.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-calendar"></i> Listar Escalas
                                </a>
                                <a href="{{ route('escalas.create') }}" class="btn btn-outline-success">
                                    <i class="fas fa-plus"></i> Nova Escala
                                </a>
                            </div>
                        </div>
                    @endallpermissions

                    <!-- Menu de Navegação Baseado em Permissões -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Menu de Navegação</h5>
                            <ul class="list-group">
                                @permission('dashboard.acessar')
                                    <li class="list-group-item">
                                        <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                            <i class="fas fa-tachometer-alt"></i> Dashboard
                                        </a>
                                    </li>
                                @endpermission

                                @permission('alunos.listar')
                                    <li class="list-group-item">
                                        <a href="{{ route('alunos.index') }}" class="text-decoration-none">
                                            <i class="fas fa-graduation-cap"></i> Alunos
                                        </a>
                                    </li>
                                @endpermission

                                @permission('responsaveis.listar')
                                    <li class="list-group-item">
                                        <a href="{{ route('responsaveis.index') }}" class="text-decoration-none">
                                            <i class="fas fa-users"></i> Responsáveis
                                        </a>
                                    </li>
                                @endpermission

                                @permission('funcionarios.listar')
                                    <li class="list-group-item">
                                        <a href="{{ route('funcionarios.index') }}" class="text-decoration-none">
                                            <i class="fas fa-user-tie"></i> Funcionários
                                        </a>
                                    </li>
                                @endpermission

                                @permission('presencas.listar')
                                    <li class="list-group-item">
                                        <a href="{{ route('presencas.index') }}" class="text-decoration-none">
                                            <i class="fas fa-check-circle"></i> Presenças
                                        </a>
                                    </li>
                                @endpermission

                                @permission('usuarios.listar')
                                    <li class="list-group-item">
                                        <a href="{{ route('usuarios.index') }}" class="text-decoration-none">
                                            <i class="fas fa-user-cog"></i> Usuários
                                        </a>
                                    </li>
                                @endpermission

                                @permission('cargos.listar')
                                    <li class="list-group-item">
                                        <a href="{{ route('cargos.index') }}" class="text-decoration-none">
                                            <i class="fas fa-user-tag"></i> Cargos
                                        </a>
                                    </li>
                                @endpermission
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h5>Informações do Usuário</h5>
                            <div class="card">
                                <div class="card-body">
                                    <p><strong>Nome:</strong> {{ auth()->user()->name }}</p>
                                    <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                                    
                                    @if(auth()->user()->cargos->count() > 0)
                                        <p><strong>Cargos:</strong></p>
                                        <ul>
                                            @foreach(auth()->user()->cargos as $cargo)
                                                <li>{{ $cargo->nome }}</li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @superadmin
                                        <span class="badge bg-danger">Super Administrador</span>
                                    @endsuperadmin
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Exemplo de Mensagens Condicionais -->
                    <div class="mt-4">
                        <h5>Mensagens Condicionais</h5>
                        
                        @permission('relatorios.gerar')
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Você pode gerar relatórios do sistema.
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Você não tem permissão para gerar relatórios.
                            </div>
                        @endpermission

                        @cargo('Professor')
                            <div class="alert alert-success">
                                <i class="fas fa-chalkboard-teacher"></i>
                                Bem-vindo, Professor! Você pode gerenciar suas turmas.
                            </div>
                        @endcargo
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection