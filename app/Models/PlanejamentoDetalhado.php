<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanejamentoDetalhado extends Model
{
    protected $table = 'planejamentos_detalhados';
    
    protected $fillable = [
        'planejamento_id',
        'campos_experiencia_selecionados',
        'saberes_conhecimentos',
        'objetivos_aprendizagem_selecionados',
        'encaminhamentos_metodologicos',
        'recursos',
        'registros_anotacoes',
        'status',
        'finalizado_em',
        'aprovado_em',
        'aprovado_por',
        'observacoes_aprovacao'
    ];
    
    protected $casts = [
        'campos_experiencia_selecionados' => 'array',
        'objetivos_aprendizagem_selecionados' => 'array',
        'finalizado_em' => 'datetime',
        'aprovado_em' => 'datetime'
    ];
    
    /**
     * Relacionamento com planejamento
     */
    public function planejamento(): BelongsTo
    {
        return $this->belongsTo(Planejamento::class);
    }
    
    /**
     * Relacionamento com usuário que aprovou
     */
    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }
    
    /**
     * Scope para status
     */
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Verifica se o planejamento está finalizado
     */
    public function isFinalizado(): bool
    {
        return in_array($this->status, ['finalizado', 'aprovado', 'reprovado']);
    }
    
    /**
     * Verifica se o planejamento está aprovado
     */
    public function isAprovado(): bool
    {
        return $this->status === 'aprovado';
    }
}
