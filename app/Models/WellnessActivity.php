<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WellnessActivity extends Model
{
    protected $fillable = ['title', 'short_description', 'phase_type','trimester', 'duration', 'image', 'video_url', 'status'];
}