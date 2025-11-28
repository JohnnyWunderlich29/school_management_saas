<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class BillingPlan extends Model
{
    protected $table = 'billing_plans';

    protected $fillable = [
        'school_id',
        'name',
        'amount_cents',
        'currency',
        'gateway_alias',
        'periodicity',
        'day_of_month',
        'grace_days',
        'penalty_policy',
        'allowed_payment_methods',
        'penalty_policy_by_method',
        'active',
    ];

    protected $casts = [
        'penalty_policy' => 'array',
        'allowed_payment_methods' => 'array',
        'penalty_policy_by_method' => 'array',
        'active' => 'boolean',
    ];
}