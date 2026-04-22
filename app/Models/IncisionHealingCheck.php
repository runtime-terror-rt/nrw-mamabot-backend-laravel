<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncisionHealingCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_date',
        'redness',
        'swelling',
        'warmth',
        'tenderness',
        'pain_score',
        'sensations',
        'chills_fever',
        'discharge_type',
        'healing_status',
        'guidance',
    ];

    protected $casts = [
        'log_date' => 'date',
        'swelling' => 'boolean',
        'warmth' => 'boolean',
        'tenderness' => 'boolean',
        'chills_fever' => 'boolean',
        'sensations' => 'array',
    ];

    /**
     * Relationship: log belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
