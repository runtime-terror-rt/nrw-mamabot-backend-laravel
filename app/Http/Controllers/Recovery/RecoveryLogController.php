<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\RecoveryLog;
use Illuminate\Http\Request;

class RecoveryLogController extends Controller
{
   

  
   // GET /recovery-logs
public function index()
{
    try {
        $user = auth()->user();

        $log = RecoveryLog::where('user_id', $user->id)
            ->orderBy('log_date', 'desc')
            ->first();

        if (!$log) {
            return response()->json([
                'message' => 'No log found'
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


    // POST /recovery-logs
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'pain_range' => 'nullable|integer',
                'pain_type' => 'nullable|array',
                'bleeding_today' => 'required|in:None,Light,Moderate,Heavy',
                'clots_present' => 'required|boolean',
                'energy_level' => 'required|in:Very Low,Low,Normal,Good,High',
                'mood' => 'nullable|array',
                'notes' => 'nullable|string',
                'log_date' => 'nullable|date',
            ]);

            $log = RecoveryLog::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'log_date' => $validated['log_date']
                ],
                [
                    'pain_range' => $validated['pain_range'] ?? null,
                    'pain_type' => $validated['pain_type'] ?? null,
                    'bleeding_today' => $validated['bleeding_today'],
                    'clots_present' => $validated['clots_present'],
                    'energy_level' => $validated['energy_level'],
                    'mood' => $validated['mood'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            return response()->json([
                'message' => 'Recovery log saved successfully',
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
