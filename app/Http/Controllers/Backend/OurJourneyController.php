<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\OurJourney;
use Illuminate\Http\Request;

class OurJourneyController extends Controller
{
    /**
     * Display the journey data.
     * Publicly accessible.
     */
    public function index()
    {
        $data = OurJourney::latest()->get();
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Store or Update Journey content.
     * Only accessible by Admin.
     */
    public function store(Request $request)
    {
        try {
            // Only Admin can update this section
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can update the journey section.',
                ], 403);
            }

            $request->validate([
                'title' => 'required|string',
                'image_url_1' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'image_url_2' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            // Always target the first record
            $journey = OurJourney::first() ?? new OurJourney();
            
            // Fill text data
            $journey->count = $request->count;
            $journey->title = $request->title;
            $journey->description = $request->description;
            $journey->locale = $request->locale ?? 1;
            $journey->subtitle_1 = $request->subtitle_1;
            $journey->subtitle_2 = $request->subtitle_2;

            // Handle Image 1 upload
            if ($request->hasFile('image_url_1')) {
                $path1 = $request->file('image_url_1')->store('journey', 'public');
                $journey->image_url_1 = asset('storage/' . $path1);
            }

            // Handle Image 2 upload
            if ($request->hasFile('image_url_2')) {
                $path2 = $request->file('image_url_2')->store('journey', 'public');
                $journey->image_url_2 = asset('storage/' . $path2);
            }

            $journey->save();

            return response()->json([
                'success' => true,
                'message' => 'Journey section updated successfully.',
                'data' => $journey
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete Journey content.
     * Only accessible by Admin.
     */
    public function destroy(Request $request, $id)
    {
        try {
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }

            $journey = OurJourney::find($id);
            if (!$journey) {
                return response()->json(['success' => false, 'message' => 'Not found.'], 404);
            }

            $journey->delete();
            return response()->json(['success' => true, 'message' => 'Deleted successfully.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}