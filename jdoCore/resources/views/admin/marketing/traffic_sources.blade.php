@extends('admin.layouts.app')

@section('title', 'Sumber Traffic')
@section('page-title', '🌐 Analisis Sumber Traffic')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .source-card {
        border-radius: 18px;
        border: 1px solid var(--border-color);
        transition: all 0.3s;
        overflow: hidden;
    }
    .source-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.06);
    }
    .source-icon-lg {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
    }
    .change-badge {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700;
    }
    .change-badge.up { background: rgba(16,185,129,0.1); color: #059669; }
    .change-badge.down { background: rgba(239,68,68,0.1); color: #DC2626; }
    .change-badge.neutral { background: rgba(107,114,128,0.1); color: #6B7280; }
    .share-bar {
        height: 6px; border-radius: 3px; background: var(--border-color); overflow: hidden;
    }
    .share-bar-fill { height: 100%; border-radius: 3px; transition: width 0.5s ease; }
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

@php
    $sourceIcons = [
        'google' => ['icon' => 'bi-google', 'color' => '#4285F4', 'bg' => 'rgba(66,133,244,0.1)'],
        'facebook' => ['icon' => 'bi-facebook', 'color' => '#1877F2', 'bg' => 'rgba(24,119,242,0.1)'],
        'instagram' => ['icon' => 'bi-instagram', 'color' => '#E1306C', 'bg' => 'rgba(225,48,108,0.1)'],
        'tiktok' => ['icon' => 'bi-tiktok', 'color' => '#000000', 'bg' => 'rgba(0,0,0,0.06)'],
        'twitter' => ['icon' => 'bi-twitter-x', 'color' => '#000000', 'bg' => 'rgba(0,0,0,0.06)'],
        'whatsapp' => ['icon' => 'bi-whatsapp', 'color' => '#25D366', 'bg' => 'rgba(37,211,102,0.1)'],
        'youtube' => ['icon' => 'bi-youtube', 'color' => '#FF0000', 'bg' => 'rgba(255,0,0,0.1)'],
        'telegram' => ['icon' => 'bi-telegram', 'color' => '#0088CC', 'bg' => 'rgba(0,136,204,0.1)'],
        'direct' => ['icon' => 'bi-cursor-fill', 'color' => '#6B7280', 'bg' => 'rgba(107,114,128,0.1)'],
        'other' => ['icon' => 'bi-link-45deg', 'color' => '#D97706', 'bg' => 'rgba(245,158,11,0.1)'],
        'bing' => ['icon' => 'bi-search', 'color' => '#008373', 'bg' => 'rgba(0,131,115,0.1)'],
        'yahoo' => ['icon' => 'bi-search', 'color' => '#720E9E', 'bg' => 'rgba(114,14,158,0.1)'],
        'linkedin' => ['icon' => 'bi-linkedin', 'color' => '#0A66C2', 'bg' => 'rgba(10,102,194,0.1)'],
    ];
@endphp

@section('content')
    {{-- Back Link --}}
    <div class="mb-3">
        <a href="{{ route('admin.marketing.dashboard') }}" class="text-decoration-none text-muted fw-medium" style="font-size: 0.85rem;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard Marketing
        </a>
    </div>

    {{-- Date Range Bar --}}
    <div class="date-range-bar d-flex flex-wrap align-items-center gap-2 mb-4">
        <span class="fw-bold text-muted small me-2"><i class="bi bi-calendar3 me-1"></i> Periode:</span>
        @foreach(['7d' => '7 Hari', '14d' => '14 Hari', '30d' => '30 Hari', 'this_month' => 'Bulan Ini'] as $key => $label)
            <a href="{{ route('admin.marketing.traffic_sources', ['range' => $key]) }}" class="range-btn {{ $range['preset'] === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
        <form method="GET" action="{{ route('admin.marketing.traffic_sources') }}" class="ms-auto d-flex align-items-center gap-2">
            <input type="hidden" name="range" value="custom">
            <input type="text" name="start_date" class="form-control form-control-sm flatpickr" value="{{ $range['startDate'] }}" style="width: 120px; border-radius: 10px; font-size: 0.8rem;">
            <span class="text-muted small">—</span>
            <input type="text" name="end_date" class="form-control form-control-sm flatpickr" value="{{ $range['endDate'] }}" style="width: 120px; border-radius: 10px; font-size: 0.8rem;">
            <button type="submit" class="btn btn-sm btn-primary" style="border-radius: 10px;"><i class="bi bi-funnel"></i></button>
        </form>
    </div>

    {{-- Source Cards Grid --}}
    <div class="row g-3 mb-4">
        @foreach($sourceComparison as $src)
        @php
            $meta = $sourceIcons[$src['source_key']] ?? $sourceIcons['other'];
        @endphp
        <div class="col-sm-6 col-xl-4">
            <div class="card source-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="source-icon-lg" style="background: {{ $meta['bg'] }}; color: {{ $meta['color'] }};">
                            <i class="bi {{ $meta['icon'] }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold" style="font-size: 0.95rem;">{{ $src['source'] }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $src['share'] }}% dari total traffic</div>
                        </div>
                        <span class="change-badge {{ $src['change']['direction'] }}">
                            <i class="bi bi-arrow-{{ $src['change']['direction'] === 'up' ? 'up' : ($src['change']['direction'] === 'down' ? 'down' : 'right') }}-short"></i>
                            {{ $src['change']['value'] }}%
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <div class="fw-bold fs-5">{{ number_format($src['visitors']) }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">Pengunjung</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-5 text-muted">{{ number_format($src['previous_visitors']) }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">Sebelumnya</div>
                        </div>
                    </div>
                    <div class="share-bar">
                        <div class="share-bar-fill" style="width: {{ $src['share'] }}%; background: {{ $meta['color'] }};"></div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Source Trend Chart --}}
    @if(count($sourceTrend) > 0)
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2 text-primary"></i>Trend Sumber Traffic (Top 5)</h6>
        </div>
        <div class="card-body p-4">
            <canvas id="sourceTrendChart" height="300"></canvas>
        </div>
    </div>
    @endif

    {{-- Top Referrer Domains --}}
    @if($topDomains->count() > 0)
    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h6 class="mb-0 fw-bold"><i class="bi bi-link-45deg me-2 text-warning"></i>Top Domain Referrer (Lainnya)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="font-size: 0.75rem;">#</th>
                            <th style="font-size: 0.75rem;">Domain</th>
                            <th style="font-size: 0.75rem;">Pengunjung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topDomains as $i => $domain)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">{{ $i + 1 }}</td>
                            <td>
                                <span class="fw-semibold">{{ $domain->referrer_domain }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fw-bold">{{ number_format($domain->count) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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

    const trendCtx = document.getElementById('sourceTrendChart');
    if (trendCtx) {
        const colors = ['#4F46E5', '#EC4899', '#10B981', '#F59E0B', '#0EA5E9'];
        const datasets = @json($sourceTrend).map((src, i) => ({
            label: src.label,
            data: src.data,
            borderColor: colors[i % colors.length],
            backgroundColor: colors[i % colors.length] + '15',
            borderWidth: 2.5,
            fill: false,
            tension: 0.4,
            pointRadius: 2,
            pointHoverRadius: 6,
        }));

        new Chart(trendCtx, {
            type: 'line',
            data: { labels: @json($trendLabels), datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top', align: 'end',
                        labels: { boxWidth: 12, boxHeight: 12, borderRadius: 3, useBorderRadius: true, padding: 16, font: { size: 11, weight: '500' } }
                    },
                    tooltip: { backgroundColor: 'rgba(0,0,0,0.85)', padding: 12, cornerRadius: 10 }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 12, color: '#9CA3AF' } },
                    y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 }, color: '#9CA3AF' } },
                }
            }
        });
    }
});
</script>
@endpush
