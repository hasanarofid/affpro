<?php

namespace Modules\IpaymuPayment\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Modules\IpaymuPayment\app\Services\IpaymuService;

class IpaymuSettingsController extends Controller
{
    public function settings(SettingService $settings)
    {
        $ipaymuService = app(IpaymuService::class);

        return response()->json([
            'ipaymu_va' => $settings->get('ipaymu_va', ''),
            'ipaymu_api_key' => $settings->get('ipaymu_api_key', ''),
            'ipaymu_mode' => $settings->get('ipaymu_mode', 'sandbox'),
        ]);
    }

    public function channels()
    {
        $ipaymuService = app(IpaymuService::class);
        $enabledCodes = $ipaymuService->getChannels();
        $allDefs = $ipaymuService->getAllChannelDefinitions();
        $channels = array_filter($allDefs, fn($ch) => in_array($ch['code'], $enabledCodes));

        $grouped = [];
        foreach ($channels as $ch) {
            $grouped[$ch['type']][] = $ch;
        }

        return response()->json([
            'provider' => 'ipaymu',
            'channels' => array_values($channels),
            'grouped' => $grouped,
        ]);
    }
}