<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionExpiryReminderMail;
use Carbon\Carbon;

class CheckSubscriptionStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->is_subscribed && $user->subscription_expires_at) {
            $now = Carbon::now();
            $expiresAt = Carbon::parse($user->subscription_expires_at);
            $daysLeft = $now->diffInDays($expiresAt, false);

            // If 3 days or less remain, send reminder (once per day)
            if ($daysLeft > 0 && $daysLeft <= 3) {
                if (
                    !$user->last_subscription_reminder_at ||
                    $user->last_subscription_reminder_at->lt($now->copy()->subDay())
                ) {
                    Mail::to($user->email)->send(new SubscriptionExpiryReminderMail($user));
                    $user->update(['last_subscription_reminder_at' => $now]);
                }
            }

            // If expired, mark unsubscribed
            if ($daysLeft <= 0 && $user->is_subscribed) {
                $user->update(['is_subscribed' => false]);
            }
        }

        return $next($request);
    }
}
