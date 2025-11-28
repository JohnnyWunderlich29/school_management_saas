<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sala extends Model
{
    protected $fillable = [
        'escola_id',
        'nome',
        'codigo',
        'descricao',
        'capacidade',
        'tipo',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    /**
     * Scope para salas ativas
     */
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
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
     * Relacionamento com usuários (professores) - mantido para criação/edição
     */
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'user_salas', 'sala_id', 'user_id')
                    ->withPivot('escola_id')
                    ->withTimestamps();
    }

    /**
     * Relacionamento com grade de aulas (tabela pivot)
     */
    public function gradeAulas()
    {
        return $this->hasMany(GradeAula::class);
    }

    /**
     * Relacionamento com turmas através da grade de aulas
     */
    public function turmas()
    {
        return $this->belongsToMany(Turma::class, 'grade_aulas')
                    ->withPivot([
                        'disciplina_id', 
                        'professor_id', 
                        'dia_semana', 
                        'horario_inicio', 
                        'horario_fim',
                        'data_inicio',
                        'data_fim',
                        'status',
                        'observacoes'
                    ])
                    ->withTimestamps();
    }

    /**
     * Relacionamento com alunos
     */
    public function alunos()
    {
        return $this->hasMany(Aluno::class);
    }

    /**
     * Accessor para nome completo da sala
     */
    public function getNomeCompletoAttribute()
    {
        return $this->codigo . ' - ' . $this->nome;
    }

    /**
     * Accessor para nome do grupo
     */
    public function getGrupoNomeAttribute()
    {
        return $this->grupo?->nome ?? 'Não informado';
    }

    /**
     * Accessor para nome do turno
     */
    public function getTurnoNomeAttribute()
    {
        return $this->turno?->nome ?? 'Não informado';
    }

    /**
     * Accessor para identificação completa da sala
     */
    public function getIdentificacaoCompletaAttribute()
    {
        $partes = [];
        
        if ($this->grupo) {
            $partes[] = $this->grupo->nome;
        }
        
        if ($this->turma) {
            $partes[] = 'Turma ' . $this->turma;
        }
        
        if ($this->turno) {
            $partes[] = $this->turno->nome;
        }
        
        return implode(' - ', $partes) ?: $this->nome_completo;
    }



    /**
     * Scope para filtrar por grupo
     */
    public function scopePorGrupo($query, $grupoId)
    {
        return $query->where('grupo_id', $grupoId);
    }

    /**
     * Scope para filtrar por turno (novo método)
     */
    public function scopePorTurnoId($query, $turnoId)
    {
        return $query->where('turno_id', $turnoId);
    }

    /**
     * Scope para filtrar por turma
     */
    public function scopePorTurma($query, $turma)
    {
        return $query->where('turma', $turma);
    }

    /**
     * Retorna as turmas disponíveis
     */
    public static function getTurmasDisponiveis()
    {
        return ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
    }

    /**
     * Resolve route model binding com isolamento por escola
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();
        
        $query = $this->where($field, $value);
        
        // Aplicar filtro de escola baseado no usuário autenticado
        $user = auth()->user();
        if ($user) {
            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                // Super Admin e Suporte: OBRIGATÓRIO ter escola atual da sessão
                $escolaAtual = session('escola_atual');
                if ($escolaAtual) {
                    $query->where('escola_id', $escolaAtual);
                } else {
                    // Sem escola atual na sessão, negar acesso
                    return null;
                }
            } else {
                // Usuários normais: filtrar pela escola do usuário
                if ($user->escola_id) {
                    $query->where('escola_id', $user->escola_id);
                } else {
                    // Usuário sem escola, negar acesso
                    return null;
                }
            }
        } else {
            // Usuário não autenticado, negar acesso
            return null;
        }
        
        return $query->first();
    }
}
