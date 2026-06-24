<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('theme::auth.register');
    }

    public function register(Request $request)
    {
        $settings = app(\App\Services\SettingService::class);

        // Pre-clean: keep only digits and leading + so autofill garbage doesn't slip through.
        $rawIncomingPhone = $request->input('phone');
        
        if ($request->filled('phone')) {
            $rawPhone = preg_replace('/[^0-9+]/', '', (string) $rawIncomingPhone);
            $request->merge(['phone' => $settings->formatPhone($rawPhone)]);
        }

        \Illuminate\Support\Facades\Log::info('Register Attempt', [
            'email' => $request->input('email'),
            'original_phone_from_user' => $rawIncomingPhone,
            'merged_phone_for_validation' => $request->input('phone')
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => ['required', 'string', 'regex:/^[0-9]{9,15}$/', 'unique:users,phone'],
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
            'phone.unique' => 'Nomor WhatsApp ini sudah terdaftar. Silakan gunakan nomor lain atau masuk ke akun lama Anda.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'phone.regex' => 'Nomor WhatsApp tidak valid. Gunakan 9–15 digit angka, contoh: 081234567890.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $referredBy = null;
        if ($request->cookie('referral')) {
            $referrer = User::where('referral_code', $request->cookie('referral'))->first();
            if ($referrer) {
                $referredBy = $referrer->id;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'referred_by' => $referredBy,
            'is_active' => true,
        ]);

        $user->assignRole('customer');

        Auth::login($user);

        // Merge guest cart if exists
        if (session()->has('cart_id')) {
            app(\App\Services\CartService::class)->mergeGuestCart();
        }

        return redirect()->route('home')->with('success', 'Registrasi berhasil! Selamat berbelanja.');
    }
}
