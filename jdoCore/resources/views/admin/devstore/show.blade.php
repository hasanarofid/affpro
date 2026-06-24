@extends('admin.layouts.app')
@section('title', ($product['name'] ?? 'Detail') . ' — DK-DevStore')
@section('page-title', 'DK-DevStore')

@section('styles')
    <style>
        .detail-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .detail-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .detail-main-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .detail-thumbnail {
            width: 100%;
            height: 280px;
            object-fit: cover;
            border-radius: 16px;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8ecff 100%);
        }

        .detail-thumbnail-placeholder {
            width: 100%;
            height: 280px;
            border-radius: 16px;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8ecff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tier-card {
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.25rem;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .tier-card:hover,
        .tier-card.selected {
            border-color: var(--bs-primary, #667eea);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.15);
        }

        .tier-card.selected::after {
            content: '\F26A';
            font-family: 'bootstrap-icons';
            position: absolute;
            top: -10px;
            right: -10px;
            width: 26px;
            height: 26px;
            background: var(--bs-primary, #667eea);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .tier-popular {
            border-color: var(--bs-primary, #667eea);
            background: rgba(102, 126, 234, 0.03);
        }

        .tag-pill {
            display: inline-block;
            padding: 4px 10px;
            font-size: 0.72rem;
            font-weight: 500;
            border-radius: 6px;
            background: #f1f5f9;
            color: #475569;
            margin: 2px;
        }

        .screenshot-thumb {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .screenshot-thumb:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, .1);
            border-color: var(--bs-primary, #667eea);
        }

        .version-timeline {
            position: relative;
            padding-left: 1.5rem;
        }

        .version-timeline::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .version-item {
            position: relative;
            margin-bottom: 1.5rem;
            padding-left: 0.5rem;
        }

        .version-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 6px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e2e8f0;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #e2e8f0;
        }

        .version-item.latest::before {
            background: var(--bs-primary, #667eea);
            box-shadow: 0 0 0 2px var(--bs-primary, #667eea);
        }

        .btn-purchase {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            padding: 12px 24px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .btn-purchase:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-download-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            padding: 12px 24px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .btn-download-green:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .label {
            color: #94a3b8;
            font-weight: 500;
        }

        .info-row .value {
            font-weight: 600;
            color: #334155;
        }
    </style>
@endsection

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.devstore.index') }}" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke DK-DevStore
        </a>
    </div>

    <div class="row g-4">
        {{-- Left Column: Main Info --}}
        <div class="col-lg-8">
            <div class="card detail-main-card">
                <div class="card-body p-4">
                    {{-- Thumbnail --}}
                    @if(!empty($product['thumbnail']))
                        <img src="{{ $product['thumbnail'] }}" class="detail-thumbnail mb-4" alt="{{ $product['name'] }}">
                    @else
                        <div class="detail-thumbnail-placeholder mb-4">
                            <i class="bi bi-{{ ($product['category_type'] ?? 'module') === 'theme' ? 'palette2' : 'puzzle' }}"
                                style="font-size: 4rem; opacity: 0.2; color: var(--bs-primary);"></i>
                        </div>
                    @endif

                    {{-- Title + Meta --}}
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <h3 class="fw-bold mb-1">{{ $product['name'] }}</h3>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span
                                    class="badge {{ ($product['category_type'] ?? '') === 'theme' ? 'bg-purple-subtle text-purple' : 'bg-info-subtle text-info' }}"
                                    style="font-size:0.75rem; padding: 5px 12px; border-radius:8px;">
                                    {{ $product['category'] ?? ucfirst($product['category_type'] ?? 'Module') }}
                                </span>
                                @if(isset($product['latest_version']))
                                    <span class="badge bg-light text-dark"
                                        style="font-size:0.72rem;">v{{ $product['latest_version'] }}</span>
                                @endif
                                @if($product['is_featured'] ?? false)
                                    <span class="badge"
                                        style="background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff;font-size:0.7rem;padding:4px 10px;border-radius:6px;">
                                        <i class="bi bi-star-fill me-1"></i>Unggulan
                                    </span>
                                @endif
                                @if($isInstalled ?? false)
                                    <span class="badge"
                                        style="background:linear-gradient(135deg,#0ea5e9,#0284c7);color:#fff;font-size:0.7rem;padding:4px 10px;border-radius:6px;">
                                        <i class="bi bi-hdd-fill me-1"></i>Terinstall
                                        @if($installedVersion) v{{ $installedVersion }} @endif
                                    </span>
                                @endif
                                @if($isOwned)
                                    <span class="badge"
                                        style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:0.7rem;padding:4px 10px;border-radius:6px;">
                                        <i class="bi bi-check-circle-fill me-1"></i>Dimiliki
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Short description --}}
                    @if(!empty($product['short_description']))
                        <p class="text-muted mb-4">{{ $product['short_description'] }}</p>
                    @endif

                    {{-- Full description --}}
                    @if(!empty($product['description']))
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-file-text me-2"></i>Deskripsi</h6>
                            <div class="small" style="line-height:1.7;">
                                {!! $product['description'] !!}
                            </div>
                        </div>
                    @endif

                    {{-- Screenshots --}}
                    @if(!empty($product['screenshots']))
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-images me-2"></i>Tangkapan Layar</h6>
                            <div class="row g-3">
                                @foreach($product['screenshots'] as $ss)
                                    <div class="col-4 col-md-3">
                                        <img src="{{ $ss }}" class="screenshot-thumb" alt="Screenshot"
                                            onclick="window.open(this.src, '_blank')">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Tags --}}
                    @if(!empty($product['tags']))
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-tags me-2"></i>Tags</h6>
                            @foreach($product['tags'] as $tag)
                                <span class="tag-pill">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Version History --}}
                    @if(!empty($product['versions']))
                        <div class="mb-2">
                            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Riwayat Versi</h6>
                            <div class="version-timeline">
                                @foreach($product['versions'] as $i => $ver)
                                    <div class="version-item {{ ($ver['is_latest'] ?? $i === 0) ? 'latest' : '' }}">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <strong>v{{ $ver['version'] }}</strong>
                                            @if($ver['is_latest'] ?? $i === 0)
                                                <span class="badge bg-success" style="font-size:0.65rem;">Terbaru</span>
                                            @endif
                                            @if(!empty($ver['file_size']))
                                                <span class="text-muted small">{{ $ver['file_size'] }}</span>
                                            @endif
                                        </div>
                                        @if(!empty($ver['released_at']))
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($ver['released_at'])->format('d M Y') }}</small>
                                        @endif
                                        @if(!empty($ver['changelog']))
                                            <pre class="mt-1 p-2 bg-light small mb-0"
                                                style="border-radius:8px; white-space:pre-wrap; font-family: inherit; font-size:0.8rem;">{{ $ver['changelog'] }}</pre>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Purchase / Download Sidebar --}}
        <div class="col-lg-4">
            {{-- Action Card --}}
            <div class="card border-0 mb-4"
                style="border-radius:16px; box-shadow:0 6px 30px rgba(0,0,0,.06); position:sticky; top:90px;">
                <div class="card-body p-4">
                    @if($isInstalled ?? false)
                        {{-- Installed locally --}}
                        <div class="text-center mb-3">
                            <div style="width:60px;height:60px;margin:0 auto 1rem;border-radius:16px;background:rgba(14,165,233,0.1);display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-hdd-fill" style="font-size:1.8rem; color:#0284c7;"></i>
                            </div>
                            <h5 class="fw-bold mb-1">Sudah Terinstall</h5>
                            <p class="text-muted small mb-0">Versi lokal: <code>v{{ $installedVersion ?? '?' }}</code></p>
                        </div>

                        @php
                            $latestVersion = $product['latest_version'] ?? null;
                            $hasUpdate = $installedVersion && $latestVersion && version_compare($installedVersion, $latestVersion, '<');
                        @endphp

                        @if($hasUpdate)
                            <div class="alert border-0 mb-3" style="border-radius:12px; background:rgba(245,158,11,0.1); color:#92400e;">
                                <i class="bi bi-arrow-up-circle me-1"></i>
                                <strong>Update tersedia!</strong>
                                v{{ $installedVersion }} → v{{ $latestVersion }}
                            </div>
                        @else
                            <div class="alert border-0 mb-3" style="border-radius:12px; background:rgba(16,185,129,0.06); color:#166534;">
                                <i class="bi bi-check-circle me-1"></i> Versi terbaru sudah terinstall.
                            </div>
                        @endif

                        @if($isOwned && $license)
                            @if(!empty($license['download_url']))
                                <form action="{{ route('admin.devstore.install') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="download_url" value="{{ $license['download_url'] }}">
                                    <input type="hidden" name="type" value="{{ $product['category_type'] ?? 'module' }}">
                                    <button type="submit" class="btn {{ $hasUpdate ? 'btn-purchase' : 'btn-download-green' }} w-100 mb-2"
                                        onclick="this.innerHTML='<i class=\'bi bi-arrow-repeat spin me-1\'></i> {{ $hasUpdate ? 'Mengupdate...' : 'Menginstal...' }}';this.disabled=true;this.form.submit()">
                                        <i class="bi bi-{{ $hasUpdate ? 'arrow-up-circle' : 'download' }} me-2"></i>
                                        {{ $hasUpdate ? 'Update ke v' . $latestVersion : 'Re-install' }}
                                    </button>
                                </form>
                            @endif
                        @elseif(!$isOwned)
                            <div class="alert border-0 mb-3" style="border-radius:12px; background:rgba(239,68,68,0.06); color:#991b1b; font-size:0.85rem;">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <strong>Perhatian:</strong> Modul terinstall tetapi belum terdaftar sebagai pembelian resmi di DevStore. Untuk mendapatkan update otomatis, harap verifikasi pembelian Anda.
                            </div>
                        @endif

                        <div class="info-row">
                            <span class="label">Status</span>
                            <span class="value" style="color:#0284c7;">Terinstall</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Versi Lokal</span>
                            <span class="value">v{{ $installedVersion ?? '?' }}</span>
                        </div>
                        @if($latestVersion)
                            <div class="info-row">
                                <span class="label">Versi Terbaru</span>
                                <span class="value">v{{ $latestVersion }}</span>
                            </div>
                        @endif
                    @elseif($isOwned && $license)
                        {{-- Owned but not installed → Show download --}}
                        <div class="text-center mb-3">
                            <div
                                style="width:60px;height:60px;margin:0 auto 1rem;border-radius:16px;background:rgba(16,185,129,0.1);display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-check-circle-fill text-success" style="font-size:1.8rem;"></i>
                            </div>
                            <h5 class="fw-bold mb-1">Produk Dimiliki</h5>
                            <p class="text-muted small mb-0">Lisensi:
                                <code>{{ \Illuminate\Support\Str::limit($license['license_key'] ?? '-', 16) }}</code></p>
                        </div>

                        @if(!empty($license['download_url']))
                            <form action="{{ route('admin.devstore.install') }}" method="POST">
                                @csrf
                                <input type="hidden" name="download_url" value="{{ $license['download_url'] }}">
                                <input type="hidden" name="type" value="{{ $product['category_type'] ?? 'module' }}">
                                <button type="submit" class="btn btn-download-green w-100 mb-2"
                                    onclick="this.innerHTML='<i class=\'bi bi-arrow-repeat spin me-1\'></i> Menginstal...';this.disabled=true;this.form.submit()">
                                    <i class="bi bi-download me-2"></i>Download & Instal
                                </button>
                            </form>
                        @endif

                        <div class="info-row">
                            <span class="label">Status</span>
                            <span class="value text-success">{{ ucfirst($license['status'] ?? 'active') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Domain Limit</span>
                            <span class="value">{{ $license['domain_limit'] ?? '-' }}</span>
                        </div>
                    @else
                        {{-- Not owned → Show pricing tiers --}}
                        <h5 class="fw-bold mb-1">
                            @if(isset($product['price']) && $product['price'] > 0)
                                Rp {{ number_format($product['price'], 0, ',', '.') }}
                            @else
                                <span class="text-success">Gratis</span>
                            @endif
                        </h5>
                        <p class="text-muted small mb-3">Pilih paket yang sesuai:</p>

                        @if(!empty($product['pricing_tiers']))
                            <div class="d-flex flex-column gap-2 mb-3" id="tiersList">
                                @foreach($product['pricing_tiers'] as $tier)
                                    <div class="tier-card {{ ($tier['is_popular'] ?? false) ? 'tier-popular' : '' }}"
                                        data-tier-id="{{ $tier['id'] }}" data-price="{{ $tier['price'] }}" onclick="selectTier(this)">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong style="font-size:0.9rem;">{{ $tier['name'] }}</strong>
                                                @if($tier['is_popular'] ?? false)
                                                    <span class="badge bg-primary" style="font-size:0.6rem;">Populer</span>
                                                @endif
                                                <div class="text-muted small mt-1">
                                                    {{ $tier['description'] ?? $tier['domain_limit'] . ' domain' }}</div>
                                            </div>
                                            <div class="fw-bold" style="color:var(--bs-primary);">
                                                Rp {{ number_format($tier['price'], 0, ',', '.') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button class="btn btn-purchase w-100" id="btnPurchase" disabled
                                onclick="initPurchase('{{ $product['slug'] }}')">
                                <i class="bi bi-bag-check me-2"></i>Beli Sekarang
                            </button>
                        @else
                            <p class="text-muted small">Pricing belum tersedia.</p>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Info Card --}}
            <div class="card border-0" style="border-radius:16px; box-shadow:0 4px 20px rgba(0,0,0,.04);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Informasi</h6>
                    <div class="info-row">
                        <span class="label">Tipe</span>
                        <span class="value">{{ ucfirst($product['category_type'] ?? 'Module') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Kategori</span>
                        <span class="value">{{ $product['category'] ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Platform</span>
                        <span class="value">{{ $product['platform'] ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Download</span>
                        <span class="value">{{ number_format($product['download_count'] ?? 0) }}x</span>
                    </div>
                    @if(isset($product['requirements']))
                        <div class="info-row">
                            <span class="label">PHP</span>
                            <span class="value">{{ $product['requirements']['php'] ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Laravel</span>
                            <span class="value">{{ $product['requirements']['laravel'] ?? '-' }}</span>
                        </div>
                    @endif
                    @if(isset($product['vendor']))
                        <div class="info-row">
                            <span class="label">Vendor</span>
                            <span class="value">
                                {{ $product['vendor']['name'] ?? '-' }}
                                @if($product['vendor']['is_official'] ?? false)
                                    <i class="bi bi-patch-check-fill text-primary ms-1" title="Official"></i>
                                @endif
                            </span>
                        </div>
                    @endif
                    @if(!empty($product['demo_url']))
                        <a href="{{ $product['demo_url'] }}" target="_blank" class="btn btn-light btn-sm w-100 mt-3"
                            style="border-radius:10px;">
                            <i class="bi bi-globe me-1"></i> Lihat Demo
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Purchase Modal --}}
    <div class="modal fade" id="purchaseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border:none; border-radius:16px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-bag-check me-2"></i>Konfirmasi Pembelian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="purchaseModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted small mt-2">Memuat informasi pembayaran...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let selectedTierId = null;

        function selectTier(el) {
            document.querySelectorAll('.tier-card').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            selectedTierId = parseInt(el.dataset.tierId);
            document.getElementById('btnPurchase').disabled = false;
        }

        function initPurchase(slug) {
            if (!selectedTierId) return;

            const modal = new bootstrap.Modal(document.getElementById('purchaseModal'));
            modal.show();

            $.ajax({
                url: '{{ route("admin.devstore.purchase.init") }}',
                method: 'POST',
                data: { product_slug: slug, tier_id: selectedTierId, _token: window.csrfToken },
                success: function (data) {
                    if (data.already_owned) {
                        $('#purchaseModalBody').html(`
                        <div class="text-center py-3">
                            <i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
                            <h5 class="fw-bold mt-3">Sudah Dimiliki!</h5>
                            <p class="text-muted">${data.message}</p>
                            <a href="${window.location.href}" class="btn btn-primary" style="border-radius:10px;">Refresh Halaman</a>
                        </div>
                    `);
                        return;
                    }

                    let paymentHtml = '';
                    (data.payment_methods || []).forEach(pm => {
                        if (pm.type === 'bank') {
                            paymentHtml += `<div class="border rounded p-3 mb-2" style="border-radius:10px!important;">
                            <strong>${pm.bank_name}</strong><br>
                            <small class="text-muted">No. Rek:</small> <strong>${pm.account_number}</strong><br>
                            <small class="text-muted">A/N:</small> ${pm.account_holder}
                        </div>`;
                        } else if (pm.type === 'qris' && pm.qris_image) {
                            paymentHtml += `<div class="text-center border rounded p-3 mb-2" style="border-radius:10px!important;">
                            <img src="${pm.qris_image}" style="max-width:200px;" alt="QRIS"><br>
                            <small class="text-muted mt-2 d-block">Scan QRIS untuk pembayaran</small>
                        </div>`;
                        }
                    });

                    $('#purchaseModalBody').html(`
                    <div class="alert alert-info border-0" style="border-radius:12px;background:rgba(102,126,234,0.08);">
                        <strong>${data.product?.name || ''}</strong> — ${data.tier?.name || ''}<br>
                        <span class="fw-bold" style="font-size:1.2rem;color:var(--bs-primary);">
                            Rp ${Number(data.tier?.price || 0).toLocaleString('id-ID')}
                        </span>
                    </div>
                    <h6 class="fw-bold mb-2">Metode Pembayaran</h6>
                    ${paymentHtml}
                    <div class="d-grid gap-2 mt-3">
                        <select class="form-select" id="paymentMethod" style="border-radius:10px;">
                            <option value="manual_transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                        </select>
                        <button class="btn btn-purchase" onclick="createOrder('${slug}', ${selectedTierId})">
                            <i class="bi bi-cart-check me-2"></i>Buat Pesanan
                        </button>
                    </div>
                `);
                },
                error: function (xhr) {
                    $('#purchaseModalBody').html(`
                    <div class="text-center py-3">
                        <i class="bi bi-exclamation-circle text-danger" style="font-size:2.5rem;"></i>
                        <h5 class="fw-bold mt-3">Gagal</h5>
                        <p class="text-muted">${xhr.responseJSON?.error || 'Terjadi kesalahan'}</p>
                    </div>
                `);
                }
            });
        }

        function createOrder(slug, tierId) {
            let method = $('#paymentMethod').val();
            $('#purchaseModalBody').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">Membuat pesanan...</p>
            </div>
        `);

            $.ajax({
                url: '{{ route("admin.devstore.purchase.create") }}',
                method: 'POST',
                data: {
                    product_slug: slug,
                    tier_id: tierId,
                    payment_method: method,
                    _token: window.csrfToken
                },
                success: function (data) {
                    if (data.order) {
                        let instructionsHtml = '';
                        (data.payment_instructions || []).forEach(pi => {
                            if (pi.bank_name) {
                                instructionsHtml += `<div class="border rounded p-3 mb-2" style="border-radius:10px !important;">
                                <strong>${pi.bank_name}</strong> — ${pi.account_number}<br>
                                <small class="text-muted">A/N: ${pi.account_holder}</small>
                            </div>`;
                            }
                            if (pi.qris_image) {
                                instructionsHtml += `<div class="text-center border rounded p-3 mb-2" style="border-radius:10px !important;">
                                <img src="${pi.qris_image}" style="max-width:180px;" alt="QRIS">
                            </div>`;
                            }
                        });

                        $('#purchaseModalBody').html(`
                        <div class="text-center mb-3">
                            <i class="bi bi-receipt-cutoff text-success" style="font-size:2.5rem;"></i>
                            <h5 class="fw-bold mt-2">Pesanan Dibuat!</h5>
                            <p class="text-muted small">${data.message}</p>
                        </div>
                        <div class="alert alert-light border" style="border-radius:12px;">
                            <small class="text-muted">No. Order</small><br>
                            <strong style="font-size:1.1rem;">${data.order.order_number}</strong><br>
                            <small class="text-muted">Total:</small>
                            <strong>Rp ${Number(data.order.total).toLocaleString('id-ID')}</strong>
                        </div>
                        <h6 class="fw-bold mb-2">Instruksi Pembayaran</h6>
                        ${instructionsHtml}
                        <form id="proofForm" enctype="multipart/form-data" class="mt-3">
                            <label class="form-label small fw-bold">Upload Bukti Pembayaran</label>
                            <input type="file" class="form-control mb-2" id="proofImage" accept="image/*" style="border-radius:10px;">
                            <button type="button" class="btn btn-purchase w-100" onclick="uploadProof('${data.order.order_number}')">
                                <i class="bi bi-upload me-2"></i>Upload Bukti Bayar
                            </button>
                        </form>
                    `);
                    }
                },
                error: function (xhr) {
                    $('#purchaseModalBody').html(`
                    <div class="text-center py-3">
                        <i class="bi bi-exclamation-circle text-danger" style="font-size:2.5rem;"></i>
                        <h5 class="fw-bold mt-3">Gagal Membuat Pesanan</h5>
                        <p class="text-muted">${xhr.responseJSON?.error || xhr.responseJSON?.message || 'Error'}</p>
                    </div>
                `);
                }
            });
        }

        function uploadProof(orderNumber) {
            let fileInput = document.getElementById('proofImage');
            if (!fileInput.files.length) {
                alert('Pilih file bukti pembayaran terlebih dahulu.');
                return;
            }

            let formData = new FormData();
            formData.append('order_number', orderNumber);
            formData.append('proof_image', fileInput.files[0]);
            formData.append('_token', window.csrfToken);

            $('#purchaseModalBody').append(`<div class="text-center mt-3" id="uploadingIndicator">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="small text-muted ms-2">Mengunggah...</span>
        </div>`);

            $.ajax({
                url: '{{ route("admin.devstore.purchase.upload-proof") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (data) {
                    $('#purchaseModalBody').html(`
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
                        <h5 class="fw-bold mt-3">Bukti Berhasil Diupload!</h5>
                        <p class="text-muted">${data.message || 'Menunggu verifikasi admin.'}</p>
                        <small class="text-muted">Order: <strong>${orderNumber}</strong></small>
                    </div>
                `);
                },
                error: function (xhr) {
                    $('#uploadingIndicator').remove();
                    alert('Gagal upload: ' + (xhr.responseJSON?.error || 'Error'));
                }
            });
        }
    </script>
@endsection