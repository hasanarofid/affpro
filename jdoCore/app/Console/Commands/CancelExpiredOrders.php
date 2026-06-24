<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';
    protected $description = 'Cancel orders that have passed their payment deadline';

    public function handle(OrderService $orderService): int
    {
        $count = $orderService->cancelExpiredOrders();

        if ($count > 0) {
            $this->info("{$count} pesanan kadaluarsa telah dibatalkan.");
        } else {
            $this->info("Tidak ada pesanan kadaluarsa.");
        }

        return self::SUCCESS;
    }
}
