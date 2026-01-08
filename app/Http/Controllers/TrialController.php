<?php
namespace App\Http\Controllers;

use App\Models\Trial;
use App\Mail\TrialExpiredMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TrialController extends Controller
{
    const TRIAL_DAYS = 30;
    
    public function start(Request $request)
    {
        $user = $request->user();
        
        $this->checkExpiredTrials();

        if ($user->trial) {
            return response()->json([
                'message'       => 'You have already used your free trial.',
                'trial_active'  => $user->trial->active,
                'trial_ends_at' => $user->trial->ended_at,
                'expired_at'    => $user->trial->expired_at,
            ], 400);
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

   
    public function status(Request $request)
    {
        $user = $request->user();

        // Expire any overdue trials
        $this->checkExpiredTrials();

        $trial         = $user->trial;
        $trial_active  = false;
        $trial_ends_at = null;

        if ($trial) {
            $trial_ends_at = $trial->ended_at;

            if ($trial->active && now()->lessThanOrEqualTo($trial_ends_at)) {
                $trial_active = true;
            } elseif ($trial->active && now()->greaterThan($trial_ends_at)) {
                // Expire this user's trial
                $trial->active     = 0;
                $trial->expired_at = now();
                $trial->save();
            }
        }

        return response()->json([
            'has_active_subscription' => $user->hasActiveSubscription(),
            'trial_active'            => $trial_active,
            'trial_ends_at'           => $trial_ends_at,
            'expired_at'              => $trial?->expired_at,
        ]);
    }



protected function checkExpiredTrials()
{
    $expiredTrials = Trial::where('active', true)
        ->where('ended_at', '<', now())
        ->get();

    foreach ($expiredTrials as $trial) {
        $trial->active     = 0;
        $trial->expired_at = now();
        $trial->save();

        // Send email
        Mail::to($trial->user->email)->send(new TrialExpiredMail($trial->user));
    }

    return response()->json([
        'message' => count($expiredTrials) . " trials expired and notifications sent.",
    ]);
}

}
