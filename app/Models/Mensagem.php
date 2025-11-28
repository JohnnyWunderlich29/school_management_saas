<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Middleware\EscolaContext;

class Mensagem extends Model
{
    use HasFactory;

    /**
     * Força o nome da tabela para corresponder à migration (evita 'mensagems')
     */
    protected $table = 'mensagens';

    /**
     * Scope global para filtrar por escola através do remetente (User)
     * Comentado temporariamente para resolver problema de mensagens não aparecendo
     */
    protected static function booted()
    {
        // static::addGlobalScope('escola', function (Builder $builder) {
        //     $escolaId = EscolaContext::getEscolaAtual();
        //     if ($escolaId) {
        //         $builder->whereHas('remetente', function ($q) use ($escolaId) {
        //             $q->where('escola_id', $escolaId);
        //         });
        //     }
        // });
    }

    protected $fillable = [
        'conversa_id',
        'remetente_id',
        'conteudo',
        'tipo',
        'arquivo_path',
        'arquivo_nome',
        'arquivo_tamanho',
        'importante',
        'editada_em'
    ];

    protected $casts = [
        'importante' => 'boolean',
        'editada_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com a conversa
     */
    public function conversa(): BelongsTo
    {
        return $this->belongsTo(Conversa::class);
    }

    /**
     * Relacionamento com o remetente
     */
    public function remetente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remetente_id');
    }

    /**
     * Relacionamento com as leituras
     */
    public function leituras(): HasMany
    {
        return $this->hasMany(MensagemLeitura::class);
    }

    /**
     * Scope para mensagens importantes
     */
    public function scopeImportantes($query)
    {
        return $query->where('importante', true);
    }

    /**
     * Scope para mensagens por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para mensagens com arquivos
     */
    public function scopeComArquivos($query)
    {
        return $query->whereNotNull('arquivo_path');
    }

    /**
     * Scope para mensagens não lidas por um usuário
     */
    public function scopeNaoLidasPor($query, $userId)
    {
        return $query->whereDoesntHave('leituras', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('remetente_id', '!=', $userId);
    }

    /**
     * Verificar se a mensagem foi lida por um usuário
     */
    public function foiLidaPor($userId): bool
    {
        return $this->leituras()->where('user_id', $userId)->exists();
    }

    /**
     * Marcar mensagem como lida por um usuário
     */
    public function marcarComoLida($userId)
    {
        if (!$this->foiLidaPor($userId) && $this->remetente_id != $userId) {
            return $this->leituras()->create([
                'user_id' => $userId,
                'lida_em' => now()
            ]);
        }
        return null;
    }

    /**
     * Editar conteúdo da mensagem
     */
    public function editarConteudo($novoConteudo)
    {
        $this->update([
            'conteudo' => $novoConteudo,
            'editada_em' => now()
        ]);
    }

    /**
     * Verificar se a mensagem foi editada
     */
    public function foiEditada(): bool
    {
        return !is_null($this->editada_em);
    }

    /**
     * Obter conteúdo sanitizado da mensagem
     */
    public function getConteudoSanitizado(): string
    {
        if (!$this->conteudo) {
            return '';
        }

        // Escapar HTML perigoso
        $conteudo = htmlspecialchars($this->conteudo, ENT_QUOTES, 'UTF-8');
        
        // Converter quebras de linha para <br>
        $conteudo = nl2br($conteudo);
        
        return $conteudo;
    }

    /**
     * Obter URL do arquivo anexado
     */
    public function getArquivoUrlAttribute(): ?string
    {
        if ($this->arquivo_path) {
            return Storage::url($this->arquivo_path);
        }
        return null;
    }

    /**
     * Verificar se tem arquivo anexado
     */
    public function temArquivo(): bool
    {
        return !is_null($this->arquivo_path);
    }

    /**
     * Obter extensão do arquivo
     */
    public function getArquivoExtensaoAttribute(): ?string
    {
        if ($this->arquivo_nome) {
            return pathinfo($this->arquivo_nome, PATHINFO_EXTENSION);
        }
        return null;
    }

    /**
     * Formatar tamanho do arquivo
     */
    public function getArquivoTamanhoFormatadoAttribute(): ?string
    {
        if ($this->arquivo_tamanho) {
            $bytes = $this->arquivo_tamanho;
            $units = ['B', 'KB', 'MB', 'GB'];
            
            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                $bytes /= 1024;
            }
            
            return round($bytes, 2) . ' ' . $units[$i];
        }
        return null;
    }

    /**
     * Verificar se é uma imagem
     */
    public function isImagem(): bool
    {
        if ($this->tipo === 'imagem') {
            return true;
        }
        
        $extensoesImagem = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        return in_array(strtolower($this->arquivo_extensao ?? ''), $extensoesImagem);
    }

    /**
     * Verificar se é um vídeo
     */
    public function isVideo(): bool
    {
        if ($this->tipo === 'video') {
            return true;
        }
        
        $extensoesVideo = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'];
        return in_array(strtolower($this->arquivo_extensao ?? ''), $extensoesVideo);
    }

    /**
     * Verificar se é um áudio
     */
    public function isAudio(): bool
    {
        if ($this->tipo === 'audio') {
            return true;
        }
        
        $extensoesAudio = ['mp3', 'wav', 'ogg', 'aac', 'm4a'];
        return in_array(strtolower($this->arquivo_extensao ?? ''), $extensoesAudio);
    }

    /**
     * Deletar arquivo anexado
     */
    public function deletarArquivo()
    {
        if ($this->arquivo_path && Storage::exists($this->arquivo_path)) {
            Storage::delete($this->arquivo_path);
        }
    }

    /**
     * Boot method para eventos do modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Atualizar timestamp da conversa quando uma mensagem é criada
        static::created(function ($mensagem) {
            $mensagem->conversa->atualizarUltimaMensagem();
        });

        // Deletar arquivo quando mensagem é deletada
        static::deleting(function ($mensagem) {
            $mensagem->deletarArquivo();
        });
    }
}