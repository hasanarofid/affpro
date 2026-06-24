@extends('admin.layouts.app')
@section('title', 'Laporan Transaksi All')
@section('page-title', 'Laporan Transaksi All')

@section('content')
    <div class="row g-4">
        <!-- Filter Section -->
        <div class="col-12">
            <div class="card border-0" style="border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.04)">
                <div class="card-body p-4">
                    <form action="{{ route('admin.reports.transactions') }}" method="GET" class="row g-3 align-items-end">
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
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Status Pesanan</label>
                            <select name="status" class="form-select text-capitalize">
                                <option value="all">Semua Status</option>
                                @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'expired'] as $s)
                                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100" style="border-radius:8px">
                                <i class="bi bi-search me-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-12 d-flex justify-content-end gap-2">
            <form action="{{ route('admin.reports.transactions') }}" method="GET" target="_blank" class="d-inline">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <button type="submit" name="export" value="print" class="btn btn-outline-secondary"
                    style="border-radius:8px">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </form>
            <form action="{{ route('admin.reports.transactions') }}" method="GET" class="d-inline">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <input type="hidden" name="status" value="{{ $status }}">

                <button type="submit" name="export" value="pdf" class="btn btn-outline-danger" style="border-radius:8px">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                </button>
                <button type="submit" name="export" value="excel" class="btn btn-outline-success" style="border-radius:8px">
                    <i class="bi bi-file-earmark-excel me-1"></i> Download Excel
                </button>
            </form>
        </div>

        <!-- Report Table Display -->
        <div class="col-12">
            <div class="card border-0" style="border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.04)">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-1">Laporan Semua Transaksi</h5>
                    <p class="text-muted small">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                </div>
                <div class="card-body p-0 pb-3">
                    <div class="table-responsive px-4">
                        <table class="table table-hover align-middle mb-0">
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
                                                    'pending' => 'bg-warning text-dark',
                                                    'confirmed' => 'bg-info text-white',
                                                    'processing' => 'bg-primary text-white',
                                                    'shipped' => 'bg-secondary text-white',
                                                    'delivered' => 'bg-success text-white',
                                                    'cancelled' => 'bg-danger text-white',
                                                    'expired' => 'bg-dark text-white'
                                                ];
                                                $bg = $badges[$row->status] ?? 'bg-secondary text-white';
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
                                        <td colspan="5" class="text-end">Total Pendapatan (Sesuai Filter):</td>
                                        <td class="text-end text-success">Rp
                                            {{ number_format($data->sum('total'), 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection