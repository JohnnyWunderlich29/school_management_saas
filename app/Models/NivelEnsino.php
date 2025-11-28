<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\EscolaNivelConfig;

class NivelEnsino extends Model
{
    use HasFactory;

    protected $table = 'niveis_ensino';

    protected $fillable = [
        'nome',
        'codigo',
        'descricao',
        'capacidade_padrao',
        'ativo',
        'turno_matutino',
        'turno_vespertino',
        'turno_noturno',
        'turno_integral',
        'modalidades_compativeis'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'turno_matutino' => 'boolean',
        'turno_vespertino' => 'boolean',
        'turno_noturno' => 'boolean',
        'turno_integral' => 'boolean',
        'modalidades_compativeis' => 'array'
    ];

    /**
     * Relacionamento com turmas
     */
    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class);
    }

    /**
     * Relacionamento com turmas ativas
     */
    public function turmasAtivas(): HasMany
    {
        return $this->hasMany(Turma::class)->where('ativo', true);
    }

    /**
     * Relacionamento com planejamentos
     */
    public function planejamentos(): HasMany
    {
        return $this->hasMany(Planejamento::class);
    }

    /**
     * Relacionamento com configurações de escola
     */
    public function escolaNiveisConfig(): HasMany
    {
        return $this->hasMany(EscolaNivelConfig::class);
    }

    /**
     * Scope para níveis ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para filtrar níveis por turno
     */
    public function scopePorTurno($query, $turno)
    {
        switch($turno) {
            case 'matutino':
                return $query->where('turno_matutino', true);
            case 'vespertino':
                return $query->where('turno_vespertino', true);
            case 'noturno':
                return $query->where('turno_noturno', true);
            case 'integral':
                return $query->where('turno_integral', true);
            default:
                return $query;
        }
    }

    /**
     * Scope para filtrar níveis por modalidade
     */
    public function scopePorModalidade($query, $modalidade)
    {
        return $query->whereJsonContains('modalidades_compativeis', $modalidade);
    }

    /**
     * Accessor para nome completo
     */
    public function getNomeCompletoAttribute()
    {
        return $this->codigo . ' - ' . $this->nome;
    }

    /**
     * Verificar se o nível suporta um turno específico
     */
    public function suportaTurno($turno)
    {
        switch($turno) {
            case 'matutino':
                return $this->turno_matutino;
            case 'vespertino':
                return $this->turno_vespertino;
            case 'noturno':
                return $this->turno_noturno;
            case 'integral':
                return $this->turno_integral;
            default:
                return false;
        }
    }

    /**
     * Verificar se o nível é compatível com uma modalidade
     */
    public function compativel($modalidade)
    {
        return in_array($modalidade, $this->modalidades_compativeis ?? []);
    }
}