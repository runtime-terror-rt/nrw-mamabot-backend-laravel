<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::latest('submitted_at')->get();
        return response()->json(['success' => true, 'data' => $messages]);
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'first_name'   => 'required|string|max:255',
                'last_name'    => 'nullable|string|max:255',
                'email'        => 'required|email|max:255',
                'phone_number' => 'nullable|string|max:20',
                'subject_type' => 'nullable|string|max:255', 
                'message'      => 'required|string',
                'attachment'   => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:10240',
            ]);

            $data = $request->all();
            $data['submitted_at'] = now();

            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('attachments', 'public');
                
                $data['attachment'] = asset('storage/' . $path);
            }

            $contact = ContactMessage::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your message and attachment have been sent.',
                'data'    => $contact
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    
    /**
     * Delete a contact message (Admin Only).
     */
   public function destroy(Request $request, $id)
    {
        try {
            // 1. Role Check (Keep this if you aren't using Role Middleware)
            $user = $request->user();
            if (!$user || !$user->hasRole('Admin')) { 
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only admins can delete messages.',
                ], 403);
            }

            // 2. Find the record in contact_messages
            $message = ContactMessage::find($id);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found.'
                ], 404);
            }

            // 3. Delete the record
            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contact message deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

}