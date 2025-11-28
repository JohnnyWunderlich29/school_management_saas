<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permissao;
use App\Models\Cargo;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar permissões por módulo
        $permissoes = [
            // Dashboard
            ['nome' => 'dashboard.ver', 'descricao' => 'Visualizar dashboard', 'modulo' => 'Dashboard'],
            
            // Alunos
            ['nome' => 'alunos.ver', 'descricao' => 'Visualizar alunos', 'modulo' => 'Alunos'],
            ['nome' => 'alunos.listar', 'descricao' => 'Listar alunos', 'modulo' => 'Alunos'],
            ['nome' => 'alunos.criar', 'descricao' => 'Criar alunos', 'modulo' => 'Alunos'],
            ['nome' => 'alunos.editar', 'descricao' => 'Editar alunos', 'modulo' => 'Alunos'],
            ['nome' => 'alunos.excluir', 'descricao' => 'Excluir alunos', 'modulo' => 'Alunos'],
            ['nome' => 'alunos.visualizar', 'descricao' => 'Visualizar alunos', 'modulo' => 'Alunos'],
            
            // Responsáveis
            ['nome' => 'responsaveis.ver', 'descricao' => 'Visualizar responsáveis', 'modulo' => 'Responsáveis'],
            ['nome' => 'responsaveis.listar', 'descricao' => 'Listar responsáveis', 'modulo' => 'Responsáveis'],
            ['nome' => 'responsaveis.criar', 'descricao' => 'Criar responsáveis', 'modulo' => 'Responsáveis'],
            ['nome' => 'responsaveis.editar', 'descricao' => 'Editar responsáveis', 'modulo' => 'Responsáveis'],
            ['nome' => 'responsaveis.excluir', 'descricao' => 'Excluir responsáveis', 'modulo' => 'Responsáveis'],
            ['nome' => 'responsaveis.visualizar', 'descricao' => 'Visualizar responsáveis', 'modulo' => 'Responsáveis'],
            
            // Funcionários
            ['nome' => 'funcionarios.ver', 'descricao' => 'Visualizar funcionários', 'modulo' => 'Funcionários'],
            ['nome' => 'funcionarios.listar', 'descricao' => 'Listar funcionários', 'modulo' => 'Funcionários'],
            ['nome' => 'funcionarios.criar', 'descricao' => 'Criar funcionários', 'modulo' => 'Funcionários'],
            ['nome' => 'funcionarios.editar', 'descricao' => 'Editar funcionários', 'modulo' => 'Funcionários'],
            ['nome' => 'funcionarios.excluir', 'descricao' => 'Excluir funcionários', 'modulo' => 'Funcionários'],
            ['nome' => 'funcionarios.visualizar', 'descricao' => 'Visualizar funcionários', 'modulo' => 'Funcionários'],

            // Grupos
            ['nome' => 'grupos.ver', 'descricao' => 'Visualizar grupos educacionais', 'modulo' => 'Grupos'],

            // Turnos
            ['nome' => 'turnos.ver', 'descricao' => 'Visualizar turnos', 'modulo' => 'Turnos'],

            // Disciplinas
            ['nome' => 'disciplinas.ver', 'descricao' => 'Visualizar disciplinas', 'modulo' => 'Disciplinas'],
            
            // Escalas
            ['nome' => 'escalas.ver', 'descricao' => 'Visualizar escalas', 'modulo' => 'Escalas'],
            ['nome' => 'escalas.listar', 'descricao' => 'Listar escalas', 'modulo' => 'Escalas'],
            ['nome' => 'escalas.criar', 'descricao' => 'Criar escalas', 'modulo' => 'Escalas'],
            ['nome' => 'escalas.editar', 'descricao' => 'Editar escalas', 'modulo' => 'Escalas'],
            ['nome' => 'escalas.excluir', 'descricao' => 'Excluir escalas', 'modulo' => 'Escalas'],
            ['nome' => 'escalas.visualizar', 'descricao' => 'Visualizar escalas', 'modulo' => 'Escalas'],
            
            // Presenças
            ['nome' => 'presencas.ver', 'descricao' => 'Visualizar presenças', 'modulo' => 'Presenças'],
            ['nome' => 'presencas.listar', 'descricao' => 'Listar presenças', 'modulo' => 'Presenças'],
            ['nome' => 'presencas.criar', 'descricao' => 'Registrar presenças', 'modulo' => 'Presenças'],
            ['nome' => 'presencas.editar', 'descricao' => 'Editar presenças', 'modulo' => 'Presenças'],
            ['nome' => 'presencas.excluir', 'descricao' => 'Excluir presenças', 'modulo' => 'Presenças'],
            ['nome' => 'presencas.visualizar', 'descricao' => 'Visualizar presenças', 'modulo' => 'Presenças'],
            
            // Usuários
            ['nome' => 'usuarios.listar', 'descricao' => 'Listar usuários', 'modulo' => 'Usuários'],
            ['nome' => 'usuarios.criar', 'descricao' => 'Criar usuários', 'modulo' => 'Usuários'],
            ['nome' => 'usuarios.editar', 'descricao' => 'Editar usuários', 'modulo' => 'Usuários'],
            ['nome' => 'usuarios.excluir', 'descricao' => 'Excluir usuários', 'modulo' => 'Usuários'],
            ['nome' => 'usuarios.visualizar', 'descricao' => 'Visualizar usuários', 'modulo' => 'Usuários'],
            
            // Cargos
            ['nome' => 'cargos.listar', 'descricao' => 'Listar cargos', 'modulo' => 'Cargos'],
            ['nome' => 'cargos.criar', 'descricao' => 'Criar cargos', 'modulo' => 'Cargos'],
            ['nome' => 'cargos.editar', 'descricao' => 'Editar cargos', 'modulo' => 'Cargos'],
            ['nome' => 'cargos.excluir', 'descricao' => 'Excluir cargos', 'modulo' => 'Cargos'],
            ['nome' => 'cargos.visualizar', 'descricao' => 'Visualizar cargos', 'modulo' => 'Cargos'],
            
            // Salas
            ['nome' => 'salas.listar', 'descricao' => 'Listar salas', 'modulo' => 'Salas'],
            ['nome' => 'salas.criar', 'descricao' => 'Criar salas', 'modulo' => 'Salas'],
            ['nome' => 'salas.editar', 'descricao' => 'Editar salas', 'modulo' => 'Salas'],
            ['nome' => 'salas.excluir', 'descricao' => 'Excluir salas', 'modulo' => 'Salas'],
            ['nome' => 'salas.visualizar', 'descricao' => 'Visualizar salas', 'modulo' => 'Salas'],
            
            // Planejamentos
            ['nome' => 'planejamentos.listar', 'descricao' => 'Listar planejamentos de aula', 'modulo' => 'Planejamentos'],
            ['nome' => 'planejamentos.criar', 'descricao' => 'Criar planejamentos de aula', 'modulo' => 'Planejamentos'],
            ['nome' => 'planejamentos.editar', 'descricao' => 'Editar planejamentos de aula', 'modulo' => 'Planejamentos'],
            ['nome' => 'planejamentos.excluir', 'descricao' => 'Excluir planejamentos de aula', 'modulo' => 'Planejamentos'],
            ['nome' => 'planejamentos.visualizar', 'descricao' => 'Visualizar planejamentos de aula', 'modulo' => 'Planejamentos'],
            ['nome' => 'planejamentos.aprovar', 'descricao' => 'Aprovar e rejeitar planejamentos de aula', 'modulo' => 'Planejamentos'],

            // Histórico
            ['nome' => 'historico.visualizar', 'descricao' => 'Visualizar histórico de alterações', 'modulo' => 'Histórico'],

            // Perfil
            ['nome' => 'perfil.visualizar', 'descricao' => 'Visualizar próprio perfil', 'modulo' => 'Perfil'],
            ['nome' => 'perfil.editar', 'descricao' => 'Editar próprio perfil', 'modulo' => 'Perfil'],

            // Transferências
            ['nome' => 'transferencias.visualizar', 'descricao' => 'Visualizar transferências', 'modulo' => 'Transferências'],
            ['nome' => 'transferencias.criar', 'descricao' => 'Solicitar transferência de alunos', 'modulo' => 'Transferências'],
            ['nome' => 'transferencias.aprovar', 'descricao' => 'Aprovar transferências de alunos', 'modulo' => 'Transferências'],

            // Comunicação (Conversas)
            ['nome' => 'conversas.ver', 'descricao' => 'Visualizar conversas', 'modulo' => 'Comunicação'],
            ['nome' => 'conversas.criar', 'descricao' => 'Criar conversas', 'modulo' => 'Comunicação'],
            ['nome' => 'conversas.participar', 'descricao' => 'Participar de conversas', 'modulo' => 'Comunicação'],

            // Despesas / Financeiro
            ['nome' => 'despesas.ver', 'descricao' => 'Visualizar despesas', 'modulo' => 'Despesas'],
            ['nome' => 'despesas.criar', 'descricao' => 'Criar despesas', 'modulo' => 'Despesas'],
            ['nome' => 'despesas.editar', 'descricao' => 'Editar despesas', 'modulo' => 'Despesas'],
            ['nome' => 'despesas.cancelar', 'descricao' => 'Cancelar despesas', 'modulo' => 'Despesas'],
            ['nome' => 'finance.admin', 'descricao' => 'Administrar configurações financeiras', 'modulo' => 'Financeiro'],

            // Relatórios
            ['nome' => 'relatorios.ver', 'descricao' => 'Visualizar relatórios', 'modulo' => 'Relatórios'],
            ['nome' => 'relatorios.gerar', 'descricao' => 'Gerar relatórios', 'modulo' => 'Relatórios'],
            ['nome' => 'relatorios.exportar', 'descricao' => 'Exportar relatórios', 'modulo' => 'Relatórios'],

            // Módulos do sistema
            ['nome' => 'modulos.gerenciar', 'descricao' => 'Gerenciar módulos do sistema', 'modulo' => 'Módulos'],

            // Grade de Aulas
            ['nome' => 'grade_aulas.visualizar', 'descricao' => 'Visualizar grade de aulas', 'modulo' => 'Grade de Aulas'],
            ['nome' => 'grade_aulas.listar', 'descricao' => 'Listar grade de aulas', 'modulo' => 'Grade de Aulas'],
            ['nome' => 'grade_aulas.criar', 'descricao' => 'Criar grade de aulas', 'modulo' => 'Grade de Aulas'],
            ['nome' => 'grade_aulas.editar', 'descricao' => 'Editar grade de aulas', 'modulo' => 'Grade de Aulas'],
            ['nome' => 'grade_aulas.excluir', 'descricao' => 'Excluir grade de aulas', 'modulo' => 'Grade de Aulas'],

            // Eventos / Calendário
            ['nome' => 'eventos.ver', 'descricao' => 'Visualizar eventos do calendário', 'modulo' => 'Eventos'],
            ['nome' => 'eventos.criar', 'descricao' => 'Criar eventos do calendário', 'modulo' => 'Eventos'],
            ['nome' => 'eventos.editar', 'descricao' => 'Editar eventos do calendário', 'modulo' => 'Eventos'],
            ['nome' => 'eventos.excluir', 'descricao' => 'Excluir eventos do calendário', 'modulo' => 'Eventos'],
        ];
        
        foreach ($permissoes as $permissao) {
            Permissao::firstOrCreate(
                ['nome' => $permissao['nome']],
                $permissao
            );
        }
        
        // Criar cargo de Administrador
        $cargoAdmin = Cargo::firstOrCreate(
            ['nome' => 'Administrador'],
            [
                'nome' => 'Administrador',
                'descricao' => 'Acesso total ao sistema',
                'ativo' => true
            ]
        );
        
        // Atribuir todas as permissões ao cargo de Administrador
        $todasPermissoes = Permissao::all();
        $cargoAdmin->permissoes()->sync($todasPermissoes->pluck('id'));
        
        // Criar cargo de Professor
        $cargoProfessor = Cargo::firstOrCreate(
            ['nome' => 'Professor'],
            [
                'nome' => 'Professor',
                'descricao' => 'Acesso para professores',
                'ativo' => true
            ]
        );
        
        // Permissões para Professor
        $permissoesProfessor = Permissao::whereIn('nome', [
            'dashboard.ver',
            'alunos.ver',
            'alunos.listar',
            'alunos.visualizar',
            'escalas.ver',
            'escalas.listar',
            'escalas.visualizar',
            'presencas.ver',
            'presencas.listar',
            'presencas.criar',
            'presencas.editar',
            'presencas.visualizar',
            'planejamentos.listar',
            'planejamentos.criar',
            'planejamentos.editar',
            'planejamentos.visualizar'
        ])->get();
        
        $cargoProfessor->permissoes()->sync($permissoesProfessor->pluck('id'));
        
        // Criar cargo de Secretário
        $cargoSecretario = Cargo::firstOrCreate(
            ['nome' => 'Secretário'],
            [
                'nome' => 'Secretário',
                'descricao' => 'Acesso para secretários',
                'ativo' => true
            ]
        );
        
        // Permissões para Secretário
        $permissoesSecretario = Permissao::whereIn('nome', [
            'dashboard.ver',
            'alunos.ver',
            'alunos.listar',
            'alunos.criar',
            'alunos.editar',
            'alunos.visualizar',
            'responsaveis.ver',
            'responsaveis.listar',
            'responsaveis.criar',
            'responsaveis.editar',
            'responsaveis.visualizar',
            'funcionarios.ver',
            'funcionarios.listar',
            'funcionarios.visualizar',
            'escalas.ver',
            'escalas.listar',
            'escalas.visualizar',
            'presencas.ver',
            'presencas.listar',
            'presencas.visualizar',
            'salas.listar',
            'salas.criar',
            'salas.editar',
            'salas.visualizar',
            'planejamentos.listar',
            'planejamentos.visualizar'
        ])->get();
        
        $cargoSecretario->permissoes()->sync($permissoesSecretario->pluck('id'));
        
        // Criar usuário administrador padrão se não existir
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@escola.com'],
            [
                'name' => 'Administrador',
                'email' => 'admin@escola.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now()
            ]
        );
        
        // Atribuir cargo de Administrador ao usuário
        $adminUser->cargos()->sync([$cargoAdmin->id]);
        
        $this->command->info('Permissões, cargos e usuário administrador criados com sucesso!');
    }
}
