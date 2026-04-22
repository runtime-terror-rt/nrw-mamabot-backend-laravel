<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyInsight extends Model
{
    protected $fillable = [
        'user_id',
        'mode',
        'language',
        'pregnancy_week',
        'postpartum_day',
        'delivery_type',
        'insight',
    ];

    // Relation with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
