<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PregnancyFood extends Model
{
    protected $table = 'pregnancy_foods';

    protected $fillable = [
        'pregnancy_week',
        'dietary_preference',
        'foods',
    ];

    protected $casts = [
        'foods' => 'array',
    ];
}
