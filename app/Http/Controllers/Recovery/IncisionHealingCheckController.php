<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\IncisionHealingCheck;
use Illuminate\Http\Request;

class IncisionHealingCheckController extends Controller
{
    /**
     * GET /incision-healing-checks
     * Show latest log for authenticated user
     */
    public function index()
    {
        try {
            $user = auth()->user();

            $log = IncisionHealingCheck::where('user_id', $user->id)
                ->orderBy('log_date', 'desc')
                ->first();

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'No incision healing log found'
                ], 404);
            }

            return response()->json([
                'success' => true,
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

    /**
     * POST /incision-healing-checks
     * Create or update today's log (one per day)
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'redness' => 'nullable|in:none,mild,moderate,severe',
                'swelling' => 'nullable|boolean',
                'warmth' => 'nullable|boolean',
                'tenderness' => 'nullable|boolean',
                'pain_score' => 'nullable|integer|min:0|max:10',
                'sensations' => 'nullable|array',
                'chills_fever' => 'nullable|boolean',
                'discharge_type' => 'nullable|in:none,clear,bloody,yellow',
                'healing_status' => 'nullable|in:normal,attention_needed,urgent',
                'guidance' => 'nullable|string',
            ]);

            $today = now()->toDateString();

            $log = IncisionHealingCheck::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'log_date' => $today,
                ],
                [
                    'redness' => $validated['redness'] ?? null,
                    'swelling' => $validated['swelling'] ?? false,
                    'warmth' => $validated['warmth'] ?? false,
                    'tenderness' => $validated['tenderness'] ?? false,
                    'pain_score' => $validated['pain_score'] ?? null,
                    'sensations' => $validated['sensations'] ?? null,
                    'chills_fever' => $validated['chills_fever'] ?? false,
                    'discharge_type' => $validated['discharge_type'] ?? null,
                    'healing_status' => $validated['healing_status'] ?? 'normal',
                    'guidance' => $validated['guidance'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Incision healing log saved successfully',
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
