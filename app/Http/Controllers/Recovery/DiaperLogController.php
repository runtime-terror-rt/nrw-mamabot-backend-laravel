<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\DiaperLog;
use Illuminate\Http\Request;

class DiaperLogController extends Controller
{
    public function store(Request $request)
{
    try {

        $request->validate([
            'log_date' => 'required|date|after_or_equal:today|before_or_equal:today',
            'diaper_type' => 'required|in:wet,dirty,both',
            'tip' => 'nullable|string',
        ]);

        $user = auth()->user();
        $profile = $user->profile;

       $deliveryType = $profile?->delivery_type ?? 'vaginal_delivery';

        if ($deliveryType === 'cesarean_delivery') {
            $deliveryType = 'cesarean';
        } else {
            $deliveryType = 'vaginal';
        }

        // Save and get the created record
        $diaperLog = DiaperLog::create([
            'user_id' => $user->id,
            'log_date' => $request->log_date,
            'diaper_type' => $request->diaper_type,
            'tip' => $request->tip,
            'delivery_type' => $deliveryType,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Diaper log saved successfully',
            'data' => $diaperLog
        ], 201);

    } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}


    // Show all diaper logs of logged-in user
    public function index()
    {
        try {

            $user = auth()->user();
            $userId = $user->id;

            // Total diapers today
            $totalDiapersToday = DiaperLog::where('user_id', $userId)
                ->whereDate('log_date', today())
                ->count();

            // Wet count today
            $wetCount = DiaperLog::where('user_id', $userId)
                ->whereDate('log_date', today())
                ->whereIn('diaper_type', ['wet', 'both'])
                ->count();

            // Dirty count today
            $dirtyCount = DiaperLog::where('user_id', $userId)
                ->whereDate('log_date', today())
                ->whereIn('diaper_type', ['dirty', 'both'])
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_diapers_today' => $totalDiapersToday,
                    'wet_count' => $wetCount,
                    'dirty_count' => $dirtyCount,
                ]
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
