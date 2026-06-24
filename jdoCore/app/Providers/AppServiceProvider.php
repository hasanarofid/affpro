<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;
use App\Services\SettingService;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ShippingService;
use App\Services\ThemeService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Force file drivers if the app is not yet installed
        if (!file_exists(storage_path('installed'))) {
            config(['session.driver' => 'file']);
            config(['cache.default' => 'file']);
        }

        $this->app->singleton(SettingService::class);
        $this->app->singleton(CartService::class);
        $this->app->singleton(OrderService::class);
        $this->app->singleton(PaymentService::class);
        $this->app->singleton(ShippingService::class);
        $this->app->singleton(ThemeService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register events
        Event::listen(
            \App\Events\OrderCreated::class,
            \App\Listeners\CheckStockAfterOrder::class,
        );
        Event::listen(
            \App\Events\StockLow::class,
            \App\Listeners\NotifyLowStock::class,
        );

        // Share demo mode flag to all views
        \Illuminate\Support\Facades\View::share('demoMode', config('app.demo_mode'));

        // Register theme views
        app(ThemeService::class)->registerViews();

        if (file_exists(storage_path('installed'))) {
            try {
                $mailHost = \App\Models\Setting::getValue('mail_host', '');
                if (!empty($mailHost)) {
                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.host' => $mailHost,
                        'mail.mailers.smtp.port' => \App\Models\Setting::getValue('mail_port', 587),
                        'mail.mailers.smtp.encryption' => \App\Models\Setting::getValue('mail_encryption', 'tls'),
                        'mail.mailers.smtp.username' => \App\Models\Setting::getValue('mail_username', ''),
                        'mail.mailers.smtp.password' => \App\Models\Setting::getValue('mail_password', ''),
                        'mail.from.address' => \App\Models\Setting::getValue('mail_from_address', 'no-reply@domain.com'),
                        'mail.from.name' => \App\Models\Setting::getValue('store_name', 'Store'),
                    ]);
                }
            } catch (\Exception $e) {
                // Ignore DB errors
            }
        }

        // Register scheduler
        Schedule::command('orders:cancel-expired')->everyFiveMinutes();
    }
}

