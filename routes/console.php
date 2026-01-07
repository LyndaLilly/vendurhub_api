<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Trial;
use App\Models\Subscription;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Schedule::call(function () {
//     $expired = Trial::where('active', true)
//         ->where('ended_at', '<', now())
//         ->update([
//             'active'  => false,
//             'expired_at' => now(),
//         ]);
// })->everyMinute();


Schedule::call(function () {
    $expired = Subscription::where('is_active', true)
        ->where('ended_at', '<', now())
        ->update([
            'is_active' => false,
        ]);

    Log::info("🧹 Expired {$expired} subscriptions at " . now());
})->everyMinute();
