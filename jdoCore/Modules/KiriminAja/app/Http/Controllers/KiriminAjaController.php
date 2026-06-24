<?php

namespace Modules\KiriminAja\app\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\KiriminAja\app\Services\KiriminAjaService;

class KiriminAjaController extends Controller
{
    public function __construct(
        protected KiriminAjaService $kiriminAja,
        protected SettingService $settings,
    ) {
    }

    /**
     * GET /api/kiriminaja/destinations?search=jakarta
     */
    public function destinations(Request $request): JsonResponse
    {
        $request->validate(['search' => 'required|string|min:3']);

        try {
            $data = $this->kiriminAja->searchDestination($request->string('search'));
            return response()->json(['data' => $data]);
        } catch (\Throwable $e) {
            return response()->json([
                'data' => [],
                'error' => true,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * POST /api/kiriminaja/cost
     */
    public function cost(Request $request): JsonResponse
    {
        $request->validate([
            'destination_city_id' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => 'nullable|string',
            'item_value' => 'nullable|integer|min:0',
        ]);

        $originId = (int) $this->settings->get('kiriminaja_origin_id', 0);

        try {
            $rates = $this->kiriminAja->getRates(
                origin: ['city_id' => $originId],
                destination: [
                    'city_id'    => $request->integer('destination_city_id'),
                    'item_value' => $request->integer('item_value'),
                ],
                weight: $request->integer('weight'),
                courier: $request->input('courier'),
            );

            return response()->json(['data' => $rates]);
        } catch (\Throwable $e) {
            return response()->json([
                'data' => [],
                'error' => true,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * POST /api/kiriminaja/track
     */
    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'courier' => 'required|string',
        ]);

        try {
            $data = $this->kiriminAja->track(
                $request->string('courier'),
                $request->string('tracking_number'),
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

    /**
     * POST /api/kiriminaja/pickup
     *
     * Admin-only. Forwards the JSON body straight to KiriminAja request_pickup.
     */
    public function requestPickup(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        try {
            $data = $this->kiriminAja->requestPickup($request->all());
            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/kiriminaja/cancel
     */
    public function cancel(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);
        $request->validate([
            'order_id' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        try {
            $data = $this->kiriminAja->cancelPickup(
                $request->string('order_id'),
                $request->string('reason'),
            );
            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/kiriminaja/balance
     */
    public function balance(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        try {
            return response()->json(['data' => $this->kiriminAja->balance()]);
        } catch (\Throwable $e) {
            return response()->json([
                'data' => [],
                'error' => true,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * GET /api/kiriminaja/schedules
     */
    public function schedules(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        try {
            return response()->json(['data' => $this->kiriminAja->schedules()]);
        } catch (\Throwable $e) {
            return response()->json([
                'data' => [],
                'error' => true,
                'message' => $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Reject the request when the caller is not authenticated as admin.
     */
    protected function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Spatie permission: any role beginning with 'admin' is allowed.
        if (method_exists($user, 'hasRole') && !$user->hasAnyRole(['admin', 'superadmin'])) {
            abort(403, 'Forbidden');
        }
    }
}
