<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampoExperiencia extends Model
{
    protected $table = 'campos_experiencia';
    
    protected $fillable = [
        'nome',
        'descricao',
        'ativo'
    ];
    
    protected $casts = [
        'ativo' => 'boolean'
    ];
    
    /**
     * Relacionamento com objetivos de aprendizagem
     */
    public function objetivosAprendizagem(): HasMany
    {
        return $this->hasMany(ObjetivoAprendizagem::class);
    }
    
    /**
     * Scope para campos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
