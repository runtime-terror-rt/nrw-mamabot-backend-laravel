<?php

namespace App\Http\Controllers\Backend;

use App\Models\Page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of all pages.
     */
    public function index()
    {
        $pages = Page::orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'data' => $pages
        ], 200);
    }

    /**
     * Store a newly created page.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can create Page.',
            ], 403);
        }
        $validator = Validator::make($request->all(), [
            'title'            => 'required|max:255',
            'content'          => 'nullable',
            'meta_title'       => 'nullable|max:255',
            'meta_description' => 'nullable',
            'meta_keywords'    => 'nullable',
            'is_active'        => 'boolean',
            'is_indexable'     => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        // Automatically generate unique slug from title
        $data['slug'] = Str::slug($request->title); 

        $page = Page::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully.',
            'data'    => $page
        ], 201);
    }

    /**
     * Display the specified page by ID.
     */
    public function show($id)
    {
        $page = Page::find($id);
        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $page], 200);
    }

    /**
     * Update the specified page.
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can update Page.',
            ], 403);
        }
        $page = Page::find($id);
        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'   => 'required|max:255',
            'content' => 'nullable',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        // Update slug only if title changes
        if($request->has('title')) {
            $data['slug'] = Str::slug($request->title);
        }
        
        $page->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully.',
            'data'    => $page
        ], 200);
    }

    /**
     * Remove the specified page.
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $role = $user ? $user->getRoleNames()->first() : null;

        if (!$role || strcasecmp($role, 'Admin') !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can delete Page.',
            ], 403);
        }

        
        $page = Page::find($id);
        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }

        $page->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully.'
        ], 200);
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus($id)
    {
        $page = Page::find($id);
        if (!$page) {
            return response()->json(['success' => false, 'message' => 'Page not found'], 404);
        }

        $page->update(['is_active' => !$page->is_active]);
        
        return response()->json([
            'success' => true,
            'message' => 'Page status updated.',
            'is_active' => $page->is_active
        ], 200);
    }

    /**
     * Get a specific page by slug.
     */
    public function getPagesBySlug($slug)
    {
        $page = Page::where('slug', $slug)->where('is_active', true)->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found or inactive'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page
        ], 200);
    }
}