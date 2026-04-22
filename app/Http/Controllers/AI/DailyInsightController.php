<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\DailyInsight;

class DailyInsightController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();
            $records = $user->dailyInsights()->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $records
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


   public function store(Request $request)
{
    try {
        $user = auth()->user();
        $profile = $user->profile;

        // validation
        $request->validate([
            'language' => 'nullable|in:en,de',
        ]);

        // profile values
        $mode = $profile->pregnancy_status ?? 'pregnancy';
        $pregnancyWeek = $profile->current_week ?? 1;
        $postpartumDay = $profile->postpartum_day ?? 1;

        // delivery type mapping with default
        $deliveryType = 'vaginal'; // default

        if ($profile?->delivery_type === 'cesarean_delivery') {
            $deliveryType = 'cesarean';
        }

        $language = $request->language ?? 'en';

        // API parameters
        $apiParams = [
            'mode' => $mode,
            'language' => $language,
        ];

        if ($mode === 'pregnancy') {
            $apiParams['pregnancy_week'] = $pregnancyWeek;
            $apiParams['postpartum_day'] = 1;      // default
            $apiParams['delivery_type'] = 'vaginal'; // default
        }

        if ($mode === 'postpartum') {
            $apiParams['postpartum_day'] = $postpartumDay;
            $apiParams['delivery_type'] = $deliveryType;
            $apiParams['pregnancy_week'] = 1; // default
        }

        // API call
        $response = Http::get('https://ai.mamabot.de/api/v1/daily-insight', $apiParams);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Daily insight API failed',
                'error' => $response->body()
            ], $response->status());
        }

        $data = $response->json();

        // save
        $record = $user->dailyInsights()->create([
            'mode' => $mode,
            'language' => $language,
            'pregnancy_week' => $pregnancyWeek,
            'postpartum_day' => $postpartumDay,
            'delivery_type' => $deliveryType,
            'insight' => $data['insight'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daily insight generated & saved successfully',
            'data' => $record
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}

}
