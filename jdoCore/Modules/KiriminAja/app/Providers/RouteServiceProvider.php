<?php

namespace Modules\KiriminAja\app\Providers;

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
        Route::middleware('web')
            ->prefix('api/kiriminaja')
            ->name('kiriminaja.')
            ->group(module_path('KiriminAja', '/routes/api.php'));
    }
}
