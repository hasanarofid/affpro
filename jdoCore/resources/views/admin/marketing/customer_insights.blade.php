@extends('admin.layouts.app')

@section('title', 'Insight Pelanggan')
@section('page-title', '👥 Insight Pelanggan')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .insight-stat {
        border-radius: 18px; padding: 24px; text-align: center;
        border: 1px solid var(--border-color);
    }
    .insight-stat .stat-value { font-size: 1.6rem; font-weight: 800; line-height: 1.2; }
    .segment-card {
        border-radius: 16px; padding: 20px; text-align: center;
        border: 1px solid var(--border-color); transition: all 0.3s;
    }
    .segment-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.05); }
    .change-badge {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700;
    }
    .change-badge.up { background: rgba(16,185,129,0.1); color: #059669; }
    .change-badge.down { background: rgba(239,68,68,0.1); color: #DC2626; }
    .change-badge.neutral { background: rgba(107,114,128,0.1); color: #6B7280; }
    .customer-rank {
        width: 28px; height: 28px; border-radius: 8px; display: flex;
        align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700;
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
            <a href="{{ route('admin.marketing.customer_insights', ['range' => $key]) }}" class="range-btn {{ $range['preset'] === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    {{-- KPI Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card insight-stat border-0 shadow-sm">
                <div class="stat-value text-primary">{{ number_format($totalPaidCustomers) }}</div>
                <div class="text-muted small mt-1 fw-medium">Total Pembeli</div>
                <span class="change-badge {{ $customersChange['direction'] }} mt-2">
                    <i class="bi bi-arrow-{{ $customersChange['direction'] === 'up' ? 'up' : ($customersChange['direction'] === 'down' ? 'down' : 'right') }}-short"></i>
                    {{ $customersChange['value'] }}%
                </span>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card insight-stat border-0 shadow-sm">
                <div class="stat-value text-success">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</div>
                <div class="text-muted small mt-1 fw-medium">Rata-rata Order</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card insight-stat border-0 shadow-sm">
                <div class="stat-value" style="color: #7C3AED;">{{ number_format($repeatBuyers) }}</div>
                <div class="text-muted small mt-1 fw-medium">Repeat Buyer</div>
                <div class="text-muted" style="font-size: 0.68rem;">Beli > 1 kali</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card insight-stat border-0 shadow-sm">
                <div class="stat-value text-warning">{{ number_format($oneTimeBuyers) }}</div>
                <div class="text-muted small mt-1 fw-medium">One-Time Buyer</div>
                <div class="text-muted" style="font-size: 0.68rem;">Beli 1 kali saja</div>
            </div>
        </div>
    </div>

    {{-- Customer Segments Visualization --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2" style="color: #7C3AED;"></i>Segmen Pelanggan</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="segmentChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-info"></i>Distribusi Pelanggan</h6>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-center gap-4">
                    {{-- Repeat vs One-time --}}
                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold small">Repeat Buyer</span>
                            <span class="fw-bold small" style="color: #7C3AED;">{{ $totalPaidCustomers > 0 ? round($repeatBuyers / $totalPaidCustomers * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ $totalPaidCustomers > 0 ? round($repeatBuyers / $totalPaidCustomers * 100, 1) : 0 }}%; background: #7C3AED; border-radius: 5px;"></div>
                        </div>
                    </div>
                    {{-- New vs Returning --}}
                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold small">Pelanggan Baru</span>
                            <span class="fw-bold small text-success">{{ $totalPaidCustomers > 0 ? round($newBuyers / $totalPaidCustomers * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalPaidCustomers > 0 ? round($newBuyers / $totalPaidCustomers * 100, 1) : 0 }}%; border-radius: 5px;"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold small">Pelanggan Kembali</span>
                            <span class="fw-bold small text-primary">{{ $totalPaidCustomers > 0 ? round($returningBuyers / $totalPaidCustomers * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $totalPaidCustomers > 0 ? round($returningBuyers / $totalPaidCustomers * 100, 1) : 0 }}%; border-radius: 5px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Customers Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h6 class="mb-0 fw-bold"><i class="bi bi-trophy me-2 text-warning"></i>Top Pelanggan berdasarkan Spending</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="font-size: 0.75rem;">#</th>
                            <th style="font-size: 0.75rem;">Pelanggan</th>
                            <th style="font-size: 0.75rem;">Email</th>
                            <th style="font-size: 0.75rem;">Total Order</th>
                            <th style="font-size: 0.75rem;">Total Spent</th>
                            <th style="font-size: 0.75rem;">Order Terakhir</th>
                            <th class="pe-4" style="font-size: 0.75rem;">Tipe</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topCustomers as $i => $row)
                        @php $rank = $topCustomers->firstItem() + $i @endphp
                        <tr>
                            <td class="ps-4">
                                @if($rank <= 3)
                                    <div class="customer-rank" style="background: {{ $rank == 1 ? '#FFD700' : ($rank == 2 ? '#C0C0C0' : '#CD7F32') }}20; color: {{ $rank == 1 ? '#B8860B' : ($rank == 2 ? '#71706E' : '#8B4513') }};">
                                        {{ $rank }}
                                    </div>
                                @else
                                    <span class="text-muted fw-bold">{{ $rank }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold" style="font-size: 0.85rem;">{{ $row->user->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="text-muted" style="font-size: 0.8rem;">{{ $row->user->email ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fw-bold" style="font-size: 0.72rem;">{{ $row->total_orders }} order</span>
                            </td>
                            <td>
                                <span class="fw-bold text-success" style="font-size: 0.88rem;">Rp {{ number_format($row->total_spent, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="text-muted fw-medium" style="font-size: 0.8rem;">{{ \Carbon\Carbon::parse($row->last_order)->format('d M Y') }}</span>
                            </td>
                            <td class="pe-4">
                                @if($row->total_orders > 1)
                                    <span class="badge rounded-pill fw-bold" style="background: rgba(124,58,237,0.1); color: #7C3AED; font-size: 0.68rem;">Repeat</span>
                                @else
                                    <span class="badge rounded-pill fw-bold" style="background: rgba(245,158,11,0.1); color: #D97706; font-size: 0.68rem;">One-time</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-people display-4 d-block mb-3 opacity-25"></i>
                                <p class="small">Belum ada data pelanggan dalam periode ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $topCustomers->links() }}
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.flatpickr', { dateFormat: 'Y-m-d' });

    const segCtx = document.getElementById('segmentChart');
    if (segCtx) {
        new Chart(segCtx, {
            type: 'doughnut',
            data: {
                labels: ['Repeat Buyer', 'One-Time Buyer', 'Pelanggan Baru'],
                datasets: [{
                    data: [{{ $repeatBuyers }}, {{ $oneTimeBuyers }}, {{ $newBuyers }}],
                    backgroundColor: ['#7C3AED', '#F59E0B', '#10B981'],
                    borderWidth: 3, borderColor: '#fff', hoverOffset: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, boxHeight: 10, borderRadius: 3, useBorderRadius: true, padding: 12, font: { size: 11, weight: '500' } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
