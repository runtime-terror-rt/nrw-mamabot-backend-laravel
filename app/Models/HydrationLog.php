<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HydrationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_date',
        'glass_count',
        'duration_seconds',
    ];

    // If you want to automatically cast log_date to date type
    protected $casts = [
        'log_date' => 'date',
    ];

    // Relationship: User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
