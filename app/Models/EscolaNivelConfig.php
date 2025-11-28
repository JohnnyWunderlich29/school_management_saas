<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EscolaNivelConfig extends Model
{
    use HasFactory;

    protected $table = 'escola_niveis_config';

    protected $fillable = [
        'escola_id',
        'nivel_ensino_id',
        'ativo',
        'capacidade_maxima_turma',
        'capacidade_minima_turma',
        'capacidade_padrao_turma',
        'permite_turno_matutino',
        'permite_turno_vespertino',
        'permite_turno_noturno',
        'permite_turno_integral',
        'carga_horaria_semanal',
        'numero_aulas_semana',
        'duracao_aula_minutos',
        'idade_minima',
        'idade_maxima',
        'configuracoes_extras',
        'observacoes',
        'data_ativacao',
        'data_desativacao',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'permite_turno_matutino' => 'boolean',
        'permite_turno_vespertino' => 'boolean',
        'permite_turno_noturno' => 'boolean',
        'permite_turno_integral' => 'boolean',
        'configuracoes_extras' => 'array',
        'data_ativacao' => 'date',
        'data_desativacao' => 'date',
        'capacidade_maxima_turma' => 'integer',
        'capacidade_minima_turma' => 'integer',
        'capacidade_padrao_turma' => 'integer',
        'carga_horaria_semanal' => 'integer',
        'numero_aulas_semana' => 'integer',
        'duracao_aula_minutos' => 'integer',
        'idade_minima' => 'integer',
        'idade_maxima' => 'integer',
    ];

    /**
     * Relacionamento com Escola
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Relacionamento com NivelEnsino
     */
    public function nivelEnsino(): BelongsTo
    {
        return $this->belongsTo(NivelEnsino::class);
    }

    /**
     * Relacionamento com usuário que criou
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relacionamento com usuário que atualizou
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope para níveis ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para uma escola específica
     */
    public function scopeParaEscola($query, $escolaId)
    {
        return $query->where('escola_id', $escolaId);
    }

    /**
     * Scope para uma faixa etária específica
     */
    public function scopeParaIdade($query, $idade)
    {
        return $query->where(function($q) use ($idade) {
            $q->where('idade_minima', '<=', $idade)
              ->where('idade_maxima', '>=', $idade);
        });
    }

    /**
     * Verifica se o nível está ativo para a escola
     */
    public function isAtivo(): bool
    {
        return $this->ativo && 
               (!$this->data_desativacao || $this->data_desativacao->isFuture()) &&
               (!$this->data_ativacao || $this->data_ativacao->isPast());
    }

    /**
     * Retorna os turnos permitidos como array
     */
    public function getTurnosPermitidos(): array
    {
        $turnos = [];
        
        if ($this->permite_turno_matutino) $turnos[] = 'matutino';
        if ($this->permite_turno_vespertino) $turnos[] = 'vespertino';
        if ($this->permite_turno_noturno) $turnos[] = 'noturno';
        if ($this->permite_turno_integral) $turnos[] = 'integral';
        
        return $turnos;
    }

    /**
     * Retorna a capacidade padrão ou a configurada
     */
    public function getCapacidadePadrao(): int
    {
        return $this->capacidade_padrao_turma ?? $this->nivelEnsino->capacidade_padrao ?? 25;
    }

    /**
     * Verifica se uma idade está dentro da faixa permitida
     */
    public function aceitaIdade(int $idade): bool
    {
        $minima = $this->idade_minima ?? $this->nivelEnsino->idade_minima ?? 0;
        $maxima = $this->idade_maxima ?? $this->nivelEnsino->idade_maxima ?? 100;
        
        return $idade >= $minima && $idade <= $maxima;
    }

    /**
     * Retorna a carga horária semanal em horas
     */
    public function getCargaHorariaSemanalHoras(): float
    {
        return $this->carga_horaria_semanal ? $this->carga_horaria_semanal / 60 : 0;
    }

    /**
     * Calcula a carga horária total baseada no número de aulas e duração
     */
    public function calcularCargaHoraria(): int
    {
        if ($this->numero_aulas_semana && $this->duracao_aula_minutos) {
            return $this->numero_aulas_semana * $this->duracao_aula_minutos;
        }
        
        return $this->carga_horaria_semanal ?? 0;
    }
}
