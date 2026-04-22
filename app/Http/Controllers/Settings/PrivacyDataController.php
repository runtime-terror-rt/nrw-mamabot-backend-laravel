<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use App\Models\UserSettingsPersonalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrivacyDataController extends Controller
{
    public function index()
    {
        try {
            // ✅ Eager load 'user' to get user information
            $privacySetting = UserSettingsPersonalization::where('user_id', auth()->id())
                ->with('user:id,first_name,last_name,email')
                ->select('id', 'user_id', 'analytics_cookies','two_factor_auth')
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Privacy & Data retrieved successfully.',
                'data' => $privacySetting
            ], 200);

        } catch (\Exception $e) {
            Log::error('Profile Index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching Privacy & Data.',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // ✅ Validation
            $request->validate([
                'analytics_cookies' => 'required|boolean',
                'two_factor_auth' => 'required|boolean',
            ]);

            // updateOrCreate([search condition], [data to update/create])
            $privacySetting = UserSettingsPersonalization::updateOrCreate(
                ['user_id' => auth()->id()],

                $request->all()

            );

            $message = $privacySetting->wasRecentlyCreated ? 'Privacy & Data Settings created successfully.' : 'Privacy & Data Settings updated successfully.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $privacySetting
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            return response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Privacy & Data Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    public function userDevices()
    {
        try {
            // ✅ Eager load 'user' to get user information
            $userDevices = UserDevice::where('user_id', auth()->id())
                ->with('user:id,first_name,last_name,email')
                ->select('id', 'user_id', 'device_type','device_name','last_active_at','is_active')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'User Devices retrieved successfully.',
                'data' => $userDevices
            ], 200);

        } catch (\Exception $e) {
            Log::error('User Devices Index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching User Devices',
            ], 500);
        }

    }
}
