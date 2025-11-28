<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class GradeAula extends Model
{
    protected $table = 'grade_aulas';
    
    protected $fillable = [
        'turma_id',
        'disciplina_id',
        'funcionario_id',
        'sala_id',
        'tempo_slot_id',
        'dia_semana',
        'tipo_aula',
        'tipo_periodo',
        'data_inicio',
        'data_fim',
        'ativo',
        'observacoes',
        'permite_substituicao'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'permite_substituicao' => 'boolean'
    ];

    /**
     * Dias da semana disponíveis
     */
    const DIAS_SEMANA = [
        'segunda' => 'Segunda-feira',
        'terca' => 'Terça-feira',
        'quarta' => 'Quarta-feira',
        'quinta' => 'Quinta-feira',
        'sexta' => 'Sexta-feira',
        'sabado' => 'Sábado'
    ];

    // ========== RELACIONAMENTOS ==========

    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    public function funcionario(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function professor(): BelongsTo
    {
        return $this->belongsTo(Funcionario::class, 'funcionario_id');
    }

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class);
    }

    public function tempoSlot(): BelongsTo
    {
        return $this->belongsTo(TempoSlot::class);
    }

    // ========== SCOPES ==========

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopePorDia(Builder $query, string $dia): Builder
    {
        return $query->where('dia_semana', $dia);
    }

    public function scopePorTurma(Builder $query, int $turmaId): Builder
    {
        return $query->where('turma_id', $turmaId);
    }

    public function scopePorSala(Builder $query, int $salaId): Builder
    {
        return $query->where('sala_id', $salaId);
    }

    public function scopePorProfessor(Builder $query, int $funcionarioId): Builder
    {
        return $query->where('funcionario_id', $funcionarioId);
    }

    public function scopeNoPeriodo(Builder $query, ?Carbon $dataInicio = null, ?Carbon $dataFim = null): Builder
    {
        if ($dataInicio && $dataFim) {
            return $query->where(function ($q) use ($dataInicio, $dataFim) {
                $q->whereNull('data_inicio')
                  ->orWhere(function ($subQ) use ($dataInicio, $dataFim) {
                      $subQ->where('data_inicio', '<=', $dataFim)
                           ->where('data_fim', '>=', $dataInicio);
                  });
            });
        }
        
        return $query;
    }

    // ========== MÉTODOS DE VALIDAÇÃO ==========

    /**
     * Verifica se há conflito de professor no mesmo horário
     */
    public static function temConflitoProfesor(
        int $funcionarioId, 
        string $diaSemana, 
        int $tempoSlotId, 
        ?Carbon $dataInicio = null, 
        ?Carbon $dataFim = null,
        ?int $excludeId = null
    ): bool {
        $query = self::where('funcionario_id', $funcionarioId)
            ->where('dia_semana', $diaSemana)
            ->where('tempo_slot_id', $tempoSlotId)
            ->where('ativo', true);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($dataInicio && $dataFim) {
            $query->where(function ($q) use ($dataInicio, $dataFim) {
                $q->whereNull('data_inicio')
                  ->orWhere(function ($subQ) use ($dataInicio, $dataFim) {
                      $subQ->where('data_inicio', '<=', $dataFim)
                           ->where('data_fim', '>=', $dataInicio);
                  });
            });
        }

        return $query->exists();
    }

    /**
     * Verifica se há conflito de sala no mesmo horário
     */
    public static function temConflitoSala(
        int $salaId, 
        string $diaSemana, 
        int $tempoSlotId, 
        ?Carbon $dataInicio = null, 
        ?Carbon $dataFim = null,
        ?int $excludeId = null
    ): bool {
        $query = self::where('sala_id', $salaId)
            ->where('dia_semana', $diaSemana)
            ->where('tempo_slot_id', $tempoSlotId)
            ->where('ativo', true);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($dataInicio && $dataFim) {
            $query->where(function ($q) use ($dataInicio, $dataFim) {
                $q->whereNull('data_inicio')
                  ->orWhere(function ($subQ) use ($dataInicio, $dataFim) {
                      $subQ->where('data_inicio', '<=', $dataFim)
                           ->where('data_fim', '>=', $dataInicio);
                  });
            });
        }

        return $query->exists();
    }

    /**
     * Verifica se uma sala está disponível em um horário específico
     */
    public static function salaEstaDisponivel(
        int $salaId, 
        string $diaSemana, 
        int $tempoSlotId, 
        ?Carbon $dataInicio = null, 
        ?Carbon $dataFim = null
    ): bool {
        return !self::temConflitoSala($salaId, $diaSemana, $tempoSlotId, $dataInicio, $dataFim);
    }

    /**
     * Verifica se um professor está disponível em um horário específico
     */
    public static function professorEstaDisponivel(
        int $funcionarioId, 
        string $diaSemana, 
        int $tempoSlotId, 
        ?Carbon $dataInicio = null, 
        ?Carbon $dataFim = null
    ): bool {
        return !self::temConflitoProfesor($funcionarioId, $diaSemana, $tempoSlotId, $dataInicio, $dataFim);
    }

    // ========== MÉTODOS AUXILIARES ==========

    /**
     * Retorna o dia da semana formatado
     */
    public function getDiaSemanaFormatadoAttribute(): string
    {
        return self::DIAS_SEMANA[$this->dia_semana] ?? $this->dia_semana;
    }

    /**
     * Retorna o período formatado
     */
    public function getPeriodoFormatadoAttribute(): string
    {
        if ($this->data_inicio && $this->data_fim) {
            return $this->data_inicio->format('d/m/Y') . ' a ' . $this->data_fim->format('d/m/Y');
        }
        
        return 'Período indefinido';
    }

    /**
     * Retorna informações completas da aula
     */
    public function getResumoAttribute(): string
    {
        return sprintf(
            '%s - %s (%s) - %s - %s',
            $this->turma->nome ?? 'Turma não encontrada',
            $this->disciplina->nome ?? 'Disciplina não encontrada',
            $this->funcionario->nome ?? 'Professor não encontrado',
            $this->sala->nome ?? 'Sala não encontrada',
            $this->tempoSlot->nome ?? 'Horário não encontrado'
        );
    }
}
