<?php

namespace Database\Seeders;

use App\Models\AnalyticsSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class AnalyticsSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Google Analytics
            AnalyticsSetting::updateOrCreate(
                ['tool' => 'google_analytics'], // match condition
                [
                    'tracking_id' => 'G-XXXXXXXXXX', // replace with real GA4 ID
                    'enabled' => true,
                ]
            );

            // Facebook Pixel
            AnalyticsSetting::updateOrCreate(
                ['tool' => 'facebook_pixel'], // match condition
                [
                    'tracking_id' => '1234567890', // replace with real Pixel ID
                    'enabled' => false,
                ]
            );

            $this->command->info('Analytics settings seeded successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to seed analytics settings: ' . $e->getMessage());
            $this->command->error('Error seeding analytics settings. Check logs for details.');
        }
    }
}
