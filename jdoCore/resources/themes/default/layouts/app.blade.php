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

        html, body {
            overflow-x: hidden;
            width: 100%;
            position: relative;
            margin: 0;
            padding: 0;
            touch-action: pan-y; /* Prevent accidental horizontal swipes if needed */
        }

        /* Prevent potential horizontal overflow from rows */
        .container, .container-fluid {
            overflow: hidden;
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
        }

        .row {
            margin-right: -15px;
            margin-left: -15px;
        }

        @media (min-width: 1200px) {
            .container {
                overflow: visible; /* Restore for tooltips/dropdowns on desktop */
                padding-right: var(--bs-gutter-x, 0.75rem);
                padding-left: var(--bs-gutter-x, 0.75rem);
            }
            .row {
                margin-right: calc(-.5 * var(--bs-gutter-x));
                margin-left: calc(-.5 * var(--bs-gutter-x));
            }
        }

        .navbar-store {
            width: 100%;
            left: 0;
            right: 0;
            transform: translateZ(0); /* Force GPU acceleration for stability */
            -webkit-transform: translateZ(0);
            backface-visibility: hidden;
            transition: none !important; /* Disable transitions for mobile stability during scroll */
        }

        @media (min-width: 992px) {
            .navbar-store {
                transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1) !important;
            }
        }

        /* Mobile specific adjustments */
        @media (max-width: 767.98px) {
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

            /* Bottom Nav */
            .bottom-nav {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100% !important;
                height: 70px;
                background: rgba(255, 255, 255, 0.98) !important;
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                display: flex;
                justify-content: space-around;
                align-items: center;
                border-top: 1px solid rgba(0, 0, 0, 0.05);
                z-index: 1040;
                padding-bottom: env(safe-area-inset-bottom);
                transform: translateZ(0); /* Stabilize */
                -webkit-transform: translateZ(0);
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
                bottom: 85px !important;
                right: 20px !important;
            }

            /* Adjust footer for bottom nav */
            footer.footer {
                margin-bottom: 80px !important;
            }
        }

        .floating-buttons-wrapper {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('themes/default/css/theme.css') }}">
    @yield('styles')
</head>

