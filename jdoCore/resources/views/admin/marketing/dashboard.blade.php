@extends('admin.layouts.app')

@section('title', 'Marketing Dashboard')
@section('page-title', '📊 Marketing Dashboard')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<style>
    .marketing-kpi {
        border-radius: 20px;
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .marketing-kpi:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.08);
    }
    .kpi-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    .kpi-change {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .kpi-change.up { background: rgba(16,185,129,0.1); color: #059669; }
    .kpi-change.down { background: rgba(239,68,68,0.1); color: #DC2626; }
    .kpi-change.neutral { background: rgba(107,114,128,0.1); color: #6B7280; }
    .date-range-bar {
        background: var(--card-bg, #fff);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 12px 20px;
    }
    .range-btn {
        padding: 6px 16px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background: transparent;
        color: var(--text-muted);
        font-size: 0.78rem;
        font-weight: 600;
        transition: all 0.2s;
        text-decoration: none;
    }
    .range-btn:hover, .range-btn.active {
        background: var(--primary, #4F46E5);
        color: #fff;
        border-color: var(--primary, #4F46E5);
    }
    .quick-stat-card {
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 20px;
        text-align: center;
    }
    .quick-stat-card .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .abandoned-cart-item {
        border-bottom: 1px solid var(--border-color);
        padding: 14px 0;
    }
    .abandoned-cart-item:last-child { border-bottom: none; }
    .source-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
</style>
@endsection

@section('content')
    {{-- Date Range Bar --}}
    <div class="date-range-bar d-flex flex-wrap align-items-center gap-2 mb-4">
        <span class="fw-bold text-muted small me-2"><i class="bi bi-calendar3 me-1"></i> Periode:</span>
        @php
            $presets = [
                '7d' => '7 Hari',
                '14d' => '14 Hari',
                '30d' => '30 Hari',
                'this_month' => 'Bulan Ini',
                'last_month' => 'Bulan Lalu',
            ];
        @endphp
        @foreach($presets as $key => $label)
            <a href="{{ route('admin.marketing.dashboard', ['range' => $key]) }}"
               class="range-btn {{ $range['preset'] === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
        <div class="ms-auto d-flex align-items-center gap-2">
            <form method="GET" action="{{ route('admin.marketing.dashboard') }}" class="d-flex align-items-center gap-2" id="customRangeForm">
                <input type="hidden" name="range" value="custom">
                <input type="text" name="start_date" id="startDate" class="form-control form-control-sm" style="width: 120px; border-radius: 10px; font-size: 0.8rem;"
                       value="{{ $range['startDate'] }}" placeholder="Dari">
                <span class="text-muted small">—</span>
                <input type="text" name="end_date" id="endDate" class="form-control form-control-sm" style="width: 120px; border-radius: 10px; font-size: 0.8rem;"
                       value="{{ $range['endDate'] }}" placeholder="Sampai">
                <button type="submit" class="btn btn-sm btn-primary" style="border-radius: 10px; font-size: 0.78rem;">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </form>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        @php
            $kpis = [
                ['label' => 'Total Pengunjung', 'value' => number_format($totalVisitors), 'icon' => 'bi-people-fill', 'color' => '#4F46E5', 'bg' => 'rgba(79,70,229,0.1)', 'change' => $visitorsChange],
                ['label' => 'Total Halaman Dilihat', 'value' => number_format($totalPageViews), 'icon' => 'bi-eye-fill', 'color' => '#7C3AED', 'bg' => 'rgba(124,58,237,0.1)', 'change' => $pageViewsChange],
                ['label' => 'Pengunjung Unik', 'value' => number_format($uniqueVisitors), 'icon' => 'bi-person-badge-fill', 'color' => '#0EA5E9', 'bg' => 'rgba(14,165,233,0.1)', 'change' => $uniqueVisitorsChange],
                ['label' => 'Cart Abandon Rate', 'value' => $cartAbandonRate . '%', 'icon' => 'bi-cart-x-fill', 'color' => '#F59E0B', 'bg' => 'rgba(245,158,11,0.1)', 'change' => $cartAbandonChange],
            ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="col-sm-6 col-xl-3">
            <div class="card marketing-kpi border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="kpi-icon" style="background: {{ $kpi['bg'] }}; color: {{ $kpi['color'] }}">
                            <i class="bi {{ $kpi['icon'] }}"></i>
                        </div>
                        <span class="kpi-change {{ $kpi['change']['direction'] }}">
                            @if($kpi['change']['direction'] === 'up')
                                <i class="bi bi-arrow-up-short"></i>
                            @elseif($kpi['change']['direction'] === 'down')
                                <i class="bi bi-arrow-down-short"></i>
                            @else
                                <i class="bi bi-dash"></i>
                            @endif
                            {{ $kpi['change']['value'] }}%
                        </span>
                    </div>
                    <div class="fw-bold fs-4 mb-1">{{ $kpi['value'] }}</div>
                    <div class="text-muted small fw-medium">{{ $kpi['label'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Quick Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card quick-stat-card border-0 shadow-sm">
                <div class="stat-value text-success">{{ number_format($ordersInRange) }}</div>
                <div class="text-muted small mt-1 fw-medium">Pesanan Lunas</div>
                <span class="kpi-change {{ $ordersChange['direction'] }} mt-2">
                    <i class="bi bi-arrow-{{ $ordersChange['direction'] === 'up' ? 'up' : ($ordersChange['direction'] === 'down' ? 'down' : 'right') }}-short"></i>
                    {{ $ordersChange['value'] }}%
                </span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card quick-stat-card border-0 shadow-sm">
                <div class="stat-value text-primary">Rp {{ number_format($revenueInRange, 0, ',', '.') }}</div>
                <div class="text-muted small mt-1 fw-medium">Pendapatan</div>
                <span class="kpi-change {{ $revenueChange['direction'] }} mt-2">
                    <i class="bi bi-arrow-{{ $revenueChange['direction'] === 'up' ? 'up' : ($revenueChange['direction'] === 'down' ? 'down' : 'right') }}-short"></i>
                    {{ $revenueChange['value'] }}%
                </span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card quick-stat-card border-0 shadow-sm">
                <div class="stat-value" style="color: #7C3AED;">{{ number_format($newCustomers) }}</div>
                <div class="text-muted small mt-1 fw-medium">Pelanggan Baru</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card quick-stat-card border-0 shadow-sm">
                <div class="stat-value" style="color: #0EA5E9;">{{ $conversionRate }}%</div>
                <div class="text-muted small mt-1 fw-medium">Conversion Rate</div>
                <div class="text-muted small mt-1" style="font-size: 0.7rem;">Pengunjung → Pesanan</div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-4 mb-4">
        {{-- Visitor Trend --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2 text-primary"></i>Trend Pengunjung</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3" style="font-size: 0.7rem;">
                        {{ $range['rangeLabel'] }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <canvas id="visitorTrendChart" height="280"></canvas>
                </div>
            </div>
        </div>

        {{-- Traffic Source Pie --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2" style="color: #7C3AED;"></i>Sumber Traffic</h6>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    @if(count($sourceData) > 0)
                        <canvas id="trafficSourceChart" height="280"></canvas>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-pie-chart display-4 d-block mb-3 opacity-25"></i>
                            <p class="small">Belum ada data pengunjung</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Top Viewed Products + Abandoned Carts --}}
    <div class="row g-4 mb-4">
        {{-- Top Viewed Products --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-success"></i>Produk Paling Dilihat</h6>
                    <a href="{{ route('admin.marketing.product_analytics') }}" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-primary" style="font-size: 0.75rem;">
                        Detail <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-4">
                    @if(count($topViewedNames) > 0)
                        <canvas id="topViewedChart" height="250"></canvas>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bar-chart display-4 d-block mb-3 opacity-25"></i>
                            <p class="small">Belum ada data view produk</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Abandoned Carts Summary --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-cart-x me-2 text-warning"></i>Keranjang Terbengkalai</h6>
                    <a href="{{ route('admin.marketing.abandoned_carts') }}" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-primary" style="font-size: 0.75rem;">
                        Semua <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body px-4 py-2">
                    @forelse($abandonedCarts as $cart)
                        <div class="abandoned-cart-item d-flex align-items-center gap-3">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-bold text-truncate" style="font-size: 0.85rem;">{{ $cart->user->name ?? 'Guest' }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    {{ $cart->items->count() }} produk · Rp {{ number_format($cart->total, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="text-muted" style="font-size: 0.65rem;">
                                {{ $cart->updated_at->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-cart-check display-4 d-block mb-3 opacity-25"></i>
                            <p class="small">Tidak ada keranjang terbengkalai</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation Cards --}}
    <div class="row g-3">
        @php
            $navCards = [
                ['route' => 'admin.marketing.visitors', 'icon' => 'bi-eye-fill', 'color' => '#4F46E5', 'title' => 'Detail Pengunjung', 'desc' => 'Lihat data rinci setiap pengunjung, filter berdasarkan device, browser, dan sumber traffic.'],
                ['route' => 'admin.marketing.traffic_sources', 'icon' => 'bi-globe2', 'color' => '#7C3AED', 'title' => 'Sumber Traffic', 'desc' => 'Analisis mendalam sumber pengunjung dari Google, Sosmed, dan lainnya.'],
                ['route' => 'admin.marketing.customer_insights', 'icon' => 'bi-person-check-fill', 'color' => '#0EA5E9', 'title' => 'Insight Pelanggan', 'desc' => 'Statistik pembelian, repeat buyer, dan nilai rata-rata transaksi.'],
                ['route' => 'admin.marketing.product_analytics', 'icon' => 'bi-bar-chart-line-fill', 'color' => '#10B981', 'title' => 'Analitik Produk', 'desc' => 'Ranking produk: paling dilihat, dibeli, dikeranjang + conversion funnel.'],
            ];
        @endphp
        @foreach($navCards as $nav)
        <div class="col-sm-6 col-xl-3">
            <a href="{{ route($nav['route'], ['range' => $range['preset'], 'start_date' => $range['startDate'], 'end_date' => $range['endDate']]) }}"
               class="card border-0 shadow-sm text-decoration-none h-100" style="border-radius: 16px; transition: all 0.3s;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="kpi-icon" style="background: {{ $nav['color'] }}15; color: {{ $nav['color'] }};">
                            <i class="bi {{ $nav['icon'] }}"></i>
                        </div>
                        <h6 class="mb-0 fw-bold" style="color: var(--text-main);">{{ $nav['title'] }}</h6>
                    </div>
                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">{{ $nav['desc'] }}</p>
                </div>
            </a>
        </div>
        @endforeach
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Flatpickr
    flatpickr('#startDate', { dateFormat: 'Y-m-d' });
    flatpickr('#endDate', { dateFormat: 'Y-m-d' });

    // --- Visitor Trend Chart ---
    const trendCtx = document.getElementById('visitorTrendChart');
    if (trendCtx) {
        const ctx = trendCtx.getContext('2d');
        const gradient1 = ctx.createLinearGradient(0, 0, 0, 280);
        gradient1.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
        gradient1.addColorStop(1, 'rgba(79, 70, 229, 0.01)');

        const gradient2 = ctx.createLinearGradient(0, 0, 0, 280);
        gradient2.addColorStop(0, 'rgba(124, 58, 237, 0.15)');
        gradient2.addColorStop(1, 'rgba(124, 58, 237, 0.01)');

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    label: 'Pengunjung',
                    data: @json($trendVisitors),
                    borderColor: '#4F46E5',
                    backgroundColor: gradient1,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#4F46E5',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3,
                }, {
                    label: 'Page Views',
                    data: @json($trendPageViews),
                    borderColor: '#7C3AED',
                    backgroundColor: gradient2,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#7C3AED',
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top', align: 'end',
                        labels: { boxWidth: 12, boxHeight: 12, borderRadius: 3, useBorderRadius: true, padding: 20, font: { size: 12, family: "'Plus Jakarta Sans', sans-serif", weight: '500' } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)', padding: 14, cornerRadius: 12,
                        titleFont: { size: 13, family: "'Plus Jakarta Sans', sans-serif" },
                        bodyFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 10, color: '#9CA3AF' } },
                    y: { position: 'left', grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 }, color: '#9CA3AF' } },
                    y1: { position: 'right', grid: { display: false }, ticks: { font: { size: 10 }, color: '#9CA3AF' } },
                }
            }
        });
    }

    // --- Traffic Source Donut ---
    const sourceCtx = document.getElementById('trafficSourceChart');
    if (sourceCtx) {
        const sourceColors = ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981', '#0EA5E9', '#6366F1', '#8B5CF6', '#F43F5E', '#14B8A6'];
        new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: @json($sourceLabels),
                datasets: [{
                    data: @json($sourceData),
                    backgroundColor: sourceColors.slice(0, @json(count($sourceLabels))),
                    borderWidth: 3, borderColor: '#fff', hoverOffset: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10, boxHeight: 10, borderRadius: 3, useBorderRadius: true, padding: 10,
                            font: { size: 10, family: "'Plus Jakarta Sans', sans-serif", weight: '500' },
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)', padding: 12, cornerRadius: 10,
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? Math.round(ctx.raw / total * 100) : 0;
                                return ctx.label + ': ' + ctx.raw + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // --- Top Viewed Products Bar ---
    const viewedCtx = document.getElementById('topViewedChart');
    if (viewedCtx) {
        new Chart(viewedCtx, {
            type: 'bar',
            data: {
                labels: @json($topViewedNames),
                datasets: [{
                    label: 'Views',
                    data: @json($topViewedCounts),
                    backgroundColor: [
                        'rgba(79,70,229,0.8)', 'rgba(124,58,237,0.8)', 'rgba(14,165,233,0.8)',
                        'rgba(16,185,129,0.8)', 'rgba(245,158,11,0.8)', 'rgba(236,72,153,0.8)',
                        'rgba(99,102,241,0.7)', 'rgba(139,92,246,0.7)', 'rgba(244,63,94,0.7)', 'rgba(20,184,166,0.7)'
                    ],
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: 'rgba(0,0,0,0.85)', padding: 12, cornerRadius: 10 }
                },
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
