@extends('install.layout')
@section('title', 'Pengaturan Toko & Admin')

@section('steps')
    <div class="installer-steps">
        <div class="step-item completed">
            <div class="step-num"><i class="bi bi-check-lg"></i></div>
            <span class="step-label">Cek Sistem</span>
        </div>
        <div class="step-connector completed"></div>
        <div class="step-item completed">
            <div class="step-num"><i class="bi bi-check-lg"></i></div>
            <span class="step-label">Lisensi</span>
        </div>
        <div class="step-connector completed"></div>
        <div class="step-item completed">
            <div class="step-num"><i class="bi bi-check-lg"></i></div>
            <span class="step-label">Database</span>
        </div>
        <div class="step-connector completed"></div>
        <div class="step-item active">
            <div class="step-num">4</div>
            <span class="step-label">Toko</span>
        </div>
        <div class="step-connector"></div>
        <div class="step-item">
            <div class="step-num">5</div>
            <span class="step-label">Install</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="installer-card">
        <div class="installer-card-header">
            <h3><i class="bi bi-shop me-2"></i>Pengaturan Toko & Admin</h3>
            <p>Atur identitas toko online dan buat akun administrator pertama Anda.</p>
        </div>
        <div class="installer-card-body">
            <form action="/install/store" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Store Info --}}
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-shop-window me-1"></i> Identitas Toko</h6>

                <div class="mb-3">
                    <label class="form-label">Nama Toko <span class="text-danger">*</span></label>
                    <input type="text" name="store_name" class="form-control"
                        value="{{ old('store_name', 'JadiOrder') }}" required placeholder="Nama Toko Anda">
                </div>

                <div class="mb-3">
                    <label class="form-label">URL Website <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-globe"></i></span>
                        <input type="url" name="store_url" class="form-control"
                            value="{{ old('store_url', request()->getSchemeAndHttpHost()) }}" required
                            placeholder="https://example.com">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Logo Toko <small class="text-muted fw-normal">(opsional)</small></label>
                    <input type="file" name="store_logo" class="form-control" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG, SVG. Maks 2MB. Bisa diubah nanti di Pengaturan.</small>
                </div>

                <hr class="my-4">

                {{-- Admin Account --}}
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-person-badge me-1"></i> Akun Administrator</h6>

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="admin_name" class="form-control"
                            value="{{ old('admin_name', 'Administrator') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Admin <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="admin_email" class="form-control"
                            value="{{ old('admin_email') }}" required placeholder="admin@domain.com">
                    </div>
                    <small class="text-muted">Gunakan email valid untuk login ke panel admin.</small>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="admin_password" class="form-control"
                                required minlength="6" placeholder="Min. 6 karakter">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="admin_password_confirmation" class="form-control"
                                required minlength="6" placeholder="Ulangi password">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/install/database" class="btn btn-outline-installer">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-installer">
                        Lanjut ke Instalasi <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
