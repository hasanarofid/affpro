@extends('theme::account.layout')
@section('title', 'Pesanan Saya')

@section('account_content')
    <div class="card-body p-0">
        <div class="p-4 border-bottom">
            <h5 class="fw-bold mb-0"><i class="bi bi-bag-heart-fill me-2 text-primary"></i>Pesanan Saya</h5>
        </div>

        <div class="p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="orders-table">
                    <thead class="bg-light bg-opacity-50 text-muted small">
                        <tr>
                            <th class="py-3">Info Pesanan</th>
                            <th class="py-3">Produk</th>
                            <th class="py-3">Total Tagihan</th>
                            <th class="py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('account_js')
    <script>
        $(document).ready(function () {
            $('#orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('account.orders') }}",
                columns: [
                    { data: 'order_info', name: 'order_number' },
                    { data: 'items_summary', name: 'items.product_name', orderable: false },
                    { data: 'total_formatted', name: 'total' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-end' }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                pageLength: 10,
                order: [[0, 'desc']],
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-sm justify-content-end');
                }
            });
        });
    </script>
@endsection