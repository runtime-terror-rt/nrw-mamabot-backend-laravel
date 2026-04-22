<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendOtpEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;


class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            // ✅ Validation
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ], [
                'email.required' => 'Email field is required.',
                'email.email' => 'Please enter a valid email address.',
                'password.required' => 'Password field is required.',
                'password.string' => 'Password must be a string.',
                'password.min' => 'Password must be at least 6 characters.',
            ]);

            // ✅ Find user by email
            $user = User::where('email', $request->email)->first();

            // ✅ Check credentials
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.',
                ], 401);
            }

            // ✅ Check email verification
            if (!$user->email_verified_at) {

                // Generate OTP (6-digit)
                $otp = random_int(100000, 999999);

                $user->update([
                    'otp' => $otp,
                    'otp_expire_at' => Carbon::now()->addMinutes(5),
                ]);

                // Send OTP email
                SendOtpEmail::dispatch($user->id, 'verify', $otp);

                return response()->json([
                    'success' => false,
                    'message' => 'OTP Send, Please verify your email before logging in.',
                ], 403);
            }

            //Track device
            UserDevice::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_name' => $request->header('User-Agent'),
                ],

                ['device_type' => 'api-token', 'last_active_at' => now(), 'is_active' => true,]);

            // ✅ Generate token
            $token = $user->createToken('auth_token_' . $user->id)->plainTextToken;

            // ✅ Return success response
            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'role' => $user->getRoleNames()->first() ?? null,
                        'subscription Plan' => $user->subscription->plan->name ?? null,
                        'plan id' => $user->subscription->plan->id ?? null,
                        //'is_first_time' => $user->is_first_time,
                        //'subscription_type' => $user->subscription_type,
                    ],

                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ Validation error: first message only
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }


    public function resendOtp(Request $request)
    {
        try {
            // ✅ Validation with custom message format
            $request->validate([
                'email' => 'required|email',
            ], [
                'email.required' => 'Email field is required.',
                'email.email' => 'Please enter a valid email address.',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not found.'
                ], 404);
            }

//        if (!$user->email_verified_at) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Email not verified yet. Please verify your email first.'
//            ], 403);
//        }

            // ✅ Generate OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->update([
                'otp' => $otp,
                'otp_expire_at' => Carbon::now()->addMinutes(5),
            ]);

            SendOtpEmail::dispatch($user->id, 'verify', $otp);

            return response()->json([
                'success' => true,
                'message' => 'A new OTP has been sent to your email.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ Show first validation error as message
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Resend OTP Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP. Please try again.',
            ], 500);
        }
    }

    /**
     * Logout user (current token)
     */
    public function logout(Request $request)
    {
        try {
            UserDevice::where('user_id', $request->user()->id) ->update(['is_active' => false]);

            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Logout Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout. Please try again.',
            ], 500);
        }
    }
}
