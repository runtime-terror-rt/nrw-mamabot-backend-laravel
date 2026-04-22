<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostpartumDailyTip extends Model
{
    protected $fillable = [
        'user_id',
        'day',
        'delivery_type',
        'language',
        'tip',
        'description',
    ];

    // relation with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
