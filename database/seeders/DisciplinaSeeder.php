<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Disciplina;
use App\Models\Escola;

class DisciplinaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $escola = Escola::first();
        
        if (!$escola) {
            return;
        }

        $disciplinas = [
            [
                'nome' => 'Língua Portuguesa',
                'codigo' => 'LP',
                'descricao' => 'Desenvolvimento da leitura, escrita e comunicação',
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'Matemática',
                'codigo' => 'MAT',
                'descricao' => 'Fundamentos matemáticos e raciocínio lógico',
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'Ciências',
                'codigo' => 'CIE',
                'descricao' => 'Exploração científica do mundo natural',
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'História',
                'codigo' => 'HIS',
                'descricao' => 'Compreensão histórica e temporal',
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'Geografia',
                'codigo' => 'GEO',
                'descricao' => 'Estudo do espaço geográfico',
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'Educação Física',
                'codigo' => 'EDF',
                'descricao' => 'Desenvolvimento físico e motor',
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'Arte',
                'codigo' => 'ART',
                'descricao' => 'Expressão artística e criatividade',
                'escola_id' => $escola->id
            ],
            [
                'nome' => 'Inglês',
                'codigo' => 'ING',
                'descricao' => 'Língua inglesa',
                'escola_id' => $escola->id
            ]
        ];

        foreach ($disciplinas as $disciplina) {
            Disciplina::firstOrCreate(
                ['codigo' => $disciplina['codigo']],
                $disciplina
            );
        }
    }
}
