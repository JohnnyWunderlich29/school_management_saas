<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Escola extends Model
{
    use HasFactory;

    protected $table = 'escolas';

    protected $fillable = [
        'nome',
        'codigo',
        'cnpj',
        'razao_social',
        'email',
        'telefone',
        'celular',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'configuracoes',
        'logo',
        'descricao',
        'ativo',
        'max_usuarios',
        'max_alunos',
        'plano',
        'plan_id',
        'valor_mensalidade',
        'data_vencimento',
        'em_dia',
    ];

    protected $appends = ['users_count'];

    protected $casts = [
        'configuracoes' => 'array',
        'ativo' => 'boolean',
        'em_dia' => 'boolean',
        'data_vencimento' => 'date',
        'valor_mensalidade' => 'decimal:2'
    ];

    // Relacionamentos
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function funcionarios(): HasMany
    {
        return $this->hasMany(Funcionario::class);
    }

    public function responsaveis(): HasMany
    {
        return $this->hasMany(Responsavel::class);
    }

    public function salas(): HasMany
    {
        return $this->hasMany(Sala::class);
    }

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class);
    }

    public function modalidades(): HasMany
    {
        return $this->hasMany(ModalidadeEnsino::class);
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(GrupoEducacional::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(Turno::class);
    }

    public function disciplinas(): HasMany
    {
        return $this->hasMany(Disciplina::class);
    }

    public function planejamentos(): HasMany
    {
        return $this->hasMany(Planejamento::class);
    }

    public function presencas(): HasMany
    {
        return $this->hasMany(Presenca::class);
    }

    public function transferencias(): HasMany
    {
        return $this->hasMany(Transferencia::class);
    }

    public function escolaModules(): HasMany
    {
        return $this->hasMany(EscolaModule::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'escola_modules')
                    ->withPivot('is_active', 'monthly_price', 'contracted_at', 'expires_at', 'notes', 'settings')
                    ->withTimestamps();
    }

    public function modalidadeConfigs(): HasMany
    {
        return $this->hasMany(EscolaModalidadeConfig::class);
    }

    public function nivelConfigs(): HasMany
    {
        return $this->hasMany(EscolaNivelConfig::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function activeModules()
    {
        return $this->belongsToMany(Module::class, 'escola_modules')
                    ->wherePivot('is_active', true)
                    ->wherePivot(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->withPivot('is_active', 'monthly_price', 'contracted_at', 'expires_at', 'notes', 'settings')
                    ->withTimestamps();
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(SchoolLicense::class);
    }

    // Scopes
    public function scopeAtivas($query)
    {
        return $query->where('ativo', true);
    }

    public function scopeEmDia($query)
    {
        return $query->where('em_dia', true);
    }

    public function scopePorPlano($query, $plano)
    {
        return $query->where('plano', $plano);
    }

    // Métodos auxiliares
    public function getTotalUsuarios()
    {
        return $this->users()->count();
    }

    public function getTotalFuncionarios()
    {
        return $this->funcionarios()->count();
    }

    public function getTotalSalas()
    {
        return $this->salas()->count();
    }

    public function podeAdicionarUsuario()
    {
        return $this->getTotalUsuarios() < $this->max_usuarios;
    }

    public function getEnderecoCompleto()
    {
        $endereco = $this->endereco;
        if ($this->numero) {
            $endereco .= ', ' . $this->numero;
        }
        if ($this->complemento) {
            $endereco .= ', ' . $this->complemento;
        }
        if ($this->bairro) {
            $endereco .= ' - ' . $this->bairro;
        }
        if ($this->cidade && $this->estado) {
            $endereco .= ' - ' . $this->cidade . '/' . $this->estado;
        }
        if ($this->cep) {
            $endereco .= ' - CEP: ' . $this->cep;
        }
        return $endereco;
    }

    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }

    public function getStatusPagamento()
    {
        if (!$this->em_dia) {
            return 'inadimplente';
        }
        
        if ($this->data_vencimento && $this->data_vencimento->isPast()) {
            return 'vencido';
        }
        
        return 'em_dia';
    }

    /**
     * Verifica se a escola tem um módulo específico ativo
     */
    public function hasActiveModule(string $moduleName): bool
    {
        return $this->escolaModules()
                    ->whereHas('module', function($query) use ($moduleName) {
                        $query->where('name', $moduleName);
                    })
                    ->where('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->exists();
    }

    // Eventos do modelo
    protected static function booted()
    {
        static::created(function (Escola $escola) {
            // Se não foi informado código, gerar automaticamente ao final da inclusão
            if (empty($escola->codigo)) {
                $codigo = self::gerarCodigoApartirDoId($escola->id);
                // Evitar problemas com mass assignment e eventos
                $escola->forceFill(['codigo' => $codigo])->saveQuietly();
            }
        });
    }

    // Geração de código única e legível baseada no ID
    private static function gerarCodigoApartirDoId(int $id): string
    {
        // Formato: ESC-000001, garantindo unicidade por ID
        return 'ESC-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calcula o valor total dos módulos ativos
     */
    public function getTotalModulesPrice(): float
    {
        return $this->escolaModules()
                    ->where('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->sum('monthly_price');
    }

    /**
     * Retorna o valor total mensal (mensalidade + módulos)
     */
    public function getTotalMonthlyValue(): float
    {
        return $this->valor_mensalidade + $this->getTotalModulesPrice();
    }

    /**
     * Contrata um módulo para a escola
     */
    public function contractModule(Module $module, float $monthlyPrice = null, int $contractedBy = null): EscolaModule
    {
        $monthlyPrice = $monthlyPrice ?? $module->price;
        
        return $this->escolaModules()->create([
            'module_id' => $module->id,
            'is_active' => true,
            'monthly_price' => $monthlyPrice,
            'contracted_at' => now(),
            'contracted_by' => $contractedBy ?? auth()->id(),
        ]);
    }

    /**
     * Cancela um módulo da escola
     */
    public function cancelModule(Module $module): bool
    {
        return $this->escolaModules()
                    ->where('module_id', $module->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
    }

    /**
     * Obtém o preço do plano da escola
     */
    public function getPlanoPreco(): float
    {
        if ($this->plan) {
            return (float) $this->plan->price;
        }
        return match($this->plano) {
            'basico' => 99.90,
            'premium' => 199.90,
            'enterprise' => 399.90,
            'trial' => 0.00,
            default => 0.00
        };
    }

    /**
     * Obtém informações detalhadas do plano
     */
    public function getPlanoInfo(): array
    {
        if ($this->plan) {
            return [
                'nome' => $this->plan->name,
                'preco' => (float) $this->plan->price,
                'descricao' => $this->plan->description,
                'max_usuarios' => $this->plan->max_users,
                'max_alunos' => $this->plan->max_students,
                'is_trial' => $this->plan->is_trial,
                'trial_days' => $this->plan->trial_days,
            ];
        }
        return match($this->plano) {
            'trial' => [
                'nome' => 'Trial',
                'preco' => 0.00,
                'descricao' => 'Plano de testes gratuito por 7 dias',
                'max_usuarios' => 15,
                'max_alunos' => 50
            ],
            'basico' => [
                'nome' => 'Básico',
                'preco' => 99.90,
                'descricao' => 'Plano básico para escolas pequenas',
                'max_usuarios' => 50,
                'max_alunos' => 200
            ],
            'premium' => [
                'nome' => 'Premium',
                'preco' => 199.90,
                'descricao' => 'Plano premium para escolas médias',
                'max_usuarios' => 150,
                'max_alunos' => 800
            ],
            'enterprise' => [
                'nome' => 'Enterprise',
                'preco' => 399.90,
                'descricao' => 'Plano enterprise para escolas grandes',
                'max_usuarios' => 500,
                'max_alunos' => 2000
            ],
            default => [
                'nome' => 'Não definido',
                'preco' => 0.00,
                'descricao' => 'Plano não definido',
                'max_usuarios' => 0,
                'max_alunos' => 0
            ]
        };
    }
}