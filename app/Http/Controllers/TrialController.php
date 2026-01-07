<?php

namespace App\Http\Controllers;

use App\Models\Trial;
use Illuminate\Http\Request;

class TrialController extends Controller
{
    const TRIAL_DAYS = 30;

    /**
     * Start a new trial
     */
    public function start(Request $request)
    {
        $user = $request->user();

        // Already subscribed? No trial needed
        if ($user->hasActiveSubscription()) {
            return response()->json([
                'message' => 'You already have an active subscription.',
            ], 400);
        }

        // Check if trial exists
        if ($user->trial) {
            // If trial exists but expired, allow starting a new one
            if (!$user->trial->active) {
                $user->trial->delete(); // Remove old expired trial
            } else {
                return response()->json([
                    'message' => 'Trial already started.',
                ], 400);
            }
        }

        // Create new trial
        $trial = Trial::create([
            'user_id'    => $user->id,
            'started_at' => now(),
            'ended_at'   => now()->addDays(self::TRIAL_DAYS),
            'active'     => 1,
        ]);

        return response()->json([
            'message'       => 'Free trial started successfully.',
            'trial_active'  => true,
            'trial_ends_at' => $trial->ended_at,
        ]);
    }

    /**
     * Check trial status
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $trial = $user->trial;

        $trial_active  = false;
        $trial_ends_at = null;

        if ($trial) {
            $trial_ends_at = $trial->ended_at;

            if ($trial->active && now()->lessThanOrEqualTo($trial_ends_at)) {
                $trial_active = true;
            } elseif ($trial->active && now()->greaterThan($trial_ends_at)) {
                // Expire trial automatically
                $trial->active = 0;
                $trial->save();
            }
        }

        return response()->json([
            'has_active_subscription' => $user->hasActiveSubscription(),
            'trial_active'            => $trial_active,
            'trial_ends_at'           => $trial_ends_at,
        ]);
    }
}
