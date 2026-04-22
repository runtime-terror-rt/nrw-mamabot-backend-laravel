<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use PHPUnit\Metadata\Test;

class TestimonialController extends Controller
{

    public function index()
    {
        try {
            $testimonials = Testimonial::all();
            return response()->json(['success' => true, 'data' => $testimonials], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Get 3 random testimonials for the landing page.
     */
    public function randomTestimonials()
    {
        try {
            // Fetch 3 random records from the testimonials table
            $testimonials = Testimonial::inRandomOrder()
                ->take(3)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Random testimonials retrieved successfully.',
                'data'    => $testimonials
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function store(Request $request)
    {
        try {
            // Validation
            $request->validate([
                'description'  => 'required|string',
                'author_name'  => 'required|string|max:255',
                'author_title' => 'required|string|max:255',
                'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB limit
                'address'      => 'nullable|string|max:255',
            ]);

            $data = $request->all();

            // Image Handling
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('testimonials', 'public');
                $data['image'] = asset('storage/' . $path);
            }

            // Update or Create Logic
            $testimonial = Testimonial::updateOrCreate(
                ['id' => $request->id],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => $request->id ? 'Testimonial added successfully.' : 'Testimonial updated successfully.',
                'data'    => $testimonial
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            // Admin Check
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete testimonials.',
                ], 403);
            }

            $testimonial = Testimonial::find($id); //

            if (!$testimonial) {
                return response()->json(['success' => false, 'message' => 'Testimonial not found.'], 404);
            }

            $testimonial->delete();

            return response()->json([
                'success' => true,
                'message' => 'Testimonial deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}