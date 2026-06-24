@extends('admin.layouts.app')
@section('title', 'Validasi Top Up & Penarikan')
@section('page-title', 'Validasi Saldo Pelanggan')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <h5 class="mb-0 fw-bold me-auto">Validasi Saldo Pelanggan</h5>
    </div>

    <div class="card table-card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="bi bi-bank me-2 text-primary"></i>Daftar Antrean Saldo</h6>
            <div class="d-flex gap-2">
                <select id="filterType" class="form-select form-select-sm" style="border-radius:8px">
                    <option value="">-- Semua Tipe --</option>
                    <option value="deposit">Top Up</option>
                    <option value="withdrawal">Penarikan</option>
                </select>
                <select id="filterStatus" class="form-select form-select-sm" style="border-radius:8px">
                    <option value="">-- Semua Status --</option>
                    <option value="pending">Belum Diproses</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Ditolak / Batal</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover align-middle mb-0 w-100" id="requestsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Ref & Tipe</th>
                            <th>Nama Pelanggan</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Bukti Transfer -->
    <div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Bukti Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img id="proofImage" src="" class="img-fluid rounded-4 shadow-sm" alt="Bukti Transfer">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <a id="proofDownload" href="" target="_blank" class="btn btn-primary rounded-pill px-4"
                        download>Download</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function showProof(url) {
            $('#proofImage').attr('src', url);
            $('#proofDownload').attr('href', url);
            new bootstrap.Modal(document.getElementById('proofModal')).show();
        }

        $(document).ready(function () {
            let table = $('#requestsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.wallets.requests') }}",
                    data: function (d) {
                        d.type = $('#filterType').val();
                        d.status = $('#filterStatus').val();
                    }
                },
                columns: [
                    { data: 'date', name: 'created_at', className: 'ps-4' },
                    { data: 'ref_type', name: 'reference_number' },
                    { data: 'customer', name: 'wallet.user.name', orderable: false },
                    { data: 'amount', name: 'amount' },
                    { data: 'status_badge', name: 'status', orderable: false, className: 'text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'pe-4 text-center' }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                },
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                }
            });

            $('#filterType, #filterStatus').on('change', function () {
                table.draw();
            });
        });
    </script>
@endsection