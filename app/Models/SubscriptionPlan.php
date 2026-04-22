<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'price', 'billing_cycle', 'plan_type', 'limit', 'description','features', 'is_active'];

    protected $casts = [
        'features' => 'array',
    ];
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }
}
