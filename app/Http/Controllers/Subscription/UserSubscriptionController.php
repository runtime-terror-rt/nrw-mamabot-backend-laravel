<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserPayment;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;

class UserSubscriptionController extends Controller
{
    public function subscriptionCheckout(Request $request)
    {
        try {

            $request->validate([
                'plan_id' => 'required',
            ]);

            Stripe::setApiKey(env('STRIPE_SECRET'));

            $user = $request->user();

            $exists = UserSubscription::where('user_id', $user->id)
                ->whereIn('status', ['active'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a subscription in progress.',
                ], 409);
            }


            $planId = $request->input('plan_id'); // Find subscription plan

            $plan = SubscriptionPlan::findOrFail($planId);

            // Create a subscription record in DB
            $subscription = UserSubscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id' => $plan->id,
                    'started_at' => null,
                    'expires_at' => null, // adjust based on plan duration
                    'is_active' => false,
                    'auto_renew' => false,
                    'status' => 'pending',
                ]);

            //Payment Gateway Starts
            $stripe = new StripeClient(config('services.stripe.secret'));

            $session = $stripe->checkout->sessions->create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Package For Mamabot',
                        ],
                        'unit_amount' => (int)($plan->price * 100),
                        // 🔥 REQUIRED for subscriptions
                        'recurring' => [
                            'interval' => 'month', // or 'year'
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',

                'metadata' => [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                ],

                // ✅ IMPORTANT: api + v1 prefix
                'success_url' => url('/api/v1/subscription/success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => url('/api/v1/subscription/cancel'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subscription Checkout created successfully.',
                'session_id' => $session->id,
                'amount' => $plan->price,
                'url' => $session->url,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Subscription Checkout Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create Booking.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
//      $endpointSecret = env('OUR_STRIPE_WEBHOOK_SECRET');
        $endpointSecret = "whsec_yrypdD5OIxMsezvZFJiU78YyJy1fWSJp";


        echo "End point secreat = " . $endpointSecret;
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            echo "response event: " . $event;
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            // ✅ Fires immediately after checkout completes
            case 'checkout.session.completed':
                $session = $event->data->object;

                $userId = $session->metadata->user_id ?? null;
                $subscriptionId = $session->metadata->subscription_id ?? null;
                $stripeSubscriptionId = $session->subscription ?? null;

                if ($userId && $subscriptionId) {
                    UserSubscription::where('id', $subscriptionId)->update([
                        'started_at' => now(),
                        'is_active' => true,
                        'auto_renew' => true,
                        'stripe_subscription_id' => $stripeSubscriptionId,
                    ]);

                }

                Log::info("Checkout completed for session {$session->id}");
                break;

            // ✅ Subscription created in Stripe
            case 'customer.subscription.created':

                $subscription = $event->data->object;

                Log::info("Stripe subscription created {$subscription->id}");

                break;

            // ✅ Subscription updated (renewal, plan change, cancellation)
            case 'customer.subscription.updated':

                $subscription = $event->data->object;

                Log::info("Stripe subscription updated {$subscription->id}");

                break;

            // ✅ Payment succeeded (recurring invoice)
            case 'invoice.payment_succeeded':

                $invoice = $event->data->object;

                Log::info(" invoice  {$invoice}");

//              $stripeSubscriptionId = $invoice->parent->subscription_details->subscription ?? null;

                $stripeSubscriptionId = $invoice->subscription ?? ($invoice->parent->subscription_details->subscription ?? null) ?? ($invoice->lines->data[0]->parent->subscription_item_details->subscription ?? null);

                Log::info("Invoice payment  {$stripeSubscriptionId}");

                $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();

                if ($subscription) {

                    $subscription->update([
                        'expires_at' => now()->addMonth(),
                        'status' => 'active',
                    ]);

                    UserPayment::updateOrCreate(['invoice_number' => $invoice->id], // idempotency: avoid duplicates
                        [
                            'user_id' => $subscription->user_id,
                            'subscription_id' => $subscription->id,
                            'payment_provider' => 'stripe',
                            'amount' => $invoice->amount_paid / 100, // Stripe sends cents
                            'currency' => strtoupper($invoice->currency),
                            'status' => 'paid',
                            'issued_at' => now(),
                            'download_url' => $invoice->hosted_invoice_url ?? null,
                        ]);

                }

                Log::info("Invoice payment succeeded {$invoice->id}");

                break;

            // ✅ Payment failed
            case 'invoice.payment_failed':

                $invoice = $event->data->object;

                $stripeSubscriptionId = $invoice->subscription ?? ($invoice->parent->subscription_details->subscription ?? null) ?? ($invoice->lines->data[0]->parent->subscription_item_details->subscription ?? null);

                $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();


                if ($subscription) {
                    $subscription->update([
                        'status' => 'pending',
                        'is_active' => false,
                    ]);
                }

                Log::warning("Payment failed for invoice {$invoice->id}");
                break;

            default:
                Log::info("Unhandled event type: {$event->type}");
        }

        return response('Webhook handled', 200);
    }


    public function subscriptionSuccess()
    {
//        return response()->json([
//            'success' => true,
//            'message' => 'Payment Successful.',
//        ]);

        return Redirect::away('https://mamabot.de/paymentSuccess');
    }

    public function subscriptionCancel()
    {
        return response()->json([
            'success' => false,
            'message' => 'Payment Cancelled by User.',
        ]);
    }

    public function checkSubscriptionByUser()
    {
        $user = auth()->user();

        $payment = UserPayment::where('user_id',$user->id)->first();
        $invoice = $payment->invoice_number ?? null;
        $currency = $payment->currency ?? 'USD';
        $invoice_link = $payment->download_url ?? null;
        $last_four_digits = $payment->last_four_digit ?? 0000;

        return response()->json([
            'success' => true,
            'message' => 'User Subscription Information.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'role' => $user->getRoleNames()->first() ?? null,
                    'subscription Plan' => $user->subscription->plan->name ?? null,
                    'plan id' => $user->subscription->plan->id ?? null,
                    'invoice' => $invoice,
                    'currency' => $currency,
                    'invoice_link' => $invoice_link,
                    'last_four_digits' => $last_four_digits,
                    //'is_first_time' => $user->is_first_time,
                    //'subscription_type' => $user->subscription_type,
                ],

            ]
        ], 200);

    }

    public function getSubscribers()
    {
        try {
            $subscribers = UserSubscription::select('id','user_id','plan_id','started_at','expires_at','auto_renew','is_active','status')
                ->with('user:id,first_name,last_name,email','plan:id,name,price,billing_cycle')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Subscribers Information.',
                'data' => $subscribers,
            ]);
        }
        catch (\Exception $e) {

            Log::error('Subscribers Information Error' . $e->getMessage());

            return response()->json([
                'success' => false,
//                'message' => 'Something went wrong.',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
