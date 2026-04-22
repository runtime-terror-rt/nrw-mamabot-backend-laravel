<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\MamabotSupport;
use Illuminate\Http\Request;

class MamabotSupportController extends Controller
{
    public function storeSupport(Request $request)
    {
        try {
            $request->validate([
                'icon'  => 'required|string',
                'title' => 'required|string',
            ]);

            $support = MamabotSupport::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Support item added successfully.',
                'data'    => $support
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
            ], 422);
        }
    }
}
