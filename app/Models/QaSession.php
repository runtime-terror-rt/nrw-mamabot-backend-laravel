<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QaSession extends Model 
{
    protected $fillable = ['doctor_id', 'topic', 'start_time', 'end_time', 'meeting_link'];

    /**
     * Ensure dates are treated as Carbon instances for easy comparison.
     */
    protected $casts = [
        'start_time' => 'datetime', 
        'end_time' => 'datetime',
    ];

    /**
     * Relationship: A session belongs to a doctor.
     */
    public function doctor() 
    { 
        return $this->belongsTo(Doctor::class);
    }

/**
     * Relationship: A session has many user registrations.
     */
    public function qaRegistrations()
    {
        return $this->hasMany('App\Models\QaRegistration'); 
    }
}
