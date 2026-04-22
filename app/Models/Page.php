<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'meta_title', 'meta_description','meta_keywords','meta_image','is_active','is_indexable'];
}
