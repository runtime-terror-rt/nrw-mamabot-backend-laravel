<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSubscriptionSeeder extends Seeder
{
    public function run()
    {
        // Create or update subscription for user_id = 2, plan_id = 1
        $subscription = \App\Models\UserSubscription::updateOrCreate(
            [
                'user_id' => 2,
                'plan_id' => 1,
            ],
            [
                'stripe_subscription_id' => 'sub_test_12345',
                'started_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addDays(30),
                'cancelled_at' => null,
                'is_active' => true,
                'auto_renew' => true,
                'waived_withdrawal_right' => false,
                'status' => 'active',
            ]
        );

        // Create or update payment linked to subscription
        \App\Models\UserPayment::updateOrCreate(
            [
                'invoice_number' => 'INV-1001', // unique key
            ],
            [
                'user_id' => 2,
                'subscription_id' => $subscription->id,
                'payment_provider' => 'Stripe',
                'last_four_digit' => '4242',
                'amount' => 49.99,
                'currency' => 'USD',
                'stripe_payment_id' => 'pi_test_67890',
                'status' => 'paid',
                'issued_at' => Carbon::now(),
                'download_url' => 'https://example.com/invoices/INV-1001.pdf',
            ]
        );
    }

}
