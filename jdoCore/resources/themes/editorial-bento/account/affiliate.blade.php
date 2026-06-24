@extends('theme::account.layout')
@section('title', 'Program Affiliate')

@section('account_content')
    <span class="eb-kicker">Affiliate</span>
    <h3 class="fw-bold mt-2" style="font-family:var(--heading); letter-spacing:-.03em">Program Affiliate</h3>
    <p class="text-muted">Bagikan tautan toko Anda dan dapatkan komisi dari setiap transaksi yang berhasil.</p>

    <div class="p-4 rounded-4 border" style="border-color:#efe8dd!important;background:#fbfaf6">
        <div class="small text-uppercase fw-bold text-muted mb-2" style="letter-spacing:.08em">Kode Affiliate Anda</div>
        <div class="fw-bold fs-3">{{ $referralCode ?? auth()->user()->referral_code ?? '-' }}</div>
        <div class="small text-muted mt-2">Bagikan kode atau tautan referral Anda untuk mengundang pembeli baru.</div>
    </div>
@endsection
