<?php
namespace App\Http\Controllers\DoctorQA;

use App\Http\Controllers\Controller;
use App\Models\{Doctor, QaSession, QaRegistration, QaRequest};
use App\Notifications\QaRegistrationNotification;
use Illuminate\Http\Request;

class QaController extends Controller {
    

    public function getActiveDoctors() 
    {
        $doctors = Doctor::available()
        ->where('is_active', true)
        ->get();

        return response()->json(['success' => true, 'data' => $doctors]);
    }

    public function registerForSession(Request $request) 
    {
        $request->validate([
            'qa_session_id' => 'required|exists:qa_sessions,id'
        ]);

        $user = auth()->user();
        $sessionId = $request->qa_session_id;
        $session = QaSession::with('doctor')->findOrFail($sessionId);
        
        if ($session->start_time < now()->subHours(2)) { 
            return response()->json([
                'success' => false,
                'message' => 'This session has already ended.'
            ], 400);
        }

        $alreadyRegistered = QaRegistration::where('user_id', $user->id)
            ->where('qa_session_id', $sessionId)
            ->exists();

        if ($alreadyRegistered) {
            return response()->json([
                'success' => false,
                'message' => 'You have already registered for this session.'
            ], 422);
        }

        $reg = QaRegistration::create([
            'user_id' => $user->id,
            'qa_session_id' => $sessionId
        ]);

        $user->notify(new QaRegistrationNotification($session));

        return response()->json([
            'success' => true, 
            'message' => 'Registration successful! A confirmation email has been sent.'
        ]);
    }

    public function storeSession(Request $request) 
    {
        try 
        {
            $request->validate([
                'doctor_id'    => 'required|exists:doctors,id',
                'topic'        => 'required|string|max:255',
                'start_time'   => 'required|date_format:Y-m-d H:i:s', // Format: 2026-01-30 19:00:00
                'end_time'     => 'required|date_format:Y-m-d H:i:s|after:start_time',
                'meeting_link' => 'nullable|url'
            ]);

            $session = QaSession::create([
                'doctor_id'    => $request->doctor_id,
                'topic'        => $request->topic,
                'start_time'   => $request->start_time,
                'end_time'     => $request->end_time,
                'meeting_link' => $request->meeting_link
            ]);

            return response()->json([
                'success' => true,
                'message' => 'QA Session scheduled successfully!',
                'data'    => $session
            ], 201);

        } 
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSessions() 
    {
        try {
            $now = now(); 

            $sessions = QaSession::with('doctor')
                ->where('end_time', '>', $now) 
                ->orderBy('start_time', 'asc') 
                ->get();

            if ($sessions->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No active or upcoming sessions found.',
                    'data' => []
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Active sessions retrieved successfully.',
                'data' => $sessions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteSession($id) 
    {
        if (!auth()->user()->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only an Admin can delete QA sessions.'
                ], 403);
            }
        try {
            $session = QaSession::findOrFail($id);
            $session->delete();

            return response()->json([
                'success' => true,
                'message' => 'QA Session deleted successfully.'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'QA Session not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}