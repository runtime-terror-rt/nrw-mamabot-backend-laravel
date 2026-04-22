<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiChatLogController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();

            return response()->json([
                'success' => true,
                'data' => $user->aiChatLogs()->latest()->get()
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

            // ----------------  DYNAMIC LIMIT FROM SUBSCRIPTION PLAN ----------------
            $userSub = UserSubscription::where('user_id', $user->id)
                ->with('plan') 
                ->latest()
                ->first();

            $limitVal = 20; 
            $isUnlimited = false;

            if ($userSub && $userSub->plan) {
                $dbLimit = $userSub->plan->limit; 

                if (strtolower((string)$dbLimit) === 'unlimited') {
                    $isUnlimited = true;
                    $limitVal = 99999; 
                } else {
                    $limitVal = (int)$dbLimit;
                }
            }

            $todayCount = $user->aiChatLogs()
                ->whereDate('created_at', now()->toDateString())
                ->count();

            if (!$isUnlimited && $todayCount >= $limitVal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily chat limit exceeded. Your limit is ' . $limitVal,
                    'quota_exceeded' => true
                ], 429);
            }

            // ----------------  VALIDATION ----------------
            $request->validate([
                'chat_id' => 'nullable|string',
                'message' => 'required|string',
                'language' => 'nullable|in:en,de',
                'country' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'file'  => 'nullable|file|mimes:pdf,doc,docx,txt|max:5120',
            ]);

            // ----------------  DATA PREPARATION ----------------
            $chatId = $request->chat_id ?? (string) \Illuminate\Support\Str::uuid();
            $mode = $profile?->pregnancy_status ?? 'pregnancy';
            $pregnancyWeek = $profile?->current_week ?? 1;
            $postpartumDay = $profile?->postpartum_day ?? 1;

            $deliveryType = match ($profile?->delivery_type) {
                'vaginal_delivery'  => 'vaginal',
                'cesarean_delivery' => 'cesarean',
                default => 'vaginal',
            };

            $validTones = ['empathetic', 'calm', 'reassuring', 'motivational', 'neutral', 'friendly_mama', 'professional', 'spiritual', 'mindful'];
            $tone = in_array($profile?->AI_tone, $validTones) ? $profile?->AI_tone : 'empathetic';

            $validSupportTypes = ['balanced', 'emotional', 'informational', 'practical', 'reassurance'];
            $supportType = in_array($profile?->support_type, $validSupportTypes) ? $profile?->support_type : 'emotional';

            $validDietary = ['no_restriction', 'vegetarian', 'vegan', 'pescatarian', 'gluten_free', 'lactose_free', 'halal', 'kosher', 'low_sodium', 'gestational_diabetes_friendly'];
            $dietary = in_array($profile?->dietary_preferences, $validDietary) ? $profile?->dietary_preferences : 'no_restriction';

            $language = $request->language ?? $profile?->language ?? 'en';

            // ----------------  FILE UPLOAD ----------------
            $imagePath = $request->hasFile('image') ? $request->file('image')->store('ai_chat_images', 'public') : null;
            $filePath  = $request->hasFile('file') ? $request->file('file')->store('ai_chat_files', 'public') : null;

            // ----------------  API CALL (Integer Limit) ----------------
            $http = \Illuminate\Support\Facades\Http::asMultipart();
            if ($imagePath) $http->attach('image', \Illuminate\Support\Facades\Storage::disk('public')->get($imagePath), basename($imagePath));
            if ($filePath) $http->attach('file', \Illuminate\Support\Facades\Storage::disk('public')->get($filePath), basename($filePath));

            $response = $http->post('https://ai.mamabot.de/api/v1/chat', [
                'user_id' => $user->id,
                'chat_id' => $chatId,
                'mode' => $mode,
                'pregnancy_week' => $pregnancyWeek,
                'postpartum_day' => $postpartumDay,
                'delivery_type' => $deliveryType,
                'language' => $language,
                'country' => $request->country,
                'tone_of_ai' => $tone,
                'support_type' => $supportType,
                'dietary_preferences' => $dietary,
                'message' => $request->message,
                'daily_query_limit' => $limitVal,
            ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI chat API failed',
                    'error' => $response->body()
                ], $response->status());
            }

            $data = $response->json();

            // ----------------  SAVE LOG (To Integer Column) ----------------
            $record = $user->aiChatLogs()->create([
                'chat_id' => $chatId,
                'mode' => $mode,
                'pregnancy_week' => $pregnancyWeek,
                'postpartum_day' => $postpartumDay,
                'delivery_type' => $deliveryType,
                'language' => $language,
                'country' => $request->country,
                'tone_of_ai' => $tone,
                'support_type' => $supportType,
                'dietary_preferences' => $dietary,
                'user_message' => $request->message,
                'ai_response' => $data['response'] ?? null,
                'is_emergency' => $data['is_emergency'] ?? false,
                'quota_exceeded' => false,
                'used_today' => $todayCount + 1,
                'daily_query_limit' => $limitVal,
                'image_path' => $imagePath ? asset('storage/' . $imagePath) : null,
                'file_path'  => $filePath ? asset('storage/' . $filePath) : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'AI chat generated & saved successfully',
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

    public function getUserChatHistory(Request $request)
    {
        $user = auth()->user();
        $chatId = $request->query('chat_id');

        $query = $user->aiChatLogs()
            ->select('chat_id', 'user_message', 'ai_response', 'created_at')
            ->orderBy('created_at', 'asc');

        if ($chatId) {
            $query->where('chat_id', $chatId);
        }

        $logs = $query->get();

        $grouped = $logs->groupBy('chat_id')->map(function ($sessionLogs) {
            return $sessionLogs->map(function ($log) {
                return [
                    'user_message' => $log->user_message,
                    'ai_response'  => $log->ai_response,
                    'created_at'   => $log->created_at,
                ];
            });
        });

        return response()->json([
            'success' => true,
            'message' => 'Chat history fetched successfully',
            'data' => $grouped
        ]);
    }

    public function getChatQuota(Request $request)
    {
        try {
            $user = auth()->user();
            
            $userSub = \App\Models\UserSubscription::where('user_id', $user->id)
                ->with('plan') 
                ->latest()
                ->first();

            $limitVal = 0; 
            $isUnlimited = false;

            if ($userSub && $userSub->plan) {
                $dbLimit = $userSub->plan->limit;

                if (strtolower($dbLimit) === 'unlimited') {
                    $isUnlimited = true;
                } else {
                    $limitVal = (int)$dbLimit;
                }
            }

            $todayCount = $user->aiChatLogs()
                ->whereDate('created_at', now()->toDateString())
                ->count();

            if ($isUnlimited) {
                $remaining = 'Unlimited';
                $usageDisplay = $todayCount . '/∞';
                $percentage = 0;
                $isLimitReached = false;
            } else {
                $remaining = max(0, $limitVal - $todayCount);
                $usageDisplay = $todayCount . '/' . $limitVal;
                $percentage = ($limitVal > 0) ? ($todayCount / $limitVal) * 100 : 0;
                $isLimitReached = ($limitVal > 0) && ($todayCount >= $limitVal);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chat quota fetched successfully.',
                'data' => [
                    'plan_name'        => $userSub && $userSub->plan ? $userSub->plan->name : 'No Active Plan',
                    'total_queries'    => $isUnlimited ? 'Unlimited' : $limitVal,
                    'used_queries'     => $todayCount,
                    'remaining'        => $remaining,
                    'usage_display'    => $usageDisplay, 
                    'percentage'       => round(min(100, $percentage), 2),
                    'is_limit_reached' => $isLimitReached
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
