<?php

namespace App\Http\Controllers\Recovery;

use App\Http\Controllers\Controller;
use App\Models\SleepTracking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SleepTrackingController extends Controller
{
    /**
     * Store new sleep log
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $profile = $user->profile;

            $request->validate([
                'start_time'    => 'required|date',
                'end_time'      => 'required|date|after:start_time',
                'sleep_type'    => 'nullable|in:nap,night',
                'sleep_quality' => 'nullable|in:calm,restless,interrupted',
                'notes'         => 'nullable|string',
            ]);

            // Delivery type from profile
            $deliveryType = $profile?->delivery_type ?? 'vaginal_delivery';
            $deliveryType = $deliveryType === 'vaginal_delivery' ? 'vaginal' : 'cesarean';

            $startTime = Carbon::parse($request->start_time, 'UTC');
            $endTime   = Carbon::parse($request->end_time, 'UTC');

            $duration = $startTime->diffInMinutes($endTime);

            // ✅ Use create() instead of updateOrCreate()
            $sleepLog = SleepTracking::create([
                'user_id'          => $user->id,
                'log_date'         => $startTime->toDateString(),
                'start_time'       => $startTime,
                'end_time'         => $endTime,
                'duration_minutes' => $duration,
                'sleep_type'       => $request->sleep_type,
                'sleep_quality'    => $request->sleep_quality,
                'notes'            => $request->notes,
                'delivery_type'    => $deliveryType,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sleep tracking saved successfully',
                'data'    => $sleepLog
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Index + summary for logged-in user
     */
  public function index(Request $request)
{
    try {
        $userId = auth()->id();

        // Use requested date or default to today
        $date = $request->log_date ?? now('UTC')->toDateString();

        // Filter by actual sleep session date (start_time)
        $logs = SleepTracking::where('user_id', $userId)
            ->whereDate('start_time', $date)
            ->orderBy('start_time')
            ->get();

        if ($logs->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_sleep_today' => '0 hours',
                    'naps' => '0 sessions',
                    'night' => '0 long stretch',
                    'timeline' => []
                ]
            ]);
        }

        // Total sleep duration
        $totalMinutes = $logs->sum('duration_minutes');
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        $totalSleepText = $hours . ' hours';
        if ($minutes > 0) {
            $totalSleepText .= ' ' . $minutes . ' mins';
        }

        // Count naps and night sleep
        $napsCount = $logs->where('sleep_type', 'nap')->count();
        $nightCount = $logs->where('sleep_type', 'night')->count();

        // Timeline mapping with readable format
        $timeline = $logs->map(function ($log) {
            $durationMinutes = $log->duration_minutes;
            $h = floor($durationMinutes / 60);
            $m = $durationMinutes % 60;

            $durationText = $h > 0 ? $h . 'h' : '';
            if ($m > 0) {
                $durationText .= ($durationText ? ' ' : '') . $m . 'm';
            }

            return [
                'start_time' => Carbon::parse($log->start_time)->format('g:i A'),
                'sleep_type' => ucfirst($log->sleep_type),
                'duration' => $durationText,
                'sleep_quality' => ucfirst($log->sleep_quality),
                'notes' => $log->notes,
                'delivery_type' => $log->delivery_type,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_sleep_today' => $totalSleepText,
                'naps' => $napsCount . ' sessions',
                'night' => $nightCount . ' long stretch',
                'timeline' => $timeline
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
