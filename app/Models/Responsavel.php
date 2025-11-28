<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Responsavel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'responsaveis';

    protected $fillable = [
        'escola_id',
        'nome',
        'sobrenome',
        'data_nascimento',
        'genero',
        'cpf',
        'rg',
        'telefone_principal',
        'telefone_secundario',
        'email',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'parentesco',
        'autorizado_buscar',
        'contato_emergencia',
        'observacoes',
        'ativo'
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'autorizado_buscar' => 'boolean',
        'contato_emergencia' => 'boolean',
        'ativo' => 'boolean',
    ];

    /**
     * Retorna o nome completo do responsável
     */
    public function getNomeCompletoAttribute()
    {
        return "{$this->nome} {$this->sobrenome}";
    }

    /**
     * Relacionamento com escola
     */
    public function escola()
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Relacionamento com alunos
     */
    public function alunos()
    {
        return $this->belongsToMany(Aluno::class, 'aluno_responsavel')
            ->withPivot('responsavel_principal')
            ->withTimestamps();
    }

    /**
     * Scope para responsáveis ativos
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
            $q->where('nome', 'like', "%{$nome}%")
              ->orWhere('sobrenome', 'like', "%{$nome}%")
              ->orWhereRaw("CONCAT(nome, ' ', sobrenome) like ?", ["%{$nome}%"]);
        });
    }
}
