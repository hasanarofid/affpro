@extends('theme::layouts.app')

@section('title', 'Lacak Pesanan - ' . app(\App\Services\SettingService::class)->storeName())

@section('content')
    <div class="bg-light py-5">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="mb-4">
                        <a href="{{ route('home') }}"
                            class="text-decoration-none text-muted small hover-primary d-inline-flex align-items-center">
                            <i class="bi bi-arrow-left me-2"></i> Kembali ke Beranda
                        </a>
                    </div>
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm mb-3"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-box-seam text-primary" style="font-size: 2.5rem;"></i>
                        </div>
                        <h2 class="fw-bold text-dark tracking-tight">Cek Status Pesanan</h2>
                        <p class="text-muted">Lacak perjalanan paket atau status pesanan terakhir Anda dengan memasukkan
                            Nomor Pesanan/Faktur.</p>
                    </div>

                    <!-- Form Card -->
                    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                        <div class="card-body p-4 p-md-5">
                            <form action="{{ route('orders.track.process') }}" method="POST">
                                @csrf

                                @if(session('error'))
                                    <div class="alert alert-danger d-flex align-items-center mb-4 border-0"
                                        style="border-radius: 12px; background-color: #fef2f2; color: #991b1b;">
                                        <i class="bi bi-exclamation-octagon-fill fs-5 me-3"></i>
                                        <div>
                                            {{ session('error') }}
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <label for="order_number"
                                        class="form-label fw-bold small text-muted text-uppercase tracking-widest">Nomor
                                        Pesanan</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0 text-muted"
                                            style="border-radius: 12px 0 0 12px;">
                                            <i class="bi bi-hash"></i>
                                        </span>
                                        <input type="text" class="form-control bg-light border-start-0 ps-0"
                                            id="order_number" name="order_number" placeholder="Contoh: INV-20260228-xxxxx"
                                            required value="{{ old('order_number') }}"
                                            style="border-radius: 0 12px 12px 0;">
                                    </div>
                                    @error('order_number')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit"
                                        class="btn btn-primary btn-lg rounded-pill fw-bold border-0 shadow-sm"
                                        style="background: var(--gradient);">
                                        <i class="bi bi-search me-2"></i>Lacak Sekarang
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 text-center pb-4 pt-0">
                            <p class="text-muted small mb-0">Butuh bantuan? <a href="{{ route('page.show', 'bantuan') }}"
                                    class="text-primary fw-medium text-decoration-none hover-primary">Hubungi Layanan
                                    Pelanggan</a></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection