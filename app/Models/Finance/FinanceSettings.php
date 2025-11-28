<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class FinanceSettings extends Model
{
    protected $table = 'finance_settings';

    protected $fillable = [
        'school_id',
        'default_gateway_alias',
        'repasse_bank_account',
        'penalty_policy',
        'dunning_schedule',
        'allowed_payment_methods',
        'invoice_numbering',
        'legal_texts',
        'timezone',
        'currency',
    ];

    protected $casts = [
        'repasse_bank_account' => 'array',
        'penalty_policy' => 'array',
        'dunning_schedule' => 'array',
        'allowed_payment_methods' => 'array',
        'invoice_numbering' => 'array',
        'legal_texts' => 'array',
    ];
}