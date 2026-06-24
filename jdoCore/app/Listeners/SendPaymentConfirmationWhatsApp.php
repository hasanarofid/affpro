<?php

namespace App\Listeners;

use App\Contracts\WhatsAppInterface;
use App\Events\PaymentReceived;
use App\Services\SettingService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPaymentConfirmationWhatsApp implements ShouldQueue
{
    public function handle(PaymentReceived $event): void
    {
        $payment = $event->payment;
        $order = $payment->order;
        $phone = $order->user?->phone ?? $order->guest_phone;

        if (!$phone)
            return;

        try {
            $wa = app(WhatsAppInterface::class);
            if (!$wa->isConfigured())
                return;

            $storeName = app(SettingService::class)->storeName();

            $message = "Pembayaran pesanan #{$order->order_number} telah dikonfirmasi ✅\n\n"
                . "💳 Jumlah: Rp " . number_format((float) $payment->amount, 0, ',', '.') . "\n"
                . "📦 Pesanan sedang diproses.\n\n"
                . "Terima kasih, {$storeName}!";

            $wa->send($phone, $message);
        } catch (\Exception $e) {
            // fail silently
        }
    }
}
