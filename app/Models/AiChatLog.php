<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatLog extends Model
{
    protected $fillable = [
        'user_id',
        'chat_id',

        // context
        'mode',
        'pregnancy_week',
        'postpartum_day',
        'delivery_type',
        'language',
        'country',
        'dietary_preferences',

        // AI behavior
        'tone_of_ai',
        'support_type',

        // messages
        'user_message',
        'ai_response',

        // AI meta
        'is_emergency',
        'quota_exceeded',
        'used_today',
        'daily_query_limit',

        // attachments
        'image_path',
        'file_path',
    ];

    protected $casts = [
        'is_emergency' => 'boolean',
        'quota_exceeded' => 'boolean',
    ];

    /**
     * Relation with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? asset('storage/' . $this->image_path)
            : null;
    }

}
