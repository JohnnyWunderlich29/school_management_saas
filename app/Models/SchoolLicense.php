<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SchoolLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'escola_id',
        'module_name',
        'is_active',
        'expires_at',
        'max_users',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'max_users' => 'integer',
    ];

    /**
     * Relacionamento com a escola
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Relacionamento com o usuário que criou a licença
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para licenças ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope para licenças de um módulo específico
     */
    public function scopeForModule($query, string $module)
    {
        return $query->where('module_name', $module);
    }

    /**
     * Scope para licenças de uma escola específica
     */
    public function scopeForSchool($query, int $escolaId)
    {
        return $query->where('escola_id', $escolaId);
    }

    /**
     * Verifica se a licença está ativa
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->expires_at > now();
    }

    /**
     * Verifica se a licença está expirada
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    /**
     * Verifica se a licença está próxima do vencimento
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expires_at->diffInDays(now()) <= $days;
    }

    /**
     * Retorna os dias restantes da licença
     */
    public function daysRemaining(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return $this->expires_at->diffInDays(now());
    }

    /**
     * Formata a data de expiração
     */
    public function getFormattedExpirationAttribute(): string
    {
        return $this->expires_at->format('d/m/Y');
    }

    /**
     * Retorna o status da licença em texto
     */
    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inativa';
        }

        if ($this->isExpired()) {
            return 'Expirada';
        }

        if ($this->isExpiringSoon()) {
            return 'Expirando em breve';
        }

        return 'Ativa';
    }

    /**
     * Retorna a cor do status para exibição
     */
    public function getStatusColorAttribute(): string
    {
        if (!$this->is_active || $this->isExpired()) {
            return 'red';
        }

        if ($this->isExpiringSoon()) {
            return 'yellow';
        }

        return 'green';
    }
}