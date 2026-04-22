<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    // Defined according to your schema
    protected $fillable = [
        'first_name', 
        'email', 
        'subscribed_at', 
        'is_active', 
        'locale', 
        'source'
    ];

    // Disabling default timestamps as we use custom subscribed_at
    public $timestamps = false; 

    protected $casts = [
        'subscribed_at' => 'datetime',
        'is_active'     => 'boolean'
    ];
}
