<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaperLog extends Model
{
    protected $fillable = [
        'user_id',
        'log_date',
        'diaper_type',
        'tip',
        'delivery_type',
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
