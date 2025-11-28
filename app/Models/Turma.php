<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Turma extends Model
{
    use HasFactory;

    protected $fillable = [
        'escola_id',
        'nome',
        'codigo',
        'descricao',
        'capacidade',
        'ativo',
        'turno_matutino',
        'turno_vespertino',
        'turno_noturno',
        'turno_integral',
        'ano_letivo',
        'turma',
        'turno_id',
        'grupo_id',
        'nivel_ensino_id'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'turno_matutino' => 'boolean',
        'turno_vespertino' => 'boolean',
        'turno_noturno' => 'boolean',
        'turno_integral' => 'boolean',
        'ano_letivo' => 'integer'
    ];

    /**
     * Relacionamento com escola
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Relacionamento com turno
     */
    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }

    /**
     * Relacionamento com grupo
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    /**
     * Relacionamento com nível de ensino
     */
    public function nivelEnsino(): BelongsTo
    {
        return $this->belongsTo(NivelEnsino::class);
    }

    /**
     * Relacionamento com alunos
     */
    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class, 'turma_id');
    }

    /**
     * Relacionamento com planejamentos
     */
    public function planejamentos(): HasMany
    {
        return $this->hasMany(Planejamento::class);
    }

    /**
     * Relacionamento com grade de aulas
     */
    public function gradeAulas(): HasMany
    {
        return $this->hasMany(GradeAula::class, 'turma_id');
    }

    /**
     * Relacionamento com salas
     * Agora via tabela pivot grade_aulas (turma_id ↔ sala_id)
     */
    public function salas(): BelongsToMany
    {
        return $this->belongsToMany(Sala::class, 'grade_aulas', 'turma_id', 'sala_id')
            ->withPivot([
                'disciplina_id',
                'funcionario_id',
                'tempo_slot_id',
                'dia_semana',
                'data_inicio',
                'data_fim',
                'ativo',
                'observacoes'
            ])
            ->withTimestamps();
    }

    /**
     * Relacionamento com sala (primeira sala encontrada)
     * Para compatibilidade com código existente
     */
    public function sala()
    {
        // Mantém compatibilidade retornando relação many para poder usar ->first()
        return $this->belongsToMany(Sala::class, 'grade_aulas', 'turma_id', 'sala_id');
    }

    

    /**
     * Scope para turmas ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para filtrar turmas por turno
     */
    public function scopePorTurno($query, $turno)
    {
        switch($turno) {
            case 'matutino':
                return $query->where('turno_matutino', true);
            case 'vespertino':
                return $query->where('turno_vespertino', true);
            case 'noturno':
                return $query->where('turno_noturno', true);
            case 'integral':
                return $query->where('turno_integral', true);
            default:
                return $query;
        }
    }

    /**
     * Scope para turmas do ano letivo atual
     */
    public function scopeAnoAtual($query)
    {
        return $query->where('ano_letivo', date('Y'));
    }

    /**
     * Scope para turmas de um grupo específico
     */
    public function scopeDoGrupo($query, $grupoId)
    {
        return $query->where('grupo_id', $grupoId);
    }

    /**
     * Accessor para nome completo
     */
    public function getNomeCompletoAttribute()
    {
        $grupoNome = $this->grupo ? $this->grupo->nome : 'S/G';
        return $grupoNome . '-' . $this->codigo . ' - ' . $this->nome;
    }

    /**
     * Accessor para identificação única
     */
    public function getIdentificacaoAttribute()
    {
        $grupoNome = $this->grupo ? $this->grupo->nome : 'Sem Grupo';
        return $grupoNome . ' - Turma ' . $this->codigo;
    }

    /**
     * Verificar se a turma suporta um turno específico
     */
    public function suportaTurno($turno)
    {
        switch($turno) {
            case 'matutino':
                return $this->turno_matutino;
            case 'vespertino':
                return $this->turno_vespertino;
            case 'noturno':
                return $this->turno_noturno;
            case 'integral':
                return $this->turno_integral;
            default:
                return false;
        }
    }

    /**
     * Contar alunos matriculados
     */
    public function getAlunosMatriculadosAttribute()
    {
        return $this->alunos()->count();
    }

    /**
     * Verificar se há vagas disponíveis
     */
    public function getVagasDisponiveisAttribute()
    {
        return $this->capacidade - $this->alunos_matriculados;
    }

    /**
     * Verificar se a turma está lotada
     */
    public function getLotadaAttribute()
    {
        return $this->alunos_matriculados >= $this->capacidade;
    }
}