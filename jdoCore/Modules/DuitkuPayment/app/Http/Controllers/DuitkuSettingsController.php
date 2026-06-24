<?php

namespace Modules\DuitkuPayment\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Modules\DuitkuPayment\app\Services\DuitkuService;

class DuitkuSettingsController extends Controller
{
    /**
     * AJAX: Get Duitku settings data for admin panel.
     */
    public function settings(SettingService $settings)
    {
        $duitkuService = app(DuitkuService::class);

        $data = [
            'duitku_merchant_code' => $settings->get('duitku_merchant_code', ''),
            'duitku_merchant_key' => $settings->get('duitku_merchant_key', ''),
            'duitku_mode' => $settings->get('duitku_mode', 'sandbox'),
        ];

        return response()->json($data);
    }

    /**
     * AJAX: Get available channels for the checkout page.
     */
    public function channels()
    {
        $duitkuService = app(DuitkuService::class);

        $enabledCodes = $duitkuService->getChannels();
        $allDefs = $duitkuService->getAllChannelDefinitions();

        $channels = array_filter($allDefs, fn($ch) => in_array($ch['code'], $enabledCodes));

        $grouped = [];
        foreach ($channels as $ch) {
            $grouped[$ch['type']][] = $ch;
        }

        return response()->json([
            'provider' => 'duitku',
            'channels' => array_values($channels),
            'grouped' => $grouped,
        ]);
    }
}
