@extends('admin.layouts.app')
@section('title', 'Template Toko')
@section('page-title', 'Template Toko')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1">Template / Tema</h5>
            <p class="text-muted small mb-0">Kelola tampilan storefront toko Anda</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.themes.marketplace') }}" class="btn btn-outline-primary" style="border-radius:10px">
                <i class="bi bi-box-seam me-1"></i> JDO-Store
            </a>
            @if(!$demoMode)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal" style="border-radius:10px">
                <i class="bi bi-cloud-arrow-up me-1"></i> Upload Tema
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
        @forelse($themes as $theme)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 {{ $theme['active'] ? 'border-primary' : '' }}"
                    style="border-radius:14px;overflow:hidden;{{ $theme['active'] ? 'border-width:2px' : 'border:1px solid #eee' }}">
                    {{-- Thumbnail --}}
                    <div
                        style="height:180px;background:linear-gradient(135deg,{{ $theme['active'] ? 'var(--primary)' : '#667eea' }},{{ $theme['active'] ? 'var(--secondary)' : '#764ba2' }});display:flex;align-items:center;justify-content:center;position:relative">
                        @if($theme['thumbnail'] && file_exists(resource_path('themes/' . $theme['slug'] . '/' . $theme['thumbnail'])))
                            <img src="{{ asset('storage/themes/' . $theme['slug'] . '/' . $theme['thumbnail']) }}"
                                style="width:100%;height:100%;object-fit:cover">
                        @else
                            <i class="bi bi-palette2 text-white" style="font-size:3rem;opacity:.5"></i>
                        @endif
                        @if($theme['active'])
                            <span class="position-absolute top-0 end-0 m-2 badge bg-success"
                                style="border-radius:8px;padding:6px 12px">
                                <i class="bi bi-check-circle me-1"></i> Aktif
                            </span>
                        @endif
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">{{ $theme['name'] }}</h6>
                        <p class="text-muted small mb-2">{{ $theme['description'] ?: 'Tidak ada deskripsi' }}</p>
                        <span class="badge bg-light text-dark" style="border-radius:6px">v{{ $theme['version'] }}</span>
                    </div>
                    <div class="card-footer bg-white border-top-0 d-flex gap-2">
                        @if(!$theme['active'])
                                <form action="{{ route('admin.themes.activate') }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="slug" value="{{ $theme['slug'] }}">
                                    <button class="btn btn-primary btn-sm w-100" style="border-radius:8px">
                                        <i class="bi bi-check-lg me-1"></i> Aktifkan
                                    </button>
                                </form>
                                @if($theme['slug'] !== 'default')
                                    @if(!$demoMode)
                                    <form id="delete-theme-{{ $loop->index }}" action="{{ route('admin.themes.delete') }}" method="POST">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="slug" value="{{ $theme['slug'] }}">
                                        <button type="button" onclick="confirmDelete('delete-theme-{{ $loop->index }}')"
                                            class="btn btn-outline-danger btn-sm" style="border-radius:8px">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <button type="button" class="btn btn-outline-danger btn-sm" style="border-radius:8px" title="Hapus dinonaktifkan di mode demo" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                @endif
                        @else
                            <span class="btn btn-light btn-sm w-100 disabled" style="border-radius:8px">Tema Aktif Saat Ini</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">
                    <i class="bi bi-palette2" style="font-size:3rem;opacity:.3"></i>
                    <p class="mt-2">Belum ada tema. Upload tema pertama Anda.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:16px;border:none">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Upload Tema Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.themes.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="border rounded p-4 text-center"
                            style="border-radius:12px!important;border-style:dashed!important">
                            <i class="bi bi-file-earmark-zip" style="font-size:2.5rem;color:var(--primary)"></i>
                            <p class="small text-muted mt-2 mb-3">Upload file ZIP tema (maks 20MB)</p>
                            <input type="file" name="theme_zip" class="form-control" accept=".zip" required>
                        </div>
                        <div class="mt-3 small text-muted">
                            <strong>Format tema:</strong> File ZIP berisi folder tema dengan <code>config.json</code>,
                            <code>layouts/app.blade.php</code>, dan file Blade lainnya.
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                            style="border-radius:8px">Batal</button>
                        <button type="submit" class="btn btn-primary" style="border-radius:8px">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Upload & Extract
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection