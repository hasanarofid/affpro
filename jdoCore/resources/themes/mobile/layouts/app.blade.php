<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>@yield('title', app(\App\Services\SettingService::class)->storeName())</title>
 @hasSection('meta_description')
 <meta name="description" content="@yield('meta_description')">@endif

 @php
 $favicon = app(\App\Services\SettingService::class)->get('store_favicon');
 @endphp
 @if($favicon)
 <link rel="icon" type="image/png" href="{{ asset($favicon) }}">
 @else
 <link rel="icon" type="image/svg+xml"
 href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
 @endif

 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
 rel="stylesheet">
 <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

 <style>
 :root {
 --primary:
 {{ app(\App\Services\SettingService::class)->primaryColor() }}
 ;
 --secondary:
 {{ app(\App\Services\SettingService::class)->secondaryColor() }}
 ;
 --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
 }

 /* Mobile specific adjustments (applied universally on mobile theme) */
 .category-icon-wrap {
 width: 55px !important;
 height: 55px !important;
 margin-bottom: 0.5rem !important;
 }

 .category-icon-wrap i {
 font-size: 1.3rem !important;
 }

 .category-text {
 font-size: 0.75rem !important;
 }
 
 /* Force typography scale to mimic mobile */
 .app-wrapper {
 font-size: 0.9rem;
 }
 .app-wrapper h1, .app-wrapper .h1 { font-size: 1.8rem; }
 .app-wrapper h3, .app-wrapper .h3 { font-size: 1.3rem; }
 .app-wrapper h4, .app-wrapper .h4 { font-size: 1.1rem; }
 .app-wrapper h5, .app-wrapper .h5 { font-size: 1rem; }
 .app-wrapper h6, .app-wrapper .h6 { font-size: 0.9rem; }

 body {
 background-color: #f0f2f5 !important;
 }

 .app-wrapper {
 max-width: 480px;
 margin: 0 auto;
 background-color: #ffffff;
 min-height: 100vh;
 box-shadow: 0 0 20px rgba(0,0,0,0.1);
 position: relative;
 padding-bottom: 70px;
 overflow-x: hidden;
 }

 .app-wrapper .navbar-store {
 max-width: 480px;
 margin: 0 auto;
 }

 .bottom-nav {
 position: fixed;
 bottom: 0;
 left: 50%;
 transform: translateX(-50%);
 width: 100%;
 max-width: 480px;
 background: #fff;
 box-shadow: 0 -5px 15px rgba(0,0,0,0.05);
 display: flex;
 justify-content: space-around;
 padding: 10px 0;
 z-index: 1040;
 border-top-left-radius: 20px;
 border-top-right-radius: 20px;
 }

 .bottom-nav-item {
 text-align: center;
 color: #6c757d;
 text-decoration: none;
 font-size: 0.7rem;
 display: flex;
 flex-direction: column;
 align-items: center;
 gap: 4px;
 transition: all 0.2s;
 }

 .bottom-nav-item i {
 font-size: 1.3rem;
 margin-bottom: 2px;
 }

 .bottom-nav-item.active {
 color: var(--primary);
 font-weight: 600;
 }

 .bottom-nav-item:hover {
 color: var(--primary);
 }
 
 .floating-buttons-wrapper {
 position: fixed;
 bottom: 85px; /* Above bottom nav */
 left: 50%;
 transform: translateX(170px); /* Move towards the right side of the 480px container */
 z-index: 1030;
 display: flex;
 flex-direction: column;
 gap: 15px;
 }
 
 @media (max-width: 480px) {
 .floating-buttons-wrapper {
 transform: none;
 left: auto;
 right: 20px;
 }
 }
 </style>
 <link rel="stylesheet" href="{{ asset('themes/default/css/theme.css') }}">
 @yield('styles')
</head>

