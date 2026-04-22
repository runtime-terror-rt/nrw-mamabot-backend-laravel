<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\MovementRestriction;
use Illuminate\Http\Request;

class MovementRestrictionController extends Controller
{
    /**
     * GET /movement-restrictions
     * Latest log for authenticated user
     */
    public function index()
    {
        try {
            $user = auth()->user();

            $log = MovementRestriction::where('user_id', $user->id)
                ->orderBy('log_date', 'desc')
                ->first();

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'No movement restriction log found'
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
     * POST /movement-restrictions
     * Create or update today's log (one per day)
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'log_date' => 'nullable|date',
                'avoided_heavy_lifting' => 'nullable|boolean',
                'avoided_sudden_bending' => 'nullable|boolean',
                'supported_abdomen' => 'nullable|boolean',
                'rested_when_needed' => 'nullable|boolean',
                'notes' => 'nullable|string',
                'tip' => 'nullable|string',
            ]);

            // default today
            $logDate = $validated['log_date'] ?? now()->toDateString();

            $log = MovementRestriction::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'log_date' => $logDate,
                ],
                [
                    'avoided_heavy_lifting' => $validated['avoided_heavy_lifting'] ?? false,
                    'avoided_sudden_bending' => $validated['avoided_sudden_bending'] ?? false,
                    'supported_abdomen' => $validated['supported_abdomen'] ?? false,
                    'rested_when_needed' => $validated['rested_when_needed'] ?? false,
                    'notes' => $validated['notes'] ?? null,
                    'tip' => $validated['tip'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Movement restriction log saved successfully',
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
