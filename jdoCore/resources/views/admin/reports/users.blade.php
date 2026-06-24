@extends('admin.layouts.app')
@section('title', 'Laporan Transaksi User')
@section('page-title', 'Laporan Transaksi User')

@section('content')
    <div class="row g-4">
        <!-- Filter Section -->
        <div class="col-12">
            <div class="card border-0" style="border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,.04)">
                <div class="card-body p-4">
                    <form action="{{ route('admin.reports.users') }}" method="GET" class="row g-3 align-items-end">
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
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Cari Nama/Email Pengguna</label>
                            <select name="user_id" class="form-select select2-ajax" data-placeholder="Ketik nama atau id..."
                                data-url="{{ route('admin.reports.searchUsers') }}">
                                <option value=""></option>
                                @if(isset($selectedUser))
                                    <option value="{{ $selectedUser->id }}" selected>{{ $selectedUser->name }}
                                        ({{ $selectedUser->email }})</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
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
            <form action="{{ route('admin.reports.users') }}" method="GET" target="_blank" class="d-inline">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <input type="hidden" name="user_id" value="{{ $userId }}">
                <button type="submit" name="export" value="print" class="btn btn-outline-secondary"
                    style="border-radius:8px">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </form>
            <form action="{{ route('admin.reports.users') }}" method="GET" class="d-inline">
                <input type="hidden" name="start_date" value="{{ $startDate }}">
                <input type="hidden" name="end_date" value="{{ $endDate }}">
                <input type="hidden" name="user_id" value="{{ $userId }}">

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
                    <h5 class="fw-bold mb-1">Laporan Transaksi per User</h5>
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
                                            periode/filter ini.</td>
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