@extends('admin.layouts.app')

@section('title', 'Pengunjung')
@section('page-title', '👁️ Data Pengunjung')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .filter-bar {
        background: var(--card-bg, #fff);
        border-radius: 16px;
        border: 1px solid var(--border-color);
        padding: 16px 20px;
    }
    .visitor-row {
        transition: background 0.2s;
    }
    .visitor-row:hover {
        background: rgba(79,70,229,0.03) !important;
    }
    .device-badge {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .device-badge.mobile { background: rgba(236,72,153,0.1); color: #DB2777; }
    .device-badge.desktop { background: rgba(79,70,229,0.1); color: #4F46E5; }
    .device-badge.tablet { background: rgba(14,165,233,0.1); color: #0284C7; }
    .source-badge {
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .source-badge.google { background: rgba(66,133,244,0.1); color: #4285F4; }
    .source-badge.facebook { background: rgba(24,119,242,0.1); color: #1877F2; }
    .source-badge.instagram { background: rgba(225,48,108,0.1); color: #E1306C; }
    .source-badge.tiktok { background: rgba(0,0,0,0.08); color: #000; }
    .source-badge.twitter { background: rgba(29,161,242,0.1); color: #1DA1F2; }
    .source-badge.whatsapp { background: rgba(37,211,102,0.1); color: #25D366; }
    .source-badge.direct { background: rgba(107,114,128,0.1); color: #6B7280; }
    .source-badge.other { background: rgba(245,158,11,0.1); color: #D97706; }
    .stat-mini {
        text-align: center;
        padding: 16px;
        border-radius: 14px;
        border: 1px solid var(--border-color);
    }
</style>
@endsection

@section('content')
    {{-- Back Link --}}
    <div class="mb-3">
        <a href="{{ route('admin.marketing.dashboard') }}" class="text-decoration-none text-muted fw-medium" style="font-size: 0.85rem;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard Marketing
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card stat-mini border-0 shadow-sm">
                <div class="fw-bold fs-4 text-primary">{{ number_format($totalCount) }}</div>
                <div class="text-muted small fw-medium">Total Pengunjung</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card stat-mini border-0 shadow-sm">
                <div class="fw-bold fs-4" style="color: #EC4899;">{{ $mobilePercent }}%</div>
                <div class="text-muted small fw-medium">Mobile Visitors</div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card stat-mini border-0 shadow-sm">
                <div class="fw-bold fs-4 text-success">{{ $range['rangeLabel'] }}</div>
                <div class="text-muted small fw-medium">Periode</div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar mb-4">
        <form method="GET" action="{{ route('admin.marketing.visitors') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Dari</label>
                <input type="text" name="start_date" class="form-control form-control-sm flatpickr" value="{{ $range['startDate'] }}" style="border-radius: 10px;">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Sampai</label>
                <input type="text" name="end_date" class="form-control form-control-sm flatpickr" value="{{ $range['endDate'] }}" style="border-radius: 10px;">
            </div>
            <input type="hidden" name="range" value="custom">
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Sumber</label>
                <select name="source" class="form-select form-select-sm" style="border-radius: 10px;">
                    <option value="">Semua</option>
                    @foreach($sources as $src)
                        <option value="{{ $src }}" {{ request('source') == $src ? 'selected' : '' }}>{{ ucfirst($src) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Device</label>
                <select name="device" class="form-select form-select-sm" style="border-radius: 10px;">
                    <option value="">Semua</option>
                    @foreach($devices as $dev)
                        <option value="{{ $dev }}" {{ request('device') == $dev ? 'selected' : '' }}>{{ ucfirst($dev) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold mb-1">Browser</label>
                <select name="browser" class="form-select form-select-sm" style="border-radius: 10px;">
                    <option value="">Semua</option>
                    @foreach($browsers as $br)
                        <option value="{{ $br }}" {{ request('browser') == $br ? 'selected' : '' }}>{{ $br }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1" style="border-radius: 10px;">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                <a href="{{ route('admin.marketing.visitors') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 10px;">Reset</a>
            </div>
        </form>
    </div>

    {{-- Visitors Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 18px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="font-size: 0.75rem;">IP Address</th>
                            <th style="font-size: 0.75rem;">User</th>
                            <th style="font-size: 0.75rem;">Sumber</th>
                            <th style="font-size: 0.75rem;">Device</th>
                            <th style="font-size: 0.75rem;">Browser / OS</th>
                            <th style="font-size: 0.75rem;">Pages</th>
                            <th style="font-size: 0.75rem;">Landing Page</th>
                            <th class="pe-4" style="font-size: 0.75rem;">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visitors as $visitor)
                        <tr class="visitor-row">
                            <td class="ps-4">
                                <span class="fw-bold" style="font-size: 0.82rem;">{{ $visitor->ip_address ?? '-' }}</span>
                            </td>
                            <td>
                                @if($visitor->user)
                                    <span class="fw-semibold" style="font-size: 0.82rem;">{{ $visitor->user->name }}</span>
                                @else
                                    <span class="text-muted" style="font-size: 0.8rem;">Guest</span>
                                @endif
                            </td>
                            <td>
                                <span class="source-badge {{ $visitor->referrer_source }}">{{ ucfirst($visitor->referrer_source) }}</span>
                            </td>
                            <td>
                                <span class="device-badge {{ $visitor->device_type }}">
                                    <i class="bi bi-{{ $visitor->device_type === 'mobile' ? 'phone' : ($visitor->device_type === 'tablet' ? 'tablet' : 'laptop') }} me-1"></i>
                                    {{ ucfirst($visitor->device_type) }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 0.8rem;" class="fw-medium">{{ $visitor->browser ?? '-' }}</div>
                                <div style="font-size: 0.7rem;" class="text-muted">{{ $visitor->os ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fw-bold" style="font-size: 0.7rem;">{{ $visitor->total_page_views }}</span>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 150px; font-size: 0.78rem;">
                                    /{{ $visitor->landing_page ?? '-' }}
                                </span>
                            </td>
                            <td class="pe-4">
                                <div style="font-size: 0.78rem;" class="fw-medium">{{ $visitor->first_visit_at?->format('d M Y') }}</div>
                                <div style="font-size: 0.68rem;" class="text-muted">{{ $visitor->first_visit_at?->format('H:i') }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-eye-slash display-4 d-block mb-3 opacity-25"></i>
                                <p class="small">Belum ada data pengunjung dalam periode ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $visitors->links() }}
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.flatpickr', { dateFormat: 'Y-m-d' });
});
</script>
@endpush
