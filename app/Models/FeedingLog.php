<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedingLog extends Model
{
    protected $table = 'feeding_logs';

    protected $fillable = [
        'user_id',
        'log_date',
        'feeding_time',
        'feeding_method',
        'duration_left',
        'duration_right',
        'latch_quality',
        'delivery_type',
    ];

    // If you want to use Carbon dates
    protected $dates = [
        'log_date',
        'feeding_time',
        'created_at',
        'updated_at',
    ];

    // Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
