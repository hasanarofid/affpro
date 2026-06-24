@extends('theme::layouts.app')
@section('title', 'Tracking Order #' . $order->order_number)

@section('content')
 <div class="container py-5" style="max-width:800px">
 <div class="mb-4">
 <a href="{{ route('home') }}" class="text-decoration-none text-muted small hover-primary d-inline-flex align-items-center">
 <i class="bi bi-arrow-left me-2"></i> Kembali ke Beranda
 </a>
 </div>
 <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.06)">
 <div class="card-body p-4">
 <div class="d-flex justify-content-between align-items-start mb-4">
 <div>
 <h5 class="fw-bold mb-0">Order #{{ $order->order_number }}</h5>
 <small class="text-muted">{{ $order->created_at->translatedFormat('d F Y H:i') }}</small>
 </div>
 <span class="badge badge-status
 @if($order->status === 'completed') bg-success
 @elseif($order->status === 'cancelled') bg-danger
 @elseif($order->status === 'shipped') bg-info
 @elseif($order->status === 'processing') bg-warning
 @else bg-secondary @endif
 " style="font-size:.8rem;padding:6px 14px">
 {{ ucfirst($order->status) }}
 </span>
 </div>

 {{-- Payment Status --}}
 <div class="border rounded p-3 mb-4" style="border-radius:12px!important">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <div class="small fw-semibold">Pembayaran</div>
                @php
                    $hasPendingPayment = $order->payments->where('status', 'pending')->isNotEmpty();
                @endphp
                <span class="badge mt-1
                @if($order->payment_status === 'paid') bg-success
                @elseif($hasPendingPayment) bg-warning text-dark
                @else bg-secondary @endif">
                {{ $order->payment_status === 'paid' ? 'Lunas' : ($hasPendingPayment ? 'Menunggu Verifikasi' : 'Belum Dibayar') }}
                </span>
            </div>
            @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled' && !$hasPendingPayment)
                <a href="{{ route('orders.payment', $order->order_number) }}" class="btn btn-sm btn-primary"
                style="border-radius:8px">
                <i class="bi bi-upload me-1"></i> Upload Bukti
                </a>
            @endif
 </div>
 </div>

 {{-- Shipping --}}
 @if($order->shipment)
 <div class="border rounded p-3 mb-4" style="border-radius:12px!important">
 <div class="small fw-semibold mb-1">Pengiriman</div>
 <div class="small text-muted">
 <span class="text-uppercase fw-bold">{{ $order->shipment->courier_code ?? '-' }}</span> — {{ $order->shipment->courier_service ?? '' }}
 @if($order->shipment->estimated_days)
 <br><span class="badge bg-light text-dark border fw-normal mt-1" style="font-size: 0.7rem">
 <i class="bi bi-truck me-1"></i> Estimasi {{ $order->shipment->estimated_days }} Hari
 </span>
 @endif
 @if($order->shipment->tracking_number)
 <div class="mt-2 p-2 bg-light rounded border border-dashed text-dark">
 <span class="small text-muted">No. Resi:</span><br>
 <span class="fw-bold fs-6">{{ $order->shipment->tracking_number }}</span>
 </div>
 @endif
 </div>
 </div>
 @endif

 {{-- Items --}}
 <h6 class="fw-semibold mb-3">Produk</h6>
 @foreach($order->items as $item)
 <div class="d-flex gap-3 mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
 @if($item->product && $item->product->primaryImage)
 <img src="{{ asset($item->product->primaryImage->path) }}"
 style="width:60px;height:60px;object-fit:cover;border-radius:8px">
 @else
 <div style="width:60px;height:60px;border-radius:8px;background:#f0f0f0"
 class="d-flex align-items-center justify-content-center">
 <i class="bi bi-box text-muted"></i>
 </div>
 @endif
 <div class="flex-grow-1">
 <div class="fw-medium small">{{ $item->product_name }}</div>
 @if($item->variant_label) <small class="text-muted">{{ $item->variant_label }}</small> @endif
 <div class="small text-muted mb-1">{{ $item->quantity }} × Rp
 {{ number_format($item->price, 0, ',', '.') }}
 </div>

 @if($order->payment_status === 'paid' && $item->product && $item->product->type === 'digital')
 <div class="bg-light p-2 rounded mt-2 border border-info border-opacity-25 relative">
 <div class="small fw-semibold text-info mb-1"><i class="bi bi-cloud-arrow-down me-1"></i>Akses
 Produk Digital</div>
 @if($item->product->digital_info_text)
 <div class="small text-dark mb-2" style="white-space:pre-wrap;font-family:monospace">
 {{ $item->product->digital_info_text }}</div>
 @endif
 @if($item->product->digital_file_path)
 <a href="{{ asset($item->product->digital_file_path) }}" target="_blank"
 class="btn btn-sm btn-info text-white" style="border-radius:6px;font-size:0.75rem">
 <i class="bi bi-download me-1"></i> Download File
 </a>
 @endif
 </div>
 @endif
 </div>
 <div class="fw-semibold small">Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}</div>
 </div>
 @endforeach

 {{-- Summary --}}
 <div class="mt-4 p-4 rounded-4" style="background: #fdfdfd; border: 1px solid #eee;">
 @php
 // Hitung jumlah subtotal dari barang (berdasarkan harga efektif yang sudah dipotong)
 $sumEffective = $order->items->sum('subtotal');
 // Hitung potongan produk (selisih subtotal order awal dengan subtotal barang efektif)
 $productDiscount = $order->subtotal - $sumEffective;
 // Hitung potongan voucher
 $voucherDiscount = $order->discount_amount - $productDiscount;
 @endphp

 @if($productDiscount > 0)
 <div class="d-flex justify-content-between mb-1">
 <span class="text-muted small">Total Harga Normal</span>
 <span class="fw-medium text-dark text-decoration-line-through" style="font-size: 0.95rem">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
 </div>
 <div class="d-flex justify-content-between mb-2">
 <span class="text-muted small">Potongan Produk</span>
 <span class="fw-medium text-danger" style="font-size: 0.95rem">-Rp {{ number_format($productDiscount, 0, ',', '.') }}</span>
 </div>
 <hr class="my-2 border-dashed opacity-50">
 @endif

 <div class="d-flex justify-content-between mb-2">
 <span class="text-muted fw-semibold small">Subtotal Belanja</span>
 <span class="fw-bold text-dark" style="font-size: 1rem">Rp {{ number_format($sumEffective, 0, ',', '.') }}</span>
 </div>

 @if($order->shipping_cost > 0)
 <div class="d-flex justify-content-between mb-2">
 <span class="text-muted small">Biaya Pengiriman</span>
 <span class="fw-medium text-dark" style="font-size: 0.95rem">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
 </div>
 @endif

 @if($voucherDiscount > 0)
 <div class="d-flex justify-content-between mb-2">
 <span class="text-muted small">Potongan Voucher</span>
 <span class="fw-bold text-success" style="font-size: 0.95rem">-Rp {{ number_format($voucherDiscount, 0, ',', '.') }}</span>
 </div>
 @endif

 <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
 <div class="fw-bold text-dark fs-5">Total Tagihan</div>
 <div class="fw-bold fs-4" style="color: var(--primary)">
 Rp {{ number_format($order->total, 0, ',', '.') }}
 </div>
 </div>
 </div>

 {{-- Actions --}}
 <div class="mt-4 d-flex gap-2">
 <a href="{{ route('orders.invoice', $order->order_number) }}" target="_blank"
 class="btn btn-outline-primary btn-sm" style="border-radius:8px">
 <i class="bi bi-printer me-1"></i> Cetak Invoice
 </a>
 @if($order->status === 'pending')
 <form action="{{ route('orders.cancel', $order->order_number) }}" method="POST"
 onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
 @csrf
 <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius:8px">
 <i class="bi bi-x-circle me-1"></i> Batalkan Pesanan
 </button>
 </form>
 @endif
 </div>
 </div>
 </div>
 </div>
@endsection