<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArquivoDigital extends Model
{
    use HasFactory;

    protected $table = 'arquivos_digitais';

    protected $fillable = [
        'escola_id',
        'item_id',
        'tipo',
        'storage_path',
        'tamanho',
        'hash',
        'watermark',
        'expires_at',
    ];

    protected $casts = [
        'tamanho' => 'integer',
        'watermark' => 'array',
        'expires_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(ItemBiblioteca::class, 'item_id');
    }
}