<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nota extends Model
{
    use HasFactory;

    protected $fillable = [
        'aluno_id',
        'disciplina_id',
        'professor_id',
        'escola_id',
        'valor',
        'referencia',
        'data_lancamento',
        'observacoes'
    ];

    protected $casts = [
        'data_lancamento' => 'date',
        'valor' => 'decimal:2'
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'professor_id');
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
