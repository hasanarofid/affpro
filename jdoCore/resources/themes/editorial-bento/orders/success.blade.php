@extends('theme::layouts.app')
@section('title', 'Pesanan Berhasil')

@section('styles')
<style>
    .eb-success-shell { max-width: 640px; margin: 0 auto; }
    .eb-success-card { background: rgba(255,255,255,.95); border:1px solid rgba(255,255,255,.7); border-radius: 32px; box-shadow: 0 28px 70px rgba(15,23,42,.08); padding: 36px; }
    .eb-success-icon { width:84px; height:84px; border-radius:999px; background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 80%, white 20%), color-mix(in srgb, var(--secondary) 80%, white 20%)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:2.4rem; margin: 0 auto 18px; box-shadow: 0 18px 40px rgba(79,70,229,.25); }
    .eb-success-title { font-family: var(--heading); font-size: clamp(1.6rem, 3vw, 2.4rem); letter-spacing:-.03em; }
    .eb-success-total { font-family: var(--heading); font-size: clamp(2rem, 4vw, 3rem); color: var(--primary); }
    .eb-bank-row { background:#fbfaf6; border:1px solid #efe8dd; border-radius: 18px; padding: 14px 18px; }
</style>
@endsection

@section('content')
<div class="container-xxl px-3 eb-success-shell text-center py-4">
    <div class="eb-success-card">
        <div class="eb-success-icon"><i class="bi bi-check-lg"></i></div>
        <h2 class="eb-success-title fw-bold mb-2">Pesanan Berhasil!</h2>
        <p class="text-muted mb-4">Terima kasih, pesanan Anda sudah kami terima.</p>
        <div class="d-inline-flex align-items-center gap-2 mb-4">
            <span class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">No. Pesanan</span>
            <span class="fw-bold" id="orderNumber">{{ $order->order_number }}</span>
            <button type="button" class="btn btn-sm btn-light rounded-circle" onclick="navigator.clipboard.writeText('{{ $order->order_number }}')" title="Salin"><i class="bi bi-files"></i></button>
        </div>
        <div class="mb-4">
            <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">Total Tagihan</div>
            <div class="eb-success-total fw-bold mt-1">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
        </div>

        @if($order->payment_method === 'manual_transfer')
            <div class="text-start mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-bank me-2"></i>Silakan Transfer Ke</h6>
                @php
                    $val = app(\App\Services\SettingService::class)->get('bank_accounts', '[]');
                    $banks = is_array($val) ? $val : json_decode($val, true);
                @endphp
                @foreach($banks ?? [] as $bank)
                    <div class="eb-bank-row mb-2">
                        <div class="fw-bold text-uppercase small text-muted" style="letter-spacing:.08em">{{ $bank['bank'] ?? 'Bank' }}</div>
                        <div class="fw-bold fs-5" style="color:var(--primary)">{{ $bank['account_number'] ?? '' }}</div>
                        <div class="text-muted small">a.n. {{ $bank['account_name'] ?? '' }}</div>
                    </div>
                @endforeach
                <p class="text-muted small mt-2"><i class="bi bi-clock me-1"></i>Transfer sebelum {{ $order->expires_at->format('d M Y H:i') }} WIB</p>
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 justify-content-center">
            @auth
                <a href="{{ route('account.orders') }}" class="btn btn-dark rounded-pill px-4"><i class="bi bi-receipt me-1"></i> Pesanan Saya</a>
            @endauth
            <a href="{{ route('orders.track', $order->order_number) }}" class="btn btn-outline-dark rounded-pill px-4">Lacak Pesanan</a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-dark rounded-pill px-4">Lanjut Belanja</a>
        </div>
    </div>
</div>
@endsection
