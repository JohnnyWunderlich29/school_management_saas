<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TipoProfessor extends Model
{
    protected $table = 'tipos_professor';
    
    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'ativo'
    ];
    
    protected $casts = [
        'ativo' => 'boolean'
    ];
    
    /**
     * Escopo para filtrar apenas tipos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
    
    /**
     * Relacionamento com salas
     */
    public function salas(): BelongsToMany
    {
        return $this->belongsToMany(Sala::class, 'sala_tipo_professor', 'tipo_professor_id', 'sala_id')
                    ->withTimestamps();
    }
}
