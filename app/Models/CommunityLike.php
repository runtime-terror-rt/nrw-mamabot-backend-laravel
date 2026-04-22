<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityLike extends Model
{
    protected $fillable = ['post_id', 'user_id', 'liked_at'];
    public $timestamps = false; // Using custom liked_at column

    protected $casts = ['liked_at' => 'datetime'];
}
