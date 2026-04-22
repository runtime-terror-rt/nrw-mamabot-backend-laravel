<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PregnancyFoodController extends Controller
{


   public function index()
{
    try {
        $user = auth()->user();

        // fetch all saved food lists for this user
        $foods = $user->pregnancyFoods()->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $foods
        ]);
        
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function fetchFoodList(Request $request)
    {
        try {
            $user = auth()->user();

            // validation
            $request->validate([
                'pregnancy_week' => 'nullable|integer|min:1|max:40',
                'dietary_preference' => 'nullable|string|in:no_restriction,vegetarian,vegan,pescatarian,gluten_free,lactose_free,halal,kosher,low_sodium,gestational_diabetes_friendly'
            ]);

            // default values (request → profile → fallback)
            $pregnancyWeek = $request->pregnancy_week 
                ?? $user->profile->current_week 
                ?? 1;

            $dietaryPreference = $request->dietary_preference 
                ?? $user->profile->dietary_preferences 
                ?? 'vegetarian';

            // API call
            $response = Http::get(
                'https://ai.mamabot.de/api/v1/pregnancy/food/weekly',
                [
                    'user_id'            => $user->id, // Add this line
                    'pregnancy_week'     => (int) $pregnancyWeek,
                    'dietary_preference' => $dietaryPreference
                ]
            );

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'External API call failed',
                    'status' => $response->status(),
                    'api_errors' => $response->json() // Add this to see the real reason
                ], $response->status());
            }

            return response()->json([
                'success' => true,
                'data' => $response->json()
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
                'dietary_preference' => 'nullable|string|in:no_restriction,vegetarian,vegan,pescatarian,gluten_free,lactose_free,halal,kosher,low_sodium,gestational_diabetes_friendly'
            ]);

            // default values (profile → request → fallback)
            $pregnancyWeek = $request->pregnancy_week 
                ?? $profile->current_week 
                ?? 1;

            $dietaryPreference = $request->dietary_preference 
                ?? $profile->dietary_preferences 
                ?? 'vegetarian';

            // API call
            $response = Http::get(
                'https://ai.mamabot.de/api/v1/pregnancy/food/list',
                [
                    'pregnancy_week' => $pregnancyWeek,
                    'dietary_preference' => $dietaryPreference
                ]
            );

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'External API call failed',
                    'status' => $response->status(),
                ], $response->status());
            }

            $data = $response->json();

            // ✅ Save using relationship (user_id auto)
            $record = $user->pregnancyFoods()->create([
                'pregnancy_week' => $pregnancyWeek,
                'dietary_preference' => $dietaryPreference,
                'foods' => $data['foods'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data saved successfully',
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
