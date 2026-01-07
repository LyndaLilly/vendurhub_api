<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    // List all subscriptions for the logged-in user
    public function index(Request $request)
    {
        $subscriptions = $request->user()->subscriptions()->latest()->get();

        return response()->json($subscriptions);
    }

    // Cancel a subscription
    public function cancel(Request $request, $id)
    {
        $subscription = Subscription::where('id', $id)
                                    ->where('user_id', $request->user()->id)
                                    ->firstOrFail();

        $subscription->update(['is_active' => false, 'expired_at' => Carbon::now()]);

        return response()->json(['message' => 'Subscription canceled successfully.']);
    }

    // Reactivate subscription manually (optional)
    public function reactivate(Request $request, $id)
    {
        $subscription = Subscription::where('id', $id)
                                    ->where('user_id', $request->user()->id)
                                    ->firstOrFail();

        $subscription->update([
            'is_active'  => true,
            'starts_at'  => Carbon::now(),
            // 'expired_at' => Carbon::now()->addMonth(), // or plan-based logic
        ]);

        return response()->json(['message' => 'Subscription reactivated successfully.']);
    }
}
