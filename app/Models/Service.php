<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['thumbnail_img', 'main_img', 'title', 'slug', 'description', 'btn_text', 'btn_link', 'is_active'];
}
