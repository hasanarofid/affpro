<?php

namespace Modules\XenditPayment\app\Providers;

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
     * Xendit sends POST requests to this endpoint.
     */
    protected function mapWebhookRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/payment/xendit')
            ->name('xendit.')
            ->group(function () {
                // Only the callback route should be unguarded
                Route::post('/callback', [
                    \Modules\XenditPayment\app\Http\Controllers\XenditWebhookController::class,
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
            ->prefix('api/payment/xendit')
            ->name('xendit.')
            ->group(function () {
                Route::get('/channels', [
                    \Modules\XenditPayment\app\Http\Controllers\XenditSettingsController::class,
                    'channels',
                ])->name('channels');

                Route::get('/settings', [
                    \Modules\XenditPayment\app\Http\Controllers\XenditSettingsController::class,
                    'settings',
                ])->name('settings');
            });
    }
}
