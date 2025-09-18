<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PluginController;
use App\Http\Controllers\ScanController;

Route::get('/plugins', [PluginController::class, 'index']);
Route::get('/plugins/{slug}', [PluginController::class, 'showByPartialSlug']);
Route::get('/plugins/{id}', [PluginController::class, 'show']);

Route::get('/scans', [ScanController::class, 'index']);
Route::get('/scans/{id}', [ScanController::class, 'show']);
Route::get('/scans/plugin/{pluginSlug}', [ScanController::class, 'showByPluginSlug']);