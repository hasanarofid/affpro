<?php

use Illuminate\Support\Facades\Route;
use Modules\RajaOngkir\app\Http\Controllers\ShippingController;

Route::get('/destinations', [ShippingController::class, 'destinations'])->name('destinations');
Route::post('/cost', [ShippingController::class, 'cost'])->name('cost');
Route::post('/track', [ShippingController::class, 'track'])->name('track');
