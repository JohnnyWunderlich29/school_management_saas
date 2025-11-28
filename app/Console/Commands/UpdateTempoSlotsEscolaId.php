<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TempoSlot;
use App\Models\Turno;

class UpdateTempoSlotsEscolaId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tempo-slots:update-escola-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza os tempo slots existentes com escola_id baseado no turno';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando atualização dos tempo slots...');

        // Buscar todos os tempo slots sem escola_id
        $tempoSlots = TempoSlot::whereNull('escola_id')->with('turno')->get();

        if ($tempoSlots->isEmpty()) {
            $this->info('Nenhum tempo slot precisa ser atualizado.');
            return;
        }

        $updated = 0;
        $errors = 0;

        foreach ($tempoSlots as $tempoSlot) {
            if ($tempoSlot->turno && $tempoSlot->turno->escola_id) {
                $tempoSlot->escola_id = $tempoSlot->turno->escola_id;
                if ($tempoSlot->save()) {
                    $updated++;
                } else {
                    $errors++;
                    $this->error("Erro ao atualizar tempo slot ID: {$tempoSlot->id}");
                }
            } else {
                $errors++;
                $this->error("Tempo slot ID {$tempoSlot->id} não possui turno ou turno sem escola_id");
            }
        }

        $this->info("Atualização concluída!");
        $this->info("Tempo slots atualizados: {$updated}");
        if ($errors > 0) {
            $this->warn("Erros encontrados: {$errors}");
        }
    }
}
