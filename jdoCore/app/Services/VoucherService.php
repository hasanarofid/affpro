<?php

namespace App\Services;

use App\Models\Voucher;

class VoucherService
{
    /**
     * Validate and apply a voucher code.
     *
     * @param  string  $code
     * @param  float   $subtotal      Cart subtotal (products only)
     * @param  float   $shippingCost  Selected shipping cost (0 if not yet picked)
     */
    public function apply(string $code, float $subtotal, float $shippingCost = 0): array
    {
        $voucher = Voucher::where('code', strtoupper($code))->first();

        if (!$voucher) {
            return ['valid' => false, 'message' => 'Kode voucher tidak ditemukan.'];
        }

        if (!$voucher->is_active) {
            return ['valid' => false, 'message' => 'Voucher tidak aktif.'];
        }

        if ($voucher->max_usage !== null && $voucher->used_count >= $voucher->max_usage) {
            return ['valid' => false, 'message' => 'Kuota voucher sudah habis.'];
        }

        if ($voucher->starts_at && now()->lt($voucher->starts_at)) {
            return ['valid' => false, 'message' => 'Voucher belum berlaku.'];
        }

        if ($voucher->expires_at && now()->gt($voucher->expires_at)) {
            return ['valid' => false, 'message' => 'Voucher sudah kedaluwarsa.'];
        }

        if ($voucher->min_purchase && $subtotal < $voucher->min_purchase) {
            return [
                'valid' => false,
                'message' => 'Min. belanja Rp ' . number_format($voucher->min_purchase, 0, ',', '.') . ' untuk voucher ini.',
            ];
        }

        $scope = $voucher->scope ?: 'all';

        // Determine the base amount the discount runs against.
        $base = match ($scope) {
            'shipping' => $shippingCost,
            default    => $subtotal, // 'products' or 'all'
        };

        // Shipping voucher requires the user to pick a courier first.
        if ($scope === 'shipping' && $shippingCost <= 0) {
            return [
                'valid' => false,
                'message' => 'Pilih kurir/ongkos kirim terlebih dahulu untuk memakai voucher ongkir.',
            ];
        }

        $discount = $voucher->calculateDiscount($base);

        return [
            'valid'         => true,
            'voucher'       => $voucher,
            'discount'      => round($discount, 2),
            'discount_type' => $scope === 'shipping' ? 'shipping' : 'product',
            'scope'         => $scope,
            'message'       => 'Voucher berhasil diterapkan! ' . ($scope === 'shipping' ? 'Potongan Ongkir' : 'Diskon') . ' Rp ' . number_format($discount, 0, ',', '.'),
        ];
    }

    /**
     * Mark voucher as used (increment counter).
     */
    public function markUsed(Voucher $voucher): void
    {
        $voucher->increment('used_count');
    }
}
