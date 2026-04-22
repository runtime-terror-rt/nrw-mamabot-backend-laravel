<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{

    public function indexServices()
    {
        try {
            $services = Service::all();
            return response()->json(['success' => true, 'data' => $services], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
    * Retrieve only 3 services for the homepage or preview section.
    */
    public function indexServicesLimit()
    {
        $services = Service::where('is_active', true)->latest()->take(3)->get();
        return response()->json(['success' => true, 'data' => $services]);
    }

    /**
    * Toggle Service Status (Active/Deactive)
    */
    public function toggleServiceStatus($id)
    {
        try {
            $service = Service::find($id);

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found.'
                ], 404);
            }

            // Toggle the boolean value
            $service->is_active = !$service->is_active;
            $service->save();

            $statusMessage = $service->is_active ? 'Service Activated' : 'Service Deactivated';

            return response()->json([
                'success' => true,
                'message' => $statusMessage,
                'is_active' => $service->is_active
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500); // Ensures JSON output instead of HTML error pages
        }
    }
    
    public function storeService(Request $request)
    {
        try {
            // Admin Check
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete testimonials.',
                ], 403);
            }
            // Validation
            $request->validate([
                'title'         => 'required|string|max:255',
                'description'   => 'required|string',
                'thumbnail_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'main_img'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);

            $data = $request->all();
            $data['slug'] = \Illuminate\Support\Str::slug($request->title);

            // Image Handling (Apnar format onujayi)
            // Thumbnail Image Upload
            if ($request->hasFile('thumbnail_img')) {
                $path = $request->file('thumbnail_img')->store('services/thumbnails', 'public');
                $data['thumbnail_img'] = asset('storage/' . $path);
            }

            // Main Image Upload
            if ($request->hasFile('main_img')) {
                $path = $request->file('main_img')->store('services/main', 'public');
                $data['main_img'] = asset('storage/' . $path);
            }

            // Update or Create
            $service = Service::updateOrCreate(
                ['id' => $request->id],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => $request->id ? 'Service updated successfully.' : 'Service created successfully.',
                'data'    => $service
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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

            $service = Service::find($id); 

            if (!$service) {
                return response()->json(['success' => false, 'message' => 'Service not found.'], 404);
            }

            $service->delete();

            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
