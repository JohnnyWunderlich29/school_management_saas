<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueryHistory extends Model
{
    protected $table = 'query_history';
    
    protected $fillable = [
        'user_id',
        'escola_id',
        'query',
        'description',
        'execution_time_ms',
        'rows_returned',
        'has_error',
        'error_message'
    ];
    
    protected $casts = [
        'has_error' => 'boolean',
        'execution_time_ms' => 'integer',
        'rows_returned' => 'integer'
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
