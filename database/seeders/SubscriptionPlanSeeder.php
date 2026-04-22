<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'name' => 'Basic Plan',
                'price' => 10,
                'billing_cycle' => 'monthly',
                'plan_type' => 'premium',
                'limit' => '20',
                'description' => 'Perfect for first-time users who want to explore Mamabot.',
                'features' => [
                    '20 AI chatbot questions per day',
                    'Personalized answers based on your pregnancy stage',
                    'Access to selected blog articles',
                    'Weekly newsletter with tips & updates',
                    'GDPR-compliant data control'
                ]

            ],
            [
                'name' => 'Business Plan',
                'price' => 20,
                'billing_cycle' => 'monthly',
                'plan_type' => 'premium',
                'limit' => 'unlimited',
                'description' => 'Unlimited AI chats, personalization, milestone tracking, and full community access.',
                'features' => [
                    'Unlimited AI chats (GPT-4)',
                    'Extended memory & personalization',
                    'Pregnancy & baby milestone tracking',
                    'Full community access (post, reply, badges)',
                    'Smart product recommendations',
                    'Save & export chat history'
                ]
            ],
            [
                'name' => 'Enterprise Plan',
                'price' => 40,
                'billing_cycle' => 'monthly',
                'plan_type' => 'premium',
                'limit' => 'unlimited',
                'description' => 'Full motherhood companion with yearly wellness report and lifetime milestone tracker.',
                'features' => [
                    'Everything in Premium Monthly',
                    'Exclusive early access to new features',
                    'Priority community badges',
                    'Yearly wellness summary report (PDF)',
                    'Lifetime access to milestone tracker'
                ]
            ] ,
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $plan['name']], // Unique identifier
                array_merge($plan, ['is_active' => true])
            );
        }
    }
}

