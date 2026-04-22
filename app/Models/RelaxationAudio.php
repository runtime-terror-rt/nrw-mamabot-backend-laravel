<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelaxationAudio extends Model
{
    protected $table = 'relaxation_audio';

    protected $fillable = [
        'title',
        'audio_url',
        'is_active',
    ];
}
