@extends('theme::layouts.app')
@section('title', 'Pembayaran Pesanan')

@section('styles')
<style>
    .eb-payment-shell { max-width: 720px; margin: 0 auto; }
    .eb-payment-card { background: rgba(255,255,255,.95); border:1px solid rgba(255,255,255,.7); border-radius: 28px; box-shadow: 0 24px 60px rgba(15,23,42,.07); padding: 28px; }
    .eb-bank-row { background:#fbfaf6; border:1px solid #efe8dd; border-radius: 18px; padding: 14px 18px; }
    .eb-upload-zone { border:2px dashed #d6d3d1; border-radius: 22px; padding: 36px; text-align:center; cursor:pointer; transition:.2s ease; background:#fbfaf6; }
    .eb-upload-zone:hover { border-color: var(--primary); background:#fff; }
</style>
@endsection

@section('content')
<div class="container-xxl px-3 eb-payment-shell py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('orders.track', $order->order_number) }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i> Kembali ke Pesanan</a>
    </div>
    <div class="text-center mb-4">
        <span class="eb-kicker mb-2">Payment</span>
        <h1 class="eb-section-title">Selesaikan Pembayaran</h1>
        <p class="text-muted">Total tagihan untuk order <strong>{{ $order->order_number }}</strong></p>
        <div class="fw-bold" style="font-size:2.2rem; color:var(--primary)">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
    </div>

    @if(count($bankAccounts))
        <div class="eb-payment-card mb-3">
            <div class="small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.08em"><i class="bi bi-bank me-1"></i> Tujuan Transfer</div>
            <div class="row g-3">
                @foreach($bankAccounts as $bank)
                    <div class="col-md-6">
                        <div class="eb-bank-row d-flex justify-content-between align-items-start">
                            <div>
                                <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">{{ $bank['bank'] ?? 'Bank' }}</div>
                                <div class="fw-bold fs-5" id="acc-{{ $loop->index }}">{{ $bank['account_number'] ?? '' }}</div>
                                <div class="text-muted small">a.n. {{ $bank['account_name'] ?? '' }}</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-light rounded-pill px-3" onclick="navigator.clipboard.writeText(document.getElementById('acc-{{ $loop->index }}').innerText)"><i class="bi bi-files"></i></button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="alert alert-warning rounded-4 small mt-3 mb-0"><i class="bi bi-exclamation-triangle me-1"></i> Transfer pas hingga 3 digit terakhir agar verifikasi otomatis.</div>
        </div>
    @endif

    @php $lastPayment = $order->payments->last(); @endphp
    @if($lastPayment && $lastPayment->proof_image)
        <div class="eb-payment-card mb-3 text-center" style="border:2px dashed #16a34a; background:#f0fdf4;">
            <div class="small fw-bold text-success mb-2"><i class="bi bi-check-circle me-1"></i>Bukti transfer terupload</div>
            <img src="{{ asset($lastPayment->proof_image) }}" class="rounded-4" style="max-height:240px;">
            <div class="small text-muted mt-2">Silakan upload ulang jika perlu mengganti bukti.</div>
        </div>
    @endif

    <form action="{{ route('orders.uploadPayment', $order->order_number) }}" method="POST" enctype="multipart/form-data" class="eb-payment-card">
        @csrf
        <div class="small text-uppercase fw-bold text-muted mb-3" style="letter-spacing:.08em"><i class="bi bi-cloud-arrow-up me-1"></i> Upload Bukti Transfer</div>
        <label for="proof-input" class="eb-upload-zone d-block">
            <i class="bi bi-image fs-1" style="color:var(--primary)"></i>
            <div class="fw-bold mt-2">Pilih foto bukti transfer</div>
            <div class="small text-muted">JPG / PNG max 2MB</div>
            <input type="file" id="proof-input" name="proof" class="d-none" accept="image/*" required onchange="document.getElementById('proofName').textContent = this.files[0]?.name || ''">
            <div id="proofName" class="small text-success mt-2 fw-semibold"></div>
        </label>
        @error('proof')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        <button type="submit" class="btn btn-dark w-100 rounded-pill py-3 mt-3 fw-bold"><i class="bi bi-check2-circle me-1"></i>Konfirmasi Pembayaran</button>
    </form>
</div>
@endsection
