@extends('theme::layouts.app')
@section('title', app(\App\Services\SettingService::class)->storeName() . ' — Belanja Online')

@section('content')
    @include('theme::partials.promo-popup')
    <div class="container mt-4 mb-5">
        <!-- Hero / Banners -->
        @if(count($banners) > 0)
            <div id="heroBanner" class="carousel slide" data-bs-ride="carousel" style="border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.06);">
                <div class="carousel-indicators mb-3">
                    @foreach($banners as $i => $banner)
                        <button type="button" data-bs-target="#heroBanner" data-bs-slide-to="{{ $i }}"
                            class="{{ $i === 0 ? 'active' : '' }}" style="width: 8px; height: 8px; border-radius: 50%; margin: 0 4px;"></button>
                    @endforeach
                </div>
                <div class="carousel-inner">
                    @foreach($banners as $i => $banner)
                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                            @if($banner->url)<a href="{{ $banner->url }}">@endif
                                <div class="position-relative w-100">
                                    <img src="{{ asset($banner->image) }}" class="w-100" style="object-fit:cover; border-radius: 20px;" alt="{{ $banner->title }}">
                                </div>
                            @if($banner->url)</a>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <section class="hero-section rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #1A1A1A 0%, #333333 100%);">
                <div class="container py-5 text-center position-relative text-white my-5">
                    <h1 class="fw-bold mb-3" style="font-size: clamp(1.8rem, 8vw, 3rem)">Elevate Your
                        Style Game.
                    </h1>
                    <p class="lead opacity-75 mb-4 max-w-lg mx-auto" style="max-width: 500px">Discover the latest trends in fashion with our new rounded aesthetic collection designed for comfort.</p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow border-0"
                        style="font-weight:600">
                        Shop Now <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </section>
        @endif
    </div>

    <div class="container pb-5">
        <!-- Flash Sale -->
        @if(isset($flashSale) && $flashSale->products->count() > 0)
            <section class="mb-5">
                <div class="rounded-5 p-4 p-md-5 shadow-sm position-relative overflow-hidden" style="background-color: #FFF6F0;">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 position-relative z-1">
                        <div>
                            <div class="text-danger fw-bold small text-uppercase mb-1"><i class="fas fa-bolt me-1"></i> Flash Deals</div>
                            <h3 class="fw-bold mb-0 text-dark" style="font-size: 2rem;">Ends in <span id="flashSaleTimerText" class="d-none"></span></h3>
                        </div>

                        <div class="d-flex gap-2 text-center mt-3 mt-md-0" id="flashSaleTimer"
                            data-end="{{ $flashSale->end_time->format('Y-m-d H:i:s') }}">
                            <div class="bg-white text-dark rounded-4 px-3 py-2 shadow-sm border" style="min-width: 65px;">
                                <h4 class="fw-bold mb-0 hours fs-5">00</h4>
                                <div class="small text-muted" style="font-size:0.65rem; font-weight: 500;">Hours</div>
                            </div>
                            <div class="bg-white text-dark rounded-4 px-3 py-2 shadow-sm border" style="min-width: 65px;">
                                <h4 class="fw-bold mb-0 minutes fs-5">00</h4>
                                <div class="small text-muted" style="font-size:0.65rem; font-weight: 500;">Mins</div>
                            </div>
                            <div class="bg-white text-dark rounded-4 px-3 py-2 shadow-sm border" style="min-width: 65px;">
                                <h4 class="fw-bold mb-0 seconds fs-5">00</h4>
                                <div class="small text-muted" style="font-size:0.65rem; font-weight: 500;">Secs</div>
                            </div>
                        </div>
                    </div>

                    <div class="row flex-nowrap overflow-auto hide-scrollbar g-4 pb-3"
                        style="scroll-snap-type: x mandatory;">
                        @foreach($flashSale->products as $fsProduct)
                            <div class="col-8 col-sm-5 col-md-4 col-lg-3" style="scroll-snap-align: start;">
                                <div class="card h-100 border-0 shadow-sm rounded-4 text-dark text-decoration-none bg-white p-2">
                                    <a href="{{ route('products.show', $fsProduct->product->slug) }}"
                                        class="text-decoration-none text-dark d-block">
                                        <div class="position-relative">
                                            @if($fsProduct->product->primaryImage)
                                                <img src="{{ asset($fsProduct->product->primaryImage->image_path) }}"
                                                    class="card-img-top rounded-3"
                                                    style="height: 160px; object-fit: cover; background-color: #f8f9fa;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center rounded-3"
                                                    style="height: 160px;">
                                                    <i class="fas fa-box fa-2x text-muted"></i>
                                                </div>
                                            @endif

                                            @php
                                                $discountPercent = 0;
                                                if ($fsProduct->product->base_price > 0) {
                                                    $discountPercent = round((($fsProduct->product->base_price - $fsProduct->discount_price) / $fsProduct->product->base_price) * 100);
                                                }
                                                $stockPercent = ($fsProduct->sold / $fsProduct->stock) * 100;
                                            @endphp

                                            @if($discountPercent > 0)
                                                <div class="position-absolute top-0 start-0 m-2">
                                                    <span class="badge bg-danger rounded-1 shadow-sm px-2 py-1" style="font-size: 0.7rem;">
                                                        -{{ $discountPercent }}%
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="card-body p-2 pt-3">
                                            <h6 class="card-title fw-semibold mb-1 text-truncate" style="font-size: 0.95rem;">
                                                {{ $fsProduct->product->name }}
                                            </h6>

                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="fw-bold text-danger fs-6">Rp{{ number_format($fsProduct->discount_price, 0, ',', '.') }}</span>
                                                <span class="small text-muted text-decoration-line-through" style="font-size: 0.75rem;">Rp{{ number_format($fsProduct->product->base_price, 0, ',', '.') }}</span>
                                            </div>

                                            <div class="mt-2">
                                                <div class="progress mb-1" style="height: 4px; border-radius: 10px; background-color: #ffe0e0;">
                                                    <div class="progress-bar {{ $stockPercent >= 100 ? 'bg-danger' : 'bg-danger' }}"
                                                        role="progressbar" style="width: {{ $stockPercent }}%"
                                                        aria-valuenow="{{ $stockPercent }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <div class="small text-muted" style="font-size:0.7rem">
                                                    @if($stockPercent >= 100)
                                                        <span class="text-danger fw-bold">Out of stock</span>
                                                    @else
                                                        {{ $fsProduct->stock - $fsProduct->sold }} items left
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- Categories -->
        @if(count($categories) > 0)
            <section class="mb-5 pb-4">
                <div class="d-flex align-items-center mb-4">
                    <h4 class="fw-bold mb-0 text-dark">Shop by Category</h4>
                    <a href="{{ route('products.index') }}" class="ms-auto text-decoration-none" style="color: var(--bs-info); font-weight: 500;">View All &rarr;</a>
                </div>
                <div class="row g-4 justify-content-center">
                    @php
                        $catColors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary'];
                    @endphp
                    @foreach($categories as $index => $cat)
                        @php
                            $cColor = $catColors[$index % count($catColors)];
                        @endphp
                        <div class="col-4 col-sm-3 col-md-2">
                            <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
                                class="text-center d-block text-decoration-none category-card">
                                <div class="bg-{{ $cColor }} bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3 category-icon-wrap" style="width: 90px; height: 90px; border-radius: 50%; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                    <i class="fa-fw fas {{ $cat->icon ?? 'fa-th-large' }} text-{{ $cColor }}"
                                        style="font-size:2.2rem;"></i>
                                </div>
                                <div class="small fw-medium text-muted category-text" style="font-size: 0.9rem; transition: all 0.2s;">{{ $cat->name }}</div>
                            </a>
                        </div>
                    @endforeach
                    <!-- More Categories Dummy -->
                    <div class="col-4 col-sm-3 col-md-2">
                        <a href="{{ route('products.index') }}" class="text-center d-block text-decoration-none category-card">
                            <div class="bg-dark bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3 category-icon-wrap" style="width: 90px; height: 90px; border-radius: 50%; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);">
                                <i class="bi bi-grid-3x3-gap-fill text-dark" style="font-size:2.2rem;"></i>
                            </div>
                            <div class="small fw-medium text-muted category-text" style="font-size: 0.9rem; transition: all 0.2s;">More</div>
                        </a>
                    </div>
                </div>
            </section>
        @endif

        <!-- Top Brands -->
        @if(isset($brands) && count($brands) > 0)
            <section class="mb-5 py-4">
                <div class="text-center mb-4">
                    <h3 class="fw-bold mb-1 text-dark">Top Brands</h3>
                    <p class="text-muted small">Trusted by the best in the world</p>
                </div>
                <div class="d-flex justify-content-center flex-wrap gap-3">
                    @foreach($brands as $brand)
                        <div class="bg-white rounded-pill shadow-sm px-4 py-3 d-flex align-items-center justify-content-center border" style="min-width: 130px;">
                            <span class="fw-bold text-dark">{{ $brand->name }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Featured Products -->
        @if(count($featuredProducts) > 0)
            <section class="mb-5 pb-4">
                <div class="d-flex flex-wrap align-items-center mb-4 gap-3">
                    <h3 class="fw-bold mb-0 text-dark">Featured Products</h3>
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('products.index') }}" class="badge bg-info text-white rounded-pill px-3 py-2 fw-normal text-decoration-none shadow-sm">All</a>
                        <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="badge bg-white text-dark border shadow-sm rounded-pill px-3 py-2 fw-normal text-decoration-none">Popular</a>
                        <a href="{{ route('products.index', ['sort' => 'new']) }}" class="badge bg-white text-dark border shadow-sm rounded-pill px-3 py-2 fw-normal text-decoration-none">New</a>
                    </div>
                </div>
                <div class="row g-4">
                    @foreach($featuredProducts as $product)
                        @include('theme::partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Latest Products -->
        @if(count($latestProducts) > 0)
            <section class="mb-5 pb-4">
                <div class="d-flex flex-wrap align-items-center mb-4 gap-3">
                    <h3 class="fw-bold mb-0 text-dark">Latest Products</h3>
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('products.index') }}" class="badge bg-info text-white rounded-pill px-3 py-2 fw-normal text-decoration-none shadow-sm">All</a>
                        <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="badge bg-white text-dark border shadow-sm rounded-pill px-3 py-2 fw-normal text-decoration-none">Popular</a>
                        <a href="{{ route('products.index', ['sort' => 'new']) }}" class="badge bg-white text-dark border shadow-sm rounded-pill px-3 py-2 fw-normal text-decoration-none">New</a>
                    </div>
                </div>
                <div class="row g-4">
                    @foreach($latestProducts as $product)
                        @include('theme::partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Latest from Blog -->
        @if(isset($blogPosts) && count($blogPosts) > 0)
            <section class="mb-5 pb-3">
                <div class="d-flex align-items-center mb-4">
                    <h3 class="fw-bold mb-0 text-dark">Latest from Blog</h3>
                    <a href="{{ route('blog.index') }}" class="ms-auto text-decoration-none" style="color: var(--bs-info); font-weight: 500;">View All &rarr;</a>
                </div>
                <div class="row g-4">
                    @foreach($blogPosts as $post)
                        <div class="col-md-4">
                            <div class="card border-0 h-100 bg-transparent" style="transition: transform 0.3s ease;">
                                <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-dark d-block mb-3 overflow-hidden rounded-4 shadow-sm position-relative">
                                    @if($post->featured_image || $post->image_path)
                                        <img src="{{ asset($post->featured_image ?? $post->image_path) }}" class="w-100" style="height: 220px; object-fit: cover; transition: transform 0.3s ease;">
                                    @else
                                        <div class="w-100 d-flex align-items-center justify-content-center" style="height: 220px; background: linear-gradient(135deg, #667eea, #764ba2); transition: transform 0.3s ease;">
                                            <i class="bi bi-journal-text text-white" style="font-size:3rem"></i>
                                        </div>
                                    @endif
                                </a>
                                <div>
                                    <div class="text-uppercase small mb-2 fw-bold" style="color: var(--bs-info); font-size: 0.75rem; letter-spacing: 0.5px;">{{ $post->category ?? 'LIFESTYLE' }}</div>
                                    <h5 class="fw-bold mb-2 line-clamp-2"><a href="{{ route('blog.show', $post->slug) }}" class="text-dark text-decoration-none">{{ $post->title }}</a></h5>
                                    <p class="text-muted small mb-3 line-clamp-2" style="font-size: 0.85rem;">{{ Str::limit(strip_tags($post->content), 100) }}</p>
                                    <a href="{{ route('blog.show', $post->slug) }}" class="text-dark fw-bold text-decoration-none" style="font-size: 0.85rem;">Read Article &rarr;</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @if(config('app.demo_mode'))
    <style>
        .demo-modal-content {
            border-radius: 28px;
            border: none;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.4);
            overflow: hidden;
        }
        .demo-modal-header {
            background: linear-gradient(135deg, #FF416C 0%, #FF4B2B 100%);
            padding: 45px 20px 65px;
            text-align: center;
            color: white;
            position: relative;
        }
        .demo-modal-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.8;
            pointer-events: none;
        }
        .demo-modal-header::after {
            content: '';
            position: absolute;
            bottom: -25px;
            left: -10%;
            right: -10%;
            height: 50px;
            background: #ffffff;
            transform: rotate(-3deg);
            z-index: 1;
        }
        .demo-modal-icon-wrapper {
            position: relative;
            z-index: 2;
            margin-top: -55px;
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
        }
        .demo-modal-icon {
            width: 90px;
            height: 90px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 35px rgba(255, 75, 43, 0.3);
            color: #FF416C;
            font-size: 3rem;
            border: 6px solid #fff;
            position: relative;
        }
        .demo-modal-icon::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px dashed rgba(255, 65, 108, 0.4);
            animation: spinRotate 10s linear infinite;
        }
        @keyframes spinRotate {
            100% { transform: rotate(360deg); }
        }
        .demo-modal-body {
            padding: 0 35px 35px;
            position: relative;
            z-index: 2;
            background: white;
        }
        .animated-pulse-icon {
            animation: pulseInner 2s infinite cubic-bezier(0.4, 0, 0.6, 1);
        }
        @keyframes pulseInner {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .demo-alert-box {
            background-color: #fff4f4; 
            border-radius: 16px; 
            position: relative; 
            overflow: hidden;
            border: 1px solid rgba(255, 65, 108, 0.1);
        }
        .demo-alert-pattern {
            position: absolute; 
            top: 0; start: 0; width: 100%; height: 100%;
            background: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255,65,108,0.03) 10px, rgba(255,65,108,0.03) 20px);
            z-index: 0;
        }
        .btn-gorgeous {
            background: linear-gradient(135deg, #FF416C 0%, #FF4B2B 100%);
            border-radius: 14px;
            box-shadow: 0 10px 20px rgba(255, 75, 43, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: white;
            font-weight: 700;
            letter-spacing: 0.5px;
            border: none;
        }
        .btn-gorgeous:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(255, 75, 43, 0.4);
            color: white;
        }
    </style>

    <!-- Demo Mode Notice Modal -->
    <div class="modal fade" id="demoNoticeModal" tabindex="-1" aria-labelledby="demoNoticeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content demo-modal-content">
                <div class="demo-modal-header">
                    <h3 class="fw-bolder mb-1" style="font-family: 'Poppins', sans-serif; letter-spacing: 0.5px; text-shadow: 0 2px 10px rgba(0,0,0,0.1);">Peringatan Keamanan</h3>
                    <p class="mb-0 small" style="opacity: 0.9; font-weight: 500;">Harap dibaca dengan saksama!</p>
                </div>
                
                <div class="demo-modal-body">
                    <div class="demo-modal-icon-wrapper">
                        <div class="demo-modal-icon">
                            <i class="bi bi-shield-lock-fill animated-pulse-icon"></i>
                        </div>
                    </div>
                    
                    <div class="text-center mb-4">
                        <p class="text-muted mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                            Penjualan aplikasi ini <strong>secara resmi hanya</strong> via website:
                        </p>
                        <a href="https://store.dapurkode.com" target="_blank" class="fw-bold fs-5 text-decoration-none mt-2 mb-2 d-inline-block shadow-sm" style="color: #FF416C; background: #fff0eb; padding: 6px 20px; border-radius: 12px; border: 1px dashed rgba(255,65,108,0.3);">
                            <i class="bi bi-globe me-1"></i> store.dapurkode.com
                        </a>
                        <p class="mb-0 mt-1">
                            <span class="d-inline-flex align-items-center bg-light rounded-pill px-3 py-1 text-dark small border shadow-sm" style="font-weight:600">
                                <i class="bi bi-x-circle-fill text-danger me-2"></i> Tidak dijual di tempat/marketplace lain!
                            </span>
                        </p>
                    </div>
                    
                    <div class="demo-alert-box p-3 mb-4">
                        <div class="demo-alert-pattern"></div>
                        <div class="d-flex position-relative" style="z-index: 1;">
                            <div class="me-3 mt-1">
                                <i class="bi bi-exclamation-octagon-fill fs-3" style="color: #dc3545;"></i>
                            </div>
                            <div class="text-start">
                                <strong class="text-danger d-block mb-1" style="font-size: 1rem;">Hati-hati Barang Bajakan!</strong>
                                <span class="text-muted" style="font-size: 0.85rem; line-height:1.5; display:block;">
                                    Jika Anda membeli di luar tempat dan link resmi di atas, dipastikan bajakan dan rawan adanya <strong>skrip sisipan berbahaya/malware</strong>. Hal ini di luar tanggung jawab DapurKode!
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 mb-4 rounded-4 bg-light border text-center" style="border-style: dashed !important; border-width: 2px !important; border-color: #dee2e6 !important;">
                        <div class="text-muted small fw-bolder mb-2 text-uppercase" style="letter-spacing: 1.5px; font-size: 0.75rem;">Kontak Resmi Kami</div>
                        <a href="https://wa.me/6285691257411?text=Halo, saya tertarik dengan jadiorder" target="_blank" class="btn btn-outline-success rounded-pill fw-bold d-inline-flex align-items-center gap-2 px-4 shadow-sm">
                           <i class="bi bi-whatsapp fs-5"></i> +62 856-9125-7411
                        </a>
                        <div class="mt-2 small text-muted" style="font-size: 0.75rem;">Selain nomor di atas adalah <b class="text-danger">palsu</b>!</div>
                    </div>

                    <button type="button" class="btn btn-lg w-100 btn-gorgeous text-uppercase px-5 py-3" data-bs-dismiss="modal">
                        <i class="bi bi-check2-circle me-1 fs-5 align-middle"></i> Saya Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
    @if(isset($flashSale))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const timerEl = document.getElementById('flashSaleTimer');
                if (!timerEl) return;

                const endStr = timerEl.getAttribute('data-end');
                if (!endStr) return;

                // Convert yyyy-mm-dd hh:mm:ss to compatible timestamp
                const countDownDate = new Date(endStr.replace(/-/g, "/")).getTime();

                const hoursEl = timerEl.querySelector('.hours');
                const minutesEl = timerEl.querySelector('.minutes');
                const secondsEl = timerEl.querySelector('.seconds');

                const x = setInterval(function () {
                    const now = new Date().getTime();
                    const distance = countDownDate - now;

                    if (distance < 0) {
                        clearInterval(x);
                        hoursEl.innerHTML = "00";
                        minutesEl.innerHTML = "00";
                        secondsEl.innerHTML = "00";
                        return;
                    }

                    // Time calculations
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)) + Math.floor(distance / (1000 * 60 * 60 * 24)) * 24;
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    hoursEl.innerHTML = hours.toString().padStart(2, '0');
                    minutesEl.innerHTML = minutes.toString().padStart(2, '0');
                    secondsEl.innerHTML = seconds.toString().padStart(2, '0');

                }, 1000);
            });
        </script>
    @endif

    @if(config('app.demo_mode'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Tampilkan Modal Peringatan Demo Mode sekali tiap sesi
                if (!sessionStorage.getItem('demoNoticeShowed')) {
                    var demoModal = new bootstrap.Modal(document.getElementById('demoNoticeModal'));
                    demoModal.show();
                    
                    document.getElementById('demoNoticeModal').addEventListener('hidden.bs.modal', function () {
                        sessionStorage.setItem('demoNoticeShowed', 'true');
                    });
                }
            });
        </script>
    @endif
@endpush