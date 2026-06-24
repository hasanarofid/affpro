@extends('theme::layouts.app')
@section('title', 'Pesanan Berhasil')

@section('content')
    <div class="container py-5 text-center">
        <div class="mx-auto" style="max-width:500px">
            <div class="mb-4" style="font-size:5rem;color:var(--primary)"><i class="bi bi-check-circle"></i></div>
            <h3 class="fw-bold mb-2">Pesanan Berhasil!</h3>
            <p class="text-muted mb-1">Nomor pesanan Anda:</p>
            <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                <h4 class="fw-bold mb-0" style="color:var(--primary)" id="orderNumber">{{ $order->order_number }}</h4>
                <button type="button" class="btn btn-sm btn-light p-1 px-2 border shadow-sm" style="border-radius:8px" onclick="copyOrderNumber()">
                    <i class="bi bi-copy"></i>
                </button>
            </div>

            @guest
            <div class="alert alert-warning border-0 small mb-4 py-3" style="border-radius:14px; background-color: #fff8eb;">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                    </div>
                    <div class="text-start">
                        <div class="fw-bold text-dark mb-1">Simpan Nomor Pesanan Anda!</div>
                        <div class="text-muted" style="line-height: 1.4;">Karena Anda berbelanja sebagai tamu, silakan simpan atau catat nomor pesanan di atas untuk memantau status pesanan Anda di menu <strong>Lacak Pesanan</strong>.</div>
                    </div>
                </div>
            </div>
            @endguest

            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
                <div class="card-body p-4 text-center">
                    <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Total Tagihan</span>
                    <h2 class="fw-bold mt-2 mb-0" style="color: var(--primary); font-size: 2.2rem;">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </h2>
                </div>
            </div>

            @if($order->payment_method === 'manual_transfer')
                <div class="card text-start" style="border:none;border-radius:14px">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-bank me-2"></i>Silakan Transfer Ke</h6>
                        @php 
                            $val = app(\App\Services\SettingService::class)->get('bank_accounts', '[]');
                            $banks = is_array($val) ? $val : json_decode($val, true); 
                        @endphp
                        @foreach($banks ?? [] as $bank)
                            <div class="bg-light rounded-3 p-3 mb-2">
                                <div class="fw-bold">{{ $bank['bank'] ?? '' }}</div>
                                <div class="fs-5 fw-bold" style="color:var(--primary)">{{ $bank['account_number'] ?? '' }}</div>
                                <div class="text-muted small">a.n. {{ $bank['account_name'] ?? '' }}</div>
                            </div>
                        @endforeach
                        <p class="text-muted small mt-2"><i class="bi bi-clock me-1"></i>Transfer sebelum
                            {{ $order->expires_at->format('d M Y H:i') }} WIB</p>
                    </div>
                </div>
            @elseif($order->payment_method === 'gateway')
                @php
                    $latestPayment = $order->payments()->latest()->first();
                    $invoiceUrl = $latestPayment && isset($latestPayment->metadata['invoice_url']) ? $latestPayment->metadata['invoice_url'] : null;
                @endphp

                @if($order->payment_status === 'paid')
                    <div class="card border-0 text-start mb-3" style="border-radius:14px; background-color: #f0fdf4;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-check-circle-fill text-success fs-4 me-2"></i>
                                <h6 class="fw-semibold mb-0 text-success">Pembayaran Berhasil</h6>
                            </div>
                            <p class="text-muted small mb-0">Pembayaran Anda telah diterima otomatis. Pesanan sedang diproses.</p>
                        </div>
                    </div>
                @else
                    <div class="card border-0 text-start mb-3" style="border-radius:14px; background-color: #fffbeb;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-hourglass-split text-warning fs-4 me-2"></i>
                                <h6 class="fw-semibold mb-0 text-dark">Menunggu Pembayaran</h6>
                            </div>
                            <p class="text-muted small mb-2">Silakan selesaikan pembayaran Anda melalui halaman pembayaran otomatis.</p>
                            @if($invoiceUrl)
                                <a href="{{ $invoiceUrl }}" target="_blank" class="btn btn-primary btn-sm" style="border-radius:10px">
                                    <i class="bi bi-credit-card me-1"></i> Bayar Sekarang
                                </a>
                            @endif
                            <p class="text-muted small mt-2 mb-0"><i class="bi bi-clock me-1"></i>Selesaikan pembayaran sebelum
                                {{ $order->expires_at->format('d M Y H:i') }} WIB</p>
                        </div>
                    </div>
                @endif
            @endif

            <div class="mt-4 d-flex gap-2 justify-content-center">
                @auth
                    <a href="{{ route('account.orders') }}" class="btn btn-primary" style="border-radius:10px"><i
                            class="bi bi-receipt me-2"></i>Lihat Pesanan</a>
                @endauth
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary" style="border-radius:10px">Lanjut
                    Belanja</a>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        function copyOrderNumber() {
            const text = document.getElementById('orderNumber').innerText;
            navigator.clipboard.writeText(text).then(() => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Nomor pesanan disalin'
                });
            });
        }
    </script>
    @endpush
@endsection