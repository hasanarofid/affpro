@extends('admin.layouts.app')
@section('title', 'Laporan Laba Rugi')
@section('page-title', 'Laporan Laba Rugi')

@section('content')
    <div class="row g-4">
        <!-- Filter Section -->
        <div class="col-12">
            <div class="card border-0" style="border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.04)">
                <div class="card-body p-4">
                    <form action="{{ route('admin.reports.profit_loss') }}" method="GET" class="row g-3 align-items-end">
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
                                <i class="bi bi-search me-1"></i> Lihat Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-12 d-flex justify-content-end gap-2">
            <form action="{{ route('admin.reports.profit_loss') }}" method="GET" target="_blank" class="d-inline">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <button type="submit" name="export" value="print" class="btn btn-outline-secondary"
                    style="border-radius:8px">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </form>
            <form action="{{ route('admin.reports.profit_loss') }}" method="GET" class="d-inline">
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

        <!-- Report Table Display -->
        <div class="col-12">
            <div class="card border-0" style="border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.04)">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-1">Laporan Laba Rugi</h5>
                    <p class="text-muted small">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} <br>(Hanya menghitung pesanan yang
                        *Selesai*/Delivered)</p>
                </div>
                <div class="card-body p-0 pb-3">
                    <div class="table-responsive px-4">
                        <table class="table table-hover align-middle mb-0">
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
                                    <td class="fw-bold" style="font-size:1.25rem">TOTAL PENDAPATAN BERSIH</td>
                                    <td class="text-end fw-bold text-primary" style="font-size:1.4rem">Rp
                                        {{ number_format($data->total_sales_revenue ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection