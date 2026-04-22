<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MotherWellnessLog extends Model
{
    use HasFactory;

    protected $table = 'mother_wellness_logs';

    // UUID primary key
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'log_date',
        'mood',
        'energy_level',
        'provider_override',
        'override_reason',
    ];

    // Auto-generate UUID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // Relationship: each log belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
