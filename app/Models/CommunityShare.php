<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityShare extends Model
{
    protected $fillable = ['post_id', 'user_id', 'shared_at']; 
    
    public $timestamps = false; 

    protected $casts = [
        'shared_at' => 'datetime'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); 
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'post_id'); 
    }
}