<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $guarded = [];

    // Get the plan associated with this subscription
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    // Get payments linked to this subscription
    public function payments()
    {
        return $this->hasMany(UserPayment::class, 'subscription_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
