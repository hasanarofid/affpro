<?php

namespace Modules\IpaymuPayment\app\Services;

use App\Contracts\CallbackResponse;
use App\Contracts\PaymentInterface;
use App\Contracts\PaymentResponse;
use App\Models\Order;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpaymuService implements PaymentInterface
{
    protected SettingService $settings;

    public function __construct(SettingService $settings)
    {
        $this->settings = $settings;
    }

    public function getName(): string
    {
        return 'ipaymu';
    }

    protected function isProduction(): bool
    {
        return $this->settings->get('ipaymu_mode', 'sandbox') === 'production';
    }

    protected function getBaseUrl(): string
    {
        $mode = $this->isProduction() ? 'production' : 'sandbox';
        return rtrim(config("ipaymupayment.base_url.{$mode}"), '/');
    }

    protected function getVa(): string
    {
        return (string) $this->settings->get('ipaymu_va', '');
    }

    protected function getApiKey(): string
    {
        return (string) $this->settings->get('ipaymu_api_key', '');
    }

    public function getChannels(): array
    {
        $va = $this->getVa();
        $apiKey = $this->getApiKey();

        if ($va === '' || $apiKey === '') {
            return $this->getDefaultChannels();
        }

        $cacheKey = 'payment_channels.ipaymu.' . md5(($this->isProduction() ? 'production' : 'sandbox') . '|' . $va);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            try {
                $response = $this->signedRequest('GET', config('ipaymupayment.channels_path'));

                if (!$response->successful()) {
                    Log::warning('iPaymu channels HTTP error', ['status' => $response->status(), 'body' => $response->json()]);
                    return $this->getDefaultChannels();
                }

                $data = $response->json();
                $status = (int) ($data['Status'] ?? $data['status'] ?? 0);
                $payload = $data['Data'] ?? $data['data'] ?? [];

                if ($status !== 200 || !is_array($payload)) {
                    Log::warning('iPaymu channels invalid payload', ['body' => $data]);
                    return $this->getDefaultChannels();
                }

                $channels = collect($payload)
                    ->map(fn($channel) => $this->normalizeChannelCode($channel))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                return !empty($channels) ? $channels : $this->getDefaultChannels();
            } catch (\Throwable $e) {
                Log::warning('iPaymu channels fetch exception', ['error' => $e->getMessage()]);
                return $this->getDefaultChannels();
            }
        });
    }

    public function getAllChannelDefinitions(): array
    {
        return [
            ['code' => 'va:bca', 'name' => 'BCA Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'va:bni', 'name' => 'BNI Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'va:bri', 'name' => 'BRI Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'va:mandiri', 'name' => 'Mandiri Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'va:permata', 'name' => 'Permata Virtual Account', 'type' => 'virtual_account', 'icon' => 'bi-bank'],
            ['code' => 'qris:qris', 'name' => 'QRIS', 'type' => 'qris', 'icon' => 'bi-qr-code'],
            ['code' => 'wallet:ovo', 'name' => 'OVO', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'wallet:dana', 'name' => 'DANA', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'wallet:shopeepay', 'name' => 'ShopeePay', 'type' => 'ewallet', 'icon' => 'bi-phone'],
            ['code' => 'retail:indomaret', 'name' => 'Indomaret', 'type' => 'retail', 'icon' => 'bi-shop'],
            ['code' => 'retail:alfamart', 'name' => 'Alfamart', 'type' => 'retail', 'icon' => 'bi-shop'],
        ];
    }

    public function charge(Order $order, array $options = []): PaymentResponse
    {
        $va = $this->getVa();
        $apiKey = $this->getApiKey();

        if ($va === '' || $apiKey === '') {
            return new PaymentResponse(false, 'VA atau API Key iPaymu belum dikonfigurasi.');
        }

        $customerName = trim((string) ($order->user->name ?? $order->guest_name ?? 'Customer'));
        $customerEmail = trim((string) ($order->user->email ?? $order->guest_email ?? ''));
        $customerPhone = trim((string) ($order->user->phone ?? $order->guest_phone ?? ''));

        $body = array_filter([
            'name' => $customerName,
            'phone' => $customerPhone,
            'email' => $customerEmail,
            'amount' => (float) $order->total,
            'notifyUrl' => url('/api/payment/ipaymu/callback'),
            'referenceId' => $order->order_number,
        ], fn($value) => $value !== null && $value !== '');

        try {
            $response = $this->signedRequest('POST', config('ipaymupayment.payment_path'), $body);

            if ($response->successful()) {
                $data = $response->json();
                $status = (int) ($data['Status'] ?? $data['status'] ?? 0);
                $payload = $data['Data'] ?? $data['data'] ?? [];

                if ($status === 200) {
                    return new PaymentResponse(
                        success: true,
                        message: 'Transaksi iPaymu berhasil dibuat.',
                        redirectUrl: $payload['Url'] ?? $payload['url'] ?? null,
                        providerRef: $payload['SessionID'] ?? $payload['sessionId'] ?? null,
                        metadata: [
                            'session_id' => $payload['SessionID'] ?? $payload['sessionId'] ?? null,
                            'url' => $payload['Url'] ?? $payload['url'] ?? null,
                            'reference_id' => $payload['ReferenceId'] ?? $payload['referenceId'] ?? $order->order_number,
                        ],
                    );
                }

                $message = $data['Message'] ?? $data['message'] ?? 'Gagal membuat transaksi iPaymu.';
                Log::error('iPaymu charge error', ['body' => $data]);
                return new PaymentResponse(false, $message);
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['Message'] ?? $errorBody['message'] ?? 'Gagal membuat transaksi iPaymu.';
            Log::error('iPaymu charge HTTP error', ['status' => $response->status(), 'body' => $errorBody]);

            return new PaymentResponse(false, $errorMessage);
        } catch (\Throwable $e) {
            Log::error('iPaymu charge exception', ['error' => $e->getMessage()]);
            return new PaymentResponse(false, 'Gagal terhubung ke iPaymu: ' . $e->getMessage());
        }
    }

    public function callback(Request $request): CallbackResponse
    {
        $referenceId = $request->input('referenceId') ?? $request->input('reference_id') ?? $request->input('merchantOrderId');
        $statusRaw = (string) ($request->input('status') ?? $request->input('status_transaction') ?? $request->input('transactionStatus') ?? $request->input('status_code') ?? '');
        $sessionId = $request->input('sessionId') ?? $request->input('SessionID');
        $transactionId = $request->input('transactionId') ?? $request->input('trx_id');
        $amount = $request->input('amount');

        Log::info('iPaymu callback received', [
            'referenceId' => $referenceId,
            'status' => $statusRaw,
            'sessionId' => $sessionId,
            'transactionId' => $transactionId,
        ]);

        if (!$referenceId) {
            return new CallbackResponse(false, message: 'Missing referenceId');
        }

        $normalizedStatus = strtoupper(trim($statusRaw));
        $paidStates = ['BERHASIL', 'SUCCESS', 'PAID', 'SETTLED'];
        $expiredStates = ['EXPIRED'];

        $mappedStatus = in_array($normalizedStatus, $paidStates, true)
            ? 'paid'
            : (in_array($normalizedStatus, $expiredStates, true) ? 'expired' : 'failed');

        return new CallbackResponse(
            success: $mappedStatus === 'paid',
            orderNumber: $referenceId,
            status: $mappedStatus,
            message: 'iPaymu callback: ' . ($statusRaw ?: 'unknown'),
            metadata: [
                'ipaymu_status' => $statusRaw,
                'session_id' => $sessionId,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'payload' => $request->all(),
            ],
        );
    }

    protected function signedRequest(string $method, string $path, array $body = [])
    {
        $jsonBody = empty($body) ? '{}' : json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $requestBody = strtolower(hash('sha256', $jsonBody));
        $stringToSign = strtoupper($method) . ':' . $this->getVa() . ':' . $requestBody . ':' . $this->getApiKey();
        $signature = hash_hmac('sha256', $stringToSign, $this->getApiKey());
        $timestamp = now()->format('YmdHis');

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'va' => $this->getVa(),
            'signature' => $signature,
            'timestamp' => $timestamp,
        ])->timeout(30)->send($method, $this->getBaseUrl() . $path, [
            'body' => $jsonBody,
        ]);
    }

    protected function normalizeChannelCode(array $channel): ?string
    {
        $method = strtolower((string) ($channel['PaymentMethod'] ?? $channel['paymentMethod'] ?? $channel['method'] ?? ''));
        $name = strtolower((string) ($channel['PaymentChannel'] ?? $channel['paymentChannel'] ?? $channel['channel'] ?? $channel['Name'] ?? $channel['name'] ?? ''));

        if ($method === '' && $name === '') {
            return null;
        }

        $normalizedName = str_replace([' ', '-', '/'], '_', $name);

        return match (true) {
            str_contains($method, 'va') || str_contains($name, 'virtual account') => match (true) {
                str_contains($normalizedName, 'bca') => 'va:bca',
                str_contains($normalizedName, 'bni') => 'va:bni',
                str_contains($normalizedName, 'bri') => 'va:bri',
                str_contains($normalizedName, 'mandiri') => 'va:mandiri',
                str_contains($normalizedName, 'permata') => 'va:permata',
                default => null,
            },
            str_contains($method, 'wallet') || str_contains($name, 'ovo') || str_contains($name, 'dana') || str_contains($name, 'shopee') => match (true) {
                str_contains($normalizedName, 'ovo') => 'wallet:ovo',
                str_contains($normalizedName, 'dana') => 'wallet:dana',
                str_contains($normalizedName, 'shopee') => 'wallet:shopeepay',
                default => null,
            },
            str_contains($method, 'qris') || str_contains($name, 'qris') => 'qris:qris',
            str_contains($method, 'retail') || str_contains($name, 'indomaret') || str_contains($name, 'alfamart') => match (true) {
                str_contains($normalizedName, 'indomaret') => 'retail:indomaret',
                str_contains($normalizedName, 'alfamart') => 'retail:alfamart',
                default => null,
            },
            default => null,
        };
    }

    protected function getDefaultChannels(): array
    {
        return ['va:bca', 'va:bni', 'qris:qris', 'wallet:ovo'];
    }
}