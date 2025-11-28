<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Historico;
use App\Models\User;

class UpdateHistoricosEscolaId extends Command
{
    protected $signature = 'historicos:update-escola-id';

    protected $description = 'Atualiza os históricos existentes com escola_id baseado no usuário vinculado';

    public function handle()
    {
        $this->info('Iniciando atualização de escola_id nos históricos...');

        $totalNull = Historico::whereNull('escola_id')->count();
        if ($totalNull === 0) {
            $this->info('Nenhum histórico precisa ser atualizado.');
            return self::SUCCESS;
        }

        $this->info("Registros com escola_id NULL: {$totalNull}");

        $updated = 0;
        $skipped = 0;

        Historico::whereNull('escola_id')
            ->with('usuario')
            ->chunkById(1000, function ($chunk) use (&$updated, &$skipped) {
                foreach ($chunk as $historico) {
                    $usuario = $historico->usuario;
                    if ($usuario && $usuario->escola_id) {
                        $historico->escola_id = $usuario->escola_id;
                        if ($historico->save()) {
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        $modelClass = $this->resolveModelClass($historico->modelo);
                        if ($modelClass) {
                            $registro = $modelClass::find($historico->modelo_id);
                            if ($registro && isset($registro->escola_id) && $registro->escola_id) {
                                $historico->escola_id = $registro->escola_id;
                                if ($historico->save()) {
                                    $updated++;
                                } else {
                                    $skipped++;
                                }
                            } else {
                                $skipped++;
                            }
                        } else {
                            $skipped++;
                        }
                    }
                }
            });

        $this->info("Atualização concluída. Atualizados: {$updated} | Ignorados: {$skipped}");
        $restantes = Historico::whereNull('escola_id')->count();
        $this->info("Registros restantes com escola_id NULL: {$restantes}");

        return self::SUCCESS;
    }

    protected function resolveModelClass(string $modelo): ?string
    {
        $map = [
            'Sala' => \App\Models\Sala::class,
            'Funcionario' => \App\Models\Funcionario::class,
            'Turma' => \App\Models\Turma::class,
            'Disciplina' => \App\Models\Disciplina::class,
            'Escala' => \App\Models\Escala::class,
            'GradeAula' => \App\Models\GradeAula::class,
            'Turno' => \App\Models\Turno::class,
            'TempoSlot' => \App\Models\TempoSlot::class,
            'ModalidadeEnsino' => \App\Models\ModalidadeEnsino::class,
            'Grupo' => \App\Models\Grupo::class,
            'Escola' => \App\Models\Escola::class,
        ];
        return $map[$modelo] ?? null;
    }
}
