<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use Illuminate\Http\Request;

class OurMissionController extends Controller
{
    /**
     * Display a listing of missions.
     * Publicly accessible.
     */
    public function index()
    {
        // Fetch missions ordered by sort_order
        $missions = Mission::orderBy('sort_order', 'asc')->get();
        return response()->json(['success' => true, 'data' => $missions]);
    }

    /**
     * Store or Update Mission content.
     * Only accessible by Admin.
     */
    public function store(Request $request)
    {
        try {
            // Role check using Spatie
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage missions.',
                ], 403);
            }

            $request->validate([
                'title' => 'required|string|max:255',
                'icon_url' => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:1024',
            ]);

            $data = $request->all();

            // Handle Icon Upload
            if ($request->hasFile('icon_url')) {
                $path = $request->file('icon_url')->store('missions', 'public');
                $data['icon_url'] = asset('storage/' . $path);
            }

            // Create or update based on the ID provided in the request
            $mission = Mission::updateOrCreate(
                ['id' => $request->id],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => $request->id ? 'Mission updated successfully.' : 'Mission created successfully.',
                'data' => $mission
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a mission entry.
     * Only accessible by Admin.
     */
    public function destroy(Request $request, $id)
    {
        try {
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $mission = Mission::find($id);
            if (!$mission) {
                return response()->json(['success' => false, 'message' => 'Mission not found.'], 404);
            }

            $mission->delete();
            return response()->json(['success' => true, 'message' => 'Mission deleted successfully.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}