<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'invoice_id',
        'amount_paid_cents',
        'paid_at',
        'method',
        'gateway_fee_cents',
        'net_amount_cents',
        'currency',
        'gateway_payment_id',
        'status',
        'settled_at',
        'settlement_ref',
        'reconciliation_status',
    ];

    protected $casts = [
        'amount_paid_cents' => 'integer',
        'gateway_fee_cents' => 'integer',
        'net_amount_cents' => 'integer',
        'paid_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    /**
     * Fatura relacionada ao pagamento
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}