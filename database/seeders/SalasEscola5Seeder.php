<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sala;
use App\Models\Escola;

class SalasEscola5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Determinar a escola alvo
        $escolaId = 5;
        $escola = Escola::find($escolaId);

        if (!$escola) {
            // Fallback: usar a primeira escola disponível
            $escola = Escola::first();
            if (!$escola) {
                $this->command?->warn('Nenhuma escola encontrada. Execute EscolaSeeder antes de SalasEscola5Seeder.');
                return;
            }
            $escolaId = $escola->id;
        }

        $salas = [];
        // Gerar 12 salas padrão (S101..S112)
        for ($i = 101; $i <= 112; $i++) {
            $codigo = 'S' . $i;
            $salas[] = [
                'nome' => 'Sala ' . $i,
                'codigo' => $codigo,
                'capacidade' => 30,
                'escola_id' => $escolaId,
            ];
        }

        foreach ($salas as $sala) {
            Sala::updateOrCreate(
                [
                    'escola_id' => $sala['escola_id'],
                    'codigo' => $sala['codigo']
                ],
                [
                    'nome' => $sala['nome'],
                    'capacidade' => $sala['capacidade']
                ]
            );
        }

        $this->command?->info('Salas da escola ' . $escolaId . ' criadas/atualizadas: ' . count($salas));
    }
}