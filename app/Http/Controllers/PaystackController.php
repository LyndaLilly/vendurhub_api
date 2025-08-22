<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;  // Correct Mail import
use App\Mail\SubscriptionSuccessfulMail;

class PaystackController extends Controller
{
    public function initialize(Request $request)
    {
        $user = $request->user();

        $plan = $request->input('plan', 'monthly');
        if (! in_array($plan, ['monthly', 'yearly'])) {
            return response()->json(['message' => 'Invalid subscription plan'], 422);
        }

        $amount = $plan === 'monthly' ? 3000 * 100 : 30000 * 100;

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
                $expiresAt = $plan === 'monthly' ? now()->addMonth() : now()->addYear();

                $user->update([
                    'is_subscribed'           => true,
                    'subscription_type'       => $plan,
                    'subscription_expires_at' => $expiresAt,
                ]);

                // Send subscription confirmation email
                Mail::to($user->email)->send(new SubscriptionSuccessfulMail($user, $plan, $expiresAt));

                return redirect(env('FRONTEND_URL') . '/vendor/subscription?status=success');
            }
        }

        return redirect(env('FRONTEND_URL') . '/vendor/subscription?status=failure');
    }

    public function currentSubscription(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'is_subscribed'           => $user->is_subscribed,
            'subscription_type'       => $user->subscription_type,
            'subscription_expires_at' => $user->subscription_expires_at,
            'plans'                   => [
                ['type' => 'monthly', 'price' => 3000, 'duration_days' => 30],
                ['type' => 'yearly', 'price' => 30000, 'duration_days' => 365],
            ],
        ]);
    }
}