<body>
    <!-- Top Bar -->
    <div class="top-bar d-none d-md-block">
        <div class="container d-flex justify-content-between">
            <div>
                <i class="bi bi-megaphone me-1"></i> Gratis Ongkir keseluruh Indonesia S&K berlaku!
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('orders.track.index') }}" class="text-white text-decoration-none small">Lacak
                    Pesanan</a>
                <a href="{{ route('blog.index') }}" class="text-white text-decoration-none small">Berita
                    Terbaru</a>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="navbar-store" id="navbarStore">
        <div class="container d-flex align-items-center">
            <!-- Mobile Toggle -->
            <button class="navbar-toggler d-lg-none border-0 shadow-none ps-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#navContent">
                <i class="bi bi-list fs-3"></i>
            </button>

            <!-- Logo -->
            <a href="{{ route('home') }}" class="navbar-brand me-2 me-lg-4 d-flex align-items-center">
                @php
                    $logo = app(\App\Services\SettingService::class)->get('store_logo');
                @endphp
                @if($logo)
                    <img src="{{ asset($logo) }}" alt="Logo" style="height: 38px; object-fit: contain;">
                @else
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 35px; height: 35px;">
                            <i class="bi bi-lightning-charge-fill text-white small"></i>
                        </div>
                        <span
                            class="fw-black fs-4 text-dark tracking-tight">{{ app(\App\Services\SettingService::class)->storeName() }}</span>
                    </div>
                @endif
            </a>

            <!-- Search -->
            <div class="flex-grow-1 d-none d-md-block px-lg-4">
                <form action="{{ route('products.index') }}" method="GET">
                    <div class="search-box-custom d-flex align-items-center">
                        <i class="bi bi-search text-muted me-2" style="font-size: 0.9rem;"></i>
                        <input type="text" name="q" class="border-0 bg-transparent shadow-none w-100 py-1"
                            placeholder="Cari produk favoritmu..." value="{{ request('q') }}"
                            style="font-size: 0.85rem; outline: none;">
                    </div>
                </form>
            </div>

            <!-- Icons & Account -->
            <div class="d-flex align-items-center gap-2 gap-md-3 ms-auto">
                @if(!auth()->check() || !auth()->user()->hasAnyRole(['admin', 'superadmin']))
                    <a href="{{ route('cart.index') }}" class="nav-action-btn icon-only position-relative">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span
                            class="badge-indicator border border-2 border-white rounded-circle bg-danger text-white fw-bold"
                            id="cart-count">0</span>
                    </a>
                @endif

                @auth
                    <div class="dropdown">
                        <a href="#" class="nav-action-btn ps-2 pe-3" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                style="width: 28px; height: 28px;">
                                <span class="fw-bold small"
                                    style="font-size: 0.75rem;">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            </div>
                            <span class="d-none d-md-inline small text-truncate"
                                style="max-width: 100px;">{{ strtok(auth()->user()->name, ' ') }}</span>
                            <i class="bi bi-chevron-down small text-muted d-none d-md-inline ms-1"
                                style="font-size: 0.7rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-2"
                            style="border-radius: 16px; min-width: 220px;">
                            <li class="px-3 py-3 border-bottom mb-2 bg-light rounded-4">
                                <div class="fw-bold small text-dark truncate-1">{{ auth()->user()->name }}</div>
                                <div class="text-muted truncate-1" style="font-size:.7rem">{{ auth()->user()->email }}</div>
                            </li>

                            @if(!auth()->user()->hasAnyRole(['admin', 'superadmin']))
                                <li><a class="dropdown-item py-2 small rounded-3" href="{{ route('account.orders') }}"><i
                                            class="bi bi-bag-check me-2"></i>Pesanan Saya</a></li>
                                <li><a class="dropdown-item py-2 small rounded-3" href="{{ route('account.wallet') }}"><i
                                            class="bi bi-wallet2 me-2"></i>Saldo Akun</a></li>
                                <li><a class="dropdown-item py-2 small rounded-3" href="{{ route('account.affiliate') }}"><i
                                            class="bi bi-people me-2"></i>Affiliate</a></li>
                                <li>
                                    <hr class="dropdown-divider opacity-50">
                                </li>
                                <li><a class="dropdown-item py-2 small rounded-3" href="{{ route('account.profile') }}"><i
                                            class="bi bi-gear me-2"></i>Profil & Pengaturan</a></li>
                            @else
                                <li><a class="dropdown-item py-2 small rounded-3 bg-primary-subtle text-primary fw-bold"
                                        href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Admin
                                        Dashboard</a></li>
                            @endif
                            <li>
                                <hr class="dropdown-divider opacity-50">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">@csrf
                                    <button class="dropdown-item py-2 small text-danger rounded-3"><i
                                            class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="nav-action-btn nav-action-btn-primary ms-1" title="Masuk">
                        <i class="bi bi-box-arrow-in-right fs-5"></i>
                        <span class="d-none d-md-inline small">Masuk</span>
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Menu -->
    <div class="collapse d-lg-none bg-white border-bottom shadow-sm" id="navContent">
        <div class="container py-3">
            <div class="d-flex flex-column gap-3">
                <!-- Mobile Search -->
                <form action="{{ route('products.index') }}" method="GET" class="mb-2">
                    <div class="search-box-custom d-flex align-items-center">
                        <i class="bi bi-search text-muted me-2"></i>
                        <input type="text" name="q" class="border-0 bg-transparent shadow-none w-100 py-1"
                            placeholder="Cari produk..." value="{{ request('q') }}">
                    </div>
                </form>
                <a href="{{ route('home') }}" class="fw-bold text-dark text-decoration-none py-1">Beranda</a>
                <a href="{{ route('products.index') }}" class="fw-bold text-dark text-decoration-none py-1">Semua
                    Produk</a>
                <a href="{{ route('products.index', ['sort' => 'popular']) }}"
                    class="fw-bold text-dark text-decoration-none py-1">Produk Populer</a>
                <a href="{{ route('products.index', ['sort' => 'new']) }}"
                    class="fw-bold text-dark text-decoration-none py-1">Terbaru</a>
            </div>
        </div>
    </div>


    <!-- Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="footer bg-white pt-5 pb-4 mt-5 border-top"
        style="border-top: 1px solid rgba(0,0,0,0.05) !important;">
        <div class="container">
            <div class="row g-5 mb-5 text-start">
                <!-- Branding & Social -->
                <div class="col-lg-4">
                    <a href="{{ route('home') }}"
                        class="d-inline-flex align-items-center mb-4 text-dark text-decoration-none">
                        @php
                            $logo = app(\App\Services\SettingService::class)->get('store_logo');
                        @endphp
                        @if($logo)
                            <img src="{{ asset($logo) }}" alt="Logo" style="height: 42px; object-fit: contain;">
                        @else
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2"
                                style="width: 32px; height: 32px;">
                                <i class="bi bi-shop text-white" style="font-size: 0.9rem;"></i>
                            </div>
                            <span
                                class="fw-bold fs-4 tracking-tight">{{ app(\App\Services\SettingService::class)->storeName() }}</span>
                        @endif
                    </a>
                    <p class="text-muted small mb-4 pe-lg-5" style="line-height: 1.8;">
                        {{ app(\App\Services\SettingService::class)->get('store_description', 'Destinasi belanja online terpercaya dengan kurasi produk terbaik untuk gaya hidup modern Anda.') }}
                    </p>
                    <div class="d-flex gap-2">
                        @if($ig = app(\App\Services\SettingService::class)->get('social_instagram'))
                            <a href="{{ $ig }}" class="social-icon bg-light text-muted" title="Instagram" target="_blank"><i
                                    class="bi bi-instagram"></i></a>
                        @endif
                        @if($wa = app(\App\Services\SettingService::class)->get('social_whatsapp'))
                            @php
                                $waUrl = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $wa);
                            @endphp
                            <a href="{{ $waUrl }}" class="social-icon bg-light text-muted" title="WhatsApp"
                                target="_blank"><i class="bi bi-whatsapp"></i></a>
                        @endif
                        @if($fb = app(\App\Services\SettingService::class)->get('social_facebook'))
                            <a href="{{ $fb }}" class="social-icon bg-light text-muted" title="Facebook" target="_blank"><i
                                    class="bi bi-facebook"></i></a>
                        @endif
                        @if($email = app(\App\Services\SettingService::class)->get('social_email'))
                            <a href="mailto:{{ $email }}" class="social-icon bg-light text-muted" title="Email"
                                target="_blank"><i class="bi bi-envelope"></i></a>
                        @endif
                    </div>
                </div>

                <!-- Explorations -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="fw-bold text-dark mb-4 small tracking-widest text-uppercase">Navigasi</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-3">
                        <li><a href="{{ route('home') }}"
                                class="text-muted text-decoration-none hover-primary">Beranda</a></li>
                        <li><a href="{{ route('products.index') }}"
                                class="text-muted text-decoration-none hover-primary">Daftar Produk</a></li>
                        <li><a href="{{ route('blog.index') }}"
                                class="text-muted text-decoration-none hover-primary">Berita Terbaru</a></li>
                    </ul>
                </div>

                <!-- Information -->
                <div class="col-6 col-md-3 col-lg-3">
                    <h6 class="fw-bold text-dark mb-4 small tracking-widest text-uppercase">Informasi</h6>
                    <ul class="list-unstyled small d-flex flex-column gap-3">
                        @foreach(\App\Models\Page::where('is_active', true)->where('category', 'footer_navigasi')->get() as $p)
                            <li><a href="{{ route('page.show', $p->slug) }}"
                                    class="text-muted text-decoration-none hover-primary">{{ $p->title }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Store Context -->
                <div class="col-12 col-md-6 col-lg-3">
                    <h6 class="fw-bold text-dark mb-4 small tracking-widest text-uppercase">Kontak & Waktu</h6>
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 42px; height: 42px;">
                                <i class="bi bi-chat-left-dots-fill"></i>
                            </div>
                            <div class="small">
                                <div class="text-muted x-small">Customer Service</div>
                                <div class="fw-bold text-dark">
                                    {{ app(\App\Services\SettingService::class)->get('store_phone', '085691257411') }}
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-light text-warning rounded-circle d-flex align-items-center justify-content-center"
                                style="width: 42px; height: 42px;">
                                <i class="bi bi-clock-fill"></i>
                            </div>
                            <div class="small">
                                <div class="text-muted x-small">Jam Operasional</div>
                                <div class="fw-bold text-dark">
                                    {{ app(\App\Services\SettingService::class)->get('operational_hours', 'Senin - Sabtu (08:00 - 17:00 WIB)') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-top">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-4 text-center text-lg-start">
                        <p class="text-muted x-small mb-0">
                            &copy; {{ date('Y') }} <span
                                class="fw-bold">{{ app(\App\Services\SettingService::class)->storeName() }}</span>.
                            Made with <i class="bi bi-heart-fill text-danger mx-1"></i> for your best shopping
                            experience.
                        </p>
                    </div>
                    <div class="col-lg-8">
                        <div
                            class="d-flex flex-column flex-md-row justify-content-center justify-content-lg-end align-items-center gap-4">
                            <!-- Shipping -->
                            <div class="d-flex align-items-center gap-3">
                                <span class="x-small text-muted text-uppercase tracking-widest fw-bold"
                                    style="font-size: 0.6rem;">Kurir</span>
                                <div class="d-flex flex-wrap gap-3 align-items-center px-3 py-2 bg-light rounded-pill border border-white shadow-sm"
                                    style="opacity: 0.9;">
                                    @php
                                        $enabledCouriersRaw = app(\App\Services\SettingService::class)->get('enabled_couriers', ['jne', 'pos', 'tiki']);
                                        $enabledCouriers = is_string($enabledCouriersRaw) ? json_decode($enabledCouriersRaw, true) : $enabledCouriersRaw;
                                        if (!is_array($enabledCouriers))
                                            $enabledCouriers = [];
                                        $enabledCouriers = array_map('strtolower', $enabledCouriers);


                                        $courierLogos = [
                                            'jne' => 'jne.png',
                                            'jnt' => 'jnt.png',
                                            'sicepat' => 'sicepat.png',
                                            'pos' => 'pos.png',
                                            'tiki' => 'tiki.png',
                                            'anteraja' => 'anteraja.png',
                                            'ninja' => 'ninja.png',
                                            'lion' => 'lion.png',
                                            'wahana' => 'wahana.png',
                                            'sap' => 'sap.png',
                                            'rpx' => 'rpx.png',
                                            'ide' => 'ide.png'
                                        ];
                                    @endphp

                                    @foreach($enabledCouriers as $code)
                                        @if(isset($courierLogos[$code]) && file_exists(public_path('assets/images/shipping/' . $courierLogos[$code])))
                                            <img src="{{ asset('assets/images/shipping/' . $courierLogos[$code]) }}"
                                                alt="{{ strtoupper($code) }}"
                                                style="height: 14px; filter: grayscale(1) opacity(0.7);"
                                                onmouseover="this.style.filter='none'; this.style.opacity='1'"
                                                onmouseout="this.style.filter='grayscale(1) opacity(0.7)'"
                                                title="{{ strtoupper($code) }}">
                                        @endif
                                    @endforeach

                                    @if(empty($enabledCouriers))
                                        <span class="x-small text-muted">Belum diatur</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Payment -->
                            <div class="d-flex align-items-center gap-3">
                                <span class="x-small text-muted text-uppercase tracking-widest fw-bold"
                                    style="font-size: 0.6rem;">Keamanan Pembayaran</span>
                                <div class="d-flex flex-wrap gap-3 align-items-center px-3 py-2 bg-light rounded-pill border border-white shadow-sm"
                                    style="opacity: 0.9;">
                                    <img src="{{ asset('assets/images/payments/bca.svg') }}" alt="BCA"
                                        style="height: 14px; filter: grayscale(1) opacity(0.7);"
                                        onmouseover="this.style.filter='none'; this.style.opacity='1'"
                                        onmouseout="this.style.filter='grayscale(1) opacity(0.7)'">
                                    <img src="{{ asset('assets/images/payments/mandiri.svg') }}" alt="Mandiri"
                                        style="height: 12px; filter: grayscale(1) opacity(0.7);"
                                        onmouseover="this.style.filter='none'; this.style.opacity='1'"
                                        onmouseout="this.style.filter='grayscale(1) opacity(0.7)'">
                                    <img src="{{ asset('assets/images/payments/dana.svg') }}" alt="DANA"
                                        style="height: 12px; filter: grayscale(1) opacity(0.7);"
                                        onmouseover="this.style.filter='none'; this.style.opacity='1'"
                                        onmouseout="this.style.filter='grayscale(1) opacity(0.7)'">
                                    <img src="{{ asset('assets/images/payments/ovo.svg') }}" alt="OVO"
                                        style="height: 12px; filter: grayscale(1) opacity(0.7);"
                                        onmouseover="this.style.filter='none'; this.style.opacity='1'"
                                        onmouseout="this.style.filter='grayscale(1) opacity(0.7)'">
                                    @if(file_exists(public_path('assets/images/payments/qris.svg')) || file_exists(public_path('assets/images/payments/qris.png')))
                                        <img src="{{ asset(file_exists(public_path('assets/images/payments/qris.svg')) ? 'assets/images/payments/qris.svg' : 'assets/images/payments/qris.png') }}"
                                            alt="QRIS" style="height: 16px; filter: grayscale(1) opacity(0.7);"
                                            onmouseover="this.style.filter='none'; this.style.opacity='1'"
                                            onmouseout="this.style.filter='grayscale(1) opacity(0.7)'">
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
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
                        $('#cart-count').text(response.count);
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
            if (auth()->check() && !auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
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
                <span class="position-absolute top-0 start-100 translate-middle badge border border-light rounded-circle bg-danger p-1"
                    style="width:14px;height:14px; margin-left:-12px; margin-top:12px;">
                    <span class="visually-hidden">Unread messages</span>
                </span>
            @endif
        </button>
    </div>

    <!-- Chat Options Modal -->
    <div class="modal fade" id="chatOptionsModal" tabindex="-1" aria-hidden="true" style="z-index: 1061;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                <div class="modal-header border-0 pb-0 position-relative">
                    <h6 class="modal-title fw-bold text-center w-100 mt-2">Pusat Bantuan</h6>
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <p class="text-muted small mb-4">Pilih metode komunikasi untuk menghubungi kami.</p>
                    <div class="d-grid gap-3">
                        @auth
                            <a href="{{ route('chat.index') }}"
                                class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2"
                                style="border-radius: 12px; padding: 12px;">
                                <i class="bi bi-chat-text-fill fs-5"></i> Live Chat Web
                                @if($unreadStoreChat > 0)
                                    <span class="badge bg-danger ms-2 rounded-pill">{{ $unreadStoreChat }}</span>
                                @endif
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                                class="btn btn-outline-primary d-flex align-items-center justify-content-center gap-2"
                                style="border-radius: 12px; padding: 12px;">
                                <i class="bi bi-chat-text-fill fs-5"></i> Live Chat (Login)
                            </a>
                        @endauth

                        @php
                            $globalPhone = preg_replace('/[^0-9]/', '', app(\App\Services\SettingService::class)->get('store_phone', ''));
                            if (str_starts_with($globalPhone, '0')) $globalPhone = '62' . substr($globalPhone, 1);
                        @endphp

                        @if($globalPhone)
                            <a href="https://wa.me/{{ $globalPhone }}?text=Halo%20Admin%20{{ urlencode(app(\App\Services\SettingService::class)->storeName()) }},%20saya%20butuh%20bantuan."
                                target="_blank" class="btn text-white d-flex align-items-center justify-content-center gap-2"
                                style="background-color: #25D366; border-radius: 12px; padding: 12px; border: none;">
                                <i class="bi bi-whatsapp fs-5"></i> WhatsApp
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Tab Nav (Mobile Only) -->
    <div class="bottom-nav d-md-none">
        <a href="{{ route('home') }}" class="bottom-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="bi bi-house-door{{ request()->routeIs('home') ? '-fill' : '' }}"></i>
            <span>Beranda</span>
        </a>
        <a href="{{ route('products.index') }}"
            class="bottom-nav-item {{ request()->routeIs('products.*') && !request()->has('q') ? 'active' : '' }}">
            <i class="bi bi-grid{{ request()->routeIs('products.*') && !request()->has('q') ? '-fill' : '' }}"></i>
            <span>Produk</span>
        </a>
        <a href="{{ route('orders.track.index') }}"
            class="bottom-nav-item {{ request()->routeIs('orders.track.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i>
            <span>Lacak</span>
        </a>
        <a href="{{ route('cart.index') }}"
            class="bottom-nav-item {{ request()->routeIs('cart.*') ? 'active' : '' }} position-relative">
            <i class="bi bi-cart3"></i>
            <span>Keranjang</span>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="bottom-cart-count"
                style="font-size: 0.55rem; padding: 0.25em 0.4em; transform: translate(-30%, -20%) !important;">0</span>
        </a>
        @auth
            @if(auth()->user()->hasAnyRole(['admin', 'superadmin']))
                <a href="{{ route('admin.dashboard') }}" class="bottom-nav-item">
                    <i class="bi bi-speedometer2 text-danger"></i>
                    <span class="text-danger fw-bold">Panel Admin</span>
                </a>
            @else
                <a href="{{ route('account.profile') }}"
                    class="bottom-nav-item {{ request()->routeIs('account.*') ? 'active' : '' }}">
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
</body>

</html>