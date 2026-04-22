<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Hero;
use Illuminate\Http\Request;

class HeroController extends Controller
{
    public function index()
    {
        $hero = Hero::first();
        return response()->json(['success' => true, 'data' => $hero], 200);
    }   
    public function storeHero(Request $request)
    {
        try {
            // 1. Admin Authorization Check
            if (!$request->user() || !$request->user()->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can update the hero section.',
                ], 403);
            }

            // 2. Validation Logic
            $request->validate([
                'id'          => 'nullable|exists:heroes,id', 
                'title'       => 'required|string',
                'subtitle'    => 'required|string',
                'description' => 'required|string',
                'btn_text'    => 'required|string',
                'btn_link'    => 'required|string',
                'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
            ]);

            // 3. Find existing Hero if ID is provided, otherwise null
            $hero = $request->id ? Hero::find($request->id) : null;
            $data = $request->except('image');

            // 4. Handle Image Upload
            if ($request->hasFile('image')) {
                // Delete old file ONLY if we are updating an existing record
                if ($hero && $hero->image) {
                    $oldPath = str_replace(asset('storage/'), '', $hero->image);
                    \Storage::disk('public')->delete($oldPath);
                }

                // Store new file and save full URL
                $path = $request->file('image')->store('hero', 'public');
                $data['image'] = asset('storage/' . $path);
            }

            // 5. Dynamic Update or Create Logic
            $hero = Hero::updateOrCreate(
                ['id' => $request->id],
                $data
            );

            $message = $hero->wasRecentlyCreated ? 'Hero section created successfully.' : 'Hero section updated successfully.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $hero
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
