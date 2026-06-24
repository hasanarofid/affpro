<?php

namespace Modules\MidtransPayment\app\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapWebhookRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Webhook route — no CSRF, no auth middleware.
     * Midtrans sends POST notifications to this endpoint.
     */
    protected function mapWebhookRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/payment/midtrans')
            ->name('midtrans.')
            ->group(function () {
                Route::post('/callback', [
                    \Modules\MidtransPayment\app\Http\Controllers\MidtransWebhookController::class,
                    'callback',
                ])->name('callback');
            });
    }

    /**
     * Web routes — channels/settings endpoints require web middleware.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->prefix('api/payment/midtrans')
            ->name('midtrans.')
            ->group(function () {
                Route::get('/channels', [
                    \Modules\MidtransPayment\app\Http\Controllers\MidtransSettingsController::class,
                    'channels',
                ])->name('channels');

                Route::get('/settings', [
                    \Modules\MidtransPayment\app\Http\Controllers\MidtransSettingsController::class,
                    'settings',
                ])->name('settings');
            });
    }
}
