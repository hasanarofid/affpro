<?php

namespace Modules\IpaymuPayment\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\IpaymuPayment\app\Services\IpaymuService;

class IpaymuPaymentServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'IpaymuPayment';
    protected string $moduleNameLower = 'ipaymupayment';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->loadViewsFrom(module_path($this->moduleName, 'resources/views'), $this->moduleNameLower);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->singleton(IpaymuService::class);
        $this->app->singleton('payment.provider.ipaymu', function ($app) {
            return $app->make(IpaymuService::class);
        });
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);
    }
}