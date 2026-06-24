@extends('admin.layouts.app')
@section('title', 'Kelola Saldo: ' . $user->name)
@section('page-title', 'Kelola Saldo: ' . $user->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.wallets.index') }}" class="btn btn-light" style="border-radius:10px">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px">
            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px">
            {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- User Info & Balance -->
        <div class="col-lg-4">
            <div class="card mb-4" style="border:none;border-radius:14px">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold mx-auto mb-3"
                        style="width:64px;height:64px;font-size:1.5rem">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-3">{{ $user->email }} | {{ $user->phone ?? '-' }}</p>

                    <div class="bg-light p-3 rounded-3 mb-3">
                        <div class="text-muted small mb-1">Saldo Saat Ini</div>
                        <h3 class="fw-bold text-success mb-0">Rp {{ number_format($wallet->balance, 0, ',', '.') }}</h3>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#depositModal"
                            style="border-radius:8px">
                            <i class="bi bi-plus-circle me-1"></i> Top Up
                        </button>
                        <button class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#withdrawModal"
                            style="border-radius:8px">
                            <i class="bi bi-dash-circle me-1"></i> Tarik
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="col-lg-8">
            <div class="card" style="border:none;border-radius:14px">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold">Riwayat Transaksi Saldo</h6>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Tanggal</th>
                                <th>Tipe</th>
                                <th>Nominal</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                                <tr>
                                    <td class="ps-4 small">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($trx->type === 'deposit' || $trx->type === 'refund')
                                            <span class="badge bg-success bg-opacity-10 text-success"
                                                style="border-radius:6px">Tambahan Saldo</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger"
                                                style="border-radius:6px">Pengurangan Saldo</span>
                                        @endif
                                    </td>
                                    <td
                                        class="fw-medium {{ in_array($trx->type, ['deposit', 'refund']) ? 'text-success' : 'text-danger' }}">
                                        {{ in_array($trx->type, ['deposit', 'refund']) ? '+' : '-' }}Rp
                                        {{ number_format($trx->amount, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        <div class="small">{{ $trx->description }}</div>
                                        <div class="text-muted" style="font-size:0.7rem">Ref: {{ $trx->reference_number }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">Belum ada transaksi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                    <div class="card-footer bg-white border-top-0">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Deposit Modal -->
    <div class="modal fade" id="depositModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:16px;border:none">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Top Up Saldo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.wallets.transaction', $user->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="deposit">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nominal Top Up <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"
                                    style="border-top-left-radius:8px;border-bottom-left-radius:8px">Rp</span>
                                <input type="number" name="amount" class="form-control border-start-0" required min="1"
                                    step="1" inputmode="numeric"
                                    placeholder="10000" style="border-top-right-radius:8px;border-bottom-right-radius:8px">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Keterangan <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control"
                                placeholder="Contoh: Top up manual via Admin" required style="border-radius:8px">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius:8px">Batal</button>
                        <button type="submit" class="btn btn-primary" style="border-radius:8px">Top Up Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div class="modal fade" id="withdrawModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:16px;border:none">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Penarikan Saldo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.wallets.transaction', $user->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="withdrawal">
                    <div class="modal-body">
                        <div class="alert alert-warning small py-2" style="border-radius:8px">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Penarikan akan langsung memotong saldo
                            pelanggan. Pastikan dana sudah ditransfer ke pelanggan jika ini adalah pencairan manual.
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Nominal Penarikan <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"
                                    style="border-top-left-radius:8px;border-bottom-left-radius:8px">Rp</span>
                                <input type="number" name="amount" class="form-control border-start-0" required min="1"
                                    step="1" inputmode="numeric"
                                    max="{{ (int) $wallet->balance }}" placeholder="10000"
                                    style="border-top-right-radius:8px;border-bottom-right-radius:8px">
                            </div>
                            <small class="text-muted mt-1 d-block">Maksimal: Rp
                                {{ number_format($wallet->balance, 0, ',', '.') }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Keterangan <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control"
                                placeholder="Contoh: Pencairan ke Bank BCA" required style="border-radius:8px">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius:8px">Batal</button>
                        <button type="submit" class="btn btn-danger" style="border-radius:8px">Tarik Saldo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection