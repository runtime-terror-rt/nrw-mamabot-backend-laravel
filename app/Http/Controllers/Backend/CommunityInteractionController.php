<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CommunityLike;
use App\Models\CommunityComment;
use App\Models\CommunityShare;
use App\Models\ShareLog;
use Illuminate\Http\Request;
use App\Models\CommunityPost; 
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class CommunityInteractionController extends Controller
{
    /**
     * Toggle Like/Unlike on a post
     */
    public function toggleLike(Request $request)
    {
        $request->validate(['post_id' => 'required|exists:community_posts,id']);
        $userId = auth()->id();

        // Check if user already liked the post
        $existingLike = CommunityLike::where('post_id', $request->post_id)
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            return response()->json(['success' => true, 'message' => 'Unliked', 'is_liked' => false]);
        }

        CommunityLike::create([
            'post_id'  => $request->post_id,
            'user_id'  => $userId,
            'liked_at' => now() //
        ]);

        return response()->json(['success' => true, 'message' => 'Liked', 'is_liked' => true]);
    }

    /**
     * Store a new comment
     */
    public function storeComment(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:community_posts,id',
            'content' => 'required|string' 
        ]);

        $comment = CommunityComment::create([
            'post_id' => $request->post_id,
            'user_id' => auth()->id(),
            'content' => $request->content
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Comment posted successfully', 
            'data'    => $comment
        ]);
    }

    public function sharePost(Request $request)
    {
        
        $request->validate([
            'post_id'  => 'required|exists:community_posts,id',
            'platform' => 'required|string|in:facebook,linkedin,twitter,copy,mamabot_group',
            'group_id' => 'nullable|integer'
        ]);

        $userId = auth()->id();
        $now = now();

        try {
            // Record the share action
            CommunityShare::create([
                'post_id'   => $request->post_id,
                'user_id'   => $userId,
                'shared_at' => $now
            ]);

            // Detailed share log
            ShareLog::create([
                'user_id'   => $userId,
                'post_id'   => $request->post_id,
                'platform'  => $request->platform,
                'group_id'  => ($request->platform === 'mamabot_group') ? $request->group_id : null,
                'shared_at' => $now
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post successfully shared via ' . ucfirst($request->platform)
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getPopularTopics()
    {
        try {
            $oneWeekAgo = Carbon::now()->subDays(7);

            $popularPosts = DB::table('community_posts')
                ->select('community_posts.id', 'community_posts.title')
                // Count Likes
                ->leftJoin('community_likes', 'community_posts.id', '=', 'community_likes.post_id')
                // Count Comments
                ->leftJoin('community_comments', 'community_posts.id', '=', 'community_comments.post_id')
                // Count Shares
                ->leftJoin('community_shares', 'community_posts.id', '=', 'community_shares.post_id')
                ->selectRaw('
                    (COUNT(DISTINCT community_likes.id) + 
                    COUNT(DISTINCT community_comments.id) + 
                    COUNT(DISTINCT community_shares.id)) as total_interactions
                ')
                ->where('community_posts.created_at', '>=', $oneWeekAgo)
                ->groupBy('community_posts.id', 'community_posts.title')
                ->orderByDesc('total_interactions')
                ->limit(3) // To match your image UI
                ->get();

            return response()->json([
                'success' => true,
                'data' => $popularPosts
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}