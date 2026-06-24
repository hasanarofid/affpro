@extends('admin.layouts.app')
@section('title', __('admin.menu.products'))
@section('page-title', __('admin.menu.products'))

@section('content')
    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-4">
        <div class="d-flex gap-2">
            <select id="filter_category" class="form-select form-select-sm" style="width:180px;border-radius:8px">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select id="filter_status" class="form-select form-select-sm" style="width:150px;border-radius:8px">
                <option value="">Semua Status</option>
                <option value="1">Aktif</option>
                <option value="0">Draft</option>
            </select>
        </div>
        <div>
            <a href="{{ route('admin.products.import.create') }}" class="btn btn-sm btn-success me-2" style="border-radius:8px">
                <i class="bi bi-file-earmark-excel me-1"></i> Import BigSeller
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary" style="border-radius:8px">
                <i class="bi bi-plus-lg me-1"></i> Tambah Produk
            </a>
        </div>
    </div>

    <div class="card table-card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 px-4 bg-transparent d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">Daftar Produk</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="productsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width:80px">Foto</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th class="pe-4 text-end" style="width:120px">Aksi</th>
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
            let table = $('#productsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.products.index') }}",
                    data: function (d) {
                        d.category_id = $('#filter_category').val();
                        d.status = $('#filter_status').val();
                    }
                },
                columns: [
                    { data: 'image', name: 'products.id', orderable: true, searchable: false, className: 'ps-4' },
                    { data: 'name_info', name: 'name', orderable: true },
                    { data: 'category_name', name: 'category.name', orderable: false },
                    { data: 'base_price', name: 'base_price', orderable: true },
                    { data: 'stock', name: 'stock', orderable: true },
                    { data: 'is_active', name: 'is_active', orderable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'pe-4' }
                ],
                order: [[0, 'desc']], // default: latest first (by id)
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                },
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                }
            });

            $('#filter_category, #filter_status').on('change', function () {
                table.ajax.reload();
            });
        });
    </script>
@endsection