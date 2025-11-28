<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'icon',
        'color',
        'price',
        'is_active',
        'is_core',
        'features',
        'category',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'features' => 'array',
        'price' => 'decimal:2'
    ];

    /**
     * Relacionamento com as contratações de escolas
     */
    public function escolaModules(): HasMany
    {
        return $this->hasMany(EscolaModule::class);
    }

    /**
     * Escolas que contrataram este módulo
     */
    public function escolas()
    {
        return $this->belongsToMany(Escola::class, 'escola_modules')
                    ->withPivot('is_active', 'monthly_price', 'contracted_at', 'expires_at', 'notes', 'settings')
                    ->withTimestamps();
    }

    /**
     * Planos que incluem ou oferecem este módulo (pivot: plan_modules)
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_modules')
            ->withPivot('included')
            ->withTimestamps();
    }

    /**
     * Scope para módulos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para módulos por categoria
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope para ordenação
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('display_name');
    }

    /**
     * Verifica se o módulo está contratado por uma escola específica
     */
    public function isContractedBy(Escola $escola): bool
    {
        return $this->escolaModules()
                    ->where('escola_id', $escola->id)
                    ->where('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->exists();
    }

    /**
     * Retorna o preço formatado
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Retorna as categorias disponíveis
     */
    public static function getCategories(): array
    {
        return [
            'academic' => 'Acadêmico',
            'administrative' => 'Administrativo',
            'communication' => 'Comunicação',
            'financial' => 'Financeiro',
            'general' => 'Geral'
        ];
    }

    /**
     * Retorna a descrição da categoria
     */
    public function getCategoryDisplayAttribute(): string
    {
        $categories = self::getCategories();
        return $categories[$this->category] ?? 'Geral';
    }
}