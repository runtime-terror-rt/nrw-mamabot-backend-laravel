<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateProductSave extends Model
{
    protected $fillable = [
        'title',
        'category',
        'affiliate_link',
        'reason',
        'image_url',
    ];
}
