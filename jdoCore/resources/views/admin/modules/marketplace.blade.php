@extends('admin.layouts.app')
@section('title', 'DK-DevStore Modul')
@section('page-title', 'DK-DevStore Modul')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1"><i class="bi bi-box-seam me-2"></i>DK-DevStore Modul</h5>
            <p class="text-muted small mb-0">Temukan dan install modul tambahan (Payment Gateway, Kurir, dll)</p>
        </div>
        <a href="{{ route('admin.modules.index') }}" class="btn btn-light" style="border-radius:10px">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px">
            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px">
            {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!($license['valid'] ?? false))
        <div class="alert alert-warning" style="border-radius:12px;border:none">
            <div class="d-flex">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-warning"></i>
                <div>
                    <h6 class="fw-bold mb-1">License Key Belum Terhubung</h6>
                    <p class="small mb-2">Untuk menginstall modul dari DK-DevStore, Anda membutuhkan License Key yang aktif.</p>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-dark" style="border-radius:8px">Masukan
                        License Key</a>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info bg-white border-0 py-2 px-3 d-inline-flex mb-4"
            style="border-radius:20px;box-shadow:0 2px 10px rgba(0,0,0,.02)">
            <i class="bi bi-check-circle-fill text-success mt-1 me-2"></i>
            <span class="small">License Aktif: <strong>{{ $license['data']['product'] ?? 'JadiOrder' }}</strong></span>
        </div>
    @endif

    <div class="row g-4">
        @forelse($items['products'] ?? [] as $item)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0"
                    style="border-radius:14px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,.04)">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div
                                style="width:50px;height:50px;border-radius:12px;background:#f8f9ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                @if(!empty($item['thumbnail']))
                                    <img src="{{ $item['thumbnail'] }}" style="width:30px;height:30px;object-fit:contain">
                                @else
                                    <i class="bi bi-puzzle text-primary" style="font-size:1.5rem"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0">
                                    {{ $item['name'] }}
                                    @if($item['is_featured'] ?? false)
                                        <i class="bi bi-star-fill text-warning" style="font-size:.8rem" title="Unggulan"></i>
                                    @endif
                                    @if($item['is_owned'] ?? false)
                                        <i class="bi bi-check-circle-fill text-success" style="font-size:.8rem"
                                            title="Dimiliki"></i>
                                    @endif
                                </h6>
                                <span class="badge bg-light text-dark mt-1">v{{ $item['latest_version'] ?? '1.0' }}</span>
                            </div>
                        </div>
                        <p class="text-muted small mb-2" style="min-height: 40px">
                            {{ \Illuminate\Support\Str::limit($item['short_description'] ?? '', 80) }}
                        </p>
                        @if(isset($item['price']))
                            <span class="fw-bold small" style="color:var(--bs-primary);">
                                @if($item['price'] > 0)
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                @else
                                    <span class="text-success">Gratis</span>
                                @endif
                            </span>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-top-0 pb-3">
                        @if($license['valid'] ?? false)
                            <a href="{{ route('admin.devstore.show', $item['slug']) }}" class="btn btn-primary w-100"
                                style="border-radius:8px">
                                <i class="bi bi-eye me-1"></i> Lihat Detail
                            </a>
                        @else
                            <button class="btn btn-light w-100 disabled" style="border-radius:8px">
                                Butuh Lisensi Aktif
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-box-seam text-muted opacity-25" style="font-size:4rem"></i>
                <h6 class="fw-bold mt-3">Tidak ada data di DK-DevStore</h6>
                <p class="text-muted small">Pastikan server dapat terhubung ke internet dan URL DK-DevStore sesuai.</p>
            </div>
        @endforelse
    </div>
@endsection