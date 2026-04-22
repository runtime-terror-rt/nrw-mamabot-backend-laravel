<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\MentalHealthLog;
use Illuminate\Http\Request;

class MentalHealthLogController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();

            $logs = MentalHealthLog::where('user_id', $user->id)
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

    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'log_date' => 'nullable|date',
                'mood' => 'nullable|in:calm,tired,sad,overwhelmed,anxious',
                'energy_level' => 'nullable|in:low,medium,good',
                'sleep_quality' => 'nullable|in:poor,fair,good',
                'tip' => 'nullable|string',
            ]);

            $log = MentalHealthLog::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'log_date' => $validated['log_date']
                ],
                [
                    'mood' => $validated['mood'] ?? null,
                    'energy_level' => $validated['energy_level'] ?? null,
                    'sleep_quality' => $validated['sleep_quality'] ?? null,
                    'tip' => $validated['tip'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Mental health log saved successfully',
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
