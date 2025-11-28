<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardPreference extends Model
{
    protected $table = 'dashboard_preferences';

    protected $fillable = [
        'user_id',
        'school_id',
        'state',
    ];

    protected $casts = [
        'state' => 'array',
    ];
}