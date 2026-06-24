<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', app(\App\Services\SettingService::class)->storeName())</title>
    @hasSection('meta_description')<meta name="description" content="@yield('meta_description')">@endif

    @php
        $settings = app(\App\Services\SettingService::class);
        $favicon = $settings->get('store_favicon');
        $storeName = $settings->storeName();
        $primary = $settings->primaryColor();
        $secondary = $settings->secondaryColor();
        $logo = $settings->get('store_logo');
    @endphp

    @if($favicon)
        <link rel="icon" type="image/png" href="{{ asset($favicon) }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: {{ $primary }};
            --secondary: {{ $secondary }};
            --primary-rgb: 79, 70, 229;
            --secondary-rgb: 124, 58, 237;
            --bg: #f7f4ef;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --line: #e7e5e4;
            --text: #111827;
            --muted: #6b7280;
            --heading: 'Playfair Display', serif;
            --body: 'Inter', sans-serif;
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 16px;
            --shadow-soft: 0 20px 50px rgba(15,23,42,.07);
            --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
        }
        * { box-sizing: border-box; }
        body { margin:0; font-family:var(--body); color:var(--text); background: radial-gradient(circle at top left, rgba(var(--primary-rgb), .08), transparent 28%), radial-gradient(circle at top right, rgba(var(--secondary-rgb), .08), transparent 24%), var(--bg); }
        .eb-shell { min-height:100vh; }
        .container-xxl { max-width: 1420px; }
        .eb-topbar { background:#111827; color:#fff; font-size:.8rem; }
        .eb-topbar a { color:#fff; text-decoration:none; opacity:.85; }
        .eb-topbar a:hover { opacity:1; }
        .eb-navbar { position:sticky; top:0; z-index:1040; backdrop-filter: blur(16px); background: rgba(255,255,255,.82); border-bottom:1px solid rgba(255,255,255,.4); }
        .eb-nav-inner { padding: 16px 0; }
        .eb-brand { display:flex; align-items:center; gap:12px; text-decoration:none; color:var(--text); }
        .eb-brand-mark { width:42px; height:42px; border-radius:14px; background:var(--gradient); color:#fff; display:flex; align-items:center; justify-content:center; box-shadow: 0 12px 30px rgba(var(--primary-rgb), .25); }
        .eb-brand-name { font-weight:800; font-size:1.35rem; letter-spacing:-.02em; }
        .eb-search { background:#fff; border:1px solid #ece8e1; border-radius:999px; padding:10px 16px; display:flex; align-items:center; gap:10px; box-shadow: 0 10px 24px rgba(15,23,42,.04); }
        .eb-search input { border:0; outline:0; width:100%; background:transparent; }
        .eb-nav-link { color:#475569; text-decoration:none; font-weight:600; font-size:.92rem; }
        .eb-nav-link:hover { color:var(--primary); }
        .eb-icon-btn { width:42px; height:42px; border-radius:999px; background:#fff; border:1px solid #ebe7de; color:var(--text); display:inline-flex; align-items:center; justify-content:center; text-decoration:none; position:relative; box-shadow:0 8px 20px rgba(15,23,42,.04); }
        .eb-badge-count { position:absolute; top:-4px; right:-3px; width:20px; height:20px; border-radius:999px; background:#ef4444; color:#fff; font-size:.68rem; font-weight:700; display:flex; align-items:center; justify-content:center; }
        .eb-mobile-bottom { display:none; }
        .eb-page { padding: 24px 0 70px; }
        .eb-footer {
            position: relative;
            margin-top: 100px;
            color: #e5e7eb;
            background:
                radial-gradient(900px 500px at 12% 0%, rgba(var(--primary-rgb), .35), transparent 60%),
                radial-gradient(700px 420px at 92% 100%, rgba(var(--secondary-rgb), .32), transparent 65%),
                linear-gradient(180deg, #0b1020 0%, #0a0f1d 100%);
            overflow: hidden;
            isolation: isolate;
        }
        .eb-footer::before {
            content: ""; position: absolute; inset: 0; pointer-events: none; z-index: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, #000 35%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 0%, #000 35%, transparent 80%);
            opacity: .55;
        }
        .eb-footer::after {
            content: ""; position: absolute; left: -10%; right: -10%; top: -1px; height: 1px; z-index: 1;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.35) 50%, transparent);
        }
        .eb-footer > * { position: relative; z-index: 2; }
        .eb-footer a { color: #d1d5db; text-decoration: none; transition: color .2s ease, transform .2s ease; }
        .eb-footer a:hover { color: #fff; }
        .eb-footer-cta {
            border-radius: 28px;
            padding: 28px 32px;
            background: linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.02));
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 60px rgba(0,0,0,.35);
        }
        .eb-footer-headline {
            font-family: var(--heading);
            font-size: clamp(1.6rem, 2.6vw, 2.4rem);
            line-height: 1.1; letter-spacing: -.02em;
            color: #fff; margin: 0;
        }
        .eb-newsletter {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 999px;
            padding: 6px 6px 6px 18px;
        }
        .eb-newsletter input {
            background: transparent; border: 0; outline: 0;
            color: #fff; flex: 1; min-width: 0; font-size: .92rem;
        }
        .eb-newsletter input::placeholder { color: rgba(229,231,235,.55); }
        .eb-newsletter button {
            border: 0; border-radius: 999px;
            background: linear-gradient(135deg, #fff, #e5e7eb);
            color: #0b1020; font-weight: 700; font-size: .85rem;
            padding: 10px 18px; white-space: nowrap;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .eb-newsletter button:hover { transform: translateY(-1px); box-shadow: 0 12px 26px rgba(255,255,255,.18); }
        .eb-foot-title {
            font-family: var(--heading);
            font-size: .82rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .22em;
            color: rgba(255,255,255,.6);
            margin-bottom: 16px;
            display: inline-flex; align-items: center; gap: 10px;
        }
        .eb-foot-title::before {
            content: ""; width: 18px; height: 1px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        .eb-foot-link {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: .9rem; padding: 4px 0;
        }
        .eb-foot-link i {
            font-size: .7rem; opacity: 0;
            transform: translateX(-4px);
            transition: opacity .2s ease, transform .2s ease;
        }
        .eb-foot-link:hover i { opacity: 1; transform: translateX(0); }
        .eb-foot-contact {
            display: flex; gap: 12px; align-items: flex-start;
            font-size: .88rem; color: rgba(229,231,235,.85);
            padding: 10px 0;
        }
        .eb-foot-contact .ic {
            width: 36px; height: 36px; flex-shrink: 0;
            border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            color: #fff;
        }
        .eb-foot-social {
            width: 42px; height: 42px; border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.14);
            color: #fff; font-size: 1.05rem;
            transition: transform .25s ease, background .25s ease, border-color .25s ease;
        }
        .eb-foot-social:hover {
            transform: translateY(-3px) rotate(-4deg);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: transparent;
            color: #fff;
        }
        .eb-foot-divider {
            height: 1px; margin: 36px 0 20px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);
        }
        .eb-foot-bottom {
            display: flex; flex-wrap: wrap; gap: 14px;
            align-items: center; justify-content: space-between;
            font-size: .82rem; color: rgba(229,231,235,.65);
        }
        .eb-foot-bottom .legal { display: flex; flex-wrap: wrap; gap: 18px; }
        .eb-foot-payments { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .eb-foot-payments .pm {
            padding: 6px 10px; border-radius: 8px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            font-size: .7rem; font-weight: 700; letter-spacing: .12em;
            color: rgba(255,255,255,.85); text-transform: uppercase;
        }
        .eb-brand-foot {
            display: flex; align-items: center; gap: 12px; margin-bottom: 18px;
        }
        .eb-brand-foot .mark {
            width: 48px; height: 48px; border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff; display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 12px 28px rgba(var(--primary-rgb), .35);
        }
        .eb-brand-foot .name {
            font-family: var(--heading); font-weight: 700;
            font-size: 1.4rem; color: #fff; letter-spacing: -.02em;
        }
        .eb-section-title { font-family:var(--heading); font-size: clamp(1.6rem, 2vw, 2.4rem); font-weight:700; letter-spacing:-.03em; }
        .eb-kicker { display:inline-flex; padding:7px 14px; border-radius:999px; background:rgba(var(--primary-rgb), .09); color:var(--primary); font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }
        .eb-card-surface { background: rgba(255,255,255,.92); border:1px solid rgba(255,255,255,.5); border-radius:var(--radius-xl); box-shadow:var(--shadow-soft); }

        .eb-product-card { background:#fff; border-radius:20px; overflow:hidden; border:1px solid #eee7de; box-shadow:0 16px 36px rgba(15,23,42,.05); transition:transform .2s ease, box-shadow .2s ease; }
        .eb-product-card:hover { transform: translateY(-4px); box-shadow:0 24px 54px rgba(15,23,42,.11); }
        .eb-product-media { position:relative; aspect-ratio: 4/4.8; overflow:hidden; background:#f4f4f5; }
        .eb-product-media img { width:100%; height:100%; object-fit:cover; transition: transform .35s ease; }
        .eb-product-card:hover .eb-product-media img { transform:scale(1.05); }
        .eb-product-placeholder { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#cbd5e1; font-size:2rem; }
        .eb-badge-sale { position:absolute; top:14px; left:14px; background:#fff; color:#111827; border-radius:999px; padding:7px 12px; font-size:.72rem; font-weight:700; box-shadow:0 10px 20px rgba(15,23,42,.08); }
        .eb-product-body { padding:18px; }
        .eb-product-meta { font-size:.72rem; color:#64748b; text-transform:uppercase; letter-spacing:.06em; font-weight:700; margin-bottom:8px; }
        .eb-product-title { font-size:1rem; line-height:1.4; min-height:2.8em; margin-bottom:10px; font-weight:700; }
        .eb-product-price-wrap { display:flex; flex-direction:column; gap:4px; }
        .eb-product-price { font-weight:800; color:var(--text); font-size:1rem; }
        .eb-product-price-old { color:#94a3b8; text-decoration:line-through; font-size:.85rem; }

        @media (max-width: 991.98px) {
            .eb-nav-desktop { display:none !important; }
            .eb-mobile-bottom { display:flex; position:fixed; left:12px; right:12px; bottom:12px; z-index:1045; background:rgba(17,24,39,.92); backdrop-filter: blur(14px); border-radius:999px; padding:8px 10px calc(8px + env(safe-area-inset-bottom)); justify-content:space-around; box-shadow:0 16px 36px rgba(0,0,0,.22); }
            .eb-mobile-bottom a { color:#cbd5e1; text-decoration:none; font-size:.7rem; display:flex; flex-direction:column; align-items:center; gap:4px; }
            .eb-mobile-bottom a.active, .eb-mobile-bottom a:hover { color:#fff; }
            .eb-page { padding-bottom: 96px; }
            .eb-search-wrap { order: 3; width: 100%; margin-top: 12px; }
        }
    </style>
    @yield('styles')
</head>
<body>
<div class="eb-shell">
    <div class="eb-topbar py-2 d-none d-md-block">
        <div class="container-xxl px-3 d-flex justify-content-between">
            <div><i class="bi bi-stars me-1"></i>Curated storefront experience · premium & responsive</div>
            <div class="d-flex gap-3">
                <a href="{{ route('orders.track.index') }}">Lacak Pesanan</a>
                <a href="{{ route('blog.index') }}">Blog</a>
            </div>
        </div>
    </div>

    <nav class="eb-navbar">
        <div class="container-xxl px-3 eb-nav-inner">
            <div class="row align-items-center g-3">
                <div class="col-lg-3 col-md-4 col-8">
                    <a href="{{ route('home') }}" class="eb-brand">
                        @if($logo)
                            <img src="{{ asset($logo) }}" alt="{{ $storeName }}" style="height:42px;object-fit:contain;">
                        @else
                            <span class="eb-brand-mark"><i class="bi bi-gem"></i></span>
                            <span class="eb-brand-name">{{ $storeName }}</span>
                        @endif
                    </a>
                </div>
                <div class="col-lg-5 col-md-8 col-12 eb-search-wrap">
                    <form action="{{ route('products.index') }}" method="GET" class="eb-search">
                        <i class="bi bi-search text-muted"></i>
                        <input type="text" name="q" placeholder="Cari produk, kategori, atau inspirasi..." value="{{ request('q') }}">
                    </form>
                </div>
                <div class="col-lg-4 d-none d-lg-flex align-items-center justify-content-end gap-4 eb-nav-desktop">
                    <a class="eb-nav-link" href="{{ route('home') }}">Beranda</a>
                    <a class="eb-nav-link" href="{{ route('products.index') }}">Katalog</a>
                    <a class="eb-nav-link" href="{{ route('blog.index') }}">Editorial</a>
                    @if(!auth()->check() || !auth()->user()->hasAnyRole(['admin','superadmin']))
                        <a class="eb-icon-btn" href="{{ route('cart.index') }}"><i class="bi bi-bag"></i><span class="eb-badge-count" id="cart-count">0</span></a>
                    @endif
                    @auth
                        <a class="eb-icon-btn" href="{{ auth()->user()->hasAnyRole(['admin','superadmin','owner','staff']) ? route('admin.dashboard') : route('account.orders') }}"><i class="bi bi-person"></i></a>
                    @else
                        <a class="eb-icon-btn" href="{{ route('login') }}"><i class="bi bi-person"></i></a>
                    @endauth
                </div>
                <div class="col-4 d-flex d-lg-none justify-content-end gap-2">
                    @if(!auth()->check() || !auth()->user()->hasAnyRole(['admin','superadmin']))
                        <a class="eb-icon-btn" href="{{ route('cart.index') }}"><i class="bi bi-bag"></i><span class="eb-badge-count" id="cart-count">0</span></a>
                    @endif
                    <a class="eb-icon-btn" href="{{ auth()->check() ? (auth()->user()->hasAnyRole(['admin','superadmin','owner','staff']) ? route('admin.dashboard') : route('account.orders')) : route('login') }}"><i class="bi bi-person"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <main class="eb-page">
        @yield('content')
    </main>

    <footer class="eb-footer pt-5 pb-4">
        <div class="container-xxl px-3">
            <div class="row g-4 g-lg-5">
                {{-- Brand --}}
                <div class="col-lg-4">
                    <div class="eb-brand-foot">
                        @if($logo)
                            <img src="{{ asset($logo) }}" alt="{{ $storeName }}" style="height:48px; object-fit:contain;">
                        @else
                            <span class="mark"><i class="bi bi-gem"></i></span>
                            <span class="name">{{ $storeName }}</span>
                        @endif
                    </div>
                    <p class="mb-4" style="color:rgba(229,231,235,.75); font-size:.92rem; line-height:1.6; max-width: 380px;">
                        {{ $settings->get('store_description', 'Curated shopping experience built for modern brands. Tampil premium, terasa personal, dan tetap mudah digunakan.') }}
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @if($wa = $settings->get('social_whatsapp'))<a class="eb-foot-social" href="https://wa.me/{{ preg_replace('/[^0-9]/','',$wa) }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>@endif
                        @if($ig = $settings->get('social_instagram'))<a class="eb-foot-social" href="{{ $ig }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>@endif
                        @if($fb = $settings->get('social_facebook'))<a class="eb-foot-social" href="{{ $fb }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>@endif
                        @if($tt = $settings->get('social_tiktok'))<a class="eb-foot-social" href="{{ $tt }}" target="_blank" rel="noopener" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>@endif
                        @if($yt = $settings->get('social_youtube'))<a class="eb-foot-social" href="{{ $yt }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="bi bi-youtube"></i></a>@endif
                        @if($mail = $settings->get('social_email'))<a class="eb-foot-social" href="mailto:{{ $mail }}" aria-label="Email"><i class="bi bi-envelope"></i></a>@endif
                    </div>
                </div>

                {{-- Toko --}}
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="eb-foot-title">Belanja</div>
                    <div class="d-grid gap-1">
                        <a class="eb-foot-link" href="{{ route('home') }}">Beranda <i class="bi bi-arrow-right"></i></a>
                        <a class="eb-foot-link" href="{{ route('products.index') }}">Katalog <i class="bi bi-arrow-right"></i></a>
                        <a class="eb-foot-link" href="{{ route('products.index', ['sort' => 'new']) }}">Produk Baru <i class="bi bi-arrow-right"></i></a>
                        <a class="eb-foot-link" href="{{ route('orders.track.index') }}">Lacak Pesanan <i class="bi bi-arrow-right"></i></a>
                        <a class="eb-foot-link" href="{{ route('cart.index') }}">Keranjang <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>

                {{-- Konten --}}
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="eb-foot-title">Editorial</div>
                    <div class="d-grid gap-1">
                        <a class="eb-foot-link" href="{{ route('blog.index') }}">Blog <i class="bi bi-arrow-right"></i></a>
                        @foreach(\App\Models\Page::where('is_active', true)->take(4)->get() as $p)
                            <a class="eb-foot-link" href="{{ route('page.show', $p->slug) }}">{{ $p->title }} <i class="bi bi-arrow-right"></i></a>
                        @endforeach
                    </div>
                </div>

                {{-- Kontak --}}
                <div class="col-md-4 col-lg-4">
                    <div class="eb-foot-title">Hubungi Kami</div>
                    @if($addr = $settings->get('store_address'))
                        <div class="eb-foot-contact">
                            <span class="ic"><i class="bi bi-geo-alt"></i></span>
                            <div>{{ $addr }}</div>
                        </div>
                    @endif
                    @if($phone = $settings->get('store_phone'))
                        <div class="eb-foot-contact">
                            <span class="ic"><i class="bi bi-telephone"></i></span>
                            <div><a href="tel:{{ preg_replace('/[^0-9+]/','',$phone) }}">{{ $phone }}</a></div>
                        </div>
                    @endif
                    @if($mail2 = $settings->get('social_email') ?? $settings->get('store_email'))
                        <div class="eb-foot-contact">
                            <span class="ic"><i class="bi bi-envelope"></i></span>
                            <div><a href="mailto:{{ $mail2 }}">{{ $mail2 }}</a></div>
                        </div>
                    @endif
                    @if($hours = $settings->get('store_hours'))
                        <div class="eb-foot-contact">
                            <span class="ic"><i class="bi bi-clock"></i></span>
                            <div>{{ $hours }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="eb-foot-divider"></div>

            <div class="eb-foot-bottom">
                <div>&copy; {{ date('Y') }} <span class="text-white">{{ $storeName }}</span>. All rights reserved.</div>
                <div class="eb-foot-payments">
                    <span class="pm">COD</span>
                    <span class="pm">Transfer</span>
                    <span class="pm">QRIS</span>
                    <span class="pm">VA</span>
                    <span class="pm">E-Wallet</span>
                </div>
                <div class="legal">
                    @foreach(\App\Models\Page::where('is_active', true)->whereIn('slug', ['privacy-policy','terms','syarat-ketentuan','kebijakan-privasi'])->take(3)->get() as $lp)
                        <a href="{{ route('page.show', $lp->slug) }}">{{ $lp->title }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>

    <div class="eb-mobile-bottom d-lg-none">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="bi bi-house"></i><span>Home</span></a>
        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="bi bi-grid"></i><span>Katalog</span></a>
        <a href="{{ route('orders.track.index') }}" class="{{ request()->routeIs('orders.track.*') ? 'active' : '' }}"><i class="bi bi-truck"></i><span>Lacak</span></a>
        <a href="{{ auth()->check() ? (auth()->user()->hasAnyRole(['admin','superadmin','owner','staff']) ? route('admin.dashboard') : route('account.orders')) : route('login') }}"><i class="bi bi-person"></i><span>Akun</span></a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    async function refreshCartCount() {
        const el = document.getElementById('cart-count');
        if (!el) return;
        try {
            const res = await fetch('{{ route('cart.count') }}');
            const data = await res.json();
            el.textContent = data.count || 0;
            el.style.display = (data.count || 0) > 0 ? 'flex' : 'none';
        } catch (e) {}
    }
    refreshCartCount();
</script>
@yield('scripts')
</body>
</html>
