<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PregnancyFoodMealPlan;

class PregnancyFoodMealPlanController extends Controller
{
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $profile = $user->profile;

            // validation
            $request->validate([
                'food_item' => 'required|string',
                'dietary_preference' => 'nullable|string',
            ]);

            // resolve values
            $dietaryPreference = $request->dietary_preference
                ?? $profile->dietary_preferences
                ?? 'vegetarian';

            // API call
            $response = Http::get(
                'https://ai.mamabot.de/api/v1/pregnancy/food/meal-plan',
                [
                    'user_id' => (string)$user->id,
                    'food_item' => $request->food_item,
                    'dietary_preference' => $dietaryPreference,
                ]
            );

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Meal Plan API failed',
                    'error' => $response->body()
                ], $response->status());
            }

            $data = $response->json();

            // Save to DB
            $record = PregnancyFoodMealPlan::create([
                'user_id' => $user->id,
                'food_item' => $data['food_item'] ?? $request->food_item,
                'dietary_preference' => $dietaryPreference,
                'meal_plan' => $data['meal_plan'] ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Meal plan generated & saved',
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

    public function index()
    {
        try {
            $user = auth()->user();
            $records = $user->pregnancyFoodMealPlans()->latest()->get();

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
}
