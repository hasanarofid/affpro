<?php

use Illuminate\Support\Facades\Route;
use Modules\MidtransPayment\app\Http\Controllers\MidtransWebhookController;
use Modules\MidtransPayment\app\Http\Controllers\MidtransSettingsController;

/*
|--------------------------------------------------------------------------
| Midtrans Payment Module Routes
|--------------------------------------------------------------------------
*/

// Webhook — no auth, no CSRF (Midtrans sends POST from their servers)
Route::post('/callback', [MidtransWebhookController::class, 'callback'])->name('callback');

// Admin/Frontend APIs
Route::get('/channels', [MidtransSettingsController::class, 'channels'])->name('channels');
Route::get('/settings', [MidtransSettingsController::class, 'settings'])->name('settings');
