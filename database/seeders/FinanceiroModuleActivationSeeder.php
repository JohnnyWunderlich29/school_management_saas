<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinanceiroModuleActivationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ativar (ou manter ativo) o módulo Financeiro de forma idempotente
        DB::table('modules')
            ->where('name', 'financeiro_module')
            ->update(['is_active' => true, 'updated_at' => now()]);

        $this->command->info('Módulo Financeiro garantido como ativo.');
    }
}