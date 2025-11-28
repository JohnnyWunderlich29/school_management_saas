<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'email_verified_at',
        'preferences',
        'escola_id',
        'ativo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'welcome_seen_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'ativo' => 'boolean',
        ];
    }

    /**
     * Indica se o usuário já viu o modal de boas-vindas
     */
    public function hasSeenWelcome(): bool
    {
        // Em ambientes com Model::preventAccessingMissingAttributes(),
        // acessar atributos não carregados dispara exceção.
        // Se o atributo não foi recuperado na query, tratamos como "não visto".
        $attributes = $this->getAttributes();
        if (!\array_key_exists('welcome_seen_at', $attributes)) {
            return false;
        }
        return !is_null($this->getAttribute('welcome_seen_at'));
    }

    /**
     * Relacionamento com cargos
     */
    public function cargos(): BelongsToMany
    {
        return $this->belongsToMany(Cargo::class, 'user_cargos', 'user_id', 'cargo_id');
    }

    /**
     * Verifica se o usuário possui uma permissão específica
     */
    public function temPermissao(string $permissao): bool
    {
        return $this->cargos()->whereHas('permissoes', function ($query) use ($permissao) {
            $query->where('nome', $permissao)->where('ativo', true);
        })->exists();
    }

    // REMOVIDO: temPermissaoEstrita (não é necessário com temPermissao estrito)

    /**
     * Verifica permissão de forma estrita (apenas por cargos/permissões ativas),
     * sem conceder acesso total por ser Super Admin ou dono inicial da escola.
     */
    public function temPermissaoEstrita(string $permissao): bool
    {
        return $this->cargos()->whereHas('permissoes', function ($query) use ($permissao) {
            $query->where('nome', $permissao)->where('ativo', true);
        })->exists();
    }

    /**
     * Verifica se o usuário possui um cargo específico
     */
    public function temCargo(string $cargo): bool
    {
        return $this->cargos()->where('nome', $cargo)->where('ativo', true)->exists();
    }

    /**
     * Verifica se o usuário é super administrador
     */
    public function isSuperAdmin(): bool
    {
        return $this->temCargo('Super Administrador');
    }

    /**
     * Obtém todas as permissões do usuário através de seus cargos
     */
    public function todasPermissoes()
    {
        return $this->cargos()->with('permissoes')->get()->pluck('permissoes')->flatten()->unique('id');
    }

    /**
     * Relacionamento com salas
     */
    public function salas(): BelongsToMany
    {

        return $this->belongsToMany(Sala::class, 'user_salas', 'user_id', 'sala_id')
                    ->withPivot('ativo');
    }

    /**
     * Relacionamento com unidade escolar
     */
    public function unidadeEscolar()
    {
        return $this->belongsTo(UnidadeEscolar::class);
    }

    /**
     * Relacionamento com escola
     */
    public function escola()
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * Verifica se o usuário tem acesso a uma sala específica
     */
    public function temAcessoSala(int $salaId): bool
    {
        // Admin e Coordenador têm acesso a todas as salas
        if ($this->temCargo('Administrador') || $this->temCargo('Coordenador')) {
            return true;
        }
        
        // Verificar se o usuário está vinculado à sala
        return $this->salas()->where('sala_id', $salaId)->exists();
    }

    /**
     * Verifica se o usuário é admin ou coordenador
     */
    public function isAdminOrCoordinator(): bool
    {
        return $this->temCargo('Administrador') || $this->temCargo('Coordenador') || $this->isCoordenador();
    }

    /**
     * Verifica se o usuário é professor (usando sistema flexível)
     */
    public function isProfessor(): bool
    {
        return $this->cargos()->where(function($query) {
            $query->where('tipo_cargo', 'professor')
                  ->orWhere('nome', 'like', '%professor%');
        })->where('ativo', true)->exists();
    }

    /**
     * Verifica se o usuário é coordenador (usando sistema flexível)
     */
    public function isCoordenador(): bool
    {
        return $this->cargos()->where(function($query) {
            $query->where('tipo_cargo', 'coordenador')
                  ->orWhere('nome', 'like', '%coordenador%');
        })->where('ativo', true)->exists();
    }

    /**
     * Verifica se o usuário é administrador (usando sistema flexível)
     */
    public function isAdministrador(): bool
    {
        return $this->cargos()->where(function($query) {
            $query->where('tipo_cargo', 'administrador')
                  ->orWhere('nome', 'like', '%administrador%');
        })->where('ativo', true)->exists();
    }

    /**
     * Verifica se o usuário possui um papel/cargo específico (alias para temCargo)
     */
    public function hasRole(string $role): bool
    {
        // Mapear nomes em inglês para português
        $roleMap = [
            'admin' => 'Administrador de Escola',
            'administrador' => 'Administrador de Escola',
            'coordenador' => 'Coordenador',
            'professor' => 'Professor',
            'secretario' => 'Secretário',
            'funcionario' => 'Funcionário',
            'responsavel' => 'Responsável', // Para pais/responsáveis
            'superadmin' => 'Super Administrador',
            'suporte' => 'Suporte Técnico',
            'analista' => 'Analista de Dados'
        ];
        
        $cargoNome = $roleMap[$role] ?? $role;
        return $this->temCargo($cargoNome);
    }
    
    /**
     * Relacionamento com disciplinas
     */
    public function disciplinas(): BelongsToMany
    {
        return $this->belongsToMany(Disciplina::class, 'user_disciplinas', 'user_id', 'disciplina_id');
    }

    /**
     * Relacionamento com funcionario
     */
    public function funcionario()
    {
        return $this->hasOne(Funcionario::class);
    }



    /**
     * Relacionamento many-to-many com escolas
     */
    public function escolas(): BelongsToMany
    {
        return $this->belongsToMany(Escola::class, 'user_escola')
                    ->withPivot('is_current')
                    ->withTimestamps();
    }

    /**
     * Obtém a escola atual do usuário
     */
    public function escolaAtual()
    {
        return $this->escolas()->wherePivot('is_current', true)->first() ?? $this->escola;
    }

    /**
     * Verifica se o usuário pode acessar uma escola específica
     */
    public function podeAcessarEscola(int $escolaId): bool
    {
        // Super administradores e suporte podem acessar todas as escolas
        if ($this->isSuperAdmin() || $this->temCargo('Suporte')) {
            return true;
        }
        
        // Verificar se o usuário está associado à escola
        return $this->escolas()->where('escola_id', $escolaId)->exists() || 
               $this->escola_id == $escolaId;
    }

    /**
     * Troca a escola atual do usuário
     */
    public function trocarEscola(int $escolaId): bool
    {
        if (!$this->podeAcessarEscola($escolaId)) {
            return false;
        }
        
        // Remover escola atual
        $this->escolas()->updateExistingPivot($this->escolas()->pluck('id')->toArray(), ['is_current' => false]);
        
        // Definir nova escola atual
        if ($this->escolas()->where('escola_id', $escolaId)->exists()) {
            $this->escolas()->updateExistingPivot($escolaId, ['is_current' => true]);
        } else {
            // Se não existe na tabela pivot, adicionar
            $this->escolas()->attach($escolaId, ['is_current' => true]);
        }
        
        // Atualizar escola_id principal
        $this->update(['escola_id' => $escolaId]);
        
        return true;
    }

    /**
     * Resolve route model binding com isolamento por escola
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();
        
        $query = $this->where($field, $value);
        
        // Em rotas do corporativo, Super Admin e Suporte podem acessar qualquer usuário
        if (request()->routeIs('corporativo.*')) {
            $authUser = auth()->user();
            if ($authUser && ($authUser->isSuperAdmin() || $authUser->temCargo('Suporte'))) {
                return $query->first();
            }
        }
        
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

    /**
     * Verifica se o usu�rio � o primeiro usu�rio (dono inicial) da sua escola
     */
    
    /**
     * Verifica se o usu�rio � o primeiro usu�rio (dono inicial) da sua escola
     */
    public function isSchoolOwner(): bool
    {
        if (!$this->escola_id) {
            return false;
        }

        $firstUserId = self::where('escola_id', $this->escola_id)
            ->orderBy('id')
            ->value('id');

        return (int) $firstUserId === (int) $this->id;
    }

    /**
     * Verifica se o usu�rio � administrador escolar (por cargo ou dono inicial)
     */
    public function isSchoolAdmin(): bool
    {
        return $this->temCargo('Administrador de Escola') ||
               $this->temCargo('Administrador') ||
               $this->temCargo('Diretor') ||
               $this->isSchoolOwner();
    }

    /**
     * Relacionamento com empréstimos da biblioteca
     */
    public function emprestimos()
    {
        return $this->hasMany(Emprestimo::class, 'usuario_id');
    }

    /**
     * Relacionamento com reservas da biblioteca
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'usuario_id');
    }

    /**
     * Relacionamento com multas da biblioteca
     */
    public function multas()
    {
        return $this->hasMany(\App\Models\Multa::class, 'usuario_id');
    }
}
