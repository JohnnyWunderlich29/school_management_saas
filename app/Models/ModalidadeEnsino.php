<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModalidadeEnsino extends Model
{
    protected $table = 'modalidades_ensino';

    protected $fillable = [
        'codigo',
        'nome',
        'nivel',
        'descricao',
        'ativo',
        'escola_id'
    ];

    protected $casts = [
        'ativo' => 'boolean'
    ];

    /**
     * Boot do modelo para configurar eventos
     */
    protected static function boot()
    {
        parent::boot();

        // Quando uma modalidade personalizada for criada, criar automaticamente sua configuração
        static::created(function ($modalidade) {
            // Só criar configuração para modalidades personalizadas (que têm escola_id)
            if ($modalidade->escola_id) {
                \App\Models\EscolaModalidadeConfig::create([
                    'escola_id' => $modalidade->escola_id,
                    'modalidade_ensino_id' => $modalidade->id,
                    'ativo' => true,
                    'capacidade_minima_turma' => 1,
                    'capacidade_maxima_turma' => 30,
                    'permite_turno_matutino' => true,
                    'permite_turno_vespertino' => true,
                    'permite_turno_noturno' => false,
                    'permite_turno_integral' => false,
                    'data_ativacao' => now(),
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            }
        });
    }

    /**
     * Relacionamento com Escola
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Relacionamento com salas - REMOVIDO
     * A coluna modalidade_ensino_id foi removida da tabela salas na reestruturação
     * As salas agora usam um campo enum 'modalidade_ensino' em vez de foreign key
     */
    // public function salas(): HasMany
    // {
    //     return $this->hasMany(Sala::class, 'modalidade_ensino_id');
    // }

    /**
     * Relacionamento com turnos
     */
    public function turnos(): BelongsToMany
    {
        return $this->belongsToMany(Turno::class, 'modalidade_ensino_turno', 'modalidade_ensino_id', 'turno_id');
    }

    /**
     * Relacionamento com grupos
     */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'modalidade_ensino_id');
    }

    /**
     * Relacionamento com configuração da escola
     */
    public function configuracaoEscola(): HasMany
    {
        return $this->hasMany(EscolaModalidadeConfig::class, 'modalidade_ensino_id');
    }

    /**
     * Scope para modalidades ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Retorna as modalidades ativas como array para select
     */
    public static function getOptions()
    {
        return self::ativas()->pluck('nome', 'id')->toArray();
    }

    /**
     * Retorna as modalidades ativas como array com código como chave
     */
    public static function getOptionsByCodigo()
    {
        return self::ativas()->pluck('nome', 'codigo')->toArray();
    }

    /**
     * Scope para ordenar modalidades
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('nome');
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
                    $query->where('escola_id', $escolaAtual);
                } else {
                    // Sem escola atual na sessão, negar acesso
                    return null;
                }
            } else {
                // Usuários normais: filtrar pela escola do usuário
                if ($user->escola_id) {
                    $query->where('escola_id', $user->escola_id);
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
