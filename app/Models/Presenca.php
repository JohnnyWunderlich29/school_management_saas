<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Middleware\EscolaContext;

class Presenca extends Model
{
    use HasFactory;

    /**
     * Scope global para filtrar por escola através do aluno ou funcionário
     */
    protected static function booted()
    {
        static::addGlobalScope('escola', function (Builder $builder) {
            $escolaId = EscolaContext::getEscolaAtual();
            if ($escolaId) {
                $builder->where(function ($query) use ($escolaId) {
                    $query->whereHas('aluno', function ($q) use ($escolaId) {
                        $q->where('escola_id', $escolaId);
                    })->orWhereHas('funcionario', function ($q) use ($escolaId) {
                        $q->where('escola_id', $escolaId);
                    });
                });
            }
        });
    }

    protected $fillable = [
        'aluno_id',
        'sala_id',
        'funcionario_id',
        'data',
        'tempo_aula',
        'presente',
        'hora_entrada',
        'hora_saida',
        'justificativa',
        'observacoes'
    ];

    protected $casts = [
        'data' => 'date',
        'presente' => 'boolean',
        'hora_entrada' => 'datetime',
        'hora_saida' => 'datetime',
    ];

    /**
     * Relacionamento com aluno
     */
    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }

    /**
     * Relacionamento com funcionário que registrou a presença
     */
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    /**
     * Relacionamento com sala no momento do registro
     */
    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }

    /**
     * Scope para presenças de hoje
     */
    public function scopeHoje($query)
    {
        return $query->where('data', now()->toDateString());
    }

    /**
     * Scope para presenças por período
     */
    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data', [$dataInicio, $dataFim]);
    }

    /**
     * Scope para apenas presentes
     */
    public function scopePresentes($query)
    {
        return $query->where('presente', true);
    }

    /**
     * Scope para apenas ausentes
     */
    public function scopeAusentes($query)
    {
        return $query->where('presente', false);
    }

    /**
     * Scope para presenças com aluno e funcionário
     */
    public function scopeComRelacionamentos($query)
    {
        return $query->with(['aluno:id,nome,sobrenome', 'funcionario:id,nome,sobrenome']);
    }

    /**
     * Calcula a duração da permanência do aluno
     */
    public function getDuracaoAttribute()
    {
        if ($this->hora_entrada && $this->hora_saida) {
            return $this->hora_entrada->diffInHours($this->hora_saida);
        }
        
        return null;
    }
}
