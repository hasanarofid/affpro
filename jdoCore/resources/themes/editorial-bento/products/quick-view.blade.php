<div class="row g-4" x-data="initQuickView({{ htmlspecialchars(json_encode($product->variants), ENT_QUOTES, 'UTF-8') }}, {{ $product->id }}, {{ $product->min_order }})">
    <style>
        .qv-image { border-radius: 24px; object-fit: cover; }
        .qv-pill { display:inline-flex; align-items:center; padding:6px 14px; border-radius:999px; background:#f6f3ee; color:#475569; font-size:.78rem; font-weight:600; }
        .qv-variant { border:2px solid #ece7de; background:#fafafa; color:#475569; border-radius:999px; padding:8px 16px; font-weight:600; font-size:.85rem; transition:.2s; }
        .qv-variant.selected { background: var(--primary); color:#fff; border-color: var(--primary); box-shadow:0 12px 26px rgba(79,70,229,.22); }
        .qv-qty-wrap { border:2px solid #ece7de; background:#fff; border-radius:999px; padding:4px; display:inline-flex; align-items:center; }
        .qv-qty-btn { width:34px; height:34px; border-radius:999px; border:0; background:#f6f3ee; color:#475569; }
    </style>

    <div class="col-lg-5">
        <div class="position-relative h-100">
            @if($product->images->isNotEmpty())
                <img id="quickProductImage" src="{{ asset($product->images->first()->path) }}" class="qv-image w-100 h-100" style="min-height:340px;" alt="{{ $product->name }}">
            @else
                <div class="qv-image bg-light d-flex align-items-center justify-content-center w-100 h-100" style="min-height:340px;"><i class="bi bi-image text-muted opacity-50 fs-1"></i></div>
            @endif
        </div>
    </div>

    <div class="col-lg-7 d-flex flex-column">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            @if($product->category)<span class="qv-pill">{{ $product->category->name }}</span>@endif
            <span class="text-muted small"><i class="bi bi-star-fill text-warning me-1"></i>5.0 · {{ $product->sold_count }} terjual</span>
        </div>
        <h3 class="fw-bold mb-3" style="font-family: var(--heading); letter-spacing:-.03em; line-height:1.18;">{{ $product->name }}</h3>

        <div class="mb-3 pb-3 border-bottom">
            <template x-if="currentVariant">
                <span class="fw-bold" style="font-size:1.8rem; color:var(--primary)" x-text="formatRupiah(currentVariant.price)"></span>
            </template>
            <template x-if="!currentVariant">
                <div class="d-flex align-items-end gap-2">
                    <span class="fw-bold" style="font-size:1.8rem; color:var(--primary)">Rp {{ number_format($product->discount_price ?? $product->effective_price, 0, ',', '.') }}</span>
                    @if($product->discount_price)<span class="text-muted text-decoration-line-through">Rp {{ number_format($product->base_price, 0, ',', '.') }}</span>@endif
                </div>
            </template>
        </div>

        <div class="flex-grow-1">
            @if($product->has_variants && $product->variantTypes->isNotEmpty())
                @foreach($product->variantTypes as $type)
                    <div class="mb-3">
                        <div class="small fw-bold text-uppercase text-muted mb-2" style="letter-spacing:.08em">{{ $type->name }}</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($type->values as $val)
                                <button type="button" class="qv-variant" :class="{'selected': selected['{{ $type->name }}'] === '{{ $val->value }}'}" @click="selectVariant('{{ $type->name }}', '{{ $val->value }}')">{{ $val->value }}</button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif

            <div class="mb-3">
                <div class="small fw-bold text-uppercase text-muted mb-2" style="letter-spacing:.08em">Jumlah</div>
                <div class="qv-qty-wrap">
                    <button class="qv-qty-btn" type="button" @click="if(qty > minOrder) qty--">−</button>
                    <input type="number" class="border-0" style="width:50px;text-align:center;font-weight:700;background:transparent;outline:0" x-model.number="qty" :min="minOrder">
                    <button class="qv-qty-btn" type="button" @click="qty++">+</button>
                </div>
            </div>
        </div>

        <div class="pt-3 border-top mt-auto">
            @if(!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'superadmin']))
                <form action="{{ route('cart.add') }}" method="POST" class="w-100 m-0">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" :value="variantId">
                    <input type="hidden" name="quantity" :value="qty">
                    <div class="d-flex gap-2">
                        <button type="button" @click.prevent="addToCartQuickView($event)" class="btn btn-dark flex-grow-1 rounded-pill py-2 fw-bold" :disabled="{{ !$product->is_in_stock ? 'true' : 'false' }} || ({{ $product->has_variants ? 'true' : 'false' }} && !currentVariant)"><i class="bi bi-bag-plus me-1"></i>Tambah ke Keranjang</button>
                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-dark rounded-pill px-3" title="Detail penuh"><i class="bi bi-box-arrow-up-right"></i></a>
                    </div>
                </form>
            @else
                <a href="{{ route('products.show', $product->slug) }}" class="btn btn-outline-dark w-100 rounded-pill">Lihat Detail Produk</a>
            @endif
        </div>
    </div>
</div>
