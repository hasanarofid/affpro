<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;

class OtpService
{
    protected int $codeLength = 6;
    protected int $expiryMinutes = 5;
    protected int $maxAttempts = 5;

    /**
     * Generate and store OTP for a phone number.
     */
    public function generate(string $phone, string $type = 'login'): OtpCode
    {
        // Invalidate previous OTPs for same phone + type
        OtpCode::where('phone', $phone)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        return OtpCode::create([
            'phone' => $phone,
            'code' => $this->generateCode(),
            'type' => $type,
            'expires_at' => now()->addMinutes($this->expiryMinutes),
            'attempts' => 0,
        ]);
    }

    /**
     * Verify an OTP code.
     */
    public function verify(string $phone, string $code, string $type = 'login'): array
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        \Log::info("OTP Verification Attempt", [
            'phone' => $phone,
            'input_code' => $code,
            'found_otp' => $otp ? $otp->toArray() : 'NOT_FOUND',
            'now' => now()->toDateTimeString()
        ]);

        if (!$otp) {
            return ['success' => false, 'message' => 'Tidak ada permintaan OTP aktif untuk nomor ini.'];
        }

        if ($otp->expires_at->isPast()) {
            return ['success' => false, 'message' => 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang.'];
        }

        if ($otp->attempts >= $this->maxAttempts) {
            return ['success' => false, 'message' => 'Terlalu banyak percobaan. Silakan kirim ulang OTP baru.'];
        }

        if ($otp->code !== $code) {
            $otp->increment('attempts');
            return ['success' => false, 'message' => 'Kode OTP yang Anda masukkan salah.'];
        }

        $otp->update(['verified_at' => now()]);
        return ['success' => true];
    }

    /**
     * Find or create user by phone.
     */
    public function findOrCreateUser(string $phone, string $name = null): User
    {
        $phone = app(\App\Services\SettingService::class)->formatPhone($phone);
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $user = User::create([
                'name' => $name ?? 'User ' . substr($phone, -4),
                'phone' => $phone,
                'email' => $phone . '@jadiorder.com', // Safe unique placeholder
                'password' => bcrypt(str()->random(16)),
                'is_active' => true,
            ]);
            $user->assignRole('customer');
        }

        return $user;
    }

    protected function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), $this->codeLength, '0', STR_PAD_LEFT);
    }
}
