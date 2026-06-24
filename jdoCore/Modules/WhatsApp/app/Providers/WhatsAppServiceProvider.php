<?php

namespace Modules\WhatsApp\app\Providers;

use App\Contracts\WhatsAppInterface;
use Illuminate\Support\ServiceProvider;
use Modules\WhatsApp\app\Services\WhatsAppService;

class WhatsAppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WhatsAppInterface::class, WhatsAppService::class);
    }

    public function boot(): void
    {
        //
    }
}
