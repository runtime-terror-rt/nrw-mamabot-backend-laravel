<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\WebSetting;
use Illuminate\Http\Request;

class WebSettingController extends Controller
{
    // Shobai settings dekhte parbe
    public function index()
    {
        $settings = WebSetting::first();
        return response()->json(['success' => true, 'data' => $settings], 200);
    }

    public function store(Request $request)
    {
        try {
            // 1. Admin Authorization Check
            if (!$request->user() || !$request->user()->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can update settings.',
                ], 403);
            }

            // 2. Validation Logic (Images set to 'image' type)
            $request->validate([
                'site_name'            => 'nullable|string',
                'logo'                 => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
                'favicon'              => 'nullable|image|mimes:png,jpg,jpeg,ico|max:1024',
                'footer_description'   => 'nullable|string',
                'copyright_text'       => 'nullable|string',
                'footer_text'          => 'nullable|string',
                'insta_link'           => 'nullable|url',
                'fb_link'              => 'nullable|url',
                'tiktok_link'          => 'nullable|url',
                'mail_1'               => 'nullable|email',
                'mail_2'               => 'nullable|email',
                'working_hour'         => 'nullable|string',
                'headquarter_address' => 'nullable|string',
            ]);

            // 3. Fetch existing settings (Always ID 1)
            $settings = WebSetting::find(1);
            $data = $request->except(['logo', 'favicon']);

            // 4. Handle Logo Upload
            if ($request->hasFile('logo')) {
                // Delete old logo if it exists
                if ($settings && $settings->logo) {
                    $oldLogoPath = str_replace(asset('storage/'), '', $settings->logo);
                    \Storage::disk('public')->delete($oldLogoPath);
                }
                // Store new logo and save full URL
                $logoPath = $request->file('logo')->store('settings', 'public');
                $data['logo'] = asset('storage/' . $logoPath);
            }

            // 5. Handle Favicon Upload
            if ($request->hasFile('favicon')) {
                // Delete old favicon if it exists
                if ($settings && $settings->favicon) {
                    $oldFavPath = str_replace(asset('storage/'), '', $settings->favicon);
                    \Storage::disk('public')->delete($oldFavPath);
                }
                // Store new favicon and save full URL
                $favPath = $request->file('favicon')->store('settings', 'public');
                $data['favicon'] = asset('storage/' . $favPath);
            }

            // 6. Save or Update (Locked to ID 1)
            $settings = WebSetting::updateOrCreate(
                ['id' => 1], 
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Web settings updated successfully.',
                'data'    => $settings
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}