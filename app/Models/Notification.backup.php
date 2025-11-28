<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'user_id',
        'read_at',
        'is_global',
        'action_url',
        'action_text'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'is_global' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para notificações não lidas
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope para notificações lidas
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope para notificações de um usuário específico ou globais
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('is_global', true);
        });
    }

    /**
     * Scope para notificações por tipo
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Marcar notificação como lida
     */
    public function markAsRead(): bool
    {
        if ($this->read_at) {
            return true; // Já está lida
        }

        return $this->update(['read_at' => now()]);
    }

    /**
     * Marcar notificação como não lida
     */
    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    /**
     * Verificar se a notificação está lida
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Verificar se a notificação é não lida
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Obter a data de leitura formatada
     */
    public function getReadAtFormattedAttribute(): ?string
    {
        return $this->read_at ? $this->read_at->format('d/m/Y H:i') : null;
    }

    /**
     * Obter a data de criação formatada
     */
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Obter a data de criação em formato relativo
     */
    public function getCreatedAtRelativeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Obter a classe CSS baseada no tipo
     */
    public function getTypeClassAttribute(): string
    {
        return match($this->type) {
            'success' => 'text-green-600 bg-green-100',
            'warning' => 'text-yellow-600 bg-yellow-100',
            'error' => 'text-red-600 bg-red-100',
            'info' => 'text-blue-600 bg-blue-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    /**
     * Obter o ícone baseado no tipo
     */
    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'error' => 'x-circle',
            'info' => 'information-circle',
            default => 'bell'
        };
    }

    /**
     * Obter a classe do ícone baseada no tipo
     */
    public function getIconClassAttribute(): string
    {
        return match($this->type) {
            'success' => 'fas fa-check-circle text-green-600',
            'warning' => 'fas fa-exclamation-triangle text-yellow-600',
            'error' => 'fas fa-times-circle text-red-600',
            'info' => 'fas fa-info-circle text-blue-600',
            'announcement' => 'fas fa-bullhorn text-purple-600',
            default => 'fas fa-bell text-gray-600'
        };
    }

    /**
     * Obter a data formatada
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Criar uma notificação para um usuário específico
     */
    public static function createForUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        ?string $actionText = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'is_global' => false
        ]);
    }

    /**
     * Criar uma notificação global
     */
    public static function createGlobal(
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        ?string $actionText = null
    ): self {
        return self::create([
            'user_id' => null,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'is_global' => true
        ]);
    }

    /**
     * Marcar todas as notificações de um usuário como lidas
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        return self::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Obter contagem de notificações não lidas para um usuário
     */
    public static function getUnreadCountForUser(int $userId): int
    {
        return self::forUser($userId)->unread()->count();
    }

    /**
     * Limpar notificações antigas (mais de 30 dias)
     */
    public static function cleanOldNotifications(): int
    {
        return self::where('created_at', '<', now()->subDays(30))
            ->whereNotNull('read_at')
            ->delete();
    }
}