<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestaoAlternativa extends Model
{
    use HasFactory;

    protected $table = 'questao_alternativas';

    protected $fillable = [
        'questao_id',
        'texto',
        'correta',
        'ordem'
    ];

    protected $casts = [
        'correta' => 'boolean',
    ];

    public function questao(): BelongsTo
    {
        return $this->belongsTo(Questao::class);
    }
}
