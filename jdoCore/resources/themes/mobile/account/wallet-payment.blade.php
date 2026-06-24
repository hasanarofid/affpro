@extends('theme::layouts.app')
@section('title', 'Pilih Metode Pembayaran Top Up')

@section('content')
 <div class="container py-5" style="max-width:800px">
 <h4 class="fw-bold mb-4"><i class="bi bi-wallet2 me-2"></i>Pembayaran Top Up Saldo</h4>

 <div class="row g-4">
 <div>
 <div class="card mb-4" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.04)">
 <div class="card-body p-4">
 <h6 class="fw-bold mb-3"><i class="bi bi-bank me-2 text-primary"></i>Metode Pembayaran Transfer
 </h6>
 <p class="text-muted small mb-4 lh-base">Silakan lakukan transfer sesuai nominal tagihan ke salah
 satu rekening
 di bawah ini:</p>

 @if(count($bankAccounts) > 0)
 <div class="border mb-4" style="border-radius:12px; background-color: #f8fafc;">
 @foreach($bankAccounts as $bank)
 <div class="p-3">
 <div class="d-flex justify-content-between align-items-center mb-1">
 <div class="text-primary fw-bold" style="font-size: 0.9rem;">
 {{ $bank['bank'] ?? '-' }}
 </div>
 <div class="text-muted small text-end">Atas Nama</div>
 </div>
 <div class="d-flex justify-content-between align-items-center">
 <div class="text-dark fw-bold fs-6 tracking-wide" style="letter-spacing: 0.5px;">
 {{ $bank['account_number'] ?? '' }}
 </div>
 <div class="fw-semibold text-dark small text-end">{{ $bank['account_name'] ?? '' }}</div>
 </div>
 </div>
 @if(!$loop->last)
 <div class="border-bottom opacity-50 mx-3"></div>
 @endif
 @endforeach
 </div>
 @else
 <div class="alert alert-warning small border-0" style="border-radius: 10px;">
 <i class="bi bi-exclamation-circle-fill me-2"></i>Belum ada rekening bank yang dikonfigurasi.
 </div>
 @endif

 <hr class="my-4 opacity-10">

 <h6 class="fw-bold mb-3"><i class="bi bi-upload me-2 text-primary"></i>Konfirmasi Pembayaran
 </h6>
 <p class="text-muted small mb-3 lh-base">Setelah melakukan transfer, silakan upload bukti
 struk/screenshot pembayaran Anda di bawah ini agar transaksi dapat segera diproses.</p>

 <form action="{{ route('account.wallet.uploadPayment', $transaction->reference_number) }}"
 method="POST" enctype="multipart/form-data">
 @csrf
 <input type="hidden" name="payment_method" value="manual_transfer">

 <div class="mb-4">
 <label class="form-label small fw-semibold text-dark">Upload Bukti Transfer <span
 class="text-danger">*</span></label>
 <input type="file" name="proof" class="form-control bg-light"
 accept="image/jpeg,image/jpg,image/png" style="font-size:0.9rem;" required>
 <div class="form-text mt-1 text-muted" style="font-size:0.8rem;"><i class="bi bi-info-circle me-1"></i>Format yang didukung: JPG,
 JPEG, PNG (Maks 2MB)</div>
 @error('proof')
 <div class="text-danger small mt-2 fw-medium"><i
 class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}</div>
 @enderror
 </div>

 <button type="submit" class="btn btn-primary w-100 py-2 mt-1 fs-6 shadow-sm"
 style="border-radius:10px; font-weight: 600;">
 <i class="bi bi-check-circle-fill me-2"></i> Konfirmasi Pembayaran
 </button>
 </form>
 </div>
 </div>
 </div>

 <div>
 <div class="card"
 style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.04);position:sticky;top:90px">
 <div class="card-body p-4">
 <h6 class="fw-bold mb-4 border-bottom pb-3">Ringkasan Transaksi</h6>

 <div class="d-flex flex-column gap-3 mb-4">
 <div class="d-flex justify-content-between">
 <span class="text-muted small">No Referensi</span>
 <span class="fw-semibold text-dark small font-monospace">
 {{ $transaction->reference_number }}</span>
 </div>
 <div class="d-flex justify-content-between">
 <span class="text-muted small">Tanggal</span>
 <span class="fw-semibold text-dark small">
 {{ $transaction->created_at->format('d M Y H:i') }}</span>
 </div>
 <div class="d-flex justify-content-between">
 <span class="text-muted small">Keterangan</span>
 <span class="fw-semibold text-dark small text-end lh-sm" style="max-width: 65%;">
 {{ $transaction->description }}</span>
 </div>
 </div>

 <div class="p-3 rounded-3" style="background-color: #f1f5f9;">
 <div class="text-muted small fw-medium mb-1">Total Tagihan</div>
 <h3 class="fw-bold text-primary mb-0 position-relative" style="letter-spacing: -0.5px;">
 <span class="fs-5 position-relative" style="top: -2px;">Rp</span>
 {{ number_format($transaction->amount, 0, ',', '.') }}
 </h3>
 </div>

 <div class="alert alert-info border-0 small mt-4 mb-0 d-flex align-items-start gap-2 lh-base"
 style="border-radius:12px; background-color: #eff6ff; color: #1e40af;">
 <i class="bi bi-info-circle-fill mt-1"></i>
 <div>Sebisa mungkin lakukan transfer tepat sesuai nominal hingga 3 digit terakhir untuk
 mempercepat verifikasi otomatis.</div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
@endsection