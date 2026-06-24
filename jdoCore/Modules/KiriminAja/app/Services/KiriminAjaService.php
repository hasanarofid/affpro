<?php

namespace Modules\KiriminAja\app\Services;

use App\Contracts\CourierInterface;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * KiriminAja Mitra API client.
 *
 * Implements the CourierInterface contract so it can be plugged into
 * App\Services\ShippingService alongside (or instead of) RajaOngkir.
 *
 * Endpoints used (Mitra API v6/v7):
 *  - POST /api/v6/check_pricing_domestic
 *  - GET  /api/v6/district?search=...
 *  - POST /api/v6/awb (tracking)
 *  - POST /api/v6/request_pickup
 *  - POST /api/v6/cancel_pickup
 *  - GET  /api/v6/expedition (list)
 *  - GET  /api/v6/check_balance
 *  - GET  /api/v6/schedules
 *
 * Reference: https://developer.kiriminaja.com/docs/introduction
 */
class KiriminAjaService implements CourierInterface
{
    public function __construct(protected SettingService $settings)
    {
    }

    // ---------------------------------------------------------------------
    // Configuration helpers (read from settings table, group=shipping)
    // ---------------------------------------------------------------------

    protected function apiKey(): string
    {
        return (string) $this->settings->get('kiriminaja_api_key', '');
    }

    protected function mode(): string
    {
        $mode = strtolower((string) $this->settings->get('kiriminaja_mode', 'staging'));
        return in_array($mode, ['staging', 'production'], true) ? $mode : 'staging';
    }

    protected function baseUrl(): string
    {
        $endpoints = config('kiriminaja.endpoints', [
            'staging'    => 'https://tdev.kiriminaja.com',
            'production' => 'https://client.kiriminaja.com',
        ]);
        return rtrim($endpoints[$this->mode()] ?? $endpoints['staging'], '/');
    }

    protected function originId(): int
    {
        return (int) $this->settings->get('kiriminaja_origin_id', 0);
    }

