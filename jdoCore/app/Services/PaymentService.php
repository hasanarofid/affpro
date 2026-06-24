<?php

namespace App\Services;

use App\Contracts\PaymentInterface;
use App\Contracts\PaymentResponse;
use App\Contracts\CallbackResponse;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentService
{
    /**
     * Get the active payment provider by name.
     * Module providers register themselves with:
     *   $app->singleton('payment.provider.{name}', ...)
     */
    public function resolveProvider(string $provider): ?PaymentInterface
    {
        $binding = "payment.provider.{$provider}";

        if (app()->bound($binding)) {
            return app($binding);
        }

        return null;
    }

    /**
     * Get all registered gateway provider names.
     * Scans the container for any 'payment.provider.*' bindings.
     */
    public function getRegisteredProviders(): array
    {
        $providers = [];
        $bindings = app()->getBindings();

        foreach ($bindings as $key => $binding) {
            if (str_starts_with($key, 'payment.provider.')) {
                $providerName = str_replace('payment.provider.', '', $key);
                $instance = $this->resolveProvider($providerName);
                if ($instance) {
                    $providers[$providerName] = $instance;
                }
            }
        }

        return $providers;
    }

    /**
     * Check if any payment gateway module is installed & active.
     */
    public function hasGatewayProvider(): bool
    {
        return !empty($this->getRegisteredProviders());
    }

    /**
     * Get the first (or only) registered gateway provider.
     * In most setups, there's only one gateway module installed.
     */
    public function getActiveGatewayProvider(): ?PaymentInterface
    {
        $providers = $this->getRegisteredProviders();

        if (empty($providers)) {
            return null;
        }

        // Return the first one
        return reset($providers);
    }

    /**
     * Get the active gateway provider name.
     */
    public function getActiveGatewayName(): ?string
    {
        $providers = $this->getRegisteredProviders();

        if (empty($providers)) {
            return null;
        }

        return array_key_first($providers);
    }

    /**
     * Charge an order via a payment gateway.
     */
    public function charge(Order $order, string $provider, array $options = []): PaymentResponse
    {
        $gateway = $this->resolveProvider($provider);

        if (!$gateway) {
            return new PaymentResponse(false, "Payment provider '{$provider}' tidak ditemukan atau belum diinstall.");
        }

        $result = $gateway->charge($order, $options);

        // Record payment
        Payment::create([
            'order_id' => $order->id,
            'method' => $provider,
            'provider_ref' => $result->providerRef,
            'amount' => $order->total,
            'status' => $result->success ? 'pending' : 'failed',
            'metadata' => $result->metadata,
        ]);

        return $result;
    }

    /**
     * Handle callback from payment gateway.
     */
    public function handleCallback(string $provider, Request $request): CallbackResponse
    {
        $gateway = $this->resolveProvider($provider);

        if (!$gateway) {
            return new CallbackResponse(false, message: "Provider not found");
        }

        $result = $gateway->callback($request);

        if ($result->success && $result->orderNumber) {
            $order = Order::where('order_number', $result->orderNumber)->first();
            if ($order) {
                /** @var \App\Models\Payment|null $payment */
                $payment = $order->payments()->where('method', $provider)->latest()->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'success',
                        'metadata' => array_merge($payment->metadata ?? [], $result->metadata),
                    ]);
                }
                app(OrderService::class)->markAsPaid($order);
            }
        }

        return $result;
    }

    /**
     * Create a manual transfer payment record.
     */
    public function createManualTransfer(Order $order, array $data): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'method' => 'manual_transfer',
            'amount' => $order->total,
            'status' => 'pending',
            'bank_name' => $data['bank_name'] ?? null,
            'account_name' => $data['account_name'] ?? null,
            'proof_image' => $data['proof_image'] ?? null,
        ]);
    }

    /**
     * Verify a manual transfer payment (admin action).
     */
    public function verifyManualTransfer(Payment $payment, int $adminId): void
    {
        $payment->update([
            'status' => 'success',
            'verified_by' => $adminId,
            'verified_at' => now(),
        ]);

        app(OrderService::class)->markAsPaid($payment->order);
    }

    /**
     * Get available payment methods (including dynamically registered gateways).
     */
    public function getAvailableMethods(): array
    {
        $settingService = app(SettingService::class);

        $methods = [];

        // Static methods from settings
        if ($settingService->get('payment_method_manual', '1') == '1') {
            $methods[] = [
                'key' => 'manual_transfer',
                'name' => 'Transfer Bank Manual',
                'description' => 'Transfer ke rekening toko, lalu upload bukti bayar.',
                'active' => true,
                'type' => 'manual',
            ];
        }

        if ($settingService->get('payment_method_cod', '1') == '1') {
            $methods[] = [
                'key' => 'cod',
                'name' => 'Bayar di Tempat (COD)',
                'description' => 'Bayar ke kurir saat barang tiba.',
                'active' => true,
                'type' => 'cod',
            ];
        }

        // Dynamic gateway methods from registered modules
        if ($settingService->get('payment_method_gateway', '0') == '1') {
            $providers = $this->getRegisteredProviders();
            foreach ($providers as $name => $provider) {
                $methods[] = [
                    'key' => 'gateway',
                    'provider' => $name,
                    'name' => 'Pembayaran Instan (' . ucfirst($name) . ')',
                    'description' => 'Virtual Account, E-Wallet, QRIS — otomatis terbaca.',
                    'active' => true,
                    'type' => 'gateway',
                ];
            }
        }

        return $methods;
    }
}
