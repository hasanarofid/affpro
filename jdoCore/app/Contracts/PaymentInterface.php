<?php

namespace App\Contracts;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentInterface
{
    /**
     * Get the provider name.
     */
    public function getName(): string;

    /**
     * Get available payment channels.
     */
    public function getChannels(): array;

    /**
     * Create a charge/transaction for the given order.
     */
    public function charge(Order $order, array $options = []): PaymentResponse;

    /**
     * Handle callback/webhook from the payment provider.
     */
    public function callback(Request $request): CallbackResponse;
}
