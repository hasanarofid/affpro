<?php

namespace Modules\XenditPayment\app\Services;

use App\Contracts\PaymentInterface;
use App\Contracts\PaymentResponse;
use App\Contracts\CallbackResponse;
use App\Models\Order;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService implements PaymentInterface
{
    protected SettingService $settings;
    protected string $baseUrl;

    public function __construct(SettingService $settings)
    {
        $this->settings = $settings;
        $this->baseUrl = config('xenditpayment.base_url', 'https://api.xendit.co');
    }

    /**
     * Get the provider name.
     */
    public function getName(): string
    {
        return 'xendit';
    }

    /**
     * Get available payment channels from Xendit.
     */
    public function getChannels(): array
    {
        $enabledRaw = $this->settings->get('xendit_enabled_channels', '[]');
        $enabled = is_string($enabledRaw) ? json_decode($enabledRaw, true) : $enabledRaw;

        if (!is_array($enabled) || empty($enabled)) {
            // Return default channels
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
            // Virtual Account
            ['code' => 'BCA', 'name' => 'BCA Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'BNI', 'name' => 'BNI Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'BRI', 'name' => 'BRI Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'MANDIRI', 'name' => 'Mandiri Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'PERMATA', 'name' => 'Permata Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'BSI', 'name' => 'BSI Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'CIMB', 'name' => 'CIMB Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'SAHABAT_SAMPOERNA', 'name' => 'Sahabat Sampoerna VA', 'type' => 'virtual_account', 'icon' => 'bi-bank'],

            // E-Wallet
            ['code' => 'OVO', 'name' => 'OVO', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'DANA', 'name' => 'DANA', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'LINKAJA', 'name' => 'LinkAja', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'ASTRAPAY', 'name' => 'AstraPay', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'JENIUSPAY', 'name' => 'Jenius Pay', 'type' => 'ewallet', 'icon' => 'bi-phone'],

            // QRIS
            ['code' => 'QRIS', 'name' => 'QRIS (Semua E-Wallet)', 'type' => 'qris', 'icon' => 'bi-qr-code'],

            // Retail Outlet
            ['code' => 'ALFAMART', 'name' => 'Alfamart', 'type' => 'retail', 'icon' => 'bi-shop'],
            ['code' => 'INDOMARET', 'name' => 'Indomaret', 'type' => 'retail', 'icon' => 'bi-shop'],
        ];
    }

    /**
     * Create a charge/invoice for the given order.
     */
    public function charge(Order $order, array $options = []): PaymentResponse
    {
        $secretKey = $this->settings->get('xendit_secret_key');

        if (empty($secretKey)) {
            return new PaymentResponse(false, 'Kunci API Xendit belum dikonfigurasi.');
        }

        $invoiceDuration = config('xenditpayment.invoice_duration', 86400);
        $currency = config('xenditpayment.currency', 'IDR');

        // Build items array
        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
            ];
        }

        // Build customer object
        $customer = [];
        if ($order->user) {
            $customer = [
                'given_names' => $order->user->name,
                'email' => $order->user->email,
                'mobile_number' => $order->user->phone ?? null,
            ];
        } else {
            $customer = [
                'given_names' => $order->guest_name ?? 'Guest',
                'email' => $order->guest_email ?? null,
                'mobile_number' => $order->guest_phone ?? null,
            ];
        }
        $customer = array_filter($customer); // Remove nulls

        // Build the payload
        $payload = [
            'external_id' => $order->order_number,
            'amount' => (float) $order->total,
            'description' => 'Pembayaran pesanan ' . $order->order_number,
            'invoice_duration' => $invoiceDuration,
            'currency' => $currency,
            'items' => $items,
            'customer' => $customer,
            'success_redirect_url' => route('orders.success', $order->order_number),
            'failure_redirect_url' => route('orders.payment', $order->order_number),
        ];

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->timeout(30)
                ->post("{$this->baseUrl}/v2/invoices", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return new PaymentResponse(
                    success: true,
                    message: 'Invoice Xendit berhasil dibuat.',
                    redirectUrl: $data['invoice_url'] ?? null,
                    providerRef: $data['id'] ?? null,
                    metadata: [
                        'invoice_id' => $data['id'] ?? null,
                        'invoice_url' => $data['invoice_url'] ?? null,
                        'expiry_date' => $data['expiry_date'] ?? null,
                        'status' => $data['status'] ?? null,
                    ],
                );
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] ?? 'Gagal membuat invoice Xendit.';
            Log::error('Xendit charge error', ['status' => $response->status(), 'body' => $errorBody]);

            return new PaymentResponse(false, $errorMessage);
        } catch (\Exception $e) {
            Log::error('Xendit charge exception', ['error' => $e->getMessage()]);
            return new PaymentResponse(false, 'Gagal terhubung ke Xendit: ' . $e->getMessage());
        }
    }

    /**
     * Handle callback/webhook from Xendit.
     */
    public function callback(Request $request): CallbackResponse
    {
        // Verify callback token
        $callbackToken = $this->settings->get('xendit_callback_token');
        $headerToken = $request->header('x-callback-token');

        if ($callbackToken && $headerToken !== $callbackToken) {
            Log::warning('Xendit callback: invalid token', [
                'expected' => substr($callbackToken, 0, 5) . '...',
                'received' => substr($headerToken ?? '', 0, 5) . '...',
            ]);
            return new CallbackResponse(false, message: 'Invalid callback token');
        }

        $externalId = $request->input('external_id');
        $status = $request->input('status');
        $paidAmount = $request->input('paid_amount');
        $paymentMethod = $request->input('payment_method');
        $paymentChannel = $request->input('payment_channel');

        Log::info('Xendit callback received', [
            'external_id' => $externalId,
            'status' => $status,
            'paid_amount' => $paidAmount,
            'payment_method' => $paymentMethod,
            'payment_channel' => $paymentChannel,
        ]);

        if (!$externalId) {
            return new CallbackResponse(false, message: 'Missing external_id');
        }

        // Map Xendit status to our status
        $mappedStatus = match (strtoupper($status ?? '')) {
            'PAID', 'SETTLED' => 'paid',
            'EXPIRED' => 'expired',
            default => 'failed',
        };

        return new CallbackResponse(
            success: $mappedStatus === 'paid',
            orderNumber: $externalId,
            status: $mappedStatus,
            message: "Xendit callback: {$status}",
            metadata: [
                'xendit_status' => $status,
                'paid_amount' => $paidAmount,
                'payment_method' => $paymentMethod,
                'payment_channel' => $paymentChannel,
            ],
        );
    }

}
