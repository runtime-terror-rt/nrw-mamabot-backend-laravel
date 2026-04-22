<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\MotherWellnessLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MotherWellnessLogController extends Controller
{
    /**
     * GET /mother-wellness-logs
     * Get logged-in user's latest log + streak
     */
    public function index()
    {
        try {
            $user = auth()->user();

            $log = MotherWellnessLog::where('user_id', $user->id)
                ->orderBy('log_date', 'desc')
                ->first();

            if (!$log) {
                return response()->json([
                    'message' => 'No wellness log found',
                    'data' => null
                ], 404);
            }

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
     * POST /mother-wellness-logs
     * Create or update today's log
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'mood' => 'nullable|in:good,neutral,low',
                'energy_level' => 'nullable|in:low,medium,good',
                'provider_override' => 'nullable|boolean',
                'override_reason' => 'nullable|string',
            ]);

            $today = Carbon::today()->toDateString();

            $log = MotherWellnessLog::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'log_date' => $today,
                ],
                [
                    'mood' => $validated['mood'] ?? null,
                    'energy_level' => $validated['energy_level'] ?? null,
                    'provider_override' => $validated['provider_override'] ?? false,
                    'override_reason' => $validated['override_reason'] ?? null,
                ]
            );

            return response()->json([
                'message' => 'Mother wellness log saved successfully',
                'data' => $log
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
