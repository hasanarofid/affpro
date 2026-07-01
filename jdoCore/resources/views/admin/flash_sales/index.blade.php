@extends('admin.layouts.app')
@section('title', __('admin.menu.flash_sale'))
@section('page-title', __('admin.menu.flash_sale'))

@section('content')
    <div class="d-flex align-items-center mb-4">
        <h5 class="mb-0 fw-bold me-auto">Manajemen Flash Sale</h5>
        <a href="{{ route('admin.flash-sales.create') }}" class="btn btn-sm btn-primary" style="border-radius:8px">
            <i class="bi bi-plus-lg me-1"></i> Buat Flash Sale
        </a>
    </div>

    <div class="card table-card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 px-4 bg-transparent d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">Daftar Flash Sale</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="flashSalesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="5%">No</th>
                            <th width="25%">Judul Event</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Selesai</th>
                            <th class="text-center">Total Produk</th>
                            <th class="text-center">Status</th>
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
            $('#flashSalesTable').DataTable({
                stateSave: true,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.flash-sales.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4 text-muted small' },
                    { data: 'title_format', name: 'title' },
                    { data: 'start_time_format', name: 'start_time' },
                    { data: 'end_time_format', name: 'end_time' },
                    { data: 'products_count_format', name: 'products_count', searchable: false },
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