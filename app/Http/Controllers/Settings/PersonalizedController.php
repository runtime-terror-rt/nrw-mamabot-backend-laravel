<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserSettingsPersonalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PersonalizedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // ✅ Eager load 'user' to get user information
            $personalized = UserSettingsPersonalization::where('user_id', auth()->id())
                ->with('user:id,first_name,last_name,email')
                ->select('id','user_id','AI_tone','chatbot_speed','background_sound')
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // ✅ Validation
            $request->validate([
                'AI_tone' => 'required|in:empathetic,casual,professional',
                'chatbot_speed' => 'required|in:normal,calm,fast',
                'background_sound' => 'required|in:enabled,disabled',
            ]);

            // updateOrCreate([search condition], [data to update/create])
            $personalized = UserSettingsPersonalization::updateOrCreate(
                ['user_id' => auth()->id()],

                    $request->all()

            );

            $message = $personalized->wasRecentlyCreated ? 'Profile created successfully.' : 'Profile updated successfully.';

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
            Log::error('Personalized Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
