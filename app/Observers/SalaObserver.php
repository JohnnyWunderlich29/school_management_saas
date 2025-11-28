<?php

namespace App\Observers;

use App\Models\Sala;
use Illuminate\Support\Facades\DB;

class SalaObserver
{
    /**
     * Handle the Sala "created" event.
     */
    public function created(Sala $sala): void
    {
        $this->syncModalidadeTurno($sala);
    }

    /**
     * Handle the Sala "updated" event.
     */
    public function updated(Sala $sala): void
    {
        // Só sincronizar se modalidade ou turno mudaram
        if ($sala->wasChanged(['modalidade_ensino_id', 'turno_id'])) {
            $this->syncModalidadeTurno($sala);
        }
    }

    /**
     * Sincroniza a tabela pivot modalidade_ensino_turno
     */
    private function syncModalidadeTurno(Sala $sala): void
    {
        if ($sala->modalidade_ensino_id && $sala->turno_id) {
            // Verificar se o relacionamento já existe
            $existe = DB::table('modalidade_ensino_turno')
                ->where('modalidade_ensino_id', $sala->modalidade_ensino_id)
                ->where('turno_id', $sala->turno_id)
                ->exists();

            if (!$existe) {
                DB::table('modalidade_ensino_turno')->insert([
                    'modalidade_ensino_id' => $sala->modalidade_ensino_id,
                    'turno_id' => $sala->turno_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}