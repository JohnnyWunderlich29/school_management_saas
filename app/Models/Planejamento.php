<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ModalidadeEnsino;
use App\Http\Middleware\EscolaContext;

class Planejamento extends Model
{
    use HasFactory;

    /**
     * Scope global para filtrar por escola
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
        'escola_id',
        'user_id',
        'turma_id',
        'disciplina_id',
        'professor_id',
        'tipo_professor', // Mantido temporariamente para compatibilidade
        'modalidade_id',
        'modalidade', // Campo para compatibilidade
        'nivel_ensino_id',
        'nivel_ensino', // Campo para compatibilidade
        'turno_id',
        'grupo_id',
        'numero_dias',
        'data_inicio',
        'data_fim',
        'titulo',
        'objetivo_geral',
        'objetivos_especificos',
        'competencias_bncc',
        'habilidades_bncc',
        'metodologia',
        'recursos_necessarios',
        'avaliacao_metodos',
        'status',
        'observacoes',
        'observacoes_rejeicao',
        'observacoes_finais',
        'unidade_escolar',
        'professor_responsavel',
        'carga_horaria_aula',
        'carga_horaria_total',
        'aulas_por_semana',
        'total_aulas',
        'tipo_periodo',
        'bimestre',
        'ano_letivo',
        'campos_experiencia',
        'saberes_conhecimentos',
        'objetivos_aprendizagem',
        'rejeicoes_count'
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'objetivos_especificos' => 'array',
        'competencias_bncc' => 'array',
        'habilidades_bncc' => 'array',
        'recursos_necessarios' => 'array',
        'avaliacao_metodos' => 'array',
        'campos_experiencia' => 'array',
        'objetivos_aprendizagem' => 'array',
        'cronograma' => 'array',
        'numero_dias' => 'integer',
        'carga_horaria_aula' => 'decimal:2',
        'carga_horaria_total' => 'decimal:2',
        'aulas_por_semana' => 'integer',
        'total_aulas' => 'integer',
        'rejeicoes_count' => 'integer'
    ];
    
    protected $appends = [
        'tipo_professor_formatado',
        'modalidade_ensino',
        'modalidade_ensino_id',
        'status_efetivo',
        'status_efetivo_formatado'
    ];

    /**
     * Relacionamento com o usuário (professor)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o nível de ensino
     */
    public function nivelEnsino(): BelongsTo
    {
        return $this->belongsTo(NivelEnsino::class);
    }
    
    /**
     * Relacionamento com a disciplina
     */
    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    /**
     * Relacionamento com a turma
     */
    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    /**
     * Relacionamento com a escola
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Relacionamento com o professor responsável (User)
     */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    /**
     * Relacionamento com o criador (User)
     */
    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relacionamento com planejamento detalhado
     */
    public function planejamentoDetalhado()
    {
        return $this->hasOne(PlanejamentoDetalhado::class);
    }

    /**
     * Relação com planejamentos diários (Etapa 5 granular)
     */
    public function diarios()
    {
        return $this->hasMany(PlanejamentoDiario::class, 'planejamento_id');
    }

    /**
     * Accessor para obter o aprovador (User) via planejamento detalhado
     */
    public function getAprovadorAttribute()
    {
        return $this->planejamentoDetalhado ? $this->planejamentoDetalhado->aprovadoPor : null;
    }

    /**
     * Scope para planejamentos por status
     */
    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para planejamentos do usuário atual
     */
    public function scopeDoUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para planejamentos por modalidade
     */
    public function scopePorModalidade($query, $modalidade)
    {
        return $query->where('modalidade', $modalidade);
    }

    /**
     * Accessor para nome da modalidade formatado
     */
    public function getModalidadeFormatadaAttribute()
    {
        // Verificar se temos um ID de modalidade
        if (isset($this->attributes['modalidade_id']) && is_numeric($this->attributes['modalidade_id'])) {
            // Buscar a modalidade pelo ID
            $modalidadeObj = ModalidadeEnsino::find($this->attributes['modalidade_id']);
            if ($modalidadeObj) {
                return $modalidadeObj->nome;
            }
        }
        
        // Verificar se temos um ID de modalidade no campo antigo
        if (isset($this->attributes['modalidade']) && is_numeric($this->attributes['modalidade'])) {
            // Buscar a modalidade pelo ID
            $modalidadeObj = ModalidadeEnsino::find($this->attributes['modalidade']);
            if ($modalidadeObj) {
                return $modalidadeObj->nome;
            }
        }
        
        // Verificar se temos um código de modalidade
        $modalidades = ModalidadeEnsino::getOptionsByCodigo();
        $modalidade = is_array($this->modalidade) ? (string) json_encode($this->modalidade) : $this->modalidade;
        
        // Retornar o nome da modalidade ou o valor original
        return $modalidades[$modalidade] ?? $modalidade;
    }

    /**
     * Accessor para modalidade_ensino (compatibilidade)
     */
    public function getModalidadeEnsinoAttribute()
    {
        // Verificar se temos um ID de modalidade
        if (isset($this->attributes['modalidade_id']) && is_numeric($this->attributes['modalidade_id'])) {
            // Buscar a modalidade pelo ID
            $modalidadeObj = ModalidadeEnsino::find($this->attributes['modalidade_id']);
            if ($modalidadeObj) {
                return $modalidadeObj->nome;
            }
        }
        
        // Verificar se temos um ID de modalidade no campo antigo
        if (isset($this->attributes['modalidade']) && is_numeric($this->attributes['modalidade'])) {
            // Buscar a modalidade pelo ID
            $modalidadeObj = ModalidadeEnsino::find($this->attributes['modalidade']);
            if ($modalidadeObj) {
                return $modalidadeObj->nome;
            }
        }
        
        // Verificar se temos um código de modalidade
        $modalidades = ModalidadeEnsino::getOptionsByCodigo();
        $modalidade = is_array($this->modalidade) ? (string) json_encode($this->modalidade) : $this->modalidade;
        
        // Retornar o nome da modalidade ou o valor original
        return $modalidades[$modalidade] ?? $modalidade ?? 'Não definida';
    }

