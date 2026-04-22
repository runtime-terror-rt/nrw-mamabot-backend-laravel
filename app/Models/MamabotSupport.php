<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MamabotSupport extends Model
{
    protected $fillable = ['image', 'title', 'subtitle', 'description', 'btn_text', 'btn_link'];
}
