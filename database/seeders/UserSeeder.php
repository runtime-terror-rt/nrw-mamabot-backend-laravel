<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ---- CREATE ADMIN USER ---
        $admin = User::updateOrCreate(
            [
                'email' => 'admin@gmail.com',
                'phone' => '01700000001'
            ],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '01700000001', // ✅ matches DB column
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_first_time' => true,
                'accepted_terms' => true,
                'consent_health_data' => true,
                'newsletter_opt_in' => false,
                'accepted_withdrawal_waiver' => true,
                'accepted_auto_renewal' => true,
                'is_blocked' => false,
                'chat_id' => Str::uuid()->toString(),
            ]
        );
        $admin->syncRoles(['Admin']);

        // ---- CREATE DEFAULT USER ---
        $user = User::withTrashed()->updateOrCreate(
            [
                'email' => 'user@gmail.com',
                'phone' => '01700000002',
            ],
            [
                'first_name' => 'User',
                'last_name' => 'Demo',
                'phone' => '01700000002', // ✅ corrected
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_first_time' => true,
                'accepted_terms' => true,
                'consent_health_data' => false,
                'newsletter_opt_in' => true,
                'accepted_withdrawal_waiver' => false,
                'accepted_auto_renewal' => false,
                'is_blocked' => false,
                'chat_id' => Str::uuid()->toString(),
            ]
        );

        // If the record was soft-deleted, restore
        //
        if ($user->trashed()) {
            $user->restore();
        }
        $user->syncRoles(['User']);


    }
}

