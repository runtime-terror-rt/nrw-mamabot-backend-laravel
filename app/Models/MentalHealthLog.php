<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentalHealthLog extends Model
{
    use HasFactory;

    protected $table = 'mental_health_logs';

    protected $fillable = [
        'user_id',
        'log_date',
        'mood',
        'energy_level',
        'sleep_quality',
        'tip',
    ];

    protected $dates = ['log_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
