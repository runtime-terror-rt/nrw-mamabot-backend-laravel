<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title', 'slug', 'category_id', 'phase_type','delivery_type', 'week', 
        'short_description', 'long_description', 'author_id', 
        'read_duration', 'thumb_img', 'main_img', 'status', 
        'feature_status', 'response_time'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }
}
