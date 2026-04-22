<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'name',
        'specialty',
        'image',
        'is_active',
    ];

    public function scopeAvailable($query)
    {
            $now = now();

        return $query->whereDoesntHave('qaSessions', function ($q) use ($now) {
            $q->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now);
        });
    }

    public function qaSessions()
    {
        return $this->hasMany(QaSession::class);
    }
}
