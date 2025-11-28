<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueryFavorite extends Model
{
    protected $fillable = [
        'user_id',
        'escola_id',
        'name',
        'query',
        'description',
        'tags'
    ];
    
    protected $casts = [
        'tags' => 'array'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }
}
