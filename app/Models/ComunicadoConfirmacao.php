<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComunicadoConfirmacao extends Model
{
    use HasFactory;

    protected $table = 'comunicado_confirmacoes';

    protected $fillable = [
        'comunicado_id',
        'user_id',
        'confirmado_em',
        'observacoes'
    ];

    protected $casts = [
        'confirmado_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com o comunicado
     */
    public function comunicado(): BelongsTo
    {
        return $this->belongsTo(Comunicado::class);
    }

    /**
     * Relacionamento com o usuário
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para confirmações de um usuário específico
     */
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para confirmações de um comunicado específico
     */
    public function scopePorComunicado($query, $comunicadoId)
    {
        return $query->where('comunicado_id', $comunicadoId);
    }

    /**
     * Scope para confirmações em um período
     */
    public function scopeEntreDatas($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('confirmado_em', [$dataInicio, $dataFim]);
    }

    /**
     * Scope para confirmações com observações
     */
    public function scopeComObservacoes($query)
    {
        return $query->whereNotNull('observacoes');
    }
}