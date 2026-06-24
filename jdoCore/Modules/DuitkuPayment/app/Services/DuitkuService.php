<?php

namespace Modules\DuitkuPayment\app\Services;

use App\Contracts\PaymentInterface;
use App\Contracts\PaymentResponse;
use App\Contracts\CallbackResponse;
use App\Models\Order;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DuitkuService implements PaymentInterface
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
        return 'duitku';
    }

    /**
     * Check if production mode.
     */
    protected function isProduction(): bool
    {
        return $this->settings->get('duitku_mode', 'sandbox') === 'production';
    }

    /**
     * Get the create invoice URL.
     */
    protected function getCreateInvoiceUrl(): string
    {
        $mode = $this->isProduction() ? 'production' : 'sandbox';
        return config("duitkupayment.create_invoice_url.{$mode}");
    }

    /**
     * Get the check transaction URL.
     */
    protected function getCheckTransactionUrl(): string
    {
        $mode = $this->isProduction() ? 'production' : 'sandbox';
        return config("duitkupayment.check_transaction_url.{$mode}");
    }

    /**
     * Get merchant code.
     */
    protected function getMerchantCode(): string
    {
        return $this->settings->get('duitku_merchant_code', '');
    }

    /**
     * Get merchant key.
     */
    protected function getMerchantKey(): string
    {
        return $this->settings->get('duitku_merchant_key', '');
    }

    /**
     * Get available payment channels.
     */
    public function getChannels(): array
    {
        $merchantCode = $this->getMerchantCode();
        $merchantKey = $this->getMerchantKey();

        if ($merchantCode === '' || $merchantKey === '') {
            return $this->getDefaultChannels();
        }

        $cacheKey = 'payment_channels.duitku.' . md5(($this->isProduction() ? 'production' : 'sandbox') . '|' . $merchantCode);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($merchantCode, $merchantKey) {
            try {
                $paymentAmount = 10000;
                $datetime = now()->format('Y-m-d H:i:s');
                $signature = md5($merchantCode . $paymentAmount . $datetime . $merchantKey);

                $mode = $this->isProduction() ? 'production' : 'sandbox';
                $response = Http::timeout(30)->post(config("duitkupayment.get_payment_method_url.{$mode}"), [
                    'merchantcode' => $merchantCode,
                    'amount' => $paymentAmount,
                    'datetime' => $datetime,
                    'signature' => $signature,
                ]);

                if (!$response->successful()) {
                    Log::warning('Duitku channels HTTP error', ['status' => $response->status(), 'body' => $response->json()]);
                    return $this->getDefaultChannels();
                }

                $payload = $response->json();
                if (!is_array($payload)) {
                    Log::warning('Duitku channels invalid payload', ['body' => $response->body()]);
                    return $this->getDefaultChannels();
                }

                $channels = collect($payload)
                    ->map(fn($channel) => $channel['paymentMethod'] ?? $channel['paymentMethodCode'] ?? $channel['payment_method'] ?? null)
                    ->filter(fn($code) => is_string($code) && $code !== '')
                    ->unique()
                    ->values()
                    ->all();

                return !empty($channels) ? $channels : $this->getDefaultChannels();
            } catch (\Throwable $e) {
                Log::warning('Duitku channels fetch exception', ['error' => $e->getMessage()]);
                return $this->getDefaultChannels();
            }
        });
    }

    /**
     * Get all supported channel definitions (for admin UI).
     * Ref: https://docs.duitku.com/pop/id/#payment-method
     */
    public function getAllChannelDefinitions(): array
    {
        return [
            // Credit Card
            ['code' => 'VC', 'name' => 'Kartu Kredit (Visa/Master)', 'type' => 'credit_card', 'icon' => 'bi-credit-card'],

            // Virtual Account
            ['code' => 'BC', 'name' => 'BCA Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'M2', 'name' => 'Mandiri Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'VA', 'name' => 'Maybank Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'I1', 'name' => 'BNI Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'B1', 'name' => 'CIMB Niaga Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'BT', 'name' => 'Permata Bank Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'A1', 'name' => 'ATM Bersama', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'AG', 'name' => 'Bank Artha Graha', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'NC', 'name' => 'Bank Neo Commerce / BNC', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'BR', 'name' => 'BRI Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'S1', 'name' => 'Bank Sahabat Sampoerna', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'DM', 'name' => 'Danamon Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'BV', 'name' => 'BSI Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],

            // E-Wallet
            ['code' => 'OV', 'name' => 'OVO', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'SA', 'name' => 'ShopeePay', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'LF', 'name' => 'LinkAja', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'DA', 'name' => 'DANA', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'LA', 'name' => 'LinkAja App (Fixed)', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'NQ', 'name' => 'Nobu E-Wallet', 'type' => 'ewallet', 'icon' => 'bi-phone'],

            // QRIS
            ['code' => 'SP', 'name' => 'ShopeePay QRIS', 'type' => 'qris', 'icon' => 'bi-qr-code'],
            ['code' => 'LQ', 'name' => 'LinkAja QRIS', 'type' => 'qris', 'icon' => 'bi-qr-code'],
            ['code' => 'NQ', 'name' => 'Nobu QRIS', 'type' => 'qris', 'icon' => 'bi-qr-code'],

            // Retail / Convenience Store
            ['code' => 'FT', 'name' => 'Pegadaian / Alfa Group', 'type' => 'retail', 'icon' => 'bi-shop'],
            ['code' => 'IR', 'name' => 'Indomaret', 'type' => 'retail', 'icon' => 'bi-shop'],
        ];
    }

    /**
     * Create a Duitku Pop invoice for the given order.
     */
    public function charge(Order $order, array $options = []): PaymentResponse
    {
        $merchantCode = $this->getMerchantCode();
        $merchantKey = $this->getMerchantKey();

        if (empty($merchantCode) || empty($merchantKey)) {
            return new PaymentResponse(false, 'Merchant Code atau Merchant Key Duitku belum dikonfigurasi.');
        }

        $paymentAmount = (int) $order->total;
        $merchantOrderId = $order->order_number;
        $expiryPeriod = config('duitkupayment.expiry_period', 1440);

        // Generate signature: MD5(merchantCode + merchantOrderId + paymentAmount + merchantKey)
        $signature = md5($merchantCode . $merchantOrderId . $paymentAmount . $merchantKey);

        // Build item details
        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'name' => mb_substr($item->product_name, 0, 50),
                'price' => (int) $item->price,
                'quantity' => $item->quantity,
            ];
        }

        if ($order->shipping_cost > 0) {
            $itemDetails[] = [
                'name' => 'Ongkos Kirim',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
            ];
        }

        if ($order->discount_amount > 0) {
            $itemDetails[] = [
                'name' => 'Diskon',
                'price' => -1 * (int) $order->discount_amount,
                'quantity' => 1,
            ];
        }

        // Customer detail
        $customerName = $order->user ? $order->user->name : ($order->guest_name ?? 'Guest');
        $customerEmail = $order->user ? $order->user->email : ($order->guest_email ?? '');
        $customerPhone = $order->user ? ($order->user->phone ?? '') : ($order->guest_phone ?? '');

        // Shipping address
        $shippingAddress = '';
        $shippingCity = '';
        $shippingPostalCode = '';
        if (!empty($order->shipping_address) && is_array($order->shipping_address)) {
            $sa = $order->shipping_address;
            $shippingAddress = $sa['address'] ?? '';
            $shippingCity = $sa['city'] ?? '';
            $shippingPostalCode = $sa['postal_code'] ?? '';
        }

        $productDetails = 'Pembayaran pesanan ' . $merchantOrderId;

        // Build payload for Duitku Pop createInvoice
        $payload = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $paymentAmount,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => '',
            'merchantUserInfo' => $customerName,
            'customerVaName' => $customerName,
            'email' => $customerEmail,
            'phoneNumber' => $customerPhone,
            'itemDetails' => $itemDetails,
            'customerDetail' => [
                'firstName' => $customerName,
                'lastName' => '',
                'email' => $customerEmail,
                'phoneNumber' => $customerPhone,
                'shippingAddress' => [
                    'firstName' => $customerName,
                    'address' => $shippingAddress,
                    'city' => $shippingCity,
                    'postalCode' => $shippingPostalCode,
                    'countryCode' => 'ID',
                ],
            ],
            'callbackUrl' => url('/api/payment/duitku/callback'),
            'returnUrl' => route('orders.success', $order->order_number),
            'expiryPeriod' => $expiryPeriod,
            'signature' => $signature,
        ];

        try {
            $response = Http::timeout(30)
                ->post($this->getCreateInvoiceUrl(), $payload);

            if ($response->successful()) {
                $data = $response->json();

                $statusCode = $data['statusCode'] ?? '';
                $statusMessage = $data['statusMessage'] ?? '';

                if ($statusCode === '00' || $statusCode === '000') {
                    return new PaymentResponse(
                        success: true,
                        message: 'Invoice Duitku berhasil dibuat.',
                        redirectUrl: $data['paymentUrl'] ?? null,
                        providerRef: $data['reference'] ?? null,
                        metadata: [
                            'reference' => $data['reference'] ?? null,
                            'payment_url' => $data['paymentUrl'] ?? null,
                            'status_code' => $statusCode,
                            'status_message' => $statusMessage,
                        ],
                    );
                }

                Log::error('Duitku createInvoice error', ['status' => $statusCode, 'message' => $statusMessage, 'body' => $data]);
                return new PaymentResponse(false, "Duitku: {$statusMessage} (Code: {$statusCode})");
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['Message'] ?? $errorBody['statusMessage'] ?? 'Gagal membuat invoice Duitku.';
            Log::error('Duitku charge HTTP error', ['status' => $response->status(), 'body' => $errorBody]);

            return new PaymentResponse(false, $errorMessage);
        } catch (\Exception $e) {
            Log::error('Duitku charge exception', ['error' => $e->getMessage()]);
            return new PaymentResponse(false, 'Gagal terhubung ke Duitku: ' . $e->getMessage());
        }
    }

    /**
     * Handle callback/notification from Duitku.
     */
    public function callback(Request $request): CallbackResponse
    {
        $merchantCode = $this->getMerchantCode();
        $merchantKey = $this->getMerchantKey();

        $merchantOrderId = $request->input('merchantOrderId');
        $amount = $request->input('amount');
        $resultCode = $request->input('resultCode');
        $reference = $request->input('reference');
        $signature = $request->input('signature');
        $publisherOrderId = $request->input('publisherOrderId');
        $spUserHash = $request->input('spUserHash');

        Log::info('Duitku callback received', [
            'merchantOrderId' => $merchantOrderId,
            'resultCode' => $resultCode,
            'amount' => $amount,
            'reference' => $reference,
        ]);

        // Verify signature: MD5(merchantCode + amount + merchantOrderId + merchantKey)
        $expectedSignature = md5($merchantCode . $amount . $merchantOrderId . $merchantKey);
        if ($signature !== $expectedSignature) {
            Log::warning('Duitku callback: invalid signature', [
                'merchantOrderId' => $merchantOrderId,
                'expected' => substr($expectedSignature, 0, 10) . '...',
                'received' => substr($signature ?? '', 0, 10) . '...',
            ]);
            return new CallbackResponse(false, message: 'Invalid signature');
        }

        if (!$merchantOrderId) {
            return new CallbackResponse(false, message: 'Missing merchantOrderId');
        }

        // Map Duitku result codes
        // 00 = Success, 01 = Pending, 02 = Cancelled/Failed
        $mappedStatus = match ($resultCode) {
            '00' => 'paid',
            '01' => 'pending',
            '02' => 'failed',
            default => 'failed',
        };

        return new CallbackResponse(
            success: $mappedStatus === 'paid',
            orderNumber: $merchantOrderId,
            status: $mappedStatus,
            message: "Duitku callback: resultCode={$resultCode}",
            metadata: [
                'result_code' => $resultCode,
                'reference' => $reference,
                'publisher_order_id' => $publisherOrderId,
                'amount' => $amount,
            ],
        );
    }

    /**
     * Default channels if none configured.
     */
    protected function getDefaultChannels(): array
    {
        return ['BC', 'M2', 'I1', 'BR', 'BT', 'OV', 'SA', 'DA', 'SP', 'FT', 'IR'];
    }
}
