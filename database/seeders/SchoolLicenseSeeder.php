<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SchoolLicense;
use App\Models\Escola;
use Carbon\Carbon;

class SchoolLicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca todas as escolas existentes
        $escolas = Escola::all();
        
        foreach ($escolas as $escola) {
            // Cria licenças para todos os módulos com 1 ano de validade
            $modules = [
                'comunicacao_module',
                'alunos_module', 
                'funcionarios_module',
                'academico_module',
                'administracao_module',
                'relatorios_module',
                'financeiro_module'
            ];
            
            foreach ($modules as $module) {
                SchoolLicense::updateOrCreate(
                    [
                        'escola_id' => $escola->id,
                        'module_name' => $module,
                    ],
                    [
                        'expires_at' => now()->addYear(),
                        'is_active' => true,
                        'max_users' => null,
                    ]
                );
            }
        }
        
        $this->command->info('Licenças criadas para ' . $escolas->count() . ' escola(s)');
    }
}
