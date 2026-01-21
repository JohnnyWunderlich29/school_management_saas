<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecorrenciaDespesa extends Model
{
    use HasFactory;

    protected $table = 'recorrencia_despesas';

    protected $fillable = [
        'escola_id',
        'descricao',
        'categoria',
        'valor',
        'frequencia',
        'dia_vencimento',
        'data_inicio',
        'data_fim',
        'proxima_geracao',
        'ativo',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'proxima_geracao' => 'date',
        'ativo' => 'boolean',
    ];

    public function escola()
    {
        return $this->belongsTo(Escola::class);
    }

    public function despesas()
    {
        return $this->hasMany(Despesa::class, 'recorrencia_id');
    }
}
