<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ItemBiblioteca extends Model
{
    use HasFactory;

    protected $table = 'item_biblioteca';

    protected $fillable = [
        'escola_id',
        'titulo',
        'autores',
        'editora',
        'ano',
        'isbn',
        'tipo',
        'categorias',
        'palavras_chave',
        'status',
        'habilitado_emprestimo',
        'quantidade_fisica',
        'metadata',
    ];

    protected $casts = [
        'categorias' => 'array',
        'palavras_chave' => 'array',
        'metadata' => 'array',
    ];

    public function emprestimos()
    {
        return $this->hasMany(Emprestimo::class, 'item_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'item_id');
    }

    public function arquivosDigitais()
    {
        return $this->hasMany(ArquivoDigital::class, 'item_id');
    }

    /**
     * URL da capa do item (derivado de metadata ou arquivo digital)
     */
    public function getCapaUrlAttribute(): ?string
    {
        $meta = $this->metadata ?? [];
        if (is_array($meta)) {
            if (!empty($meta['capa_url'])) {
                return $meta['capa_url'];
            }
            if (!empty($meta['capa_path'])) {
                return Storage::url($meta['capa_path']);
            }
        }

        $arquivoCapa = $this->arquivosDigitais()
            ->whereIn('tipo', ['capa', 'imagem'])
            ->latest()
            ->first();

        if ($arquivoCapa && $arquivoCapa->storage_path) {
            return Storage::url($arquivoCapa->storage_path);
        }

        return null;
    }

    /**
     * Quantidade disponível (exemplares físicos menos empréstimos ativos)
     */
    public function getQuantidadeDisponivelAttribute(): int
    {
        $ativos = $this->emprestimos()->where('status', 'ativo')->count();
        $qtd = (int) ($this->quantidade_fisica ?? 0) - $ativos;
        return $qtd > 0 ? $qtd : 0;
    }

    /**
     * Localização física do item (derivado de metadata)
     */
    public function getLocalizacaoAttribute(): ?string
    {
        $meta = $this->metadata ?? [];
        return is_array($meta) ? ($meta['localizacao'] ?? null) : null;
    }
}