<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CommunityGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommunityGroupsController extends Controller
{
    /**
     * Display a listing of active community groups.
     * Publicly accessible.
     */
   public function index()
    {
        try {
            $user = auth('sanctum')->user(); 

            $rawGroups = CommunityGroup::where('is_active', true)
                ->withCount('users')
                ->with('users') 
                ->get();

            $groups = $rawGroups->map(function ($group) use ($user) {
                
                $isMember = false;
                if ($user) {
                    $isMember = $group->users->contains('id', $user->id);
                }

                $group->makeHidden('users');

                $group->is_member = $isMember;

                if($group->image) {
                    $group->image = str_contains($group->image, 'http') 
                        ? $group->image 
                        : asset('storage/' . $group->image);
                }

                return $group;
            });

            return response()->json([
                'success' => true,
                'data' => $groups 
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create or Update a community group.
     * Only accessible by Admin.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'  => 'required|string|max:255', 
                'stage' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048', 
            ]);

            $data = $request->all();

            if ($request->name) {
                $data['slug'] = Str::slug($request->name);
            }

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('groups', 'public');
                $data['image'] = asset('storage/' . $path);
            }

            $group = CommunityGroup::updateOrCreate(
                ['id' => $request->id],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => $request->id ? 'Group updated successfully.' : 'Group created successfully.',
                'data'    => $group
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified community group.
     * Only accessible by Admin.
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Check if the authenticated user has the 'Admin' role using Spatie
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized! Only Admin can delete groups.'
                ], 403);
            }

            $group = CommunityGroup::find($id); 

            if (!$group) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Group not found.'
                ], 404);
            }

            $group->delete();

            return response()->json([
                'success' => true,
                'message' => 'Community group deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function joinGroup(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:community_groups,id'
        ]);

        $user = auth()->user();
        $group = CommunityGroup::find($request->group_id);

        $status = $user->groups()->toggle($group->id);

        if (count($status['attached']) > 0) {
            $group->increment('member_count');
            return response()->json([
                'success' => true,
                'message' => 'Successfully joined the ' . $group->name,
                'is_member' => true
            ]);
        }

        $group->decrement('member_count');
        return response()->json([
            'success' => true,
            'message' => 'You have left the group.',
            'is_member' => false
        ]);
    }

    public function activeDeactiveGroup(Request $request, $id)
    {
        try {
            if (!$request->user() || !$request->user()->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can manage groups.',
                ], 403);
            }

            $group = CommunityGroup::find($id);
            if (!$group) {
                return response()->json(['success' => false, 'message' => 'Group not found'], 404);
            }

            $group->is_active = !$group->is_active;
            $group->save();

            return response()->json([
                'success' => true,
                'message' => 'Group ' . ($group->is_active ? 'activated' : 'deactivated') . ' successfully.',
                'data' => $group
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function myGroups()
    {
        $groups = auth()->user()->groups;
        return response()->json([
            'success' => true,
            'data' => $groups
        ]);
    }
}