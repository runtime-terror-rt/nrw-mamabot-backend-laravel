<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{

    /**
    * List all subscribers for Admin view.
    */
    public function index()
    {
        $subscribers = NewsletterSubscriber::latest('subscribed_at')->get();
        return response()->json(['success' => true, 'data' => $subscribers]);
    }


    /**
     * Store a new subscription.
     */
    public function subscribe(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'nullable|string|max:255',
                'email'      => 'required|email|unique:newsletter_subscribers,email', //
                'locale'     => 'nullable|string|max:10',
                'source'     => 'nullable|string'
            ]);

            $subscriber = NewsletterSubscriber::create([
                'first_name'    => $request->first_name,
                'email'         => $request->email,
                'subscribed_at' => now(), //
                'is_active'     => true,
                'locale'        => $request->locale ?? 'en',
                'source'        => $request->source ?? 'web'
            ]);

            return response()->json(['success' => true, 'data' => $subscriber], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a subscriber by ID.
     */
    public function destroy($id)
    {
        try {
            $subscriber = NewsletterSubscriber::find($id);

            if (!$subscriber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscriber not found.'
                ], 404);
            }

            $subscriber->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subscriber deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System Error: ' . $e->getMessage()
            ], 500);
        }
    }                  
}