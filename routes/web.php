<?php

use App\Http\Controllers\AppLaunchController;
use App\Http\Controllers\OAuthController;
use App\Http\Middleware\InstallHmacSignatureMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/welcome', fn() => dd('Welcome Page'))->name('app.welcome');
Route::get('/app', [AppLaunchController::class, 'launch'])->name('app.launch');
Route::get('/oauth/{platform}/install', [OAuthController::class, 'redirectToProvider'])->middleware(InstallHmacSignatureMiddleware::class);
Route::get('/oauth/{platform}/callback', [OAuthController::class, 'handleOAuthCallback'])->middleware(InstallHmacSignatureMiddleware::class);
Route::get('/dashboard', fn() => dd('Dashboard Page'))->name('app.dashboard');
