<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityPostReport extends Model
{
    protected $fillable = ['post_id', 'user_id', 'comment', 'report_cause'];
}
