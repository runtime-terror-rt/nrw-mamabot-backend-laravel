<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class PregnancyFoodWeeklyLog extends Model
{
    protected $table = 'pregnancy_food_weekly_logs';

    protected $fillable = [
        'user_id',
        'pregnancy_week',
        'dietary_preference',
        'food_items',
        'week',
        'daily_plan',
    ];

    protected $casts = [
        'food_items' => 'array',
        'daily_plan' => 'array',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
