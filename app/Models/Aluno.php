<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Aluno extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'escola_id',
        'nome',
        'matricula',
        'sobrenome',
        'data_nascimento',
        'cpf',
        'rg',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'telefone',
        'email',
        'genero',
        'tipo_sanguineo',
        'alergias',
        'medicamentos',
        'observacoes',
        'ativo',
        'sala_id',
        'turma_id'
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'ativo' => 'boolean',
    ];

    /**
     * Retorna o nome completo do aluno
     */
    public function getNomeCompletoAttribute()
    {
        return "{$this->nome} {$this->sobrenome}";
    }

    /**
     * Scope para alunos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para busca por nome
     */
    public function scopeBuscarPorNome($query, $nome)
    {
        return $query->where(function($q) use ($nome) {
            $q->where('nome', 'like', '%' . $nome . '%')
              ->orWhere('sobrenome', 'like', '%' . $nome . '%')
              ->orWhereRaw("CONCAT(nome, ' ', sobrenome) LIKE ?", ['%' . $nome . '%']);
        });
    }

    /**
     * Scope para alunos com sala
     */
    public function scopeComSala($query)
    {
        return $query->whereNotNull('sala_id');
    }

    /**
     * Relacionamento com responsáveis
     */
    public function responsaveis()
    {
        return $this->belongsToMany(Responsavel::class, 'aluno_responsavel')
            ->withPivot('responsavel_principal')
            ->withTimestamps();
    }

    /**
     * Relacionamento com presenças
     */
    public function presencas()
    {
        return $this->hasMany(Presenca::class);
    }

    /**
     * Relacionamento com Sala
     */
    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }

    public function turma()
    {
        return $this->belongsTo(Turma::class);
    }

    /**
     * Relacionamento com a escola
     */
    public function escola()
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Relacionamento com documentos
     */
    public function documentos()
    {
        return $this->hasMany(AlunoDocumento::class);
    }

    /**
     * Scope para filtrar por escola
     */
    public function scopePorEscola($query, $escolaId)
    {
        return $query->where('escola_id', $escolaId);
    }

    /**
     * Retorna o responsável principal do aluno
     */
    public function responsavelPrincipal()
    {
        return $this->belongsToMany(Responsavel::class, 'aluno_responsavel')
            ->wherePivot('responsavel_principal', true)
            ->first();
    }
}
