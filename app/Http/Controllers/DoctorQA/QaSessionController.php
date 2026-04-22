<?php

namespace App\Http\Controllers\DoctorQA;

use App\Http\Controllers\Controller;
use App\Models\QaRegistration;
use Illuminate\Http\Request;

class QaSessionController extends Controller
{
    public function registerForSession(Request $request)
    {
        try {
            $request->validate([
                'qa_session_id' => 'required|exists:qa_sessions,id'
            ]);

            $registration = QaRegistration::firstOrCreate([
                'user_id' => auth()->id(), 
                'qa_session_id' => $request->qa_session_id
            ]);

            $message = $registration->wasRecentlyCreated 
                ? 'Registration confirmed! Profile will be hidden during the session.' 
                : 'You are already registered for this session.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $registration
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
