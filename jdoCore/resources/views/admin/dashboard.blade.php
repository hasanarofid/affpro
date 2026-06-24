@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', __('general.dashboard'))

@section('content')
    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        @php
            $stats = [
                ['label' => __('admin.stats.today_revenue'), 'value' => 'Rp ' . number_format($todayRevenue ?? 0, 0, ',', '.'), 'icon' => 'bi-currency-dollar', 'color' => 'primary'],
                ['label' => __('admin.stats.today_orders'), 'value' => $todayOrders ?? 0, 'icon' => 'bi-cart-check', 'color' => 'success'],
                ['label' => __('admin.stats.pending_orders'), 'value' => $pendingOrders ?? 0, 'icon' => 'bi-clock-history', 'color' => 'warning'],
                ['label' => __('admin.stats.low_stock'), 'value' => $lowStockCount ?? 0, 'icon' => 'bi-exclamation-triangle', 'color' => 'danger'],
            ];
        @endphp
        @foreach($stats as $stat)
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-body p-4 d-flex align-items-center gap-4">
                    <div class="stat-icon bg-{{ $stat['color'] }} bg-opacity-10 text-{{ $stat['color'] }}">
                        <i class="bi {{ $stat['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-medium small mb-1">{{ $stat['label'] }}</div>
                        <div class="fw-bold fs-4 ls-tight">{{ $stat['value'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Overview Row -->
    <div class="row g-4 mb-4">
        @php
            $overview = [
                ['label' => __('admin.stats.total_products'), 'value' => $totalProducts ?? 0, 'icon' => 'bi-box-seam', 'color' => 'primary'],
                ['label' => __('admin.stats.total_orders'), 'value' => $totalOrders ?? 0, 'icon' => 'bi-bag-check', 'color' => 'secondary'],
                ['label' => __('admin.stats.total_customers'), 'value' => $totalCustomers ?? 0, 'icon' => 'bi-people', 'color' => 'info'],
                ['label' => __('admin.stats.total_revenue'), 'value' => 'Rp ' . number_format($totalRevenue ?? 0, 0, ',', '.'), 'icon' => 'bi-wallet2', 'color' => 'success'],
            ];
        @endphp
        @foreach($overview as $item)
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100 shadow-sm border-0">
                <div class="card-body p-4 d-flex align-items-center gap-4">
                    <div class="stat-icon bg-{{ $item['color'] }} bg-opacity-10 text-{{ $item['color'] }}" style="width: 52px; height: 52px; font-size: 1.4rem;">
                        <i class="bi {{ $item['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-muted fw-medium small mb-1">{{ $item['label'] }}</div>
                        <div class="fw-bold fs-5 ls-tight">{{ $item['value'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- AI Insights -->
    @if(isset($aiInsight))
        <div class="card border-0 mb-4 overflow-hidden" style="border-radius: 24px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(124, 58, 237, 0.05) 100%); border: 1px solid var(--border-color) !important;">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="bg-white dark-bg-gray-800 p-3 rounded-xl shadow-sm d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; border-radius: 16px;">
                            <i class="bi bi-stars text-primary fs-3"></i>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <h6 class="fw-bold mb-0">Gemini AI Business Insight</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3" style="font-size: 0.65rem;">BETA</span>
                        </div>
                        <div class="text-secondary" style="font-size: 0.95rem; line-height: 1.7; font-weight: 450;">
                            {!! nl2br(e($aiInsight)) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Sales Chart 30 Days -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2 text-primary"></i>Penjualan 30 Hari Terakhir</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3" style="font-size: 0.7rem;">
                        <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::now()->subDays(29)->format('d M') }} - {{ \Carbon\Carbon::now()->format('d M Y') }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <canvas id="salesChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <!-- Pie Chart Top Products -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-primary"></i>Produk Terlaris</h6>
                </div>
                <div class="card-body p-4 d-flex align-items-center justify-content-center">
                    @if(count($pieData ?? []) > 0)
                        <canvas id="topProductsChart" height="280"></canvas>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-pie-chart display-4 d-block mb-3 opacity-25"></i>
                            <p class="small">Belum ada data penjualan</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card table-card h-100 border-0 shadow-sm">
                <div class="card-header border-bottom py-3 px-4 bg-transparent d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">Pesanan Terbaru</h6>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold text-primary" style="font-size: 0.75rem;">
                        Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No. Pesanan</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="pe-4">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders ?? [] as $order)
                                    <tr>
                                        <td class="ps-4">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none fw-bold text-primary">
                                                {{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $order->customer_name }}</div>
                                            <div class="text-muted small" style="font-size: 0.7rem;">Order #{{ $order->id }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning', 
                                                    'confirmed' => 'info', 
                                                    'processing' => 'primary', 
                                                    'shipped' => 'secondary', 
                                                    'delivered' => 'success', 
                                                    'cancelled' => 'danger', 
                                                    'expired' => 'dark'
                                                ];
                                                $color = $statusColors[$order->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-status bg-{{ $color }}">
                                                {{ __('order.status_' . $order->status) }}
                                            </span>
                                        </td>
                                        <td class="pe-4">
                                            <div class="text-muted small fw-medium">
                                                <i class="bi bi-calendar3 me-1 opacity-50"></i>
                                                {{ $order->created_at->format('d M Y') }}
                                            </div>
                                            <div class="text-muted small opacity-75" style="font-size: 0.7rem;">
                                                <i class="bi bi-clock me-1 opacity-50"></i>
                                                {{ $order->created_at->format('H:i') }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="bi bi-inbox display-4 d-block mb-3 opacity-25"></i>
                                            {{ __('general.no_data') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best Sellers -->
        <div class="col-lg-4">
            <div class="card table-card h-100 border-0 shadow-sm">
                <div class="card-header border-bottom py-3 px-4 bg-transparent">
                    <h6 class="mb-0 fw-bold">Produk Terlaris</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush border-0">
                        @forelse($bestSellers ?? [] as $product)
                            <li class="list-group-item d-flex align-items-center py-3 px-4 bg-transparent border-bottom" style="border-color: var(--border-color) !important;">
                                <div class="me-3 position-relative">
                                    @if($product->primaryImage)
                                        <img src="{{ asset($product->primaryImage->path) }}" class="rounded shadow-sm" width="52" height="52"
                                            style="object-fit:cover; border-radius: 12px;">
                                    @else
                                        <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                                            style="width:52px;height:52px; background: var(--sidebar-hover); border-radius: 12px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                    <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-primary border border-white" style="font-size: 0.6rem; padding: 0.35em 0.65em;">
                                        {{ $loop->iteration }}
                                    </span>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="fw-bold text-truncate mb-1" style="font-size: 0.9rem;">{{ Str::limit($product->name, 25) }}</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill fw-bold" style="font-size:0.65rem">
                                            <i class="bi bi-graph-up-arrow me-1"></i>{{ (int) ($product->total_sold ?? 0) }} terjual
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end ms-2">
                                    <div class="fw-bold text-primary" style="font-size: 0.95rem;">Rp {{ number_format($product->base_price, 0, ',', '.') }}</div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-5 bg-transparent border-0">
                                <i class="bi bi-box-seam display-4 d-block mb-3 opacity-25"></i>
                                {{ __('general.no_data') }}
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Sales Chart (Line) ---
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        const gradient = salesCtx.getContext('2d');
        const gradientFill = gradient.createLinearGradient(0, 0, 0, 280);
        gradientFill.addColorStop(0, 'rgba(79, 70, 229, 0.25)');
        gradientFill.addColorStop(1, 'rgba(79, 70, 229, 0.01)');

        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: @json($chartRevenue),
                    borderColor: '#4F46E5',
                    backgroundColor: gradientFill,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#4F46E5',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 3,
                }, {
                    label: 'Jumlah Pesanan',
                    data: @json($chartOrders),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#10B981',
                    yAxisID: 'y1',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            borderRadius: 3,
                            useBorderRadius: true,
                            padding: 20,
                            font: { size: 12, family: "'Plus Jakarta Sans', sans-serif", weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        titleFont: { size: 13, family: "'Plus Jakarta Sans', sans-serif" },
                        bodyFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                        cornerRadius: 10,
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.datasetIndex === 0) {
                                    return 'Pendapatan: Rp ' + ctx.raw.toLocaleString('id-ID');
                                }
                                return 'Pesanan: ' + ctx.raw;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, family: "'Plus Jakarta Sans', sans-serif" },
                            maxTicksLimit: 10,
                            color: '#9CA3AF'
                        }
                    },
                    y: {
                        position: 'left',
                        grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                        ticks: {
                            font: { size: 10, family: "'Plus Jakarta Sans', sans-serif" },
                            color: '#9CA3AF',
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                return 'Rp ' + value;
                            }
                        }
                    },
                    y1: {
                        position: 'right',
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, family: "'Plus Jakarta Sans', sans-serif" },
                            color: '#9CA3AF',
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // --- Pie Chart (Top Products) ---
    const pieCtx = document.getElementById('topProductsChart');
    if (pieCtx) {
        const pieColors = ['#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981'];
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: @json($pieLabels),
                datasets: [{
                    data: @json($pieData),
                    backgroundColor: pieColors,
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            boxHeight: 10,
                            borderRadius: 3,
                            useBorderRadius: true,
                            padding: 12,
                            font: { size: 11, family: "'Plus Jakarta Sans', sans-serif", weight: '500' },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                                    const shortLabel = label.length > 15 ? label.substring(0, 15) + '...' : label;
                                    return {
                                        text: shortLabel + ' (' + value + ' pcs, ' + pct + '%)',
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].backgroundColor[i],
                                        index: i
                                    };
                                });
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        cornerRadius: 10,
                        titleFont: { size: 13, family: "'Plus Jakarta Sans', sans-serif" },
                        bodyFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                        callbacks: {
                            label: function(ctx) {
                                return ctx.label + ': ' + ctx.raw + ' terjual';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush