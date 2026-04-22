<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserSettingsNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // ✅ Eager load 'user' to get user information
            $notificationSetting = UserSettingsNotification::where('user_id', auth()->id())
                ->with('user:id,first_name,last_name,email')
                ->select('id','user_id','health_wellness','baby_movement_recovery',
                    'community','recommendation','mindful_moments','announcements')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'User Notification info retrieved successfully.',
                'data' => $notificationSetting
            ], 200);

        } catch (\Exception $e) {
            Log::error('Notification Settings Index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching Notification info.',
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
                'health_wellness' => 'required|boolean',
                'baby_movement_recovery' => 'required|boolean',
                'community' => 'required|boolean',
                'recommendation' => 'required|boolean',
                'mindful_moments' => 'required|boolean',
                'announcements' => 'required|boolean',
            ]);

            // updateOrCreate([search condition], [data to update/create])
            $notificationSetting = UserSettingsNotification::updateOrCreate(
                ['user_id' => auth()->id()],

                $request->all()

            );

            $message = $notificationSetting->wasRecentlyCreated ? 'Notification Settings created successfully.' : 'Notification Settings updated successfully.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $notificationSetting
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Notification Settings Error: ' . $e->getMessage());
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
