<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObjetivoAprendizagem extends Model
{
    protected $table = 'objetivos_aprendizagem';
    
    protected $fillable = [
        'campo_experiencia_id',
        'saber_conhecimento_id',
        'codigo',
        'descricao',
        'faixa_etaria',
        'ativo'
    ];
    
    protected $casts = [
        'ativo' => 'boolean'
    ];
    
    /**
     * Relacionamento com campo de experiência
     */
    public function campoExperiencia(): BelongsTo
    {
        return $this->belongsTo(CampoExperiencia::class);
    }

    /**
     * Relacionamento com saber/conhecimento
     */
    public function saberConhecimento(): BelongsTo
    {
        return $this->belongsTo(SaberConhecimento::class, 'saber_conhecimento_id');
    }
    
    /**
     * Scope para objetivos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
    
    /**
     * Scope para filtrar por faixa etária
     */
    public function scopePorFaixaEtaria($query, $faixaEtaria)
    {
        return $query->where('faixa_etaria', $faixaEtaria);
    }
}