    /**
     * Accessor para modalidade_ensino_id (compatibilidade)
     */
    public function getModalidadeEnsinoIdAttribute()
    {
        // Retornar modalidade_id se existir
        if (isset($this->attributes['modalidade_id']) && is_numeric($this->attributes['modalidade_id'])) {
            return $this->attributes['modalidade_id'];
        }
        
        // Retornar modalidade se for numérico (ID)
        if (isset($this->attributes['modalidade']) && is_numeric($this->attributes['modalidade'])) {
            return $this->attributes['modalidade'];
        }
        
        return null;
    }



    /**
     * Accessor para nome do turno formatado
     */
    public function getTurnoFormatadoAttribute()
    {
        $turnos = [
            'matutino' => 'Matutino',
            'vespertino' => 'Vespertino',
            'noturno' => 'Noturno',
            'integral' => 'Integral'
        ];

        $turno = is_array($this->turno) ? (string) json_encode($this->turno) : $this->turno;
        return $turnos[$turno] ?? $turno;
    }

    /**
     * Accessor para tipo de professor formatado
     */
    public function getTipoProfessorFormatadoAttribute()
    {
        // Verificar se o atributo tipo_professor existe
        if (!isset($this->attributes['tipo_professor'])) {
            return 'N/A';
        }
        
        // Buscar o tipo de professor pelo código
        $tipoProfessor = \App\Models\TipoProfessor::where('codigo', $this->attributes['tipo_professor'])->first();
        
        // Se encontrou, retorna o nome, senão retorna o código original ou N/A
        return $tipoProfessor ? $tipoProfessor->nome : ($this->attributes['tipo_professor'] ?: 'N/A');
    }
    
    /**
     * Relacionamento com o tipo de professor
     */
    public function tipoProfessor()
    {
        return $this->belongsTo(\App\Models\TipoProfessor::class, 'tipo_professor', 'codigo');
    }

    /**
     * Accessor para status formatado
     */
    public function getStatusFormatadoAttribute()
    {
        $status = [
            'rascunho' => 'Rascunho',
            'revisao' => 'Aguardando Aprovação',
            'finalizado' => 'Aguardando Aprovação',
            'aprovado' => 'Aprovado',
            'rejeitado' => 'Rejeitado'
        ];

        return $status[$this->status] ?? $this->status;
    }

    /**
     * Accessor para status efetivo considerando planejamento detalhado
     */
    public function getStatusEfetivoAttribute()
    {
        $detalhado = $this->planejamentoDetalhado;
        if ($detalhado && $detalhado->status) {
            $st = $detalhado->status;
            // Normalizar possíveis diferenças de nomenclatura
            if ($st === 'reprovado') {
                $st = 'rejeitado';
            }
            return $st;
        }
        return $this->status;
    }

    /**
     * Accessor para status efetivo formatado (usado em badges na UI)
     */
    public function getStatusEfetivoFormatadoAttribute()
    {
        // Quando existir planejamento detalhado com status, usamos a formatação própria
        $detalhado = $this->planejamentoDetalhado;
        if ($detalhado && $detalhado->status) {
            $map = [
                'rascunho' => 'Rascunho',
                'revisao' => 'Aguardando Aprovação',
                'finalizado' => 'Finalizado',
                'aprovado' => 'Aprovado',
                'rejeitado' => 'Rejeitado',
                'reprovado' => 'Rejeitado',
            ];
            $st = $detalhado->status === 'reprovado' ? 'rejeitado' : $detalhado->status;
            return $map[$st] ?? ucfirst((string) $st);
        }

        // Caso contrário, usar formatação do status base do Planejamento
        // Aqui, 'finalizado' no status base significa "Aguardando Aprovação"
        return $this->status_formatado;
    }

    /**
     * Método para obter as opções de modalidade
     */
    public static function getModalidadesOptions()
    {
        return ModalidadeEnsino::getOptionsByCodigo();
    }

    /**
     * Método para obter as opções de turno
     */
    public static function getTurnosOptions()
    {
        return [
            'matutino' => 'Matutino',
            'vespertino' => 'Vespertino',
            'noturno' => 'Noturno',
            'integral' => 'Integral'
        ];
    }

    /**
     * Método para obter as opções de tipo de professor
     */
    public static function getTiposProfessorOptions()
    {
        return [
            'pedagogia' => 'Pedagogia',
            'educacao_fisica' => 'Educação Física',
            'artes_visuais' => 'Artes Visuais',
            'artes_danca' => 'Artes - Dança',
            'artes_musica' => 'Artes - Música',
            'artes_teatro' => 'Artes - Teatro',
            'lingua_portuguesa' => 'Língua Portuguesa',
            'matematica' => 'Matemática',
            'ciencias' => 'Ciências',
            'historia' => 'História',
            'geografia' => 'Geografia',
            'lingua_inglesa' => 'Língua Inglesa',
            'ensino_religioso' => 'Ensino Religioso',
            'outros' => 'Outros'
        ];
    }

    /**
     * Método para obter as opções de turma
     */
    public static function getTurmasOptions()
    {
        return [
            'A' => 'Turma A',
            'B' => 'Turma B',
            'C' => 'Turma C',
            'D' => 'Turma D',
            'E' => 'Turma E',
            'F' => 'Turma F',
            'G' => 'Turma G',
            'H' => 'Turma H'
        ];
    }
}