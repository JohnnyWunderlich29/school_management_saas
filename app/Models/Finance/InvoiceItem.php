<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'subscription_id',
        'description',
        'amount_cents',
        'qty',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'qty' => 'integer',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
