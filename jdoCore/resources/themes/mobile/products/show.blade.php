@extends('theme::layouts.app')
@section('title', $product->meta_title ?? $product->name)
@section('meta_description', $product->meta_description ?? Str::limit(strip_tags($product->description), 160))

@section('styles')
<style>
 .product-gallery-card { border: none; border-radius: 24px; overflow: hidden; box-shadow: 0 12px 30px rgba(0,0,0,0.03); }
 .product-info-card { border: none; border-radius: 24px; box-shadow: 0 12px 30px rgba(0,0,0,0.03); }
 .product-desc-card { border: none; border-radius: 24px; box-shadow: 0 12px 30px rgba(0,0,0,0.03); }
 .thumbnail-img { width: 75px; height: 75px; object-fit: cover; cursor: pointer; border-radius: 14px; border: 2px solid transparent; transition: all 0.3s ease; opacity: 0.6; }
 .thumbnail-img:hover { opacity: 0.9; transform: translateY(-2px); }
 .thumbnail-img.active { border-color: var(--primary); opacity: 1; box-shadow: 0 4px 12px rgba(var(--primary-rgb, 0,0,0), 0.15); }
 .variant-btn { border-radius: 12px; padding: 10px 20px; font-weight: 500; transition: all 0.3s; border: 2px solid #edf2f7; background: #f8fafc; color: #475569; }
 .variant-btn:hover { border-color: #cbd5e1; }
 .variant-btn.selected { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 6px 15px rgba(var(--primary-rgb, 0,0,0), 0.25); transform: translateY(-1px); }
 .html-content { line-height: 1.8; color: #334155; font-size: 1rem; letter-spacing: 0.01em; }
 .html-content h1, .html-content h2, .html-content h3 { font-weight: 700; color: #0f172a; margin-top: 1.5em; margin-bottom: 0.75em; }
 .html-content img { max-width: 100%; height: auto; border-radius: 16px; margin: 15px 0; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
 .html-content ul, .html-content ol { padding-left: 1.2em; margin-bottom: 1.2em; }
 .html-content li { margin-bottom: 0.5em; }
 .custom-qty-btn { background: #f1f5f9; border: none; width: 40px; height: 40px; border-radius: 10px !important; display: flex; align-items: center; justify-content: center; transition: all 0.2s; font-weight: bold; color: #475569; }
 .custom-qty-btn:hover { background: #e2e8f0; color: #0f172a; }
 .custom-qty-input { border: none !important; width: 50px; text-align: center; font-weight: 600; font-size: 1.1rem; color: #0f172a; background: transparent; padding: 0; box-shadow: none !important; }
 .qty-wrapper { border: 2px solid #edf2f7; border-radius: 14px; padding: 4px; display: inline-flex; align-items: center; background: white; }
 .share-btn { border-radius: 12px; transition: all 0.2s; font-weight: 600; letter-spacing: 0.5px; }
 .share-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.1) !important; filter: brightness(1.05); }
</style>
@endsection

@section('content')
 <div class="container py-4 pb-5">
 <nav aria-label="breadcrumb" class="mb-4">
 <ol class="breadcrumb small fw-medium text-uppercase" style="letter-spacing: 0.05em;">
 <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted hover-primary">Beranda</a></li>
 @if($product->category)
 <li class="breadcrumb-item"><a
 href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="text-decoration-none text-muted hover-primary">{{ $product->category->name }}</a>
 </li>@endif
 <li class="breadcrumb-item active text-dark">{{ Str::limit($product->name, 30) }}</li>
 </ol>
 </nav>

 <div class="row g-5" x-data="variantSelector()">
 <!-- Gallery -->
 <div>
 <div class="product-gallery-card bg-white position-relative">
 @if($product->images->isNotEmpty())
 <div id="productGallery" class="carousel slide" data-bs-ride="false">
 <div class="carousel-inner">
 @foreach($product->images as $i => $img)
 <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
 <img id="mainProductImage" src="{{ asset($img->path) }}"
 style="width:100%;height:450px;object-fit:cover;object-position:center;" alt="{{ $product->name }}">
 </div>
 @endforeach
 </div>
 @if($product->images->count() > 1)
 <button class="carousel-control-prev" type="button" data-bs-target="#productGallery"
 data-bs-slide="prev">
 <div class="bg-white text-dark rounded-circle shadow d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; opacity: 0.8;"><span class="carousel-control-prev-icon" style="filter: invert(1) grayscale(100); width: 1.2rem; height: 1.2rem;"></span></div>
 </button>
 <button class="carousel-control-next" type="button" data-bs-target="#productGallery"
 data-bs-slide="next">
 <div class="bg-white text-dark rounded-circle shadow d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; opacity: 0.8;"><span class="carousel-control-next-icon" style="filter: invert(1) grayscale(100); width: 1.2rem; height: 1.2rem;"></span></div>
 </button>
 @endif
 </div>
 <!-- Thumbnails -->
 @if($product->images->count() > 1)
 <div class="d-flex gap-3 p-4 bg-light align-items-center overflow-auto thumbnail-container">
 @foreach($product->images as $i => $img)
 <img src="{{ asset($img->path) }}" class="thumbnail-img {{ $i === 0 ? 'active' : '' }}"
 onclick="document.querySelector('#productGallery').querySelector('.carousel-item.active')?.classList.remove('active');document.querySelectorAll('#productGallery .carousel-item')[{{ $i }}].classList.add('active');this.parentNode.querySelectorAll('img').forEach(i=>i.classList.remove('active'));this.classList.add('active')">
 @endforeach
 </div>
 @endif
 @else
 <div class="bg-light d-flex align-items-center justify-content-center" style="height:450px">
 <i class="bi bi-image text-muted" style="font-size:5rem; opacity: 0.2;"></i>
 </div>
 @endif
 </div>
 </div>

 <!-- Product Info -->
 <div>
 <div class="product-info-card card bg-white">
 <div class="card-body p-4 ">
 <div class="d-flex align-items-center gap-3 mb-3">
 <div class="d-flex align-items-center px-3 py-1 bg-light rounded-pill">
 <span class="text-primary fw-medium small">{{ $product->category->name ?? '' }}</span>
 </div>
 @if($product->brand)
 <span class="text-muted small fw-medium"><i class="bi bi-award me-1"></i>{{ $product->brand->name }}</span>
 @endif
 @if($product->type === 'digital')
 <span class="badge bg-success px-3 py-2 rounded-pill" style="font-size:0.7rem; letter-spacing: 0.5px;">PRODUK DIGITAL</span>
 @endif
 </div>
 
  <h1 class="fw-bold text-dark mb-4" style="font-size: clamp(1.4rem, 6vw, 2rem); line-height: 1.3; letter-spacing: -0.02em;">{{ $product->name }}</h1>

 <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom">
 <div class="d-flex align-items-center gap-1">
 <i class="bi bi-star-fill text-warning"></i>
 <span class="fw-bold">5.0</span>
 <span class="text-muted small ms-1">(0 Ulasan)</span>
 </div>
 <div class="text-muted">|</div>
 <div class="text-muted small"><span class="fw-bold text-dark">{{ $product->sold_count }}</span> Terjual</div>
 </div>

 <div class="mb-4">
 <template x-if="currentVariant">
 <span class="fw-black"
 style="font-size:2.5rem; background:var(--gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; letter-spacing: -1px;"
 x-text="formatRupiah(currentVariant.price)"></span>
 </template>
 <template x-if="!currentVariant">
 <div class="d-flex align-items-end gap-3 flex-wrap">
 <span class="fw-black"
 style="font-size:2.5rem; background:var(--gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; letter-spacing: -1px; line-height: 1;">
 Rp {{ number_format($product->discount_price ?? $product->effective_price, 0, ',', '.') }}
 </span>
 @if($product->discount_price)
 <div class="text-muted text-decoration-line-through fw-medium" style="font-size:1.1rem; padding-bottom: 6px;">
 Rp {{ number_format($product->base_price, 0, ',', '.') }}
 </div>
 @php
 $discountPercentage = round((($product->base_price - $product->discount_price) / $product->base_price) * 100);
 @endphp
 <div class="badge bg-danger-subtle text-danger px-2 py-1 mb-2 fw-bold">{{ $discountPercentage }}% OFF</div>
 @endif
 </div>
 </template>
 @if($product->has_variants)<div class="text-muted small mt-1 fw-medium" x-show="!currentVariant">*Pilih variasi untuk harga spesifik</div>@endif
 </div>

 <!-- Variant Selection (Alpine.js) -->
 @if($product->has_variants && $product->variantTypes->isNotEmpty())
 <div class="mb-4 pt-2">
 @foreach($product->variantTypes as $type)
 <div class="mb-3">
 <label class="form-label small fw-bold text-uppercase tracking-widest text-muted">{{ $type->name }}</label>
 <div class="d-flex flex-wrap gap-2">
 @foreach($type->values as $val)
 <button type="button" class="btn variant-btn"
 :class="{'selected': selected['{{ $type->name }}'] === '{{ $val->value }}'}"
 @click="selectVariant('{{ $type->name }}', '{{ $val->value }}')">
 {{ $val->value }}
 </button>
 @endforeach
 </div>
 </div>
 @endforeach
 <input type="hidden" name="variant_id" x-model="variantId">
 </div>
 @endif

 <div class="d-flex gap-4 mb-4 align-items-end flex-wrap">
 <!-- Quantity -->
 <div>
 <label class="form-label small fw-bold text-uppercase tracking-widest text-muted">Akurasi Jumlah</label>
 <div class="qty-wrapper">
 <button class="custom-qty-btn" type="button"
 onclick="let i=document.getElementById('qty');if(i.value>{{ $product->min_order }})i.value--"><i class="bi bi-dash"></i></button>
 <input type="number" id="qty" class="custom-qty-input"
 value="{{ $product->min_order }}" min="{{ $product->min_order }}">
 <button class="custom-qty-btn" type="button"
 onclick="document.getElementById('qty').value++"><i class="bi bi-plus"></i></button>
 </div>
 @if($product->min_order > 1)
 <div class="text-danger mt-2 fw-medium" style="font-size:0.75rem"><i class="bi bi-info-circle me-1"></i>Min. Beli: {{ $product->min_order }}</div>
 @endif
 </div>

 <!-- Stock -->
 <div class="mb-2">
 @if($product->is_in_stock)
 <template x-if="currentVariant">
 <div class="d-flex align-items-center gap-2 bg-success-subtle text-success px-3 py-2 rounded-pill fw-medium" style="font-size: 0.9rem;">
 <i class="bi bi-check-circle-fill"></i>
 <span x-text="'Tersedia ' + currentVariant.stock + ' item'"></span>
 </div>
 </template>
 <template x-if="!currentVariant">
 <div class="d-flex align-items-center gap-2 bg-success-subtle text-success px-3 py-2 rounded-pill fw-medium" style="font-size: 0.9rem;">
 <i class="bi bi-check-circle-fill"></i>
 <span>Stok Tersedia</span>
 </div>
 </template>
 @else
 <div class="d-flex align-items-center gap-2 bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-medium" style="font-size: 0.9rem;">
 <i class="bi bi-x-circle-fill"></i>
 <span>Stok Habis</span>
 </div>
 @endif
 </div>
 </div>

 <!-- Actions -->
 <div class="pt-4 border-top mt-2">
 @if(!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'superadmin']))
 <form id="addToCartForm" action="{{ route('cart.add') }}" method="POST">
 @csrf
 <input type="hidden" name="product_id" value="{{ $product->id }}">
 <input type="hidden" name="variant_id" :value="variantId">
 <input type="hidden" name="quantity" id="qty-hidden" value="1">
 <div class="d-flex gap-3 flex-column ">
 <button type="button" @click="addToCart()" class="btn btn-primary flex-grow-1 py-3 fw-bold fs-6 shadow text-white"
 style="border-radius:16px;"
 :disabled="{{ !$product->is_in_stock ? 'true' : 'false' }} || ({{ $product->has_variants ? 'true' : 'false' }} && !currentVariant)">
 <i class="bi bi-cart-plus-fill me-2 fs-5"></i> Masukkan Keranjang
 </button>
 
 @php
 $phone = preg_replace('/[^0-9]/', '', app(\App\Services\SettingService::class)->get('store_phone', ''));
 if (str_starts_with($phone, '0'))
 $phone = '62' . substr($phone, 1);
 $waText = urlencode("Halo Admin, saya ingin bertanya tentang produk: *" . $product->name . "*");
 @endphp
 
 <div class="d-flex gap-2 justify-content-center">
 @if($phone)
 <a href="https://wa.me/{{ $phone }}?text={{ $waText }}" target="_blank"
 class="btn py-3 px-4 d-flex align-items-center justify-content-center text-white font-weight-bold" style="border-radius:16px; background-color: #25D366; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);"
 title="Tanya via WhatsApp">
 <i class="fab fa-whatsapp fs-4"></i>
 </a>
 @endif
 </div>
 </div>
 </form>
 <form action="{{ route('chat.store') }}" method="POST" class="mt-2 w-100 d-none " id="webChatForm">
 @csrf
 <input type="hidden" name="message"
 value="Halo Admin, saya tertarik dengan: {{ $product->name }} ({{ route('products.show', $product->slug) }})">
 </form>
 <button type="button" onclick="document.getElementById('webChatForm').submit();"
 class="btn btn-outline-primary w-100 py-3 mt-3 fw-bold d-flex align-items-center justify-content-center"
 style="border-radius:16px; border-width: 2px;" title="Chat di Web">
 <i class="bi bi-chat-text-fill me-2 fs-5"></i> Chat dengan Admin
 </button>
 @else
 <div class="alert alert-info border-0 p-4 d-flex align-items-center gap-3"
 style="border-radius:20px; background:#f0f9ff; color:#0369a1;">
 <div class="bg-white rounded-circle p-2 shadow-sm d-flex align-items-center flex-shrink-0">
 <i class="bi bi-shield-check fs-4"></i>
 </div>
 <div>
 <h6 class="fw-bold mb-1">Mode Admin Aktif</h6>
 <p class="mb-0 small">Anda melihat halaman produk ini sebagai pengelola. Interaksi pembelian dinonaktifkan.</p>
 </div>
 </div>
 @endif
 </div>

 </div>
 </div>
 </div>

 <!-- Bottom Content (Desc & Wholesale) -->
 <div class="col-12 mt-5">
 <div class="row g-5">
 <div>
 @if($product->description)
 <div class="product-desc-card card bg-white">
 <div class="card-body p-4 ">
 <h4 class="fw-bold text-dark mb-4 pb-3 border-bottom d-flex align-items-center">
 <i class="bi bi-file-text text-primary me-2"></i> Detail Produk
 </h4>
 <!-- HTML FORMAT -->
 <div class="html-content">{!! $product->description !!}</div>
 </div>
 </div>
 @else
 <div class="product-desc-card card bg-white text-center py-5">
 <i class="bi bi-card-text text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
 <p class="text-muted fw-medium">Tidak ada deskripsi rinci untuk produk ini.</p>
 </div>
 @endif
 </div>
 
 <div>
 <!-- Wholesale Prices -->
 @if($product->wholesalePrices->isNotEmpty())
 <div class="product-desc-card card bg-gradient-primary text-white mb-4" style="background: var(--gradient);">
 <div class="card-body p-4">
 <h5 class="fw-bold mb-4 d-flex align-items-center">
 <i class="bi bi-tags-fill me-2 opacity-75"></i> Harga Spesial Grosir
 </h5>
 <div class="d-flex flex-column gap-3">
 @foreach($product->wholesalePrices as $wp)
 <div class="d-flex justify-content-between align-items-center p-3 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-25">
 <div class="fw-bold fs-6">≥ {{ $wp->min_qty }} <span class="fw-normal small">pcs</span></div>
 <div class="fw-bold fs-5 text-warning">Rp {{ number_format($wp->price, 0, ',', '.') }}</div>
 </div>
 @endforeach
 </div>
 </div>
 </div>
 @endif

 <!-- Social Sharing -->
 @php
 $shareUrl = route('products.show', $product->slug);
 if (auth()->check() && auth()->user()->referral_code) {
 $shareUrl .= '?ref=' . auth()->user()->referral_code;
 }
 $shareText = urlencode("Beli " . $product->name . " di " . config('app.name', 'JadiOrder') . "!");
 @endphp
 <div class="product-desc-card card bg-light border-0">
 <div class="card-body p-4">
 <h6 class="fw-bold text-dark mb-3">Bagikan Produk</h6>
 <div class="d-flex gap-2 flex-wrap">
 <a href="https://wa.me/?text={{ $shareText }}%20{{ urlencode($shareUrl) }}" target="_blank"
 class="share-btn btn text-white flex-grow-1"
 style="background-color: #25D366;">
 <i class="bi bi-whatsapp"></i>
 </a>
 <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
 target="_blank" class="share-btn btn text-white flex-grow-1"
 style="background-color: #1877F2;">
 <i class="bi bi-facebook"></i>
 </a>
 <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ $shareText }}"
 target="_blank" class="share-btn btn text-white flex-grow-1"
 style="background-color: #0088cc;">
 <i class="bi bi-telegram"></i>
 </a>
 <button onclick="copyProductLink('{{ $shareUrl }}')" id="copyProductBtn"
 class="share-btn btn bg-white border text-dark flex-grow-1">
 <i class="bi bi-link-45deg"></i>
 </button>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
@endsection

@section('scripts')
 <script>
 function variantSelector() {
 return {
 selected: {},
 variantId: null,
 currentVariant: null,
 variants: {!! $product->variants->toJson() !!},
 selectVariant(type, value) {
 this.selected[type] = value;
 this.updateCurrent();
 },
 updateCurrent() {
 const selectedValues = Object.values(this.selected);
 const totalTypes = {{ $product->variantTypes->count() }};

 if (selectedValues.length < totalTypes) {
 this.currentVariant = null;
 this.variantId = null;
 return;
 }

 this.currentVariant = this.variants.find(v => {
 if (!v.label) return false;
 const variantValues = v.label.split(' / ');
 return selectedValues.length === variantValues.length && 
 selectedValues.every(val => variantValues.includes(val));
 });
 if (this.currentVariant) {
 this.variantId = this.currentVariant.id;
 document.querySelector('input[name="product_id"]').value = {{ $product->id }}; // Fallback just in case

 if (this.currentVariant.image_path) {
 let mainImg = document.getElementById('mainProductImage');
 if (mainImg) {
 // Extract filename or prepend if relative
 let filename = this.currentVariant.image_path.split('/').pop();
 mainImg.src = '/uploads/products/variants/' + filename;
 }
 }
 } else {
 this.variantId = null;
 }
 },
 addToCart() {
 let form = document.getElementById('addToCartForm');
 let formData = new FormData(form);

 fetch(form.action, {
 method: 'POST',
 body: formData,
 headers: {
 'X-Requested-With': 'XMLHttpRequest',
 'Accept': 'application/json'
 }
 })
 .then(response => response.json())
 .then(data => {
 if (data.success) {
 const countEl = document.getElementById('cart-count');
 if (countEl) countEl.innerText = data.count;

 const Toast = Swal.mixin({
 toast: true,
 position: "bottom-end",
 showConfirmButton: false,
 timer: 3000,
 timerProgressBar: true
 });
 Toast.fire({
 icon: "success",
 title: "<span style='font-weight:600'>" + (data.message || "Berhasil masuk keranjang!") + "</span>"
 });
 }
 })
 .catch(error => {
 console.error('Error:', error);
 });
 },
 formatRupiah(angka) {
 return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
 }
 }
 }
 document.getElementById('qty')?.addEventListener('change', function () {
 if (this.value < {{ $product->min_order }}) this.value = {{ $product->min_order }};
 document.getElementById('qty-hidden').value = this.value;
 });
 function copyProductLink(text) {
 const btn = document.getElementById('copyProductBtn');
 const originalHtml = btn.innerHTML;

 navigator.clipboard.writeText(text).then(() => {
 btn.innerHTML = '<i class="bi bi-check2"></i>';
 btn.classList.add('bg-success', 'text-white', 'border-success');

 setTimeout(() => {
 btn.innerHTML = originalHtml;
 btn.classList.remove('bg-success', 'text-white', 'border-success');
 }, 2000);
 });
 }
 </script>
@endsection