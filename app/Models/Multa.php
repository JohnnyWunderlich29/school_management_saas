<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Multa extends Model
{
    use HasFactory;

    protected $table = 'multas';

    protected $fillable = [
        'escola_id',
        'emprestimo_id',
        'usuario_id',
        'valor',
        'status',
        'paga',
        'data_multa',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'paga' => 'boolean',
        'data_multa' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function emprestimo()
    {
        return $this->belongsTo(Emprestimo::class, 'emprestimo_id');
    }

    public function getItemAttribute()
    {
        return $this->emprestimo ? $this->emprestimo->item : null;
    }
}