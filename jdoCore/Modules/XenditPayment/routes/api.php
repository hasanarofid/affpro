<?php

use Illuminate\Support\Facades\Route;
use Modules\XenditPayment\app\Http\Controllers\XenditWebhookController;
use Modules\XenditPayment\app\Http\Controllers\XenditSettingsController;

/*
|--------------------------------------------------------------------------
| Xendit Payment Module Routes
|--------------------------------------------------------------------------
*/

// Webhook — no auth, no CSRF (Xendit sends POST from their servers)
Route::post('/callback', [XenditWebhookController::class, 'callback'])->name('callback');

// Admin settings API (protected by web middleware in RouteServiceProvider)
Route::get('/channels', [XenditSettingsController::class, 'channels'])->name('channels');
Route::get('/settings', [XenditSettingsController::class, 'settings'])->name('settings');
