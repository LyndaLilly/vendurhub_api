<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionSuccessfulMail;
use Carbon\Carbon;

class PaystackController extends Controller
{
    public function initialize(Request $request)
    {
        $user = $request->user();

        $plan = $request->input('plan', 'monthly'); // must be 'monthly' or 'yearly'
        if (! in_array($plan, ['monthly', 'yearly'])) {
            return response()->json(['message' => 'Invalid subscription plan'], 422);
        }

        $amount = $plan === 'monthly' ? 5000 * 100 : 30000 * 100; // in kobo

        $callback_url = route('paystack.callback');

        $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
            ->post('https://api.paystack.co/transaction/initialize', [
                'email'        => $user->email,
                'amount'       => $amount,
                'callback_url' => $callback_url,
                'metadata'     => [
                    'plan'    => $plan,
                    'user_id' => $user->id,
                ],
            ]);

        return response()->json(json_decode($response->body(), true));
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect(env('FRONTEND_URL') . '/vendor/subscription?status=failure');
        }

        $response = Http::withToken(env('PAYSTACK_SECRET_KEY'))
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        $data = json_decode($response->body(), true);

        if ($data['status'] && $data['data']['status'] === 'success') {
            $plan   = $data['data']['metadata']['plan'] ?? 'monthly';
            $userId = $data['data']['metadata']['user_id'] ?? null;

            $user = User::find($userId);

            if ($user) {
                $startsAt  = Carbon::now();
                $expiresAt = $plan === 'monthly'
                    ? $startsAt->copy()->addMonth()
                    : $startsAt->copy()->addYear();

                Subscription::create([
                    'user_id'    => $user->id,
                    'plan'       => $plan,
                    'is_active'  => true,
                    'starts_at'  => $startsAt,
                    'ended_at'   => $expiresAt,
                    'expired_at' => $expiresAt,
                ]);

                Mail::to($user->email)
                    ->send(new SubscriptionSuccessfulMail($user, $plan, $expiresAt));

                return redirect(env('FRONTEND_URL') . '/vendor/subscription?status=success');
            }
        }

        return redirect(env('FRONTEND_URL') . '/vendor/subscription?status=failure');
    }

    public function currentSubscription(Request $request)
    {
        $user = $request->user();

        $subscription = Subscription::where('user_id', $user->id)
            ->where('is_active', 1)
            ->latest()
            ->first();

        $hasActiveSubscription = $subscription ? true : false;

        // Trial check
        $trial = \App\Models\Trial::where('user_id', $user->id)->first();
        $trialActive = false;
        $trialEndsAt = null;

        if ($trial) {
            $trialEndsAt = $trial->started_at->copy()->addDays(30);
            $trialActive = now()->lessThan($trialEndsAt);
        }

        // Plans (match frontend exactly)
        $plans = [
            ['type' => 'monthly', 'price' => 5000, 'duration_days' => 30],
            ['type' => 'yearly', 'price' => 30000, 'duration_days' => 365],
        ];

        return response()->json([
            'has_active_subscription' => $hasActiveSubscription,
            'subscription_type' => $subscription?->plan,
            'subscription_expired_at' => $subscription?->expired_at,
            'trial_active' => $trialActive,
            'trial_started_at' => $trial?->started_at,
            'trial_ends_at' => $trialEndsAt,
            'plans' => $plans,
        ]);
    }
}
