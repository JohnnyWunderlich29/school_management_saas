<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EscolaModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'escola_id',
        'module_id',
        'is_active',
        'monthly_price',
        'contracted_at',
        'expires_at',
        'contracted_by',
        'notes',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'monthly_price' => 'decimal:2',
        'contracted_at' => 'datetime',
        'expires_at' => 'datetime',
        'settings' => 'array'
    ];

    /**
     * Relacionamento com a escola
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Relacionamento com o módulo
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Relacionamento com o usuário que contratou
     */
    public function contractedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contracted_by');
    }

    /**
     * Scope para módulos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para módulos não expirados
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope para módulos válidos (ativos e não expirados)
     */
    public function scopeValid($query)
    {
        return $query->active()->notExpired();
    }

    /**
     * Scope para uma escola específica
     */
    public function scopeForEscola($query, $escolaId)
    {
        return $query->where('escola_id', $escolaId);
    }

    /**
     * Verifica se o módulo está válido (ativo e não expirado)
     */
    public function isValid(): bool
    {
        return $this->is_active && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Verifica se o módulo está expirado
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Retorna os dias restantes até a expiração
     */
    public function getDaysUntilExpiration(): ?int
    {
        if ($this->expires_at === null) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Retorna o status do módulo
     */
    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        $daysUntilExpiration = $this->getDaysUntilExpiration();
        if ($daysUntilExpiration !== null && $daysUntilExpiration <= 7) {
            return 'expiring_soon';
        }

        return 'active';
    }

    /**
     * Retorna a descrição do status
     */
    public function getStatusDescriptionAttribute(): string
    {
        switch ($this->status) {
            case 'inactive':
                return 'Inativo';
            case 'expired':
                return 'Expirado';
            case 'expiring_soon':
                return 'Expira em breve';
            case 'active':
                return 'Ativo';
            default:
                return 'Desconhecido';
        }
    }

    /**
     * Retorna a cor do status
     */
    public function getStatusColorAttribute(): string
    {
        switch ($this->status) {
            case 'inactive':
                return 'gray';
            case 'expired':
                return 'red';
            case 'expiring_soon':
                return 'yellow';
            case 'active':
                return 'green';
            default:
                return 'gray';
        }
    }

    /**
     * Ativa o módulo
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Desativa o módulo
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Renova o módulo por um período específico
     */
    public function renew(int $months = 1): bool
    {
        $newExpirationDate = $this->expires_at 
            ? $this->expires_at->addMonths($months)
            : now()->addMonths($months);

        return $this->update([
            'expires_at' => $newExpirationDate,
            'is_active' => true
        ]);
    }
}