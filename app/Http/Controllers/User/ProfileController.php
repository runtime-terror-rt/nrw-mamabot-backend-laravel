<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Profile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Get all profiles (Admin View).
     */
    public function index()
    {
        try {
            $profiles = Profile::with(['user.subscription.plan'])->get();

            $data = $profiles->map(function ($profile) {
                $profileData = $profile->toArray();

                if ($profile->user) {
                    $userData = $profile->user->toArray();

                    $userData['subscription_plan'] = $profile->user->subscription->plan->name ?? null;
                    $userData['plan_id'] = $profile->user->subscription->plan->plan_id ?? null;

                    $profileData['user'] = $userData;
                }

                return $profileData;
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or Update Profile (Ensures 1 user = 1 profile ID).
     */
    public function storeOrUpdate(Request $request)
    {
        try {
            $user = Auth::user();
            $existingProfile = Profile::where('user_id', $user->id)->first();

            $validatedData = $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'phone' => 'nullable|string',
                'pregnancy_status' => 'required|in:pregnancy,postpartum',
                'address' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'due_date' => 'nullable|date',
                'current_week' => 'nullable|integer|min:1|max:42',
                'baby_nickname' => 'nullable|string',
                'delivery_type' => 'nullable|required_if:pregnancy_status,postpartum|in:vaginal_delivery,cesarean_delivery',
                'postpartum_day' => 'nullable|required_if:pregnancy_status,postpartum|integer',
                'language' => 'nullable|string',
                'doctor_name' => 'nullable|string',
                'hospital_name' => 'nullable|string',
                'isKickRemind' => 'nullable|boolean',
                'isHydrationGoal' => 'nullable|boolean',
                'isWeightTrack' => 'nullable|boolean',
                'AI_tone' => 'nullable|string',
                'support_type' => 'nullable|string',
                'product_interest' => 'nullable|string',
                'dietary_preferences' => 'nullable|string',
                'two_factor_auth' => 'nullable|boolean',
            ]);

            if ($existingProfile && $existingProfile->pregnancy_status === 'postpartum') {
                if ($request->pregnancy_status === 'pregnancy') {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot change status back to pregnancy once it is set to postpartum.'
                    ], 422);
                }
                $validatedData['pregnancy_status'] = 'postpartum';
            }

            if ($request->pregnancy_status === 'pregnancy') {
                $validatedData['delivery_type'] = null;
                $validatedData['postpartum_day'] = 0;
            } else {
                $validatedData['current_week'] = null;
            }

            $user->update([
                'first_name' => $request->first_name ?? $user->first_name,
                'last_name' => $request->last_name ?? $user->last_name,
                'phone' => $request->phone ?? $user->phone,
            ]);

            if ($request->hasFile('image')) {
                $data['image'] = asset('storage/' . $request->file('image')->store('profiles', 'public'));
            }

            $profile = Profile::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($validatedData, $data ?? [])    
            );

            return response()->json([
                'success' => true,
                'message' => $profile->wasRecentlyCreated ? 'Profile created' : 'Profile updated',
                'user' => $user->load('profile'),
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show logged-in user's profile.
     */
    public function showMyProfile()
    {
        try {
            $profile = Profile::with(['user.subscription.plan'])->where('user_id', Auth::id())->first();

            if (!$profile) {
                return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
            }
            $data = $profile->toArray();

            if ($profile->user) {
                $userData = $profile->user->toArray();

                $userData['subscription_plan'] = $profile->user->subscription->plan->name ?? null;
                $userData['plan_id'] = $profile->user->subscription->plan->id ?? null;

                $data['user'] = $userData;
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin delete profile.
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $profile = Profile::find($id);
        if ($profile) {
            $profile->delete();
            return response()->json(['success' => true, 'message' => 'Deleted successfully']);
        }

        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    public function uploadDocument(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }

            // Validation
            $request->validate([
                'document' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120', // Max 5MB
            ]);

            if ($request->hasFile('document')) {
                $profile = $user->profile;

                if ($profile->pregnancy_document) {
                    $oldPath = str_replace(asset('storage/'), '', $profile->pregnancy_document);
                    \Storage::disk('public')->delete($oldPath);
                }

                // File Store
                $path = $request->file('document')->store('profile/documents', 'public');
                $fileUrl = asset('storage/' . $path);

                // Database Update
                $profile->update([
                    'pregnancy_document' => $fileUrl
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Document uploaded successfully.',
                    'document_url' => $fileUrl
                ]);
            }

            return response()->json(['success' => false, 'message' => 'File not provided.'], 400);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteUser()
    {
        try {
            $user = Auth::user();
            if ($user) {

                $user->delete();
                return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
            }

        } catch (\Exception $e) {
            Log::error('Delete User Error Message:' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function changeUserPassword(Request $request)
    {
        try {

            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:6|confirmed',
                'new_password_confirmation' => 'required|string|min:6'
            ]);

            $user = Auth::user();

            // ✅ Check current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.'
                ], 400);
            }
            // ✅ Update password
            $user->password = Hash::make($request->new_password);
            $user->save();
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully.'
            ], 200);


        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);

        } catch (\Exception $e) {

            Log::error('Change Password Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while changing the password.'], 500);
        }
    }
}
