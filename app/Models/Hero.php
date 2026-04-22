<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    protected $fillable = ['image', 'title', 'subtitle', 'description', 'btn_text', 'btn_link'];
}
