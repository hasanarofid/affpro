<?php

namespace App\Listeners;

use App\Contracts\WhatsAppInterface;
use App\Events\OrderStatusChanged;
use App\Services\SettingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendShippingWhatsApp implements ShouldQueue
{
    public function handle(OrderStatusChanged $event): void
    {
        if ($event->newStatus !== 'shipped')
            return;

        $order = $event->order;
        $phone = $order->user?->phone ?? $order->guest_phone;

        if (!$phone)
            return;

        try {
            $wa = app(WhatsAppInterface::class);
            if (!$wa->isConfigured())
                return;

            $storeName = app(SettingService::class)->storeName();
            $resi = $order->shipment?->tracking_number ?? '-';
            $courier = $order->shipment?->courier_code ?? '';

            $message = "Pesanan #{$order->order_number} sedang dalam pengiriman 🚚\n\n"
                . "📦 Kurir: {$courier}\n"
                . "🔢 No. Resi: {$resi}\n\n"
                . "Terima kasih telah berbelanja di {$storeName}!";

            $wa->send($phone, $message);
        } catch (\Exception $e) {
            // fail silently
        }
    }
}