    /**
     * @return array<int,string>
     */
    protected function enabledCouriers(): array
    {
        $val = $this->settings->get('kiriminaja_couriers', '["jne","jnt","sicepat","anteraja","ide"]');
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            return is_array($decoded) ? array_values(array_filter(array_map('strval', $decoded))) : ['jne', 'jnt'];
        }
        return is_array($val) ? array_values($val) : ['jne', 'jnt'];
    }

    // ---------------------------------------------------------------------
    // CourierInterface
    // ---------------------------------------------------------------------

    public function getName(): string
    {
        return 'KiriminAja';
    }

    public function getProviderKey(): string
    {
        return 'kiriminaja';
    }

    public function isConfigured(): bool
    {
        return $this->apiKey() !== '';
    }

    /**
     * Get available shipping rates (express domestic).
     *
     * `$origin` and `$destination` accept array with keys:
     *   - city_id  (we map this to KiriminAja's kecamatan_id / district id)
     */
    public function getRates(array $origin, array $destination, int $weight, ?string $courier = null): array
    {
        $originId = (int) ($origin['city_id'] ?? $this->originId());
        $destId   = (int) ($destination['city_id'] ?? 0);
        $weightG  = max($weight, 1);

        if (!$originId || !$destId) {
            return [];
        }

        $couriers = $courier ? [strtolower($courier)] : $this->enabledCouriers();

        $payload = [
            'origin'      => $originId,
            'destination' => $destId,
            'weight'      => $weightG,
            'item_value'  => (int) ($destination['item_value'] ?? 0),
            'insurance'   => 0,
            'courier'     => $couriers,
        ];

        $response = $this->request('POST', '/api/v6/check_pricing_domestic', $payload);

        return $this->normalizePricing($response);
    }

    /**
     * Track shipment by AWB / order ID.
     */
    public function track(string $courier, string $trackingNumber): array
    {
        $response = $this->request('POST', '/api/v6/awb', [
            'awb'     => $trackingNumber,
            'courier' => strtolower($courier),
        ]);

        return $response['data'] ?? $response['result'] ?? $response ?? [];
    }

    /**
     * Search destination (district / kecamatan) by name.
     *
     * Returns rows compatible with the front-end Select2 used in checkout:
     *   [ ['id' => 123, 'label' => 'Kecamatan, Kota, Provinsi 12345', 'postal_code' => '12345'], ... ]
     */
    public function searchDestination(string $search): array
    {
        if (!$this->isConfigured() || strlen(trim($search)) < 3) {
            return [];
        }

        $cacheKey = 'kiriminaja.dest.' . md5(strtolower($search));
        $ttl = (int) config('kiriminaja.cache_ttl', 3600);

        return Cache::remember($cacheKey, $ttl, function () use ($search) {
            $response = $this->request('GET', '/api/v6/district', [
                'search' => $search,
            ]);

            $rows = $response['data'] ?? $response['result'] ?? [];
            $out = [];

            foreach ($rows as $row) {
                $id = $row['id'] ?? $row['kecamatan_id'] ?? $row['district_id'] ?? null;
                if (!$id) {
                    continue;
                }

                $district = $row['kecamatan'] ?? $row['district'] ?? $row['name'] ?? '';
                $city     = $row['kota'] ?? $row['kabupaten'] ?? $row['city'] ?? '';
                $province = $row['provinsi'] ?? $row['province'] ?? '';
                $postal   = (string) ($row['kodepos'] ?? $row['postal_code'] ?? '');

                $label = trim(implode(', ', array_filter([$district, $city, $province])));
                if ($postal !== '') {
                    $label .= ' ' . $postal;
                }

                $out[] = [
                    'id'           => (int) $id,
                    'label'        => $label,
                    'subdistrict'  => $district,
                    'city'         => $city,
                    'province'     => $province,
                    'postal_code'  => $postal,
                ];
            }

            return $out;
        });
    }

    // ---------------------------------------------------------------------
    // Optional flow methods (pickup / cancel / balance)
    // ---------------------------------------------------------------------

    public function requestPickup(array $payload): array
    {
        return $this->request('POST', '/api/v6/request_pickup', $payload);
    }

    public function cancelPickup(string $orderId, string $reason = ''): array
    {
        return $this->request('POST', '/api/v6/cancel_pickup', [
            'order_id' => $orderId,
            'reason'   => $reason ?: 'Cancelled by store',
        ]);
    }

    public function balance(): array
    {
        $response = $this->request('GET', '/api/v6/check_balance');
        return $response['data'] ?? $response['result'] ?? $response ?? [];
    }

    public function schedules(): array
    {
        $response = $this->request('GET', '/api/v6/schedules');
        return $response['data'] ?? $response['result'] ?? $response ?? [];
    }

    // ---------------------------------------------------------------------
    // Internal helpers
    // ---------------------------------------------------------------------

    /**
     * Normalize pricing response from KiriminAja into the same flat shape
     * used by the front-end checkout (RajaOngkir-compatible structure).
     *
     *  [
     *      ['courier_code', 'courier_name', 'service', 'description', 'cost', 'etd'],
     *      ...
     *  ]
     */
    protected function normalizePricing(array $response): array
    {
        $rows = $response['results'] ?? $response['data'] ?? [];
        if (!is_array($rows)) {
            return [];
        }

        $out = [];

        foreach ($rows as $row) {
            // Each row may be a courier service block.
            $code  = strtolower((string) ($row['service'] ?? $row['courier'] ?? $row['code'] ?? ''));
            $name  = (string) ($row['service_name'] ?? $row['courier_name'] ?? strtoupper($code));
            $type  = (string) ($row['service_type'] ?? $row['type'] ?? '');
            $desc  = (string) ($row['description'] ?? $row['service_description'] ?? $type);
            $cost  = (int) ($row['price'] ?? $row['cost'] ?? $row['total'] ?? 0);
            $etd   = (string) ($row['etd'] ?? $row['estimation'] ?? $row['estimated_delivery'] ?? '-');

            if ($cost <= 0 || $code === '') {
                continue;
            }

            $out[] = [
                'courier_code' => $code,
                'courier_name' => $name ?: strtoupper($code),
                'service'      => $type !== '' ? $type : ($row['service_name'] ?? strtoupper($code)),
                'description'  => $desc,
                'cost'         => $cost,
                'etd'          => $etd,
            ];
        }

        usort($out, fn($a, $b) => $a['cost'] <=> $b['cost']);

        return $out;
    }

    /**
     * HTTP request helper with bearer auth and consistent error handling.
     */
    protected function request(string $method, string $endpoint, array $data = []): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('KiriminAja: API key belum dikonfigurasi.');
        }

        $url = $this->baseUrl() . $endpoint;

        try {
            $http = Http::withoutVerifying()
                ->withToken($this->apiKey())
                ->acceptJson()
                ->timeout(20);

            if (strtoupper($method) === 'GET') {
                $response = $http->get($url, $data);
            } else {
                $response = $http->asJson()->post($url, $data);
            }

            $body = $response->json() ?? [];

            if (!$response->successful()) {
                $message = $body['message'] ?? $body['error'] ?? $response->body();
                Log::error('KiriminAja API error', [
                    'status'   => $response->status(),
                    'endpoint' => $endpoint,
                    'message'  => $message,
                ]);
                throw new \RuntimeException("KiriminAja: {$message}", $response->status());
            }

            // Mitra API often wraps responses with `{status: bool, message: string, ...}`.
            if (isset($body['status']) && $body['status'] === false) {
                $message = $body['message'] ?? 'Request gagal';
                throw new \RuntimeException("KiriminAja: {$message}");
            }

            return $body;
        } catch (\RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('KiriminAja request failed: ' . $e->getMessage(), ['endpoint' => $endpoint]);
            throw new \RuntimeException('KiriminAja: Koneksi gagal — ' . $e->getMessage());
        }
    }
}
