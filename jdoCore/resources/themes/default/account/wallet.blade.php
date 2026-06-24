@extends('theme::account.layout')
@section('title', 'Saldo Akun')

@section('account_content')
    <div class="card-body p-0">
        <!-- Premium Balance Header -->
        <div class="p-4 p-md-5 text-white"
            style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); position: relative; overflow: hidden; border-radius: 24px 24px 0 0;">
            <!-- Decorative bubbles -->
            <div
                style="position: absolute; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%; top: -50px; right: -20px;">
            </div>
            <div
                style="position: absolute; width: 80px; height: 80px; background: rgba(255,255,255,0.05); border-radius: 50%; bottom: 20px; right: 100px;">
            </div>

            <div class="row align-items-center position-relative" style="z-index: 1;">
                <div class="col-md-7">
                    <span
                        class="badge bg-white text-primary rounded-pill px-3 py-2 mb-3 fw-bold small text-uppercase shadow-sm"
                        style="letter-spacing: 1px;">
                        <i class="bi bi-shield-check me-1"></i> Dompet Terverifikasi
                    </span>
                    <p class="text-white-50 mb-1 fw-medium">Total Saldo Tersedia</p>
                    <h1 class="display-5 fw-bold mb-0">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</h1>
                </div>
                <div class="col-md-5 mt-4 mt-md-0 d-flex gap-2 justify-content-md-end flex-wrap">
                    <button
                        class="btn btn-warning text-dark fw-bold px-4 py-3 shadow-sm hover-up flex-grow-1 flex-md-grow-0"
                        style="border-radius:15px;" data-bs-toggle="modal" data-bs-target="#topupModal">
                        <i class="bi bi-plus-circle me-2"></i> Top Up Saldo
                    </button>
                    <button
                        class="btn btn-white bg-white text-primary fw-bold px-4 py-3 shadow-sm hover-up flex-grow-1 flex-md-grow-0"
                        style="border-radius:15px; border:none;" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                        <i class="bi bi-cash-stack me-2"></i> Tarik Saldo
                    </button>
                </div>
            </div>
        </div>

        <!-- Features/Info Row -->
        <div class="row g-0 border-bottom">
            <div class="col-4 border-end">
                <div class="p-3 text-center">
                    <i class="bi bi-lightning-fill text-warning mb-1 d-block fs-4"></i>
                    <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Proses Cepat</span>
                </div>
            </div>
            <div class="col-4 border-end">
                <div class="p-3 text-center">
                    <i class="bi bi-shield-lock-fill text-success mb-1 d-block fs-4"></i>
                    <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Aman 100%</span>
                </div>
            </div>
            <div class="col-4">
                <div class="p-3 text-center">
                    <i class="bi bi-headset text-info mb-1 d-block fs-4"></i>
                    <span class="small text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Support 24/7</span>
                </div>
            </div>
        </div>

        <div class="p-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Transaksi
                </h5>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm rounded-pill px-3 border" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px;">
                        <li><a class="dropdown-item small py-2" href="#">Semua Status</a></li>
                        <li><a class="dropdown-item small py-2" href="#">Berhasil</a></li>
                        <li><a class="dropdown-item small py-2" href="#">Diproses</a></li>
                    </ul>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="wallet-table">
                    <thead class="bg-light bg-opacity-50 text-muted small">
                        <tr>
                            <th class="py-3">Tanggal & Waktu</th>
                            <th class="py-3">Keterangan Transaksi</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal (Same as before) -->
    <div class="modal fade" id="withdrawModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px; border:none;">
                <form action="{{ route('account.wallet.withdraw') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h6 class="modal-title fw-bold">Tarik Saldo Ke Rekening Pribadi</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4">
                        @if($bankAccounts->isEmpty())
                            <div class="alert alert-warning border-0 small mb-0"
                                style="border-radius:10px; background-color: #fefce8; color: #a16207;">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>Anda belum menambahkan rekening bank. Silakan
                                tambahkan rekening terlebih dahulu di menu <strong>Data Rekening</strong>.
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Pilih Rekening Tujuan *</label>
                                <select name="bank_account_id" class="form-select w-100" required style="border-radius:10px;">
                                    <option value="">-- Pilih Rekening --</option>
                                    @foreach($bankAccounts as $bank)
                                        <option value="{{ $bank->id }}" {{ $bank->is_main ? 'selected' : '' }}>
                                            {{ $bank->bank_name }} - {{ $bank->account_number }} (a.n {{ $bank->account_name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Nominal Penarikan *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"
                                        style="border-radius: 10px 0 0 10px; font-weight: 500;">Rp</span>
                                    <input type="number" name="amount" class="form-control border-start-0" required min="10000"
                                        step="1" inputmode="numeric"
                                        max="{{ (int) $wallet->balance }}" placeholder="Minimal 10.000"
                                        style="border-radius: 0 10px 10px 0;">
                                </div>
                                <div class="form-text small mt-2 d-flex justify-content-between">
                                    <span>Minimal penarikan: Rp 10.000</span>
                                    <span>Saldo: <strong class="text-primary">Rp
                                            {{ number_format($wallet->balance, 0, ',', '.') }}</strong></span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-top-0 pb-4 px-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius:10px">Batal</button>
                        <button type="submit" class="btn btn-primary" style="border-radius:10px" {{ $bankAccounts->isEmpty() || $wallet->balance < 10000 ? 'disabled' : '' }}>Request Penarikan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Top Up Modal -->
    <div class="modal fade" id="topupModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px; border:none;">
                <form action="{{ route('account.wallet.topup') }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h6 class="modal-title fw-bold">Top Up Saldo Akun</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4">
                        <div class="alert alert-info border-0 small"
                            style="border-radius:10px; background-color: #eff6ff; color: #1e40af;">
                            <i class="bi bi-info-circle-fill me-2"></i>Setelah memasukkan nominal, Anda akan diarahkan ke
                            halaman pembayaran untuk instruksi transfer dan upload bukti.
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nominal Top Up *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"
                                    style="border-radius: 10px 0 0 10px; font-weight: 500;">Rp</span>
                                <input type="number" name="amount" class="form-control border-start-0" required min="10000"
                                    step="1" inputmode="numeric"
                                    placeholder="Minimal 10.000" style="border-radius: 0 10px 10px 0;">
                            </div>
                            <div class="form-text small mt-2">
                                Minimal top up: Rp 10.000
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pb-4 px-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius:10px">Batal</button>
                        <button type="submit" class="btn btn-primary" style="border-radius:10px">Lanjut ke
                            Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('account_js')
    <script>
        $(document).ready(function () {
            $('#wallet-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('account.wallet') }}",
                columns: [
                    { data: 'date', name: 'created_at' },
                    { data: 'description_formatted', name: 'description' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'amount_formatted', name: 'amount', className: 'text-end' }
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