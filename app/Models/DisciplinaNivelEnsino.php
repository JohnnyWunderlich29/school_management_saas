<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaNivelEnsino extends Model
{
    protected $table = 'disciplina_nivel_ensino';

    protected $fillable = [
        'disciplina_id',
        'nivel_ensino_id',
        'carga_horaria_semanal',
        'carga_horaria_anual',
        'obrigatoria',
        'ordem'
    ];

    protected $casts = [
        'carga_horaria_semanal' => 'integer',
        'carga_horaria_anual' => 'integer',
        'obrigatoria' => 'boolean',
        'ordem' => 'integer'
    ];

    /**
     * Relacionamento com Disciplina
     */
    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    /**
     * Relacionamento com NivelEnsino
     */
    public function nivelEnsino(): BelongsTo
    {
        return $this->belongsTo(NivelEnsino::class);
    }
}