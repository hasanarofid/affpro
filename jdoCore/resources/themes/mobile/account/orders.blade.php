@extends('theme::account.layout')
@section('title', 'Pesanan Saya')

@section('account_content')
 <div class="card-body p-0 pb-4">
    <div class="p-3 border-bottom d-flex align-items-center bg-light" style="border-radius: 20px 20px 0 0;">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-bag-heart-fill me-2 text-primary"></i>Daftar Transaksi
        </h6>
    </div>

    <div class="p-3">
        @forelse($orders as $order)
            @php
                $statusColors = [
                    'pending' => 'warning',
                    'confirmed' => 'info',
                    'processing' => 'primary',
                    'shipped' => 'secondary',
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                    'expired' => 'dark'
                ];
                $color = $statusColors[$order->status] ?? 'secondary';
                $item = $order->items->first();
            @endphp
            <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px; border: 1px solid rgba(0,0,0,0.05) !important;">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark" style="font-size: 0.8rem;"><i class="bi bi-receipt me-1 text-muted"></i> {{ $order->order_number }}</span>
                    <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} rounded-pill px-2" style="font-size: 0.65rem;">
                        {{ __('order.status_' . $order->status) }}
                    </span>
                </div>
                
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        @if($item && $item->product && $item->product->primaryImage)
                            <img src="{{ asset($item->product->primaryImage->image_path ?? $item->product->primaryImage->path) }}" alt="{{ $item->product_name }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 12px; border: 1px solid #f8f9fa;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 12px;">
                                <i class="bi bi-bag text-muted fs-4"></i>
                            </div>
                        @endif
                        
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-dark fw-bold" style="font-size: 0.85rem; line-height: 1.3;">{{ $item ? Str::limit($item->product_name, 40) : 'Produk Dihapus' }}</h6>
                            <div class="text-muted small mb-1" style="font-size: 0.7rem;">{{ $order->created_at->format('d M Y, H:i') }}</div>
                            @if($order->items->count() > 1)
                                <div class="text-muted fw-medium" style="font-size: 0.7rem;">+{{ $order->items->count() - 1 }} produk lainnya</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top d-flex flex-column gap-3 py-3" style="border-radius: 0 0 16px 16px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-medium" style="font-size: 0.75rem;">Total Tagihan</span>
                        <span class="fw-bold fs-6 text-dark text-primary">Rp{{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>

                    <div class="d-flex gap-2 w-100">
                        @if($order->status === 'pending' && $order->payment_status === 'unpaid')
                            <a href="{{ route('orders.payment', $order->order_number) }}" class="btn btn-primary btn-sm flex-grow-1 fw-bold py-2" style="border-radius: 10px; font-size: 0.8rem;">Bayar Sekarang</a>
                        @endif
                        <a href="{{ route('orders.track', $order->order_number) }}" class="btn btn-outline-primary btn-sm flex-grow-1 fw-bold bg-light" style="border-radius: 10px; font-size: 0.8rem; border:none; padding:8px 0;">Detail Transaksi</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-bag-x text-muted mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                <h6 class="fw-bold text-dark">Belum Ada Transaksi</h6>
                <p class="text-muted small">Anda belum memiliki riwayat pesanan apapun.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary rounded-pill px-4 mt-2">Mulai Belanja</a>
            </div>
        @endforelse

        <!-- Pagination -->
        @if($orders->hasPages())
        <div class="mt-4 mb-2 d-flex justify-content-center mobile-pagination">
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
 </div>
@endsection
<style>
.mobile-pagination .pagination {
    margin-bottom: 0;
    --bs-pagination-padding-x: 0.5rem;
    --bs-pagination-padding-y: 0.25rem;
    --bs-pagination-font-size: 0.875rem;
}
</style>