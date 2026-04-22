<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\HydrationLog;
use Illuminate\Http\Request;

class HydrationLogController extends Controller
{
    /**
     * GET /hydration-logs
     * Latest log for authenticated user
     */
    public function index()
    {
        $user = auth()->user();

        $log = HydrationLog::where('user_id', $user->id)
            ->orderBy('log_date', 'desc')
            ->first();

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'No hydration log found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log
        ], 200);
    }

    /**
     * POST /hydration-logs
     * Create or update today's log (one per day)
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'log_date' => 'nullable|date',
            'glass_count' => 'nullable|integer|min:0',
            'duration_seconds' => 'nullable|integer|min:0',
        ]);

        // default today
        $logDate = $validated['log_date'] ?? now()->toDateString();

        $log = HydrationLog::updateOrCreate(
            [
                'user_id' => $user->id,
                'log_date' => $logDate,
            ],
            [
                'glass_count' => $validated['glass_count'] ?? 0,
                'duration_seconds' => $validated['duration_seconds'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Hydration log saved successfully',
            'data' => $log
        ], 200);
    }
}
