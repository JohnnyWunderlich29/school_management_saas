<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questao extends Model
{
    use HasFactory;

    protected $table = 'questoes';

    protected $fillable = [
        'prova_id',
        'tipo',
        'enunciado',
        'imagem_path',
        'ordem',
        'valor'
    ];

    public function prova(): BelongsTo
    {
        return $this->belongsTo(Prova::class);
    }

    public function alternativas(): HasMany
    {
        return $this->hasMany(QuestaoAlternativa::class)->orderBy('ordem');
    }
}
