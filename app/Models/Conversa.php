<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Conversa extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'tipo',
        'descricao',
        'criador_id',
        'turma_id',
        'ativo',
        'ultima_mensagem_at'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ultima_mensagem_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com o criador da conversa
     */
    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criador_id');
    }

    /**
     * Relacionamento com a turma (se aplicável)
     */
    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    /**
     * Relacionamento com as mensagens
     */
    public function mensagens(): HasMany
    {
        return $this->hasMany(Mensagem::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relacionamento com os participantes
     */
    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversa_participantes')
                    ->withPivot(['tipo_participante', 'entrou_em', 'saiu_em', 'ativo'])
                    ->withTimestamps();
    }

    /**
     * Participantes ativos
     */
    public function participantesAtivos(): BelongsToMany
    {
        return $this->participantes()->wherePivot('ativo', true);
    }

    /**
     * Última mensagem da conversa
     */
    public function ultimaMensagem()
    {
        return $this->hasOne(Mensagem::class)->latest();
    }

    /**
     * Scope para conversas ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('conversas.ativo', true);
    }

    /**
     * Scope para conversas por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para conversas de uma turma
     */
    public function scopePorTurma($query, $turmaId)
    {
        return $query->where('turma_id', $turmaId);
    }

    /**
     * Scope para conversas que o usuário participa
     */
    public function scopeParticipante($query, $userId)
    {
        return $query->whereHas('participantes', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('conversa_participantes.ativo', true);
        });
    }

    /**
     * Adicionar participante à conversa
     */
    public function adicionarParticipante($userId, $tipoParticipante = 'responsavel')
    {
        return $this->participantes()->attach($userId, [
            'tipo_participante' => $tipoParticipante,
            'entrou_em' => now(),
            'ativo' => true
        ]);
    }

    /**
     * Remover participante da conversa
     */
    public function removerParticipante($userId)
    {
        return $this->participantes()->updateExistingPivot($userId, [
            'saiu_em' => now(),
            'ativo' => false
        ]);
    }

    /**
     * Verificar se usuário é participante ativo
     */
    public function isParticipante($userId): bool
    {
        return $this->participantesAtivos()->where('user_id', $userId)->exists();
    }

    /**
     * Atualizar timestamp da última mensagem
     */
    public function atualizarUltimaMensagem()
    {
        $this->update(['ultima_mensagem_at' => now()]);
    }

    /**
     * Contar mensagens não lidas para um usuário
     */
    public function contarMensagensNaoLidas($userId): int
    {
        return $this->mensagens()
            ->whereDoesntHave('leituras', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('remetente_id', '!=', $userId)
            ->count();
    }

    /**
     * Scope para carregar contagem de mensagens não lidas
     */
    public function scopeComContagemMensagensNaoLidas($query, $userId)
    {
        return $query->withCount([
            'mensagens as mensagens_nao_lidas' => function ($q) use ($userId) {
                $q->whereDoesntHave('leituras', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->where('remetente_id', '!=', $userId);
            }
        ]);
    }

    /**
     * Marcar todas as mensagens como lidas para um usuário
     */
    public function marcarComoLida($userId)
    {
        $mensagensNaoLidas = $this->mensagens()
            ->whereDoesntHave('leituras', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('remetente_id', '!=', $userId)
            ->get();

        foreach ($mensagensNaoLidas as $mensagem) {
            $mensagem->leituras()->create([
                'user_id' => $userId,
                'lida_em' => now()
            ]);
        }
    }
}