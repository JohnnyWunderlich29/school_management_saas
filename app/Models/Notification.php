<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Middleware\EscolaContext;

class Notification extends Model
{
    use HasFactory;

    /**
     * Escopo global por escola usando escola_id
     */
    protected static function booted()
    {
        static::addGlobalScope('escola', function (Builder $builder) {
            $escolaId = EscolaContext::getEscolaAtual();
            if ($escolaId) {
                $builder->where('escola_id', $escolaId);
            }
        });
    }

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'user_id',
        'escola_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('is_global', true);
        });
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function markAsRead(): bool
    {
        if ($this->read_at) {
            return true;
        }
        return $this->update(['read_at' => now()]);
    }

    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function getReadAtFormattedAttribute(): ?string
    {
        return $this->read_at ? $this->read_at->format('d/m/Y H:i') : null;
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getCreatedAtRelativeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

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

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public static function createForUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        ?string $actionText = null
    ): self {
        $user = User::find($userId);
        $escolaId = $user?->escola_id ?? EscolaContext::getEscolaAtual();
        return self::create([
            'user_id' => $userId,
            'escola_id' => $escolaId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'is_global' => false
        ]);
    }

    public static function createGlobal(
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $actionUrl = null,
        ?string $actionText = null
    ): self {
        $escolaId = EscolaContext::getEscolaAtual();
        return self::create([
            'user_id' => null,
            'escola_id' => $escolaId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'action_text' => $actionText,
            'is_global' => true
        ]);
    }

    public static function markAllAsReadForUser(int $userId): int
    {
        return self::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    public static function getUnreadCountForUser(int $userId): int
    {
        return self::forUser($userId)->unread()->count();
    }

    public static function cleanOldNotifications(): int
    {
        return self::where('created_at', '<', now()->subDays(30))
            ->whereNotNull('read_at')
            ->delete();
    }
}