<?php

namespace Modules\RajaOngkir\app\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\CourierInterface;
use Modules\RajaOngkir\app\Services\RajaOngkirService;

class RajaOngkirServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'RajaOngkir';
    protected string $moduleNameLower = 'rajaongkir';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        // Bind provider-specific key (used by ShippingService::resolveProvider).
        $this->app->singleton('courier.provider.rajaongkir', RajaOngkirService::class);

        // Backward-compat: keep generic binding pointing to RajaOngkir.
        $this->app->singleton(CourierInterface::class, RajaOngkirService::class);
        $this->app->singleton(RajaOngkirService::class);
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'config/config.php'),
            $this->moduleNameLower
        );
    }
}
