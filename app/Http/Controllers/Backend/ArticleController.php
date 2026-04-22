<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{

    public function index()
    {
        // with(['author', 'category'])
        $articles = Article::with(['author:id,first_name,email', 'category:id,title'])->get();

        return response()->json([
            'success' => true,
            'data'    => $articles
        ], 200);
    }

    /**
     * Get the latest 4 articles for the homepage/sidebar.
     */
    public function latestArticles()
    {
        try {
            // Ensure 'user' and 'category' match the function names in your Article model
            $articles = Article::with(['author:id,first_name,email', 'category:id,title'])
                ->where('status', 'published')
                ->latest()
                ->take(4)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Latest articles retrieved successfully.',
                'data'    => $articles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get 6 random articles personalized to the user's pregnancy status.
     */
    public function getTypeBasedArticles(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || !$user->profile) {
                return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
            }

            $profile = $user->profile;
            $pStatus = $profile->pregnancy_status;
            $dType   = $profile->delivery_type;

            $query = Article::with(['author:id,first_name,email', 'category:id,title'])
                ->where('status', 'published');

            if ($pStatus === 'pregnancy') {
                $query->where('phase_type', 'pregnancy');
            } 
            elseif ($pStatus === 'postpartum') {
                $query->where('phase_type', 'postpartum');

                if ($dType) {
                    $query->where('delivery_type', $dType);
                }
            }

            $articles = $query->latest()->take(10)->get();

            if ($articles->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No specific articles found for your current stage.',
                    'data'    => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Articles retrieved successfully.',
                'data'    => $articles
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Get the articles by category ID passed in the URL.
     */
    public function getArticlesByCategoryAll($slug)
    {
        try {
            // Use whereHas to filter articles by the category's slug column
            $articles = Article::with(['author:id,first_name,email', 'category:id,title,slug'])
                ->whereHas('category', function ($query) use ($slug) {
                    $query->where('slug', $slug); // Find the category with this specific slug
                })
                ->where('status', 'published') // Ensure only published articles are shown
                ->latest()
                ->get();

            if ($articles->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No articles found for the category: ' . $slug,
                    'data'    => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Articles retrieved for category: ' . $slug,
                'data'    => $articles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getArticlesByCategory($id)
    {
        try {
            // Fetch articles where category_id matches the ID in URL
            $articles = Article::with(['author:id,first_name,email', 'category:id,title'])
                ->where('category_id', $id)
                ->where('status', 'published') // Only show active/published articles
                ->latest()
                ->take(7)
                ->get();

            if ($articles->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No articles found for this category.',
                    'data'    => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Articles retrieved for category ID: ' . $id,
                'data'    => $articles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    //  Show All Categories with their Articles
    public function indexCategories()
    {
        $categories = ArticleCategory::with('articles')->get();

        return response()->json([
            'success' => true,
            'data'    => $categories
        ], 200);
    }
    public function storeCategory(Request $request)
    {
        try {
            $request->validate([
                'id'     => 'nullable|integer|exists:article_categories,id',
                'title'  => 'required|string|max:255',
                'status' => 'nullable|boolean',
            ]);

            $slug = Str::slug($request->title);

            $category = ArticleCategory::updateOrCreate(
                ['id' => $request->id], 
                [
                    'title'  => $request->title,
                    'slug'   => $slug,
                    'status' => $request->status ?? 1,
                ]
            );

            $message = $category->wasRecentlyCreated ? 'Category created' : 'Category updated';

            return response()->json(['success' => true, 'message' => $message, 'data' => $category]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            // 1. Validation according to your fillable fields
            $request->validate([
                'id'                => 'nullable|exists:articles,id',
                'title'             => 'required|string|max:255',
                'category_id'       => 'required|integer|exists:article_categories,id',
                'phase_type'        => 'required|string',
                'week'              => 'nullable|integer',
                'delivery_type'     => 'nullable|string',
                'short_description' => 'required|string',
                'long_description'  => 'required|string',
                'read_duration'     => 'nullable|string',
                'status'            => 'nullable|in:draft,published',
                'feature_status'    => 'nullable|boolean',
                'thumb_img'         => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
                'main_img'          => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            ]);

            // 2. Prepare Data
            $data = $request->only([
                'title', 'category_id', 'phase_type', 'week','delivery_type', 
                'short_description', 'long_description', 
                'read_duration', 'status', 'feature_status'
            ]);

            $data['slug'] = \Illuminate\Support\Str::slug($request->title);
            $data['author_id'] = auth()->id(); // Set logged-in user

            // 3. Handle Image Uploads (Optional: Updates only if new file is sent)
            if ($request->hasFile('thumb_img')) {
                $path = $request->file('thumb_img')->store('articles/thumbs', 'public');
                $data['thumb_img'] = asset('storage/' . $path);
            }
            if ($request->hasFile('main_img')) {
                $path = $request->file('main_img')->store('articles/main', 'public');
                $data['main_img'] = asset('storage/' . $path);
            }

            // 4. Update or Create Logic
            // If 'id' is in the request, it finds that ID. If not, it creates a new record.
            $article = Article::updateOrCreate(
                ['id' => $request->id], 
                $data
            );

            $message = $article->wasRecentlyCreated ? 'Article created successfully.' : 'Article updated successfully.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $article
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $article = Article::with(['author:id,first_name,email', 'category:id,title'])->find($id);

            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not found.' 
                ], 404);
            }

            return response()->json(['success' => true, 'data' => $article], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    } 

    public function destroy(Request $request, $id)
    {
        try {
            // Admin Check
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete articles.',
                ], 403);
            }

            $article = Article::find($id); //

            if (!$article) {
                return response()->json(['success' => false, 'message' => 'Article not found.'], 404);
            }

            $article->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyCategory(Request $request, $id)
    {
        try {
            // Admin Check
            if (!$request->user() || $request->user()->getRoleNames()->first() !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete article categories.',
                ], 403);
            }

            $articleCategory = ArticleCategory::find($id); //

            if (!$articleCategory) {
                return response()->json(['success' => false, 'message' => 'Article category not found.'], 404);
            }

            $articleCategory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article category deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
