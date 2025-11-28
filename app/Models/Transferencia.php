<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Middleware\EscolaContext;
use App\Models\Turma;

class Transferencia extends Model
{
    /**
     * Scope global para filtrar por escola através do aluno
     */
    protected static function booted()
    {
        static::addGlobalScope('escola', function (Builder $builder) {
            $escolaId = EscolaContext::getEscolaAtual();
            if ($escolaId) {
                $builder->whereHas('aluno', function ($q) use ($escolaId) {
                    $q->where('escola_id', $escolaId);
                });
            }
        });
    }
    protected $fillable = [
        'aluno_id',
        'turma_id',
        'turma_destino_id',
        'solicitante_id',
        'aprovador_id',
        'status',
        'motivo',
        'observacoes_aprovador',
        'data_solicitacao',
        'data_aprovacao'
    ];

    protected $casts = [
        'data_solicitacao' => 'datetime',
        'data_aprovacao' => 'datetime'
    ];

    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    public function turmaOrigem(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    public function turmaDestino(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_destino_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovador_id');
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeAprovadas($query)
    {
        return $query->where('status', 'aprovada');
    }

    public function scopeRejeitadas($query)
    {
        return $query->where('status', 'rejeitada');
    }

    /**
     * Resolve route model binding com isolamento por escola
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();
        
        $query = $this->where($field, $value);
        
        // Aplicar filtro de escola baseado no usuário autenticado
        $user = auth()->user();
        if ($user) {
            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                // Super Admin e Suporte: OBRIGATÓRIO ter escola atual da sessão
                $escolaAtual = session('escola_atual');
                if ($escolaAtual) {
                    $query->whereHas('aluno', function ($q) use ($escolaAtual) {
                        $q->where('escola_id', $escolaAtual);
                    });
                } else {
                    // Sem escola atual na sessão, negar acesso
                    return null;
                }
            } else {
                // Usuários normais: filtrar pela escola do usuário
                if ($user->escola_id) {
                    $query->whereHas('aluno', function ($q) use ($user) {
                        $q->where('escola_id', $user->escola_id);
                    });
                } else {
                    // Usuário sem escola, negar acesso
                    return null;
                }
            }
        } else {
            // Usuário não autenticado, negar acesso
            return null;
        }
        
        return $query->first();
    }
}
