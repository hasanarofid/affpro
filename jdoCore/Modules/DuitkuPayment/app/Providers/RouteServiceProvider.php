<?php

namespace Modules\DuitkuPayment\app\Providers;

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
     * Duitku sends POST callbacks to this endpoint.
     */
    protected function mapWebhookRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/payment/duitku')
            ->name('duitku.')
            ->group(function () {
                Route::post('/callback', [
                    \Modules\DuitkuPayment\app\Http\Controllers\DuitkuWebhookController::class,
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
            ->prefix('api/payment/duitku')
            ->name('duitku.')
            ->group(function () {
                Route::get('/channels', [
                    \Modules\DuitkuPayment\app\Http\Controllers\DuitkuSettingsController::class,
                    'channels',
                ])->name('channels');

                Route::get('/settings', [
                    \Modules\DuitkuPayment\app\Http\Controllers\DuitkuSettingsController::class,
                    'settings',
                ])->name('settings');
            });
    }
}
