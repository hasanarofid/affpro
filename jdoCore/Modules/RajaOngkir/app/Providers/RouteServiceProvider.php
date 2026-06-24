<?php

namespace Modules\RajaOngkir\app\Providers;

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
        $this->mapApiRoutes();
    }

    protected function mapApiRoutes(): void
    {
        // Note: shared shipping endpoints (/api/shipping/*) are now defined in
        // the main app routes/web.php and resolved via ShippingService against
        // the active provider. We keep RajaOngkir's own endpoints here only as
        // legacy/admin-only routes — disabled by default to avoid duplicates.
        //
        // To re-enable RajaOngkir's direct routes, uncomment below:
        //
        // Route::middleware('web')
        //     ->prefix('api/rajaongkir')
        //     ->name('rajaongkir.')
        //     ->group(module_path('RajaOngkir', '/routes/api.php'));
    }
}
