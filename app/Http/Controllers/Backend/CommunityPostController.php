<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CommunityComment;
use App\Models\CommunityLike;
use App\Models\CommunityPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CommunityPostController extends Controller
{
    /**
     * List all posts with like and comment counts
     */
    public function index()
    {
        try {
            $user = auth('sanctum')->user();

            $posts = CommunityPost::with([
                    'user.profile:user_id,image',
                    'comments.user.profile:user_id,image',
                    'shares.user:first_name,last_name,id',
                    'group'
                ])
                ->where('moderation_report_status', '!=', 'removed') 
                ->withCount(['likes', 'comments', 'shares'])
                ->latest()
                ->get();

            $transformedPosts = $posts->map(function ($post) use ($user) {
                $post->is_liked = false;
                if ($user) {
                    $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
                }
                $post->is_joined = false;
                if ($user && $post->group) {
                    $post->is_joined = \DB::table('community_group_by_users')
                        ->where('group_id', $post->group_id)
                        ->where('user_id', $user->id)
                        ->exists();
                }

                if ($user && $post->user_id === $user->id) {
                    $post->role_label = "Admin";
                } else {
                    $post->role_label = "User";
                }

                $post->makeHidden(['likes', 'users']); 

                return $post;
            });

            return response()->json([
                'success' => true, 
                'data' => $transformedPosts
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    // public function index()
    // {
    //     try {
    //         $user = auth('sanctum')->user();

    //         $posts = CommunityPost::with([
    //                 'user.profile:user_id,image',
    //                 'comments.user.profile:user_id,image',
    //                 'shares.user:first_name,last_name,id',
    //                 'group'
    //             ])
    //             ->where('moderation_report_status', '!=', 'removed') 
    //             ->withCount(['likes', 'comments', 'shares'])
    //             ->latest()
    //             ->get();

    //         $transformedPosts = $posts->map(function ($post) use ($user) {
    //             $post->is_liked = false;
    //             if ($user) {
    //                 $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();
    //             }

    //             $post->is_joined = false;
    //             if ($user && $post->group) {
    //                 $post->is_joined = \DB::table('community_group_by_users')
    //                     ->where('group_id', $post->group_id)
    //                     ->where('user_id', $user->id)
    //                     ->exists();
    //             }

    //             $post->makeHidden(['likes', 'users']); 

    //             return $post;
    //         });

    //         return response()->json([
    //             'success' => true, 
    //             'data' => $transformedPosts
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }

    /**
     * Admin Community Monitoring API
     */
    public function monitoring(Request $request)
    {
        try {
            $filter = $request->query('filter', 'all');

            $query = CommunityPost::with([
                    'user' => function($q) {
                        $q->select('id', 'first_name', 'last_name')
                        ->with('profile:user_id,image'); 
                    }, 
                    'comments.user' => function($q) {
                        $q->select('id', 'first_name')
                        ->with('profile:user_id,image');
                    }
                ])
                ->withCount(['likes', 'comments', 'shares']);

            if ($filter == 'latest') {
                $query->latest();
            } 
            elseif ($filter == 'most_active') {
                $query->orderByRaw('(likes_count + comments_count) DESC');
            } 
            elseif ($filter == 'reported') {
                $query->where('reported_count', '>', 0)->orderBy('reported_count', 'DESC');
            } 
            else {
                $query->latest();
            }

            $posts = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Community ' . $filter . ' posts retrieved.',
                'data'    => $posts
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function landingPageCommunity()
    {
        $posts = CommunityPost::with([
                'user', 
                'comments.user',
                'shares.user'
            ])
            ->withCount(['likes', 'comments', 'shares'])
            ->take(3)
            ->latest()
            ->get();

        return response()->json([
            'success' => true, 
            'data' => $posts
        ]);
    }

    /**
     * Store or Update Post with Multiple Images
     */
    // public function store(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'group_id' => 'required|integer', //
    //             'title'    => 'required|string|max:255',
    //             'content'  => 'required|string',
    //             'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
    //         ]);

    //         // Find existing post for update or create new
    //         $post = CommunityPost::find($request->id) ?? new CommunityPost();

    //         // Security: Only owner or Admin can update
    //         if ($request->id && $post->user_id !== auth()->id() && !auth()->user()->hasRole('Admin')) {
    //             return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    //         }

    //         $post->user_id  = auth()->id(); //
    //         $post->group_id = $request->group_id; //
    //         $post->title    = $request->title;
    //         $post->slug     = Str::slug($request->title);
    //         $post->content  = $request->content;
            
    //         // UI Metadata: Role label like "Week 22"
    //         $post->week       = $request->week ?? auth()->user()->current_week; 
    //         $post->role_label = "Week " . $post->week; 
    //         $post->posted_at  = now();

    //         // Handling Multiple Images
    //         if ($request->hasFile('images')) {
    //             $imagePaths = [];
    //             foreach ($request->file('images') as $file) {
    //                 $path = $file->store('community/posts', 'public');
    //                 $imagePaths[] = asset('storage/' . $path);
    //             }
    //             // Array will be stored as JSON string in DB due to model casting
    //             $post->image_urls = $imagePaths; 
    //         }

    //         $post->save();

    //         return response()->json([
    //             'success' => true, 
    //             'message' => 'Post saved successfully', 
    //             'data'    => $post
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'group_id' => 'required|integer',
                'title'    => 'required|string|max:255',
                'content'  => 'required|string',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            ]);

            $user = auth()->user();

            $isUpdate = $request->filled('id');
            $post = $isUpdate ? CommunityPost::find($request->id) : new CommunityPost();

            if ($isUpdate && !$post) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }

            if ($isUpdate && $post->user_id !== $user->id && !$user->hasRole('Admin')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized to update this post'], 403);
            }

            if (!$isUpdate) {
                $post->user_id = $user->id;
                $post->posted_at = now();
            }

            $post->group_id = $request->group_id;
            $post->title    = $request->title;
            $post->slug     = \Illuminate\Support\Str::slug($request->title);
            $post->content  = $request->content;
            
            $post->role_label = "Admin"; 
            
            $post->week = $request->week ?? $user->current_week ?? 1;

            if ($request->hasFile('images')) {
                if ($isUpdate && is_array($post->image_urls)) {
                    foreach ($post->image_urls as $oldUrl) {
                        $oldPath = str_replace(asset('storage/'), '', $oldUrl);
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                    }
                }

                $imagePaths = [];
                foreach ($request->file('images') as $file) {
                    $path = $file->store('community/posts', 'public');
                    $imagePaths[] = asset('storage/' . $path);
                }
                $post->image_urls = $imagePaths; 
            }

            $post->save();

            return response()->json([
                'success' => true, 
                'message' => $isUpdate ? 'Post updated successfully' : 'Post created successfully', 
                'data'    => $post
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete Post and its physical images
     */
    public function destroy($id)
    {
        try {
            // 1. Find the post
            $post = CommunityPost::find($id);

            if (!$post) {
                return response()->json(['success' => false, 'message' => 'Post not found'], 404);
            }

            // 2. Strict Security Check: Only Admin allowed
            // We removed ($post->user_id !== auth()->id()) so owners cannot delete their own posts.
            if (!auth()->user()->hasRole('Admin')) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized. Only an Admin can delete community posts.'
                ], 403);
            }

            // 3. Delete physical files from storage
            if ($post->image_urls) {
                foreach ($post->image_urls as $url) {
                    // Extracts the relative path from the full URL
                    $filePath = str_replace(asset('storage/'), '', $url);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($filePath);
                }
            }

            // 4. Delete the database record
            $post->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Post and associated images deleted successfully by Admin.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * when user reports a post
     */
    public function reportPost(Request $request)
    {
        try {
            $request->validate([
                'post_id' => 'required|exists:community_posts,id',
                'comment'  => 'nullable|string|max:255',
                'report_cause' => 'required|in:spam,sexual_content,harassment,other'
            ]);

            $user = auth()->user();
            $postId = $request->post_id;

            $exists = \DB::table('community_post_reports')
                ->where('post_id', $postId)
                ->where('user_id', $user->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reported this post.'
                ], 400);
            }

            \DB::table('community_post_reports')->insert([
                'post_id'    => $postId,
                'user_id'    => $user->id,
                'comment'     => $request->comment,
                'report_cause' => $request->report_cause,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $post = CommunityPost::find($postId);
            $post->increment('reported_count');

            return response()->json([
                'success' => true,
                'message' => 'Post has been reported successfully.',
                'total_reports' => $post->reported_count
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reported Content page stats and reported posts list
    */
    public function getReportedContentStats()
    {
        try {
            $stats = [
                'pending_review' => CommunityPost::where('moderation_report_status', 'pending')
                                    ->where('reported_count', '>', 0)->count(), 

                'approved'       => CommunityPost::where('moderation_report_status', 'approved')->count(),
                'removed'        => CommunityPost::where('moderation_report_status', 'removed')->count(), 
            ];

            $reportedPosts = CommunityPost::with(['user:id,first_name,last_name'])
                ->where('reported_count', '>', 0)
                //->where('moderation_report_status', 'pending')
                ->orderBy('reported_count', 'DESC')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Reported content and stats retrieved successfully.',
                'stats'   => $stats,
                'data'    => $reportedPosts
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update post status based on admin decision (Approve or Remove).
     */
    public function moderatePost(Request $request, $id)
    {
        // Validate the action sent from the frontend
        $request->validate([
            'action' => 'required|in:approve,remove'
        ]);

        try {
            $post = CommunityPost::findOrFail($id);

            if ($request->action === 'approve') {
                // Set status to approved and clear report counts
                $post->update([
                    'moderation_report_status' => 'approved',
                    'reported_count' => 0 
                ]);
                $message = 'Post has been approved and cleared from reports.';
            } else {
                // Set status to removed to hide it from the community
                $post->update([
                    'moderation_report_status' => 'removed'
                ]);
                $message = 'Post has been removed successfully.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $post
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Community Stats for Dashboard
     */
    public function getCommunityStats()
    {
        try {
            $stats = [
                'total_posts'    => CommunityPost::count(),
                'total_comments' => CommunityComment::count(),
                'total_likes'    => CommunityLike::count(), 
                'reported_posts' => CommunityPost::where('reported_count', '>', 0)->count(),
            ];

            return response()->json([
                'success' => true,
                'data'    => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function myPosts()
    {
        try {
            $user = auth('sanctum')->user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $posts = CommunityPost::with([
                    'user.profile:user_id,image',
                    'comments.user.profile:user_id,image',
                    'shares.user:first_name,last_name,id',
                    'group'
                ])
                ->where('user_id', $user->id)
                ->where('moderation_report_status', '!=', 'removed') 
                ->withCount(['likes', 'comments', 'shares'])
                ->latest()
                ->get();

            $transformedPosts = $posts->map(function ($post) use ($user) {
                $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();

                $post->is_joined = false;
                if ($post->group) {
                    $post->is_joined = \DB::table('community_group_by_users')
                        ->where('group_id', $post->group_id)
                        ->where('user_id', $user->id)
                        ->exists();
                }

                $post->makeHidden(['likes', 'users']); 

                return $post;
            });

            return response()->json([
                'success' => true, 
                'data' => $transformedPosts
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}