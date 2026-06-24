@extends('admin.layouts.app')

@section('title', 'Keranjang Terbengkalai')
@section('page-title', '🛒 Keranjang Terbengkalai')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .abandoned-stat {
        border-radius: 18px;
        border: 1px solid var(--border-color);
        padding: 24px;
        text-align: center;
    }
    .abandoned-stat .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .cart-detail-card {
        border-radius: 18px;
        border: 1px solid var(--border-color);
        transition: all 0.3s;
        overflow: hidden;
    }
    .cart-detail-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    }
    .cart-product-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
    }
    .cart-product-item:last-child { border-bottom: none; }
    .cart-product-img {
        width: 48px; height: 48px; border-radius: 10px; object-fit: cover;
    }
    .top-abandoned-item {
        display: flex; align-items: center; gap: 12px; padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
    }
    .top-abandoned-item:last-child { border-bottom: none; }
    .date-range-bar {
        background: var(--card-bg, #fff); border-radius: 16px; border: 1px solid var(--border-color); padding: 12px 20px;
    }
    .range-btn {
        padding: 6px 16px; border-radius: 10px; border: 1px solid var(--border-color);
        background: transparent; color: var(--text-muted); font-size: 0.78rem; font-weight: 600; transition: all 0.2s; text-decoration: none;
    }
    .range-btn:hover, .range-btn.active { background: var(--primary, #4F46E5); color: #fff; border-color: var(--primary, #4F46E5); }
</style>
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.marketing.dashboard') }}" class="text-decoration-none text-muted fw-medium" style="font-size: 0.85rem;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard Marketing
        </a>
    </div>

    {{-- Date Range & Filter --}}
    <div class="date-range-bar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="fw-bold text-muted small me-2"><i class="bi bi-calendar3 me-1"></i> Periode:</span>
            @foreach(['7d' => '7 Hari', '14d' => '14 Hari', '30d' => '30 Hari', 'this_month' => 'Bulan Ini'] as $key => $label)
                <a href="{{ route('admin.marketing.abandoned_carts', ['range' => $key, 'type' => $type]) }}" class="range-btn {{ $range['preset'] === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.marketing.abandoned_carts', ['range' => $range['preset'], 'type' => 'all']) }}" class="range-btn {{ $type === 'all' ? 'active' : '' }}">
                Semua ({{ number_format($totalAbandoned) }})
            </a>
            <a href="{{ route('admin.marketing.abandoned_carts', ['range' => $range['preset'], 'type' => 'user']) }}" class="range-btn {{ $type === 'user' ? 'active' : '' }}">
                <i class="bi bi-person me-1"></i> User ({{ number_format($userCount) }})
            </a>
            <a href="{{ route('admin.marketing.abandoned_carts', ['range' => $range['preset'], 'type' => 'guest']) }}" class="range-btn {{ $type === 'guest' ? 'active' : '' }}">
                <i class="bi bi-incognito me-1"></i> Guest ({{ number_format($guestCount) }})
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card abandoned-stat border-0 shadow-sm">
                <div class="stat-value text-warning">{{ number_format($totalAbandoned) }}</div>
                <div class="text-muted small mt-1 fw-medium">Keranjang Terbengkalai</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card abandoned-stat border-0 shadow-sm">
                <div class="stat-value text-danger">Rp {{ number_format($potentialRevenue, 0, ',', '.') }}</div>
                <div class="text-muted small mt-1 fw-medium">Potensi Pendapatan Hilang</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card abandoned-stat border-0 shadow-sm">
                <div class="stat-value text-info">{{ $topAbandoned->count() }}</div>
                <div class="text-muted small mt-1 fw-medium">Produk Paling Sering Ditinggalkan</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Cart List --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cart-x me-2 text-warning"></i>Daftar Keranjang</h6>
                </div>
                <div class="card-body p-0">
                    @forelse($carts as $cart)
                    <div class="p-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="bi bi-person-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $cart->user->name ?? 'Guest' }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        @if($cart->user)
                                            {{ $cart->user->email ?? '' }} · {{ $cart->user->phone ?? '' }}
                                        @else
                                            Session: {{ \Str::limit($cart->session_id, 15) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-danger">Rp {{ number_format($cart->total, 0, ',', '.') }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $cart->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>

                        {{-- Cart Items --}}
                        @foreach($cart->items->take(3) as $item)
                        <div class="cart-product-item">
                            @if($item->product && $item->product->primaryImage)
                                <img src="{{ asset($item->product->primaryImage->path) }}" class="cart-product-img" alt="">
                            @else
                                <div class="cart-product-img d-flex align-items-center justify-content-center" style="background: var(--sidebar-hover); border-radius: 10px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-semibold text-truncate" style="font-size: 0.85rem;">{{ $item->product->name ?? 'Produk Dihapus' }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    {{ $item->quantity }} pcs
                                    @if($item->variant)
                                        · {{ $item->variant->label ?? '' }}
                                    @endif
                                </div>
                            </div>
                            <div class="fw-bold text-primary" style="font-size: 0.85rem;">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </div>
                        </div>
                        @endforeach
                        @if($cart->items->count() > 3)
                            <div class="text-muted text-center py-2" style="font-size: 0.75rem;">
                                + {{ $cart->items->count() - 3 }} produk lainnya
                            </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center text-muted py-5 px-4">
                        <i class="bi bi-cart-check display-3 d-block mb-3 opacity-25"></i>
                        <h6 class="fw-bold">Tidak Ada Keranjang Terbengkalai</h6>
                        <p class="small">Semua pelanggan sudah menyelesaikan pesanannya 🎉</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $carts->links() }}
            </div>
        </div>

        {{-- Top Abandoned Products --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Produk Sering Ditinggalkan</h6>
                </div>
                <div class="card-body px-4 py-2">
                    @forelse($topAbandoned as $item)
                    <div class="top-abandoned-item">
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill fw-bold" style="font-size: 0.65rem; width: 24px; text-align: center;">{{ $loop->iteration }}</span>
                        @if($item->product && $item->product->primaryImage)
                            <img src="{{ asset($item->product->primaryImage->path) }}" class="cart-product-img" alt="" style="width: 40px; height: 40px;">
                        @else
                            <div class="d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: var(--sidebar-hover); border-radius: 10px;">
                                <i class="bi bi-image text-muted small"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold" style="font-size: 0.82rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; white-space: normal; line-height: 1.3;">{{ $item->product->name ?? 'N/A' }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.7rem;">{{ $item->cart_count }} keranjang · {{ $item->total_qty }} pcs</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">
                        <p class="small">Belum ada data</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Action Tips --}}
            <div class="card border-0 shadow-sm mt-4" style="border-radius: 20px; background: linear-gradient(135deg, rgba(79,70,229,0.05) 0%, rgba(236,72,153,0.05) 100%);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb me-2 text-warning"></i>Tips Follow-up</h6>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1" style="font-size: 0.85rem;"></i>
                            <span class="small text-muted">Kirim reminder WA ke pelanggan yang sudah isi keranjang > 24 jam</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1" style="font-size: 0.85rem;"></i>
                            <span class="small text-muted">Berikan voucher diskon khusus untuk produk yang sering ditinggalkan</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1" style="font-size: 0.85rem;"></i>
                            <span class="small text-muted">Cek apakah harga atau ongkir terlalu tinggi di produk tersebut</span>
                        </div>
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill text-success mt-1" style="font-size: 0.85rem;"></i>
                            <span class="small text-muted">Tambahkan flash sale untuk boost konversi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
flatpickr('.flatpickr', { dateFormat: 'Y-m-d' });
</script>
@endpush
