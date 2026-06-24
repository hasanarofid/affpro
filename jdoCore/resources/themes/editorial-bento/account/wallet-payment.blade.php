@extends('theme::account.layout')
@section('title', 'Pembayaran Top Up')

@section('account_content')
    <span class="eb-kicker">Wallet Payment</span>
    <h3 class="fw-bold mt-2 mb-3" style="font-family:var(--heading); letter-spacing:-.03em">Selesaikan Pembayaran</h3>
    <p class="text-muted">Lengkapi pembayaran top up agar saldo bisa langsung digunakan.</p>

    <div class="p-4 rounded-4 border mb-3" style="border-color:#efe8dd!important;background:#fbfaf6">
        <div class="d-flex justify-content-between">
            <div>
                <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">Nominal</div>
                <div class="fw-bold fs-4" style="color:var(--primary)">Rp {{ number_format($transaction->amount ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="text-end">
                <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">Status</div>
                <span class="badge text-bg-warning">{{ ucfirst($transaction->status ?? '-') }}</span>
            </div>
        </div>
    </div>

    @if(isset($bankAccounts) && count($bankAccounts))
        <h6 class="fw-bold mt-4 mb-2">Transfer ke salah satu rekening</h6>
        @foreach($bankAccounts as $b)
            <div class="p-3 rounded-4 border mb-2" style="border-color:#efe8dd!important;background:#fff">
                <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">{{ $b['bank'] ?? '' }}</div>
                <div class="fw-bold">{{ $b['account_number'] ?? '' }}</div>
                <div class="small text-muted">a.n. {{ $b['account_name'] ?? '' }}</div>
            </div>
        @endforeach
    @endif

    <form action="{{ route('account.wallet.uploadPayment', $transaction) }}" method="POST" enctype="multipart/form-data" class="mt-3">
        @csrf
        <label class="form-label small fw-semibold">Upload Bukti Transfer</label>
        <input type="file" name="proof" class="form-control rounded-3" accept="image/*" required>
        <button class="btn btn-dark rounded-pill mt-3 px-4"><i class="bi bi-upload me-1"></i>Konfirmasi Pembayaran</button>
    </form>
@endsection
