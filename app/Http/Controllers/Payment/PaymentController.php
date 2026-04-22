<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\UserPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $payments = UserPayment::get()->map(function ($payment) {

                return [
                    'id' => $payment->id,
                    'provider' => $payment->payment_provider ?? null,
                    'card' => $payment->last_four_digit ?? null,
                    'invoice' => $payment->invoice_number ?? null,
                    'invoice_url' => $payment->download_url ?? null,
                    'currency' => $payment->currency ?? null,
                    'amount' => $payment->amount ?? null,
                    'status' => $payment->status ?? null,
                    'issued_at' => $payment->issued_at ?? null,
                ];
            });


            return response()->json([
                'success' => true,
                'message' => 'Payments retrieved successfully.',
                'data' => $payments
            ]);

        } catch (\Exception $e) {
            Log::info('fetch payments error: ' . $e->getMessage());


            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);

        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getPaymentInfoByUser()
    {
        try {

            $user = auth()->user();

            $paymentInfo = UserPayment::where('user_id', $user->id)->first();

            if ($paymentInfo) {
                $paymentData = [
                    'id' => $paymentInfo->id,
                    'invoice' => $paymentInfo->invoice_number ?? null,
                    'amount' => $paymentInfo->amount,
                    'currency' => $paymentInfo->currency,
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Payment info retrieved successfully.',
                    'data' => $paymentData
                ]);
            }
        } catch (\Exception $e) {
            Log::info('getPaymentInfoByUser error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
