<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementRestriction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_date',
        'avoided_heavy_lifting',
        'avoided_sudden_bending',
        'supported_abdomen',
        'rested_when_needed',
        'notes',
        'tip',
    ];

    protected $casts = [
        'log_date' => 'date',
        'avoided_heavy_lifting' => 'boolean',
        'avoided_sudden_bending' => 'boolean',
        'supported_abdomen' => 'boolean',
        'rested_when_needed' => 'boolean',
    ];

    /**
     * Relationship: MovementRestriction belongs to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
