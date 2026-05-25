<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- CALL OTHER SEEDERS ----
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SubscriptionPlanSeeder::class,
            UserSubscriptionSeeder::class,
            AnalyticsSettingsSeeder::class

        ]);
    }
}
