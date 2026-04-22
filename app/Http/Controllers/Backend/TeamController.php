<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        try {
            $teams = Team::where('status', true)->latest()->get();

            return response()->json([
                'success' => true,
                'message' => 'Team fetched successfully.',
                'data'    => ['teams' => $teams]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Admin Check
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can create or update team members.',
                ], 403);
            }

            $request->validate([
                'name'  => 'required|string|max:255', 
                'title' => 'required|string|max:255', 
                'thumbnail_img' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
            ]);

            $data = $request->all();

            // Image Handling
            if ($request->hasFile('thumbnail_img')) {
                $path = $request->file('thumbnail_img')->store('teams', 'public');
                $data['thumbnail_img'] = asset('storage/' . $path);
            }

            // Create or Update
            $team = Team::updateOrCreate(
                ['id' => $request->id],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => $request->id ? 'Team member updated successfully.' : 'Team member created successfully.',
                'data'    => $team
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function isActive(Request $request, Team $team)
    {
        try {
            // Admin Check
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
            $newStatus = null;
            if ($request->has('status')) {
                $newStatus = $request->boolean('status');
            } elseif ($request->has('is_active')) {
                $newStatus = $request->boolean('is_active');
            }

            if ($newStatus !== null) {
                $team->update([
                    'status' => $newStatus
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $newStatus ? 'Team member activated.' : 'Team member deactivated.',
                    'data' => $team
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Status field is missing in request.'
            ], 400);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    //  Delete: Only Admin can delete team members  ---
    public function destroy(Request $request, $id)
    {
        try {
            // Admin Check
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete team members.',
                ], 403);
            }

            $team = Team::find($id); //

            if (!$team) {
                return response()->json(['success' => false, 'message' => 'Team member not found.'], 404);
            }

            $team->delete();

            return response()->json([
                'success' => true,
                'message' => 'Team member deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function teamLandingPage()
    {
        try {
            $teams = Team::where('status', true)->latest()->get();

            return response()->json([
                'success' => true,
                'data'    => ['teams' => $teams]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}