<body>
 <div class="app-wrapper">
 <!-- Navbar -->
 <nav class="navbar-store border-bottom sticky-top bg-white" id="navbarStore">
 <div class="container d-flex align-items-center justify-content-between py-2">
 <!-- Logo -->
 <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center">
 @php
 $logo = app(\App\Services\SettingService::class)->get('store_logo');
 @endphp
 @if($logo)
 <img src="{{ asset($logo) }}" alt="Logo" style="height: 32px; object-fit: contain;">
 @else
 <div class="d-flex align-items-center gap-2">
 <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
 style="width: 30px; height: 30px;">
 <i class="bi bi-lightning-charge-fill text-white small"></i>
 </div>
 <span class="fw-black fs-5 text-dark tracking-tight">{{ app(\App\Services\SettingService::class)->storeName() }}</span>
 </div>
 @endif
 </a>

 <!-- Icons & Account -->
 <div class="d-flex align-items-center gap-2">
 <a href="{{ route('products.index') }}" class="nav-action-btn icon-only text-dark">
 <i class="bi bi-search" style="font-size: 1.2rem;"></i>
 </a>
 @if(!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'superadmin']))
 <a href="{{ route('cart.index') }}" class="nav-action-btn icon-only position-relative text-dark ms-1">
 <i class="bi bi-cart3" style="font-size: 1.2rem;"></i>
 <span class="badge-indicator border border-2 border-white rounded-circle bg-danger text-white fw-bold" id="cart-count">0</span>
 </a>
 @endif
 </div>
 </div>
 </nav>

 <!-- Content -->
 @yield('content')

 <!-- Footer -->    <!-- Mobile App Footer -->
    <footer class="footer bg-white pt-4 pb-5 mt-4 border-top" style="margin-bottom: 70px;">
        <div class="container px-4 text-center">


            <!-- Quick Links -->
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-4">
                <a href="{{ route('home') }}" class="text-muted text-decoration-none small fw-medium">Beranda</a>
                <span class="text-muted opacity-25">•</span>
                <a href="{{ route('products.index') }}" class="text-muted text-decoration-none small fw-medium">Produk</a>
                <span class="text-muted opacity-25">•</span>
                <a href="{{ route('orders.track.index') }}" class="text-muted text-decoration-none small fw-medium">Lacak Pesanan</a>
                @foreach(\App\Models\Page::where('is_active', true)->where('category', 'footer_navigasi')->take(2)->get() as $p)
                    <span class="text-muted opacity-25">•</span>
                    <a href="{{ route('page.show', $p->slug) }}" class="text-muted text-decoration-none small fw-medium">{{ $p->title }}</a>
                @endforeach
            </div>

            <!-- Social Media -->
            <div class="d-flex justify-content-center gap-3 mb-4">
                @if($ig = app(\App\Services\SettingService::class)->get('social_instagram'))
                    <a href="{{ $ig }}" class="btn btn-light rounded-circle text-muted p-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" target="_blank"><i class="bi bi-instagram"></i></a>
                @endif
                @if($wa = app(\App\Services\SettingService::class)->get('social_whatsapp'))
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $wa) }}" class="btn btn-light rounded-circle text-muted p-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" target="_blank"><i class="bi bi-whatsapp"></i></a>
                @endif
                @if($email = app(\App\Services\SettingService::class)->get('social_email'))
                    <a href="mailto:{{ $email }}" class="btn btn-light rounded-circle text-muted p-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" target="_blank"><i class="bi bi-envelope"></i></a>
                @endif
            </div>

            <!-- Payments Banner -->
            <div class="d-flex flex-column align-items-center gap-3 mb-4 py-3 border-top border-bottom" style="border-color: rgba(0,0,0,0.05) !important;">
                <div class="small fw-semibold text-muted text-uppercase" style="letter-spacing: 1px; font-size: 0.65rem;">Mendukung Pembayaran</div>
                <div class="d-flex flex-wrap justify-content-center align-items-center gap-3" style="opacity: 0.7;">
                    <img src="{{ asset('assets/images/payments/bca.svg') }}" style="height: 12px;" alt="BCA" onerror="this.style.display='none'">
                    <img src="{{ asset('assets/images/payments/mandiri.svg') }}" style="height: 12px;" alt="Mandiri" onerror="this.style.display='none'">
                    <img src="{{ asset('assets/images/payments/dana.svg') }}" style="height: 12px;" alt="DANA" onerror="this.style.display='none'">
                    @if(file_exists(public_path('assets/images/payments/qris.svg')) || file_exists(public_path('assets/images/payments/qris.png')))
                        <img src="{{ asset(file_exists(public_path('assets/images/payments/qris.svg')) ? 'assets/images/payments/qris.svg' : 'assets/images/payments/qris.png') }}" alt="QRIS" style="height: 16px;">
                    @else
                        <i class="bi bi-shield-check fs-5 text-success"></i>
                    @endif
                </div>
            </div>

            <p class="text-muted mb-0" style="font-size: 0.75rem;">
                &copy; {{ date('Y') }} <span class="fw-bold">{{ app(\App\Services\SettingService::class)->storeName() }}</span>.<br>All rights reserved.
            </p>
        </div>
    </footer>


 <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
 <script>
 window.csrfToken = '{{ csrf_token() }}';

 // Navbar scroll effect
 window.addEventListener('scroll', function () {
 const navbar = document.getElementById('navbarStore');
 if (window.scrollY > 50) {
 navbar.classList.add('scrolled');
 } else {
 navbar.classList.remove('scrolled');
 }
 });

 $(document).ready(function () {
 function updateCartCount() {
 $.ajax({
 url: "{{ route('cart.count') }}",
 method: 'GET',
 success: function (response) {
 if($('#cart-count').length) $('#cart-count').text(response.count);
 if($('#bottom-cart-count').length) $('#bottom-cart-count').text(response.count);
 }
 });
 }
 updateCartCount();
 });
 </script>
 @yield('scripts')
 @stack('scripts')


 <!-- Floating Chat Button -->
 <div class="floating-buttons-wrapper">
 @php
 $unreadStoreChat = 0;
 if(auth()->check() && !auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
 $unreadStoreChat = \App\Models\Message::whereHas('conversation', function ($q) {
 $q->where('user_id', auth()->id());
 })
 ->where('sender_id', '!=', auth()->id())
 ->where('is_read', false)
 ->count();
 }
 @endphp
 <button type="button" class="btn btn-primary d-flex align-items-center justify-content-center shadow position-relative"
 style="width: 56px; height: 56px; border-radius: 50%; border: none; transition: transform 0.3s;"
 onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'"
 data-bs-toggle="modal" data-bs-target="#chatOptionsModal" aria-label="Chat">
 <i class="bi bi-chat-dots-fill" style="font-size: 24px;"></i>
 @if($unreadStoreChat > 0)
 <span class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger p-1" style="width:14px;height:14px; margin-left:-12px; margin-top:12px;">
 <span class="visually-hidden">Unread messages</span>
 </span>
 @endif
 </button>
 </div>

 <!-- Chat Options Modal -->
 <div class="modal fade" id="chatOptionsModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
 <div class="modal-dialog modal-dialog-centered modal-sm">
 <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
 <div class="modal-header border-0 pb-0 position-relative">
 <h6 class="modal-title fw-bold text-center w-100 mt-2">Pusat Bantuan</h6>
 <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
 </div>
 <div class="modal-body p-4 text-center">
 <p class="text-muted small mb-4">Pilih metode komunikasi untuk menghubungi kami.</p>
 <div class="d-grid gap-3">
 @auth
 <a href="{{ route('chat.index') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px; padding: 12px;">
 <i class="bi bi-chat-text-fill fs-5"></i> Live Chat Web
 @if($unreadStoreChat > 0)
 <span class="badge bg-danger ms-2 rounded-pill">{{ $unreadStoreChat }}</span>
 @endif
 </a>
 @else
 <a href="{{ route('login') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2" style="border-radius: 12px; padding: 12px;">
 <i class="bi bi-chat-text-fill fs-5"></i> Live Chat (Login)
 </a>
 @endauth

 @php
 $globalPhone = preg_replace('/[^0-9]/', '', app(\App\Services\SettingService::class)->get('store_phone', ''));
 if (str_starts_with($globalPhone, '0')) $globalPhone = '62' . substr($globalPhone, 1);
 @endphp
 @if($globalPhone)
 <a href="https://wa.me/{{ $globalPhone }}?text=Halo%20Admin%20{{ urlencode(app(\App\Services\SettingService::class)->storeName()) }},%20saya%20butuh%20bantuan."
 target="_blank" class="btn text-white d-flex align-items-center justify-content-center gap-2" style="background-color: #25D366; border-radius: 12px; padding: 12px; border: none;">
 <i class="bi bi-whatsapp fs-5"></i> WhatsApp
 </a>
 @endif
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Quick View / Add to Cart Modal -->
 <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-lg modal-dialog-centered">
 <div class="modal-content"
 style="border-radius:16px; border:none; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
 <div class="modal-header border-bottom-0 pb-0 position-absolute w-100 z-1"
 style="justify-content: flex-end;">
 <button type="button" class="btn-close bg-white rounded-circle shadow-sm m-3 p-2"
 data-bs-dismiss="modal" aria-label="Close"></button>
 </div>
 <div class="modal-body p-4" id="quickViewContent">
 <div class="text-center py-5 my-5">
 <div class="spinner-border text-primary" role="status">
 <span class="visually-hidden">Loading...</span>
 </div>
 <div class="mt-2 text-muted small fw-medium">Memuat informasi produk...</div>
 </div>
 </div>
 </div>
 </div>
 </div>

 <script>
 function openQuickView(slug) {
 const modalEl = document.getElementById('quickViewModal');
 let quickViewModal = bootstrap.Modal.getInstance(modalEl);
 if (!quickViewModal) quickViewModal = new bootstrap.Modal(modalEl);

 const contentEl = document.getElementById('quickViewContent');
 contentEl.innerHTML = `<div class="text-center py-5 my-5">
 <div class="spinner-border text-primary" role="status">
 <span class="visually-hidden">Loading...</span>
 </div>
 <div class="mt-2 text-muted small fw-medium">Memuat informasi produk...</div>
 </div>`;

 quickViewModal.show();

 fetch(`/products/${slug}/quick-view`)
 .then(response => {
 if (!response.ok) throw new Error('Network response was not ok');
 return response.text();
 })
 .then(html => {
 contentEl.innerHTML = html;
 })
 .catch(error => {
 contentEl.innerHTML = `<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-circle fs-1 mb-2"></i><p>Gagal memuat produk.</p></div>`;
 });
 }

 function initQuickView(variants, productId, minQty) {
 return {
 selected: {},
 variantId: null,
 currentVariant: null,
 variants: variants || [],
 qty: minQty || 1,
 minOrder: minQty || 1,
 selectVariant(type, value) {
 this.selected[type] = value;
 this.updateCurrent();
 },
 updateCurrent() {
 const label = Object.values(this.selected).join(' / ');
 this.currentVariant = this.variants.find(v => v.label === label);
 if (this.currentVariant) {
 this.variantId = this.currentVariant.id;
 if (this.currentVariant.image_path) {
 let mainImg = document.getElementById('quickProductImage');
 if (mainImg) {
 let filename = this.currentVariant.image_path.split('/').pop();
 mainImg.src = '/uploads/products/variants/' + filename;
 }
 }
 } else {
 this.variantId = null;
 }
 },
 formatRupiah(angka) {
 return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
 },
 addToCartQuickView(event) {
 let form = event.target.closest('form');
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
 const bottomCountEl = document.getElementById('bottom-cart-count');
 if (bottomCountEl) bottomCountEl.innerText = data.count;

 const modalEl = document.getElementById('quickViewModal');
 let quickViewModal = bootstrap.Modal.getInstance(modalEl);
 if (quickViewModal) quickViewModal.hide();

 const Toast = Swal.mixin({
 toast: true,
 position: "bottom-end",
 showConfirmButton: false,
 timer: 3000,
 timerProgressBar: true
 });
 Toast.fire({
 icon: "success",
 title: data.message || "Produk ditambahkan ke keranjang"
 });
 }
 })
 .catch(error => {
 console.error('Error:', error);
 });
 }
 }
 }
 </script>
 
 <!-- Bottom Tab Nav -->
 <div class="bottom-nav">
 <a href="{{ route('home') }}" class="bottom-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
 <i class="bi bi-house-door{{ request()->routeIs('home') ? '-fill' : '' }}"></i>
 <span>Beranda</span>
 </a>
 <a href="{{ route('products.index') }}" class="bottom-nav-item {{ request()->routeIs('products.*') && !request()->has('q') ? 'active' : '' }}">
 <i class="bi bi-grid{{ request()->routeIs('products.*') && !request()->has('q') ? '-fill' : '' }}"></i>
 <span>Produk</span>
 </a>
 <a href="{{ route('orders.track.index') }}" class="bottom-nav-item {{ request()->routeIs('orders.track.*') ? 'active' : '' }}">
 <i class="bi bi-truck"></i>
 <span>Lacak</span>
 </a>
 <a href="{{ route('cart.index') }}" class="bottom-nav-item {{ request()->routeIs('cart.*') ? 'active' : '' }} position-relative">
 <i class="bi bi-cart3"></i>
 <span>Keranjang</span>
 <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="bottom-cart-count" style="font-size: 0.55rem; padding: 0.25em 0.4em; transform: translate(-30%, -20%) !important;">0</span>
 </a>
 @auth
 @if(auth()->user()->hasAnyRole(['admin', 'superadmin']))
 <a href="{{ route('admin.dashboard') }}" class="bottom-nav-item">
 <i class="bi bi-speedometer2 text-danger"></i>
 <span class="text-danger fw-bold">Panel Admin</span>
 </a>
 @else
 <a href="{{ route('account.profile') }}" class="bottom-nav-item {{ request()->routeIs('account.*') ? 'active' : '' }}">
 <i class="bi bi-person{{ request()->routeIs('account.*') ? '-fill' : '' }}"></i>
 <span>Akun</span>
 </a>
 @endif
 @else
 <a href="{{ route('login') }}" class="bottom-nav-item">
 <i class="bi bi-box-arrow-in-right"></i>
 <span>Masuk</span>
 </a>
 @endauth
 </div>
 </div> <!-- Close app-wrapper -->
</body>

</html>