<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'school_id',
        'subscription_id',
        'number',
        'due_date',
        'total_cents',
        'currency',
        'status',
        'paid_at',
        'gateway_alias',
        'gateway_status',
        'gateway_error_code',
        'gateway_error',
        'charge_id',
        'boleto_url',
        'barcode',
        'linha_digitavel',
        'pix_qr_code',
        'pix_code',
    ];

    protected $casts = [
        'due_date' => 'date',
        'total_cents' => 'integer',
        'paid_at' => 'datetime',
    ];
}