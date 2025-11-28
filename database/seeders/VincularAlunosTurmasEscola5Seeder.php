<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Aluno;
use App\Models\Turma;

class VincularAlunosTurmasEscola5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $escolaId = 5;

        $turmas = Turma::where('escola_id', $escolaId)
            ->with('nivelEnsino')
            ->get();

        if ($turmas->isEmpty()) {
            $this->command?->warn('Nenhuma turma encontrada para escola_id=5. Nada a vincular.');
            return;
        }

        $turmasPorModalidade = [
            'EI' => $turmas->filter(function ($t) {
                return $t->nivelEnsino && is_array($t->nivelEnsino->modalidades_compativeis)
                    && in_array('EI', $t->nivelEnsino->modalidades_compativeis);
            })->values(),
            'EF' => $turmas->filter(function ($t) {
                return $t->nivelEnsino && is_array($t->nivelEnsino->modalidades_compativeis)
                    && in_array('EF', $t->nivelEnsino->modalidades_compativeis);
            })->values(),
            'EM' => $turmas->filter(function ($t) {
                return $t->nivelEnsino && is_array($t->nivelEnsino->modalidades_compativeis)
                    && in_array('EM', $t->nivelEnsino->modalidades_compativeis);
            })->values(),
        ];

        // Fallback: se algum grupo estiver vazio, usa todas as turmas disponíveis
        foreach (['EI','EF','EM'] as $key) {
            if ($turmasPorModalidade[$key]->isEmpty()) {
                $turmasPorModalidade[$key] = $turmas->values();
            }
        }

        // Seleciona alunos da escola 5, priorizando os sem turma
        $alunos = Aluno::where('escola_id', $escolaId)
            ->inRandomOrder()
            ->get();

        if ($alunos->isEmpty()) {
            $this->command?->warn('Nenhum aluno encontrado para escola_id=5.');
            return;
        }

        // Vincular parte dos alunos (ex.: 400) e ajustar idades conforme EI/EF/EM
        $quantidadeParaVincular = min(400, $alunos->count());
        $alunosParaVincular = $alunos->filter(function ($a) {
            return $a->turma_id === null;
        })->take($quantidadeParaVincular)->values();

        if ($alunosParaVincular->isEmpty()) {
            $this->command?->info('Todos os alunos já possuem turma. Ajustando idades conforme turmas atuais.');
            $alunosParaVincular = $alunos->take($quantidadeParaVincular);
        }

        DB::transaction(function () use ($alunosParaVincular, $turmasPorModalidade) {
            foreach ($alunosParaVincular as $aluno) {
                // Escolhe modalidade-alvo de forma balanceada: 30% EI, 50% EF, 20% EM
                $rand = mt_rand(1, 100);
                $modalidade = $rand <= 30 ? 'EI' : ($rand <= 80 ? 'EF' : 'EM');
                $listaTurmas = $turmasPorModalidade[$modalidade];
                if ($listaTurmas->isEmpty()) {
                    $listaTurmas = $turmas; // fallback: usa qualquer turma disponível
                }

                // Escolhe turma aleatória do grupo
                $turma = $listaTurmas[mt_rand(0, max(0, $listaTurmas->count() - 1))];

                // Ajusta a idade conforme a modalidade (data_nascimento)
                // EI: 3-5 anos, EF: 6-14 anos, EM: 15-18 anos
                $anos = 10; // default fallback
                if ($modalidade === 'EI') {
                    $anos = mt_rand(3, 5);
                } elseif ($modalidade === 'EF') {
                    $anos = mt_rand(6, 14);
                } elseif ($modalidade === 'EM') {
                    $anos = mt_rand(15, 18);
                }

                $dataNascimento = now()->subYears($anos)->subDays(mt_rand(0, 365));

                $aluno->turma_id = $turma->id;
                $aluno->data_nascimento = $dataNascimento;
                $aluno->ativo = true;
                $aluno->save();
            }
        });

        $this->command?->info('Alunos vinculados às turmas e idades ajustadas por EI/EF/EM.');
    }
}