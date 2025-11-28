<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected $fillable = [
        'escola_id',
        'item_id',
        'usuario_id',
        'data_reserva',
        'status',
        'expires_at',
        'prioridade',
    ];

    protected $casts = [
        'data_reserva' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(ItemBiblioteca::class, 'item_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Data em que a reserva foi processada.
     * Retorna o atributo persistido se existir; caso contrário, quando status for 'processada', usa updated_at.
     */
    public function getDataProcessamentoAttribute(): ?Carbon
    {
        if (array_key_exists('data_processamento', $this->attributes) && $this->attributes['data_processamento']) {
            try {
                return Carbon::parse($this->attributes['data_processamento']);
            } catch (\Throwable $e) {
                return null;
            }
        }

        return $this->status === 'processada' && $this->updated_at ? Carbon::parse($this->updated_at) : null;
    }

    /**
     * Data de cancelamento da reserva.
     * Retorna o atributo persistido se existir; caso contrário, quando status for 'cancelada', usa updated_at.
     */
    public function getDataCancelamentoAttribute(): ?Carbon
    {
        if (array_key_exists('data_cancelamento', $this->attributes) && $this->attributes['data_cancelamento']) {
            try {
                return Carbon::parse($this->attributes['data_cancelamento']);
            } catch (\Throwable $e) {
                return null;
            }
        }

        return $this->status === 'cancelada' && $this->updated_at ? Carbon::parse($this->updated_at) : null;
    }

    /**
     * Motivo do cancelamento, quando disponível.
     */
    public function getMotivoCancelamentoAttribute(): ?string
    {
        return array_key_exists('motivo_cancelamento', $this->attributes)
            ? ($this->attributes['motivo_cancelamento'] ?: null)
            : null;
    }

    /**
     * Posição na fila de reservas para o item.
     * Replica a lógica usada no controller para calcular a posição.
     */
    public function getPosicaoFilaAttribute(): ?int
    {
        // Apenas para reservas ativas a posição faz sentido
        if ($this->status !== 'ativa') {
            return null;
        }

        $countAntes = static::where('item_id', $this->item_id)
            ->where('status', 'ativa')
            ->where(function ($query) {
                $query->where('prioridade', '<', $this->prioridade)
                    ->orWhere(function ($q) {
                        $q->where('prioridade', $this->prioridade)
                            ->where('created_at', '<', $this->created_at);
                    });
            })
            ->count();

        return $countAntes + 1;
    }
}