@extends('theme::account.layout')
@section('title', 'Pesanan Saya')

@section('account_content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <span class="eb-kicker">My Orders</span>
            <h3 class="fw-bold mt-2 mb-0" style="font-family:var(--heading); letter-spacing:-.03em">Riwayat Pesanan</h3>
            <div class="text-muted small mt-1">Semua transaksi Anda tersimpan di sini.</div>
        </div>
    </div>

    @if(isset($orders) && count($orders))
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th class="text-end">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $o)
                        <tr>
                            <td><span class="fw-bold">{{ $o->order_number }}</span></td>
                            <td class="text-muted small">{{ $o->created_at->translatedFormat('d M Y') }}</td>
                            <td><span class="badge text-bg-light">{{ ucfirst($o->status) }}</span></td>
                            <td class="text-end fw-bold">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                            <td class="text-end"><a href="{{ route('orders.track', $o->order_number) }}" class="btn btn-sm btn-outline-dark rounded-pill">Detail</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(method_exists($orders, 'links'))<div class="mt-3">{{ $orders->links() }}</div>@endif
    @else
        <div class="text-center py-5">
            <i class="bi bi-bag fs-1 text-muted opacity-50"></i>
            <h5 class="fw-bold mt-3">Belum ada pesanan</h5>
            <p class="text-muted mb-3">Mulai belanja produk pilihan untuk melihat riwayat di sini.</p>
            <a href="{{ route('products.index') }}" class="btn btn-dark rounded-pill px-4">Mulai Belanja</a>
        </div>
    @endif
@endsection
