<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'max_users',
        'max_students',
        'is_active',
        'is_trial',
        'trial_days',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_trial' => 'boolean',
    ];

    /**
     * Módulos vinculados ao plano (pivot: plan_modules, coluna extra: included)
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'plan_modules')
            ->withPivot('included')
            ->withTimestamps();
    }

    /**
     * Módulos incluídos no plano (incluídos por padrão)
     */
    public function includedModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('included', true);
    }

    /**
     * Módulos opcionais do plano (não incluídos por padrão)
     */
    public function optionalModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('included', false);
    }
}