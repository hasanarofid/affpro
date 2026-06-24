<?php

namespace App\Listeners;

use App\Events\StockLow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyLowStock implements ShouldQueue
{
    public function handle(StockLow $event): void
    {
        Log::warning("Stok menipis: {$event->product->name} — sisa {$event->currentStock} unit");

        // TODO: Send notification to admin (WhatsApp module will listen to this)
    }
}
