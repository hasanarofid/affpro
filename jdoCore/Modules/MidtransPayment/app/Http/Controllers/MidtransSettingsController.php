<?php

namespace Modules\MidtransPayment\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Modules\MidtransPayment\app\Services\MidtransService;

class MidtransSettingsController extends Controller
{
    /**
     * AJAX: Get Midtrans settings data for admin panel.
     */
    public function settings(SettingService $settings)
    {
        $midtransService = app(MidtransService::class);

        $data = [
            'midtrans_server_key' => $settings->get('midtrans_server_key', ''),
            'midtrans_client_key' => $settings->get('midtrans_client_key', ''),
            'midtrans_mode' => $settings->get('midtrans_mode', 'sandbox'),
        ];

        return response()->json($data);
    }

    /**
     * AJAX: Get available channels for the checkout page.
     */
    public function channels()
    {
        $midtransService = app(MidtransService::class);

        $enabledCodes = $midtransService->getChannels();
        $allDefs = $midtransService->getAllChannelDefinitions();

        // Only return channels that are in the enabled list
        $channels = array_filter($allDefs, fn($ch) => in_array($ch['code'], $enabledCodes));

        // Group by type
        $grouped = [];
        foreach ($channels as $ch) {
            $grouped[$ch['type']][] = $ch;
        }

        return response()->json([
            'provider' => 'midtrans',
            'channels' => array_values($channels),
            'grouped' => $grouped,
        ]);
    }
}
