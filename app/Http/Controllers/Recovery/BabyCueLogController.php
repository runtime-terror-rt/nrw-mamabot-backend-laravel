<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\BabyCueLog;
use Illuminate\Http\Request;

class BabyCueLogController extends Controller
{
    /**
     * GET /baby-cue-logs
     * Logged-in user's logs
     */
    public function index()
    {
        try {
            $user = auth()->user();

            $logs = BabyCueLog::where('user_id', $user->id)
                ->orderBy('log_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $logs
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /baby-cue-logs
     * Create or update one log per day
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'log_date' => 'required|date',
                'notes'    => 'nullable|string',
                'tip'      => 'nullable|string',
            ]);

            $log = BabyCueLog::updateOrCreate(
                [
                    'user_id'  => $user->id,
                    'log_date' => $validated['log_date'],
                ],
                [
                    'notes' => $validated['notes'] ?? null,
                    'tip'   => $validated['tip'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Baby cue log saved successfully',
                'data' => $log
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
