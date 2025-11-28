<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelatoriosModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Garante a existência do módulo de Relatórios de forma idempotente
        $now = Carbon::now();

        $module = [
            'name' => 'relatorios_module',
            'display_name' => 'Relatórios',
            'description' => 'Geração e exportação de relatórios acadêmicos, administrativos e financeiros.',
            'icon' => 'fas fa-chart-line',
            'color' => '#0EA5E9',
            'price' => 0.0,
            'is_active' => true,
            'is_core' => false,
            'features' => json_encode([
                'Relatórios acadêmicos',
                'Relatórios administrativos',
                'Exportação de relatórios',
            ]),
            'category' => 'administrative',
            'sort_order' => 9,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        DB::table('modules')->upsert(
            [$module],
            ['name'],
            ['display_name','description','icon','color','price','is_active','is_core','features','category','sort_order','updated_at']
        );

        $this->command->info('Módulo de Relatórios garantido com sucesso (criado/atualizado).');
    }
}