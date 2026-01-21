<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prova extends Model
{
    use HasFactory;

    protected $table = 'provas';

    protected $fillable = [
        'escola_id',
        'turma_id',
        'disciplina_id',
        'funcionario_id',
        'grade_aula_id',
        'titulo',
        'descricao',
        'data_aplicacao',
        'status'
    ];

    protected $casts = [
        'data_aplicacao' => 'date',
    ];

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function gradeAula(): BelongsTo
    {
        return $this->belongsTo(GradeAula::class);
    }

    public function questoes(): HasMany
    {
        return $this->hasMany(Questao::class)->orderBy('ordem');
    }

    public function scopePorEscola($query, $escolaId)
    {
        return $query->where('escola_id', $escolaId);
    }
}
