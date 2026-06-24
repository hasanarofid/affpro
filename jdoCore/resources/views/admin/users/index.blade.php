@extends('admin.layouts.app')
@section('title', 'Kelola Pengguna Website')
@section('page-title', 'Kelola Pengguna Website')

@section('content')
    <div class="card table-card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 px-4 bg-transparent">
            <h6 class="mb-0 fw-bold">Daftar Pengguna</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="usersTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Bergabung</th>
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
            $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.users.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4 text-muted small' },
                    { data: 'name_val', name: 'name' },
                    { data: 'email', name: 'email', className: 'text-muted' },
                    { data: 'role_badge', name: 'roles.name', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'is_active', orderable: false, searchable: false },
                    { data: 'joined_date', name: 'created_at' }
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