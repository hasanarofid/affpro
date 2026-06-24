@extends('admin.layouts.app')
@section('title', 'Voucher')
@section('page-title', 'Kelola Voucher')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <h5 class="mb-0 fw-bold me-auto">Manajemen Voucher</h5>
        <a href="{{ route('admin.vouchers.create') }}" class="btn btn-sm btn-primary" style="border-radius:8px">
            <i class="bi bi-plus-lg me-1"></i> Tambah Voucher
        </a>
    </div>

    <div class="card table-card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 px-4 bg-transparent d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">Daftar Voucher</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="vouchersTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="5%">No</th>
                            <th>Kode Voucher</th>
                            <th>Nilai Diskon</th>
                            <th>Masa Berlaku</th>
                            <th>Penggunaan</th>
                            <th>Status</th>
                            <th class="pe-4 text-end" width="15%">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#vouchersTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.vouchers.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4 text-muted small' },
                    { data: 'code_badge', name: 'code' },
                    { data: 'discount_info', name: 'value' },
                    { data: 'validity', name: 'starts_at', orderable: false, searchable: false },
                    { data: 'usage_info', name: 'used_count', searchable: false },
                    { data: 'status_badge', name: 'is_active', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'pe-4 text-end' }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                },
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                }
            });
        });
    </script>
@endsection