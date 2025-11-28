<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoliticaAcesso extends Model
{
    use HasFactory;

    protected $table = 'politicas_acesso';

    protected $fillable = [
        'escola_id',
        'perfil',
        'tipo_item',
        'max_emprestimos',
        'prazo_dias',
        'max_reservas',
        'acesso_digital_perfil',
        'janelas',
        'regras',
    ];

    protected $casts = [
        'acesso_digital_perfil' => 'boolean',
        'janelas' => 'array',
        'regras' => 'array',
    ];
}