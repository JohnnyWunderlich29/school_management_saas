<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AlunoDocumento extends Model
{
    protected $table = 'aluno_documentos';

    protected $fillable = [
        'aluno_id',
        'nome_original',
        'nome_arquivo',
        'tipo_mime',
        'tamanho',
        'caminho'
    ];

    /**
     * Relacionamento com Aluno
     */
    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    /**
     * Retorna a URL do arquivo
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->caminho);
    }

    /**
     * Retorna o tamanho formatado
     */
    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
