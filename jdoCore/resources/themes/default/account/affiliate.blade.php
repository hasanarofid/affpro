@extends('theme::account.layout')
@section('title', 'Program Affiliate')

@section('account_content')
    <div class="card-body p-0">
        <!-- Affiliate Header - Adjusted Height -->
        <div class="px-4 py-5 text-white"
            style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); position: relative; overflow: hidden; min-height: 180px; display: flex; align-items: center; border-radius: 24px 24px 0 0;">
            <div
                style="position: absolute; top: -10px; right: -10px; font-size: 8rem; opacity: 0.05; transform: rotate(15deg);">
                <i class="bi bi-people"></i>
            </div>

            <div class="container-fluid position-relative" style="z-index: 1;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="fw-bold mb-2">Program Affiliate</h3>
                        <p class="text-white-50 mb-0 small" style="max-width: 500px;">Bagikan kode Anda dan dapatkan komisi
                            dari setiap transaksi teman Anda.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-4 mt-md-0">
                        <div class="d-inline-block text-start bg-white bg-opacity-10 p-2 px-3 rounded-4 backdrop-blur"
                            style="border: 1px solid rgba(255,255,255,0.1);">
                            <p class="text-white-50 small mb-0 fw-medium text-uppercase"
                                style="letter-spacing: 1px; font-size: 0.65rem;">Total Komisi</p>
                            <h4 class="fw-bold mb-0 text-white">Rp {{ number_format($totalCommission, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-4">
            <div class="row g-4 mb-4">
                <!-- Referral Code Premium Display - Fixed Proportions -->
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm overflow-hidden"
                        style="border-radius: 16px; background: #f8fafc; border: 1px solid #eef2f6 !important;">
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <div class="col-md-7 p-4 border-end border-dashed">
                                    <h6 class="fw-bold text-dark mb-3 small text-uppercase"
                                        style="letter-spacing: 0.5px; color: #64748b !important;">Link Referal Anda</h6>
                                    <div class="d-flex align-items-center flex-wrap gap-3">
                                        <div class="bg-white border-2 border-primary border-dashed p-2 px-4 text-center"
                                            style="border-radius: 12px; min-width: 140px;">
                                            <span class="fs-4 fw-bold text-primary tracking-widest"
                                                style="font-family: 'Courier New', Courier, monospace; letter-spacing: 4px;">{{ auth()->user()->referral_code }}</span>
                                        </div>
                                        <button class="btn btn-primary shadow-sm px-4 py-2 fw-bold"
                                            style="border-radius: 10px;" onclick="copyToClipboard('{{ $referralLink }}');"
                                            id="copyBtn">
                                            <i class="bi bi-link-45deg me-2"></i> Salin Link
                                        </button>
                                    </div>
                                    <div class="mt-3">
                                        <code class="text-muted small word-break-all">{{ $referralLink }}</code>
                                    </div>
                                    <p class="text-muted small mt-2 mb-0" style="font-size: 0.75rem;"><i
                                            class="bi bi-info-circle me-1"></i> Klik salin dan bagikan link lengkap di atas
                                        ke media sosial Anda.</p>
                                </div>
                                <div
                                    class="col-md-5 p-4 bg-primary bg-opacity-10 d-flex flex-column justify-content-center">
                                    <div class="d-flex align-items-start mb-2">
                                        <div class="bg-white rounded-circle p-1 me-2 shadow-sm border d-flex align-items-center justify-content-center"
                                            style="width:24px; height:24px;">
                                            <i class="bi bi-check-lg text-success small" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <p class="small text-dark mb-0 fw-semibold" style="font-size: 0.8rem;">Daftar
                                            menggunakan kode Anda</p>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <div class="bg-white rounded-circle p-1 me-2 shadow-sm border d-flex align-items-center justify-content-center"
                                            style="width:24px; height:24px;">
                                            <i class="bi bi-check-lg text-success small" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <p class="small text-dark mb-0 fw-semibold" style="font-size: 0.8rem;">Dapatkan
                                            komisi saldo otomatis</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3 mt-2">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill me-2 text-primary"></i>Teman Yang Terdaftar
                </h6>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-bold" style="font-size: 0.7rem;">
                    {{ auth()->user()->referals_count ?? auth()->user()->referrals()->count() }} User
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="referrals-table">
                    <thead class="bg-light bg-opacity-50 text-muted">
                        <tr>
                            <th class="py-3">Info Pengguna</th>
                            <th class="py-3">Tanggal Bergabung</th>
                            <th class="py-3 text-end">Status Akun</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            const btn = document.getElementById('copyBtn');
            const originalHtml = btn.innerHTML;

            navigator.clipboard.writeText(text).then(() => {
                btn.innerHTML = '<i class="bi bi-check2 me-2"></i> Berhasil!';
                btn.classList.replace('btn-primary', 'btn-success');

                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.replace('btn-success', 'btn-primary');
                }, 2000);
            }).catch(err => {
                const tempInput = document.createElement("input");
                tempInput.value = text;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);

                btn.innerHTML = '<i class="bi bi-check2 me-2"></i> Berhasil!';
                btn.classList.replace('btn-primary', 'btn-success');
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.replace('btn-success', 'btn-primary');
                }, 2000);
            });
        }
    </script>
@endsection

@section('account_js')
    <script>
        $(document).ready(function () {
            $('#referrals-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('account.affiliate') }}",
                columns: [
                    { data: 'user_info', name: 'name' },
                    { data: 'date', name: 'created_at' },
                    { data: 'status_badge', name: 'is_active' }
                ],
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                pageLength: 10,
                order: [[1, 'desc']],
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-sm justify-content-end');
                }
            });
        });
    </script>
@endsection