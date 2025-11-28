<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'school',
        'role',
        'message',
        'status',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'origin_url',
        'consent',
        'contacted_at',
    ];

    protected $casts = [
        'consent' => 'boolean',
        'contacted_at' => 'datetime',
    ];

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}

