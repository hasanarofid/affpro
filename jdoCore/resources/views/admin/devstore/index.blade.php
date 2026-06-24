@extends('admin.layouts.app')
@section('title', 'DK-DevStore')
@section('page-title', 'DK-DevStore')

@section('styles')
    <style>
        .devstore-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .devstore-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .devstore-hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .devstore-hero h2 {
            font-weight: 800;
            font-size: 1.8rem;
            position: relative;
            z-index: 1;
        }

        .devstore-hero p {
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .devstore-filter-bar {
            background: #fff;
            border-radius: 16px;
            padding: 1rem 1.25rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        [data-theme="dark"] .devstore-filter-bar {
            background: var(--card-bg);
        }

        .devstore-filter-bar .form-control,
        .devstore-filter-bar .form-select {
            border-radius: 10px;
            font-size: 0.85rem;
            border: 1px solid #e2e8f0;
        }

        .devstore-tab {
            display: inline-flex;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 4px;
            gap: 4px;
        }

        .devstore-tab a {
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .devstore-tab a:hover {
            background: rgba(0, 0, 0, 0.03);
            color: #334155;
        }

        .devstore-tab a.active {
            background: #fff;
            color: var(--bs-primary, #667eea);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        [data-theme="dark"] .devstore-tab {
            background: rgba(255, 255, 255, 0.05);
        }

        [data-theme="dark"] .devstore-tab a.active {
            background: var(--card-bg);
        }

        .product-card {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
        }

        .product-card .card-img-top-wrapper {
            height: 180px;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8ecff 100%);
            position: relative;
            overflow: hidden;
        }

        .product-card .card-img-top-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .product-card:hover .card-img-top-wrapper img {
            transform: scale(1.05);
        }

        .product-card .card-img-top-wrapper .placeholder-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .product-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 2;
        }

        .badge-featured {
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            color: white;
            font-size: 0.7rem;
            padding: 5px 10px;
            border-radius: 8px;
            font-weight: 700;
        }

        .badge-owned {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            font-size: 0.7rem;
            padding: 5px 10px;
            border-radius: 8px;
            font-weight: 700;
        }

        .badge-type {
            font-size: 0.7rem;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
        }

        .badge-theme {
            background: rgba(139, 92, 246, 0.1);
            color: #8b5cf6;
        }

        .badge-module {
            background: rgba(14, 165, 233, 0.1);
            color: #0ea5e9;
        }

        .product-price {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--bs-primary, #667eea);
        }

        .product-price .text-muted {
            font-size: 0.75rem;
            font-weight: 400;
        }

        .product-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .product-meta i {
            font-size: 0.7rem;
        }

        .btn-install {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.82rem;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }

        .btn-install:hover {
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.35);
        }

        .btn-download-owned {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.82rem;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }

        .btn-download-owned:hover {
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
        }

        .unconfigured-card {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: none;
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
        }

        .unconfigured-card .icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 20px;
            background: rgba(245, 158, 11, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stats-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 0.82rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state .icon-circle {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8ecff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .license-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .license-valid {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .license-invalid {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .spin {
            animation: spin 1s linear infinite;
            display: inline-block;
        }
    </style>
@endsection

@section('content')

    @if(!$configured)
        {{-- License not configured --}}
        <div class="unconfigured-card">
            <div class="icon-wrapper">
                <i class="bi bi-key-fill text-warning" style="font-size: 2.5rem;"></i>
            </div>
            <h4 class="fw-bold mb-2">Hubungkan DK-DevStore</h4>
            <p class="text-muted mb-4" style="max-width: 450px; margin: 0 auto;">
                Masukkan License Key untuk mengakses ribuan tema & modul resmi dari DK-DevStore. <br>
                Instal, perbarui, dan kelola langsung dari panel admin Anda.
            </p>
            <a href="{{ route('admin.settings.index') }}" class="btn btn-install px-4 py-2">
                <i class="bi bi-gear me-2"></i> Buka Pengaturan
            </a>
        </div>
    @else
        {{-- Hero --}}
        <div class="devstore-hero">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <div>
                    <h2><i class="bi bi-box-seam-fill me-2"></i> DK-DevStore</h2>
                    <p class="mb-3">Marketplace Resmi — Instal tema & modul premium langsung dari sini.</p>
                    <div class="d-flex flex-wrap gap-2">
                        @if(($license['valid'] ?? false))
                            <span class="stats-pill">
                                <i class="bi bi-shield-check"></i> Lisensi Aktif
                            </span>
                            @if(isset($license['data']['product']))
                                <span class="stats-pill">
                                    <i class="bi bi-box-seam"></i> {{ $license['data']['product'] }}
                                </span>
                            @endif
                            @if(isset($license['data']['domains_used']) && isset($license['data']['domain_limit']))
                                <span class="stats-pill">
                                    <i class="bi bi-globe2"></i>
                                    {{ $license['data']['domains_used'] }}/{{ $license['data']['domain_limit'] }} Domain
                                </span>
                            @endif
                        @else
                            <span class="stats-pill" style="background: rgba(239,68,68,0.2);">
                                <i class="bi bi-shield-exclamation"></i> {{ $license['message'] ?? 'Lisensi tidak valid' }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="{{ route('admin.devstore.my-products') }}" class="btn btn-light btn-sm"
                        style="border-radius:10px; font-weight:600;">
                        <i class="bi bi-collection me-1"></i> Produk Saya
                    </a>
                    <button class="btn btn-light btn-sm" style="border-radius:10px; font-weight:600;"
                        onclick="checkForUpdates()">
                        <i class="bi bi-arrow-repeat me-1"></i> Cek Update
                    </button>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="devstore-filter-bar">
            <div class="devstore-tab">
                <a href="{{ route('admin.devstore.index') }}" class="{{ !request('type') ? 'active' : '' }}">Semua</a>
                <a href="{{ route('admin.devstore.index', ['type' => 'theme'] + request()->except('type', 'page')) }}"
                    class="{{ request('type') === 'theme' ? 'active' : '' }}">
                    <i class="bi bi-palette me-1"></i> Tema
                </a>
                <a href="{{ route('admin.devstore.index', ['type' => 'module'] + request()->except('type', 'page')) }}"
                    class="{{ request('type') === 'module' ? 'active' : '' }}">
                    <i class="bi bi-puzzle me-1"></i> Modul
                </a>
            </div>

            <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
                <form action="{{ route('admin.devstore.index') }}" method="GET" class="d-flex gap-2">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    <div class="input-group" style="max-width: 260px;">
                        <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px;">
                            <i class="bi bi-search text-muted" style="font-size:0.8rem;"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Cari tema atau modul..." value="{{ request('search') }}"
                            style="border-radius:0 10px 10px 0;">
                    </div>
                    <select name="sort" class="form-select" style="width:auto;" onchange="this.form.submit()">
                        <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Populer</option>
                        <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga ↑</option>
                        <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga ↓</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nama A-Z</option>
                    </select>
                </form>
            </div>
        </div>

        {{-- Products Grid --}}
        <div class="row g-4">
            @forelse($products as $product)
                <div class="col-md-6 col-xl-4">
                    <div class="card product-card">
                        <div class="card-img-top-wrapper">
                            @if(!empty($product['thumbnail']))
                                <img src="{{ $product['thumbnail'] }}" alt="{{ $product['name'] }}">
                            @else
                                <div class="placeholder-icon">
                                    @if(($product['category_type'] ?? '') === 'theme')
                                        <i class="bi bi-palette2 text-primary" style="font-size:3.5rem; opacity:0.2;"></i>
                                    @else
                                        <i class="bi bi-puzzle text-primary" style="font-size:3.5rem; opacity:0.2;"></i>
                                    @endif
                                </div>
                            @endif

                            <div class="product-badge d-flex gap-1">
                                @if($product['is_installed'] ?? false)
                                    <span class="badge-owned" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                                        <i class="bi bi-hdd-fill me-1"></i>Terinstall
                                        @if($product['installed_version'] ?? false)
                                            v{{ $product['installed_version'] }}
                                        @endif
                                    </span>
                                @endif
                                @if($product['is_owned'] ?? false)
                                    <span class="badge-owned"><i class="bi bi-check-circle-fill me-1"></i>Dimiliki</span>
                                @endif
                                @if($product['is_featured'] ?? false)
                                    <span class="badge-featured"><i class="bi bi-star-fill me-1"></i>Unggulan</span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body pb-2">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div>
                                    <h6 class="fw-bold mb-1" style="font-size: 0.95rem;">{{ $product['name'] }}</h6>
                                    <span
                                        class="badge-type {{ ($product['category_type'] ?? '') === 'theme' ? 'badge-theme' : 'badge-module' }}">
                                        {{ $product['category'] ?? ucfirst($product['category_type'] ?? 'module') }}
                                    </span>
                                </div>
                                @if(isset($product['latest_version']))
                                    <span class="badge bg-light text-dark" style="font-size:0.7rem; border-radius:6px;">
                                        v{{ $product['latest_version'] }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-muted small mb-2" style="min-height: 36px; line-height:1.4;">
                                {{ \Illuminate\Support\Str::limit($product['short_description'] ?? '', 90) }}
                            </p>
                            <div class="product-meta mb-2">
                                @if(isset($product['download_count']))
                                    <span><i class="bi bi-download me-1"></i>{{ number_format($product['download_count']) }}</span>
                                @endif
                                @if(isset($product['platform']))
                                    <span><i class="bi bi-cpu me-1"></i>{{ $product['platform'] }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="card-footer bg-transparent border-top-0 pt-0 pb-3">
                            <div class="d-flex align-items-center justify-content-between">
                                @if($product['is_installed'] ?? false)
                                    <span class="fw-bold small" style="color: #0284c7;">
                                        <i class="bi bi-hdd-fill me-1"></i>Sudah Terinstall
                                    </span>
                                @elseif($product['is_owned'] ?? false)
                                    <span class="text-success fw-bold small"><i class="bi bi-check-circle me-1"></i>Sudah
                                        Dimiliki</span>
                                @else
                                    <span class="product-price">
                                        @if(isset($product['price']) && $product['price'] > 0)
                                            Rp {{ number_format($product['price'], 0, ',', '.') }}
                                        @else
                                            <span class="text-success">Gratis</span>
                                        @endif
                                    </span>
                                @endif

                                <a href="{{ route('admin.devstore.show', $product['slug']) }}" class="btn btn-sm btn-install">
                                    <i class="bi bi-arrow-right me-1"></i> Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-state">
                        <div class="icon-circle">
                            <i class="bi bi-box-seam text-primary" style="font-size: 2.5rem; opacity:0.4;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Belum Ada Produk</h5>
                        <p class="text-muted" style="max-width: 360px; margin: 0 auto;">
                            @if(request('search'))
                                Tidak ditemukan produk dengan kata kunci "{{ request('search') }}". Coba ubah filter.
                            @else
                                Produk di DK-DevStore belum tersedia atau server tidak dapat dihubungi.
                            @endif
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($pagination && ($pagination['last_page'] ?? 1) > 1)
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination pagination-sm">
                        @for($i = 1; $i <= $pagination['last_page']; $i++)
                            <li class="page-item {{ $i == ($pagination['current_page'] ?? 1) ? 'active' : '' }}">
                                <a class="page-link"
                                    href="{{ route('admin.devstore.index', array_merge(request()->all(), ['page' => $i])) }}"
                                    style="border-radius: 8px; margin: 0 2px;">{{ $i }}</a>
                            </li>
                        @endfor
                    </ul>
                </nav>
            </div>
        @endif
    @endif

    {{-- Update Check Modal --}}
    <div class="modal fade" id="updateModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border:none; border-radius:16px;">
                <div class="modal-header border-0"
                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:white; border-radius:16px 16px 0 0;">
                    <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i>Cek Pembaruan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="updateModalBody" style="min-height: 200px;">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status"></div>
                        <p class="text-muted">Memeriksa pembaruan dari DK-DevStore...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function checkForUpdates() {
            const modal = new bootstrap.Modal(document.getElementById('updateModal'));
            modal.show();

            $.ajax({
                url: '{{ route("admin.devstore.check-updates") }}',
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': window.csrfToken },
                success: function (data) {
                    let updates = data.updates || [];
                    let hasUpdates = updates.filter(u => u.has_update).length;
                    let html = '';

                    if (hasUpdates === 0) {
                        html = `<div class="text-center py-4">
                        <div style="width:70px;height:70px;margin:0 auto 1rem;border-radius:50%;background:rgba(16,185,129,0.1);display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-check-circle-fill text-success" style="font-size:2rem;"></i>
                        </div>
                        <h5 class="fw-bold">Semua Terupdate!</h5>
                        <p class="text-muted small">Tidak ada pembaruan yang tersedia saat ini.</p>
                    </div>`;
                    } else {
                        html = `<div class="alert alert-info border-0" style="border-radius:12px; background:rgba(102,126,234,0.08);">
                        <strong>${hasUpdates} pembaruan</strong> tersedia.
                    </div>`;
                        updates.forEach(u => {
                            if (u.has_update) {
                                html += `<div class="card border-0 mb-3" style="border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.04);">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="fw-bold mb-1">${u.product_name}</h6>
                                            <span class="badge bg-warning text-dark me-1" style="font-size:0.7rem;">${u.current_version} → ${u.latest_version}</span>
                                            ${u.file_size ? `<span class="text-muted small">${u.file_size}</span>` : ''}
                                        </div>
                                        ${u.download_url ?
                                        `<form action="{{ route('admin.devstore.install') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="download_url" value="${u.download_url}">
                                                <input type="hidden" name="type" value="module">
                                                <button type="submit" class="btn btn-sm btn-install">
                                                    <i class="bi bi-download me-1"></i> Update
                                                </button>
                                            </form>` : ''}
                                    </div>
                                    ${u.changelog ? `<pre class="mt-2 p-2 bg-light small mb-0" style="border-radius:8px; white-space:pre-wrap; font-family:inherit;">${u.changelog}</pre>` : ''}
                                </div>
                            </div>`;
                            }
                        });
                    }
                    $('#updateModalBody').html(html);
                },
                error: function () {
                    $('#updateModalBody').html(`<div class="text-center py-4">
                    <i class="bi bi-wifi-off text-danger" style="font-size:2.5rem;"></i>
                    <h5 class="fw-bold mt-3">Gagal Terhubung</h5>
                    <p class="text-muted small">Pastikan koneksi internet tersedia dan license key valid.</p>
                </div>`);
                }
            });
        }
    </script>
@endsection