<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BibliotecaPolitica extends Model
{
    protected $table = 'biblioteca_politicas';

    protected $fillable = [
        'escola_id',
        'permitir_funcionarios',
        'permitir_alunos',
        'max_emprestimos_por_usuario',
        'prazo_padrao_dias',
        'bloquear_por_multas',
    ];

    protected $casts = [
        'permitir_funcionarios' => 'boolean',
        'permitir_alunos' => 'boolean',
        'bloquear_por_multas' => 'boolean',
        'max_emprestimos_por_usuario' => 'integer',
        'prazo_padrao_dias' => 'integer',
    ];
}