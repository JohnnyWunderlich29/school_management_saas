<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TempoSlot extends Model
{
    protected $table = 'tempo_slots';
    
    protected $fillable = [
        'escola_id',
        'turno_id',
        'nome',
        'tipo',
        'hora_inicio',
        'hora_fim',
        'ordem',
        'duracao_minutos',
        'descricao',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer',
        'duracao_minutos' => 'integer'
    ];

    /**
     * Tipos de tempo slot disponíveis
     */
    const TIPOS = [
        'aula' => 'Aula',
        'recreio' => 'Recreio',
        'almoco' => 'Almoço',
        'intervalo' => 'Intervalo'
    ];

    /**
     * Relacionamento com Escola
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Relacionamento com Turno
     */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    /**
     * Scope para slots ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para ordenação
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem');
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para slots de aula (excluindo recreios e intervalos)
     */
    public function scopeAulas($query)
    {
        return $query->where('tipo', 'aula');
    }

    /**
     * Retorna o horário formatado
     */
    public function getHorarioFormatadoAttribute()
    {
        $inicio = $this->hora_inicio ? substr($this->hora_inicio, 0, 5) : 'N/A';
        $fim = $this->hora_fim ? substr($this->hora_fim, 0, 5) : 'N/A';
        return $inicio . ' às ' . $fim;
    }

    /**
     * Retorna o tipo formatado
     */
    public function getTipoFormatadoAttribute()
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /**
     * Verifica se é um slot de aula
     */
    public function getIsAulaAttribute()
    {
        return $this->tipo === 'aula';
    }

    /**
     * Verifica se é um slot de recreio/intervalo
     */
    public function getIsIntervaloAttribute()
    {
        return in_array($this->tipo, ['recreio', 'intervalo', 'almoco']);
    }

    /**
     * Calcula automaticamente a duração em minutos
     */
    public function calcularDuracao()
    {
        if ($this->hora_inicio && $this->hora_fim) {
            $inicio = Carbon::parse($this->hora_inicio);
            $fim = Carbon::parse($this->hora_fim);
            $this->duracao_minutos = $fim->diffInMinutes($inicio);
        }
    }

    /**
     * Boot method para calcular duração automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($tempoSlot) {
            $tempoSlot->calcularDuracao();
        });
    }

    /**
     * Retorna opções para select de tipos
     */
    public static function getTiposOptions()
    {
        return self::TIPOS;
    }

    /**
     * Valida se não há sobreposição de horários no mesmo turno
     */
    public function validarSobreposicao()
    {
        $query = self::where('turno_id', $this->turno_id)
                     ->where('ativo', true);
                     
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        return $query->where(function ($q) {
            $q->whereBetween('hora_inicio', [$this->hora_inicio, $this->hora_fim])
              ->orWhereBetween('hora_fim', [$this->hora_inicio, $this->hora_fim])
              ->orWhere(function ($q2) {
                  $q2->where('hora_inicio', '<=', $this->hora_inicio)
                     ->where('hora_fim', '>=', $this->hora_fim);
              });
        })->exists();
    }

    /**
     * Retorna o tempo slot anterior no mesmo turno
     */
    public function getTempoSlotAnterior()
    {
        return self::where('turno_id', $this->turno_id)
                   ->where('ordem', '<', $this->ordem)
                   ->orderBy('ordem', 'desc')
                   ->first();
    }

    /**
     * Retorna o próximo tempo slot no mesmo turno
     */
    public function getProximoTempoSlot()
    {
        return self::where('turno_id', $this->turno_id)
                   ->where('ordem', '>', $this->ordem)
                   ->orderBy('ordem', 'asc')
                   ->first();
    }

    /**
     * Retorna a hora de início formatada
     */
    public function getHoraInicioFormatadaAttribute()
    {
        return $this->hora_inicio ? substr($this->hora_inicio, 0, 5) : '';
    }

    /**
     * Retorna a hora de fim formatada
     */
    public function getHoraFimFormatadaAttribute()
    {
        return $this->hora_fim ? substr($this->hora_fim, 0, 5) : '';
    }
}
