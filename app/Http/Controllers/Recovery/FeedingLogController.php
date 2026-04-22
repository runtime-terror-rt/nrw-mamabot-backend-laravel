<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\FeedingLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FeedingLogController extends Controller
{
   

    public function store(Request $request)
{
    try {
        $user = auth()->user();
        $profile = $user->profile;

        $request->validate([
            'log_date' => 'nullable|date',
            'feeding_time' => 'required',
            'feeding_method' => 'nullable|in:breastfeeding,bottle_formula,bottle_pumped,mixed_feeding',
            'duration_left' => 'nullable|integer',
            'duration_right' => 'nullable|integer',
            'latch_quality' => 'nullable|in:good,difficult,painful',
        ]);

        // -------------------------
        // DEFAULT LOG DATE
        // -------------------------
        $logDate = $request->log_date ?? now()->toDateString();

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
        // PREVENT DUPLICATE
        // -------------------------
        $exists = FeedingLog::where('user_id', $user->id)
            ->where('log_date', $logDate)
            ->where('feeding_time', $request->feeding_time)
            ->first();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This feeding log already exists for this time.'
            ], 409);
        }

        // -------------------------
        // SAVE
        // -------------------------
        $log = FeedingLog::create([
            'user_id' => $user->id,
            'log_date' => $logDate,
            'feeding_time' => $request->feeding_time,
            'feeding_method' => $request->feeding_method,
            'duration_left' => $request->duration_left,
            'duration_right' => $request->duration_right,
            'latch_quality' => $request->latch_quality,
            'delivery_type' => $deliveryType,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feeding log saved successfully',
            'data' => $log
        ], 201);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function index()
{
    try {
        $userId = auth()->id();
        $today = now()->toDateString();

        $totalSessionsToday = FeedingLog::where('user_id', $userId)
            ->where('log_date', $today)
            ->count();

        $breastfeedingCount = FeedingLog::where('user_id', $userId)
            ->where('log_date', $today)
            ->where('feeding_method', 'breastfeeding')
            ->count();

        $bottleFeedingCount = FeedingLog::where('user_id', $userId)
            ->where('log_date', $today)
            ->whereIn('feeding_method', ['bottle_formula', 'bottle_pumped'])
            ->count();

        $logs = FeedingLog::where('user_id', $userId)
            ->where('log_date', $today)
            ->orderBy('feeding_time')
            ->pluck('feeding_time');

        $intervals = [];
        for ($i = 1; $i < count($logs); $i++) {
            $intervals[] = abs(
                Carbon::createFromFormat('H:i:s', $logs[$i])
                    ->diffInMinutes(Carbon::createFromFormat('H:i:s', $logs[$i - 1]))
            ) / 60;
        }

        $averageInterval = count($intervals)
            ? (int) floor(array_sum($intervals) / count($intervals))
            : null;

        $lastFeeding = FeedingLog::where('user_id', $userId)
            ->orderBy('log_date', 'desc')
            ->orderBy('feeding_time', 'desc')
            ->first();

        $hoursAgo = null;
        if ($lastFeeding) {
            $lastDateTime = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $lastFeeding->log_date . ' ' . $lastFeeding->feeding_time
            );

            $now = now();

            $hoursAgo = $lastDateTime->greaterThan($now)
                ? 0
                : (int) floor($lastDateTime->diffInMinutes($now) / 60);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_sessions_today' => $totalSessionsToday,
                'breastfeeding_count' => $breastfeedingCount,
                'bottle_feeding_count' => $bottleFeedingCount,
                'average_interval_hours' => $averageInterval,
                'last_feeding_hours_ago' => $hoursAgo,
            ]
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
