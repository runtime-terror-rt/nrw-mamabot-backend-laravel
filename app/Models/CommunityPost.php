<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityPost extends Model
{
    protected $fillable = [
        'user_id', 'group_id', 'title', 'slug', 'content', 
        'role_label', 'week', 'image_urls', 'posted_at', 'is_reported', 'moderation_report_status'
    ];

    // Automatically cast JSON string to PHP array and dates to objects
    protected $casts = [
        'image_urls' => 'array',
        'posted_at'  => 'datetime',
        'week'       => 'integer',
    ];

    /**
     * Relationship: User who created the post
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Likes on this post
     */
    public function likes(): HasMany
    {
        return $this->hasMany(CommunityLike::class, 'post_id');
    }

    /**
     * Relationship: Comments on this post
     */
    public function comments(): HasMany
    {
        return $this->hasMany(CommunityComment::class, 'post_id');
    }
    /**
     * Relationship: Shares of this post
     */
    public function shares(): HasMany
    {
        return $this->hasMany(CommunityShare::class, 'post_id');
    }       

    public function reports()
    {
        return $this->hasMany(CommunityPostReport::class, 'post_id');
    }

    Public function group(): BelongsTo
    {
        return $this->belongsTo(CommunityGroup::class, 'group_id');
    }
}