@extends('admin.layouts.app')
@section('title', 'Halaman')
@section('page-title', 'Kelola Halaman')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <h5 class="mb-0 fw-bold me-auto">Manajemen Halaman</h5>
        <a href="{{ route('admin.pages.create') }}" class="btn btn-sm btn-primary" style="border-radius:8px">
            <i class="bi bi-plus-lg me-1"></i> Buat Halaman
        </a>
    </div>

    <div class="card table-card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 px-4 bg-transparent d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">Daftar Halaman</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="pagesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Judul</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Dibuat</th>
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
            $('#pagesTable').DataTable({
                stateSave: true,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.pages.index') }}",
                columns: [
                    { data: 'title_val', name: 'title', className: 'ps-4' },
                    { data: 'slug_val', name: 'slug' },
                    { data: 'status_badge', name: 'is_active', orderable: false, searchable: false },
                    { data: 'created_date', name: 'created_at' },
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