<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permissao;
use App\Models\Cargo;

class CargosAdministrativosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Este seeder cria cargos administrativos para o sistema SaaS (não para escolas)
     */
    public function run(): void
    {
        // Permissões administrativas específicas do sistema SaaS
        $permissoesAdministrativas = [
            // Relatórios do sistema
            ['nome' => 'relatorios.gerar', 'modulo' => 'relatorios', 'descricao' => 'Gerar relatórios do sistema'],
            ['nome' => 'relatorios.exportar', 'modulo' => 'relatorios', 'descricao' => 'Exportar relatórios'],
            ['nome' => 'relatorios.ver', 'modulo' => 'relatorios', 'descricao' => 'Visualizar relatórios'],

            // Configurações do sistema
            ['nome' => 'configuracoes.ver', 'modulo' => 'configuracoes', 'descricao' => 'Visualizar configurações do sistema'],
            ['nome' => 'configuracoes.editar', 'modulo' => 'configuracoes', 'descricao' => 'Editar configurações do sistema'],

            // Gestão de escolas (para administradores do SaaS)
            ['nome' => 'escolas.listar', 'modulo' => 'escolas', 'descricao' => 'Listar escolas do sistema'],
            ['nome' => 'escolas.criar', 'modulo' => 'escolas', 'descricao' => 'Criar novas escolas'],
            ['nome' => 'escolas.editar', 'modulo' => 'escolas', 'descricao' => 'Editar escolas'],
            ['nome' => 'escolas.excluir', 'modulo' => 'escolas', 'descricao' => 'Excluir escolas'],
            ['nome' => 'escolas.suspender', 'modulo' => 'escolas', 'descricao' => 'Suspender escolas'],

            // Modalidades (gestão centralizada)
            ['nome' => 'modalidades.ver', 'modulo' => 'modalidades', 'descricao' => 'Visualizar modalidades'],
            ['nome' => 'modalidades.criar', 'modulo' => 'modalidades', 'descricao' => 'Criar modalidades'],
            ['nome' => 'modalidades.editar', 'modulo' => 'modalidades', 'descricao' => 'Editar modalidades'],
            ['nome' => 'modalidades.excluir', 'modulo' => 'modalidades', 'descricao' => 'Excluir modalidades'],

            // Comunicados do sistema (para todas as escolas)
            ['nome' => 'comunicados.sistema.ver', 'modulo' => 'comunicacao', 'descricao' => 'Visualizar comunicados do sistema'],
            ['nome' => 'comunicados.sistema.criar', 'modulo' => 'comunicacao', 'descricao' => 'Criar comunicados do sistema'],
            ['nome' => 'comunicados.sistema.editar', 'modulo' => 'comunicacao', 'descricao' => 'Editar comunicados do sistema'],
            ['nome' => 'comunicados.sistema.excluir', 'modulo' => 'comunicacao', 'descricao' => 'Excluir comunicados do sistema'],
            ['nome' => 'comunicados.sistema.publicar', 'modulo' => 'comunicacao', 'descricao' => 'Publicar comunicados do sistema'],

            // Gestão de cargos (para administradores do sistema)
            ['nome' => 'cargos.gerenciar', 'modulo' => 'cargos', 'descricao' => 'Gerenciar cargos do sistema'],
            ['nome' => 'cargos.criar', 'modulo' => 'cargos', 'descricao' => 'Criar cargos'],
            ['nome' => 'cargos.editar', 'modulo' => 'cargos', 'descricao' => 'Editar cargos'],
            ['nome' => 'cargos.excluir', 'modulo' => 'cargos', 'descricao' => 'Excluir cargos'],

            // Auditoria e logs
            ['nome' => 'auditoria.ver', 'modulo' => 'auditoria', 'descricao' => 'Visualizar logs de auditoria'],
            ['nome' => 'logs.sistema.ver', 'modulo' => 'logs', 'descricao' => 'Visualizar logs do sistema']
        ];

        foreach ($permissoesAdministrativas as $permissao) {
            Permissao::firstOrCreate(
                ['nome' => $permissao['nome']],
                $permissao
            );
        }

        // Cargos administrativos do sistema SaaS
        $cargosAdministrativos = [
            [
                'nome' => 'Super Administrador',
                'descricao' => 'Acesso total ao sistema SaaS',
                'permissoes' => array_merge(
                    // Todas as permissões de escola
                    Permissao::whereNotIn('modulo', ['relatorios', 'configuracoes', 'escolas', 'modalidades', 'auditoria', 'logs'])
                        ->whereNotLike('nome', 'comunicados.sistema.%')
                        ->whereNotIn('nome', ['cargos.gerenciar', 'cargos.criar', 'cargos.editar', 'cargos.excluir'])
                        ->pluck('nome')->toArray(),
                    // Todas as permissões administrativas
                    array_column($permissoesAdministrativas, 'nome')
                )
            ],
            [
                'nome' => 'Administrador do Sistema',
                'descricao' => 'Administrador do sistema SaaS com acesso a gestão de escolas',
                'permissoes' => [
                    'dashboard.ver',
                    'escolas.listar', 'escolas.criar', 'escolas.editar', 'escolas.suspender',
                    'modalidades.ver', 'modalidades.criar', 'modalidades.editar', 'modalidades.excluir',
                    'relatorios.gerar', 'relatorios.exportar', 'relatorios.ver',
                    'configuracoes.ver', 'configuracoes.editar',
                    'comunicados.sistema.ver', 'comunicados.sistema.criar', 'comunicados.sistema.editar', 'comunicados.sistema.publicar',
                    'cargos.gerenciar', 'cargos.criar', 'cargos.editar',
                    'auditoria.ver', 'logs.sistema.ver',
                    'historico.ver',
                    'perfil.ver', 'perfil.editar'
                ]
            ],
            [
                'nome' => 'Suporte Técnico',
                'descricao' => 'Suporte técnico com acesso limitado para resolução de problemas',
                'permissoes' => [
                    'dashboard.ver',
                    'escolas.listar',
                    'relatorios.ver',
                    'configuracoes.ver',
                    'auditoria.ver', 'logs.sistema.ver',
                    'historico.ver',
                    'perfil.ver', 'perfil.editar',
                    'presencas.ver', 'presencas.criar', 'presencas.editar', 'presencas.excluir'
                ]
            ],
            [
                'nome' => 'Analista de Dados',
                'descricao' => 'Analista com acesso a relatórios e dados do sistema',
                'permissoes' => [
                    'dashboard.ver',
                    'escolas.listar',
                    'relatorios.gerar', 'relatorios.exportar', 'relatorios.ver',
                    'auditoria.ver',
                    'historico.ver',
                    'perfil.ver', 'perfil.editar'
                ]
            ]
        ];

        foreach ($cargosAdministrativos as $cargoData) {
            $cargo = Cargo::firstOrCreate(
                ['nome' => $cargoData['nome']],
                [
                    'nome' => $cargoData['nome'],
                    'descricao' => $cargoData['descricao']
                ]
            );

            // Associar permissões ao cargo
            if ($cargoData['nome'] === 'Super Administrador') {
                // Para Super Administrador, dar TODAS as permissões do sistema
                $permissoesIds = Permissao::pluck('id');
            } else {
                $permissoesIds = Permissao::whereIn('nome', $cargoData['permissoes'])->pluck('id');
            }
            
            $cargo->permissoes()->sync($permissoesIds);
        }

        $this->command->info('Cargos administrativos do sistema SaaS criados com sucesso!');
    }
}