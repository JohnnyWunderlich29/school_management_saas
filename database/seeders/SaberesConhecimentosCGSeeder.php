<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaberesConhecimentosCGSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campoNome = 'Corpo, gestos e movimentos';
        $campoId = DB::table('campos_experiencia')->where('nome', $campoNome)->value('id');
        if (!$campoId) {
            $this->command?->warn("Campo de experiência '{$campoNome}' não encontrado. Pulando seeder de saberes.");
            return;
        }

        $agora = now();
        $saberes = [
            [
                'campo_experiencia_id' => $campoId,
                'titulo' => 'Domínio progressivo da coordenação motora ampla (correr, pular, subir, descer)',
                'descricao' => null,
                'ordem' => 1,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $campoId,
                'titulo' => 'Coordenação motora fina (pegar objetos pequenos, empilhar, encaixar)',
                'descricao' => null,
                'ordem' => 2,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $campoId,
                'titulo' => 'Brincadeiras de movimento coletivo',
                'descricao' => null,
                'ordem' => 3,
                'ativo' => true,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
            [
                'campo_experiencia_id' => $campoId,
                'titulo' => 'Descoberta de limites corporais',
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