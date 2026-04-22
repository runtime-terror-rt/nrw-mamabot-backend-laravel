<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['image', 'description', 'author_name', 'author_title', 'address'];
}
