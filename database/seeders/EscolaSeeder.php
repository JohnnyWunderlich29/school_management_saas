<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Escola;
use Carbon\Carbon;

class EscolaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $escolas = [
            [
                'nome' => 'Escola Municipal João da Silva',
                'codigo' => 'EMJS001'
            ],
            [
                'nome' => 'Colégio Estadual Maria Santos',
                'codigo' => 'CEMS002'
            ],
            [
                'nome' => 'Instituto Técnico São José',
                'codigo' => 'ITSJ003'
            ],
            [
                'nome' => 'Escola Particular Elite',
                'codigo' => 'EPE004'
            ],
            [
                'nome' => 'Centro Educacional Futuro',
                'codigo' => 'CEF005'
            ]
        ];

        foreach ($escolas as $escola) {
            Escola::firstOrCreate(
                ['codigo' => $escola['codigo']],
                $escola
            );
        }

        $this->command->info('Escolas criadas com sucesso!');
    }
}