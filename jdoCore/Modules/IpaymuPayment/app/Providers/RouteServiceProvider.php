<?php

namespace Modules\IpaymuPayment\app\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

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

    protected function mapWebhookRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api/payment/ipaymu')
            ->name('ipaymu.')
            ->group(function () {
                Route::post('/callback', [\Modules\IpaymuPayment\app\Http\Controllers\IpaymuWebhookController::class, 'callback'])->name('callback');
            });
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->prefix('api/payment/ipaymu')
            ->name('ipaymu.')
            ->group(function () {
                Route::get('/channels', [\Modules\IpaymuPayment\app\Http\Controllers\IpaymuSettingsController::class, 'channels'])->name('channels');
                Route::get('/settings', [\Modules\IpaymuPayment\app\Http\Controllers\IpaymuSettingsController::class, 'settings'])->name('settings');
            });
    }
}