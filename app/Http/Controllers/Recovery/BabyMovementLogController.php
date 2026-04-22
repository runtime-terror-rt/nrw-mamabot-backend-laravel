<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\BabyMovementLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BabyMovementLogController extends Controller
{
    /**
     * GET /baby-movement-logs
     * Get all logs for authenticated user for today
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $today = now()->toDateString(); // Y-m-d

            $logs = BabyMovementLog::where('user_id', $user->id)
                ->where('log_date', $today)
                ->orderBy('start_time')
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
     * POST /baby-movement-logs
     * Create or update a session (one per start_time)
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'log_date'        => 'required|date',
                'start_time'      => 'required|date_format:H:i:s',
                'end_time'        => 'required|date_format:H:i:s|after:start_time',
                'kick_count'      => 'required|integer|min:0',
                'movement_status' => 'nullable|in:normal,reduced,urgent',
                'pregnancy_week'  => 'nullable|integer|min:1|max:42',
                'note'            => 'nullable|string',
            ]);

            $start = Carbon::parse($validated['start_time']);
            $end   = Carbon::parse($validated['end_time']);

            $durationSeconds = $start->diffInSeconds($end);

            // Important: Include start_time in unique check
            $log = BabyMovementLog::updateOrCreate(
                [
                    'user_id'    => $user->id,
                    'log_date'   => $validated['log_date'],
                    'start_time' => $validated['start_time'], // required for unique constraint
                ],
                [
                    'end_time'         => $validated['end_time'],
                    'duration_seconds' => $durationSeconds,
                    'kick_count'       => $validated['kick_count'],
                    'movement_status'  => $validated['movement_status'] ?? 'normal',
                    'pregnancy_week'   => $validated['pregnancy_week'],
                    'note'             => $validated['note'],
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Baby movement log saved successfully',
                'data'    => $log
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
