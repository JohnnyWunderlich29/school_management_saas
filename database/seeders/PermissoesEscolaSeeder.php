<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permissao;
use App\Models\Cargo;
use Illuminate\Support\Facades\DB;

class PermissoesEscolaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar permissões e cargos existentes
        DB::table('cargo_permissoes')->delete();
        Cargo::query()->delete();
        Permissao::query()->delete();

        // Permissões específicas para usuários de escolas (sem permissões administrativas desnecessárias)
        $permissoes = [
            // Dashboard
            ['nome' => 'dashboard.ver', 'modulo' => 'dashboard', 'descricao' => 'Visualizar dashboard'],

            // Alunos
            ['nome' => 'alunos.listar', 'modulo' => 'alunos', 'descricao' => 'Listar alunos'],
            ['nome' => 'alunos.ver', 'modulo' => 'alunos', 'descricao' => 'Visualizar alunos'],
            ['nome' => 'alunos.criar', 'modulo' => 'alunos', 'descricao' => 'Criar alunos'],
            ['nome' => 'alunos.editar', 'modulo' => 'alunos', 'descricao' => 'Editar alunos'],
            ['nome' => 'alunos.excluir', 'modulo' => 'alunos', 'descricao' => 'Excluir alunos'],

            // Responsáveis
            ['nome' => 'responsaveis.listar', 'modulo' => 'responsaveis', 'descricao' => 'Listar responsáveis'],
            ['nome' => 'responsaveis.criar', 'modulo' => 'responsaveis', 'descricao' => 'Criar responsáveis'],
            ['nome' => 'responsaveis.editar', 'modulo' => 'responsaveis', 'descricao' => 'Editar responsáveis'],
            ['nome' => 'responsaveis.excluir', 'modulo' => 'responsaveis', 'descricao' => 'Excluir responsáveis'],

            // Funcionários
            ['nome' => 'funcionarios.listar', 'modulo' => 'funcionarios', 'descricao' => 'Listar funcionários'],
            ['nome' => 'funcionarios.ver', 'modulo' => 'funcionarios', 'descricao' => 'Visualizar funcionários'],
            ['nome' => 'funcionarios.criar', 'modulo' => 'funcionarios', 'descricao' => 'Criar funcionários'],
            ['nome' => 'funcionarios.editar', 'modulo' => 'funcionarios', 'descricao' => 'Editar funcionários'],
            ['nome' => 'funcionarios.excluir', 'modulo' => 'funcionarios', 'descricao' => 'Excluir funcionários'],

            // Grupos
            ['nome' => 'grupos.ver', 'modulo' => 'grupos', 'descricao' => 'Visualizar grupos'],
            ['nome' => 'grupos.criar', 'modulo' => 'grupos', 'descricao' => 'Criar grupos'],
            ['nome' => 'grupos.editar', 'modulo' => 'grupos', 'descricao' => 'Editar grupos'],
            ['nome' => 'grupos.excluir', 'modulo' => 'grupos', 'descricao' => 'Excluir grupos'],

            // Turnos
            ['nome' => 'turnos.ver', 'modulo' => 'turnos', 'descricao' => 'Visualizar turnos'],
            ['nome' => 'turnos.criar', 'modulo' => 'turnos', 'descricao' => 'Criar turnos'],
            ['nome' => 'turnos.editar', 'modulo' => 'turnos', 'descricao' => 'Editar turnos'],
            ['nome' => 'turnos.excluir', 'modulo' => 'turnos', 'descricao' => 'Excluir turnos'],

            // Disciplinas
            ['nome' => 'disciplinas.ver', 'modulo' => 'disciplinas', 'descricao' => 'Visualizar disciplinas'],
            ['nome' => 'disciplinas.criar', 'modulo' => 'disciplinas', 'descricao' => 'Criar disciplinas'],
            ['nome' => 'disciplinas.editar', 'modulo' => 'disciplinas', 'descricao' => 'Editar disciplinas'],
            ['nome' => 'disciplinas.excluir', 'modulo' => 'disciplinas', 'descricao' => 'Excluir disciplinas'],

            // Escalas
            ['nome' => 'escalas.ver', 'modulo' => 'escalas', 'descricao' => 'Visualizar escalas'],
            ['nome' => 'escalas.criar', 'modulo' => 'escalas', 'descricao' => 'Criar escalas'],
            ['nome' => 'escalas.editar', 'modulo' => 'escalas', 'descricao' => 'Editar escalas'],
            ['nome' => 'escalas.excluir', 'modulo' => 'escalas', 'descricao' => 'Excluir escalas'],
            ['nome' => 'escalas.alterar_status', 'modulo' => 'escalas', 'descricao' => 'Alterar status das escalas'],
            ['nome' => 'escalas.ver_todas', 'modulo' => 'escalas', 'descricao' => 'Ver todas as escalas'],
            ['nome' => 'escalas.ver_proprias', 'modulo' => 'escalas', 'descricao' => 'Ver apenas próprias escalas'],

            // Presenças
            ['nome' => 'presencas.ver', 'modulo' => 'presencas', 'descricao' => 'Visualizar presenças'],
            ['nome' => 'presencas.criar', 'modulo' => 'presencas', 'descricao' => 'Registrar presenças'],
            ['nome' => 'presencas.editar', 'modulo' => 'presencas', 'descricao' => 'Editar presenças'],
            ['nome' => 'presencas.excluir', 'modulo' => 'presencas', 'descricao' => 'Excluir presenças'],

            // Usuários (apenas para gestão interna da escola)
            ['nome' => 'usuarios.listar', 'modulo' => 'usuarios', 'descricao' => 'Listar usuários'],
            ['nome' => 'usuarios.criar', 'modulo' => 'usuarios', 'descricao' => 'Criar usuários'],
            ['nome' => 'usuarios.editar', 'modulo' => 'usuarios', 'descricao' => 'Editar usuários'],

            // Cargos (apenas visualização para escolas)
            ['nome' => 'cargos.listar', 'modulo' => 'cargos', 'descricao' => 'Listar cargos'],

            // Salas
            ['nome' => 'salas.listar', 'modulo' => 'salas', 'descricao' => 'Listar salas'],
            ['nome' => 'salas.criar', 'modulo' => 'salas', 'descricao' => 'Criar salas'],
            ['nome' => 'salas.editar', 'modulo' => 'salas', 'descricao' => 'Editar salas'],
            ['nome' => 'salas.excluir', 'modulo' => 'salas', 'descricao' => 'Excluir salas'],

            // Planejamentos
            ['nome' => 'planejamentos.listar', 'modulo' => 'planejamentos', 'descricao' => 'Listar planejamentos'],
            ['nome' => 'planejamentos.criar', 'modulo' => 'planejamentos', 'descricao' => 'Criar planejamentos'],
            ['nome' => 'planejamentos.editar', 'modulo' => 'planejamentos', 'descricao' => 'Editar planejamentos'],
            ['nome' => 'planejamentos.excluir', 'modulo' => 'planejamentos', 'descricao' => 'Excluir planejamentos'],
            ['nome' => 'planejamentos.aprovar', 'modulo' => 'planejamentos', 'descricao' => 'Aprovar planejamentos'],

            // Histórico
            ['nome' => 'historico.ver', 'modulo' => 'historico', 'descricao' => 'Visualizar histórico'],

            // Perfil
            ['nome' => 'perfil.ver', 'modulo' => 'perfil', 'descricao' => 'Visualizar perfil'],
            ['nome' => 'perfil.editar', 'modulo' => 'perfil', 'descricao' => 'Editar perfil'],

            // Transferências
            ['nome' => 'transferencias.ver', 'modulo' => 'transferencias', 'descricao' => 'Visualizar transferências'],
            ['nome' => 'transferencias.criar', 'modulo' => 'transferencias', 'descricao' => 'Criar transferências'],
            ['nome' => 'transferencias.aprovar', 'modulo' => 'transferencias', 'descricao' => 'Aprovar transferências'],

            // Comunicação
            ['nome' => 'conversas.ver', 'modulo' => 'comunicacao', 'descricao' => 'Visualizar conversas'],
            ['nome' => 'conversas.criar', 'modulo' => 'comunicacao', 'descricao' => 'Criar conversas'],
            ['nome' => 'conversas.participar', 'modulo' => 'comunicacao', 'descricao' => 'Participar de conversas']
        ];

        // Despesas
        $permissoes = array_merge($permissoes, [
            ['nome' => 'despesas.ver', 'modulo' => 'despesas', 'descricao' => 'Visualizar despesas'],
            ['nome' => 'despesas.criar', 'modulo' => 'despesas', 'descricao' => 'Criar despesas'],
            ['nome' => 'despesas.editar', 'modulo' => 'despesas', 'descricao' => 'Editar despesas'],
            ['nome' => 'despesas.cancelar', 'modulo' => 'despesas', 'descricao' => 'Cancelar despesas'],
        ]);

        foreach ($permissoes as $permissao) {
            Permissao::create($permissao);
        }

        // Criar cargos específicos para escolas (sem permissões administrativas desnecessárias)
        $cargos = [
            [
                'nome' => 'Administrador de Escola',
                'descricao' => 'Administrador com acesso completo à escola',
                'permissoes' => [
                    'dashboard.ver',
                    'alunos.listar', 'alunos.ver', 'alunos.criar', 'alunos.editar', 'alunos.excluir',
                    'responsaveis.listar', 'responsaveis.criar', 'responsaveis.editar', 'responsaveis.excluir',
                    'funcionarios.listar', 'funcionarios.ver', 'funcionarios.criar', 'funcionarios.editar', 'funcionarios.excluir',
                    'grupos.ver', 'grupos.criar', 'grupos.editar', 'grupos.excluir',
                    'turnos.ver', 'turnos.criar', 'turnos.editar', 'turnos.excluir',
                    'disciplinas.ver', 'disciplinas.criar', 'disciplinas.editar', 'disciplinas.excluir',
                    'escalas.ver', 'escalas.criar', 'escalas.editar', 'escalas.excluir', 'escalas.alterar_status', 'escalas.ver_todas',
                    'presencas.ver', 'presencas.criar', 'presencas.editar', 'presencas.excluir',
                    'usuarios.listar', 'usuarios.criar', 'usuarios.editar',
                    'cargos.listar',
                    'salas.listar', 'salas.criar', 'salas.editar', 'salas.excluir',
                    'planejamentos.listar', 'planejamentos.criar', 'planejamentos.editar', 'planejamentos.excluir', 'planejamentos.aprovar',
                    'historico.ver',
                    'perfil.ver', 'perfil.editar',
                    'transferencias.ver', 'transferencias.criar', 'transferencias.aprovar',
                    'conversas.ver', 'conversas.criar', 'conversas.participar',
                    'despesas.ver', 'despesas.criar', 'despesas.editar', 'despesas.cancelar'
                ]
            ],
            [
                'nome' => 'Coordenador',
                'descricao' => 'Coordenador pedagógico',
                'permissoes' => [
                    'dashboard.ver',
                    'alunos.listar', 'alunos.ver', 'alunos.criar', 'alunos.editar',
                    'responsaveis.listar', 'responsaveis.criar', 'responsaveis.editar',
                    'funcionarios.listar', 'funcionarios.ver',
                    'grupos.ver', 'grupos.criar', 'grupos.editar',
                    'turnos.ver', 'turnos.criar', 'turnos.editar',
                    'disciplinas.ver', 'disciplinas.criar', 'disciplinas.editar',
                    'escalas.ver', 'escalas.criar', 'escalas.editar', 'escalas.ver_todas',
                    'presencas.ver', 'presencas.criar', 'presencas.editar',
                    'salas.listar', 'salas.criar', 'salas.editar',
                    'planejamentos.listar', 'planejamentos.criar', 'planejamentos.editar', 'planejamentos.aprovar',
                    'historico.ver',
                    'perfil.ver', 'perfil.editar',
                    'transferencias.ver', 'transferencias.criar', 'transferencias.aprovar',
                    'conversas.ver', 'conversas.criar', 'conversas.participar',
                    'despesas.ver'
                ]
            ],
            [
                'nome' => 'Professor',
                'descricao' => 'Professor da escola',
                'permissoes' => [
                    'dashboard.ver',
                    'alunos.listar', 'alunos.ver',
                    'grupos.ver',
                    'turnos.ver',
                    'disciplinas.ver',
                    'escalas.ver', 'escalas.alterar_status', 'escalas.ver_proprias',
                    'presencas.ver', 'presencas.criar', 'presencas.editar',
                    'salas.listar',
                    'planejamentos.listar', 'planejamentos.criar', 'planejamentos.editar',
                    'historico.ver',
                    'perfil.ver', 'perfil.editar',
                    'conversas.ver', 'conversas.participar'
                ]
            ],
            [
                'nome' => 'Secretário',
                'descricao' => 'Secretário escolar',
                'permissoes' => [
                    'dashboard.ver',
                    'alunos.listar', 'alunos.ver', 'alunos.criar', 'alunos.editar',
                    'responsaveis.listar', 'responsaveis.criar', 'responsaveis.editar',
                    'funcionarios.listar', 'funcionarios.ver',
                    'grupos.ver',
                    'turnos.ver',
                    'disciplinas.ver',
                    'escalas.ver',
                    'presencas.ver',
                    'salas.listar',
                    'planejamentos.listar',
                    'historico.ver',
                    'perfil.ver', 'perfil.editar',
                    'transferencias.ver', 'transferencias.criar',
                    'conversas.ver', 'conversas.participar',
                    'despesas.ver', 'despesas.criar', 'despesas.editar', 'despesas.cancelar'
                ]
            ]
        ];

        foreach ($cargos as $cargoData) {
            $cargo = Cargo::create([
                'nome' => $cargoData['nome'],
                'descricao' => $cargoData['descricao']
            ]);

            // Associar permissões ao cargo
            $permissoesIds = Permissao::whereIn('nome', $cargoData['permissoes'])->pluck('id');
            $cargo->permissoes()->sync($permissoesIds);
        }

        $this->command->info('Permissões e cargos específicos para escolas criados com sucesso!');
    }
}