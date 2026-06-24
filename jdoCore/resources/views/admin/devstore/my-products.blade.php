@extends('admin.layouts.app')
@section('title', 'Produk Saya — DK-DevStore')
@section('page-title', 'DK-DevStore')

@section('styles')
    <style>
        .my-products-hero {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 20px;
            padding: 2rem 2.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .my-products-hero::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -15%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .my-product-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .my-product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
        }

        .license-key-display {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 8px 14px;
            font-family: monospace;
            font-size: 0.8rem;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        [data-theme="dark"] .license-key-display {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: #aaa;
        }

        .domain-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 500;
            background: #f1f5f9;
            color: #64748b;
        }

        .domain-badge.active {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
        }

        .domain-badge.local {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
        }

        .btn-dl {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.82rem;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }

        .btn-dl:hover {
            color: white;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
            transform: translateY(-1px);
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
    <div class="my-products-hero">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div style="position:relative;z-index:1;">
                <h2 class="fw-bold mb-1"><i class="bi bi-collection-fill me-2"></i>Produk Saya</h2>
                <p class="mb-0 opacity-75">Tema & modul yang sudah Anda miliki dari DK-DevStore</p>
            </div>
            <a href="{{ route('admin.devstore.index') }}" class="btn btn-light btn-sm"
                style="border-radius:10px; font-weight:600; position:relative; z-index:1;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Store
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" style="border-radius:12px;">
            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px;">
            {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        @forelse($products as $item)
            <div class="col-md-6 col-xl-4">
                <div class="card my-product-card h-100">
                    <div class="card-body p-4">
                        {{-- Header --}}
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div
                                style="width:50px;height:50px;border-radius:14px;background:linear-gradient(135deg,#f0f4ff,#e8ecff);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                @if(!empty($item['product']['thumbnail']))
                                    <img src="{{ $item['product']['thumbnail'] }}"
                                        style="width:30px;height:30px;object-fit:contain;">
                                @else
                                    <i class="bi bi-{{ ($item['product']['type'] ?? 'module') === 'theme' ? 'palette2' : 'puzzle' }} text-primary"
                                        style="font-size:1.4rem;"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1" style="font-size:0.95rem;">{{ $item['product']['name'] ?? 'Produk' }}
                                </h6>
                                <div class="d-flex align-items-center gap-2">
                                    <span
                                        class="badge {{ ($item['product']['type'] ?? '') === 'theme' ? 'bg-purple-subtle text-purple' : 'bg-info-subtle text-info' }}"
                                        style="font-size:0.65rem; padding:3px 8px; border-radius:6px;">
                                        {{ ucfirst($item['product']['type'] ?? 'module') }}
                                    </span>
                                    @if(!empty($item['product']['current_version']))
                                        <span class="badge bg-light text-dark"
                                            style="font-size:0.65rem;">v{{ $item['product']['current_version'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- License Key --}}
                        <div class="license-key-display mb-3">
                            <i class="bi bi-key text-muted"></i>
                            <span class="text-truncate">{{ $item['license_key'] ?? '-' }}</span>
                            <button type="button" class="btn btn-sm p-0 ms-auto border-0"
                                onclick="navigator.clipboard.writeText('{{ $item['license_key'] ?? '' }}');" title="Copy">
                                <i class="bi bi-clipboard text-muted" style="font-size:0.8rem;"></i>
                            </button>
                        </div>

                        {{-- License Info --}}
                        <div class="d-flex justify-content-between small mb-2">
                            <span class="text-muted">Status</span>
                            <span
                                class="fw-semibold {{ ($item['license_status'] ?? '') === 'active' ? 'text-success' : 'text-danger' }}">
                                {{ ucfirst($item['license_status'] ?? '-') }}
                            </span>
                        </div>
                        <div class="d-flex justify-content-between small mb-3">
                            <span class="text-muted">Domain</span>
                            <span class="fw-semibold">{{ $item['domains_used'] ?? 0 }} / {{ $item['domain_limit'] ?? 0 }}</span>
                        </div>

                        {{-- Domains --}}
                        @if(!empty($item['domains']))
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                @foreach($item['domains'] as $domain)
                                    <span class="domain-badge {{ ($domain['is_local'] ?? false) ? 'local' : 'active' }}">
                                        <i class="bi bi-{{ ($domain['is_local'] ?? false) ? 'laptop' : 'globe2' }}"
                                            style="font-size:0.65rem;"></i>
                                        {{ $domain['domain'] }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="card-footer bg-transparent border-top-0 p-4 pt-0">
                        <div class="d-flex gap-2">
                            @if(!empty($item['product']['download_url']))
                                <form action="{{ route('admin.devstore.install') }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="download_url" value="{{ $item['product']['download_url'] }}">
                                    <input type="hidden" name="type" value="{{ $item['product']['type'] ?? 'module' }}">
                                    <button type="submit" class="btn btn-dl w-100"
                                        onclick="this.innerHTML='<i class=\'bi bi-arrow-repeat spin me-1\'></i> Menginstal...';this.disabled=true;this.form.submit()">
                                        <i class="bi bi-download me-1"></i> Instal / Update
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.devstore.show', $item['product']['slug'] ?? '#') }}" class="btn btn-light"
                                style="border-radius:10px;">
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div
                    style="width:100px;height:100px;margin:0 auto 1.5rem;border-radius:50%;background:linear-gradient(135deg,#f0f4ff,#e8ecff);display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-collection text-primary" style="font-size:2.5rem; opacity:0.3;"></i>
                </div>
                <h5 class="fw-bold">Belum Ada Produk</h5>
                <p class="text-muted" style="max-width:360px; margin:0 auto;">
                    Anda belum memiliki tema atau modul dari DK-DevStore. Kunjungi store untuk melihat produk tersedia.
                </p>
                <a href="{{ route('admin.devstore.index') }}" class="btn btn-dl mt-3">
                    <i class="bi bi-box-seam me-2"></i> Jelajahi Store
                </a>
            </div>
        @endforelse
    </div>
@endsection