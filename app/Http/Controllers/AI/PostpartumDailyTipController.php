<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PostpartumDailyTip;

class PostpartumDailyTipController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();
            $records = $user->postpartumDailyTips()->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $records
            ]);

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
        $profile = $user->profile;

        // validation
        $request->validate([
            'language' => 'nullable|in:en,de',
            'delivery_type' => 'nullable|in:vaginal,cesarean',
        ]);

        // ---------------------------
        // 1) DAY (postpartum_day) from profile only
        // ---------------------------
        $day = $profile->postpartum_day ?? 1;   // default 1 if profile not found

        // ---------------------------
        // 2) DELIVERY TYPE from profile (or request)
        // ---------------------------
        $deliveryType = 'vaginal'; // default

        if ($profile?->delivery_type === 'cesarean_delivery') {
            $deliveryType = 'cesarean';
        } elseif ($profile?->delivery_type === 'vaginal_delivery') {
            $deliveryType = 'vaginal';
        }

        // If profile not found, then request value
        $deliveryType = $profile ? $deliveryType : ($request->delivery_type ?? 'vaginal');

        $language = $request->language ?? 'en';

        // ---------------------------
        // API CALL
        // ---------------------------
        $response = Http::get('https://ai.mamabot.de/api/v1/postpartum/daily-tip', [
            'user_id' => (string) $user->id,
            'day' => $day,
            'delivery_type' => $deliveryType,
            'language' => $language,
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Postpartum daily tip API failed',
                'error' => $response->body()
            ], $response->status());
        }

        $data = $response->json();

        // ---------------------------
        // SAVE LOG
        // ---------------------------
        $record = $user->postpartumDailyTips()->create([
            'day' => $day,
            'delivery_type' => $deliveryType,
            'language' => $language,
            'tip' => $data['tip'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Postpartum daily tip generated & saved successfully',
            'data' => $record
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
