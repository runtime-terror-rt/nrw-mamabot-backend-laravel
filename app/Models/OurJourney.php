<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OurJourney extends Model
{
    protected $fillable = [
        'count', 'title', 'description', 'image_url_1', 
        'image_url_2', 'locale', 'subtitle_1', 'subtitle_2'
    ];
}
