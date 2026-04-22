<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    /**
     * Display the About Us content.
     * Publicly accessible.
     */
    public function show()
    {
        $aboutUs = AboutUs::first();
        return response()->json([
            'success' => true,
            'data'    => $aboutUs
        ], 200);
    }

    /**
     * Create or Update About Us content.
     * Only accessible by Admin.
     */
    public function save(Request $request)
    {
        try {
            // Check if the authenticated user has the 'Admin' role using Spatie
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage the About Us section.',
                ], 403);
            }

            $validated = $request->validate([
                'title'       => 'required|string|max:255',
                'subtitle'    => 'nullable|string|max:255',
                'content'     => 'required|string',
                'locale'      => 'required|string|max:10',
                'main_img'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'inset_img'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            // Fetch the first record or create a new instance
            $aboutUs = AboutUs::first() ?? new AboutUs();
            $aboutUs->fill($validated);

            // Handle main image upload
            if ($request->hasFile('main_img')) {
                $path = $request->file('main_img')->store('about_us', 'public');
                $aboutUs->main_img = asset('storage/' . $path);
            }

            // Handle inset image upload
            if ($request->hasFile('inset_img')) {
                $path = $request->file('inset_img')->store('about_us', 'public');
                $aboutUs->inset_img = asset('storage/' . $path);
            }

            $aboutUs->save();

            return response()->json([
                'success' => true,
                'message' => $aboutUs->wasRecentlyCreated
                    ? 'About Us created successfully.'
                    : 'About Us updated successfully.',
                'data'    => $aboutUs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete the About Us content.
     * Only accessible by Admin.
     */

    
    // public function destroy(Request $request)
    // {
    //     try {
    //         // Admin Role Check
    //         if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Unauthorized. Only admins can delete this section.',
    //             ], 403);
    //         }

    //         $aboutUs = AboutUs::first();
            
    //         if ($aboutUs) {
    //             $aboutUs->delete();
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'About Us content deleted successfully.'
    //             ], 200);
    //         }

    //         return response()->json([
    //             'success' => false, 
    //             'message' => 'No content found to delete.'
    //         ], 404);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false, 
    //             'message' => 'Error: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
}