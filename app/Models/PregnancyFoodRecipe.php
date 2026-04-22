<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PregnancyFoodRecipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'food_item',
        'dietary_preference',
        'recipes',
    ];

    protected $casts = [
        'recipes' => 'array',
    ];

    // Relationship: user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
