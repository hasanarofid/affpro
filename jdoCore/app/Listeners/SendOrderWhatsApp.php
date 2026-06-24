<?php

namespace App\Listeners;

use App\Contracts\WhatsAppInterface;
use App\Events\OrderCreated;
use App\Services\SettingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderWhatsApp implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $phone = $order->user?->phone ?? $order->guest_phone;

        if (!$phone)
            return;

        try {
            $wa = app(WhatsAppInterface::class);
            if (!$wa->isConfigured())
                return;

            $storeName = app(SettingService::class)->storeName();
            $total = 'Rp ' . number_format((float) $order->total, 0, ',', '.');

            $message = "Halo! Pesanan Anda di {$storeName} telah diterima 🎉\n\n"
                . "📋 No. Order: #{$order->order_number}\n"
                . "💰 Total: {$total}\n"
                . "📦 Status: Menunggu Pembayaran\n\n"
                . "Segera lakukan pembayaran agar pesanan diproses.\n"
                . "Terima kasih telah berbelanja di {$storeName}!";

            $wa->send($phone, $message);
        } catch (\Exception $e) {
            // WA not available — fail silently
        }
    }
}
