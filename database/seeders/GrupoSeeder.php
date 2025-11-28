<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Grupo;
use App\Models\Escola;

class GrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $escola = Escola::first();
        
        if (!$escola) {
            $this->command->error('Nenhuma escola encontrada. Execute o EscolaSeeder primeiro.');
            return;
        }

        $grupos = [
            [
                'nome' => 'Berçário I',
                'codigo' => 'BER1',
                'idade_minima' => 0,
                'idade_maxima' => 1,
                'ano_serie' => null,
                'descricao' => 'Berçário para bebês de 0 a 1 ano',
                'ativo' => true,
                'ordem' => 1,
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'Berçário II',
                'codigo' => 'BER2',
                'idade_minima' => 1,
                'idade_maxima' => 2,
                'ano_serie' => null,
                'descricao' => 'Berçário para bebês de 1 a 2 anos',
                'ativo' => true,
                'ordem' => 2,
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'Maternal I',
                'codigo' => 'MAT1',
                'idade_minima' => 2,
                'idade_maxima' => 3,
                'ano_serie' => null,
                'descricao' => 'Maternal para crianças de 2 a 3 anos',
                'ativo' => true,
                'ordem' => 3,
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'Maternal II',
                'codigo' => 'MAT2',
                'idade_minima' => 3,
                'idade_maxima' => 4,
                'ano_serie' => null,
                'descricao' => 'Maternal para crianças de 3 a 4 anos',
                'ativo' => true,
                'ordem' => 4,
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'Pré-escola',
                'codigo' => 'PRE',
                'idade_minima' => 4,
                'idade_maxima' => 5,
                'ano_serie' => null,
                'descricao' => 'Pré-escola para crianças de 4 a 5 anos',
                'ativo' => true,
                'ordem' => 5,
                'escola_id' => $escola->id
            ],
            [
                'nome' => '1º Ano',
                'codigo' => 'EF1_1',
                'idade_minima' => 6,
                'idade_maxima' => 7,
                'ano_serie' => 1,
                'descricao' => '1º ano do Ensino Fundamental',
                'ativo' => true,
                'ordem' => 6,
                'escola_id' => $escola->id
            ],
            [
                'nome' => '2º Ano',
                'codigo' => 'EF1_2',
                'idade_minima' => 7,
                'idade_maxima' => 8,
                'ano_serie' => 2,
                'descricao' => '2º ano do Ensino Fundamental',
                'ativo' => true,
                'ordem' => 7,
                'escola_id' => $escola->id
            ],
            [
                'nome' => '3º Ano',
                'codigo' => 'EF1_3',
                'idade_minima' => 8,
                'idade_maxima' => 9,
                'ano_serie' => 3,
                'descricao' => '3º ano do Ensino Fundamental',
                'ativo' => true,
                'ordem' => 8,
                'escola_id' => $escola->id
            ]
        ];

        foreach ($grupos as $grupo) {
            Grupo::firstOrCreate(
                ['codigo' => $grupo['codigo']],
                $grupo
            );
        }
    }
}
