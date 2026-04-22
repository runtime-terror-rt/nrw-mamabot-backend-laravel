<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\PainMovementLog;
use Illuminate\Http\Request;

class PainMovementLogController extends Controller
{
    /**
     * GET /pain-movement-logs
     * Show latest log for authenticated user
     */
    public function index()
    {
        try {
            $user = auth()->user();

            $log = PainMovementLog::where('user_id', $user->id)
                ->orderBy('log_date', 'desc')
                ->get();

            if (!$log) {
                return response()->json([
                    'message' => 'No pain movement log found'
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
     * POST /pain-movement-logs
     * Create or update today's log (one per day)
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $profile = $user->profile;

            // -------------------------
            // Validation (no delivery_type here)
            // -------------------------
            $validated = $request->validate([
                'pain_level' => 'nullable|integer|min:0|max:10',
                'discomfort_areas' => 'nullable|array',
                'movement_status' => 'nullable|in:Difficulty walking,Pain when sitting,Hard to bend,Limited mobility,Normal movement',
                'tip_shown' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            $today = now()->toDateString();

            // -------------------------
            // DELIVERY TYPE FROM PROFILE
            // -------------------------
            $deliveryType = $profile?->delivery_type ?? 'vaginal_delivery';

            if ($deliveryType === 'cesarean_delivery') {
                $deliveryType = 'cesarean';
            } else {
                $deliveryType = 'vaginal';
            }


            // -------------------------
            // CREATE OR UPDATE LOG
            // -------------------------
            $log = PainMovementLog::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'log_date' => $today,
                ],
                [
                    'pain_level' => $validated['pain_level'] ?? null,
                    'discomfort_areas' => $validated['discomfort_areas'] ?? null,
                    'movement_status' => $validated['movement_status'] ?? null,
                    'tip_shown' => $validated['tip_shown'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'delivery_type' => $deliveryType,
                ]
            );

            return response()->json([
                'message' => 'Pain movement log saved successfully',
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
