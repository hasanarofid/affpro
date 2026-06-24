<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()->latest()->get();
        return view('theme::account.address', compact('addresses'));
    }

    public function store(Request $request)
    {
        $settings = app(\App\Services\SettingService::class);
        if ($request->filled('phone')) {
            $request->merge(['phone' => $settings->formatPhone($request->phone)]);
        }

        $request->validate([
            'title' => 'nullable|string|max:100',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:500',
            'province_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $data = $request->all();
        $user = auth()->user();

        if ($user->addresses()->count() === 0) {
            $data['is_main'] = true;
        }

        $address = $user->addresses()->create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil ditambahkan.',
                'address' => $address
            ]);
        }

        return back()->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(Request $request, UserAddress $address)
    {
        $settings = app(\App\Services\SettingService::class);
        if ($request->filled('phone')) {
            $request->merge(['phone' => $settings->formatPhone($request->phone)]);
        }

        if ($address->user_id != auth()->id())
            abort(403);

        $request->validate([
            'title' => 'nullable|string|max:100',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line' => 'required|string|max:500',
            'province_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $address->update($request->all());

        return back()->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(UserAddress $address)
    {
        if ($address->user_id != auth()->id())
            abort(403);

        $address->delete();

        return back()->with('success', 'Alamat berhasil dihapus.');
    }

    public function setDefault(UserAddress $address)
    {
        if ($address->user_id != auth()->id())
            abort(403);

        auth()->user()->addresses()->update(['is_main' => false]);
        $address->update(['is_main' => true]);

        return back()->with('success', 'Alamat utama berhasil diubah.');
    }
}
