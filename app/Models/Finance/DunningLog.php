<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class DunningLog extends Model
{
    protected $fillable = [
        'school_id',
        'invoice_id',
        'offset_type',
        'offset_days',
        'channel',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
