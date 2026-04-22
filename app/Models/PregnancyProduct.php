<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PregnancyProduct extends Model
{
    protected $fillable = [
    'user_id',
    'pregnancy_week',
    'phase',
    'products',
    'mode',
    ];

    protected $casts = [
        'products' => 'array',
    ];

    // relation with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
