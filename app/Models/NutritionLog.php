<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionLog extends Model
{
    use HasFactory;

    protected $table = 'nutrition_logs';

    protected $fillable = [
        'user_id',
        'log_date',
        'notes',
        'tip',
    ];

    protected $dates = ['log_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
