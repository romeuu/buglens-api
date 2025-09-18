<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PluginController;

Route::get('/plugins', [PluginController::class, 'index']);
Route::get('/plugins/{slug}', [PluginController::class, 'findBySlug']);