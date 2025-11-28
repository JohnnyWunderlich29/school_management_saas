<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emprestimo extends Model
{
    use HasFactory;

    protected $table = 'emprestimos';

    protected $fillable = [
        'escola_id',
        'item_id',
        'usuario_id',
        'data_emprestimo',
        'data_prevista',
        'data_devolucao',
        'status',
        'multa_calculada',
    ];

    protected $casts = [
        'data_emprestimo' => 'datetime',
        'data_prevista' => 'datetime',
        'data_devolucao' => 'datetime',
        'multa_calculada' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(ItemBiblioteca::class, 'item_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}