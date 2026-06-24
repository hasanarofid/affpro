@extends('theme::layouts.app')
@section('title', $product->meta_title ?? $product->name)
@section('meta_description', $product->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($product->description), 160))
@section('styles')
<style>
	.eb-product-page { display:grid; grid-template-columns: 1.05fr .95fr; gap: 36px; align-items: start; }
	.eb-gallery-card, .eb-info-card, .eb-desc-card { background: rgba(255,255,255,.92); border:1px solid rgba(255,255,255,.6); border-radius: 28px; box-shadow: 0 20px 50px rgba(15,23,42,.06); overflow: hidden; }
	.eb-main-image-wrap {
		width: 100%;
		background: #f8fafc;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 18px;
	}
	.eb-main-image {
		display: block;
		max-width: 100%;
		max-height: 70vh;
		width: auto;
		height: auto;
		object-fit: contain;
		border-radius: 18px;
	}
	.eb-thumbs { display:flex; gap:12px; padding:18px; overflow-x:auto; background:#fcfbf9; }
	.eb-thumbs img { width:76px; height:76px; object-fit:cover; border-radius:16px; border:2px solid transparent; cursor:pointer; opacity:.7; transition:all .2s ease; flex-shrink: 0; }
	.eb-thumbs img.active, .eb-thumbs img:hover { opacity:1; border-color: var(--primary); }
	.eb-price-main { font-size: clamp(2rem, 4vw, 3rem); font-weight: 800; letter-spacing:-.04em; }
	.eb-variant-btn { border:1px solid #e7e5e4; background:#fff; border-radius:999px; padding:10px 18px; font-weight:600; transition:.2s ease; }
	.eb-variant-btn:hover { border-color: var(--primary); color: var(--primary); }
	.eb-variant-btn.selected { background:var(--primary); color:#fff; border-color:var(--primary); box-shadow:0 12px 26px rgba(79,70,229,.22); }
	.eb-qty-wrap { display:inline-flex; align-items:center; gap:10px; background:#fff; border:1px solid #e7e5e4; border-radius:999px; padding:8px; }
	.eb-qty-btn { width:38px; height:38px; border-radius:999px; border:0; background:#f5f7fb; }
	.eb-qty-input { width:56px; border:0; text-align:center; font-weight:700; outline:0; }
	.eb-desc-content { line-height:1.9; color:#334155; }
	.eb-desc-content img { max-width:100%; height:auto; border-radius:20px; }
	@media (max-width: 991.98px) { .eb-product-page { grid-template-columns:1fr; } .eb-main-image { max-height: 60vh; } }
	@media (max-width: 575.98px) { .eb-main-image { max-height: 80vw; } }
</style>
@endsection
@section('content')
<div class="container-xxl px-3">
	<nav aria-label="breadcrumb" class="mb-4">
		<ol class="breadcrumb small mb-0">
			<li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Beranda</a></li>
			@if($product->category)
				<li class="breadcrumb-item"><a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="text-decoration-none text-muted">{{ $product->category->name }}</a></li>
			@endif
			<li class="breadcrumb-item active text-dark">{{ \Illuminate\Support\Str::limit($product->name, 36) }}</li>
		</ol>
	</nav>

	<div class="eb-product-page" x-data="variantSelector()">
		<section>
			<div class="eb-gallery-card">
				@if($product->images->isNotEmpty())
					<div class="eb-main-image-wrap">
						<img id="ebMainProductImage" src="{{ asset($product->images->first()->path) }}" class="eb-main-image" alt="{{ $product->name }}">
					</div>
					@if($product->images->count() > 1)
						<div class="eb-thumbs">
							@foreach($product->images as $i => $img)
								<img src="{{ asset($img->path) }}" class="{{ $i===0 ? 'active' : '' }}" onclick="document.getElementById('ebMainProductImage').src=this.src; this.parentNode.querySelectorAll('img').forEach(i=>i.classList.remove('active')); this.classList.add('active');">
							@endforeach
						</div>
					@endif
				@else
					<div class="d-flex align-items-center justify-content-center" style="height:520px;background:#f8fafc"><i class="bi bi-image text-muted opacity-50 fs-1"></i></div>
				@endif
			</div>
		</section>

		<section>
			<div class="eb-info-card p-4 p-lg-5">
				<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
					@if($product->category)<span class="eb-kicker">{{ $product->category->name }}</span>@endif
					@if($product->brand)<span class="small text-muted fw-semibold">{{ $product->brand->name }}</span>@endif
					@if($product->type === 'digital')<span class="badge rounded-pill text-bg-success">Digital</span>@endif
				</div>
				<h1 class="mb-3" style="font-family:var(--heading); font-size: clamp(1.8rem, 3vw, 3rem); line-height:1.12; letter-spacing:-.04em;">{{ $product->name }}</h1>
				<div class="d-flex align-items-center gap-3 mb-4 text-muted small">
					<span><i class="bi bi-star-fill text-warning me-1"></i>5.0</span>
					<span>•</span>
					<span>{{ $product->sold_count }} terjual</span>
					@if($product->sku)<span>•</span><span>SKU: {{ $product->sku }}</span>@endif
				</div>
				<div class="mb-4">
					<template x-if="currentVariant">
						<div class="eb-price-main" style="color:var(--primary)">Rp <span x-text="formatRupiah(currentVariant.price)"></span></div>
					</template>
					<template x-if="!currentVariant">
						<div class="d-flex flex-wrap align-items-end gap-3">
							<div class="eb-price-main" style="color:var(--primary)">Rp {{ number_format($product->discount_price ?? $product->effective_price, 0, ',', '.') }}</div>
							@if($product->discount_price)
								<div class="text-muted text-decoration-line-through pb-2">Rp {{ number_format($product->base_price, 0, ',', '.') }}</div>
							@endif
						</div>
					</template>
				</div>

				@if($product->has_variants && $product->variantTypes->isNotEmpty())
					<div class="mb-4">
						@foreach($product->variantTypes as $type)
							<div class="mb-3">
								<div class="small text-uppercase fw-bold text-muted mb-2" style="letter-spacing:.08em">{{ $type->name }}</div>
								<div class="d-flex flex-wrap gap-2">
									@foreach($type->values as $val)
										<button type="button" class="eb-variant-btn" :class="{'selected': selected['{{ $type->name }}'] === '{{ $val->value }}'}" @click="selectVariant('{{ $type->name }}', '{{ $val->value }}')">{{ $val->value }}</button>
									@endforeach
								</div>
							</div>
						@endforeach
						<input type="hidden" name="variant_id" x-model="variantId">
					</div>
				@endif

				<div class="d-flex flex-wrap align-items-end gap-4 mb-4">
					<div>
						<div class="small text-uppercase fw-bold text-muted mb-2" style="letter-spacing:.08em">Jumlah</div>
						<div class="eb-qty-wrap">
							<button class="eb-qty-btn" type="button" onclick="let i=document.getElementById('qty');if(i.value>{{ $product->min_order }})i.value--">−</button>
							<input id="qty" class="eb-qty-input" type="number" value="{{ $product->min_order }}" min="{{ $product->min_order }}">
							<button class="eb-qty-btn" type="button" onclick="document.getElementById('qty').value++">+</button>
						</div>
					</div>
					<div class="pb-2">
						@if($product->is_in_stock)
							<template x-if="currentVariant"><span class="badge rounded-pill text-bg-success px-3 py-2">Stok: <span x-text="currentVariant.stock"></span></span></template>
							<template x-if="!currentVariant"><span class="badge rounded-pill text-bg-success px-3 py-2">Tersedia</span></template>
						@else
							<span class="badge rounded-pill text-bg-danger px-3 py-2">Stok Habis</span>
						@endif
					</div>
				</div>

				@if(!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'superadmin']))
					<form id="addToCartForm" action="{{ route('cart.add') }}" method="POST">
						@csrf
						<input type="hidden" name="product_id" value="{{ $product->id }}">
						<input type="hidden" name="variant_id" :value="variantId">
						<input type="hidden" name="quantity" id="qty-hidden" value="1">
						<div class="d-flex flex-column flex-sm-row gap-3">
							<button type="button" @click="addToCart()" class="btn btn-dark btn-lg rounded-pill px-4 flex-grow-1" :disabled="{{ !$product->is_in_stock ? 'true' : 'false' }} || ({{ $product->has_variants ? 'true' : 'false' }} && !currentVariant)">
								<i class="bi bi-bag-plus me-2"></i>Tambah ke Keranjang
							</button>
							@php
								$phone = preg_replace('/[^0-9]/', '', app(\App\Services\SettingService::class)->get('store_phone', ''));
								if (str_starts_with($phone, '0')) $phone = '62' . substr($phone, 1);
								$waText = urlencode("Halo Admin, saya tertarik dengan produk: " . $product->name);
							@endphp
							@if($phone)
								<a href="https://wa.me/{{ $phone }}?text={{ $waText }}" target="_blank" class="btn btn-outline-success btn-lg rounded-pill px-4">
									<i class="bi bi-whatsapp me-1"></i>WhatsApp
								</a>
							@endif
						</div>
					</form>
				@else
					<div class="alert alert-info border-0 rounded-4">Mode admin aktif. Interaksi pembelian dinonaktifkan.</div>
				@endif
			</div>

			<div class="eb-desc-card p-4 p-lg-5 mt-4">
				<div class="d-flex align-items-center justify-content-between mb-3">
					<h3 class="eb-section-title mb-0" style="font-size:1.6rem">Deskripsi Produk</h3>
				</div>
				<div class="eb-desc-content">{!! $product->description ?: '<p class="text-muted mb-0">Belum ada deskripsi untuk produk ini.</p>' !!}</div>
			</div>
		</section>
	</div>
</div>
@endsection

@section('scripts')
@php
	$variantData = $product->variants->map(function ($v) {
		return [
			'id' => $v->id,
			'label' => $v->label,
			'price' => (int) $v->price,
			'stock' => (int) $v->stock,
			'values' => $v->values->pluck('value')->toArray(),
		];
	})->values();
@endphp
<script>
function variantSelector() {
	return {
		selected: {},
		variantId: null,
		currentVariant: null,
		variants: {!! $variantData->toJson() !!},
		selectVariant(type, value) {
			this.selected[type] = value;
			this.findMatchingVariant();
		},
		findMatchingVariant() {
			const selectedValues = Object.values(this.selected).filter(Boolean);
			if (!selectedValues.length) { this.variantId = null; this.currentVariant = null; return; }
			const match = this.variants.find(v => selectedValues.every(val => v.values.includes(val)) && v.values.length === selectedValues.length);
			this.currentVariant = match || null;
			this.variantId = match ? match.id : null;
		},
		formatRupiah(value) {
			return new Intl.NumberFormat('id-ID').format(value || 0);
		},
		addToCart() {
			document.getElementById('qty-hidden').value = document.getElementById('qty').value;
			document.getElementById('addToCartForm').submit();
		}
	};
}
</script>
@endsection
