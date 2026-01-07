<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Trial;
use Carbon\Carbon;

class ExpireTrials extends Command
{
    protected $signature = 'trials:expire';
    protected $description = 'Expire trials that have reached their end date';

    public function handle()
    {
        $now = Carbon::now();

        $expiredTrials = Trial::where('active', 1)
                              ->where('ended_at', '<', $now)
                              ->get();

        foreach ($expiredTrials as $trial) {
            $trial->active = 0;
            $trial->save();
            $this->info("Trial for user {$trial->user_id} expired.");
        }

        $this->info('All expired trials processed.');
        return 0;
    }
}
