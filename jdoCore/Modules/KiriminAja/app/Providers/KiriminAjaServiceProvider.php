<?php

namespace Modules\KiriminAja\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\KiriminAja\app\Services\KiriminAjaService;

class KiriminAjaServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'KiriminAja';
    protected string $moduleNameLower = 'kiriminaja';

    public function boot(): void
    {
        $this->registerConfig();
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        // Bind provider-specific key — used by ShippingService::resolveProvider().
        $this->app->singleton('courier.provider.kiriminaja', KiriminAjaService::class);

        // Concrete singleton so controllers can type-hint it directly.
        $this->app->singleton(KiriminAjaService::class);
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
