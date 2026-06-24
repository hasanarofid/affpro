<?php

use Illuminate\Support\Facades\Route;
use Modules\DuitkuPayment\app\Http\Controllers\DuitkuWebhookController;
use Modules\DuitkuPayment\app\Http\Controllers\DuitkuSettingsController;

/*
|--------------------------------------------------------------------------
| Duitku Payment Module Routes
|--------------------------------------------------------------------------
*/

Route::post('/callback', [DuitkuWebhookController::class, 'callback'])->name('callback');
Route::get('/channels', [DuitkuSettingsController::class, 'channels'])->name('channels');
Route::get('/settings', [DuitkuSettingsController::class, 'settings'])->name('settings');
