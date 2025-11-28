<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disciplina extends Model
{
    protected $fillable = [
        'nome',
        'codigo',
        'area_conhecimento',
        'descricao',
        'cor_hex',
        'obrigatoria',
        'ativo',
        'ordem'
    ];

    protected $casts = [
        'obrigatoria' => 'boolean',
        'ativo' => 'boolean',
        'ordem' => 'integer'
    ];

    /**
     * Relacionamento com funcionários (professores)
     */
    public function funcionarios()
    {
        return $this->belongsToMany(Funcionario::class, 'funcionario_disciplina');
    }

    /**
     * Relacionamento com Planejamentos
     */
    /**
     * Relacionamento com Salas (através da tabela pivô sala_disciplinas)
     */
    public function salas()
    {
        return $this->belongsToMany(Sala::class, 'sala_disciplinas', 'disciplina_id', 'sala_id');
    }

    /**
     * Relacionamento com Planejamentos
     */
    public function planejamentos(): HasMany
    {
        return $this->hasMany(Planejamento::class);
    }

    /**
     * Relacionamento com DisciplinaNivelEnsino
     */
    public function niveisEnsino(): HasMany
    {
        return $this->hasMany(DisciplinaNivelEnsino::class);
    }

    /**
     * Relacionamento com DisciplinaNivelEnsino (alias para consistência)
     */
    public function disciplinaNiveis(): HasMany
    {
        return $this->hasMany(DisciplinaNivelEnsino::class);
    }



    /**
     * Relacionamento muitos-para-muitos com salas
     */
    public function salasVinculadas()
    {
        return $this->belongsToMany(Sala::class, 'sala_disciplinas', 'disciplina_id', 'sala_id')
                    ->withPivot('ativo')
                    ->withTimestamps()
                    ->wherePivot('ativo', true);
    }

    /**
     * Scope para disciplinas ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para disciplinas obrigatórias
     */
    public function scopeObrigatorias($query)
    {
        return $query->where('obrigatoria', true);
    }

    /**
     * Scope para disciplinas por área de conhecimento
     */
    public function scopePorArea($query, $area)
    {
        return $query->where('area_conhecimento', $area);
    }

    /**
     * Scope para ordenação
     */
    public function scopeOrdenadas($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }

    /**
     * Retorna as áreas de conhecimento disponíveis
     */
    public static function getAreasConhecimento()
    {
        return [
            'Linguagens' => 'Linguagens',
            'Matemática' => 'Matemática',
            'Ciências da Natureza' => 'Ciências da Natureza',
            'Ciências Humanas' => 'Ciências Humanas',
            'Ensino Religioso' => 'Ensino Religioso'
        ];
    }

    /**
     * Retorna opções para select
     */
    public static function getOptions($modalidadeId = null)
    {
        $query = self::ativas()->ordenadas();
        
        return $query->pluck('nome', 'id')->toArray();
    }

    /**
     * Retorna a cor formatada para CSS
     */
    public function getCorCssAttribute()
    {
        return $this->cor_hex ?: '#6c757d'; // Cor padrão se não definida
    }

    // Removido route model binding com isolamento por escola.
    // Disciplinas são padronizadas nacionalmente e não possuem escola_id.
}
