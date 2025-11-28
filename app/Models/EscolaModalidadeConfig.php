<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EscolaModalidadeConfig extends Model
{
    use HasFactory;

    protected $table = 'escola_modalidades_config';

    protected $fillable = [
        'escola_id',
        'modalidade_ensino_id',
        'ativo',
        'capacidade_maxima_turma',
        'capacidade_minima_turma',
        'capacidade_padrao_turma',
        'permite_turno_matutino',
        'permite_turno_vespertino',
        'permite_turno_noturno',
        'permite_turno_integral',
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
     * Scope para modalidades ativas
     */
    public function scopeAtivas($query)
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
     * Verifica se a modalidade está ativa para a escola
     */
    public function isAtiva(): bool
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
        return $this->capacidade_padrao_turma ?? $this->modalidadeEnsino->capacidade_padrao ?? 25;
    }
}
