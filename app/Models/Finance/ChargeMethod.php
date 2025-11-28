<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class ChargeMethod extends Model
{
    protected $table = 'charge_methods';

    protected $fillable = [
        'school_id',
        'gateway_alias',
        'method',
        'penalty_policy',
        'active',
    ];

    protected $casts = [
        'penalty_policy' => 'array',
        'active' => 'boolean',
    ];
}