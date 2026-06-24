<?php

namespace Modules\MidtransPayment\app\Services;

use App\Contracts\PaymentInterface;
use App\Contracts\PaymentResponse;
use App\Contracts\CallbackResponse;
use App\Models\Order;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService implements PaymentInterface
{
    protected SettingService $settings;

    public function __construct(SettingService $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get the provider name.
     */
    public function getName(): string
    {
        return 'midtrans';
    }

    /**
     * Determine if running in production mode.
     */
    protected function isProduction(): bool
    {
        return $this->settings->get('midtrans_mode', 'sandbox') === 'production';
    }

    /**
     * Get Snap API URL based on mode.
     */
    protected function getSnapUrl(): string
    {
        $mode = $this->isProduction() ? 'production' : 'sandbox';
        return config("midtranspayment.snap_url.{$mode}");
    }

    /**
     * Get base URL.
     */
    protected function getBaseUrl(): string
    {
        $mode = $this->isProduction() ? 'production' : 'sandbox';
        return config("midtranspayment.base_url.{$mode}");
    }

    /**
     * Get Snap.js URL for frontend embedding.
     */
    public function getSnapJsUrl(): string
    {
        $mode = $this->isProduction() ? 'production' : 'sandbox';
        return config("midtranspayment.snap_js.{$mode}");
    }

    /**
     * Get client key for frontend.
     */
    public function getClientKey(): string
    {
        return $this->settings->get('midtrans_client_key', '');
    }

    /**
     * Get available payment channels.
     */
    public function getChannels(): array
    {
        $enabledRaw = $this->settings->get('midtrans_enabled_channels', '[]');
        $enabled = is_string($enabledRaw) ? json_decode($enabledRaw, true) : $enabledRaw;

        if (!is_array($enabled) || empty($enabled)) {
            return $this->getDefaultChannels();
        }

        return $enabled;
    }

    /**
     * Get all supported channel definitions (for admin UI).
     */
    public function getAllChannelDefinitions(): array
    {
        return [
            // Credit Card
            ['code' => 'credit_card', 'name' => 'Kartu Kredit / Debit', 'type' => 'credit_card', 'icon' => 'bi-credit-card'],

            // Virtual Account / Bank Transfer
            ['code' => 'bca_va', 'name' => 'BCA Virtual Account', 'type' => 'bank_transfer', 'icon' => 'bi-bank'],
            ['code' => 'bni_va', 'name' => 'BNI Virtual Account', 'type' => 'bank_transfer', 'icon' => 'bi-bank'],
            ['code' => 'bri_va', 'name' => 'BRI Virtual Account', 'type' => 'bank_transfer', 'icon' => 'bi-bank'],
            ['code' => 'permata_va', 'name' => 'Permata Virtual Account', 'type' => 'bank_transfer', 'icon' => 'bi-bank'],
            ['code' => 'cimb_va', 'name' => 'CIMB Virtual Account', 'type' => 'bank_transfer', 'icon' => 'bi-bank'],
            ['code' => 'echannel', 'name' => 'Mandiri Bill Payment', 'type' => 'bank_transfer', 'icon' => 'bi-bank'],

            // E-Wallet
            ['code' => 'gopay', 'name' => 'GoPay', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'shopeepay', 'name' => 'ShopeePay', 'type' => 'ewallet', 'icon' => 'bi-phone'],

            // QRIS
            ['code' => 'other_qris', 'name' => 'QRIS (Semua E-Wallet)', 'type' => 'qris', 'icon' => 'bi-qr-code'],

            // Retail / Over the Counter
            ['code' => 'indomaret', 'name' => 'Indomaret', 'type' => 'retail', 'icon' => 'bi-shop'],
            ['code' => 'alfamart', 'name' => 'Alfamart', 'type' => 'retail', 'icon' => 'bi-shop'],
        ];
    }

    /**
     * Create a Snap transaction for the given order.
     */
    public function charge(Order $order, array $options = []): PaymentResponse
    {
        $serverKey = $this->settings->get('midtrans_server_key');

        if (empty($serverKey)) {
            return new PaymentResponse(false, 'Server Key Midtrans belum dikonfigurasi.');
        }

        $expiryDuration = config('midtranspayment.expiry_duration', 1440);

        // Build item details
        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'id' => (string) $item->id,
                'name' => mb_substr($item->product_name, 0, 50),
                'price' => (int) $item->price,
                'quantity' => $item->quantity,
            ];
        }

        // Add shipping cost as line item if present
        if ($order->shipping_cost > 0) {
            $itemDetails[] = [
                'id' => 'shipping',
                'name' => 'Ongkos Kirim',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
            ];
        }

        // Add discount as negative line item if present
        if ($order->discount_amount > 0) {
            $itemDetails[] = [
                'id' => 'discount',
                'name' => 'Diskon',
                'price' => -1 * (int) $order->discount_amount,
                'quantity' => 1,
            ];
        }

        // Build customer details
        $customerDetails = [];
        if ($order->user) {
            $customerDetails = [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->user->phone ?? '',
            ];
        } else {
            $customerDetails = [
                'first_name' => $order->guest_name ?? 'Guest',
                'email' => $order->guest_email ?? '',
                'phone' => $order->guest_phone ?? '',
            ];
        }
        $customerDetails = array_filter($customerDetails);

        // Build shipping address if available
        if (!empty($order->shipping_address) && is_array($order->shipping_address)) {
            $sa = $order->shipping_address;
            $customerDetails['shipping_address'] = [
                'first_name' => $sa['name'] ?? ($customerDetails['first_name'] ?? ''),
                'phone' => $sa['phone'] ?? ($customerDetails['phone'] ?? ''),
                'address' => $sa['address'] ?? '',
                'city' => $sa['city'] ?? '',
                'postal_code' => $sa['postal_code'] ?? '',
                'country_code' => 'IDN',
            ];
        }

        // Build the Snap payload
        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'expiry' => [
                'unit' => 'minutes',
                'duration' => $expiryDuration,
            ],
            'callbacks' => [
                'finish' => route('orders.success', $order->order_number),
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->timeout(30)
                ->post($this->getSnapUrl(), $payload);

            if ($response->successful()) {
                $data = $response->json();

                $redirectUrl = $data['redirect_url'] ?? null;
                $snapToken = $data['token'] ?? null;

                return new PaymentResponse(
                    success: true,
                    message: 'Transaksi Snap Midtrans berhasil dibuat.',
                    redirectUrl: $redirectUrl,
                    providerRef: $snapToken,
                    metadata: [
                        'snap_token' => $snapToken,
                        'redirect_url' => $redirectUrl,
                    ],
                );
            }

            $errorBody = $response->json();
            $errorMessages = $errorBody['error_messages'] ?? [];
            $errorMessage = !empty($errorMessages) ? implode(', ', $errorMessages) : 'Gagal membuat transaksi Midtrans.';
            Log::error('Midtrans Snap error', ['status' => $response->status(), 'body' => $errorBody]);

            return new PaymentResponse(false, $errorMessage);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap exception', ['error' => $e->getMessage()]);
            return new PaymentResponse(false, 'Gagal terhubung ke Midtrans: ' . $e->getMessage());
        }
    }

    /**
     * Handle notification/webhook callback from Midtrans.
     */
    public function callback(Request $request): CallbackResponse
    {
        $serverKey = $this->settings->get('midtrans_server_key');

        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $signatureKey = $request->input('signature_key');
        $transactionStatus = $request->input('transaction_status');
        $fraudStatus = $request->input('fraud_status', 'accept');
        $paymentType = $request->input('payment_type');

        Log::info('Midtrans callback received', [
            'order_id' => $orderId,
            'transaction_status' => $transactionStatus,
            'payment_type' => $paymentType,
            'status_code' => $statusCode,
        ]);

        // Verify signature
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans callback: invalid signature', [
                'order_id' => $orderId,
                'expected' => substr($expectedSignature, 0, 10) . '...',
                'received' => substr($signatureKey ?? '', 0, 10) . '...',
            ]);
            return new CallbackResponse(false, message: 'Invalid signature');
        }

        if (!$orderId) {
            return new CallbackResponse(false, message: 'Missing order_id');
        }

        // Map Midtrans transaction status to our status
        $mappedStatus = $this->mapTransactionStatus($transactionStatus, $fraudStatus);

        return new CallbackResponse(
            success: $mappedStatus === 'paid',
            orderNumber: $orderId,
            status: $mappedStatus,
            message: "Midtrans callback: {$transactionStatus}",
            metadata: [
                'midtrans_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'payment_type' => $paymentType,
                'status_code' => $statusCode,
                'gross_amount' => $grossAmount,
            ],
        );
    }

    /**
     * Map Midtrans transaction status to internal status.
     */
    protected function mapTransactionStatus(string $status, string $fraudStatus = 'accept'): string
    {
        return match ($status) {
            'capture' => ($fraudStatus === 'accept') ? 'paid' : 'failed',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny', 'cancel' => 'failed',
            'expire' => 'expired',
            'refund', 'partial_refund' => 'refunded',
            default => 'failed',
        };
    }

}
