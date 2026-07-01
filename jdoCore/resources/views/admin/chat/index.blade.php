@extends('admin.layouts.app')
@section('title', 'Pesan Masuk')
@section('page-title', 'Pesan Pelanggan')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <h5 class="mb-0 fw-bold me-auto">Pesan Pelanggan</h5>
    </div>

    <div class="card table-card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 px-4 bg-transparent d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">Daftar Obrolan</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="chatTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Pelanggan</th>
                            <th>Pesan Terakhir</th>
                            <th>Waktu</th>
                            <th class="text-center pe-4" width="15%">Aksi</th>
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
            $('#chatTable').DataTable({
                stateSave: true,
                processing: true,
                serverSide: true,
                responsive: true,
                order: [[2, 'desc']], // Assuming data is ordered by last update
                ajax: "{{ route('admin.chat.index') }}",
                columns: [
                    { data: 'user_info', name: 'user.name', className: 'ps-4' },
                    { data: 'last_message', name: 'last_message_at', orderable: false, searchable: false },
                    { data: 'time', name: 'last_message_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center pe-4' }
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