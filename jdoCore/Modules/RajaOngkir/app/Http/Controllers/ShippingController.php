<?php

namespace Modules\RajaOngkir\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\RajaOngkir\app\Services\RajaOngkirService;

class ShippingController extends Controller
{
    public function __construct(
        protected RajaOngkirService $rajaOngkir
    ) {
    }

    /**
     * GET /api/shipping/destinations?search=XXX
     */
    public function destinations(Request $request): JsonResponse
    {
        $request->validate(['search' => 'required|string|min:2']);
        try {
            $destinations = $this->rajaOngkir->searchDestination($request->search);
            return response()->json(['data' => $destinations]);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [],
                'error' => true,
                'message' => $e->getMessage()
            ], 200);
        }
    }

    /**
     * POST /api/shipping/cost
     */
    public function cost(Request $request): JsonResponse
    {
        $request->validate([
            'destination_city_id' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => 'nullable|string',
        ]);

        $originCityId = (int) app(\App\Services\SettingService::class)->get('origin_city_id', 0);
        $destCityId = $request->integer('destination_city_id');
        $weight = $request->integer('weight');

        \Illuminate\Support\Facades\Log::info('ShippingCost request', [
            'origin' => $originCityId,
            'dest' => $destCityId,
            'weight' => $weight,
        ]);

        try {
            if ($request->courier) {
                $rawResults = $this->rajaOngkir->checkCost($originCityId, $destCityId, $weight, $request->courier);
            } else {
                $rawResults = $this->rajaOngkir->checkAllCouriers($destCityId, $weight);
            }
        } catch (\RuntimeException $e) {
            return response()->json([
                'data' => [],
                'error' => true,
                'message' => $e->getMessage(),
            ], 200); // Return 200 so frontend can parse JSON
        }

        // Handle both flat and nested structures gracefully.
        $options = [];
        foreach ($rawResults as $item) {
            // Flat structure (Komerce format): has courier_code, service, cost directly
            if (isset($item['courier_code']) || isset($item['service'])) {
                $options[] = [
                    'courier_code' => $item['courier_code'] ?? $item['code'] ?? '',
                    'courier_name' => $item['courier_name'] ?? $item['name'] ?? '',
                    'service' => $item['service'] ?? '',
                    'description' => $item['description'] ?? '',
                    'cost' => $item['cost'] ?? $item['value'] ?? 0,
                    'etd' => $item['etd'] ?? '-',
                ];
            }
            // Nested structure (classic RajaOngkir format): has 'costs' key
            elseif (isset($item['costs'])) {
                foreach ($item['costs'] as $service) {
                    foreach ($service['cost'] ?? [] as $cost) {
                        $options[] = [
                            'courier_code' => $item['code'] ?? '',
                            'courier_name' => $item['name'] ?? '',
                            'service' => $service['service'] ?? '',
                            'description' => $service['description'] ?? '',
                            'cost' => $cost['value'] ?? 0,
                            'etd' => $cost['etd'] ?? '-',
                        ];
                    }
                }
            }
        }

        usort($options, fn($a, $b) => $a['cost'] <=> $b['cost']);

        return response()->json(['data' => $options]);

    }

    /**
     * POST /api/shipping/track
     */
    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'courier' => 'required|string',
        ]);

        $result = $this->rajaOngkir->track($request->courier, $request->tracking_number);
        return response()->json(['data' => $result]);
    }
}
