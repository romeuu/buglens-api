<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CheckVersions;
use App\Console\Commands\DownloadUpdates;
use App\Console\Commands\LoadJobs;
use App\Console\Commands\StartWorkers;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Mantener CheckVersions diario
Schedule::command(CheckVersions::class)->daily();

// Inicio del proceso - Descargas (1:00 AM)
Schedule::command(DownloadUpdates::class)->cron('0 1 * * 1,3,5,0');

// LoadJobs 3 horas después de iniciar las descargas (4:00 AM)
Schedule::command(LoadJobs::class)->cron('0 4 * * 1,3,5,0');

// StartWorkers 1 hora después de crear los jobs (5:00 AM)
Schedule::command(StartWorkers::class)->cron('0 5 * * 1,3,5,0');