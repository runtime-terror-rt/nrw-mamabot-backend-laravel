<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PregnancyFoodRecipe;

class PregnancyFoodRecipeController extends Controller
{
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $profile = $user->profile;

            $request->validate([
                'food_item' => 'required|string',
                'dietary_preference' => 'nullable|string'
            ]);

            // preference from request -> profile -> fallback
            $dietaryPreference = $request->dietary_preference 
                ?? $profile->dietary_preferences 
                ?? 'vegetarian';

            $response = Http::get('https://ai.mamabot.de/api/v1/pregnancy/food/recipes', [
                'user_id' => $user->id,
                'food_item' => $request->food_item,
                'dietary_preference' => $dietaryPreference,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'External API call failed',
                    'status' => $response->status(),
                ], $response->status());
            }

            $data = $response->json();

            $record = PregnancyFoodRecipe::create([
                'user_id' => $user->id,
                'food_item' => $data['food_item'],
                'dietary_preference' => $dietaryPreference,
                'recipes' => $data['recipes'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Recipes saved successfully',
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
        $records = $user->pregnancyFoodRecipes()->latest()->get();

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
