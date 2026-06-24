<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Provider-agnostic shipping endpoints used by storefront checkout.
 *
 * The active courier provider (rajaongkir | kiriminaja | ...) is resolved
 * via ShippingService based on the `active_courier_provider` setting.
 */
class ShippingController extends Controller
{
    public function __construct(
        protected ShippingService $shipping,
        protected SettingService $settings,
    ) {
    }

    /**
     * GET /api/shipping/destinations?search=jakarta
     */
    public function destinations(Request $request): JsonResponse
    {
        $request->validate(['search' => 'required|string|min:2']);

        try {
            $data = $this->shipping->searchDestination((string) $request->string('search'));
            return response()->json([
                'data' => $data,
                'provider' => $this->shipping->activeProviderKey(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'data' => [],
                'error' => true,
                'message' => $e->getMessage(),
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
            'item_value' => 'nullable|integer|min:0',
        ]);

        $providerKey = $this->shipping->activeProviderKey();
        $originId = $providerKey === 'kiriminaja'
            ? (int) $this->settings->get('kiriminaja_origin_id', 0)
            : (int) $this->settings->get('origin_city_id', 0);

        if (!$originId) {
            return response()->json([
                'data' => [],
                'error' => true,
                'message' => 'Lokasi asal pengiriman belum dikonfigurasi oleh admin.',
            ], 200);
        }

        try {
            $rates = $this->shipping->getRates(
                origin: ['city_id' => $originId],
                destination: [
                    'city_id'    => $request->integer('destination_city_id'),
                    'item_value' => $request->integer('item_value'),
                ],
                weight: $request->integer('weight'),
                courier: $request->input('courier'),
            );

            return response()->json([
                'data' => $rates,
                'provider' => $providerKey,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'data' => [],
                'error' => true,
                'message' => $e->getMessage(),
            ], 200);
        }
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

        try {
            $data = $this->shipping->track(
                (string) $request->string('courier'),
                (string) $request->string('tracking_number'),
            );
            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'data' => [],
                'error' => true,
                'message' => $e->getMessage(),
            ], 200);
        }
    }
}
