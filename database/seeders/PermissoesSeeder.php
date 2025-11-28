<?php

namespace Database\Seeders;

use App\Models\Cargo;
use App\Models\Permissao;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar permissões
        $permissoes = [
            // Dashboard
            ['nome' => 'dashboard.ver', 'modulo' => 'dashboard', 'descricao' => 'Visualizar dashboard'],
            
            // Alunos
            ['nome' => 'alunos.ver', 'modulo' => 'alunos', 'descricao' => 'Visualizar alunos'],
            ['nome' => 'alunos.adicionar', 'modulo' => 'alunos', 'descricao' => 'Adicionar alunos'],
            ['nome' => 'alunos.editar', 'modulo' => 'alunos', 'descricao' => 'Editar alunos'],
            ['nome' => 'alunos.excluir', 'modulo' => 'alunos', 'descricao' => 'Excluir alunos'],
            
            // Responsáveis
            ['nome' => 'responsaveis.ver', 'modulo' => 'responsaveis', 'descricao' => 'Visualizar responsáveis'],
            ['nome' => 'responsaveis.adicionar', 'modulo' => 'responsaveis', 'descricao' => 'Adicionar responsáveis'],
            ['nome' => 'responsaveis.editar', 'modulo' => 'responsaveis', 'descricao' => 'Editar responsáveis'],
            ['nome' => 'responsaveis.excluir', 'modulo' => 'responsaveis', 'descricao' => 'Excluir responsáveis'],
            
            // Funcionários
            ['nome' => 'funcionarios.ver', 'modulo' => 'funcionarios', 'descricao' => 'Visualizar funcionários'],
            ['nome' => 'funcionarios.adicionar', 'modulo' => 'funcionarios', 'descricao' => 'Adicionar funcionários'],
            ['nome' => 'funcionarios.editar', 'modulo' => 'funcionarios', 'descricao' => 'Editar funcionários'],
            ['nome' => 'funcionarios.excluir', 'modulo' => 'funcionarios', 'descricao' => 'Excluir funcionários'],
            
            // Escalas
            ['nome' => 'escalas.ver', 'modulo' => 'escalas', 'descricao' => 'Visualizar escalas'],
            ['nome' => 'escalas.adicionar', 'modulo' => 'escalas', 'descricao' => 'Adicionar escalas'],
            ['nome' => 'escalas.editar', 'modulo' => 'escalas', 'descricao' => 'Editar escalas'],
            ['nome' => 'escalas.excluir', 'modulo' => 'escalas', 'descricao' => 'Excluir escalas'],
            ['nome' => 'escalas.alterar_status', 'modulo' => 'escalas', 'descricao' => 'Alterar status das escalas'],
            ['nome' => 'escalas.ver_todas', 'modulo' => 'escalas', 'descricao' => 'Ver todas as escalas'],
            ['nome' => 'escalas.ver_proprias', 'modulo' => 'escalas', 'descricao' => 'Ver apenas suas próprias escalas'],
            
            // Presenças
            ['nome' => 'presencas.ver', 'modulo' => 'presencas', 'descricao' => 'Visualizar presenças'],
            ['nome' => 'presencas.adicionar', 'modulo' => 'presencas', 'descricao' => 'Adicionar presenças'],
            ['nome' => 'presencas.editar', 'modulo' => 'presencas', 'descricao' => 'Editar presenças'],
            ['nome' => 'presencas.excluir', 'modulo' => 'presencas', 'descricao' => 'Excluir presenças'],
            
            // Usuários e Permissões
            ['nome' => 'usuarios.ver', 'modulo' => 'usuarios', 'descricao' => 'Visualizar usuários'],
            ['nome' => 'usuarios.adicionar', 'modulo' => 'usuarios', 'descricao' => 'Adicionar usuários'],
            ['nome' => 'usuarios.editar', 'modulo' => 'usuarios', 'descricao' => 'Editar usuários'],
            ['nome' => 'usuarios.excluir', 'modulo' => 'usuarios', 'descricao' => 'Excluir usuários'],
            ['nome' => 'cargos.gerenciar', 'modulo' => 'cargos', 'descricao' => 'Gerenciar cargos e permissões'],
            
            // Relatórios
            ['nome' => 'relatorios.gerar', 'modulo' => 'relatorios', 'descricao' => 'Gerar relatórios'],
            ['nome' => 'relatorios.exportar', 'modulo' => 'relatorios', 'descricao' => 'Exportar relatórios'],
            
            // Configurações
            ['nome' => 'configuracoes.ver', 'modulo' => 'configuracoes', 'descricao' => 'Visualizar configurações'],
            ['nome' => 'configuracoes.editar', 'modulo' => 'configuracoes', 'descricao' => 'Editar configurações do sistema'],
            
            // Grupos Educacionais
            ['nome' => 'grupos.ver', 'modulo' => 'grupos', 'descricao' => 'Visualizar grupos educacionais'],
            ['nome' => 'grupos.adicionar', 'modulo' => 'grupos', 'descricao' => 'Adicionar grupos educacionais'],
            ['nome' => 'grupos.editar', 'modulo' => 'grupos', 'descricao' => 'Editar grupos educacionais'],
            ['nome' => 'grupos.excluir', 'modulo' => 'grupos', 'descricao' => 'Excluir grupos educacionais'],
            
            // Turnos
            ['nome' => 'turnos.ver', 'modulo' => 'turnos', 'descricao' => 'Visualizar turnos'],
            ['nome' => 'turnos.adicionar', 'modulo' => 'turnos', 'descricao' => 'Adicionar turnos'],
            ['nome' => 'turnos.editar', 'modulo' => 'turnos', 'descricao' => 'Editar turnos'],
            ['nome' => 'turnos.excluir', 'modulo' => 'turnos', 'descricao' => 'Excluir turnos'],
            
            // Modalidades de Ensino
            ['nome' => 'modalidades.ver', 'modulo' => 'modalidades', 'descricao' => 'Visualizar modalidades de ensino'],
            ['nome' => 'modalidades.adicionar', 'modulo' => 'modalidades', 'descricao' => 'Adicionar modalidades de ensino'],
            ['nome' => 'modalidades.editar', 'modulo' => 'modalidades', 'descricao' => 'Editar modalidades de ensino'],
            ['nome' => 'modalidades.excluir', 'modulo' => 'modalidades', 'descricao' => 'Excluir modalidades de ensino'],
            
            // Disciplinas
            ['nome' => 'disciplinas.ver', 'modulo' => 'disciplinas', 'descricao' => 'Visualizar disciplinas'],
            ['nome' => 'disciplinas.adicionar', 'modulo' => 'disciplinas', 'descricao' => 'Adicionar disciplinas'],
            ['nome' => 'disciplinas.editar', 'modulo' => 'disciplinas', 'descricao' => 'Editar disciplinas'],
            ['nome' => 'disciplinas.excluir', 'modulo' => 'disciplinas', 'descricao' => 'Excluir disciplinas'],
            
            // Turmas
            ['nome' => 'turmas.ver', 'modulo' => 'turmas', 'descricao' => 'Visualizar turmas'],
            ['nome' => 'turmas.adicionar', 'modulo' => 'turmas', 'descricao' => 'Adicionar turmas'],
            ['nome' => 'turmas.editar', 'modulo' => 'turmas', 'descricao' => 'Editar turmas'],
            ['nome' => 'turmas.excluir', 'modulo' => 'turmas', 'descricao' => 'Excluir turmas'],
            
            // Comunicação
            ['nome' => 'comunicados.ver', 'modulo' => 'comunicacao', 'descricao' => 'Visualizar comunicados'],
            ['nome' => 'comunicados.criar', 'modulo' => 'comunicacao', 'descricao' => 'Criar comunicados'],
            ['nome' => 'comunicados.editar', 'modulo' => 'comunicacao', 'descricao' => 'Editar comunicados'],
            ['nome' => 'comunicados.excluir', 'modulo' => 'comunicacao', 'descricao' => 'Excluir comunicados'],
            ['nome' => 'comunicados.publicar', 'modulo' => 'comunicacao', 'descricao' => 'Publicar comunicados'],
            ['nome' => 'conversas.ver', 'modulo' => 'comunicacao', 'descricao' => 'Visualizar conversas'],
            ['nome' => 'conversas.criar', 'modulo' => 'comunicacao', 'descricao' => 'Criar conversas'],
            ['nome' => 'conversas.participar', 'modulo' => 'comunicacao', 'descricao' => 'Participar de conversas']
        ];

        foreach ($permissoes as $permissao) {
            Permissao::firstOrCreate(
                ['nome' => $permissao['nome']],
                $permissao
            );
        }

        // Criar cargos
        $cargos = [
            [
                'nome' => 'Super Administrador',
                'descricao' => 'Acesso total ao sistema',
                'permissoes' => array_column($permissoes, 'nome') // Todas as permissões
            ],
            [
                'nome' => 'Administrador',
                'descricao' => 'Administrador do sistema',
                'permissoes' => [
                    'dashboard.ver',
                    'alunos.ver', 'alunos.adicionar', 'alunos.editar', 'alunos.excluir',
                    'responsaveis.ver', 'responsaveis.adicionar', 'responsaveis.editar', 'responsaveis.excluir',
                    'funcionarios.ver', 'funcionarios.adicionar', 'funcionarios.editar', 'funcionarios.excluir',
                    'escalas.ver', 'escalas.adicionar', 'escalas.editar', 'escalas.excluir', 'escalas.alterar_status', 'escalas.ver_todas',
                    'presencas.ver', 'presencas.adicionar', 'presencas.editar', 'presencas.excluir',
                    'usuarios.ver', 'usuarios.adicionar', 'usuarios.editar',
                    'relatorios.gerar', 'relatorios.exportar',
                    'configuracoes.ver',
                    'grupos.ver', 'grupos.adicionar', 'grupos.editar', 'grupos.excluir',
                    'turnos.ver', 'turnos.adicionar', 'turnos.editar', 'turnos.excluir',
                    'modalidades.ver', 'modalidades.adicionar', 'modalidades.editar', 'modalidades.excluir',
                    'disciplinas.ver', 'disciplinas.adicionar', 'disciplinas.editar', 'disciplinas.excluir',
                    'turmas.ver', 'turmas.adicionar', 'turmas.editar', 'turmas.excluir',
                    'comunicados.ver', 'comunicados.criar', 'comunicados.editar', 'comunicados.excluir', 'comunicados.publicar',
                    'conversas.ver', 'conversas.criar', 'conversas.participar'
                ]
            ],
            [
                'nome' => 'Coordenador',
                'descricao' => 'Coordenador pedagógico',
                'permissoes' => [
                    'dashboard.ver',
                    'alunos.ver', 'alunos.adicionar', 'alunos.editar',
                    'responsaveis.ver', 'responsaveis.adicionar', 'responsaveis.editar',
                    'funcionarios.ver',
                    'escalas.ver', 'escalas.adicionar', 'escalas.editar', 'escalas.ver_todas',
                    'presencas.ver', 'presencas.adicionar', 'presencas.editar',
                    'relatorios.gerar',
                    'grupos.ver', 'grupos.adicionar', 'grupos.editar',
                    'turnos.ver', 'turnos.adicionar', 'turnos.editar',
                    'modalidades.ver', 'modalidades.adicionar', 'modalidades.editar',
                    'disciplinas.ver', 'disciplinas.adicionar', 'disciplinas.editar',
                    'turmas.ver', 'turmas.adicionar', 'turmas.editar',
                    'comunicados.ver', 'comunicados.criar', 'comunicados.editar', 'comunicados.publicar',
                    'conversas.ver', 'conversas.criar', 'conversas.participar'
                ]
            ],
            [
                'nome' => 'Professor',
                'descricao' => 'Professor do sistema',
                'permissoes' => [
                    'dashboard.ver',
                    'alunos.ver',
                    'escalas.ver', 'escalas.alterar_status', 'escalas.ver_proprias',
                    'presencas.ver', 'presencas.adicionar', 'presencas.editar',
                    'comunicados.ver',
                    'conversas.ver', 'conversas.participar'
                ]
            ],
            [
                'nome' => 'Secretário',
                'descricao' => 'Secretário escolar',
                'permissoes' => [
                    'dashboard.ver',
                    'alunos.ver', 'alunos.adicionar', 'alunos.editar',
                    'responsaveis.ver', 'responsaveis.adicionar', 'responsaveis.editar',
                    'funcionarios.ver',
                    'escalas.ver',
                    'presencas.ver',
                    'relatorios.gerar',
                    'comunicados.ver',
                    'conversas.ver', 'conversas.participar'
                ]
            ]
        ];

        foreach ($cargos as $cargoData) {
            $cargo = Cargo::firstOrCreate(
                ['nome' => $cargoData['nome']],
                [
                    'nome' => $cargoData['nome'],
                    'descricao' => $cargoData['descricao']
                ]
            );

            // Associar permissões ao cargo
            $permissoesIds = Permissao::whereIn('nome', $cargoData['permissoes'])->pluck('id');
            $cargo->permissoes()->sync($permissoesIds);
        }

        // Criar usuário super administrador
        $superAdmin = User::firstOrCreate(
            ['email' => 'johnny@teste.com.br'],
            [
                'name' => 'Johnny Super Admin',
                'email' => 'johnny@teste.com.br',
                'password' => bcrypt('123456789')
            ]
        );

        // Associar cargo de Super Administrador
        $cargoSuperAdmin = Cargo::where('nome', 'Super Administrador')->first();
        if ($cargoSuperAdmin) {
            $superAdmin->cargos()->syncWithoutDetaching([$cargoSuperAdmin->id]);
        }

        $this->command->info('Permissões, cargos e usuário super administrador criados com sucesso!');
    }
}
