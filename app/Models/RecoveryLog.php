<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoveryLog extends Model
{
    use HasFactory;

    protected $table = 'recovery_logs';

    protected $fillable = [
        'user_id',
        'pain_range',
        'pain_type',
        'bleeding_today',
        'clots_present',
        'energy_level',
        'mood',
        'notes',
        'log_date',
    ];

    // If you want to cast JSON columns automatically
    protected $casts = [
        'pain_type' => 'array',
        'mood' => 'array',
        'clots_present' => 'boolean',
        'log_date' => 'date',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
