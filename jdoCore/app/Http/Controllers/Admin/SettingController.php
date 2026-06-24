<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(SettingService $settingService)
    {
        $settings = [
            'general' => $settingService->getGroup('general'),
            'store' => $settingService->getGroup('store'),
            'payment' => $settingService->getGroup('payment'),
            'shipping' => $settingService->getGroup('shipping'),
            'whatsapp' => $settingService->getGroup('whatsapp'),
            'ai' => $settingService->getGroup('ai'),
            'email' => $settingService->getGroup('email'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request, SettingService $settingService)
    {
        // Convert courier checkboxes to JSON
        if ($request->has('enabled_couriers_list')) {
            $request->merge([
                'enabled_couriers' => json_encode($request->input('enabled_couriers_list', [])),
            ]);
        }

        // Convert KiriminAja courier checkboxes to JSON
        if ($request->has('kiriminaja_couriers_list')) {
            $request->merge([
                'kiriminaja_couriers' => json_encode($request->input('kiriminaja_couriers_list', [])),
            ]);
        }

        // Convert Xendit channel checkboxes to JSON
        if ($request->has('xendit_channels_list')) {
            $request->merge([
                'xendit_enabled_channels' => json_encode($request->input('xendit_channels_list', [])),
            ]);
        }

        // Convert Midtrans channel checkboxes to JSON
        if ($request->has('midtrans_channels_list')) {
            $request->merge([
                'midtrans_enabled_channels' => json_encode($request->input('midtrans_channels_list', [])),
            ]);
        }

        // Convert Duitku channel checkboxes to JSON
        if ($request->has('duitku_channels_list')) {
            $request->merge([
                'duitku_enabled_channels' => json_encode($request->input('duitku_channels_list', [])),
            ]);
        }

        // Convert iPaymu channel checkboxes to JSON
        if ($request->has('ipaymu_channels_list')) {
            $request->merge([
                'ipaymu_enabled_channels' => json_encode($request->input('ipaymu_channels_list', [])),
            ]);
        }

        $skip = ['_token', '_method', '_group', '_type', 'enabled_couriers_list', 'kiriminaja_couriers_list', 'xendit_channels_list', 'midtrans_channels_list', 'duitku_channels_list', 'ipaymu_channels_list', 'store_logo_file', 'store_favicon_file'];
        $fields = $request->except($skip);

        // Handle File Uploads
        if ($request->hasFile('store_logo_file')) {
            $file = $request->file('store_logo_file');
            $filename = time() . '_logo_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/settings/logo'), $filename);
            $settingService->set('store_logo', 'uploads/settings/logo/' . $filename, 'general', 'string');
        }

        if ($request->hasFile('store_favicon_file')) {
            $file = $request->file('store_favicon_file');
            $filename = time() . '_favicon_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/settings/logo'), $filename);
            $settingService->set('store_favicon', 'uploads/settings/logo/' . $filename, 'general', 'string');
        }

        foreach ($fields as $key => $value) {
            if (str_starts_with($key, '_'))
                continue;
            $group = $request->input("_group.{$key}", 'general');
            $type = $request->input("_type.{$key}", 'string');
            $settingService->set($key, $value, $group, $type);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }

    public function testWhatsApp(Request $request, \App\Contracts\WhatsAppInterface $wa)
    {
        if (config('app.demo_mode')) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur ini dinonaktifkan dalam Mode Demo.'
            ]);
        }

        $request->validate([
            'phone' => 'required',
            'message' => 'required',
        ]);

        $result = $wa->test($request->phone, $request->message);

        return response()->json($result);
    }
}
