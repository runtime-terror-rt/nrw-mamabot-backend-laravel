<?php

namespace App\Http\Controllers\DoctorQA;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function getActiveDoctors()
    {
        try {
            $doctors = Doctor::available()
                ->select('id', 'name', 'specialty', 'image', 'is_active')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Available doctors retrieved successfully.',
                'data' => $doctors
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve doctors: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeDoctor(Request $request) 
    {
        $request->validate([
            'name' => 'required', 
            'specialty' => 'required', 
            'image' => 'nullable|image']);
        $data = $request->all();


        if ($request->hasFile('image')) {
            $data['image'] = asset('storage/' . $request->file('image')->store('doctors', 'public'));
        }
        return response()->json(['success' => true, 'data' => Doctor::create($data)]);
    }

    public function updateDoctor(Request $request, $id) 
    {
        $doctor = Doctor::findOrFail($id);
        $request->validate([
            'name' => 'required', 
            'specialty' => 'required', 
            'image' => 'nullable|image']);
        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = asset('storage/' . $request->file('image')->store('doctors', 'public'));
        }
        $doctor->update($data);
        return response()->json(['success' => true, 'data' => $doctor]);
    }

    public function destroy(Doctor $doctor)
    {
        try {
            $doctor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Doctor deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete doctor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleActiveStatus(Doctor $doctor)
    {
        try {
            $doctor->is_active = !$doctor->is_active;
            $doctor->save();

            return response()->json([
                'success' => true,
                'message' => 'Doctor status updated successfully.',
                'data' => ['is_active' => $doctor->is_active]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update doctor status: ' . $e->getMessage()
            ], 500);
        }
    }
}
