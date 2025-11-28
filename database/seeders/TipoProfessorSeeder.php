<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoProfessor;

class TipoProfessorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tipos = [
            ['nome' => 'Pedagogia', 'codigo' => 'pedagogia'],
            ['nome' => 'Educação Física', 'codigo' => 'educacao_fisica'],
            ['nome' => 'Artes Visuais', 'codigo' => 'artes_visuais'],
            ['nome' => 'Artes - Dança', 'codigo' => 'artes_danca'],
            ['nome' => 'Artes - Música', 'codigo' => 'artes_musica'],
            ['nome' => 'Artes - Teatro', 'codigo' => 'artes_teatro'],
            ['nome' => 'Língua Portuguesa', 'codigo' => 'lingua_portuguesa'],
            ['nome' => 'Matemática', 'codigo' => 'matematica'],
            ['nome' => 'Ciências', 'codigo' => 'ciencias'],
            ['nome' => 'História', 'codigo' => 'historia'],
            ['nome' => 'Geografia', 'codigo' => 'geografia'],
            ['nome' => 'Língua Inglesa', 'codigo' => 'lingua_inglesa'],
            ['nome' => 'Ensino Religioso', 'codigo' => 'ensino_religioso'],
            ['nome' => 'Outros', 'codigo' => 'outros'],
        ];

        foreach ($tipos as $tipo) {
            TipoProfessor::firstOrCreate(
                ['codigo' => $tipo['codigo']],
                [
                    'nome' => $tipo['nome'],
                    'descricao' => 'Tipo de professor: ' . $tipo['nome'],
                    'ativo' => true,
                ]
            );
        }
    }
}