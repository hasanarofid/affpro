@extends('theme::layouts.app')
@section('title', 'Lacak Pesanan')

@section('styles')
<style>
    .eb-track-shell { max-width: 640px; margin: 0 auto; }
    .eb-track-card { background: rgba(255,255,255,.95); border:1px solid rgba(255,255,255,.7); border-radius: 28px; box-shadow: 0 24px 60px rgba(15,23,42,.07); padding: 28px; }
    .eb-track-input { border-radius:14px; padding:14px 16px; }
</style>
@endsection

@section('content')
<div class="container-xxl px-3 eb-track-shell py-3">
    <div class="text-center mb-4">
        <span class="eb-kicker mb-2">Order Tracking</span>
        <h1 class="eb-section-title">Lacak status pesanan Anda.</h1>
        <p class="text-muted">Masukkan nomor pesanan untuk melihat detail dan progres pengiriman.</p>
    </div>
    <div class="eb-track-card">
        <form action="{{ route('orders.track.process') }}" method="POST">
            @csrf
            @if(session('error'))
                <div class="alert alert-danger rounded-4 py-2 small">{{ session('error') }}</div>
            @endif
            <label class="form-label small fw-bold text-uppercase text-muted" style="letter-spacing:.08em">Nomor Pesanan</label>
            <input type="text" name="order_number" class="form-control eb-track-input" placeholder="Contoh: JO-20260520-XXXX" value="{{ old('order_number') }}" required>
            @error('order_number') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
            <button type="submit" class="btn btn-dark w-100 rounded-pill mt-3 py-3">
                <i class="bi bi-search me-1"></i> Lacak Pesanan
            </button>
        </form>
    </div>
</div>
@endsection
