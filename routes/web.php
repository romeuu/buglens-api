<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PluginController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\VulnerabilityController;

Route::get('/plugins', [PluginController::class, 'index']);
Route::get('/plugins/{slug}', [PluginController::class, 'showByPartialSlug']);
Route::get('/plugins/{id}', [PluginController::class, 'show']);

Route::get('/scans', [ScanController::class, 'index']);
Route::get('/scans/{id}', [ScanController::class, 'show']);
Route::get('/scans/plugin/{pluginSlug}', [ScanController::class, 'showByPluginSlug']);

Route::get('/vulnerabilities', [VulnerabilityController::class, 'index']);
Route::get('/vulnerabilities/{id}', [VulnerabilityController::class, 'show']);
Route::get('/vulnerabilities/vuln-name/{vulnName}', [VulnerabilityController::class, 'showByVulnName']);
Route::get('/vulnerabilities/sink-name/{sinkName}', [VulnerabilityController::class, 'showBySinkName']);
Route::get('/vulnerabilities/plugin/{pluginId}', [VulnerabilityController::class, 'showByPluginId']);