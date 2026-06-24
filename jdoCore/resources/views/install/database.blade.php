@extends('install.layout')
@section('title', 'Konfigurasi Database')

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
        <div class="step-item active">
            <div class="step-num">3</div>
            <span class="step-label">Database</span>
        </div>
        <div class="step-connector"></div>
        <div class="step-item">
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
            <h3><i class="bi bi-database me-2"></i>Konfigurasi Database</h3>
            <p>Masukkan informasi database MySQL yang sudah Anda buat di hosting/server.</p>
        </div>
        <div class="installer-card-body">
            <div class="alert installer-alert" style="background: #EFF6FF; color: #1E40AF;">
                <i class="bi bi-info-circle me-2"></i>
                Pastikan Anda sudah membuat database kosong terlebih dahulu di cPanel atau phpMyAdmin.
            </div>

            <form action="/install/database" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Database Host <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hdd-network"></i></span>
                            <input type="text" name="db_host" class="form-control"
                                value="{{ old('db_host', '127.0.0.1') }}" required>
                        </div>
                        <small class="text-muted">Biasanya <code>127.0.0.1</code> atau <code>localhost</code></small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Port <span class="text-danger">*</span></label>
                        <input type="number" name="db_port" class="form-control"
                            value="{{ old('db_port', '3306') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Database <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-database"></i></span>
                        <input type="text" name="db_database" class="form-control"
                            value="{{ old('db_database') }}" placeholder="nama_database" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Username Database <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="db_username" class="form-control"
                                value="{{ old('db_username', 'root') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password Database</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="db_password" class="form-control"
                                value="{{ old('db_password') }}" placeholder="Kosongkan jika tidak ada">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/install/license" class="btn btn-outline-installer">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-installer">
                        Tes Koneksi & Lanjut <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
