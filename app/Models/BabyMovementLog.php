<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BabyMovementLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_date',
        'start_time',
        'end_time',
        'duration_seconds',
        'kick_count',
        'movement_status',
        'note',
        'pregnancy_week',
    ];

    protected $casts = [
    'log_date'   => 'date',
    
];


    /**
     * Relation: belongs to user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
