<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Turma;
use App\Models\Turno;
use App\Models\NivelEnsino;
use App\Models\Escola;

class TurmasEscola5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir escola alvo
        $escolaId = 5;
        $escola = Escola::find($escolaId);
        if (!$escola) {
            $escola = Escola::first();
            if (!$escola) {
                $this->command?->warn('Nenhuma escola encontrada. Execute EscolaSeeder antes de TurmasEscola5Seeder.');
                return;
            }
            $escolaId = $escola->id;
        }

        // Garantir turnos básicos para a escola
        $turnosDef = [
            ['codigo' => 'MAT', 'nome' => 'Matutino', 'hora_inicio' => '07:00', 'hora_fim' => '11:00', 'ordem' => 1],
            ['codigo' => 'VES', 'nome' => 'Vespertino', 'hora_inicio' => '13:00', 'hora_fim' => '17:00', 'ordem' => 2],
            ['codigo' => 'NOT', 'nome' => 'Noturno', 'hora_inicio' => '19:00', 'hora_fim' => '22:00', 'ordem' => 3],
            ['codigo' => 'INT', 'nome' => 'Integral', 'hora_inicio' => '08:00', 'hora_fim' => '16:00', 'ordem' => 4],
        ];

        $turnos = [];
        foreach ($turnosDef as $def) {
            $turnos[$def['codigo']] = Turno::updateOrCreate(
                ['escola_id' => $escolaId, 'codigo' => $def['codigo']],
                [
                    'nome' => $def['nome'],
                    'hora_inicio' => $def['hora_inicio'],
                    'hora_fim' => $def['hora_fim'],
                    'ativo' => true,
                    'ordem' => $def['ordem']
                ]
            );
        }

        // Selecionar níveis por modalidade
        $niveisEI = NivelEnsino::whereJsonContains('modalidades_compativeis', 'EI')->get();
        $niveisEF = NivelEnsino::whereJsonContains('modalidades_compativeis', 'EF')->get();
        $niveisEM = NivelEnsino::whereJsonContains('modalidades_compativeis', 'EM')->get();

        $anoLetivo = (int) date('Y');

        $createdCount = 0;

        // Helper para criar duas turmas por nível (A e B)
        $criarTurmasPorNivel = function ($nivel, $turnoCodigoA = 'MAT', $turnoCodigoB = 'VES') use ($escolaId, $turnos, $anoLetivo, &$createdCount) {
            $capacidade = $nivel->capacidade_padrao ?: 30;
            $turmas = [
                ['codigo' => 'A', 'nome' => $nivel->nome . ' - Turma A', 'turno_codigo' => $turnoCodigoA],
                ['codigo' => 'B', 'nome' => $nivel->nome . ' - Turma B', 'turno_codigo' => $turnoCodigoB],
            ];

            foreach ($turmas as $t) {
                Turma::updateOrCreate(
                    [
                        'nivel_ensino_id' => $nivel->id,
                        'codigo' => $t['codigo'],
                        'ano_letivo' => $anoLetivo,
                        'escola_id' => $escolaId,
                    ],
                    [
                        'nome' => $t['nome'],
                        'descricao' => null,
                        'capacidade' => $capacidade,
                        'ativo' => true,
                        'turno_matutino' => $nivel->turno_matutino,
                        'turno_vespertino' => $nivel->turno_vespertino,
                        'turno_noturno' => $nivel->turno_noturno,
                        'turno_integral' => $nivel->turno_integral,
                        'turno_id' => $turnos[$t['turno_codigo']]->id ?? null,
                        'grupo_id' => null,
                    ]
                );
                $createdCount++;
            }
        };

        // Criar turmas EI (ex.: Maternal I/II, Pré)
        foreach ($niveisEI->whereIn('codigo', ['MAT1', 'MAT2', 'PRE']) as $nivel) {
            $criarTurmasPorNivel($nivel, 'MAT', 'VES');
        }

        // Criar turmas EF (1EF..5EF e 6EF..9EF, alguns exemplos)
        foreach ($niveisEF->whereIn('codigo', ['1EF', '2EF', '3EF', '6EF', '9EF']) as $nivel) {
            $criarTurmasPorNivel($nivel, 'MAT', 'VES');
        }

        // Criar turmas EM (1EM..3EM)
        foreach ($niveisEM->whereIn('codigo', ['1EM', '2EM', '3EM']) as $nivel) {
            $criarTurmasPorNivel($nivel, 'MAT', 'NOT');
        }

        $this->command?->info("Turmas criadas/atualizadas para a escola {$escolaId}: {$createdCount}");
    }
}