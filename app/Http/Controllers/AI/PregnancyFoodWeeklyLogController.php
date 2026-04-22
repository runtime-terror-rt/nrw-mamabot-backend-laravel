<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PregnancyFoodWeeklyLog;

class PregnancyFoodWeeklyLogController extends Controller
{

public function index()
    {
        try {
            $user = auth()->user();
            $logs = $user->pregnancyFoodWeeklyLogs()->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $logs
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
                'pregnancy_week' => 'nullable|integer|min:1|max:40',
                'dietary_preference' => 'nullable|string|in:no_restriction,vegetarian,vegan,pescatarian,gluten_free,lactose_free,halal,kosher,low_sodium,gestational_diabetes_friendly',
                
            ]);

            // resolve values
            $pregnancyWeek = $request->pregnancy_week ?? $profile->current_week ?? 1;
            $dietaryPreference = $request->dietary_preference ?? $profile->dietary_preferences ?? 'vegetarian';

            // Build URL with repeated query params
            $baseUrl = 'https://ai.mamabot.de/api/v1/pregnancy/food/weekly';

            $query = [
                'user_id' => (string) $user->id,
                'pregnancy_week' => $pregnancyWeek,
                'dietary_preference' => $dietaryPreference,
            ];

            // Add repeated food_items param
            $foodQuery = '';
            foreach ($request->food_items as $item) {
                $foodQuery .= '&food_items=' . urlencode($item);
            }

            $url = $baseUrl . '?' . http_build_query($query) . $foodQuery;

            // External API call
            $response = Http::get($url);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Weekly food API failed',
                    'error' => $response->body()
                ], $response->status());
            }

            $data = $response->json();

            // SAVE AI RESPONSE
            $log = PregnancyFoodWeeklyLog::create([
                'user_id' => $user->id,
                'pregnancy_week' => $pregnancyWeek,
                'dietary_preference' => $dietaryPreference,
                'food_items' => $request->food_items,
                'week' => $data['week'] ?? null,
                'daily_plan' => $data['daily_plan'] ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Weekly food plan generated & saved',
                'data' => $log
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
