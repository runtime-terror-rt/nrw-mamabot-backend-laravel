<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CommunityGroup extends Model
{

    protected $fillable = ['name', 'slug', 'description', 'stage', 'member_count', 'is_active', 'image'];

    //  when set name, auto-generate slug
    protected static function boot() {
        parent::boot();
        static::creating(function ($group) {
            $group->slug = Str::slug($group->name);
        });
    }
    public function users() 
    {
        return $this->belongsToMany(User::class, 'community_group_by_users', 'group_id', 'user_id');
    }

}
