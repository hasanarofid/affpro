<?php

namespace Modules\MidtransPayment\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\MidtransPayment\app\Services\MidtransService;

class MidtransPaymentServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'MidtransPayment';
    protected string $moduleNameLower = 'midtranspayment';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->loadViewsFrom(module_path($this->moduleName, 'resources/views'), $this->moduleNameLower);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        // Register MidtransService as singleton
        $this->app->singleton(MidtransService::class);

        // Register into the payment provider registry using the standard binding convention
        // This allows PaymentService::resolveProvider('midtrans') to find us
        $this->app->singleton('payment.provider.midtrans', function ($app) {
            return $app->make(MidtransService::class);
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
