<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('trials:expire-daily', function () {
    $this->call('trials:expire');
})->describe('Expire trials that have reached their end date');
