<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            [
                'name' => 'comunicacao_module',
                'display_name' => 'Comunicação Escolar',
                'description' => 'Sistema completo de comunicação entre escola, alunos e responsáveis. Inclui comunicados, notificações e mensagens.',
                'icon' => 'fas fa-comments',
                'color' => '#10B981',
                'price' => 49.90,
                'is_active' => true,
                'is_core' => false,
                'features' => json_encode([
                    'Comunicados gerais',
                    'Notificações push',
                    'Mensagens diretas',
                    'Histórico de comunicação'
                ]),
                'category' => 'communication',
                'sort_order' => 1
            ],
            [
                'name' => 'alunos_module',
                'display_name' => 'Gestão de Alunos',
                'description' => 'Módulo completo para gestão de alunos, matrículas, responsáveis e transferências.',
                'icon' => 'fas fa-user-graduate',
                'color' => '#3B82F6',
                'price' => 79.90,
                'is_active' => true,
                'is_core' => true,
                'features' => json_encode([
                    'Cadastro de alunos',
                    'Gestão de responsáveis',
                    'Controle de matrículas',
                    'Sistema de transferências',
                    'Documentos dos alunos'
                ]),
                'category' => 'academic',
                'sort_order' => 2
            ],
            [
                'name' => 'funcionarios_module',
                'display_name' => 'Gestão de Funcionários',
                'description' => 'Sistema para gestão de funcionários, escalas de trabalho e controle de presenças.',
                'icon' => 'fas fa-users',
                'color' => '#8B5CF6',
                'price' => 69.90,
                'is_active' => true,
                'is_core' => false,
                'features' => json_encode([
                    'Cadastro de funcionários',
                    'Escalas de trabalho',
                    'Controle de presenças',
                    'Gestão de cargos',
                    'Relatórios de RH'
                ]),
                'category' => 'administrative',
                'sort_order' => 3
            ],
            [
                'name' => 'academico_module',
                'display_name' => 'Gestão Acadêmica',
                'description' => 'Módulo para gestão acadêmica completa incluindo salas, planejamentos e atividades pedagógicas.',
                'icon' => 'fas fa-graduation-cap',
                'color' => '#F59E0B',
                'price' => 89.90,
                'is_active' => true,
                'is_core' => false,
                'features' => json_encode([
                    'Gestão de salas',
                    'Planejamentos pedagógicos',
                    'Controle de turmas',
                    'Disciplinas e modalidades',
                    'Relatórios acadêmicos'
                ]),
                'category' => 'academic',
                'sort_order' => 4
            ],
            [
                'name' => 'administracao_module',
                'display_name' => 'Administração',
                'description' => 'Módulo administrativo para gestão de usuários, permissões e configurações do sistema.',
                'icon' => 'fas fa-cogs',
                'color' => '#EF4444',
                'price' => 59.90,
                'is_active' => true,
                'is_core' => true,
                'features' => json_encode([
                    'Gestão de usuários',
                    'Controle de permissões',
                    'Configurações do sistema',
                    'Auditoria e logs',
                    'Backup e segurança'
                ]),
                'category' => 'administrative',
                'sort_order' => 5
            ],
            [
                'name' => 'financeiro_module',
                'display_name' => 'Gestão Financeira',
                'description' => 'Sistema completo para gestão financeira da escola, incluindo mensalidades, pagamentos e relatórios.',
                'icon' => 'fas fa-dollar-sign',
                'color' => '#059669',
                'price' => 99.90,
                'is_active' => true,
                'is_core' => false,
                'features' => json_encode([
                    'Controle de mensalidades',
                    'Gestão de pagamentos',
                    'Relatórios financeiros',
                    'Controle de inadimplência',
                    'Integração bancária'
                ]),
                'category' => 'financial',
                'sort_order' => 6
            ],
            [
                'name' => 'biblioteca_module',
                'display_name' => 'Biblioteca Digital',
                'description' => 'Sistema de gestão de biblioteca com controle de empréstimos, acervo e reservas.',
                'icon' => 'fas fa-book',
                'color' => '#7C3AED',
                'price' => 39.90,
                'is_active' => true,
                'is_core' => false,
                'features' => json_encode([
                    'Catálogo de livros',
                    'Controle de empréstimos',
                    'Sistema de reservas',
                    'Multas e devoluções',
                    'Relatórios de uso'
                ]),
                'category' => 'academic',
                'sort_order' => 7
            ],
            [
                'name' => 'eventos_module',
                'display_name' => 'Gestão de Eventos',
                'description' => 'Módulo para organização e gestão de eventos escolares, reuniões e atividades.',
                'icon' => 'fas fa-calendar-alt',
                'color' => '#EC4899',
                'price' => 29.90,
                'is_active' => true,
                'is_core' => false,
                'features' => json_encode([
                    'Calendário de eventos',
                    'Inscrições online',
                    'Controle de participantes',
                    'Notificações automáticas',
                    'Relatórios de eventos'
                ]),
                'category' => 'communication',
                'sort_order' => 8
            ]
        ];

        // Upsert para evitar violação de unicidade no campo 'name'
        $now = Carbon::now();
        $modules = array_map(function ($m) use ($now) {
            $m['created_at'] = $now;
            $m['updated_at'] = $now;
            return $m;
        }, $modules);

        DB::table('modules')->upsert(
            $modules,
            ['name'],
            ['display_name','description','icon','color','price','is_active','is_core','features','category','sort_order','updated_at']
        );

        $this->command->info('Módulos criados/atualizados com sucesso!');
    }
}