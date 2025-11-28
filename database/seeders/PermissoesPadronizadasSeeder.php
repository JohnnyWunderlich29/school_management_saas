<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permissao;
use App\Models\Cargo;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissoesPadronizadasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar permissões existentes para evitar conflitos
        $this->command->info('Limpando permissões existentes...');
        
        // Criar permissões padronizadas por módulo
        $permissoes = [
            // Dashboard
            ['nome' => 'dashboard.ver', 'descricao' => 'Visualizar dashboard', 'modulo' => 'Dashboard'],
            
            // Alunos
            ['nome' => 'alunos.ver', 'descricao' => 'Visualizar alunos', 'modulo' => 'Alunos'],
            ['nome' => 'alunos.criar', 'descricao' => 'Criar alunos', 'modulo' => 'Alunos'],
            ['nome' => 'alunos.editar', 'descricao' => 'Editar alunos', 'modulo' => 'Alunos'],
            ['nome' => 'alunos.excluir', 'descricao' => 'Excluir alunos', 'modulo' => 'Alunos'],
            
            // Responsáveis
            ['nome' => 'responsaveis.listar', 'descricao' => 'Visualizar responsáveis', 'modulo' => 'Responsáveis'],
            ['nome' => 'responsaveis.criar', 'descricao' => 'Criar responsáveis', 'modulo' => 'Responsáveis'],
            ['nome' => 'responsaveis.editar', 'descricao' => 'Editar responsáveis', 'modulo' => 'Responsáveis'],
            ['nome' => 'responsaveis.excluir', 'descricao' => 'Excluir responsáveis', 'modulo' => 'Responsáveis'],
            
            // Funcionários
            ['nome' => 'funcionarios.ver', 'descricao' => 'Visualizar funcionários', 'modulo' => 'Funcionários'],
            ['nome' => 'funcionarios.criar', 'descricao' => 'Criar funcionários', 'modulo' => 'Funcionários'],
            ['nome' => 'funcionarios.editar', 'descricao' => 'Editar funcionários', 'modulo' => 'Funcionários'],
            ['nome' => 'funcionarios.excluir', 'descricao' => 'Excluir funcionários', 'modulo' => 'Funcionários'],
            
            // Grupos (adicionado)
            ['nome' => 'grupos.ver', 'descricao' => 'Visualizar grupos educacionais', 'modulo' => 'Grupos'],
            
            // Turnos (adicionado)
            ['nome' => 'turnos.ver', 'descricao' => 'Visualizar turnos', 'modulo' => 'Turnos'],
            
            // Disciplinas (adicionado)
            ['nome' => 'disciplinas.ver', 'descricao' => 'Visualizar disciplinas', 'modulo' => 'Disciplinas'],
            
            // Escalas
            ['nome' => 'escalas.ver', 'descricao' => 'Visualizar escalas', 'modulo' => 'Escalas'],
            ['nome' => 'escalas.criar', 'descricao' => 'Criar escalas', 'modulo' => 'Escalas'],
            ['nome' => 'escalas.editar', 'descricao' => 'Editar escalas', 'modulo' => 'Escalas'],
            ['nome' => 'escalas.excluir', 'descricao' => 'Excluir escalas', 'modulo' => 'Escalas'],
            
            // Presenças
            ['nome' => 'presencas.ver', 'descricao' => 'Visualizar presenças', 'modulo' => 'Presenças'],
            ['nome' => 'presencas.criar', 'descricao' => 'Registrar presenças', 'modulo' => 'Presenças'],
            ['nome' => 'presencas.editar', 'descricao' => 'Editar presenças', 'modulo' => 'Presenças'],
            ['nome' => 'presencas.excluir', 'descricao' => 'Excluir presenças', 'modulo' => 'Presenças'],
            
            // Usuários
            ['nome' => 'usuarios.listar', 'descricao' => 'Visualizar usuários', 'modulo' => 'Usuários'],
            ['nome' => 'usuarios.criar', 'descricao' => 'Criar usuários', 'modulo' => 'Usuários'],
            ['nome' => 'usuarios.editar', 'descricao' => 'Editar usuários', 'modulo' => 'Usuários'],
            ['nome' => 'usuarios.excluir', 'descricao' => 'Excluir usuários', 'modulo' => 'Usuários'],
            
            // Cargos
            ['nome' => 'cargos.listar', 'descricao' => 'Visualizar cargos', 'modulo' => 'Cargos'],
            ['nome' => 'cargos.criar', 'descricao' => 'Criar cargos', 'modulo' => 'Cargos'],
            ['nome' => 'cargos.editar', 'descricao' => 'Editar cargos', 'modulo' => 'Cargos'],
            ['nome' => 'cargos.excluir', 'descricao' => 'Excluir cargos', 'modulo' => 'Cargos'],
            
            // Salas
            ['nome' => 'salas.listar', 'descricao' => 'Visualizar salas', 'modulo' => 'Salas'],
            ['nome' => 'salas.criar', 'descricao' => 'Criar salas', 'modulo' => 'Salas'],
            ['nome' => 'salas.editar', 'descricao' => 'Editar salas', 'modulo' => 'Salas'],
            ['nome' => 'salas.excluir', 'descricao' => 'Excluir salas', 'modulo' => 'Salas'],
            
            // Despesas
            ['nome' => 'despesas.ver', 'descricao' => 'Visualizar despesas', 'modulo' => 'Despesas'],
            ['nome' => 'despesas.criar', 'descricao' => 'Criar despesas', 'modulo' => 'Despesas'],
            ['nome' => 'despesas.editar', 'descricao' => 'Editar despesas', 'modulo' => 'Despesas'],
            ['nome' => 'despesas.cancelar', 'descricao' => 'Cancelar despesas', 'modulo' => 'Despesas'],

            // Financeiro — Recebimentos e Recorrências (granular)
            ['nome' => 'recebimentos.ver', 'descricao' => 'Visualizar recebimentos', 'modulo' => 'Recebimentos'],
            ['nome' => 'recebimentos.editar', 'descricao' => 'Editar recebimentos', 'modulo' => 'Recebimentos'],
            ['nome' => 'recebimentos.exportar', 'descricao' => 'Exportar recebimentos', 'modulo' => 'Recebimentos'],
            ['nome' => 'recorrencias.ver', 'descricao' => 'Visualizar recorrências', 'modulo' => 'Recorrências'],
            ['nome' => 'recorrencias.editar', 'descricao' => 'Editar recorrências', 'modulo' => 'Recorrências'],
            ['nome' => 'recorrencias.cancelar', 'descricao' => 'Cancelar recorrências', 'modulo' => 'Recorrências'],
            
            // Planejamentos
            ['nome' => 'planejamentos.visualizar', 'descricao' => 'Visualizar planejamentos de aula', 'modulo' => 'Planejamentos'],
            ['nome' => 'planejamentos.criar', 'descricao' => 'Criar planejamentos de aula', 'modulo' => 'Planejamentos'],
            ['nome' => 'planejamentos.editar', 'descricao' => 'Editar planejamentos de aula', 'modulo' => 'Planejamentos'],
            ['nome' => 'planejamentos.excluir', 'descricao' => 'Excluir planejamentos de aula', 'modulo' => 'Planejamentos'],
            ['nome' => 'planejamentos.aprovar', 'descricao' => 'Aprovar e rejeitar planejamentos de aula', 'modulo' => 'Planejamentos'],
            
            // Histórico (adicionado)
            ['nome' => 'historico.visualizar', 'descricao' => 'Visualizar histórico de alterações', 'modulo' => 'Histórico'],
            
            // Perfil de usuário (adicionado)
            ['nome' => 'perfil.visualizar', 'descricao' => 'Visualizar próprio perfil', 'modulo' => 'Perfil'],
            ['nome' => 'perfil.editar', 'descricao' => 'Editar próprio perfil', 'modulo' => 'Perfil'],
            
            // Transferências de alunos (adicionado)
            ['nome' => 'transferencias.visualizar', 'descricao' => 'Visualizar transferências', 'modulo' => 'Transferências'],
            ['nome' => 'transferencias.criar', 'descricao' => 'Solicitar transferência de alunos', 'modulo' => 'Transferências'],
            ['nome' => 'transferencias.aprovar', 'descricao' => 'Aprovar transferências de alunos', 'modulo' => 'Transferências'],
            
            // Módulos do sistema
            ['nome' => 'modulos.gerenciar', 'descricao' => 'Gerenciar módulos do sistema', 'modulo' => 'Módulos'],
            
            // Comunicação
            ['nome' => 'conversas.ver', 'descricao' => 'Visualizar conversas', 'modulo' => 'Comunicação'],
            ['nome' => 'conversas.criar', 'descricao' => 'Criar conversas', 'modulo' => 'Comunicação'],
            ['nome' => 'conversas.participar', 'descricao' => 'Participar de conversas', 'modulo' => 'Comunicação'],
        ];
        
        $this->command->info('Criando permissões padronizadas...');
        foreach ($permissoes as $permissao) {
            Permissao::updateOrCreate(
                ['nome' => $permissao['nome']],
                $permissao
            );
        }
        
        // Criar cargos específicos para usuários de escolas
        $this->criarCargosEscola();
        
        $this->command->info('Permissões padronizadas criadas com sucesso!');
    }
    
    private function criarCargosEscola(): void
    {
        // Cargo Administrador de Escola
        $cargoAdminEscola = Cargo::updateOrCreate(
            ['nome' => 'Administrador de Escola'],
            [
                'nome' => 'Administrador de Escola',
                'descricao' => 'Administrador com acesso completo à escola',
                'ativo' => true
            ]
        );
        
        // Permissões para Administrador de Escola
        $permissoesAdminEscola = Permissao::whereIn('nome', [
            'dashboard.ver',
            'alunos.ver', 'alunos.criar', 'alunos.editar', 'alunos.excluir',
            'responsaveis.listar', 'responsaveis.criar', 'responsaveis.editar', 'responsaveis.excluir',
            'funcionarios.ver', 'funcionarios.criar', 'funcionarios.editar', 'funcionarios.excluir',
            'grupos.ver', 'turnos.ver', 'disciplinas.ver',
            'escalas.ver', 'escalas.criar', 'escalas.editar', 'escalas.excluir',
            'presencas.ver', 'presencas.criar', 'presencas.editar', 'presencas.excluir',
            'usuarios.listar', 'usuarios.criar', 'usuarios.editar', 'usuarios.excluir',
            'cargos.listar', 'cargos.criar', 'cargos.editar', 'cargos.excluir',
            'salas.listar', 'salas.criar', 'salas.editar', 'salas.excluir',
            'despesas.ver', 'despesas.criar', 'despesas.editar', 'despesas.cancelar',
            'recebimentos.ver', 'recebimentos.editar', 'recebimentos.exportar',
            'recorrencias.ver', 'recorrencias.editar', 'recorrencias.cancelar',
            'planejamentos.visualizar', 'planejamentos.criar', 'planejamentos.editar', 'planejamentos.excluir', 'planejamentos.aprovar',
            'historico.visualizar',
            'perfil.visualizar', 'perfil.editar',
            'transferencias.visualizar', 'transferencias.criar', 'transferencias.aprovar',
            'conversas.ver', 'conversas.criar', 'conversas.participar',
            'eventos.ver', 'eventos.criar', 'eventos.editar', 'eventos.excluir',
            'modulos.gerenciar'
        ])->get();
        
        $cargoAdminEscola->permissoes()->sync($permissoesAdminEscola->pluck('id'));
        
        // Cargo Coordenador
        $cargoCoordenador = Cargo::updateOrCreate(
            ['nome' => 'Coordenador'],
            [
                'nome' => 'Coordenador',
                'descricao' => 'Coordenador pedagógico',
                'ativo' => true
            ]
        );
        
        // Permissões para Coordenador
        $permissoesCoordenador = Permissao::whereIn('nome', [
            'dashboard.ver',
            'alunos.ver', 'alunos.criar', 'alunos.editar',
            'responsaveis.listar', 'responsaveis.criar', 'responsaveis.editar',
            'funcionarios.ver',
            'grupos.ver', 'turnos.ver', 'disciplinas.ver',
            'escalas.ver', 'escalas.criar', 'escalas.editar',
            'presencas.ver', 'presencas.criar', 'presencas.editar',
            'despesas.ver',
            'recebimentos.ver', 'recebimentos.editar', 'recebimentos.exportar',
            'recorrencias.ver', 'recorrencias.editar',
            'planejamentos.visualizar', 'planejamentos.aprovar',
            'historico.visualizar',
            'perfil.visualizar', 'perfil.editar',
            'transferencias.visualizar', 'transferencias.criar', 'transferencias.aprovar',
            'conversas.ver', 'conversas.criar', 'conversas.participar',
            'eventos.ver', 'eventos.criar', 'eventos.editar'
        ])->get();
        
        $cargoCoordenador->permissoes()->sync($permissoesCoordenador->pluck('id'));
        
        // Cargo Professor
        $cargoProfessor = Cargo::updateOrCreate(
            ['nome' => 'Professor'],
            [
                'nome' => 'Professor',
                'descricao' => 'Professor da escola',
                'ativo' => true
            ]
        );
        
        // Permissões para Professor
        $permissoesProfessor = Permissao::whereIn('nome', [
            'dashboard.ver',
            'alunos.ver',
            'grupos.ver', 'turnos.ver', 'disciplinas.ver',
            'escalas.ver',
            'presencas.ver', 'presencas.criar', 'presencas.editar',
            'planejamentos.visualizar', 'planejamentos.criar', 'planejamentos.editar',
            'perfil.visualizar', 'perfil.editar',
            'conversas.ver', 'conversas.participar',
            'eventos.ver'
        ])->get();
        
        $cargoProfessor->permissoes()->sync($permissoesProfessor->pluck('id'));
        
        // Cargo Secretário
        $cargoSecretario = Cargo::updateOrCreate(
            ['nome' => 'Secretário'],
            [
                'nome' => 'Secretário',
                'descricao' => 'Secretário escolar',
                'ativo' => true
            ]
        );
        
        // Permissões para Secretário
        $permissoesSecretario = Permissao::whereIn('nome', [
            'dashboard.ver',
            'alunos.ver', 'alunos.criar', 'alunos.editar',
            'responsaveis.listar', 'responsaveis.criar', 'responsaveis.editar',
            'funcionarios.ver',
            'grupos.ver', 'turnos.ver', 'disciplinas.ver',
            'escalas.ver',
            'presencas.ver',
            'salas.listar', 'salas.criar', 'salas.editar',
            'despesas.ver', 'despesas.criar', 'despesas.editar', 'despesas.cancelar',
            'planejamentos.visualizar',
            'perfil.visualizar', 'perfil.editar',
            'transferencias.visualizar', 'transferencias.criar',
            'conversas.ver', 'conversas.participar',
            'eventos.ver'
        ])->get();
        
        $cargoSecretario->permissoes()->sync($permissoesSecretario->pluck('id'));
        
        $this->command->info('Cargos de escola criados e configurados!');
    }
}