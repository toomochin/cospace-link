<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    DB::table('processed_webhooks')
        ->where('created_at', '<', now()->subDays(90))
        ->delete();
})->name('prune-processed-webhooks')->dailyAt('03:15')->onOneServer();

Schedule::command('queue:prune-failed --hours=168')
    ->dailyAt('03:30')
    ->onOneServer();

Schedule::command('queue:prune-batches --hours=48 --unfinished=72 --cancelled=72')
    ->dailyAt('03:45')
    ->onOneServer();
