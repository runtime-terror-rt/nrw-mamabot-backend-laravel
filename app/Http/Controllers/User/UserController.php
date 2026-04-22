<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function userDashboard()
    {
        try {
            $user = auth()->user();

            // Check if user has subscription and plan to avoid errors
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'current_phase' => $user->current_phase,
                    'delivery_type' => $user->delivery_type,
                    'last_activity' => $user->last_activity,
                    'subscription_plan' => $user->subscription->plan->name ?? null,
                    'plan_id' => $user->subscription->plan->id ?? null,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function isBlockedUnblockedTogggle(Request $request, $id)    
    {
        try {
            $user = User::findOrFail($id); 

            // Toggle logic
            $user->is_blocked = !$user->is_blocked;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => $user->is_blocked ? 'User has been blocked successfully.' : 'User has been unblocked successfully.',
                'data'    => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'is_blocked' => $user->is_blocked
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getUserManagementStats()
    {
        try {
            $today = now();
            $previous = now()->subDays(7); 

            // 1. Total users growth logic
            $totalUsers = User::count();
            $previousUsers = User::where('created_at', '<', $previous)->count();
            $userGrowth = $previousUsers > 0
                ? round((($totalUsers - $previousUsers) / $previousUsers) * 100, 2)
                : 0;

            // 2. Active users growth logic
            $activeUsers = User::where('is_blocked', 0)->count();
            $previousActive = User::where('is_blocked', 0)->where('created_at', '<', $previous)->count();
            $activeGrowth = $previousActive > 0
                ? round((($activeUsers - $previousActive) / $previousActive) * 100, 2)
                : 0;

            // 3. AI chat logs growth logic
            $aiChatLogsCount = DB::table('ai_chat_logs')->count();
            $previousLogs = DB::table('ai_chat_logs')->where('created_at', '<', $previous)->count();
            $logGrowth = $previousLogs > 0
                ? round((($aiChatLogsCount - $previousLogs) / $previousLogs) * 100, 2)
                : 0;

            // 4. Postpartum Segment logic
            $postpartumUsers = User::whereHas('profile', function ($q) {
                $q->where('pregnancy_status', 'LIKE', '%Postpartum%');
            })->count();

            $postpartumPercentage = $totalUsers > 0
                ? round(($postpartumUsers / $totalUsers) * 100, 2)
                : 0;

            $previousPostpartum = User::whereHas('profile', function ($q) {
                $q->where('pregnancy_status', 'LIKE', '%Postpartum%');
            })->where('created_at', '<', $previous)->count();

            $previousPercentage = $previousUsers > 0
                ? round(($previousPostpartum / $previousUsers) * 100, 2)
                : 0;

            $postpartumChange = round($postpartumPercentage - $previousPercentage, 2);

            // Response Data Structure
            $stats = [
                'total_users' => [
                    'count' => $totalUsers,
                    'change_percent' => $userGrowth
                ],
                'active_users' => [
                    'count' => $activeUsers,
                    'change_percent' => $activeGrowth
                ],
                'ai_chat_logs' => [
                    'count' => $aiChatLogsCount,
                    'change_percent' => $logGrowth
                ],
                'postpartum_segment' => [
                    'percentage' => $postpartumPercentage . '%',
                    'change_percent' => $postpartumChange
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserManagement(Request $request)
    {
        $search = $request->query('search');

        $users = User::query()
            ->when($search, function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");
            })
            ->select('id', 'first_name', 'last_name', 'email', 'status', 'current_phase', 'delivery_type', 'last_activity')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }
}