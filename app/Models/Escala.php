<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Middleware\EscolaContext;

class Escala extends Model
{
    use HasFactory;

    /**
     * Scope global para filtrar por escola através do funcionário ou sala
     */
    protected static function booted()
    {
        static::addGlobalScope('escola', function (Builder $builder) {
            $escolaId = EscolaContext::getEscolaAtual();
            if ($escolaId) {
                $builder->where(function ($query) use ($escolaId) {
                    $query->whereHas('funcionario', function ($q) use ($escolaId) {
                        $q->where('escola_id', $escolaId);
                    })->orWhereHas('sala', function ($q) use ($escolaId) {
                        $q->where('escola_id', $escolaId);
                    });
                });
            }
        });
    }

    protected $fillable = [
        'funcionario_id',
        'sala_id',
        'data',
        'hora_inicio',
        'hora_fim',
        'tipo_escala',
        'tipo_atividade',
        'status',
        'observacoes'
    ];

    /**
     * Tipos de escala disponíveis
     */
    const TIPOS_ESCALA = [
        'Normal' => 'Normal',
        'Extra' => 'Extra',
        'Substituição' => 'Substituição'
    ];

    /**
     * Status disponíveis
     */
    const STATUS = [
        'Agendada' => 'Agendada',
        'Ativa' => 'Ativa',
        'Concluída' => 'Concluída'
    ];

    /**
     * Tipos de atividade disponíveis
     */
    const TIPOS_ATIVIDADE = [
        'em_sala' => 'Em Sala',
        'pl' => 'PL (Planejamento)',
        'ausente' => 'Ausente'
    ];

    protected $casts = [
        'data' => 'date',
    ];

    /**
     * Mutators para garantir formato correto das horas e data
     */
    public function getHoraInicioAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    public function getHoraFimAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : null;
    }

    /**
     * Accessor para formatar data no padrão brasileiro
     */
    public function getDataFormatadaAttribute()
    {
        return $this->data ? $this->data->format('d/m/Y') : null;
    }

    /**
     * Accessor para formatar data no formato Y-m-d para inputs
     */
    public function getDataInputAttribute()
    {
        return $this->data ? $this->data->format('Y-m-d') : null;
    }

    /**
     * Relacionamento com funcionário
     */
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    /**
     * Relacionamento com Sala
     */
    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }

    /**
     * Verifica se a escala está ativa
     */
    public function isAtiva(): bool
    {
        return $this->status === 'Ativa';
    }

    /**
     * Verifica se a escala está agendada
     */
    public function isAgendada(): bool
    {
        return $this->status === 'Agendada';
    }

    /**
     * Verifica se a escala está concluída
     */
    public function isConcluida(): bool
    {
        return $this->status === 'Concluída';
    }

    /**
     * Verifica se é uma escala normal
     */
    public function isNormal(): bool
    {
        return $this->tipo_escala === 'Normal';
    }

    /**
     * Verifica se é uma escala extra
     */
    public function isExtra(): bool
    {
        return $this->tipo_escala === 'Extra';
    }

    /**
     * Verifica se é uma escala de substituição
     */
    public function isSubstituicao(): bool
    {
        return $this->tipo_escala === 'Substituição';
    }

    /**
     * Calcula a duração da escala em horas
     */
    public function getDuracaoAttribute()
    {
        return $this->hora_inicio->diffInHours($this->hora_fim);
    }

    /**
     * Verifica se o professor está em sala neste horário
     */
    public function isEmSala()
    {
        return $this->tipo_atividade === 'em_sala';
    }

    /**
     * Verifica se o professor está em PL neste horário
     */
    public function isPL()
    {
        return $this->tipo_atividade === 'pl';
    }

    /**
     * Verifica se o professor está ausente neste horário
     */
    public function isAusente()
    {
        return $this->tipo_atividade === 'ausente';
    }

    /**
     * Scope para escalas em sala
     */
    public function scopeEmSala($query)
    {
        return $query->where('tipo_atividade', 'em_sala');
    }

    /**
     * Scope para escalas de PL
     */
    public function scopePL($query)
    {
        return $query->where('tipo_atividade', 'pl');
    }
}
