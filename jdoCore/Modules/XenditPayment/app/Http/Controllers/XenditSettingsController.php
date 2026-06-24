<?php

namespace Modules\XenditPayment\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Modules\XenditPayment\app\Services\XenditService;

class XenditSettingsController extends Controller
{
    /**
     * AJAX: Get Xendit settings partial for admin panel.
     */
    public function settings(SettingService $settings)
    {
        $xenditService = app(XenditService::class);

        $data = [
            'xendit_secret_key' => $settings->get('xendit_secret_key', ''),
            'xendit_callback_token' => $settings->get('xendit_callback_token', ''),
            'xendit_mode' => $settings->get('xendit_mode', 'sandbox'),
        ];

        return response()->json($data);
    }

    /**
     * AJAX: Get available channels for the checkout page.
     */
    public function channels()
    {
        $xenditService = app(XenditService::class);

        $enabledCodes = $xenditService->getChannels();
        $allDefs = $xenditService->getAllChannelDefinitions();

        // Only return channels that are in the enabled list
        $channels = array_filter($allDefs, fn($ch) => in_array($ch['code'], $enabledCodes));

        // Group by type
        $grouped = [];
        foreach ($channels as $ch) {
            $grouped[$ch['type']][] = $ch;
        }

        return response()->json([
            'provider' => 'xendit',
            'channels' => array_values($channels),
            'grouped' => $grouped,
        ]);
    }
}
