<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlunoAnotacao extends Model
{
    use HasFactory;

    protected $table = 'aluno_anotacoes';

    protected $fillable = [
        'aluno_id',
        'escola_id',
        'usuario_id',
        'tipo',
        'titulo',
        'descricao',
        'data_ocorrencia'
    ];

    protected $casts = [
        'data_ocorrencia' => 'date'
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Scope para filtrar por escola
     */
    public function scopePorEscola($query, $escolaId)
    {
        return $query->where('escola_id', $escolaId);
    }
}
