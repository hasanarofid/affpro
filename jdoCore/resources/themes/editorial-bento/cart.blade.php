@extends('theme::layouts.app')
@section('title', __('product.cart') . ' — ' . app(\App\Services\SettingService::class)->storeName())

@section('styles')
<style>
	.eb-cart-grid { display:grid; grid-template-columns: 1fr 380px; gap: 24px; }
	.eb-cart-card, .eb-summary-card { background: rgba(255,255,255,.92); border:1px solid rgba(255,255,255,.65); border-radius: 28px; box-shadow: 0 20px 50px rgba(15,23,42,.06); }
	.eb-cart-row { display:grid; grid-template-columns: 96px 1fr auto; gap: 18px; padding: 22px; align-items:center; }
	.eb-cart-row + .eb-cart-row { border-top: 1px solid #f1ede6; }
	.eb-cart-image { width:96px; height:96px; border-radius:20px; object-fit:cover; background:#f8fafc; }
	.eb-qty-wrap { display:inline-flex; align-items:center; gap:10px; background:#fff; border:1px solid #e7e5e4; border-radius:999px; padding:8px; }
	.eb-qty-btn { width:34px; height:34px; border-radius:999px; border:0; background:#f5f7fb; }
	.eb-qty-input { width:44px; border:0; text-align:center; font-weight:700; outline:0; background:transparent; }
	.eb-empty { text-align:center; padding: 80px 20px; }
	@media (max-width: 991.98px) { .eb-cart-grid { grid-template-columns: 1fr; } }
	@media (max-width: 575.98px) { .eb-cart-row { grid-template-columns: 78px 1fr; } .eb-cart-row > :last-child { grid-column: 1 / -1; } }
</style>
@endsection

@section('content')
<div class="container-xxl px-3">
	<div class="d-flex align-items-center justify-content-between mb-4">
		<div>
			<span class="eb-kicker mb-2">Your Cart</span>
			<h1 class="eb-section-title mb-0">Keranjang Belanja</h1>
		</div>
		<a href="{{ route('products.index') }}" class="btn btn-outline-dark rounded-pill px-4">Lanjut Belanja</a>
	</div>

	@if($cart && $cart->items->count() > 0)
		<div class="eb-cart-grid" id="cart-container">
			<div class="eb-cart-card" id="cart-items-wrapper">
				@foreach($cart->items as $item)
					<div class="eb-cart-row cart-item" id="item-{{ $item->id }}">
						<div>
							@if($item->product->primaryImage)
								<img src="{{ asset($item->product->primaryImage->path) }}" class="eb-cart-image">
							@else
								<div class="eb-cart-image d-flex align-items-center justify-content-center"><i class="bi bi-image text-muted"></i></div>
							@endif
						</div>
						<div>
							<a href="{{ route('products.show', $item->product->slug) }}" class="text-decoration-none text-dark fw-bold d-block mb-1">{{ $item->product->name }}</a>
							@if($item->variant)
								<div class="small text-muted mb-2">{{ $item->variant->label }}</div>
							@endif
							<div class="fw-bold" style="color:var(--primary)">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
						</div>
						<div class="d-flex flex-column align-items-end gap-3">
							<div class="eb-qty-wrap">
								<button type="button" class="eb-qty-btn btn-minus" data-id="{{ $item->id }}" {{ $item->quantity <= 1 ? 'disabled' : '' }}>−</button>
								<input type="number" class="eb-qty-input item-qty-{{ $item->id }}" value="{{ $item->quantity }}" min="1" readonly>
								<button type="button" class="eb-qty-btn btn-plus" data-id="{{ $item->id }}">+</button>
							</div>
							<button class="btn btn-sm btn-light text-danger rounded-pill px-3 btn-remove-item" data-id="{{ $item->id }}"><i class="bi bi-trash me-1"></i>Hapus</button>
						</div>
					</div>
				@endforeach
			</div>

			<aside class="eb-summary-card p-4" id="cart-summary-wrapper">
				<h5 class="fw-bold mb-4">Ringkasan Pesanan</h5>
				<div class="d-flex justify-content-between text-muted mb-2"><span>Total Item</span><span><span id="summary-total-items">{{ $cart->total_items }}</span> produk</span></div>
				<div class="d-flex justify-content-between fw-bold fs-5 pt-3 mt-3 border-top"><span>Total Harga</span><span id="summary-total-price" style="color:var(--primary)">Rp {{ number_format($cart->total, 0, ',', '.') }}</span></div>
				<a href="{{ route('checkout.index') }}" class="btn btn-dark w-100 rounded-pill py-3 mt-4">Lanjut ke Checkout</a>
			</aside>
		</div>
	@else
		<div class="eb-cart-card eb-empty">
			<i class="bi bi-bag-x fs-1 text-muted opacity-50"></i>
			<h4 class="fw-bold mt-3">Keranjang Masih Kosong</h4>
			<p class="text-muted mb-4">Tambahkan produk favorit Anda untuk mulai checkout.</p>
			<a href="{{ route('products.index') }}" class="btn btn-dark rounded-pill px-4">Jelajahi Produk</a>
		</div>
	@endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
	$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
	let updateTimer;
	function updateCartItem(itemId, qtyInput) {
		let quantity = parseInt(qtyInput.val()); if(isNaN(quantity) || quantity < 1) quantity = 1;
		let url = "{{ route('cart.update', ':id') }}".replace(':id', itemId);
		$.ajax({ url: url, type: 'PUT', data: { quantity: quantity }, success: function(response) { if(response.success) { location.reload(); } } });
	}
	$('.btn-minus').on('click', function() { let itemId = $(this).data('id'); let input = $('.item-qty-' + itemId); let qty = parseInt(input.val()) - 1; if(qty >= 1) { input.val(qty); clearTimeout(updateTimer); updateTimer = setTimeout(() => updateCartItem(itemId, input), 200); } });
	$('.btn-plus').on('click', function() { let itemId = $(this).data('id'); let input = $('.item-qty-' + itemId); input.val(parseInt(input.val()) + 1); clearTimeout(updateTimer); updateTimer = setTimeout(() => updateCartItem(itemId, input), 200); });
	$('.btn-remove-item').on('click', function() { let itemId = $(this).data('id'); let url = "{{ route('cart.remove', ':id') }}".replace(':id', itemId); $.ajax({ url: url, type: 'DELETE', success: function(){ location.reload(); } }); });
});
</script>
@endpush