<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Notifications\UserRegistered;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use App\Jobs\SendOtpEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SignUpController extends Controller
{
    public function register(Request $request)
    {
        try {
            DB::beginTransaction();

            // ✅ Validation with custom messages
            $request->validate([
                'email' => 'required|email',
                'first_name' => 'required',
                'last_name' => 'required',
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)->letters()->mixedCase()->numbers()->symbols()
                ],
                'phone' => 'nullable|string|max:20',
                'accepted_terms' => 'required|boolean',
                'consent_health_data' => 'required|boolean',
                'newsletter_opt_in' => 'boolean|boolean',
                'accepted_withdrawal_waiver' => 'required|boolean',
                'accepted_auto_renewal' => 'required|boolean',

            ], [
                'email.required' => 'Email field is required.',
                'email.email' => 'Please enter a valid email address.',
                'password.required' => 'Password is required.',
                'password.confirmed' => 'Password confirmation does not match.',

            ]);

            // Check if email already exists
            if (User::where('email', $request->email)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email is already registered.'
                ], 409);
            }

            // Check if role exists
            if (!\Spatie\Permission\Models\Role::where('name', 'User')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found.'
                ], 404);
            }

            // Create user
            $user = User::create([
                'first_name'   => $request->first_name,
                'last_name'    => $request->last_name,
                'email'        => $request->email,
                'phone'        => $request->phone,  
                'password'     => Hash::make($request->password),
                'accepted_terms' => $request->accepted_terms ?? false,
                'consent_health_data' => $request->consent_health_data ?? false,
                'newsletter_opt_in' => $request->newsletter_opt_in ?? false,
                'accepted_withdrawal_waiver' => $request->accepted_withdrawal_waiver ?? false,
                'accepted_auto_renewal' => $request->accepted_auto_renewal ?? false,
                'chat_id' => Str::uuid()->toString(),
            ]);

            // Assign role
            $user->assignRole('User');

            //trigger Notification
            $user->notify(new UserRegistered());

            // Commit if everything succeeds
             DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful.',
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ Show first validation error as message
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422);

        } catch (\Exception $e) {
            // Rollback if any error occurs
            DB::rollBack();

            Log::error('Registration Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        try {
            // ✅ Validation with custom messages
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|digits:6',
            ], [
                'email.required' => 'Email field is required.',
                'email.email' => 'Please enter a valid email address.',
                'otp.required' => 'OTP field is required.',
                'otp.digits' => 'OTP must be 6 digits.',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email not found.'
                ], 404);
            }

            if (!$user->otp || !$user->otp_expire_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'No OTP found. Please request a new OTP.'
                ], 400);
            }

            $otpExpire = Carbon::parse($user->otp_expire_at);

            if ($otpExpire->lt(Carbon::now())) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP has expired. Please request a new one.'
                ], 400);
            }

            if ((string)$user->otp !== (string)$request->otp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid OTP. Please try again.'
                ], 400);
            }

            // OTP is valid → verify email and clear OTP
            $user->update([
                'email_verified_at' => $user->email_verified_at ?? Carbon::now(),
                'otp' => null,
                'otp_expire_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully.',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // ✅ Show first validation error as message
            $firstError = collect($e->errors())->flatten()->first();

            return response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422);

        } catch (\Exception $e) {
            Log::error('Verify OTP Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'OTP verification failed. Please try again.',
            ], 500);
        }
    }

}
