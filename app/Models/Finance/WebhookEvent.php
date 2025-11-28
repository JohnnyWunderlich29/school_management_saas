<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $table = 'webhook_events';

    protected $fillable = [
        'gateway_alias',
        'event_type',
        'external_id',
        'signature_valid',
        'payload',
        'processed',
        'attempts',
        'last_error',
    ];

    protected $casts = [
        'signature_valid' => 'boolean',
        'processed' => 'boolean',
        'attempts' => 'integer',
    ];
}