<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensagemLeitura extends Model
{
    use HasFactory;

    protected $table = 'mensagem_leituras';

    protected $fillable = [
        'mensagem_id',
        'user_id',
        'lida_em'
    ];

    protected $casts = [
        'lida_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com a mensagem
     */
    public function mensagem(): BelongsTo
    {
        return $this->belongsTo(Mensagem::class);
    }

    /**
     * Relacionamento com o usuário
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope para leituras de um usuário específico
     */
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para leituras de uma mensagem específica
     */
    public function scopePorMensagem($query, $mensagemId)
    {
        return $query->where('mensagem_id', $mensagemId);
    }

    /**
     * Scope para leituras em um período
     */
    public function scopeEntreDatas($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('lida_em', [$dataInicio, $dataFim]);
    }
}