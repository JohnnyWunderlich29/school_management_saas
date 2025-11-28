<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaberConhecimento extends Model
{
    protected $table = 'saberes_conhecimentos';

    protected $fillable = [
        'campo_experiencia_id',
        'titulo',
        'descricao',
        'ordem',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
    ];

    public function campoExperiencia(): BelongsTo
    {
        return $this->belongsTo(CampoExperiencia::class);
    }

    public function objetivosAprendizagem(): HasMany
    {
        return $this->hasMany(ObjetivoAprendizagem::class, 'saber_conhecimento_id');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}