<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Profile;
use Carbon\Carbon;

class UpdatePregnancyData extends Command
{
    protected $signature = 'pregnancy:update-data';
    protected $description = 'Updates pregnancy weeks every 7 days and postpartum days daily';

    public function handle()
    {
        $pregnantUsers = Profile::where('pregnancy_status', 'pregnancy')
            ->whereNotNull('current_week')->get();
        
        foreach ($pregnantUsers as $profile) {
            $daysDiff = Carbon::parse($profile->created_at)->diffInDays(now());
            if ($daysDiff > 0 && $daysDiff % 7 == 0) {
                $profile->increment('current_week');
            }
        }

        Profile::where('pregnancy_status', 'postpartum')
            ->increment('postpartum_day');

        $this->info('Pregnancy/Postpartum data updated successfully!');
    }
}