<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Http\Middleware\EscolaContext;

class Report extends Model
{
    use HasFactory;

    /**
     * Scope global para filtrar por escola através do usuário
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
        'name',
        'type',
        'description',
        'filters',
        'data',
        'format',
        'status',
        'file_path',
        'file_size',
        'user_id',
        'escola_id',
        'generated_at',
        'expires_at'
    ];

    protected $casts = [
        'filters' => 'array',
        'data' => 'array',
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com escola
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Scope para relatórios pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para relatórios em processamento
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope para relatórios concluídos
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para relatórios falhados
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope para relatórios por tipo
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para relatórios não expirados
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope para relatórios expirados
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
    }

    /**
     * Verificar se o relatório está concluído
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verificar se o relatório está pendente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verificar se o relatório está em processamento
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Verificar se o relatório falhou
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Verificar se o relatório expirou
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Marcar como processando
     */
    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Marcar como concluído
     */
    public function markAsCompleted($filePath, $fileSize = null)
    {
        $this->update([
            'status' => 'completed',
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'generated_at' => now(),
            'expires_at' => now()->addDays(30) // Expira em 30 dias
        ]);
    }

    /**
     * Marcar como falhado
     */
    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Obter URL de download
     */
    public function getDownloadUrlAttribute(): ?string
    {
        if (!$this->isCompleted() || !$this->file_path || $this->isExpired()) {
            return null;
        }

        return route('reports.download', $this->id);
    }

    /**
     * Obter tamanho formatado do arquivo
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Obter classe CSS baseada no status
     */
    public function getStatusClassAttribute(): string
    {
        return match($this->status) {
            'completed' => 'bg-green-100 text-green-800',
            'processing' => 'bg-yellow-100 text-yellow-800',
            'failed' => 'bg-red-100 text-red-800',
            'pending' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Obter ícone baseado no status
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'completed' => 'fas fa-check-circle',
            'processing' => 'fas fa-spinner fa-spin',
            'failed' => 'fas fa-times-circle',
            'pending' => 'fas fa-clock',
            default => 'fas fa-question-circle'
        };
    }

    /**
     * Obter ícone baseado no formato
     */
    public function getFormatIconAttribute(): string
    {
        return match($this->format) {
            'pdf' => 'fas fa-file-pdf text-red-600',
            'excel' => 'fas fa-file-excel text-green-600',
            'csv' => 'fas fa-file-csv text-blue-600',
            default => 'fas fa-file'
        };
    }

    /**
     * Excluir arquivo do storage quando o modelo for deletado
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($report) {
            if ($report->file_path && Storage::exists($report->file_path)) {
                Storage::delete($report->file_path);
            }
        });
    }

    /**
     * Limpar relatórios expirados
     */
    public static function cleanExpired()
    {
        $expiredReports = self::expired()->get();
        
        foreach ($expiredReports as $report) {
            $report->delete();
        }
        
        return $expiredReports->count();
    }
}