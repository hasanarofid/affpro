@extends('admin.layouts.app')
@section('title', 'Laporan Penjualan Produk')
@section('page-title', 'Laporan Penjualan Produk')

@section('content')
    <div class="row g-4">
        <!-- Filter Section -->
        <div class="col-12">
            <div class="card border-0" style="border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.04)">
                <div class="card-body p-4">
                    <form action="{{ route('admin.reports.products') }}" method="GET" class="row g-3 align-items-end">
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
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold">Status Pesanan</label>
                            <select name="order_status" class="form-select text-capitalize">
                                <option value="all_except_cancelled" {{ $orderStatus === 'all_except_cancelled' ? 'selected' : '' }}>Semua Kecuali Batal</option>
                                <option value="active" {{ $orderStatus === 'active' ? 'selected' : '' }}>Pesanan Aktif
                                </option>
                                <option value="completed" {{ $orderStatus === 'completed' ? 'selected' : '' }}>Selesai /
                                    Dikirim</option>
                                <option value="cancelled" {{ $orderStatus === 'cancelled' ? 'selected' : '' }}>Dibatalkan
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Cari Nama Produk</label>
                            <select name="product_id" class="form-select select2-ajax"
                                data-placeholder="Ketik nama produk..."
                                data-url="{{ route('admin.reports.searchProducts') }}">
                                <option value=""></option>
                                    @if(isset($selectedProduct))
                                        <option value="{{ $selectedProduct->id }}" selected>{{ $selectedProduct->name }}</option>
                                    @endif
                            </select>
                        </div>
                        <div class="col-md-1 d-flex gap-2">
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
            <form action="{{ route('admin.reports.products') }}" method="GET" target="_blank" class="d-inline">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <input type="hidden" name="product_id" value="{{ $productId }}">
                <input type="hidden" name="order_status" value="{{ $orderStatus }}">
                <button type="submit" name="export" value="print" class="btn btn-outline-secondary"
                    style="border-radius:8px">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </form>
            <form action="{{ route('admin.reports.products') }}" method="GET" class="d-inline">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <input type="hidden" name="product_id" value="{{ $productId }}">
                <input type="hidden" name="order_status" value="{{ $orderStatus }}">

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
                    <h5 class="fw-bold mb-1">Laporan Penjualan Produk Terlaris</h5>
                    <p class="text-muted small">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </p>
                </div>
                <div class="card-body p-0 pb-3">
                    <div class="table-responsive px-4">
                        <table class="table table-hover align-middle mb-0">
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
                                            filter periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
@endsection

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('.select2-ajax').each(function () {
                    var url = $(this).data('url');
                    var placeholder = $(this).data('placeholder');
                    $(this).select2({
                        theme: 'bootstrap-5',
                        placeholder: placeholder,
                        allowClear: true,
                        ajax: {
                            url: url,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term // search term
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.results
                                };
                            },
                            cache: true
                        }
                    });
                });
            });
        </script>
    @endpush