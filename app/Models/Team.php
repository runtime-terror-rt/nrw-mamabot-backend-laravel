<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'thumbnail_img', 'name', 'title', 'fb_link', 'twitter_link', 'linkedin_link', 'long_description', 'status'
    ];
}
