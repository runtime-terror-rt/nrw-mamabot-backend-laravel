<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserSettingsPersonalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmartPersonalizedController extends Controller
{
    public function index()
    {
        try {
            // ✅ Eager load 'user' to get user information
            $personalized = UserSettingsPersonalization::where('user_id', auth()->id())
                ->with('user:id,first_name,last_name,email')
                ->select('id', 'user_id', 'motherhood_context', 'activity_awareness',
                    'personalized_nutrition', 'reminder_style', 'mood_tracking', 'voice_feedback')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Personalized info retrieved successfully.',
                'data' => $personalized
            ], 200);

        } catch (\Exception $e) {
            Log::error('Profile Index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching Personalized info.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // ✅ Validation
            $request->validate([
                'motherhood_context' => 'required|boolean',
                'activity_awareness' => 'required|boolean',
                'personalized_nutrition' => 'required|boolean',
                'reminder_style' => 'required|in:normal,loud',
                'mood_tracking' => 'required|boolean',
                'voice_feedback' => 'required|boolean',
            ]);

            // updateOrCreate([search condition], [data to update/create])
            $personalized = UserSettingsPersonalization::updateOrCreate(
                ['user_id' => auth()->id()],

                $request->all()

            );

            $message = $personalized->wasRecentlyCreated ? 'Smart Personalized Settings created successfully.' : 'Smart Personalized Settings updated successfully.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $personalized
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Smart Personalized Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }
}
