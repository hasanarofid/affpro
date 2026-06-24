@extends('admin.layouts.app')
@section('title', 'Modul & Plugin')
@section('page-title', 'Modul & Plugin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1">Modul & Plugin</h5>
            <p class="text-muted small mb-0">Kelola addon/plugin terintegrasi — payment gateway, WhatsApp API, kurir, dll
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.modules.marketplace') }}" class="btn btn-outline-primary" style="border-radius:10px">
                <i class="bi bi-box-seam me-1"></i> JDO-Store
            </a>
            @if(!$demoMode)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal" style="border-radius:10px">
                <i class="bi bi-cloud-arrow-up me-1"></i> Upload Modul
            </button>
            @endif
        </div>
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

    <div class="row g-4">
        @forelse($modules as $module)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100"
                    style="border-radius:14px;border:1px solid {{ $module['enabled'] ? '#e0e7ff' : '#eee' }}">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div
                                style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                                                                                background:{{ $module['enabled'] ? 'linear-gradient(135deg, var(--primary), var(--secondary))' : '#f0f0f0' }}">
                                @if(strtolower($module['alias'] ?? '') === 'rajaongkir')
                                    <i class="bi bi-truck {{ $module['enabled'] ? 'text-white' : 'text-muted' }}"
                                        style="font-size:1.3rem"></i>
                                @elseif(strtolower($module['alias'] ?? '') === 'kiriminaja')
                                    <i class="bi bi-box-seam {{ $module['enabled'] ? 'text-white' : 'text-muted' }}"
                                        style="font-size:1.3rem"></i>
                                @elseif(strtolower($module['alias'] ?? '') === 'whatsapp')
                                    <i class="bi bi-whatsapp {{ $module['enabled'] ? 'text-white' : 'text-muted' }}"
                                        style="font-size:1.3rem"></i>
                                @else
                                    <i class="bi bi-puzzle {{ $module['enabled'] ? 'text-white' : 'text-muted' }}"
                                        style="font-size:1.3rem"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0">{{ $module['name'] }}</h6>
                                <span class="badge {{ $module['enabled'] ? 'bg-success' : 'bg-secondary' }}"
                                    style="border-radius:6px;font-size:.7rem">
                                    {{ $module['enabled'] ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                        <p class="text-muted small mb-3">{{ $module['description'] }}</p>

                        {{-- Module-specific settings hints --}}
                        @if(strtolower($module['alias'] ?? '') === 'rajaongkir')
                            <div class="small text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i> Konfigurasi API key di <a
                                    href="{{ route('admin.settings.index') }}">Pengaturan → Pengiriman</a>
                            </div>
                        @elseif(strtolower($module['alias'] ?? '') === 'kiriminaja')
                            <div class="small text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i> Konfigurasi API key & origin di <a
                                    href="{{ route('admin.settings.index') }}">Pengaturan → Pengiriman</a>
                            </div>
                        @elseif(strtolower($module['alias'] ?? '') === 'whatsapp')
                            <div class="small text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i> Konfigurasi WA API di <a
                                    href="{{ route('admin.settings.index') }}">Pengaturan → Umum</a>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-top-0 pt-0 d-flex gap-2">
                        @if($module['is_system'] ?? false)
                            <button class="btn btn-sm w-100 btn-light text-muted" style="border-radius:8px; cursor:not-allowed;"
                                disabled>
                                <i class="bi bi-lock-fill me-1"></i> Modul Inti Sistem
                            </button>
                        @else
                            @if(!$demoMode)
                                <form action="{{ route('admin.modules.toggle') }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $module['name'] }}">
                                    <button class="btn btn-sm w-100 {{ $module['enabled'] ? 'btn-outline-warning' : 'btn-primary' }}"
                                        style="border-radius:8px">
                                        @if($module['enabled'])
                                            <i class="bi bi-pause-circle me-1"></i> Nonaktifkan
                                        @else
                                            <i class="bi bi-play-circle me-1"></i> Aktifkan
                                        @endif
                                    </button>
                                </form>
                                <form id="delete-module-{{ $loop->index }}" action="{{ route('admin.modules.delete') }}" method="POST">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="name" value="{{ $module['name'] }}">
                                    <button type="button" onclick="confirmDelete('delete-module-{{ $loop->index }}')"
                                        class="btn btn-sm btn-outline-danger" style="border-radius:8px">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="btn btn-light btn-sm w-100 disabled" style="border-radius:8px">Read Only</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-puzzle" style="font-size:3rem;opacity:.3"></i>
                    <p class="mt-2">Belum ada modul terinstal.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:16px;border:none">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Upload Modul Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.modules.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="border rounded p-4 text-center"
                            style="border-radius:12px!important;border-style:dashed!important">
                            <i class="bi bi-file-earmark-zip" style="font-size:2.5rem;color:var(--primary)"></i>
                            <p class="small text-muted mt-2 mb-3">Upload file ZIP modul (maks 50MB)</p>
                            <input type="file" name="module_zip" class="form-control" accept=".zip" required>
                        </div>
                        <div class="mt-3 small text-muted">
                            <strong>Format Modul:</strong> File ZIP harus menggunakan ekstensi struktur modul yang valid
                            (nwidart/laravel-modules) dengan adanya <code>module.json</code>.
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius:8px">Batal</button>
                        <button type="submit" class="btn btn-primary" style="border-radius:8px">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Upload & Install
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection