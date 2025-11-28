<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = [
        'school_id',
        'student_id',
        'payer_id',
        'billing_plan_id',
        'amount_cents',
        'currency',
        'charge_method_id',
        'day_of_month',
        'status',
        'description',
        'start_at',
        'end_at',
        'discount_percent',
        'notes',
    ];

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'discount_percent' => 'integer',
        'amount_cents' => 'integer',
        'day_of_month' => 'integer',
    ];

    public function payer()
    {
        return $this->belongsTo(\App\Models\Responsavel::class, 'payer_id');
    }

    public function chargeMethod()
    {
        return $this->belongsTo(ChargeMethod::class, 'charge_method_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'subscription_id');
    }
}