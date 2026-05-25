<?php

namespace App\Http\Controllers\AnalyticsSetting;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsSetting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AnalyticsSettingController extends Controller
{
    // List all settings
    public function index()
    {
        return response()->json(AnalyticsSetting::all(), 200);
    }

    // Show single setting
    public function show($id)
    {
        try {
            $setting = AnalyticsSetting::findOrFail($id);
            return response()->json($setting, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Setting not found'], 404);
        }
    }

    public function showByTool()
    {

    }

    // Create new setting
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tool' => 'required|string|in:google_analytics,facebook_pixel',
                'tracking_id' => 'nullable|string',
                'enabled' => 'boolean'
            ]);

            $setting = AnalyticsSetting::create($validated);
            return response()->json($setting, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create setting', 'details' => $e->getMessage()], 500);
        }
    }

    // Update existing setting
    public function update(Request $request, $id)
    {
        try {
            $setting = AnalyticsSetting::findOrFail($id);

            $validated = $request->validate([
                'tracking_id' => 'nullable|string',
                'enabled' => 'boolean'
            ]);

            $setting->update($validated);
            return response()->json($setting, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Setting not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update setting', 'details' => $e->getMessage()], 500);
        }
    }

    // Delete setting
    public function destroy($id)
    {
        try {
            $setting = AnalyticsSetting::findOrFail($id);
            $setting->delete();
            return response()->json(['message' => 'Setting deleted'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Setting not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete setting', 'details' => $e->getMessage()], 500);
        }
    }


}
