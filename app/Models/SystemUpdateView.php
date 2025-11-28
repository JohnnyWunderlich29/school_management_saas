<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemUpdateView extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_update_id',
        'user_id',
        'viewed_at',
    ];

    public function systemUpdate()
    {
        return $this->belongsTo(SystemUpdate::class, 'system_update_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}