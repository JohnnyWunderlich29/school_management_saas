<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeHorario extends Model
{
    protected $fillable = [
        'turma_id',
        'funcionario_id',
        'disciplina_id',
        'sala_id',
        'dia_semana',
        'tempo_aula',
    ];

    /**
     * Relacionamento com a turma
     */
    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    /**
     * Relacionamento com o funcionário
     */
    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class);
    }

    /**
     * Relacionamento com a disciplina
     */
    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    /**
     * Relacionamento com a sala
     */
    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }
}
