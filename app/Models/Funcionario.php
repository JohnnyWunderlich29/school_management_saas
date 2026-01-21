<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'escola_id',
        'nome',
        'sobrenome',
        'cpf',
        'rg',
        'data_nascimento',
        'telefone',
        'email',
        'endereco',
        'cidade',
        'estado',
        'cep',
        'cargo',
        'departamento',
        'data_contratacao',
        'data_demissao',
        'salario',
        'ativo',
        'observacoes',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_contratacao' => 'date',
        'data_demissao' => 'date',
        'salario' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    /**
     * Retorna o nome completo do funcionário
     */
    public function getNomeCompletoAttribute()
    {
        return "{$this->nome} {$this->sobrenome}";
    }

    /**
     * Scope para funcionários ativos
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
        return $query->where(function ($q) use ($nome) {
            $q->where('nome', 'like', '%'.$nome.'%')
                ->orWhere('sobrenome', 'like', '%'.$nome.'%')
                ->orWhereRaw("CONCAT(nome, ' ', sobrenome) LIKE ?", ['%'.$nome.'%']);
        });
    }

    /**
     * Scope para funcionários por cargo (através do relacionamento com user)
     */
    public function scopePorCargo($query, $cargo)
    {
        return $query->whereHas('user.cargos', function ($q) use ($cargo) {
            $q->where('nome', $cargo);
        });
    }

    /**
     * Relacionamento com o usuário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
     * Relacionamento com escalas
     */
    public function escalas()
    {
        return $this->hasMany(Escala::class);
    }

    /**
     * Relacionamento com presenças registradas
     */
    public function presencasRegistradas()
    {
        return $this->hasMany(Presenca::class);
    }

    /**
     * Relacionamento com templates de escalas
     */
    public function templates()
    {
        return $this->hasMany(FuncionarioTemplate::class);
    }

    /**
     * Relacionamento com template ativo
     */
    public function templateAtivo()
    {
        return $this->hasOne(FuncionarioTemplate::class)->where('ativo', true);
    }

    /**
     * Relacionamento com cargos através do usuário
     * Os cargos são atribuídos ao usuário, não diretamente ao funcionário
     */
    public function cargos()
    {
        return $this->hasOneThrough(
            \Illuminate\Database\Eloquent\Collection::class,
            User::class,
            'id', // Foreign key on users table
            'user_id', // Foreign key on funcionarios table
            'user_id', // Local key on funcionarios table
            'id' // Local key on users table
        );
    }

    /**
     * Helper para acessar os cargos do funcionário através do usuário
     */
    public function getCargosAttribute()
    {
        return $this->user ? $this->user->cargos : collect();
    }

    /**
     * Relacionamento com disciplinas
     */
    public function disciplinas()
    {
        return $this->belongsToMany(Disciplina::class, 'funcionario_disciplina');
    }
}
