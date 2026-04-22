<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityComment extends Model
{
    // Table includes post_id, user_id, and content
    protected $fillable = ['post_id', 'user_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id'); 
    }
}
