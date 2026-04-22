<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelvicExerciseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_date',
        'duration_seconds',
        'completed',
        'skipped',
        'streak_count',
        'tip_shown',
    ];

    protected $casts = [
        'log_date' => 'date',
        'completed' => 'boolean',
        'skipped' => 'boolean',
    ];

    /**
     * Relationship: Log belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
