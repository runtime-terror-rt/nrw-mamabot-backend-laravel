<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();
        return response()->json(['success' => true, 'data' => $plans]);
    }

    public function guestSubscriptionPlan()
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();
        return response()->json(['success' => true, 'data' => $plans ?? null]);
    }

    // public function store(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'name'          => 'required|string',
    //             'price'         => 'required|numeric',
    //             'billing_cycle' => 'required|in:monthly,yearly,lifetime',
    //             'plan_type'     => 'required|in:free,premium',
    //         ]);

    //         $plan = SubscriptionPlan::updateOrCreate(
    //             ['id' => $request->id],
    //             $request->all()
    //         );

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Subscription plan saved successfully.',
    //             'data'    => $plan
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'          => 'required|string',
                'price'         => 'required|numeric',
                'billing_cycle' => 'required|in:monthly,yearly,lifetime',
                'plan_type'     => 'required|in:free,premium',
                'limit'         => 'nullable|string', 
            ]);

            $data = $request->all();

            if ($request->has('limit') && !empty($request->limit)) {
                $data['limit'] = $request->limit;
            } else {
                $data['limit'] = 'unlimited';
            }

            $plan = SubscriptionPlan::updateOrCreate(
                ['id' => $request->id],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan saved successfully.',
                'data'    => $plan
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (!auth()->user()->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only an Admin can delete community posts.'
                ], 403);
            }

            $plan = SubscriptionPlan::find($id);
            if (!$plan) {
                return response()->json(['success' => false, 'message' => 'Subscription plan not found.'], 404);
            }

            $plan->delete();
            return response()->json(['success' => true, 'message' => 'Subscription plan deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $plan = SubscriptionPlan::find($id);
            if (!$plan) {
                return response()->json(['success' => false, 'message' => 'Subscription plan not found.'], 404);
            }

            $plan->is_active = !$plan->is_active;
            $plan->save();

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan status updated successfully.',
                'data'    => [
                    'id' => $plan->id,
                    'is_active' => $plan->is_active
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
