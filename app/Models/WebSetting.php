<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebSetting extends Model
{
    protected $fillable = [
        'site_name', 'logo', 'favicon', 'footer_description', 
        'copyright_text', 'footer_text', 'insta_link', 'fb_link', 
        'tiktok_link', 'mail_1', 'mail_2', 'working_hour', 'headquarter_address'
    ];
}
