<?php

namespace Modules\XenditPayment\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\XenditPayment\app\Services\XenditService;

class XenditPaymentServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'XenditPayment';
    protected string $moduleNameLower = 'xenditpayment';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->loadViewsFrom(module_path($this->moduleName, 'resources/views'), $this->moduleNameLower);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);

        // Register XenditService as singleton
        $this->app->singleton(XenditService::class);

        // Register into the payment provider registry using the standard binding convention
        // This allows PaymentService::resolveProvider('xendit') to find us
        $this->app->singleton('payment.provider.xendit', function ($app) {
            return $app->make(XenditService::class);
        });

        // Register this module as a gateway provider in the payment gateway registry
        // This allows the core system to dynamically discover all active payment gateways
        $this->app->resolving('payment.gateway.registry', function ($registry) {
            $registry['xendit'] = [
                'name' => 'Xendit',
                'description' => 'Payment Gateway — Virtual Account, E-Wallet, QRIS, dll',
                'service' => XenditService::class,
                'settings_route' => 'xendit.settings',
                'channels_route' => 'xendit.channels',
                'icon' => 'bi-credit-card-2-front',
            ];
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
