<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadeEscolar extends Model
{
    use HasFactory;

    protected $table = 'unidades_escolares';

    protected $fillable = [
        'nome',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function salas()
    {
        return $this->hasMany(Sala::class);
    }
}