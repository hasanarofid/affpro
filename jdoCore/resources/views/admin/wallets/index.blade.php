@extends('admin.layouts.app')
@section('title', 'Manajemen Saldo Pelanggan')
@section('page-title', 'Saldo Pelanggan')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <h5 class="mb-0 fw-bold me-auto">Manajemen Saldo Pelanggan</h5>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white" style="border-radius: 14px; border:none">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white bg-opacity-25 rounded p-2 me-3">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-white-50">Total Saldo Mengendap</h6>
                            <h4 class="mb-0 fw-bold">Rp {{ number_format($totalBalance, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card table-card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Daftar Pelanggan</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="walletsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Pelanggan</th>
                            <th>Kontak</th>
                            <th>Saldo Saat Ini</th>
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
            $('#walletsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.wallets.index') }}",
                columns: [
                    { data: 'user_info', name: 'name', className: 'ps-4' },
                    { data: 'contact', name: 'email' },
                    { data: 'balance', name: 'wallet.balance', searchable: false },
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