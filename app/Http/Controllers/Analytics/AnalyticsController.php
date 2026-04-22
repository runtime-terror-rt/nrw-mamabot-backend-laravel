<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AiChatLog;
use App\Models\CommunityPost;
use App\Models\Task;
use App\Models\MoodCheck;
use App\Models\RecommendationLog; // Assuming this model exists for recommendations
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function getDashboardAnalytics()
    {
        try {
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();

            // 1. Summary Cards (Top 4 Cards)
            $stats = [
                'active_users' => $this->getMetricWithGrowth(
                    User::whereDate('last_seen', $today)->count(),
                    User::whereDate('last_seen', $yesterday)->count()
                ),
                'ai_chats' => $this->getMetricWithGrowth(
                    AiChatLog::whereDate('created_at', $today)->count(),
                    AiChatLog::whereDate('created_at', $yesterday)->count()
                ),
                // 'tasks' => $this->getMetricWithGrowth(
                //     Task::where('status', 'completed')->whereDate('updated_at', $today)->count(),
                //     Task::where('status', 'completed')->whereDate('updated_at', $yesterday)->count()
               // ),
                'posts' => $this->getMetricWithGrowth(
                    CommunityPost::whereDate('created_at', $today)->count(),
                    CommunityPost::whereDate('created_at', $yesterday)->count()
                )
            ];

            // 2. Daily Active Users (7-Day Line Chart)
            $lineChart = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $lineChart[] = [
                    'label' => $date->format('M d'),
                    'value' => User::whereDate('last_seen', $date)->count()
                ];
            }

            // 3. Feature Engagement (Today's Bar Chart)
            $barChart = [
                ['feature' => 'AI Chat', 'count' => AiChatLog::whereDate('created_at', $today)->count()],
                // ['feature' => 'Tasks Completed', 'count' => Task::where('status', 'completed')->whereDate('updated_at', $today)->count()],
                // ['feature' => 'Mood Check', 'count' => MoodCheck::whereDate('created_at', $today)->count()],
                // ['feature' => 'Recommendations', 'count' => RecommendationLog::whereDate('created_at', $today)->count()],
                 ['feature' => 'Community Posts', 'count' => CommunityPost::whereDate('created_at', $today)->count()],
            ];

            // 4. User Activity by Phase (Last 4 Weeks - Grouped Bar Chart)
            // Assuming User model has a 'phase' column (Pregnancy/Postpartum)
            $phaseActivity = [];
            for ($i = 3; $i >= 0; $i--) {
                $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();

                $phaseActivity = [];
                    for ($i = 3; $i >= 0; $i--) {
                        $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                        $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();

                        $phaseActivity[] = [
                            'week' => 'Week ' . (4 - $i),
                            // Counting users who were active this week and have 'pregnancy' status in their profile
                            'pregnancy' => User::whereBetween('last_seen', [$startOfWeek, $endOfWeek])
                                ->whereHas('profile', function($query) {
                                    $query->where('pregnancy_status', 'pregnancy');
                                })->count(),

                            // Counting users who were active this week and have 'postpartum' status in their profile
                            'postpartum' => User::whereBetween('last_seen', [$startOfWeek, $endOfWeek])
                                ->whereHas('profile', function($query) {
                                    $query->where('pregnancy_status', 'postpartum');
                                })->count(),
                        ];
                    }
            }

            // 5. Daily Interactions Summary (Small Colored Cards)
            $interactions = [
                //'mood_checks' => MoodCheck::whereDate('created_at', $today)->count(),
                //'health_logs' => Task::where('category', 'health')->whereDate('created_at', $today)->count(), // Example logic
                //'recommendations_viewed' => RecommendationLog::whereDate('created_at', $today)->count(),
            ];

            return response()->json([
                'success' => true,
                'summary_cards' => $stats,
                'dau_line_chart' => $lineChart,
                'engagement_bar_chart' => $barChart,
                'phase_activity_chart' => $phaseActivity,
                'interaction_summary' => $interactions,
                'key_insights' => $this->generateKeyInsights() // Optional Helper
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function getMetricWithGrowth($current, $previous)
    {
        $growth = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
        return [
            'current' => $current,
            'growth' => ($growth >= 0 ? '+' : '') . number_format($growth, 0) . '%'
        ];
    }

    private function generateKeyInsights()
    {
        // Static or calculated logic for the bottom "Key Insights" section
        return [
            'most_used_feature' => 'Personalized recommendations are viewed 42 times daily on average.',
            'engagement_trend' => 'Active users have increased by 33% over the past week.',
            'community_activity' => '87 new community posts today with an average of 12 comments per post.'
        ];
    }
}