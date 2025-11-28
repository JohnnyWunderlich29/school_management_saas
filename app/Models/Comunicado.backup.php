<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Http\Middleware\EscolaContext;

class Comunicado extends Model
{
    use HasFactory;

    /**
     * Scope global para filtrar por escola através do autor (User)
     */
    protected static function booted()
    {
        static::addGlobalScope('escola', function (Builder $builder) {
            $escolaId = EscolaContext::getEscolaAtual();
            if ($escolaId) {
                $builder->whereHas('autor', function ($q) use ($escolaId) {
                    $q->where('escola_id', $escolaId);
                });
            }
        });
    }

    protected $fillable = [
        'titulo',
        'conteudo',
        'tipo',
        'destinatario_tipo',
        'turma_id',
        'autor_id',
        'requer_confirmacao',
        'data_evento',
        'hora_evento',
        'local_evento',
        'ativo',
        'publicado_em'
    ];

    protected $casts = [
        'requer_confirmacao' => 'boolean',
        'ativo' => 'boolean',
        'data_evento' => 'date',
        'hora_evento' => 'datetime',
        'publicado_em' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com o autor
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    /**
     * Relacionamento com a turma (se aplicável)
     */
    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    /**
     * Relacionamento com as confirmações
     */
    public function confirmacoes(): HasMany
    {
        return $this->hasMany(ComunicadoConfirmacao::class);
    }

    /**
     * Scope para comunicados ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para comunicados ativas (alias para ativos)
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para comunicados publicados
     */
    public function scopePublicados($query)
    {
        return $query->whereNotNull('publicado_em')
                    ->where('publicado_em', '<=', now());
    }

    /**
     * Scope para comunicados por tipo
     */
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para comunicados por destinatário
     */
    public function scopePorDestinatario($query, $destinatarioTipo)
    {
        return $query->where('destinatario_tipo', $destinatarioTipo);
    }

    /**
     * Scope para comunicados de uma turma específica
     */
    public function scopePorTurma($query, $turmaId)
    {
        return $query->where('turma_id', $turmaId);
    }

    /**
     * Scope para comunicados urgentes
     */
    public function scopeUrgentes($query)
    {
        return $query->where('tipo', 'urgente');
    }

    /**
     * Scope para eventos futuros
     */
    public function scopeEventosFuturos($query)
    {
        return $query->whereNotNull('data_evento')
                    ->where('data_evento', '>=', now()->toDateString());
    }

    /**
     * Scope para comunicados que requerem confirmação
     */
    public function scopeRequeremConfirmacao($query)
    {
        return $query->where('requer_confirmacao', true);
    }

    /**
     * Verificar se o comunicado está publicado
     */
    public function isPublicado(): bool
    {
        return !is_null($this->publicado_em) && $this->publicado_em <= now();
    }

    /**
     * Verificar se é um evento
     */
    public function isEvento(): bool
    {
        return !is_null($this->data_evento);
    }

    /**
     * Verificar se o evento já passou
     */
    public function eventoPassou(): bool
    {
        if (!$this->isEvento()) {
            return false;
        }
        
        $dataEvento = Carbon::parse($this->data_evento);
        if ($this->hora_evento) {
            $horaEvento = Carbon::parse($this->hora_evento);
            $dataEvento = $dataEvento->setTime($horaEvento->hour, $horaEvento->minute);
        }
        
        return $dataEvento->isPast();
    }

    /**
     * Publicar comunicado
     */
    public function publicar()
    {
        $this->update([
            'publicado_em' => now(),
            'ativo' => true
        ]);
    }

    /**
     * Despublicar comunicado
     */
    public function despublicar()
    {
        $this->update([
            'ativo' => false
        ]);
    }

    /**
     * Verificar se usuário confirmou o comunicado
     */
    public function foiConfirmadoPor($userId): bool
    {
        return $this->confirmacoes()->where('user_id', $userId)->exists();
    }

    /**
     * Confirmar comunicado por um usuário
     */
    public function confirmarPor($userId, $observacoes = null)
    {
        if (!$this->foiConfirmadoPor($userId)) {
            return $this->confirmacoes()->create([
                'user_id' => $userId,
                'confirmado_em' => now(),
                'observacoes' => $observacoes
            ]);
        }
        return null;
    }

    /**
     * Contar confirmações
     */
    public function contarConfirmacoes(): int
    {
        return $this->confirmacoes()->count();
    }

    /**
     * Obter porcentagem de confirmações (baseado nos destinatários)
     */
    public function porcentagemConfirmacoes(): float
    {
        $totalDestinatarios = $this->contarDestinatarios();
        if ($totalDestinatarios === 0) {
            return 0;
        }
        
        $confirmacoes = $this->contarConfirmacoes();
        return ($confirmacoes / $totalDestinatarios) * 100;
    }

    /**
     * Contar destinatários baseado no tipo
     */
    public function contarDestinatarios(): int
    {
        switch ($this->destinatario_tipo) {
            case 'todos':
                return User::count();
            case 'pais':
                return User::whereHas('responsaveis')->count();
            case 'professores':
                return User::whereHas('funcionario')->count();
            case 'turma_especifica':
                if ($this->turma_id) {
                    return User::whereHas('alunos.turma', function ($query) {
                        $query->where('id', $this->turma_id);
                    })->count();
                }
                return 0;
            default:
                return 0;
        }
    }

    /**
     * Obter data e hora formatadas do evento
     */
    public function getDataHoraEventoFormatadaAttribute(): ?string
    {
        if (!$this->isEvento()) {
            return null;
        }
        
        $dataFormatada = Carbon::parse($this->data_evento)->format('d/m/Y');
        
        if ($this->hora_evento) {
            $horaFormatada = Carbon::parse($this->hora_evento)->format('H:i');
            return $dataFormatada . ' às ' . $horaFormatada;
        }
        
        return $dataFormatada;
    }

    /**
     * Obter classe CSS baseada no tipo
     */
    public function getClasseTipoAttribute(): string
    {
        $classes = [
            'informativo' => 'bg-blue-100 text-blue-800',
            'urgente' => 'bg-red-100 text-red-800',
            'evento' => 'bg-green-100 text-green-800',
            'reuniao' => 'bg-purple-100 text-purple-800',
            'aviso' => 'bg-yellow-100 text-yellow-800'
        ];
        
        return $classes[$this->tipo] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Obter ícone baseado no tipo
     */
    public function getIconeTipoAttribute(): string
    {
        $icones = [
            'informativo' => 'fas fa-info-circle',
            'urgente' => 'fas fa-exclamation-triangle',
            'evento' => 'fas fa-calendar-alt',
            'reuniao' => 'fas fa-users',
            'aviso' => 'fas fa-bell'
        ];
        
        return $icones[$this->tipo] ?? 'fas fa-file-alt';
    }
}