<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultaRegra extends Model
{
    use HasFactory;

    protected $table = 'multa_regras';

    protected $fillable = [
        'escola_id',
        'taxa_por_dia',
        'valor_maximo',
        'excecoes',
    ];

    protected $casts = [
        'taxa_por_dia' => 'decimal:2',
        'valor_maximo' => 'decimal:2',
        'excecoes' => 'array',
    ];
}