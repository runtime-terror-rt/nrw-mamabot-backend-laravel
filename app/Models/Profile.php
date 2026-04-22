<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'address', 'language', 'pregnancy_status', 'due_date', 
        'current_week', 'baby_nickname', 'doctor_name', 'hospital_name', 
        'isKickRemind', 'isHydrationGoal', 'isWeightTrack', 'AI_tone', 
        'support_type', 'product_interest', 'dietary_preferences', 'two_factor_auth',
        'delivery_type','postpartum_day', 'pregnancy_document', 'image'
    ];
    
    // Boolean value gulo ke thikmoto handle korar jonno casting
    protected $casts = [
        'isKickRemind' => 'boolean',
        'isHydrationGoal' => 'boolean',
        'isWeightTrack' => 'boolean',
        'two_factor_auth' => 'boolean',
        'due_date' => 'date',
    ];

    // User er sathe relationship
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
