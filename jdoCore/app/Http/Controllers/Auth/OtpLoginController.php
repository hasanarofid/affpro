<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use App\Contracts\WhatsAppInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpLoginController extends Controller
{
    public function showForm()
    {
        if (Auth::check()) {
            return Auth::user()->hasRole('admin')
                ? redirect()->route('admin.dashboard')
                : redirect()->route('home');
        }
        return view('theme::auth.otp-login');
    }

    /**
     * Step 1: Send OTP to phone number.
     */
    public function sendOtp(Request $request, OtpService $otpService)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:15',
        ]);

        $phone = $this->normalizePhone($request->phone);
        $otp = $otpService->generate($phone, 'login');

        // Send via WhatsApp directly (Synchronous: instant delivery for OTP)
        try {
            $wa = app(\App\Contracts\WhatsAppInterface::class);
            if ($wa->isConfigured()) {
                $storeName = app(\App\Services\SettingService::class)->storeName();
                $message = "Kode OTP Anda di {$storeName}: {$otp->code}\nBerlaku {$otp->expires_at->diffForHumans()}.\nJangan bagikan kode ini kepada siapapun.";
                
                $wa->send($phone, $message);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WhatsApp OTP Send failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke WhatsApp Anda.',
            'expires_at' => $otp->expires_at->toISOString(),
            // Show code in local/dev for testing
            'debug_code' => app()->environment('local') ? $otp->code : null,
        ]);
    }

    /**
     * Step 2: Verify OTP and login.
     */
    public function verifyOtp(Request $request, OtpService $otpService)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $phone = $this->normalizePhone($request->phone);
        $result = $otpService->verify($phone, $request->code, 'login');

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 422);
        }

        $user = $otpService->findOrCreateUser($phone);
        Auth::login($user, true);

        // Merge guest cart
        app(\App\Services\CartService::class)->mergeGuestCart();

        $redirect = $user->hasRole('admin') ? route('admin.dashboard') : route('home');

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'redirect' => $redirect,
        ]);
    }

    protected function normalizePhone(string $phone): string
    {
        return app(\App\Services\SettingService::class)->formatPhone($phone);
    }
}
