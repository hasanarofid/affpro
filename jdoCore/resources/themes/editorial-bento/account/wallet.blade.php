@extends('theme::account.layout')
@section('title', 'Saldo Akun')

@section('account_content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <span class="eb-kicker">Wallet</span>
            <h3 class="fw-bold mt-2 mb-0" style="font-family:var(--heading); letter-spacing:-.03em">Saldo Saya</h3>
            <div class="text-muted small mt-1">Top up, tarik tunai, dan riwayat transaksi saldo.</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-dark rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#topupModal"><i class="bi bi-plus-lg me-1"></i>Top Up</button>
            <button class="btn btn-outline-dark rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#withdrawModal"><i class="bi bi-cash-coin me-1"></i>Tarik</button>
        </div>
    </div>

    <div class="p-4 rounded-4" style="background:linear-gradient(135deg, color-mix(in srgb, var(--primary) 84%, black 16%), color-mix(in srgb, var(--secondary) 84%, black 16%)); color:#fff">
        <div class="small text-uppercase fw-bold opacity-75" style="letter-spacing:.08em">Saldo Tersedia</div>
        <div class="fw-bold" style="font-size:2rem">Rp {{ number_format(($wallet->balance ?? 0), 0, ',', '.') }}</div>
    </div>

    @if(isset($transactions) && count($transactions))
        <h6 class="fw-bold mt-4 mb-3">Riwayat Transaksi</h6>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Tanggal</th><th>Jenis</th><th>Status</th><th class="text-end">Jumlah</th></tr></thead>
                <tbody>
                    @foreach($transactions as $t)
                        <tr>
                            <td class="text-muted small">{{ $t->created_at->translatedFormat('d M Y H:i') }}</td>
                            <td>{{ ucfirst($t->type ?? '-') }}</td>
                            <td><span class="badge text-bg-light">{{ ucfirst($t->status ?? '-') }}</span></td>
                            <td class="text-end fw-bold">Rp {{ number_format($t->amount ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center text-muted py-5 small">Belum ada transaksi saldo.</div>
    @endif

    {{-- Topup Modal --}}
    <div class="modal fade" id="topupModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4"><form action="{{ route('account.wallet.topup') }}" method="POST">@csrf<div class="modal-header border-0"><h5 class="modal-title">Top Up Saldo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label small fw-semibold">Nominal</label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="amount" class="form-control" min="10000" step="1" inputmode="numeric" required placeholder="10.000"></div></div><div class="modal-footer border-0"><button type="submit" class="btn btn-dark rounded-pill px-4">Lanjut Bayar</button></div></form></div></div></div>

    {{-- Withdraw Modal --}}
    <div class="modal fade" id="withdrawModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4"><form action="{{ route('account.wallet.withdraw') }}" method="POST">@csrf<div class="modal-header border-0"><h5 class="modal-title">Tarik Saldo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label small fw-semibold">Nominal</label><div class="input-group"><span class="input-group-text">Rp</span><input type="number" name="amount" class="form-control" min="10000" step="1" inputmode="numeric" required></div></div><div class="modal-footer border-0"><button type="submit" class="btn btn-dark rounded-pill px-4">Request Penarikan</button></div></form></div></div></div>
@endsection
