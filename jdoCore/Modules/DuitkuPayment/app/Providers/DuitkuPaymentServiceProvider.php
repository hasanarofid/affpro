<?php

namespace Modules\DuitkuPayment\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\DuitkuPayment\app\Services\DuitkuService;

class DuitkuPaymentServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'DuitkuPayment';
    protected string $moduleNameLower = 'duitkupayment';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->loadViewsFrom(module_path($this->moduleName, 'resources/views'), $this->moduleNameLower);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        // Register DuitkuService as singleton
        $this->app->singleton(DuitkuService::class);

        // Register into the payment provider registry
        // PaymentService::resolveProvider('duitku') will find us
        $this->app->singleton('payment.provider.duitku', function ($app) {
            return $app->make(DuitkuService::class);
        });
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
