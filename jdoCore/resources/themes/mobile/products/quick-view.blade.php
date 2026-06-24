<div class="row g-4"
 x-data="initQuickView({{ htmlspecialchars(json_encode($product->variants), ENT_QUOTES, 'UTF-8') }}, {{ $product->id }}, {{ $product->min_order }})">

 <style>
 .quick-view-img {
 border-radius: 16px;
 object-fit: cover;
 }

 .qv-variant-btn {
 border-radius: 10px;
 padding: 6px 14px;
 font-weight: 500;
 font-size: 0.85rem;
 border: 2px solid #edf2f7;
 background: #f8fafc;
 color: #475569;
 transition: all 0.2s;
 }

 .qv-variant-btn.selected {
 background: var(--primary);
 color: white;
 border-color: var(--primary);
 }

 .qv-qty-wrapper {
 border: 2px solid #edf2f7;
 border-radius: 12px;
 padding: 4px;
 display: inline-flex;
 align-items: center;
 background: white;
 }

 .qv-qty-btn {
 background: #f1f5f9;
 border: none;
 width: 34px;
 height: 34px;
 border-radius: 8px !important;
 display: flex;
 align-items: center;
 justify-content: center;
 font-weight: bold;
 color: #475569;
 transition: 0.2s;
 }

 .qv-qty-input {
 border: none !important;
 width: 40px;
 text-align: center;
 font-weight: 600;
 font-size: 1rem;
 color: #0f172a;
 background: transparent;
 padding: 0;
 box-shadow: none !important;
 }
 </style>

 <!-- Gallery -->
 <div>
 <div class="position-relative h-100">
 @if($product->images->isNotEmpty())
 <img id="quickProductImage" src="{{ asset($product->images->first()->path) }}"
 class="quick-view-img shadow-sm w-100 h-100" style="min-height:300px;" alt="{{ $product->name }}">
 @else
 <div class="bg-light d-flex align-items-center justify-content-center quick-view-img w-100 h-100"
 style="min-height:300px;">
 <i class="bi bi-image text-muted" style="font-size:4rem; opacity:0.3;"></i>
 </div>
 @endif
 @if($product->type === 'digital')
 <div class="position-absolute top-0 start-0 m-3 badge bg-success px-3 py-2 rounded-pill shadow-sm"
 style="font-size:0.7rem; letter-spacing: 0.5px;">PRODUK DIGITAL</div>
 @endif
 </div>
 </div>

 <!-- Product Info -->
 <div class=" d-flex flex-column">
 <div class="d-flex align-items-center gap-2 mb-2">
 <span
 class="text-primary fw-medium small bg-primary-subtle px-2 py-1 rounded">{{ $product->category->name ?? '' }}</span>
 <span class="text-muted small fw-medium"><i class="bi bi-star-fill text-warning me-1"></i>5.0</span>
 <span class="text-muted small">· <span class="fw-bold text-dark">{{ $product->sold_count }}</span>
 Terjual</span>
 </div>

 <h2 class="fw-bold mb-3 text-dark" style="font-size:1.5rem; line-height: 1.3; letter-spacing: -0.01em;">
 {{ $product->name }}</h2>

 <div class="mb-3 pb-3 border-bottom">
 <template x-if="currentVariant">
 <span class="fw-black"
 style="font-size:1.8rem; background:var(--gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; letter-spacing: -0.5px;"
 x-text="formatRupiah(currentVariant.price)"></span>
 </template>
 <template x-if="!currentVariant">
 <div class="d-flex align-items-end gap-2 flex-wrap">
 <span class="fw-black"
 style="font-size:1.8rem; background:var(--gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; letter-spacing: -0.5px; line-height: 1;">
 Rp {{ number_format($product->discount_price ?? $product->effective_price, 0, ',', '.') }}
 </span>
 @if($product->discount_price)
 <div class="text-muted text-decoration-line-through fw-medium"
 style="font-size:0.9rem; padding-bottom:4px;">
 Rp {{ number_format($product->base_price, 0, ',', '.') }}
 </div>
 @endif
 </div>
 </template>
 </div>

 <div class="flex-grow-1">
 @if($product->has_variants && $product->variantTypes->isNotEmpty())
 <div class="mb-3">
 @foreach($product->variantTypes as $type)
 <div class="mb-2">
 <label class="form-label small fw-bold text-uppercase text-muted"
 style="letter-spacing: 0.5px; font-size: 0.75rem;">{{ $type->name }}</label>
 <div class="d-flex flex-wrap gap-2">
 @foreach($type->values as $val)
 <button type="button" class="btn qv-variant-btn"
 :class="{'selected': selected['{{ $type->name }}'] === '{{ $val->value }}'}"
 @click="selectVariant('{{ $type->name }}', '{{ $val->value }}')">
 {{ $val->value }}
 </button>
 @endforeach
 </div>
 </div>
 @endforeach
 </div>
 @endif

 <div class="mb-4">
 <label class="form-label small fw-bold text-uppercase text-muted"
 style="letter-spacing: 0.5px; font-size: 0.75rem;">Jumlah</label>
 <div class="d-flex align-items-center gap-3">
 <div class="qv-qty-wrapper">
 <button class="qv-qty-btn" type="button" @click="if(qty > minOrder) qty--"><i
 class="bi bi-dash"></i></button>
 <input type="number" class="qv-qty-input focus-ring-0" x-model.number="qty" :min="minOrder">
 <button class="qv-qty-btn" type="button" @click="qty++"><i class="bi bi-plus"></i></button>
 </div>
 @if($product->is_in_stock)
 <template x-if="currentVariant">
 <span
 class="badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1"
 x-text="'Sisa: ' + currentVariant.stock"></span>
 </template>
 @endif
 </div>
 @if($product->min_order > 1)
 <div class="text-danger mt-1 fw-medium" style="font-size:0.75rem"><i
 class="bi bi-info-circle me-1"></i>Min. order {{ $product->min_order }} pcs</div>
 @endif
 </div>
 </div>

 <!-- Actions Footer -->
 <div class="pt-3 border-top mt-auto">
 @if(!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'superadmin']))
 <form action="{{ route('cart.add') }}" method="POST" class="w-100 m-0">
 @csrf
 <input type="hidden" name="product_id" value="{{ $product->id }}">
 <input type="hidden" name="variant_id" :value="variantId">
 <input type="hidden" name="quantity" :value="qty">

 <div class="d-flex gap-2 w-100">
 <button type="button" @click.prevent="addToCartQuickView($event)"
 class="btn btn-primary flex-grow-1 py-2 fw-bold text-white shadow-sm"
 style="border-radius:12px;"
 :disabled="{{ !$product->is_in_stock ? 'true' : 'false' }} || ({{ $product->has_variants ? 'true' : 'false' }} && !currentVariant)">
 <i class="bi bi-cart-plus me-1"></i> + Keranjang
 </button>
 <button type="submit" name="buy_now" value="1"
 class="btn btn-outline-primary py-2 fw-bold px-3 "
 style="border-radius:12px; border-width: 2px;" title="Beli Langsung"
 :disabled="{{ !$product->is_in_stock ? 'true' : 'false' }} || ({{ $product->has_variants ? 'true' : 'false' }} && !currentVariant)">
 Beli
 </button>
 <a href="{{ route('products.show', $product->slug) }}"
 class="btn btn-light py-2 px-3 border border-secondary" style="border-radius:12px;"
 title="Lihat Detail Penuh">
 <i class="bi bi-box-arrow-up-right fw-bold"></i>
 </a>
 </div>
 </form>
 @else
 <div class="w-100 d-flex flex-column gap-2">
 <div class="alert alert-info w-100 text-center mb-0 py-2 d-flex align-items-center justify-content-center gap-2"
 style="border-radius:12px; border:none; background:#f0f9ff; color:#0369a1; font-size: 0.85rem;">
 <i class="bi bi-shield-check"></i> Mode Admin
 </div>
 <a href="{{ route('products.show', $product->slug) }}"
 class="btn btn-outline-secondary w-100 py-2 fw-medium" style="border-radius:12px;">
 Lihat Halaman Produk
 </a>
 </div>
 @endif
 </div>
 </div>
</div>