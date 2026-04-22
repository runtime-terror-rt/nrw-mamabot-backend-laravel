<?php

namespace App\Http\Controllers\RelaxationAudio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RelaxationAudio;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RelaxationAudioController extends Controller
{
    // Fetch all audio records
public function index()
    {
        try {
            $audios = \DB::table('relaxation_audio')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $audios
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }   

    public function userListen()
    {
        try {
            $audio = RelaxationAudio::inRandomOrder()->first(); 

            if (!$audio) {
                return response()->json([
                    'success' => false,
                    'message' => 'No audio found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $audio
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Something went wrong!' 
            ], 500);
        }
    }


    public function uploadAudio(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'audio_url' => 'required|mimes:mp3,wav,ogg|max:10240', 
            ]);

            if ($request->hasFile('audio_url')) {
                $file = $request->file('audio_url');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('audio_url', $fileName, 'public');

                $audio = \DB::table('relaxation_audio')->insertGetId([
                    'title' => $request->title,
                    'audio_url' => asset('storage/' . $filePath),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Audio uploaded successfully!',
                    'data' => ['id' => $audio, 'url' => asset('storage/' . $filePath)]
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Fetch a single audio record by ID
    public function show($id)
    {
        $audio = \DB::table('relaxation_audio')->where('id', $id)->first();

        if (!$audio) {
            return response()->json(['success' => false, 'message' => 'Audio not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $audio], 200);
    }
    
    public function destroy($id)
    {
        if (!auth()->user()->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only admins can delete audio files.',
            ], 403);
        }   

        try {
            $audio = RelaxationAudio::find($id);
            if (!$audio) {
                return response()->json(['success' => false, 'message' => 'Audio not found'], 404);
            }

            // Delete the audio file from storage
            $filePath = str_replace(asset('storage/'), '', $audio->audio_url);
            Storage::disk('public')->delete($filePath);

            // Delete the database record
            $audio->delete();

            return response()->json(['success' => true, 'message' => 'Audio deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
