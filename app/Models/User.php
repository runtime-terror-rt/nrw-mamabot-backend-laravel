<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

use Stripe\Subscription;

use App\Models\PregnancyFoodMealPlan;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $guard_name = 'sanctum';

    protected $guarded = [];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_seen' => 'datetime',
        'is_blocked' => 'boolean',
        'is_first_time' => 'boolean',
    ];


    // App\Models\User.php
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }


    public function subscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where('is_active', 1);
    }


    public function pregnancyFoods()
    {
        return $this->hasMany(PregnancyFood::class);
    }


    public function pregnancyFoodWeeklyLogs()
    {
        return $this->hasMany(PregnancyFoodWeeklyLog::class);
    }


    public function pregnancyFoodRecipes()
    {
        return $this->hasMany(PregnancyFoodRecipe::class);
    }


    public function pregnancyFoodMealPlans()
    {
        return $this->hasMany(PregnancyFoodMealPlan::class);
    }

    public function pregnancyProducts()
    {
        return $this->hasMany(PregnancyProduct::class);
    }


    public function dailyInsights()
    {
        return $this->hasMany(DailyInsight::class);
    }


    public function postpartumDailyTips()
    {
        return $this->hasMany(PostpartumDailyTip::class);
    }



    public function aiChatLogs()
    {
        return $this->hasMany(AiChatLog::class);
    }

    public function qaRegistrations()
    {
        return $this->hasMany('App\Models\QaRegistration');
    }

    public function groups()
    {
        return $this->belongsToMany(CommunityGroup::class, 'community_group_by_users', 'user_id', 'group_id');
    }

    public function communityPosts()
    {
        return $this->hasMany(CommunityPost::class);
    }
}
