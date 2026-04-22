<?php

namespace App\Http\Controllers\WekknessSelfCare;
use App\Http\Controllers\Controller;

use App\Models\WellnessActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WellnessActivityController extends Controller
{
    public function storeOrUpdate(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id'                => 'nullable|exists:wellness_activities,id',
                'title'             => 'required_without:id|string|max:255',
                'short_description' => 'required_without:id|string',
                'phase_type'        => 'required_without:id|in:pregnancy,postpartum',
                'trimester'         => 'required_without:id|in:1,2,3,all',
                'duration'          => 'required_without:id|string',
                'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'video_url'         => 'nullable|url',
                'status'            => 'nullable|boolean',
            ]);

            if ($request->hasFile('image')) {
                if ($request->id) {
                    $oldActivity = WellnessActivity::find($request->id);
                    if ($oldActivity && $oldActivity->image) {
                        $oldPath = str_replace(asset('storage/'), '', $oldActivity->image);
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                
                $path = $request->file('image')->store('wellness', 'public');
                $validatedData['image'] = asset('storage/' . $path);
            }

            $activity = WellnessActivity::updateOrCreate(
                ['id' => $request->id], 
                $validatedData   
            );

            return response()->json([
                'success' => true,
                'message' => $request->id ? 'Activity updated successfully' : 'Activity created successfully',
                'data'    => $activity
            ], $request->id ? 200 : 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function getWellnessActivities(Request $request)
    {
        try {
            $user = auth()->user();
            $profile = $user->profile; 

            if (!$profile) {
                return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
            }

            $query = WellnessActivity::where('status', 1);

            if ($profile->pregnancy_status === 'pregnancy') {
                $week = $profile->current_week ?? 1;
                
                $trimester = '1';
                if ($week >= 13 && $week <= 26) {
                    $trimester = '2';
                } elseif ($week >= 27) {
                    $trimester = '3';
                }

                $query->where('phase_type', 'pregnancy')
                    ->where(function($q) use ($trimester) {
                        $q->where('trimester', $trimester)
                            ->orWhere('trimester', 'all');
                    });
            } else {
                $query->where('phase_type', 'postpartum');
            }

            $activities = $query->latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Recommended activities based on your week ' . ($profile->current_week ?? 'N/A'),
                'data'    => $activities
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        try {
            $activity = WellnessActivity::find($id);
            if (!$activity) {
                return response()->json(['success' => false, 'message' => 'Activity not found'], 404);
            }

            if ($activity->image) {
                $oldPath = str_replace(asset('storage/'), '', $activity->image);
                Storage::disk('public')->delete($oldPath);
            }

            $activity->delete();

            return response()->json(['success' => true, 'message' => 'Activity deleted successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
