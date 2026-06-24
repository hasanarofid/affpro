<?php

use Illuminate\Support\Facades\Route;
use Modules\KiriminAja\app\Http\Controllers\KiriminAjaController;

Route::get('/destinations', [KiriminAjaController::class, 'destinations'])->name('destinations');
Route::post('/cost', [KiriminAjaController::class, 'cost'])->name('cost');
Route::post('/track', [KiriminAjaController::class, 'track'])->name('track');
Route::post('/pickup', [KiriminAjaController::class, 'requestPickup'])->name('pickup');
Route::post('/cancel', [KiriminAjaController::class, 'cancel'])->name('cancel');
Route::get('/balance', [KiriminAjaController::class, 'balance'])->name('balance');
Route::get('/schedules', [KiriminAjaController::class, 'schedules'])->name('schedules');
