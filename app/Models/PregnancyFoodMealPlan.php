<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PregnancyFoodMealPlan extends Model
{
    use HasFactory;

    protected $table = 'pregnancy_food_meal_plans';

    protected $fillable = [
        'user_id',
        'food_item',
        'dietary_preference',
        'meal_plan',
    ];

    protected $casts = [
        'meal_plan' => 'array', 
    ];

    // Relation with User
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
