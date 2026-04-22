<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProductSave;
use App\Models\Article;
use App\Models\CommunityPost;
use App\Models\SavedItem;
use Illuminate\Http\Request;
use App\Models\Product;

class SavedItemsController extends Controller
{
    /**
     * Retrieve all saved items based on the selected tab in the UI.
     * Matches the tabs: All, Products, Community Posts, Articles.
     */
    public function getSavedItems()
    {
        $userId = auth()->id(); // Get authenticated user

        // Fetch all items with their polymorphic details
        $savedItems = SavedItem::where('user_id', $userId)
            ->with('savable') 
            ->latest()
            ->get();

        // Map your class names to a priority order for sorting
        // Priority: 1 (Product), 2 (Article), 3 (Post)
        $sortedItems = $savedItems->sortBy(function ($item) {
            return match ($item->savable_type) {
                AffiliateProductSave::class => 1,
                Article::class => 2,
                CommunityPost::class => 3,
                default => 4,
            };
        })->values(); // Reset array keys after sorting

        return response()->json([
            'success' => true, 
            'data' => $sortedItems
        ], 200);
    }

    /**
     * Toggle the save status of an item. 
     * If it exists, remove it; if not, create a new record.
     */
    public function toggleSave(Request $request)
    {
        $userId = auth()->id();
        $id = $request->item_id;
        
        // Map 'article' to its full class name
        $typeMap = [
            'article' => \App\Models\Article::class,
            'post'    => \App\Models\CommunityPost::class,
            'product' => AffiliateProductSave::class,
        ];

        $modelType = $typeMap[$request->item_type] ?? null;

        if (!$modelType) {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }

        $existing = SavedItem::where('user_id', $userId)
            ->where('savable_id', $id)
            ->where('savable_type', $modelType)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['success' => true, 'is_saved' => false]);
        }

        SavedItem::create([
            'user_id' => $userId,
            'savable_id' => $id,
            'savable_type' => $modelType,
        ]);

        return response()->json(['success' => true, 'is_saved' => true]);
    }

}
