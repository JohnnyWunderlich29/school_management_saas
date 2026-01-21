<?php

namespace App\Models\Finance;

use App\Models\Escola;
use Illuminate\Database\Eloquent\Model;

class BillingAutomation extends Model
{
    protected $table = 'billing_automations';

    protected $fillable = [
        'school_id',
        'name',
        'days_advance',
        'consolidate_default',
        'active',
    ];

    protected $casts = [
        'days_advance' => 'integer',
        'consolidate_default' => 'boolean',
        'active' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(Escola::class, 'school_id');
    }
}
