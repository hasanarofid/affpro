<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\StockLow;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckStockAfterOrder implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        foreach ($event->order->items as $item) {
            $product = $item->product;
            $stock = $product->effective_stock;

            if ($stock > 0 && $stock <= $product->min_stock_alert) {
                event(new StockLow($product, $stock));
            }
        }
    }
}
