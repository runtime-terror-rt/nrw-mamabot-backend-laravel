<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'first_name', 
        'last_name', 
        'email', 
        'phone_number', 
        'message', 
        'attachment',
        'agreed_to_privacy', 
        'submitted_at'
    ];

    public $timestamps = false; 

    protected $casts = [
        'agreed_to_privacy' => 'boolean',
        'submitted_at'      => 'datetime'
    ];
}
