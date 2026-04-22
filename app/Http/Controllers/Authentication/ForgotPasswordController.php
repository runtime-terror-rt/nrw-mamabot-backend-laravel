<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Jobs\SendOtpEmail;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        try {
            // ✅ Validation with custom message
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

            if (!$user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not verified. Please verify your email first.'
                ], 403);
            }

            // ✅ Generate 6-digit OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->update([
                'otp' => $otp,
                'otp_expire_at' => now()->addMinutes(5),
            ]);

            SendOtpEmail::dispatch($user->id, 'forgot', $otp);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email for password reset.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ Show first validation error as message
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Forgot Password Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ], 500);
        }
    }


    public function resetPassword(Request $request)
    {
        try {
            // ✅ Validation with custom message
            $request->validate([
                'email' => 'required|email',
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)->letters()->mixedCase()->numbers()->symbols()
                ],
            ], [
                'email.required' => 'Email field is required.',
                'email.email' => 'Please enter a valid email address.',
                'password.required' => 'Password is required.',
                'password.confirmed' => 'Password confirmation does not match.',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not found.'
                ], 404);
            }

            if (!$user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not verified. Please verify your email first.'
                ], 403);
            }

            $user->update([
                'password' => Hash::make($request->password),
                'otp' => null,
                'otp_expire_at' => null,
            ]);

            SendOtpEmail::dispatch($user->id, 'reset_success');

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ Show first validation error as message
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Reset Password Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password. Please try again.',
            ], 500);
        }
    }


}
