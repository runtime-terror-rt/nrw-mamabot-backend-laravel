<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PainMovementLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_date',
        'pain_level',
        'discomfort_areas',
        'movement_status',
        'tip_shown',
        'notes',
        'delivery_type',  
    ];

    protected $casts = [
        'log_date' => 'date',
        'discomfort_areas' => 'array',
    ];

    /**
     * Relationship: log belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
