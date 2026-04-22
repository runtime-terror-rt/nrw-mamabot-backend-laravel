<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShareLog extends Model
{
    protected $fillable = ['user_id', 'post_id', 'platform', 'group_id', 'shared_at'];
    
    public $timestamps = false;

    protected $casts = [
        'shared_at' => 'datetime'
    ];
}
