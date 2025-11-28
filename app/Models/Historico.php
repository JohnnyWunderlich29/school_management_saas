<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Http\Middleware\EscolaContext;

class Historico extends Model
{
    protected $fillable = [
        'acao',
        'modelo',
        'modelo_id',
        'usuario_id',
        'escola_id',
        'dados_antigos',
        'dados_novos',
        'ip_address',
        'user_agent',
        'observacoes'
    ];

    protected $casts = [
        'dados_antigos' => 'array',
        'dados_novos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com o usuário que fez a ação
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Método estático para registrar uma ação
     */
    public static function registrar(string $acao, string $modelo, int $modeloId, array $dadosAntigos = null, array $dadosNovos = null, string $observacoes = null)
    {
        $escolaId = EscolaContext::getEscolaAtual() ?: (auth()->user()->escola_id ?? null);
        return self::create([
            'acao' => $acao,
            'modelo' => $modelo,
            'modelo_id' => $modeloId,
            'usuario_id' => auth()->id(),
            'escola_id' => $escolaId,
            'dados_antigos' => $dadosAntigos,
            'dados_novos' => $dadosNovos,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'observacoes' => $observacoes
        ]);
    }

    /**
     * Scope para filtrar por modelo
     */
    public function scopeDoModelo($query, string $modelo)
    {
        return $query->where('modelo', $modelo);
    }

    /**
     * Scope para filtrar por ação
     */
    public function scopeDaAcao($query, string $acao)
    {
        return $query->where('acao', $acao);
    }

    /**
     * Scope para filtrar por usuário
     */
    public function scopeDoUsuario($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }
}
