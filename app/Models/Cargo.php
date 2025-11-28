<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cargo extends Model
{
    use HasFactory;

    protected $fillable = [
        'escola_id',
        'nome',
        'tipo_cargo',
        'descricao',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean'
    ];

    /**
     * Relacionamento com permissões
     */
    public function permissoes(): BelongsToMany
    {
        return $this->belongsToMany(Permissao::class, 'cargo_permissoes', 'cargo_id', 'permissao_id');
    }

    /**
     * Relacionamento com usuários
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_cargos', 'cargo_id', 'user_id');
    }

    /**
     * Verifica se o cargo possui uma permissão específica
     */
    public function temPermissao(string $permissao): bool
    {
        return $this->permissoes()->where('nome', $permissao)->exists();
    }

    /**
     * Relacionamento com a escola
     */
    public function escola()
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Scope para filtrar por escola
     */
    public function scopePorEscola($query, $escolaId)
    {
        return $query->where('escola_id', $escolaId);
    }

    /**
     * Verifica se o cargo é do tipo Professor
     */
    public function isProfessor(): bool
    {
        return $this->tipo_cargo === 'professor' || 
               stripos($this->nome, 'professor') !== false;
    }

    /**
     * Verifica se o cargo é do tipo Coordenador
     */
    public function isCoordenador(): bool
    {
        return $this->tipo_cargo === 'coordenador' || 
               stripos($this->nome, 'coordenador') !== false;
    }

    /**
     * Verifica se o cargo é do tipo Administrador
     */
    public function isAdministrador(): bool
    {
        return $this->tipo_cargo === 'administrador' || 
               stripos($this->nome, 'administrador') !== false;
    }

    /**
     * Scope para filtrar cargos por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where(function($q) use ($tipo) {
            $q->where('tipo_cargo', $tipo)
              ->orWhere('nome', 'like', '%' . $tipo . '%');
        });
    }

    /**
     * Scope para cargos de professor
     */
    public function scopeProfessores($query)
    {
        return $query->where(function($q) {
            $q->where('tipo_cargo', 'professor')
              ->orWhere('nome', 'like', '%professor%');
        });
    }

    /**
     * Scope para cargos de coordenador
     */
    public function scopeCoordenadores($query)
    {
        return $query->where(function($q) {
            $q->where('tipo_cargo', 'coordenador')
              ->orWhere('nome', 'like', '%coordenador%');
        });
    }
}
