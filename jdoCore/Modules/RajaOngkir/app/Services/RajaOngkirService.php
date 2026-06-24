<?php

namespace Modules\RajaOngkir\app\Services;

use App\Contracts\CourierInterface;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajaOngkirService implements CourierInterface
{
    protected SettingService $settings;

    protected static array $baseUrls = [
        'komerce' => 'https://rajaongkir.komerce.id/api/v1',
        'starter' => 'https://api.rajaongkir.com/starter',
        'basic' => 'https://api.rajaongkir.com/basic',
        'pro' => 'https://pro.rajaongkir.com/api',
    ];

    public function __construct(SettingService $settings)
    {
        $this->settings = $settings;
    }

    // ---- Dynamic config from Settings table ----

    protected function apiKey(): string
    {
        return (string) $this->settings->get('rajaongkir_api_key', '');
    }

    protected function accountType(): string
    {
        $type = (string) $this->settings->get('rajaongkir_type', 'komerce');
        return $type ?: 'komerce';
    }

    protected function baseUrl(): string
    {
        $type = $this->accountType();
        return self::$baseUrls[$type] ?? self::$baseUrls['komerce'];
    }

    protected function originCityId(): int
    {
        return (int) $this->settings->get('origin_city_id', 0);
    }

    protected function enabledCouriers(): array
    {
        $val = $this->settings->get('enabled_couriers', '["jne","pos","tiki"]');
        if (is_string($val)) {
            return json_decode($val, true) ?: ['jne', 'pos', 'tiki'];
        }
        return is_array($val) ? $val : ['jne', 'pos', 'tiki'];
    }

    // ---- CourierInterface Implementation ----

    public function getName(): string
    {
        return 'RajaOngkir';
    }

    public function getProviderKey(): string
    {
        return 'rajaongkir';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey());
    }

    public function getRates(array $origin, array $destination, int $weight, ?string $courier = null): array
    {
        $originCity = $origin['city_id'] ?? $this->originCityId();
        $destCity = $destination['city_id'] ?? 0;
        $weightGrams = max($weight, 1);

        if (!$destCity) {
            return [];
        }

        $results = [];
        $couriers = $courier ? [$courier] : $this->enabledCouriers();

        $lastException = null;

        foreach ($couriers as $c) {
            try {
                $costs = $this->checkCost($originCity, $destCity, $weightGrams, $c);

                // Komerce API returns flat: [{code, name, service, description, cost, etd}]
                // Standard RajaOngkir returns nested: [{code, name, costs:[{service, cost:[{value,etd}]}]}]
                $isKomerce = $this->accountType() === 'komerce';

                foreach ($costs as $item) {
                    if ($isKomerce) {
                        // Flat format from Komerce
                        $results[] = [
                            'courier_code' => $item['code'] ?? $c,
                            'courier_name' => $item['name'] ?? strtoupper($c),
                            'service'      => $item['service'] ?? '',
                            'description'  => $item['description'] ?? '',
                            'cost'         => $item['cost'] ?? 0,
                            'etd'          => $item['etd'] ?? '-',
                        ];
                    } else {
                        // Nested format from standard RajaOngkir
                        foreach ($item['costs'] ?? [] as $service) {
                            foreach ($service['cost'] ?? [] as $cost) {
                                $results[] = [
                                    'courier_code' => $item['code'] ?? $c,
                                    'courier_name' => $item['name'] ?? strtoupper($c),
                                    'service'      => $service['service'] ?? '',
                                    'description'  => $service['description'] ?? '',
                                    'cost'         => $cost['value'] ?? 0,
                                    'etd'          => $cost['etd'] ?? '-',
                                ];
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("RajaOngkir error for courier {$c}: " . $e->getMessage());
            }
        }

        if (empty($results) && $lastException) {
            throw $lastException;
        }

        usort($results, fn($a, $b) => $a['cost'] <=> $b['cost']);

        return $results;
    }

    public function track(string $courier, string $trackingNumber): array
    {
        $type = $this->accountType();
        
        if ($type === 'komerce') {
            $response = $this->request('POST', '/track/waybill', [
                'awb' => $trackingNumber,
                'courier' => strtolower($courier),
            ]);
            return $response['data'] ?? [];
        }

        if ($type === 'starter') {
            return ['error' => 'Akun Starter tidak mendukung fitur lacak resi otomatis dari API.'];
        }

        $response = $this->request('POST', '/waybill', [
            'waybill' => $trackingNumber,
            'courier' => strtolower($courier),
        ]);

        return $response['rajaongkir']['result'] ?? [];
    }

    // ---- Additional Methods ----

    /**
     * Get destinations by searching (Komerce API) or filtering in-memory for standard API.
     */
    public function searchDestination(string $search): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $type = $this->accountType();
        $cacheKey = 'rajaongkir.dest.' . $type . '.' . md5($search . $this->apiKey());

        return Cache::remember($cacheKey, 3600, function () use ($search, $type) {
            if ($type === 'komerce') {
                $response = $this->request('GET', '/destination/domestic-destination', [
                    'search' => $search,
                    'limit' => 20,
                    'offset' => 0,
                ]);
                return $response['data'] ?? [];
            }
            
            // Standard RajaOngkir (Starter/Basic) only has /city endpoint without search. 
            $citiesCacheKey = 'rajaongkir.cities.' . $type . '.' . md5($this->apiKey());
            $cities = Cache::remember($citiesCacheKey, 86400, function () {
                $response = $this->request('GET', '/city');
                return $response['rajaongkir']['results'] ?? [];
            });
            
            $searchLower = strtolower($search);
            $filtered = array_filter($cities, function($city) use ($searchLower) {
                return str_contains(strtolower($city['city_name']), $searchLower) 
                    || str_contains(strtolower($city['province'] ?? ''), $searchLower);
            });
            
            $results = [];
            foreach(array_slice($filtered, 0, 20) as $item) {
                $label = ($item['type'] ?? '') . ' ' . $item['city_name'] . ', ' . $item['province'];
                $results[] = [
                    'id' => $item['city_id'],
                    'city_id' => $item['city_id'],
                    'province_id' => $item['province_id'],
                    'city_name' => $item['city_name'],
                    'province' => $item['province'],
                    'postal_code' => $item['postal_code'],
                    'label' => trim($label)
                ];
            }
            
            return $results;
        });
    }

    /**
     * Check shipping cost.
     */
    public function checkCost(int $originCityId, int $destinationId, int $weight, string $courier): array
    {
        $type = $this->accountType();
        $cacheKey = "rajaongkir.cost.{$type}.{$originCityId}.{$destinationId}.{$weight}.{$courier}";

        return Cache::remember($cacheKey, 3600, function () use ($originCityId, $destinationId, $weight, $courier, $type) {
            if ($type === 'komerce') {
                $response = $this->request('POST', '/calculate/domestic-cost', [
                    'origin' => $originCityId,
                    'destination' => $destinationId,
                    'weight' => max($weight, 1),
                    'courier' => $courier,
                ]);

                return $response['data'] ?? [];
            }
            
            // Standard RajaOngkir cost
            $response = $this->request('POST', '/cost', [
                'origin' => $originCityId,
                'destination' => $destinationId,
                'weight' => max($weight, 1),
                'courier' => $courier,
            ]);
            
            return $response['rajaongkir']['results'] ?? [];
        });
    }

    /**
     * Check cost for all configured couriers.
     */
    public function checkAllCouriers(int $destinationCityId, int $weight): array
    {
        $originCityId = $this->originCityId();
        $couriers = $this->enabledCouriers();
        $results = [];

        foreach ($couriers as $courier) {
            try {
                $cost = $this->checkCost($originCityId, $destinationCityId, $weight, $courier);
                $results = array_merge($results, $cost);
            } catch (\Exception $e) {
                Log::warning("RajaOngkir error for courier {$courier}: " . $e->getMessage());
            }
        }

        return $results;
    }

    // ---- HTTP Helper ----

    protected function request(string $method, string $endpoint, array $data = []): array
    {
        $url = rtrim($this->baseUrl(), '/') . $endpoint;

        try {
            $http = Http::withoutVerifying()->withHeaders(['key' => $this->apiKey()])
                ->timeout(15);

            if ($method === 'POST') {
                if ($endpoint === '/track/waybill') {
                    $response = $http->post($url . '?' . http_build_query($data));
                } else {
                    $response = $http->asForm()->post($url, $data);
                }
            } else {
                $response = $http->get($url, $data);
            }

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            // Extract error message from API response
            $body = $response->json();
            $apiMessage = $body['meta']['message'] ?? $response->body();
            Log::error("RajaOngkir API error [{$response->status()}]: {$apiMessage}");

            // Throw exception so callers can handle it properly
            throw new \RuntimeException("RajaOngkir: {$apiMessage}", $response->status());

        } catch (\RuntimeException $e) {
            // Re-throw RuntimeException (our own) so it propagates
            throw $e;
        } catch (\Exception $e) {
            Log::error("RajaOngkir request failed: " . $e->getMessage());
            throw new \RuntimeException("RajaOngkir: Koneksi gagal - " . $e->getMessage());
        }
    }
}
