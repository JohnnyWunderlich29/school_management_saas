<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    use HasFactory;

    protected $table = 'despesas';

    protected $fillable = [
        'escola_id',
        'descricao',
        'categoria',
        'data',
        'valor',
        'status',
        'cancelamento_motivo',
        'cancelado_por',
        'cancelado_em',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
        'cancelado_em' => 'datetime',
    ];
}