<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CheckVersions;
use App\Console\Commands\DownloadUpdates;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(CheckVersions::class)->daily();
Schedule::command(DownloadUpdates::class)->cron('0 1 * * 1,3,5,0');