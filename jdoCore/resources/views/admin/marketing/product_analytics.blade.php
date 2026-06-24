@extends('admin.layouts.app')

@section('title', 'Analitik Produk')
@section('page-title', '📦 Analitik Produk')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .tab-nav {
        display: flex; gap: 4px; background: var(--card-bg, #f3f4f6); border-radius: 14px; padding: 4px;
        border: 1px solid var(--border-color);
    }
    .tab-btn {
        padding: 8px 20px; border-radius: 10px; border: none;
        background: transparent; color: var(--text-muted); font-size: 0.82rem; font-weight: 600;
        transition: all 0.2s; cursor: pointer; text-decoration: none;
    }
    .tab-btn:hover { background: rgba(79,70,229,0.06); color: #4F46E5; }
    .tab-btn.active { background: var(--primary, #4F46E5); color: #fff; box-shadow: 0 4px 12px rgba(79,70,229,0.3); }
    .product-rank-card {
        display: flex; align-items: center; gap: 14px; padding: 14px 0;
        border-bottom: 1px solid var(--border-color);
    }
    .product-rank-card:last-child { border-bottom: none; }
    .rank-badge {
        width: 30px; height: 30px; border-radius: 10px; display: flex;
        align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 800;
    }
    .rank-1 { background: rgba(255,215,0,0.2); color: #B8860B; }
    .rank-2 { background: rgba(192,192,192,0.3); color: #71706E; }
    .rank-3 { background: rgba(205,127,50,0.2); color: #8B4513; }
    .rank-default { background: rgba(107,114,128,0.1); color: #6B7280; }
    .product-img-sm {
        width: 48px; height: 48px; border-radius: 12px; object-fit: cover;
    }
    .change-badge {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 700;
    }
    .change-badge.up { background: rgba(16,185,129,0.1); color: #059669; }
    .change-badge.down { background: rgba(239,68,68,0.1); color: #DC2626; }
    .change-badge.neutral { background: rgba(107,114,128,0.1); color: #6B7280; }
    .funnel-row {
        display: flex; align-items: center; gap: 14px; padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
    }
    .funnel-row:last-child { border-bottom: none; }
    .funnel-bar {
        height: 8px; border-radius: 4px; transition: width 0.5s ease;
    }
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

    {{-- Date Range --}}
    <div class="date-range-bar d-flex flex-wrap align-items-center gap-2 mb-4">
        <span class="fw-bold text-muted small me-2"><i class="bi bi-calendar3 me-1"></i> Periode:</span>
        @foreach(['7d' => '7 Hari', '14d' => '14 Hari', '30d' => '30 Hari', 'this_month' => 'Bulan Ini'] as $key => $label)
            <a href="{{ route('admin.marketing.product_analytics', ['range' => $key, 'tab' => $tab]) }}" class="range-btn {{ $range['preset'] === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    {{-- Tab Navigation --}}
    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <div class="tab-nav">
            <a href="{{ route('admin.marketing.product_analytics', ['range' => $range['preset'], 'tab' => 'viewed', 'start_date' => $range['startDate'], 'end_date' => $range['endDate']]) }}"
               class="tab-btn {{ $tab === 'viewed' ? 'active' : '' }}">
                <i class="bi bi-eye me-1"></i>Paling Dilihat
            </a>
            <a href="{{ route('admin.marketing.product_analytics', ['range' => $range['preset'], 'tab' => 'bought', 'start_date' => $range['startDate'], 'end_date' => $range['endDate']]) }}"
               class="tab-btn {{ $tab === 'bought' ? 'active' : '' }}">
                <i class="bi bi-bag-check me-1"></i>Paling Dibeli
            </a>
            <a href="{{ route('admin.marketing.product_analytics', ['range' => $range['preset'], 'tab' => 'carted', 'start_date' => $range['startDate'], 'end_date' => $range['endDate']]) }}"
               class="tab-btn {{ $tab === 'carted' ? 'active' : '' }}">
                <i class="bi bi-cart-plus me-1"></i>Paling Di-keranjang
            </a>
            <a href="{{ route('admin.marketing.product_analytics', ['range' => $range['preset'], 'tab' => 'funnel', 'start_date' => $range['startDate'], 'end_date' => $range['endDate']]) }}"
               class="tab-btn {{ $tab === 'funnel' ? 'active' : '' }}">
                <i class="bi bi-funnel me-1"></i>Conversion Funnel
            </a>
        </div>
    </div>

    @if($tab === 'viewed')
    {{-- PALING DILIHAT --}}
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-eye-fill me-2 text-primary"></i>Top 20 Produk Paling Dilihat</h6>
                </div>
                <div class="card-body px-4 py-2">
                    @forelse($mostViewed as $item)
                    <div class="product-rank-card">
                        <div class="rank-badge {{ $loop->iteration <= 3 ? 'rank-'.$loop->iteration : 'rank-default' }}">{{ $loop->iteration }}</div>
                        @if($item->product && $item->product->primaryImage)
                            <img src="{{ asset($item->product->primaryImage->path) }}" class="product-img-sm" alt="">
                        @else
                            <div class="product-img-sm d-flex align-items-center justify-content-center" style="background: var(--sidebar-hover);">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-bold text-truncate" style="font-size: 0.85rem;">{{ $item->product->name ?? 'N/A' }}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fw-bold" style="font-size: 0.68rem;">{{ number_format($item->views) }} views</span>
                                <span class="change-badge {{ $item->change['direction'] }}">
                                    <i class="bi bi-arrow-{{ $item->change['direction'] === 'up' ? 'up' : ($item->change['direction'] === 'down' ? 'down' : 'right') }}-short"></i>
                                    {{ $item->change['value'] }}%
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-eye-slash display-4 d-block mb-3 opacity-25"></i>
                        <p class="small">Belum ada data view produk</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-primary"></i>Grafik Views</h6>
                </div>
                <div class="card-body p-4">
                    @if(count($viewedChartData) > 0)
                        <canvas id="viewedChart" height="400"></canvas>
                    @else
                        <div class="text-center text-muted py-4"><p class="small">Belum ada data</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @elseif($tab === 'bought')
    {{-- PALING DIBELI --}}
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bag-check-fill me-2 text-success"></i>Top 20 Produk Paling Dibeli</h6>
                </div>
                <div class="card-body px-4 py-2">
                    @forelse($mostBought as $item)
                    <div class="product-rank-card">
                        <div class="rank-badge {{ $loop->iteration <= 3 ? 'rank-'.$loop->iteration : 'rank-default' }}">{{ $loop->iteration }}</div>
                        @if($item->product && $item->product->primaryImage)
                            <img src="{{ asset($item->product->primaryImage->path) }}" class="product-img-sm" alt="">
                        @else
                            <div class="product-img-sm d-flex align-items-center justify-content-center" style="background: var(--sidebar-hover);">
                                <i class="bi bi-image text-muted"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-bold text-truncate" style="font-size: 0.85rem;">{{ $item->product_name }}</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill fw-bold" style="font-size: 0.68rem;">{{ number_format($item->total_sold) }} terjual</span>
                                <span class="text-muted" style="font-size: 0.7rem;">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</span>
                                <span class="change-badge {{ $item->change['direction'] }}">
                                    <i class="bi bi-arrow-{{ $item->change['direction'] === 'up' ? 'up' : ($item->change['direction'] === 'down' ? 'down' : 'right') }}-short"></i>
                                    {{ $item->change['value'] }}%
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-bag display-4 d-block mb-3 opacity-25"></i>
                        <p class="small">Belum ada data penjualan</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-success"></i>Grafik Penjualan</h6>
                </div>
                <div class="card-body p-4">
                    @if(count($boughtChartData) > 0)
                        <canvas id="boughtChart" height="400"></canvas>
                    @else
                        <div class="text-center text-muted py-4"><p class="small">Belum ada data</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @elseif($tab === 'carted')
    {{-- PALING DI-KERANJANG --}}
    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h6 class="mb-0 fw-bold"><i class="bi bi-cart-plus-fill me-2 text-warning"></i>Top 20 Produk Paling Dimasukkan Keranjang</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="font-size: 0.75rem;">#</th>
                            <th style="font-size: 0.75rem;">Produk</th>
                            <th style="font-size: 0.75rem;">Jumlah Keranjang</th>
                            <th style="font-size: 0.75rem;">Total Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mostCarted as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="rank-badge {{ $loop->iteration <= 3 ? 'rank-'.$loop->iteration : 'rank-default' }}">{{ $loop->iteration }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($item->product && $item->product->primaryImage)
                                        <img src="{{ asset($item->product->primaryImage->path) }}" class="product-img-sm" alt="">
                                    @else
                                        <div class="product-img-sm d-flex align-items-center justify-content-center" style="background: var(--sidebar-hover);">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                    <span class="fw-bold" style="font-size: 0.85rem;">{{ $item->product->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill fw-bold" style="font-size: 0.72rem;">{{ number_format($item->cart_count) }} keranjang</span>
                            </td>
                            <td>
                                <span class="fw-bold">{{ number_format($item->total_qty) }} pcs</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-cart display-4 d-block mb-3 opacity-25"></i>
                                <p class="small">Belum ada data keranjang</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @elseif($tab === 'funnel')
    {{-- CONVERSION FUNNEL --}}
    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h6 class="mb-0 fw-bold"><i class="bi bi-funnel-fill me-2" style="color: #7C3AED;"></i>Conversion Funnel — Top 10 Produk</h6>
        </div>
        <div class="card-body px-4 py-2">
            @forelse($funnelProducts as $f)
            <div class="funnel-row">
                @if($f['product']->primaryImage)
                    <img src="{{ asset($f['product']->primaryImage->path) }}" class="product-img-sm" alt="">
                @else
                    <div class="product-img-sm d-flex align-items-center justify-content-center" style="background: var(--sidebar-hover);">
                        <i class="bi bi-image text-muted"></i>
                    </div>
                @endif
                <div class="flex-grow-1">
                    <div class="fw-bold mb-2" style="font-size: 0.88rem;">{{ $f['product']->name }}</div>
                    <div class="d-flex gap-3 flex-wrap">
                        {{-- Views --}}
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted" style="font-size: 0.7rem;">Views</span>
                                <span class="fw-bold" style="font-size: 0.72rem;">{{ number_format($f['views']) }}</span>
                            </div>
                            <div style="height: 8px; background: var(--border-color); border-radius: 4px; overflow: hidden;">
                                <div class="funnel-bar" style="width: 100%; background: #4F46E5;"></div>
                            </div>
                        </div>
                        {{-- Cart --}}
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted" style="font-size: 0.7rem;">Cart ({{ $f['view_to_cart'] }}%)</span>
                                <span class="fw-bold" style="font-size: 0.72rem;">{{ number_format($f['carts']) }}</span>
                            </div>
                            <div style="height: 8px; background: var(--border-color); border-radius: 4px; overflow: hidden;">
                                <div class="funnel-bar" style="width: {{ $f['views'] > 0 ? round($f['carts'] / $f['views'] * 100) : 0 }}%; background: #F59E0B;"></div>
                            </div>
                        </div>
                        {{-- Purchase --}}
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted" style="font-size: 0.7rem;">Purchase ({{ $f['view_to_purchase'] }}%)</span>
                                <span class="fw-bold" style="font-size: 0.72rem;">{{ number_format($f['purchases']) }}</span>
                            </div>
                            <div style="height: 8px; background: var(--border-color); border-radius: 4px; overflow: hidden;">
                                <div class="funnel-bar" style="width: {{ $f['views'] > 0 ? max(round($f['purchases'] / $f['views'] * 100), 2) : 0 }}%; background: #10B981;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-5">
                <i class="bi bi-funnel display-4 d-block mb-3 opacity-25"></i>
                <p class="small">Belum ada data funnel. Data akan muncul setelah pengunjung mulai melihat produk.</p>
            </div>
            @endforelse
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.flatpickr', { dateFormat: 'Y-m-d' });

    const barColors = [
        'rgba(79,70,229,0.8)', 'rgba(124,58,237,0.8)', 'rgba(14,165,233,0.8)',
        'rgba(16,185,129,0.8)', 'rgba(245,158,11,0.8)', 'rgba(236,72,153,0.8)',
        'rgba(99,102,241,0.7)', 'rgba(139,92,246,0.7)', 'rgba(244,63,94,0.7)', 'rgba(20,184,166,0.7)'
    ];

    // Viewed Chart
    const viewedCtx = document.getElementById('viewedChart');
    if (viewedCtx) {
        new Chart(viewedCtx, {
            type: 'bar',
            data: {
                labels: @json($viewedChartNames),
                datasets: [{ label: 'Views', data: @json($viewedChartData), backgroundColor: barColors, borderRadius: 8, borderSkipped: false }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(0,0,0,0.85)', padding: 12, cornerRadius: 10 } },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 }, color: '#9CA3AF' } },
                    y: { grid: { display: false }, ticks: { font: { size: 11, weight: '500' }, color: '#374151' } },
                }
            }
        });
    }

    // Bought Chart
    const boughtCtx = document.getElementById('boughtChart');
    if (boughtCtx) {
        new Chart(boughtCtx, {
            type: 'bar',
            data: {
                labels: @json($boughtChartNames),
                datasets: [{ label: 'Terjual', data: @json($boughtChartData), backgroundColor: barColors.map(c => c.replace('0.8', '0.8').replace('229', '181')), borderRadius: 8, borderSkipped: false }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(0,0,0,0.85)', padding: 12, cornerRadius: 10 } },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 }, color: '#9CA3AF' } },
                    y: { grid: { display: false }, ticks: { font: { size: 11, weight: '500' }, color: '#374151' } },
                }
            }
        });
    }
});
</script>
@endpush
