@extends('admin.layouts.app')
@section('title', __('admin.menu.orders'))
@section('page-title', __('admin.menu.orders'))

@section('content')
    <div class="d-flex align-items-center mb-4">
        <div class="d-flex flex-wrap gap-3 align-items-center mb-4">
            <div class="d-flex gap-2">
                <select id="filter_status" class="form-select form-select-sm" style="width:180px;border-radius:8px">
                    <option value="">Semua Status</option>
                    @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'expired'] as $s)
                        <option value="{{ $s }}">{{ __('order.status_' . $s) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="card table-card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 px-4 bg-transparent">
            <h6 class="mb-0 fw-bold">Daftar Pesanan</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="ordersTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No. Pesanan</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th class="pe-4 text-end">Aksi</th>
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
            let table = $('#ordersTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                order: [[5, 'desc']], // Default order by date
                ajax: {
                    url: "{{ route('admin.orders.index') }}",
                    data: function (d) {
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'order_number_link', name: 'order_number', className: 'ps-4' },
                    { data: 'customer_name_label', name: 'customer_name' },
                    { data: 'total_format', name: 'total' },
                    { data: 'payment_status_badge', name: 'payment_status' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'date_format', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'pe-4 text-end' }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                },
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                }
            });

            $('#filter_status').on('change', function () {
                table.ajax.reload();
            });
        });
    </script>
@endsection