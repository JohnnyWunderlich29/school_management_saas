<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Turno extends Model
{
    protected $fillable = [
        'nome',
        'codigo',
        'hora_inicio',
        'hora_fim',
        'descricao',
        'ativo',
        'ordem',
        'escola_id'
    ];

    protected $casts = [
        'ativo' => 'boolean',
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
     * Relacionamento com Salas
     */
    public function salas(): HasMany
    {
        return $this->hasMany(Sala::class);
    }

    /**
     * Relacionamento com modalidades de ensino
     */
    public function modalidadesEnsino(): BelongsToMany
    {
        return $this->belongsToMany(ModalidadeEnsino::class, 'modalidade_ensino_turno', 'turno_id', 'modalidade_ensino_id');
    }

    /**
     * Relacionamento com grupos
     */
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupo_turno', 'turno_id', 'grupo_id');
    }

    /**
     * Relacionamento com tempo slots
     */
    public function tempoSlots(): HasMany
    {
        return $this->hasMany(TempoSlot::class);
    }

    /**
     * Relacionamento com tempo slots ativos ordenados
     */
    public function tempoSlotsAtivos(): HasMany
    {
        return $this->hasMany(TempoSlot::class)->ativos()->ordenados();
    }

    /**
     * Relacionamento apenas com slots de aula
     */
    public function slotsAula(): HasMany
    {
        return $this->hasMany(TempoSlot::class)->aulas()->ativos()->ordenados();
    }

    /**
     * Scope para turnos ativos
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
        return $query->orderBy('ordem')->orderBy('nome');
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
     * Verifica se é turno integral
     */
    public function getIsIntegralAttribute()
    {
        return strtolower($this->codigo) === 'int' || strtolower($this->nome) === 'integral';
    }

    /**
     * Retorna opções para select
     */
    public static function getOptions()
    {
        return self::ativos()->ordenados()->pluck('nome', 'id')->toArray();
    }

    /**
     * Retorna a quantidade total de slots de aula
     */
    public function getQuantidadeSlotsAulaAttribute()
    {
        return $this->slotsAula()->count();
    }

    /**
     * Retorna a duração total de aulas em minutos
     */
    public function getDuracaoTotalAulasAttribute()
    {
        return $this->slotsAula()->sum('duracao_minutos');
    }

    /**
     * Retorna a duração formatada do turno
     */
    public function getDuracaoFormatadaAttribute()
    {
        $inicio = $this->hora_inicio;
        $fim = $this->hora_fim;
        
        if (!$inicio || !$fim) {
            return 'N/A';
        }
        
        $duracao = $inicio->diffInMinutes($fim);
        $horas = intval($duracao / 60);
        $minutos = $duracao % 60;
        
        if ($horas > 0 && $minutos > 0) {
            return $horas . 'h ' . $minutos . 'min';
        } elseif ($horas > 0) {
            return $horas . 'h';
        } else {
            return $minutos . 'min';
        }
    }

    /**
     * Retorna todos os slots formatados para exibição
     */
    public function getSlotsFormatadosAttribute()
    {
        return $this->tempoSlotsAtivos->map(function ($slot) {
            return [
                'id' => $slot->id,
                'nome' => $slot->nome,
                'tipo' => $slot->tipo_formatado,
                'horario' => $slot->horario_formatado,
                'duracao' => $slot->duracao_minutos . ' min',
                'is_aula' => $slot->is_aula
            ];
        });
    }

    /**
     * Cria slots padrão para o turno baseado no tipo
     */
    public function criarSlotsPadrao()
    {
        $slots = [];
        
        switch (strtolower($this->codigo)) {
            case 'mat': // Matutino
                $slots = [
                    ['nome' => '1º Tempo', 'tipo' => 'aula', 'hora_inicio' => '07:00', 'hora_fim' => '07:50', 'ordem' => 1],
                    ['nome' => '2º Tempo', 'tipo' => 'aula', 'hora_inicio' => '07:50', 'hora_fim' => '08:40', 'ordem' => 2],
                    ['nome' => 'Recreio', 'tipo' => 'recreio', 'hora_inicio' => '08:40', 'hora_fim' => '09:00', 'ordem' => 3],
                    ['nome' => '3º Tempo', 'tipo' => 'aula', 'hora_inicio' => '09:00', 'hora_fim' => '09:50', 'ordem' => 4],
                    ['nome' => '4º Tempo', 'tipo' => 'aula', 'hora_inicio' => '09:50', 'hora_fim' => '10:40', 'ordem' => 5],
                ];
                break;
                
            case 'ves': // Vespertino
                $slots = [
                    ['nome' => '1º Tempo', 'tipo' => 'aula', 'hora_inicio' => '13:00', 'hora_fim' => '13:50', 'ordem' => 1],
                    ['nome' => '2º Tempo', 'tipo' => 'aula', 'hora_inicio' => '13:50', 'hora_fim' => '14:40', 'ordem' => 2],
                    ['nome' => 'Recreio', 'tipo' => 'recreio', 'hora_inicio' => '14:40', 'hora_fim' => '15:00', 'ordem' => 3],
                    ['nome' => '3º Tempo', 'tipo' => 'aula', 'hora_inicio' => '15:00', 'hora_fim' => '15:50', 'ordem' => 4],
                    ['nome' => '4º Tempo', 'tipo' => 'aula', 'hora_inicio' => '15:50', 'hora_fim' => '16:40', 'ordem' => 5],
                ];
                break;
                
            case 'not': // Noturno
                $slots = [
                    ['nome' => '1º Tempo', 'tipo' => 'aula', 'hora_inicio' => '19:00', 'hora_fim' => '19:45', 'ordem' => 1],
                    ['nome' => '2º Tempo', 'tipo' => 'aula', 'hora_inicio' => '19:45', 'hora_fim' => '20:30', 'ordem' => 2],
                    ['nome' => 'Intervalo', 'tipo' => 'intervalo', 'hora_inicio' => '20:30', 'hora_fim' => '20:45', 'ordem' => 3],
                    ['nome' => '3º Tempo', 'tipo' => 'aula', 'hora_inicio' => '20:45', 'hora_fim' => '21:30', 'ordem' => 4],
                ];
                break;
                
            case 'int': // Integral
                $slots = [
                    ['nome' => '1º Tempo', 'tipo' => 'aula', 'hora_inicio' => '07:00', 'hora_fim' => '07:50', 'ordem' => 1],
                    ['nome' => '2º Tempo', 'tipo' => 'aula', 'hora_inicio' => '07:50', 'hora_fim' => '08:40', 'ordem' => 2],
                    ['nome' => 'Recreio', 'tipo' => 'recreio', 'hora_inicio' => '08:40', 'hora_fim' => '09:00', 'ordem' => 3],
                    ['nome' => '3º Tempo', 'tipo' => 'aula', 'hora_inicio' => '09:00', 'hora_fim' => '09:50', 'ordem' => 4],
                    ['nome' => '4º Tempo', 'tipo' => 'aula', 'hora_inicio' => '09:50', 'hora_fim' => '10:40', 'ordem' => 5],
                    ['nome' => 'Almoço', 'tipo' => 'almoco', 'hora_inicio' => '11:00', 'hora_fim' => '12:00', 'ordem' => 6],
                    ['nome' => '5º Tempo', 'tipo' => 'aula', 'hora_inicio' => '13:00', 'hora_fim' => '13:50', 'ordem' => 7],
                    ['nome' => '6º Tempo', 'tipo' => 'aula', 'hora_inicio' => '13:50', 'hora_fim' => '14:40', 'ordem' => 8],
                    ['nome' => 'Recreio', 'tipo' => 'recreio', 'hora_inicio' => '14:40', 'hora_fim' => '15:00', 'ordem' => 9],
                    ['nome' => '7º Tempo', 'tipo' => 'aula', 'hora_inicio' => '15:00', 'hora_fim' => '15:50', 'ordem' => 10],
                ];
                break;
        }

        foreach ($slots as $slotData) {
            $this->tempoSlots()->create($slotData);
        }
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
