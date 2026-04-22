<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\PelvicExerciseLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PelvicExerciseLogController extends Controller
{
    /**
     * GET /pelvic-exercise-logs
     * Logged-in user's latest exercise log + streak
     */
    public function index()
    {
        try {
            $user = auth()->user();

            $log = PelvicExerciseLog::where('user_id', $user->id)
                ->orderBy('log_date', 'desc')
                ->first();

            if (!$log) {
                return response()->json([
                    'message' => 'No pelvic exercise log found',
                    'data' => null
                ], 404);
            }

            $streak = $this->calculateStreak($user->id);

            // add streak_count into data
            $log->streak_count = $streak;

            return response()->json([
                'data' => $log
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /pelvic-exercise-logs
     * Create or update today's log (one per day)
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'duration_seconds' => 'nullable|integer|min:0',
                'completed'        => 'nullable|boolean',
                'skipped'          => 'nullable|boolean',
                'tip_shown'        => 'nullable|string',
            ]);

            $today = Carbon::today()->toDateString();

            $log = PelvicExerciseLog::updateOrCreate(
                [
                    'user_id'  => $user->id,
                    'log_date' => $today,
                ],
                [
                    'duration_seconds' => $validated['duration_seconds'] ?? null,
                    'completed'        => $validated['completed'] ?? false,
                    'skipped'          => $validated['skipped'] ?? false,
                    'tip_shown'        => $validated['tip_shown'] ?? null,
                ]
            );

            $streak = $this->calculateStreak($user->id);

            // add streak_count into data
            $log->streak_count = $streak;

            return response()->json([
                'message' => 'Pelvic exercise log saved successfully',
                'data' => $log
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate consecutive streak (completed = true)
     */
    private function calculateStreak($userId)
    {
        $streak = 0;
        $currentDate = Carbon::today();

        while (true) {
            $log = PelvicExerciseLog::where('user_id', $userId)
                ->whereDate('log_date', $currentDate)
                ->where('completed', true)
                ->first();

            if (!$log) {
                break;
            }

            $streak++;
            $currentDate->subDay();
        }

        return $streak;
    }
}
