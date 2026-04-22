<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedItem extends Model
{
    protected $fillable = ['user_id', 'savable_id', 'savable_type'];

    public function savable()
    {
        return $this->morphTo();
    }
}