<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SleepTracking extends Model
{
    protected $table = 'sleep_trackings';

    protected $fillable = [
        'user_id',
        'log_date',          // ✅ add this if you use log_date in migration
        'start_time',
        'end_time',
        'duration_minutes',
        'sleep_type',
        'sleep_quality',
        'notes',
        'delivery_type',
    ];

    /**
     * Relationship: SleepTracking belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
