<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formacao extends Model
{
    protected $table = 'formacoes';
    
    protected $fillable = [
        'nome',
        'descricao',
        'ativo'
    ];
    
    public function funcionarios()
    {
        return $this->belongsToMany(Funcionario::class, 'funcionario_formacao');
    }
}
