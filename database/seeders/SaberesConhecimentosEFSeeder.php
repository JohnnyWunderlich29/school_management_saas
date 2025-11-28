<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaberesConhecimentosEFSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campoNome = 'Escuta, fala, pensamento e imaginação';
        $campoId = DB::table('campos_experiencia')->where('nome', $campoNome)->value('id');
        if (!$campoId) {
            $this->command?->warn("Campo de experiência '{$campoNome}' não encontrado. Pulando seeder de saberes.");
            return;
        }

        $agora = now();
        $saberes = [
            [
                'campo_experiencia_id' => $campoId,
                'titulo' => 'Desenvolvimento da linguagem oral (palavras e frases curtas).',
                'descricao' => null,
                'ordem' => 1,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $campoId,
                'titulo' => 'Participação em conversas simples.',
                'descricao' => null,
                'ordem' => 2,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $campoId,
                'titulo' => 'Escuta de histórias, cantigas e rimas.',
                'descricao' => null,
                'ordem' => 3,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $campoId,
                'titulo' => 'Brincadeiras de faz-de-conta mais elaboradas.',
                'descricao' => null,
                'ordem' => 4,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
        ];

        foreach ($saberes as $s) {
            DB::table('saberes_conhecimentos')->updateOrInsert(
                [
                    'campo_experiencia_id' => $s['campo_experiencia_id'],
                    'titulo' => $s['titulo'],
                ],
                [
                    'descricao' => $s['descricao'],
                    'ordem' => $s['ordem'],
                    'ativo' => $s['ativo'],
                    'created_at' => $s['created_at'],
                    'updated_at' => $s['updated_at'],
                ]
            );
        }
    }
}