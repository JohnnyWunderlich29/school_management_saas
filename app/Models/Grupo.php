<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    protected $fillable = [
        'nome',
        'codigo',
        'idade_minima',
        'idade_maxima',
        'ano_serie',
        'descricao',
        'ativo',
        'ordem',
        'escola_id',
        'modalidade_ensino_id'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'idade_minima' => 'integer',
        'idade_maxima' => 'integer',
        'ano_serie' => 'integer',
        'ordem' => 'integer'
    ];

    /**
     * Relacionamento com Escola
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Relacionamento com ModalidadeEnsino
     */
    public function modalidadeEnsino(): BelongsTo
    {
        return $this->belongsTo(ModalidadeEnsino::class);
    }

    /**
     * Relacionamento com Salas
     */
    public function salas(): HasMany
    {
        return $this->hasMany(Sala::class);
    }

    /**
     * Relacionamento com Turmas
     */
    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class, 'grupo_id');
    }

    /**
     * Relacionamento com Turnos
     */
    public function turnos(): BelongsToMany
    {
        return $this->belongsToMany(Turno::class, 'grupo_turno', 'grupo_id', 'turno_id');
    }

    /**
     * Scope para grupos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    // Scope para grupos por modalidade removido conforme solicitado

    /**
     * Scope para ordenação
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    /**
     * Retorna a faixa etária formatada (para Educação Infantil)
     */
    public function getFaixaEtariaAttribute()
    {
        if ($this->idade_minima && $this->idade_maxima) {
            return "{$this->idade_minima} a {$this->idade_maxima} anos";
        }
        return null;
    }

    /**
     * Retorna o ano/série formatado (para Fundamental e Médio)
     */
    public function getAnoSerieFormatadoAttribute()
    {
        if ($this->ano_serie) {
            $modalidade = $this->modalidadeEnsino->nome ?? '';
            
            if (str_contains($modalidade, 'Fundamental')) {
                return $this->ano_serie . 'º Ano';
            } elseif (str_contains($modalidade, 'Médio')) {
                return $this->ano_serie . 'º Ano EM';
            }
        }
        return null;
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
