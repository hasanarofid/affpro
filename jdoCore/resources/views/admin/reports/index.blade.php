@extends('admin.layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
    <div class="row g-4">
        <!-- Filter Section -->
        <div class="col-12">
            <div class="card border-0" style="border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.04)">
                <div class="card-body p-4">
                    <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Jenis Laporan</label>
                            <select name="type" class="form-select text-capitalize">
                                <option value="transactions" {{ $type === 'transactions' ? 'selected' : '' }}>Transaksi All
                                </option>
                                <option value="users" {{ $type === 'users' ? 'selected' : '' }}>Transaksi per User</option>
                                <option value="profit_loss" {{ $type === 'profit_loss' ? 'selected' : '' }}>Laba Rugi</option>
                                <option value="products" {{ $type === 'products' ? 'selected' : '' }}>Penjualan Produk
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Mulai Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}"
                                max="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}"
                                max="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100" style="border-radius:8px">
                                <i class="bi bi-search me-1"></i> Lihat
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-12 d-flex justify-content-end gap-2">
            <form action="{{ route('admin.reports.index') }}" method="GET" target="_blank" class="d-inline">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <button type="submit" name="export" value="print" class="btn btn-outline-secondary"
                    style="border-radius:8px">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </form>
            <form action="{{ route('admin.reports.index') }}" method="GET" class="d-inline">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">

                <button type="submit" name="export" value="pdf" class="btn btn-outline-danger" style="border-radius:8px">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                </button>
                <button type="submit" name="export" value="excel" class="btn btn-outline-success" style="border-radius:8px">
                    <i class="bi bi-file-earmark-excel me-1"></i> Download Excel
                </button>
            </form>
        </div>

        <div class="col-12">
            <div class="card table-card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-1">
                        @if($type === 'transactions') Laporan Semua Transaksi
                        @elseif($type === 'users') Laporan Transaksi per User
                        @elseif($type === 'profit_loss') Laporan Laba Rugi
                        @elseif($type === 'products') Laporan Penjualan Produk (Terlaris)
                        @endif
                    </h6>
                    <p class="text-muted small mb-0">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive p-3">
                        <table class="table table-hover align-middle mb-0 w-100" id="{{ $type !== 'profit_loss' ? 'reportTable' : '' }}">
                            @if($type === 'transactions')
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px">No</th>
                                        <th>Tanggal</th>
                                        <th>Nomor Order</th>
                                        <th>Pelanggan</th>
                                        <th>Status</th>
                                        <th class="text-end">Total (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $i => $row)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $row->id) }}"
                                                    class="fw-medium text-decoration-none">
                                                    {{ $row->order_number }}
                                                </a>
                                            </td>
                                            <td>{{ $row->customer_name }}</td>
                                            <td>
                                                @php
                                                    $badges = [
                                                        'pending' => 'bg-warning',
                                                        'confirmed' => 'bg-info',
                                                        'processing' => 'bg-primary',
                                                        'shipped' => 'bg-secondary',
                                                        'delivered' => 'bg-success',
                                                        'cancelled' => 'bg-danger',
                                                        'expired' => 'bg-dark'
                                                    ];
                                                    $bg = $badges[$row->status] ?? 'bg-secondary';
                                                @endphp
                                                <span class="badge {{ $bg }}">{{ ucfirst($row->status) }}</span>
                                            </td>
                                            <td class="text-end fw-medium">{{ number_format($row->total, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data transaksi pada
                                                periode ini.</td>
                                        </tr>
                                    @endforelse
                                    @if($data->isNotEmpty())
                                        <tr class="fw-bold table-light">
                                            <td colspan="5" class="text-end">Total Pendapatan:</td>
                                            <td class="text-end text-success">Rp
                                                {{ number_format($data->sum('total'), 0, ',', '.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>

                            @elseif($type === 'users')
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px">No</th>
                                        <th>Pelanggan</th>
                                        <th>Email</th>
                                        <th class="text-center">Total Transaksi</th>
                                        <th class="text-end">Total Nilai (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $i => $row)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td class="fw-medium">{{ $row->user->name ?? '-' }}</td>
                                            <td>{{ $row->user->email ?? '-' }}</td>
                                            <td class="text-center">{{ $row->total_orders }}</td>
                                            <td class="text-end fw-medium">{{ number_format($row->sum_total, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Tidak ada data performa user pada
                                                periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            @elseif($type === 'profit_loss')
                                <tbody>
                                    <tr>
                                        <td class="fw-medium text-muted w-50" style="font-size: 1.1rem">Total Pesanan
                                            Selesai/Dibayar</td>
                                        <td class="text-end fw-bold" style="font-size: 1.1rem">{{ $data->total_orders ?? 0 }}
                                            Transaksi</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium text-muted">Total Subtotal Penjualan</td>
                                        <td class="text-end fw-medium">Rp
                                            {{ number_format($data->total_subtotal ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium text-muted">Total Diskon Diberikan</td>
                                        <td class="text-end fw-medium text-danger">-Rp
                                            {{ number_format($data->total_discount ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium text-muted">Total Pendapatan Ongkir</td>
                                        <td class="text-end fw-medium text-success">+Rp
                                            {{ number_format($data->total_shipping ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="fw-bold" style="font-size:1.25rem">TOTAL PENDAPATAN</td>
                                        <td class="text-end fw-bold text-primary" style="font-size:1.4rem">Rp
                                            {{ number_format($data->total_sales_revenue ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>

                            @elseif($type === 'products')
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px">No</th>
                                        <th>Nama Produk</th>
                                        <th>Varian</th>
                                        <th class="text-center">Terjual (Pcs)</th>
                                        <th class="text-end">Total Pendapatan (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $i => $row)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td class="fw-medium">{{ $row->product_name }}</td>
                                            <td>{{ $row->variant_label ?? '-' }}</td>
                                            <td class="text-center fw-bold text-primary">{{ $row->total_qty }}</td>
                                            <td class="text-end fw-medium">{{ number_format($row->total_revenue, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada produk yang terjual pada
                                                periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection

@if($type !== 'profit_loss')
@section('scripts')
<script>
    $(document).ready(function() {
        $('#reportTable').DataTable({
                stateSave: true,
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            },
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
            }
        });
    });
</script>
@endsection
@endif