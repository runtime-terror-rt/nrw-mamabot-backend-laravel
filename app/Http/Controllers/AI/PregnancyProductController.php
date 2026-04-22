<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProductSave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\PregnancyProduct;

class PregnancyProductController extends Controller
{

 // GET: fetch products from external API without saving
    public function fetch(Request $request)
{
    try {
        $request->validate(['pregnancy_week' => 'nullable|integer|min:1|max:40']);
        $pregnancyWeek = $request->pregnancy_week ?? 1;

        $response = Http::get('https://ai.mamabot.de/api/v1/pregnancy/products', [
            'pregnancy_week' => $pregnancyWeek
        ]);

        if ($response->failed()) {
            return response()->json(['success' => false, 'message' => 'API failed'], 500);
        }

        $apiData = $response->json();
        $finalProducts = [];

        foreach ($apiData['products'] as $item) {
            $product = AffiliateProductSave::firstOrCreate(
                ['affiliate_link' => $item['affiliate_link']], 
                [
                    'title' => $item['title'],
                    'category' => $item['category'],
                    'reason' => $item['reason'],
                    'image_url' => $item['image_url'] ?? null,
                ]
            );

            $item['id'] = $product->id; 
            
            $item['is_saved'] = \App\Models\SavedItem::where('user_id', auth()->id())
                ->where('savable_id', $product->id)
                ->where('savable_type', AffiliateProductSave::class)
                ->exists();

            $finalProducts[] = $item;
        }

        $apiData['products'] = $finalProducts;

        return response()->json([
            'success' => true,
            'data' => $apiData
        ]);

    } catch (\Throwable $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

    
    public function index()
    {
        try {
            $user = auth()->user();
            $records = $user->pregnancyProducts()->latest()->get();

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
                'pregnancy_week' => 'nullable|integer|min:1|max:40',
            ]);

            // default values
            $pregnancyWeek = $request->pregnancy_week 
                ?? $profile->current_week 
                ?? 1;

            // External API call
            $response = Http::get(
                'https://ai.mamabot.de/api/v1/pregnancy/products',
                [
                    'pregnancy_week' => $pregnancyWeek
                ]
            );

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product API failed',
                    'error' => $response->body()
                ], $response->status());
            }

            $data = $response->json();

            // save in database (phase + products)
            $record = $user->pregnancyProducts()->create([
                'mode' => $data['mode'] ?? null,
                'pregnancy_week' => $pregnancyWeek,
                'phase' => $data['phase'] ?? null,
                'products' => $data['products'] ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Products generated & saved successfully',
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
