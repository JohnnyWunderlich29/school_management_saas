<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\NivelEnsino;

class TurmasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $niveisEnsino = NivelEnsino::all();
        $anoLetivo = date('Y');
        $turmas = [];

        foreach ($niveisEnsino as $nivel) {
            // Para cada nível de ensino, criar turmas A, B, C
            $letrasTurmas = ['A', 'B', 'C'];
            
            // Para níveis de ensino médio, adicionar mais turmas
            if (str_contains($nivel->codigo, 'MED')) {
                $letrasTurmas = ['A', 'B', 'C', 'D'];
            }
            
            // Para educação infantil, menos turmas
            if (in_array($nivel->codigo, ['BER', 'MAT1', 'MAT2'])) {
                $letrasTurmas = ['A', 'B'];
            }

            foreach ($letrasTurmas as $letra) {
                $turmas[] = [
                    'nivel_ensino_id' => $nivel->id,
                    'nome' => $letra,
                    'codigo' => $nivel->codigo . '-' . $letra,
                    'descricao' => $nivel->nome . ' - Turma ' . $letra,
                    'capacidade' => $nivel->capacidade_padrao,
                    'ativo' => true,
                    'turno_matutino' => $nivel->turno_matutino,
                    'turno_vespertino' => $nivel->turno_vespertino,
                    'turno_noturno' => $nivel->turno_noturno,
                    'turno_integral' => $nivel->turno_integral,
                    'ano_letivo' => $anoLetivo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach ($turmas as $turma) {
            DB::table('turmas')->updateOrInsert(
                [
                    'nivel_ensino_id' => $turma['nivel_ensino_id'],
                    'codigo' => $turma['codigo'],
                    'ano_letivo' => $turma['ano_letivo']
                ],
                $turma
            );
        }
    }
}