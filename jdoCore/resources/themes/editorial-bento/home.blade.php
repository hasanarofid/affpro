@extends('theme::layouts.app')
@section('title', app(\App\Services\SettingService::class)->storeName() . ' — Curated Shopping')

@section('styles')
<style>
    .eb-home-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }

    /* === HERO SLIDER (image only) === */
    .eb-hero-slider {
        position: relative;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 30px 60px rgba(15,23,42,.12);
        background: #f1f0eb;
    }
    .eb-hero-slider .carousel-inner,
    .eb-hero-slider .carousel-item { border-radius: 28px; }
    .eb-hero-img {
        display: block;
        width: 100%;
        height: clamp(220px, 38vw, 520px);
        object-fit: cover;
    }
    .eb-hero-link { display: block; }
    .eb-hero-slider .carousel-control-prev,
    .eb-hero-slider .carousel-control-next {
        width: 48px; height: 48px; top: 50%; transform: translateY(-50%);
        background: rgba(17,24,39,.55); backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 999px; opacity: 0;
        margin: 0 16px; transition: opacity .25s ease, background .2s ease;
    }
    .eb-hero-slider:hover .carousel-control-prev,
    .eb-hero-slider:hover .carousel-control-next { opacity: 1; }
    .eb-hero-slider .carousel-control-prev { left: 0; }
    .eb-hero-slider .carousel-control-next { right: 0; }
    .eb-hero-slider .carousel-control-prev:hover,
    .eb-hero-slider .carousel-control-next:hover { background: rgba(17,24,39,.78); }
    .eb-hero-slider .carousel-control-prev-icon,
    .eb-hero-slider .carousel-control-next-icon { width: 16px; height: 16px; }
    .eb-hero-slider .carousel-indicators {
        bottom: 14px; margin-bottom: 0; gap: 8px;
    }
    .eb-hero-slider .carousel-indicators [data-bs-target] {
        width: 24px; height: 4px; border-radius: 999px;
        background: rgba(255,255,255,.55); border: 0; opacity: 1;
        box-shadow: 0 2px 6px rgba(0,0,0,.18);
        transition: width .25s ease, background .25s ease;
    }
    .eb-hero-slider .carousel-indicators .active {
        width: 44px; background: #fff;
    }
    @media (max-width: 575.98px) {
        .eb-hero-slider .carousel-control-prev,
        .eb-hero-slider .carousel-control-next { display: none; }
    }

    /* === FEATURE STRIP === */
    .eb-feature-strip { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    @media (max-width: 767.98px) { .eb-feature-strip { grid-template-columns: 1fr; } }
    .eb-feature-card {
        background: #fff; border: 1px solid #efe8dd; border-radius: 22px;
        padding: 20px 22px; display: flex; gap: 14px; align-items: flex-start;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .eb-feature-card:hover { transform: translateY(-3px); box-shadow: 0 18px 36px rgba(15,23,42,.07); }
    .eb-feature-card .ic {
        width: 46px; height: 46px; border-radius: 14px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        background: color-mix(in srgb, var(--primary) 12%, #fff 88%);
        color: var(--primary); font-size: 1.15rem;
    }
    .eb-feature-card h6 { font-family: var(--heading); font-weight: 700; margin: 0 0 4px; font-size: 1.05rem; letter-spacing: -.01em; }
    .eb-feature-card p { margin: 0; font-size: .85rem; color: #64748b; line-height: 1.45; }

    /* === KATEGORI EPIC === */
    .eb-category-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
    @media (max-width: 1199.98px) { .eb-category-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 767.98px) { .eb-category-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; } }
    .eb-category-card {
        --cat-from: #6366f1;
        --cat-to: #8b5cf6;
        --cat-glow: rgba(99,102,241,.45);
        position: relative;
        height: 100%;
        min-height: 180px;
        border-radius: 24px;
        overflow: hidden;
        padding: 22px 22px 20px;
        background: linear-gradient(135deg, var(--cat-from), var(--cat-to));
        color: #fff;
        box-shadow: 0 14px 30px rgba(15,23,42,.08);
        transition: transform .3s ease, box-shadow .3s ease;
        display: flex; flex-direction: column; justify-content: space-between; gap: 16px;
        isolation: isolate;
    }
    .eb-category-card::before,
    .eb-category-card::after {
        content: ""; position: absolute; border-radius: 50%;
        filter: blur(28px); pointer-events: none; z-index: 0;
        transition: transform .5s ease;
    }
    .eb-category-card::before {
        width: 160px; height: 160px;
        right: -50px; top: -50px;
        background: rgba(255,255,255,.35);
    }
    .eb-category-card::after {
        width: 140px; height: 140px;
        left: -40px; bottom: -50px;
        background: rgba(0,0,0,.18);
    }
    .eb-category-card > * { position: relative; z-index: 1; }
    .eb-category-card:hover {
        transform: translateY(-5px) scale(1.01);
        box-shadow: 0 24px 50px var(--cat-glow);
    }
    .eb-category-card:hover::before { transform: scale(1.2) translate(-10px, 10px); }
    .eb-category-card:hover::after { transform: scale(1.15); }

    .eb-category-card .eb-cat-ghost {
        position: absolute; right: 12px; bottom: -18px;
        font-family: var(--heading);
        font-size: 6.5rem; font-weight: 800;
        line-height: 1; letter-spacing: -.06em;
        color: rgba(255,255,255,.14);
        pointer-events: none; z-index: 0;
        text-transform: uppercase;
    }
    .eb-category-card .eb-cat-icon {
        width: 54px; height: 54px; border-radius: 16px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,.22);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,.28);
        color: #fff;
        font-size: 1.35rem;
        transition: transform .3s ease, background .3s ease;
    }
    .eb-category-card:hover .eb-cat-icon {
        transform: rotate(-6deg) scale(1.08);
        background: rgba(255,255,255,.32);
    }
    .eb-category-card .eb-cat-num {
        font-size: .72rem; font-weight: 700;
        letter-spacing: .14em; text-transform: uppercase;
        color: rgba(255,255,255,.75);
        background: rgba(255,255,255,.12);
        padding: 5px 10px; border-radius: 999px;
        border: 1px solid rgba(255,255,255,.18);
    }
    .eb-category-card .eb-cat-name {
        font-family: var(--heading);
        font-size: 1.3rem; font-weight: 700;
        line-height: 1.1; letter-spacing: -.02em;
        margin: 0 0 6px;
        text-shadow: 0 2px 12px rgba(0,0,0,.18);
    }
    .eb-category-card .eb-cat-meta {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: .82rem; font-weight: 600;
        color: rgba(255,255,255,.9);
    }
    .eb-category-card .eb-cat-meta i { transition: transform .25s ease; }
    .eb-category-card:hover .eb-cat-meta i { transform: translateX(5px); }

    /* Color palette rotations */
    .eb-cat-c1 { --cat-from:#6366f1; --cat-to:#8b5cf6; --cat-glow:rgba(99,102,241,.45); }
    .eb-cat-c2 { --cat-from:#ec4899; --cat-to:#f43f5e; --cat-glow:rgba(244,63,94,.45); }
    .eb-cat-c3 { --cat-from:#14b8a6; --cat-to:#0ea5e9; --cat-glow:rgba(14,165,233,.45); }
    .eb-cat-c4 { --cat-from:#f59e0b; --cat-to:#ef4444; --cat-glow:rgba(245,158,11,.45); }
    .eb-cat-c5 { --cat-from:#10b981; --cat-to:#22d3ee; --cat-glow:rgba(16,185,129,.45); }
    .eb-cat-c6 { --cat-from:#a855f7; --cat-to:#ec4899; --cat-glow:rgba(168,85,247,.45); }
    .eb-cat-c7 { --cat-from:#0f172a; --cat-to:#475569; --cat-glow:rgba(15,23,42,.45); }
    .eb-cat-c8 { --cat-from:#f43f5e; --cat-to:#fb923c; --cat-glow:rgba(251,146,60,.45); }

    .eb-blog-grid { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; }
    .eb-blog-card {
        background: #fff; border-radius: 24px; overflow:hidden; border:1px solid #efe8dd;
        box-shadow: 0 18px 42px rgba(15,23,42,.05); transition: transform .2s ease, box-shadow .2s ease;
    }
    .eb-blog-card:hover { transform: translateY(-3px); box-shadow: 0 26px 56px rgba(15,23,42,.1); }
    .eb-blog-card img { width:100%; height:260px; object-fit:cover; }
    .eb-blog-media-placeholder { width:100%; height:260px; display:flex; align-items:center; justify-content:center; background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 65%, black 35%), color-mix(in srgb, var(--secondary) 70%, black 30%)); color:#fff; }
    .eb-blog-body { padding: 22px; }
    .eb-blog-title { font-family: var(--heading); font-size: 1.45rem; line-height:1.15; letter-spacing:-.03em; margin-bottom: 10px; }
    @media (max-width: 1399.98px) { .eb-home-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 991.98px) { .eb-home-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .eb-blog-grid { grid-template-columns: 1fr; } }
    @media (max-width: 575.98px) { .eb-home-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; } }
</style>
@endsection

@section('content')
    @include('theme::partials.promo-popup')
    @php $settings = app(\App\Services\SettingService::class); @endphp

    <div class="container-xxl px-3">
        @php
            $heroSlides = [];
            if (isset($banners) && count($banners) > 0) {
                foreach ($banners as $b) {
                    if (!$b->image) continue;
                    $heroSlides[] = [
                        'image' => asset($b->image),
                        'url'   => $b->url ?: null,
                        'title' => $b->title ?? '',
                    ];
                }
            }
        @endphp

        @if(count($heroSlides) > 0)
        <section class="mb-5">
            <div id="ebHeroSlider" class="carousel slide eb-hero-slider" data-bs-ride="carousel" data-bs-interval="5500">
                @if(count($heroSlides) > 1)
                    <div class="carousel-indicators">
                        @foreach($heroSlides as $i => $s)
                            <button type="button" data-bs-target="#ebHeroSlider" data-bs-slide-to="{{ $i }}" class="{{ $i === 0 ? 'active' : '' }}" aria-label="Slide {{ $i + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
                <div class="carousel-inner">
                    @foreach($heroSlides as $i => $s)
                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                            @if($s['url'])
                                <a href="{{ $s['url'] }}" class="eb-hero-link">
                                    <img src="{{ $s['image'] }}" alt="{{ $s['title'] }}" class="eb-hero-img">
                                </a>
                            @else
                                <img src="{{ $s['image'] }}" alt="{{ $s['title'] }}" class="eb-hero-img">
                            @endif
                        </div>
                    @endforeach
                </div>
                @if(count($heroSlides) > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#ebHeroSlider" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#ebHeroSlider" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                @endif
            </div>

            <div class="eb-feature-strip mt-4">
                <div class="eb-feature-card">
                    <span class="ic"><i class="bi bi-truck"></i></span>
                    <div>
                        <h6>Pengiriman ke seluruh Indonesia</h6>
                        <p>Kerja sama dengan banyak ekspedisi, lengkap dengan tracking otomatis di akun Anda.</p>
                    </div>
                </div>
                <div class="eb-feature-card">
                    <span class="ic"><i class="bi bi-shield-check"></i></span>
                    <div>
                        <h6>Pembayaran aman & fleksibel</h6>
                        <p>Bayar pakai transfer bank, QRIS, e-wallet, virtual account, atau COD sesuai kenyamanan Anda.</p>
                    </div>
                </div>
                <div class="eb-feature-card">
                    <span class="ic"><i class="bi bi-headset"></i></span>
                    <div>
                        <h6>Layanan pelanggan responsif</h6>
                        <p>Tim kami siap membantu via WhatsApp dan email — dari pertanyaan produk hingga komplain pengiriman.</p>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if(count($categories) > 0)
            <section class="mb-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <div class="eb-kicker mb-2">Explore</div>
                        <h2 class="eb-section-title mb-0">Kategori Pilihan</h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-dark rounded-pill px-4">Lihat semua</a>
                </div>
                <div class="eb-category-grid">
                    @foreach($categories->take(8) as $cat)
                        @php $palette = 'eb-cat-c' . ((($loop->index) % 8) + 1); @endphp
                        <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="text-decoration-none">
                            <div class="eb-category-card {{ $palette }}">
                                <span class="eb-cat-ghost">{{ strtoupper(mb_substr($cat->name, 0, 3)) }}</span>
                                <div class="d-flex align-items-start justify-content-between">
                                    <span class="eb-cat-icon">
                                        <i class="fa-fw fas {{ $cat->icon ?? 'fa-th-large' }}"></i>
                                    </span>
                                    <span class="eb-cat-num">#{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div>
                                    <div class="eb-cat-name">{{ $cat->name }}</div>
                                    <span class="eb-cat-meta">
                                        Lihat koleksi <i class="bi bi-arrow-right"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if(count($featuredProducts) > 0)
            <section class="mb-5">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <div class="eb-kicker mb-2">Featured</div>
                        <h2 class="eb-section-title mb-0">Produk Unggulan</h2>
                    </div>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-dark rounded-pill px-4">Lihat katalog</a>
                </div>
                    <div class="eb-home-grid">
                    @foreach($featuredProducts as $product)
                        @include('theme::partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endif

        @if(isset($flashSale) && $flashSale->products->count() > 0)
            <section class="eb-card-surface p-4 p-lg-5 mb-5">
                <div class="row g-4 align-items-center mb-3">
                    <div class="col-lg-6">
                        <div class="eb-kicker mb-2">Flash Sale</div>
                        <h2 class="eb-section-title mb-2">Penawaran spesial dengan ritme visual yang lebih fresh.</h2>
                        <p class="text-muted mb-0">Buat promo lebih terasa seperti campaign, bukan sekadar daftar diskon.</p>
                    </div>
                    <div class="col-lg-6 text-lg-end">
                        <div class="d-inline-flex gap-2" id="flashSaleTimer" data-end="{{ $flashSale->end_time->format('Y-m-d H:i:s') }}">
                            <div class="px-3 py-2 rounded-4 bg-dark text-white text-center"><div class="hours fw-bold fs-5">00</div><small>Jam</small></div>
                            <div class="px-3 py-2 rounded-4 bg-dark text-white text-center"><div class="minutes fw-bold fs-5">00</div><small>Menit</small></div>
                            <div class="px-3 py-2 rounded-4 bg-dark text-white text-center"><div class="seconds fw-bold fs-5">00</div><small>Detik</small></div>
                        </div>
                    </div>
                </div>
                    <div class="eb-home-grid">
                    @foreach($flashSale->products->take(4) as $fs)
                        @include('theme::partials.product-card', ['product' => $fs->product])
                    @endforeach
                </div>
            </section>
        @endif

        @if(count($latestProducts) > 0)
            <section class="mb-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <div class="eb-kicker mb-2">Latest</div>
                        <h2 class="eb-section-title mb-0">Produk Terbaru</h2>
                    </div>
                </div>
                    <div class="eb-home-grid">
                    @foreach($latestProducts as $product)
                        @include('theme::partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endif

        @if(isset($blogPosts) && count($blogPosts) > 0)
            <section class="mb-5 pb-3">
                <div class="d-flex align-items-center justify-content-between mb-4 gap-3">
                    <div>
                        <div class="eb-kicker mb-2">Editorial</div>
                        <h2 class="eb-section-title mb-0">Cerita, inspirasi, dan insight dari brand Anda.</h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="btn btn-outline-dark rounded-pill px-4">Lihat semua artikel</a>
                </div>
                <div class="eb-blog-grid">
                    @foreach($blogPosts as $post)
                        <article class="eb-blog-card h-100">
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-dark d-block">
                                @if($post->featured_image || $post->image_path)
                                    <img src="{{ asset($post->featured_image ?? $post->image_path) }}" alt="{{ $post->title }}">
                                @else
                                    <div class="eb-blog-media-placeholder"><i class="bi bi-journal-text" style="font-size:3rem"></i></div>
                                @endif
                                <div class="eb-blog-body">
                                    <div class="small text-uppercase fw-bold text-muted mb-2" style="letter-spacing:.08em">{{ $post->category ?? 'Editorial' }} · {{ $post->created_at->translatedFormat('d M Y') }}</div>
                                    <h3 class="eb-blog-title text-dark">{{ $post->title }}</h3>
                                    <p class="text-muted mb-3">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 110) }}</p>
                                    <span class="fw-semibold" style="color:var(--primary)">Baca artikel <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

@section('scripts')
<script>
(function(){
    const timer = document.getElementById('flashSaleTimer');
    if(!timer) return;
    const end = new Date(timer.dataset.end.replace(' ', 'T')).getTime();
    function pad(n){ return String(n).padStart(2,'0'); }
    function tick(){
        const diff = Math.max(0, end - Date.now());
        const h = Math.floor(diff / 1000 / 60 / 60);
        const m = Math.floor((diff / 1000 / 60) % 60);
        const s = Math.floor((diff / 1000) % 60);
        timer.querySelector('.hours').textContent = pad(h);
        timer.querySelector('.minutes').textContent = pad(m);
        timer.querySelector('.seconds').textContent = pad(s);
    }
    tick(); setInterval(tick, 1000);
})();
</script>
@endsection
