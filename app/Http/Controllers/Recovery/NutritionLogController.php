<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\NutritionLog;
use Illuminate\Http\Request;

class NutritionLogController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();

            $logs = NutritionLog::where('user_id', $user->id)
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
                'log_date' => 'required|date',
                'notes' => 'nullable|string',
                'tip' => 'nullable|string',
            ]);

            $log = NutritionLog::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'log_date' => $validated['log_date']
                ],
                [
                    'notes' => $validated['notes'] ?? null,
                    'tip' => $validated['tip'] ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Nutrition log saved successfully',
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
