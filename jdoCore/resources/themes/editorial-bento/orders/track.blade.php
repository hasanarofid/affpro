@extends('theme::layouts.app')
@section('title', 'Tracking #' . $order->order_number)

@section('styles')
<style>
    .eb-track-detail { max-width: 880px; margin: 0 auto; }
    .eb-track-card { background: rgba(255,255,255,.96); border:1px solid rgba(255,255,255,.7); border-radius: 28px; box-shadow: 0 24px 60px rgba(15,23,42,.07); padding: 28px; }
    .eb-status-pill { padding: 8px 16px; border-radius: 999px; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; }
    .eb-row-block { padding:18px; border:1px solid #efe8dd; border-radius:20px; background:#fbfaf6; }
    .eb-product-row { display:grid; grid-template-columns: 64px 1fr auto; gap: 14px; align-items:center; padding: 12px 0; }
    .eb-product-row + .eb-product-row { border-top: 1px dashed #efe8dd; }
    .eb-product-row img { width:64px; height:64px; border-radius:14px; object-fit:cover; background:#f0f0f0; }
</style>
@endsection

@section('content')
<div class="container-xxl px-3 eb-track-detail py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('home') }}" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left me-1"></i> Beranda</a>
        <span class="eb-status-pill" style="background: color-mix(in srgb, var(--primary) 12%, white 88%); color: var(--primary);">{{ ucfirst($order->status) }}</span>
    </div>
    <div class="eb-track-card">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-4">
            <div>
                <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">Order</div>
                <h3 class="fw-bold mb-0">{{ $order->order_number }}</h3>
                <div class="small text-muted">{{ $order->created_at->translatedFormat('d F Y H:i') }}</div>
            </div>
            <a href="{{ route('orders.invoice', $order->order_number) }}" target="_blank" class="btn btn-outline-dark rounded-pill px-4"><i class="bi bi-printer me-1"></i> Cetak Invoice</a>
        </div>

        @php $hasPendingPayment = $order->payments->where('status', 'pending')->isNotEmpty(); @endphp
        <div class="eb-row-block d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">Pembayaran</div>
                <span class="badge mt-1 @if($order->payment_status === 'paid') text-bg-success @elseif($hasPendingPayment) text-bg-warning @else text-bg-secondary @endif">{{ $order->payment_status === 'paid' ? 'Lunas' : ($hasPendingPayment ? 'Menunggu Verifikasi' : 'Belum Dibayar') }}</span>
            </div>
            @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled' && !$hasPendingPayment)
                <a href="{{ route('orders.payment', $order->order_number) }}" class="btn btn-dark rounded-pill px-4"><i class="bi bi-upload me-1"></i> Upload Bukti</a>
            @endif
        </div>

        @if($order->shipment)
            <div class="eb-row-block mb-3">
                <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">Pengiriman</div>
                <div class="fw-semibold mt-1">{{ strtoupper($order->shipment->courier_code ?? '-') }} — {{ $order->shipment->courier_service ?? '' }}</div>
                @if($order->shipment->tracking_number)
                    <div class="mt-2 small">No. Resi: <strong>{{ $order->shipment->tracking_number }}</strong></div>
                @endif
            </div>
        @endif

        <div class="eb-row-block mb-3">
            <div class="small text-uppercase fw-bold text-muted mb-2" style="letter-spacing:.08em">Produk Pesanan</div>
            @foreach($order->items as $item)
                <div class="eb-product-row">
                    @if($item->product && $item->product->primaryImage)
                        <img src="{{ asset($item->product->primaryImage->path) }}" alt="">
                    @else
                        <div class="eb-product-row__placeholder" style="width:64px;height:64px;border-radius:14px;background:#f0f0f0;display:flex;align-items:center;justify-content:center"><i class="bi bi-box text-muted"></i></div>
                    @endif
                    <div>
                        <div class="fw-semibold">{{ $item->product_name }}</div>
                        @if($item->variant_label)<div class="small text-muted">{{ $item->variant_label }}</div>@endif
                        <div class="small text-muted">{{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                    </div>
                    <div class="fw-bold">Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>

        @php $sumEffective = $order->items->sum('subtotal'); $productDiscount = $order->subtotal - $sumEffective; $voucherDiscount = $order->discount_amount - $productDiscount; @endphp
        <div class="eb-row-block">
            <div class="d-flex justify-content-between small text-muted"><span>Subtotal</span><span>Rp {{ number_format($sumEffective, 0, ',', '.') }}</span></div>
            @if($order->shipping_cost > 0)<div class="d-flex justify-content-between small text-muted"><span>Ongkir</span><span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span></div>@endif
            @if($voucherDiscount > 0)<div class="d-flex justify-content-between small text-success"><span>Voucher</span><span>-Rp {{ number_format($voucherDiscount, 0, ',', '.') }}</span></div>@endif
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5"><span>Total Tagihan</span><span style="color:var(--primary)">Rp {{ number_format($order->total, 0, ',', '.') }}</span></div>
        </div>
    </div>
</div>
@endsection
