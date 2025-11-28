<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class GatewayCustomer extends Model
{
    protected $table = 'gateway_customers';

    protected $fillable = [
        'school_id',
        'payer_id',
        'gateway_alias',
        'external_customer_id',
        'status',
    ];
}